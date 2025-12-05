<?php
/**
 * GOMISHOT 2.0 - Modificar Ingreso con Base de Datos
 */

session_start();
require 'bd.php';

if (!isset($_SESSION['usuario']) || !isset($_SESSION['id_usuario'])) {
    header("Location: ingresar.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ingreso.php");
    exit();
}

// Validar CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Token inválido";
    header("Location: ingreso.php");
    exit();
}

$codigo = strtoupper(trim($_POST["codigo"] ?? ''));
$nueva_cantidad = $_POST["cantidad_modificada"] ?? '';
$justificacion = trim($_POST["justificacion"] ?? '');

// Validaciones
if (!is_numeric($nueva_cantidad) || (int)$nueva_cantidad <= 0 || (int)$nueva_cantidad > 10000) {
    $_SESSION['error'] = "Cantidad inválida";
    header("Location: ingreso.php");
    exit();
}

if (empty($justificacion) || strlen($justificacion) < 5) {
    $_SESSION['error'] = "La justificación debe tener al menos 5 caracteres";
    header("Location: ingreso.php");
    exit();
}

$nueva_cantidad = abs((int)$nueva_cantidad);

try {
    // Obtener el ingreso más reciente con ese código
    $stmt = $pdo->prepare("SELECT id_ingreso, cantidad FROM ingresos 
                           WHERE codigo = :codigo 
                           ORDER BY fecha_registro DESC 
                           LIMIT 1");
    $stmt->execute([':codigo' => $codigo]);
    $ingreso = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ingreso) {
        $_SESSION['error'] = "Código no encontrado";
        header("Location: ingreso.php");
        exit();
    }

    $cantidad_anterior = $ingreso['cantidad'];
    $id_ingreso = $ingreso['id_ingreso'];

    // Actualizar la cantidad en el ingreso
    $stmt_update = $pdo->prepare("UPDATE ingresos 
                                   SET cantidad = :cantidad 
                                   WHERE id_ingreso = :id_ingreso");
    $stmt_update->execute([
        ':cantidad' => $nueva_cantidad,
        ':id_ingreso' => $id_ingreso
    ]);

    // Registrar la modificación en la tabla de auditoría
    $stmt_audit = $pdo->prepare("INSERT INTO modificaciones 
                                  (codigo, cantidad_anterior, cantidad_nueva, justificacion, id_usuario) 
                                  VALUES (:codigo, :cant_ant, :cant_nueva, :justif, :id_usuario)");
    $stmt_audit->execute([
        ':codigo' => $codigo,
        ':cant_ant' => $cantidad_anterior,
        ':cant_nueva' => $nueva_cantidad,
        ':justif' => $justificacion,
        ':id_usuario' => $_SESSION['id_usuario']
    ]);

    // Registrar en logs
    $stmt_log = $pdo->prepare("INSERT INTO logs_sistema (id_usuario, evento, detalle) 
                               VALUES (:id_usuario, 'modificacion_ingreso', :detalle)");
    $stmt_log->execute([
        ':id_usuario' => $_SESSION['id_usuario'],
        ':detalle' => "Código: $codigo - De $cantidad_anterior a $nueva_cantidad - Justificación: $justificacion"
    ]);

    $_SESSION['mensaje'] = "Ingreso modificado correctamente. Justificación: $justificacion";
    
} catch (PDOException $e) {
    error_log("Error al modificar ingreso: " . $e->getMessage());
    $_SESSION['error'] = "Error al modificar el ingreso";
}

header("Location: ingreso.php");
exit();
?>
