/**
 * STUDY MATCH • Mis publicaciones (app/views/ofertas/mis-publicaciones.php)
 * - Un mismo modal sirve para publicar y para editar una oferta.
 * - Si el servidor rechazó el último envío, el modal se reabre con lo escrito.
 * - Confirmación antes de eliminar.
 */
(function () {
  'use strict';

  var modal = document.getElementById('modalOferta');
  if (!modal) return;

  var form = document.getElementById('formOferta');
  var campos = {
    id: document.getElementById('ofertaId'),
    materia: document.getElementById('ofertaMateria'),
    modalidad: document.getElementById('ofertaModalidad'),
    disponibilidad: document.getElementById('ofertaDisponibilidad'),
    descripcion: document.getElementById('ofertaDescripcion'),
    precio: document.getElementById('ofertaPrecio')
  };
  var medios = modal.querySelectorAll('input[name="medios_pago[]"]');
  var ayudaMedios = document.getElementById('ofertaMediosAyuda');
  var contador = document.getElementById('ofertaDescripcionContador');
  var titulo = document.getElementById('modalOfertaLabel');
  var textoBoton = document.getElementById('btnGuardarOfertaTexto');
  var btnGuardar = document.getElementById('btnGuardarOferta');
  var datosPendientes = null;

  function precioMayorACero() {
    var valor = campos.precio.value.replace(/[$\s.]/g, '').replace(',', '.');
    return parseFloat(valor) > 0;
  }

  function actualizarMedios() {
    var conPrecio = precioMayorACero();
    ayudaMedios.textContent = conPrecio
      ? 'Elegí al menos un medio de pago.'
      : 'Solo si la tutoría tiene precio.';
    ayudaMedios.classList.toggle('text-warning-emphasis', conPrecio);
  }

  function actualizarContador() {
    contador.textContent = campos.descripcion.value.length;
  }

  function completar(datos) {
    var edicion = datos && datos.id_oferta;
    campos.id.value = edicion ? datos.id_oferta : '';
    campos.materia.value = datos ? String(datos.id_materia || '') : '';
    campos.modalidad.value = datos && datos.modalidad ? datos.modalidad : campos.modalidad.options[0].value;
    campos.disponibilidad.value = datos ? (datos.disponibilidad || '') : '';
    campos.descripcion.value = datos ? (datos.descripcion || '') : '';
    campos.precio.value = datos && datos.precio !== undefined && datos.precio !== '' ? datos.precio : '0';
    var elegidos = datos && datos.medios_pago ? datos.medios_pago : [];
    medios.forEach(function (casilla) {
      casilla.checked = elegidos.indexOf(casilla.value) !== -1;
    });

    titulo.textContent = edicion ? 'Editar publicación' : 'Publicar nueva tutoría';
    textoBoton.textContent = edicion ? 'Guardar cambios' : 'Publicar';
    btnGuardar.disabled = false;
    actualizarMedios();
    actualizarContador();
  }

  modal.addEventListener('show.bs.modal', function (event) {
    var origen = event.relatedTarget;
    if (datosPendientes) {
      completar(datosPendientes);
      datosPendientes = null;
    } else if (origen && origen.getAttribute('data-oferta')) {
      completar(JSON.parse(origen.getAttribute('data-oferta')));
    } else {
      completar(null);
    }
  });

  campos.precio.addEventListener('input', actualizarMedios);
  campos.descripcion.addEventListener('input', actualizarContador);

  // Evita envíos duplicados por doble clic
  form.addEventListener('submit', function () {
    btnGuardar.disabled = true;
  });

  // Reabrir el modal con los datos del envío que falló
  var previo = document.getElementById('ofertaFormularioPrevio');
  if (previo) {
    try {
      datosPendientes = JSON.parse(previo.textContent);
      bootstrap.Modal.getOrCreateInstance(modal).show();
    } catch (e) {
      datosPendientes = null;
    }
  } else if (new URLSearchParams(window.location.search).get('nueva') === '1') {
    // Llegada desde "Publicar nueva tutoría" del Panel Tutor
    bootstrap.Modal.getOrCreateInstance(modal).show();
  }

  // Confirmación de eliminación
  var modalEliminar = document.getElementById('modalEliminarOferta');
  if (modalEliminar) {
    modalEliminar.addEventListener('show.bs.modal', function (event) {
      var origen = event.relatedTarget;
      if (!origen) return;
      var conSolicitudes = origen.getAttribute('data-con-solicitudes') === '1';

      document.getElementById('eliminarOfertaId').value = origen.getAttribute('data-id-oferta') || '';
      document.getElementById('eliminarOfertaMateria').textContent = origen.getAttribute('data-materia') || '';
      document.getElementById('eliminarOfertaTexto').textContent = conSolicitudes
        ? 'Esta publicación ya recibió solicitudes: no se va a borrar, se va a pausar para conservar el historial y los chats.'
        : 'Esta acción no se puede deshacer.';
      document.getElementById('btnConfirmarEliminar').textContent = conSolicitudes ? 'Pausar publicación' : 'Eliminar';
    });
  }
})();
