<?php
/**
 * Meta Boxes para el CPT de Ubicaciones
 * Maneja los campos personalizados para coordenadas y datos de Google Maps
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Agregar meta boxes al CPT de ubicaciones
 */
function agregar_ubicaciones_metaboxes() {
    add_meta_box(
        'ubicaciones_datos_maps',           // ID del meta box
        'Datos de Google Maps',             // Título
        'mostrar_ubicaciones_metabox',      // Función callback
        'ubicaciones',                      // Post type
        'normal',                           // Contexto
        'high'                              // Prioridad
    );
}
add_action('add_meta_boxes', 'agregar_ubicaciones_metaboxes');

/**
 * Mostrar el contenido del meta box
 */
function mostrar_ubicaciones_metabox($post) {
    // Nonce para seguridad
    wp_nonce_field(basename(__FILE__), 'ubicaciones_nonce');
    
    // Obtener valores guardados
    $latitud = get_post_meta($post->ID, '_ubicacion_latitud', true);
    $longitud = get_post_meta($post->ID, '_ubicacion_longitud', true);
    $direccion = get_post_meta($post->ID, '_ubicacion_direccion', true);
    $place_id = get_post_meta($post->ID, '_ubicacion_place_id', true);
    $telefono = get_post_meta($post->ID, '_ubicacion_telefono', true);
    $email = get_post_meta($post->ID, '_ubicacion_email', true);
    $horarios = get_post_meta($post->ID, '_ubicacion_horarios', true);
    
    ?>
    <style>
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
    </style>
    
    <div class="ubicaciones-metabox">
        <table class="form-table">
            <tr>
                <th><label for="ubicacion_direccion">Dirección:</label></th>
                <td>
                    <input type="text" 
                           id="ubicacion_direccion" 
                           name="ubicacion_direccion" 
                           value="<?php echo esc_attr($direccion); ?>" 
                           placeholder="Ingresa la dirección completa">
                    <button type="button" id="buscar_direccion" class="button">Buscar en Maps</button>
                    <p class="description">Ingresa una dirección y presiona "Buscar en Maps" para obtener las coordenadas automáticamente.</p>
                </td>
            </tr>
            <tr>
                <th><label>Coordenadas:</label></th>
                <td>
                    <div class="coordenadas-grupo">
                        <div>
                            <label for="ubicacion_latitud">Latitud:</label>
                            <input type="text" 
                                   id="ubicacion_latitud" 
                                   name="ubicacion_latitud" 
                                   value="<?php echo esc_attr($latitud); ?>" 
                                   placeholder="Ej: 4.6097">
                        </div>
                        <div>
                            <label for="ubicacion_longitud">Longitud:</label>
                            <input type="text" 
                                   id="ubicacion_longitud" 
                                   name="ubicacion_longitud" 
                                   value="<?php echo esc_attr($longitud); ?>" 
                                   placeholder="Ej: -74.0817">
                        </div>
                    </div>
                    <p class="description">Las coordenadas se llenan automáticamente al buscar la dirección. También puedes hacer clic en el mapa para seleccionar una ubicación.</p>
                </td>
            </tr>
            <tr>
                <th><label for="ubicacion_telefono">Teléfono:</label></th>
                <td>
                    <input type="tel" 
                           id="ubicacion_telefono" 
                           name="ubicacion_telefono" 
                           value="<?php echo esc_attr($telefono); ?>" 
                           placeholder="Ej: +57 1 234 5678">
                    <p class="description">Número de teléfono de contacto para esta ubicación.</p>
                </td>
            </tr>
            <tr>
                <th><label for="ubicacion_email">Email:</label></th>
                <td>
                    <input type="email" 
                           id="ubicacion_email" 
                           name="ubicacion_email" 
                           value="<?php echo esc_attr($email); ?>" 
                           placeholder="contacto@ejemplo.com">
                    <p class="description">Email de contacto para esta ubicación.</p>
                </td>
            </tr>
            <tr>
                <th><label for="ubicacion_horarios">Horarios:</label></th>
                <td>
                    <textarea id="ubicacion_horarios" 
                              name="ubicacion_horarios" 
                              placeholder="Lunes a Viernes: 8:00 AM - 6:00 PM&#10;Sábados: 9:00 AM - 2:00 PM&#10;Domingos: Cerrado"><?php echo esc_textarea($horarios); ?></textarea>
                    <p class="description">Horarios de atención de esta ubicación.</p>
                </td>
            </tr>
            <tr>
                <th><label for="ubicacion_place_id">Google Place ID:</label></th>
                <td>
                    <input type="text" 
                           id="ubicacion_place_id" 
                           name="ubicacion_place_id" 
                           value="<?php echo esc_attr($place_id); ?>" 
                           readonly>
                    <p class="description">ID único de Google Places (se llena automáticamente al buscar la dirección).</p>
                </td>
            </tr>
        </table>
        
        <!-- Contenedor para el mapa -->
        <h4>Mapa Interactivo:</h4>
        <div id="mapa_ubicacion" style="height: 400px; width: 100%; margin-top: 10px; border: 1px solid #ddd;"></div>
        <p class="description" style="margin-top: 10px;">
            <strong>Instrucciones:</strong> Haz clic en el mapa para seleccionar una ubicación, o arrastra el marcador para cambiar la posición. También puedes buscar una dirección usando el campo de arriba.
        </p>
    </div>
    
    <script>
        // Variables globales para el mapa
        let map;
        let marker;
        let geocoder;
        
        // Inicializar el mapa cuando se carga la página
        function inicializarMapa() {
            // Coordenadas por defecto (Bogotá, Colombia)
            const coordenadasIniciales = {
                lat: <?php echo !empty($latitud) ? floatval($latitud) : '4.6097'; ?>,
                lng: <?php echo !empty($longitud) ? floatval($longitud) : '-74.0817'; ?>
            };
            
            // Crear el mapa
            map = new google.maps.Map(document.getElementById('mapa_ubicacion'), {
                zoom: 13,
                center: coordenadasIniciales,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            });
            
            // Crear el geocoder
            geocoder = new google.maps.Geocoder();
            
            // Crear marcador
            marker = new google.maps.Marker({
                position: coordenadasIniciales,
                map: map,
                draggable: true,
                title: 'Arrastra para cambiar ubicación'
            });
            
            // Evento cuando se arrastra el marcador
            marker.addListener('dragend', function(event) {
                actualizarCoordenadas(event.latLng.lat(), event.latLng.lng());
            });
            
            // Evento de clic en el mapa
            map.addListener('click', function(event) {
                marker.setPosition(event.latLng);
                actualizarCoordenadas(event.latLng.lat(), event.latLng.lng());
            });
        }
        
        // Función para actualizar las coordenadas en los campos
        function actualizarCoordenadas(lat, lng) {
            document.getElementById('ubicacion_latitud').value = lat.toFixed(6);
            document.getElementById('ubicacion_longitud').value = lng.toFixed(6);
            
            // Obtener la dirección mediante geocoding inverso
            geocoder.geocode({location: {lat: lat, lng: lng}}, function(results, status) {
                if (status === 'OK' && results[0]) {
                    document.getElementById('ubicacion_direccion').value = results[0].formatted_address;
                    document.getElementById('ubicacion_place_id').value = results[0].place_id;
                }
            });
        }
        
        // Función para buscar dirección
        function buscarDireccion() {
            const direccion = document.getElementById('ubicacion_direccion').value;
            if (!direccion) {
                alert('Por favor ingresa una dirección');
                return;
            }
            
            geocoder.geocode({address: direccion}, function(results, status) {
                if (status === 'OK' && results[0]) {
                    const location = results[0].geometry.location;
                    
                    // Centrar mapa y mover marcador
                    map.setCenter(location);
                    marker.setPosition(location);
                    
                    // Actualizar campos
                    document.getElementById('ubicacion_latitud').value = location.lat().toFixed(6);
                    document.getElementById('ubicacion_longitud').value = location.lng().toFixed(6);
                    document.getElementById('ubicacion_direccion').value = results[0].formatted_address;
                    document.getElementById('ubicacion_place_id').value = results[0].place_id;
                } else {
                    alert('No se pudo encontrar la dirección: ' + status);
                }
            });
        }
        
        // Evento para el botón de búsqueda
        document.addEventListener('DOMContentLoaded', function() {
            const botonBuscar = document.getElementById('buscar_direccion');
            if (botonBuscar) {
                botonBuscar.addEventListener('click', buscarDireccion);
            }
            
            // También permitir buscar con Enter
            const campoDireccion = document.getElementById('ubicacion_direccion');
            if (campoDireccion) {
                campoDireccion.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        buscarDireccion();
                    }
                });
            }
        });
    </script>
    <?php
}

