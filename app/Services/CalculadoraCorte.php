// app/Services/CalculadoraCorte.php
<?php

namespace App\Services;

class CalculadoraCorte
{
    const LIBRAS_POR_KG = 2.2046;
    const MM_POR_PULGADA = 25.4;
    const MAX_ROLLOS_POR_CORTE = 30;

    public static function anchoEnMm(array $numeroCorte, float $anchoValor): float
    {
        return ($numeroCorte['unidad_ancho'] ?? 'mm') === 'pulgada'
            ? $anchoValor * self::MM_POR_PULGADA
            : $anchoValor;
    }

    public static function totalAncho(array $numeroCorte): float
    {
        return collect($numeroCorte['rollos'])->sum(fn ($r) => (float) ($r['ancho'] ?? 0));
    }

    public static function coreParteLb(array $numeroCorte, float $anchoIndividual): float
    {
        $totalAncho = self::totalAncho($numeroCorte);
        $core = (float) ($numeroCorte['core_lb'] ?: 0);
        if ($totalAncho <= 0) {
            return 0;
        }
        return ($core / $totalAncho) * $anchoIndividual;
    }

    public static function pesoNetoLb(array $numeroCorte, float $pesoBruto, float $anchoIndividual): float
    {
        return max(0, $pesoBruto - self::coreParteLb($numeroCorte, $anchoIndividual));
    }

    public static function pesoKg(float $pesoNeto): float
    {
        return $pesoNeto ? round($pesoNeto / self::LIBRAS_POR_KG, 3) : 0;
    }
}