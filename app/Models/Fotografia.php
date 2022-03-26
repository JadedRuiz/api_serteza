<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fotografia extends Model {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $primaryKey = "id_fotografia";
    protected $table = 'gen_cat_fotografia';
    protected $fillable = [
        'id_fotografia', 'nombre', 'fotografia', 'extension', 'fecha_creacion', 'fecha_modificacion', 'usuario_creacion', 'usuario_modificacion', 'activo'
    ];
}