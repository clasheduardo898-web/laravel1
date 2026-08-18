<?php

namespace App\Exports;

use App\Models\Corte;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class InformeAnchoSheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(protected array $filtros = [])
    {
    }

    private function cortesFiltrados()
    {
        $query = Corte::with('numerosCorte.rollosCortados')->where('estado', 'finalizado');

        if (!empty($this->filtros['fecha_desde'])) $query->whereDate('fecha', '>=', $this->filtros['fecha_desde']);
        if (!empty($this->filtros['fecha_hasta'])) $query->whereDate('fecha', '<=', $this->filtros['fecha_hasta']);
        if (!empty($this->filtros['operario'])) $query->where('operario', $this->filtros['operario']);
        if (!empty($this->filtros['tipo_papel_id'])) $query->where('tipo_papel_id', $this->filtros['tipo_papel_id']);
        if (!empty($this->filtros['largo_master_id'])) $query->where('largo_master_id', $this->filtros['largo_master_id']);

        return $query->get();
    }

    public function collection(): Collection
    {
        $rollos = $this->cortesFiltrados()->flatMap->numerosCorte->flatMap->rollosCortados;

        return $rollos->groupBy('ancho_mm')
            ->map(fn ($grupo, $ancho) => [
                (float) $ancho,
                $grupo->count(),
                round($grupo->sum('peso_neto_lb'), 3),
                round($grupo->sum('peso_kg'), 3),
            ])
            ->sortBy(fn ($fila) => $fila[0])
            ->values();
    }

    public function headings(): array
    {
        return ['Ancho (mm)', 'Cantidad', 'Neto (lb)', 'Neto (kg)'];
    }

    public function title(): string
    {
        return 'Por ancho';
    }
}