<?php

/**
 * Reportes enviados por alumnos y tutores (sala, tutoría, mensaje, tutor o alumno).
 * Responde JSON: lo consume el modal de reporte (partials/modal-reportar.php).
 */
final class ReporteController extends Controller
{
    /**
     * Registra un reporte. Las validaciones (que lo reportado exista, que el
     * denunciante pueda reportarlo, motivos permitidos, duplicados) están en el modelo.
     */
    public function crear()
    {
        if (!$this->isPost()) {
            $this->redirect('index.php?r=dashboard');
            return;
        }

        $idDenunciante = Session::userId();
        $tipo = isset($_POST['tipo_entidad']) && is_string($_POST['tipo_entidad']) ? trim($_POST['tipo_entidad']) : '';
        $idReferencia = isset($_POST['id_referencia']) ? (int) $_POST['id_referencia'] : 0;
        $motivo = isset($_POST['motivo']) && is_string($_POST['motivo']) ? $_POST['motivo'] : '';
        $descripcion = isset($_POST['descripcion']) && is_string($_POST['descripcion']) ? $_POST['descripcion'] : '';

        try {
            $db = Database::getConnection();
            $resultado = (new Reporte($db))->crear($idDenunciante, $tipo, $idReferencia, $motivo, $descripcion);

            if (!$resultado['ok']) {
                $this->json(['ok' => false, 'error' => $resultado['error']]);
                return;
            }

            // Constancia en la auditoría
            $db->prepare(
                "INSERT INTO auditoria (id_usuario, accion, descripcion, ip) VALUES (:usuario, 'crear', :descripcion, :ip)"
            )->execute([
                'usuario' => $idDenunciante,
                'descripcion' => "Reporte #{$resultado['id']} sobre {$tipo} #{$idReferencia}. Motivo: " . trim($motivo),
                'ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
            ]);

            $this->json(['ok' => true, 'mensaje' => 'Tu reporte fue enviado al equipo de moderación.']);
        } catch (PDOException $exception) {
            error_log('Error al crear el reporte: ' . $exception->getMessage());
            $this->json(['ok' => false, 'error' => 'No se pudo enviar el reporte. Intentá nuevamente más tarde.']);
        }
    }
}
