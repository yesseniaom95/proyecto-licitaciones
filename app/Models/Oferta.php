<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//Modal ofertas
class Oferta extends Model {
    protected $table = 'ofertas'; 
    
    protected $fillable = [
        'consecutivo', 'objeto', 'descripcion', 'moneda', 
        'presupuesto', 'actividad_id', 'fecha_inicio', 
        'hora_inicio', 'fecha_cierre', 'hora_cierre', 'estado'
    ];

    
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    //Relación con el modelo ofertas documentos
    public function documentos() {
        return $this->hasMany(OfertaDocumento::class, 'licitacion_id');
    }
}