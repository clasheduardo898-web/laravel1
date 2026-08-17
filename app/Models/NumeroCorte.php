<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/NumeroCorte.php
class NumeroCorte extends Model
{
    protected $fillable = ['corte_id', 'numero', 'core_lb', 'unidad_ancho'];

    public function corte() { return $this->belongsTo(Corte::class); }
    public function rollosCortados() { return $this->hasMany(RolloCortado::class); }
}