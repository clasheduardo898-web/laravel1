<?php

namespace App\Http\Controllers;

use App\Models\Corte;
use App\Models\LargoMaster;
use App\Models\NumeroCorte;
use App\Models\Operario;
use App\Models\TipoPapel;
use App\Http\Requests\GuardarCorteRequest;
use App\Services\CalculadoraCorte;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CorteController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $puedeCrear = $user->can('cortes.crear');
        $puedeVerTodos = $user->can('cortes.ver-todos') || $user->can('cortes.verificar');

        $corteEnEdicion = null;
        if ($id = $request->query('editar')) {
            $corte = Corte::with('numerosCorte.rollosCortados', 'numerosCorte.verificador')->findOrFail($id);

            abort_unless($puedeVerTodos || $corte->creado_por === $user->id, 403);

            $corteEnEdicion = [
                'id' => $corte->id,
                'fecha' => $corte->fecha->format('Y-m-d'),
                'operario' => $corte->operario,
                'tipo_papel_id' => $corte->tipo_papel_id,
                'largo_master_id' => $corte->largo_master_id,
                'rollo_peso' => $corte->rollo_peso_kg,
                'merma_kg' => $corte->merma_kg,
                'numeros_corte' => $corte->numerosCorte->map(fn ($nc) => [
                    'id' => $nc->id,
                    'numero' => $nc->numero,
                    'core_lb' => $nc->core_lb,
                    'core_individual' => $nc->core_individual,
                    'unidad_ancho' => $nc->unidad_ancho,
                    'verificado_por' => $nc->verificado_por,
                    'verificado_en' => $nc->verificado_en?->format('d/m/Y H:i'),
                    'verificador' => $nc->verificador?->name,
                    'rollos' => $nc->rollosCortados->map(fn ($r) => [
                        'ancho' => (string) $r->ancho_mm,
                        'peso_lb' => (string) $r->peso_lb,
                        'core_lb' => (string) $r->core_lb,
                    ]),
                ]),
            ];
        }

        $borradoresQuery = Corte::where('estado', 'borrador')->latest();
        if (!$puedeVerTodos) {
            $borradoresQuery->where('creado_por', $user->id);
        }

        return Inertia::render('Cortes/Index', [
            'tiposPapel' => TipoPapel::where('activo', true)->orderBy('nombre')->get(),
            'operariosDisponibles' => Operario::where('activo', true)->orderBy('nombre')->get(),
            'borradores' => ($puedeCrear || $puedeVerTodos) ? $borradoresQuery->get() : collect(),
            'corteEnEdicion' => $corteEnEdicion,
            'puedeCrear' => $puedeCrear,
            'puedeVerificar' => $user->can('cortes.verificar'),
            'esAdmin' => $user->hasRole('admin'),
        ]);
    }

    public function store(GuardarCorteRequest $request)
    {
        $this->guardar($request, null, $request->boolean('finalizar'));
        return redirect('/cortes');
    }

    public function update(GuardarCorteRequest $request, Corte $corte)
    {
        $this->guardar($request, $corte, $request->boolean('finalizar'));

        if ($request->boolean('finalizar')) {
            return $request->user()->hasRole('operario') ? redirect('/cortes') : redirect('/historial');
        }
        return redirect('/cortes');
    }

    private function guardar(GuardarCorteRequest $request, ?Corte $corte, bool $finalizar): void
    {
        $data = $request->validated();
        $tipoPapel = TipoPapel::findOrFail($data['tipo_papel_id']);
        $largo = LargoMaster::findOrFail($data['largo_master_id']);

        $atributos = [
            'fecha' => $data['fecha'],
            'operario' => $data['operario'],
            'tipo_papel' => $tipoPapel->nombre,
            'tipo_papel_id' => $tipoPapel->id,
            'rollo_largo_mm' => $largo->largo_mm,
            'largo_master_id' => $largo->id,
            'rollo_peso_kg' => $data['rollo_peso'],
            'merma_kg' => $data['merma_kg'] ?? 0,
            'estado' => $finalizar ? 'finalizado' : 'borrador',
        ];

        if ($corte) {
            $corte->update($atributos);
        } else {
            $atributos['creado_por'] = $request->user()->id;
            $corte = Corte::create($atributos);
        }

        $idsEnviados = collect($data['numeros_corte'])->pluck('id')->filter()->all();

        $corte->numerosCorte()
            ->whereNotIn('id', $idsEnviados ?: [0])
            ->whereNull('verificado_por')
            ->delete();

        foreach ($data['numeros_corte'] as $nc) {
            $existente = !empty($nc['id']) ? $corte->numerosCorte()->find($nc['id']) : null;

            if ($existente && $existente->verificado_por) {
                continue;
            }

            $coreIndividual = !empty($nc['core_individual']);

            if ($existente) {
                $existente->update([
                    'numero' => $nc['numero'],
                    'core_lb' => $coreIndividual ? 0 : ($nc['core_lb'] ?: 0),
                    'core_individual' => $coreIndividual,
                    'unidad_ancho' => $nc['unidad_ancho'],
                ]);
                $existente->rollosCortados()->delete();
                $numeroCorte = $existente;
            } else {
                $numeroCorte = $corte->numerosCorte()->create([
                    'numero' => $nc['numero'],
                    'core_lb' => $coreIndividual ? 0 : ($nc['core_lb'] ?: 0),
                    'core_individual' => $coreIndividual,
                    'unidad_ancho' => $nc['unidad_ancho'],
                ]);
            }

            foreach ($nc['rollos'] as $rollo) {
                $ancho = (float) $rollo['ancho'];
                $pesoBruto = (float) $rollo['peso_lb'];

                if ($coreIndividual) {
                    $core = (float) ($rollo['core_lb'] ?? 0);
                    $neto = max(0, $pesoBruto - $core);
                } else {
                    $core = CalculadoraCorte::coreParteLb($nc, $ancho);
                    $neto = CalculadoraCorte::pesoNetoLb($nc, $pesoBruto, $ancho);
                }

                $numeroCorte->rollosCortados()->create([
                    'ancho_mm' => $ancho,
                    'core_lb' => round($core, 3),
                    'peso_lb' => $pesoBruto,
                    'peso_neto_lb' => round($neto, 3),
                    'peso_kg' => CalculadoraCorte::pesoKg($neto),
                ]);
            }
        }

        session()->flash('message', $finalizar ? 'Corte guardado en el historial.' : 'Borrador guardado.');
    }

    public function destroyBorrador(Request $request, Corte $corte)
    {
        $user = $request->user();
        abort_unless(
            $corte->estado === 'borrador' && ($corte->creado_por === $user->id || $user->hasRole('admin')),
            403
        );
        $corte->delete();
        session()->flash('message', 'Borrador eliminado.');
        return back();
    }

    public function verificarNumeroCorte(Request $request, NumeroCorte $numeroCorte)
    {
        $numeroCorte->update([
            'verificado_por' => $request->user()->id,
            'verificado_en' => now(),
        ]);
        session()->flash('message', 'Número de corte verificado.');
        return back();
    }

    public function revertirNumeroCorte(Request $request, NumeroCorte $numeroCorte)
    {
        abort_unless($request->user()->hasRole('admin'), 403);
        $numeroCorte->update(['verificado_por' => null, 'verificado_en' => null]);
        session()->flash('message', 'Verificación revertida.');
        return back();
    }

    public function imprimir(Corte $corte)
    {
        $corte->load('numerosCorte.rollosCortados', 'numerosCorte.verificador');

        $pdf = Pdf::loadView('pdf.corte', ['corte' => $corte])->setPaper('a4', 'landscape');

        return $pdf->stream("corte-{$corte->id}.pdf");
    }
}