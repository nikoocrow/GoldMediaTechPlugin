<?php
/**
 * Manejo de peticiones AJAX para el formulario de contacto
 * Este archivo procesa las solicitudes de contacto enviadas desde el modal
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registrar hooks AJAX
 */
function registrar_ajax_ubicaciones() {
    // Para usuarios logueados y no logueados
    add_action('wp_ajax_procesar_contacto_ubicacion', 'procesar_contacto_ubicacion');
    add_action('wp_ajax_nopriv_procesar_contacto_ubicacion', 'procesar_contacto_ubicacion');
}
add_action('init', 'registrar_ajax_ubicaciones');

/**
 * Procesar formulario de contacto
 */
function procesar_contacto_ubicacion() {
    // Verificar nonce
    if (!wp_verify_nonce($_POST['nonce'], 'ubicaciones_frontend_nonce')) {
        wp_die(json_encode([
            'success' => false,
            'data' => 'Error de seguridad. Token inválido.'
        ]));
    }
    
    // Sanitizar y validar datos
    $datos = [
        'ubicacion_id' => sanitize_text_field($_POST['ubicacion_id']),
        'ubicacion_nombre' => sanitize_text_field($_POST['ubicacion_nombre']),
        'tipo_solicitud' => sanitize_text_field($_POST['tipo_solicitud']),
        'nombre_completo' => sanitize_text_field($_POST['nombre_completo']),
        'email' => sanitize_email($_POST['email']),
        'telefono' => sanitize_text_field($_POST['telefono']),
        'empresa' => sanitize_text_field($_POST['empresa']),
        'mensaje' => sanitize_textarea_field($_POST['mensaje']),
        'fecha_envio' => current_time('mysql'),
        'ip_cliente' => $_SERVER['REMOTE_ADDR']
    ];
    
    // Campos específicos según el tipo de solicitud
    if ($datos['tipo_solicitud'] === 'consulta') {
        $datos['asunto'] = sanitize_text_field($_POST['asunto']);
    } elseif ($datos['tipo_solicitud'] === 'cita') {
        $datos['tipo_cita'] = sanitize_text_field($_POST['tipo_cita']);
        $datos['fecha_preferida'] = sanitize_text_field($_POST['fecha_preferida']);
        $datos['hora_preferida'] = sanitize_text_field($_POST['hora_preferida']);
    }
    
    // Validaciones básicas
    $errores = [];
    
    if (empty($datos['nombre_completo'])) {
        $errores[] = 'El nombre completo es obligatorio';
    }
    
    if (empty($datos['email']) || !is_email($datos['email'])) {
        $errores[] = 'El email es obligatorio y debe ser válido';
    }
    
    if (empty($datos['telefono'])) {
        $errores[] = 'El teléfono es obligatorio';
    }
    
    if (empty($datos['mensaje'])) {
        $errores[] = 'El mensaje es obligatorio';
    }
    
    if (!empty($errores)) {
        wp_die(json_encode([
            'success' => false,
            'data' => implode(', ', $errores)
        ]));
    }
    
    // Guardar en base de datos
    $resultado_bd = guardar_contacto_bd($datos);
    
    if (!$resultado_bd) {
        wp_die(json_encode([
            'success' => false,
            'data' => 'Error al guardar en la base de datos'
        ]));
    }
    
    // Enviar emails
    $email_enviado = enviar_emails_contacto($datos);
    
    if (!$email_enviado) {
        wp_die(json_encode([
            'success' => false,
            'data' => 'Error al enviar el email'
        ]));
    }
    
    // Respuesta exitosa
    wp_die(json_encode([
        'success' => true,
        'data' => [
            'mensaje' => 'Tu solicitud ha sido enviada correctamente. Te contactaremos pronto.',
            'id_solicitud' => $resultado_bd
        ]
    ]));
}

/**
 * Guardar contacto en base de datos
 */
function guardar_contacto_bd($datos) {
    global $wpdb;
    
    // Crear tabla si no existe
    crear_tabla_contactos();
    
    $tabla = $wpdb->prefix . 'ubicaciones_contactos';
    
    $datos_bd = [
        'ubicacion_id' => $datos['ubicacion_id'],
        'ubicacion_nombre' => $datos['ubicacion_nombre'],
        'tipo_solicitud' => $datos['tipo_solicitud'],
        'nombre_completo' => $datos['nombre_completo'],
        'email' => $datos['email'],
        'telefono' => $datos['telefono'],
        'empresa' => $datos['empresa'],
        'mensaje' => $datos['mensaje'],
        'datos_adicionales' => json_encode([
            'asunto' => isset($datos['asunto']) ? $datos['asunto'] : '',
            'tipo_cita' => isset($datos['tipo_cita']) ? $datos['tipo_cita'] : '',
            'fecha_preferida' => isset($datos['fecha_preferida']) ? $datos['fecha_preferida'] : '',
            'hora_preferida' => isset($datos['hora_preferida']) ? $datos['hora_preferida'] : '',
        ]),
        'fecha_envio' => $datos['fecha_envio'],
        'ip_cliente' => $datos['ip_cliente'],
        'estado' => 'pendiente'
    ];
    
    $resultado = $wpdb->insert($tabla, $datos_bd);
    
    if ($resultado === false) {
        error_log('Error al insertar contacto: ' . $wpdb->last_error);
        return false;
    }
    
    return $wpdb->insert_id;
}

