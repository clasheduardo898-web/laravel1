<?php

namespace App\Http\Controllers;

use App\Exports\InformesExport;
use App\Models\Corte;
use App\Models\TipoPapel;
use App\Models\Operario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class InformeController extends Controller
{
    private function filtros(Request $request): array
    {
        return $request->only(['fecha_desde', 'fecha_hasta', 'operario', 'tipo_papel_id', 'largo_master_id']);
    }

    private function cortesFiltrados(array $filtros)
    {
        $query = Corte::with('numerosCorte.rollosCortados')->where('estado', 'finalizado');

        if (!empty($filtros['fecha_desde'])) $query->whereDate('fecha', '>=', $filtros['fecha_desde']);
        if (!empty($filtros['fecha_hasta'])) $query->whereDate('fecha', '<=', $filtros['fecha_hasta']);
        if (!empty($filtros['operario'])) $query->where('operario', $filtros['operario']);
        if (!empty($filtros['tipo_papel_id'])) $query->where('tipo_papel_id', $filtros['tipo_papel_id']);
        if (!empty($filtros['largo_master_id'])) $query->where('largo_master_id', $filtros['largo_master_id']);

        return $query->get();
    }

    private function calcularResumen(array $filtros): array
    {
        $cortes = $this->cortesFiltrados($filtros);

        $resumenTipo = $cortes->groupBy(fn ($c) => $c->tipo_papel . '|' . $c->rollo_largo_mm)
            ->map(function ($grupo) {
                $primero = $grupo->first();
                return [
                    'tipo_papel' => $primero->tipo_papel,
                    'largo_mm' => $primero->rollo_largo_mm,
                    'cantidad' => $grupo->count(),
                    'peso_total_kg' => round($grupo->sum('rollo_peso_kg'), 3),
                    'merma_total_kg' => round($grupo->sum('merma_kg'), 3),
                ];
            })->values();

        $todosRollos = $cortes->flatMap->numerosCorte->flatMap->rollosCortados;

        $resumenAncho = $todosRollos->groupBy('ancho_mm')
            ->map(fn ($grupo, $ancho) => [
                'ancho_mm' => (float) $ancho,
                'cantidad' => $grupo->count(),
                'peso_neto_lb' => round($grupo->sum('peso_neto_lb'), 3),
                'peso_neto_kg' => round($grupo->sum('peso_kg'), 3),
            ])->sortBy('ancho_mm')->values();

        $totales = [
            'masters' => $cortes->count(),
            'pesoMasterKg' => round($cortes->sum('rollo_peso_kg'), 3),
            'mermaKg' => round($cortes->sum('merma_kg'), 3),
            'rollos' => $todosRollos->count(),
            'netoLb' => round($todosRollos->sum('peso_neto_lb'), 3),
            'netoKg' => round($todosRollos->sum('peso_kg'), 3),
        ];

        return compact('resumenTipo', 'resumenAncho', 'totales');
    }

    public function index(Request $request)
    {
        $filtros = $this->filtros($request);
        $resumen = $this->calcularResumen($filtros);

        return Inertia::render('Informes/Index', [
            'filtros' => $filtros,
            'tiposPapel' => TipoPapel::orderBy('nombre')->get(),
            'operariosDisponibles' => Operario::orderBy('nombre')->get(),
            'resumenTipo' => $resumen['resumenTipo'],
            'resumenAncho' => $resumen['resumenAncho'],
            'totales' => $resumen['totales'],
        ]);
    }

    public function exportar(Request $request)
    {
        return Excel::download(new InformesExport($this->filtros($request)), 'informes_cortes.xlsx');
    }

    public function exportarPdf(Request $request)
    {
        $filtros = $this->filtros($request);
        $resumen = $this->calcularResumen($filtros);

        $tipoPapelNombre = !empty($filtros['tipo_papel_id'])
            ? TipoPapel::find($filtros['tipo_papel_id'])?->nombre
            : null;

        $pdf = Pdf::loadView('pdf.informes', [
            'filtros' => $filtros,
            'tipoPapelNombre' => $tipoPapelNombre,
            'resumenTipo' => $resumen['resumenTipo'],
            'resumenAncho' => $resumen['resumenAncho'],
            'totales' => $resumen['totales'],
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('informe_cortes.pdf');
    }
}