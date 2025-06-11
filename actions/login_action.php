<?php
require_once '../config.php'; // Session is started in config.php

$username = "";
$password = "";
$login_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Check if username is empty
    if(empty(trim($_POST["username"]))){
        $login_err = "Please enter username.";
    } else{
        $username = trim($_POST["username"]);
    }

    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $login_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    if(empty($login_err)){
        // Prepare a select statement
        $sql = "SELECT id, username, password FROM users WHERE username = ?";

        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;

            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);

                // Check if username exists, if yes then verify password
                if(mysqli_stmt_num_rows($stmt) == 1){
                    mysqli_stmt_bind_result($stmt, $id, $db_username, $hashed_password);
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
                            // Password is correct, so start a new session
                            // session_start(); // Already started in config.php

                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $db_username;

                            // Redirect user to welcome page (index.php)
                            header("location: ../index.php");
                            exit;
                        } else{
                            // Password is not valid
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else{
                    // Username doesn't exist
                    $login_err = "Invalid username or password.";
                }
            } else{
                $login_err = "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $login_err = "Database error: Could not prepare statement.";
        }
    }

    // If there were any errors, redirect back to login page with error
    if(!empty($login_err)){
        header("location: ../login.php?error=" . urlencode($login_err));
        exit;
    }
    mysqli_close($conn);

} else {
    // Not a POST request, redirect to login page or show error
    header("location: ../login.php?error=Invalid request.");
    exit;
}
?>
