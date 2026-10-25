<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoConjunto extends Model
{
    protected $table = 'tipo_conjunto';

    protected $fillable = ['codigo', 'nombre_conjunto', 'servicio_id'];

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function instrumentos(): BelongsToMany
    {
        return $this->belongsToMany(Instrumento::class, 'tipo_conjunto_instrumentos', 'tipo_conjunto_id', 'instrumento_id')->withTimestamps();
    }
}
