<?php
session_start();
require_once '../../db/conexion.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.html");
    exit;
}

$errores = [];
$titulo = $contenido = $estado = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    $estado = $_POST['estado'];
    $imagen = $_FILES['imagen'] ?? null;

    // Validaciones
    if (empty($titulo)) {
        $errores[] = 'El título es obligatorio';
    }

    if (empty($contenido)) {
        $errores[] = 'El contenido es obligatorio';
    }

    if (!in_array($estado, ['publicada', 'borrador'])) {
        $errores[] = 'Estado no válido';
    }

    // Procesar imagen
    $nombreImagen = null;
    if ($imagen && $imagen['error'] === UPLOAD_ERR_OK) {
        $extension = pathinfo($imagen['name'], PATHINFO_EXTENSION);
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array(strtolower($extension), $extensionesPermitidas)) {
            $errores[] = 'Formato de imagen no válido';
        } else {
            $nombreImagen = uniqid() . '.' . $extension;
            $rutaDestino = '../../uploads/' . $nombreImagen;

            if (!move_uploaded_file($imagen['tmp_name'], $rutaDestino)) {
                $errores[] = 'Error al subir la imagen';
            }
        }
    }

    if (empty($errores)) {
        try {
            $sql = "INSERT INTO noticias (titulo, contenido, fecha_publicacion, estado, imagen_url, usuario_id) 
                    VALUES (:titulo, :contenido, NOW(), :estado, :imagen_url, :usuario_id)";

            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                ':titulo' => $titulo,
                ':contenido' => $contenido,
                ':estado' => $estado,
                ':imagen_url' => $nombreImagen,
                ':usuario_id' => $_SESSION['admin_id']
            ]);

            header("Location: listar.php?exito=1");
            exit;
        } catch (PDOException $e) {
            $errores[] = "Error al crear la noticia: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Noticia - COOPMAIMÓN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/G-styles.css">
    <link rel="stylesheet" href="../../css/crearNoticias.css">
</head>

<body>

    <header>
        <div class="contenedor">
            <h1>Noticias</h1>
            <nav>
                <ul>
                    <li><a href="../../admin/panel.php" class="activo"><i class="fas fa-home"></i> Inicio</a></li>
                    <li><a href="../../php/servicios/listar.php"><i class="fas fa-concierge-bell"></i> Servicios</a>
                    </li>
                    <li><a href="../../php/logout.php"><i class="fas fa-sign-out-alt"></i> Salir</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="noticia-crear-container">
        <h1>Crear Nueva Noticia</h1>

        <a href="listar.php" class="noticia-crear-volver"><i class="fas fa-arrow-left"></i> Volver</a>

        <?php if (!empty($errores)): ?>
            <div class="noticia-crear-error">
                <strong>Errores encontrados:</strong>
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="noticia-crear-form">
            <div class="noticia-crear-form-group">
                <label for="titulo" class="noticia-crear-form-label">Título:</label>
                <input type="text" id="titulo" name="titulo" value="<?= htmlspecialchars($titulo) ?>"
                    class="noticia-crear-form-control" required>
            </div>

            <div class="noticia-crear-form-group">
                <label for="contenido" class="noticia-crear-form-label">Contenido:</label>
                <textarea id="contenido" name="contenido" rows="10"
                    class="noticia-crear-form-control noticia-crear-textarea"
                    required><?= htmlspecialchars($contenido) ?></textarea>
            </div>

            <div class="noticia-crear-form-group">
                <label for="estado" class="noticia-crear-form-label">Estado:</label>
                <select id="estado" name="estado" class="noticia-crear-form-control noticia-crear-select" required>
                    <option value="publicada" <?= $estado === 'publicada' ? 'selected' : '' ?>>Publicada</option>
                    <option value="borrador" <?= $estado === 'borrador' ? 'selected' : '' ?>>Borrador</option>
                </select>
            </div>

            <div class="noticia-crear-form-group">
                <label for="imagen" class="noticia-crear-form-label">Imagen (opcional):</label>
                <input type="file" id="imagen" name="imagen" accept="image/*" class="noticia-crear-form-control">
            </div>

            <button type="submit" class="noticia-crear-submit"><i class="fas fa-save"></i> Guardar Noticia</button>
        </form>
    </div>

    <footer class="noticia-crear-footer">
        <div class="contenedor">
            <p><i class="fas fa-map-marker-alt"></i> Oficina Principal: Calle Padre Fantino No. 7, Maimón, Monseñor
                Nouel.</p>
            <p>&copy; <?php echo date('Y'); ?> COOPMAIMÓN. Todos los derechos reservados.</p>
        </div>
    </footer>
</body>

</html>