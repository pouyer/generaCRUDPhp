<?php
    $registrosPorPagina = isset($_GET['registrosPorPagina']) ? (int)$_GET['registrosPorPagina'] : 100;
    $paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log de Acciones</title>
    <?php include('../headIconos.php'); ?>
    <link rel="stylesheet" href="../css/estilos.css">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="icon-history"></i> Log de Acciones del Sistema</h4>
                <div class="btn-group">
                    <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="icon-export"></i> Exportar
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="../controladores/controlador_acc_log.php?action=exportar&formato=excel&busqueda=<?php echo isset($_GET['busqueda']) ? urlencode($_GET['busqueda']) : ''; ?>">Excel</a></li>
                        <li><a class="dropdown-item" href="../controladores/controlador_acc_log.php?action=exportar&formato=csv&busqueda=<?php echo isset($_GET['busqueda']) ? urlencode($_GET['busqueda']) : ''; ?>">CSV</a></li>
                        <li><a class="dropdown-item" href="../controladores/controlador_acc_log.php?action=exportar&formato=txt&busqueda=<?php echo isset($_GET['busqueda']) ? urlencode($_GET['busqueda']) : ''; ?>">TXT</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="../controladores/controlador_acc_log.php" class="row g-3 mb-4">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="icon-search-outline"></i></span>
                            <input type="text" name="busqueda" class="form-control" placeholder="Buscar por acción, tabla, detalles o usuario..." value="<?php echo isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : ''; ?>">
                            <button type="submit" class="btn btn-primary">Buscar</button>
                            <?php if(isset($_GET['busqueda']) && $_GET['busqueda'] !== ''): ?>
                                <a href="../controladores/controlador_acc_log.php" class="btn btn-outline-danger"><i class="icon-eraser"></i> Limpiar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="input-group">
                            <label class="input-group-text" for="registrosPorPagina">Ver:</label>
                            <select id="registrosPorPagina" name="registrosPorPagina" class="form-select" onchange="this.form.submit()">
                                <option value="100" <?= $registrosPorPagina == 100 ? 'selected' : '' ?>>100 registros</option>
                                <option value="300" <?= $registrosPorPagina == 300 ? 'selected' : '' ?>>300 registros</option>
                                <option value="500" <?= $registrosPorPagina == 500 ? 'selected' : '' ?>>500 registros</option>
                            </select>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 80px;">ID</th>
                                <th style="width: 150px;">Usuario</th>
                                <th style="width: 120px;">Acción</th>
                                <th style="width: 150px;">Tabla</th>
                                <th>Detalles</th>
                                <th style="width: 130px;">IP</th>
                                <th style="width: 180px;">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            require_once '../modelos/modelo_acc_log.php';
                            $modelo = new ModeloAcc_log();
                            $termino = $_GET['busqueda'] ?? '';
                            $offset = ($paginaActual - 1) * $registrosPorPagina;

                            if (!empty($termino)) {
                                $totalRegistros = $modelo->contarRegistrosPorBusqueda($termino);
                                $registros = $modelo->buscar($termino, $registrosPorPagina, $offset);
                            } else {
                                $totalRegistros = $modelo->contarRegistros();
                                $registros = $modelo->obtenerTodos($registrosPorPagina, $offset);
                            }

                            if ($registros):
                                foreach ($registros as $reg):
                                    $badgeClass = 'bg-secondary';
                                    if($reg['accion'] == 'LOGIN') $badgeClass = 'bg-success';
                                    if($reg['accion'] == 'DELETE') $badgeClass = 'bg-danger';
                                    if($reg['accion'] == 'UPDATE') $badgeClass = 'bg-warning text-dark';
                                    if($reg['accion'] == 'CREATE') $badgeClass = 'bg-info text-dark';
                                    if($reg['accion'] == 'EXPORT') $badgeClass = 'bg-primary';
                            ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $reg['id_log'] ?></td>
                                <td><i class="icon-user"></i> <?= htmlspecialchars($reg['username'] ?? 'Sistema') ?></td>
                                <td><span class="badge <?= $badgeClass ?> w-100"><?= $reg['accion'] ?></span></td>
                                <td><code><?= htmlspecialchars($reg['tabla'] ?? '-') ?></code></td>
                                <td class="small text-muted"><?= htmlspecialchars($reg['detalles']) ?></td>
                                <td class="text-nowrap"><?= $reg['ip'] ?></td>
                                <td class="text-nowrap"><?= $reg['fecha'] ?></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="7" class="text-center py-4">No se encontraron registros de actividad.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <nav class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        Mostrando página <?= $paginaActual ?> de <?= ceil($totalRegistros / $registrosPorPagina) ?> (<?= $totalRegistros ?> registros totales)
                    </div>
                    <ul class="pagination pagination-sm mb-0">
                        <?php
                        $totalPaginas = ceil($totalRegistros / $registrosPorPagina);
                        $max_links = 5;
                        $start = max(1, $paginaActual - floor($max_links / 2));
                        $end = min($totalPaginas, $start + $max_links - 1);
                        if ($end - $start < $max_links - 1) $start = max(1, $end - $max_links + 1);

                        for ($i = $start; $i <= $end; $i++): ?>
                            <li class="page-item <?= $i == $paginaActual ? 'active' : '' ?>">
                                <a class="page-link" href="?pagina=<?= $i ?>&registrosPorPagina=<?= $registrosPorPagina ?>&busqueda=<?= urlencode($termino) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
