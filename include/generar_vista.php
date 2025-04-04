<?php
function generar_vista($tabla, $campos, $directorio, $es_vista) {
    $nombreClase = ucfirst($tabla);
    $contenido =  "<?php\n";
    $contenido .= "/**
 * GeneraCRUDphp
 *
 * es desarrollada para ajilizar el desarrollo de aplicaciones PHP
 * permitir la administracion de tablas creando leer, actualizar, editar y elimar reguistros
 * Desarrollado por Carlos Mejia
 * 2024-12-06
 * Version 0.4.0
 * 
 */\n";
    $contenido .= "require_once '../accesos/verificar_sesion.php';\n";
    $contenido .= "    \$registrosPorPagina = isset(\$_GET['registrosPorPagina']) ? (int)\$_GET['registrosPorPagina'] : 10;\n";
    $contenido .= "    \$paginaActual = isset(\$_GET['pagina']) ? (int)\$_GET['pagina'] : 1;\n";
    $contenido .= "?>\n";
    $contenido .= "<!DOCTYPE html>\n";
    $contenido .= "<html lang=\"es\">\n";
    $contenido .= "<head>\n";
    $contenido .= "    <meta charset=\"UTF-8\">\n";
    $contenido .= "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
    $contenido .= "    <title>$nombreClase - " . ($es_vista ? "Vista" : "Tabla") . "</title>\n";

    $contenido .= "    <?php include('../headIconos.php'); // Incluir los elementos del encabezado iconos?>\n";
    $contenido .= "    <link rel=\"stylesheet\" href=\"../css/estilos.css\">\n";
    $contenido .= "</head>\n";
    $contenido .= "<body>\n";
    $contenido .= "    <div class=\"container\">\n";
    
    // Encabezado con título y botones
    $contenido .= "        <h1 class=\"text-center\">$nombreClase</h1>\n";
    $contenido .= "        <div class=\"d-flex justify-content-between mb-3\">\n";
    
    // Solo mostrar botón de crear si no es una vista
    if (!$es_vista) {
        $contenido .= "            <button type=\"button\" class=\"btn btn-primary icon-plus\" data-bs-toggle=\"modal\" data-bs-target=\"#modalCrear\">\n";
        $contenido .= "                Crear\n";
        $contenido .= "            </button>\n";
    } else {
        $contenido .= "            <div></div>\n"; // Espacio vacío para mantener el layout
    }
    $contenido .= "            <div class=\"btn-group\">\n";
    $contenido .= "                <button class=\"btn btn-success dropdown-toggle icon-export\" type=\"button\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\">\n";
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
    $contenido .= "        <form method=\"GET\" action=\"../controladores/controlador_$tabla.php\" class=\"d-flex mb-3\">\n";
    $contenido .= "            <div class=\"input-group\" style=\"width: 100%;\">\n";
    $contenido .= "                <input type=\"text\" name=\"busqueda\" class=\"form-control\" placeholder=\"Buscar...\" value=\"<?php echo isset(\$_GET['busqueda']) ? htmlspecialchars(\$_GET['busqueda']) : ''; ?>\">\n";
    $contenido .= "                <input type=\"hidden\" name=\"action\" value=\"buscar\">\n";
    $contenido .= "                <input type=\"hidden\" name=\"registrosPorPagina\" value=\"<?= \$registrosPorPagina ?>\">\n";
    $contenido .= "                <input type=\"hidden\" name=\"pagina\" value=\"<?= \$paginaActual ?>\">\n";
   // $contenido .= "                <div class=\"input-group-append\">\n"; 
    $contenido .= "                    <button type=\"submit\" class=\"btn btn-secondary icon-search-outline\"> </button>\n";
    $contenido .= "                    <?php if(isset(\$_GET['busqueda']) && \$_GET['busqueda'] !== ''): ?>\n";
    $contenido .= "                        <a href=\"../controladores/controlador_$tabla.php\" class=\"btn btn-outline-danger icon-eraser\"> </a>  <!-- Aqui boton limpiar si requiere nombre -->\n";
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
    if (!$es_vista) {
        $contenido .= "                    <th>Acciones</th>\n";
    }    
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
    if (!$es_vista) {
        $contenido .= "                        <button type=\"button\" class=\"btn btn-warning icon-edit\" data-bs-toggle=\"modal\" data-bs-target=\"#modalActualizar\" data-idActualizar=\"<?php echo \$registro['" . $campos[0]['Field'] . "']; ?>\"";
        // Agregar atributos data-* para cada campo que se desea cargar
        foreach ($campos as $campo) {
            $contenido .= "\n                           data-" . htmlspecialchars($campo['Field']) . "=\"<?php echo htmlspecialchars(\$registro['" . $campo['Field'] . "']); ?>\"";
        }
        $contenido .= "> </button>  <!-- Boton Editar si requiere nombre aqui se pone -->\n";
        $contenido .= "                        <button class=\"btn btn-danger icon-trash-2\" onclick=\"eliminar('<?php echo htmlspecialchars(\$registro['" . $campos[0]['Field'] . "']); ?>')\"> </button>  <!-- Boton Eliminar si requiere nombre aqui se pone -->\n";
    }
    $contenido .= "                </tr>\n";
    $contenido .= "                <?php endforeach; else: ?>\n";
    $contenido .= "                <tr><td colspan=\"" . (count($campos) + 1) . "\">No hay registros disponibles.</td></tr>\n";
    $contenido .= "                <?php endif; ?>\n";
    $contenido .= "            </tbody>\n";
    $contenido .= "        </table>\n";

    // 1. Agregar un formulario para seleccionar el número de registros por página
    $contenido .= "        <div class=\"mb-3\">\n";
    $contenido .= "            <form method=\"GET\" class=\"d-flex\">\n";
    $contenido .= "                <label for=\"registrosPorPagina\" class=\"mr-2\">Registros por página:</label>\n";
    $contenido .= "                <select id=\"registrosPorPagina\" name=\"registrosPorPagina\" class=\"form-control mr-2\" onchange=\"this.form.submit()\">\n";

// Opciones del select
foreach ([15, 30, 50, 100] as $opcion) {
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
    $contenido .= "        <div class=\"modal fade\" id=\"modalCrear\" tabindex=\"-1\" aria-labelledby=\"modalCrearLabel\" aria-hidden=\"true\">\n";
    $contenido .= "            <div class=\"modal-dialog modal-lg\">\n";
    $contenido .= "                <div class=\"modal-content\">\n";
    $contenido .= "                    <div class=\"modal-header\">\n";
    $contenido .= "                        <h5 class=\"modal-title\" id=\"modalCrearLabel\">Crear $nombreClase</h5>\n";
    $contenido .= "                        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\"></button>\n";
    $contenido .= "                    </div>\n";
    $contenido .= "                    <div class=\"modal-body\">\n";
    $contenido .= "                        <form id=\"formCrear\" method=\"post\">\n";

    // Campos del formulario de creación
    // Inicializar el contador
    $contador = 0;

    // Filtrar los campos válidos
    $camposValidos = array_filter($campos, function($campo) {
        return !($campo['Extra'] == 'auto_increment' || 
                 $campo['Extra'] == 'on update current_timestamp()' || 
                 $campo['Key'] == 'PRI' || 
                 $campo['Default'] == 'current_timestamp()');
    });
   
    // Mostrar contenido del array $camposValidos en log de php
    //error_log(print_r($camposValidos, true));

    // Iterar sobre los campos válidos
    foreach ($camposValidos as $index => $campo) {
        // Incrementar el contador por cada campo válido
        $contador++;

        // Aquí puedes usar el contador para determinar si debes crear una nueva fila
        if ($contador % 2 == 1) {    
            $contenido .= "                            <div class=\"row\">\n"; // Abrir nueva fila
        }

        $contenido .= "                                <div class=\"col-md-6 mb-3\">\n"; // Cambiado a col-md-6
        $contenido .= "                                    <label for=\"{$campo['Field']}\">" . htmlspecialchars($campo['Field']) . ":</label>\n";
        if (strpos($campo['Type'], 'date') !== false || strpos($campo['Type'], 'datetime') !== false) {
            $contenido .= "                                     <input type=\"date\" class=\"form-control\" id=\"{$campo['Field']}\" name=\"{$campo['Field']}\"" . ($campo['Null'] == 'NO' ? ' required' : '') . ">\n";
        } elseif (strpos($campo['Type'], 'int') !== false || strpos($campo['Type'], 'float') !== false) {
            $contenido .= "                                     <input type=\"number\" class=\"form-control\" id=\"{$campo['Field']}\" name=\"{$campo['Field']}\"" . ($campo['Null'] == 'NO' ? ' required' : '') . ">\n";
        } else {
            $contenido .= "                                     <input type=\"text\" class=\"form-control\" id=\"{$campo['Field']}\" name=\"{$campo['Field']}\"" . ($campo['Null'] == 'NO' ? ' required' : '') . ">\n";
        }
        $contenido .= "                                </div>\n";

        // Cerrar la fila después de dos campos o si es el último
        if ($index % 2 == 0 || $index == count($camposValidos)) { 
            $contenido .= "                            </div>\n"; // Cerrar fila
        }
    }

    $contenido .= "                            <button type=\"submit\" class=\"btn btn-primary icon-ok-2\">Crear</button>\n";
    $contenido .= "                        </form>\n";
    $contenido .= "                    </div>\n";
    $contenido .= "                </div>\n";
    $contenido .= "            </div>\n";
    $contenido .= "        </div>\n";

    // Modal para actualizar
    $contenido .= "        <div class=\"modal fade\" id=\"modalActualizar\" tabindex=\"-1\" aria-labelledby=\"modalActualizarLabel\" aria-hidden=\"true\">\n";
    $contenido .= "            <div class=\"modal-dialog modal-lg\" >\n";
    $contenido .= "                <div class=\"modal-content\">\n";

    $contenido .= "                <div class=\"modal-body\">\n";
    $contenido .= "                    <form id=\"formActualizar\" method=\"post\">\n";
    // Campos del formulario de actualización

    $contador= 0;
    foreach ($campos as $index => $campo) {
        if ($campo['Key'] == 'PRI') {
            $contenido .= "                     <div class=\"modal-header\">\n";
            $contenido .= "                         <div class=\"row\">\n";
            $contenido .= "                             <div class=\"form-group col-md-8\">\n";
            $contenido .= "                               <h5 class=\"modal-title\">Actualizar $nombreClase - ID: </h5>\n";
            $contenido .= "                             </div>\n";
            $contenido .= "                             <div class=\"form-group col-md-3\">\n";
            $contenido .= "                                <div class=\"form-group mb-0 d-flex align-items-center\">\n";
            $contenido .= "                                    <input type=\"text\" class=\"form-control\" id=\"{$campo['Field']}\" name=\"{$campo['Field']}\" value=\"<?php echo htmlspecialchars(\$registro['{$campo['Field']}']); ?>\" readonly>\n";
            $contenido .= "                                </div>\n";  
            $contenido .= "                             </div>\n";  
            $contenido .= "                         </div>\n"; 
            $contenido .= "                         <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\" aria-label=\"Close\">\n";
            $contenido .= "                         </button>\n";
            $contenido .= "                     </div>\n";
        }  else {
            if ($campo['Default'] != 'current_timestamp()' && $campo['Extra'] != 'on update current_timestamp()') { // Excluimos CURRENT_TIMESTAMP
                $contador++;
                if ($contador % 2 == 1) { // Cada dos campos en una fila
                    $contenido .= "                            <div class=\"row\">\n";
                }
                $contenido .= "                                 <div class=\"col-md-6 mb-3\">\n"; // Cambiado a col-md-6
                $contenido .= "                                     <label for=\"{$campo['Field']}\">" . htmlspecialchars($campo['Field']) . ":</label>\n";
                if (strpos($campo['Type'], 'date') !== false || strpos($campo['Type'], 'datetime') !== false) {
                    $contenido .= "                                     <input type=\"date\" class=\"form-control\" id=\"{$campo['Field']}\" name=\"{$campo['Field']}\"" . ($campo['Null'] == 'NO' ? ' required' : '') . ">\n";
                } elseif (strpos($campo['Type'], 'int') !== false || strpos($campo['Type'], 'float') !== false) {
                    $contenido .= "                                     <input type=\"number\" class=\"form-control\" id=\"{$campo['Field']}\" name=\"{$campo['Field']}\"" . ($campo['Null'] == 'NO' ? ' required' : '') . ">\n";
                } else {
                    $contenido .= "                                     <input type=\"text\" class=\"form-control\" id=\"{$campo['Field']}\" name=\"{$campo['Field']}\"" . ($campo['Null'] == 'NO' ? ' required' : '') . ">\n";
                }
            $contenido .= "                                </div>\n"; // cierre la class a col-md-6
          
            if ($contador % 2 == 0 && $index != count($camposValidos)) { // Cerrar la fila después de dos campos o si es el último
                $contenido .= "                            </div>  <!-- Par no fin de registros  -->\n";
            }    
            if (  $index == count($camposValidos)) { // Cerrar la fila después de dos campos o si es el último    
                $contenido .= "                            </div> <!--  fin de registros  --> \n";
            }

            }
        }
    } // cierra el foreach
   // $contenido .= "                            </div>  <!-- Adicional  -->\n";
    $contenido .= "                                 <input type=\"hidden\" id=\"idActualizar\" name=\"idActualizar\">\n";
    $contenido .= "                                 <button type=\"submit\" class=\"btn btn-warning icon-ok-2\">Actualizar</button>\n";
    $contenido .= "                    </form>\n";
    $contenido .= "                 </div>\n";
    $contenido .= "             </div>\n";
    $contenido .= "         </div>\n";
    $contenido .= "     </div>\n";

    $contenido .= "    <!-- Scripts necesarios -->\n";
    $contenido .= "    <script src=\"https://code.jquery.com/jquery-3.7.1.min.js\"></script>\n";
    $contenido .= "    <script src=\"https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js\"></script>\n";
    $contenido .= "    <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js\"></script>\n\n";

    // Agregar el script de inicialización de modales
    $contenido .= "    <script>\n";
    $contenido .= "        document.addEventListener('DOMContentLoaded', function() {\n";
    $contenido .= "            // Inicializar todos los modales\n";
    $contenido .= "            var myModalCreate = new bootstrap.Modal(document.getElementById('modalCrear'));\n";
    $contenido .= "            var myModalUpdate = new bootstrap.Modal(document.getElementById('modalActualizar'));\n\n";

    $contenido .= "            // Manejador para el botón crear\n";
    $contenido .= "            document.querySelector('[data-bs-target=\"#modalCrear\"]').addEventListener('click', function() {\n";
    $contenido .= "                myModalCreate.show();\n";
    $contenido .= "            });\n\n";

    $contenido .= "            // Manejador del formulario crear\n";
    $contenido .= "            document.getElementById('formCrear').addEventListener('submit', function(e) {\n";
    $contenido .= "                e.preventDefault();\n";
    $contenido .= "                const formData = new FormData(this);\n";
    $contenido .= "                fetch('../controladores/controlador_$tabla.php?action=crear', {\n";
    $contenido .= "                    method: 'POST',\n";
    $contenido .= "                    body: new URLSearchParams(formData)\n";
    $contenido .= "                })\n";
    $contenido .= "                .then(response => response.json())\n";
    $contenido .= "                .then(data => {\n";
    $contenido .= "                    if(data) {\n";
    $contenido .= "                        myModalCreate.hide();\n";
    $contenido .= "                        location.reload();\n";
    $contenido .= "                    } else {\n";
    $contenido .= "                        alert('Error al crear el registro.');\n";
    $contenido .= "                    }\n";
    $contenido .= "                })\n";
    $contenido .= "                .catch(error => {\n";
    $contenido .= "                    console.error('Error:', error);\n";
    $contenido .= "                    alert('Error al procesar la solicitud.');\n";
    $contenido .= "                });\n";
    $contenido .= "            });\n";

    // Agregar debug para verificar la inicialización
    $contenido .= "            console.log('Modal crear:', document.getElementById('modalCrear'));\n";
    $contenido .= "            console.log('Botón crear:', document.querySelector('[data-bs-target=\"#modalCrear\"]'));\n";

    $contenido .= "        });\n";
    $contenido .= "    </script>\n";

    // Actualizar el script para manejar el modal de actualización
    $contenido .= "    <script>\n";
    $contenido .= "        document.addEventListener('DOMContentLoaded', function() {\n";
    $contenido .= "            // Inicializar el modal de actualización\n";
    $contenido .= "            var modalActualizar = new bootstrap.Modal(document.getElementById('modalActualizar'));\n\n";

    $contenido .= "            // Manejar el evento show.bs.modal\n";
    $contenido .= "            document.getElementById('modalActualizar').addEventListener('show.bs.modal', function(event) {\n";
    $contenido .= "                // Botón que activó el modal\n";
    $contenido .= "                var button = event.relatedTarget;\n";
    $contenido .= "                var modal = this;\n\n";

    // Agregar código para cargar los datos en el formulario
    foreach ($campos as $campo) {
        $contenido .= "                // Cargar {$campo['Field']}\n";
        $contenido .= "                var valor{$campo['Field']} = button.getAttribute('data-{$campo['Field']}');\n";
        $contenido .= "                if(modal.querySelector('#{$campo['Field']}')) {\n";
        $contenido .= "                    modal.querySelector('#{$campo['Field']}').value = valor{$campo['Field']};\n";
        $contenido .= "                }\n";
    }

    $contenido .= "            });\n\n";

    // Manejar el envío del formulario de actualización
    $contenido .= "            document.getElementById('formActualizar').addEventListener('submit', function(e) {\n";
    $contenido .= "                e.preventDefault();\n";
    $contenido .= "                const formData = new FormData(this);\n";
    $contenido .= "                fetch('../controladores/controlador_$tabla.php?action=actualizar', {\n";
    $contenido .= "                    method: 'POST',\n";
    $contenido .= "                    body: new URLSearchParams(formData)\n";
    $contenido .= "                })\n";
    $contenido .= "                .then(response => response.json())\n";
    $contenido .= "                .then(data => {\n";
    $contenido .= "                    if(data) {\n";
    $contenido .= "                        modalActualizar.hide();\n";
    $contenido .= "                        location.reload();\n";
    $contenido .= "                    } else {\n";
    $contenido .= "                        alert('Error al actualizar el registro.');\n";
    $contenido .= "                    }\n";
    $contenido .= "                })\n";
    $contenido .= "                .catch(error => {\n";
    $contenido .= "                    console.error('Error:', error);\n";
    $contenido .= "                    alert('Error al procesar la solicitud.');\n";
    $contenido .= "                });\n";
    $contenido .= "            });\n\n";

    // Agregar logs de depuración
    $contenido .= "            console.log('Modal actualizar:', document.getElementById('modalActualizar'));\n";
    $contenido .= "            console.log('Botones actualizar:', document.querySelectorAll('[data-bs-target=\"#modalActualizar\"]'));\n";

    $contenido .= "        });\n";
    $contenido .= "    </script>\n";

    // Función eliminar con confirmación
    $contenido .= "        <script>\n";
    $contenido .= "            // Función para eliminar registros con confirmación\n";
    $contenido .= "            function eliminar(id) {\n";
    $contenido .= "                if (confirm('¿Estás seguro de que deseas eliminar este registro?')) {\n";
    $contenido .= "                    // Realizar la petición de eliminación\n";
    $contenido .= "                    fetch('../controladores/controlador_$tabla.php?action=eliminar', {\n";
    $contenido .= "                        method: 'POST',\n";
    $contenido .= "                        headers: {\n";
    $contenido .= "                            'Content-Type': 'application/x-www-form-urlencoded',\n";
    $contenido .= "                        },\n";
    $contenido .= "                        body: 'id=' + encodeURIComponent(id)\n";
    $contenido .= "                    })\n";
    $contenido .= "                    .then(response => {\n";
    $contenido .= "                        if (!response.ok) {\n";
    $contenido .= "                            throw new Error('Error en la respuesta del servidor');\n";
    $contenido .= "                        }\n";
    $contenido .= "                        return response.json();\n";
    $contenido .= "                    })\n";
    $contenido .= "                    .then(data => {\n";
    $contenido .= "                        if (data) {\n";
    $contenido .= "                            // Si la eliminación fue exitosa, recargar la página\n";
    $contenido .= "                            location.reload();\n";
    $contenido .= "                        } else {\n";
    $contenido .= "                            // Si hubo un error en la eliminación\n";
    $contenido .= "                            alert('Error al eliminar el registro.');\n";
    $contenido .= "                        }\n";
    $contenido .= "                    })\n";
    $contenido .= "                    .catch(error => {\n";
    $contenido .= "                        // Manejo de errores en la petición\n";
    $contenido .= "                        console.error('Error:', error);\n";
    $contenido .= "                        alert('Error al eliminar el registro: ' + error.message);\n";
    $contenido .= "                    });\n";
    $contenido .= "                }\n";
    $contenido .= "            }\n";

    // Opcional: Agregar una función para mostrar mensajes de confirmación más estilizados con Bootstrap
    $contenido .= "            // Función para mostrar mensajes de confirmación estilizados\n";
    $contenido .= "            function mostrarMensaje(mensaje, tipo = 'success') {\n";
    $contenido .= "                const alertPlaceholder = document.createElement('div');\n";
    $contenido .= "                alertPlaceholder.innerHTML = `\n";
    $contenido .= "                    <div class=\"alert alert-\${tipo} alert-dismissible fade show\" role=\"alert\">\n";
    $contenido .= "                        \${mensaje}\n";
    $contenido .= "                        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>\n";
    $contenido .= "                    </div>\n";
    $contenido .= "                `;\n";
    $contenido .= "                document.querySelector('.container').insertBefore(alertPlaceholder, document.querySelector('.container').firstChild);\n";
    $contenido .= "                // Remover el mensaje después de 3 segundos\n";
    $contenido .= "                setTimeout(() => {\n";
    $contenido .= "                    alertPlaceholder.remove();\n";
    $contenido .= "                }, 3000);\n";
    $contenido .= "            }\n";

    $contenido .= "        </script>\n";

    // Asegurarse de que los estilos del modal estén correctos
    $contenido .= "    <style>\n";
    $contenido .= "        .modal-backdrop {\n";
    $contenido .= "            z-index: 1040;\n";
    $contenido .= "        }\n";
    $contenido .= "        .modal {\n";
    $contenido .= "            z-index: 1050;\n";
    $contenido .= "        }\n";
    $contenido .= "    </style>\n";

    // Inicialización de dropdowns de Bootstrap 5
    $contenido .= "        <script>\n";
    $contenido .= "            // Inicializar todos los dropdowns\n";
    $contenido .= "            document.addEventListener('DOMContentLoaded', function() {\n";
    $contenido .= "                var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));\n";
    $contenido .= "                var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {\n";
    $contenido .= "                    return new bootstrap.Dropdown(dropdownToggleEl);\n";
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
