<?php
/**
 * ============================================================================
 * SCRIPT DE AUTO-LOGIN SSO PARA SIGECEL
 * ============================================================================
 * 
 * PROPÓSITO:
 * Este script permite que usuarios logueados en el Sistema Principal CEL
 * puedan acceder a SIGECEL automáticamente SIN necesidad de volver a
 * ingresar email y contraseña.
 * 
 * ============================================================================
 * CÓMO FUNCIONA:
 * ============================================================================
 * 
 * 1. Usuario está logueado en Sistema Principal CEL
 *    - Sistema guarda su ID en $_SESSION['user_id']
 * 
 * 2. Usuario hace click en botón/enlace "SIGECEL"
 *    - Sistema llama a: generateSIGECELLink($_SESSION['user_id'])
 * 
 * 3. Este script hace petición HTTP a SIGECEL
 *    - POST /api/generate-auto-login-token
 *    - Envía: user_id, ip_address, user_agent, api_key
 * 
 * 4. SIGECEL genera token único temporal
 *    - Token expira en 1 minuto
 *    - Token es de un solo uso
 *    - Retorna URL: https://sigecel.cel.org.pe/auto-login?token=ABC123
 * 
 * 5. Usuario es redirigido a esa URL
 *    - SIGECEL valida el token
 *    - Autentica automáticamente al usuario
 *    - Redirige al dashboard
 * 
 * ============================================================================
 * REQUISITOS:
 * ============================================================================
 * 
 * 1. Ambos sistemas deben usar la MISMA base de datos (tabla: cms_users)
 * 2. API_KEY debe ser IDÉNTICA en:
 *    - Sistema Principal: define('SSO_API_KEY', 'clave...')
 *    - SIGECEL: .env → SSO_API_KEY=clave...
 * 3. Sistema Principal debe tener acceso a cURL
 * 4. Usuario debe estar logueado ($_SESSION['user_id'] debe existir)
 * 
 * ============================================================================
 * INSTALACIÓN EN SISTEMA PRINCIPAL:
 * ============================================================================
 * 
 * PASO 1: Copiar este archivo al servidor del Sistema Principal
 *         Ubicación recomendada: /includes/sigecel_autologin.php
 * 
 * PASO 2: Generar API Key única y segura
 *         Comando: php -r "echo bin2hex(random_bytes(32));"
 *         Resultado ejemplo: 9a97427a428faa5e42cbf9fda8ee89e32ef0d6273332da201d71d1a9297b68f8
 * 
 * PASO 3: Configurar API Key en este archivo (línea con define('SSO_API_KEY'))
 *         Usar LA MISMA clave que en SIGECEL/.env
 * 
 * PASO 4: Configurar URL de SIGECEL (línea con define('SIGECEL_URL'))
 *        
 *         Producción: https://sigecel.cel.org.pe
 *          
 * PASO 5: Modificar menú/sidebar del Sistema Principal
 *         Ver ejemplo de uso al final de este archivo
 * 
 * ============================================================================
 * CONFIGURACIÓN
 * ============================================================================
 */

// URL de SIGECEL (cambiar según ambiente)
// 
// Producción: https://sigecel.cel.org.pe
define('SIGECEL_URL', 'https://sigecel.cel.org.pe');

// API Key compartida (debe coincidir EXACTAMENTE con SSO_API_KEY en .env de SIGECEL)
// IMPORTANTE: Generar clave segura única con: php -r "echo bin2hex(random_bytes(32));"
// Ejemplo de clave generada: 9a97427a428faa5e42cbf9fda8ee89e32ef0d6273332da201d71d1a9297b68f8
define('SSO_API_KEY', '');  // ← PEGAR AQUÍ LA CLAVE GENERADA

/**
 * ============================================================================
 * FUNCIONES PRINCIPALES
 * ============================================================================
 */

