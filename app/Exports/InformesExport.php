<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InformesExport implements Export, WithMultipleSheets
{
    public function __construct(protected array $filtros = [])
    {
    }

    public function sheets(): array
    {
        return [
            'Por tipo de papel' => new InformeTipoPapelSheet($this->filtros),
            'Por ancho' => new InformeAnchoSheet($this->filtros),
        ];
    }
}