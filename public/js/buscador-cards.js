/**
 * STUDY MATCH • Buscador compacto (partial buscador-compacto.php)
 *
 * Filtra en el navegador las cards ".buscador-item" ya renderizadas por PHP.
 * Cada card declara:
 *   data-busqueda  texto en el que se busca
 *   data-materias  ids de materia separados por coma
 *   data-orden     posición por fecha (0 = la más reciente)
 *
 * Sin filtros se muestran "data-pagina" cards y el botón #buscadorVerMas suma
 * otra tanda; con un filtro activo se muestran todas las coincidencias.
 */
(function () {
  'use strict';

  var barra = document.querySelector('[data-buscador]');
  if (!barra) return;

  var grid = document.getElementById(barra.getAttribute('data-grid'));
  // Sin resultados del servidor no hay nada que filtrar
  if (!grid) return;

  var input = document.getElementById('buscadorTexto');
  var selectMateria = document.getElementById('buscadorMateria');
  var selectOrden = document.getElementById('buscadorOrden');
  var total = document.getElementById('buscadorTotal');
  var btnLimpiar = document.getElementById('buscadorLimpiar');
  var sinResultados = document.getElementById('buscadorSinResultados');
  var btnRestablecer = document.getElementById('buscadorRestablecer');
  var btnVerMas = document.getElementById('buscadorVerMas');

  var pagina = parseInt(barra.getAttribute('data-pagina'), 10) || 0;
  var visiblesSinFiltro = pagina;
  var cards = Array.prototype.slice.call(grid.querySelectorAll('.buscador-item'));

  // Minúsculas y sin tildes, para que "matematica" encuentre "Matemática"
  function normalizar(texto) {
    return texto.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').trim();
  }

  cards.forEach(function (card) {
    card.textoBusqueda = normalizar(card.getAttribute('data-busqueda') || '');
  });

  function aplicar() {
    var palabras = normalizar(input.value).split(/\s+/).filter(Boolean);
    var materia = selectMateria.value;
    var hayFiltro = palabras.length > 0 || materia !== 'todas';
    var coincidencias = 0;
    var mostradas = 0;

    cards.slice().sort(function (a, b) {
      var diferencia = a.getAttribute('data-orden') - b.getAttribute('data-orden');
      return selectOrden.value === 'antiguo' ? -diferencia : diferencia;
    }).forEach(function (card) {
      grid.appendChild(card);

      var coincide = palabras.every(function (palabra) {
        return card.textoBusqueda.indexOf(palabra) !== -1;
      }) && (materia === 'todas'
        || card.getAttribute('data-materias').split(',').indexOf(materia) !== -1);

      var visible = coincide && (hayFiltro || !pagina || mostradas < visiblesSinFiltro);
      card.classList.toggle('d-none', !visible);
      if (coincide) coincidencias++;
      if (visible) mostradas++;
    });

    total.textContent = coincidencias;
    if (sinResultados) sinResultados.classList.toggle('d-none', coincidencias !== 0);
    if (btnVerMas) btnVerMas.classList.toggle('d-none', mostradas >= coincidencias);
  }

  // Búsqueda automática: al escribir (con debounce) y al cambiar filtros
  var temporizador = null;
  input.addEventListener('input', function () {
    clearTimeout(temporizador);
    temporizador = setTimeout(aplicar, 250);
  });
  input.addEventListener('keydown', function (event) {
    if (event.key === 'Enter') {
      clearTimeout(temporizador);
      aplicar();
    }
  });
  selectMateria.addEventListener('change', aplicar);
  selectOrden.addEventListener('change', aplicar);

  function limpiar() {
    input.value = '';
    selectMateria.value = 'todas';
    selectOrden.value = 'reciente';
    visiblesSinFiltro = pagina;
    aplicar();
    input.focus();
  }

  btnLimpiar.addEventListener('click', limpiar);
  if (btnRestablecer) btnRestablecer.addEventListener('click', limpiar);

  if (btnVerMas) {
    btnVerMas.addEventListener('click', function () {
      visiblesSinFiltro += pagina;
      aplicar();
    });
  }

  aplicar();
})();
