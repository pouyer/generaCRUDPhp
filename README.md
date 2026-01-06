# generaCRUDPhp

**generaCRUDPhp** es un potente generador de código PHP diseñado para acelerar el desarrollo de aplicaciones web administrativas. Crea automáticamente módulos CRUD (Create, Read, Update, Delete) completos, seguros y visualmente personalizados, basándose en la estructura de tus tablas MySQL.

El sistema utiliza el patrón **MVC** (Modelo-Vista-Controlador), integra **Bootstrap 5** para la interfaz, y cuenta con un robusto sistema de **Control de Acceso Basado en Roles (RBAC)** y **Auditoría**.

## 🚀 Características Principales

1.  **CRUD Completo y Automatizado**: Genera operaciones de gestión de datos listas para usar en segundos.
2.  **Control de Acceso Granular (RBAC)**:
    *   Gestión de Usuarios, Roles y Permisos.
    *   Permisos específicos por tabla: Insertar, Actualizar, Eliminar y Exportar.
    *   Validación de seguridad tanto en Vista (UI) como en Controlador (Backend).
3.  **Personalización Visual Avanzada**:
    *   **Temas Predefinidos**: Azul Océano, Verde Esmeralda, Gris Premium, Púrpura Real.
    *   **Personalización**: Selector de color primario e iconos (Fontello/FontAwesome) por tabla.
    *   Diseño moderno "Premium" con sombras suaves, gradientes y micro-interacciones.
4.  **Auditoría Automática de Usuarios**:
    *   Configuración visual para asignar campos de "Usuario Creador" y "Usuario Modificador".
    *   Inyección automática del ID de usuario en el backend.
    *   Ocultamiento automático de estos campos en los formularios para evitar manipulación.
7.  **Relaciones Inteligentes**: Detección automática de llaves foráneas para generar selectores (dropdowns) en los formularios.
8.  **Exportación de Datos**: Soporte nativo para Excel, CSV y TXT con filtrado activo.
9.  **Generación de Vistas SQL (Solo Lectura)**: 
    *   Detección automática de `VIEWS` en la base de datos.
    *   Generación de interfaces de consulta de solo lectura, sin botones de creación o edición.
10. **Búsqueda Avanzada y Paginación**: 
    *   **Búsqueda Global**: Para tablas base (CRUD completo).
    *   **Búsqueda por Campo**: Filtro avanzado para Vistas que permite seleccionar campo y valor.
    *   Paginación configurable para todos los módulos.

## 📂 Estructura del Proyecto Generado

El sistema organiza el código generado en una arquitectura MVC limpia:

```
X:/RUTA/MI_PROYECTO/
│   index.php (Login)
│   conexion.php
├───accesos/                  # Módulo de Seguridad y Accesos
│   ├───controlador_login.php
│   ├───verificar_sesion.php
│   └───...
├───controladores/            # Lógica de Negocio
│   └───controlador_<tabla>.php
├───modelos/                  # Acceso a Datos
│   └───modelo_<tabla>.php
├───vistas/                   # Interfaz de Usuario
│   └───vista_<tabla>.php
├───css/
│   └───estilos.css
└───iconos-web/               # Fuente de iconos
```

## 🛠️ Instalación y Configuración

1.  **Clonar el repositorio**:
    ```bash
    git clone https://github.com/pouyer/generaCRUDPhp.git
    ```

2.  **Configurar Base de Datos del Proyecto**:
    *   Asegúrate de tener una base de datos MySQL creada para tu proyecto.

3.  **Ejecutar el Generador**:
    *   Accede a `http://localhost/generaCRUDPhp`.
    *   Ingresa las credenciales de conexión (Host, Usuario, Password, Base de Datos del Proyecto).
    *   Configura la ruta local donde se guardarán los archivos generados.

4.  **Generar Módulo de Acceso (Primer Paso Obligatorio)**:
    *   Antes de generar CRUDs, ve a la sección "Configuración del Proyecto".
    *   Haz clic en **"Crear Tablas de Acceso"** (esto creará las tablas de usuarios y roles en tu BD).
    *   Crea un usuario administrador inicial.
    *   Haz clic en **"Crear Pantalla de Login"** y **"Crear Menú Principal"**.

5.  **Generar CRUDs de tus Tablas**:
    *   Selecciona las tablas de tu base de datos.
    *   Haz clic en **"Configurar"** en cada tabla para personalizar:
        *   **Relaciones**: Define qué campo mostrar en los selectores de llaves foráneas.
        *   **Layout**: Elige 1, 2, 3 o 4 columnas para tus formularios.
        *   **Apariencia**: Elige tema, color e icono.
        *   **Vistas y Exportación**: Activa/desactiva campos en lista/exportación y configura la **Auditoría de Usuario**.
    *   Haz clic en **"Generar CRUD"**.

6.  **Sincronizar Permisos**:
    *   Una vez generados los archivos, ve al módulo de "Programas" (en tu aplicación generada o en el gestor) y ejecuta **"Sincronizar Programas"**.
    *   Asigna permisos a los roles sobre los nuevos programas creados (vistas).

## ⚙️ Configuración Avanzada

### Auditoría de Usuarios
Para llevar un registro automático de quién crea o modifica registros:
1.  En el generador, abre la configuración de la tabla.
2.  Ve a la pestaña **"Vistas y Exportación"**.
3.  En la columna **"Auditoría Usuario"**, selecciona:
    *   `Usuario Inserta`: Para el campo que guardará el ID del creador.
    *   `Usuario Modifica`: Para el campo que guardará el ID del último editor.
4.  El sistema se encargará de llenar estos datos y ocultarlos de la interfaz automáticamente.

### Personalización de Apariencia
El sistema permite que cada módulo (tabla) tenga su propia identidad visual:
1.  En configuración, pestaña **"Apariencia"**.
2.  Selecciona un **Tema** base y personaliza el **Color Primario**.
3.  Asigna un **Icono** representativo (usando clases de Fontello/FontAwesome).

## 📋 Requisitos

*   **Servidor Web**: Apache (XAMPP/WAMP recomendado).
*   **PHP**: 7.4 o superior.
*   **MySQL**: 5.7 o superior.
*   **Navegador**: Moderno con soporte ES6.

## 🤝 Contribuciones

Si deseas contribuir:
1.  Haz un fork del repositorio.
2.  Crea una rama (`git checkout -b feature/nueva-funcionalidad`).
3.  Commit (`git commit -m "Agrega nueva funcionalidad"`).
4.  Push (`git push origin feature/nueva-funcionalidad`).
5.  Abre un Pull Request.

## 📄 Licencia

Este proyecto está bajo la licencia MIT. Consulta el archivo LICENSE para más detalles.