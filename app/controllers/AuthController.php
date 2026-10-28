<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';

final class AuthController
{
    public function showRegistrationForm()
    {
        require __DIR__ . '/../views/auth/register.php';
    }

    public function currentUser()
    {
        header('Content-Type: application/json; charset=utf-8');
        session_start();

        if (empty($_SESSION['logueado']) || empty($_SESSION['id_usuario'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No hay una sesión activa.']);
            return;
        }

        try {
            $user = (new User(Database::getConnection()))->findById($_SESSION['id_usuario']);
            if ($user === false) {
                http_response_code(404);
                echo json_encode(['error' => 'No se encontró el usuario de la sesión.']);
                return;
            }

            echo json_encode([
                'nombre' => $user['nombre_usuario'],
                'email' => $user['correo_usuario'],
                'carrera' => $user['carrera_usuario'],
                'nivel' => $user['anio_cursada_usuario'],
            ]);
        } catch (PDOException $exception) {
            error_log('Error al cargar el usuario de la sesión: ' . $exception->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo cargar el usuario.']);
        }
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.html');
            exit();
        }

        session_start();

        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        if (!is_string($email) || !is_string($password)) {
            exit('Error: Por favor, ingresa tu correo y contraseña.');
        }

        $email = trim(filter_var($email, FILTER_SANITIZE_EMAIL));
        if ($email === '' || $password === '') {
            exit('Error: Por favor, ingresa tu correo y contraseña.');
        }

        try {
            $user = (new User(Database::getConnection()))->findByEmail($email);

            if ($user === false || !password_verify($password, $user['password_usuario'])) {
                exit('Error: Las credenciales ingresadas son incorrectas.');
            }

            session_regenerate_id(true);
            $_SESSION['id_usuario'] = $user['id_usuario'];
            $_SESSION['nombre_usuario'] = $user['nombre_usuario'];
            $_SESSION['logueado'] = true;

            header('Location: dashboard.html');
            exit();
        } catch (PDOException $exception) {
            exit('Error de base de datos: ' . $exception->getMessage());
        }
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: registro.html');
            exit();
        }

        session_start();

        $name = isset($_POST['nombre_usuario']) ? $_POST['nombre_usuario'] : '';
        $email = isset($_POST['correo_usuario']) ? $_POST['correo_usuario'] : '';
        $password = isset($_POST['password_usuario']) ? $_POST['password_usuario'] : '';
        $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

        if (
            !is_string($name)
            || !is_string($email)
            || !is_string($password)
            || !is_string($confirmPassword)
        ) {
            exit('Error: Todos los campos son obligatorios.');
        }

        $name = htmlspecialchars(trim($name), ENT_QUOTES, 'UTF-8');
        $email = trim(filter_var($email, FILTER_SANITIZE_EMAIL));

        if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
            exit('Error: Todos los campos son obligatorios.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            exit('Error: El formato del correo no es válido.');
        }

        if ($password !== $confirmPassword) {
            exit('Error: Las contraseñas no coinciden.');
        }

        if (strlen($password) < 8) {
            exit('Error: La contraseña debe tener al menos 8 caracteres para ser segura.');
        }

        try {
            $user = new User(Database::getConnection());
            if ($user->emailExists($email)) {
                exit('Error: Ya existe una cuenta registrada con este correo institucional.');
            }

            $user->create($name, $email, password_hash($password, PASSWORD_DEFAULT));
            header('Location: index.html?registro=exitoso');
            exit();
        } catch (PDOException $exception) {
            exit('Error en la base de datos: ' . $exception->getMessage());
        }
    }
}
