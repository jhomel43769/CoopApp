<?php
session_start();
require_once '../../db/conexion.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.html");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: listar.php");
    exit;
}

$id = $_GET['id'];

try {
    $stmt = $conexion->prepare("DELETE FROM servicios WHERE id = :id");
    $stmt->execute([':id' => $id]);

    header("Location: listar.php?exito=eliminado");
    exit;
} catch (PDOException $e) {
    header("Location: listar.php?error=" . urlencode("Error al eliminar: " . $e->getMessage()));
    exit;
}