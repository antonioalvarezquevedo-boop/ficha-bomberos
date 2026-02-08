<?php
// Setup Database - Auto-initialize database on first run
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
      $host = 'mysql.railway.internal';
      $user = 'root';
      $pass = '';
      $dbname = 'ficha_bombero';

    try {
              // Conectar sin especificar BD
              $conn = new PDO("mysql:host=$host", $user, $pass);
              $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

              // Crear BD
              $conn->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
              $conn->exec("USE `$dbname`");

              // Crear tabla de usuarios
              $sql_usuarios = "CREATE TABLE IF NOT EXISTS usuarios (
                          id INT PRIMARY KEY AUTO_INCREMENT,
                                      username VARCHAR(100) UNIQUE NOT NULL,
                                                  password VARCHAR(255) NOT NULL,
                                                              nombre VARCHAR(100),
                                                                          nivel ENUM('administrador', 'oficial', 'consulta') DEFAULT 'oficial',
                                                                                      activo BOOLEAN DEFAULT TRUE,
                                                                                                  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                                                                                                          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
              $conn->exec($sql_usuarios);

              // Insertar usuario admin
              $stmt = $conn->prepare("INSERT IGNORE INTO usuarios (username, password, nombre, nivel) VALUES (?, ?, ?, ?)");
              $admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
              $stmt->execute(['admin', $admin_pass, 'Administrador', 'administrador']);

              echo json_encode(['success' => true, 'message' => 'Base de datos inicializada correctamente']);
    } catch (Exception $e) {
              echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
      exit;
}
?>
