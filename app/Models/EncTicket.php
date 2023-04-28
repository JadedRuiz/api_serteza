<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EncTicket extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $primaryKey = "id_ticket";
    protected $table = 'fac_enctickets';
    protected $fillable = [
        'id_ticket', 'id_empresa','folio','fecha','importepagar','id_factura','id_estatus'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
}