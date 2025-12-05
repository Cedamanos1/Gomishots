<?php
/**
 * GOMISHOT 2.0 - Script de Migración de CSV a Base de Datos
 * 
 * Este script migra los datos existentes en archivos CSV a la base de datos SQL
 * Ejecutar una sola vez después de configurar la base de datos
 */

require 'bd.php';

echo "=== GOMISHOTS - MIGRACIÓN DE DATOS CSV A BASE DE DATOS ===\n\n";

// Directorios
$data_dir = __DIR__ . '/data/';
$ingresos_file = $data_dir . 'ingresos.csv';
$salidas_file = $data_dir . 'salidas.csv';

$errores = [];
$exitos = [];

// MIGRAR INGRESOS
echo "1. Migrando INGRESOS...\n";
if (file_exists($ingresos_file)) {
    try {
        $fp = fopen($ingresos_file, 'r');
        $contador = 0;
        
        while (($data = fgetcsv($fp)) !== false) {
            if (count($data) >= 4) {
                $codigo = $data[0];
                $nombre = $data[1];
                $cantidad = (int)$data[2];
                $fecha = $data[3];
                
                // Insertar en la base de datos
                $stmt = $pdo->prepare("INSERT INTO ingresos (codigo, nombre, cantidad, fecha) 
                                       VALUES (:codigo, :nombre, :cantidad, :fecha)");
                $stmt->execute([
                    ':codigo' => $codigo,
                    ':nombre' => $nombre,
                    ':cantidad' => $cantidad,
                    ':fecha' => $fecha
                ]);
                
                $contador++;
            }
        }
        
        fclose($fp);
        $exitos[] = "✅ Ingresos: $contador registros migrados";
        echo "   ✅ $contador registros de ingresos migrados\n";
        
    } catch (Exception $e) {
        $errores[] = "❌ Error al migrar ingresos: " . $e->getMessage();
        echo "   ❌ Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ⚠️  Archivo ingresos.csv no encontrado\n";
}

// MIGRAR SALIDAS
echo "\n2. Migrando SALIDAS...\n";
if (file_exists($salidas_file)) {
    try {
        $fp = fopen($salidas_file, 'r');
        $contador = 0;
        
        while (($data = fgetcsv($fp)) !== false) {
            if (count($data) >= 4) {
                $codigo = $data[0];
                $nombre = $data[1];
                $cantidad = (int)$data[2];
                $fecha = $data[3];
                
                // Insertar en la base de datos
                $stmt = $pdo->prepare("INSERT INTO salidas (codigo, nombre, cantidad, fecha) 
                                       VALUES (:codigo, :nombre, :cantidad, :fecha)");
                $stmt->execute([
                    ':codigo' => $codigo,
                    ':nombre' => $nombre,
                    ':cantidad' => $cantidad,
                    ':fecha' => $fecha
                ]);
                
                $contador++;
            }
        }
        
        fclose($fp);
        $exitos[] = "✅ Salidas: $contador registros migrados";
        echo "   ✅ $contador registros de salidas migrados\n";
        
    } catch (Exception $e) {
        $errores[] = "❌ Error al migrar salidas: " . $e->getMessage();
        echo "   ❌ Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ⚠️  Archivo salidas.csv no encontrado\n";
}

// RESUMEN
echo "\n=== RESUMEN DE MIGRACIÓN ===\n";
if (!empty($exitos)) {
    foreach ($exitos as $exito) {
        echo "$exito\n";
    }
}

if (!empty($errores)) {
    echo "\nERRORES:\n";
    foreach ($errores as $error) {
        echo "$error\n";
    }
}

echo "\n=== MIGRACIÓN COMPLETADA ===\n";
echo "Ahora puedes usar el sistema con base de datos SQL.\n";
echo "Los archivos CSV antiguos se mantienen como respaldo.\n\n";

// Registrar en logs
try {
    $stmt = $pdo->prepare("INSERT INTO logs_sistema (evento, detalle) 
                           VALUES ('migracion_csv', :detalle)");
    $stmt->execute([
        ':detalle' => "Migración completada - Éxitos: " . count($exitos) . " - Errores: " . count($errores)
    ]);
} catch (Exception $e) {
    echo "⚠️  No se pudo registrar en logs: " . $e->getMessage() . "\n";
}
?>
