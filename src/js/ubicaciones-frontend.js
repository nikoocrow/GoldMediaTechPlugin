/**
 * JavaScript para mostrar mapas de Google en el frontend
 * Maneja la visualización de ubicaciones en páginas públicas
 */

// Variables globales
let mapasInicializados = [];

/**
 * Inicializar todos los mapas en la página
 */
function inicializarMapasFrontend() {
    // Buscar todos los contenedores de mapas individuales
    const contenedoresMapas = document.querySelectorAll('.mapa-ubicacion');
    
    contenedoresMapas.forEach(function(contenedor, index) {
        inicializarMapaIndividual(contenedor, index);
    });
    
    // Inicializar mapa de archivo de ubicaciones si existe
    const mapaArchivo = document.querySelector('#mapa-archivo-ubicaciones');
    if (mapaArchivo) {
        inicializarMapaArchivo();
    }
}

/**
 * Inicializar un mapa individual para una ubicación específica
 */
function inicializarMapaIndividual(contenedor, index) {
    // Obtener datos del contenedor
    const latitud = parseFloat(contenedor.dataset.lat);
    const longitud = parseFloat(contenedor.dataset.lng);
    const titulo = contenedor.dataset.titulo || 'Ubicación';
    const direccion = contenedor.dataset.direccion || '';
    const telefono = contenedor.dataset.telefono || '';
    const email = contenedor.dataset.email || '';
    const horarios = contenedor.dataset.horarios || '';
    
    // Verificar que las coordenadas sean válidas
    if (isNaN(latitud) || isNaN(longitud)) {
        contenedor.innerHTML = '<p class="error-mapa" style="text-align: center; color: #666; padding: 50px;">Error: Coordenadas no válidas para mostrar el mapa</p>';
        return;
    }
    
    // Crear el mapa
    const mapa = new google.maps.Map(contenedor, {
        zoom: 15,
        center: { lat: latitud, lng: longitud },
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        styles: obtenerEstilosPersonalizados(),
        mapTypeControl: true,
        streetViewControl: true,
        fullscreenControl: true
    });
    
    // Crear marcador
    const marcador = new google.maps.Marker({
        position: { lat: latitud, lng: longitud },
        map: mapa,
        title: titulo,
        animation: google.maps.Animation.DROP
    });
    
    // Crear contenido de la ventana de información
    let contenidoInfo = '<div class="info-window" style="max-width: 300px;">';
    contenidoInfo += '<h4 style="margin: 0 0 10px 0;">' + titulo + '</h4>';
    
    if (direccion) {
        contenidoInfo += '<p style="margin: 5px 0;"><strong>📍 Dirección:</strong><br>' + direccion + '</p>';
    }
    
    if (telefono) {
        contenidoInfo += '<p style="margin: 5px 0;"><strong>📞 Teléfono:</strong><br><a href="tel:' + telefono + '">' + telefono + '</a></p>';
    }
    
    if (email) {
        contenidoInfo += '<p style="margin: 5px 0;"><strong>✉️ Email:</strong><br><a href="mailto:' + email + '">' + email + '</a></p>';
    }
    
    if (horarios) {
        contenidoInfo += '<p style="margin: 5px 0;"><strong>🕒 Horarios:</strong><br>' + horarios.replace(/\n/g, '<br>') + '</p>';
    }
    
    contenidoInfo += '<p style="margin: 10px 0 5px 0;"><strong>📍 Coordenadas:</strong><br>' + latitud.toFixed(6) + ', ' + longitud.toFixed(6) + '</p>';
    contenidoInfo += '<p style="margin: 10px 0 0 0;">';
    contenidoInfo += '<a href="https://www.google.com/maps/dir/?api=1&destination=' + latitud + ',' + longitud + '" target="_blank" style="background: #4285f4; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 12px;">Cómo llegar</a>';
    contenidoInfo += '</p>';
    contenidoInfo += '</div>';
    
    const ventanaInfo = new google.maps.InfoWindow({
        content: contenidoInfo
    });
    
    // Mostrar ventana de información al hacer clic en el marcador
    marcador.addListener('click', function() {
        ventanaInfo.open(mapa, marcador);
    });
    
    // Guardar referencia del mapa
    mapasInicializados[index] = mapa;
}

