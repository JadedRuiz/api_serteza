<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Serie extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $primaryKey = "id_serie";
    protected $table = 'fac_catseries';
    protected $fillable = [
        'id_serie', 'id_empresa', 'id_direccion', 'serie', 'fecha_creacion', 'fecha_modificacion', 'usuario_creacion', 'usuario_modificacion', 'activo'
    ];
}
