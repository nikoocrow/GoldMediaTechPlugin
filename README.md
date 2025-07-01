# 🗺️ Configuración de Google Maps

## 📋 Pasos de Configuración

### 🔌 Paso 1: Activar el Plugin
Primero **activa el plugin** desde tu panel de WordPress.

### ⚙️ Paso 2: Acceder a Configuración
1. Ve a **Settings** (Ajustes) en tu panel de administración
2. Busca la opción **"Ubicaciones Maps"**

### 🔑 Paso 3: Configurar API Key
1. Localiza la casilla **"API Key de Google"**
2. Introduce tu **API de Google Maps**

---

## 📝 Notas Importantes

> ⚠️ **Importante**: Asegúrate de tener una API Key válida de Google Maps antes de continuar con la configuración.

> 💡 **Tip**: Si no tienes una API Key, puedes obtenerla desde la [Consola de Google Cloud](https://console.cloud.google.com/).

---

## 🔗 Recursos Adicionales

- 📖 [Documentación oficial de Google Maps API](https://developers.google.com/maps/documentation)
- 🛠️ [Guía para obtener API Key](https://developers.google.com/maps/get-started)


# 📍 Plugin Ubicaciones Gold Media Test

## 🚀 Descripción
Plugin que permite gestionar ubicaciones con integración de Google Maps API. Crea un Custom Post Type para ubicaciones con coordenadas, direcciones y mapas interactivos.

**Versión:** 1.0.0 | **Autor:** Gold Media Tech

## 🔧 Funciones Personalizadas

El plugin incluye varias funciones PHP que puedes usar en tus templates:

### 📍 Funciones de Datos
- `obtener_datos_ubicacion($post_id)` - Obtiene todos los datos de una ubicación
- `obtener_coordenadas_ubicacion($post_id)` - Obtiene latitud y longitud específicas
- `modificar_query_ubicaciones($query)` - Modifica la consulta del archivo para filtros

### 🗺️ Funciones de Interfaz  
- `abrirModalContacto($id, $titulo)` - JavaScript para modal de contacto

### 📋 Datos que Maneja el Plugin
- **Dirección completa**
- **Coordenadas GPS** (latitud/longitud)
- **Teléfono de contacto**
- **Email de contacto**
- **Horarios de atención**
- **Google Place ID**
- **Imagen destacada**
- **Descripción/excerpt**

---

## 🎯 Shortcode Avanzado

### 📝 Shortcode `[mapa_ubicaciones]`

Muestra mapas interactivos con ubicaciones personalizables:

**Parámetros disponibles:**
- `id` - ID específico de ubicación
- `categoria` - Filtrar por categoría  
- `limite` - Número máximo de ubicaciones (-1 = todas)
- `altura` - Altura del mapa (ej: "400px")
- `zoom` - Nivel de zoom inicial (1-20)

**Ejemplos de uso:**
```php
// Mapa con una ubicación específica
[mapa_ubicaciones id="123" altura="300px"]

// Mapa con ubicaciones de una categoría
[mapa_ubicaciones categoria="oficinas" limite="5"]

// Mapa general con todas las ubicaciones
[mapa_ubicaciones altura="500px" zoom="10"]
```

### 🔍 Detección Inteligente
- **Detección automática** en contenido de páginas/posts
- **Detección en widgets** de texto y bloques
- **Carga condicional** de scripts solo cuando es necesario
- **Scripts de respaldo** si fallan las cargas principales

---

## 💬 Sistema de Contacto

### 📧 Formulario de Contacto Avanzado
- **Modal interactivo** desde mapas y ubicaciones
- **Diferentes tipos** de solicitud (consulta general, cita)
- **Validación completa** de datos
- **Base de datos propia** para almacenar solicitudes

### 📨 Emails Automáticos
- **Email al administrador** con todos los detalles
- **Email de confirmación** al cliente
- **Plantillas personalizables** según tipo de solicitud
- **Información técnica** incluida (IP, fecha, etc.)

### 🗃️ Panel de Administración
- **Lista de solicitudes** recibidas
- **Estados de seguimiento** (pendiente, procesando, completado)
- **Gestión desde el admin** de WordPress
- **Filtros y búsqueda** de contactos

---

## 🛠️ Metaboxes del Admin

### 🗺️ Mapa Interactivo
- **Búsqueda automática** de direcciones
- **Geocoding en tiempo real** 
- **Marcador arrastrable** para ajustar posición
- **Clic en mapa** para seleccionar ubicación
- **Autocompletado** de Place ID de Google

### 📝 Campos Disponibles
- **Dirección completa** con autocompletado
- **Coordenadas GPS** (latitud/longitud)
- **Teléfono de contacto**
- **Email de contacto** 
- **Horarios de atención**
- **Google Place ID** (automático)

### ⚡ Funcionalidades
- **Validación en tiempo real**
- **Geocoding automático** al escribir direcciones
- **Geocoding inverso** al hacer clic en mapa
- **Interfaz responsive** para diferentes pantallas

---

## ⚡ Características Principales

- 📍 **Custom Post Type** para gestionar ubicaciones
- 🗺️ **Integración completa con Google Maps API**
- 📊 **Metaboxes personalizados** para datos de ubicación
- 🎯 **Coordenadas GPS** y direcciones
- 🔧 **Shortcodes** para mostrar mapas
- ⚡ **Ajax handlers** para funcionalidades dinámicas

---

## 🛠️ Instalación y Configuración

### 🔌 Paso 1: Activar el Plugin
1. Sube el plugin a tu directorio `/wp-content/plugins/`
2. **Activa el plugin** desde el panel de WordPress
3. El sistema creará automáticamente el Custom Post Type "Ubicaciones"

### ⚙️ Paso 2: Configuración Inicial
1. Ve a **Settings** (Ajustes) en tu panel de administración
2. Busca la opción **"Ubicaciones Settings"**
3. Accede a la configuración del plugin

### 🔑 Paso 3: Configurar API Key de Google Maps
1. Localiza la casilla **"API Key de Google"**
2. Introduce tu **API de Google Maps**
3. Guarda los cambios

> ⚠️ **Aviso importante**: El plugin mostrará una notificación en el admin hasta que configures la API Key.

---

## 📱 Cómo Usar el Plugin

### ➕ Crear Nueva Ubicación
1. Ve a **Ubicaciones** en el menú lateral del admin
2. Haz clic en **"Añadir nueva"**
3. Completa los datos de la ubicación usando los metaboxes
4. Publica la ubicación

### 🗺️ Gestionar Ubicaciones
- **Ver todas las ubicaciones:** `Ubicaciones > Todas las ubicaciones`
- **Editar ubicación:** Click en el título de cualquier ubicación
- **Configurar mapas:** Usa los metaboxes personalizados

---

## 🎨 Templates del Theme

El plugin incluye templates personalizados que debes colocar en tu theme activo:

### 📄 Template Archive (archive-ubicaciones.php)
**Funcionalidades principales:**
- 🗺️ **Mapa general** con todas las ubicaciones
- 🔍 **Filtros de búsqueda** por texto y categoría
- 📱 **Grid responsivo** de ubicaciones
- 📍 **Material Icons** de Google integrados
- 📞 **Meta información** con iconos (dirección, teléfono, email)
- 🚗 **Enlaces directos** a Google Maps para direcciones
- 💬 **Modal de contacto** para cada ubicación
- 📑 **Paginación** estilizada
- 📱 **Responsive design** completo

### 🎨 Elementos de Interfaz Incluidos
- **Header** con título y descripción
- **Mapa interactivo** general
- **Formulario de filtros** con búsqueda
- **Cards de ubicaciones** con thumbnails
- **Botones de acción** (Contactar, Ver detalles)
- **Estado de "no resultados"** cuando no hay coincidencias

### 🎯 Variables CSS Personalizables
```css
--primary-color: #1B1B1B    /* Color principal */
--secondary-color: #64646A  /* Color secundario */
--success-color: #EEEFF1    /* Color de éxito */
--info-color: #F3F4F5       /* Color informativo */
--white-color: #FFFFFF      /* Color blanco */
```

### 📁 Instalación de Templates
1. Copia `archive-ubicaciones.php` a tu carpeta del theme activo
2. Copia `single-ubicaciones.php` a tu carpeta del theme activo  
3. Los estilos están incluidos en los templates
4. Los templates usarán automáticamente Material Icons de Google

---

## ⚠️ Requisitos

- WordPress 5.0 o superior
- PHP 7.4 o superior
- **API Key de Google Maps** (obligatorio)

---

## 🔗 Enlaces Rápidos

Después de activar el plugin, encontrarás estos enlaces en la página de plugins:
- 🔧 **Configuración** - Acceso directo a settings
- 📍 **Ubicaciones** - Gestionar todas las ubicaciones

---

## 🆘 Solución de Problemas

### ❌ No aparecen los mapas
**Posibles causas y soluciones:**
- ✅ Verifica que hayas configurado la **API Key de Google Maps**
- ✅ Asegúrate de tener habilitadas las **APIs necesarias** en Google Cloud Console:
  - Maps JavaScript API
  - Geocoding API  
  - Places API
- ✅ Revisa las **restricciones de la API Key** (HTTP referrer)
- ✅ Verifica el **límite de facturación** en Google Cloud

### ❌ Error de permisos o "API Key inválida"
**Soluciones:**
- ✅ Confirma que la API Key esté **copiada correctamente** (sin espacios)
- ✅ Verifica que el **dominio esté autorizado** en las restricciones
- ✅ Asegúrate de que la **facturación esté habilitada** en Google Cloud

### ❌ El shortcode no funciona
**Verificaciones:**
- ✅ Usa la sintaxis correcta: `[mapa_ubicaciones]`
- ✅ Asegúrate de que las **ubicaciones tengan coordenadas** válidas
- ✅ Verifica que la **API Key esté configurada**
- ✅ Activa el **debug mode**: añade `?debug_shortcodes=1` a la URL

### ❌ Formulario de contacto no envía emails
**Soluciones:**
- ✅ Verifica la **configuración de email** de WordPress
- ✅ Revisa la **carpeta de spam** en tu email
- ✅ Comprueba los **logs del servidor** para errores de PHP
- ✅ Asegúrate de que **wp_mail()** funcione correctamente

### ❌ Los metaboxes no cargan en el admin
**Verificaciones:**
- ✅ Asegúrate de que estés editando un **post type "ubicaciones"**
- ✅ Verifica que la **API Key esté configurada**
- ✅ Revisa la **consola del navegador** para errores JavaScript
- ✅ Desactiva otros **plugins que puedan causar conflictos**

### 🔧 Debug Mode
Para activar el modo debug, añade `?debug_shortcodes=1` a cualquier URL (solo para administradores). Esto mostrará:
- Estado de carga de scripts
- Información de API Key
- Detección de shortcodes
- Errores de JavaScript

---

## 📝 Notas Técnicas

### 🗂️ Estructura de Archivos
```
ubicaciones-gold-media-test/
├── ubicaciones-gold-media-test.php (archivo principal)
├── includes/
│   ├── ubicaciones-cpt.php (Custom Post Type)
│   ├── ubicaciones-metaboxes.php (metaboxes interactivos)
│   ├── google-maps-config.php (configuración de Google Maps)
│   ├── shortcode-detector.php (detección de shortcodes)
│   └── ajax-handler.php (formularios de contacto)
├── assets/css/ (estilos compilados)
├── assets/js/ (scripts frontend)
└── src/ (archivos fuente)
```

### 🗄️ Base de Datos
El plugin crea la tabla `wp_ubicaciones_contactos` para almacenar:
- Datos de contacto completos
- Tipo de solicitud (consulta/cita)
- Estado de seguimiento
- Información técnica (IP, fecha)
- Notas administrativas

### 🔗 APIs de Google Utilizadas
- **Maps JavaScript API** - Mapas interactivos
- **Geocoding API** - Conversión direcciones ↔ coordenadas  
- **Places API** - Búsqueda y autocompletado de lugares

### ⚡ Optimizaciones
- **Carga condicional** de scripts según necesidad
- **Detección inteligente** de shortcodes
- **Prevención de conflictos** con otros plugins
- **Scripts de respaldo** para mayor confiabilidad
- **Minificación automática** en producción (cuando WP_DEBUG = false)

### 🛡️ Seguridad
- **Nonces de verificación** en formularios
- **Sanitización completa** de datos
- **Validación del lado servidor**
- **Escape de datos** en salida HTML
- **Prevención de acceso directo** a archivos

---

## 🔗 Recursos Adicionales

- 📖 [Documentación oficial de Google Maps API](https://developers.google.com/maps/documentation)
- 🛠️ [Obtener API Key de Google](https://console.cloud.google.com/)
- 🌐 [Gold Media Tech](https://goldmediatech.com)
