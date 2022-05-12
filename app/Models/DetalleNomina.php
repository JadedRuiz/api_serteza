<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleNomina extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $primaryKey = "id_detalle_nomina";
    protected $table = 'detalle_nomina';
    protected $fillable = [
        'id_detalle_nomina', 'id_bobeda', 'tipo', 'clave', 'concepto', 'importe', 'importe_gravado', 'clave_tipo', 'fecha_creacion', 'fecha_modificacion', 'usuario_creacion', 'usuario_modificacion', 'activo'
    ];
}