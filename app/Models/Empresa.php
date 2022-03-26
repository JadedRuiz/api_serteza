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
    protected $table = 'gen_cat_empresa';
    protected $fillable = [
        'id', 
        'id_direccion', 
        'id_fotografia',
        'id_status', 
        'empresa', 
        'rfc', 
        'descripcion', 
        'razon_social', 
        'fecha_creacion', 
        'fecha_modificacion', 
        'usuario_creacion', 
        'usuario_modificacion', 
        'representante_legal',
        'cargo_repre',
        'rfc_repre',
        'curp',
        'no_certificado',
        'key',
        'certificado',
        'activo'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
}