/**
 * Crear tabla de contactos
 */
function crear_tabla_contactos() {
    global $wpdb;
    
    $tabla = $wpdb->prefix . 'ubicaciones_contactos';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $tabla (
        id int(11) NOT NULL AUTO_INCREMENT,
        ubicacion_id varchar(50) NOT NULL,
        ubicacion_nombre varchar(255) NOT NULL,
        tipo_solicitud varchar(50) NOT NULL,
        nombre_completo varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        telefono varchar(50) NOT NULL,
        empresa varchar(255),
        mensaje text NOT NULL,
        datos_adicionales text,
        fecha_envio datetime NOT NULL,
        ip_cliente varchar(45),
        estado varchar(20) DEFAULT 'pendiente',
        fecha_procesado datetime NULL,
        notas_admin text,
        PRIMARY KEY (id),
        KEY ubicacion_id (ubicacion_id),
        KEY tipo_solicitud (tipo_solicitud),
        KEY fecha_envio (fecha_envio),
        KEY estado (estado)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Enviar emails de confirmación
 */
function enviar_emails_contacto($datos) {
    $email_admin_enviado = enviar_email_admin($datos);
    $email_cliente_enviado = enviar_email_cliente($datos);
    
    return $email_admin_enviado && $email_cliente_enviado;
}

/**
 * Enviar email al administrador
 */
function enviar_email_admin($datos) {
    $admin_email = get_option('admin_email');
    $sitio_nombre = get_bloginfo('name');
    
    $asunto = sprintf('[%s] Nueva solicitud de contacto - %s', 
        $sitio_nombre, 
        ucfirst($datos['tipo_solicitud'])
    );
    
    // Preparar datos adicionales
    $datos_adicionales = '';
    if ($datos['tipo_solicitud'] === 'consulta' && !empty($datos['asunto'])) {
        $datos_adicionales = "\nAsunto: " . $datos['asunto'];
    } elseif ($datos['tipo_solicitud'] === 'cita') {
        if (!empty($datos['tipo_cita'])) {
            $datos_adicionales .= "\nTipo de cita: " . $datos['tipo_cita'];
        }
        if (!empty($datos['fecha_preferida'])) {
            $datos_adicionales .= "\nFecha preferida: " . $datos['fecha_preferida'];
        }
        if (!empty($datos['hora_preferida'])) {
            $datos_adicionales .= "\nHora preferida: " . $datos['hora_preferida'];
        }
    }
    
    $mensaje = sprintf("
Nueva solicitud de contacto recibida:

=== INFORMACIÓN DEL CLIENTE ===
Nombre: %s
Email: %s
Teléfono: %s
Empresa: %s

=== SOLICITUD ===
Tipo: %s
Ubicación: %s (ID: %s)%s

Mensaje:
%s

=== INFORMACIÓN TÉCNICA ===
Fecha: %s
IP: %s

---
Este email fue generado automáticamente por el sistema de ubicaciones.
    ",
        $datos['nombre_completo'],
        $datos['email'],
        $datos['telefono'],
        !empty($datos['empresa']) ? $datos['empresa'] : 'No especificada',
        ucfirst($datos['tipo_solicitud']),
        $datos['ubicacion_nombre'],
        $datos['ubicacion_id'],
        $datos_adicionales,
        $datos['mensaje'],
        $datos['fecha_envio'],
        $datos['ip_cliente']
    );
    
    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . $sitio_nombre . ' <' . $admin_email . '>',
        'Reply-To: ' . $datos['nombre_completo'] . ' <' . $datos['email'] . '>'
    ];
    
    return wp_mail($admin_email, $asunto, $mensaje, $headers);
}

/**
 * Enviar email de confirmación al cliente
 */
function enviar_email_cliente($datos) {
    $admin_email = get_option('admin_email');
    $sitio_nombre = get_bloginfo('name');
    $sitio_url = get_home_url();
    
    $asunto = sprintf('Confirmación de solicitud - %s', $sitio_nombre);
    
    // Determinar mensaje según tipo de solicitud
    $tipo_mensaje = '';
    if ($datos['tipo_solicitud'] === 'consulta') {
        $tipo_mensaje = 'Tu consulta general ha sido recibida';
        $tiempo_respuesta = 'Te responderemos en un plazo máximo de 24 horas';
    } else {
        $tipo_mensaje = 'Tu solicitud de cita ha sido recibida';
        $tiempo_respuesta = 'Te contactaremos pronto para confirmar la disponibilidad';
    }
    
    // Preparar datos específicos para mostrar al cliente
    $detalles_solicitud = '';
    if ($datos['tipo_solicitud'] === 'consulta' && !empty($datos['asunto'])) {
        $detalles_solicitud = "\n• Asunto: " . $datos['asunto'];
    } elseif ($datos['tipo_solicitud'] === 'cita') {
        if (!empty($datos['tipo_cita'])) {
            $detalles_solicitud .= "\n• Tipo de cita: " . $datos['tipo_cita'];
        }
        if (!empty($datos['fecha_preferida'])) {
            $detalles_solicitud .= "\n• Fecha solicitada: " . date('d/m/Y', strtotime($datos['fecha_preferida']));
        }
        if (!empty($datos['hora_preferida'])) {
            $detalles_solicitud .= "\n• Hora solicitada: " . $datos['hora_preferida'];
        }
    }
    
    $mensaje = sprintf("
Hola %s,

%s correctamente. %s.

=== RESUMEN DE TU SOLICITUD ===
• Ubicación: %s
• Tipo de solicitud: %s%s
• Mensaje: %s

=== PRÓXIMOS PASOS ===
Nuestro equipo revisará tu solicitud y te contactará a través de:
• Email: %s
• Teléfono: %s

=== INFORMACIÓN DE CONTACTO ===
Si tienes alguna pregunta urgente, puedes contactarnos directamente:
• Sitio web: %s
• Email: %s

¡Gracias por contactarnos!

Atentamente,
El equipo de %s

---
Este es un email automático. Por favor no respondas directamente a este mensaje.
    ",
        $datos['nombre_completo'],
        $tipo_mensaje,
        $tiempo_respuesta,
        $datos['ubicacion_nombre'],
        ucfirst($datos['tipo_solicitud']),
        $detalles_solicitud,
        $datos['mensaje'],
        $datos['email'],
        $datos['telefono'],
        $sitio_url,
        $admin_email,
        $sitio_nombre
    );
    
    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . $sitio_nombre . ' <' . $admin_email . '>',
        'Reply-To: ' . $admin_email
    ];
    
    return wp_mail($datos['email'], $asunto, $mensaje, $headers);
}

