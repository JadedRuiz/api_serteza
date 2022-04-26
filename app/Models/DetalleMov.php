<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleMov extends Model
{
    protected $table = 'rh_detalle_movimiento';
    protected $primaryKey = 'id_detalle';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $fillable = [
        'id_detalle', 'id_status', 'id_movimiento', 'id_candidato', 'id_sucursal', 'id_puesto', 'id_nomina', 'sueldo', 'sueldo_neto', 'observacion', 'fecha_detalle', 'usuario_creacion', 'usuario_modificacion', 'fecha_creacion', 'fecha_modificacion', 'activo'
    ];
}