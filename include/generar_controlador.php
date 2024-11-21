<?php
function generar_controlador($tabla, $campos, $directorio, $archivo_conexion, $es_vista) {
    $nombreClase = ucfirst($tabla);
    $contenido = "<?php\n";
    $contenido .= "require_once '../modelos/modelo_$tabla.php';\n\n";
    $contenido .= "class Controlador$nombreClase {\n";
    $contenido .= "    private \$modelo;\n";
    $contenido .= "    private \$es_vista;\n\n";
    
    // Constructor
    $contenido .= "    public function __construct() {\n";
    $contenido .= "        \$this->modelo = new Modelo$nombreClase();\n";
    $contenido .= "        \$this->es_vista = " . ($es_vista ? 'true' : 'false') . ";\n";
    $contenido .= "    }\n\n";
    
    
    if (!$es_vista) {
        // Método para crear un nuevo registro    
        $contenido .= "    public function crear(\$datos) {\n";
        $contenido .= "        return \$this->modelo->crear(\$datos);\n";
        $contenido .= "    }\n\n";

        // Método para actualizar un registro
        $contenido .= "    public function actualizar(\$id, \$datos) {\n";
        $contenido .= "        return \$this->modelo->actualizar(\$id, \$datos);\n";
        $contenido .= "    }\n\n";
        
        // Método para eliminar un registro
        $contenido .= "    public function eliminar(\$id) {\n";
        $contenido .= "        return \$this->modelo->eliminar(\$id);\n";
        $contenido .= "    }\n\n";
    }
    // Método para obtener todos los registros
    $contenido .= "    public function obtenerTodos(\$registrosPorPagina, \$pagina, \$busqueda = '') {\n";
    $contenido .= "        \$offset = (\$pagina - 1) * \$registrosPorPagina;\n";
    $contenido .= "        return \$this->modelo->obtenerTodos(\$registrosPorPagina, \$offset, \$busqueda);\n"; // Modificado para incluir búsqueda
    $contenido .= "    }\n\n";
    
    // Método para obtener un registro por ID
    $contenido .= "    public function obtenerPorId(\$id) {\n";
    $contenido .= "        return \$this->modelo->obtenerPorId(\$id);\n";
    $contenido .= "    }\n\n";
    
    // Método para buscar registros
    $contenido .= "    public function buscar(\$termino, \$registrosPorPagina, \$offset) {\n";
    $contenido .= "        return \$this->modelo->buscar(\$termino, \$registrosPorPagina, \$offset);\n";
    $contenido .= "    }\n\n";

    // Método para contar registros encontrados en la busqueda
    $contenido .= "    public function contarRegistrosPorBusqueda(\$termino) {\n";
    $contenido .= "         return \$this->modelo->contarRegistrosPorBusqueda(\$termino);\n";
    $contenido .= "    }\n\n"; // fin unction contarRegistrosPorBusqueda
    
    // funcion exportar en formatos Exel, CSV, TXT
    // Agregar los nuevos métodos de exportación
    $contenido .= "    public function exportar(\$formato) {\n";
    $contenido .= "        try {\n";
    $contenido .= "            \$termino = \$_GET['busqueda'] ?? '';\n";
    $contenido .= "            \$datos = \$this->modelo->exportarDatos(\$termino);\n";
    $contenido .= "            if (\$datos === false) {\n";
    $contenido .= "                throw new Exception('Error al obtener los datos para exportar');\n";
    $contenido .= "            }\n";
    $contenido .= "            if (empty(\$datos)) {\n";
    $contenido .= "                throw new Exception('No hay datos para exportar');\n";
    $contenido .= "            }\n\n";
    $contenido .= "            \$timestamp = date('Y-m-d_H-i-s');\n";
    $contenido .= "            \$filename = \"{$tabla}_export_{\$timestamp}\";\n";
    $contenido .= "            if (!empty(\$termino)) {\n";
    $contenido .= "                \$filename .= \"_busqueda_\" . preg_replace('/[^a-zA-Z0-9]/', '_', \$termino);\n";
    $contenido .= "            }\n\n";
    $contenido .= "            switch (\$formato) {\n";
    $contenido .= "                case 'excel':\n";
    $contenido .= "                    header('Content-Type: application/vnd.ms-excel');\n";
    $contenido .= "                    header(\"Content-Disposition: attachment; filename=\\\"\$filename.xls\\\"\");\n";
    $contenido .= "                    \$this->exportarExcel(\$datos);\n";
    $contenido .= "                    break;\n\n";
    $contenido .= "                case 'csv':\n";
    $contenido .= "                    header('Content-Type: text/csv; charset=utf-8');\n";
    $contenido .= "                    header(\"Content-Disposition: attachment; filename=\\\"\$filename.csv\\\"\");\n";
    $contenido .= "                    \$this->exportarCSV(\$datos);\n";
    $contenido .= "                    break;\n\n";
    $contenido .= "                case 'txt':\n";
    $contenido .= "                    header('Content-Type: text/plain; charset=utf-8');\n";
    $contenido .= "                    header(\"Content-Disposition: attachment; filename=\\\"\$filename.txt\\\"\");\n";
    $contenido .= "                    \$this->exportarTXT(\$datos);\n";
    $contenido .= "                    break;\n";
    $contenido .= "                default:\n";
    $contenido .= "                    throw new Exception('Formato de exportación no válido');\n";
    $contenido .= "            }\n";
    $contenido .= "        } catch (Exception \$e) {\n";
    $contenido .= "            error_log('Error en exportación: ' . \$e->getMessage());\n";
    $contenido .= "            echo 'Error: ' . \$e->getMessage();\n";
    $contenido .= "            exit;\n";
    $contenido .= "        }\n";
    $contenido .= "        exit;\n";
    $contenido .= "    }\n\n"; // fin funcion exportar

    $contenido .= "    private function exportarExcel(\$datos) {\n";
    $contenido .= "        echo \"<table border='1'>\\n\";\n";
    $contenido .= "        if (!empty(\$datos)) {\n";
    $contenido .= "            echo \"<tr>\\n\";\n";
    $contenido .= "            foreach (array_keys(\$datos[0]) as \$campo) {\n";
    $contenido .= "                echo \"<th>\" . htmlspecialchars(\$campo) . \"</th>\\n\";\n";
    $contenido .= "            }\n";
    $contenido .= "            echo \"</tr>\\n\";\n";
    $contenido .= "        }\n";
    $contenido .= "        foreach (\$datos as \$fila) {\n";
    $contenido .= "            echo \"<tr>\\n\";\n";
    $contenido .= "            foreach (\$fila as \$valor) {\n";
    $contenido .= "                echo \"<td>\" . htmlspecialchars(\$valor) . \"</td>\\n\";\n";
    $contenido .= "            }\n";
    $contenido .= "            echo \"</tr>\\n\";\n";
    $contenido .= "        }\n";
    $contenido .= "        echo \"</table>\";\n";
    $contenido .= "    }\n\n";

    $contenido .= "    private function exportarCSV(\$datos) {\n";
    $contenido .= "        \$output = fopen('php://output', 'w');\n";
    $contenido .= "        if (!empty(\$datos)) {\n";
    $contenido .= "            fputcsv(\$output, array_keys(\$datos[0]));\n";
    $contenido .= "        }\n";
    $contenido .= "        foreach (\$datos as \$fila) {\n";
    $contenido .= "            fputcsv(\$output, \$fila);\n";
    $contenido .= "        }\n";
    $contenido .= "        fclose(\$output);\n";
    $contenido .= "    }\n\n";

    $contenido .= "    private function exportarTXT(\$datos) {\n";
    $contenido .= "        if (!empty(\$datos)) {\n";
    $contenido .= "            echo implode(\"\\t\", array_keys(\$datos[0])) . \"\\n\";\n";
    $contenido .= "            foreach (\$datos as \$fila) {\n";
    $contenido .= "                echo implode(\"\\t\", \$fila) . \"\\n\";\n";
    $contenido .= "            }\n";
    $contenido .= "        }\n";
    $contenido .= "    }\n\n";

    $contenido .= "}\n\n"; // fin class

    // Manejo de las acciones
    $contenido .= "\$accion = \$_GET['action'] ?? '';\n";
    $contenido .= "\$controlador = new Controlador$nombreClase();\n\n";

    $contenido .= "switch (\$accion) {\n";
    $contenido .= "    case 'crear':\n";
    $contenido .= "        \$datos = \$_POST;\n";
    $contenido .= "        \$resultado = \$controlador->crear(\$datos);\n";
    $contenido .= "        echo json_encode(\$resultado);\n";
    $contenido .= "        break;\n\n";

    $contenido .= "    case 'actualizar':\n";
    $contenido .= "        \$id = \$_POST['" . getPrimaryKey($campos) . "']; // Usar el campo de llave primaria\n"; // Cambia 'user_id' por el campo de llave primaria
    $contenido .= "        \$datos = \$_POST;\n";
    $contenido .= "        unset(\$datos['" . getPrimaryKey($campos) . "']); // Eliminar el ID de los datos\n"; // Cambia 'user_id' por el campo de llave primaria
    $contenido .= "        \$resultado = \$controlador->actualizar(\$id, \$datos);\n";
    $contenido .= "        echo json_encode(\$resultado);\n";
    $contenido .= "        break;\n\n";

    $contenido .= "    case 'eliminar':\n";
    $contenido .= "        \$id = \$_POST['id'];\n"; // Aquí puedes mantener 'id' si es un campo genérico
    $contenido .= "        \$resultado = \$controlador->eliminar(\$id);\n";
    $contenido .= "        echo json_encode(\$resultado);\n";
    $contenido .= "        break;\n\n";

    $contenido .= "    case 'buscar':\n"; // Nueva acción para buscar
    $contenido .= "        \$termino = \$_GET['busqueda'] ?? '';\n"; // Obtener el término de búsqueda
    $contenido .= "        \$registrosPorPagina = \$_GET['registrosPorPagina'] ?? 10; // Número de registros por página\n";
    $contenido .= "        \$paginaActual = \$_GET['pagina'] ?? 1; // Página actual\n";
    $contenido .= "        \$offset = (\$paginaActual - 1) * \$registrosPorPagina; // Calcular el offset\n";
    $contenido .= "        \$totalRegistros = \$controlador->contarRegistrosPorBusqueda(\$termino); // Contar registros que coinciden con la búsqueda\n";
    $contenido .= "        \$totalPaginas = ceil(\$totalRegistros / \$registrosPorPagina); // Calcular total de páginas\n";       
    $contenido .= "        \$resultado = \$controlador->buscar(\$termino, \$registrosPorPagina, \$offset);\n"; // Llamar al método de búsqueda\n";
  //  $contenido .= "        echo json_encode(\$resultado);\n"; // Devolver el resultado en formato JSON\n";
    $contenido .= "        // Aquí debes incluir la vista con los resultados\n";
    $contenido .= "        include '../vistas/vista_$tabla.php'; // Incluir la vista correspondiente\n"; // Asegúrate de que la vista esté en la ruta correcta
    $contenido .= "        break;\n\n";

    // accion de exportar
    $contenido .= "    case 'exportar':\n";
    $contenido .= "        \$formato = \$_GET['formato'] ?? 'excel';\n";
    $contenido .= "        \$controlador->exportar(\$formato);\n";
    $contenido .= "        break;\n\n";

    $contenido .= "    default:\n";
    $contenido .= "        \$registrosPorPagina = (int)(\$_GET['registrosPorPagina'] ?? 10); // Asegúrate de que sea un entero\n"; // Asegúrate de que sea un entero
    $contenido .= "        \$paginaActual = (int)(\$_GET['pagina'] ?? 1); // Asegúrate de que sea un entero\n"; // Asegúrate de que sea un entero
    $contenido .= "        \$offset = (\$paginaActual - 1) * \$registrosPorPagina; // Calcular el offset\n"; // Asegúrate de que ambas variables sean enteros
    $contenido .= "        \$registros = \$controlador->obtenerTodos(\$registrosPorPagina, \$paginaActual);\n"; // Llama a la función con los parámetros
    $contenido .= "        include '../vistas/vista_$tabla.php'; // Incluir la vista correspondiente\n"; // Asegúrate de que la vista esté en la ruta correcta
    $contenido .= "        break;\n";
    $contenido .= "}\n";

    $archivo = "$directorio/controlador_$tabla.php";
    return file_put_contents($archivo, $contenido) !== false;
}

// Función para obtener el campo de llave primaria
function getPrimaryKey($campos) {
    foreach ($campos as $campo) {
        if ($campo['Key'] === 'PRI') {
            return $campo['Field'];
        }
    }
    return null; // Retorna null si no se encuentra una llave primaria
}
?>