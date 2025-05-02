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
    $where = " WHERE nombre LIKE :busqueda OR descripcion LIKE :busqueda";
}

try {
    // Contar total de registros
    $sqlCount = "SELECT COUNT(*) as total FROM servicios $where";
    $stmtCount = $conexion->prepare($sqlCount);
    if (!empty($busqueda)) {
        $stmtCount->bindValue(':busqueda', "%$busqueda%");
    }
    $stmtCount->execute();
    $totalServicios = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPaginas = ceil($totalServicios / $porPagina);

    // Obtener servicios
    $sql = "SELECT id, nombre, descripcion, icono, url, destacado, orden 
            FROM servicios $where 
            ORDER BY orden ASC, nombre ASC
            LIMIT :offset, :limit";

    $stmt = $conexion->prepare($sql);
    $offset = ($pagina - 1) * $porPagina;
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $porPagina, PDO::PARAM_INT);

    if (!empty($busqueda)) {
        $stmt->bindValue(':busqueda', "%$busqueda%");
    }

    $stmt->execute();
    $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al cargar servicios: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Servicios - COOPMAIMÓN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/G-styles.css">
    <link rel="stylesheet" href="../../css/listarServicios.css">
</head>

<body>
    <header class="admin-header">
        <div class="contenedor">
            <h1 class="admin-title"><i class="fas fa-concierge-bell"></i> Servicios</h1>
            <nav class="admin-nav">
                <ul>
                    <li><a href="../../admin/panel.php"><i class="fas fa-home"></i> Inicio</a></li>
                    <li><a href="../noticias/listar.php"><i class="fas fa-newspaper"></i> Noticias</a></li>
                    <li><a href="listar.php" class="activo"><i class="fas fa-concierge-bell"></i> Servicios</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Salir</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="admin-container">
        <div class="admin-actions">
            <a href="crear.php" class="btn"><i class="fas fa-plus"></i> Nuevo Servicio</a>
            <form method="get" class="search-form">
                <input type="text" name="busqueda" placeholder="Buscar servicios..."
                    value="<?= htmlspecialchars($busqueda) ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['exito'])): ?>
            <div class="exito">
                <?php
                switch ($_GET['exito']) {
                    case 'creado':
                        echo "Servicio creado correctamente";
                        break;
                    case 'actualizado':
                        echo "Servicio actualizado correctamente";
                        break;
                    case 'eliminado':
                        echo "Servicio eliminado correctamente";
                        break;
                }
                ?>
            </div>
        <?php endif; ?>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Orden</th>
                    <th>Nombre</th>
                    <th>Icono</th>
                    <th>Destacado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($servicios as $servicio): ?>
                    <tr>
                        <td><?= $servicio['orden'] ?></td>
                        <td><?= htmlspecialchars($servicio['nombre']) ?></td>
                        <td><i class="<?= htmlspecialchars($servicio['icono']) ?>"></i>
                            <?= htmlspecialchars($servicio['icono']) ?></td>
                        <td><?= $servicio['destacado'] ? 'Sí' : 'No' ?></td>
                        <td>
                            <a href="editar.php?id=<?= $servicio['id'] ?>" class="btn secondary" title="Editar"><i
                                    class="fas fa-edit"></i></a>
                            <a href="eliminar.php?id=<?= $servicio['id'] ?>" class="btn secondary" title="Eliminar"
                                onclick="return confirm('¿Eliminar este servicio?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Paginación -->
        <?php if ($totalPaginas > 1): ?>
            <div class="pagination">
                <?php if ($pagina > 1): ?>
                    <a href="?pagina=<?= $pagina - 1 ?>&busqueda=<?= urlencode($busqueda) ?>"><i
                            class="fas fa-chevron-left"></i></a>
                <?php endif; ?>

                <?php
                $inicio = max(1, $pagina - 2);
                $fin = min($totalPaginas, $pagina + 2);

                for ($i = $inicio; $i <= $fin; $i++): ?>
                    <a href="?pagina=<?= $i ?>&busqueda=<?= urlencode($busqueda) ?>" <?= $i == $pagina ? 'class="current"' : '' ?>>
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($pagina < $totalPaginas): ?>
                    <a href="?pagina=<?= $pagina + 1 ?>&busqueda=<?= urlencode($busqueda) ?>"><i
                            class="fas fa-chevron-right"></i></a>
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