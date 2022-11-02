<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConConcepto extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $primaryKey = "id_concepto";
    protected $table = 'con_catconceptos';
    protected $fillable = [
        'id_concepto', 'id_empresa', 'concepto', 'cuentacontable', 'confacura', 'cancelaiva', 'fecha_creacion', 'fecha_modificacion', 'usuario_creacion', 'usuario_modificacion', 'tipomovimiento'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
}
