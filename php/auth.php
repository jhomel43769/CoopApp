<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.html");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$inactivity_limit = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactivity_limit)) {
    session_unset();
    session_destroy();
    header("Location: ../login.html?timeout=1");
    exit;
}
$_SESSION['last_activity'] = time();

require_once 'conexion.php';
?>