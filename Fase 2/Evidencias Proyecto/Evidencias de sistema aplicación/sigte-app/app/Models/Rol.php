<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    public const ADMINISTRADORA = 'administradora';
    public const ENFERMERA = 'enfermera';
    public const OPERADOR = 'operador';
    public const SECRETARIA = 'secretaria';

    protected $table = 'roles';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'rol_id');
    }

    public function etiqueta(): string
    {
        return match ($this->nombre) {
            self::ADMINISTRADORA => 'Administradora',
            self::ENFERMERA => 'Enfermera de turno',
            self::OPERADOR => 'Operadora',
            self::SECRETARIA => 'Secretaría',
            default => $this->nombre,
        };
    }

    public function claseVisual(): string
    {
        return match ($this->nombre) {
            self::ADMINISTRADORA => 'rol-admin',
            self::OPERADOR => 'rol-operadora',
            self::SECRETARIA => 'rol-secretaria',
            default => 'rol-enfermera',
        };
    }
}
