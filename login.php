<?php
/**
 * GOMISHOT 2.0 - Login con Base de Datos
 */

session_start();
require 'bd.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        // Buscar usuario en la base de datos
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = :u AND activo = TRUE LIMIT 1");
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // LOGIN EXITOSO
            session_regenerate_id(true);
            
            $_SESSION['usuario'] = $user['username'];
            $_SESSION['id_usuario'] = $user['id_usuario'];
            $_SESSION['rol'] = $user['rol'];
            $_SESSION['login_time'] = time();
            $_SESSION['ultimo_acceso'] = time();

            // Registrar login exitoso en logs
            $stmt_log = $pdo->prepare("INSERT INTO logs_sistema (id_usuario, evento, detalle) 
                                       VALUES (:id_usuario, 'login_exitoso', :detalle)");
            $stmt_log->execute([
                ':id_usuario' => $user['id_usuario'],
                ':detalle' => "Login correcto desde IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'desconocida')
            ]);

            header("Location: inventario.php");
            exit();
            
        } else {
            // LOGIN FALLIDO
            // Registrar intento fallido
            $stmt_log = $pdo->prepare("INSERT INTO logs_sistema (evento, detalle) 
                                       VALUES ('login_fallido', :detalle)");
            $stmt_log->execute([
                ':detalle' => "Intento fallido - Usuario: $username - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'desconocida')
            ]);

            header("Location: ingresar.html?error=credenciales_incorrectas");
            exit();
        }
        
    } catch (PDOException $e) {
        // Error de base de datos
        error_log("Error en login: " . $e->getMessage());
        header("Location: ingresar.html?error=sistema");
        exit();
    }
} else {
    // Método no permitido
    header("Location: ingresar.html");
    exit();
}
?>
