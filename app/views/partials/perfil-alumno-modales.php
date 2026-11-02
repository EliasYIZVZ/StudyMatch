<?php defined('APP_PATH') || exit('Acceso denegado.'); ?>
<?php /* Modales de perfil (editar datos, foto, Discord) y toast de confirmación. Lógica en public/js/perfil.js */ ?>
<?php
/**
 * $perfilRol (opcional): 'alumno' (por defecto) o 'tutor'. Solo cambia textos y colores.
 */
$esTutorPerfil = isset($perfilRol) && $perfilRol === 'tutor';
$colorPerfil = $esTutorPerfil ? 'success' : 'primary';
$avataresPerfil = require APP_PATH . '/config/avatares.php';
?>
  <!-- ==========================================================================
       MODAL 1: EDITAR INFORMACIÓN DE PERFIL
       ========================================================================== -->
  <div class="modal fade" id="modalEditarPerfil" tabindex="-1" aria-labelledby="modalEditarPerfilLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content rounded-4 border-0 shadow-lg">
        <div class="modal-header border-bottom px-4 pt-4 pb-3">
          <div class="d-flex align-items-center gap-2">
            <div class="bg-<?= $colorPerfil ?>-subtle text-<?= $colorPerfil ?> p-2 rounded-3">
              <i class="bi bi-person-lines-fill fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold text-dark" id="modalEditarPerfilLabel"><?= $esTutorPerfil ? 'Editar Perfil del Tutor' : 'Editar Perfil del Estudiante' ?></h5>
              <p class="text-secondary small mb-0">Actualizá tus datos académicos y canales de contacto</p>
            </div>
          </div>
          <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <form id="formPerfil">
          <div class="modal-body p-4">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="editNombre" class="form-label small fw-semibold text-dark mb-2">Nombre y Apellido</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-person"></i></span>
                  <input type="text" class="form-control border-start-0 ps-0" id="editNombre" value="" required>
                </div>
              </div>

              <div class="col-md-6">
                <label for="editEmail" class="form-label small fw-semibold text-dark mb-2">Correo Institucional UTN</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-envelope"></i></span>
                  <input type="email" class="form-control border-start-0 ps-0 profile-field-readonly" id="editEmail" value="" readonly>
                </div>
              </div>

              <div class="col-md-6">
                <label for="editRegional" class="form-label small fw-semibold text-dark">Facultad Regional</label>
                <input type="text" class="form-control profile-field-readonly" id="editRegional" value="FRH (Haedo)" readonly>
              </div>

              <div class="col-md-6">
                <label for="editCarrera" class="form-label small fw-semibold text-dark">Carrera</label>
                <select class="form-select profile-field-readonly" id="editCarrera" disabled>
                  <option value="Tecnicatura Universitaria en Programación (TUP)" selected>Tecnicatura Universitaria en Programación (TUP)</option>
                </select>
              </div>

              <div class="col-md-6">
                <label for="editNivel" class="form-label small fw-semibold text-dark">Año de Cursada</label>
                <select class="form-select" id="editNivel">
                  <option value="1° Año">1° Año (Ingresante)</option>
                  <option value="2° Año">2° Año</option>
                </select>
              </div>

              <div class="col-md-6">
                <label for="editDiscord" class="form-label small fw-semibold text-dark">Usuario de Discord</label>
                <div class="input-group">
                  <span class="input-group-text bg-light border-end-0" style="color: #2563eb;"><i class="bi bi-discord"></i></span>
                  <input type="text" class="form-control border-start-0 ps-0" id="editDiscord" value="" placeholder="usuario#0000">
                </div>
              </div>

              <div class="col-12">
                <label for="editBio" class="form-label small fw-semibold text-dark"><?= $esTutorPerfil ? 'Materias que enseñás / Experiencia' : 'Materias de interés / Breve descripción' ?></label>
                <textarea class="form-control" id="editBio" rows="3" placeholder="Ingresa tu descripción..."></textarea>
              </div>
            </div>
          </div>

          <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
            <button type="button" class="btn btn-outline-secondary px-4 rounded-pill" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-<?= $colorPerfil ?> px-4 rounded-pill fw-semibold shadow-sm">
              <i class="bi bi-check2 me-1"></i> Guardar Cambios
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ==========================================================================
       MODAL 2: SUBIR / CAMBIAR FOTO DE PERFIL
       ========================================================================== -->
  <div class="modal fade" id="modalSubirFoto" tabindex="-1" aria-labelledby="modalSubirFotoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 border-0 shadow-lg">
        <div class="modal-header border-bottom px-4 pt-4 pb-3">
          <div class="d-flex align-items-center gap-2">
            <div class="bg-<?= $colorPerfil ?>-subtle text-<?= $colorPerfil ?> p-2 rounded-3">
              <i class="bi bi-camera-fill fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold text-dark" id="modalSubirFotoLabel">Actualizar Foto de Perfil</h5>
              <p class="text-secondary small mb-0">Seleccioná un avatar o subí tu propia foto</p>
            </div>
          </div>
          <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body p-4 text-center">
          <div class="preview-avatar-box mb-3" id="previewAvatarBox">
            <span id="previewInitials">AL</span>
          </div>

          <div class="mb-4">
            <label for="inputUploadFile" class="btn btn-outline-<?= $colorPerfil ?> rounded-pill px-4 fw-semibold shadow-sm">
              <i class="bi bi-upload me-1"></i> Subir imagen desde la PC
            </label>
            <input type="file" id="inputUploadFile" class="d-none" accept="image/jpeg,image/png,image/webp">
            <div class="text-muted small mt-1" style="font-size: 0.72rem;">Formatos JPG, PNG o WEBP. Tamaño máximo 2 MB</div>
          </div>

          <div class="border-top pt-3">
            <span class="small fw-semibold text-secondary d-block mb-3">O seleccioná un avatar ilustrado:</span>
            <div class="d-flex justify-content-center gap-3">
              <?php foreach ($avataresPerfil as $claveAvatar => $rutaAvatar): ?>
                <img src="<?= Vista::e($rutaAvatar) ?>" class="preset-avatar" alt="Avatar <?= Vista::e($claveAvatar) ?>"
                     data-avatar="<?= Vista::e($claveAvatar) ?>" tabindex="0" role="button" width="52" height="52">
              <?php endforeach; ?>
            </div>
          </div>

          <p id="fotoError" class="text-danger small mt-3 mb-0" role="alert"></p>
        </div>

        <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
          <button type="button" id="btnQuitarFoto" class="btn btn-link text-danger text-decoration-none me-auto px-0 d-none">
            <i class="bi bi-trash me-1"></i>Quitar foto
          </button>
          <button type="button" class="btn btn-outline-secondary px-4 rounded-pill" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" id="btnGuardarFoto" class="btn btn-<?= $colorPerfil ?> px-4 rounded-pill fw-semibold shadow-sm">
            Guardar Foto
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ==========================================================================
       MODAL 3: DISCORD Y SALAS DE ESTUDIO
       ========================================================================== -->
  <div class="modal fade" id="modalDiscordConfig" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 border-0 shadow-lg">
        <div class="modal-header border-bottom px-4 pt-4 pb-3">
          <div class="d-flex align-items-center gap-2">
            <div class="p-2 rounded-3 text-white" style="background-color: #2563eb;">
              <i class="bi bi-discord fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title fw-bold text-dark">Comunidad de Discord UTN</h5>
              <p class="text-secondary small mb-0">Canales de voz y salas de estudio compartidas</p>
            </div>
          </div>
          <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body p-4">
          <div class="p-3 rounded-3 mb-3 border d-flex align-items-center justify-content-between" style="background-color: #f5f6fe;">
            <div class="d-flex align-items-center gap-3">
              <i class="bi bi-broadcast fs-3" style="color: #2563eb;"></i>
              <div>
                <span class="d-block fw-bold text-dark small">Servidor Oficial Study Match</span>
                <span class="text-muted" style="font-size: 0.75rem;">+250 estudiantes en línea ahora</span>
              </div>
            </div>
            <span class="badge bg-success rounded-pill px-2.5 py-1 small">Conectado</span>
          </div>
          <p class="text-secondary small">
            Al realizar un match con un tutor, se les asigna automáticamente una sala privada de audio y video en nuestro servidor de Discord para compartir pantalla y resolver dudas.
          </p>
        </div>
        <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
          <button type="button" class="btn btn-primary w-100 rounded-pill fw-semibold shadow-sm" data-bs-dismiss="modal">
            Entendido
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- TOAST FLOTANTE DE NOTIFICACIÓN -->
  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
    <div id="toastSuccess" class="toast align-items-center text-bg-dark border-0 rounded-4 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body d-flex align-items-center gap-2">
          <i class="bi bi-check-circle-fill text-success fs-5"></i>
          <span id="toastMessage">¡Perfil actualizado correctamente!</span>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
      </div>
    </div>
  </div>
