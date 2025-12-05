<?php
/**
 * GOMISHOT 2.0 - Script para Crear Usuarios
 * 
 * Ejecutar: php crear_usuario.php
 */

require 'bd.php';

echo "=== GOMISHOTS - CREAR NUEVO USUARIO ===\n\n";

// Solicitar datos del usuario
echo "Ingrese el nombre de usuario: ";
$username = trim(fgets(STDIN));

if (empty($username) || strlen($username) < 3) {
    die("❌ El nombre de usuario debe tener al menos 3 caracteres\n");
}

echo "Ingrese la contraseña: ";
$password = trim(fgets(STDIN));

if (empty($password) || strlen($password) < 6) {
    die("❌ La contraseña debe tener al menos 6 caracteres\n");
}

echo "Confirme la contraseña: ";
$password_confirm = trim(fgets(STDIN));

if ($password !== $password_confirm) {
    die("❌ Las contraseñas no coinciden\n");
}

echo "Seleccione el rol:\n";
echo "1. Administrador\n";
echo "2. Operador\n";
echo "Opción (1 o 2): ";
$rol_opcion = trim(fgets(STDIN));

$rol = ($rol_opcion == '1') ? 'administrador' : 'operador';

try {
    // Verificar si el usuario ya existe
    $stmt_check = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE username = :username");
    $stmt_check->execute([':username' => $username]);
    
    if ($stmt_check->fetch()) {
        die("❌ El usuario '$username' ya existe\n");
    }
    
    // Crear hash de la contraseña
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    
    // Insertar usuario
    $stmt = $pdo->prepare("INSERT INTO usuarios (username, password_hash, rol, activo) 
                           VALUES (:username, :password_hash, :rol, TRUE)");
    $stmt->execute([
        ':username' => $username,
        ':password_hash' => $password_hash,
        ':rol' => $rol
    ]);
    
    $id_usuario = $pdo->lastInsertId();
    
    // Registrar en logs
    $stmt_log = $pdo->prepare("INSERT INTO logs_sistema (id_usuario, evento, detalle) 
                               VALUES (:id_usuario, 'usuario_creado', :detalle)");
    $stmt_log->execute([
        ':id_usuario' => $id_usuario,
        ':detalle' => "Usuario '$username' creado con rol: $rol"
    ]);
    
    echo "\n✅ Usuario creado exitosamente!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "ID: $id_usuario\n";
    echo "Usuario: $username\n";
    echo "Rol: $rol\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "\nAhora puede iniciar sesión con estas credenciales.\n\n";
    
} catch (PDOException $e) {
    die("❌ Error al crear usuario: " . $e->getMessage() . "\n");
}
?>
