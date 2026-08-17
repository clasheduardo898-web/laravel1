<?php

namespace App\Http\Controllers;

use App\Exports\CortesExport;
use App\Models\Corte;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class HistorialController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return Inertia::render('Historial/Index', [
            'cortes' => Corte::with('numerosCorte.rollosCortados', 'verificador')
                ->where('estado', 'finalizado')
                ->latest()
                ->paginate(5)
                ->withQueryString(),
            'puedeEditar' => $user->hasAnyRole(['admin', 'bodega']),
            'puedeEliminar' => $user->hasRole('admin'),
            'esAdmin' => $user->hasRole('admin'),
        ]);
    }

    public function verificar(Request $request, Corte $corte)
    {
        $corte->update(['verificado_por' => $request->user()->id, 'verificado_en' => now()]);
        session()->flash('message', 'Corte verificado.');
        return back();
    }

    public function revertirVerificacion(Request $request, Corte $corte)
    {
        abort_unless($request->user()->hasRole('admin'), 403);
        $corte->update(['verificado_por' => null, 'verificado_en' => null]);
        session()->flash('message', 'Verificación revertida.');
        return back();
    }

    public function destroy(Request $request, Corte $corte)
    {
        abort_unless($request->user()->hasRole('admin'), 403);
        $corte->delete();
        session()->flash('message', 'Corte eliminado correctamente.');
        return back();
    }

    public function exportar(Request $request)
    {
        $ids = $request->query('ids');
        $idsArray = $ids ? explode(',', $ids) : null;
        return Excel::download(new CortesExport($idsArray), 'historial_cortes.xlsx');
    }
}