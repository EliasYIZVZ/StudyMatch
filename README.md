# Study Match

Plataforma web de tutorías entre estudiantes de la UTN (FRH). Proyecto en PHP puro (sin framework ni Composer) con arquitectura **MVC** y un único punto de entrada (*front controller*).

---

## Requisitos

- **MAMP** (o cualquier Apache + PHP + MySQL equivalente)
- **PHP 7.4 o superior** con las extensiones `pdo_mysql` y `mbstring`
- **MySQL / MariaDB**

## Puesta en marcha

1. Clonar o copiar el proyecto en `C:\MAMP\htdocs\StudyMatch`.
2. Iniciar los servidores de MAMP (Apache y MySQL).
3. Crear la base de datos ejecutando `sql/studymatch_db.sql` (desde phpMyAdmin o la consola de MySQL).
   - **Atención:** el script borra y vuelve a crear todas las tablas, así que se pierden los datos que hubiera.
   - Opcional: `sql/datos_oferta_tutoria.sql` carga materias, tutores y ofertas de prueba.
   - Si tu base ya existía antes de la foto de perfil, ejecutá una sola vez `sql/migracion_002_foto_usuario.sql` (no borra datos).
   - PHP necesita la extensión **GD** (viene activada en MAMP) para procesar las fotos subidas.
   - Para tener un administrador, registrá una cuenta normal y después:
     ```sql
     UPDATE usuarios SET rol_usuario = 'admin' WHERE correo_usuario = 'tu-correo@frh.utn.edu.ar';
     ```
4. Revisar la conexión en `app/config/Database.php`. Por defecto usa:

   | Variable de entorno | Valor por defecto |
   |---------------------|-------------------|
   | `DB_HOST`           | `localhost`       |
   | `DB_NAME`           | `StudyMatch_DB`   |
   | `DB_USER`           | `root`            |
   | `DB_PASSWORD`       | `root`            |

   Si tu entorno es distinto, definí esas variables de entorno en lugar de cambiar el código.
5. Abrir `http://localhost/StudyMatch/` (agregá el puerto si tu Apache no usa el 80, por ejemplo `http://localhost:8888/StudyMatch/`).

> Apache tiene que permitir archivos `.htaccess` (`AllowOverride All`, que es lo habitual en MAMP). Se usan para bloquear el acceso directo a `app/` y `sql/`.

---

## Estructura del proyecto

```
StudyMatch/
├── index.php              # Front controller: toda petición dinámica entra por acá
├── .htaccess              # DirectoryIndex + sin listado de carpetas
├── app/                   # Código de la aplicación (no accesible desde el navegador)
│   ├── config/
│   │   ├── Database.php   # Conexión PDO a MySQL
│   │   └── routes.php     # Tabla de rutas
│   ├── core/
│   │   ├── Router.php     # Resuelve la ruta y verifica la sesión
│   │   ├── Controller.php # Clase base: render(), redirect(), json(), isPost()
│   │   └── Session.php    # Inicio, login, logout y estado de la sesión
│   ├── controllers/       # Lógica de cada sección
│   ├── models/            # Acceso a datos (una clase por tabla)
│   └── views/             # Plantillas PHP/HTML
│       ├── home/          # Inicio + formulario de login
│       ├── auth/          # Registro
│       ├── dashboard/     # Área privada de alumnos/tutores
│       ├── tutorias/      # Mis tutorías y detalle con chat
│       ├── partials/      # Piezas reutilizables (menús laterales, cards, modales, mensajes flash)
│       └── admin/         # Panel de administración
├── public/                # Archivos accesibles directamente
│   ├── *.html             # Páginas públicas estáticas
│   ├── css/  js/  img/
└── sql/
    ├── studymatch_db.sql         # Script único de creación de la base de datos
    └── datos_oferta_tutoria.sql  # Datos de prueba (opcional)
```

### Base de datos

Tablas principales (ver `sql/studymatch_db.sql`; datos de prueba en `sql/datos_oferta_tutoria.sql`):

