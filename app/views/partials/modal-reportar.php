<!-- ==========================================================================
     MODAL REUTILIZABLE DE REPORTE (ALUMNOS Y TUTORES)
     ========================================================================== -->
<div class="modal fade" id="modalReportarIncidente" tabindex="-1" aria-labelledby="modalReportarIncidenteLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow-lg">
      
      <div class="modal-header border-bottom px-4 pt-4 pb-3">
        <div class="d-flex align-items-center gap-2">
          <div class="bg-danger-subtle text-danger p-2 rounded-3">
            <i class="bi bi-shield-exclamation fs-5"></i>
          </div>
          <div>
            <h5 class="modal-title fw-bold text-dark" id="modalReportarIncidenteLabel">Reportar Incidente</h5>
            <p class="text-secondary small mb-0">Moderación y normas de convivencia StudyMatch</p>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <form id="formReportarIncidente" onsubmit="enviarReporteIncidente(event)">
        <div class="modal-body p-4">
          
          <!-- Elemento seleccionado -->
          <div class="p-3 bg-light rounded-3 border mb-3">
            <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.68rem;" id="modalRepSubtitulo">Denunciando:</span>
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" id="modalRepNombre">
              <span id="modalRepIcono"><i class="bi bi-flag"></i></span>
              <span id="modalRepNombreTexto">Cargando...</span>
            </h6>
          </div>

          <!-- Campos ocultos de referencia -->
          <input type="hidden" name="tipo_entidad" id="modalRepTipo" value="tutor">
          <input type="hidden" name="id_referencia" id="modalRepReferencia" value="0">

          <!-- Desplegable de Motivo -->
          <div class="mb-3">
            <label for="modalRepMotivo" class="form-label small fw-semibold text-secondary">
              Motivo principal de la denuncia: <span class="text-danger">*</span>
            </label>
            <select class="form-select rounded-3 shadow-none" id="modalRepMotivo" name="motivo" required onchange="cambiarMotivoReporte(this.value)">
              <option value="" disabled selected>Seleccioná un motivo...</option>
            </select>
          </div>

          <!-- Descripción libre -->
          <div class="mb-2">
            <label for="modalRepDescripcion" class="form-label small fw-semibold text-secondary">
              Descripción detallada de lo sucedido: <span class="text-danger">*</span>
            </label>
            <textarea class="form-control rounded-3 shadow-none" 
                      id="modalRepDescripcion" 
                      name="descripcion" 
                      rows="3"
                      maxlength="<?= (int) Reporte::MAX_DESCRIPCION ?>"
                      placeholder="Explicá con claridad qué ocurrió (podés incluir enlaces si corresponde)..." 
                      required></textarea>
            <div class="form-text" style="font-size: 0.72rem;">
              Tu reporte será evaluado de forma anónima y confidencial por los administradores de la UTN.
            </div>
          </div>

          <div id="alertaErrorReporte" class="alert alert-danger py-2 small mb-0 mt-3" style="display: none;"></div>

        </div>

        <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
          <button type="button" class="btn btn-outline-secondary rounded-pill px-4 btn-sm" data-bs-dismiss="modal">
            Cancelar
          </button>
          <button type="submit" class="btn btn-danger rounded-pill px-4 btn-sm fw-semibold d-inline-flex align-items-center gap-1.5" id="btnEnviarReporteModal">
            <i class="bi bi-send-fill"></i>
            <span>Enviar Reporte</span>
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

<script>
  // Motivos permitidos por tipo (vienen del modelo Reporte, que también los valida)
  const MOTIVOS_REPORTE = <?= json_encode(Reporte::MOTIVOS, JSON_UNESCAPED_UNICODE) ?>;

  // Texto del encabezado e ícono de cada tipo de reporte
  const TIPOS_REPORTE = {
    sala:    { subtitulo: 'Sala de Discord a reportar:', icono: '<i class="bi bi-discord text-primary"></i>' },
    tutoria: { subtitulo: 'Tutoría a reportar:',          icono: '<i class="bi bi-journal-text text-primary"></i>' },
    mensaje: { subtitulo: 'Mensaje a reportar:',          icono: '<i class="bi bi-chat-left-text text-danger"></i>' },
    tutor:   { subtitulo: 'Tutor a reportar:',            icono: '<i class="bi bi-person-fill text-danger"></i>' },
    alumno:  { subtitulo: 'Alumno a reportar:',           icono: '<i class="bi bi-person-fill text-danger"></i>' }
  };

  /**
   * Abre el modal de reporte configurado para lo que se reporta.
   *
   * @param {string} tipo 'sala' | 'tutoria' | 'mensaje' | 'tutor' | 'alumno'
   * @param {number} idReferencia ID de la sala, tutoría, mensaje o usuario a reportar
   * @param {string} nombreElemento Texto para mostrar en el encabezado
   */
  function abrirModalReporte(tipo, idReferencia, nombreElemento) {
    document.getElementById('modalRepTipo').value = tipo;
    document.getElementById('modalRepReferencia').value = idReferencia;
    document.getElementById('modalRepNombreTexto').textContent = nombreElemento;
    const config = TIPOS_REPORTE[tipo] || TIPOS_REPORTE.tutor;
    document.getElementById('modalRepSubtitulo').textContent = config.subtitulo;
    document.getElementById('modalRepIcono').innerHTML = config.icono;

    // Rellenar opciones de motivos según el tipo
    const select = document.getElementById('modalRepMotivo');
    select.innerHTML = '<option value="" disabled selected>Seleccioná un motivo...</option>';
    
    const lista = MOTIVOS_REPORTE[tipo] || [];
    lista.forEach(m => {
      const opt = document.createElement('option');
      opt.value = m;
      opt.textContent = m;
      select.appendChild(opt);
    });

    document.getElementById('modalRepDescripcion').value = '';
    const alerta = document.getElementById('alertaErrorReporte');
    if (alerta) alerta.style.display = 'none';

    const modalEl = document.getElementById('modalReportarIncidente');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
  }

  function cambiarMotivoReporte(motivo) {
    const desc = document.getElementById('modalRepDescripcion');
    if (motivo === 'Otro motivo') {
      desc.placeholder = 'Por favor, contanos en detalle qué ocurrió...';
      desc.focus();
    } else {
      desc.placeholder = 'Explicá con claridad qué ocurrió (podés incluir enlaces si corresponde)...';
    }
  }

  function enviarReporteIncidente(e) {
    e.preventDefault();
    const form = document.getElementById('formReportarIncidente');
    const btn = document.getElementById('btnEnviarReporteModal');
    const alerta = document.getElementById('alertaErrorReporte');
    if (alerta) alerta.style.display = 'none';

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enviando...';

    const formData = new FormData(form);

    fetch('index.php?r=crear-reporte', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Enviar Reporte';

      if (data.ok) {
        const modalEl = document.getElementById('modalReportarIncidente');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        alert(data.mensaje || 'Reporte enviado con éxito.');
      } else {
        if (alerta) {
          alerta.textContent = data.error || 'Ocurrió un error al enviar el reporte.';
          alerta.style.display = 'block';
        } else {
          alert(data.error);
        }
      }
    })
    .catch(err => {
      console.error(err);
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Enviar Reporte';
      if (alerta) {
        alerta.textContent = 'Error de conexión con el servidor.';
        alerta.style.display = 'block';
      }
    });
  }
</script>

