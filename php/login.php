<?php
session_start();
include('../db/conexion.php');

echo "DEBUG: Entró al PHP<br>";  // <-- Agrega esta línea

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $clave = $_POST['clave'];

    echo "DEBUG: Usuario recibido: $usuario<br>"; // <-- Y esta otra


    $sql = "SELECT * FROM usuarios WHERE usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$usuario]);
    $usuarioEncontrado = $stmt->fetch();

    if ($usuarioEncontrado && password_verify($clave, $usuarioEncontrado['clave'])) {
        $_SESSION['admin'] = $usuario;
        header("Location: /CoopApp/admin/panel.php");
        exit;
    } else {
        echo "<script>alert('Usuario o contraseña incorrectos'); window.location='../login.html';</script>";
        exit;
    }
}
?>