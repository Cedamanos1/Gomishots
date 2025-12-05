<?php
/**
 * GOMISHOT 2.0 - Exportar Excel con Base de Datos
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

// Configurar headers para Excel
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="Reporte_Stock_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// BOM para UTF-8
echo "\xEF\xBB\xBF";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid black; padding: 8px; text-align: center; }
        th { background-color: #ffa500; font-weight: bold; }
        .header { background-color: #000; color: #ffa500; padding: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>GOMISHOTS - REPORTE DE STOCK</h1>
        <p>Generado: <?php echo date('d/m/Y H:i:s'); ?></p>
        <p>Usuario: <?php echo htmlspecialchars($_SESSION['usuario']); ?></p>
    </div>

    <h2>Resumen General</h2>
    <table>
        <tr>
            <th>Total Ingresos</th>
            <th>Total Salidas</th>
            <th>Stock Disponible</th>
            <th>Productos</th>
        </tr>
        <tr>
            <td><?php echo number_format(array_sum(array_column($stock, 'ingresos'))); ?></td>
            <td><?php echo number_format(array_sum(array_column($stock, 'salidas'))); ?></td>
            <td><?php echo number_format(array_sum(array_column($stock, 'disponible'))); ?></td>
            <td><?php echo count($stock); ?></td>
        </tr>
    </table>

    <h2>Detalle por Producto</h2>
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
                    $estado = 'SIN STOCK';
                } elseif ($datos['disponible'] <= 5) {
                    $estado = 'STOCK BAJO';
                } else {
                    $estado = 'OK';
                }
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($nombre); ?></strong></td>
                    <td><?php echo number_format($datos['ingresos']); ?></td>
                    <td><?php echo number_format($datos['salidas']); ?></td>
                    <td><?php echo number_format($datos['disponible']); ?></td>
                    <td><?php echo $estado; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p style="margin-top: 30px; text-align: center; color: #666;">
        <small>© <?php echo date('Y'); ?> GomiShots - Todos los derechos reservados</small>
    </p>
</body>
</html>
