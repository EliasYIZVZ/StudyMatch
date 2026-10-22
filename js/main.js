/**
 * STUDY MATCH • JavaScript Tradicional (Vanilla JS)
 * Gestión de Modo Oscuro / Modo Claro y Persistencia en LocalStorage
 */

(function () {
  'use strict';

  var THEME_KEY = 'study_match_theme';

  // 1. Obtener tema guardado o predeterminar oscuro
  function getPreferredTheme() {
    var saved = localStorage.getItem(THEME_KEY);
    if (saved === 'light' || saved === 'dark') {
      return saved;
    }
    return 'dark'; // Modo oscuro por defecto
  }

  // 2. Aplicar tema al elemento raíz <html>
  function applyTheme(theme) {
    document.documentElement.setAttribute('data-bs-theme', theme);
    updateThemeIcons(theme);
  }

  // 3. Actualizar íconos del botón en el Navbar
  function updateThemeIcons(theme) {
    var toggleButtons = document.querySelectorAll('.theme-toggle-btn');
    toggleButtons.forEach(function (btn) {
      var icon = btn.querySelector('.theme-icon') || btn.querySelector('i');
      if (!icon) return;

      if (theme === 'dark') {
        icon.className = 'theme-icon bi bi-sun-fill text-warning';
        btn.setAttribute('title', 'Cambiar a modo claro');
        btn.setAttribute('aria-label', 'Cambiar a modo claro');
      } else {
        icon.className = 'theme-icon bi bi-moon-stars-fill text-primary';
        btn.setAttribute('title', 'Cambiar a modo oscuro');
        btn.setAttribute('aria-label', 'Cambiar a modo oscuro');
      }
    });
  }

  // 4. Alternar entre modos claro y oscuro
  function toggleTheme() {
    var currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'dark';
    var newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    localStorage.setItem(THEME_KEY, newTheme);
    applyTheme(newTheme);
  }

  // Ejecución inmediata para sincronizar estado
  var activeTheme = getPreferredTheme();
  applyTheme(activeTheme);

  // Vincular eventos cuando el DOM esté listo
  document.addEventListener('DOMContentLoaded', function () {
    updateThemeIcons(document.documentElement.getAttribute('data-bs-theme') || 'dark');

    document.addEventListener('click', function (e) {
      var btn = e.target.closest('.theme-toggle-btn');
      if (btn) {
        e.preventDefault();
        toggleTheme();
      }
    });

    // Notificaciones de estado desde la URL (por ej. tras registro exitoso)
    var urlParams = new URLSearchParams(window.location.search);
    var feedback = document.getElementById('loginFeedback');
    if (feedback && urlParams.get('registro') === 'exitoso') {
      feedback.innerHTML =
        '<div class="alert alert-success alert-dismissible fade show small py-2 px-3 mb-3 d-flex align-items-center gap-2" role="alert">' +
        '  <i class="bi bi-check-circle-fill flex-shrink-0"></i>' +
        '  <div><strong>¡Registro exitoso!</strong> Ya podés iniciar sesión con tus credenciales.</div>' +
        '  <button type="button" class="btn-close p-2" data-bs-dismiss="alert" aria-label="Cerrar"></button>' +
        '</div>';
    }
  });
})();
