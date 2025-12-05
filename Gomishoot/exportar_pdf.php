<?php
/**
 * GOMISHOT 2.0 - Exportar PDF con Base de Datos
 */

session_start();
require 'bd.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ingresar.html");
    exit();
}

// Función para calcular stock
function calcularStock($pdo) {
    $stock = [];
    
    try {
        $stmt = $pdo->query("SELECT DISTINCT codigo, nombre FROM ingresos");
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($productos as $producto) {
            $codigo = $producto['codigo'];
            $nombre = $producto['nombre'];
            
            $stmt_ing = $pdo->prepare("SELECT COALESCE(SUM(cantidad), 0) as total FROM ingresos WHERE codigo = :codigo");
            $stmt_ing->execute([':codigo' => $codigo]);
            $ingresos = $stmt_ing->fetch(PDO::FETCH_ASSOC)['total'];
            
            $stmt_sal = $pdo->prepare("SELECT COALESCE(SUM(cantidad), 0) as total FROM salidas WHERE codigo = :codigo");
            $stmt_sal->execute([':codigo' => $codigo]);
            $salidas = $stmt_sal->fetch(PDO::FETCH_ASSOC)['total'];
            
            $stock[$nombre] = [
                'ingresos' => $ingresos,
                'salidas' => $salidas,
                'disponible' => $ingresos - $salidas
            ];
        }
    } catch (PDOException $e) {
        error_log("Error al calcular stock: " . $e->getMessage());
    }
    
    return $stock;
}

$stock = calcularStock($pdo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte PDF - GomiShots</title>
    <style>
        @media print {
            .no-print { display: none; }
        }
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #000;
            color: #ffa500;
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .header h1 { margin: 0; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 30px;
        }
        .stat-box {
            border: 2px solid #ffa500;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
        }
        .stat-box h3 {
            color: #ffa500;
            font-size: 0.9rem;
            margin: 0 0 10px 0;
        }
        .stat-box .number {
            font-size: 2rem;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #333;
            padding: 10px;
            text-align: center;
        }
        th {
            background: #ffa500;
            color: #000;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        .alert {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10px;
            margin: 10px 0;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 0.9rem;
        }
        .btn-print {
            background: #ffa500;
            color: #000;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin: 10px;
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" class="btn-print">🖨️ Imprimir / Guardar PDF</button>
        <button onclick="window.close()" class="btn-print" style="background:#666; color:#fff;">❌ Cerrar</button>
    </div>

    <div class="header">
        <h1>GOMISHOTS</h1>
        <h2>REPORTE DE INVENTARIO</h2>
        <p>Generado: <?php echo date('d/m/Y H:i:s'); ?></p>
        <p>Usuario: <?php echo htmlspecialchars($_SESSION['usuario']); ?></p>
    </div>

    <h2 style="color: #ffa500;">📊 Resumen General</h2>
    <div class="stats-grid">
        <div class="stat-box">
            <h3>Total Ingresos</h3>
            <div class="number"><?php echo number_format(array_sum(array_column($stock, 'ingresos'))); ?></div>
        </div>
        <div class="stat-box">
            <h3>Total Salidas</h3>
            <div class="number"><?php echo number_format(array_sum(array_column($stock, 'salidas'))); ?></div>
        </div>
        <div class="stat-box">
            <h3>Stock Disponible</h3>
            <div class="number" style="color: #00aa00;"><?php echo number_format(array_sum(array_column($stock, 'disponible'))); ?></div>
        </div>
        <div class="stat-box">
            <h3>Productos</h3>
            <div class="number"><?php echo count($stock); ?></div>
        </div>
    </div>

    <?php
    $alertas = [];
    foreach ($stock as $nombre => $datos) {
        if ($datos['disponible'] <= 5 && $datos['disponible'] > 0) {
            $alertas[] = "⚠️ $nombre: Stock bajo ({$datos['disponible']} unidades)";
        } elseif ($datos['disponible'] <= 0) {
            $alertas[] = "❌ $nombre: Sin stock disponible";
        }
    }
    ?>

    <?php if (!empty($alertas)): ?>
        <h2 style="color: #ffa500;">⚠️ Alertas</h2>
        <?php foreach ($alertas as $alerta): ?>
            <div class="alert"><?php echo $alerta; ?></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <h2 style="color: #ffa500;">📦 Detalle por Producto</h2>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Total Ingresos</th>
                <th>Total Salidas</th>
                <th>Stock Disponible</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stock as $nombre => $datos): ?>
                <?php
                if ($datos['disponible'] <= 0) {
                    $estado = '❌ Sin Stock';
                    $color = '#ff0000';
                } elseif ($datos['disponible'] <= 5) {
                    $estado = '⚠️ Stock Bajo';
                    $color = '#ffcc00';
                } else {
                    $estado = '✅ OK';
                    $color = '#00aa00';
                }
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($nombre); ?></strong></td>
                    <td><?php echo number_format($datos['ingresos']); ?></td>
                    <td><?php echo number_format($datos['salidas']); ?></td>
                    <td style="color: <?php echo $color; ?>; font-weight: bold;">
                        <?php echo number_format($datos['disponible']); ?>
                    </td>
                    <td><?php echo $estado; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>© <?php echo date('Y'); ?> GomiShots - Todos los derechos reservados</p>
        <p><small>Este documento es un reporte generado automáticamente por el sistema</small></p>
    </div>

    <script>
        // Auto-imprimir al cargar (opcional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
