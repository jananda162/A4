<?php
$baseUrl = ''; // Or determine dynamically
require_once 'config.php';

// Check if the user is logged in, otherwise redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once 'includes/header.php';

// Initialize variables
$total_received = 0;
$total_issued_grand = 0;
$issued_per_department = [];
$departments_list = ["Merchandising", "Commercial", "CAD", "Finance"]; // Ensure all depts are shown

// Initialize issued_per_department for all known departments to ensure they appear in the report
foreach ($departments_list as $dept) {
    $issued_per_department[htmlspecialchars($dept)] = 0;
}

// --- Calculate Total Received ---
$sql_total_received = "SELECT SUM(quantity_received) AS total_received FROM paper_stock";
$result_total_received = mysqli_query($conn, $sql_total_received);
if ($result_total_received && mysqli_num_rows($result_total_received) > 0) {
    $row = mysqli_fetch_assoc($result_total_received);
    $total_received = $row['total_received'] ? (int)$row['total_received'] : 0;
} else {
    // Error or no stock received yet
    // echo "<p style='color:red;'>Error fetching total received: " . mysqli_error($conn) . "</p>";
}

// --- Calculate Total Issued per Department ---
$sql_issued_dept = "SELECT department, SUM(quantity_issued) AS total_issued FROM paper_issues GROUP BY department";
$result_issued_dept = mysqli_query($conn, $sql_issued_dept);
if ($result_issued_dept) {
    while ($row = mysqli_fetch_assoc($result_issued_dept)) {
        if (!empty($row['department']) && in_array($row['department'], $departments_list)) { // Ensure department is known
            $issued_per_department[htmlspecialchars($row['department'])] = (int)$row['total_issued'];
        }
    }
} else {
    // Error fetching issued per department
    // echo "<p style='color:red;'>Error fetching issued per department: " . mysqli_error($conn) . "</p>";
}

// --- Calculate Grand Total Issued ---
// This approach sums up from the $issued_per_department array, which is fine
// as it's initialized and populated carefully.
foreach ($issued_per_department as $dept_total) {
    $total_issued_grand += $dept_total;
}
// Alternative: Query SUM(quantity_issued) from paper_issues without GROUP BY for grand total.
// $sql_grand_total_issued = "SELECT SUM(quantity_issued) AS grand_total_issued FROM paper_issues";
// $res_grand_total_issued = mysqli_query($conn, $sql_grand_total_issued);
// if ($res_grand_total_issued && mysqli_num_rows($res_grand_total_issued) > 0) {
//     $row_grand = mysqli_fetch_assoc($res_grand_total_issued);
//     $total_issued_grand = $row_grand['grand_total_issued'] ? (int)$row_grand['grand_total_issued'] : 0;
// }


// --- Calculate Closing Balance ---
$closing_balance = $total_received - $total_issued_grand;

// --- Opening Stock Definition ---
// For this report, Opening Stock is considered 0, representing the state before any transactions.
$opening_stock = 0;

?>

<div class="container">
    <h2>Tally Report</h2>

    <div class="tally-section">
        <h3>Overall Stock Summary</h3>
        <ul>
            <li><strong>Opening Stock (System Start):</strong> <?php echo $opening_stock; ?> sheets</li>
            <li><strong>Total Paper Received:</strong> <?php echo htmlspecialchars($total_received); ?> sheets</li>
            <li><strong>Grand Total Paper Issued:</strong> <?php echo htmlspecialchars($total_issued_grand); ?> sheets</li>
            <li><strong>Closing Balance (Current Stock):</strong> <?php echo htmlspecialchars($closing_balance); ?> sheets</li>
        </ul>
    </div>

    <div class="tally-section">
        <h3>Paper Issued per Department</h3>
        <ul>
            <?php foreach($issued_per_department as $dept => $qty): ?>
                <li><strong><?php echo $dept; // Already htmlspecialchar'd when populating array keys ?>:</strong> <?php echo htmlspecialchars($qty); ?> sheets</li>
            <?php endforeach; ?>
        </ul>
    </div>

    <p style="font-size:0.9em; color:grey;">
        Note: 'Opening Stock' for this report is assumed to be 0, representing the balance before any system transactions.
        This report reflects all-time totals.
    </p>

</div>

<?php require_once 'includes/footer.php'; ?>
