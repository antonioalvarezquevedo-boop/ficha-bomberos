<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

requerirAuth();
verificarTimeout();

$db = getDB();

// Parámetros de búsqueda
$search = $_GET['search'] ?? '';
$compania = $_GET['compania'] ?? '';
$estado = $_GET['estado'] ?? 'activo';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Construir consulta
$where = [];
$params = [];

if ($estado) {
    $where[] = "b.estado = :estado";
    $params[':estado'] = $estado;
}

if ($search) {
    $where[] = "(b.nombre LIKE :search OR b.apellido_paterno LIKE :search OR 
                 b.apellido_materno LIKE :search OR b.rut LIKE :search OR 
                 b.numero_bombero LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($compania) {
    $where[] = "b.compania = :compania";
    $params[':compania'] = $compania;
}

$where_sql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

// Obtener total de registros
$sql_count = "SELECT COUNT(*) as total FROM bomberos b $where_sql";
$stmt = $db->prepare($sql_count);
$stmt->execute($params);
$total_registros = $stmt->fetch()['total'];
$total_paginas = ceil($total_registros / $per_page);

// Obtener bomberos
$sql = "SELECT 
            b.id,
            b.rut,
            b.numero_bombero,
            CONCAT(b.nombre, ' ', b.apellido_paterno, ' ', b.apellido_materno) as nombre_completo,
            b.compania,
            b.grado_actual,
            b.cargo_actual,
            b.fecha_ingreso,
            b.estado,
            b.foto,
            TIMESTAMPDIFF(YEAR, b.fecha_ingreso, CURDATE()) as anos_servicio,
            dp.celular,
            dp.email
        FROM bomberos b
        LEFT JOIN datos_personales dp ON b.id = dp.bombero_id
        $where_sql
        ORDER BY b.numero_bombero ASC
        LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$bomberos = $stmt->fetchAll();

// Obtener compañías para filtro
$sql_companias = "SELECT DISTINCT compania FROM bomberos WHERE compania IS NOT NULL ORDER BY compania";
$companias = $db->query($sql_companias)->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bomberos - Sistema Ficha Bombero</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/nav.php'; ?>
    
    <div class="container">
        <div class="d-flex justify-between align-center mb-3">
            <h2 style="color: var(--color-primario);">Gestión de Bomberos</h2>
            <?php if (tieneRol('oficial')): ?>
                <a href="nuevo_bombero.php" class="btn btn-primary">
                    + Nuevo Bombero
                </a>
            <?php endif; ?>
        </div>
        
        <!-- Filtros de búsqueda -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="">
                    <div class="form-row">
                        <div class="form-group" style="flex: 2;">
                            <label class="form-label" for="search">Búsqueda</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="search" 
                                name="search" 
                                placeholder="Nombre, RUT, N° Bombero..."
                                value="<?php echo htmlspecialchars($search); ?>"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="compania">Compañía</label>
                            <select class="form-control" id="compania" name="compania">
                                <option value="">Todas</option>
                                <?php foreach ($companias as $comp): ?>
                                    <option value="<?php echo $comp; ?>" <?php echo $compania === $comp ? 'selected' : ''; ?>>
                                        <?php echo $comp; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="estado">Estado</label>
                            <select class="form-control" id="estado" name="estado">
                                <option value="">Todos</option>
                                <option value="activo" <?php echo $estado === 'activo' ? 'selected' : ''; ?>>Activo</option>
                                <option value="inactivo" <?php echo $estado === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                                <option value="retirado" <?php echo $estado === 'retirado' ? 'selected' : ''; ?>>Retirado</option>
                            </select>
                        </div>
                        
                        <div class="form-group" style="display: flex; align-items: flex-end;">
                            <button type="submit" class="btn btn-primary" style="margin-right: 10px;">
                                Buscar
                            </button>
                            <a href="bomberos.php" class="btn btn-secondary">
                                Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Resultados -->
        <div class="card">
            <div class="card-header">
                Bomberos (<?php echo $total_registros; ?> registros)
            </div>
            <div class="card-body">
                <?php if (count($bomberos) > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Foto</th>
                                    <th>N° Bombero</th>
                                    <th>RUT</th>
                                    <th>Nombre Completo</th>
                                    <th>Compañía</th>
                                    <th>Grado</th>
                                    <th>Años Servicio</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bomberos as $bombero): ?>
                                    <tr>
                                        <td>
                                            <?php if ($bombero['foto']): ?>
                                                <img src="uploads/<?php echo $bombero['foto']; ?>" 
                                                     alt="Foto" 
                                                     style="width: 40px; height: 50px; object-fit: cover; border-radius: 5px;">
                                            <?php else: ?>
                                                <div style="width: 40px; height: 50px; background: var(--color-borde); border-radius: 5px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; color: var(--color-texto-claro);">
                                                    Sin foto
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?php echo $bombero['numero_bombero']; ?></strong></td>
                                        <td><?php echo formatearRut($bombero['rut']); ?></td>
                                        <td><?php echo $bombero['nombre_completo']; ?></td>
                                        <td><?php echo $bombero['compania']; ?></td>
                                        <td><?php echo $bombero['grado_actual']; ?></td>
                                        <td><?php echo $bombero['anos_servicio']; ?> años</td>
                                        <td>
                                            <?php
                                            $badge_class = [
                                                'activo' => 'badge-success',
                                                'inactivo' => 'badge-warning',
                                                'retirado' => 'badge-secondary',
                                                'fallecido' => 'badge-danger'
                                            ];
                                            ?>
                                            <span class="badge <?php echo $badge_class[$bombero['estado']] ?? 'badge-secondary'; ?>">
                                                <?php echo ucfirst($bombero['estado']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="ficha.php?id=<?php echo $bombero['id']; ?>" 
                                                   class="btn btn-info btn-sm">
                                                    Ver Ficha
                                                </a>
                                                <?php if (tieneRol('oficial')): ?>
                                                    <a href="editar_bombero.php?id=<?php echo $bombero['id']; ?>" 
                                                       class="btn btn-secondary btn-sm">
                                                        Editar
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación -->
                    <?php if ($total_paginas > 1): ?>
                        <div class="pagination">
                            <?php
                            $query_params = $_GET;
                            unset($query_params['page']);
                            $query_string = http_build_query($query_params);
                            $query_string = $query_string ? '&' . $query_string : '';
                            ?>
                            
                            <button 
                                onclick="window.location.href='?page=1<?php echo $query_string; ?>'" 
                                <?php echo $page <= 1 ? 'disabled' : ''; ?>>
                                &laquo;
                            </button>
                            
                            <button 
                                onclick="window.location.href='?page=<?php echo $page-1; ?><?php echo $query_string; ?>'" 
                                <?php echo $page <= 1 ? 'disabled' : ''; ?>>
                                &lsaquo;
                            </button>
                            
                            <?php
                            $start = max(1, $page - 2);
                            $end = min($total_paginas, $page + 2);
                            
                            for ($i = $start; $i <= $end; $i++):
                            ?>
                                <button 
                                    onclick="window.location.href='?page=<?php echo $i; ?><?php echo $query_string; ?>'"
                                    class="<?php echo $i === $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </button>
                            <?php endfor; ?>
                            
                            <button 
                                onclick="window.location.href='?page=<?php echo $page+1; ?><?php echo $query_string; ?>'" 
                                <?php echo $page >= $total_paginas ? 'disabled' : ''; ?>>
                                &rsaquo;
                            </button>
                            
                            <button 
                                onclick="window.location.href='?page=<?php echo $total_paginas; ?><?php echo $query_string; ?>'" 
                                <?php echo $page >= $total_paginas ? 'disabled' : ''; ?>>
                                &raquo;
                            </button>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <p class="text-center" style="color: var(--color-texto-claro); padding: 40px;">
                        No se encontraron bomberos con los criterios de búsqueda especificados
                    </p>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>
