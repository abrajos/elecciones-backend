<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VotoPersona extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'voto_persona';
    protected $primaryKey = 'id_persona';
    
    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'ci',
        'celular',
        'email'
    ];
    
    /**
     * Relación con usuario
     */
    public function usuario()
    {
        return $this->hasOne(VotoUsuario::class, 'id_persona', 'id_persona');
    }
    
    /**
     * Obtener nombre completo
     */
    public function getNombreCompletoAttribute()
    {
        return trim("{$this->nombre} {$this->apellido_paterno} {$this->apellido_materno}");
    }
}