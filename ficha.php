<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

requerirAuth();
verificarTimeout();

$db = getDB();
$bombero_id = $_GET['id'] ?? 0;

if (!$bombero_id) {
    header('Location: bomberos.php');
    exit();
}

// Obtener datos del bombero
$sql = "SELECT 
            b.*,
            dp.*,
            TIMESTAMPDIFF(YEAR, b.fecha_nacimiento, CURDATE()) as edad,
            TIMESTAMPDIFF(YEAR, b.fecha_ingreso, CURDATE()) as anos_servicio,
            TIMESTAMPDIFF(DAY, b.fecha_ingreso, CURDATE()) as dias_servicio
        FROM bomberos b
        LEFT JOIN datos_personales dp ON b.id = dp.bombero_id
        WHERE b.id = :id";

$stmt = $db->prepare($sql);
$stmt->execute([':id' => $bombero_id]);
$bombero = $stmt->fetch();

if (!$bombero) {
    header('Location: bomberos.php');
    exit();
}

// Obtener historial de grados
$sql = "SELECT * FROM historial_grados WHERE bombero_id = :id ORDER BY fecha_promocion DESC";
$stmt = $db->prepare($sql);
$stmt->execute([':id' => $bombero_id]);
$historial_grados = $stmt->fetchAll();

// Obtener historial de cargos
$sql = "SELECT * FROM historial_cargos WHERE bombero_id = :id ORDER BY fecha_inicio DESC";
$stmt = $db->prepare($sql);
$stmt->execute([':id' => $bombero_id]);
$historial_cargos = $stmt->fetchAll();

// Obtener cursos
$sql = "SELECT * FROM cursos WHERE bombero_id = :id ORDER BY fecha_termino DESC";
$stmt = $db->prepare($sql);
$stmt->execute([':id' => $bombero_id]);
$cursos = $stmt->fetchAll();

// Obtener certificaciones
$sql = "SELECT * FROM certificaciones WHERE bombero_id = :id ORDER BY fecha_vencimiento ASC";
$stmt = $db->prepare($sql);
$stmt->execute([':id' => $bombero_id]);
$certificaciones = $stmt->fetchAll();

// Obtener sanciones
$sql = "SELECT * FROM sanciones WHERE bombero_id = :id ORDER BY fecha_falta DESC";
$stmt = $db->prepare($sql);
$stmt->execute([':id' => $bombero_id]);
$sanciones = $stmt->fetchAll();

// Obtener reconocimientos
$sql = "SELECT * FROM reconocimientos WHERE bombero_id = :id ORDER BY fecha DESC";
$stmt = $db->prepare($sql);
$stmt->execute([':id' => $bombero_id]);
$reconocimientos = $stmt->fetchAll();

// Obtener servicios
$sql = "SELECT * FROM servicios WHERE bombero_id = :id ORDER BY fecha_servicio DESC LIMIT 50";
$stmt = $db->prepare($sql);
$stmt->execute([':id' => $bombero_id]);
$servicios = $stmt->fetchAll();

// Estadísticas de servicios
$sql = "SELECT 
            COUNT(*) as total_servicios,
            SUM(horas_servicio) as total_horas,
            MAX(fecha_servicio) as ultimo_servicio
        FROM servicios WHERE bombero_id = :id";
$stmt = $db->prepare($sql);
$stmt->execute([':id' => $bombero_id]);
$stats_servicios = $stmt->fetch();

// Obtener equipamiento
$sql = "SELECT * FROM equipamiento WHERE bombero_id = :id ORDER BY fecha_asignacion DESC";
$stmt = $db->prepare($sql);
$stmt->execute([':id' => $bombero_id]);
$equipamiento = $stmt->fetchAll();

