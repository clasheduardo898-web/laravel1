<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 10mm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }

    h1 { font-size: 15px; margin: 0 0 4px 0; }
    .subtitulo { font-size: 9px; color: #555; margin-bottom: 10px; }

    .filtros { margin-bottom: 10px; }
    .filtros table { border-collapse: collapse; }
    .filtros td { padding: 2px 8px 2px 0; font-size: 9px; }
    .filtros .label { font-weight: bold; }

    .totales { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    .totales td {
        border: 1px solid #ccc;
        padding: 6px 8px;
        width: 20%;
        text-align: center;
    }
    .totales .valor { font-size: 14px; font-weight: bold; display: block; margin-top: 2px; }
    .totales .etq { font-size: 8px; color: #555; }

    h2 { font-size: 11px; margin: 14px 0 4px 0; }

    table.datos { width: 100%; border-collapse: collapse; font-size: 9px; margin-bottom: 10px; }
    table.datos th, table.datos td { border: 1px solid #999; padding: 3px 6px; text-align: center; }
    table.datos th { background: #eee; }
    table.datos td.vacio { text-align: center; color: #888; padding: 6px; }
</style>
</head>
<body>
    <h1>Informe de cortes</h1>
    <div class="subtitulo">Generado el {{ now()->format('d/m/Y H:i') }}</div>

    <div class="filtros">
        <table>
            <tr>
                <td class="label">Desde:</td>
                <td>{{ $filtros['fecha_desde'] ?? 'Sin límite' }}</td>
                <td class="label">Hasta:</td>
                <td>{{ $filtros['fecha_hasta'] ?? 'Sin límite' }}</td>
                <td class="label">Operario:</td>
                <td>{{ $filtros['operario'] ?? 'Todos' }}</td>
                <td class="label">Tipo de papel:</td>
                <td>{{ $tipoPapelNombre ?? 'Todos' }}</td>
            </tr>
        </table>
    </div>

    <table class="totales">
        <tr>
            <td><span class="etq">Cantidad de masters</span><span class="valor">{{ $totales['masters'] }}</span></td>
            <td><span class="etq">Peso total masters</span><span class="valor">{{ $totales['pesoMasterKg'] }} kg</span></td>
            <td><span class="etq">Desperdicio total</span><span class="valor">{{ $totales['mermaKg'] }} kg</span></td>
            <td><span class="etq">Rollos cortados</span><span class="valor">{{ $totales['rollos'] }}</span></td>
            <td><span class="etq">Peso neto cortado</span><span class="valor">{{ $totales['netoKg'] }} kg</span></td>
        </tr>
    </table>

    <h2>Totales por tipo de papel y largo de master</h2>
    <table class="datos">
        <thead>
            <tr>
                <th>Tipo de papel</th>
                <th>Largo (mm)</th>
                <th>Cantidad</th>
                <th>Peso total (kg)</th>
                <th>Merma (kg)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($resumenTipo as $fila)
                <tr>
                    <td>{{ $fila['tipo_papel'] }}</td>
                    <td>{{ $fila['largo_mm'] }}</td>
                    <td>{{ $fila['cantidad'] }}</td>
                    <td>{{ $fila['peso_total_kg'] }}</td>
                    <td>{{ $fila['merma_total_kg'] }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="vacio">Sin datos para los filtros seleccionados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Totales por ancho de rollo cortado</h2>
    <table class="datos">
        <thead>
            <tr>
                <th>Ancho (mm)</th>
                <th>Cantidad</th>
                <th>Neto (lb)</th>
                <th>Neto (kg)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($resumenAncho as $fila)
                <tr>
                    <td>{{ $fila['ancho_mm'] }}</td>
                    <td>{{ $fila['cantidad'] }}</td>
                    <td>{{ $fila['peso_neto_lb'] }}</td>
                    <td>{{ $fila['peso_neto_kg'] }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="vacio">Sin datos para los filtros seleccionados.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>