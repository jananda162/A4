<?php
require_once '../config.php';

// Check if the user is logged in, otherwise redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate date
    $date = trim($_POST["date"]);
    if(empty($date)){
        header("location: ../stock_entry.php?error=Date is required.");
        exit;
    }
    // Further date validation if necessary (e.g., format)

    // Validate quantity_received
    $quantity_received = trim($_POST["quantity_received"]);
    if(empty($quantity_received) || !ctype_digit($quantity_received) || (int)$quantity_received <= 0){
        header("location: ../stock_entry.php?error=Quantity must be a positive number.");
        exit;
    }

    // Validate received_by
    $received_by = trim($_POST["received_by"]);
    if(empty($received_by)){
        header("location: ../stock_entry.php?error=Received By is required.");
        exit;
    }
    if(strlen($received_by) > 255){
        header("location: ../stock_entry.php?error=Received By cannot exceed 255 characters.");
        exit;
    }

    // Prepare an insert statement
    $sql = "INSERT INTO paper_stock (date, quantity_received, received_by) VALUES (?, ?, ?)";

    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "sis", $param_date, $param_quantity, $param_received_by);

        // Set parameters
        $param_date = $date;
        $param_quantity = (int)$quantity_received;
        $param_received_by = $received_by;

        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            header("location: ../stock_entry.php?status=success");
            exit;
        } else{
            header("location: ../stock_entry.php?error=Database error: " . mysqli_error($conn));
            exit;
        }
        mysqli_stmt_close($stmt);
    } else {
        header("location: ../stock_entry.php?error=Database error: Could not prepare statement.");
        exit;
    }
    mysqli_close($conn);
} else {
    // If not a POST request, redirect to form or show error
    header("location: ../stock_entry.php?error=Invalid request method.");
    exit;
}
?>
