<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model {

    protected $table = 'gen_cat_departamento';
    protected $primaryKey = "id_departamento";
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $fillable = [
        'id_departamento',
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
