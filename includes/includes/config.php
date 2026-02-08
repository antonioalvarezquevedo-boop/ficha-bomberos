<?php
// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'mysql.railway.internal');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'ficha_bombero');
define('DB_PORT', getenv('DB_PORT') ?: 3306);

// Site Configuration
define('SITE_URL', getenv('SITE_URL') ?: 'https://ficha-bomberos-production.up.railway.app');
define('SITE_NAME', 'Sistema Ficha Bombero');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.gc_maxlifetime', 3600);

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', getenv('APP_DEBUG') ? 1 : 0);
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/php-errors.log');

// PDO Database Connection
try {
      $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                array(
                              PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                              PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                          )
            );
} catch(PDOException $e) {
      die('Error de conexión a la base de datos: ' . $e->getMessage());
}
?>