| Tabla              | Modelo                   | Uso                                         |
|--------------------|--------------------------|---------------------------------------------|
| `usuarios`         | `app/models/User.php`    | Registro, login, datos del perfil y rol (`rol_usuario`: `usuario` / `admin`) |
| `consultausuarios` | `app/models/Consulta.php`| Mensajes enviados desde el formulario de contacto |
| `oferta_tutoria` | `app/models/OfertaTutoria.php` | Tutorías publicadas (1 a 1): materia, modalidad, disponibilidad, precio (0 = gratuita), medios de pago y estado `activa`/`pausada`. El dashboard del alumno muestra las 8 activas más recientes, sin las propias |
| `solicitud_tutoria` | `app/models/SolicitudTutoria.php` | Un alumno pide una oferta y el tutor responde. Estados: `pendiente` → `aceptada`/`rechazada` (tutor), `pendiente`/`aceptada` → `cancelada` (alumno), `aceptada` → `finalizada` (tutor). Un alumno no puede tener dos solicitudes vigentes para la misma oferta ni pedir una oferta propia o pausada |
| `mensaje_tutoria` | `app/models/MensajeTutoria.php` | Chat de cada tutoría. Se puede escribir solo con la solicitud `aceptada`; al finalizar (o cancelar) queda en solo lectura. Solo texto: los links `http(s)` se muestran como enlaces |
| `materia` / `carreras` | `app/models/Materia.php` | Cada materia pertenece a una carrera (por ahora solo TUP) y puede indicar su año en el plan |

---

## Rutas

Todas las URLs dinámicas tienen la forma `index.php?r=<ruta>`. Se definen en `app/config/routes.php`:

| Ruta              | Controlador → método                  | Acceso         | Descripción |
|-------------------|---------------------------------------|----------------|-------------|
| *(vacía)*         | `HomeController::index`               | Público        | Inicio con formulario de login |
| `login`           | `AuthController::login`               | Público (POST) | Valida credenciales e inicia sesión |
| `registro`        | `AuthController::register`            | Público        | GET muestra el formulario, POST registra |
| `logout`          | `AuthController::logout`              | Público        | Destruye la sesión y vuelve al inicio |
| `contacto`        | `ContactoController::validarMensaje`  | Público (POST) | Guarda una consulta de contacto |
| `dashboard`       | `DashboardController::alumno`         | **Con sesión** | Panel del alumno |
| `dashboard-tutor` | `DashboardController::tutor`          | **Con sesión** | Panel del tutor: resumen con métricas reales y las 3 solicitudes más antiguas sin responder |
| `buscar-tutor`    | `DashboardController::buscarTutor`    | **Con sesión** | Búsqueda de tutores |
| `solicitar-tutoria`   | `SolicitudController::solicitar` | **Con sesión** (POST) | El alumno solicita una oferta (`id_oferta`, `mensaje` opcional) |
| `responder-solicitud` | `SolicitudController::responder` | **Con sesión** (POST) | El tutor acepta, rechaza o finaliza (`id_solicitud`, `accion`) |
| `cancelar-solicitud`  | `SolicitudController::cancelar`  | **Con sesión** (POST) | El alumno cancela su solicitud (`id_solicitud`) |
| `mis-publicaciones` | `OfertaController::lista`        | **Con sesión** | Ofertas propias del tutor: publicar, editar, pausar/activar y eliminar |
| `guardar-oferta`    | `OfertaController::guardar`      | **Con sesión** (POST) | Alta (sin `id_oferta`) o edición (con `id_oferta`) de una oferta propia |
| `estado-oferta`     | `OfertaController::cambiarEstado`| **Con sesión** (POST) | `accion=pausar|activar|eliminar`. Una oferta con solicitudes no se borra: se pausa |
| `mis-tutorias`    | `TutoriaController::lista`         | **Con sesión** | Mis tutorías, con pestañas `&rol=alumno` / `&rol=tutor` y filtro `&estado=pendientes|en-curso|historial` ("Solicitudes" del menú tutor = pendientes) |
| `tutoria`         | `TutoriaController::detalle`       | **Con sesión** | Detalle de una tutoría con su chat (`&id=`). Solo para el alumno y el tutor de esa tutoría |
| `enviar-mensaje`  | `TutoriaController::enviarMensaje` | **Con sesión** (POST) | Envía un mensaje; responde JSON si se llama con `X-Requested-With: XMLHttpRequest` |
| `mensajes-tutoria`| `TutoriaController::mensajes`      | **Con sesión** (API) | Mensajes nuevos (`&id=&desde=`); el chat la consulta cada 5 s |
| `foto-perfil`     | `UsuarioController::foto`        | **Con sesión** (API, POST) | Foto de perfil: `accion=avatar&avatar=<clave>`, `accion=subir` + archivo `foto` (JPG/PNG/WEBP, máx. 2 MB; se recorta a 256×256 con GD y se guarda en `public/uploads/usuarios/<id>/`) o `accion=quitar` |
| `usuario-actual`  | `UsuarioController::actual`           | **Con sesión** (API) | JSON con los datos del usuario logueado |
| `admin`           | `AdminController::dashboard`          | **Admin**      | Resumen del panel de administración |
| `admin-usuarios`  | `AdminController::usuarios`           | **Admin**      | Gestión de usuarios |
| `admin-materias`  | `AdminController::materias`           | **Admin**      | Gestión de carreras y materias |
| `admin-salas`     | `AdminController::salas`              | **Admin**      | Moderación de salas públicas |
| `admin-reportes`  | `AdminController::reportes`           | **Admin**      | Reportes de moderación |
| `admin-moderar-sala` | `AdminController::moderarSala`     | **Admin** (API) | Suspender / reactivar una sala (POST) |
| `admin-moderar-usuario` | `AdminController::moderarUsuario` | **Admin** (API) | Suspender / reactivar la cuenta de un alumno o tutor (POST) |
| `admin-resolver-reporte` | `AdminController::resolverReporte` | **Admin** (API) | Finalizar (inactiva al usuario reportado o suspende la sala) o rechazar un reporte (POST) |
| `crear-reporte`   | `ReporteController::crear`            | **Con sesión** (API) | Reportar una sala, tutoría, mensaje, tutor o alumno (POST) |

