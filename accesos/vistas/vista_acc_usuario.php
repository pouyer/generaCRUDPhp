<?php
/**
 * GeneraCRUDphp
 * Desarrollado por Carlos Mejia
 * 2024-12-31
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
    <title>Usuarios del Sistema - Administrar</title>
    <?php include('../headIconos.php'); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
        }
        body { background-color: #f0f2f5; font-family: 'Inter', system-ui, sans-serif; }
        .dashboard-card { border: none; border-radius: 16px; background: #ffffff; box-shadow: 0 4px 25px rgba(0,0,0,0.08); overflow: hidden; }
        .header-gradient { background: var(--primary-gradient); color: white; padding: 25px; }
        .table thead th { background: #fafbfc; color: #555; text-transform: uppercase; font-size: 0.75rem; font-weight: 700; border-bottom: 2px solid #edf2f7; }
        .btn-premium { border-radius: 10px; font-weight: 600; transition: all 0.3s ease; }
        .btn-premium:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .user-avatar { width: 35px; height: 35px; background: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="dashboard-card">
            <div class="header-gradient d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h4 mb-0 fw-bold"><i class="fas fa-users-cog me-2"></i> Usuarios del Sistema</h2>
                    <p class="mb-0 opacity-75 small">Gestiona los accesos y perfiles de usuario</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-white text-primary border-0 shadow-sm bg-white" data-bs-toggle="modal" data-bs-target="#modalCrear">
                        <i class="fas fa-user-plus"></i> Nuevo Usuario
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-white text-muted border-0 shadow-sm bg-white dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-file-export"></i> Exportar
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li><a class="dropdown-item py-2" href="../controladores/controlador_acc_usuario.php?action=exportar&formato=excel"><i class="fas fa-file-excel text-success me-2"></i> Excel</a></li>
                            <li><a class="dropdown-item py-2" href="../controladores/controlador_acc_usuario.php?action=exportar&formato=csv"><i class="fas fa-file-csv text-info me-2"></i> CSV</a></li>
                            <li><a class="dropdown-item py-2" href="../controladores/controlador_acc_usuario.php?action=exportar&formato=txt"><i class="fas fa-file-alt text-secondary me-2"></i> TXT</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="p-4">
                <form method="GET" action="../controladores/controlador_acc_usuario.php" class="row g-3 mb-4">
                    <input type="hidden" name="action" value="buscar">
                    <div class="col-md-9">
                        <div class="input-group shadow-sm rounded-3 overflow-hidden">
                            <span class="input-group-text bg-white border-0 ps-3 text-muted"><i class="fas fa-search"></i></span>
                            <input type="text" name="busqueda" class="form-control border-0 py-2 shadow-none" placeholder="Buscar por nombre, usuario o email..." value="<?php echo htmlspecialchars($_GET['busqueda'] ?? ''); ?>">
                            <?php if(!empty($_GET['busqueda'])): ?>
                                <a href="../controladores/controlador_acc_usuario.php" class="btn btn-outline-danger border-0 d-flex align-items-center"><i class="fas fa-eraser"></i></a>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-light px-4 border-0">Buscar</button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="registrosPorPagina" class="form-select border-0 shadow-sm bg-light" onchange="this.form.submit()">
                            <option value="10" <?= $registrosPorPagina == 10 ? 'selected' : '' ?>>10 por página</option>
                            <option value="20" <?= $registrosPorPagina == 20 ? 'selected' : '' ?>>20 por página</option>
                            <option value="50" <?= $registrosPorPagina == 50 ? 'selected' : '' ?>>50 por página</option>
                        </select>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th class="ps-3">ID</th>
                                <th>Usuario</th>
                                <th>Nombre completo</th>
                                <th>Email</th>
                                <th>Estado</th>
                                <th class="text-center pe-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            require_once '../modelos/modelo_acc_usuario.php';
                            $modelo = new ModeloAcc_usuario();
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
                                <td class="ps-3 text-muted fw-bold"><?= $reg['id_usuario'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar small fw-bold text-primary"><?= strtoupper(substr($reg['username'], 0, 1)) ?></div>
                                        <span class="fw-semibold"><?= htmlspecialchars($reg['username']) ?></span>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($reg['fullname']) ?></td>
                                <td class="small text-muted"><?= htmlspecialchars($reg['correo']) ?></td>
                                <td>
                                    <span class="badge <?= $reg['estado'] === 'A' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?> rounded-pill px-3">
                                        <?= $reg['estado'] === 'A' ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </td>
                                <td class="text-center pe-3">
                                    <div class="btn-group shadow-sm">
                                        <button class="btn btn-sm btn-white text-primary border" data-bs-toggle="modal" data-bs-target="#modalActualizar" 
                                            data-id-usuario="<?= htmlspecialchars($reg['id_usuario']) ?>"
                                            data-fullname="<?= htmlspecialchars($reg['fullname']) ?>"
                                            data-username="<?= htmlspecialchars($reg['username']) ?>"
                                            data-correo="<?= htmlspecialchars($reg['correo']) ?>"
                                            data-estado="<?= htmlspecialchars($reg['estado']) ?>"
                                            title="Editar">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button class="btn btn-sm btn-white text-danger border" onclick="eliminar('<?= htmlspecialchars($reg['id_usuario']) ?>')" title="Eliminar">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                        <button class="btn btn-sm btn-white text-info border" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalRoles" 
                                            data-id-usuario="<?= htmlspecialchars($reg['id_usuario']) ?>"
                                            data-fullname="<?= htmlspecialchars($reg['fullname']) ?>"
                                            title="Asignar Roles">
                                            <i class="fa-solid fa-user-tag"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="6" class="text-center py-5 text-muted">No se encontraron usuarios.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 d-flex justify-content-between align-items-center bg-light p-3 rounded-4">
                    <div class="small text-muted">Mostrando página <?= $paginaActual ?> de <?= ceil($totalRegistros/$registrosPorPagina) ?: 1 ?></div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php
                            $totalPaginas = ceil($totalRegistros / $registrosPorPagina);
                            for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                <li class="page-item <?= $i == $paginaActual ? 'active' : '' ?>">
                                    <a class="page-link shadow-none border-0" href="?pagina=<?= $i ?>&registrosPorPagina=<?= $registrosPorPagina ?>&busqueda=<?= urlencode($termino) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales (Crear, Actualizar, Roles) con diseño mejorado -->
    <div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <form id="formCrear">
                    <div class="modal-header bg-primary text-white border-0 py-3 px-4">
                        <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i> Nuevo Usuario</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">USERNAME</label>
                                <input type="text" class="form-control bg-light border-0 p-3" name="username" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">NOMBRE COMPLETO</label>
                                <input type="text" class="form-control bg-light border-0 p-3" name="fullname" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">EMAIL</label>
                                <input type="email" class="form-control bg-light border-0 p-3" name="correo">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">PASSWORD</label>
                                <input type="password" class="form-control bg-light border-0 p-3" name="password" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">ESTADO</label>
                                <select class="form-select bg-light border-0 p-3" name="estado" required>
                                    <option value="A">Activo</option>
                                    <option value="I">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4">Guardar Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalActualizar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <form id="formActualizar">
                    <div class="modal-header bg-warning text-dark border-0 py-3 px-4">
                        <h5 class="modal-title fw-bold"><i class="fas fa-user-edit me-2"></i> Actualizar Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" id="upd_id_usuario" name="id_usuario">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">USERNAME</label>
                                <input type="text" class="form-control bg-light border-0 p-3" id="upd_username" name="username" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">NOMBRE COMPLETO</label>
                                <input type="text" class="form-control bg-light border-0 p-3" id="upd_fullname" name="fullname" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">EMAIL</label>
                                <input type="email" class="form-control bg-light border-0 p-3" id="upd_correo" name="correo">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">ESTADO</label>
                                <select class="form-select bg-light border-0 p-3" id="upd_estado" name="estado" required>
                                    <option value="A">Activo</option>
                                    <option value="I">Inactivo</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <hr class="my-3 opacity-10">
                                <p class="small text-muted px-2 py-1 bg-warning-subtle rounded"><i class="fas fa-info-circle"></i> Deja en blanco para no cambiar la contraseña.</p>
                                <label class="form-label small fw-bold">NUEVA CONTRASEÑA</label>
                                <input type="password" class="form-control bg-light border-0 p-3" name="password">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-warning px-4 fw-bold">Actualizar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalRoles" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-info text-white border-0 py-3 px-4">
                    <h5 class="modal-title fw-bold" id="modalRolesTitle">Gestionar Roles</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="rolesContent" class="mb-4"></div>
                    <div class="card bg-light border-0 rounded-4">
                        <div class="card-body">
                            <h6 class="fw-bold small mb-3">ASIGNAR NUEVO ROL</h6>
                            <form id="formAgregarRol" class="row g-2">
                                <div class="col-8">
                                    <select class="form-select border-0 shadow-none bg-white py-2" name="id_rol" required>
                                        <option value="">Seleccione...</option>
                                        <?php
                                        $roles = $modelo->obtenerTodosRoles();
                                        foreach ($roles as $rol) {
                                            echo "<option value='" . $rol['id_rol'] . "'>" . htmlspecialchars($rol['nombre_rol']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                    <input type="hidden" id="id_usuario_rol" name="id_usuario_rol">
                                </div>
                                <div class="col-4">
                                    <button type="submit" class="btn btn-info text-white fw-bold w-100 py-2">Asignar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function eliminar(id) {
            if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
                $.post('../controladores/controlador_acc_usuario.php?action=eliminar', { id: id }, () => location.reload(), 'json');
            }
        }

        $(document).ready(function() {
            $('#modalActualizar').on('show.bs.modal', function(event) {
                const btn = $(event.relatedTarget);
                $('#upd_id_usuario').val(btn.data('id-usuario'));
                $('#upd_username').val(btn.data('username'));
                $('#upd_fullname').val(btn.data('fullname'));
                $('#upd_correo').val(btn.data('correo'));
                $('#upd_estado').val(btn.data('estado'));
            });

            $('#modalRoles').on('show.bs.modal', function(event) {
                const btn = $(event.relatedTarget);
                const id = btn.data('id-usuario');
                $('#modalRolesTitle').text('Roles: ' + btn.data('fullname'));
                $('#id_usuario_rol').val(id);
                cargarRoles(id);
            });

            function cargarRoles(id) {
                $.get('../controladores/controlador_acc_usuario.php?action=obtenerRoles&id_usuario=' + id, res => $('#rolesContent').html(res));
            }

            $('#formCrear').submit(function(e) {
                e.preventDefault();
                $.post('../controladores/controlador_acc_usuario.php?action=crear', $(this).serialize(), () => location.reload(), 'json');
            });

            $('#formActualizar').submit(function(e) {
                e.preventDefault();
                $.post('../controladores/controlador_acc_usuario.php?action=actualizar', $(this).serialize(), () => location.reload(), 'json');
            });

            $('#formAgregarRol').submit(function(e) {
                e.preventDefault();
                $.post('../controladores/controlador_acc_usuario.php?action=agregarRol', $(this).serialize(), () => cargarRoles($('#id_usuario_rol').val()), 'json');
            });
        });

        function eliminarRol(id_rol, id_usuario) {
            if (confirm('¿Desvincular este rol?')) {
                $.post('../controladores/controlador_acc_usuario.php?action=eliminarRol', { id_usuario: id_usuario, id_rol: id_rol }, () => cargarRoles(id_usuario), 'json');
            }
        }
    </script>
</body>
</html>
