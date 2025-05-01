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
$errores = [];
$noticia = null;

// Obtener noticia actual
try {
    $stmt = $conexion->prepare("SELECT * FROM noticias WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $noticia = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$noticia) {
        header("Location: listar.php");
        exit;
    }
} catch (PDOException $e) {
    $errores[] = "Error al cargar la noticia: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    $estado = $_POST['estado'];
    $imagen = $_FILES['imagen'] ?? null;
    $eliminarImagen = isset($_POST['eliminar_imagen']);

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
    $nombreImagen = $noticia['imagen_url'];
    if ($eliminarImagen && $nombreImagen) {
        // Eliminar archivo físico
        $rutaImagen = '../../uploads/' . $nombreImagen;
        if (file_exists($rutaImagen)) {
            unlink($rutaImagen);
        }
        $nombreImagen = null;
    } elseif ($imagen && $imagen['error'] === UPLOAD_ERR_OK) {
        // Validar tipo de imagen
        $extension = strtolower(pathinfo($imagen['name'], PATHINFO_EXTENSION));
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($extension, $extensionesPermitidas)) {
            $errores[] = 'Formato de imagen no válido. Use JPG, PNG o GIF';
        } else {
            // Eliminar imagen anterior si existe
            if ($nombreImagen) {
                $rutaAnterior = '../../uploads/' . $nombreImagen;
                if (file_exists($rutaAnterior)) {
                    unlink($rutaAnterior);
                }
            }

            // Subir nueva imagen
            $nombreImagen = uniqid() . '.' . $extension;
            $rutaDestino = '../../uploads/' . $nombreImagen;

            if (!move_uploaded_file($imagen['tmp_name'], $rutaDestino)) {
                $errores[] = 'Error al subir la imagen';
            }
        }
    }

    if (empty($errores)) {
        try {
            $sql = "UPDATE noticias 
                    SET titulo = :titulo, 
                        contenido = :contenido, 
                        estado = :estado, 
                        imagen_url = :imagen_url,
                        fecha_publicacion = CASE WHEN :estado = 'publicada' AND estado = 'borrador' THEN NOW() ELSE fecha_publicacion END
                    WHERE id = :id";

            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                ':titulo' => $titulo,
                ':contenido' => $contenido,
                ':estado' => $estado,
                ':imagen_url' => $nombreImagen,
                ':id' => $id
            ]);

            header("Location: listar.php?exito=actualizado");
            exit;
        } catch (PDOException $e) {
            $errores[] = "Error al actualizar la noticia: " . $e->getMessage();
        }
    }
} else {
    // Rellenar formulario con datos actuales
    $titulo = $noticia['titulo'];
    $contenido = $noticia['contenido'];
    $estado = $noticia['estado'];
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Noticia - COOPMAIMÓN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/G-styles.css">
    <link rel="stylesheet" href="../../css/G-crud-styles.css">
</head>

<body>
    <div class="crud-container">
        <div class="crud-header">
            <h1 class="crud-title"><i class="fas fa-newspaper"></i> Editar Noticia</h1>
            <a href="listar.php" class="crud-btn crud-btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>

        <?php if (!empty($errores)): ?>
            <div class="crud-alert crud-alert-error">
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="crud-form">
            <div class="crud-form-group">
                <label class="crud-form-label">Título:*</label>
                <input type="text" class="crud-form-control" name="titulo" value="<?= htmlspecialchars($titulo) ?>"
                    required>
            </div>

            <div class="crud-form-group">
                <label class="crud-form-label">Contenido:*</label>
                <textarea class="crud-form-control" name="contenido" rows="10"
                    required><?= htmlspecialchars($contenido) ?></textarea>
            </div>

            <div class="crud-form-group">
                <label class="crud-form-label">Estado:*</label>
                <select class="crud-form-control" name="estado" required>
                    <option value="publicada" <?= $estado === 'publicada' ? 'selected' : '' ?>>Publicada</option>
                    <option value="borrador" <?= $estado === 'borrador' ? 'selected' : '' ?>>Borrador</option>
                </select>
            </div>

            <div class="crud-form-group">
                <label class="crud-form-label">Imagen:</label>
                <?php if ($noticia['imagen_url']): ?>
                    <div style="margin-bottom: 15px;">
                        <img src="../../uploads/<?= htmlspecialchars($noticia['imagen_url']) ?>" class="crud-image-preview"
                            alt="Imagen actual">
                        <div class="crud-checkbox" style="margin-top: 10px;">
                            <input type="checkbox" id="eliminar_imagen" name="eliminar_imagen">
                            <label for="eliminar_imagen">Eliminar imagen actual</label>
                        </div>
                    </div>
                <?php endif; ?>
                <input type="file" class="crud-form-control" name="imagen" accept="image/*">
                <small>Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 2MB</small>
            </div>

            <button type="submit" class="crud-btn crud-btn-primary"><i class="fas fa-save"></i> Guardar Cambios</button>
        </form>
    </div>

    <script>
        // Validación básica del formulario
        document.querySelector('form').addEventListener('submit', function (e) {
            const titulo = document.querySelector('[name="titulo"]').value.trim();
            const contenido = document.querySelector('[name="contenido"]').value.trim();

            if (!titulo || !contenido) {
                e.preventDefault();
                alert('Por favor complete todos los campos obligatorios');
            }
        });
    </script>
</body>

</html>