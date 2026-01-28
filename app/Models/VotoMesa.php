<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VotoMesa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'voto_mesa';
    protected $primaryKey = 'id_mesa';
    
    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'numero_personas',
        'id_recinto',
        'activa'
    ];
    
    protected $casts = [
        'numero_personas' => 'integer',
        'activa' => 'boolean'
    ];
    
    /**
     * Relación con recinto
     */
    public function recinto()
    {
        return $this->belongsTo(VotoGeografico::class, 'id_recinto', 'id_geografico');
    }
}