<?php
session_start();
require_once '../../db/conexion.php';

if (!isset($_SESSION['admin'])) {
    header("Location: ../../login.html");
    exit;
}

$busqueda = $_GET['busqueda'] ?? '';
$pagina = $_GET['pagina'] ?? 1;
$porPagina = 10;

$where = '';
if (!empty($busqueda)) {
    $where = " WHERE titulo LIKE :busqueda";
}

$noticias = [];
$totalPaginas = 0;

try {
    $sqlCount = "SELECT COUNT(*) as total FROM noticias $where";
    $stmtCount = $conexion->prepare($sqlCount);
    if (!empty($busqueda)) {
        $stmtCount->bindValue(':busqueda', "%$busqueda%");
    }
    $stmtCount->execute();
    $totalNoticias = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPaginas = ceil($totalNoticias / $porPagina);

    $sql = "SELECT id, titulo, fecha_publicacion, estado 
            FROM noticias $where 
            ORDER BY fecha_publicacion DESC 
            LIMIT :offset, :limit";

    $stmt = $conexion->prepare($sql);
    $offset = ($pagina - 1) * $porPagina;
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $porPagina, PDO::PARAM_INT);

    if (!empty($busqueda)) {
        $stmt->bindValue(':busqueda', "%$busqueda%");
    }

    $stmt->execute();
    $noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al cargar noticias: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Noticias - COOPMAIMÓN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/G-styles.css">
    <link rel="stylesheet" href="../../css/ListarNoticias.css">
</head>

<body>
    <header class="admin-header">
        <div class="contenedor">
            <h1><i class="fas fa-newspaper"></i> Noticias</h1>
            <nav class="admin-nav">
                <ul>
                    <li><a href="../../admin/panel.php"><i class="fas fa-home"></i> Inicio</a></li>
                    <li><a href="#" class="activo"><i class="fas fa-newspaper"></i> Noticias</a></li>
                    <li><a href="../servicios/listar.php"><i class="fas fa-concierge-bell"></i> Servicios</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Salir</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="admin-container">
        <div class="noticias-actions">
            <a href="crear.php" class="btn-noticia btn-primary">
                <i class="fas fa-plus"></i> Nueva Noticia
            </a>

            <form method="get" class="search-form">
                <input type="text" name="busqueda" placeholder="Buscar noticias..."
                    value="<?= htmlspecialchars($busqueda) ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['exito'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php
                switch ($_GET['exito']) {
                    case 'creado':
                        echo "Noticia creada exitosamente";
                        break;
                    case 'actualizado':
                        echo "Noticia actualizada exitosamente";
                        break;
                    case 'eliminado':
                        echo "Noticia eliminada exitosamente";
                        break;
                }
                ?>
            </div>
        <?php endif; ?>

        <table class="table-noticias">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($noticias)): ?>
                    <tr>
                        <td colspan="4" style="text-align:center;" class="alert alert-warning">
                            <i class="fas fa-info-circle"></i> No se encontraron noticias con ese título.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($noticias as $noticia): ?>
                        <tr>
                            <td><?= htmlspecialchars($noticia['titulo']) ?></td>
                            <td>
                                <?= !empty($noticia['fecha_publicacion']) ? date('d/m/Y', strtotime($noticia['fecha_publicacion'])) : 'Sin fecha' ?>
                            </td>
                            <td><?= $noticia['estado'] == 'publicada' ? 'Publicada' : 'Borrador' ?></td>
                            <td>
                                <div class="acciones-noticia">
                                    <a href="editar.php?id=<?= $noticia['id'] ?>&origen=listar" class="btn-action btn-edit"
                                        title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="eliminar.php?id=<?= $noticia['id'] ?>" class="btn-action btn-delete"
                                        title="Eliminar" onclick="return confirm('¿Eliminar esta noticia?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($totalPaginas > 1): ?>
            <div class="pagination-noticias">
                <?php if ($pagina > 1): ?>
                    <a href="?pagina=<?= $pagina - 1 ?>&busqueda=<?= urlencode($busqueda) ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <a href="?pagina=<?= $i ?>&busqueda=<?= urlencode($busqueda) ?>" <?= $i == $pagina ? 'class="current"' : '' ?>>
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($pagina < $totalPaginas): ?>
                    <a href="?pagina=<?= $pagina + 1 ?>&busqueda=<?= urlencode($busqueda) ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer class="admin-footer">
        <div class="contenedor">
            <p><i class="fas fa-map-marker-alt"></i> Oficina Principal: Calle Padre Fantino No. 7, Maimón, Monseñor
                Nouel.</p>
            <p>&copy; <?= date('Y') ?> COOPMAIMÓN</p>
        </div>
    </footer>
</body>

</html>