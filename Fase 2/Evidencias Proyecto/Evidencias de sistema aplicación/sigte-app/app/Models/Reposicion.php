<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reposicion extends Model
{
    protected $table = 'reposiciones';

    protected $fillable = [
        'elemento_inventario_id',
        'cantidad',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
        ];
    }

    public function elemento(): BelongsTo
    {
        return $this->belongsTo(ElementoInventario::class, 'elemento_inventario_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
