<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Puesto extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $primaryKey = "id_puesto";
    protected $table = 'cat_puesto';
    protected $fillable = [
        'id_empresa', 
        'puesto', 
        'disponibilidad', 
        'descripcion', 
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
