<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $primaryKey = "id_empresa";
    protected $table = 'cat_empresa ';
    protected $fillable = [
        'id', 
        'id_direccion', 
        'id_fotografia',
        'id_estatus', 
        'empresa', 
        'rfc', 
        'descripcion', 
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
