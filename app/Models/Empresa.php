<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'gen_cat_empresas';
    protected $fillable = [
        'id', 
        'direccion_id', 
        'estatus_id', 
        'empresa', 
        'rfc', 
        'curp', 
        'razon_social', 
        'fecha_creacion', 
        'fecha_modificacion', 
        'cat_usuario_c_id', 
        'cat_usuario_m_id', 
        'activo'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
}
