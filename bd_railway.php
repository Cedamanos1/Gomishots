<?php
/**
 * GOMISHOT 2.0 - Conexión a Base de Datos
 * Optimizado para Railway + Neon (PostgreSQL)
 * Compatible con MySQL local para desarrollo
 */

// Detectar si estamos en Railway (producción) o local
$is_production = getenv('RAILWAY_ENVIRONMENT') !== false;

if ($is_production) {
    // ══════════════════════════════════════════════════════════
    // PRODUCCIÓN: Railway + Neon (PostgreSQL)
    // ══════════════════════════════════════════════════════════
    
    $database_url = getenv('DATABASE_URL');
    
    if (!$database_url) {
        error_log("ERROR: DATABASE_URL no está configurada en Railway");
        die("Error de configuración. Contacte al administrador.");
    }
    
    // Parsear la URL de conexión de Neon
    $db = parse_url($database_url);
    
    $driver   = 'pgsql';
    $host     = $db['host'];
    $port     = $db['port'] ?? 5432;
    $dbname   = ltrim($db['path'], '/');
    $user     = $db['user'];
    $pass     = $db['pass'];
    $sslmode  = 'require';
    
    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode={$sslmode}";
    
    error_log("Conectando a Neon PostgreSQL: {$host}/{$dbname}");
    
} else {
    // ══════════════════════════════════════════════════════════
    // LOCAL: Desarrollo (MySQL o PostgreSQL)
    // ══════════════════════════════════════════════════════════
    
    $driver   = getenv('DB_CONNECTION') ?: 'mysql';
    $host     = getenv('DB_HOST')       ?: 'localhost';
    $dbname   = getenv('DB_NAME')       ?: 'gomishots';
    $user     = getenv('DB_USER')       ?: 'root';
    $pass     = getenv('DB_PASSWORD')   ?: '';
    $port     = getenv('DB_PORT')       ?: ($driver === 'pgsql' ? 5432 : 3306);
    $sslmode  = getenv('DB_SSLMODE')    ?: null;
    
    if ($driver === 'mysql') {
        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    } elseif ($driver === 'pgsql') {
        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
        if ($sslmode) {
            $dsn .= ";sslmode={$sslmode}";
        }
    } else {
        die("Driver de base de datos no soportado: {$driver}");
    }
    
    error_log("Entorno local: Conectando a {$driver} en {$host}");
}

// ══════════════════════════════════════════════════════════
// Establecer Conexión PDO
// ══════════════════════════════════════════════════════════

try {
    $pdo = new PDO($dsn, $user, $pass);
    
    // Configuración de PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Log de conexión exitosa solo en desarrollo
    if (!$is_production) {
        error_log("✅ Conexión exitosa a {$driver} - {$dbname}");
    }
    
} catch (PDOException $e) {
    // Manejo de errores
    $error_message = $e->getMessage();
    
    // Log del error completo (servidor)
    error_log("❌ Error de conexión PDO: " . $error_message);
    
    // En producción, no mostrar detalles del error al usuario
    if ($is_production) {
        die("Error de conexión a la base de datos. Por favor, contacte al administrador.");
    } else {
        // En desarrollo, mostrar el error completo
        die("Error de conexión: " . $error_message . "\n\nDSN: " . $dsn);
    }
}

// ══════════════════════════════════════════════════════════
// Funciones de Compatibilidad (opcional)
// ══════════════════════════════════════════════════════════

/**
 * Detecta si estamos usando PostgreSQL
 * @return bool
 */
function isPostgreSQL() {
    global $pdo;
    return $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql';
}

/**
 * Detecta si estamos usando MySQL
 * @return bool
 */
function isMySQL() {
    global $pdo;
    return $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql';
}
?>
