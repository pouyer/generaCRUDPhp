<?php
header('Content-Type: application/json');

// Obtener ruta inicial
$ruta = isset($_POST['ruta']) && !empty($_POST['ruta']) ? $_POST['ruta'] : getcwd();
$ruta = str_replace(['/', '\\'], '/', $ruta);
$real_path = realpath($ruta);

if ($real_path && is_dir($real_path)) {
    $ruta = $real_path;
} elseif (!is_dir($ruta)) {
    // Si la ruta no es válida ni existe, volver al directorio actual
    $ruta = getcwd();
}

$response = [
    'ruta_actual' => $ruta,
    'directorios' => [],
    'error' => null
];

try {
    // Escanear directorio
    $items = scandir($ruta);
    $directorios = [];

    // Agregar padre si no estamos en la raíz (simple heuristic)
    // En windows la raíz es C:\ o D:\
    if (dirname($ruta) !== $ruta) {
        $directorios[] = [
            'nombre' => '.. (Subir nivel)',
            'ruta' => dirname($ruta),
            'tipo' => 'padre'
        ];
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $fullPath = $ruta . '/' . $item;
        
        if (is_dir($fullPath)) {
            $directorios[] = [
                'nombre' => $item,
                'ruta' => $fullPath,
                'tipo' => 'carpeta'
            ];
        }
    }

    $response['directorios'] = $directorios;

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>
