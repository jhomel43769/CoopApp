<?php
session_start();

require_once '../db/conexion.php';

if (!isset($_SESSION['admin'])) {
  header("Location: ../login.html");
  exit;
}

// Consulta para noticias
try {
  $stmtNoticias = $conexion->query("
          SELECT 
              id,
              titulo, 
              SUBSTRING(contenido, 1, 100) AS resumen, 
              DATE_FORMAT(fecha_publicacion, '%d/%m/%Y %H:%i') AS fecha_formateada,
              estado,
              imagen_url
          FROM noticias 
          ORDER BY fecha_publicacion DESC
          LIMIT 5
      ");
  $noticias = $stmtNoticias->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $errorNoticias = "Error al cargar noticias: " . $e->getMessage();
}

// Consulta para servicios
try {
  $stmtServicios = $conexion->query("
          SELECT 
              id,
              nombre, 
              SUBSTRING(descripcion, 1, 80) AS resumen,
              icono,
              CASE WHEN destacado = 1 THEN 'Sí' ELSE 'No' END AS destacado,
              orden
          FROM servicios 
          ORDER BY orden ASC, nombre ASC
          LIMIT 5
      ");
  $servicios = $stmtServicios->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $errorServicios = "Error al cargar servicios: " . $e->getMessage();
}

// Contar total de registros
try {
  $totalNoticias = $conexion->query("SELECT COUNT(*) FROM noticias")->fetchColumn();
  $totalServicios = $conexion->query("SELECT COUNT(*) FROM servicios")->fetchColumn();
} catch (PDOException $e) {
  $errorContador = "Error al contar registros: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Panel de Administración - COOPMAIMÓN</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="../css/G-styles.css" />
  <link rel="stylesheet" href="../css/adminPanel.css" />

  <style>
    .card-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 20px;
    }

    .btn-link {
      color: #007bff;
      text-decoration: none;
      font-size: 14px;
      font-weight: bold;
      transition: color 0.3s ease;
    }

    .btn-link:hover {
      color: #0056b3;
    }

    .boton-grande {
      display: inline-block;
      background-color: #28a745;
      color: #fff;
      padding: 10px 20px;
      border-radius: 5px;
      font-size: 14px;
      font-weight: bold;
      text-decoration: none;
      transition: background-color 0.3s ease;
    }

    .boton-grande:hover {
      background-color: #218838;
    }

    .bienvenida {
      background: linear-gradient(135deg, #f0f4f8, #d9e2ec);
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      text-align: center;
    }

    .bienvenida h2 {
      font-size: 24px;
      color: #333;
      margin-bottom: 10px;
    }

    .bienvenida p {
      font-size: 16px;
      color: #555;
      margin-bottom: 20px;
    }

    .dashboard-stats {
      display: flex;
      justify-content: space-around;
      gap: 20px;
      margin-top: 20px;
    }

    .stat-card {
      background: #fff;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      padding: 15px;
      text-align: center;
      flex: 1;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    }

    .stat-card i {
      font-size: 30px;
      color: #007bff;
      margin-bottom: 10px;
    }

    .stat-card h3 {
      font-size: 22px;
      color: #333;
      margin: 0;
    }

    .stat-card p {
      font-size: 14px;
      color: #777;
      margin: 5px 0 0;
    }
  </style>


</head>

<body>
  <header>
    <div class="contenedor">
      <h1>Panel de Administración</h1>
      <nav>
        <ul>
          <li><a href="panel.php" class="activo"><i class="fas fa-home"></i> Inicio</a></li>
          <li><a href="../php/noticias/listar.php"><i class="fas fa-newspaper"></i> Noticias</a></li>
          <li><a href="../php/servicios/listar.php"><i class="fas fa-concierge-bell"></i> Servicios</a></li>
          <li><a href="../php/logout.php"><i class="fas fa-sign-out-alt"></i> Salir</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main>
    <section class="bienvenida contenedor">
      <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['admin']); ?></h2>
      <p>Desde aquí puedes administrar todo el contenido del sitio web de COOPMAIMÓN.</p>
      <div class="dashboard-stats">
        <div class="stat-card">
          <i class="fas fa-newspaper"></i>
          <h3><?php echo $totalNoticias ?? 0; ?></h3>
          <p>Noticias</p>
        </div>
        <div class="stat-card">
          <i class="fas fa-concierge-bell"></i>
          <h3><?php echo $totalServicios ?? 0; ?></h3>
          <p>Servicios</p>
        </div>
      </div>
    </section>

    <div class="dashboard-grid contenedor">
      <!-- Sección de Noticias -->
      <section class="dashboard-card">
        <div class="card-header">
          <h2><i class="fas fa-newspaper"></i> Últimas Noticias</h2>
          <a href="../php/noticias/crear.php" class="btn-small">Agregar</a>
        </div>
        <?php if (isset($errorNoticias)): ?>
          <div class="error-msg"><?php echo $errorNoticias; ?></div>
        <?php else: ?>
          <div class="table-responsive">
            <table>
              <thead>
                <tr>
                  <th>Título</th>
                  <th>Resumen</th>
                  <th>Fecha</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($noticias as $noticia): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($noticia['titulo']); ?></td>
                    <td><?php echo htmlspecialchars($noticia['resumen']); ?>...</td>
                    <td><?php echo $noticia['fecha_formateada']; ?></td>
                    <td><span
                        class="estado-badge <?php echo $noticia['estado']; ?>"><?php echo ucfirst($noticia['estado']); ?></span>
                    </td>
                    <td class="acciones">
                      <a href="../php/noticias/editar.php?id=<?php echo $noticia['id']; ?>" class="btn-action"
                        title="Editar"><i class="fas fa-edit"></i></a>
                      <a href="../php/noticias/eliminar.php?id=<?php echo $noticia['id']; ?>" class="btn-action btn-danger"
                        title="Eliminar" onclick="return confirm('¿Eliminar esta noticia?')"><i
                          class="fas fa-trash"></i></a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="card-footer">
            <a href="../php/noticias/listar.php" class="btn-link">Ver todas las noticias <i
                class="fas fa-arrow-right"></i></a>
            <a href="../php/noticias/crear.php" class="boton-grande"><i class="fas fa-plus"></i> Nueva Noticia</a>
          </div>
        <?php endif; ?>
      </section>

      <!-- Sección de Servicios -->
      <section class="dashboard-card">
        <div class="card-header">
          <h2><i class="fas fa-concierge-bell"></i> Servicios Recientes</h2>
          <a href="../php/servicios/crear.php" class="btn-small">Agregar</a>
        </div>
        <?php if (isset($errorServicios)): ?>
          <div class="error-msg"><?php echo $errorServicios; ?></div>
        <?php else: ?>
          <div class="table-responsive">
            <table>
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Descripción</th>
                  <th>Icono</th>
                  <th>Destacado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($servicios as $servicio): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($servicio['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($servicio['resumen']); ?>...</td>
                    <td><i class="<?php echo htmlspecialchars($servicio['icono']); ?>"></i></td>
                    <td><?php echo $servicio['destacado']; ?></td>
                    <td class="acciones">
                      <a href="../php/servicios/editar.php?id=<?php echo $servicio['id']; ?>" class="btn-action"
                        title="Editar"><i class="fas fa-edit"></i></a>
                      <a href="../php/servicios/eliminar.php?id=<?php echo $servicio['id']; ?>"
                        class="btn-action btn-danger" title="Eliminar"
                        onclick="return confirm('¿Eliminar este servicio?')"><i class="fas fa-trash"></i></a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="card-footer">
            <a href="../php/servicios/listar.php" class="btn-link">Ver todos los servicios <i
                class="fas fa-arrow-right"></i></a>
            <a href="../php/servicios/crear.php" class="boton-grande"><i class="fas fa-cogs"></i> Nuevo Servicio</a>
          </div>
        <?php endif; ?>
      </section>
    </div>
  </main>

  <footer>
    <div class="contenedor">
      <p><i class="fas fa-map-marker-alt"></i> Oficina Principal: Calle Padre Fantino No. 7, Maimón, Monseñor Nouel.</p>
      <p>&copy; <?php echo date('Y'); ?> COOPMAIMÓN. Todos los derechos reservados.</p>
    </div>
  </footer>

</body>

</html>