<?php
$baseUrl = ''; // Or determine dynamically
require_once 'config.php';

// Check if the user is logged in, otherwise redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once 'includes/header.php';

// Define departments and transaction types for filters
$departments = ["Merchandising", "Commercial", "CAD", "Finance"];
$transaction_types = ['all' => 'All', 'received' => 'Received', 'issued' => 'Issued'];

// Get filter values safely
$filter_start_date = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$filter_end_date = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';
$filter_department = isset($_GET['department']) ? trim($_GET['department']) : '';
$filter_type = isset($_GET['type']) && array_key_exists($_GET['type'], $transaction_types) ? $_GET['type'] : 'all';

$transactions = [];

// --- SQL Construction ---
$where_clauses_received = [];
$where_clauses_issued = [];

if(!empty($filter_start_date)) {
    $esc_start_date = mysqli_real_escape_string($conn, $filter_start_date);
    $where_clauses_received[] = "date >= '$esc_start_date'";
    $where_clauses_issued[] = "date >= '$esc_start_date'";
}
if(!empty($filter_end_date)) {
    $esc_end_date = mysqli_real_escape_string($conn, $filter_end_date);
    $where_clauses_received[] = "date <= '$esc_end_date'";
    $where_clauses_issued[] = "date <= '$esc_end_date'";
}
if(!empty($filter_department)) {
    $esc_department = mysqli_real_escape_string($conn, $filter_department);
    $where_clauses_issued[] = "department = '$esc_department'";
}

$sql_received_base = "SELECT id, date, quantity_received, received_by, NULL as quantity_issued, NULL as department, NULL as issued_by, 'received' as type FROM paper_stock";
$sql_issued_base = "SELECT id, date, NULL as quantity_received, NULL as received_by, quantity_issued, department, issued_by, 'issued' as type FROM paper_issues";

$sql_received = $sql_received_base;
if (!empty($where_clauses_received)) {
    $sql_received .= " WHERE " . implode(" AND ", $where_clauses_received);
}

$sql_issued = $sql_issued_base;
if (!empty($where_clauses_issued)) {
    $sql_issued .= " WHERE " . implode(" AND ", $where_clauses_issued);
}

// --- Fetch Data ---
if ($filter_type == 'all' || $filter_type == 'received') {
    $result_received = mysqli_query($conn, $sql_received);
    if ($result_received) {
        while ($row = mysqli_fetch_assoc($result_received)) {
            $transactions[] = $row;
        }
    } else {
        echo "<p style='color:red;'>Error fetching received stock: " . mysqli_error($conn) . "</p>";
    }
}

if ($filter_type == 'all' || $filter_type == 'issued') {
    $result_issued = mysqli_query($conn, $sql_issued);
    if ($result_issued) {
        while ($row = mysqli_fetch_assoc($result_issued)) {
            $transactions[] = $row;
        }
    } else {
        echo "<p style='color:red;'>Error fetching issued stock: " . mysqli_error($conn) . "</p>";
    }
}

// --- Sort Transactions ---
usort($transactions, function($a, $b) {
    if ($a['date'] == $b['date']) {
        if ($a['type'] == 'received' && $b['type'] == 'issued') return -1;
        if ($a['type'] == 'issued' && $b['type'] == 'received') return 1;
        // Fallback to ID comparison if types are same or one is null (should not happen with current SQL)
        $id_a = isset($a['id']) ? (int)$a['id'] : 0;
        $id_b = isset($b['id']) ? (int)$b['id'] : 0;
        return $id_a <=> $id_b;
    }
    // Ensure dates are comparable, e.g. by converting to timestamp or using string comparison if format is consistent
    $time_a = strtotime($a['date']);
    $time_b = strtotime($b['date']);
    return $time_a <=> $time_b;
});

?>

