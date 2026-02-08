<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Si ya está autenticado, redirigir al dashboard
if (estaAutenticado()) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = limpiarInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Por favor ingrese usuario y contraseña';
    } else {
        if (login($username, $password)) {
            header('Location: index.php');
            exit();
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Ficha Bombero</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #c41e3a 0%, #1a1a1a 100%);
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-logo {
            width: 80px;
            height: 80px;
            background: var(--color-primario);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
        }
        
        .login-header h1 {
            color: var(--color-primario);
            margin-bottom: 5px;
        }
        
        .login-header p {
            color: var(--color-texto-claro);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-logo">CB</div>
            <h1>Sistema de Ficha</h1>
            <p>Cuerpo de Bomberos de Viña del Mar</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="username">Usuario</label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="username" 
                    name="username" 
                    required 
                    autofocus
                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                >
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">Contraseña</label>
                <input 
                    type="password" 
                    class="form-control" 
                    id="password" 
                    name="password" 
                    required
                >
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Iniciar Sesión
            </button>
        </form>
        
        <div style="margin-top: 20px; text-align: center; color: var(--color-texto-claro); font-size: 0.85rem;">
            Usuario por defecto: admin / Contraseña: admin123
        </div>
    </div>
</body>
</html>
