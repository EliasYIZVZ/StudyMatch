<?php
// Iniciar sesión para el manejo de variables o mensajes futuros
session_start();

// Configuración de la conexión a la base de datos (Ajusta estos valores a tu entorno)
$host = 'localhost';
$dbname = 'studymatch_db'; 
$username = 'root';
$password = 'root';

// Establecer conexión con PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Configurar PDO para que lance excepciones en caso de error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Verificar que los datos vengan por el método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Recepción y saneamiento básico de entradas
    $nombre = htmlspecialchars(trim($_POST['nombre_usuario']), ENT_QUOTES, 'UTF-8');
    $correo = trim(filter_var($_POST['correo_usuario'], FILTER_SANITIZE_EMAIL));
    $pass = $_POST['password_usuario'];
    $confirm_pass = $_POST['confirm_password'];

    // 2. Validación de campos vacíos (Doble validación del lado del servidor)
    if (empty($nombre) || empty($correo) || empty($pass) || empty($confirm_pass)) {
        die("Error: Todos los campos son obligatorios.");
    }

    // 3. Validación de formato de correo institucional
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        die("Error: El formato del correo no es válido.");
    }

    // 4. Verificación de contraseñas
    if ($pass !== $confirm_pass) {
        die("Error: Las contraseñas no coinciden.");
    }

    // 5. Validación de longitud de contraseña
    if (strlen($pass) < 8) {
        die("Error: La contraseña debe tener al menos 8 caracteres para ser segura.");
    }

    try {
        // 6. Verificar si el correo ya existe en la base de datos
        $stmt_check = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE correo_usuario = :correo LIMIT 1");
        $stmt_check->bindParam(':correo', $correo);
        $stmt_check->execute();

        if ($stmt_check->rowCount() > 0) {
            die("Error: Ya existe una cuenta registrada con este correo institucional.");
        }

        // 7. Hashing seguro de la contraseña
        // PASSWORD_DEFAULT usa el algoritmo más fuerte disponible (actualmente BCRYPT)
        $password_hashed = password_hash($pass, PASSWORD_DEFAULT);

        // 8. Inserción de los datos
        $stmt_insert = $pdo->prepare("INSERT INTO usuarios (nombre_usuario, correo_usuario, password_usuario) VALUES (:nombre, :correo, :password)");
        $stmt_insert->bindParam(':nombre', $nombre);
        $stmt_insert->bindParam(':correo', $correo);
        $stmt_insert->bindParam(':password', $password_hashed);

        if ($stmt_insert->execute()) {
            // Registro exitoso: Redirección al inicio o login
            header("Location: index.html?registro=exitoso");
            exit();
        } else {
            die("Error: No se pudo completar el registro.");
        }

    } catch (PDOException $e) {
        // En producción, es mejor guardar este error en un log en lugar de mostrarlo al usuario
        die("Error en la base de datos: " . $e->getMessage());
    }
} else {
    // Si se intenta acceder al archivo directamente sin POST, redirigir al formulario
    header("Location: registro.html");
    exit();
}
?>