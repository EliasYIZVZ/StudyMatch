<?php

/**
 * Solicitudes de tutoría. Todas las acciones son POST y terminan en una
 * redirección con un mensaje flash (patrón Post/Redirect/Get).
 */
final class SolicitudController extends Controller
{
    private const MAX_MENSAJE = 500;

    /**
     * El alumno solicita una oferta de tutoría.
     */
    public function solicitar()
    {
        $volver = 'index.php?r=dashboard#salas-publicas';

        if (!$this->isPost()) {
            $this->redirect($volver);
        }

        $idOferta = $this->idDesdePost('id_oferta');
        $mensaje = isset($_POST['mensaje']) ? $_POST['mensaje'] : '';

        if ($idOferta === null || !is_string($mensaje)) {
            $this->flash('error', 'La solicitud enviada no es válida.');
            $this->redirect($volver, 303);
        }

        $mensaje = trim($mensaje);
        if (mb_strlen($mensaje, 'UTF-8') > self::MAX_MENSAJE) {
            $this->flash('error', 'El mensaje no puede superar los ' . self::MAX_MENSAJE . ' caracteres.');
            $this->redirect($volver, 303);
        }
        $mensaje = $mensaje === '' ? null : $mensaje;

        $idAlumno = Session::userId();

        try {
            $database = Database::getConnection();
            $oferta = (new OfertaTutoria($database))->buscarParaSolicitud($idOferta);

            if ($oferta === false || $oferta['estado'] !== 'activa' || $oferta['estado_tutor'] !== 'activo') {
                $this->flash('error', 'Esta tutoría ya no está disponible.');
                $this->redirect($volver, 303);
            }

            if ((int) $oferta['id_tutor'] === $idAlumno) {
                $this->flash('error', 'No podés solicitar una tutoría publicada por vos.');
                $this->redirect($volver, 303);
            }

            $solicitudes = new SolicitudTutoria($database);

            if ($solicitudes->existeVigente($idOferta, $idAlumno)) {
                $this->flash('error', 'Ya tenés una solicitud en curso para esta tutoría.');
                $this->redirect($volver, 303);
            }

            $solicitudes->crear($idOferta, $idAlumno, $mensaje);
        } catch (PDOException $exception) {
            error_log('Error al crear la solicitud de tutoría: ' . $exception->getMessage());
            $this->flash('error', 'No se pudo enviar la solicitud. Intentá nuevamente más tarde.');
            $this->redirect($volver, 303);
        }

        $this->flash('exito', 'Solicitud enviada a ' . $oferta['tutor'] . ' para ' . $oferta['materia'] . '. Podés seguirla desde Mis tutorías.');
        $this->redirect($volver, 303);
    }

    /**
     * El tutor acepta, rechaza o finaliza una solicitud recibida.
     */
    public function responder()
    {
        if (!$this->isPost()) {
            $this->redirect('index.php?r=dashboard-tutor#solicitudes');
        }

        $idSolicitud = $this->idDesdePost('id_solicitud');
        $volver = $this->destino('index.php?r=dashboard-tutor#solicitudes', $idSolicitud);
        $accion = isset($_POST['accion']) ? $_POST['accion'] : '';

        if ($idSolicitud === null || !is_string($accion) || !isset(SolicitudTutoria::ACCIONES_TUTOR[$accion])) {
            $this->flash('error', 'La acción solicitada no es válida.');
            $this->redirect($volver, 303);
        }

        try {
            $cambio = (new SolicitudTutoria(Database::getConnection()))
                ->responderComoTutor($idSolicitud, Session::userId(), $accion);
        } catch (PDOException $exception) {
            error_log('Error al responder la solicitud de tutoría: ' . $exception->getMessage());
            $this->flash('error', 'No se pudo actualizar la solicitud. Intentá nuevamente más tarde.');
            $this->redirect($volver, 303);
        }

        if (!$cambio) {
            $this->flash('error', 'La solicitud no existe o ya fue respondida.');
            $this->redirect($volver, 303);
        }

        // Al aceptar desde el panel, se abre directamente el chat de la tutoría
        if ($accion === 'aceptar' && !isset($_POST['volver'])) {
            $volver = 'index.php?r=tutoria&id=' . $idSolicitud;
        }

        $mensajes = [
            'aceptar'   => 'Solicitud aceptada. Ya podés coordinar el encuentro por chat.',
            'rechazar'  => 'Solicitud rechazada.',
            'finalizar' => 'Tutoría marcada como finalizada.',
        ];
        $this->flash('exito', $mensajes[$accion]);
        $this->redirect($volver, 303);
    }

    /**
     * El alumno cancela una solicitud propia pendiente o aceptada.
     */
    public function cancelar()
    {
        if (!$this->isPost()) {
            $this->redirect('index.php?r=dashboard#salas-publicas');
        }

        $idSolicitud = $this->idDesdePost('id_solicitud');
        $volver = $this->destino('index.php?r=dashboard#salas-publicas', $idSolicitud);
        if ($idSolicitud === null) {
            $this->flash('error', 'La solicitud indicada no es válida.');
            $this->redirect($volver, 303);
        }

        try {
            $cancelada = (new SolicitudTutoria(Database::getConnection()))
                ->cancelarComoAlumno($idSolicitud, Session::userId());
        } catch (PDOException $exception) {
            error_log('Error al cancelar la solicitud de tutoría: ' . $exception->getMessage());
            $this->flash('error', 'No se pudo cancelar la solicitud. Intentá nuevamente más tarde.');
            $this->redirect($volver, 303);
        }

        if ($cancelada) {
            $this->flash('exito', 'Solicitud cancelada.');
        } else {
            $this->flash('error', 'La solicitud no existe o ya no se puede cancelar.');
        }
        $this->redirect($volver, 303);
    }

    /**
     * Página a la que se vuelve después de la acción, según el campo POST "volver".
     * Solo se aceptan destinos conocidos (nunca una URL enviada por el usuario).
     */
    private function destino($predeterminado, $idSolicitud)
    {
        $volver = isset($_POST['volver']) && is_string($_POST['volver']) ? $_POST['volver'] : '';

        switch ($volver) {
            case 'tutoria':
                return $idSolicitud !== null ? 'index.php?r=tutoria&id=' . $idSolicitud : $predeterminado;
            case 'mis-tutorias-alumno':
            case 'mis-tutorias-tutor':
                $url = 'index.php?r=mis-tutorias&rol=' . ($volver === 'mis-tutorias-tutor' ? 'tutor' : 'alumno');
                $filtro = isset($_POST['filtro']) && is_string($_POST['filtro']) ? $_POST['filtro'] : '';
                if (isset(TutoriaController::FILTROS[$filtro])) {
                    $url .= '&estado=' . $filtro;
                }
                return $url;
            default:
                return $predeterminado;
        }
    }

    /**
     * Entero positivo enviado por POST, o null si falta o no es válido.
     */
    private function idDesdePost($campo)
    {
        if (!isset($_POST[$campo]) || !is_string($_POST[$campo])) {
            return null;
        }

        $id = filter_var($_POST[$campo], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? null : $id;
    }
}
