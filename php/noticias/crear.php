<?php
session_start();  // Iniciar sesión al comienzo del archivo
require_once '../../db/conexion.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.html");  // Redirige a login si no hay sesión activa
    exit;
}

$errores = [];
$titulo = $contenido = $estado = '';
$admin_username = $_SESSION['admin']; // Contiene 'admin'

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

    // Obtener el ID del usuario a partir del nombre de usuario
    try {
        $sql = "SELECT id FROM usuarios WHERE usuario = :admin_username";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':admin_username' => $admin_username]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            // Si no encuentra el usuario por ese campo, podemos intentar con email por si acaso
            $sql_alt = "SELECT id FROM usuarios WHERE email = :admin_username";
            $stmt = $conexion->prepare($sql_alt);
            $stmt->execute([':admin_username' => $admin_username]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                $errores[] = 'El usuario administrador no existe en la base de datos';
            }
        }

        if ($usuario) {
            $usuario_id = $usuario['id']; // Usar el ID real encontrado en la base de datos
        } else {
            // Si aún no encontramos el usuario, podemos:
            // 1. Usar un ID de administrador predeterminado
            $usuario_id = 1; // Asumiendo que el ID 1 pertenece a un administrador
            // 2. O crear un nuevo usuario para el administrador en este momento
            /*
            $sql_insert = "INSERT INTO usuarios (usuario, nombre_completo, rol, fecha_creacion) 
                         VALUES (:usuario, 'Administrador', 'editor', NOW())";
            $stmt = $conexion->prepare($sql_insert);
            $stmt->execute([':usuario' => $admin_username]);
            $usuario_id = $conexion->lastInsertId();
            */
        }

    } catch (PDOException $e) {
        $errores[] = "Error al verificar el usuario: " . $e->getMessage();
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

    // Si no hay errores, insertamos la noticia
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