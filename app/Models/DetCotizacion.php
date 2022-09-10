<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetCotizacion extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $primaryKey = "id_detalle";
    protected $table = 'nom_detcotizaciones';
    protected $fillable = [
        'id_detalle', 'id_cotizacion', 'identificador', 'id_puesto', 'fecha_nacimiento', 'fecha_ingreso', 'sueldo_mensual','notas', 'fecha_creacion', 'usuario_creacion', 'activo'
    ];
}