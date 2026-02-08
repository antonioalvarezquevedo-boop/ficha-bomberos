<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación - Sistema Ficha Bombero</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #c41e3a 0%, #1a1a1a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
        }
        
        h1 {
            color: #c41e3a;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .step {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #c41e3a;
        }
        
        .step h3 {
            color: #c41e3a;
            margin-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        button {
            background: #c41e3a;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
        }
        
        button:hover {
            background: #a01828;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #28a745;
            margin-bottom: 20px;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #dc3545;
            margin-bottom: 20px;
        }
        
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #17a2b8;
            margin-bottom: 20px;
        }
        
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚒 Instalación Sistema Ficha Bombero</h1>
        
        <?php
        $instalado = file_exists('includes/instalado.lock');
        
        if ($instalado) {
            echo '<div class="success">';
            echo '<strong>✓ Sistema ya instalado</strong><br>';
            echo 'El sistema ya ha sido instalado correctamente.';
            echo '</div>';
            echo '<a href="index.php" style="display: block; text-align: center; color: #c41e3a; text-decoration: none; font-weight: bold;">Ir al sistema →</a>';
        } else {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $errores = [];
                
                // Validar datos
                $db_host = $_POST['db_host'] ?? '';
                $db_name = $_POST['db_name'] ?? '';
                $db_user = $_POST['db_user'] ?? '';
                $db_pass = $_POST['db_pass'] ?? '';
                
                if (empty($db_host) || empty($db_name) || empty($db_user)) {
                    $errores[] = 'Todos los campos de base de datos son obligatorios';
                }
                
                if (empty($errores)) {
                    // Intentar conexión
                    try {
                        $dsn = "mysql:host=$db_host;charset=utf8mb4";
                        $pdo = new PDO($dsn, $db_user, $db_pass);
                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        
                        // Crear base de datos si no existe
                        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                        $pdo->exec("USE `$db_name`");
                        
                        // Leer y ejecutar SQL
                        $sql = file_get_contents('sql/database.sql');
                        $sql = str_replace('USE ficha_bombero;', '', $sql);
                        
                        // Ejecutar por partes
                        $statements = array_filter(array_map('trim', explode(';', $sql)));
                        
                        foreach ($statements as $statement) {
                            if (!empty($statement)) {
                                $pdo->exec($statement);
                            }
                        }
                        
                        // Actualizar config.php
                        $config_content = file_get_contents('includes/config.php');
                        $config_content = str_replace("define('DB_HOST', 'localhost');", "define('DB_HOST', '$db_host');", $config_content);
                        $config_content = str_replace("define('DB_USER', 'root');", "define('DB_USER', '$db_user');", $config_content);
                        $config_content = str_replace("define('DB_PASS', '');", "define('DB_PASS', '$db_pass');", $config_content);
                        $config_content = str_replace("define('DB_NAME', 'ficha_bombero');", "define('DB_NAME', '$db_name');", $config_content);
                        
                        file_put_contents('includes/config.php', $config_content);
                        
                        // Crear archivo de instalación completada
                        file_put_contents('includes/instalado.lock', date('Y-m-d H:i:s'));
                        
                        echo '<div class="success">';
                        echo '<strong>✓ Instalación completada exitosamente</strong><br><br>';
                        echo 'Base de datos creada e inicializada correctamente.<br><br>';
                        echo '<strong>Credenciales de acceso:</strong><br>';
                        echo 'Usuario: <code>admin</code><br>';
                        echo 'Contraseña: <code>admin123</code><br><br>';
                        echo '<strong>⚠️ IMPORTANTE:</strong> Cambie la contraseña del administrador inmediatamente después del primer acceso.';
                        echo '</div>';
                        
                        echo '<a href="index.php" style="display: inline-block; background: #c41e3a; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; width: 100%; text-align: center; margin-top: 20px;">Acceder al Sistema →</a>';
                        
                    } catch (PDOException $e) {
                        $errores[] = 'Error de conexión: ' . $e->getMessage();
                    }
                }
                
                if (!empty($errores)) {
                    echo '<div class="error">';
                    echo '<strong>Errores en la instalación:</strong><br><br>';
                    foreach ($errores as $error) {
                        echo '• ' . $error . '<br>';
                    }
                    echo '</div>';
                }
            }
            
            if (!isset($_POST['db_host']) || !empty($errores)) {
        ?>
        
        <div class="info">
            <strong>Antes de comenzar:</strong><br>
            Asegúrese de tener creada una base de datos MySQL/MariaDB y las credenciales de acceso.
        </div>
        
        <form method="POST" action="">
            <div class="step">
                <h3>Paso 1: Configuración de Base de Datos</h3>
                
                <div class="form-group">
                    <label>Host de Base de Datos:</label>
                    <input type="text" name="db_host" value="localhost" required>
                </div>
                
                <div class="form-group">
                    <label>Nombre de Base de Datos:</label>
                    <input type="text" name="db_name" value="ficha_bombero" required>
                </div>
                
                <div class="form-group">
                    <label>Usuario de Base de Datos:</label>
                    <input type="text" name="db_user" value="root" required>
                </div>
                
                <div class="form-group">
                    <label>Contraseña de Base de Datos:</label>
                    <input type="password" name="db_pass">
                </div>
            </div>
            
            <button type="submit">Instalar Sistema</button>
        </form>
        
        <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; font-size: 12px; color: #666;">
            <strong>Nota:</strong> Si necesita crear manualmente la base de datos:<br>
            <code>CREATE DATABASE ficha_bombero CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</code>
        </div>
        
        <?php
            }
        }
        ?>
    </div>
</body>
</html>
