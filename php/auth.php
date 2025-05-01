<?php
session_start();

// Redirige si no está logueado
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.html");
    exit;
}

// Protección contra CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Inactividad de 30 minutos
$inactivity_limit = 1800; // 30 minutos
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactivity_limit)) {
    session_unset();
    session_destroy();
    header("Location: ../login.html?timeout=1");
    exit;
}
$_SESSION['last_activity'] = time();

// Conexión a base de datos
require_once 'conexion.php';
?>