// Obtener evaluaciones
$sql = "SELECT * FROM evaluaciones WHERE bombero_id = :id ORDER BY fecha_evaluacion DESC";
$stmt = $db->prepare($sql);
$stmt->execute([':id' => $bombero_id]);
$evaluaciones = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha - <?php echo $bombero['nombre'] . ' ' . $bombero['apellido_paterno']; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            .header, .nav { display: none !important; }
            .card { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/nav.php'; ?>
    
    <div class="container">
        <!-- Encabezado de ficha -->
        <div class="ficha-header">
            <div class="ficha-foto">
                <?php if ($bombero['foto']): ?>
                    <img src="uploads/<?php echo $bombero['foto']; ?>" alt="Foto" class="foto-bombero">
                <?php else: ?>
                    <div class="foto-placeholder">
                        <?php echo strtoupper(substr($bombero['nombre'], 0, 1) . substr($bombero['apellido_paterno'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="ficha-datos" style="flex-grow: 1;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px;">
                    <div>
                        <h2 style="color: var(--color-primario); margin-bottom: 5px;">
                            <?php echo $bombero['nombre'] . ' ' . $bombero['apellido_paterno'] . ' ' . $bombero['apellido_materno']; ?>
                        </h2>
                        <p style="color: var(--color-texto-claro); font-size: 1.1rem;">
                            <?php echo $bombero['grado_actual']; ?>
                            <?php if ($bombero['cargo_actual']): ?>
                                - <?php echo $bombero['cargo_actual']; ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="btn-group no-print">
                        <?php if (tieneRol('oficial')): ?>
                            <a href="editar_bombero.php?id=<?php echo $bombero_id; ?>" class="btn btn-primary">
                                Editar Datos
                            </a>
                        <?php endif; ?>
                        <button onclick="window.print()" class="btn btn-secondary">
                            Imprimir Ficha
                        </button>
                        <a href="bomberos.php" class="btn btn-secondary">
                            Volver
                        </a>
                    </div>
                </div>
                
                <div class="datos-grid">
                    <div class="dato-item">
                        <div class="dato-label">N° Bombero</div>
                        <div class="dato-valor"><?php echo $bombero['numero_bombero']; ?></div>
                    </div>
                    <div class="dato-item">
                        <div class="dato-label">RUT</div>
                        <div class="dato-valor"><?php echo formatearRut($bombero['rut']); ?></div>
                    </div>
                    <div class="dato-item">
                        <div class="dato-label">Compañía</div>
                        <div class="dato-valor"><?php echo $bombero['compania']; ?></div>
                    </div>
                    <div class="dato-item">
                        <div class="dato-label">Fecha Ingreso</div>
                        <div class="dato-valor"><?php echo date('d/m/Y', strtotime($bombero['fecha_ingreso'])); ?></div>
                    </div>
                    <div class="dato-item">
                        <div class="dato-label">Años de Servicio</div>
                        <div class="dato-valor"><?php echo $bombero['anos_servicio']; ?> años</div>
                    </div>
                    <div class="dato-item">
                        <div class="dato-label">Estado</div>
                        <div class="dato-valor">
                            <span class="badge badge-<?php echo $bombero['estado'] === 'activo' ? 'success' : 'secondary'; ?>">
                                <?php echo ucfirst($bombero['estado']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="tabs no-print">
            <button class="tab active" data-tab="personales">Datos Personales</button>
            <button class="tab" data-tab="institucional">Historial Institucional</button>
            <button class="tab" data-tab="capacitacion">Capacitación</button>
            <button class="tab" data-tab="disciplinario">Registro Disciplinario</button>
            <button class="tab" data-tab="reconocimientos">Reconocimientos</button>
            <button class="tab" data-tab="servicios">Servicios</button>
            <button class="tab" data-tab="equipamiento">Equipamiento</button>
            <button class="tab" data-tab="evaluaciones">Evaluaciones</button>
        </div>
        
        <!-- Contenido Tab 1: Datos Personales -->
        <div class="tab-content active" id="personales">
            <div class="card">
                <div class="card-header">Datos Personales</div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Nombre Completo</label>
                            <div class="dato-valor">
                                <?php echo $bombero['nombre'] . ' ' . $bombero['apellido_paterno'] . ' ' . $bombero['apellido_materno']; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fecha de Nacimiento</label>
                            <div class="dato-valor">
                                <?php echo date('d/m/Y', strtotime($bombero['fecha_nacimiento'])); ?>
                                (<?php echo $bombero['edad']; ?> años)
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Dirección</label>
                            <div class="dato-valor"><?php echo $bombero['direccion'] ?? 'No registrada'; ?></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Comuna</label>
                            <div class="dato-valor"><?php echo $bombero['comuna'] ?? 'No registrada'; ?></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ciudad</label>
                            <div class="dato-valor"><?php echo $bombero['ciudad'] ?? 'No registrada'; ?></div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Teléfono</label>
                            <div class="dato-valor"><?php echo $bombero['telefono'] ?? 'No registrado'; ?></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Celular</label>
                            <div class="dato-valor"><?php echo $bombero['celular'] ?? 'No registrado'; ?></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <div class="dato-valor"><?php echo $bombero['email'] ?? 'No registrado'; ?></div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Grupo Sanguíneo</label>
                            <div class="dato-valor"><?php echo $bombero['grupo_sanguineo'] ?? 'No registrado'; ?></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Condiciones Médicas</label>
                            <div class="dato-valor"><?php echo $bombero['condiciones_medicas'] ?? 'Ninguna registrada'; ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">Contacto de Emergencia</div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Nombre</label>
                            <div class="dato-valor"><?php echo $bombero['contacto_emergencia_nombre'] ?? 'No registrado'; ?></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Teléfono</label>
                            <div class="dato-valor"><?php echo $bombero['contacto_emergencia_telefono'] ?? 'No registrado'; ?></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Relación</label>
                            <div class="dato-valor"><?php echo $bombero['contacto_emergencia_relacion'] ?? 'No registrada'; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contenido Tab 2: Historial Institucional -->
        <div class="tab-content" id="institucional">
            <div class="card">
                <div class="card-header">
                    Historial de Grados
                    <?php if (tieneRol('oficial')): ?>
                        <button class="btn btn-sm btn-primary no-print" onclick="abrirModalGrado()">
                            + Agregar Grado
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (count($historial_grados) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Grado</th>
                                        <th>Fecha Promoción</th>
                                        <th>Resolución</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historial_grados as $grado): ?>
                                        <tr>
                                            <td><strong><?php echo $grado['grado']; ?></strong></td>
                                            <td><?php echo date('d/m/Y', strtotime($grado['fecha_promocion'])); ?></td>
                                            <td><?php echo $grado['resolucion'] ?? '-'; ?></td>
                                            <td><?php echo $grado['observaciones'] ?? '-'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center" style="color: var(--color-texto-claro);">
                            No hay historial de grados registrado
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card mt-2">
                <div class="card-header">
                    Historial de Cargos
                    <?php if (tieneRol('oficial')): ?>
                        <button class="btn btn-sm btn-primary no-print" onclick="abrirModalCargo()">
                            + Agregar Cargo
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (count($historial_cargos) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Cargo</th>
                                        <th>Compañía</th>
                                        <th>Desde</th>
                                        <th>Hasta</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historial_cargos as $cargo): ?>
                                        <tr>
                                            <td><strong><?php echo $cargo['cargo']; ?></strong></td>
                                            <td><?php echo $cargo['compania']; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($cargo['fecha_inicio'])); ?></td>
                                            <td>
                                                <?php 
                                                echo $cargo['fecha_termino'] 
                                                    ? date('d/m/Y', strtotime($cargo['fecha_termino'])) 
                                                    : '<span class="badge badge-success">Vigente</span>'; 
                                                ?>
                                            </td>
                                            <td>
                                                <?php if (!$cargo['fecha_termino']): ?>
                                                    <span class="badge badge-success">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Finalizado</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center" style="color: var(--color-texto-claro);">
                            No hay cargos registrados
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Contenido Tab 3: Capacitación -->
        <div class="tab-content" id="capacitacion">
            <div class="card">
                <div class="card-header">
                    Cursos y Capacitaciones
                    <?php if (tieneRol('oficial')): ?>
                        <button class="btn btn-sm btn-primary no-print" onclick="abrirModalCurso()">
                            + Agregar Curso
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (count($cursos) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Curso</th>
                                        <th>Institución</th>
                                        <th>Tipo</th>
                                        <th>Fecha</th>
                                        <th>Horas</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cursos as $curso): ?>
                                        <tr>
                                            <td><strong><?php echo $curso['nombre_curso']; ?></strong></td>
                                            <td><?php echo $curso['institucion']; ?></td>
                                            <td><?php echo ucfirst($curso['tipo_curso']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($curso['fecha_termino'])); ?></td>
                                            <td><?php echo $curso['horas_cronologicas']; ?> hrs</td>
                                            <td>
                                                <span class="badge badge-<?php echo $curso['aprobado'] ? 'success' : 'danger'; ?>">
                                                    <?php echo $curso['aprobado'] ? 'Aprobado' : 'Reprobado'; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center" style="color: var(--color-texto-claro);">
                            No hay cursos registrados
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card mt-2">
                <div class="card-header">
                    Certificaciones Vigentes
                    <?php if (tieneRol('oficial')): ?>
                        <button class="btn btn-sm btn-primary no-print" onclick="abrirModalCertificacion()">
                            + Agregar Certificación
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (count($certificaciones) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Certificación</th>
                                        <th>Entidad Emisora</th>
                                        <th>Emisión</th>
                                        <th>Vencimiento</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($certificaciones as $cert): ?>
                                        <tr>
                                            <td><strong><?php echo $cert['nombre_certificacion']; ?></strong></td>
                                            <td><?php echo $cert['entidad_emisora']; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($cert['fecha_emision'])); ?></td>
                                            <td>
                                                <?php 
                                                if ($cert['fecha_vencimiento']) {
                                                    echo date('d/m/Y', strtotime($cert['fecha_vencimiento']));
                                                    $dias = (strtotime($cert['fecha_vencimiento']) - time()) / (60*60*24);
                                                    if ($dias > 0 && $dias <= 30) {
                                                        echo ' <span class="badge badge-warning">' . round($dias) . ' días</span>';
                                                    }
                                                } else {
                                                    echo 'Sin vencimiento';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo $cert['vigente'] ? 'success' : 'danger'; ?>">
                                                    <?php echo $cert['vigente'] ? 'Vigente' : 'Vencida'; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center" style="color: var(--color-texto-claro);">
                            No hay certificaciones registradas
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Tab 4: Registro Disciplinario -->
        <div class="tab-content" id="disciplinario">
            <div class="card">
                <div class="card-header">
                    Registro Disciplinario
                    <?php if (tieneRol('oficial')): ?>
                        <button class="btn btn-sm btn-danger no-print" onclick="abrirModalSancion()">
                            + Registrar Sanción
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (count($sanciones) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Fecha Falta</th>
                                        <th>Motivo</th>
                                        <th>Resolución</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sanciones as $sancion): ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                $tipos = [
                                                    'amonestacion_verbal' => 'Amonestación Verbal',
                                                    'amonestacion_escrita' => 'Amonestación Escrita',
                                                    'suspension' => 'Suspensión',
                                                    'multa' => 'Multa',
                                                    'otra' => 'Otra'
                                                ];
                                                echo $tipos[$sancion['tipo']];
                                                ?>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($sancion['fecha_falta'])); ?></td>
                                            <td><?php echo substr($sancion['motivo'], 0, 100) . '...'; ?></td>
                                            <td><?php echo $sancion['numero_resolucion'] ?? '-'; ?></td>
                                            <td>
                                                <?php
                                                $badge_estado = [
                                                    'vigente' => 'badge-danger',
                                                    'cumplida' => 'badge-success',
                                                    'anulada' => 'badge-secondary'
                                                ];
                                                ?>
                                                <span class="badge <?php echo $badge_estado[$sancion['estado']]; ?>">
                                                    <?php echo ucfirst($sancion['estado']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center" style="color: var(--color-exito); font-weight: 600;">
                            ✓ No hay sanciones registradas
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Tab 5: Reconocimientos -->
        <div class="tab-content" id="reconocimientos">
            <div class="card">
                <div class="card-header">
                    Reconocimientos y Anotaciones Positivas
                    <?php if (tieneRol('oficial')): ?>
                        <button class="btn btn-sm btn-success no-print" onclick="abrirModalReconocimiento()">
                            + Agregar Reconocimiento
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (count($reconocimientos) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Fecha</th>
                                        <th>Motivo</th>
                                        <th>Otorgado Por</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reconocimientos as $rec): ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                $tipos = [
                                                    'condecoración' => 'Condecoración',
                                                    'mencion_honrosa' => 'Mención Honrosa',
                                                    'nota_merito' => 'Nota de Mérito',
                                                    'acto_valor' => 'Acto de Valor',
                                                    'servicio_destacado' => 'Servicio Destacado',
                                                    'otro' => 'Otro'
                                                ];
                                                echo $tipos[$rec['tipo']];
                                                ?>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($rec['fecha'])); ?></td>
                                            <td><?php echo $rec['motivo']; ?></td>
                                            <td><?php echo $rec['otorgado_por'] ?? '-'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center" style="color: var(--color-texto-claro);">
                            No hay reconocimientos registrados
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Tab 6: Servicios -->
        <div class="tab-content" id="servicios">
            <div class="card">
                <div class="card-header">Estadísticas de Servicios</div>
                <div class="card-body">
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-value"><?php echo $stats_servicios['total_servicios'] ?? 0; ?></div>
                            <div class="stat-label">Total Servicios</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value"><?php echo number_format($stats_servicios['total_horas'] ?? 0, 1); ?></div>
                            <div class="stat-label">Horas de Servicio</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value" style="font-size: 1.5rem;">
                                <?php 
                                echo $stats_servicios['ultimo_servicio'] 
                                    ? date('d/m/Y', strtotime($stats_servicios['ultimo_servicio'])) 
                                    : 'N/A'; 
                                ?>
                            </div>
                            <div class="stat-label">Último Servicio</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-2">
                <div class="card-header">
                    Historial de Servicios (Últimos 50)
                    <?php if (tieneRol('oficial')): ?>
                        <button class="btn btn-sm btn-primary no-print" onclick="abrirModalServicio()">
                            + Registrar Servicio
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (count($servicios) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Fecha/Hora</th>
                                        <th>Tipo Emergencia</th>
                                        <th>Dirección</th>
                                        <th>Rol</th>
                                        <th>Horas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($servicios as $servicio): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($servicio['fecha_servicio'])); ?></td>
                                            <td><?php echo $servicio['tipo_emergencia']; ?></td>
                                            <td><?php echo $servicio['direccion'] ?? '-'; ?></td>
                                            <td><?php echo $servicio['rol_desempenado'] ?? '-'; ?></td>
                                            <td><?php echo $servicio['horas_servicio'] ?? '-'; ?></td>
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
        
        <!-- Tab 7: Equipamiento -->
        <div class="tab-content" id="equipamiento">
            <div class="card">
                <div class="card-header">
                    Equipamiento Asignado
                    <?php if (tieneRol('oficial')): ?>
                        <button class="btn btn-sm btn-primary no-print" onclick="abrirModalEquipamiento()">
                            + Asignar Equipo
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (count($equipamiento) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Tipo Equipo</th>
                                        <th>Marca/Modelo</th>
                                        <th>N° Serie</th>
                                        <th>Fecha Asignación</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($equipamiento as $equipo): ?>
                                        <tr>
                                            <td><strong><?php echo $equipo['tipo_equipo']; ?></strong></td>
                                            <td><?php echo ($equipo['marca'] ?? '') . ' ' . ($equipo['modelo'] ?? ''); ?></td>
                                            <td><?php echo $equipo['numero_serie'] ?? '-'; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($equipo['fecha_asignacion'])); ?></td>
                                            <td>
                                                <?php
                                                $badge_estado = [
                                                    'bueno' => 'badge-success',
                                                    'regular' => 'badge-warning',
                                                    'malo' => 'badge-danger',
                                                    'dado_baja' => 'badge-secondary'
                                                ];
                                                ?>
                                                <span class="badge <?php echo $badge_estado[$equipo['estado']]; ?>">
                                                    <?php echo ucfirst($equipo['estado']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center" style="color: var(--color-texto-claro);">
                            No hay equipamiento asignado
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Tab 8: Evaluaciones -->
        <div class="tab-content" id="evaluaciones">
            <div class="card">
                <div class="card-header">
                    Evaluaciones de Desempeño
                    <?php if (tieneRol('oficial')): ?>
                        <button class="btn btn-sm btn-primary no-print" onclick="abrirModalEvaluacion()">
                            + Nueva Evaluación
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (count($evaluaciones) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Período</th>
                                        <th>Fecha</th>
                                        <th>Evaluador</th>
                                        <th>Puntaje Total</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($evaluaciones as $eval): ?>
                                        <tr>
                                            <td><?php echo $eval['periodo']; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($eval['fecha_evaluacion'])); ?></td>
                                            <td><?php echo $eval['evaluador']; ?></td>
                                            <td><strong><?php echo $eval['puntaje_total']; ?></strong></td>
                                            <td>
                                                <button class="btn btn-sm btn-info" onclick="verEvaluacion(<?php echo $eval['id']; ?>)">
                                                    Ver Detalle
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center" style="color: var(--color-texto-claro);">
                            No hay evaluaciones registradas
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
    </div>
    
    <script src="js/main.js"></script>
    <script>
        // Manejo de tabs
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Remover active de todos los tabs
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Activar tab seleccionado
                this.classList.add('active');
                document.getElementById(this.dataset.tab).classList.add('active');
            });
        });
        
        // Funciones para abrir modales (a implementar según necesidad)
        function abrirModalGrado() {
            alert('Funcionalidad de agregar grado en desarrollo');
        }
        
        function abrirModalCargo() {
            alert('Funcionalidad de agregar cargo en desarrollo');
        }
        
        function abrirModalCurso() {
            alert('Funcionalidad de agregar curso en desarrollo');
        }
        
        function abrirModalCertificacion() {
            alert('Funcionalidad de agregar certificación en desarrollo');
        }
        
        function abrirModalSancion() {
            alert('Funcionalidad de agregar sanción en desarrollo');
        }
        
        function abrirModalReconocimiento() {
            alert('Funcionalidad de agregar reconocimiento en desarrollo');
        }
        
        function abrirModalServicio() {
            alert('Funcionalidad de agregar servicio en desarrollo');
        }
        
        function abrirModalEquipamiento() {
            alert('Funcionalidad de agregar equipamiento en desarrollo');
        }
        
        function abrirModalEvaluacion() {
            alert('Funcionalidad de agregar evaluación en desarrollo');
        }
        
        function verEvaluacion(id) {
            alert('Ver detalle de evaluación #' + id);
        }
    </script>
</body>
</html>
