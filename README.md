# generaCRUDPhp
Generador de codigo PHP para crear CRUD de tablas en Mysql. 
usando MVC y visualizacion MODAL.

cada tabla que crea su CRUD queda en la sigiente estructura:
```
 X:\XAMPP\HTDOCS\MIPROYECTO
        │   conexion.php
        ├───controladores
        │ controlador_<nombre_tabla>.php
        ├───css
        │ estilos.css
        ├───modelos
        │ modelo_<nombre_tabla>.php
        └───vistas
        | vista_<nombre_tabla>.php
        
```
cada tabla queda con :
1. paginacion
2. Opcion Buscar por cualquier campo.
3. Opcion de exportar en exel,csv,txt

el programa principal de cada tabla es el que queda en la carpeta vistas.
