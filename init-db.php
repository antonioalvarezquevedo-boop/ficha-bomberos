<?php
// Script para inicializar la BD automáticamente
$host = getenv('DB_HOST') ?: 'mysql.railway.internal';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$dbname = getenv('DB_NAME') ?: 'ficha_bombero';

try {
      $conn = new PDO("mysql:host=$host", $user, $pass);

      // Crear BD si no existe
      $conn->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

      // Seleccionar BD
      $conn->exec("USE `$dbname`");

      // Crear tabla de usuarios
      $conn->exec("CREATE TABLE IF NOT EXISTS usuarios (
              id INT PRIMARY KEY AUTO_INCREMENT,
                      username VARCHAR(100) UNIQUE NOT NULL,
                              password VARCHAR(255) NOT NULL,
                                      nombre VARCHAR(100),
                                              nivel ENUM('administrador', 'oficial', 'consulta') DEFAULT 'oficial',
                                                      activo BOOLEAN DEFAULT TRUE,
                                                              fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                                                                  )");

      // Crear usuario admin por defecto
      $admin_user = 'admin';
      $admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
      $conn->exec("INSERT IGNORE INTO usuarios (username, password, nombre, nivel) VALUES ('$admin_user', '$admin_pass', 'Administrador', 'administrador')");

      echo json_encode(['success' => true, 'message' => 'Base de datos inicializada correctamente']);
} catch (Exception $e) {
      echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
