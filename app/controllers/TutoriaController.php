<?php

/**
 * "Mis tutorías": vista compartida entre alumno y tutor, detalle de cada
 * tutoría y chat para coordinar el encuentro.
 *
 * Reglas del chat:
 *  - Se puede escribir solo mientras la solicitud está 'aceptada'.
 *  - Con la tutoría finalizada (o cancelada después de haber chateado) queda en solo lectura.
 *  - Pendiente / rechazada: no hay chat (el alumno ya pudo dejar un mensaje al solicitar).
 *  - Solo texto; los links se muestran como enlaces.
 */
final class TutoriaController extends Controller
{
    private const MAX_MENSAJE = 1000;

    /** Filtros de Mis tutorías: clave => estados incluidos. */
    public const FILTROS = [
        'pendientes' => ['pendiente'],
        'en-curso'   => ['aceptada'],
        'historial'  => ['rechazada', 'cancelada', 'finalizada'],
    ];

    public function lista()
    {
        $rol = isset($_GET['rol']) && $_GET['rol'] === 'tutor' ? 'tutor' : 'alumno';
        $comoAlumno = [];
        $comoTutor = [];
        $errorTutorias = false;

        try {
            $solicitudes = new SolicitudTutoria(Database::getConnection());
            $comoAlumno = $solicitudes->listarComoAlumno(Session::userId());
            $comoTutor = $solicitudes->listarComoTutor(Session::userId());
        } catch (PDOException $exception) {
            error_log('Error al cargar Mis tutorías: ' . $exception->getMessage());
            $errorTutorias = true;
        }

        $filtro = isset($_GET['estado']) && is_string($_GET['estado']) && isset(self::FILTROS[$_GET['estado']])
            ? $_GET['estado']
            : 'todas';

        $delRol = $rol === 'tutor' ? $comoTutor : $comoAlumno;

        // Cantidad por filtro (para los botones) y lista filtrada
        $cantidades = ['todas' => count($delRol)];
        foreach (self::FILTROS as $clave => $estados) {
            $cantidades[$clave] = count(array_filter($delRol, function ($t) use ($estados) {
                return in_array($t['estado'], $estados, true);
            }));
        }
        $tutorias = $filtro === 'todas' ? $delRol : array_values(array_filter($delRol, function ($t) use ($filtro) {
            return in_array($t['estado'], self::FILTROS[$filtro], true);
        }));

        $this->render('tutorias/mis-tutorias', [
            'rol' => $rol,
            'filtro' => $filtro,
            'cantidades' => $cantidades,
            'tutorias' => $tutorias,
            'noLeidosAlumno' => array_sum(array_column($comoAlumno, 'no_leidos')),
            'noLeidosTutor' => array_sum(array_column($comoTutor, 'no_leidos')),
            'errorTutorias' => $errorTutorias,
        ]);
    }

    public function detalle()
    {
        $idSolicitud = $this->idDesde($_GET, 'id');
        if ($idSolicitud === null) {
            $this->redirect('index.php?r=mis-tutorias');
        }

        $idUsuario = Session::userId();

        try {
            $database = Database::getConnection();
            $tutoria = (new SolicitudTutoria($database))->buscarParaParticipante($idSolicitud, $idUsuario);

            if ($tutoria === false) {
                $this->flash('error', 'La tutoría no existe o no tenés acceso a ella.');
                $this->redirect('index.php?r=mis-tutorias', 303);
            }

            $mensajes = [];
            if (in_array($tutoria['estado'], ['aceptada', 'finalizada', 'cancelada'], true)) {
                $chat = new MensajeTutoria($database);
                $mensajes = $chat->listar($idSolicitud);
                $chat->marcarLeidos($idSolicitud, $idUsuario);
            }
        } catch (PDOException $exception) {
            error_log('Error al cargar la tutoría: ' . $exception->getMessage());
            $this->flash('error', 'No se pudo cargar la tutoría. Intentá nuevamente más tarde.');
            $this->redirect('index.php?r=mis-tutorias', 303);
        }

        $rol = (int) $tutoria['id_tutor'] === $idUsuario ? 'tutor' : 'alumno';

        $this->render('tutorias/detalle', [
            'tutoria' => $tutoria,
            'rol' => $rol,
            'mensajes' => $mensajes,
            'idUsuario' => $idUsuario,
            'chatHabilitado' => $tutoria['estado'] === 'aceptada',
            'chatVisible' => in_array($tutoria['estado'], ['aceptada', 'finalizada'], true) || !empty($mensajes),
            'maxMensaje' => self::MAX_MENSAJE,
        ]);
    }

