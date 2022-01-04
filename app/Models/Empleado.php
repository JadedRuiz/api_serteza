<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class Empleado extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;
    
    protected $table = 'nom_empleados';
    protected $primaryKey = 'id_empleado';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $fillable = [
        'id_candidato', 'id_estatus', 'id_nomina', 'id_puesto', 'id_sucursal', 'id_registropatronal', 'id_catbanco', 'id_contratosat', 'folio', 'fecha_ingreso', 'fecha_antiguedad', 'cuenta', 'tarjeta', 'clabe', 'tipo_salario', 'jornada', 'sueldo_diario', 'sueldo_integrado', 'sueldo_complemento', 'aplicarsueldoneto', 'sinsubsidio', 'prestaciones_antiguedad', 'descripcion', 'usuario_creacion', 'usuario_modificacion', 'fecha_creacion', 'fecha_modificacion'
    ];
}
