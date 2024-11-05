<?php
function generar_controlador($tabla, $campos, $directorio, $archivo_conexion) {
    $nombreClase = ucfirst($tabla);
    $contenido = "<?php\n";
    $contenido .= "require_once '../modelos/modelo_$tabla.php';\n\n";
    $contenido .= "class Controlador$nombreClase {\n";
    $contenido .= "    private \$modelo;\n\n";
    
    // Constructor
    $contenido .= "    public function __construct() {\n";
    $contenido .= "        \$this->modelo = new Modelo$nombreClase();\n";
    $contenido .= "    }\n\n";
    
    // Método para crear un nuevo registro
    $contenido .= "    public function crear(\$datos) {\n";
    $contenido .= "        return \$this->modelo->crear(\$datos);\n";
    $contenido .= "    }\n\n";
    
    // Método para obtener todos los registros
    $contenido .= "    public function obtenerTodos() {\n";
    $contenido .= "        return \$this->modelo->obtenerTodos();\n";
    $contenido .= "    }\n\n";
    
    // Método para obtener un registro por ID
    $contenido .= "    public function obtenerPorId(\$id) {\n";
    $contenido .= "        return \$this->modelo->obtenerPorId(\$id);\n";
    $contenido .= "    }\n\n";
    
    // Método para actualizar un registro
    $contenido .= "    public function actualizar(\$id, \$datos) {\n";
    $contenido .= "        return \$this->modelo->actualizar(\$id, \$datos);\n";
    $contenido .= "    }\n\n";
    
    // Método para eliminar un registro
    $contenido .= "    public function eliminar(\$id) {\n";
    $contenido .= "        return \$this->modelo->eliminar(\$id);\n";
    $contenido .= "    }\n";
    
    $contenido .= "}\n\n";

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

    $contenido .= "    default:\n";
    $contenido .= "        \$registros = \$controlador->obtenerTodos();\n";
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
