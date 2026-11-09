<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialProduccion extends Model
{
    protected $table = 'materiales_produccion';

    protected $fillable = [
        'nombre',
        'categoria',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function registros()
    {
        return $this->hasMany(
            RegistroProduccion::class,
            'material_id'
        );
    }
}
