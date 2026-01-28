<?php
namespace App\Controllers;

use App\Models\Oferta;
use App\Models\OfertaDocumento;
use Exception; // Importamos la clase global para no usar la barra cada vez

class LicitacionController {
    
    public function index() {
        $buscar = $_GET['buscar'] ?? '';

    //Inicio de la consulta
    $query = Oferta::query();

    // implementación de filtro por consecutivo, objeto o descripción
    if (!empty($buscar)) {
        $query->where(function($q) use ($buscar) {
            $q->where('consecutivo', 'LIKE', "%$buscar%")
                ->orWhere('objeto', 'LIKE', "%$buscar%")
                ->orWhere('descripcion', 'LIKE', "%$buscar%");
        });
    }

    //Se realiza el orden de manera descendente
    $licitaciones = $query->orderBy('id', 'DESC')->get();
    echo json_encode($licitaciones);
    }

    //Función para crear, validar fechas y generar el consecutivo.
    public function store() {
        // Leemos el flujo de entrada
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);

        try {
            // Validación para verificar que se hayan recibido datos
            if (!$data) {
                throw new Exception("No se recibieron datos válidos.");
            }

            //Validación de caracteres por medio del backend
            if (empty($data['objeto']) || strlen($data['objeto']) > 150) {
                throw new Exception("El objeto es obligatorio y máximo 150 caracteres.");
            }

            //validación de fechas
            $f_inicio = $data['fecha_inicio'] ?? '';
            $h_inicio = $data['hora_inicio'] ?? '';
            $f_cierre = $data['fecha_cierre'] ?? '';
            $h_cierre = $data['hora_cierre'] ?? '';

            $inicio = strtotime("$f_inicio $h_inicio");
            $cierre = strtotime("$f_cierre $h_cierre");

            if (!$inicio || !$cierre || $cierre <= $inicio) {
                throw new Exception("La fecha/hora de cierre debe ser mayor a la de inicio.");
            }

            // Lógica del Consecutivo: O-0001-26
            $ultimoId = Oferta::max('id') ?? 0;
            $nuevoId = str_pad($ultimoId + 1, 4, '0', STR_PAD_LEFT);
            $ano = date('y');
            $consecutivo = "O-{$nuevoId}-{$ano}";

            // Validación del objetos
            $oferta = Oferta::create([
                'consecutivo' => $consecutivo,
                'objeto'      => $data['objeto'],
                'descripcion' => $data['descripcion'] ?? '',
                'moneda'      => $data['moneda'] ?? 'COP',
                'presupuesto' => $data['presupuesto'],
                'actividad_id'=> $data['actividad_id'],
                'fecha_inicio'=> $f_inicio,
                'hora_inicio' => $h_inicio,
                'fecha_cierre'=> $f_cierre,
                'hora_cierre' => $h_cierre,
                'estado'      => 'Publicada'
            ]);

            echo json_encode(['status' => 'success', 'data' => $oferta]);

        } catch (Exception $e) {
            http_response_code(400); 
            echo json_encode([
                'status' => 'error', 
                'message' => $e->getMessage()
            ]);
        }
    }
    public function actualizar()
    {
        //Control de acceso
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['message' => 'Método no permitido'], 405);
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            return $this->jsonResponse(['message' => 'ID de licitación no proporcionado'], 400);
        }

        //Validación de fechas
        $fecha_inicio = $_POST['fecha_inicio'] . ' ' . $_POST['hora_inicio'];
        $fecha_cierre = $_POST['fecha_cierre'] . ' ' . $_POST['hora_cierre'];

        if (strtotime($fecha_inicio) >= strtotime($fecha_cierre)) {
            return $this->jsonResponse(
                ['message' => 'La fecha de inicio debe ser menor a la de cierre'],
                400
            );
        }

        try {
        
            $oferta = Oferta::find($id);
            if (!$oferta) {
                return $this->jsonResponse(['message' => 'Licitación no encontrada'], 404);
            }

            // Actualizar del objeto enviado
            $oferta->objeto        = $_POST['objeto'];
            $oferta->presupuesto   = $_POST['presupuesto'];
            $oferta->moneda        = $_POST['moneda'];
            $oferta->actividad_id  = $_POST['actividad_id'];
            $oferta->descripcion   = $_POST['descripcion'];
            $oferta->fecha_inicio  = $_POST['fecha_inicio'];
            $oferta->hora_inicio   = $_POST['hora_inicio'];
            $oferta->fecha_cierre  = $_POST['fecha_cierre'];
            $oferta->hora_cierre   = $_POST['hora_cierre'];
            $oferta->save();

            //Validación para la carga de documentos
                if (isset($_FILES['docs']) && isset($_FILES['docs']['name'])) {
                    $rutaDestino = __DIR__ . "/../../uploads/";
                    
                    // Asegurarse de que la carpeta existe
                    if (!is_dir($rutaDestino)) {
                        mkdir($rutaDestino, 0777, true);
                    }

                    foreach ($_FILES['docs']['name'] as $index => $nombreOriginal) {
                        if (empty($_FILES['docs']['tmp_name'][$index])) continue;

                        //validación de extensión pdf y zip
                        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
                        if (!in_array($extension, ['pdf', 'zip'])) continue;

                        //Asignación de nombre unico para evitar inconvenientes con los documentos.
                        $nuevoNombre = uniqid('doc_') . '.' . $extension;

                        if (move_uploaded_file($_FILES['docs']['tmp_name'][$index], $rutaDestino . $nuevoNombre)) {
                            
                            $documento = new OfertaDocumento();
                            $documento->licitacion_id = $id;
                        
                            // php recibe docs_meta como un array si se mandó correctamente desde JS
                            $meta = $_POST['docs_meta'][$index] ?? [];
                            
                            $documento->titulo = $meta['titulo'] ?? 'Documento';
                            $documento->descripcion = $meta['descripcion'] ?? '';
                            $documento->archivo = $nuevoNombre;
                            $documento->save();
                        }
                    }
                }
            //Retorno de respuesta en caso de cumplir la excepción 200
            return $this->jsonResponse([
                'status'  => 'success',
                'message' => 'Actualizado correctamente'
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse([
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile()
            ], 500);
        }
    }
    //Excepción 200
    private function jsonResponse($data, $code = 200) {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode($data);
        exit;
    }
}