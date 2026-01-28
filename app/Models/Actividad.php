<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividad extends Model {
    protected $table = 'actividades';
    public $timestamps = false; // Esta tabla no suele llevar created_at

    protected $fillable = [
        'codigo_segmento', 'segmento', 'codigo_familia', 'familia',
        'codigo_clase', 'clase', 'codigo_producto', 'producto'
    ];
}