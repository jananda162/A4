<?php
require_once '../config.php';

// Check if the user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

$departments = ["Merchandising", "Commercial", "CAD", "Finance"];

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate date
    $date = trim($_POST["date"]);
    if(empty($date)){
        header("location: ../issue_paper.php?error=Date is required.");
        exit;
    }

    // Validate department
    $department = trim($_POST["department"]);
    if(empty($department) || !in_array($department, $departments)){
        header("location: ../issue_paper.php?error=Invalid department selected.");
        exit;
    }

    // Validate quantity_issued
    $quantity_issued = trim($_POST["quantity_issued"]);
    if(empty($quantity_issued) || !ctype_digit($quantity_issued) || (int)$quantity_issued <= 0){
        header("location: ../issue_paper.php?error=Issued quantity must be a positive number.");
        exit;
    }
    $quantity_issued_int = (int)$quantity_issued;

    // Validate issued_by
    $issued_by = trim($_POST["issued_by"]);
    if(empty($issued_by)){
        header("location: ../issue_paper.php?error=Issued By is required.");
        exit;
    }
    if(strlen($issued_by) > 255){
        header("location: ../issue_paper.php?error=Issued By cannot exceed 255 characters.");
        exit;
    }

    // Check available stock
    $total_received = 0;
    $sql_received = "SELECT SUM(quantity_received) AS total_received FROM paper_stock";
    if($result_received = mysqli_query($conn, $sql_received)){
        $row_received = mysqli_fetch_assoc($result_received);
        $total_received = $row_received['total_received'] ? (int)$row_received['total_received'] : 0;
    } else {
        header("location: ../issue_paper.php?error=Error fetching received stock: " . mysqli_error($conn));
        exit;
    }

    $total_issued_previously = 0;
    $sql_issued_prev = "SELECT SUM(quantity_issued) AS total_issued FROM paper_issues";
    if($result_issued_prev = mysqli_query($conn, $sql_issued_prev)){
        $row_issued_prev = mysqli_fetch_assoc($result_issued_prev);
        $total_issued_previously = $row_issued_prev['total_issued'] ? (int)$row_issued_prev['total_issued'] : 0;
    } else {
        header("location: ../issue_paper.php?error=Error fetching issued stock: " . mysqli_error($conn));
        exit;
    }

    $available_stock = $total_received - $total_issued_previously;

    if($quantity_issued_int > $available_stock){
        header("location: ../issue_paper.php?error=Insufficient stock. Available: " . $available_stock);
        exit;
    }

    // Prepare an insert statement for paper_issues
    $sql_insert_issue = "INSERT INTO paper_issues (date, department, quantity_issued, issued_by) VALUES (?, ?, ?, ?)";

    if($stmt = mysqli_prepare($conn, $sql_insert_issue)){
        mysqli_stmt_bind_param($stmt, "ssis", $param_date, $param_department, $param_quantity_issued, $param_issued_by);

        $param_date = $date;
        $param_department = $department;
        $param_quantity_issued = $quantity_issued_int;
        $param_issued_by = $issued_by;

        if(mysqli_stmt_execute($stmt)){
            header("location: ../issue_paper.php?status=success");
            exit;
        } else {
            header("location: ../issue_paper.php?error=Database error issuing paper: " . mysqli_error($conn));
            exit;
        }
        mysqli_stmt_close($stmt);
    } else {
        header("location: ../issue_paper.php?error=Database error: Could not prepare issue statement.");
        exit;
    }
    mysqli_close($conn);
} else {
    header("location: ../issue_paper.php?error=Invalid request method.");
    exit;
}
?>
