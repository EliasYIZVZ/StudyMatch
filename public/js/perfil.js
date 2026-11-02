/**
 * STUDY MATCH • Perfil del usuario
 *
 * Lógica compartida del menú de perfil y sus modales
 * (app/views/partials/perfil-alumno-menu.php y perfil-alumno-modales.php):
 * editar datos, foto de perfil, toast de confirmación y carga de los datos
 * del usuario de la sesión (index.php?r=usuario-actual).
 *
 * La foto se guarda en el servidor (index.php?r=foto-perfil): un avatar
 * precargado o una imagen propia (JPG, PNG o WEBP de hasta 2 MB).
 */
(function () {
  'use strict';

  var MAX_BYTES = 2 * 1024 * 1024;
  var TIPOS_FOTO = ['image/jpeg', 'image/png', 'image/webp'];

  var nombreActual = '';
  var fotoActual = null;      // ruta de la foto guardada en el servidor (o null)
  var seleccion = null;       // cambio elegido en el modal, todavía sin guardar
  var urlVistaPrevia = null;  // URL temporal de la imagen elegida desde la PC

  // La foto ya no se guarda en el navegador: se limpia el dato viejo
  try { localStorage.removeItem('study-match-user-photo'); } catch (e) { /* sin almacenamiento */ }

  function iniciales(nombre) {
    var partes = String(nombre || '').trim().split(/\s+/).filter(Boolean);
    if (partes.length === 0) return '?';
    var letras = partes.length >= 2 ? partes[0][0] + partes[1][0] : partes[0].substring(0, 2);
    return letras.toUpperCase();
  }

  function pintarCirculo(elemento, url, textoInicial) {
    if (!elemento) return;
    if (url) {
      elemento.style.backgroundImage = 'url("' + String(url).replace(/"/g, '%22') + '")';
      elemento.textContent = '';
      elemento.classList.add('avatar-con-foto');
    } else {
      elemento.style.backgroundImage = '';
      elemento.textContent = textoInicial;
      elemento.classList.remove('avatar-con-foto');
    }
  }

  // Avatares del usuario en la barra superior, el menú lateral y el menú de perfil
  function pintarAvatares() {
    document.querySelectorAll('.userAvatarSlot').forEach(function (slot) {
      pintarCirculo(slot, fotoActual, iniciales(nombreActual));
    });
  }

  // ==========================================
  // 1. Edición de perfil (por ahora en el navegador)
  // ==========================================
  var formPerfil = document.getElementById('formPerfil');
  if (formPerfil) {
    formPerfil.addEventListener('submit', function (e) {
      e.preventDefault();

      var perfilData = {
        nombre: document.getElementById('editNombre').value.trim(),
        email: document.getElementById('editEmail').value.trim(),
        regional: document.getElementById('editRegional').value,
        carrera: document.getElementById('editCarrera').value,
        nivel: document.getElementById('editNivel').value,
        discord: document.getElementById('editDiscord').value.trim()
      };

      try { localStorage.setItem('study-match-user-profile', JSON.stringify(perfilData)); } catch (err) { /* sin almacenamiento */ }
      aplicarDatosPerfil(perfilData);

      var modalInstance = bootstrap.Modal.getInstance(document.getElementById('modalEditarPerfil'));
      if (modalInstance) modalInstance.hide();
      mostrarToast('¡Perfil actualizado con éxito!');
    });
  }

  function aplicarDatosPerfil(data) {
    if (!data) return;
    nombreActual = data.nombre || nombreActual;
    document.querySelectorAll('.userNameSlot').forEach(function (el) { el.textContent = data.nombre; });
    document.querySelectorAll('.userEmailSlot').forEach(function (el) { el.textContent = data.email; });

    var carreraCorta = String(data.carrera || '').indexOf('Programación') !== -1 ? 'TUP' : 'Sistemas';
    document.querySelectorAll('.userCareerSlot').forEach(function (el) { el.textContent = carreraCorta + ' • ' + data.nivel; });

    var regionalCorta = data.regional ? data.regional.split(' ')[0] : 'FRH';
    var legajoSlot = document.getElementById('userLegajoSlot');
    if (legajoSlot) legajoSlot.textContent = 'Legajo: 51294 • ' + regionalCorta;

    pintarAvatares();
  }

  // ==========================================
  // 2. Foto de perfil
  // ==========================================
  var modalFoto = document.getElementById('modalSubirFoto');
  var preview = document.getElementById('previewAvatarBox');
  var previewInitials = document.getElementById('previewInitials');
  var inputUploadFile = document.getElementById('inputUploadFile');
  var btnGuardarFoto = document.getElementById('btnGuardarFoto');
  var btnQuitarFoto = document.getElementById('btnQuitarFoto');
  var fotoError = document.getElementById('fotoError');

  function mostrarErrorFoto(mensaje) {
    if (fotoError) fotoError.textContent = mensaje || '';
  }

  function pintarVistaPrevia(url) {
    if (!preview) return;
    if (url) {
      preview.style.backgroundImage = 'url("' + String(url).replace(/"/g, '%22') + '")';
      if (previewInitials) previewInitials.textContent = '';
    } else {
      preview.style.backgroundImage = '';
      if (previewInitials) previewInitials.textContent = iniciales(nombreActual);
    }
  }

  function marcarAvatar(clave) {
    document.querySelectorAll('.preset-avatar').forEach(function (img) {
      img.classList.toggle('selected', img.getAttribute('data-avatar') === clave);
    });
  }

  function liberarVistaPrevia() {
    if (urlVistaPrevia) {
      URL.revokeObjectURL(urlVistaPrevia);
      urlVistaPrevia = null;
    }
  }

  if (modalFoto) {
    // Cada vez que se abre, muestra la foto guardada actualmente
    modalFoto.addEventListener('show.bs.modal', function () {
      seleccion = null;
      liberarVistaPrevia();
      if (inputUploadFile) inputUploadFile.value = '';
      mostrarErrorFoto('');
      marcarAvatar(null);
      pintarVistaPrevia(fotoActual);
      if (btnQuitarFoto) btnQuitarFoto.classList.toggle('d-none', !fotoActual);
      if (btnGuardarFoto) btnGuardarFoto.disabled = false;
    });

    // Avatares precargados
    modalFoto.querySelectorAll('.preset-avatar').forEach(function (img) {
      img.addEventListener('click', function () {
        liberarVistaPrevia();
        seleccion = { accion: 'avatar', avatar: img.getAttribute('data-avatar') };
        marcarAvatar(seleccion.avatar);
        pintarVistaPrevia(img.getAttribute('src'));
        mostrarErrorFoto('');
      });
      img.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          img.click();
        }
      });
    });
  }

  // Imagen propia (se valida acá y de nuevo en el servidor)
  if (inputUploadFile) {
    inputUploadFile.addEventListener('change', function () {
      var archivo = inputUploadFile.files[0];
      if (!archivo) return;

      if (TIPOS_FOTO.indexOf(archivo.type) === -1) {
        mostrarErrorFoto('El archivo debe ser una imagen JPG, PNG o WEBP.');
        inputUploadFile.value = '';
        return;
      }
      if (archivo.size > MAX_BYTES) {
        mostrarErrorFoto('La imagen supera el máximo de 2 MB.');
        inputUploadFile.value = '';
        return;
      }

      liberarVistaPrevia();
      urlVistaPrevia = URL.createObjectURL(archivo);
      seleccion = { accion: 'subir', foto: archivo };
      marcarAvatar(null);
      pintarVistaPrevia(urlVistaPrevia);
      mostrarErrorFoto('');
    });
  }

  if (btnQuitarFoto) {
    btnQuitarFoto.addEventListener('click', function () {
      liberarVistaPrevia();
      if (inputUploadFile) inputUploadFile.value = '';
      seleccion = { accion: 'quitar' };
      marcarAvatar(null);
      pintarVistaPrevia(null);
      mostrarErrorFoto('');
    });
  }

  if (btnGuardarFoto) {
    btnGuardarFoto.addEventListener('click', function () {
      var modal = bootstrap.Modal.getInstance(modalFoto);
      if (!seleccion) {
        if (modal) modal.hide();
        return;
      }

      var datos = new FormData();
      datos.append('accion', seleccion.accion);
      if (seleccion.accion === 'avatar') datos.append('avatar', seleccion.avatar);
      if (seleccion.accion === 'subir') datos.append('foto', seleccion.foto);

      btnGuardarFoto.disabled = true;
      fetch('index.php?r=foto-perfil', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: datos
      })
        .then(function (respuesta) {
          return respuesta.json().catch(function () {
            throw new Error('No se pudo guardar la foto. Intentá nuevamente.');
          }).then(function (json) {
            if (!respuesta.ok) throw new Error(json.error || 'No se pudo guardar la foto.');
            return json;
          });
        })
        .then(function (json) {
          fotoActual = json.foto || null;
          pintarAvatares();
          liberarVistaPrevia();
          if (modal) modal.hide();
          mostrarToast(fotoActual ? '¡Foto de perfil actualizada!' : 'Foto de perfil quitada.');
        })
        .catch(function (error) {
          mostrarErrorFoto(error.message);
        })
        .then(function () {
          btnGuardarFoto.disabled = false;
        });
    });
  }

  // ==========================================
  // 3. Toast
  // ==========================================
  function mostrarToast(mensaje) {
    var toastEl = document.getElementById('toastSuccess');
    if (!toastEl) return;
    document.getElementById('toastMessage').textContent = mensaje;
    new bootstrap.Toast(toastEl, { delay: 3000 }).show();
  }

  // ==========================================
  // 4. Datos del usuario de la sesión
  // ==========================================
  try {
    var perfilGuardado = localStorage.getItem('study-match-user-profile');
    if (perfilGuardado) aplicarDatosPerfil(JSON.parse(perfilGuardado));
  } catch (e) { /* datos viejos o sin almacenamiento */ }

  function aplicarNombreDeSesion(nombre) {
    nombreActual = nombre;
    document.querySelectorAll('.userNameSlot').forEach(function (el) { el.textContent = nombre; });
    var editNombre = document.getElementById('editNombre');
    if (editNombre) editNombre.value = nombre;
    pintarAvatares();
  }

  fetch('index.php?r=usuario-actual', { credentials: 'same-origin' })
    .then(function (response) {
      if (!response.ok) throw new Error('No se pudo cargar el usuario de la sesión.');
      return response.json();
    })
    .then(function (user) {
      if (typeof user.nombre !== 'string' || user.nombre.trim() === ''
        || typeof user.email !== 'string' || user.email.trim() === '') {
        throw new Error('La respuesta del usuario no contiene un nombre o correo válido.');
      }
      fotoActual = typeof user.foto === 'string' && user.foto !== '' ? user.foto : null;
      aplicarNombreDeSesion(user.nombre);
      document.getElementById('editEmail').value = user.email;
      // La carrera es fija (TUP): no se toma de la base de datos.
      var nivelPorValorDB = {
        '1ro': '1° Año', '2do': '2° Año', '3ro': '3° Año',
        '4to': '4° Año', '5to': '5° Año', 'graduado': 'Graduado'
      };
      var editNivel = document.getElementById('editNivel');
      if (editNivel && typeof user.nivel === 'string' && nivelPorValorDB[user.nivel]) {
        editNivel.value = nivelPorValorDB[user.nivel];
      }
      document.querySelectorAll('.userEmailSlot').forEach(function (el) { el.textContent = user.email; });
    })
    .catch(function (error) {
      console.error(error);
      document.querySelectorAll('.userNameSlot').forEach(function (el) { el.textContent = 'No disponible'; });
      document.querySelectorAll('.userEmailSlot').forEach(function (el) { el.textContent = 'No disponible'; });
    });

  // Usada desde atributos onclick del HTML
  window.mostrarToast = mostrarToast;
})();
