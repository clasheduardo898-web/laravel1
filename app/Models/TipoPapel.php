<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/TipoPapel.php
class TipoPapel extends Model
{
    protected $table = 'tipos_papel';
    protected $fillable = ['nombre', 'activo'];

    public function largos()
    {
        return $this->hasMany(LargoMaster::class);
    }
}