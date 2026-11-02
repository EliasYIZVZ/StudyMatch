/**
 * STUDY MATCH • Perfil del usuario (área alumno)
 *
 * Lógica compartida del menú de perfil y sus modales
 * (app/views/partials/perfil-alumno-menu.php y perfil-alumno-modales.php):
 * editar datos, cambiar foto, toast de confirmación y carga de los datos
 * del usuario de la sesión (index.php?r=usuario-actual).
 */
(function () {
  'use strict';

  // ==========================================
  // 3. Edición de Perfil y Sincronización LocalStorage
  // ==========================================
  const formPerfil = document.getElementById('formPerfil');
  formPerfil.addEventListener('submit', function(e) {
    e.preventDefault();

    const perfilData = {
      nombre: document.getElementById('editNombre').value.trim(),
      email: document.getElementById('editEmail').value.trim(),
      regional: document.getElementById('editRegional').value,
      carrera: document.getElementById('editCarrera').value,
      nivel: document.getElementById('editNivel').value,
      discord: document.getElementById('editDiscord').value.trim()
    };

    localStorage.setItem('study-match-user-profile', JSON.stringify(perfilData));
    aplicarDatosPerfil(perfilData);

    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('modalEditarPerfil'));
    if (modalInstance) modalInstance.hide();
    mostrarToast("¡Perfil actualizado con éxito!");
  });

  function aplicarDatosPerfil(data) {
    if (!data) return;
    document.querySelectorAll('.userNameSlot').forEach(el => el.textContent = data.nombre);
    document.querySelectorAll('.userEmailSlot').forEach(el => el.textContent = data.email);
    
    const carreraCorta = data.carrera.includes('Programación') ? 'TUP' : 'Sistemas';
    document.querySelectorAll('.userCareerSlot').forEach(el => el.textContent = `${carreraCorta} • ${data.nivel}`);
    
    const regionalCorta = data.regional ? data.regional.split(' ')[0] : 'FRH';
    const legajoSlot = document.getElementById('userLegajoSlot');
    if (legajoSlot) legajoSlot.textContent = `Legajo: 51294 • ${regionalCorta}`;

    if (!selectedPhotoUrl) {
      const partes = data.nombre.split(' ');
      const iniciales = partes.length >= 2 
        ? (partes[0][0] + partes[1][0]).toUpperCase() 
        : data.nombre.substring(0, 2).toUpperCase();

      document.querySelectorAll('.userAvatarSlot').forEach(el => {
        el.style.backgroundImage = 'none';
        el.textContent = iniciales;
      });
      const prevInit = document.getElementById('previewInitials');
      if (prevInit) prevInit.textContent = iniciales;
    }
  }

  // ==========================================
  // 4. Subir y Cambiar Foto de Perfil
  // ==========================================
  let selectedPhotoUrl = localStorage.getItem('study-match-user-photo') || null;
  const inputUploadFile = document.getElementById('inputUploadFile');
  const previewAvatarBox = document.getElementById('previewAvatarBox');
  const previewInitials = document.getElementById('previewInitials');
  const btnGuardarFoto = document.getElementById('btnGuardarFoto');

  if (selectedPhotoUrl) {
    document.querySelectorAll('.userAvatarSlot').forEach(slot => {
      slot.style.backgroundImage = `url('${selectedPhotoUrl}')`;
      slot.textContent = '';
    });
    if (previewAvatarBox) previewAvatarBox.style.backgroundImage = `url('${selectedPhotoUrl}')`;
    if (previewInitials) previewInitials.textContent = '';
  }

  if (inputUploadFile) {
    inputUploadFile.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
          selectedPhotoUrl = event.target.result;
          previewAvatarBox.style.backgroundImage = `url('${selectedPhotoUrl}')`;
          previewInitials.textContent = '';
          document.querySelectorAll('.preset-avatar').forEach(p => p.classList.remove('selected'));
        };
        reader.readAsDataURL(file);
      }
    });
  }

  function seleccionarPresetAvatar(url) {
    selectedPhotoUrl = url;
    previewAvatarBox.style.backgroundImage = `url('${url}')`;
    previewInitials.textContent = '';
    document.querySelectorAll('.preset-avatar').forEach(p => {
      p.classList.toggle('selected', p.src === url);
    });
  }

  if (btnGuardarFoto) {
    btnGuardarFoto.addEventListener('click', function() {
      if (selectedPhotoUrl) {
        localStorage.setItem('study-match-user-photo', selectedPhotoUrl);
        document.querySelectorAll('.userAvatarSlot').forEach(slot => {
          slot.style.backgroundImage = `url('${selectedPhotoUrl}')`;
          slot.textContent = '';
        });
        mostrarToast("¡Foto de perfil actualizada!");
      }
      const modalFotoInstance = bootstrap.Modal.getInstance(document.getElementById('modalSubirFoto'));
      if (modalFotoInstance) modalFotoInstance.hide();
    });
  }

  // ==========================================
  // 5. Toast Helper
  // ==========================================
  function mostrarToast(mensaje) {
    const toastEl = document.getElementById('toastSuccess');
    document.getElementById('toastMessage').textContent = mensaje;
    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
  }

  // Cargar perfil guardado si existe
  const perfilGuardado = localStorage.getItem('study-match-user-profile');
  if (perfilGuardado) {
    try {
      aplicarDatosPerfil(JSON.parse(perfilGuardado));
    } catch(e) {}
  }

  function aplicarNombreDeSesion(nombre) {
    document.querySelectorAll('.userNameSlot').forEach(el => {
      el.textContent = nombre;
    });

    const editNombre = document.getElementById('editNombre');
    if (editNombre) editNombre.value = nombre;

    if (!selectedPhotoUrl) {
      const partes = nombre.trim().split(/\s+/);
      const iniciales = partes.length >= 2
        ? (partes[0][0] + partes[1][0]).toUpperCase()
        : nombre.substring(0, 2).toUpperCase();
      document.querySelectorAll('.userAvatarSlot').forEach(el => {
        el.style.backgroundImage = 'none';
        el.textContent = iniciales;
      });
      const prevInit = document.getElementById('previewInitials');
      if (prevInit) prevInit.textContent = iniciales;
    }
  }

  fetch('index.php?r=usuario-actual', { credentials: 'same-origin' })
    .then(response => {
      if (!response.ok) throw new Error('No se pudo cargar el usuario de la sesión.');
      return response.json();
    })
    .then(user => {
      if (
        typeof user.nombre !== 'string'
        || user.nombre.trim() === ''
        || typeof user.email !== 'string'
        || user.email.trim() === ''
      ) {
        throw new Error('La respuesta del usuario no contiene un nombre o correo válido.');
      }
      aplicarNombreDeSesion(user.nombre);
      document.getElementById('editEmail').value = user.email;
      // La carrera es fija (TUP): no se toma de la base de datos.
      const nivelPorValorDB = {
        '1ro': '1° Año',
        '2do': '2° Año',
        '3ro': '3° Año',
        '4to': '4° Año',
        '5to': '5° Año',
        'graduado': 'Graduado'
      };
      if (typeof user.nivel === 'string' && nivelPorValorDB[user.nivel]) {
        document.getElementById('editNivel').value = nivelPorValorDB[user.nivel];
      }
      document.querySelectorAll('.userEmailSlot').forEach(el => {
        el.textContent = user.email;
      });
    })
    .catch(error => {
      console.error(error);
      document.querySelectorAll('.userNameSlot').forEach(el => {
        el.textContent = 'No disponible';
      });
      document.querySelectorAll('.userEmailSlot').forEach(el => {
        el.textContent = 'No disponible';
      });
    });

  // Funciones usadas desde atributos onclick del HTML
  window.seleccionarPresetAvatar = seleccionarPresetAvatar;
  window.mostrarToast = mostrarToast;
})();
