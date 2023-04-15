<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Concepto extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $primaryKey = "id_concepto_empresa";
    protected $table = 'fac_catconceptos';
    protected $fillable = [
        'id_concepto_empresa', 'id_empresa', 'id_ClaveProdServ', 'id_UnidadMedida', 'clave_producto', 'descripcion', 'descuento', 'iva', 'iva_r', 'ieps', 'otros_imp', 'isr_r', 'fecha_creacion', 'fecha_modificacion', 'usuario_creacion', 'usuario_modificacion', 'activo','id_objetoimp'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
}