/**
 * Generar URL de auto-login para SIGECEL
 * 
 * Esta función:
 * 1. Recibe el ID del usuario logueado en el Sistema Principal
 * 2. Hace petición HTTP a SIGECEL para generar un token SSO
 * 3. Retorna la URL completa con el token para redirigir al usuario
 * 
 * @param int $userId ID del usuario en la tabla cms_users
 * @return string|false URL de auto-login o false si falla
 * 
 * EJEMPLOS DE USO:
 * 
 * // Opción 1: Enlace simple
 * $url = generateSIGECELLink($_SESSION['user_id']);
 * echo "<a href='$url'>Ir a SIGECEL</a>";
 * 
 * // Opción 2: Redirección directa
 * $url = generateSIGECELLink($_SESSION['user_id']);
 * header("Location: $url");
 * exit;
 * 
 * // Opción 3: Con validación
 * if (isset($_SESSION['user_id'])) {
 *     $url = generateSIGECELLink($_SESSION['user_id']);
 *     echo "<a href='$url'>SIGECEL</a>";
 * } else {
 *     echo "Por favor, inicie sesión";
 * }
 */
function generateSIGECELLink($userId)
{
    // Validar que el user_id sea válido
    if (!$userId || !is_numeric($userId)) {
        error_log("SIGECEL Auto-Login: ID de usuario inválido: " . var_export($userId, true));
        return false;
    }

    // Preparar datos para enviar a SIGECEL
    $postData = [
        'user_id' => $userId,                            // ID del usuario en cms_users
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null, // IP del cliente
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null, // Navegador del cliente
        'api_key' => SSO_API_KEY,                        // Clave de autenticación
    ];

    // Configurar petición cURL a SIGECEL
    $ch = curl_init(SIGECEL_URL . '/api/generate-auto-login-token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,               // Retornar respuesta como string
        CURLOPT_POST => true,                         // Método POST
        CURLOPT_POSTFIELDS => http_build_query($postData), // Datos del form
        CURLOPT_HTTPHEADER => [
            'X-API-Key: ' . SSO_API_KEY,              // Header de autenticación
        ],
        CURLOPT_TIMEOUT => 5,                         // Timeout máximo: 5 segundos
        CURLOPT_CONNECTTIMEOUT => 3,                  // Timeout de conexión: 3 segundos
    ]);

    // Ejecutar petición
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    // Manejar error de conexión
    if ($error) {
        error_log("SIGECEL Auto-Login: Error de conexión - $error");
        // Fallback: redirigir a login manual
        return SIGECEL_URL . '/login';
    }

    // Manejar error HTTP
    if ($httpCode !== 200) {
        error_log("SIGECEL Auto-Login: Error HTTP $httpCode - Respuesta: $response");
        // Fallback: redirigir a login manual
        return SIGECEL_URL . '/login';
    }

    // Decodificar respuesta JSON
    $data = json_decode($response, true);

    // Validar respuesta
    if (!isset($data['success']) || !$data['success'] || !isset($data['url'])) {
        error_log("SIGECEL Auto-Login: Respuesta inválida - " . var_export($data, true));
        // Fallback: redirigir a login manual
        return SIGECEL_URL . '/login';
    }

    // Retornar URL de auto-login con token
    // Ejemplo: https://sigecel.cel.org.pe/auto-login?token=abc123...
    return $data['url'];
}

/**
 * ============================================================================
 * FUNCIONES AUXILIARES
 * ============================================================================
 */

/**
 * Generar enlace HTML completo para SIGECEL
 * 
 * @param int $userId ID del usuario
 * @param string $text Texto del enlace (opcional)
 * @param string $cssClass Clase CSS para el enlace (opcional)
 * @return string HTML del enlace <a>
 * 
 * EJEMPLO DE USO:
 * echo getSIGECELLinkHTML($_SESSION['user_id'], 'Ir a SIGECEL', 'btn btn-primary');
 * 
 * RESULTADO:
 * <a href="https://sigecel.cel.org.pe/auto-login?token=..." class="btn btn-primary">Ir a SIGECEL</a>
 */
