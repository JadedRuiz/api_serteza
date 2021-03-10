<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
    
class Candidato extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $table = 'rh_cat_candidatos';
    protected $fillable = [
        'id',
        'cat_status_id',
        'cat_clientes_id',
        'cat_fotografia_id',
        'cat_direccion_id',
        'apellido_paterno',
        'apellid_materno',
        'nombre',
        'rfc',
        'curp',
        'numero_seguro',
        'fecha_nacimiento',
        'correo',
        'telefono',
        'telefono_dos',
        'telefono_tres',
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
