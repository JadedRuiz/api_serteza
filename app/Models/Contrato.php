<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    protected $table = 'rh_contratos';
    protected $primaryKey = 'id_contrato';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $fillable = [
        'id_contrato', 'id_empresa', 'nombre', 'url_contrato', 'activo'
    ];
}