<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $table = 'gen_cat_cliente';
    protected $primaryKey = "id_cliente";
    protected $fillable = [
        'cliente', 
        'contacto',
        'descripcion', 
        'direccion_id', 
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
