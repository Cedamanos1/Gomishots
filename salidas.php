<?php
/**
 * GOMISHOT 2.0 - Salidas de Productos con Base de Datos
 */

session_start();
require 'bd.php';

if (!isset($_SESSION['usuario']) || !isset($_SESSION['id_usuario'])) {
    header("Location: ingresar.html");
    exit();
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Función para obtener stock de un producto
function obtenerStock($pdo, $codigo) {
    try {
        // Sumar ingresos
        $stmt_ing = $pdo->prepare("SELECT COALESCE(SUM(cantidad), 0) as total 
                                    FROM ingresos WHERE codigo = :codigo");
        $stmt_ing->execute([':codigo' => $codigo]);
        $ingresos = $stmt_ing->fetch(PDO::FETCH_ASSOC)['total'];

        // Sumar salidas
        $stmt_sal = $pdo->prepare("SELECT COALESCE(SUM(cantidad), 0) as total 
                                    FROM salidas WHERE codigo = :codigo");
        $stmt_sal->execute([':codigo' => $codigo]);
        $salidas = $stmt_sal->fetch(PDO::FETCH_ASSOC)['total'];

        return $ingresos - $salidas;
        
    } catch (PDOException $e) {
        error_log("Error al obtener stock: " . $e->getMessage());
        return 0;
    }
}

// Obtener lista de productos disponibles
$productos = [];
try {
    $stmt = $pdo->query("SELECT DISTINCT i.codigo, i.nombre 
                         FROM ingresos i 
                         ORDER BY i.nombre");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $codigo = $row['codigo'];
        $productos[$codigo] = [
            'nombre' => $row['nombre'],
            'stock' => obtenerStock($pdo, $codigo)
        ];
    }
} catch (PDOException $e) {
    error_log("Error al cargar productos: " . $e->getMessage());
}

// Registrar salida
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["registrar"])) {
    // Validar CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Token inválido";
        header("Location: salidas.php");
        exit();
    }

    $codigo = strtoupper(trim($_POST['codigo'] ?? ''));
    $cantidad = $_POST['cantidad'] ?? '';
    $fecha = trim($_POST['fecha'] ?? '');
    
    if (!isset($productos[$codigo])) {
        $_SESSION['error'] = "Código inválido";
        header("Location: salidas.php");
        exit();
    }
    
    $cantidad = (int)$cantidad;
    if ($cantidad <= 0) {
        $_SESSION['error'] = "Cantidad inválida";
        header("Location: salidas.php");
        exit();
    }
    
    $stock_actual = obtenerStock($pdo, $codigo);
    
    if ($cantidad > $stock_actual) {
        $_SESSION['error'] = "Stock insuficiente. Disponible: $stock_actual";
        header("Location: salidas.php");
        exit();
    }
    
    try {
        $nombre = $productos[$codigo]['nombre'];
        
        // Insertar salida
        $stmt = $pdo->prepare("INSERT INTO salidas (codigo, nombre, cantidad, fecha, id_usuario) 
                               VALUES (:codigo, :nombre, :cantidad, :fecha, :id_usuario)");
        $stmt->execute([
            ':codigo' => $codigo,
            ':nombre' => $nombre,
            ':cantidad' => $cantidad,
            ':fecha' => $fecha,
            ':id_usuario' => $_SESSION['id_usuario']
        ]);

        // Registrar en logs
        $stmt_log = $pdo->prepare("INSERT INTO logs_sistema (id_usuario, evento, detalle) 
                                   VALUES (:id_usuario, 'salida_registrada', :detalle)");
        $stmt_log->execute([
            ':id_usuario' => $_SESSION['id_usuario'],
            ':detalle' => "Salida: $nombre ($codigo) - $cantidad unidades"
        ]);

        $stock_restante = $stock_actual - $cantidad;
        $_SESSION['mensaje'] = "Salida registrada. Stock restante: $stock_restante";
        
    } catch (PDOException $e) {
        error_log("Error al registrar salida: " . $e->getMessage());
        $_SESSION['error'] = "Error al registrar la salida";
    }
    
    header("Location: salidas.php");
    exit();
}

// Descargar reporte
if (isset($_GET['descargar'])) {
    try {
        $stmt = $pdo->query("SELECT codigo, nombre, cantidad, fecha 
                             FROM salidas 
                             ORDER BY fecha DESC");
        $salidas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Reporte_Salidas_' . date('Y-m-d') . '.csv"');
        echo "\xEF\xBB\xBF"; // BOM para Excel

        // Escribir encabezados
        echo "Codigo,Nombre,Cantidad,Fecha\n";
        
        // Escribir datos
        foreach ($salidas as $row) {
            echo implode(',', $row) . "\n";
        }
        exit();
        
    } catch (PDOException $e) {
        error_log("Error al generar reporte: " . $e->getMessage());
        $_SESSION['error'] = "Error al generar el reporte";
        header("Location: salidas.php");
        exit();
    }
}

// Obtener salidas registradas
$salidas = [];
try {
    $stmt = $pdo->query("SELECT codigo, nombre, cantidad, fecha 
                         FROM salidas 
                         ORDER BY fecha_registro DESC 
                         LIMIT 100");
    $salidas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al cargar salidas: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salidas de Producto</title>
    <link rel="stylesheet" href="styles/salida.css">
</head>
<body>
    <div class="top-bar">
        <div class="logo-text">Gomi<span class="highlight">Shots</span></div>
        <div>
            <a href="inventario.php" class="volver-link">Volver al Panel</a>
            <a href="logout.php" class="logout-link">Cerrar sesión</a>
        </div>
    </div>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <div style="background:#004400; color:#00ff00; padding:15px; margin:20px; border-radius:5px; text-align:center;">
            ✅ <?php echo htmlspecialchars($_SESSION['mensaje'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['mensaje']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div style="background:#440000; color:#ff6666; padding:15px; margin:20px; border-radius:5px; text-align:center;">
            ❌ <?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="panel">
        <div class="formulario">
            <h2>Registrar Salida</h2>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <select name="codigo" id="selectProducto" required>
                    <option value="">Seleccione el producto</option>
                    <?php foreach ($productos as $codigo => $info): ?>
                        <option value="<?php echo htmlspecialchars($codigo); ?>" data-stock="<?php echo $info['stock']; ?>">
                            <?php echo htmlspecialchars($info['nombre']); ?> (<?php echo htmlspecialchars($codigo); ?>) - Stock: <?php echo $info['stock']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <div id="stockInfo" style="display:none; background:#1a1a1a; padding:10px; margin:10px 0; border-left:3px solid #ffa500;">
                    <strong>Stock disponible:</strong> <span id="stockDisponible">0</span> unidades
                </div>
                
                <input type="number" name="cantidad" id="cantidadSalida" placeholder="Cantidad" min="1" required>
                
                <input type="date" name="fecha" max="<?php echo date('Y-m-d'); ?>" required>
                
                <div class="boton-group">
                    <button type="submit" name="registrar" class="boton">Registrar</button>
                    <a class="boton" href="?descargar=1">Generar Reporte</a>
                </div>
            </form>

            <h2>Salidas Registradas</h2>
            <table>
                <thead>
                    <tr><th>Código</th><th>Producto</th><th>Cantidad</th><th>Fecha</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($salidas)): ?>
                        <tr><td colspan="4">No hay salidas registradas</td></tr>
                    <?php else: ?>
                        <?php foreach ($salidas as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['codigo']); ?></td>
                                <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($row['cantidad']); ?></td>
                                <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById('selectProducto').addEventListener('change', function() {
            const stockInfo = document.getElementById('stockInfo');
            const stockDisponible = document.getElementById('stockDisponible');
            const cantidadInput = document.getElementById('cantidadSalida');
            
            if (this.value) {
                const stock = this.options[this.selectedIndex].dataset.stock;
                stockDisponible.textContent = stock;
                stockInfo.style.display = 'block';
                cantidadInput.max = stock;
            } else {
                stockInfo.style.display = 'none';
                cantidadInput.removeAttribute('max');
            }
        });
        
        document.getElementById('cantidadSalida').addEventListener('input', function() {
            const max = parseInt(this.max);
            const valor = parseInt(this.value);
            
            if (max && valor > max) {
                this.value = max;
                alert('La cantidad no puede exceder el stock disponible (' + max + ')');
            }
        });
    </script>
</body>
</html>
