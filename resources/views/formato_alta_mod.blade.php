<html>
    <table>
        <thead>
            <tr>
                <th colspan="3">
                    <img src="{{$datos_empresa->url_foto}}" width="90">
                </th>
                <th colspan="12" style="font-size: 24px;">
                    CONTROL DE ALTAS DE EMPLEADOS
                </th>
            </tr>
            <tr>
                <th>Empresa</th>
                <th>Cliente</th>
                <th>Sucursal</th>
                <th>Registro patronal</th>
                <th>Nómina</th>
                <th>Apellido paterno</th>
                <th>Apellido materno</th>
                <th>Nombre(s)</th>
                <th>R.F.C</th>
                <th>CURP</th>
                <th>Num. IMSS</th>
                <th>Fecha ingreso</th>
                <th>Puesto</th>
                <th>Departamento</th>
                <th>Cuenta bancaria</th>
                <th>Sueldo diario</th>
                <th>Sueldo integrado</th>
                <th>Sueldo complemento</th>
                <th>Calle</th>
                <th>Numero interior</th>
                <th>Numero exterior</th>
                <th>Cruzamiento uno</th>
                <th>Cruzamiento dos</th>
                <th>Colonia</th>
                <th>Municipio</th>
                <th>Estado</th>
                <th>Codigo postal</th>
                <th>Telefono</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($datos as $empleado)
                <tr>
                    <td>{{$datos_empresa->empresa}}</td>
                    <td>{{$empleado->cliente}}</td>
                    <td>{{$empleado->sucursal}}</td>
                    <td></td>
                    <td>{{$empleado->nomina}}</td>
                    <td>{{$empleado->apellido_paterno}}</td>
                    <td>{{$empleado->apellido_materno}}</td>
                    <td>{{$empleado->nombre}}</td>
                    <td>{{$empleado->rfc}}</td>
                    <td>{{$empleado->curp}}</td>
                    <td>{{$empleado->numero_seguro}}</td>
                    <td>{{date("d-m-Y",strtotime($empleado->fecha_ingreso))}}</td>
                    <td>{{$empleado->puesto}}</td>
                    <td>{{$empleado->departamento}}</td>
                    <td>{{$empleado->cuenta}}</td>
                    <td>{{$empleado->sueldo_diario}}</td>
                    <td>{{$empleado->sueldo_integrado}}</td>
                    <td>{{$empleado->sueldo_complemento}}</td>
                    <td>{{$empleado->calle}}</td>
                    <td>{{$empleado->numero_interior}}</td>
                    <td>{{$empleado->numero_exterior}}</td>
                    <td>{{$empleado->cruzamiento_uno}}</td>
                    <td>{{$empleado->cruzamiento_dos}}</td>
                    <td>{{$empleado->colonia}}</td>
                    <td>{{$empleado->municipio}}</td>
                    <td>{{$empleado->estado}}</td>
                    <td>{{$empleado->codigo_postal}}</td>
                    <td>{{$empleado->telefono}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</html>