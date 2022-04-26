<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetFactura extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $primaryKey = "id_detfactura";
    protected $table = 'fac_detfactura';
    protected $fillable = [
        'id_detfactura', 'id_factura', 'id_concepto', 'descripcion', 'descuento', 'cantidad', 'importe', 'iva', 'ieps', 'otros_imp', 'subtotal', 'total', 'fecha_creacion', 'usuario_creacion', 'activo'
    ];
}
