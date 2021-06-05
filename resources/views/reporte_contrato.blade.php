<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reporte de contratación</title>
    <style>
        .fuente_normal_Heebo{
            font-family: 'Heebo', sans-serif;
            font-weight: normal;
        }
        .fuente_titulos_Heebo{
            font-family: 'Heebo', sans-serif;
            font-weight: bold;
        }
        .fuente_textos_monospace{
            font-family: 'Teko', sans-serif;
            font-weight: normal;
        }
        .fuente_bold_monospace{
            font-family: 'Teko', sans-serif;
            font-weight: bold;
        }
        .titulo_grande{
            font-size: 30px;
            color: negro;
        }
        .padre_header{
            border: 2px black solid;
        }
        .padre_header_uno{
            border-bottom: 2px black solid;
            padding-bottom: 0px;
            margin-bottom: -10px;
            height: auto;
        }
        .hijo_header{
            margin-top:20px;
            width: 55%;
            display: inline-block;
            height: 130px;
            margin-left: 10px;
            margin-bottom: -30px;
        }
        .hijo_header_dos{
            margin-top:0px;
            width: 35%;
            display: inline-block;
            text-align: center;
            margin-right: 10px;
            margin-bottom: -30px;
        }
        .logo_cliente{
            width: 100%;
            height: 130px;
        }
        .dib{
            display: inline-block;
        }
        .vat{
            vertical-align: top;
        }
        .mt-5{
            margin-top: 15px;
        }
        .mt-10{
            margin-top: 30px;
        }
        .fecha_folio{
            width: 59%;
        }
        .usuario_trabajador{
            width: 30%;
        }
        .bb{
            border-top: 2px black solid;
            border-bottom: 2px black solid;
        }
        .w100{
            width: 100%;
        }
        .text-center{
            text-align: center;
        }
        .firma_rh{
            width: 49%;
        }
        .firma_user{
            width: 49%;
        }
        .linea_firma{
            border-bottom: 1px black solid;
            width: 80%;
            margin-left: 40px;
            height: 30px;
        }
    </style>
</head>
<body>
    <header class="padre_header_uno">
        <div class="hijo_header">
            <img alt="" src="{{$reporte_contrato[0]->foto_cliente}}" class="logo_cliente">
        </div>
        <div class="hijo_header_dos fuente_titulos_Heebo titulo_grande">{{$reporte_contrato[0]->cliente}}</div>
    </header>
    <p class="fuente_titulos_Heebo" style="margin-left: 230px;">REPORTE DE CONTRATACIÓN</p>
    <div class="info_general mt-10">
        <div class="fecha_folio dib">
            <div class="fecha dib vat fuente_titulos_Heebo">Fecha: <div class="fecha_valor dib vat ww fuente_normal_Heebo">{{$reporte_contrato[0]->fecha_contratacion}}</div></div>
            <br>
            <div class="folio dib vat mt-10 fuente_titulos_Heebo">Folio: <div class="folio_valor dib vat ww fuente_normal_Heebo">{{$reporte_contrato[0]->folio}}</div></div>
        </div>
        <div class="usuario_trabajador dib">
            <div class="fecha dib va fuente_titulos_Heebo">Usuario: <div class="fecha_valor dib vat ww fuente_normal_Heebo">{{$reporte_contrato[0]->usuario}}</div></div>
            <br>
            <div class="folio dib vat mt-10 fuente_titulos_Heebo">No. de trabajadores: <div class="folio_valor dib vat fuente_normal_Heebo">{{count($reporte_contrato[0]->detalle)}}</div></div>
        </div>
    </div>
    <div class="table">
        <table class="w100">
            <thead class="bb">
                <tr class="fuente_titulos_Heebo">
                    <th colspan="1">NOMBRE DEL EMPLEADO</th>
                    <th colspan="1">EMPRESA</th>
                    <th colspan="1">DEPARTAMENTO</th>
                    <th colspan="1">PUESTO</th>
                    <th colspan="1">SUELDO</th>
                </tr>
            </thead>
            <tbody>
                @if (count($reporte_contrato[0]->detalle)>0)
                    @foreach ($reporte_contrato[0]->detalle as $trabajador)
                        <tr class="fuente_normal_Heebo text-center">
                            <td colspan="1">{{$trabajador->nombre.' '.$trabajador->apellido_paterno.' '.$trabajador->apellido_materno}}</td>
                            <td colspan="1">{{$trabajador->empresa}}</td>
                            <td colspan="1" style="text-transform: uppercase;">{{$trabajador->departamento}}</td>
                            <td colspan="1" style="text-transform: uppercase;">{{$trabajador->puesto}}</td>
                            <td colspan="1" style="text-transform: uppercase;">{{$trabajador->sueldo}}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4">No hay trabajadores en está contratación</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    <div class="firmas mt-10 text-center">
        <div class="firma_user dib">
            <p class="fuente_titulos_Heebo">Firma</p>
            <div class="linea_firma"></div>
            <div class="nombre_firma fuente_textos_monospace">{{$reporte_contrato[0]->usuario}}</div>
        </div>
        <div class="firma_rh dib text-center">
            <p class="fuente_titulos_Heebo">Firma</p>
            <div class="linea_firma"></div>
            <div class="nombre_firma fuente_textos_monospace">RECURSOS HUMANOS</div>
        </div>
    </div>
</body>
</html>