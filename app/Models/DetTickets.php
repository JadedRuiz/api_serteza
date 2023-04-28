<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetTicket extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $primaryKey = "id_detticket";
    protected $table = 'fac_dettickets';
    protected $fillable = [
        'id_detticket', 'id_ticket','id_concepto','cantidad','preciounitario','tasa_iva','importe_iva','tasa_ieps','importe_ieps','tasa_otrosimp','importe_otrosimp','tasa_retiva','importe_retiva','tasa_retisr','importe_retisr'
    ];
}