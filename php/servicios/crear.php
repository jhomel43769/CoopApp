<?php
session_start();
require_once '../../db/conexion.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.html");
    exit;
}

$errores = [];
$nombre = $descripcion = $url = '';
$destacado = $orden = 0;
$icono = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $url = trim($_POST['url']);
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    $orden = intval($_POST['orden']);

    if (isset($_FILES['icono']) && $_FILES['icono']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['icono']['name'], PATHINFO_EXTENSION);
        $icono = uniqid('icono_') . '.' . $ext;
        $rutaDestino = '../../uploads/icon/' . $icono;

        if (!is_dir('../../uploads/icon')) {
            mkdir('../../uploads/icon', 0777, true);
        }

        if (!move_uploaded_file($_FILES['icono']['tmp_name'], $rutaDestino)) {
            $errores[] = 'Error al subir la imagen del icono.';
        }
    } else {
        $errores[] = 'Debes subir una imagen para el icono.';
    }

    if (empty($nombre)) {
        $errores[] = 'El nombre es obligatorio';
    }

    if (empty($descripcion)) {
        $errores[] = 'La descripción es obligatoria';
    }

    if (empty($errores)) {
        try {
            $sql = "INSERT INTO servicios (nombre, descripcion, icono, url, destacado, orden) 
                    VALUES (:nombre, :descripcion, :icono, :url, :destacado, :orden)";

            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                ':nombre' => $nombre,
                ':descripcion' => $descripcion,
                ':icono' => $icono,
                ':url' => $url,
                ':destacado' => $destacado,
                ':orden' => $orden
            ]);

            header("Location: listar.php?exito=creado");
            exit;
        } catch (PDOException $e) {
            $errores[] = "Error al crear el servicio: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Servicio - COOPMAIMÓN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../ccs/G-styles.css">
    <link rel="stylesheet" href="../../css/CrearServicios.css">
</head>

<body>
    <div class="contenedor">
        <h1><i class="fas fa-plus-circle"></i> Crear Nuevo Servicio</h1>

        <a href="listar.php" class="boton-volver"><i class="fas fa-arrow-left"></i> Volver</a>

        <?php if (!empty($errores)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="formulario" enctype="multipart/form-data">
            <div class="campo">
                <label for="nombre">Nombre del servicio:*</label>
                <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($nombre) ?>" required>
            </div>

            <div class="campo">
                <label for="descripcion">Descripción:*</label>
                <textarea id="descripcion" name="descripcion" rows="5"
                    required><?= htmlspecialchars($descripcion) ?></textarea>
            </div>

            <div class="campo">
                <label for="url">URL (opcional):</label>
                <input type="url" id="url" name="url" value="<?= htmlspecialchars($url) ?>"
                    placeholder="https://ejemplo.com">
            </div>

            <div class="campo-checkbox">
                <input type="checkbox" id="destacado" name="destacado" value="1" <?= $destacado ? 'checked' : '' ?>>
                <label for="destacado">Servicio destacado</label>
            </div>

            <div class="campo">
                <label for="orden">Orden de visualización:</label>
                <input type="number" id="orden" name="orden" value="<?= $orden ?>" min="0">
            </div>

            <div class="campo">
                <label for="icono">Icono (sube una imagen):*</label>
                <input type="file" id="icono" name="icono" accept="image/*" required>
            </div>

            <button type="submit" class="boton"><i class="fas fa-save"></i> Guardar Servicio</button>
        </form>
    </div>
</body>

</html>