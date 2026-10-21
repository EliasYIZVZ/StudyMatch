<?php
// Iniciar la sesión para guardar los datos del usuario logueado
session_start();

// Configuración de la conexión a la base de datos (Igual que en ValidarRegistro.php)
$host = 'localhost';
$dbname = 'studymatch_db'; 
$username = 'root';
$password_db = 'root'; // Renombrado para no confundir con la contraseña del usuario

// Establecer conexión con PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Verificar que los datos lleguen por el método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Recepción y saneamiento de entradas
    $correo = trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
    $pass = $_POST['password'];

    // 2. Validación de campos vacíos
    if (empty($correo) || empty($pass)) {
        die("Error: Por favor, ingresa tu correo y contraseña.");
    }

    try {
        // 3. Buscar el usuario por su correo institucional (Consulta preparada)
        $stmt = $pdo->prepare("SELECT id_usuario, nombre_usuario, password_usuario FROM usuarios WHERE correo_usuario = :correo LIMIT 1");
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();

        // 4. Verificar si el usuario existe
        if ($stmt->rowCount() == 1) {
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // 5. Verificar la contraseña usando password_verify()
            // Esta función compara la contraseña en texto plano con el hash guardado en la BD
            if (password_verify($pass, $usuario['password_usuario'])) {
                
                // 6. Regenerar el ID de sesión (Prevención de ataques Session Fixation)
                session_regenerate_id(true);

                // 7. Guardar datos importantes en variables de sesión
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
                $_SESSION['logueado'] = true;

                // 8. Redirigir al dashboard
                header("Location: dashboard.html");
                exit();

            } else {
                // Contraseña incorrecta
                die("Error: Las credenciales ingresadas son incorrectas.");
            }
        } else {
            // Usuario no encontrado
            // Nota de seguridad: Se usa el mismo mensaje de error para no revelar si el correo existe o no en la BD
            die("Error: Las credenciales ingresadas son incorrectas.");
        }

    } catch (PDOException $e) {
        die("Error en la base de datos: " . $e->getMessage());
    }
} else {
    // Redirigir si se intenta acceder al archivo directamente por la URL (GET)
    header("Location: index.html");
    exit();
}
?>