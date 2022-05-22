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
    protected $primaryKey = "id_boveda";
    protected $table = 'con_bovedaxml';
    protected $fillable = [
        'id_boveda', 'id_empresa', 'uuid', 'rfc', 'curp', 'nombre', 'num_seguro', 'tipo_combrobante', 'emitidos', 'id_estatus', 'subtotal', 'total', 'moneda', 'cambio_subtotal', 'cambio_total', 'tipo_cambio', 'descuento', 'fecha_inicial_pago', 'fecha_final_pago', 'fecha_pago', 'salario_base', 'salario_diario', 'xml', 'fecha_creacion', 'fecha_modificacion', 'usuario_creacion', 'usuario_modificacion', 'activo'
    ];
}