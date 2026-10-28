# Study Match - Entorno de Desarrollo Local

Bienvenidos al repositorio de **Study Match**. Este documento detalla los pasos necesarios para configurar y ejecutar el proyecto en un entorno local utilizando **MAMP** (Mac o Windows).

## 🛠 Prerrequisitos

* Tener instalado [MAMP](https://www.mamp.info/).

* Contar con el archivo `.sql` del proyecto (que contiene la estructura de la base de datos, incluyendo la tabla `usuarios`).

## 🚀 Guía de Instalación y Ejecución

### 1. Ubicar los archivos del proyecto

1. Descarga o clona este repositorio en tu computadora.

2. Mueve la carpeta completa del proyecto a la carpeta raíz de MAMP:

   * **Mac:** `/Applications/MAMP/htdocs/`

   * **Windows:** `C:\MAMP\htdocs\`

3. Para facilitar el acceso, te recomendamos nombrar la carpeta del proyecto como `study-match`.

### 2. Iniciar los servidores

1. Abre la aplicación **MAMP**.

2. Haz clic en el botón **"Start Servers"** (Iniciar Servidores).

3. Asegúrate de que tanto el servidor **Apache** como **MySQL** estén marcados en verde.

### 3. Configurar la Base de Datos (phpMyAdmin)

1. En la pantalla principal de MAMP, haz clic en **"Open WebStart page"** (o ve a `http://localhost:8888/MAMP/` / `http://localhost/MAMP/`).

2. En el menú superior, ve a **Tools > phpMyAdmin**.

3. En la barra lateral izquierda, haz clic en **Nueva** para crear una base de datos.

4. Nombra la base de datos como `StudyMatch_DB` (igual que en el script SQL) y selecciona el cotejamiento `utf8mb4_general_ci` o `utf8_general_ci`. Haz clic en **Crear**.

5. Selecciona la base de datos `StudyMatch_DB` recién creada en el panel izquierdo.

6. Ve a la pestaña **Importar** en el menú superior.

7. Haz clic en **Seleccionar archivo**, elige el archivo `.sql` del proyecto que tienes en tu equipo, baja hasta el final de la página y haz clic en **Importar**.

### 4. Configurar las Credenciales de PHP

Dependiendo de tu sistema operativo, las contraseñas por defecto de MAMP para MySQL varían. Actualiza las credenciales en `app/config/Database.php` o define las variables de entorno `DB_HOST`, `DB_NAME`, `DB_USER` y `DB_PASSWORD`:

Los valores predeterminados son `localhost`, `StudyMatch_DB`, `root` y `root`.

### 5. Visualizar el Proyecto

Abre tu navegador web e ingresa a la siguiente ruta dependiendo de la configuración de puertos de tu MAMP:

* Si usas el puerto por defecto de Mac (8888): `http://localhost:8888/study-match/`

* Si usas el puerto por defecto de Windows (80): `http://localhost/study-match/`

## 📂 Estructura del Proyecto

* `index.html` - Landing page y punto de inicio de sesión.

* `registro.html` - Redirección compatible para los enlaces anteriores al cambio.

* `dashboard.html` - Panel principal post-login (Actualmente maquetado estático).

* `login.php` y `ValidarRegistro.php` - Puntos de entrada compatibles para las acciones de autenticación.

* `registro.php` - Controlador de la vista del formulario de registro.

* `app/controllers/` - Controladores que procesan el inicio de sesión y el registro.

* `app/models/` - Acceso a datos de usuarios.

* `app/config/` - Configuración y conexión a la base de datos.

* `app/views/auth/` - Vistas del flujo de autenticación.

* `api/current_user.php` - Devuelve el nombre y correo del usuario asociado a la sesión activa para los dashboards.

* `css/` - Hojas de estilo y personalización (incluye Glassmorphism y Dark mode).

* `img/` - Recursos gráficos e imágenes.

## 📌 Notas para el Equipo de Desarrollo

* **Seguridad:** Las contraseñas se hashean usando `password_hash()`. Por favor, **no** inserten usuarios de prueba directamente en phpMyAdmin con contraseñas en texto plano, ya que el sistema no podrá validarlos. Creen los usuarios siempre desde el formulario de registro.

* **Sesiones:** `app/controllers/AuthController.php` genera las variables de sesión (`$_SESSION['id_usuario']`). `dashboard.html` todavía es estático y no protege la ruta; su conversión a una vista PHP queda pendiente.