Páginas públicas estáticas (sin PHP): `public/conoce.html`, `public/quienessomos.html` y `public/contacto.html`.

---

## Autenticación y control de acceso

- El tercer valor de cada ruta en `routes.php` define el acceso:
  - `null` → pública.
  - `'page'` → requiere sesión de **usuario común**; sin sesión redirige al inicio y un admin es enviado a su panel.
  - `'admin'` → requiere sesión con `rol_usuario = 'admin'`; un usuario común es enviado a su dashboard.
  - `'api'` → requiere cualquier sesión; si no hay, responde **401** en JSON.
- La verificación la hace `Router` **antes** de llamar al controlador, así que los controladores del área privada no necesitan revisarla.
- Las páginas privadas se envían con cabeceras `no-store`: después de cerrar sesión, el botón "Atrás" no las muestra desde la caché.
- Al iniciar sesión se regenera el ID de sesión y la cookie es `HttpOnly` / `SameSite=Lax`.
- Solo pueden iniciar sesión los usuarios con `estado_usuario = 'activo'`.
- Las contraseñas se guardan con `password_hash()` y se validan con `password_verify()`.
- **Registro restringido a alumnos de la UTN FRH:** solo se aceptan correos que terminen exactamente en `@alumnos.frh.utn.edu.ar` (constante `PATRON_CORREO_INSTITUCIONAL` en `AuthController`). Todavía no se verifica que el correo exista: está previsto hacerlo enviando un código por mail.
- **Roles:** todos usan el mismo login. Después de iniciar sesión, un admin va a `index.php?r=admin` y un usuario común a `index.php?r=dashboard`. Cada uno solo puede entrar a su área.
- Alumno y tutor **no** son roles distintos: cualquier usuario común puede entrar a ambos dashboards.
- El rol se lee de la base al iniciar sesión. Si se le cambia el rol a alguien, el cambio se aplica en su próximo login.

Datos que se guardan en `$_SESSION` al iniciar sesión: `id_usuario`, `nombre_usuario`, `correo_usuario`, `rol_usuario` y `logueado`.

### Mensajes de error en formularios

Login y registro siguen el mismo patrón:

1. El controlador valida los datos.
2. Si algo falla, guarda el mensaje en la sesión (`login_error` / `registro_error`) junto con los datos escritos (`login_datos` / `registro_datos`) y redirige al formulario.
3. La vista muestra la alerta, vuelve a completar los campos y borra esos valores de la sesión.

Los errores de base de datos se registran con `error_log()` y nunca se muestran al usuario.

En el área privada, las acciones (por ejemplo, solicitar o responder una tutoría) usan `Controller::flash('exito'|'error', $mensaje)` antes de redirigir. La vista muestra el mensaje una sola vez con `views/partials/flash.php`.

---

## Cómo agregar una página nueva

