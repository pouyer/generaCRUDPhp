<?php
/**
 * Plantilla para generar Vistas PHP
 * 
 * Variables disponibles:
 * @var string $nombreClase Nombre de la clase (ucfirst)
 * @var string $tabla Nombre de la tabla
 * @var bool $es_vista Indica si es una vista SQL
 * @var array $campos Array con información de las columnas
 * @var array $camposValidosCrear Array de campos válidos para el formulario de creación
 * @var array $camposValidosActualizar Array de campos válidos para el formulario de actualización
 */
?>
<?php echo "<?php\n"; ?>
/**
 * GeneraCRUDphp - Vista Generada
 */
require_once '../accesos/verificar_sesion.php';

// Cargar permisos para este programa
$mi_programa = 'vista_<?php echo $tabla; ?>.php'; // Debe coincidir con el nombre_archivo en acc_programa
$permisos = $_SESSION['permisos'][$mi_programa] ?? ['ins' => 1, 'upd' => 1, 'del' => 1, 'exp' => 1];

$registrosPorPagina = isset($_GET['registrosPorPagina']) ? (int)$_GET['registrosPorPagina'] : 15;
$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
<?php echo "?>\n"; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nombreClase; ?></title>

    <?php echo "<?php include('../headIconos.php'); ?>\n"; ?>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        :root {
            --primary-color: <?php echo $config['color'] ?? '#1e3c72'; ?>;
            --primary-gradient: linear-gradient(135deg, <?php echo $config['color'] ?? '#1e3c72'; ?> 0%, #2a5298 100%);
            --accent-color: #ff9800;
        }
        body { background-color: #f4f7fa; font-family: 'Inter', sans-serif; }
        .main-card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow: hidden; background: white; margin-top: 2rem; }
        .card-header-custom { background: var(--primary-gradient); color: white; padding: 1.5rem; border: none; }
        .btn-premium { border-radius: 10px; padding: 0.6rem 1.2rem; font-weight: 600; transition: all 0.3s; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .btn-premium:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.15); }
        .table thead th { background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; color: #495057; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
        .table tbody tr { transition: all 0.2s; }
        .table tbody tr:hover { background-color: rgba(30, 60, 114, 0.03); }
        .badge-status { border-radius: 30px; padding: 0.4em 0.8em; }
        .pagination .page-link { border-radius: 8px; margin: 0 3px; border: none; color: var(--primary-color); font-weight: 600; }
        .pagination .page-item.active .page-link { background: var(--primary-gradient); }
        .search-box { border-radius: 10px 0 0 10px; border: 1px solid #dee2e6; }
        .search-btn { border-radius: 0 10px 10px 0; background: var(--primary-color); color: white; }
    </style>
</head>
<body>
    <div class="container pb-5">
        
        <div class="main-card">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><i class="<?php echo $config['icono'] ?? 'icon-table'; ?> me-2"></i> <?php echo $nombreClase; ?></h3>
                    <small class="opacity-75"><?php echo $es_vista ? "Vista de Datos" : "Gestión de Registros"; ?></small>
                </div>
                <div class="d-flex gap-2">
                    <?php echo "<?php if (\$permisos['exp']): ?>\n"; ?>
                    <div class="dropdown">
                        <button class="btn btn-light btn-premium dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="icon-export me-1"></i> Exportar
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li><a class="dropdown-item" href="../controladores/controlador_<?php echo $tabla; ?>.php?action=exportar&formato=excel&busqueda=<?php echo "<?php echo isset(\$_GET['busqueda']) ? urlencode(\$_GET['busqueda']) : ''; ?>"; ?>&campo=<?php echo "<?php echo isset(\$_GET['campo']) ? urlencode(\$_GET['campo']) : ''; ?>"; ?>"><i class="icon-file-excel text-success me-2"></i> Excel</a></li>
                            <li><a class="dropdown-item" href="../controladores/controlador_<?php echo $tabla; ?>.php?action=exportar&formato=csv&busqueda=<?php echo "<?php echo isset(\$_GET['busqueda']) ? urlencode(\$_GET['busqueda']) : ''; ?>"; ?>&campo=<?php echo "<?php echo isset(\$_GET['campo']) ? urlencode(\$_GET['campo']) : ''; ?>"; ?>"><i class="icon-file-text text-primary me-2"></i> CSV</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../controladores/controlador_<?php echo $tabla; ?>.php?action=exportar&formato=txt&busqueda=<?php echo "<?php echo isset(\$_GET['busqueda']) ? urlencode(\$_GET['busqueda']) : ''; ?>"; ?>&campo=<?php echo "<?php echo isset(\$_GET['campo']) ? urlencode(\$_GET['campo']) : ''; ?>"; ?>"><i class="icon-doc-text-inv text-secondary me-2"></i> TXT</a></li>
                        </ul>
                    </div>
                    <?php echo "<?php endif; ?>\n"; ?>

                    <?php if (!$es_vista): ?>
                    <?php echo "<?php if (\$permisos['ins']): ?>\n"; ?>
                    <button type="button" class="btn btn-premium btn-warning text-white" data-bs-toggle="modal" data-bs-target="#modalCrear">
                        <i class="icon-plus me-1"></i> Nuevo Registro
                    </button>
                    <?php echo "<?php endif; ?>\n"; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-body p-4">
                <!-- Buscador -->
                <form method="GET" action="vista_<?php echo $tabla; ?>.php" class="mb-4">
                    <div class="input-group">
<?php if ($es_vista): ?>
                        <select name="campo" class="form-select" style="max-width: 200px;">
                            <option value="">Seleccionar Campo...</option>
<?php foreach ($campos as $campo): ?>
                            <option value="<?php echo $campo['Field']; ?>" <?php echo "<?php echo (isset(\$_GET['campo']) && \$_GET['campo'] === '{$campo['Field']}') ? 'selected' : ''; ?>"; ?>>
                                <?php echo htmlspecialchars($campo['Field']); ?>
                            </option>
<?php endforeach; ?>
                        </select>
<?php endif; ?>
                        <input type="text" name="busqueda" class="form-control search-box p-2" placeholder="<?php echo $es_vista ? 'Valor a buscar...' : 'Buscar por cualquier campo...'; ?>" value="<?php echo "<?php echo isset(\$_GET['busqueda']) ? htmlspecialchars(\$_GET['busqueda']) : ''; ?>"; ?>">
                        <input type="hidden" name="action" value="buscar">
                        <input type="hidden" name="registrosPorPagina" value="<?php echo "<?= \$registrosPorPagina ?>"; ?>">
                        <button type="submit" class="btn search-btn px-4"><i class="icon-search"></i></button>
                        <?php echo "<?php if(isset(\$_GET['busqueda']) && \$_GET['busqueda'] !== ''): ?>\n"; ?>
                            <a href="vista_<?php echo $tabla; ?>.php" class="btn btn-outline-danger d-flex align-items-center"><i class="icon-cancel"></i></a>
                        <?php echo "<?php endif; ?>\n"; ?>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
<?php 
// Parámetros de ordenamiento actuales
$llavePrimaria = 'id'; // Valor por defecto
foreach ($campos as $c) {
    if ($c['Key'] === 'PRI') {
        $llavePrimaria = $c['Field'];
        break;
    }
}

echo "<?php\n";
echo "\$sort = \$_GET['sort'] ?? \"`$tabla`.`$llavePrimaria`\";\n";
echo "\$dir = \$_GET['dir'] ?? 'DESC';\n";
echo "\$nextDir = (\$dir === 'ASC') ? 'DESC' : 'ASC';\n";
echo "?>\n";

foreach ($campos as $campo): 
    $fName = $campo['Field'];
    $showInList = (isset($config['fields'][$fName]['list'])) ? $config['fields'][$fName]['list'] : true;
    if (!$showInList) continue;
    $sortCol = isset($relaciones[$fName]) ? "`{$relaciones[$fName]['parent']}`.`{$relaciones[$fName]['display']}`" : "`{$tabla}`.`{$fName}`";
?>
                                <th>
                                    <a href="?<?php echo "<?php echo http_build_query(array_merge(\$_GET, ['sort' => \"$sortCol\", 'dir' => \$nextDir])); ?>"; ?>" class="text-decoration-none text-muted">
                                        <?php echo htmlspecialchars($fName); ?>
                                        <?php echo "<?php if (str_replace(['`',' '], '', \$sort) === str_replace(['`',' '], '', \"$sortCol\")): ?>"; ?>
                                            <i class="icon-<?php echo "<?php echo (\$dir === 'ASC') ? 'up-dir' : 'down-dir'; ?>"; ?> ms-1"></i>
                                        <?php echo "<?php endif; ?>"; ?>
                                    </a>
                                </th>
<?php endforeach; ?>
<?php if (!$es_vista): ?>
                                <th class="text-center">Acciones</th>
<?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php echo "<?php\n"; ?>
                            require_once '../modelos/modelo_<?php echo $tabla; ?>.php';
                            if (file_exists('../modelos/modelo_acc_log.php')) {
                                require_once '../modelos/modelo_acc_log.php';
                            } elseif (file_exists('../accesos/modelos/modelo_acc_log.php')) {
                                require_once '../accesos/modelos/modelo_acc_log.php';
                            }
                            $modelo = new Modelo<?php echo $nombreClase; ?>();
                            $modeloLog = new ModeloAcc_log();
                            $modeloLog->registrar($_SESSION['usuario_id'] ?? 0, 'VIEW', '<?php echo $tabla; ?>', 'Acceso a la pantalla de listado');
                            $termino = $_GET['busqueda'] ?? '';
                            $campoFiltro = $_GET['campo'] ?? '';
                            $registrosPorPagina = isset($_GET['registrosPorPagina']) ? (int)$_GET['registrosPorPagina'] : 15;
                            $paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
                            $offset = ($paginaActual - 1) * $registrosPorPagina;

                            if (isset($_GET['action']) && $_GET['action'] === 'buscar') {
                                if (!empty($campoFiltro) && !empty($termino)) {
                                     // Búsqueda avanzada por campo
                                     $totalRegistros = $modelo->contarPorCampo($campoFiltro, $termino);
                                     $registros = $modelo->buscarPorCampo($campoFiltro, $termino, $registrosPorPagina, $offset, $sort, $dir);
                                } else {
                                     // Búsqueda general
                                     $totalRegistros = $modelo->contarRegistrosPorBusqueda($termino);
                                     $registros = $modelo->buscar($termino, $registrosPorPagina, $offset, $sort, $dir);
                                }
                            } else {
                                $totalRegistros = $modelo->contarRegistros();
                                $registros = $modelo->obtenerTodos($registrosPorPagina, $offset, $sort, $dir);
                            }

                            if ($registros):
                                foreach ($registros as $registro):
                            <?php echo "?>\n"; ?>
                <tr>
<?php foreach ($campos as $campo): 
    $fieldName = $campo['Field'];
    $showInList = (isset($config['fields'][$fieldName]['list'])) ? $config['fields'][$fieldName]['list'] : true;
    if (!$showInList) continue;
    $hasRel = isset($relaciones[$fieldName]);
?>
                    <td><?php 
                        $isEnumStatus = (strpos($campo['Type'], "enum('activo','inactivo')") !== false || strpos($campo['Type'], "enum('inactivo','activo')") !== false);
                        if ($isEnumStatus) {
                            echo "<?php \$isChecked = (\$registro['" . $fieldName . "'] == 'activo') ? 'checked' : ''; ?>";
                            echo '<div class="form-check form-switch d-flex justify-content-center ps-0">';
                            echo '<input class="form-check-input" type="checkbox" disabled <?php echo $isChecked; ?>>';
                            echo '</div>';
                        } elseif ($hasRel) {
                            echo "<?php echo htmlspecialchars(\$registro['" . $fieldName . "_display'] ?? \$registro['" . $fieldName . "']); ?>";
                        } else {
                            echo "<?php echo htmlspecialchars(\$registro['" . $fieldName . "']); ?>";
                        }
                    ?></td>
<?php endforeach; ?>
                    <td class="text-center">
<?php if (!$es_vista): ?>
                        <div class="d-flex justify-content-center gap-2">
                        <?php echo "<?php if (\$permisos['upd']): ?>\n"; ?>
                        <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalActualizar" data-idActualizar="<?php echo "<?php echo \$registro['" . $campos[0]['Field'] . "']; ?>"; ?>"
<?php foreach ($campos as $campo): ?>
                           data-<?php echo htmlspecialchars($campo['Field']); ?>="<?php echo "<?php echo htmlspecialchars(\$registro['" . $campo['Field'] . "']); ?>"; ?>"
<?php endforeach; ?>
                        > <i class="icon-edit"></i></button>
                        <?php echo "<?php endif; ?>\n"; ?>

                        <?php echo "<?php if (\$permisos['del']): ?>\n"; ?>
                        <button class="btn btn-sm btn-outline-danger" onclick="eliminar('<?php echo "<?php echo htmlspecialchars(\$registro['" . $campos[0]['Field'] . "']); ?>"; ?>')"> <i class="icon-trash-2"></i></button>
                        <?php echo "<?php endif; ?>\n"; ?>
                        </div>
<?php endif; ?>
                    </td>
                </tr>
                <?php echo "<?php endforeach; else: ?>\n"; ?>
                <?php 
                    $colsCount = 0;
                    foreach($campos as $c) if((isset($config['fields'][$c['Field']]['list']) ? $config['fields'][$c['Field']]['list'] : true)) $colsCount++;
                    $colsCount += ($es_vista ? 0 : 1);
                ?>
                <tr><td colspan="<?php echo $colsCount; ?>">No hay registros disponibles.</td></tr>
                <?php echo "<?php endif; ?>\n"; ?>
            </tbody>
        </table>

        <div class="mb-3">
            <form method="GET" class="d-flex">
                <label for="registrosPorPagina" class="mr-2">Registros por página:</label>
                <select id="registrosPorPagina" name="registrosPorPagina" class="form-control mr-2" onchange="this.form.submit()">
<?php foreach ([15, 30, 50, 100] as $opcion): ?>
                    <option value="<?php echo $opcion; ?>" <?php echo "<?= \$registrosPorPagina == $opcion ? 'selected' : '' ?>"; ?>><?php echo $opcion; ?></option>
<?php endforeach; ?>
                </select>
                <input type="hidden" name="pagina" value="<?php echo "<?= \$paginaActual ?>"; ?>">
                <?php echo "<?php if(isset(\$_GET['action']) && \$_GET['action'] == 'buscar'): ?>"; ?>
                    <input type="hidden" name="action" value="buscar">
                    <input type="hidden" name="busqueda" value="<?php echo "<?= htmlspecialchars(\$termino) ?>"; ?>">
                    <input type="hidden" name="campo" value="<?php echo "<?= htmlspecialchars(\$campoFiltro) ?>"; ?>">
                <?php echo "<?php endif; ?>"; ?>
            </form>
        </div>

        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php echo "<?php\n"; ?>
                // Cálculo de páginas ya realizado arriba
                $totalPaginas = ceil($totalRegistros / $registrosPorPagina);
                for ($i = 1; $i <= $totalPaginas; $i++):
                <?php echo "?>\n"; ?>
                    <li class="page-item <?php echo "<?= \$i == \$paginaActual ? 'active' : '' ?>"; ?> ">
                        <a class="page-link" href="?pagina=<?php echo "<?= \$i ?>"; ?>&registrosPorPagina=<?php echo "<?= \$registrosPorPagina ?>"; ?>&action=<?php echo "<?= \$_GET['action'] ?? '' ?>"; ?>&busqueda=<?php echo "<?= urlencode(\$termino) ?>"; ?>&campo=<?php echo "<?= urlencode(\$campoFiltro) ?>"; ?>"><?php echo "<?= \$i ?>"; ?></a>
                    </li>
                <?php echo "<?php endfor; ?>\n"; ?>
            </ul>
        </nav>

        <!-- Modal para crear -->
        <div class="modal fade shadow" id="modalCrear" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 overflow-hidden" style="border-radius: 20px;">
                    <div class="modal-header text-white" style="background: var(--primary-gradient);">
                        <h5 class="modal-title fw-bold"><i class="icon-plus-circle me-2"></i>Nuevo Registro</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form id="formCrear" method="post">
<?php 
    $numCols = (isset($config['columns'])) ? (int)$config['columns'] : 2;
    $bsClass = 'col-md-6';
    if ($numCols == 1) $bsClass = 'col-md-12';
    if ($numCols == 3) $bsClass = 'col-md-4';
    if ($numCols == 4) $bsClass = 'col-md-3';

    $contador = 0;
    foreach ($camposValidosCrear as $index => $campo): 
        $fieldName = $campo['Field'];
        // Ignorar campos de auditoría en creación
        if (isset($config['fields'][$fieldName]['audit']) && ($config['fields'][$fieldName]['audit'] === 'insert' || $config['fields'][$fieldName]['audit'] === 'update')) {
            continue;
        }

        $hasRel = isset($relaciones[$fieldName]);
        if ($contador % $numCols == 0) echo '                            <div class="row">';
        $contador++;
?>
                                <div class="<?php echo $bsClass; ?> mb-3">
                                    <label for="<?php echo $fieldName; ?>"><?php echo htmlspecialchars($fieldName); ?>:</label>
<?php 
    $isEnumStatus = (strpos($campo['Type'], "enum('activo','inactivo')") !== false || strpos($campo['Type'], "enum('inactivo','activo')") !== false);
?>
<?php if ($isEnumStatus): ?>
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="<?php echo $fieldName; ?>" value="inactivo">
                                        <input class="form-check-input" type="checkbox" id="<?php echo $fieldName; ?>" name="<?php echo $fieldName; ?>" value="activo" checked>
                                        <label class="form-check-label" for="<?php echo $fieldName; ?>">Activo/Inactivo</label>
                                    </div>
<?php elseif ($hasRel): ?>
                                    <select class="form-select" id="<?php echo $fieldName; ?>" name="<?php echo $fieldName; ?>"<?php echo ($campo['Null'] == 'NO') ? ' required' : ''; ?>>
                                        <?php echo "<?php if('{$campo['Null']}' == 'YES'): ?>"; ?>
                                        <option value="">-- Seleccionar --</option>
                                        <?php echo "<?php endif; ?>"; ?>
                                        <?php echo "<?php foreach (\$modelo->obtenerRelacionado_{$fieldName}() as \$opcion): ?>"; ?>
                                        <option value="<?php echo "<?= \$opcion['id'] ?>"; ?>"><?php echo "<?= htmlspecialchars(\$opcion['texto']) ?>"; ?></option>
                                        <?php echo "<?php endforeach; ?>"; ?>
                                    </select>
<?php else: ?>
<?php 
        $type = 'text';
        if (strpos($campo['Type'], 'date') !== false || strpos($campo['Type'], 'datetime') !== false) $type = 'date';
        elseif (strpos($campo['Type'], 'int') !== false || strpos($campo['Type'], 'float') !== false) $type = 'number';
        $required = ($campo['Null'] == 'NO') ? ' required' : '';
?>
                                    <input type="<?php echo $type; ?>" class="form-control" id="<?php echo $fieldName; ?>" name="<?php echo $fieldName; ?>"<?php echo $required; ?>>
<?php endif; ?>
                                </div>
<?php 
        if ($contador % $numCols == 0 || $index === array_key_last($camposValidosCrear)) echo '                            </div>';
    endforeach; 
?>
                            <div class="text-end mt-4">
                                <button type="button" class="btn btn-light btn-premium me-2" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-premium btn-primary px-5"><i class="icon-ok-2 me-1"></i> Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para actualizar -->
        <div class="modal fade shadow" id="modalActualizar" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 overflow-hidden" style="border-radius: 20px;">
                    <div class="modal-header text-white" style="background: var(--primary-gradient);">
                        <h5 class="modal-title fw-bold"><i class="icon-edit me-2"></i>Editar Registro</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                    <form id="formActualizar" method="post">
<?php
    $contador = 0;
    // Extraer campos PK y validos para el cuerpo
    $pkFields = array_filter($campos, function($c) { return $c['Key'] == 'PRI'; });
    
    // Header con PKs
    foreach ($pkFields as $campo) {
?>
                     <div class="modal-header">
                         <div class="row">
                             <div class="form-group col-md-8">
                               <h5 class="modal-title">Actualizar <?php echo $nombreClase; ?> - ID: </h5>
                             </div>
                             <div class="form-group col-md-3">
                                <div class="form-group mb-0 d-flex align-items-center">
                                    <input type="text" class="form-control" id="<?php echo $campo['Field']; ?>" name="<?php echo $campo['Field']; ?>" readonly>
                                </div>
                             </div>
                         </div>
                         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                         </button>
                     </div>
<?php
    }

    // Cuerpo del form
    $contador = 0;
    foreach ($camposValidosActualizar as $index => $campo):
        $fieldName = $campo['Field'];
        // Ignorar campos de auditoría en actualización (solo los modificables, insert no importa aquí porque ya existe)
        // Pero si es 'update', se llena automático, así que no mostrar. 'insert' se muestra? No debería ser editable. 
        // Mejor regla: si tiene audit 'update', no se muestra. Si tiene 'insert', no se muestra para no editarlo erróneamente.
        if (isset($config['fields'][$fieldName]['audit']) && ($config['fields'][$fieldName]['audit'] === 'update' || $config['fields'][$fieldName]['audit'] === 'insert')) {
            continue;
        }
        
        $hasRel = isset($relaciones[$fieldName]);
        if ($contador % $numCols == 0) echo '                            <div class="row">';
        $contador++;
?>
                                 <div class="<?php echo $bsClass; ?> mb-3">
                                     <label for="<?php echo $fieldName; ?>"><?php echo htmlspecialchars($fieldName); ?>:</label>
<?php 
    $isEnumStatus = (strpos($campo['Type'], "enum('activo','inactivo')") !== false || strpos($campo['Type'], "enum('inactivo','activo')") !== false);
?>
<?php if ($isEnumStatus): ?>
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="<?php echo $fieldName; ?>" value="inactivo">
                                        <input class="form-check-input" type="checkbox" id="<?php echo $fieldName; ?>" name="<?php echo $fieldName; ?>" value="activo">
                                        <label class="form-check-label" for="<?php echo $fieldName; ?>">Activo/Inactivo</label>
                                    </div>
<?php elseif ($hasRel): ?>
                                    <select class="form-select" id="<?php echo $fieldName; ?>" name="<?php echo $fieldName; ?>"<?php echo ($campo['Null'] == 'NO') ? ' required' : ''; ?>>
                                        <?php echo "<?php if('{$campo['Null']}' == 'YES'): ?>"; ?>
                                        <option value="">-- Seleccionar --</option>
                                        <?php echo "<?php endif; ?>"; ?>
                                        <?php echo "<?php foreach (\$modelo->obtenerRelacionado_{$fieldName}() as \$opcion): ?>"; ?>
                                        <option value="<?php echo "<?= \$opcion['id'] ?>"; ?>"><?php echo "<?= htmlspecialchars(\$opcion['texto']) ?>"; ?></option>
                                        <?php echo "<?php endforeach; ?>"; ?>
                                    </select>
<?php else: ?>
<?php 
        $type = 'text';
        if (strpos($campo['Type'], 'date') !== false || strpos($campo['Type'], 'datetime') !== false) $type = 'date';
        elseif (strpos($campo['Type'], 'int') !== false || strpos($campo['Type'], 'float') !== false) $type = 'number';
        $required = ($campo['Null'] == 'NO') ? ' required' : '';
?>
                                     <input type="<?php echo $type; ?>" class="form-control" id="<?php echo $fieldName; ?>" name="<?php echo $fieldName; ?>"<?php echo $required; ?>>
<?php endif; ?>
                                </div>
<?php
        if ($contador % $numCols == 0 || $index === array_key_last($camposValidosActualizar)) echo '                            </div>';
    endforeach;
?>
                                 <input type="hidden" id="idActualizar" name="idActualizar">
                                 <div class="text-end mt-4">
                                     <button type="button" class="btn btn-light btn-premium me-2" data-bs-dismiss="modal">Cancelar</button>
                                     <button type="submit" class="btn btn-premium btn-warning text-white px-5"><i class="icon-ok-2 me-1"></i> Actualizar</button>
                                 </div>
                    </form>
                 </div>
             </div>
         </div>
     </div>

    <!-- Scripts necesarios -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var myModalCreate = new bootstrap.Modal(document.getElementById('modalCrear'));
            var myModalUpdate = new bootstrap.Modal(document.getElementById('modalActualizar'));

            // Manejador para el botón crear
            var btnCrear = document.querySelector('[data-bs-target="#modalCrear"]');
            if(btnCrear){
                btnCrear.addEventListener('click', function() {
                    myModalCreate.show();
                });
            }

            // Manejador del formulario crear
            document.getElementById('formCrear').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch('../controladores/controlador_<?php echo $tabla; ?>.php?action=crear', {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if(data) {
                        myModalCreate.hide();
                        location.reload();
                    } else {
                        alert('Error al crear el registro.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud.');
                });
            });

            // Inicializar el modal de actualización
            var modalActualizarElement = document.getElementById('modalActualizar');
            var modalActualizar = new bootstrap.Modal(modalActualizarElement);

            modalActualizarElement.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var modal = this;

<?php foreach ($campos as $campo): ?>
                var valor<?php echo $campo['Field']; ?> = button.getAttribute('data-<?php echo $campo['Field']; ?>');
                var input<?php echo $campo['Field']; ?> = modal.querySelector('#<?php echo $campo['Field']; ?>');
                if(input<?php echo $campo['Field']; ?>) {
                    if (input<?php echo $campo['Field']; ?>.type === 'checkbox') {
                        input<?php echo $campo['Field']; ?>.checked = (valor<?php echo $campo['Field']; ?> === 'activo');
                    } else {
                        input<?php echo $campo['Field']; ?>.value = valor<?php echo $campo['Field']; ?>;
                    }
                }
<?php endforeach; ?>
            });

            document.getElementById('formActualizar').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                fetch('../controladores/controlador_<?php echo $tabla; ?>.php?action=actualizar', {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if(data) {
                        modalActualizar.hide();
                        location.reload();
                    } else {
                        alert('Error al actualizar el registro.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud.');
                });
            });
        });

        function eliminar(id) {
            if (confirm('¿Estás seguro de que deseas eliminar este registro?')) {
                fetch('../controladores/controlador_<?php echo $tabla; ?>.php?action=eliminar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + encodeURIComponent(id)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data) {
                        location.reload();
                    } else {
                        alert('Error al eliminar el registro.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar el registro: ' + error.message);
                });
            }
        }
    </script>

    <style>
        .modal-backdrop { z-index: 1040; }
        .modal { z-index: 1050; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
            var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });
        });
    </script>
    </div>
</body>
</html>
