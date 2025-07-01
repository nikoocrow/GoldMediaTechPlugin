<?php
/**
 * Template para el archivo de ubicaciones con Google Font Icons
 * Archivo: archive-ubicaciones.php
 * Coloca este archivo en la carpeta de tu tema activo
 */

get_header(); ?>

<div class="ubicaciones-archive">
    
    <!-- Header del archivo -->
    <header class="page-header">
        <h1 class="page-title">Nuestras Ubicaciones</h1>
        <p class="archive-description">Encuentra todas nuestras ubicaciones disponibles</p>
    </header>
    
    <!-- Mapa con todas las ubicaciones -->
    <?php
    // Obtener todas las ubicaciones con coordenadas
    $ubicaciones_query = new WP_Query(array(
        'post_type' => 'ubicaciones',
        'post_status' => 'publish',
        'posts_per_page' => -1,
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
    ));
    
    if ($ubicaciones_query->have_posts()) :
        // Preparar datos para el mapa
        $ubicaciones_mapa = array();
        while ($ubicaciones_query->have_posts()) : $ubicaciones_query->the_post();
            $datos = obtener_datos_ubicacion(get_the_ID());
            if ($datos['latitud'] && $datos['longitud']) {
                $ubicaciones_mapa[] = array(
                    'id' => get_the_ID(),
                    'titulo' => get_the_title(),
                    'lat' => floatval($datos['latitud']),
                    'lng' => floatval($datos['longitud']),
                    'direccion' => $datos['direccion'],
                    'telefono' => $datos['telefono'],
                    'email' => $datos['email'],
                    'horarios' => $datos['horarios'],
                    'url' => get_permalink(),
                    'excerpt' => get_the_excerpt()
                );
            }
        endwhile;
        wp_reset_postdata();
        
        if (!empty($ubicaciones_mapa)) : ?>
        <div class="mapa-general">
            <h2>Mapa General</h2>
            <div id="mapa-archivo-ubicaciones" 
                 data-ubicaciones="<?php echo esc_attr(json_encode($ubicaciones_mapa)); ?>">
            </div>
        </div>
        <?php endif;
    endif; ?>
    
    <!-- Filtros de búsqueda -->
    <div class="ubicaciones-filtros">
        <h3><span class="material-icons">search</span> Filtrar ubicaciones</h3>
        <form class="filtros-form" method="GET">
            <div class="campo">
                <label for="buscar_texto">Buscar por texto:</label>
                <input type="text" 
                       id="buscar_texto" 
                       name="buscar" 
                       value="<?php echo esc_attr(get_query_var('buscar')); ?>" 
                       placeholder="Nombre, dirección...">
            </div>
            
            <div class="campo">
                <label for="categoria_filtro">Categoría:</label>
                <select id="categoria_filtro" name="categoria">
                    <option value="">Todas las categorías</option>
                    <?php
                    $categorias = get_categories(array(
                        'taxonomy' => 'category',
                        'hide_empty' => true
                    ));
                    foreach ($categorias as $categoria) :
                        $selected = (get_query_var('categoria') == $categoria->slug) ? 'selected' : '';
                        ?>
                        <option value="<?php echo esc_attr($categoria->slug); ?>" <?php echo $selected; ?>>
                            <?php echo esc_html($categoria->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="campo">
                <button type="submit">Filtrar</button>
            </div>
        </form>
    </div>
    
    <!-- Lista de ubicaciones -->
    <div class="ubicaciones-contenido">
        
        <?php if (have_posts()) : ?>
            
            <!-- Información de resultados -->
            <div class="resultados-info">
                <p>Se encontraron <strong><?php echo $wp_query->found_posts; ?></strong> ubicaciones</p>
            </div>
            
            <!-- Grid de ubicaciones -->
            <div class="ubicaciones-grid">
                
                <?php while (have_posts()) : the_post(); 
                    $datos_ubicacion = obtener_datos_ubicacion(get_the_ID());
                    $coordenadas = obtener_coordenadas_ubicacion(get_the_ID());
                ?>
                
                <article id="post-<?php the_ID(); ?>" <?php post_class('ubicacion-card'); ?>>
                    
                    <!-- Imagen destacada -->
                    <?php if (has_post_thumbnail()) : ?>
                    <div class="thumbnail">
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail('medium', array('alt' => get_the_title())); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Contenido -->
                    <div class="content">
                        <h3>
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>
                        
                        <?php if (has_excerpt()) : ?>
                        <div class="excerpt">
                            <?php the_excerpt(); ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Meta información -->
                        <div class="meta-info">
                            
                            <?php if ($datos_ubicacion['direccion']) : ?>
                            <div class="meta-item">
                                <span class="material-icons">location_on</span>
                                <span><?php echo esc_html($datos_ubicacion['direccion']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($datos_ubicacion['telefono']) : ?>
                            <div class="meta-item">
                                <span class="material-icons">phone</span>
                                <span><a href="tel:<?php echo esc_attr($datos_ubicacion['telefono']); ?>"><?php echo esc_html($datos_ubicacion['telefono']); ?></a></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($coordenadas) : ?>
                            <div class="meta-item">
                                <span class="material-icons">email</span>
                                <span>
                                    <button onclick="abrirModalContacto('<?php echo get_the_ID(); ?>', '<?php echo esc_js(get_the_title()); ?>')" 
                                            class="boton-contactar">
                                        Contactar
                                    </button>
                                </span>
                            </div>
                            
                            <div class="meta-item">
                                <span class="material-icons">directions</span>
                                <span>
                                    <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo esc_attr($coordenadas['lat'] . ',' . $coordenadas['lng']); ?>" 
                                       target="_blank">
                                        Cómo llegar
                                    </a>
                                </span>
                            </div>
                            <?php endif; ?>
                            
                        </div>
                        
                        <!-- Enlace para ver más -->
                        <div class="ver-mas">
                            <a href="<?php the_permalink(); ?>" class="boton-ver-mas">
                                Ver detalles completos
                            </a>
                        </div>
                        
                    </div>
                    
                </article>
                
                <?php endwhile; ?>
                
            </div>
            
            <!-- Paginación -->
            <div class="ubicaciones-paginacion">
                <?php
                the_posts_pagination(array(
                    'mid_size' => 2,
                    'prev_text' => 'Anterior',
                    'next_text' => 'Siguiente',
                ));
                ?>
            </div>
            
        <?php else : ?>
            
            <!-- No se encontraron ubicaciones -->
            <div class="no-ubicaciones">
                <span class="material-icons no-results-icon">location_off</span>
                <h2>No se encontraron ubicaciones</h2>
                <p>Lo sentimos, no hay ubicaciones que coincidan con tu búsqueda.</p>
                <p><a href="<?php echo get_post_type_archive_link('ubicaciones'); ?>" class="boton-reset">Ver todas las ubicaciones</a></p>
            </div>
            
        <?php endif; ?>
        
    </div>
    
</div>

<style>
/* Importar Google Fonts - Material Icons */
@import url('https://fonts.googleapis.com/icon?family=Material+Icons');

/* Variables de colores */
:root {
    --primary-color: #1B1B1B;
    --secondary-color: #64646A;
    --success-color: #EEEFF1;
    --info-color: #F3F4F5;
    --white-color: #FFFFFF;
}

/* Estilos específicos para el template de archivo */
.page-header {
    text-align: center;
    margin-bottom: 40px;
    padding: 40px 20px;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: var(--white-color);
    border-radius: 8px;
}

.page-header .page-title {
    font-size: 2.5em;
    margin: 0 0 10px 0;
    font-weight: 700;
}

.page-header .archive-description {
    font-size: 1.1em;
    margin: 0;
    opacity: 0.9;
}

/* Estilos para Material Icons */
.material-icons {
    font-family: 'Material Icons';
    font-weight: normal;
    font-style: normal;
    font-size: 18px;
    line-height: 1;
    letter-spacing: normal;
    text-transform: none;
    display: inline-block;
    white-space: nowrap;
    word-wrap: normal;
    direction: ltr;
    -webkit-font-smoothing: antialiased;
    vertical-align: middle;
    margin-right: 8px;
}

/* Ajustes específicos para meta-items */
.meta-item .material-icons {
    color: var(--primary-color);
    font-size: 16px;
    margin-right: 6px;
    vertical-align: text-top;
}

/* Filtros con iconos */
.ubicaciones-filtros h3 .material-icons {
    color: var(--secondary-color);
    font-size: 20px;
    vertical-align: text-bottom;
}

/* Botones */
.boton-contactar {
    background: var(--primary-color);
    color: var(--white-color);
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    transition: background 0.2s ease;
}

.boton-contactar:hover {
    background: var(--secondary-color);
}

.boton-ver-mas {
    background: var(--primary-color);
    color: var(--white-color);
    padding: 8px 16px;
    text-decoration: none;
    border-radius: 4px;
    font-size: 0.9em;
    font-weight: 600;
    display: inline-block;
    margin-top: 10px;
    transition: background 0.2s ease;
}

.boton-ver-mas:hover {
    background: var(--secondary-color);
    color: var(--white-color);
    text-decoration: none;
}

/* Icono de no resultados */
.no-results-icon {
    font-size: 48px !important;
    color: var(--secondary-color);
    margin-bottom: 20px;
    display: block;
    opacity: 0.5;
}

.mapa-general {
    margin: 40px 0;
}

.mapa-general h2 {
    margin-bottom: 15px;
    color: var(--primary-color);
    font-size: 1.4em;
    text-align: center;
}

.resultados-info {
    margin: 20px 0;
    text-align: center;
    color: var(--secondary-color);
}

.boton-ver-mas {
    background: var(--primary-color);
    color: var(--white-color);
    padding: 8px 16px;
    text-decoration: none;
    border-radius: 4px;
    font-size: 0.9em;
    font-weight: 600;
    display: inline-block;
    margin-top: 10px;
    transition: background 0.2s ease;
}

.boton-ver-mas:hover {
    background: var(--secondary-color);
    color: var(--white-color);
    text-decoration: none;
}

.ubicaciones-paginacion {
    margin: 40px 0 20px 0;
    text-align: center;
}

.ubicaciones-paginacion .nav-links {
    display: flex;
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
}

.ubicaciones-paginacion .nav-links a,
.ubicaciones-paginacion .nav-links span {
    padding: 8px 16px;
    border: 1px solid var(--success-color);
    border-radius: 4px;
    text-decoration: none;
    color: var(--primary-color);
    background: var(--white-color);
    transition: all 0.2s ease;
    display: inline-block;
}

.ubicaciones-paginacion .nav-links a:hover {
    background: var(--primary-color);
    color: var(--white-color);
    border-color: var(--primary-color);
}

.ubicaciones-paginacion .nav-links .current {
    background: var(--primary-color);
    color: var(--white-color);
    border-color: var(--primary-color);
}

.no-ubicaciones {
    text-align: center;
    padding: 60px 20px;
    background: var(--info-color);
    border-radius: 8px;
    margin: 40px 0;
}

.no-ubicaciones h2 {
    color: var(--primary-color);
    margin-bottom: 15px;
}

.no-ubicaciones p {
    color: var(--secondary-color);
    margin-bottom: 10px;
}

.boton-reset {
    background: var(--secondary-color);
    color: var(--white-color);
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 4px;
    font-weight: 600;
    display: inline-block;
    margin-top: 15px;
    transition: background 0.2s ease;
}

.boton-reset:hover {
    background: var(--primary-color);
    color: var(--white-color);
    text-decoration: none;
}

@media (max-width: 768px) {
    .page-header {
        padding: 30px 15px;
    }
    
    .page-header .page-title {
        font-size: 2em;
    }
    
    .ubicaciones-paginacion .nav-links {
        gap: 5px;
    }
    
    .ubicaciones-paginacion .nav-links a,
    .ubicaciones-paginacion .nav-links span {
        padding: 6px 12px;
        font-size: 0.9em;
    }
    
    .material-icons {
        font-size: 14px;
    }
    
    .meta-item .material-icons {
        font-size: 14px;
    }
}
</style>

<?php
// Función personalizada para modificar la query del archivo
function modificar_query_ubicaciones($query) {
    if (!is_admin() && $query->is_main_query()) {
        if (is_post_type_archive('ubicaciones')) {
            // Aplicar filtros de búsqueda
            if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
                $buscar = sanitize_text_field($_GET['buscar']);
                $query->set('s', $buscar);
            }
            
            if (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
                $categoria = sanitize_text_field($_GET['categoria']);
                $query->set('category_name', $categoria);
            }
            
            // Ordenar por título
            $query->set('orderby', 'title');
            $query->set('order', 'ASC');
            
            // Mostrar 12 ubicaciones por página
            $query->set('posts_per_page', 12);
        }
    }
}
add_action('pre_get_posts', 'modificar_query_ubicaciones');

get_footer(); ?>