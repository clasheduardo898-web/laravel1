<?php

namespace App\Http\Controllers;

use App\Models\Operario;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OperarioController extends Controller
{
    public function index()
    {
        return Inertia::render('Operarios/Index', [
            'operarios' => Operario::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['nombre' => 'required|min:2']);
        Operario::create(['nombre' => $data['nombre'], 'activo' => true]);
        session()->flash('message', 'Operario agregado.');
        return back();
    }

    public function update(Request $request, Operario $operario)
    {
        $data = $request->validate([
            'nombre' => 'required|min:2',
            'activo' => 'sometimes|boolean',
        ]);
        $operario->update($data);
        session()->flash('message', 'Operario actualizado.');
        return back();
    }

    public function destroy(Operario $operario)
    {
        $operario->delete();
        session()->flash('message', 'Operario eliminado.');
        return back();
    }
}