    /**
     * Envía un mensaje. Responde JSON si la petición viene por fetch
     * (cabecera X-Requested-With); si no, redirige al detalle.
     */
    public function enviarMensaje()
    {
        $esAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

        if (!$this->isPost()) {
            $this->redirect('index.php?r=mis-tutorias');
        }

        $idSolicitud = $this->idDesde($_POST, 'id_solicitud');
        $contenido = isset($_POST['contenido']) && is_string($_POST['contenido']) ? trim($_POST['contenido']) : '';
        $volver = $idSolicitud !== null ? 'index.php?r=tutoria&id=' . $idSolicitud . '#chat' : 'index.php?r=mis-tutorias';

        if ($idSolicitud === null) {
            $this->fallar($esAjax, 400, 'La tutoría indicada no es válida.', 'index.php?r=mis-tutorias');
        }

        if ($contenido === '') {
            $this->fallar($esAjax, 422, 'Escribí un mensaje antes de enviarlo.', $volver);
        }

        if (mb_strlen($contenido, 'UTF-8') > self::MAX_MENSAJE) {
            $this->fallar($esAjax, 422, 'El mensaje no puede superar los ' . self::MAX_MENSAJE . ' caracteres.', $volver);
        }

        $idUsuario = Session::userId();

        try {
            $database = Database::getConnection();
            $tutoria = (new SolicitudTutoria($database))->buscarParaParticipante($idSolicitud, $idUsuario);

            if ($tutoria === false) {
                $this->fallar($esAjax, 404, 'La tutoría no existe o no tenés acceso a ella.', 'index.php?r=mis-tutorias');
            }

            if ($tutoria['estado'] !== 'aceptada') {
                $this->fallar($esAjax, 409, 'El chat solo está habilitado mientras la tutoría está aceptada.', $volver);
            }

            $mensaje = (new MensajeTutoria($database))->crear($idSolicitud, $idUsuario, $contenido);
        } catch (PDOException $exception) {
            error_log('Error al enviar el mensaje: ' . $exception->getMessage());
            $this->fallar($esAjax, 500, 'No se pudo enviar el mensaje. Intentá nuevamente.', $volver);
        }

        if ($esAjax) {
            $this->json(['mensaje' => $this->mensajeParaJson($mensaje, $idUsuario)], 201);
        }

        $this->redirect($volver, 303);
    }

    /**
     * Mensajes nuevos (id > desde) en JSON, para actualizar el chat abierto.
     */
    public function mensajes()
    {
        $idSolicitud = $this->idDesde($_GET, 'id');
        $desde = isset($_GET['desde']) ? filter_var($_GET['desde'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) : 0;

        if ($idSolicitud === null || $desde === false) {
            $this->json(['error' => 'Parámetros inválidos.'], 400);
        }

        $idUsuario = Session::userId();

        try {
            $database = Database::getConnection();
            $tutoria = (new SolicitudTutoria($database))->buscarParaParticipante($idSolicitud, $idUsuario);

            if ($tutoria === false) {
                $this->json(['error' => 'La tutoría no existe o no tenés acceso a ella.'], 404);
            }

            $nuevos = [];
            if (in_array($tutoria['estado'], ['aceptada', 'finalizada', 'cancelada'], true)) {
                $chat = new MensajeTutoria($database);
                $nuevos = $chat->listar($idSolicitud, $desde);
                if (!empty($nuevos)) {
                    $chat->marcarLeidos($idSolicitud, $idUsuario);
                }
            }
        } catch (PDOException $exception) {
            error_log('Error al consultar mensajes: ' . $exception->getMessage());
            $this->json(['error' => 'No se pudieron cargar los mensajes.'], 500);
        }

        $this->json([
            'estado' => $tutoria['estado'],
            'mensajes' => array_map(function ($mensaje) use ($idUsuario) {
                return $this->mensajeParaJson($mensaje, $idUsuario);
            }, $nuevos),
        ]);
    }

    private function mensajeParaJson(array $mensaje, $idUsuario)
    {
        return [
            'id' => (int) $mensaje['id_mensaje'],
            'propio' => (int) $mensaje['id_emisor'] === $idUsuario,
            'contenido' => $mensaje['contenido'],
            'fecha' => Vista::fecha($mensaje['fecha'], 'd/m H:i'),
        ];
    }

    private function fallar($esAjax, $status, $mensaje, $volver)
    {
        if ($esAjax) {
            $this->json(['error' => $mensaje], $status);
        }

        $this->flash('error', $mensaje);
        $this->redirect($volver, 303);
    }

    private function idDesde(array $origen, $campo)
    {
        if (!isset($origen[$campo]) || !is_string($origen[$campo])) {
            return null;
        }

        $id = filter_var($origen[$campo], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? null : $id;
    }
}
