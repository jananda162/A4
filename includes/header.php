<?php require_once __DIR__ . '/../config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A4 Paper Stock Management</title>
    <link rel="stylesheet" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>css/style.css">
</head>
<body>
<header>
    <h1>A4 Paper Stock Management System</h1>
    <nav>
        <ul>
            <li><a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>index.php">Home</a></li>
            <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                <li><a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>stock_entry.php">Stock Entry</a></li>
                <li><a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>issue_paper.php">Issue Paper</a></li>
                <li><a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>ledger_report.php">Ledger Report</a></li>
                <li><a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>login.php">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<main>
