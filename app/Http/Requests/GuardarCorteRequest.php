// app/Http/Requests/GuardarCorteRequest.php
<?php

namespace App\Http\Requests;

use App\Services\CalculadoraCorte;
use Illuminate\Foundation\Http\FormRequest;

class GuardarCorteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // la autorización de ruta ya la resuelve el middleware 'permission'
    }

    public function rules(): array
    {
        return [
            'fecha' => 'required|date',
            'operario' => 'required|string|min:2',
            'tipo_papel_id' => 'required|exists:tipos_papel,id',
            'largo_master_id' => 'required|exists:largos_master,id',
            'rollo_peso' => 'required|numeric|min:0.01',
            'merma_kg' => 'nullable|numeric|min:0',
            'numeros_corte' => 'required|array|min:1',
            'numeros_corte.*.numero' => 'required|string',
            'numeros_corte.*.core_lb' => 'nullable|numeric|min:0',
            'numeros_corte.*.unidad_ancho' => 'required|in:mm,pulgada',
            'numeros_corte.*.rollos' => 'required|array|min:1|max:' . CalculadoraCorte::MAX_ROLLOS_POR_CORTE,
            'numeros_corte.*.rollos.*.ancho' => 'required|numeric|min:1',
            'numeros_corte.*.rollos.*.peso_lb' => 'required|numeric|min:0.001',
        ];
    }
}