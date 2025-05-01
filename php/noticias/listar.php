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
    $where = " WHERE titulo LIKE :busqueda OR contenido LIKE :busqueda";
}

try {
    // Contar total de registros
    $sqlCount = "SELECT COUNT(*) as total FROM noticias $where";
    $stmtCount = $conexion->prepare($sqlCount);
    if (!empty($busqueda)) {
        $stmtCount->bindValue(':busqueda', "%$busqueda%");
    }
    $stmtCount->execute();
    $totalNoticias = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPaginas = ceil($totalNoticias / $porPagina);

    // Obtener noticias
    $sql = "SELECT id, titulo, DATE_FORMAT(fecha_publicacion, '%d/%m/%Y %H:%i') as fecha, estado 
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
    <meta charset="UTF-8" />
    <title>Noticias - COOPMAIMÓN</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../../css/G-styles.css" />
    <link rel="stylesheet" href="../../css/listarNoticias.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>

<body>
    <header class="admin-header">
        <div class="contenedor">
            <h1 class="admin-title"><i class="fas fa-newspaper"></i> Noticias</h1>
            <nav class="admin-nav">
                <ul>
                    <li><a href="../../admin/panel.php"><i class="fas fa-home"></i> Inicio</a></li>
                    <li><a href="listar.php" class="activo"><i class="fas fa-newspaper"></i> Noticias</a></li>
                    <li><a href="../servicios/listar.php"><i class="fas fa-concierge-bell"></i> Servicios</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Salir</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="admin-container">
        <div class="admin-actions">
            <a href="crear.php" class="btn"><i class="fas fa-plus"></i> Nueva Noticia</a>
            <form method="get" class="search-form">
                <input type="text" name="busqueda" placeholder="Buscar noticias..."
                    value="<?= htmlspecialchars($busqueda) ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($noticias as $n): ?>
                    <tr>
                        <td><?= $n['id'] ?></td>
                        <td><?= htmlspecialchars($n['titulo']) ?></td>
                        <td><?= $n['fecha'] ?></td>
                        <td><?= $n['estado'] ?></td>
                        <td>
                            <a href="editar.php?id=<?= $n['id'] ?>" class="btn secondary" title="Editar"><i
                                    class="fas fa-edit"></i></a>
                            <a href="eliminar.php?id=<?= $n['id'] ?>" class="btn secondary"
                                onclick="return confirm('¿Eliminar esta noticia?');" title="Eliminar"><i
                                    class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPaginas > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <?php if ($i == $pagina): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?pagina=<?= $i ?>&busqueda=<?= urlencode($busqueda) ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
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