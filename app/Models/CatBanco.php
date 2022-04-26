<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class CatBanco extends Model 
{
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $primaryKey = "id_catbanco";
    protected $table = 'ban_catbancos';
    protected $fillable = [
        'id_catbanco','id_empresa','id_bancosat','cuenta','tarjeta','clabe','contrato','cuentacontable','usuario_creacion','usuario_modificacion', 'fecha_creacion', 'fecha_modificacion'
    ];
}