<?php

/**
 * "Mis publicaciones": el tutor publica, edita, pausa, reactiva y elimina sus
 * ofertas de tutoría. Todas las acciones terminan en una redirección con un
 * mensaje flash (patrón Post/Redirect/Get).
 */
final class OfertaController extends Controller
{
    public const MODALIDADES = ['Virtual', 'Presencial en UTN', 'Hibrido'];
    public const MEDIOS_PAGO = ['efectivo', 'transferencia', 'mercado_pago'];

    private const MAX_DISPONIBILIDAD = 255;
    private const MAX_DESCRIPCION = 1000;
    private const VOLVER = 'index.php?r=mis-publicaciones';

    public function lista()
    {
        $ofertas = [];
        $materias = [];
        $errorOfertas = false;

        try {
            $database = Database::getConnection();
            $ofertas = (new OfertaTutoria($database))->listarDeTutor(Session::userId());
            $materias = (new Materia($database))->listarActivas();
        } catch (PDOException $exception) {
            error_log('Error al cargar Mis publicaciones: ' . $exception->getMessage());
            $errorOfertas = true;
        }

        // Datos del formulario que falló la validación, para reabrir el modal con lo escrito
        $formulario = isset($_SESSION['oferta_form']) ? $_SESSION['oferta_form'] : null;
        unset($_SESSION['oferta_form']);

        $this->render('ofertas/mis-publicaciones', [
            'ofertas' => $ofertas,
            'materias' => $materias,
            'errorOfertas' => $errorOfertas,
            'formulario' => $formulario,
            'maxDisponibilidad' => self::MAX_DISPONIBILIDAD,
            'maxDescripcion' => self::MAX_DESCRIPCION,
        ]);
    }

    /**
     * Alta (sin id_oferta) o edición (con id_oferta) de una oferta propia.
     */
    public function guardar()
    {
        if (!$this->isPost()) {
            $this->redirect(self::VOLVER);
        }

        $idOferta = $this->idDesdePost('id_oferta');
        $esEdicion = isset($_POST['id_oferta']) && $_POST['id_oferta'] !== '';
        $datos = $this->leerFormulario();
        $error = $this->validar($datos);

        if ($esEdicion && $idOferta === null) {
            $error = 'La publicación indicada no es válida.';
        }

        $idTutor = Session::userId();

        try {
            $database = Database::getConnection();
            $ofertas = new OfertaTutoria($database);

            if ($error === null && !(new Materia($database))->existeActiva($datos['id_materia'])) {
                $error = 'Elegí una materia de la lista.';
            }

            if ($error === null && $esEdicion && $ofertas->buscarDeTutor($idOferta, $idTutor) === false) {
                $this->flash('error', 'La publicación no existe o no te pertenece.');
                $this->redirect(self::VOLVER, 303);
            }

            if ($error !== null) {
                $this->volverConError($error, $datos, $esEdicion ? $idOferta : null);
            }

            $guardar = $this->datosParaGuardar($datos);
            if ($esEdicion) {
                $ofertas->actualizar($idOferta, $idTutor, $guardar);
                $this->flash('exito', 'Publicación actualizada.');
            } else {
                $ofertas->crear($idTutor, $guardar);
                $this->flash('exito', '¡Tutoría publicada! Ya la pueden ver los alumnos en su dashboard.');
            }
        } catch (PDOException $exception) {
            error_log('Error al guardar la oferta de tutoría: ' . $exception->getMessage());
            $this->volverConError('No se pudo guardar la publicación. Intentá nuevamente más tarde.', $datos, $esEdicion ? $idOferta : null);
        }

        $this->redirect(self::VOLVER, 303);
    }

    /**
     * Pausar, reactivar o eliminar una oferta propia. Una oferta que ya recibió
     * solicitudes no se elimina: se pausa para conservar el historial y los chats.
     */
    public function cambiarEstado()
    {
        if (!$this->isPost()) {
            $this->redirect(self::VOLVER);
        }

        $idOferta = $this->idDesdePost('id_oferta');
        $accion = isset($_POST['accion']) && is_string($_POST['accion']) ? $_POST['accion'] : '';

        if ($idOferta === null || !in_array($accion, ['pausar', 'activar', 'eliminar'], true)) {
            $this->flash('error', 'La acción solicitada no es válida.');
            $this->redirect(self::VOLVER, 303);
        }

        $idTutor = Session::userId();

        try {
            $ofertas = new OfertaTutoria(Database::getConnection());

            if ($ofertas->buscarDeTutor($idOferta, $idTutor) === false) {
                $this->flash('error', 'La publicación no existe o no te pertenece.');
                $this->redirect(self::VOLVER, 303);
            }

            if ($accion === 'eliminar') {
                if ($ofertas->eliminarSinSolicitudes($idOferta, $idTutor)) {
                    $this->flash('exito', 'Publicación eliminada.');
                } else {
                    $ofertas->cambiarEstado($idOferta, $idTutor, 'pausada');
                    $this->flash('exito', 'La publicación ya recibió solicitudes: se pausó en lugar de eliminarse para conservar el historial y los chats.');
                }
            } else {
                $ofertas->cambiarEstado($idOferta, $idTutor, $accion === 'activar' ? 'activa' : 'pausada');
                $this->flash('exito', $accion === 'activar'
                    ? 'Publicación activada: vuelve a mostrarse a los alumnos.'
                    : 'Publicación pausada: los alumnos ya no la ven ni pueden solicitarla.');
            }
        } catch (PDOException $exception) {
            error_log('Error al cambiar el estado de la oferta: ' . $exception->getMessage());
            $this->flash('error', 'No se pudo actualizar la publicación. Intentá nuevamente más tarde.');
        }

        $this->redirect(self::VOLVER, 303);
    }

