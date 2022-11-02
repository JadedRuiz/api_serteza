<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class DetBanco extends Model 
{
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $primaryKey = "id_detbanco";
    protected $table = 'ban_detbancos';
    protected $fillable = [
        'id_detbanco','id_encbanco','id_movfactura','cuentacontable','descripcion','importe','iva','ieps','retencion_iva','retencion_isr','tipocambio','id_cifras_nomina'
    ];
}