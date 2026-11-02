<?php defined('APP_PATH') || exit('Acceso denegado.'); ?>
<?php /* Menú de perfil de la barra superior (área alumno). Lógica en public/js/perfil.js */ ?>
          <!-- MENÚ DE PERFIL DESPLEGABLE -->
          <div class="dropdown">
            <button class="btn p-1 border-0 d-flex align-items-center gap-2 rounded-pill hover-bg-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <div class="avatar-user-display userAvatarSlot">
                AL
              </div>
              <div class="d-none d-md-block text-start lh-1 pe-1">
                <span class="d-block fw-bold text-dark small userNameSlot">Cargando...</span>
                <span class="text-secondary userRoleSlot" style="font-size: 0.72rem;">UTN • Alumno</span>
              </div>
              <i class="bi bi-chevron-down text-secondary small d-none d-md-inline"></i>
            </button>

            <!-- Contenido del Menú Desplegable -->
            <ul class="dropdown-menu profile-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2" style="min-width: 270px;">
              <li class="px-3 py-2 border-bottom mb-1 bg-light rounded-3">
                <div class="fw-bold text-dark userNameSlot">Cargando...</div>
                <div class="text-secondary small userEmailSlot">Cargando...</div>
              </li>
              
              <!-- Opciones del menú -->
              <li>
                <a class="dropdown-item rounded-3 py-2 d-flex align-items-center gap-2" href="#" onclick="irAModoTutor()">
                  <i class="bi bi-arrow-left-right text-success fs-6"></i>
                  <span>Cambiar a Modo Tutor</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item rounded-3 py-2 d-flex align-items-center gap-2" href="#" data-bs-toggle="modal" data-bs-target="#modalEditarPerfil">
                  <i class="bi bi-pencil-square text-primary fs-6"></i>
                  <span>Editar mi información</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item rounded-3 py-2 d-flex align-items-center gap-2" href="#" data-bs-toggle="modal" data-bs-target="#modalSubirFoto">
                  <i class="bi bi-camera text-primary fs-6"></i>
                  <span>Cambiar foto de perfil</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item rounded-3 py-2 d-flex align-items-center gap-2" href="#" data-bs-toggle="modal" data-bs-target="#modalDiscordConfig">
                  <i class="bi bi-discord fs-6" style="color: #2563eb;"></i>
                  <span>Vincular Discord UTN</span>
                </a>
              </li>
              <li><hr class="dropdown-divider my-1"></li>
              <li>
                <a class="dropdown-item text-danger rounded-3 py-2 d-flex align-items-center gap-2" href="index.php?r=logout">
                  <i class="bi bi-box-arrow-left fs-6"></i>
                  <span>Cerrar sesión</span>
                </a>
              </li>
            </ul>
          </div>
