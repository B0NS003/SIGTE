<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResuelveUsuario;
use App\Models\Caja;
use Illuminate\View\View;

class EnfermeraController extends Controller
{
    use ResuelveUsuario;

    public function cierreTurno(): View
    {
        return view('enfermera.cierre', [
            'usuario' => $this->usuarioSesion(),
            'turno' => [
                'fecha' => '15 sep 2026',
                'bloque' => 'Mañana · 08:00–16:00',
                'responsable' => 'Alejandra Riquelme',
                'relevo' => 'Patricia Vega',
                'operadoras' => 'Y. Maureira, C. Muñoz',
            ],
            'resumen' => [
                ['label' => 'Recepciones', 'value' => 14],
                ['label' => 'Entregas', 'value' => 11],
                ['label' => 'En proceso al cierre', 'value' => 7],
                ['label' => 'Alertas abiertas', 'value' => 3],
            ],
            'pendientes' => [
                'SET-042 sigue en esterilización (Autoclave 2) — revisar al inicio del próximo turno.',
                'CAJA-118 sin custodia al salir de lavado — validar con operadora de tarde.',
                'SET-CES-02 bajo mínimo (2 / 3) — avisar a administradora si no llega set de refuerzo.',
            ],
            'historial' => [
                ['fecha' => '14 sep', 'bloque' => 'Tarde 16:00–00:00', 'responsable' => 'Patricia Vega', 'estado' => 'Cerrado'],
                ['fecha' => '14 sep', 'bloque' => 'Mañana 08:00–16:00', 'responsable' => 'Alejandra Riquelme', 'estado' => 'Cerrado'],
                ['fecha' => '13 sep', 'bloque' => 'Tarde 16:00–00:00', 'responsable' => 'Alejandra Riquelme', 'estado' => 'Cerrado'],
            ],
        ]);
    }
}
