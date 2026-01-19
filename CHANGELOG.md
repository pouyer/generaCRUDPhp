# CHANGELOG

Este archivo documenta los cambios más relevantes realizados en el proyecto **generaCRUDPhp**.

## [1.0.1] - 2026-01-18
### 🚀 Nuevas Funcionalidades
- **Filtros SQL en Relaciones**: Posibilidad de añadir claúsulas `WHERE` personalizadas a los comboboxes de llaves foráneas desde la configuración.
- **Sincronización Inteligente de Rutas**: El sistema ahora detecta y corrige automáticamente la ruta de los archivos PHP al sincronizar, respetando la estructura personalizada del proyecto.
- **Switches Interactivos**: Reemplazo de checkboxes/radios por interruptores tipo switch de Bootstrap 5 en los modales de creación y edición.

### 🛠️ Correcciones
- **Persistencia de Tipos Numéricos**: Solucionado error crítico donde campos tipo `YEAR`, `INT` o `DECIMAL` vacíos causaban fallos en MySQL (`Incorrect integer value: ''`). Ahora se envían correctamente como `NULL`.
- **Auditoría UI**: Corrección en la visibilidad de campos de auditoría en los modales, asegurando consistencia entre sistemas operativos (Windows/Linux).
- **Protección de Rutas**: La función de actualización de rutas ahora protege las configuraciones personalizadas y solo afecta a los módulos base del sistema.

## [1.0.0] - 2026-01-06
### ✨ Características Premium
- **Vistas SQL de Solo Lectura**: Detección automática de vistas y generación de interfaces de consulta protegidas (sin creación/edición).
- **Búsqueda Avanzada por Campo**: Nuevo buscador para Vistas que permite filtrar por columna específica y valor.
- **Auditoría Automática de Usuarios**: Sistema inteligente que asigna ID de usuario a campos de auditoría y los oculta de la interfaz.
- **Temas Visuales Avanzados**: Implementación de 4 temas premium con personalización de color primario e iconos.
- **RBAC Robusto**: Sistema de control de acceso basado en roles con permisos granulares (Inserta, Actualiza, Elimina, Exporta).
- **Exportación Filtrada**: Soporte para exportar a Excel, CSV y TXT respetando los filtros activos.

### 🛠️ Mejoras y Correcciones
- **Detección de Tablas**: Corregida la lógica JS para identificar correctamente `VIEW` vs `BASE TABLE`.
- **Compatibilidad con Vistas**: Implementado fallback de ordenamiento por primera columna cuando no existe PK definida.
- **Estabilidad de Plantillas**: Eliminada la redundancia de métodos en el modelo generado que causaba errores fatales.
- **Seguridad Git**: Agregado archivo `.gitignore` para proteger configuraciones locales y directorios temporales.
- **Arquitectura Limpia**: Refactorización de las rutas de inclusión para mejorar la portabilidad de los proyectos generados.

## [0.5.2] - 2025-04-30
### Cambios
- Se corrige la creación de vistas del módulo de accesos para mayor eficiencia.
- Se corrige error en etiquetas HTML en la generación de vista_tabla.
- Se ajusta el renombre del archivo de conexión en el módulo accesos.
- Se adiciona cambio dinámico del nombre del menú principal.
- Se ajusta la presentación visual de las pantallas de acceso.

## [0.5.1] - 2025-04-05
### Cambios
- Corrige bug de nombre del proyecto.
- Implementa seguridad en el hashing de Password.

## [0.5.0] - 2025-04-04
### Cambios
- Implementación de generación de pantalla de Login.
- Creación de flujo de Registro de Usuario.
- Funcionalidad de Restablecer Contraseña.

## [0.4.0] - 2025-03-04
### Cambios
- Migración completa a **Bootstrap 5**.
- Eliminación de dependencias de **jQuery** en el núcleo.
- Inclusión de `headIconos.php` para centralizar recursos.
- Mejora en la generación de vistas con soporte para modales.

## [0.3.0] - 2025-01-15
### Cambios
- Implementación de exportación de datos en formatos **Excel**, **CSV** y **TXT**.
- Inclusión de soporte para búsquedas dinámicas.

## [0.2.0] - 2024-12-10
### Cambios
- Soporte para paginación configurable.
- Inclusión de validación básica en formularios.

## [0.1.0] - 2024-10-01
### Cambios
- Primera versión del generador de CRUDs basada en MVC.