<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//Modelo ofertas documentos
class OfertaDocumento extends Model
{
    protected $table = 'ofertas_documentos';

    public $timestamps = false;

    protected $fillable = [
        'licitacion_id',
        'titulo',
        'descripcion',
        'archivo',
        'creado_en'
    ];

    public function oferta() {
        return $this->belongsTo(Oferta::class, 'licitacion_id');
    }
}