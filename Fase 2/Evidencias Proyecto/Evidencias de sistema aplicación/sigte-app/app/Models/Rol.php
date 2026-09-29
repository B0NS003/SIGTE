<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    public const ADMINISTRADORA = 'administradora';
    public const ENFERMERA = 'enfermera';
    public const OPERADOR = 'operador';

    protected $table = 'roles';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'rol_id');
    }
}
