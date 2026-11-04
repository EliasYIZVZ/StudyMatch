<?php defined('APP_PATH') || exit('Acceso denegado.'); ?>
<?php
/**
 * Card de Buscar Tutor: perfil resumido de un tutor (no de una tutoría).
 * Espera $tutor (armado en DashboardController::buscarTutor) y $posicion
 * (0 = tutor con la publicación más reciente).
 */
$anios = [
    '1ro' => '1° año', '2do' => '2° año', '3ro' => '3° año',
    '4to' => '4° año', '5to' => '5° año', 'graduado' => 'Graduado/a',
];
$perfilTutor = [];
if (!empty($tutor['carrera'])) {
    $perfilTutor[] = $tutor['carrera'];
}
if (!empty($tutor['anio']) && isset($anios[$tutor['anio']])) {
    $perfilTutor[] = $anios[$tutor['anio']];
}

$modalidades = array_keys($tutor['modalidades']);
$cantidadMaterias = count($tutor['materias']);
$tutoriasDadas = $tutor['tutorias_dadas'];

// Precio: "Gratuita" si todas lo son; si no, el menor precio pago ("Desde ...")
$preciosPagos = array_filter($tutor['precios'], function ($precio) {
    return $precio > 0;
});
$tieneGratuitas = count($preciosPagos) < count($tutor['precios']);
$precioMinimo = empty($preciosPagos) ? 0 : min($preciosPagos);
$montoTexto = '$ ' . number_format($precioMinimo, fmod($precioMinimo, 1) == 0 ? 0 : 2, ',', '.');

// Texto en el que busca el filtro del navegador
$textoBusqueda = mb_strtolower(implode(' ', array_merge(
    [$tutor['nombre'], $tutor['carrera']],
    array_values($tutor['materias']),
    array_map(['Vista', 'modalidad'], $modalidades)
)), 'UTF-8');
?>
<div class="col-md-6 col-xl-4 buscador-item"
     data-materias="<?= Vista::e(implode(',', array_keys($tutor['materias']))) ?>"
     data-orden="<?= (int) $posicion ?>"
     data-busqueda="<?= Vista::e($textoBusqueda) ?>">
  <article class="tutor-result-card p-4 h-100 d-flex flex-column justify-content-between">
    <div>
      <!-- Tutor: avatar, nombre, carrera y año -->
      <div class="d-flex align-items-center gap-3 mb-3">
        <?= Vista::avatar($tutor['nombre'], $tutor['foto']) ?>
        <div class="min-w-0">
          <h3 class="h6 fw-bold mb-0 text-dark text-truncate"><?= Vista::e($tutor['nombre']) ?></h3>
          <?php if (!empty($perfilTutor)): ?>
            <small class="text-secondary d-block"><?= Vista::e(implode(' • ', $perfilTutor)) ?></small>
          <?php endif; ?>
        </div>
      </div>

      <!-- Materias que enseña -->
      <p class="text-secondary small fw-semibold mb-2">
        <?= $cantidadMaterias === 1 ? 'Enseña 1 materia' : 'Enseña ' . $cantidadMaterias . ' materias' ?>
      </p>
      <div class="d-flex flex-wrap gap-1 mb-3">
        <?php foreach ($tutor['materias'] as $nombreMateria): ?>
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill fw-semibold text-wrap text-start"><?= Vista::e($nombreMateria) ?></span>
        <?php endforeach; ?>
      </div>

      <!-- Modalidades en las que da clases -->
      <div class="d-flex flex-wrap gap-1 mb-3">
        <?php foreach ($modalidades as $modalidad): ?>
          <span class="badge bg-light text-secondary border">
            <i class="bi <?= Vista::e(Vista::iconoModalidad($modalidad)) ?> me-1"></i><?= Vista::e(Vista::modalidad($modalidad)) ?>
          </span>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Experiencia y precio -->
    <div class="pt-3 border-top">
      <div class="d-flex justify-content-between align-items-center text-secondary small gap-2">
        <span>
          <i class="bi bi-mortarboard-fill text-primary me-1"></i>
          <?php if ($tutoriasDadas > 0): ?>
            <strong><?= $tutoriasDadas ?></strong> <?= $tutoriasDadas === 1 ? 'tutoría dada' : 'tutorías dadas' ?>
          <?php else: ?>
            Tutor/a nuevo/a
          <?php endif; ?>
        </span>
        <?php if (empty($preciosPagos)): ?>
          <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill"><i class="bi bi-gift me-1"></i>Gratuita</span>
        <?php else: ?>
          <span class="text-nowrap" title="Precio por clase<?= $tieneGratuitas ? ' (también tiene tutorías gratuitas)' : '' ?>">
            <?= $tieneGratuitas ? 'Gratis o desde' : 'Desde' ?> <strong class="text-dark"><?= Vista::e($montoTexto) ?></strong>
          </span>
        <?php endif; ?>
      </div>

      <?php if (!empty($tutor['id_usuario'])): ?>
        <div class="text-center mt-2">
          <button type="button"
                  class="btn btn-link text-muted p-0 text-decoration-none small d-inline-flex align-items-center gap-1"
                  style="font-size: 0.72rem;"
                  onclick="abrirModalReporte('tutor', <?= (int) $tutor['id_usuario'] ?>, '<?= Vista::e(addslashes($tutor['nombre'])) ?>')">
            <i class="bi bi-flag"></i> Reportar perfil
          </button>
        </div>
      <?php endif; ?>
    </div>
  </article>
</div>
