<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BobedaXML extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $primaryKey = "id_bobeda";
    protected $table = 'bobeda_xml';
    protected $fillable = [
        'id_bobeda', 'id_empresa', 'uuid', 'tipo_combrobante', 'emitidos', 'id_estatus', 'subtotal', 'total', 'moneda', 'cambio_subtotal', 'cambio_total', 'tipo_cambio', 'descuento', 'fecha_creacion', 'fecha_modificacion', 'usuario_creacion', 'usuario_modificacion', 'activo'
    ];
}