1. Crear la vista en `app/views/<seccion>/<nombre>.php`. La primera línea tiene que ser:
   ```php
   <?php defined('APP_PATH') || exit('Acceso denegado.'); ?>
   ```
2. Agregar un método al controlador correspondiente (o crear uno nuevo en `app/controllers/`, extendiendo `Controller`):
   ```php
   public function miPagina()
   {
       $this->render('seccion/nombre');
   }
   ```
3. Registrar la ruta en `app/config/routes.php`:
   ```php
   'mi-pagina' => ['MiController', 'miPagina', 'page'], // null, 'page', 'admin' o 'api'
   ```
4. Enlazarla como `index.php?r=mi-pagina`.

Las clases de `core/`, `controllers/` y `models/` se cargan solas (autoload en `index.php`): el nombre del archivo tiene que coincidir con el de la clase.

### Rutas de archivos dentro de las vistas

Las vistas se sirven desde `index.php` (raíz del proyecto), así que los recursos se referencian con `public/` adelante:

```html
<link rel="stylesheet" href="public/css/dashboard.css">
<img src="public/img/juli.jpeg">
<a href="index.php?r=dashboard">Inicio</a>
```

En cambio, desde los `.html` de `public/` se usa `../index.php?r=...` para volver al front controller.

**No usar rutas absolutas con `/StudyMatch/`**: con rutas relativas, el proyecto funciona aunque se renombre la carpeta.

---

## Convenciones

- Consultas SQL **solo en los modelos** y siempre con sentencias preparadas (PDO).
- Los controladores no imprimen HTML: usan `render()`, `redirect()` o `json()`.
- Escapar todo dato dinámico en las vistas con `Vista::e($valor)` (o `htmlspecialchars($valor, ENT_QUOTES, 'UTF-8')`). Para texto de usuarios con links, usar `Vista::enlazar()`.
- Los menús laterales son parciales (`partials/sidebar-alumno.php`, `partials/sidebar-tutor.php`): se incluyen definiendo antes `$sidebarActivo`. `Controller::render()` les agrega los contadores de mensajes sin leer y solicitudes pendientes.
- Después de un POST exitoso, redirigir con código **303** (patrón Post/Redirect/Get).

---

## Pendientes conocidos

- Algunas páginas del área privada todavía muestran datos estáticos de ejemplo.
- **Panel admin:** *Usuarios*, *Salas públicas* y *Reportes* leen y escriben en la base (modelos `User`, `SalaPublica` y `Reporte`; tablas `usuarios`, `salas_publicas`, `reportes` y `auditoria`). Un reporte guarda qué se reportó (`sala`, `tutoria`, `mensaje`, `tutor` o `alumno`), el usuario responsable y una copia del contenido; al finalizarlo, el admin inactiva a ese usuario (o suspende la sala) y puede reactivarlo desde *Gestión de usuarios*. Un usuario inactivo no puede iniciar sesión y, si ya estaba conectado, el Router le cierra la sesión en su próxima petición. El resumen (dashboard) y las materias siguen con datos simulados en los métodos `datos*()` de `AdminController`. Las tablas nuevas están en `sql/studymatch_db.sql`; si tu base es anterior, creá `auditoria`, `reportes` y `salas_publicas` con ese script.
- El enlace "¿Olvidaste tu contraseña?" apunta a `public/recuperar.html`, que aún no existe.
- El formulario de contacto muestra sus errores como texto plano.
- Los formularios no tienen protección CSRF.



## Fotos de perfil

- La columna `usuarios.foto_usuario` guarda una ruta relativa: un avatar precargado (`public/img/avatares/…`, lista en `app/config/avatares.php`) o una foto subida, guardada en la carpeta del usuario (`public/uploads/usuarios/<id>/perfil_<aleatorio>.jpg`). `NULL` muestra las iniciales.
- Las fotos subidas se validan por su contenido real y se vuelven a generar como JPG de 256×256 (`app/core/FotoPerfil.php`): así se descartan metadatos y cualquier código escondido. Al cambiar la foto, la anterior se borra del disco.
- El tipo de imagen se detecta con `getimagesize` (GD), por lo que **no** hace falta la extensión `fileinfo` (MAMP la trae desactivada).
- `public/uploads/.htaccess` impide ejecutar scripts dentro de esa carpeta.
- En las vistas, usar `Vista::avatar($nombre, $foto)`: muestra la foto o, si no hay, las iniciales.
