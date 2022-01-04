<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    protected $table = 'rh_movimientos';
    protected $primaryKey = 'id_movimiento';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $fillable = [
        'id_movimiento', 'id_cliente', 'id_status', 'fecha_movimiento', 'tipo_movimiento', 'usuario_creacion', 'usuario_modificacion', 'fecha_creacion', 'fecha_modificacion', 'activo'
    ];
}