<?php defined('APP_PATH') || exit('Acceso denegado.'); ?>
<?php /* Modal para solicitar una oferta de tutoría. Lógica en public/js/solicitud-tutoria.js */ ?>
  <div class="modal fade" id="modalSolicitarTutoria" tabindex="-1" aria-labelledby="modalSolicitarTutoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 border-0 shadow-lg">
        <form method="POST" action="index.php?r=solicitar-tutoria" id="formSolicitarTutoria">
          <div class="modal-header border-bottom px-4 pt-4 pb-3">
            <div class="d-flex align-items-center gap-2">
              <div class="bg-primary-subtle text-primary p-2 rounded-3">
                <i class="bi bi-send fs-5"></i>
              </div>
              <div>
                <h5 class="modal-title fw-bold text-dark" id="modalSolicitarTutoriaLabel">Solicitar tutoría</h5>
                <p class="text-secondary small mb-0">
                  <span id="solicitudMateria">Materia</span> con <span id="solicitudTutor">tutor</span>
                </p>
              </div>
            </div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>

          <div class="modal-body p-4">
            <input type="hidden" name="id_oferta" id="solicitudIdOferta" value="">
            <label for="solicitudMensaje" class="form-label small fw-semibold text-dark">Mensaje para el tutor <span class="text-secondary fw-normal">(opcional)</span></label>
            <textarea class="form-control" id="solicitudMensaje" name="mensaje" rows="4" maxlength="500"
                      placeholder="Contale qué tema necesitás repasar y cuándo te vendría bien." style="resize: none;"></textarea>
            <div class="text-end text-secondary small mt-1"><span id="solicitudMensajeContador">0</span> / 500</div>
          </div>

          <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
            <button type="button" class="btn btn-outline-secondary px-4 rounded-pill" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary px-4 rounded-pill fw-semibold shadow-sm" id="btnEnviarSolicitud">
              <i class="bi bi-send me-1"></i> Enviar solicitud
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
