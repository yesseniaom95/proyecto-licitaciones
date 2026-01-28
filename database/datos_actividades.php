<?php
require_once __DIR__ . '/../config/database.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Actividad;

// Aumentamos el tiempo y memoria por si el Excel es muy grande
ini_set('memory_limit', '512M');
set_time_limit(0);

try {
    $archivo = __DIR__ . '/actividades.xlsx';
    $spreadsheet = IOFactory::load($archivo);
    $hoja = $spreadsheet->getActiveSheet();
    $filas = $hoja->toArray();

    echo "Iniciando importación...\n";

    foreach ($filas as $fila) {
        // VALIDACIÓN CLAVE: Si la primera celda no es un número, saltamos la fila
        if (isset($fila[0]) && is_numeric($fila[0])) { 
            Actividad::create([
                'codigo_segmento' => (int)$fila[0],
                'segmento'        => $fila[1],
                'codigo_familia'  => (int)$fila[2],
                'familia'         => $fila[3],
                'codigo_clase'    => (int)$fila[4],
                'clase'           => $fila[5],
                'codigo_producto' => (int)$fila[6],
                'producto'        => $fila[7],
            ]);
        }
    }

    echo "\n¡Importación completada con éxito!";

} catch (\Exception $e) {
    die("\nError al importar: " . $e->getMessage());
}