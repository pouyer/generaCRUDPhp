<?php
// Detectar ruta base para iconos-web de forma robusta
if (file_exists(__DIR__ . '/iconos-web')) {
    // Caso: Proyecto generado o Raíz del generador
    $prefix = (strpos($_SERVER['PHP_SELF'], '/vistas/') !== false || strpos($_SERVER['PHP_SELF'], '/roles_programas/') !== false) ? '../' : './';
} else {
    // Caso: Subcarpetas donde iconos-web está un nivel arriba
    $prefix = (strpos($_SERVER['PHP_SELF'], '/vistas/') !== false || strpos($_SERVER['PHP_SELF'], '/roles_programas/') !== false) ? '../../' : '../';
}
?>
<!-- headIconos.php -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

<!-- Incluir estilos de iconos Fontello -->
<link href="<?= $prefix ?>iconos-web/css/fontello.css" rel="stylesheet" type="text/css">
<link href="<?= $prefix ?>iconos-web/css/fontello-embedded.css" rel="stylesheet" type="text/css">
<link href="<?= $prefix ?>iconos-web/css/animation.css" rel="stylesheet" type="text/css">
<link href="<?= $prefix ?>iconos-web/css/fontello-codes.css" rel="stylesheet" type="text/css">


<!-- Otros estilos o scripts que necesites -->
