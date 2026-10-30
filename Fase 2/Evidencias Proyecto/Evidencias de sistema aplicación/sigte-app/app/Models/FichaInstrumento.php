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
        'cantidad',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'orden' => 'integer',
        ];
    }

    public function ficha(): BelongsTo
    {
        return $this->belongsTo(Ficha::class);
    }

    public function instrumento(): BelongsTo
    {
        return $this->belongsTo(Instrumento::class);
    }
}
