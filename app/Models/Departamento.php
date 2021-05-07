<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $table = 'cat_departamento';
    protected $primaryKey = "id_departamento";
    protected $fillable = [
        'id_empresa',
        'departamento', 
        'disponibilidad',
        'descripcion', 
        'direccion_id', 
        'fecha_creacion', 
        'fecha_modificacion', 
        'usuario_creacion', 
        'usuario_modificacion', 
        'activo'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
}