/**
 * Agregar página de administración para ver contactos
 */
function agregar_menu_contactos_admin() {
    add_submenu_page(
        'edit.php?post_type=ubicaciones',
        'Solicitudes de Contacto',
        'Contactos',
        'manage_options',
        'ubicaciones-contactos',
        'mostrar_pagina_contactos_admin'
    );
}
add_action('admin_menu', 'agregar_menu_contactos_admin');

/**
 * Mostrar página de administración de contactos
 */
function mostrar_pagina_contactos_admin() {
    global $wpdb;
    
    $tabla = $wpdb->prefix . 'ubicaciones_contactos';
    
    // Procesar acciones
    if (isset($_POST['accion']) && $_POST['accion'] === 'cambiar_estado') {
        $id = intval($_POST['contacto_id']);
        $nuevo_estado = sanitize_text_field($_POST['nuevo_estado']);
        $notas = sanitize_textarea_field($_POST['notas_admin']);
        
        $wpdb->update(
            $tabla,
            [
                'estado' => $nuevo_estado,
                'notas_admin' => $notas,
                'fecha_procesado' => current_time('mysql')
            ],
            ['id' => $id]
        );
        
        echo '<div class="notice notice-success"><p>Estado actualizado correctamente.</p></div>';
    }
    
    // Obtener contactos
    $contactos = $wpdb->get_results("
        SELECT * FROM $tabla 
        ORDER BY fecha_envio DESC 
        LIMIT 50
    ");
    
    ?>
    <div class="wrap">
        <h1>Solicitudes de Contacto</h1>
        
        <div class="tablenav top">
            <div class="alignleft actions">
                <p class="description">Últimas 50 solicitudes recibidas</p>
            </div>
        </div>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Ubicación</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($contactos)) : ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px;">
                            No hay solicitudes de contacto registradas.
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($contactos as $contacto) : ?>
                        <tr>
                            <td><?php echo $contacto->id; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($contacto->fecha_envio)); ?></td>
                            <td><?php echo esc_html($contacto->nombre_completo); ?></td>
                            <td><a href="mailto:<?php echo esc_attr($contacto->email); ?>"><?php echo esc_html($contacto->email); ?></a></td>
                            <td><a href="tel:<?php echo esc_attr($contacto->telefono); ?>"><?php echo esc_html($contacto->telefono); ?></a></td>
                            <td><?php echo esc_html($contacto->ubicacion_nombre); ?></td>
                            <td><?php echo ucfirst($contacto->tipo_solicitud); ?></td>
                            <td>
                                <span class="status-<?php echo $contacto->estado; ?>">
                                    <?php echo ucfirst($contacto->estado); ?>
                                </span>
                            </td>
                            <td>
                                <button class="button button-small" onclick="verDetalle(<?php echo $contacto->id; ?>)">
                                    Ver detalle
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <style>
        .status-pendiente { color: #d63638; font-weight: bold; }
        .status-procesando { color: #dba617; font-weight: bold; }
        .status-completado { color: #46b450; font-weight: bold; }
        .status-cerrado { color: #666; }
    </style>
    
    <script>
        function verDetalle(id) {
            // Por ahora solo mostrar ID, en el futuro se puede implementar un modal
            alert('Ver detalle del contacto ID: ' + id);
        }
    </script>
    <?php
}