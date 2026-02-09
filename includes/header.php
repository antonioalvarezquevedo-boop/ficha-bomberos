<header class="header">
    <div class="header-content">
        <div class="header-logo">
            <span class="logo-icon">CB</span>
            <div>
                <h1>Sistema de Ficha</h1>
                <small>Cuerpo de Bomberos de Vina del Mar</small>
            </div>
        </div>
        <div class="header-user">
            <span><?php echo $_SESSION['username'] ?? 'Admin'; ?></span>
            <a href="logout.php" class="btn btn-sm" style="background: rgba(255,255,255,0.2); color: white; padding: 5px 15px; border-radius: 5px; text-decoration: none; margin-left: 10px;">Cerrar Sesion</a>
        </div>
    </div>
</header>
