</main>
<footer>
    <p>&copy; <?php echo date("Y"); ?> Paper Stock Management System</p>
</footer>
<?php
// Close database connection
if(isset($conn)){
    mysqli_close($conn);
}
?>
<script src="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>js/script.js"></script>
</body>
</html>
