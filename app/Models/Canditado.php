<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Canditado extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'rec_cat_canditados';
    protected $fillable = [
        'id',
        'calle',
        'numero_interior',
        'numero_exterior',
        'cruzamiento_uno', 
        'cruzamiento_dos', 
        'codigo_postal', 
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
