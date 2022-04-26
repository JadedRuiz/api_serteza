<html>
    <table>
        <thead>
        <tr>
            <th>Clave</th>
            <th>Nombre</th>
            @foreach($conceptos as $concepto)
                <th style="text-align: center; background-color: lightseagreen;">{{$concepto->id_concepto}}</th>
                <th colspan="2" style="text-align: center; background-color: salmon">{{ $concepto->concepto }}</th>
            @endforeach
        </tr>
        <tr>
            <th></th>
            <th></th>
            @foreach($conceptos as $concepto)
                <th style="text-align: center;">Unidades</th>
                <th style="text-align: center;">Importe</th>
                <th style="text-align: center;">Saldo</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($empleados as $empleado)
            <tr>
                <td>{{ $empleado->id_empleado }}</td>
                <td>{{ $empleado->nombre }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</html>
