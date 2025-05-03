# COOPMAIMÓN - Sistema Web

Este proyecto es un sistema web para la gestión de noticias y servicios de la cooperativa COOPMAIMÓN. Incluye un sitio público informativo y un panel administrativo protegido para gestionar el contenido.

## Estructura del Proyecto

```
index.html
login.html
nosotros.html
servicios.html
admin/
  panel.php
css/
  ...
db/
  conexion.php
img/
  ...
php/
  auth.php
  login.php
  logout.php
  noticias/
    crear.php
    editar.php
    eliminar.php
    listar.php
  servicios/
    crear.php
    editar.php
    eliminar.php
    listar.php
uploads/
```

## Descripción General

- **Sitio Público:**  
  - `index.html`: Página principal con información general y destacados.
  - `nosotros.html`: Información institucional, misión, visión, valores y equipo.
  - `servicios.html`: Detalle de los servicios ofrecidos.
  - `login.html`: Acceso para administradores.

- **Panel de Administración (`admin/panel.php`):**  
  Acceso solo para usuarios autenticados. Permite gestionar noticias y servicios.

- **Gestión de Noticias (`php/noticias/`):**  
  - `listar.php`: Lista, busca y pagina noticias.
  - `crear.php`: Formulario para crear una noticia (con imagen opcional).
  - `editar.php`: Edita noticias existentes.
  - `eliminar.php`: Elimina noticias (y su imagen asociada).

- **Gestión de Servicios (`php/servicios/`):**  
  - `listar.php`: Lista, busca y pagina servicios.
  - `crear.php`: Formulario para crear un servicio.
  - `editar.php`: Edita servicios existentes.
  - `eliminar.php`: Elimina servicios.

- **Autenticación y Seguridad:**
  - `login.php`: Valida credenciales y crea sesión.
  - `logout.php`: Cierra sesión.
  - `auth.php`: Middleware para proteger rutas administrativas.

- **Base de Datos:**
  - `db/conexion.php`: Conexión PDO a MySQL.
  - Tablas principales: `usuarios`, `noticias`, `servicios`.

- **Recursos:**
  - `css/`: Estilos para frontend y panel admin.
  - `img/`: Imágenes usadas en el sitio.
  - `uploads/`: Imágenes subidas para noticias.

## Flujo de Uso

1. **Visitante:**  
   Navega por las páginas públicas (`index.html`, `nosotros.html`, `servicios.html`).

2. **Administrador:**
   - Ingresa por `login.html` con usuario y contraseña.
   - Accede al panel (`admin/panel.php`).
   - Desde el panel puede:
     - Crear, editar, eliminar y listar noticias.
     - Crear, editar, eliminar y listar servicios.
   - Las acciones de noticias y servicios están protegidas por sesión.

3. **Gestión de Contenido:**
   - Al crear/editar noticias, se puede subir una imagen (guardada en `uploads/`).
   - Al eliminar una noticia, también se elimina su imagen asociada.
   - Los servicios pueden tener iconos y orden de visualización.

4. **Seguridad:**
   - Solo usuarios autenticados pueden acceder al panel y a las rutas de gestión.
   - Las sesiones expiran tras 30 minutos de inactividad (ver `auth.php`).

## Requisitos

- PHP 7.x o superior
- Servidor web: XAMPP
- MySQL

## Instalación

1. Clona o descarga el repositorio.
2. Configura la base de datos MySQL y ajusta los datos en `db/conexion.php`.
3. Asegúrate de que la carpeta `uploads/` tenga permisos de escritura.
4. Inicia el servidor web y accede a `index.html` o `login.html`.

## Notas

- El sistema no incluye registro de usuarios desde el frontend; los administradores deben ser creados directamente en la base de datos.
- Los iconos de servicios usan clases de FontAwesome.
- El diseño es responsive y amigable para dispositivos móviles.

---

**Desarrollado para COOPMAIMÓN**