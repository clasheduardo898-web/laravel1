<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 8mm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111; }

    .header table { width: 480px; border-collapse: collapse; margin-bottom: 6px; }
    .header td { padding: 2px 6px; font-size: 10px; }
    .header td.label { font-weight: bold; width: 110px; }
    .header td.valor { width: 130px; }

    table.bloques { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 4px; }
    table.bloques > tbody > tr > td {
        vertical-align: top;
        width: 33.33%;
        border: 1px solid #000;
        padding: 4px;
    }

    .bloque-header { font-size: 9px; border-bottom: 1px solid #000; padding-bottom: 3px; margin-bottom: 4px; }
    .bloque-header strong { font-size: 10px; }

    table.rollos { width: 100%; border-collapse: collapse; font-size: 8px; }
    table.rollos th, table.rollos td { border: 1px solid #000; padding: 2px 3px; text-align: center; }
    table.rollos th { background: #eee; font-size: 7.5px; }
    table.rollos td.vacia { color: #fff; }

    table.firmas-bloque { width: 100%; border-collapse: collapse; margin-top: 40px; font-size: 8px; }
    table.firmas-bloque td.firma { padding-top: 20px; border-top: 1px solid #000; text-align: left; width: 45%; }
    table.firmas-bloque td.espacio { width: 10%; }

    .salto { page-break-before: always; }
</style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td class="label">Operario:</td>
                <td class="valor">{{ $corte->operario }}</td>
                <td class="label">Fecha:</td>
                <td class="valor">{{ $corte->fecha->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="label">Tipo de papel:</td>
                <td class="valor">{{ $corte->tipo_papel }}</td>
                <td class="label">Largo master:</td>
                <td class="valor">{{ $corte->rollo_largo_mm }} mm</td>
            </tr>
            <tr>
                <td class="label">Peso master:</td>
                <td class="valor">{{ $corte->rollo_peso_kg }} kg</td>
                <td class="label">Merma:</td>
                <td class="valor">{{ $corte->merma_kg }} kg</td>
            </tr>
            <tr>
                <td class="label">N&deg; de corte:</td>
                <td class="valor">#{{ $corte->id }} ({{ $corte->estado === 'finalizado' ? 'Finalizado' : 'Borrador' }})</td>
                <td class="label">N&deg; de m&aacute;quina:</td>
                <td class="valor">______________</td>
            </tr>
        </table>
    </div>

    @foreach ($corte->numerosCorte->chunk(3) as $index => $grupo)
        <table class="bloques {{ $index > 0 ? 'salto' : '' }}">
            <tr>
                @foreach ($grupo as $nc)
                    <td>
                        <div class="bloque-header">
                            N&deg; de corte: <strong>{{ $nc->numero }}</strong><br>
                            Core total: {{ $nc->core_lb }} lb &nbsp;|&nbsp;
                            Unidad: {{ $nc->unidad_ancho === 'pulgada' ? 'pulgadas' : 'mm' }}
                        </div>
                        <table class="rollos">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Medida<br>rollo (mm)</th>
                                    <th>PB<br>rollo (lb)</th>
                                    <th>Core</th>
                                    <th>PN<br>rollo (lb)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($nc->rollosCortados as $i => $rc)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $rc->ancho_mm }}</td>
                                        <td>{{ $rc->peso_lb }}</td>
                                        <td>{{ $rc->core_lb }}</td>
                                        <td>{{ $rc->peso_neto_lb }}</td>
                                    </tr>
                                @endforeach
                                @for ($i = $nc->rollosCortados->count(); $i < 20; $i++)
                                    <tr>
                                        <td class="vacia">{{ $i + 1 }}</td>
                                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                        <table class="firmas-bloque">
                            <tr>
                                <td class="firma">Operario</td>
                                <td class="espacio"></td>
                                <td class="firma">Rev. f&iacute;sico</td>
                            </tr>
                        </table>
                    </td>
                @endforeach
                @for ($i = $grupo->count(); $i < 3; $i++)
                    <td></td>
                @endfor
            </tr>
        </table>
    @endforeach
</body>
</html>