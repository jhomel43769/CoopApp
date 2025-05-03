<?php
session_start();
require_once '../../db/conexion.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.html");
    exit;
}

$errores = [];
$titulo = $contenido = $estado = '';
$admin_username = $_SESSION['admin'];

$uploadDir = __DIR__ . '/../../uploads/';

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    $estado = $_POST['estado'];
    $imagen = $_FILES['imagen'] ?? null;

    if (empty($titulo)) {
        $errores[] = 'El título es obligatorio';
    }

    if (empty($contenido)) {
        $errores[] = 'El contenido es obligatorio';
    }

    if (!in_array($estado, ['publicada', 'borrador'])) {
        $errores[] = 'Estado no válido';
    }

    try {
        $sql = "SELECT id FROM usuarios WHERE usuario = :admin_username";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':admin_username' => $admin_username]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            $errores[] = 'El usuario administrador no existe en la base de datos';
        } else {
            $usuario_id = $usuario['id'];
        }
    } catch (PDOException $e) {
        $errores[] = "Error al verificar el usuario: " . $e->getMessage();
    }

    $nombreImagen = null;
    if ($imagen && $imagen['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($imagen['tmp_name']);

        if (!in_array($fileType, $allowedTypes)) {
            $errores[] = 'Solo se permiten imágenes JPG, PNG o GIF';
        } else {
            if ($imagen['size'] > 2 * 1024 * 1024) {
                $errores[] = 'La imagen no debe superar 2MB';
            } else {
                $extension = pathinfo($imagen['name'], PATHINFO_EXTENSION);
                $nombreImagen = uniqid() . '.' . $extension;
                $rutaDestino = $uploadDir . $nombreImagen;

                if (!move_uploaded_file($imagen['tmp_name'], $rutaDestino)) {
                    $errores[] = 'Error al subir la imagen. Verifica permisos de la carpeta uploads';
                    error_log("Error al mover archivo: " . print_r(error_get_last(), true));
                }
            }
        }
    } elseif ($imagen && $imagen['error'] !== UPLOAD_ERR_NO_FILE) {
        switch ($imagen['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errores[] = 'El archivo es demasiado grande';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errores[] = 'La subida se interrumpió';
                break;
            default:
                $errores[] = 'Error al subir la imagen (Código: ' . $imagen['error'] . ')';
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
                ':usuario_id' => $usuario_id
            ]);

            header("Location: listar.php?exito=creado");
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

</body>

</html>