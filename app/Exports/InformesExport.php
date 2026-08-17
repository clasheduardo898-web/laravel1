<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InformesExport implements WithMultipleSheets
{
    protected $filtros;

    public function __construct(array $filtros)
    {
        $this->filtros = $filtros;
    }

    public function sheets(): array
    {
        return [
            new InformeTipoPapelSheet($this->filtros),
            new InformeAnchoSheet($this->filtros),
        ];
    }
}