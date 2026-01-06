<?php
/**
 * GeneraCRUDphp
 *
 * Desarrollado por Carlos Mejia
 * 2024-12-31
 * Version 0.5.0
 * 
 */
require_once '../verificar_sesion.php';
$registrosPorPagina = isset($_GET['registrosPorPagina']) ? (int)$_GET['registrosPorPagina'] : 10;
$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Estados - Administrar</title>
    <?php include('../headIconos.php'); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        body { background-color: #fcfcfc; font-family: 'Inter', sans-serif; }
        .main-card { border: none; border-radius: 20px; box-shadow: 0 15px 35px rgba(245, 87, 108, 0.05); background: white; }
        .btn-gradient-danger { background: var(--primary-gradient); color: white; border: none; border-radius: 12px; transition: all 0.3s ease; }
        .btn-gradient-danger:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(245, 87, 108, 0.3); color: white; }
        .table thead th { border: none; background: #fff5f6; color: #d63384; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; padding: 15px; }
        .table tbody td { padding: 15px; border-bottom: 1px solid #fff0f0; }
        .visible-badge { width: 12px; height: 12px; border-radius: 50%; display: inline-block; margin-right: 5px; }
        .visible-1 { background-color: #28a745; }
        .visible-0 { background-color: #dc3545; }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="row mb-4">
            <div class="col-lg-8">
                <h1 class="display-6 fw-bold text-dark">Estados de Tabla</h1>
                <p class="text-muted">Gestiona los estados lógicos y su visibilidad en los diferentes módulos</p>
            </div>
            <div class="col-lg-4 text-lg-end d-flex align-items-center justify-content-lg-end gap-2">
                <button type="button" class="btn btn-gradient-danger px-4 py-2 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalCrear">
                    <i class="fas fa-plus"></i> Añadir Estado
                </button>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle rounded-3" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-file-export"></i> Exportar
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                        <li><a class="dropdown-item" href="../controladores/controlador_acc_estado.php?action=exportar&formato=excel"><i class="fas fa-file-excel text-success me-2"></i> Excel</a></li>
                        <li><a class="dropdown-item" href="../controladores/controlador_acc_estado.php?action=exportar&formato=csv"><i class="fas fa-file-csv text-info me-2"></i> CSV</a></li>
                        <li><a class="dropdown-item" href="../controladores/controlador_acc_estado.php?action=exportar&formato=txt"><i class="fas fa-file-alt text-secondary me-2"></i> TXT</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="main-card overflow-hidden">
            <div class="p-4 border-bottom">
                <form method="GET" action="../controladores/controlador_acc_estado.php">
                    <input type="hidden" name="action" value="buscar">
                    <div class="input-group bg-light rounded-4 overflow-hidden p-1 border">
                        <span class="input-group-text bg-transparent border-0"><i class="fas fa-search"></i></span>
                        <input type="text" name="busqueda" class="form-control bg-transparent border-0 shadow-none px-3" placeholder="Filtrar por tabla o nombre de estado..." value="<?php echo htmlspecialchars($_GET['busqueda'] ?? ''); ?>">
                        <button type="submit" class="btn btn-dark rounded-3 px-4">Filtrar</button>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Tabla</th>
                            <th>Código</th>
                            <th>Etiqueta</th>
                            <th class="text-center">Visibilidad</th>
                            <th class="text-center">Orden</th>
                            <th class="text-center pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        require_once '../modelos/modelo_acc_estado.php';
                        $modelo = new ModeloAcc_estado();
                        $termino = $_GET['busqueda'] ?? '';
                        $offset = ($paginaActual - 1) * $registrosPorPagina;

                        if (isset($_GET['action']) && $_GET['action'] === 'buscar') {
                            $totalRegistros = $modelo->contarRegistrosPorBusqueda($termino);
                            $registros = $modelo->buscar($termino, $registrosPorPagina, $offset);
                        } else {
                            $totalRegistros = $modelo->contarRegistros();
                            $registros = $modelo->obtenerTodos($registrosPorPagina, $offset);
                        }

                        if ($registros):
                            foreach ($registros as $reg):
                        ?>
                        <tr>
                            <td class="ps-4 text-muted small"><?php echo htmlspecialchars($reg['id_estado']); ?></td>
                            <td><span class="badge bg-light text-dark border fw-medium"><?php echo htmlspecialchars($reg['tabla']); ?></span></td>
                            <td class="fw-bold text-primary"><?php echo htmlspecialchars($reg['estado']); ?></td>
                            <td><?php echo htmlspecialchars($reg['nombre_estado']); ?></td>
                            <td class="text-center">
                                <span class="visible-badge visible-<?php echo $reg['visible']; ?>"></span>
                                <span class="small"><?php echo ($reg['visible'] ? 'Público' : 'Oculto'); ?></span>
                            </td>
                            <td class="text-center"><span class="text-muted"><?php echo htmlspecialchars($reg['orden']); ?></span></td>
                            <td class="text-center pe-4">
                                <div class="btn-group shadow-sm border rounded-3 overflow-hidden">
                                    <button class="btn btn-white btn-sm px-3 border-end" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalActualizar"
                                            data-id_estado="<?= $reg['id_estado'] ?>"
                                            data-tabla="<?= htmlspecialchars($reg['tabla']) ?>"
                                            data-estado="<?= htmlspecialchars($reg['estado']) ?>"
                                            data-nombre_estado="<?= htmlspecialchars($reg['nombre_estado']) ?>"
                                            data-visible="<?= $reg['visible'] ?>"
                                            data-orden="<?= $reg['orden'] ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-white btn-sm px-3" onclick="eliminar(<?= $reg['id_estado'] ?>)">
                                        <i class="fas fa-trash-alt text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">No se encontraron estados configurados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 bg-light border-top d-flex justify-content-between align-items-center">
                <form method="GET" class="d-flex align-items-center gap-3">
                    <span class="small text-muted">Ver por página:</span>
                    <select name="registrosPorPagina" class="form-select form-select-sm border-0 shadow-none bg-white rounded-3" onchange="this.form.submit()" style="width: auto;">
                        <?php foreach([10,20,50,100] as $r): ?>
                            <option value="<?= $r ?>" <?= $registrosPorPagina == $r ? 'selected' : '' ?>><?= $r ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php
                        $totalPaginas = ceil($totalRegistros / $registrosPorPagina);
                        for ($i = 1; $i <= $totalPaginas; $i++): ?>
                            <li class="page-item <?= $i == $paginaActual ? 'active' : '' ?>">
                                <a class="page-link shadow-none border-0 mx-1 rounded-3" href="?pagina=<?= $i ?>&registrosPorPagina=<?= $registrosPorPagina ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Modales (Crear/Actualizar) -->
    <div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-2xl border-0 rounded-5">
                <div class="modal-header border-0 bg-dark text-white p-4">
                    <h5 class="modal-title fw-bold">Definir Nuevo Estado</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formCrear">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">NOMBRE DE LA TABLA</label>
                                <input type="text" class="form-control p-3 bg-light border-0 shadow-none" name="tabla" required placeholder="Ej: acc_programa">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">CÓDIGO (1-2 chars)</label>
                                <input type="text" class="form-control p-3 bg-light border-0 shadow-none" name="estado" maxlength="2" required placeholder="Ej: A, I, P">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">ORDEN</label>
                                <input type="number" class="form-control p-3 bg-light border-0 shadow-none" name="orden" value="1">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">ETIQUETA DESCRIPTIVA</label>
                                <input type="text" class="form-control p-3 bg-light border-0 shadow-none" name="nombre_estado" required placeholder="Ej: Activo, Inactivo...">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">VISIBILIDAD</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="visible" value="1" checked id="vis1">
                                        <label class="form-check-label" for="vis1">Visible</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="visible" value="0" id="vis0">
                                        <label class="form-check-label" for="vis0">Oculto</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-dark px-4">Guardar Registro</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Actualizar -->
    <div class="modal fade" id="modalActualizar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-2xl border-0 rounded-5">
                <div class="modal-header border-0 bg-primary text-white p-4">
                    <h5 class="modal-title fw-bold">Actualizar Estado #<span id="display_id"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formActualizar">
                    <input type="hidden" name="id_estado" id="update_id">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">TABLA</label>
                                <input type="text" class="form-control p-3 bg-light border-0 shadow-none" name="tabla" id="update_tabla" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">CÓDIGO</label>
                                <input type="text" class="form-control p-3 bg-light border-0 shadow-none" name="estado" id="update_estado" maxlength="2" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">ORDEN</label>
                                <input type="number" class="form-control p-3 bg-light border-0 shadow-none" name="orden" id="update_orden">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">ETIQUETA</label>
                                <input type="text" class="form-control p-3 bg-light border-0 shadow-none" name="nombre_estado" id="update_nombre" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">VISIBILIDAD</label>
                                <select class="form-select p-3 bg-light border-0 shadow-none" name="visible" id="update_visible">
                                    <option value="1">Público / Visible</option>
                                    <option value="0">Oculto / Interno</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-4 fw-bold">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalUpd = document.getElementById('modalActualizar');
            modalUpd.addEventListener('show.bs.modal', function(event) {
                const btn = event.relatedTarget;
                const modal = this;
                const id = btn.getAttribute('data-id_estado');
                modal.querySelector('#display_id').textContent = id;
                modal.querySelector('#update_id').value = id;
                modal.querySelector('#update_tabla').value = btn.getAttribute('data-tabla');
                modal.querySelector('#update_estado').value = btn.getAttribute('data-estado');
                modal.querySelector('#update_nombre').value = btn.getAttribute('data-nombre_estado');
                modal.querySelector('#update_visible').value = btn.getAttribute('data-visible');
                modal.querySelector('#update_orden').value = btn.getAttribute('data-orden');
            });

            $('#formCrear').on('submit', function(e) { e.preventDefault(); $.post('../controladores/controlador_acc_estado.php?action=crear', $(this).serialize(), (r) => { if(r) location.reload(); }, 'json'); });
            $('#formActualizar').on('submit', function(e) { e.preventDefault(); $.post('../controladores/controlador_acc_estado.php?action=actualizar', $(this).serialize(), (r) => { if(r) location.reload(); }, 'json'); });
        });

        function eliminar(id) { if(confirm('¿Seguro que deseas eliminar este estado?')) { $.post('../controladores/controlador_acc_estado.php?action=eliminar', {id: id}, (r) => { if(r) location.reload(); }, 'json'); } }
    </script>
</body>
</html>
