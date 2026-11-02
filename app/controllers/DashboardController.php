<?php

/**
 * Páginas del área privada. El Router ya verificó la sesión antes de llegar aquí.
 */
final class DashboardController extends Controller
{
    /** Cantidad de tutorías que se muestran en el dashboard del alumno. */
    private const OFERTAS_VISIBLES = 8;

    public function alumno()
    {
        $ofertas = [];
        $hayMasOfertas = false;
        $errorOfertas = false;

        try {
            // Se pide una fila extra solo para saber si hay más de las que se muestran.
            $ofertas = (new OfertaTutoria(Database::getConnection()))
                ->listarParaAlumno(Session::userId(), self::OFERTAS_VISIBLES + 1);

            $hayMasOfertas = count($ofertas) > self::OFERTAS_VISIBLES;
            $ofertas = array_slice($ofertas, 0, self::OFERTAS_VISIBLES);
        } catch (PDOException $exception) {
            error_log('Error al cargar las ofertas de tutoría: ' . $exception->getMessage());
            $errorOfertas = true;
        }

        $this->render('dashboard/dashboard', [
            'ofertas' => $ofertas,
            'hayMasOfertas' => $hayMasOfertas,
            'errorOfertas' => $errorOfertas,
        ]);
    }

    /** Cantidad de solicitudes pendientes que se muestran en el Panel Tutor. */
    private const SOLICITUDES_EN_PANEL = 3;

    /**
     * Panel Tutor: resumen con métricas reales y las solicitudes más antiguas
     * sin responder. La gestión completa está en Mis publicaciones y Mis tutorías.
     */
    public function tutor()
    {
        $solicitudesPendientes = [];
        $resumenOfertas = ['total' => 0, 'activas' => 0];
        $resumenSolicitudes = ['pendientes' => 0, 'en_curso' => 0, 'finalizadas' => 0, 'alumnos' => 0];
        $errorSolicitudes = false;

        try {
            $database = Database::getConnection();
            $solicitudes = new SolicitudTutoria($database);
            $solicitudesPendientes = array_slice(
                $solicitudes->listarPendientesDeTutor(Session::userId()),
                0,
                self::SOLICITUDES_EN_PANEL
            );
            $resumenSolicitudes = $solicitudes->resumenDeTutor(Session::userId());
            $resumenOfertas = (new OfertaTutoria($database))->resumenDeTutor(Session::userId());
        } catch (PDOException $exception) {
            error_log('Error al cargar el Panel Tutor: ' . $exception->getMessage());
            $errorSolicitudes = true;
        }

        $this->render('dashboard/dashboard-tutor', [
            'solicitudesPendientes' => $solicitudesPendientes,
            'resumenOfertas' => $resumenOfertas,
            'resumenSolicitudes' => $resumenSolicitudes,
            'errorSolicitudes' => $errorSolicitudes,
        ]);
    }

    public function buscarTutor()
    {
        $this->render('dashboard/buscar-tutor');
    }
}
