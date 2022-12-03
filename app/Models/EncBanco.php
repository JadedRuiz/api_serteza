<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class EncBanco extends Model 
{
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $primaryKey = "id_encbanco";
    protected $table = 'ban_encbancos';
    protected $fillable = [
        'id_encbanco','id_catbanco','id_concepto','id_estatus','mes','ejercicio','fechamovto','documento','beneficiario','descripcion','importe','tipo_cambio','usuario_creacion','usuario_modificacion', 'fecha_creacion', 'fecha_modificacion'
    ];
}