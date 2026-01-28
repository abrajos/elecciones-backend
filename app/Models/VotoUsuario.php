<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class VotoUsuario extends Authenticatable
{
    use HasFactory, SoftDeletes, HasApiTokens, HasRoles;

    protected $table = 'voto_usuario';
    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'nombre_usuario',
        'contrasena',
        'fecha_fin',
        'token',
        'activo',
        'id_persona'
    ];

    protected $hidden = [
        'contrasena',
        'token',
        'remember_token'
    ];

    protected $casts = [
        'fecha_fin' => 'date',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName()
    {
        return 'id_usuario';
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword()
    {
        return $this->contrasena;
    }

    /**
     * Relación con persona
     */
    public function persona()
    {
        return $this->belongsTo(VotoPersona::class, 'id_persona', 'id_persona');
    }

    /**
     * Mutator para encriptar contraseña
     */
    public function setContrasenaAttribute($value)
    {
        $this->attributes['contrasena'] = bcrypt($value);
    }

    /**
     * Verificar si el usuario está activo
     */
    public function isActive()
    {
        if (!$this->activo) {
            return false;
        }

        if ($this->fecha_fin && $this->fecha_fin < now()) {
            return false;
        }

        return true;
    }
}
