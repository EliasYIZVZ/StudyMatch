<?php

final class AuthController extends Controller
{
    public function login()
    {
        if (!$this->isPost()) {
            $this->redirect('index.php');
        }

        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        $fail = function ($message) use ($email) {
            $_SESSION['login_error'] = $message;
            $_SESSION['login_datos'] = [
                'email' => is_string($email) ? trim($email) : '',
            ];

            $this->redirect('index.php', 303);
        };

        if (!is_string($email) || !is_string($password)) {
            $fail('Por favor, ingresá tu correo y contraseña.');
        }

        $email = trim($email);
        if ($email === '' || $password === '') {
            $fail('Por favor, ingresá tu correo y contraseña.');
        }

        try {
            $user = (new User(Database::getConnection()))->findByEmail($email);
        } catch (PDOException $exception) {
            error_log('Error al iniciar sesión: ' . $exception->getMessage());
            $fail('No se pudo iniciar sesión. Intentá nuevamente más tarde.');
        }

        if (
            $user === false
            || !password_verify($password, $user['password_usuario'])
            || $user['estado_usuario'] !== 'activo'
        ) {
            $fail('Las credenciales ingresadas son incorrectas.');
        }

        unset($_SESSION['login_error'], $_SESSION['login_datos']);
        Session::login($user);

        $this->redirect(Session::isAdmin() ? 'index.php?r=admin' : 'index.php?r=dashboard', 303);
    }

    public function logout()
    {
        Session::destroy();

        $this->redirect('index.php', 303);
    }

    public function register()
    {
        if (!$this->isPost()) {
            $this->render('auth/register');
            return;
        }

        $name = isset($_POST['nombre_usuario']) ? $_POST['nombre_usuario'] : '';
        $email = isset($_POST['correo_usuario']) ? $_POST['correo_usuario'] : '';
        $password = isset($_POST['password_usuario']) ? $_POST['password_usuario'] : '';
        $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

        $fail = function ($message) use ($name, $email) {
            $_SESSION['registro_error'] = $message;
            $_SESSION['registro_datos'] = [
                'nombre_usuario' => is_string($name) ? $name : '',
                'correo_usuario' => is_string($email) ? $email : '',
            ];

            $this->redirect('index.php?r=registro', 303);
        };

        if (
            !is_string($name)
            || !is_string($email)
            || !is_string($password)
            || !is_string($confirmPassword)
        ) {
            $fail('Los datos enviados no son válidos.');
        }

        $name = trim($name);
        $email = trim($email);

        if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
            $fail('Todos los campos son obligatorios.');
        }

        if (mb_strlen($name, 'UTF-8') > 100 || mb_strlen($email, 'UTF-8') > 100) {
            $fail('El nombre o el correo superan la longitud permitida.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $fail('El formato del correo no es válido.');
        }

        if ($password !== $confirmPassword) {
            $fail('Las contraseñas no coinciden.');
        }

        if (strlen($password) < 8) {
            $fail('La contraseña debe tener al menos 8 caracteres.');
        }

        try {
            $user = new User(Database::getConnection());

            if ($user->emailExists($email)) {
                $fail('Ya existe una cuenta registrada con este correo.');
            }

            $user->create($name, $email, password_hash($password, PASSWORD_DEFAULT));
        } catch (PDOException $exception) {
            error_log('Error al registrar usuario: ' . $exception->getMessage());
            $fail('No se pudo completar el registro. Intentá nuevamente.');
        }

        unset($_SESSION['registro_error'], $_SESSION['registro_datos']);

        $this->redirect('index.php?registro=exitoso', 303);
    }
}
