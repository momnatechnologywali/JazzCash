<?php
// logout.php
include 'db.php';
session_destroy();
echo "<script>window.location.href = 'index.php';</script>";
exit;
?>
Syntax highlighting powered by GeSHi
