<?php
/**
 * Configuración de Google Maps API
 * Maneja la carga de scripts y configuración de la API de Google Maps
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registrar y cargar scripts de Google Maps en el admin
 */
function cargar_google_maps_admin() {
    // Solo cargar en las páginas de ubicaciones
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'ubicaciones') {
        return;
    }
    
    // Obtener la API key desde las opciones de WordPress
    $api_key = get_option('google_maps_api_key', '');
    
    if (empty($api_key)) {
        // Mostrar aviso si no hay API key
        add_action('admin_notices', 'aviso_falta_api_key');
        return;
    }
    
    // Prevenir conflictos con otros scripts
    add_action('admin_footer', 'ubicaciones_prevenir_conflictos_admin', 5);
    
    // Cargar script de Google Maps con manejo de errores
    wp_enqueue_script(
        'google-maps-api-ubicaciones',
        'https://maps.googleapis.com/maps/api/js?key=' . $api_key . '&libraries=places&callback=inicializarMapaUbicaciones',
        array(),
        UBICACIONES_PLUGIN_VERSION,
        true
    );
    
    // Script para manejar errores y conflictos
    wp_add_inline_script('google-maps-api-ubicaciones', '
        // Función global para inicializar mapas (callback de Google Maps)
        window.inicializarMapaUbicaciones = function() {
            if (typeof inicializarMapa === "function") {
                inicializarMapa();
            }
        };
        
        // Verificar carga de Google Maps
        window.addEventListener("load", function() {
            setTimeout(function() {
                if (typeof google === "undefined") {
                    console.error("Ubicaciones Plugin: Google Maps no se cargó correctamente");
                    var mapContainer = document.getElementById("mapa_ubicacion");
                    if (mapContainer) {
                        mapContainer.innerHTML = "<div style=\"display: flex; align-items: center; justify-content: center; height: 100%; text-align: center; color: #d63638; padding: 50px;\"><span class=\"material-icons\" style=\"font-size: 48px; margin-right: 10px;\">error</span><span>Error: No se pudo cargar Google Maps. Verifica tu API Key.</span></div>";
                    }
                }
            }, 2000);
        });
    ', 'before');
    
    // CSS personalizado para el admin
    wp_add_inline_style('wp-admin', '
        .ubicaciones-metabox { padding: 15px 0; }
        .ubicaciones-metabox table { width: 100%; }
        .ubicaciones-metabox th { width: 150px; text-align: left; padding: 10px 15px 10px 0; }
        .ubicaciones-metabox td { padding: 10px 0; }
        .ubicaciones-metabox input[type="text"], 
        .ubicaciones-metabox input[type="email"], 
        .ubicaciones-metabox input[type="tel"],
        .ubicaciones-metabox textarea { width: 100%; max-width: 400px; }
        .ubicaciones-metabox textarea { height: 80px; }
        .ubicaciones-metabox .button { margin-left: 10px; }
        .ubicaciones-metabox .description { font-style: italic; color: #666; margin-top: 5px; }
        #mapa_ubicacion { border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .coordenadas-grupo { display: flex; gap: 15px; }
        .coordenadas-grupo > div { flex: 1; }
        
        /* Estilos para mensajes de error */
        .ubicaciones-error-api {
            background: #fef7f7;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
    ');
    
    // Cargar Google Icons
    wp_enqueue_style(
        'google-material-icons',
        'https://fonts.googleapis.com/icon?family=Material+Icons',
        array(),
        null
    );
}
add_action('admin_enqueue_scripts', 'cargar_google_maps_admin');

/**
 * Mostrar aviso cuando falta la API key
 */
function aviso_falta_api_key() {
    ?>
    <div class="notice notice-warning is-dismissible">
        <p>
            <span class="material-icons" style="vertical-align: middle; margin-right: 5px;">warning</span>
            <strong>Ubicaciones:</strong> 
            Para usar Google Maps necesitas configurar tu API Key. 
            <a href="<?php echo admin_url('options-general.php?page=ubicaciones-settings'); ?>">
                Configúrala aquí
            </a>
        </p>
    </div>
    <?php
}

/**
 * Prevenir conflictos con scripts del tema en el admin
 */
function ubicaciones_prevenir_conflictos_admin() {
    ?>
    <script>
        // Prevenir conflictos con otros scripts
        (function() {
            // Guardar referencias de funciones importantes
            var originalConsoleError = console.error;
            
            // Filtrar errores irrelevantes del tema
            console.error = function() {
                var message = Array.prototype.slice.call(arguments).join(' ');
                if (message.indexOf('main.min.js') === -1 && 
                    message.indexOf('theme') === -1) {
                    originalConsoleError.apply(console, arguments);
                }
            };
            
            // Asegurar que jQuery esté disponible para nuestro plugin
            if (typeof jQuery === 'undefined') {
                console.warn('Ubicaciones Plugin: jQuery no disponible, cargando desde CDN');
                var script = document.createElement('script');
                script.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
                script.onload = function() {
                    console.log('Ubicaciones Plugin: jQuery cargado desde CDN');
                };
                document.head.appendChild(script);
            }
        })();
    </script>
    <?php
}

/**
 * Cargar Google Maps en el frontend de forma segura
 */
function cargar_google_maps_frontend() {
    // Verificar si necesitamos cargar Google Maps
    $cargar_maps = false;
    
    // Páginas específicas de ubicaciones
    if (is_singular('ubicaciones') || is_post_type_archive('ubicaciones') || is_page_template('page-mapa.php')) {
        $cargar_maps = true;
    }
    
    // Verificar si hay shortcodes de mapas en el contenido
    if (!$cargar_maps && is_singular()) {
        global $post;
        if ($post && (has_shortcode($post->post_content, 'mapa_ubicaciones') || 
                     strpos($post->post_content, '[mapa_ubicaciones') !== false)) {
            $cargar_maps = true;
        }
    }
    
    // Verificar widgets que puedan tener shortcodes
    if (!$cargar_maps) {
        $sidebars = wp_get_sidebars_widgets();
        foreach ($sidebars as $sidebar_id => $widget_ids) {
            if (is_array($widget_ids)) {
                foreach ($widget_ids as $widget_id) {
                    $widget_data = get_option('widget_text');
                    if (is_array($widget_data)) {
                        foreach ($widget_data as $widget_content) {
                            if (is_array($widget_content) && isset($widget_content['text'])) {
                                if (strpos($widget_content['text'], '[mapa_ubicaciones') !== false) {
                                    $cargar_maps = true;
                                    break 3;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    
    if ($cargar_maps) {
        $api_key = get_option('google_maps_api_key', '');
        
        if (!empty($api_key)) {
            // Prevenir conflictos en frontend
            add_action('wp_footer', 'ubicaciones_prevenir_conflictos_frontend', 5);
            
            // Cargar Google Maps con prioridad alta
            wp_enqueue_script(
                'google-maps-api-frontend-ubicaciones',
                'https://maps.googleapis.com/maps/api/js?key=' . $api_key . '&libraries=places&callback=inicializarMapasFrontendUbicaciones',
                array(),
                UBICACIONES_PLUGIN_VERSION,
                true
            );
            
            // Script personalizado para el frontend
            wp_enqueue_script(
                'ubicaciones-frontend-js',
                UBICACIONES_PLUGIN_URL . 'src/js/ubicaciones-frontend.js',
                array('google-maps-api-frontend-ubicaciones'),
                UBICACIONES_PLUGIN_VERSION,
                true
            );
            
            // Cargar Google Icons en frontend
            wp_enqueue_style(
                'google-material-icons',
                'https://fonts.googleapis.com/icon?family=Material+Icons',
                array(),
                null
            );
            
            // CSS para el frontend (compilado por Gulp)
            if (WP_DEBUG) {
                // En desarrollo, usar archivos sin minificar
                wp_enqueue_style(
                    'ubicaciones-frontend-css',
                    UBICACIONES_PLUGIN_URL . 'assets/css/ubicaciones.css',
                    array(),
                    UBICACIONES_PLUGIN_VERSION
                );
                
                wp_enqueue_style(
                    'modal-contacto-css',
                    UBICACIONES_PLUGIN_URL . 'assets/css/modal-contacto.css',
                    array(),
                    UBICACIONES_PLUGIN_VERSION
                );
            } else {
                // En producción, usar archivos minificados
                wp_enqueue_style(
                    'ubicaciones-frontend-css',
                    UBICACIONES_PLUGIN_URL . 'assets/css/ubicaciones.min.css',
                    array(),
                    UBICACIONES_PLUGIN_VERSION
                );
                
                wp_enqueue_style(
                    'modal-contacto-css',
                    UBICACIONES_PLUGIN_URL . 'assets/css/modal-contacto.min.css',
                    array(),
                    UBICACIONES_PLUGIN_VERSION
                );
            }
            
            // Pasar datos al script con manejo de errores
            wp_localize_script('ubicaciones-frontend-js', 'ubicacionesData', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ubicaciones_frontend_nonce'),
                'plugin_url' => UBICACIONES_PLUGIN_URL,
                'debug' => WP_DEBUG
            ));
            
            // Script inline para manejar callback
            wp_add_inline_script('google-maps-api-frontend-ubicaciones', '
                window.inicializarMapasFrontendUbicaciones = function() {
                    console.log("Google Maps callback ejecutado");
                    if (typeof inicializarMapasFrontend === "function") {
                        inicializarMapasFrontend();
                    }
                    // También inicializar shortcodes
                    if (typeof inicializarShortcodeMapas === "function") {
                        inicializarShortcodeMapas();
                    }
                };
            ', 'before');
        }
    }
}
add_action('wp_enqueue_scripts', 'cargar_google_maps_frontend');

/**
 * Prevenir conflictos con scripts del tema en el frontend
 */
function ubicaciones_prevenir_conflictos_frontend() {
    ?>
    <script>
        // Prevenir conflictos en frontend
        (function() {
            // Crear namespace para nuestro plugin
            window.UbicacionesPlugin = window.UbicacionesPlugin || {};
            
            // Función para verificar dependencias
            window.UbicacionesPlugin.verificarDependencias = function() {
                var errores = [];
                
                if (typeof google === 'undefined') {
                    errores.push('Google Maps API no está disponible');
                }
                
                if (errores.length > 0) {
                    console.error('Ubicaciones Plugin - Errores:', errores);
                    
                    // Mostrar mensaje en contenedores de mapas
                    var contenedores = document.querySelectorAll('.mapa-ubicacion, #mapa-archivo-ubicaciones, .mapa-ubicaciones-shortcode');
                    contenedores.forEach(function(contenedor) {
                        contenedor.innerHTML = '<div style="background: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px; text-align: center; display: flex; align-items: center; justify-content: center;"><span class="material-icons" style="font-size: 24px; margin-right: 10px;">error</span><strong>Error:</strong> ' + errores.join(', ') + '</div>';
                    });
                    
                    return false;
                }
                
                return true;
            };
            
            // Verificar cuando el DOM esté listo
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(window.UbicacionesPlugin.verificarDependencias, 1000);
                });
            } else {
                setTimeout(window.UbicacionesPlugin.verificarDependencias, 1000);
            }
            
            // Filtrar errores irrelevantes
            var originalConsoleError = console.error;
            console.error = function() {
                var message = Array.prototype.slice.call(arguments).join(' ');
                if (message.indexOf('main.min.js') === -1 && 
                    message.indexOf('Failed to load resource') === -1 ||
                    message.indexOf('ubicaciones') !== -1 ||
                    message.indexOf('google') !== -1) {
                    originalConsoleError.apply(console, arguments);
                }
            };
        })();
    </script>
    <?php
}

/**
 * Agregar página de configuración en el admin
 */
function agregar_menu_configuracion() {
    add_options_page(
        'Configuración de Ubicaciones',  // Título de la página
        'Ubicaciones Maps',              // Título del menú
        'manage_options',                // Capacidad requerida
        'ubicaciones-settings',          // Slug de la página
        'mostrar_pagina_configuracion'   // Función callback
    );
}
add_action('admin_menu', 'agregar_menu_configuracion');

/**
 * Mostrar la página de configuración
 */
function mostrar_pagina_configuracion() {
    // Procesar formulario si se envió
    if (isset($_POST['submit'])) {
        if (wp_verify_nonce($_POST['ubicaciones_settings_nonce'], 'ubicaciones_settings')) {
            $api_key = sanitize_text_field($_POST['google_maps_api_key']);
            update_option('google_maps_api_key', $api_key);
            echo '<div class="notice notice-success"><p><span class="material-icons" style="vertical-align: middle; margin-right: 5px;">check_circle</span>Configuración guardada correctamente.</p></div>';
        }
    }
    
    $api_key = get_option('google_maps_api_key', '');
    ?>
    <div class="wrap">
        <h1><span class="material-icons" style="vertical-align: middle; margin-right: 10px;">settings</span>Configuración de Ubicaciones con Google Maps</h1>
        
        <form method="post" action="">
            <?php wp_nonce_field('ubicaciones_settings', 'ubicaciones_settings_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="google_maps_api_key">
                            <span class="material-icons" style="vertical-align: middle; margin-right: 5px;">vpn_key</span>
                            API Key de Google Maps
                        </label>
                    </th>
                    <td>
                        <input type="text" 
                               id="google_maps_api_key" 
                               name="google_maps_api_key" 
                               value="<?php echo esc_attr($api_key); ?>" 
                               class="regular-text" 
                               placeholder="Pega aquí tu API Key de Google Maps">
                        <p class="description">
                            <span class="material-icons" style="vertical-align: middle; margin-right: 3px; font-size: 16px;">info</span>
                            Necesitas una API Key de Google Maps para usar las funcionalidades de mapas.
                            <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">
                                ¿Cómo obtener una API Key?
                            </a>
                        </p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button('Guardar Configuración'); ?>
        </form>
        
        <div class="card" style="max-width: 800px;">
            <h2><span class="material-icons" style="vertical-align: middle; margin-right: 10px;">help_outline</span>Instrucciones de configuración</h2>
            <ol>
                <li>Ve a <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a></li>
                <li>Crea un nuevo proyecto o selecciona uno existente</li>
                <li>Habilita las siguientes APIs:
                    <ul style="margin: 10px 0; padding-left: 20px;">
                        <li><span class="material-icons" style="vertical-align: middle; margin-right: 5px; font-size: 16px;">map</span>Maps JavaScript API</li>
                        <li><span class="material-icons" style="vertical-align: middle; margin-right: 5px; font-size: 16px;">place</span>Geocoding API</li>
                        <li><span class="material-icons" style="vertical-align: middle; margin-right: 5px; font-size: 16px;">search</span>Places API</li>
                    </ul>
                </li>
                <li>Ve a "Credenciales" y crea una API Key</li>
                <li>Copia la API Key y pégala en el campo de arriba</li>
                <li>Configura restricciones de la API Key (recomendado):
                    <ul style="margin: 10px 0; padding-left: 20px;">
                        <li><span class="material-icons" style="vertical-align: middle; margin-right: 5px; font-size: 16px;">security</span>Restricción por HTTP referrer</li>
                        <li>Agrega tu dominio: <code><?php echo esc_html(get_site_url()); ?>/*</code></li>
                    </ul>
                </li>
                <li>Guarda la configuración</li>
            </ol>
            
            <h3><span class="material-icons" style="vertical-align: middle; margin-right: 10px;">api</span>APIs necesarias:</h3>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li><strong><span class="material-icons" style="vertical-align: middle; margin-right: 5px; font-size: 16px;">map</span>Maps JavaScript API:</strong> Para mostrar mapas interactivos</li>
                <li><strong><span class="material-icons" style="vertical-align: middle; margin-right: 5px; font-size: 16px;">place</span>Geocoding API:</strong> Para convertir direcciones en coordenadas</li>
                <li><strong><span class="material-icons" style="vertical-align: middle; margin-right: 5px; font-size: 16px;">search</span>Places API:</strong> Para búsqueda de lugares y autocompletado</li>
            </ul>
            
            <div style="background: #f0f6fc; border: 1px solid #c3d0e4; padding: 15px; margin-top: 20px; border-radius: 4px;">
                <h4 style="margin-top: 0;"><span class="material-icons" style="vertical-align: middle; margin-right: 5px;">warning</span>Importante sobre costos:</h4>
                <p>Google Maps cobra por el uso de sus APIs. Revisa los precios en 
                   <a href="https://cloud.google.com/maps-platform/pricing" target="_blank">Google Maps Platform Pricing</a>
                   y considera establecer límites de facturación para evitar costos inesperados.</p>
            </div>
        </div>
        
        <?php if (!empty($api_key)): ?>
        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2><span class="material-icons" style="vertical-align: middle; margin-right: 10px;">check_circle</span>Estado de la configuración</h2>
            <p><span class="material-icons" style="color: green; vertical-align: middle; margin-right: 5px;">check_circle</span>API Key configurada correctamente</p>
            <p>Puedes empezar a crear ubicaciones visitando <a href="<?php echo admin_url('post-new.php?post_type=ubicaciones'); ?>">Agregar Nueva Ubicación</a></p>
        </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Shortcode para mostrar un mapa con ubicaciones
 */
function shortcode_mapa_ubicaciones($atts) {
    // Asegurar que los scripts se cargan
    static $shortcode_usado = false;
    if (!$shortcode_usado) {
        $shortcode_usado = true;
        // Forzar la carga de scripts si no se han cargado
        add_action('wp_footer', 'forzar_carga_scripts_shortcode', 5);
    }
    
    $atts = shortcode_atts(array(
        'id' => '',
        'categoria' => '',
        'limite' => -1,
        'altura' => '400px',
        'zoom' => 13
    ), $atts);
    
    // Si se especifica un ID específico
    if (!empty($atts['id'])) {
        $ubicacion = get_post($atts['id']);
        if (!$ubicacion || $ubicacion->post_type !== 'ubicaciones') {
            return '<div style="background: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px; text-align: center; display: flex; align-items: center; justify-content: center;"><span class="material-icons" style="font-size: 24px; margin-right: 10px;">error</span>Error: Ubicación no encontrada (ID: ' . esc_html($atts['id']) . ')</div>';
        }
        $ubicaciones = array($ubicacion);
    } else {
        // Query para obtener ubicaciones
        $args = array(
            'post_type' => 'ubicaciones',
            'post_status' => 'publish',
            'posts_per_page' => intval($atts['limite']),
            'meta_query' => array(
                array(
                    'key' => '_ubicacion_latitud',
                    'compare' => 'EXISTS'
                ),
                array(
                    'key' => '_ubicacion_longitud',
                    'compare' => 'EXISTS'
                )
            )
        );
        
        if (!empty($atts['categoria'])) {
            $args['category_name'] = sanitize_text_field($atts['categoria']);
        }
        
        $ubicaciones = get_posts($args);
    }
    
    if (empty($ubicaciones)) {
        return '<div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; padding: 30px; text-align: center; color: #6c757d; display: flex; align-items: center; justify-content: center;"><span class="material-icons" style="font-size: 48px; margin-right: 10px;">location_off</span>No se encontraron ubicaciones para mostrar en el mapa.</div>';
    }
    
    // Preparar datos para JavaScript
    $datos_ubicaciones = array();
    foreach ($ubicaciones as $ubicacion) {
        if (!function_exists('obtener_datos_ubicacion')) {
            continue; // Saltar si la función no está disponible
        }
        
        $datos = obtener_datos_ubicacion($ubicacion->ID);
        if ($datos['latitud'] && $datos['longitud']) {
            $datos_ubicaciones[] = array(
                'id' => $ubicacion->ID,
                'titulo' => get_the_title($ubicacion->ID),
                'lat' => floatval($datos['latitud']),
                'lng' => floatval($datos['longitud']),
                'direccion' => $datos['direccion'],
                'telefono' => $datos['telefono'],
                'email' => $datos['email'],
                'horarios' => $datos['horarios'],
                'url' => get_permalink($ubicacion->ID),
                'excerpt' => get_the_excerpt($ubicacion->ID)
            );
        }
    }
    
    if (empty($datos_ubicaciones)) {
        return '<div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 30px; text-align: center; color: #856404; display: flex; align-items: center; justify-content: center;"><span class="material-icons" style="font-size: 48px; margin-right: 10px;">warning</span>Las ubicaciones encontradas no tienen coordenadas válidas.</div>';
    }
    
    $id_mapa = 'mapa-ubicaciones-' . uniqid();
    
    ob_start();
    ?>
    <div style="margin: 20px 0;">
        <div id="<?php echo esc_attr($id_mapa); ?>" 
             class="mapa-ubicaciones-shortcode" 
             style="height: <?php echo esc_attr($atts['altura']); ?>; width: 100%; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"
             data-ubicaciones="<?php echo esc_attr(json_encode($datos_ubicaciones)); ?>"
             data-zoom="<?php echo esc_attr($atts['zoom']); ?>">
            <div style="display: flex; align-items: center; justify-content: center; height: 100%; background: #f8f9fa; color: #6c757d;">
                <div style="text-align: center;">
                    <span class="material-icons" style="font-size: 48px; margin-bottom: 10px;">map</span>
                    <div>Cargando mapa...</div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Función para inicializar este mapa específico
        function inicializar<?php echo str_replace('-', '_', $id_mapa); ?>() {
            if (typeof google === 'undefined' || !google.maps) {
                console.error('Google Maps no disponible para shortcode');
                document.getElementById('<?php echo $id_mapa; ?>').innerHTML = 
                    '<div style="background: #f8d7da; color: #721c24; padding: 30px; text-align: center; border-radius: 8px; display: flex; align-items: center; justify-content: center;"><span class="material-icons" style="font-size: 24px; margin-right: 10px;">error</span><strong>Error:</strong> Google Maps no está disponible. Verifica la configuración de la API Key.</div>';
                return;
            }
            
            inicializarMapaShortcode('<?php echo $id_mapa; ?>');
        }
        
        // Intentar inicializar cuando el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(inicializar<?php echo str_replace('-', '_', $id_mapa); ?>, 1000);
            });
        } else {
            setTimeout(inicializar<?php echo str_replace('-', '_', $id_mapa); ?>, 1000);
        }
        
        // También intentar cuando Google Maps esté disponible
        window.addEventListener('load', function() {
            setTimeout(inicializar<?php echo str_replace('-', '_', $id_mapa); ?>, 2000);
        });
    </script>
    <?php
    
    return ob_get_clean();
}

/**
 * Forzar carga de scripts cuando se usa shortcode
 */
function forzar_carga_scripts_shortcode() {
    $api_key = get_option('google_maps_api_key', '');
    
    if (!empty($api_key) && !wp_script_is('google-maps-api-frontend-ubicaciones', 'enqueued')) {
        ?>
        <script>
            // Cargar Google Maps dinámicamente para shortcode
            if (typeof google === 'undefined') {
                console.log('Cargando Google Maps para shortcode...');
                var script = document.createElement('script');
                script.src = 'https://maps.googleapis.com/maps/api/js?key=<?php echo esc_js($api_key); ?>&libraries=places&callback=inicializarMapasFrontendUbicaciones';
                script.async = true;
                script.defer = true;
                document.head.appendChild(script);
                
                // Cargar Google Icons si no están ya cargados
                var iconLink = document.getElementById('google-material-icons-css');
                if (!iconLink) {
                    var link = document.createElement('link');
                    link.id = 'google-material-icons-css';
                    link.rel = 'stylesheet';
                    link.href = 'https://fonts.googleapis.com/icon?family=Material+Icons';
                    document.head.appendChild(link);
                }
                
                window.inicializarMapasFrontendUbicaciones = function() {
                    console.log('Google Maps cargado dinámicamente para shortcode');
                    if (typeof inicializarShortcodeMapas === 'function') {
                        inicializarShortcodeMapas();
                    }
                };
            }
        </script>
        <?php
    }
}
add_shortcode('mapa_ubicaciones', 'shortcode_mapa_ubicaciones');