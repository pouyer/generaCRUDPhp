<?php
session_start();
require_once '../modelos/modelo_acc_log.php';

class ControladorAcc_log {
    private $modelo;

    public function __construct() {
        $this->modelo = new ModeloAcc_log();
    }

    public function exportar($formato) {
        try {
            $termino = $_GET['busqueda'] ?? '';
            $datos = $this->modelo->exportarDatos($termino);
            
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "log_acciones_{$timestamp}";

            switch ($formato) {
                case 'excel':
                    header('Content-Type: application/vnd.ms-excel');
                    header("Content-Disposition: attachment; filename=\"$filename.xls\"");
                    $this->exportarExcel($datos);
                    break;
                case 'csv':
                    header('Content-Type: text/csv; charset=utf-8');
                    header("Content-Disposition: attachment; filename=\"$filename.csv\"");
                    $this->exportarCSV($datos);
                    break;
                case 'txt':
                    header('Content-Type: text/plain; charset=utf-8');
                    header("Content-Disposition: attachment; filename=\"$filename.txt\"");
                    $this->exportarTXT($datos);
                    break;
            }
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
        exit;
    }

    private function exportarExcel($datos) {
        if (!empty($datos)) {
            $columnas = array_keys($datos[0]);
            $salida = implode("\t", $columnas) . "\n";
            foreach ($datos as $fila) {
                $salida .= implode("\t", $fila) . "\n";
            }
            echo "\xFF\xFE";
            echo mb_convert_encoding($salida, 'UTF-16LE', 'UTF-8');
        }
    }

    private function exportarCSV($datos) {
        echo "\xEF\xBB\xBF";
        $output = fopen('php://output', 'w');
        if (!empty($datos)) fputcsv($output, array_keys($datos[0]));
        foreach ($datos as $fila) fputcsv($output, $fila);
        fclose($output);
    }

    private function exportarTXT($datos) {
        echo "\xEF\xBB\xBF";
        if (!empty($datos)) {
            echo implode("\t", array_keys($datos[0])) . "\n";
            foreach ($datos as $fila) echo implode("\t", $fila) . "\n";
        }
    }
}

$accion = $_GET['action'] ?? '';
$controlador = new ControladorAcc_log();

if ($accion === 'exportar') {
    $controlador->exportar($_GET['formato'] ?? 'excel');
} else {
    // Para búsqueda y visualización normal
    include '../vistas/vista_acc_log.php';
}
?>
