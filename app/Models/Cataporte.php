<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cataporte extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $primaryKey = "id_cataporte";
    protected $table = 'fac_cataportes';
    protected $fillable = [
        'id_cataporte', 'id_factura', 'id_operador', 'id_vehiculo', 'id_remolque', 'id_propietario'
    ];
}
