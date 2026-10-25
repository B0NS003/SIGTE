<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FichaInstrumento extends Model
{
    protected $table = 'ficha_instrumentos';

    protected $fillable = [
        'ficha_id',
        'instrumento_id',
        'tipo_conjunto_id',
        'cantidad',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'ficha_id' => 'integer',
            'instrumento_id' => 'integer',
            'tipo_conjunto_id' => 'integer',
            'cantidad' => 'integer',
            'orden' => 'integer',
        ];
    }

    public function ficha(): BelongsTo
    {
        return $this->belongsTo(Ficha::class, 'ficha_id');
    }

    public function instrumento(): BelongsTo
    {
        return $this->belongsTo(Instrumento::class, 'instrumento_id');
    }

    public function tipoConjunto(): BelongsTo
    {
        return $this->belongsTo(TipoConjunto::class, 'tipo_conjunto_id');
    }
}