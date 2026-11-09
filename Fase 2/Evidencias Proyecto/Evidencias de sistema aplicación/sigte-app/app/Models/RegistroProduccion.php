<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroProduccion extends Model
{
    protected $table = 'registros_produccion';

    protected $fillable = [
        'fecha',
        'servicio_id',
        'material_id',
        'cantidad',
        'factor_litros',
        'user_id',
        'observacion',
    ];

    protected $casts = [
        'fecha' => 'date',
        'cantidad' => 'integer',
        'factor_litros' => 'decimal:2',
    ];

    public function material()
    {
        return $this->belongsTo(
            MaterialProduccion::class,
            'material_id'
        );
    }

    public function servicio()
    {
        return $this->belongsTo(
            Servicio::class,
            'servicio_id'
        );
    }

    public function usuario()
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    public function getLitrosAttribute()
    {
        return round(
            $this->cantidad * $this->factor_litros,
            2
        );
    }
}
