<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VotoGeografico extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'voto_geografico';
    protected $primaryKey = 'id_geografico';
    
    protected $fillable = [
        'nombre',
        'codigo',
        'ubicacion',
        'tipo',
        'fk_id_geografico'
    ];
    
    // Constantes para tipos
    const TIPO_PAIS = 'PAIS';
    const TIPO_CIUDAD = 'CIUDAD';
    const TIPO_MUNICIPIO = 'MUNICIPIO';
    const TIPO_LOCALIDAD = 'LOCALIDAD';
    const TIPO_RECINTO = 'RECINTO';
    
    /**
     * Obtener todos los tipos disponibles
     */
    public static function getTipos()
    {
        return [
            self::TIPO_PAIS => 'País',
            self::TIPO_CIUDAD => 'Ciudad',
            self::TIPO_MUNICIPIO => 'Municipio',
            self::TIPO_LOCALIDAD => 'Localidad',
            self::TIPO_RECINTO => 'Recinto Electoral'
        ];
    }
    
    /**
     * Obtener jerarquía permitida
     */
    public static function getJerarquiaTipos()
    {
        return [
            self::TIPO_PAIS => null,
            self::TIPO_CIUDAD => self::TIPO_PAIS,
            self::TIPO_MUNICIPIO => self::TIPO_CIUDAD,
            self::TIPO_LOCALIDAD => self::TIPO_MUNICIPIO,
            self::TIPO_RECINTO => self::TIPO_LOCALIDAD
        ];
    }
    
    /**
     * Relación padre
     */
    public function padre()
    {
        return $this->belongsTo(VotoGeografico::class, 'fk_id_geografico', 'id_geografico');
    }
    
    /**
     * Relación hijos
     */
    public function hijos()
    {
        return $this->hasMany(VotoGeografico::class, 'fk_id_geografico', 'id_geografico');
    }
    
    /**
     * Relación con mesas (un recinto puede tener muchas mesas)
     */
    public function mesas()
    {
        return $this->hasMany(VotoMesa::class, 'id_recinto', 'id_geografico');
    }
}