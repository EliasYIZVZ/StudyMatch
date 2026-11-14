<?php

final class UsuarioController extends Controller
{
    public const MAX_NOMBRE = 100;
    public const MAX_DISCORD = 50;
    public const MAX_BIO = 500;

    /** Años de cursada permitidos (valores del ENUM anio_cursada_usuario). */
    public const NIVELES = ['1ro', '2do', '3ro', '4to', '5to', 'graduado'];

    /**
     * Devuelve en JSON los datos del usuario de la sesión actual y las carreras
     * que puede elegir en "Editar mi información".
     */
    public function actual()
    {
        try {
            $database = Database::getConnection();
            $user = (new User($database))->findById(Session::userId());
            $carreras = (new Materia($database))->listarCarrerasActivas();
        } catch (PDOException $exception) {
            error_log('Error al cargar el usuario de la sesión: ' . $exception->getMessage());
            $this->json(['error' => 'No se pudo cargar el usuario.'], 500);
        }

        if ($user === false || $user['estado_usuario'] !== 'activo') {
            Session::destroy();
            $this->json(['error' => 'No se encontró el usuario de la sesión.'], 401);
        }

        $datos = $this->datosPublicos($user);
        $datos['carreras'] = $carreras;
        $this->json($datos);
    }

    /**
     * Guarda los datos del perfil del usuario de la sesión (POST, responde JSON):
     * nombre, carrera, año de cursada, usuario de Discord y descripción.
     * El correo no se modifica.
     */
    public function actualizarPerfil()
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Método no permitido.'], 405);
        }

        $texto = function ($campo) {
            $valor = isset($_POST[$campo]) && is_string($_POST[$campo]) ? trim($_POST[$campo]) : '';
            return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $valor);   // sin caracteres de control
        };

        $nombre = preg_replace('/\s+/u', ' ', $texto('nombre'));
        $carrera = $texto('carrera');
        $nivel = $texto('nivel');
        $discord = $texto('discord');
        $bio = $texto('bio');

        if ($nombre === '' || mb_strlen($nombre, 'UTF-8') < 2 || mb_strlen($nombre, 'UTF-8') > self::MAX_NOMBRE) {
            $this->json(['error' => 'Ingresá tu nombre y apellido (entre 2 y ' . self::MAX_NOMBRE . ' caracteres).'], 422);
        }

        if ($nivel !== '' && !in_array($nivel, self::NIVELES, true)) {
            $this->json(['error' => 'Elegí un año de cursada válido.'], 422);
        }

        if ($discord !== '' && !preg_match('/^[\p{L}\p{N}_.#-]{2,' . self::MAX_DISCORD . '}$/u', $discord)) {
            $this->json(['error' => 'El usuario de Discord solo puede tener letras, números y los símbolos _ . # - (hasta ' . self::MAX_DISCORD . ' caracteres).'], 422);
        }

        if (mb_strlen($bio, 'UTF-8') > self::MAX_BIO) {
            $this->json(['error' => 'La descripción puede tener hasta ' . self::MAX_BIO . ' caracteres.'], 422);
        }

        $idUsuario = Session::userId();

        try {
            $database = Database::getConnection();
            $usuarios = new User($database);
            $usuario = $usuarios->findById($idUsuario);
            if ($usuario === false) {
                $this->json(['error' => 'No se encontró el usuario de la sesión.'], 401);
            }

            // La carrera tiene que ser una carrera activa; si no cambió, se acepta aunque se haya desactivado después
            $carreras = (new Materia($database))->listarCarrerasActivas();
            $codigos = array_column($carreras, 'codigo');
            if ($carrera !== '' && !in_array($carrera, $codigos, true) && $carrera !== $usuario['carrera_usuario']) {
                $this->json(['error' => 'Elegí una carrera de la lista.'], 422);
            }

            $usuarios->actualizarPerfil(
                $idUsuario,
                $nombre,
                $carrera === '' ? null : $carrera,
                $nivel === '' ? null : $nivel,
                $discord === '' ? null : $discord,
                $bio === '' ? null : $bio
            );

            $this->registrarAuditoria($database, $idUsuario);
            $actualizado = $usuarios->findById($idUsuario);
        } catch (PDOException $exception) {
            error_log('Error al guardar el perfil: ' . $exception->getMessage());
            $this->json(['error' => 'No se pudo guardar el perfil. Intentá nuevamente.'], 500);
        }

        // El nombre también vive en la sesión (lo usan otras pantallas)
        $_SESSION['nombre_usuario'] = $actualizado['nombre_usuario'];

        $datos = $this->datosPublicos($actualizado);
        $datos['carreras'] = $carreras;
        $this->json(['ok' => true, 'usuario' => $datos]);
    }

    /** Datos del usuario que se envían al navegador (nunca la contraseña). */
    private function datosPublicos(array $user)
    {
        return [
            'nombre' => $user['nombre_usuario'],
            'email' => $user['correo_usuario'],
            'carrera' => $user['carrera_usuario'],
            'nivel' => $user['anio_cursada_usuario'],
            'discord' => $user['discord_usuario'],
            'bio' => $user['bio_usuario'],
            'foto' => Vista::urlFoto($user['foto_usuario']),
        ];
    }

    /** Deja constancia del cambio de perfil; si falla, el guardado no se deshace. */
    private function registrarAuditoria(PDO $database, $idUsuario)
    {
        try {
            $database->prepare(
                "INSERT INTO auditoria (id_usuario, accion, descripcion, ip) VALUES (:usuario, 'modificar', :descripcion, :ip)"
            )->execute([
                'usuario' => (int) $idUsuario,
                'descripcion' => 'El usuario actualizó los datos de su perfil.',
                'ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
            ]);
        } catch (PDOException $exception) {
            error_log('No se pudo registrar la auditoría del perfil: ' . $exception->getMessage());
        }
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
