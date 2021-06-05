<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class Usuario extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;
    
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $table = 'cat_usuario';
    protected $primaryKey = "id_usuario";
    protected $fillable = [
        'id_fotografia','nombre', 'password', 'usuario','fecha_creacion','fecha_modificacion','usuario_creacion','usuario_modificacion','activo'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];
}
