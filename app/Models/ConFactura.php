<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConFactura extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $primaryKey = "id_movfactura";
    protected $table = 'con_movfacturas';
    protected $fillable = [
        'id_movfactura', 'id_empresa', 'id_movedaxml', 'id_concepto', 'id_provcliente', 'id_status', 'folio', 'fecha', 'subtotal', 'total', 'iva', 'retencion_iva', 'retencion_isr', 'id_cativas', 'cuentacontable', 'tipo_documento', 'ieps', 'tipocambio'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
}
