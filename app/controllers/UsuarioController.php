<?php

final class UsuarioController extends Controller
{
    /**
     * Devuelve en JSON los datos del usuario de la sesión actual.
     */
    public function actual()
    {
        try {
            $user = (new User(Database::getConnection()))->findById(Session::userId());
        } catch (PDOException $exception) {
            error_log('Error al cargar el usuario de la sesión: ' . $exception->getMessage());
            $this->json(['error' => 'No se pudo cargar el usuario.'], 500);
        }

        if ($user === false || $user['estado_usuario'] !== 'activo') {
            Session::destroy();
            $this->json(['error' => 'No se encontró el usuario de la sesión.'], 401);
        }

        $this->json([
            'nombre' => $user['nombre_usuario'],
            'email' => $user['correo_usuario'],
            'carrera' => $user['carrera_usuario'],
            'nivel' => $user['anio_cursada_usuario'],
            'foto' => Vista::urlFoto($user['foto_usuario']),
        ]);
    }

    /**
     * Cambia la foto de perfil (POST, responde JSON):
     *  - accion=avatar&avatar=<clave>  elige un avatar precargado (app/config/avatares.php)
     *  - accion=subir + archivo "foto" sube una imagen propia (ver FotoPerfil)
     *  - accion=quitar                 vuelve a mostrar las iniciales
     */
    public function foto()
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Método no permitido.'], 405);
        }

        // Si el archivo supera post_max_size, PHP descarta todo el formulario
        if (empty($_POST) && empty($_FILES) && !empty($_SERVER['CONTENT_LENGTH'])) {
            $this->json(['error' => 'La imagen supera el máximo de 2 MB.'], 413);
        }

        $accion = isset($_POST['accion']) && is_string($_POST['accion']) ? $_POST['accion'] : '';
        $idUsuario = Session::userId();
        $rutaNueva = null;

        try {
            $usuarios = new User(Database::getConnection());
            $usuario = $usuarios->findById($idUsuario);
            if ($usuario === false) {
                $this->json(['error' => 'No se encontró el usuario de la sesión.'], 401);
            }

            switch ($accion) {
                case 'avatar':
                    $avatares = require APP_PATH . '/config/avatares.php';
                    $clave = isset($_POST['avatar']) && is_string($_POST['avatar']) ? $_POST['avatar'] : '';
                    if (!isset($avatares[$clave])) {
                        $this->json(['error' => 'Elegí uno de los avatares disponibles.'], 422);
                    }
                    $rutaNueva = $avatares[$clave];
                    break;

                case 'subir':
                    $rutaNueva = FotoPerfil::guardarSubida(isset($_FILES['foto']) ? $_FILES['foto'] : null, $idUsuario);
                    break;

                case 'quitar':
                    $rutaNueva = null;
                    break;

                default:
                    $this->json(['error' => 'La acción solicitada no es válida.'], 400);
            }

            $usuarios->actualizarFoto($idUsuario, $rutaNueva);
        } catch (RuntimeException $exception) {
            $this->json(['error' => $exception->getMessage()], 422);
        } catch (PDOException $exception) {
            error_log('Error al guardar la foto de perfil: ' . $exception->getMessage());
            if ($accion === 'subir') {
                FotoPerfil::borrarSiEsSubida($rutaNueva);
            }
            $this->json(['error' => 'No se pudo guardar la foto. Intentá nuevamente.'], 500);
        }

        // La foto subida anterior ya no se usa: se borra del disco
        if ($usuario['foto_usuario'] !== $rutaNueva) {
            FotoPerfil::borrarSiEsSubida($usuario['foto_usuario']);
        }

        $this->json(['foto' => Vista::urlFoto($rutaNueva)]);
    }
}
