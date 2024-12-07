<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 menu-fondo">
                <h2 class="text-center">Menu Principal</h2>
                <div class="accordion accordion-flush" id="accordionFlushExample">
                    <?php
                    require_once '../modelos/modelo_menu_principal.php';
                    $modelo = new ModeloMenu();
                    $modulos = $modelo->obtenerModulos(); // Método que obtiene los módulos

                    foreach ($modulos as $index => $modulo): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse<?php echo $index; ?>" aria-expanded="false" aria-controls="flush-collapse<?php echo $index; ?>">
                                    <strong><?php echo htmlspecialchars($modulo['modulo']); ?></strong>
                                </button>
                            </h2>
                            <div id="flush-collapse<?php echo $index; ?>" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                                <ul class="nested-nav">
                                    <?php
                                    // Obtener los menús para el módulo actual
                                    $menus = $modelo->obtenerMenusPorModulo($modulo['modulo']); 
                                    if (empty($menus)): ?>
                                        <li>No hay menús disponibles para este módulo.</li>
                                    <?php else: 
                                        foreach ($menus as $menu): 
                                            $ruta = rtrim($menu['ruta_programa'], '/');
                                            $nombrePrograma = isset($menu['nombre_programaPHP']) ? $menu['nombre_programaPHP'] : 'programa_no_definido'; // Manejo de error
                                            $url = $ruta . '/' . $nombrePrograma;
                                            ?>
                                            <li>
                                                <a href="<?php echo htmlspecialchars($url); ?>" target="iframeTrabajo">
                                                    <?php echo htmlspecialchars($menu['nombre_menu']); ?>
                                                </a>
                                            </li>
                                        <?php endforeach; 
                                    endif; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-md-10">
                <iframe name="iframeTrabajo" style="width: 105%; height: 600px;"></iframe>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>