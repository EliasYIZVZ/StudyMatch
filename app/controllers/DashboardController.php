<?php

/**
 * Páginas del área privada. El Router ya verificó la sesión antes de llegar aquí.
 */
final class DashboardController extends Controller
{
    /** Tutorías visibles en el dashboard del alumno sin filtros (y por cada "Ver más"): "Ver más" aparece con más de estas. */
    private const OFERTAS_VISIBLES = 6;

    /** Máximo de tutorías que se cargan para buscar (el filtrado es en el navegador). */
    private const OFERTAS_BUSQUEDA = 120;

    /**
     * Dashboard del alumno: tutorías disponibles para solicitar (sin las que ya
     * solicitó: esas se ven y cancelan solo en Mis tutorías), para buscar y
     * filtrar en el navegador, y las materias presentes en ellas para el filtro.
     * También las salas públicas de Discord activas, que se pueden reportar.
     */
    public function alumno()
    {
        $ofertas = [];
        $materias = [];
        $salasPublicas = [];
        $errorOfertas = false;

        try {
            $ofertas = (new OfertaTutoria(Database::getConnection()))
                ->listarParaAlumno(Session::userId(), self::OFERTAS_BUSQUEDA, true);

            $materias = $this->materiasDeOfertas($ofertas);
        } catch (PDOException $exception) {
            error_log('Error al cargar las ofertas de tutoría: ' . $exception->getMessage());
            $errorOfertas = true;
        }

        // Aparte de las tutorías: si las salas no se pueden leer, el resto del dashboard sigue funcionando
        try {
            $salasPublicas = (new SalaPublica(Database::getConnection()))->listarActivas();
        } catch (PDOException $exception) {
            error_log('Error al cargar las salas públicas: ' . $exception->getMessage());
        }

        $this->render('dashboard/dashboard', [
            'ofertas' => $ofertas,
            'materias' => $materias,
            'salasPublicas' => $salasPublicas,
            'ofertasVisibles' => self::OFERTAS_VISIBLES,
            'errorOfertas' => $errorOfertas,
        ]);
    }

    /**
     * Materias presentes en un listado de ofertas: [id_materia => nombre], ordenadas.
     */
    private function materiasDeOfertas(array $ofertas)
    {
        $materias = [];
        foreach ($ofertas as $oferta) {
            $materias[(int) $oferta['id_materia']] = $oferta['materia'];
        }
        asort($materias, SORT_NATURAL | SORT_FLAG_CASE);

        return $materias;
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

    /**
     * Buscar Tutor: una card por tutor (no por tutoría). Las ofertas visibles
     * para el alumno se agrupan por tutor; el orden es el de su publicación más
     * reciente. También devuelve las materias presentes para el filtro.
     */
    public function buscarTutor()
    {
        $tutores = [];
        $materias = [];
        $errorOfertas = false;

        try {
            $ofertas = (new OfertaTutoria(Database::getConnection()))
                ->listarParaAlumno(Session::userId(), self::OFERTAS_BUSQUEDA);

            // Las ofertas llegan de la más reciente a la más antigua, así que la
            // primera de cada tutor fija su posición en la lista.
            foreach ($ofertas as $oferta) {
                $idTutor = (int) $oferta['id_tutor'];
                $idMateria = (int) $oferta['id_materia'];

                if (!isset($tutores[$idTutor])) {
                    $tutores[$idTutor] = [
                        'id_usuario' => $idTutor,
                        'nombre' => $oferta['tutor'],
                        'foto' => $oferta['tutor_foto'],
                        'carrera' => $oferta['tutor_carrera'],
                        'anio' => $oferta['tutor_anio'],
                        'tutorias_dadas' => (int) $oferta['tutorias_dadas'],
                        'materias' => [],
                        'modalidades' => [],
                        'precios' => [],
                    ];
                }

                $tutores[$idTutor]['materias'][$idMateria] = $oferta['materia'];
                $tutores[$idTutor]['modalidades'][$oferta['modalidad']] = true;
                $tutores[$idTutor]['precios'][] = (float) $oferta['precio'];
            }
            $materias = $this->materiasDeOfertas($ofertas);
        } catch (PDOException $exception) {
            error_log('Error al cargar la búsqueda de tutores: ' . $exception->getMessage());
            $errorOfertas = true;
        }

        $this->render('dashboard/buscar-tutor', [
            'tutores' => array_values($tutores),
            'materias' => $materias,
            'errorOfertas' => $errorOfertas,
        ]);
    }
}
