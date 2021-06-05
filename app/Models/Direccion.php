<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Direccion extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $table = 'cat_direccion';
    protected $primaryKey = "id_direccion";
    protected $fillable = [
        'id', 
        'calle', 
        'numero_interior', 
        'numero_exterior', 
        'cruzamiento_uno', 
        'cruzamiento_dos', 
        'codigo_postal',
        'colonia',
        'localidad', 
        'municipio', 
        'estado', 
        'descripcion', 
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
