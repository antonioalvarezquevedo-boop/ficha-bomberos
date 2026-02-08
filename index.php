<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

requerirAuth();
verificarTimeout();

$db = getDB();

// Obtener estadísticas
$stats = [];

// Total bomberos activos
$sql = "SELECT COUNT(*) as total FROM bomberos WHERE estado = 'activo'";
$stats['bomberos_activos'] = $db->query($sql)->fetch()['total'];

// Total servicios este mes
$sql = "SELECT COUNT(*) as total FROM servicios 
        WHERE MONTH(fecha_servicio) = MONTH(CURDATE()) 
        AND YEAR(fecha_servicio) = YEAR(CURDATE())";
$stats['servicios_mes'] = $db->query($sql)->fetch()['total'];

// Certificaciones por vencer
$sql = "SELECT COUNT(*) as total FROM certificaciones 
        WHERE vigente = 1 
        AND fecha_vencimiento IS NOT NULL 
        AND DATEDIFF(fecha_vencimiento, CURDATE()) BETWEEN 0 AND 30";
$stats['cert_vencer'] = $db->query($sql)->fetch()['total'];

// Cursos realizados este año
$sql = "SELECT COUNT(*) as total FROM cursos 
        WHERE YEAR(fecha_termino) = YEAR(CURDATE())";
$stats['cursos_anio'] = $db->query($sql)->fetch()['total'];

// Últimos bomberos ingresados
$sql = "SELECT 
            id,
            numero_bombero,
            CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) as nombre_completo,
            compania,
            grado_actual,
            fecha_ingreso,
            TIMESTAMPDIFF(DAY, fecha_ingreso, CURDATE()) as dias_servicio
        FROM bomberos 
        WHERE estado = 'activo'
        ORDER BY fecha_ingreso DESC 
        LIMIT 5";
$ultimos_bomberos = $db->query($sql)->fetchAll();

// Certificaciones por vencer próximamente
$sql = "SELECT 
            b.numero_bombero,
            CONCAT(b.nombre, ' ', b.apellido_paterno, ' ', b.apellido_materno) as nombre_completo,
            c.nombre_certificacion,
            c.fecha_vencimiento,
            DATEDIFF(c.fecha_vencimiento, CURDATE()) as dias_restantes
        FROM certificaciones c
        INNER JOIN bomberos b ON c.bombero_id = b.id
        WHERE c.vigente = 1 
        AND c.fecha_vencimiento IS NOT NULL
        AND DATEDIFF(c.fecha_vencimiento, CURDATE()) BETWEEN 0 AND 30
        ORDER BY c.fecha_vencimiento ASC
        LIMIT 10";
$cert_vencer = $db->query($sql)->fetchAll();

// Servicios recientes
$sql = "SELECT 
            s.fecha_servicio,
            s.tipo_emergencia,
            s.direccion,
            CONCAT(b.nombre, ' ', b.apellido_paterno) as bombero,
            b.numero_bombero
        FROM servicios s
        INNER JOIN bomberos b ON s.bombero_id = b.id
        ORDER BY s.fecha_servicio DESC
        LIMIT 8";
$servicios_recientes = $db->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema Ficha Bombero</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/nav.php'; ?>
    
    <div class="container">
        <h2 style="margin-bottom: 25px; color: var(--color-primario);">
            Panel de Control
        </h2>
        
        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['bomberos_activos']; ?></div>
                <div class="stat-label">Bomberos Activos</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['servicios_mes']; ?></div>
                <div class="stat-label">Servicios Este Mes</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value" style="color: var(--color-advertencia);">
                    <?php echo $stats['cert_vencer']; ?>
                </div>
                <div class="stat-label">Certificaciones por Vencer</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value" style="color: var(--color-exito);">
                    <?php echo $stats['cursos_anio']; ?>
                </div>
                <div class="stat-label">Cursos Este Año</div>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(500px, 1fr)); gap: 25px;">
            
            <!-- Últimos bomberos ingresados -->
            <div class="card">
                <div class="card-header">
                    Últimos Bomberos Ingresados
                </div>
                <div class="card-body">
                    <?php if (count($ultimos_bomberos) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>N°</th>
                                        <th>Nombre</th>
                                        <th>Compañía</th>
                                        <th>Días Servicio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ultimos_bomberos as $bombero): ?>
                                        <tr>
                                            <td>
                                                <a href="ficha.php?id=<?php echo $bombero['id']; ?>" style="color: var(--color-primario); text-decoration: none; font-weight: 600;">
                                                    <?php echo $bombero['numero_bombero']; ?>
                                                </a>
                                            </td>
                                            <td><?php echo $bombero['nombre_completo']; ?></td>
                                            <td><?php echo $bombero['compania']; ?></td>
                                            <td><?php echo $bombero['dias_servicio']; ?> días</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center" style="color: var(--color-texto-claro);">
                            No hay bomberos registrados
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Certificaciones por vencer -->
            <div class="card">
                <div class="card-header">
                    Certificaciones por Vencer (Próximos 30 días)
                </div>
                <div class="card-body">
                    <?php if (count($cert_vencer) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Bombero</th>
                                        <th>Certificación</th>
                                        <th>Días</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cert_vencer as $cert): ?>
                                        <tr>
                                            <td><?php echo $cert['numero_bombero']; ?></td>
                                            <td><?php echo $cert['nombre_certificacion']; ?></td>
                                            <td>
                                                <span class="badge <?php echo $cert['dias_restantes'] <= 7 ? 'badge-danger' : 'badge-warning'; ?>">
                                                    <?php echo $cert['dias_restantes']; ?> días
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center" style="color: var(--color-texto-claro);">
                            No hay certificaciones próximas a vencer
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
        
        <!-- Servicios recientes -->
        <div class="card mt-3">
            <div class="card-header">
                Servicios Recientes
            </div>
            <div class="card-body">
                <?php if (count($servicios_recientes) > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Fecha/Hora</th>
                                    <th>Tipo Emergencia</th>
                                    <th>Dirección</th>
                                    <th>Bombero</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($servicios_recientes as $servicio): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($servicio['fecha_servicio'])); ?></td>
                                        <td><?php echo $servicio['tipo_emergencia']; ?></td>
                                        <td><?php echo $servicio['direccion'] ?? '-'; ?></td>
                                        <td><?php echo $servicio['bombero']; ?> (<?php echo $servicio['numero_bombero']; ?>)</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center" style="color: var(--color-texto-claro);">
                        No hay servicios registrados
                    </p>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>
