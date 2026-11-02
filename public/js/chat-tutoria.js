/**
 * STUDY MATCH • Chat de una tutoría (app/views/tutorias/detalle.php)
 *
 * - Envía mensajes con fetch (TutoriaController::enviarMensaje).
 * - Mientras la tutoría está aceptada y la pestaña está visible, busca
 *   mensajes nuevos cada 5 segundos (TutoriaController::mensajes).
 * - Muestra los links http(s) como enlaces. El texto se inserta siempre
 *   como texto plano: nunca se interpreta HTML escrito por los usuarios.
 */
(function () {
  'use strict';

  var INTERVALO_MS = 5000;
  var chat = document.getElementById('chat');
  if (!chat) return;

  var idSolicitud = chat.getAttribute('data-id-solicitud');
  var ultimoId = parseInt(chat.getAttribute('data-ultimo-id'), 10) || 0;
  var habilitado = chat.getAttribute('data-habilitado') === '1';
  var lista = document.getElementById('chatMensajes');
  var form = document.getElementById('chatForm');
  var texto = document.getElementById('chatTexto');
  var contador = document.getElementById('chatContador');
  var error = document.getElementById('chatError');
  var btnEnviar = document.getElementById('chatEnviar');
  var consultando = false;

  function irAlFinal() {
    if (lista) lista.scrollTop = lista.scrollHeight;
  }

  // Agrega el texto convirtiendo los links en <a>, sin interpretar HTML.
  function agregarTextoConLinks(destino, contenido) {
    var regex = /(https?:\/\/[^\s<>"]+)/gi;
    var inicio = 0;
    var coincidencia;

    while ((coincidencia = regex.exec(contenido)) !== null) {
      var url = coincidencia[0];
      var resto = (url.match(/[.,;:!?)\]]+$/) || [''])[0];
      if (resto) url = url.slice(0, -resto.length);

      destino.appendChild(document.createTextNode(contenido.slice(inicio, coincidencia.index)));
      var enlace = document.createElement('a');
      enlace.href = url;
      enlace.textContent = url;
      enlace.target = '_blank';
      enlace.rel = 'noopener noreferrer nofollow';
      destino.appendChild(enlace);
      if (resto) destino.appendChild(document.createTextNode(resto));
      inicio = coincidencia.index + coincidencia[0].length;
    }
    destino.appendChild(document.createTextNode(contenido.slice(inicio)));
  }

  function agregarMensaje(mensaje) {
    if (!lista || !mensaje || lista.querySelector('[data-id="' + mensaje.id + '"]')) return;

    var vacio = document.getElementById('chatVacio');
    if (vacio) vacio.remove();

    var item = document.createElement('div');
    item.className = 'chat-msg' + (mensaje.propio ? ' chat-msg-propio' : '');
    item.setAttribute('data-id', mensaje.id);

    var burbuja = document.createElement('div');
    burbuja.className = 'chat-burbuja chat-texto';
    // Respeta los saltos de línea del mensaje
    mensaje.contenido.split('\n').forEach(function (linea, indice) {
      if (indice > 0) burbuja.appendChild(document.createElement('br'));
      agregarTextoConLinks(burbuja, linea);
    });

    var meta = document.createElement('div');
    meta.className = 'chat-meta';
    meta.textContent = mensaje.fecha;

    item.appendChild(burbuja);
    item.appendChild(meta);
    lista.appendChild(item);
    ultimoId = Math.max(ultimoId, mensaje.id);
    irAlFinal();
  }

  function mostrarError(mensaje) {
    if (error) error.textContent = mensaje || '';
  }

  function leerJson(respuesta) {
    return respuesta.json()
      .catch(function () {
        throw new Error('Tu sesión expiró. Volvé a iniciar sesión.');
      })
      .then(function (datos) {
        if (!respuesta.ok) throw new Error(datos.error || 'Ocurrió un error.');
        return datos;
      });
  }

  function consultarNuevos() {
    if (consultando || document.visibilityState !== 'visible') return;
    consultando = true;

    fetch('index.php?r=mensajes-tutoria&id=' + encodeURIComponent(idSolicitud) + '&desde=' + ultimoId, {
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(leerJson)
      .then(function (datos) {
        (datos.mensajes || []).forEach(agregarMensaje);
        // Si la otra parte finalizó o canceló, se recarga para mostrar el nuevo estado
        if (datos.estado !== 'aceptada') window.location.reload();
      })
      .catch(function () { /* se reintenta en la próxima consulta */ })
      .then(function () { consultando = false; });
  }

  irAlFinal();

  if (!habilitado || !form) return;

  function actualizarContador() {
    contador.textContent = texto.value.length;
  }

  texto.addEventListener('input', function () {
    actualizarContador();
    mostrarError('');
  });

  // Enter envía; Shift+Enter agrega un salto de línea
  texto.addEventListener('keydown', function (event) {
    if (event.key === 'Enter' && !event.shiftKey) {
      event.preventDefault();
      if (form.requestSubmit) {
        form.requestSubmit();
      } else {
        form.dispatchEvent(new Event('submit', { cancelable: true }));
      }
    }
  });

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    if (texto.value.trim() === '') {
      mostrarError('Escribí un mensaje antes de enviarlo.');
      return;
    }

    btnEnviar.disabled = true;
    fetch(form.action, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: new FormData(form)
    })
      .then(leerJson)
      .then(function (datos) {
        agregarMensaje(datos.mensaje);
        texto.value = '';
        actualizarContador();
        mostrarError('');
      })
      .catch(function (e) { mostrarError(e.message); })
      .then(function () {
        btnEnviar.disabled = false;
        texto.focus();
      });
  });

  window.setInterval(consultarNuevos, INTERVALO_MS);
  document.addEventListener('visibilitychange', function () {
    if (document.visibilityState === 'visible') consultarNuevos();
  });
})();
