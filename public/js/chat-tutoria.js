/**
 * STUDY MATCH • Chat de una tutoría (app/views/tutorias/detalle.php)
 *
 * - Envía mensajes con fetch (TutoriaController::enviarMensaje).
 * - Mientras la tutoría está aceptada y la pestaña está visible, busca
 *   mensajes nuevos cada 5 segundos (TutoriaController::mensajes).
 * - Muestra los links http(s) como enlaces. El texto se inserta siempre
 *   como texto plano: nunca se interpreta HTML escrito por los usuarios.
 * - Reporte de mensajes del otro usuario (solo con la tutoría aceptada): una
 *   bandera junto al mensaje. Con mouse aparece tras 2 s de hover (CSS); en
 *   pantallas táctiles, al mantener presionado el mensaje 1 s.
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

  function crearBandera() {
    var boton = document.createElement('button');
    boton.type = 'button';
    boton.className = 'chat-flag';
    boton.title = 'Reportar mensaje';
    boton.setAttribute('aria-label', 'Reportar mensaje');
    var icono = document.createElement('i');
    icono.className = 'bi bi-flag';
    boton.appendChild(icono);
    return boton;
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

    if (!mensaje.propio && habilitado) {
      var fila = document.createElement('div');
      fila.className = 'chat-fila';
      fila.appendChild(burbuja);
      fila.appendChild(crearBandera());
      item.appendChild(fila);
    } else {
      item.appendChild(burbuja);
    }
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

  // ---- Reportar un mensaje del otro usuario ----
  var PRESION_MS = 1000;
  var OCULTAR_BANDERA_MS = 5000;
  var otroNombre = chat.getAttribute('data-otro-nombre') || 'el otro usuario';
  var temporizadorPresion = null;
  var temporizadorOcultar = null;
  var puntoInicial = null;
  var mensajeConBandera = null;

  function ocultarBanderaTactil() {
    clearTimeout(temporizadorOcultar);
    if (mensajeConBandera) {
      mensajeConBandera.classList.remove('chat-msg-bandera');
      mensajeConBandera = null;
    }
  }

  function cancelarPresion() {
    clearTimeout(temporizadorPresion);
    temporizadorPresion = null;
  }

  if (lista && habilitado) {
    // Clic en la bandera: abre el modal de reporte (partials/modal-reportar.php)
    lista.addEventListener('click', function (event) {
      var bandera = event.target.closest('.chat-flag');
      if (!bandera || typeof window.abrirModalReporte !== 'function') return;

      var item = bandera.closest('.chat-msg');
      var fragmento = item.querySelector('.chat-burbuja').textContent.trim();
      if (fragmento.length > 60) fragmento = fragmento.slice(0, 60) + '…';

      ocultarBanderaTactil();
      window.abrirModalReporte('mensaje', item.getAttribute('data-id'), otroNombre + ': “' + fragmento + '”');
    });

    // Pantallas táctiles: mantener presionado 1 s el mensaje muestra la bandera
    lista.addEventListener('pointerdown', function (event) {
      if (event.pointerType === 'mouse' || event.target.closest('.chat-flag')) return;

      var item = event.target.closest('.chat-msg');
      if (!item || !item.querySelector('.chat-flag')) return;

      puntoInicial = { x: event.clientX, y: event.clientY };
      cancelarPresion();
      temporizadorPresion = setTimeout(function () {
        ocultarBanderaTactil();
        item.classList.add('chat-msg-bandera');
        mensajeConBandera = item;
        temporizadorOcultar = setTimeout(ocultarBanderaTactil, OCULTAR_BANDERA_MS);
      }, PRESION_MS);
    });

    lista.addEventListener('pointermove', function (event) {
      if (temporizadorPresion && puntoInicial
          && Math.hypot(event.clientX - puntoInicial.x, event.clientY - puntoInicial.y) > 10) {
        cancelarPresion();
      }
    });
    lista.addEventListener('pointerup', cancelarPresion);
    lista.addEventListener('pointercancel', cancelarPresion);
    lista.addEventListener('scroll', cancelarPresion);

    // En el celular, el menú contextual del navegador no debe tapar a la bandera
    lista.addEventListener('contextmenu', function (event) {
      if (window.matchMedia('(hover: none)').matches && event.target.closest('.chat-fila')) {
        event.preventDefault();
      }
    });

    // Tocar en cualquier otro lado la oculta
    document.addEventListener('pointerdown', function (event) {
      if (mensajeConBandera && !event.target.closest('.chat-msg-bandera')) ocultarBanderaTactil();
    });
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
