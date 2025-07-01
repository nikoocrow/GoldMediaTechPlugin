<?php
/**
 * Custom Post Type para Ubicaciones
 * Este archivo registra el CPT de ubicaciones con Google Maps
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registrar el Custom Post Type de Ubicaciones
 */
function registrar_ubicaciones_cpt() {
    // Etiquetas para el CPT
    $labels = array(
        'name'                  => 'Ubicaciones',
        'singular_name'         => 'Ubicación',
        'menu_name'             => 'Ubicaciones',
        'name_admin_bar'        => 'Ubicación',
        'archives'              => 'Archivo de Ubicaciones',
        'attributes'            => 'Atributos de Ubicación',
        'parent_item_colon'     => 'Ubicación Padre:',
        'all_items'             => 'Todas las Ubicaciones',
        'add_new_item'          => 'Agregar Nueva Ubicación',
        'add_new'               => 'Agregar Nueva',
        'new_item'              => 'Nueva Ubicación',
        'edit_item'             => 'Editar Ubicación',
        'update_item'           => 'Actualizar Ubicación',
        'view_item'             => 'Ver Ubicación',
        'view_items'            => 'Ver Ubicaciones',
        'search_items'          => 'Buscar Ubicaciones',
        'not_found'             => 'No se encontraron ubicaciones',
        'not_found_in_trash'    => 'No hay ubicaciones en la papelera',
        'featured_image'        => 'Imagen destacada',
        'set_featured_image'    => 'Establecer imagen destacada',
        'remove_featured_image' => 'Remover imagen destacada',
        'use_featured_image'    => 'Usar como imagen destacada',
        'insert_into_item'      => 'Insertar en ubicación',
        'uploaded_to_this_item' => 'Subido a esta ubicación',
        'items_list'            => 'Lista de ubicaciones',
        'items_list_navigation' => 'Navegación de lista de ubicaciones',
        'filter_items_list'     => 'Filtrar lista de ubicaciones',
    );

    // Argumentos del CPT
    $args = array(
        'label'                 => 'Ubicación',
        'description'           => 'Custom Post Type para manejar ubicaciones con Google Maps',
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'taxonomies'            => array('category', 'post_tag'), // Permitir categorías y etiquetas
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 20,
        'menu_icon'             => 'dashicons-location', //dashicons-location-alt
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => 'ubicaciones', // Archivo personalizado
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true, // Para Gutenberg y API REST
        'rewrite'               => array(
            'slug'       => 'ubicacion',
            'with_front' => false,
        ),
    );

    // Registrar el CPT
    register_post_type('ubicaciones', $args);
}

// Hook para registrar el CPT
add_action('init', 'registrar_ubicaciones_cpt', 0);

/**
 * Agregar columnas personalizadas en la lista de ubicaciones del admin
 */
function agregar_columnas_ubicaciones($columnas) {
    // Insertar columnas después del título
    $nuevas_columnas = array();
    foreach ($columnas as $key => $valor) {
        $nuevas_columnas[$key] = $valor;
        if ($key == 'title') {
            $nuevas_columnas['ubicacion_direccion'] = 'Dirección';
            $nuevas_columnas['ubicacion_coordenadas'] = 'Coordenadas';
        }
    }
    return $nuevas_columnas;
}
add_filter('manage_ubicaciones_posts_columns', 'agregar_columnas_ubicaciones');

/**
 * Mostrar contenido de las columnas personalizadas
 */
function mostrar_contenido_columnas_ubicaciones($columna, $post_id) {
    switch ($columna) {
        case 'ubicacion_direccion':
            $direccion = get_post_meta($post_id, '_ubicacion_direccion', true);
            echo $direccion ? esc_html($direccion) : '<em>Sin dirección</em>';
            break;
            
        case 'ubicacion_coordenadas':
            $latitud = get_post_meta($post_id, '_ubicacion_latitud', true);
            $longitud = get_post_meta($post_id, '_ubicacion_longitud', true);
            
            if ($latitud && $longitud) {
                echo '<small>' . esc_html($latitud) . ', ' . esc_html($longitud) . '</small>';
            } else {
                echo '<em>Sin coordenadas</em>';
            }
            break;
    }
}
add_action('manage_ubicaciones_posts_custom_column', 'mostrar_contenido_columnas_ubicaciones', 10, 2);

/**
 * Hacer las columnas personalizadas ordenables
 */
function hacer_columnas_ubicaciones_ordenables($columnas) {
    $columnas['ubicacion_direccion'] = 'ubicacion_direccion';
    return $columnas;
}
add_filter('manage_edit-ubicaciones_sortable_columns', 'hacer_columnas_ubicaciones_ordenables');

/**
 * Personalizar la consulta de ordenamiento
 */
function orderby_ubicaciones_columnas($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    $orderby = $query->get('orderby');

    if ('ubicacion_direccion' == $orderby) {
        $query->set('meta_key', '_ubicacion_direccion');
        $query->set('orderby', 'meta_value');
    }
}
add_action('pre_get_posts', 'orderby_ubicaciones_columnas');

/**
 * Personalizar mensajes de actualización para el CPT
 */
function mensajes_ubicaciones_actualizadas($mensajes) {
    $post = get_post();
    
    $mensajes['ubicaciones'] = array(
        0  => '', // Sin usar. Los mensajes comienzan en índice 1.
        1  => sprintf('Ubicación actualizada. <a href="%s">Ver ubicación</a>', esc_url(get_permalink($post->ID))),
        2  => 'Campo personalizado actualizado.',
        3  => 'Campo personalizado eliminado.',
        4  => 'Ubicación actualizada.',
        5  => isset($_GET['revision']) ? sprintf('Ubicación restaurada desde revisión del %s', wp_post_revision_title((int) $_GET['revision'], false)) : false,
        6  => sprintf('Ubicación publicada. <a href="%s">Ver ubicación</a>', esc_url(get_permalink($post->ID))),
        7  => 'Ubicación guardada.',
        8  => sprintf('Ubicación enviada. <a target="_blank" href="%s">Vista previa</a>', esc_url(add_query_arg('preview', 'true', get_permalink($post->ID)))),
        9  => sprintf('Ubicación programada para: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Vista previa</a>', date_i18n('M j, Y @ G:i', strtotime($post->post_date)), esc_url(get_permalink($post->ID))),
        10 => sprintf('Borrador de ubicación actualizado. <a target="_blank" href="%s">Vista previa</a>', esc_url(add_query_arg('preview', 'true', get_permalink($post->ID)))),
    );

    return $mensajes;
}
add_filter('post_updated_messages', 'mensajes_ubicaciones_actualizadas');