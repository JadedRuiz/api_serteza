<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $primaryKey = "id_cotizacion";
    protected $table = 'nom_cotizaciones';
    protected $fillable = [
        'id_cotizacion', 'folio', 'cliente', 'fecha', 'id_empresa', 'id_status', 'correo', 'fecha_creacion', 'usuario_creacion', 'activo'
    ];
}
