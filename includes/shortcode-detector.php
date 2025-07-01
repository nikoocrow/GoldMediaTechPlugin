<?php
/**
 * Detector de shortcodes para cargar scripts de Google Maps
 * Este archivo detecta si hay shortcodes de mapas en el contenido
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Detectar shortcodes temprano en el proceso de carga
 */
function detectar_shortcodes_ubicaciones() {
    global $post;
    
    // Solo verificar en el frontend
    if (is_admin()) {
        return;
    }
    
    // Verificar el post actual
    if ($post && !empty($post->post_content)) {
        if (has_shortcode($post->post_content, 'mapa_ubicaciones')) {
            add_action('wp_enqueue_scripts', 'forzar_carga_google_maps_shortcode', 5);
        }
    }
    
    // Verificar widgets
    $widgets = get_option('widget_text', array());
    foreach ($widgets as $widget) {
        if (is_array($widget) && isset($widget['text'])) {
            if (strpos($widget['text'], '[mapa_ubicaciones') !== false) {
                add_action('wp_enqueue_scripts', 'forzar_carga_google_maps_shortcode', 5);
                break;
            }
        }
    }
    
    // Verificar widgets de bloques (Gutenberg)
    $widget_blocks = get_option('widget_block', array());
    foreach ($widget_blocks as $widget_block) {
        if (is_array($widget_block) && isset($widget_block['content'])) {
            if (strpos($widget_block['content'], '[mapa_ubicaciones') !== false) {
                add_action('wp_enqueue_scripts', 'forzar_carga_google_maps_shortcode', 5);
                break;
            }
        }
    }
}
add_action('wp', 'detectar_shortcodes_ubicaciones');

/**
 * Forzar carga de Google Maps cuando se detecta shortcode
 */
function forzar_carga_google_maps_shortcode() {
    $api_key = get_option('google_maps_api_key', '');
    
    if (empty($api_key)) {
        return;
    }
    
    // Prevenir múltiples cargas
    if (wp_script_is('google-maps-api-frontend-ubicaciones', 'enqueued')) {
        return;
    }
    
    // Cargar Google Maps
    wp_enqueue_script(
        'google-maps-api-frontend-ubicaciones',
        'https://maps.googleapis.com/maps/api/js?key=' . $api_key . '&libraries=places&callback=inicializarMapasFrontendUbicaciones',
        array(),
        UBICACIONES_PLUGIN_VERSION,
        true
    );
    
    // Cargar nuestro script frontend
    wp_enqueue_script(
        'ubicaciones-frontend-js',
        UBICACIONES_PLUGIN_URL . 'src/js/ubicaciones-frontend.js',
        array('google-maps-api-frontend-ubicaciones'),
        UBICACIONES_PLUGIN_VERSION,
        true
    );
    
    // Cargar CSS
    wp_enqueue_style(
        'ubicaciones-frontend-css',
        UBICACIONES_PLUGIN_URL . 'src/scss/ubicaciones-frontend.css',
        array(),
        UBICACIONES_PLUGIN_VERSION
    );
    
    // Pasar datos al script
    wp_localize_script('ubicaciones-frontend-js', 'ubicacionesData', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ubicaciones_frontend_nonce'),
        'plugin_url' => UBICACIONES_PLUGIN_URL,
        'debug' => WP_DEBUG,
        'shortcode_detected' => true
    ));
    
    // Script para manejar el callback
    wp_add_inline_script('google-maps-api-frontend-ubicaciones', '
        window.inicializarMapasFrontendUbicaciones = function() {
            console.log("Google Maps callback ejecutado (shortcode detectado)");
            // Pequeño delay para asegurar que el DOM esté listo
            setTimeout(function() {
                if (typeof inicializarMapasFrontend === "function") {
                    inicializarMapasFrontend();
                }
                if (typeof inicializarShortcodeMapas === "function") {
                    inicializarShortcodeMapas();
                }
            }, 500);
        };
    ', 'before');
}

/**
 * Agregar scripts de respaldo en el footer si no se cargaron
 */
