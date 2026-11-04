<?php

/**
 * Controlador de reportes enviados por alumnos y tutores.
 */
final class ReporteController extends Controller
{
    /**
     * Procesa la creación de un nuevo reporte enviado desde la interfaz.
     */
    public function crear()
    {
        if (!$this->isPost()) {
            $this->redirect('index.php?r=dashboard');
            return;
        }

        $idDenunciante = Session::userId();
        $tipoEntidad = trim($_POST['tipo_entidad'] ?? '');
        $idReferencia = (int) ($_POST['id_referencia'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (!$idDenunciante || !in_array($tipoEntidad, ['sala', 'usuario'], true) || $idReferencia <= 0 || empty($motivo) || empty($descripcion)) {
            $this->json(['ok' => false, 'error' => 'Por favor completa todos los campos del reporte.']);
            return;
        }

        try {
            $db = Database::getConnection();
            $reporteModel = new Reporte($db);
            $idReporte = $reporteModel->crear($idDenunciante, $tipoEntidad, $idReferencia, $motivo, $descripcion);

            // Dejar constancia en auditoria
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $stmtAud = $db->prepare(
                "INSERT INTO auditoria (id_usuario, accion, descripcion, ip) VALUES (:user, 'crear', :desc, :ip)"
            );
            $stmtAud->execute([
                'user' => $idDenunciante,
                'desc' => "Reporte #{$idReporte} generado sobre {$tipoEntidad} #{$idReferencia} por motivo: {$motivo}",
                'ip'   => $ip,
            ]);

            $this->json(['ok' => true, 'mensaje' => 'Tu reporte fue enviado al equipo de moderación.']);
        } catch (Exception $e) {
            error_log('Error al crear reporte: ' . $e->getMessage());
            $this->json(['ok' => false, 'error' => 'Ocurrió un error al procesar tu denuncia.']);
        }
    }
}

