<?php
/**
 * Componente: Selector de Iconos Fontello
 * Desarrollado para GeneraCRUDphp
 */

// Extraer iconos del CSS de Fontello de forma robusta
$iconos = [];
$posibles_css = [
    __DIR__ . '/../iconos-web/css/fontello-codes.css', // Proyecto generado (vistas/../iconos-web)
    __DIR__ . '/../../iconos-web/css/fontello-codes.css', // Generador (accesos/vistas/../../iconos-web)
    dirname(__DIR__, 2) . '/iconos-web/css/fontello-codes.css',
    $_SERVER['DOCUMENT_ROOT'] . dirname($_SERVER['SCRIPT_NAME']) . '/../../iconos-web/css/fontello-codes.css'
];

$css_encontrado = false;
foreach ($posibles_css as $ruta) {
    if (file_exists($ruta)) {
        $content = file_get_contents($ruta);
        if ($content && preg_match_all('/\.icon-([a-z0-9-]+):before/', $content, $matches)) {
            $iconos = array_unique($matches[1]);
            sort($iconos);
            $css_encontrado = true;
            break;
        }
    }
}

if (!$css_encontrado) {
    error_log("IconPicker Error: No se pudo encontrar o leer fontello-codes.css en las rutas intentadas.");
}
?>

<!-- Modal Selector de Iconos -->
<div class="modal fade" id="modalIconPicker" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="fas fa-icons me-2"></i> Seleccionar Icono</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="input-group mb-4 shadow-sm rounded-3 overflow-hidden">
                    <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="searchIcon" class="form-control border-0 py-2 shadow-none" placeholder="Buscar icono por nombre (ej: user, lock, cog)...">
                </div>
                
                <div class="row g-2" id="iconGrid" style="min-height: 300px;">
                    <?php if (!empty($iconos)): ?>
                        <?php foreach ($iconos as $icon): ?>
                            <div class="col-6 col-md-3 col-lg-2 icon-item" data-name="<?= $icon ?>">
                                <div class="card h-100 border-0 bg-light text-center p-3 icon-card cursor-pointer" 
                                     onclick="selectIcon('icon-<?= $icon ?>')"
                                     title="icon-<?= $icon ?>">
                                    <i class="icon-<?= $icon ?> h3 mb-2"></i>
                                    <div class="small text-truncate text-muted" style="font-size: 0.7rem;">icon-<?= $icon ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5">
                            <i class="fas fa-exclamation-triangle h1 text-warning opacity-25"></i>
                            <p class="text-muted mt-3">No se pudieron cargar los iconos desde Fontello.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .icon-card {
        transition: all 0.2s ease;
        border-radius: 12px;
    }
    .icon-card:hover {
        background-color: #e8f0fe !important;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        color: #0d6efd;
    }
    .cursor-pointer {
        cursor: pointer;
    }
    #iconGrid {
        max-height: 50vh;
        overflow-y: auto;
    }
</style>

<script>
    let targetIconInput = null;

    function openIconPicker(targetInputId) {
        targetIconInput = document.getElementById(targetInputId);
        const modal = new bootstrap.Modal(document.getElementById('modalIconPicker'));
        modal.show();
    }

    function selectIcon(iconClass) {
        if (targetIconInput) {
            targetIconInput.value = iconClass;
            // Disparar evento change por si hay previsualización
            targetIconInput.dispatchEvent(new Event('change'));
        }
        const modalElement = document.getElementById('modalIconPicker');
        bootstrap.Modal.getInstance(modalElement).hide();
    }

    document.getElementById('searchIcon').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        document.querySelectorAll('.icon-item').forEach(item => {
            const name = item.getAttribute('data-name').toLowerCase();
            item.style.display = name.includes(term) ? 'block' : 'none';
        });
    });
</script>
