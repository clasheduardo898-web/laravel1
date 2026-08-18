<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/Corte.php
class Corte extends Model
{
    protected $fillable = [
        'fecha', 'operario', 'tipo_papel', 'tipo_papel_id',
        'rollo_largo_mm', 'largo_master_id', 'rollo_peso_kg', 'merma_kg',
        'estado', 'creado_por', 'verificado_por', 'verificado_en',
    ];

    protected $casts = ['verificado_en' => 'datetime', 'fecha' => 'date'];

    public function numerosCorte() { return $this->hasMany(NumeroCorte::class); }
    public function creador() { return $this->belongsTo(User::class, 'creado_por'); }
    public function verificador() { return $this->belongsTo(User::class, 'verificado_por'); }
}