/**
 * Guardar los datos del meta box
 */
function guardar_ubicaciones_metabox($post_id) {
    // Verificar nonce
    if (!isset($_POST['ubicaciones_nonce']) || !wp_verify_nonce($_POST['ubicaciones_nonce'], basename(__FILE__))) {
        return $post_id;
    }
    
    // Verificar permisos
    if (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }
    
    // Verificar que no sea un auto-save
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }
    
    // Verificar que sea el post type correcto
    if (get_post_type($post_id) !== 'ubicaciones') {
        return $post_id;
    }
    
    // Definir campos a guardar
    $campos = array(
        'ubicacion_latitud' => '_ubicacion_latitud',
        'ubicacion_longitud' => '_ubicacion_longitud',
        'ubicacion_direccion' => '_ubicacion_direccion',
        'ubicacion_place_id' => '_ubicacion_place_id',
        'ubicacion_telefono' => '_ubicacion_telefono',
        'ubicacion_email' => '_ubicacion_email',
        'ubicacion_horarios' => '_ubicacion_horarios'
    );
    
    // Guardar cada campo
    foreach ($campos as $campo => $meta_key) {
        if (isset($_POST[$campo])) {
            $valor = $_POST[$campo];
            
            // Sanitizar según el tipo de campo
            switch ($campo) {
                case 'ubicacion_email':
                    $valor = sanitize_email($valor);
                    break;
                case 'ubicacion_horarios':
                    $valor = sanitize_textarea_field($valor);
                    break;
                case 'ubicacion_latitud':
                case 'ubicacion_longitud':
                    $valor = floatval($valor);
                    break;
                default:
                    $valor = sanitize_text_field($valor);
            }
            
            // Actualizar el meta field
            if (!empty($valor)) {
                update_post_meta($post_id, $meta_key, $valor);
            } else {
                delete_post_meta($post_id, $meta_key);
            }
        }
    }
}
add_action('save_post', 'guardar_ubicaciones_metabox');

/**
 * Funciones auxiliares para obtener datos de ubicación
 */

// Función para obtener las coordenadas de una ubicación
function obtener_coordenadas_ubicacion($post_id) {
    $latitud = get_post_meta($post_id, '_ubicacion_latitud', true);
    $longitud = get_post_meta($post_id, '_ubicacion_longitud', true);
    
    if ($latitud && $longitud) {
        return array(
            'lat' => floatval($latitud),
            'lng' => floatval($longitud)
        );
    }
    
    return false;
}

// Función para obtener todos los datos de una ubicación
function obtener_datos_ubicacion($post_id) {
    return array(
        'latitud' => get_post_meta($post_id, '_ubicacion_latitud', true),
        'longitud' => get_post_meta($post_id, '_ubicacion_longitud', true),
        'direccion' => get_post_meta($post_id, '_ubicacion_direccion', true),
        'place_id' => get_post_meta($post_id, '_ubicacion_place_id', true),
        'telefono' => get_post_meta($post_id, '_ubicacion_telefono', true),
        'email' => get_post_meta($post_id, '_ubicacion_email', true),
        'horarios' => get_post_meta($post_id, '_ubicacion_horarios', true)
    );
}