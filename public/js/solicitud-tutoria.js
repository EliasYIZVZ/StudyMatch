/**
 * STUDY MATCH • Modal "Solicitar tutoría" (dashboard del alumno)
 * Completa el modal con los datos de la card que lo abrió.
 */
(function () {
  'use strict';

  var modal = document.getElementById('modalSolicitarTutoria');
  if (!modal) return;

  var form = document.getElementById('formSolicitarTutoria');
  var inputOferta = document.getElementById('solicitudIdOferta');
  var mensaje = document.getElementById('solicitudMensaje');
  var contador = document.getElementById('solicitudMensajeContador');
  var materia = document.getElementById('solicitudMateria');
  var tutor = document.getElementById('solicitudTutor');
  var btnEnviar = document.getElementById('btnEnviarSolicitud');

  function actualizarContador() {
    contador.textContent = mensaje.value.length;
  }

  modal.addEventListener('show.bs.modal', function (event) {
    var origen = event.relatedTarget;
    if (!origen) return;

    inputOferta.value = origen.getAttribute('data-id-oferta') || '';
    materia.textContent = origen.getAttribute('data-materia') || '';
    tutor.textContent = origen.getAttribute('data-tutor') || '';
    mensaje.value = '';
    btnEnviar.disabled = false;
    actualizarContador();
  });

  modal.addEventListener('shown.bs.modal', function () {
    mensaje.focus();
  });

  mensaje.addEventListener('input', actualizarContador);

  // Evita envíos duplicados por doble clic
  form.addEventListener('submit', function () {
    btnEnviar.disabled = true;
  });
})();
