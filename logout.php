<?php
/**
 * GOMISHOT 2.0 - Cerrar Sesión con Base de Datos
 */

session_start();
require 'bd.php';

// Registrar logout en la base de datos
if (isset($_SESSION['id_usuario'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO logs_sistema (id_usuario, evento, detalle) 
                               VALUES (:id_usuario, 'logout', :detalle)");
        $stmt->execute([
            ':id_usuario' => $_SESSION['id_usuario'],
            ':detalle' => "Cierre de sesión - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'desconocida')
        ]);
    } catch (PDOException $e) {
        error_log("Error al registrar logout: " . $e->getMessage());
    }
}

// Destruir sesión
session_unset();
session_destroy();

// Destruir cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirigir
header("Location: ingresar.html");
exit();
?>
