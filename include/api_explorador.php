<?php
header('Content-Type: application/json');

// Obtener ruta inicial
$ruta = isset($_POST['ruta']) && !empty($_POST['ruta']) ? $_POST['ruta'] : getcwd();
$ruta = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $ruta);
$ruta = realpath($ruta);

if (!$ruta || !is_dir($ruta)) {
    // Si la ruta no es válida, volver al directorio actual o root
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
        
        $fullPath = $ruta . DIRECTORY_SEPARATOR . $item;
        
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
