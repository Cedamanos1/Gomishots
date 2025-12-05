<?php
/**
 * GOMISHOT 2.0 - Procesar Ingreso con Base de Datos
 */

session_start();
require 'bd.php';

if (!isset($_SESSION['usuario']) || !isset($_SESSION['id_usuario'])) {
    header("Location: ingresar.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['error'] = "Método inválido";
    header("Location: ingreso.php");
    exit();
}

// Validar CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Token inválido";
    header("Location: ingreso.php");
    exit();
}

$codigo = strtoupper(trim($_POST['codigo'] ?? ''));
$nombre = trim($_POST['nombre'] ?? '');
$cantidad = $_POST['cantidad'] ?? '';
$fecha = trim($_POST['fecha'] ?? '');

$errores = [];

// Validaciones
if (empty($codigo) || !preg_match('/^[A-Z0-9]{3,10}$/', $codigo)) {
    $errores[] = "Código inválido";
}

$productos_permitidos = ['Jagger', 'Whisky Sour', 'Algarrobina', 'Apple Drunk', 'Cuba Libre'];
if (empty($nombre) || !in_array($nombre, $productos_permitidos)) {
    $errores[] = "Producto inválido";
}

if (!is_numeric($cantidad) || (int)$cantidad <= 0 || (int)$cantidad > 10000) {
    $errores[] = "Cantidad debe ser positiva (1-10000)";
}

$fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha);
if (!$fecha_obj || $fecha_obj->format('Y-m-d') !== $fecha) {
    $errores[] = "Fecha inválida";
}

if (!empty($errores)) {
    $_SESSION['error'] = implode(". ", $errores);
    header("Location: ingreso.php");
    exit();
}

$cantidad = abs((int)$cantidad);

try {
    // Insertar en la tabla de ingresos
    $stmt = $pdo->prepare("INSERT INTO ingresos (codigo, nombre, cantidad, fecha, id_usuario) 
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
                               VALUES (:id_usuario, 'ingreso_registrado', :detalle)");
    $stmt_log->execute([
        ':id_usuario' => $_SESSION['id_usuario'],
        ':detalle' => "Ingreso: $nombre ($codigo) - $cantidad unidades"
    ]);

    $_SESSION['mensaje'] = "Producto registrado: $nombre - $cantidad unidades";
    
} catch (PDOException $e) {
    error_log("Error al registrar ingreso: " . $e->getMessage());
    $_SESSION['error'] = "Error al guardar el ingreso";
}

header("Location: ingreso.php");
exit();
?>
