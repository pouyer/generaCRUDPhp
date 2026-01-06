<?php
/**
 * GeneraCRUDphp
 *
 * Desarrollado por Carlos Mejia
 * 2024-12-31
 */
require_once '../verificar_sesion.php';

// Buscar conexion.php
$rutaConexion = '';
$directorios = ['../../conexion.php', '../conexion.php', './conexion.php'];
foreach ($directorios as $dir) {
    if (file_exists($dir)) {
        $rutaConexion = $dir;
        break;
    }
}
if ($rutaConexion) {
    require_once $rutaConexion;
}

require_once '../controladores/controller_roles_programas.php';

$controller = new ControllerRolesProgramas($conexion);
$id_rol = isset($_GET['id_rol']) ? (int)$_GET['id_rol'] : null;
$roles = $controller->obtenerRoles();
$programas_no_asignados = $id_rol ? $controller->obtenerProgramasNoAsignados($id_rol) : [];
$programas_asignados = $id_rol ? $controller->obtenerProgramasAsignados($id_rol) : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permisos por Rol - Administrar</title>
    <?php include('../headIconos.php'); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        }
        body { background-color: #f8f9fa; font-family: 'Outfit', sans-serif; }
        .assignment-container { height: calc(100vh - 200px); min-height: 500px; }
        .list-group-scroll { overflow-y: auto; border-radius: 12px; border: 1px solid #e0e0e0; background: white; }
        .list-group-item { cursor: pointer; border-left: 0; border-right: 0; padding: 12px 20px; transition: all 0.2s ease; border-bottom: 1px solid #f1f1f1; }
        .list-group-item:hover { background-color: #f8f9ff; color: #2a5298; }
        .list-group-item.active { background-color: #e8f0fe !important; color: #1e3c72 !important; border-color: transparent; font-weight: 600; }
        .list-group-item.selected { background-color: #fff9e6; border-left: 4px solid #ffc107; }
        .icon-box { min-width: 30px; display: inline-block; text-align: center; margin-right: 10px; color: #1e3c72; }
        .perm-toggle { width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 0.8rem; border: 2px solid transparent; transition: all 0.2s ease; }
        .perm-toggle.active { opacity: 1; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transform: scale(1.05); }
        .perm-toggle:not(.active) { opacity: 0.2; filter: grayscale(1); background-color: #eee !important; border-color: #ddd !important; color: #999 !important; }
        .transfer-btns { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 15px; }
        .column-header-box { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .hidden-filter { display: none !important; }
    </style>
</head>
<body>
    <div class="container-fluid py-4 px-lg-5">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-4 shadow-sm">
                    <div>
                        <h1 class="h4 fw-bold mb-0">Asignación de Permisos</h1>
                        <p class="text-muted small mb-0">Asocia programas a los diferentes roles del sistema</p>
                    </div>
                    <div>
                        <button id="btnGuardar" class="btn btn-primary d-flex align-items-center gap-2 px-4 py-2 rounded-3 shadow-sm" <?= !$id_rol ? 'disabled' : '' ?>>
                            <i class="fas fa-save me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-custom overflow-hidden">
            <div class="row g-0">
                <!-- Sidebar Roles -->
                <div class="col-md-3 bg-white role-sidebar">
                    <div class="p-4">
                        <h3 class="column-header"><i class="fa-solid fa-users-gear me-2"></i> Seleccionar Rol</h3>
                        <div class="list-group list-group-flush list-group-scroll" style="height: 400px; border: none;">
                            <?php foreach ($roles as $rol): ?>
                                <a href="?id_rol=<?= $rol['id_rol'] ?>" class="list-group-item list-group-item-action <?= $id_rol == $rol['id_rol'] ? 'active' : '' ?>">
                                    <i class="fas fa-chevron-right small me-2"></i> <?= htmlspecialchars($rol['nombre_rol']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Program Assignment -->
                <div class="col-md-9 bg-light p-4">
                    <?php if (!$id_rol): ?>
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 py-5">
                            <i class="icon-attention h1 text-muted opacity-25"></i>
                            <p class="text-muted mt-3">Por favor, selecciona un rol de la lista para gestionar sus programas.</p>
                        </div>
                    <?php else: ?>
                        <div class="row assignment-container g-3">
                            <div class="col-md-5 d-flex flex-column h-100">
                                <h3 class="column-header text-muted">Programas Disponibles</h3>
                                <div class="mb-2">
                                    <input type="text" id="search-disponibles" class="form-control form-control-sm" placeholder="🔍 Filtrar disponibles...">
                                </div>
                                <div class="list-group list-group-scroll flex-grow-1" id="list-disponibles">
                                    <?php foreach ($programas_no_asignados as $prog): ?>
                                        <div class="list-group-item list-group-item-action d-flex align-items-center" data-id="<?= $prog['id_programas'] ?>">
                                            <span class="icon-box"><i class="<?= $prog['icono'] ?: 'icon-dot-circled' ?>"></i></span>
                                            <span class="fw-medium"><?= htmlspecialchars($prog['nombre_menu']) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-2 transfer-btns">
                                 <button id="toRight" class="btn btn-white shadow-sm border border-2 border-primary-subtle text-primary py-3 rounded-4" title="Asignar seleccionados">
                                     <i class="icon-angle-right h4 mb-0 d-none d-md-block"></i>
                                     <i class="icon-angle-down h4 mb-0 d-md-none"></i>
                                 </button>
                                 <button id="toLeft" class="btn btn-white shadow-sm border border-2 border-primary-subtle text-primary py-3 rounded-4" title="Quitar seleccionados">
                                     <i class="icon-angle-left h4 mb-0 d-none d-md-block"></i>
                                     <i class="icon-angle-up h4 mb-0 d-md-none"></i>
                                 </button>
                            </div>

                            <div class="col-md-5 d-flex flex-column h-100">
                                <div class="column-header-box">
                                    <h3 class="column-header text-primary mb-0">Programas Asignados</h3>
                                    <div class="d-flex gap-2">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle border-0" type="button" data-bs-toggle="dropdown">
                                                <i class="icon-cog"></i> Masivo
                                            </button>
                                            <ul class="dropdown-menu shadow">
                                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="bulkPerm('ins', 1)">Activar Crear</a></li>
                                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="bulkPerm('ins', 0)">Quitar Crear</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="bulkPerm('upd', 1)">Activar Editar</a></li>
                                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="bulkPerm('upd', 0)">Quitar Editar</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="bulkPerm('del', 1)">Activar Eliminar</a></li>
                                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="bulkPerm('del', 0)">Quitar Eliminar</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="bulkPerm('exp', 1)">Activar Exportar</a></li>
                                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="bulkPerm('exp', 0)">Quitar Exportar</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <input type="text" id="search-asignados" class="form-control form-control-sm" placeholder="🔍 Buscar programas asignados...">
                                </div>
                                <div class="list-group list-group-scroll flex-grow-1 border-primary-subtle" id="list-asignados">
                                    <?php foreach ($programas_asignados as $prog): ?>
                                        <div class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-2" data-id="<?= $prog['id_programas'] ?>">
                                            <div class="d-flex align-items-center">
                                                <span class="icon-box"><i class="<?= $prog['icono'] ?: 'icon-dot-circled' ?>"></i></span>
                                                <span class="fw-medium"><?= htmlspecialchars($prog['nombre_menu']) ?></span>
                                            </div>
                                            <div class="permission-toggles d-flex gap-1">
                                                <button type="button" class="btn btn-sm perm-toggle btn-success <?= $prog['permiso_insertar'] ? 'active' : '' ?>" data-perm="ins" title="Crear"><i class="icon-plus"></i></button>
                                                <button type="button" class="btn btn-sm perm-toggle btn-warning <?= $prog['permiso_actualizar'] ? 'active' : '' ?>" data-perm="upd" title="Editar"><i class="icon-edit"></i></button>
                                                <button type="button" class="btn btn-sm perm-toggle btn-danger <?= $prog['permiso_eliminar'] ? 'active' : '' ?>" data-perm="del" title="Eliminar"><i class="icon-trash-2"></i></button>
                                                <button type="button" class="btn btn-sm perm-toggle btn-info <?= $prog['permiso_exportar'] ? 'active' : '' ?>" data-perm="exp" title="Exportar"><i class="icon-export"></i></button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="liveToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage">Cambios guardados con éxito.</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Click to select row (only if not clicking a toggle)
            $(document).on('click', '.list-group-item', function(e) {
                if ($(e.target).closest('.perm-toggle').length) return;
                $(this).toggleClass('selected active');
            });

            // Delegación de eventos para los botones de permisos (ahora sin stopPropagation en el HTML)
            $(document).on('click', '.perm-toggle', function(e) {
                e.preventDefault();
                $(this).toggleClass('active');
            });

            // Función para cambios masivos
            window.bulkPerm = function(perm, valor) {
                $('#list-asignados .perm-toggle[data-perm="' + perm + '"]').each(function() {
                    if (valor) $(this).addClass('active');
                    else $(this).removeClass('active');
                });
            };

            // Move Right
            $('#toRight').click(function() {
                $('#list-disponibles .selected').each(function() {
                    const id = $(this).data('id');
                    const name = $(this).find('.fw-medium').text();
                    const icon = $(this).find('.icon-box i').attr('class') || 'icon-dot-circled';
                    
                    const newHtml = `
                        <div class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-2" data-id="${id}">
                            <div class="d-flex align-items-center">
                                <span class="icon-box"><i class="${icon}"></i></span>
                                <span class="fw-medium">${name}</span>
                            </div>
                            <div class="permission-toggles d-flex gap-1">
                                <button type="button" class="btn btn-sm perm-toggle btn-success active" data-perm="ins" title="Crear"><i class="icon-plus"></i></button>
                                <button type="button" class="btn btn-sm perm-toggle btn-warning active" data-perm="upd" title="Editar"><i class="icon-edit"></i></button>
                                <button type="button" class="btn btn-sm perm-toggle btn-danger active" data-perm="del" title="Eliminar"><i class="icon-trash-2"></i></button>
                                <button type="button" class="btn btn-sm perm-toggle btn-info active" data-perm="exp" title="Exportar"><i class="icon-export"></i></button>
                            </div>
                        </div>
                    `;
                    $(this).remove();
                    $('#list-asignados').append(newHtml);
                });
            });

            // Move Left
            $('#toLeft').click(function() {
                $('#list-asignados .selected').each(function() {
                    const id = $(this).data('id');
                    const name = $(this).find('.fw-medium').text().trim();
                    const icon = $(this).find('.icon-box i').attr('class');
                    
                    const newHtml = `
                        <div class="list-group-item list-group-item-action d-flex align-items-center" data-id="${id}">
                            <span class="icon-box"><i class="${icon}"></i></span>
                            <span class="fw-medium">${name}</span>
                        </div>
                    `;
                    $(this).remove();
                    $('#list-disponibles').append(newHtml);
                });
            });

            // Búsqueda / Filtrado
            $('#search-disponibles').on('keyup input change', function() {
                const value = $(this).val().toLowerCase().trim();
                $("#list-disponibles .list-group-item").each(function() {
                    const text = $(this).find('.fw-medium').text().toLowerCase();
                    if (text.indexOf(value) > -1) {
                        $(this).removeClass('hidden-filter');
                    } else {
                        $(this).addClass('hidden-filter');
                    }
                });
            });

            $('#search-asignados').on('keyup input change', function() {
                const value = $(this).val().toLowerCase().trim();
                $("#list-asignados .list-group-item").each(function() {
                    const text = $(this).find('.fw-medium').text().toLowerCase();
                    if (text.indexOf(value) > -1) {
                        $(this).removeClass('hidden-filter');
                    } else {
                        $(this).addClass('hidden-filter');
                    }
                });
            });

            // Guardar Cambios
            $('#btnGuardar').click(function() {
                const btn = $(this);
                const programas = [];
                $('#list-asignados .list-group-item').each(function() {
                    const id = $(this).data('id');
                    const ins = $(this).find('.perm-toggle[data-perm="ins"]').hasClass('active') ? 1 : 0;
                    const upd = $(this).find('.perm-toggle[data-perm="upd"]').hasClass('active') ? 1 : 0;
                    const del = $(this).find('.perm-toggle[data-perm="del"]').hasClass('active') ? 1 : 0;
                    const exp = $(this).find('.perm-toggle[data-perm="exp"]').hasClass('active') ? 1 : 0;
                    programas.push({ id, ins, upd, del, exp });
                });

                if (!<?= (int)$id_rol ?>) {
                    alert('Debe seleccionar un rol primero');
                    return;
                }

                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Guardando...');

                $.ajax({
                    url: '../controladores/controller_roles_programas.php?action=guardarCambios',
                    type: 'POST',
                    data: {
                        id_rol: <?= (int)$id_rol ?>,
                        programas: programas
                    },
                    dataType: 'json',
                    success: function(res) {
                        const toast = new bootstrap.Toast($('#liveToast'));
                        if(res.status === 'success') {
                            $('#toastMessage').text('Los permisos han sido actualizados correctamente.');
                            $('#liveToast').removeClass('bg-danger').addClass('bg-success');
                        } else {
                            $('#toastMessage').text('Error: ' + res.message);
                            $('#liveToast').removeClass('bg-success').addClass('bg-danger');
                        }
                        toast.show();
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Guardar Cambios');
                    }
                });
            });
        });
    </script>
</body>
</html>
