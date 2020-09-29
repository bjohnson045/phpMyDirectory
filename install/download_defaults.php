<?php
session_start();
include('../includes/class_serve_file.php');
$serve = new ServeFile(null);
$serve->serve('defaults.php', $_SESSION['defaults_content']);
?>