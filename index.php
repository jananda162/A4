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
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h2>
    <p>This is the main dashboard. The Tally Report will be displayed here.</p>
    <p><a href="logout.php">Logout</a></p>
</div>

<?php require_once 'includes/footer.php'; ?>
