<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/LargoMaster.php
class LargoMaster extends Model
{
    protected $table = 'largos_master';
    protected $fillable = ['tipo_papel_id', 'largo_mm', 'valor_original', 'unidad_medida', 'activo'];

    public function tipoPapel()
    {
        return $this->belongsTo(TipoPapel::class);
    }
}
