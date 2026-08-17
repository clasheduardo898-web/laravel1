<?php

namespace App\Http\Controllers;

use App\Models\LargoMaster;
use App\Models\TipoPapel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TipoPapelController extends Controller
{
    const MM_POR_PULGADA = 25.4;

    public function index()
    {
        return Inertia::render('TiposPapel/Index', [
            'tiposPapel' => TipoPapel::with('largos')->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['nombre' => 'required|min:2']);
        TipoPapel::create(['nombre' => $data['nombre'], 'activo' => true]);
        session()->flash('message', 'Tipo de papel agregado.');
        return back();
    }

    public function agregarLargo(Request $request, TipoPapel $tipoPapel)
    {
        $data = $request->validate([
            'valor' => 'required|numeric|min:0.01',
            'unidad_medida' => 'required|in:mm,pulgada',
        ]);

        $largoMm = $data['unidad_medida'] === 'pulgada' ? $data['valor'] * self::MM_POR_PULGADA : $data['valor'];

        $tipoPapel->largos()->create([
            'valor_original' => $data['valor'],
            'unidad_medida' => $data['unidad_medida'],
            'largo_mm' => $largoMm,
            'activo' => true,
        ]);

        session()->flash('message', 'Largo agregado.');
        return back();
    }

    public function actualizarLargo(Request $request, LargoMaster $largo)
    {
        $data = $request->validate([
            'valor' => 'required|numeric|min:0.01',
            'unidad_medida' => 'required|in:mm,pulgada',
        ]);

        $largo->update([
            'valor_original' => $data['valor'],
            'unidad_medida' => $data['unidad_medida'],
            'largo_mm' => $data['unidad_medida'] === 'pulgada' ? $data['valor'] * self::MM_POR_PULGADA : $data['valor'],
        ]);

        session()->flash('message', 'Largo actualizado.');
        return back();
    }

    public function alternarLargo(LargoMaster $largo)
    {
        $largo->update(['activo' => !$largo->activo]);
        return back();
    }

    public function eliminarLargo(LargoMaster $largo)
    {
        $largo->delete();
        session()->flash('message', 'Largo eliminado.');
        return back();
    }
}