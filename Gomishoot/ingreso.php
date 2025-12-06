<?php
/**
 * GOMISHOT 2.0 - Ingreso de Productos con Base de Datos
 */

session_start();
require 'bd.php';

if (!isset($_SESSION['usuario']) || !isset($_SESSION['id_usuario'])) {
    header("Location: ingresar.html");
    exit();
}

// Generar token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Leer ingresos desde la base de datos
$ingresos = [];
$codigos_unicos = [];

try {
    // Obtener todos los ingresos
    $stmt = $pdo->query("SELECT codigo, nombre, cantidad, fecha 
                         FROM ingresos 
                         ORDER BY fecha_registro DESC");
    $ingresos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener códigos únicos
    $stmt_codigos = $pdo->query("SELECT DISTINCT codigo FROM ingresos ORDER BY codigo");
    $codigos_unicos = $stmt_codigos->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    error_log("Error al cargar ingresos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingreso de Productos</title>
    <link rel="stylesheet" href="styles/ingreso.css">
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
            <h2>Registrar Producto</h2>
            <form method="POST" action="procesar_ingreso.php" id="formIngreso">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <input type="text" name="codigo" placeholder="Código (ej: JAG001)" 
                       pattern="[A-Z0-9]{3,10}" title="3-10 caracteres: A-Z y 0-9" 
                       maxlength="10" required>
                
                <select name="nombre" required>
                    <option value="">Seleccione el producto</option>
                    <option value="Jagger">Jagger</option>
                    <option value="Whisky Sour">Whisky Sour</option>
                    <option value="Algarrobina">Algarrobina</option>
                    <option value="Apple Drunk">Apple Drunk</option>
                    <option value="Cuba Libre">Cuba Libre</option>
                </select>
                
                <input type="number" name="cantidad" id="cantidadInput"
                       placeholder="Cantidad (solo positivos)" min="1" max="10000" step="1" required>
                
                <input type="date" name="fecha" max="<?php echo date('Y-m-d'); ?>" required>
                
                <div class="boton-group">
                    <button class="boton" type="submit">Registrar</button>
                    <a href="generar_reporte_ingresos.php" class="boton">Generar Reporte</a>
                </div>
            </form>

            <h2>Modificar Ingreso</h2>
            <form method="POST" action="modificar_i.php" id="formModificar">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <select name="codigo" required>
                    <option value="">Seleccione el código</option>
                    <?php foreach ($codigos_unicos as $codigo): ?>
                        <option value="<?php echo htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <input type="number" name="cantidad_modificada" id="cantidadMod"
                       placeholder="Nueva cantidad" min="1" max="10000" required>
                
                <input type="text" name="justificacion" placeholder="Justificación" maxlength="200" required>
                
                <button class="boton" type="submit">Modificar</button>
            </form>
        </div>

        <div class="tabla">
            <h3>Buscar Producto</h3>
            <input type="text" id="buscar" class="buscar" placeholder="Buscar...">
            
            <table id="tablaProductos">
                <thead>
                    <tr><th>Código</th><th>Nombre</th><th>Cantidad</th><th>Fecha</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($ingresos)): ?>
                        <tr><td colspan="4">No hay ingresos</td></tr>
                    <?php else: ?>
                        <?php foreach ($ingresos as $ing): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ing['codigo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($ing['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($ing['cantidad'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($ing['fecha'], ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Validación de cantidad
        function validarCantidad(input) {
            const valor = parseFloat(input.value);
            if (valor <= 0 || isNaN(valor)) {
                input.value = '';
                alert('❌ La cantidad debe ser mayor a 0');
                return false;
            }
            if (valor % 1 !== 0) input.value = Math.floor(valor);
            if (valor > 10000) input.value = 10000;
            return true;
        }
        
        // Prevenir números negativos
        function prevenirNegativos(e) {
            if (e.key === '-' || e.key === 'e' || e.key === '+' || e.key === '.') {
                e.preventDefault();
            }
        }
        
        // Aplicar validaciones
        ['cantidadInput', 'cantidadMod'].forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('keydown', prevenirNegativos);
                input.addEventListener('change', function() { validarCantidad(this); });
                input.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            }
        });
        
        // Búsqueda en tiempo real
        document.getElementById("buscar").addEventListener("keyup", function() {
            const valor = this.value.toLowerCase();
            document.querySelectorAll("#tablaProductos tbody tr").forEach(fila => {
                fila.style.display = fila.textContent.toLowerCase().includes(valor) ? "" : "none";
            });
        });
    </script>
</body>
</html>
