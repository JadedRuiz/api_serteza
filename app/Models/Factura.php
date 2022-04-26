<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Factura extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $primaryKey = "id_factura";
    protected $table = 'fac_factura';
    protected $fillable = [
        'id_factura', 'id_empresa', 'id_catclientes', 'id_serie', 'folio', 'id_formapago', 'id_metodopago', 'numero_cuenta', 'id_tipomoneda', 'id_usocfdi', 'id_tipocomprobante', 'condicion_pago', 'tipo_cambio', 'observaciones', 'usa_ine', 'usa_cataporte', 'subtotal', 'descuento', 'iva', 'ieps', 'otros', 'total', 'fecha_creacion', 'usuario_creacion', 'activo'
    ];
}