function getSIGECELLinkHTML($userId, $text = 'Ir a SIGECEL', $cssClass = '')
{
    $url = generateSIGECELLink($userId);
    $class = $cssClass ? " class=\"$cssClass\"" : '';
    return "<a href=\"$url\"$class>$text</a>";
}

/**
 * Generar enlace directo al Reporte de Cumpleaños
 * 
 * @param int $userId ID del usuario
 * @return string URL de auto-login al reporte de cumpleaños
 * 
 * EJEMPLO DE USO:
 * $url = getBirthdayReportLink($_SESSION['user_id']);
 * echo "<a href='$url'>Reporte de Cumpleaños</a>";
 */
function getBirthdayReportLink($userId)
{
    $autoLoginUrl = generateSIGECELLink($userId);
    
    if ($autoLoginUrl === false || strpos($autoLoginUrl, '/login') !== false) {
        // Si falla auto-login, ir directo a login
        return SIGECEL_URL . '/login';
    }
    
    // Modificar URL para ir directamente al reporte de cumpleaños
    $token = parse_url($autoLoginUrl, PHP_URL_QUERY);
    return SIGECEL_URL . '/auto-login?' . $token . '#birthday';
}

/**
 * ============================================================================
 * EJEMPLOS DE INTEGRACIÓN EN EL SISTEMA PRINCIPAL
 * ============================================================================
 * 
 * A continuación se muestran diferentes formas de integrar este script
 * en el menú/sidebar del Sistema Principal CEL.
 */

/*
// ============================================================================
// EJEMPLO 1: Enlace Simple en Menú Lateral
// ============================================================================
Es realizar la prueba
<?php
session_start();
require_once 'includes/sigecel_autologin.php';

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $sigecelUrl = generateSIGECELLink($userId);
    
    echo '<div class="menu">';
    echo '  <a href="index.php">Inicio</a>';
    echo '  <a href="colegiados.php">Colegiados</a>';
    echo '  <a href="' . htmlspecialchars($sigecelUrl) . '">SIGECEL</a>';
    echo '  <a href="reportes.php">Reportes</a>';
    echo '</div>';
}
?>

// ============================================================================
// EJEMPLO 2: Menú con Bootstrap
// ============================================================================

<?php
session_start();
require_once 'includes/sigecel_autologin.php';

if (isset($_SESSION['user_id'])) {
    $sigecelUrl = generateSIGECELLink($_SESSION['user_id']);
?>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="nav-link" href="<?= htmlspecialchars($sigecelUrl) ?>">
                <i class="fa fa-rocket"></i> SIGECEL
            </a>
        </div>
    </nav>
<?php
}
?>

// ============================================================================
// EJEMPLO 3: Múltiples Enlaces a SIGECEL
// ============================================================================

<?php
session_start();
require_once 'includes/sigecel_autologin.php';

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    
    // Enlace principal a SIGECEL
    $sigecelUrl = generateSIGECELLink($userId);
    echo '<a href="' . $sigecelUrl . '">SIGECEL Dashboard</a>';
    
    // Enlace directo a Reporte de Cumpleaños
    $birthdayUrl = getBirthdayReportLink($userId);
    echo '<a href="' . $birthdayUrl . '">Cumpleaños</a>';
    
    // Enlace con HTML personalizado
    echo getSIGECELLinkHTML($userId, 'Ir a SIGECEL', 'btn btn-primary');
}
?>

// ============================================================================
// EJEMPLO 4: Con Validación y Fallback
// ============================================================================

<?php
session_start();
require_once 'includes/sigecel_autologin.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    echo '<a href="login.php">Iniciar Sesión</a>';
    exit;
}

// Generar enlace de auto-login
$sigecelUrl = generateSIGECELLink($_SESSION['user_id']);

// Si falla, mostrar enlace de login manual
if ($sigecelUrl === false) {
    $sigecelUrl = 'https://sigecel.cel.org.pe/login';
    $linkText = 'SIGECEL (Login Manual)';
} else {
    $linkText = 'SIGECEL';
}

echo '<a href="' . htmlspecialchars($sigecelUrl) . '">' . $linkText . '</a>';
?>

*/
