<?php

/**
 * Salas públicas de Discord: cualquier usuario (como alumno o como tutor) puede
 * publicar el enlace de invitación de su sala, ver las de la comunidad y eliminar
 * las propias. Se guardan en la tabla `salas_publicas`; el administrador las
 * modera desde su panel (index.php?r=admin-salas).
 *
 * Las acciones terminan en una redirección con un mensaje flash (Post/Redirect/Get).
 */
final class SalaController extends Controller
{
    public const MAX_TITULO = 150;
    public const MAX_DESCRIPCION = 500;
    public const MAX_ENLACE = 255;
    public const MAX_SALAS_POR_USUARIO = 5;

    // Solo invitaciones de Discord por https: discord.gg/xxxx o discord.com/invite/xxxx
    private const PATRON_ENLACE = '~^https://(?:discord\.gg|(?:www\.)?discord(?:app)?\.com/invite)/[A-Za-z0-9-]{2,64}/?$~';

    public function lista()
    {
        $rol = $this->rol();
        $salasVisibles = [];
        $cantPropias = 0;
        $errorSalas = false;
        $idUsuario = Session::userId();

        try {
            // Un solo listado: las activas de la comunidad y las propias (se filtran con "Mis salas")
            $salasVisibles = (new SalaPublica(Database::getConnection()))->listarVisiblesPara($idUsuario);

            foreach ($salasVisibles as $sala) {
                if ((int) $sala['id_creador'] === (int) $idUsuario) {
                    $cantPropias++;
                }
            }
        } catch (PDOException $exception) {
            error_log('Error al cargar las salas públicas: ' . $exception->getMessage());
            $errorSalas = true;
        }

        // Datos del formulario que falló la validación, para reabrir el modal con lo escrito
        $formulario = isset($_SESSION['sala_form']) && is_array($_SESSION['sala_form']) ? $_SESSION['sala_form'] : null;
        unset($_SESSION['sala_form']);

        $this->render('salas/salas-publicas-discord', [
            'rol' => $rol,
            'salas' => $salasVisibles,
            'idUsuario' => $idUsuario,
            'errorSalas' => $errorSalas,
            'formulario' => $formulario,
            'puedePublicar' => !$errorSalas && $cantPropias < self::MAX_SALAS_POR_USUARIO,
        ]);
    }

    /**
     * Publica una sala nueva del usuario logueado.
     */
    public function guardar()
    {
        $volver = $this->volver();

        if (!$this->isPost()) {
            $this->redirect($volver);
        }

        $datos = [
            'titulo' => $this->postTexto('titulo'),
            'enlace' => $this->postTexto('enlace_discord'),
            'descripcion' => $this->postTexto('descripcion'),
        ];
        $error = $this->validar($datos);
        $idUsuario = Session::userId();

        try {
            $database = Database::getConnection();
            $salas = new SalaPublica($database);

            if ($error === null && $salas->contarDeUsuario($idUsuario) >= self::MAX_SALAS_POR_USUARIO) {
                $error = 'Ya publicaste ' . self::MAX_SALAS_POR_USUARIO . ' salas, que es el máximo. Eliminá una para publicar otra.';
            }

            if ($error === null && $salas->existeEnlace($datos['enlace'])) {
                $error = 'Ese enlace de Discord ya está publicado en Study Match.';
            }

            if ($error !== null) {
                $this->volverConError($error, $datos, $volver);
            }

            $idSala = $salas->crear(
                $idUsuario,
                $datos['titulo'],
                $datos['descripcion'] === '' ? null : $datos['descripcion'],
                $datos['enlace']
            );

            $this->registrarAuditoria($database, 'crear', "Sala pública #{$idSala} publicada: \"{$datos['titulo']}\"");
            $this->flash('exito', '¡Sala publicada! Ya la puede ver toda la comunidad.');
        } catch (PDOException $exception) {
            error_log('Error al publicar la sala: ' . $exception->getMessage());
            $this->volverConError('No se pudo publicar la sala. Intentá nuevamente más tarde.', $datos, $volver);
        }

        $this->redirect($volver, 303);
    }

    /**
     * Elimina una sala propia (solo si está activa).
     */
    public function eliminar()
    {
        $volver = $this->volver();

        if (!$this->isPost()) {
            $this->redirect($volver);
        }

        $idSala = filter_var($this->postTexto('id_sala'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($idSala === false) {
            $this->flash('error', 'La sala indicada no es válida.');
            $this->redirect($volver, 303);
        }

        try {
            $database = Database::getConnection();

            if ((new SalaPublica($database))->eliminarPropia($idSala, Session::userId())) {
                $this->registrarAuditoria($database, 'eliminar', "Sala pública #{$idSala} eliminada por su creador");
                $this->flash('exito', 'Sala eliminada.');
            } else {
                $this->flash('error', 'La sala no existe, no te pertenece o está suspendida por moderación.');
            }
        } catch (PDOException $exception) {
            error_log('Error al eliminar la sala: ' . $exception->getMessage());
            $this->flash('error', 'No se pudo eliminar la sala. Intentá nuevamente más tarde.');
        }

        $this->redirect($volver, 303);
    }

    /**
     * Devuelve el primer error encontrado o null si los datos son válidos.
     */
    private function validar(array $datos)
    {
        if ($datos['titulo'] === '' || mb_strlen($datos['titulo'], 'UTF-8') > self::MAX_TITULO) {
            return 'Escribí un título para la sala (hasta ' . self::MAX_TITULO . ' caracteres).';
        }

        if (strlen($datos['enlace']) > self::MAX_ENLACE || !preg_match(self::PATRON_ENLACE, $datos['enlace'])) {
            return 'Pegá un enlace de invitación de Discord válido, por ejemplo https://discord.gg/abc123.';
        }

        if (mb_strlen($datos['descripcion'], 'UTF-8') > self::MAX_DESCRIPCION) {
            return 'La descripción puede tener hasta ' . self::MAX_DESCRIPCION . ' caracteres.';
        }

        return null;
    }

    /** La vista es compartida: se muestra con el menú del rol desde el que se entró. */
    private function rol()
    {
        $rol = isset($_GET['rol']) ? $_GET['rol'] : (isset($_POST['rol']) ? $_POST['rol'] : '');

        return $rol === 'tutor' ? 'tutor' : 'alumno';
    }

    private function volver()
    {
        return 'index.php?r=salas-publicas&rol=' . $this->rol();
    }

    private function volverConError($mensaje, array $datos, $volver)
    {
        $this->flash('error', $mensaje);
        $_SESSION['sala_form'] = $datos;
        $this->redirect($volver, 303);
    }

    /** Valor de $_POST como texto recortado ('' si falta o no es texto). */
    private function postTexto($clave)
    {
        return isset($_POST[$clave]) && is_string($_POST[$clave]) ? trim($_POST[$clave]) : '';
    }

    /** Deja constancia en la auditoría; si falla, la acción principal no se deshace. */
    private function registrarAuditoria(PDO $database, $accion, $descripcion)
    {
        try {
            $database->prepare(
                'INSERT INTO auditoria (id_usuario, accion, descripcion, ip) VALUES (:usuario, :accion, :descripcion, :ip)'
            )->execute([
                'usuario' => Session::userId(),
                'accion' => $accion,
                'descripcion' => mb_substr($descripcion, 0, 255, 'UTF-8'),
                'ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
            ]);
        } catch (PDOException $exception) {
            error_log('No se pudo registrar la auditoría de salas: ' . $exception->getMessage());
        }
    }
}
