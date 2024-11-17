<?php
function generar_vista($tabla, $campos, $directorio) {
    $nombreClase = ucfirst($tabla);
    $contenido =  "<?php\n";
    $contenido .= "    \$registrosPorPagina = isset(\$_GET['registrosPorPagina']) ? (int)\$_GET['registrosPorPagina'] : 10;\n";
    $contenido .= "    \$paginaActual = isset(\$_GET['pagina']) ? (int)\$_GET['pagina'] : 1;\n";
    $contenido .= "?>\n";
    $contenido .= "<!DOCTYPE html>\n";
    $contenido .= "<html lang=\"es\">\n";
    $contenido .= "<head>\n";
    $contenido .= "    <meta charset=\"UTF-8\">\n";
    $contenido .= "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
    $contenido .= "    <title>$nombreClase - Vista</title>\n";
    $contenido .= "    <link rel=\"stylesheet\" href=\"https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css\">\n";
    $contenido .= "    <link rel=\"stylesheet\" href=\"../css/estilos.css\">\n";
    $contenido .= "</head>\n";
    $contenido .= "<body>\n";
    $contenido .= "    <div class=\"container\">\n";
    
    // Encabezado con título y botones
    $contenido .= "        <h1 class=\"text-center\">$nombreClase</h1>\n";
    $contenido .= "        <div class=\"d-flex justify-content-between mb-3\">\n";
    $contenido .= "            <button class=\"btn btn-primary\" data-toggle=\"modal\" data-target=\"#modalCrear\">Crear</button>\n";
    $contenido .= "            <div class=\"btn-group\">\n";
    $contenido .= "                <button class=\"btn btn-success dropdown-toggle\" type=\"button\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">\n";
    $contenido .= "                    Exportar\n";
    $contenido .= "                </button>\n";
    $contenido .= "                <div class=\"dropdown-menu\">\n";
    $contenido .= "                    <a class=\"dropdown-item\" href=\"../controladores/controlador_$tabla.php?action=exportar&formato=excel&busqueda=<?php echo isset(\$_GET['busqueda']) ? urlencode(\$_GET['busqueda']) : ''; ?>\">Excel</a>\n";
    $contenido .= "                    <a class=\"dropdown-item\" href=\"../controladores/controlador_$tabla.php?action=exportar&formato=csv&busqueda=<?php echo isset(\$_GET['busqueda']) ? urlencode(\$_GET['busqueda']) : ''; ?>\">CSV</a>\n";
    $contenido .= "                    <a class=\"dropdown-item\" href=\"../controladores/controlador_$tabla.php?action=exportar&formato=txt&busqueda=<?php echo isset(\$_GET['busqueda']) ? urlencode(\$_GET['busqueda']) : ''; ?>\">TXT</a>\n";
    $contenido .= "                </div>\n";
    $contenido .= "            </div>\n";
    $contenido .= "        </div>\n";
    
    // Formulario de búsqueda
    $contenido .= "        <form method=\"GET\" action=\"../controladores/controlador_$tabla.php\" class=\"form-inline mb-3\">\n";
    $contenido .= "            <div class=\"input-group\" style=\"width: 100%;\">\n";
    $contenido .= "                <input type=\"text\" name=\"busqueda\" class=\"form-control\" placeholder=\"Buscar...\" value=\"<?php echo isset(\$_GET['busqueda']) ? htmlspecialchars(\$_GET['busqueda']) : ''; ?>\">\n";
    $contenido .= "                <input type=\"hidden\" name=\"action\" value=\"buscar\">\n";
    $contenido .= "                <input type=\"hidden\" name=\"registrosPorPagina\" value=\"<?= \$registrosPorPagina ?>\">\n";
    $contenido .= "                <input type=\"hidden\" name=\"pagina\" value=\"<?= \$paginaActual ?>\">\n";
    $contenido .= "                <div class=\"input-group-append\">\n";
    $contenido .= "                    <button type=\"submit\" class=\"btn btn-secondary\">Buscar</button>\n";
    $contenido .= "                    <?php if(isset(\$_GET['busqueda']) && \$_GET['busqueda'] !== ''): ?>\n";
    $contenido .= "                        <a href=\"../controladores/controlador_$tabla.php\" class=\"btn btn-outline-danger\">Limpiar</a>\n";
    $contenido .= "                    <?php endif; ?>\n";
    $contenido .= "                </div>\n";
    $contenido .= "            </div>\n";
    $contenido .= "        </form>\n";

    // Tabla
    $contenido .= "        <table class=\"table table-striped table-sm mt-3\">\n";
    $contenido .= "            <thead>\n";
    $contenido .= "                <tr>\n";

    // Encabezados de la tabla
    foreach ($campos as $campo) {
        $contenido .= "                    <th>" . htmlspecialchars($campo['Field']) . "</th>\n";
    }
    $contenido .= "                    <th>Acciones</th>\n";
    $contenido .= "                </tr>\n";
    $contenido .= "            </thead>\n";
    $contenido .= "            <tbody>\n";
    $contenido .= "                <?php\n";
    $contenido .= "                require_once '../modelos/modelo_$tabla.php';\n";
    $contenido .= "                \$modelo = new Modelo$nombreClase();\n";
    $contenido .= "                \$termino = \$_GET['busqueda'] ?? ''; // Inicializar la variable \$termino\n";
    $contenido .= "                \$registrosPorPagina = isset(\$_GET['registrosPorPagina']) ? (int)\$_GET['registrosPorPagina'] : 10;\n";
    $contenido .= "                \$paginaActual = isset(\$_GET['pagina']) ? (int)\$_GET['pagina'] : 1;\n";
    $contenido .= " 			   \$offset = (\$paginaActual - 1) * \$registrosPorPagina; // Calcular el offset para la paginación\n";
    $contenido .= "				   	// Verifica si se está realizando una búsqueda \n"; 
    $contenido .= " 			   	if (isset(\$_GET['action']) && \$_GET['action'] === 'buscar') { \n";		
    $contenido .= " 			   	// Si se está buscando, obtenemos los registros filtrados \n";
    $contenido .= " 			   	\$termino = \$_GET['busqueda'] ?? ''; \n";
    $contenido .= " 			   	\$totalRegistros = \$modelo->contarRegistrosPorBusqueda(\$termino); // Contar registros que coinciden con la búsqueda\n";
    $contenido .= " 			   	\$registros = \$modelo->buscar(\$termino, \$registrosPorPagina, \$offset); // Llama a la función de búsqueda con paginación\n";
    $contenido .= " 			   } else { \n";
    $contenido .= " 			   // Si no se está buscando, obtenemos todos los registros con paginación \n";
    $contenido .= " 			    \$totalRegistros = \$modelo->contarRegistros(); // Total de registros en la base de datos\n";
    $contenido .= " 			   	\$registros = \$modelo->obtenerTodos(\$registrosPorPagina, \$offset); // Llama a la función para obtener todos\n";
    $contenido .= " 			   }\n";
    $contenido .= " 			   // Verifica si hay registros y los muestra\n";
    $contenido .= "                if (\$registros):\n";
    $contenido .= "                    foreach (\$registros as \$registro):\n";
    $contenido .= "                ?>\n";

    $contenido .= "                <tr>\n";
    // Datos de la tabla
    foreach ($campos as $campo) {
        $contenido .= "                    <td><?php echo htmlspecialchars(\$registro['" . $campo['Field'] . "']); ?></td>\n";
    }
    $contenido .= "                    <td>\n";
    $contenido .= "                        <button class=\"btn btn-warning\" data-toggle=\"modal\" data-target=\"#modalActualizar\" data-idActualizar=\"<?php echo \$registro['" . $campos[0]['Field'] . "']; ?>\"";

    // Agregar atributos data-* para cada campo que se desea cargar
    foreach ($campos as $campo) {
        $contenido .= "\n                           data-" . htmlspecialchars($campo['Field']) . "=\"<?php echo htmlspecialchars(\$registro['" . $campo['Field'] . "']); ?>\"";
    }
    $contenido .= ">Actualizar</button>\n"; // Asegúrate de que 'nombre' sea un campo válido
    $contenido .= "                        <button class=\"btn btn-danger\" onclick=\"eliminar('<?php echo htmlspecialchars(\$registro['" . $campos[0]['Field'] . "']); ?>')\">Eliminar</button>\n";
    $contenido .= "                    </td>\n";
    $contenido .= "                </tr>\n";
    $contenido .= "                <?php endforeach; else: ?>\n";
    $contenido .= "                <tr><td colspan=\"" . (count($campos) + 1) . "\">No hay registros disponibles.</td></tr>\n";
    $contenido .= "                <?php endif; ?>\n";
    $contenido .= "            </tbody>\n";
    $contenido .= "        </table>\n";

    // 1. Agregar un formulario para seleccionar el número de registros por página
    $contenido .= "        <div class=\"mb-3\">\n";
    $contenido .= "            <form method=\"GET\" class=\"form-inline\">\n";
    $contenido .= "                <label for=\"registrosPorPagina\" class=\"mr-2\">Registros por página:</label>\n";
    $contenido .= "                <select id=\"registrosPorPagina\" name=\"registrosPorPagina\" class=\"form-control mr-2\" onchange=\"this.form.submit()\">\n";

// Opciones del select
foreach ([10, 20, 30, 50] as $opcion) {
    $contenido .= "                    <option value=\"".$opcion."\" <?= \$registrosPorPagina == ".$opcion." ? 'selected' : '' ?>>".$opcion."</option>\n";
}

    $contenido .= "                </select>\n";
    $contenido .= "                <input type=\"hidden\" name=\"pagina\" value=\"<?= \$paginaActual ?>\">\n";
    $contenido .= "            </form>\n";
    $contenido .= "        </div>\n";




    // 3. Agregar la lógica de paginación
// 3. Agregar la lógica de paginación
$contenido .= "        <nav aria-label=\"Page navigation\">\n";
$contenido .= "            <ul class=\"pagination\">\n";
$contenido .= "                <?php\n";
$contenido .= "                // Verifica si se está realizando una búsqueda\n";
$contenido .= "                if (isset(\$_GET['action']) && \$_GET['action'] === 'buscar') {\n";
$contenido .= "                \$termino = \$_GET['busqueda'] ?? ''; // Inicializar la variable \$termino\n";
$contenido .= "                    \$totalRegistros = \$modelo->contarRegistrosPorBusqueda(\$termino); // Contar registros que coinciden con la búsqueda\n";
$contenido .= "                } else {\n";
$contenido .= "                    \$totalRegistros = \$modelo->contarRegistros(); // Total de registros en la base de datos\n";
$contenido .= "                }\n";
$contenido .= "                \$totalPaginas = ceil(\$totalRegistros / \$registrosPorPagina);\n";
$contenido .= "                for (\$i = 1; \$i <= \$totalPaginas; \$i++):\n";
$contenido .= "                ?>\n";
$contenido .= "                    <li class=\"page-item <?= \$i == \$paginaActual ? 'active' : '' ?> \">\n";
$contenido .= "                        <a class=\"page-link\" href=\"?pagina=<?= \$i ?>&registrosPorPagina=<?= \$registrosPorPagina ?>&busqueda=<?= urlencode(\$termino) ?>\"><?= \$i ?></a>\n";
$contenido .= "                    </li>\n";
$contenido .= "                <?php endfor; ?>\n";
$contenido .= "            </ul>\n";
$contenido .= "        </nav>\n";

    // Modal para crear
    $contenido .= "        <div class=\"modal fade\" id=\"modalCrear\" tabindex=\"-1\" role=\"dialog\">\n";
    $contenido .= "            <div class=\"modal-dialog\" role=\"document\">\n";
    $contenido .= "                <div class=\"modal-content\">\n";
    $contenido .= "                    <div class=\"modal-header\">\n";
    $contenido .= "                        <h5 class=\"modal-title\">Crear $nombreClase</h5>\n";
    $contenido .= "                        <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">\n";
    $contenido .= "                            <span aria-hidden=\"true\">&times;</span>\n";
    $contenido .= "                        </button>\n";
    $contenido .= "                    </div>\n";
    $contenido .= "                    <div class=\"modal-body\">\n";
    $contenido .= "                        <form id=\"formCrear\">\n";

    // Campos del formulario de creación
    foreach ($campos as $campo) {
        
        if ($campo['Extra'] != 'auto_increment' && $campo['Extra'] != 'on update current_timestamp()'  && $campo['Key'] != 'PRI' && $campo['Default'] != 'current_timestamp()' ) { // Excluimos la llave primaria, campos auto_increment y CURRENT_TIMESTAMP
            $contenido .= "                            <div class=\"form-group\">\n";
            $contenido .= "                                <label for=\"{$campo['Field']}\">" . htmlspecialchars($campo['Field']) . ":</label>\n";
            if (strpos($campo['Type'], 'date') !== false || strpos($campo['Type'], 'datetime') !== false) {
                $contenido .= "                                <input type=\"date\" class=\"form-control\" id=\"{$campo['Field']}\" name=\"{$campo['Field']}\"" . ($campo['Null'] == 'NO' ? ' required' : '') . ">\n";
            } elseif (strpos($campo['Type'], 'int') !== false || strpos($campo['Type'], 'float') !== false) {
                $contenido .= "                                <input type=\"number\" class=\"form-control\" id=\"{$campo['Field']}\" name=\"{$campo['Field']}\"" . ($campo['Null'] == 'NO' ? ' required' : '') . ">\n";
            } else {
                $contenido .= "                                <input type=\"text\" class=\"form-control\" id=\"{$campo['Field']}\" name=\"{$campo['Field']}\"" . ($campo['Null'] == 'NO' ? ' required' : '') . ">\n";
            }
            $contenido .= "                            </div>\n";
        }
    }

    $contenido .= "                            <button type=\"submit\" class=\"btn btn-primary\">Crear</button>\n";
    $contenido .= "                        </form>\n";
    $contenido .= "                    </div>\n";
    $contenido .= "                </div>\n";
    $contenido .= "            </div>\n";
    $contenido .= "        </div>\n";

    // Modal para actualizar
    $contenido .= "        <div class=\"modal fade\" id=\"modalActualizar\" tabindex=\"-1\" role=\"dialog\">\n";
    $contenido .= "            <div class=\"modal-dialog\" role=\"document\">\n";
    $contenido .= "                <div class=\"modal-content\">\n";
    $contenido .= "                    <div class=\"modal-header\">\n";
    $contenido .= "                        <h5 class=\"modal-title\">Actualizar $nombreClase</h5>\n";
    $contenido .= "                        <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">\n";
    $contenido .= "                            <span aria-hidden=\"true\">&times;</span>\n";
    $contenido .= "                        </button>\n";
    $contenido .= "                    </div>\n";
    $contenido .= "                    <div class=\"modal-body\">\n";
    $contenido .= "                        <form id=\"formActualizar\">\n";

    // Campos del formulario de actualización
    foreach ($campos as $campo) {
        if ($campo['Default'] != 'current_timestamp()' && $campo['Extra'] != 'on update current_timestamp()') { // Excluimos CURRENT_TIMESTAMP
            $contenido .= "                            <div class=\"form-group\">\n";
            $contenido .= "                                <label for=\"{$campo['Field']}\">" . htmlspecialchars($campo['Field']) . ":</label>\n";
            
            // Si es la llave primaria, mostrar como solo lectura
            if ($campo['Key'] == 'PRI') {
                $contenido .= "                                <input type=\"text\" class=\"form-control\" id=\"{$campo['Field']}\" name=\"{$campo['Field']}\" value=\"<?php echo htmlspecialchars(\$registro['{$campo['Field']}']); ?>\" readonly>\n";
            } else 
                {
                if (strpos($campo['Type'], 'date') !== false || strpos($campo['Type'], 'datetime') !== false) {
                    $contenido .= "                                <input type=\"date\" class=\"form-control\" id=\"{$campo['Field']}\" name=\"{$campo['Field']}\"" . ($campo['Null'] == 'NO' ? ' required' : '') . ">\n";
                } elseif (strpos($campo['Type'], 'int') !== false || strpos($campo['Type'], 'float') !== false) {
                    $contenido .= "                                <input type=\"number\" class=\"form-control\" id=\"{$campo['Field']}\" name=\"{$campo['Field']}\"" . ($campo['Null'] == 'NO' ? ' required' : '') . ">\n";
                } else {
                    $contenido .= "                                <input type=\"text\" class=\"form-control\" id=\"{$campo['Field']}\" name=\"{$campo['Field']}\"" . ($campo['Null'] == 'NO' ? ' required' : '') . ">\n";
                }
            $contenido .= "                            </div>\n";
            }
        }
    }

    $contenido .= "                            <input type=\"hidden\" id=\"idActualizar\" name=\"idActualizar\">\n";
    $contenido .= "                            <button type=\"submit\" class=\"btn btn-warning\">Actualizar</button>\n";
    $contenido .= "                        </form>\n";
    $contenido .= "                    </div>\n";
    $contenido .= "                </div>\n";
    $contenido .= "            </div>\n";
    $contenido .= "        </div>\n";

    $contenido .= "    <script src=\"https://code.jquery.com/jquery-3.6.0.min.js\"></script>\n";
    $contenido .= "    <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js\"></script>\n";

    // Script para manejar la eliminación
    $contenido .= "        <script>\n";
    $contenido .= "            function eliminar(id) {\n";
    $contenido .= "                if (confirm('¿Estás seguro de que deseas eliminar este registro?')) {\n";
    $contenido .= "                    $.ajax({\n";
    $contenido .= "                        type: 'POST',\n";
    $contenido .= "                        url: '../controladores/controlador_$tabla.php?action=eliminar', // Cambia esto a la ruta correcta\n";
    $contenido .= "                        data: { id: id },\n";
    $contenido .= "                        success: function(response) {\n";
    $contenido .= "                            location.reload();\n";
    $contenido .= "                        },\n";
    $contenido .= "                        error: function(xhr, status, error) {\n";
    $contenido .= "                            console.error(error);\n";
    $contenido .= "                            alert('Error al eliminar el registro.');\n";
    $contenido .= "                        }\n";
    $contenido .= "                    });\n";
    $contenido .= "                }\n";
    $contenido .= "            }\n";
    $contenido .= "        </script>\n";

    // Script para manejar el envío del formulario de creación
    $contenido .= "        <script>\n";
    $contenido .= "            $(document).ready(function() {\n";
    $contenido .= "                $('#formCrear').on('submit', function(e) {\n";
    $contenido .= "                    e.preventDefault(); // Evitar el envío normal del formulario\n";
    $contenido .= "                    $.ajax({\n";
    $contenido .= "                        type: 'POST',\n";
    $contenido .= "                        url: '../controladores/controlador_$tabla.php?action=crear', // Cambia esto a la ruta correcta\n";
    $contenido .= "                        data: $(this).serialize(),\n";
    $contenido .= "                        success: function(response) {\n";
    $contenido .= "                            location.reload(); // Recargar la página para ver los cambios\n";
    $contenido .= "                        },\n";
    $contenido .= "                        error: function(xhr, status, error) {\n";
    $contenido .= "                            console.error(error);\n";
    $contenido .= "                            alert('Error al crear el registro.');\n";
    $contenido .= "                        }\n";
    $contenido .= "                    });\n";
    $contenido .= "                });\n";

    // Script para manejar el envío del formulario de actualización
    $contenido .= "                $('#formActualizar').on('submit', function(e) {\n";
    $contenido .= "                    e.preventDefault(); // Evitar el envío normal del formulario\n";
    $contenido .= "                    console.log($(this).serialize()); // Verificar los datos enviados\n";
    $contenido .= "                    $.ajax({\n";
    $contenido .= "                        type: 'POST',\n";
    $contenido .= "                        url: '../controladores/controlador_$tabla.php?action=actualizar', // Cambia esto a la ruta correcta\n";
    $contenido .= "                        data: $(this).serialize(),\n";
    $contenido .= "                        success: function(response) {\n";
    $contenido .= "                            location.reload(); // Recargar la página para ver los cambios\n";
    $contenido .= "                        },\n";
    $contenido .= "                        error: function(xhr, status, error) {\n";
    $contenido .= "                            console.error(error);\n";
    $contenido .= "                            alert('Error al actualizar el registro.');\n";
    $contenido .= "                        }\n";
    $contenido .= "                    });\n";
    $contenido .= "                });\n";

    // Script para cargar datos en el modal de actualización
    $contenido .= "                $('#modalActualizar').on('show.bs.modal', function(event) {\n";
    $contenido .= "                    var button = $(event.relatedTarget);\n";
    $contenido .= "                    var id = button.data('idActualizar');\n";
    $contenido .= "                    var modal = $(this);\n";
    $contenido .= "                    modal.find('#idActualizar').val(id);\n";

    // Cargar cada campo en el modal
    foreach ($campos as $campo) {
        $contenido .= "                modal.find('#{$campo['Field']}').val(button.data('{$campo['Field']}'));\n";
    }

    $contenido .= "                });\n";

    $contenido .= "            });\n";
    $contenido .= "        </script>\n";

    $contenido .= "    </div>\n";
    $contenido .= "</body>\n";
    $contenido .= "</html>\n";

    $archivo = "$directorio/vista_$tabla.php";
    return file_put_contents($archivo, $contenido) !== false;
}

function generar_vista_css($directorio) {
    $contenido = "   body {
       font-size: 0.9rem; /* Ajustar el tamaño de fuente general */
   }
   .table th, .table td {
       font-size: 0.85rem; /* Ajustar el tamaño de fuente de la tabla */
   }";
    $archivo = "$directorio/estilos.css";
    return file_put_contents($archivo, $contenido) !== false;
}
?>
