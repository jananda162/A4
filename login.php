<?php
// Initialize the session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.php");
    exit;
}
$baseUrl = ''; // Or determine dynamically
require_once 'config.php';
require_once 'includes/header.php';
?>

<div class="container">
    <h2>Login</h2>
    <p>Please fill in your credentials to login.</p>
    <form action="actions/login_action.php" method="post">
        <div>
            <label>Username</label>
            <input type="text" name="username" required>
        </div>
        <div>
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <div>
            <input type="submit" value="Login">
        </div>
        <?php
        if(isset($_GET['error'])) {
            echo '<p style="color:red;">' . htmlspecialchars($_GET['error']) . '</p>';
        }
        ?>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