/**
 * Inicializar mapa con múltiples ubicaciones (para archivo)
 */
function inicializarMapaArchivo() {
    const contenedor = document.querySelector('#mapa-archivo-ubicaciones');
    const ubicacionesData = contenedor.dataset.ubicaciones;
    
    if (!ubicacionesData) {
        contenedor.innerHTML = '<p class="sin-ubicaciones" style="text-align: center; color: #666; padding: 50px;">No hay ubicaciones para mostrar en el mapa</p>';
        return;
    }
    
    let ubicaciones;
    try {
        ubicaciones = JSON.parse(ubicacionesData);
    } catch (e) {
        console.error('Error parsing ubicaciones data:', e);
        contenedor.innerHTML = '<p class="error-mapa" style="text-align: center; color: #666; padding: 50px;">Error al cargar las ubicaciones</p>';
        return;
    }
    
    if (ubicaciones.length === 0) {
        contenedor.innerHTML = '<p class="sin-ubicaciones" style="text-align: center; color: #666; padding: 50px;">No hay ubicaciones para mostrar</p>';
        return;
    }
    
    // Crear el mapa centrado en Bogotá por defecto
    const mapa = new google.maps.Map(contenedor, {
        zoom: 10,
        center: { lat: 4.6097, lng: -74.0817 },
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        styles: obtenerEstilosPersonalizados(),
        mapTypeControl: true,
        streetViewControl: true,
        fullscreenControl: true
    });
    
    const bounds = new google.maps.LatLngBounds();
    const marcadores = [];
    
    // Agregar marcadores para cada ubicación
    ubicaciones.forEach(function(ubicacion, index) {
        const latitud = parseFloat(ubicacion.lat);
        const longitud = parseFloat(ubicacion.lng);
        
        if (isNaN(latitud) || isNaN(longitud)) {
            return; // Saltar ubicaciones con coordenadas inválidas
        }
        
        const posicion = { lat: latitud, lng: longitud };
        
        const marcador = new google.maps.Marker({
            position: posicion,
            map: mapa,
            title: ubicacion.titulo,
            animation: google.maps.Animation.DROP
        });
        
        // Crear contenido de ventana de información
        let contenidoInfo = '<div class="info-window" style="max-width: 300px;">';
        contenidoInfo += '<h4 style="margin: 0 0 10px 0;"><a href="' + ubicacion.url + '" target="_blank" style="text-decoration: none;">' + ubicacion.titulo + '</a></h4>';
        
        if (ubicacion.direccion) {
            contenidoInfo += '<p style="margin: 5px 0;"><strong>📍 Dirección:</strong><br>' + ubicacion.direccion + '</p>';
        }
        
        if (ubicacion.telefono) {
            contenidoInfo += '<p style="margin: 5px 0;"><strong>📞 Teléfono:</strong><br><a href="tel:' + ubicacion.telefono + '">' + ubicacion.telefono + '</a></p>';
        }
        
        if (ubicacion.email) {
            contenidoInfo += '<p style="margin: 5px 0;"><strong>✉️ Email:</strong><br><a href="mailto:' + ubicacion.email + '">' + ubicacion.email + '</a></p>';
        }
        
        if (ubicacion.excerpt) {
            contenidoInfo += '<p style="margin: 10px 0;">' + ubicacion.excerpt + '</p>';
        }
        
        contenidoInfo += '<p style="margin: 10px 0;">';
        contenidoInfo += '<button onclick="abrirModalContacto(\'' + ubicacion.ID + '\', \'' + ubicacion.titulo.replace(/'/g, "\\'") + '\')" style="background: #28a745; color: white; padding: 5px 10px; border: none; border-radius: 3px; font-size: 12px; margin-right: 5px; cursor: pointer;">📞 Contacto</button>';
        contenidoInfo += '<a href="https://www.google.com/maps/dir/?api=1&destination=' + latitud + ',' + longitud + '" target="_blank" style="background: #4285f4; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 12px;">🗺️ Cómo llegar</a>';
        contenidoInfo += '</p>';
        contenidoInfo += '</div>';
        
        const ventanaInfo = new google.maps.InfoWindow({
            content: contenidoInfo
        });
        
        marcador.addListener('click', function() {
            // Cerrar otras ventanas abiertas
            marcadores.forEach(function(m) {
                if (m.ventanaInfo) {
                    m.ventanaInfo.close();
                }
            });
            
            ventanaInfo.open(mapa, marcador);
        });
        
        marcador.ventanaInfo = ventanaInfo;
        marcadores.push(marcador);
        bounds.extend(posicion);
    });
    
    // Ajustar el mapa para mostrar todas las ubicaciones
    if (ubicaciones.length > 1) {
        mapa.fitBounds(bounds);
        
        // Asegurar un zoom mínimo
        google.maps.event.addListenerOnce(mapa, 'bounds_changed', function() {
            if (mapa.getZoom() > 15) {
                mapa.setZoom(15);
            }
        });
    } else {
        mapa.setCenter(bounds.getCenter());
        mapa.setZoom(15);
    }
}

/**
 * Obtener estilos personalizados para el mapa
 */
function obtenerEstilosPersonalizados() {
    return [
        {
            "featureType": "all",
            "elementType": "geometry.fill",
            "stylers": [{"weight": "2.00"}]
        },
        {
            "featureType": "all",
            "elementType": "geometry.stroke",
            "stylers": [{"color": "#9c9c9c"}]
        },
        {
            "featureType": "all",
            "elementType": "labels.text",
            "stylers": [{"visibility": "on"}]
        },
        {
            "featureType": "landscape",
            "elementType": "all",
            "stylers": [{"color": "#f2f2f2"}]
        },
        {
            "featureType": "landscape",
            "elementType": "geometry.fill",
            "stylers": [{"color": "#ffffff"}]
        },
        {
            "featureType": "poi",
            "elementType": "all",
            "stylers": [{"visibility": "simplified"}]
        },
        {
            "featureType": "road",
            "elementType": "all",
            "stylers": [{"saturation": -100}, {"lightness": 45}]
        },
        {
            "featureType": "road.highway",
            "elementType": "all",
            "stylers": [{"visibility": "simplified"}]
        },
        {
            "featureType": "road.arterial",
            "elementType": "labels.icon",
            "stylers": [{"visibility": "off"}]
        },
        {
            "featureType": "transit",
            "elementType": "all",
            "stylers": [{"visibility": "off"}]
        },
        {
            "featureType": "water",
            "elementType": "all",
            "stylers": [{"color": "#46bcec"}, {"visibility": "on"}]
        }
    ];
}

/**
 * Redimensionar mapas cuando cambia el tamaño de la ventana
 */
function redimensionarMapas() {
    mapasInicializados.forEach(function(mapa) {
        if (mapa) {
            google.maps.event.trigger(mapa, 'resize');
        }
    });
}

/**
 * Calcular distancia entre dos puntos
 */
function calcularDistancia(lat1, lng1, lat2, lng2) {
    const R = 6371; // Radio de la Tierra en km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLng/2) * Math.sin(dLng/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

/**
 * Inicializar mapas de shortcodes
 */
function inicializarShortcodeMapas() {
    const shortcodeMapas = document.querySelectorAll('.mapa-ubicaciones-shortcode');
    shortcodeMapas.forEach(function(contenedor) {
        const id = contenedor.id;
        if (id) {
            inicializarMapaShortcode(id);
        }
    });
}

/**
 * Función para inicializar mapas de shortcode
 */
function inicializarMapaShortcode(idMapa) {
    const contenedor = document.getElementById(idMapa);
    if (!contenedor) {
        console.error('Contenedor de mapa no encontrado:', idMapa);
        return;
    }
    
    const ubicacionesData = contenedor.dataset.ubicaciones;
    const zoom = parseInt(contenedor.dataset.zoom) || 13;
    
    if (!ubicacionesData) {
        contenedor.innerHTML = '<div style="background: #fff3cd; color: #856404; padding: 30px; text-align: center; border-radius: 8px;">⚠️ No hay datos de ubicaciones para mostrar.</div>';
        return;
    }
    
    let ubicaciones;
    try {
        ubicaciones = JSON.parse(ubicacionesData);
    } catch (e) {
        console.error('Error parsing ubicaciones data:', e);
        contenedor.innerHTML = '<div style="background: #f8d7da; color: #721c24; padding: 30px; text-align: center; border-radius: 8px;"><strong>Error:</strong> Datos de ubicaciones inválidos.</div>';
        return;
    }
    
    if (ubicaciones.length === 0) {
        contenedor.innerHTML = '<div style="background: #f8f9fa; color: #6c757d; padding: 30px; text-align: center; border-radius: 8px;">📍 No hay ubicaciones para mostrar en el mapa.</div>';
        return;
    }
    
    // Crear el mapa
    const centroInicial = ubicaciones.length === 1 ? 
        {lat: ubicaciones[0].lat, lng: ubicaciones[0].lng} : 
        {lat: 4.6097, lng: -74.0817}; // Bogotá por defecto
    
    const mapa = new google.maps.Map(contenedor, {
        zoom: zoom,
        center: centroInicial,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        styles: obtenerEstilosPersonalizados(),
        mapTypeControl: true,
        streetViewControl: true,
        fullscreenControl: true
    });
    
    const bounds = new google.maps.LatLngBounds();
    const marcadores = [];
    
    // Agregar marcadores
    ubicaciones.forEach(function(ubicacion, index) {
        const latitud = parseFloat(ubicacion.lat);
        const longitud = parseFloat(ubicacion.lng);
        
        if (isNaN(latitud) || isNaN(longitud)) {
            console.warn('Coordenadas inválidas para ubicación:', ubicacion.titulo);
            return;
        }
        
        const posicion = {lat: latitud, lng: longitud};
        
        const marcador = new google.maps.Marker({
            position: posicion,
            map: mapa,
            title: ubicacion.titulo,
            animation: google.maps.Animation.DROP
        });
        
        // Crear contenido de la ventana de información
        let contenidoInfo = '<div style="max-width: 300px; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;">';
        contenidoInfo += '<h4 style="margin: 0 0 12px 0; color: #333;"><a href="' + ubicacion.url + '" style="text-decoration: none; color: #0073aa;">' + ubicacion.titulo + '</a></h4>';
        
        if (ubicacion.direccion) {
            contenidoInfo += '<p style="margin: 8px 0; font-size: 14px;"><strong>📍 Dirección:</strong><br>' + ubicacion.direccion + '</p>';
        }
        
        if (ubicacion.telefono) {
            contenidoInfo += '<p style="margin: 8px 0; font-size: 14px;"><strong>📞 Teléfono:</strong><br><a href="tel:' + ubicacion.telefono + '" style="color: #0073aa;">' + ubicacion.telefono + '</a></p>';
        }
        
        if (ubicacion.email) {
            contenidoInfo += '<p style="margin: 8px 0; font-size: 14px;"><strong>✉️ Email:</strong><br><a href="mailto:' + ubicacion.email + '" style="color: #0073aa;">' + ubicacion.email + '</a></p>';
        }
        
        if (ubicacion.horarios) {
            contenidoInfo += '<p style="margin: 8px 0; font-size: 14px;"><strong>🕒 Horarios:</strong><br>' + ubicacion.horarios.replace(/\n/g, '<br>') + '</p>';
        }
        
        if (ubicacion.excerpt) {
            contenidoInfo += '<p style="margin: 12px 0; font-size: 14px; color: #666;">' + ubicacion.excerpt + '</p>';
        }
        
        contenidoInfo += '<div style="margin: 15px 0 5px 0; text-align: center;">';
        contenidoInfo += '<button onclick="abrirModalContacto(\'' + ubicacion.id + '\', \'' + ubicacion.titulo.replace(/'/g, "\\'") + '\')" style="background: #28a745; color: white; padding: 8px 16px; border: none; border-radius: 4px; font-size: 13px; margin-right: 8px; cursor: pointer;">📞 Contacto</button>';
        contenidoInfo += '<a href="https://www.google.com/maps/dir/?api=1&destination=' + latitud + ',' + longitud + '" target="_blank" style="background: #4285f4; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; font-size: 13px; display: inline-block;">🗺️ Cómo llegar</a>';
        contenidoInfo += '</div>';
        contenidoInfo += '</div>';
        
        const ventanaInfo = new google.maps.InfoWindow({
            content: contenidoInfo,
            maxWidth: 350
        });
        
        marcador.addListener('click', function() {
            // Cerrar otras ventanas abiertas
            marcadores.forEach(function(m) {
                if (m.ventanaInfo) {
                    m.ventanaInfo.close();
                }
            });
            
            ventanaInfo.open(mapa, marcador);
        });
        
        marcador.ventanaInfo = ventanaInfo;
        marcadores.push(marcador);
        bounds.extend(posicion);
    });
    
    // Ajustar vista del mapa
    if (ubicaciones.length > 1) {
        mapa.fitBounds(bounds);
        
        // Asegurar un zoom mínimo
        google.maps.event.addListenerOnce(mapa, 'bounds_changed', function() {
            if (mapa.getZoom() > 15) {
                mapa.setZoom(15);
            }
        });
    } else if (ubicaciones.length === 1) {
        mapa.setCenter({lat: ubicaciones[0].lat, lng: ubicaciones[0].lng});
        mapa.setZoom(zoom);
    }
    
    console.log('Mapa shortcode inicializado:', idMapa, 'con', ubicaciones.length, 'ubicaciones');
}

// Eventos
document.addEventListener('DOMContentLoaded', function() {
    // Función para verificar e inicializar mapas
    function verificarEInicializar() {
        if (typeof google !== 'undefined' && google.maps) {
            console.log('Ubicaciones Plugin: Google Maps disponible, inicializando...');
            inicializarMapasFrontend();
        } else {
            console.warn('Ubicaciones Plugin: Google Maps no disponible, reintentando...');
            // Mostrar mensaje temporal en contenedores
            var contenedores = document.querySelectorAll('.mapa-ubicacion, #mapa-archivo-ubicaciones, .mapa-ubicaciones-shortcode');
            contenedores.forEach(function(contenedor) {
                contenedor.innerHTML = '<p style="text-align: center; color: #666; padding: 30px;">⏳ Cargando Google Maps...</p>';
            });
            
            // Reintentar después de 2 segundos
            setTimeout(function() {
                if (typeof google !== 'undefined' && google.maps) {
                    inicializarMapasFrontend();
                } else {
                    // Error final
                    contenedores.forEach(function(contenedor) {
                        contenedor.innerHTML = '<p style="text-align: center; color: #d63638; padding: 30px;">❌ Error: No se pudo cargar Google Maps</p>';
                    });
                }
            }, 2000);
        }
    }
    
    // Verificar inicialmente
    verificarEInicializar();
});

// Redimensionar mapas al cambiar tamaño de ventana
window.addEventListener('resize', function() {
    setTimeout(redimensionarMapas, 300);
});

// Callback global para Google Maps
window.inicializarMapasFrontend = inicializarMapasFrontend;
window.inicializarShortcodeMapas = inicializarShortcodeMapas;
window.inicializarMapaShortcode = inicializarMapaShortcode;

// Función de callback específica para nuestro plugin
window.inicializarMapasFrontendUbicaciones = function() {
    console.log('Callback de Google Maps ejecutado para Ubicaciones Plugin');
    if (typeof inicializarMapasFrontend === 'function') {
        inicializarMapasFrontend();
    }
    if (typeof inicializarShortcodeMapas === 'function') {
        inicializarShortcodeMapas();
    }
};

/**
 * FUNCIONES DEL MODAL DE CONTACTO
 */

// Variables globales del modal
let modalContacto = null;
let pasoActual = 1;
let datosFormulario = {};

/**
 * Abrir modal de contacto
 */
function abrirModalContacto(ubicacionId, nombreUbicacion) {
    datosFormulario = {
        ubicacion_id: ubicacionId,
        ubicacion_nombre: nombreUbicacion,
        tipo_solicitud: '',
        paso: 1
    };
    
    crearModal();
    mostrarPaso1();
    document.body.style.overflow = 'hidden';
}

/**
 * Crear el modal
 */
function crearModal() {
    // Remover modal existente si existe
    if (modalContacto) {
        modalContacto.remove();
    }
    
    modalContacto = document.createElement('div');
    modalContacto.id = 'modal-contacto-ubicaciones';
    modalContacto.innerHTML = `
        <div class="modal-overlay" onclick="cerrarModal()"></div>
        <div class="modal-container">
            <div class="modal-header">
                <h3 id="modal-titulo">Formulario de Contacto</h3>
                <button class="modal-close" onclick="cerrarModal()">&times;</button>
            </div>
            <div class="modal-body" id="modal-contenido">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer" id="modal-footer">
                <!-- Botones dinámicos -->
            </div>
        </div>
    `;
    
    // Agregar estilos
    const estilos = document.createElement('style');
    estilos.textContent = `
        #modal-contacto-ubicaciones {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }
        
        .modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
        }
        
        .modal-container {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.3s ease;
        }
        
        .modal-header {
            padding: 20px 25px 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #333;
            font-size: 1.3em;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background 0.2s;
        }
        
        .modal-close:hover {
            background: #f0f0f0;
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .modal-footer {
            padding: 15px 25px 25px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .opcion-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            width: 100%;
            max-width: 250px;
        }
        
        .opcion-btn:hover {
            border-color: #0073aa;
            box-shadow: 0 4px 12px rgba(0, 115, 170, 0.15);
            transform: translateY(-2px);
        }
        
        .opcion-btn.selected {
            border-color: #0073aa;
            background: #f0f8ff;
        }
        
        .opcion-btn .icono {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .opcion-btn h4 {
            margin: 0 0 8px 0;
            color: #333;
            font-size: 1.1em;
        }
        
        .opcion-btn p {
            margin: 0;
            color: #666;
            font-size: 0.9em;
            line-height: 1.4;
        }
        
        .opciones-container {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            margin: 20px 0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #0073aa;
        }
        
        .form-group.required label::after {
            content: " *";
            color: #e74c3c;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background: #0073aa;
            color: white;
        }
        
        .btn-primary:hover {
            background: #005a87;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #333;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 8px;
        }
        
        .mensaje-exito {
            text-align: center;
            padding: 40px 20px;
        }
        
        .mensaje-exito .icono {
            font-size: 64px;
            color: #28a745;
            margin-bottom: 20px;
        }
        
        .mensaje-exito h4 {
            color: #28a745;
            margin-bottom: 15px;
        }
        
        .mensaje-exito p {
            color: #666;
            line-height: 1.5;
        }
        
        .progress-bar {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .progress-fill {
            height: 100%;
            background: #0073aa;
            border-radius: 2px;
            transition: width 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .modal-container {
                width: 95%;
                margin: 20px;
            }
            
            .opciones-container {
                flex-direction: column;
                align-items: center;
            }
            
            .opcion-btn {
                max-width: 100%;
            }
            
            .modal-footer {
                flex-direction: column;
            }
        }
    `;
    
    document.head.appendChild(estilos);
    document.body.appendChild(modalContacto);
}

/**
 * Mostrar paso 1: Selección de tipo de solicitud
 */
function mostrarPaso1() {
    pasoActual = 1;
    
    document.getElementById('modal-titulo').textContent = 'Tipo de Solicitud - ' + datosFormulario.ubicacion_nombre;
    
    document.getElementById('modal-contenido').innerHTML = `
        <div class="progress-bar">
            <div class="progress-fill" style="width: 33%"></div>
        </div>
        
        <p style="text-align: center; color: #666; margin-bottom: 30px;">
            Selecciona el tipo de solicitud que deseas realizar:
        </p>
        
        <div class="opciones-container">
            <div class="opcion-btn" onclick="seleccionarTipo('consulta')">
                <div class="icono">💬</div>
                <h4>Consulta General</h4>
                <p>Información sobre servicios, horarios, precios y disponibilidad</p>
            </div>
            
            <div class="opcion-btn" onclick="seleccionarTipo('cita')">
                <div class="icono">📅</div>
                <h4>Solicitar Cita</h4>
                <p>Agendar una cita o reunión presencial en esta ubicación</p>
            </div>
        </div>
    `;
    
    document.getElementById('modal-footer').innerHTML = `
        <button class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
    `;
}

/**
 * Seleccionar tipo de solicitud
 */
function seleccionarTipo(tipo) {
    datosFormulario.tipo_solicitud = tipo;
    
    // Marcar opción seleccionada
    document.querySelectorAll('.opcion-btn').forEach(btn => btn.classList.remove('selected'));
    event.target.closest('.opcion-btn').classList.add('selected');
    
    // Avanzar al siguiente paso después de un pequeño delay
    setTimeout(() => {
        mostrarPaso2();
    }, 500);
}

/**
 * Mostrar paso 2: Formulario específico
 */
function mostrarPaso2() {
    pasoActual = 2;
    
    const titulo = datosFormulario.tipo_solicitud === 'consulta' ? 'Consulta General' : 'Solicitar Cita';
    document.getElementById('modal-titulo').textContent = titulo + ' - ' + datosFormulario.ubicacion_nombre;
    
    let contenidoEspecifico = '';
    
    if (datosFormulario.tipo_solicitud === 'consulta') {
        contenidoEspecifico = `
            <div class="form-group required">
                <label for="asunto">Asunto de la consulta</label>
                <select id="asunto" name="asunto" required>
                    <option value="">Selecciona un asunto</option>
                    <option value="servicios">Información sobre servicios</option>
                    <option value="precios">Consulta de precios</option>
                    <option value="horarios">Horarios de atención</option>
                    <option value="ubicacion">Información de ubicación</option>
                    <option value="otro">Otro</option>
                </select>
            </div>
        `;
    } else {
        contenidoEspecifico = `
            <div class="form-group required">
                <label for="tipo_cita">Tipo de cita</label>
                <select id="tipo_cita" name="tipo_cita" required>
                    <option value="">Selecciona el tipo de cita</option>
                    <option value="reunion">Reunión de negocios</option>
                    <option value="consulta_presencial">Consulta presencial</option>
                    <option value="visita_tecnica">Visita técnica</option>
                    <option value="entrevista">Entrevista</option>
                    <option value="otro">Otro</option>
                </select>
            </div>
            
            <div class="form-group required">
                <label for="fecha_preferida">Fecha preferida</label>
                <input type="date" id="fecha_preferida" name="fecha_preferida" required min="${new Date().toISOString().split('T')[0]}">
            </div>
            
            <div class="form-group">
                <label for="hora_preferida">Hora preferida</label>
                <select id="hora_preferida" name="hora_preferida">
                    <option value="">Selecciona una hora</option>
                    <option value="09:00">09:00 AM</option>
                    <option value="10:00">10:00 AM</option>
                    <option value="11:00">11:00 AM</option>
                    <option value="14:00">02:00 PM</option>
                    <option value="15:00">03:00 PM</option>
                    <option value="16:00">04:00 PM</option>
                </select>
            </div>
        `;
    }
    
    document.getElementById('modal-contenido').innerHTML = `
        <div class="progress-bar">
            <div class="progress-fill" style="width: 66%"></div>
        </div>
        
        <form id="formulario-contacto">
            <div class="form-group required">
                <label for="nombre_completo">Nombre completo</label>
                <input type="text" id="nombre_completo" name="nombre_completo" required>
            </div>
            
            <div class="form-group required">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group required">
                <label for="telefono">Teléfono</label>
                <input type="tel" id="telefono" name="telefono" required>
            </div>
            
            <div class="form-group">
                <label for="empresa">Empresa (opcional)</label>
                <input type="text" id="empresa" name="empresa">
            </div>
            
            ${contenidoEspecifico}
            
            <div class="form-group required">
                <label for="mensaje">Mensaje</label>
                <textarea id="mensaje" name="mensaje" rows="4" required placeholder="Describe tu solicitud en detalle..."></textarea>
            </div>
        </form>
    `;
    
    document.getElementById('modal-footer').innerHTML = `
        <button class="btn btn-secondary" onclick="mostrarPaso1()">Atrás</button>
        <button class="btn btn-primary" onclick="enviarFormulario()">Enviar Solicitud</button>
    `;
}

/**
 * Enviar formulario con AJAX
 */
function enviarFormulario() {
    const form = document.getElementById('formulario-contacto');
    const formData = new FormData(form);
    
    // Validar campos requeridos
    const camposRequeridos = form.querySelectorAll('[required]');
    let todosCompletos = true;
    
    camposRequeridos.forEach(campo => {
        if (!campo.value.trim()) {
            campo.style.borderColor = '#e74c3c';
            todosCompletos = false;
        } else {
            campo.style.borderColor = '#e0e0e0';
        }
    });
    
    if (!todosCompletos) {
        alert('Por favor completa todos los campos obligatorios');
        return;
    }
    
    // Agregar datos adicionales
    formData.append('action', 'procesar_contacto_ubicacion');
    formData.append('ubicacion_id', datosFormulario.ubicacion_id);
    formData.append('ubicacion_nombre', datosFormulario.ubicacion_nombre);
    formData.append('tipo_solicitud', datosFormulario.tipo_solicitud);
    formData.append('nonce', ubicacionesData.nonce);
    
    // Mostrar estado de carga
    const botonEnviar = document.querySelector('#modal-footer .btn-primary');
    botonEnviar.innerHTML = '<span class="loading"></span>Enviando...';
    botonEnviar.disabled = true;
    
    // Enviar con AJAX
    fetch(ubicacionesData.ajaxurl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarExito(data.data.mensaje);
        } else {
            throw new Error(data.data || 'Error al enviar el formulario');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al enviar el formulario: ' + error.message);
        
        // Restaurar botón
        botonEnviar.innerHTML = 'Enviar Solicitud';
        botonEnviar.disabled = false;
    });
}

/**
 * Mostrar mensaje de éxito
 */
function mostrarExito(mensaje) {
    document.getElementById('modal-titulo').textContent = '¡Solicitud Enviada!';
    
    document.getElementById('modal-contenido').innerHTML = `
        <div class="progress-bar">
            <div class="progress-fill" style="width: 100%"></div>
        </div>
        
        <div class="mensaje-exito">
            <div class="icono">✅</div>
            <h4>¡Solicitud enviada correctamente!</h4>
            <p>${mensaje}</p>
            <p>Te contactaremos pronto a través de los datos proporcionados.</p>
        </div>
    `;
    
    document.getElementById('modal-footer').innerHTML = `
        <button class="btn btn-success" onclick="cerrarModal()">Cerrar</button>
    `;
}

/**
 * Cerrar modal
 */
function cerrarModal() {
    if (modalContacto) {
        modalContacto.remove();
        modalContacto = null;
    }
    document.body.style.overflow = '';
}

// Hacer funciones globales
window.abrirModalContacto = abrirModalContacto;
window.cerrarModal = cerrarModal;
window.seleccionarTipo = seleccionarTipo;
window.mostrarPaso1 = mostrarPaso1;
window.enviarFormulario = enviarFormulario;