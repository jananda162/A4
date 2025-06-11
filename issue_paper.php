<?php
$baseUrl = ''; // Or determine dynamically
require_once 'config.php';

// Check if the user is logged in, otherwise redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once 'includes/header.php';

// Define departments
$departments = ["Merchandising", "Commercial", "CAD", "Finance"];
?>

<div class="container">
    <h2>Issue Paper to Department</h2>
    <?php
    if(isset($_GET['status']) && $_GET['status'] == 'success'){
        echo '<p style="color:green;">Paper issued successfully!</p>';
    }
    if(isset($_GET['error'])){
        echo '<p style="color:red;">Error: ' . htmlspecialchars($_GET['error']) . '</p>';
    }
    ?>
    <form action="actions/issue_paper_action.php" method="post">
        <div>
            <label for="date">Date:</label>
            <input type="date" id="date" name="date" required>
        </div>
        <div>
            <label for="department">Department:</label>
            <select id="department" name="department" required>
                <option value="">Select Department</option>
                <?php foreach($departments as $dept): ?>
                    <option value="<?php echo htmlspecialchars($dept); ?>"><?php echo htmlspecialchars($dept); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="quantity_issued">Quantity Issued:</label>
            <input type="number" id="quantity_issued" name="quantity_issued" min="1" required>
        </div>
        <div>
            <label for="issued_by">Issued By:</label>
            <input type="text" id="issued_by" name="issued_by" maxlength="255" required>
        </div>
        <div>
            <input type="submit" value="Issue Paper">
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
