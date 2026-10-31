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
3. Crear la base de datos ejecutando `sql/scriptDB.sql` (desde phpMyAdmin o la consola de MySQL).
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
│       └── dashboard/     # Área privada
├── public/                # Archivos accesibles directamente
│   ├── *.html             # Páginas públicas estáticas
│   ├── css/  js/  img/
└── sql/
    └── scriptDB.sql       # Script de creación de la base de datos
```

### Base de datos

De momento existen **dos tablas** (ver `sql/scriptDB.sql`):

| Tabla              | Modelo                   | Uso                                         |
|--------------------|--------------------------|---------------------------------------------|
| `usuarios`         | `app/models/User.php`    | Registro, login y datos del perfil          |
| `consultausuarios` | `app/models/Consulta.php`| Mensajes enviados desde el formulario de contacto |

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
| `dashboard-tutor` | `DashboardController::tutor`          | **Con sesión** | Panel del tutor |
| `buscar-tutor`    | `DashboardController::buscarTutor`    | **Con sesión** | Búsqueda de tutores |
| `usuario-actual`  | `UsuarioController::actual`           | **Con sesión** (API) | JSON con los datos del usuario logueado |

Páginas públicas estáticas (sin PHP): `public/conoce.html`, `public/quienessomos.html` y `public/contacto.html`.

---

## Autenticación y control de acceso

- El tercer valor de cada ruta en `routes.php` define el acceso:
  - `null` → pública.
  - `'page'` → requiere sesión; si no hay, redirige al inicio.
  - `'api'` → requiere sesión; si no hay, responde **401** en JSON.
- La verificación la hace `Router` **antes** de llamar al controlador, así que los controladores del área privada no necesitan revisarla.
- Las páginas privadas se envían con cabeceras `no-store`: después de cerrar sesión, el botón "Atrás" no las muestra desde la caché.
- Al iniciar sesión se regenera el ID de sesión y la cookie es `HttpOnly` / `SameSite=Lax`.
- Solo pueden iniciar sesión los usuarios con `estado_usuario = 'activo'`.
- Las contraseñas se guardan con `password_hash()` y se validan con `password_verify()`.
- **No hay roles en la base de datos**: cualquier usuario logueado puede entrar a ambos dashboards.

Datos que se guardan en `$_SESSION` al iniciar sesión: `id_usuario`, `nombre_usuario` y `logueado`.

### Mensajes de error en formularios

Login y registro siguen el mismo patrón:

1. El controlador valida los datos.
2. Si algo falla, guarda el mensaje en la sesión (`login_error` / `registro_error`) junto con los datos escritos (`login_datos` / `registro_datos`) y redirige al formulario.
3. La vista muestra la alerta, vuelve a completar los campos y borra esos valores de la sesión.

Los errores de base de datos se registran con `error_log()` y nunca se muestran al usuario.

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
   'mi-pagina' => ['MiController', 'miPagina', 'page'], // 'page' si requiere sesión
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
- Escapar todo dato dinámico en las vistas con `htmlspecialchars($valor, ENT_QUOTES, 'UTF-8')`.
- Después de un POST exitoso, redirigir con código **303** (patrón Post/Redirect/Get).

---

## Pendientes conocidos

- Algunas páginas del área privada todavía muestran datos estáticos de ejemplo.
- El enlace "¿Olvidaste tu contraseña?" apunta a `public/recuperar.html`, que aún no existe.
- El formulario de contacto muestra sus errores como texto plano.
- Los formularios no tienen protección CSRF.

