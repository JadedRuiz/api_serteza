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
    protected $table = 'con_bovedaxml';
    protected $fillable = [
        'id_boveda', 'id_empresa', 'uuid', 'rfc', 'curp', 'nombre', 'num_seguro', 'tipo_combrobante', 'emitidos', 'id_estatus', 'subtotal', 'total', 'moneda', 'cambio_subtotal', 'cambio_total', 'tipo_cambio', 'descuento', 'fecha_pago', 'fecha_inicial_pago', 'fecha_final_pago', 'salario_diario', 'salario_base', 'xml', 'fecha_creacion', 'fecha_modificacion', 'usuario_creacion', 'usuario_modificacion', 'activo'
    ];
}