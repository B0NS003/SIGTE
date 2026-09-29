<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PanelController extends Controller
{
    public function resumen(): View
    {
        return view('panel.resumen', [
            'kpis' => [
                'en_proceso' => 28,
                'listas' => 12,
                'entregadas' => 19,
                'alertas' => 3,
            ],
            'fases' => [
                ['n' => 5, 'l' => 'Recepcion'],
                ['n' => 4, 'l' => 'Lavado'],
                ['n' => 6, 'l' => 'Preparacion'],
                ['n' => 7, 'l' => 'Esterilizacion'],
                ['n' => 6, 'l' => 'Almacen'],
                ['n' => 12, 'l' => 'Entrega'],
            ],
            'barras' => [
                ['d' => 'L', 'h' => 40, 'fill' => false],
                ['d' => 'M', 'h' => 55, 'fill' => false],
                ['d' => 'X', 'h' => 80, 'fill' => true],
                ['d' => 'J', 'h' => 60, 'fill' => false],
                ['d' => 'V', 'h' => 95, 'fill' => true],
                ['d' => 'S', 'h' => 35, 'fill' => false],
                ['d' => 'D', 'h' => 20, 'fill' => false],
            ],
            'movimientos' => [
                ['caja' => 'SET-042', 'fase' => 'Esterilizacion', 'area' => 'Pabellon'],
                ['caja' => 'CAJA-118', 'fase' => 'Lavado', 'area' => 'Urgencia'],
                ['caja' => 'SET-007', 'fase' => 'Entrega', 'area' => 'Maternidad'],
                ['caja' => 'CAJA-091', 'fase' => 'Recepcion', 'area' => 'Pabellon'],
            ],
        ]);
    }
}