<?php
session_start();
include('../db/conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $clave = $_POST['clave'];

    $sql = "SELECT * FROM usuarios WHERE usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$usuario]);
    $usuarioEncontrado = $stmt->fetch();

    if ($usuarioEncontrado && $clave === $usuarioEncontrado['clave']) {
        $_SESSION['admin'] = $usuarioEncontrado['usuario'];
        $_SESSION['admin_id'] = $usuarioEncontrado['id'];
        header("Location: /CoopApp/admin/panel.php");
        exit;
    } else {
        echo "<script>alert('Usuario o contraseña incorrectos'); window.location='../login.html';</script>";
        exit;
    }
}
?>