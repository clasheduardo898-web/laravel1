<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/RolloCortado.php
class RolloCortado extends Model
{
    protected $table = 'rollos_cortados';
    protected $fillable = ['numero_corte_id', 'ancho_mm', 'core_lb', 'peso_lb', 'peso_neto_lb', 'peso_kg'];

    public function numeroCorte() { return $this->belongsTo(NumeroCorte::class); }
}
