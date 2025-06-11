<?php
$baseUrl = ''; // Or determine dynamically
require_once 'config.php';

// Check if the user is logged in, otherwise redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once 'includes/header.php';
?>

<div class="container">
    <h2>Add New Paper Stock</h2>
    <?php
    if(isset($_GET['status']) && $_GET['status'] == 'success'){
        echo '<p style="color:green;">Stock added successfully!</p>';
    }
    if(isset($_GET['error'])){
        echo '<p style="color:red;">Error: ' . htmlspecialchars($_GET['error']) . '</p>';
    }
    ?>
    <form action="actions/add_stock_action.php" method="post">
        <div>
            <label for="date">Date:</label>
            <input type="date" id="date" name="date" required>
        </div>
        <div>
            <label for="quantity_received">Quantity Received:</label>
            <input type="number" id="quantity_received" name="quantity_received" min="1" required>
        </div>
        <div>
            <label for="received_by">Received By:</label>
            <input type="text" id="received_by" name="received_by" maxlength="255" required>
        </div>
        <div>
            <input type="submit" value="Add Stock">
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
