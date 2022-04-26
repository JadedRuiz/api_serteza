<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $primaryKey = "id_sucursal";
    protected $table = 'nom_sucursales';
    protected $fillable = [
        'id_sucursal', 'id_empresa', 'id_direccion', 'id_cliente', 'sucursal', 'region', 'zona', 'tasaimpuestoestatal', 'tasaimpuestoespecial', 'prima_riesgotrabajo', 'usuario_creacion', 'usuario_modificacion', 'fecha_creacion', 'fecha_modificacion', 'activo', 'representante_legal', 'curp', 'rfc'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
}
