<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/NumeroCorte.php
class NumeroCorte extends Model
{
    protected $fillable = ['corte_id', 'numero', 'core_lb', 'core_individual', 'unidad_ancho', 'verificado_por', 'verificado_en'];
    protected $casts = ['core_individual' => 'boolean', 'verificado_en' => 'datetime'];

    public function corte() { return $this->belongsTo(Corte::class); }
    public function rollosCortados() { return $this->hasMany(RolloCortado::class); }
    public function verificador() { return $this->belongsTo(User::class, 'verificado_por'); }
}