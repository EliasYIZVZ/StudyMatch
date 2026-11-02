/**
 * STUDY MATCH • Menú lateral (sidebar) del área privada
 *
 * - Escritorio (>= 992px): el botón contrae / expande el sidebar y el estado
 *   se recuerda en localStorage.
 * - Celular y tablet (< 992px): el sidebar es un menú desplegable que se abre
 *   por encima del contenido, con fondo oscuro. Se cierra con el fondo, la
 *   tecla Esc, el botón del sidebar o al elegir una opción.
 */
(function () {
  'use strict';

  var STORAGE_KEY = 'study-match-sidebar-collapsed';
  var mobileQuery = window.matchMedia('(max-width: 991.98px)');

  var sidebar = document.getElementById('mainSidebar');
  if (!sidebar) return;

  var btnToggle = document.getElementById('btnToggleSidebar');
  var btnCollapse = document.getElementById('btnCollapseSidebar');

  var backdrop = document.createElement('div');
  backdrop.className = 'sidebar-backdrop';
  backdrop.setAttribute('aria-hidden', 'true');
  sidebar.insertAdjacentElement('afterend', backdrop);

  if (btnToggle) {
    btnToggle.setAttribute('aria-controls', 'mainSidebar');
    btnToggle.setAttribute('aria-expanded', 'false');
  }

  function readCollapsed() {
    try {
      return localStorage.getItem(STORAGE_KEY) === 'true';
    } catch (e) {
      return false;
    }
  }

  function saveCollapsed(value) {
    try {
      localStorage.setItem(STORAGE_KEY, value ? 'true' : 'false');
    } catch (e) { /* almacenamiento no disponible */ }
  }

  function isMobile() {
    return mobileQuery.matches;
  }

  function isMobileOpen() {
    return sidebar.classList.contains('mobile-open');
  }

  // ---- Escritorio: contraer / expandir ----
  function setCollapsed(collapsed) {
    sidebar.classList.toggle('collapsed', collapsed);
    document.body.classList.toggle('sidebar-is-collapsed', collapsed);
  }

  // ---- Celular: abrir / cerrar el menú desplegable ----
  function openMobile() {
    sidebar.classList.add('mobile-open');
    backdrop.classList.add('show');
    document.body.classList.add('sidebar-mobile-open');
    if (btnToggle) btnToggle.setAttribute('aria-expanded', 'true');

    var first = sidebar.querySelector('a[href], button');
    if (first) first.focus({ preventScroll: true });
  }

  function closeMobile(returnFocus) {
    if (!isMobileOpen()) return;

    sidebar.classList.remove('mobile-open');
    backdrop.classList.remove('show');
    document.body.classList.remove('sidebar-mobile-open');
    if (btnToggle) btnToggle.setAttribute('aria-expanded', 'false');

    if (returnFocus && btnToggle) btnToggle.focus({ preventScroll: true });
  }

  // El estado "contraído" solo existe en escritorio.
  function syncLayout() {
    if (isMobile()) {
      setCollapsed(false);
    } else {
      closeMobile(false);
      setCollapsed(readCollapsed());
    }
  }

  if (btnToggle) {
    btnToggle.addEventListener('click', function () {
      if (isMobile()) {
        if (isMobileOpen()) {
          closeMobile(true);
        } else {
          openMobile();
        }
        return;
      }

      var collapsed = !sidebar.classList.contains('collapsed');
      setCollapsed(collapsed);
      saveCollapsed(collapsed);
    });
  }

  if (btnCollapse) {
    btnCollapse.addEventListener('click', function () {
      if (isMobile()) {
        closeMobile(true);
        return;
      }

      setCollapsed(true);
      saveCollapsed(true);
    });
  }

  backdrop.addEventListener('click', function () {
    closeMobile(true);
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && isMobileOpen()) {
      closeMobile(true);
    }
  });

  // Al elegir una opción del menú (o abrir el perfil) se cierra el desplegable.
  sidebar.addEventListener('click', function (event) {
    if (isMobile() && event.target.closest('a[href], [data-bs-toggle="modal"]')) {
      closeMobile(false);
    }
  });

  if (mobileQuery.addEventListener) {
    mobileQuery.addEventListener('change', syncLayout);
  } else if (mobileQuery.addListener) {
    mobileQuery.addListener(syncLayout);
  }

  syncLayout();
})();
