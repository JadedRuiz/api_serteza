<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Puesto extends Model {

    protected $primaryKey = "id_puesto";
    protected $table = 'gen_cat_puesto';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $fillable = [
        'id_puesto', 
        'id_departamento',
        'puesto', 
        'autorizados', 
        'sueldo_tipo_a',
        'sueldo_tipo_b',
        "sueldo_tipo_c",
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
