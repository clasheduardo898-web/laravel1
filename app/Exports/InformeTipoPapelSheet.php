<?php

namespace App\Exports;

use App\Models\Corte;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class InformeTipoPapelSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $filtros;

    public function __construct(array $filtros)
    {
        $this->filtros = $filtros;
    }

    protected function cortesFiltrados()
    {
        $query = Corte::where('estado', 'finalizado');

        if (!empty($this->filtros['fecha_desde'])) {
            $query->whereDate('fecha', '>=', $this->filtros['fecha_desde']);
        }
        if (!empty($this->filtros['fecha_hasta'])) {
            $query->whereDate('fecha', '<=', $this->filtros['fecha_hasta']);
        }
        if (!empty($this->filtros['operario'])) {
            $query->where('operario', $this->filtros['operario']);
        }
        if (!empty($this->filtros['tipo_papel_id'])) {
            $query->where('tipo_papel_id', $this->filtros['tipo_papel_id']);
        }
        if (!empty($this->filtros['largo_master_id'])) {
            $query->where('largo_master_id', $this->filtros['largo_master_id']);
        }

        return $query->get();
    }

    public function collection()
    {
        return $this->cortesFiltrados()
            ->groupBy(fn($c) => $c->tipo_papel . '|' . $c->rollo_largo_mm)
            ->map(function ($grupo) {
                $primero = $grupo->first();
                return [
                    $primero->tipo_papel,
                    $primero->rollo_largo_mm,
                    $grupo->count(),
                    round($grupo->sum('rollo_peso_kg'), 3),
                    round($grupo->sum('merma_kg'), 3),
                ];
            })->values();
    }

    public function headings(): array
    {
        return ['Tipo de papel', 'Largo (mm)', 'Cantidad de masters', 'Peso total (kg)', 'Merma total (kg)'];
    }

    public function title(): string
    {
        return 'Por tipo de papel';
    }
}