    /**
     * Campos del formulario tal como llegaron (recortados), sin validar.
     */
    private function leerFormulario()
    {
        $texto = function ($campo) {
            return isset($_POST[$campo]) && is_string($_POST[$campo]) ? trim($_POST[$campo]) : '';
        };

        $medios = isset($_POST['medios_pago']) && is_array($_POST['medios_pago']) ? $_POST['medios_pago'] : [];
        $medios = array_values(array_intersect(self::MEDIOS_PAGO, array_filter($medios, 'is_string')));

        return [
            'id_materia' => $texto('id_materia'),
            'modalidad' => $texto('modalidad'),
            'disponibilidad' => $texto('disponibilidad'),
            'descripcion' => $texto('descripcion'),
            'precio' => $texto('precio'),
            'medios_pago' => $medios,
        ];
    }

    /**
     * Devuelve el primer error encontrado o null si los datos son válidos.
     */
    private function validar(array $datos)
    {
        if (filter_var($datos['id_materia'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            return 'Elegí una materia de la lista.';
        }

        if (!in_array($datos['modalidad'], self::MODALIDADES, true)) {
            return 'Elegí una modalidad válida.';
        }

        if ($datos['disponibilidad'] === '' || mb_strlen($datos['disponibilidad'], 'UTF-8') > self::MAX_DISPONIBILIDAD) {
            return 'Indicá tu disponibilidad (hasta ' . self::MAX_DISPONIBILIDAD . ' caracteres).';
        }

        if ($datos['descripcion'] === '' || mb_strlen($datos['descripcion'], 'UTF-8') > self::MAX_DESCRIPCION) {
            return 'Escribí una descripción (hasta ' . self::MAX_DESCRIPCION . ' caracteres).';
        }

        $precio = $this->normalizarPrecio($datos['precio']);
        if ($precio === null) {
            return 'Ingresá un precio válido (por ejemplo 2500 o 2500,50). Usá 0 para una tutoría gratuita.';
        }

        if ((float) $precio > 0 && empty($datos['medios_pago'])) {
            return 'Si la tutoría tiene precio, elegí al menos un medio de pago.';
        }

        return null;
    }

    /**
     * "2.500,50" / "2500.50" / "$ 2500" -> "2500.50". Vacío = "0.00". null si no es válido.
     */
    private function normalizarPrecio($valor)
    {
        $valor = str_replace(['$', ' '], '', (string) $valor);
        if ($valor === '') {
            return '0.00';
        }

        if (strpos($valor, ',') !== false) {
            // Coma como separador decimal (formato argentino): se quitan los puntos de miles
            $valor = str_replace(['.', ','], ['', '.'], $valor);
        } elseif (preg_match('/^\d{1,3}(\.\d{3})+$/', $valor)) {
            // Solo puntos de miles, sin decimales (ej. "2.500")
            $valor = str_replace('.', '', $valor);
        }

        if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $valor)) {
            return null;
        }

        return number_format((float) $valor, 2, '.', '');
    }

    private function datosParaGuardar(array $datos)
    {
        $precio = $this->normalizarPrecio($datos['precio']);

        return [
            'id_materia' => (int) $datos['id_materia'],
            'modalidad' => $datos['modalidad'],
            'disponibilidad' => $datos['disponibilidad'],
            'descripcion' => $datos['descripcion'],
            'precio' => $precio,
            // Una tutoría gratuita no guarda medios de pago
            'medios_pago' => (float) $precio > 0 ? implode(',', $datos['medios_pago']) : null,
        ];
    }

    private function volverConError($mensaje, array $datos, $idOferta)
    {
        $this->flash('error', $mensaje);
        $_SESSION['oferta_form'] = ['id_oferta' => $idOferta, 'datos' => $datos];
        $this->redirect(self::VOLVER, 303);
    }

    private function idDesdePost($campo)
    {
        if (!isset($_POST[$campo]) || !is_string($_POST[$campo])) {
            return null;
        }

        $id = filter_var($_POST[$campo], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? null : $id;
    }
}
