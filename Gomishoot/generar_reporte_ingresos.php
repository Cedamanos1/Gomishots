<?php
/**
 * GOMISHOT 2.0 - Generar Reporte de Ingresos (CSV) desde Base de Datos
 */

session_start();
require 'bd.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: ingresar.html");
    exit();
}

try {
    // Obtener todos los ingresos de la base de datos
    $stmt = $pdo->query("SELECT codigo, nombre, cantidad, fecha 
                         FROM ingresos 
                         ORDER BY fecha DESC, fecha_registro DESC");
    $ingresos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($ingresos)) {
        $_SESSION['error'] = "No hay datos para generar reporte";
        header("Location: ingreso.php");
        exit();
    }

    // Configurar headers para descarga CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="Reporte_Ingresos_' . date('Y-m-d_H-i-s') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // BOM para Excel UTF-8
    echo "\xEF\xBB\xBF";
    
    // Abrir output como archivo
    $output = fopen('php://output', 'w');
    
    // Escribir encabezados
    fputcsv($output, ['Codigo', 'Nombre', 'Cantidad', 'Fecha']);
    
    // Escribir datos
    foreach ($ingresos as $ingreso) {
        fputcsv($output, [
            $ingreso['codigo'],
            $ingreso['nombre'],
            $ingreso['cantidad'],
            $ingreso['fecha']
        ]);
    }
    
    fclose($output);
    exit();
    
} catch (PDOException $e) {
    error_log("Error al generar reporte de ingresos: " . $e->getMessage());
    $_SESSION['error'] = "Error al generar el reporte";
    header("Location: ingreso.php");
    exit();
}
?>
