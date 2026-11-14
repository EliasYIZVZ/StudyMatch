/**
 * Salas públicas de Discord: modal de publicación, confirmación de borrado y reporte.
 */
document.addEventListener('DOMContentLoaded', function () {
  var modalSala = document.getElementById('modalSala');
  var descripcion = document.getElementById('salaDescripcion');
  var contador = document.getElementById('salaDescripcionContador');

  function actualizarContador() {
    if (descripcion && contador) {
      contador.textContent = descripcion.value.length;
    }
  }

  if (descripcion) {
    descripcion.addEventListener('input', actualizarContador);
  }

  // Si el último envío tuvo un error, se reabre el modal con lo que se había escrito
  var previo = document.getElementById('salaFormularioPrevio');
  if (previo && modalSala) {
    try {
      var datos = JSON.parse(previo.textContent);
      document.getElementById('salaTitulo').value = datos.titulo || '';
      document.getElementById('salaEnlace').value = datos.enlace || '';
      descripcion.value = datos.descripcion || '';
      actualizarContador();
      bootstrap.Modal.getOrCreateInstance(modalSala).show();
    } catch (error) {
      // Datos ilegibles: se deja el formulario vacío
    }
  }

  // Confirmación de borrado: completa el id y el título de la sala elegida
  var modalEliminar = document.getElementById('modalEliminarSala');
  if (modalEliminar) {
    modalEliminar.addEventListener('show.bs.modal', function (evento) {
      var boton = evento.relatedTarget;
      if (!boton) return;
      document.getElementById('eliminarSalaId').value = boton.getAttribute('data-id-sala');
      document.getElementById('eliminarSalaTitulo').textContent = '“' + boton.getAttribute('data-titulo') + '”';
    });
  }

  // Reportar una sala de otro usuario (modal compartido de reportes)
  document.querySelectorAll('.btn-reportar-sala').forEach(function (boton) {
    boton.addEventListener('click', function () {
      if (typeof abrirModalReporte === 'function') {
        abrirModalReporte('sala', parseInt(boton.getAttribute('data-id-sala'), 10), boton.getAttribute('data-titulo'));
      }
    });
  });
});