function scripts_respaldo_shortcode() {
    // Solo si no estamos en admin
    if (is_admin()) {
        return;
    }
    
    // Verificar si hay mapas shortcode en la página
    $content = ob_get_contents();
    if ($content && strpos($content, 'mapa-ubicaciones-shortcode') !== false) {
        ?>
        <script>
            // Script de respaldo para shortcodes
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    var shortcodeMaps = document.querySelectorAll('.mapa-ubicaciones-shortcode');
                    
                    if (shortcodeMaps.length > 0) {
                        console.log('Detectados ' + shortcodeMaps.length + ' mapas shortcode');
                        
                        if (typeof google === 'undefined') {
                            console.warn('Google Maps no disponible, cargando scripts de respaldo...');
                            
                            // Cargar Google Maps dinámicamente
                            var apiKey = '<?php echo esc_js(get_option('google_maps_api_key', '')); ?>';
                            if (apiKey) {
                                var script = document.createElement('script');
                                script.src = 'https://maps.googleapis.com/maps/api/js?key=' + apiKey + '&libraries=places&callback=inicializarMapasFrontendUbicaciones';
                                script.async = true;
                                script.defer = true;
                                
                                script.onerror = function() {
                                    console.error('Error cargando Google Maps API');
                                    shortcodeMaps.forEach(function(map) {
                                        map.innerHTML = '<div style="background: #f8d7da; color: #721c24; padding: 30px; text-align: center; border-radius: 8px;"><strong>Error:</strong> No se pudo cargar Google Maps. Verifica la configuración de la API Key.</div>';
                                    });
                                };
                                
                                document.head.appendChild(script);
                            } else {
                                shortcodeMaps.forEach(function(map) {
                                    map.innerHTML = '<div style="background: #fff3cd; color: #856404; padding: 30px; text-align: center; border-radius: 8px;"><strong>Configuración requerida:</strong> Es necesario configurar la API Key de Google Maps.</div>';
                                });
                            }
                        } else {
                            // Google Maps ya está disponible
                            if (typeof inicializarShortcodeMapas === 'function') {
                                inicializarShortcodeMapas();
                            }
                        }
                    }
                }, 1000);
            });
        </script>
        <?php
    }
}
add_action('wp_footer', 'scripts_respaldo_shortcode', 999);

/**
 * Debug de shortcodes (solo para administradores)
 */
function debug_shortcodes_ubicaciones() {
    if (!current_user_can('administrator') || !isset($_GET['debug_shortcodes'])) {
        return;
    }
    
    global $post;
    ?>
    <div style="background: white; border: 2px solid #0073aa; padding: 20px; margin: 20px; position: fixed; top: 50px; right: 20px; z-index: 9999; max-width: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
        <h3 style="margin: 0 0 15px 0; color: #0073aa;">🔍 Debug Shortcodes</h3>
        
        <p><strong>Post actual:</strong> <?php echo $post ? $post->ID . ' - ' . get_the_title() : 'Ninguno'; ?></p>
        
        <p><strong>Shortcode detectado:</strong> 
            <?php echo ($post && has_shortcode($post->post_content, 'mapa_ubicaciones')) ? '✅ Sí' : '❌ No'; ?>
        </p>
        
        <p><strong>Scripts cargados:</strong></p>
        <ul style="font-size: 12px; margin: 5px 0 0 20px;">
            <li>Google Maps: <?php echo wp_script_is('google-maps-api-frontend-ubicaciones', 'enqueued') ? '✅' : '❌'; ?></li>
            <li>Frontend JS: <?php echo wp_script_is('ubicaciones-frontend-js', 'enqueued') ? '✅' : '❌'; ?></li>
            <li>Frontend CSS: <?php echo wp_style_is('ubicaciones-frontend-css', 'enqueued') ? '✅' : '❌'; ?></li>
        </ul>
        
        <p><strong>API Key:</strong> <?php echo !empty(get_option('google_maps_api_key')) ? '✅ Configurada' : '❌ Faltante'; ?></p>
        
        <p style="margin-top: 15px;">
            <a href="<?php echo remove_query_arg('debug_shortcodes'); ?>" style="background: #0073aa; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 12px;">Cerrar debug</a>
        </p>
    </div>
    <?php
}
add_action('wp_footer', 'debug_shortcodes_ubicaciones');