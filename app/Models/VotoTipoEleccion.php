<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VotoTipoEleccion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'voto_tipo_eleccion';
    protected $primaryKey = 'id_tipo_eleccion';
    
    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'max_candidatos',
        'activo'
    ];
    
    protected $casts = [
        'max_candidatos' => 'integer',
        'activo' => 'boolean'
    ];
}