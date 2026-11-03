<?php defined('APP_PATH') || exit('Acceso denegado.'); ?>
<?php
/**
 * Barra de búsqueda compacta (Buscar Tutor y dashboard del alumno).
 * Los resultados se actualizan al escribir o al cambiar un filtro: lógica en
 * public/js/buscador-cards.js, que filtra las cards ".buscador-item" de la grilla.
 *
 * Espera $buscador con:
 *   grid        id de la grilla de cards a filtrar
 *   pagina      cards visibles sin filtros (0 = todas; el resto con "Ver más")
 *   placeholder texto de ayuda del campo
 *   materias    [id_materia => nombre] para el filtro
 *   total       cantidad de cards
 *   unidad      lo que se cuenta ("tutores", "tutorías")
 */
?>
      <div class="search-hybrid-card p-2 mb-3" data-buscador
           data-grid="<?= Vista::e($buscador['grid']) ?>"
           data-pagina="<?= (int) $buscador['pagina'] ?>">
        <div class="row g-2 align-items-center">
          <div class="col-12 col-xl">
            <div class="input-group search-hybrid-input rounded-3 overflow-hidden border">
              <span class="input-group-text bg-white border-0 text-primary ps-3 pe-2">
                <i class="bi bi-search"></i>
              </span>
              <input type="search" id="buscadorTexto" class="form-control border-0 ps-1"
                     placeholder="<?= Vista::e($buscador['placeholder']) ?>"
                     aria-label="<?= Vista::e($buscador['placeholder']) ?>"
                     autocomplete="off">
            </div>
          </div>
          <div class="col-6 col-sm col-xl-auto">
            <select id="buscadorMateria" class="form-select" aria-label="Filtrar por materia">
              <option value="todas">Todas las materias</option>
              <?php foreach ($buscador['materias'] as $idMateria => $nombreMateria): ?>
                <option value="<?= (int) $idMateria ?>"><?= Vista::e($nombreMateria) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-6 col-sm col-xl-auto">
            <select id="buscadorOrden" class="form-select" aria-label="Ordenar resultados">
              <option value="reciente">Más reciente</option>
              <option value="antiguo">Más antiguo</option>
            </select>
          </div>
          <div class="col-12 col-sm-auto d-flex align-items-center justify-content-between gap-2">
            <span class="search-hybrid-count small text-secondary text-nowrap" aria-live="polite">
              <strong id="buscadorTotal"><?= (int) $buscador['total'] ?></strong> <?= Vista::e($buscador['unidad']) ?>
            </span>
            <button type="button" id="buscadorLimpiar" class="btn btn-outline-secondary search-hybrid-reset"
                    title="Limpiar filtros" aria-label="Limpiar filtros">
              <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
            </button>
          </div>
        </div>
      </div>
