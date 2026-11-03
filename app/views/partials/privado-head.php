<?php defined('APP_PATH') || exit('Acceso denegado.'); ?>
<?php /* <head> común de las páginas del área privada. Espera $tituloPagina. */ ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Study Match - <?= Vista::e($tituloPagina) ?></title>
  <script>
    (function() {
      var theme = localStorage.getItem('study_match_theme');
      document.documentElement.setAttribute('data-bs-theme', theme === 'light' ? 'light' : 'dark');
    })();
  </script>

  <!-- Google Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Bootstrap 5.3 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Estilos del Dashboard -->
  <link rel="stylesheet" href="public/css/dashboard.css?v=20261103_page_title">
</head>
