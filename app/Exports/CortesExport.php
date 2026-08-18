<?php

namespace App\Exports;

use App\Models\Corte;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Enumerable;

class CortesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $ids;

    public function __construct($ids = null)
    {
        $this->ids = $ids;
    }

    public function collection():Enumerable
    {
        $query = Corte::with('numerosCorte.rollosCortados')->latest();

        if (!empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        }

        return $query->get()
            ->flatMap(function ($corte) {
                return $corte->numerosCorte->flatMap(function ($nc) use ($corte) {
                    return $nc->rollosCortados->map(function ($rollo) use ($corte, $nc) {
                        return (object) [
                            'fecha' => $corte->fecha,
                            'operario' => $corte->operario,
                            'tipo_papel' => $corte->tipo_papel,
                            'largo_mm' => $corte->rollo_largo_mm,
                            'peso_master_kg' => $corte->rollo_peso_kg,
                            'merma_kg' => $corte->merma_kg,
                            'numero_corte' => $nc->numero,
                            'core_total_lb' => $nc->core_lb,
                            'ancho_mm' => $rollo->ancho_mm,
                            'peso_bruto_lb' => $rollo->peso_lb,
                            'core_rollo_lb' => $rollo->core_lb,
                            'peso_neto_lb' => $rollo->peso_neto_lb,
                            'peso_kg' => $rollo->peso_kg,
                        ];
                    });
                });
            });
    }

    public function headings(): array
    {
        return ['Fecha', 'Operario', 'Tipo de papel', 'Largo (mm)', 'Peso master (kg)', 'Merma (kg)', 'N° corte', 'Core total (lb)', 'Ancho (mm)', 'Peso bruto (lb)', 'Core rollo (lb)', 'Peso neto (lb)', 'Peso (kg)'];
    }

    public function map($row): array
    {
        return array_values((array) $row);
    }
}