<div class="container">
    <h2>Ledger Report</h2>

    <form method="GET" action="ledger_report.php" class="filter-form">
        <h4>Filters:</h4>
        <div class="filter-group">
            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($filter_start_date); ?>">
        </div>
        <div class="filter-group">
            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" id="end_date" value="<?php echo htmlspecialchars($filter_end_date); ?>">
        </div>
        <div class="filter-group">
            <label for="department">Department:</label>
            <select name="department" id="department">
                <option value="">All Departments</option>
                <?php foreach($departments as $dept): ?>
                    <option value="<?php echo htmlspecialchars($dept); ?>" <?php echo ($filter_department == $dept) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($dept); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label for="type">Transaction Type:</label>
            <select name="type" id="type">
                <?php foreach($transaction_types as $key => $value): ?>
                    <option value="<?php echo htmlspecialchars($key); ?>" <?php echo ($filter_type == $key) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($value); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <input type="submit" value="Apply Filters">
            <a href="ledger_report.php" class="button-link">Clear Filters</a>
        </div>
    </form>

    <table class="ledger-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Department</th>
                <th>Received Qty</th>
                <th>Issued Qty</th>
                <th>Done By</th>
                <th>Running Balance</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // --- Calculate Opening Balance ---
            $opening_balance = 0;
            if (!empty($filter_start_date)) {
                $ob_calc_date_escaped = mysqli_real_escape_string($conn, $filter_start_date);

                $sum_received_before_start = 0;
                $sql_ob_r_sum = "SELECT SUM(quantity_received) as total FROM paper_stock WHERE date < '$ob_calc_date_escaped'";
                $res_ob_r = mysqli_query($conn, $sql_ob_r_sum);
                if ($res_ob_r && mysqli_num_rows($res_ob_r) > 0) {
                    $row = mysqli_fetch_assoc($res_ob_r);
                    $sum_received_before_start = $row['total'] ? (int)$row['total'] : 0;
                }

                $sum_issued_before_start = 0;
                $sql_ob_i_sum = "SELECT SUM(quantity_issued) as total FROM paper_issues WHERE date < '$ob_calc_date_escaped'";
                if (!empty($filter_department)) {
                    $sql_ob_i_sum .= " AND department = '" . mysqli_real_escape_string($conn, $filter_department) . "'";
                }
                $res_ob_i = mysqli_query($conn, $sql_ob_i_sum);
                if ($res_ob_i && mysqli_num_rows($res_ob_i) > 0) {
                     $row = mysqli_fetch_assoc($res_ob_i);
                    $sum_issued_before_start = $row['total'] ? (int)$row['total'] : 0;
                }

                if ($filter_type == 'received') {
                    $opening_balance = $sum_received_before_start;
                } else if ($filter_type == 'issued') {
                    // If filtering by "issued", OB is (all received before start) - (issued for specific dept OR all issued before start)
                    $opening_balance = $sum_received_before_start - $sum_issued_before_start;
                } else { // 'all' types
                    $opening_balance = $sum_received_before_start - $sum_issued_before_start;
                }

                echo "<tr>";
                echo "<td colspan='6'>Opening Balance (as of " . htmlspecialchars($filter_start_date) . ")</td>";
                echo "<td>" . htmlspecialchars($opening_balance) . "</td>";
                echo "</tr>";
            }
            $running_balance = $opening_balance;

            // --- Display Transactions ---
            if(empty($transactions)): ?>
                <tr><td colspan="7">No transactions found for the selected filters.</td></tr>
            <?php else: ?>
                <?php foreach($transactions as $t):
                    $description = '';
                    $dept_display = !empty($t['department']) ? htmlspecialchars($t['department']) : '';
                    $qty_r_display = '';
                    $qty_i_display = '';
                    $actor_display = ''; // Will hold received_by or issued_by

                    if($t['type'] == 'received'){
                        $description = "Stock Received";
                        $qty_r_val = (int)$t['quantity_received'];
                        $qty_r_display = $qty_r_val;
                        $running_balance += $qty_r_val;
                        $actor_display = !empty($t['received_by']) ? htmlspecialchars($t['received_by']) : 'N/A';
                    } else { // 'issued'
                        $description = "Paper Issued to " . htmlspecialchars($t['department']);
                        $qty_i_val = (int)$t['quantity_issued'];
                        $qty_i_display = $qty_i_val;
                        $running_balance -= $qty_i_val;
                        $actor_display = !empty($t['issued_by']) ? htmlspecialchars($t['issued_by']) : 'N/A';
                    }
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($t['date']); ?></td>
                    <td><?php echo $description; ?></td>
                    <td><?php echo $dept_display; ?></td>
                    <td><?php echo $qty_r_display; ?></td>
                    <td><?php echo $qty_i_display; ?></td>
                    <td><?php echo $actor_display; ?></td>
                    <td><?php echo $running_balance; ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>
