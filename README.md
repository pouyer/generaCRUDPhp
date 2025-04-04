# generaCRUDPhp

**generaCRUDPhp** es un generador de código PHP diseñado para crear CRUDs (Create, Read, Update, Delete) de tablas en MySQL. Utiliza el patrón de diseño **MVC** (Modelo-Vista-Controlador) y visualización mediante **modales** para una experiencia de usuario moderna y eficiente.

## Características principales

1. **CRUD completo**: Genera automáticamente las operaciones de Crear, Leer, Actualizar y Eliminar para cualquier tabla en MySQL.
2. **Paginación**: Incluye soporte para paginación en las vistas.
3. **Búsqueda avanzada**: Permite buscar registros por cualquier campo de la tabla.
4. **Exportación de datos**: Exporta los datos en formatos **Excel**, **CSV** y **TXT**.
5. **Compatibilidad con Bootstrap 5**: Las vistas generadas están diseñadas con Bootstrap 5 para un diseño moderno y responsivo.
6. **Modularidad**: Cada tabla generada sigue una estructura organizada y modular.

## Estructura del proyecto

El proyecto genera el CRUD de cada tabla en la siguiente estructura:

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
### Archivos generados por tabla

1. **Modelo**: `modelo_<nombre_tabla>.php` (gestiona la interacción con la base de datos).
2. **Controlador**: `controlador_<nombre_tabla>.php` (gestiona la lógica de negocio).
3. **Vista**: `vista_<nombre_tabla>.php` (gestiona la interfaz de usuario).

---

## Requisitos del sistema

- **Servidor web**: Apache (recomendado con XAMPP o WAMP).
- **PHP**: Versión 7.4 o superior.
- **Base de datos**: MySQL.
- **Bootstrap**: Las vistas generadas utilizan Bootstrap 5.
- **Extensiones PHP**: `mysqli` habilitado.

---

## Instalación

Sigue estos pasos para instalar y configurar el proyecto:

1. **Clonar el repositorio**:
   ```bash
   git clone https://github.com/pouyer/generaCRUDPhp.git
   ```

2. **Configurar la base de datos**:

Crea una base de datos en MySQL.
Configura el archivo conexion.php con los datos de tu base de datos:  

<?php
$conexion = new mysqli('localhost', 'usuario', 'contraseña', 'nombre_base_datos');
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>

3. **Configurar el entorno**:

Asegúrate de que el proyecto esté ubicado en el directorio raíz de tu servidor web (por ejemplo, XAMPP/htdocs).
4. **Generar CRUDs**:

Ejecuta el generador de vistas para cada tabla que desees administrar:


5. **Acceder al proyecto**:

Abre tu navegador y accede al proyecto desde http://localhost/generaCRUDPhp.

## Uso del generador
El generador de vistas se encuentra en el archivo include/generar_vista.php. Este archivo genera automáticamente los archivos necesarios para administrar una tabla en MySQL.

## Funcionalidades generadas

1. Paginación:

Configurable desde la vista generada.
Controlada por el parámetro registrosPorPagina.
2. Búsqueda:

Permite buscar registros por cualquier campo de la tabla.
3. Exportación:

Exporta los datos en formatos Excel, CSV y TXT.
4. Modales:

Las operaciones de Crear y Actualizar se realizan mediante modales.

## Personalización
Puedes personalizar los estilos y scripts del proyecto editando los siguientes archivos:

- Estilos: css/estilos.css
- Iconos y Bootstrap: Configurados en headIconos.php.

## Contribuciones
Si deseas contribuir al proyecto, sigue estos pasos:

1. Haz un fork del repositorio.
2. Crea una nueva rama para tu funcionalidad:
 ```bash
 git checkout -b nueva-funcionalidad
 ```
3. Realiza tus cambios y haz un commit:
```
 git commit -m "Descripción de los cambios"
```

4. Envía tus cambios:
```
 git push origin nueva-funcionalidad
```

5. Abre un Pull Request en GitHub.

## Licencia
Este proyecto está bajo la licencia MIT. Consulta el archivo LICENSE para más detalles.