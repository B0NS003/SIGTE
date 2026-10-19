<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sala extends Model
{
    protected $fillable = [
        'codigo',
        'nombre',
    ];

    public function elementos(): HasMany
    {
        return $this->hasMany(ElementoInventario::class, 'sala_id');
    }
}
