<?php

error_reporting(0);
ini_set('display_errors', 0);

// 2. Cargar la base de datos y modelos
require_once __DIR__ . '/../config/database.php';

// 3. Configurar cabeceras de API
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

$url = $_GET['url'] ?? '/';

if ($url === 'api/licitaciones') {
    header("Content-Type: application/json");

    $method = $_SERVER['REQUEST_METHOD'];
    $controller = new App\Controllers\LicitacionController();

    if ($method === 'POST') {
        $controller->store();
    } else {
        $controller->index();
    }
    exit;
}
elseif ($url === 'api/actividades') {
    header("Content-Type: application/json");
    echo json_encode(App\Models\Actividad::all());
    exit;
}
elseif ($url === 'detalle_oferta') {
    include __DIR__ . '/../views/detalle_oferta.php';
    exit;
}
elseif ($url === 'api/detalle-licitacion') {
    header("Content-Type: application/json");
    $id = $_GET['id'] ?? null;

    $licitacion = App\Models\Oferta::with('documentos')->find($id);

    if ($licitacion) {
        echo json_encode($licitacion);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'La oferta no existe']);
    }
    exit;
}
elseif ($url === 'api/actualizar-licitacion') {
    header("Content-Type: application/json");
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'POST') {
        $controller = new App\Controllers\LicitacionController();
        $controller->actualizar(); // El método que procesa la edición
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
    }
    exit;
}
else {
    include __DIR__ . '/../views/index.php';
}
