<?php
/**
 * Plugin Name: Ubicaciones Gold Media Test
 * Plugin URI: https://goldmediatech.com
 * Description: Custom Post Type para ubicaciones con integración de Google Maps API. Permite gestionar ubicaciones con coordenadas, direcciones y mapas interactivos.
 * Version: 1.0.0
 * Author: Gold Media Tech
 * License: GPL v2 or later
 * Text Domain: ubicaciones-gold-media
 */

// Evitar acceso directo al archivo
if (!defined('ABSPATH')) {
    exit('No se permite el acceso directo a este archivo.');
}

// Definir constantes del plugin
define('UBICACIONES_PLUGIN_URL', plugin_dir_url(__FILE__));
define('UBICACIONES_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('UBICACIONES_PLUGIN_VERSION', '1.0.0');

/**
 * Función de activación del plugin
 */
function activar_ubicaciones_plugin() {
    // Incluir el archivo CPT para registrar el post type
    require_once UBICACIONES_PLUGIN_PATH . 'includes/ubicaciones-cpt.php';
    
    // Registrar el CPT
    registrar_ubicaciones_cpt();
    
    // Refrescar las reglas de reescritura
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'activar_ubicaciones_plugin');

/**
 * Función de desactivación del plugin
 */
function desactivar_ubicaciones_plugin() {
    // Refrescar las reglas de reescritura
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'desactivar_ubicaciones_plugin');

/**
 * Incluir archivos necesarios del plugin
 */
function incluir_archivos_ubicaciones() {
    // Archivos principales
    require_once UBICACIONES_PLUGIN_PATH . 'includes/ubicaciones-cpt.php';
    require_once UBICACIONES_PLUGIN_PATH . 'includes/ubicaciones-metaboxes.php';
    require_once UBICACIONES_PLUGIN_PATH . 'includes/google-maps-config.php';
    
    // Verificar si el archivo existe antes de incluirlo
    if (file_exists(UBICACIONES_PLUGIN_PATH . 'includes/shortcode-detector.php')) {
        require_once UBICACIONES_PLUGIN_PATH . 'includes/shortcode-detector.php';
    }
    
    if (file_exists(UBICACIONES_PLUGIN_PATH . 'includes/ajax-handler.php')) {
        require_once UBICACIONES_PLUGIN_PATH . 'includes/ajax-handler.php';
    }
}
add_action('plugins_loaded', 'incluir_archivos_ubicaciones');

/**
 * Agregar enlaces de acción en la página de plugins
 */
function ubicaciones_enlaces_plugin($enlaces) {
    $enlaces_configuracion = array(
        '<a href="' . admin_url('options-general.php?page=ubicaciones-settings') . '">Configuración</a>',
        '<a href="' . admin_url('edit.php?post_type=ubicaciones') . '">Ubicaciones</a>'
    );
    return array_merge($enlaces_configuracion, $enlaces);
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'ubicaciones_enlaces_plugin');

/**
 * Función para verificar si el plugin está configurado correctamente
 */
function verificar_configuracion_ubicaciones() {
    $api_key = get_option('google_maps_api_key', '');
    if (empty($api_key)) {
        add_action('admin_notices', function() {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <strong>Plugin Ubicaciones:</strong> 
                    <p>Para usar todas las funcionalidades necesitas configurar tu API Key de Google Maps. </p>
                    <a href="<?php echo admin_url('options-general.php?page=ubicaciones-settings'); ?>">
                        Configurar ahora
                    </a>
                </p>
            </div>
            <?php
        });
    }
}
add_action('admin_init', 'verificar_configuracion_ubicaciones');