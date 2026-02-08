<?php
// Database Configuration
define('DB_HOST', 'mysql.railway.internal');
define('DB_USER', 'root');
define('DB_PASS', 'DFFUyclHZUZwXVyzJuAGAQGZMVBdkNBL');
define('DB_NAME', 'ficha_bombero');
define('DB_PORT', 3306);

// Site Configuration
define('SITE_URL', 'https://ficha-bomberos-production.up.railway.app');
define('SITE_NAME', 'Sistema Ficha Bombero');

// Session Configuration
if (session_status() === PHP_SESSION_NONE) {
      session_start();
}

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Database Connection Function
function getDB() {
      static $pdo = null;
      if ($pdo === null) {
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
                              die('Error de conexion: ' . $e->getMessage());
                }
      }
      return $pdo;
}

$pdo = getDB();
