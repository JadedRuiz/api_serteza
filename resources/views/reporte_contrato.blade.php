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
            width: 40%;
            display: inline-block;
            height: 100px;
            margin-left: 10px;
            margin-bottom: -30px;
        }
        .hijo_header_dos{
            margin-top:0px;
            width: 55%;
            display: inline-block;
            text-align: center;
            margin-right: 10px;
            margin-bottom: -30px;
        }
        .logo_cliente{
            width: 100%;
            height: 100%;
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
    <p class="fuente_titulos_Heebo" style="margin-left: 380px;">REPORTE DE CONTRATACIÓN</p>
    <div class="info_general">
        <div class="fecha_folio dib" style="font-size: 12px;">
            <div class="dib mt-5 fuente_titulos_Heebo">Fecha: <div class="fecha_valor dib vat ww fuente_normal_Heebo">{{$reporte_contrato[0]->fecha_movimiento}}</div></div>
            <br>
            <div class="dib mt-5 fuente_titulos_Heebo">Fecha de impresión: <div class="fecha_valor dib vat ww fuente_normal_Heebo">{{$reporte_contrato[0]->fecha_hoy}}</div></div>
            <br>
            <div class="dib mt-5 fuente_titulos_Heebo">Usuario: <div class="fecha_valor dib vat ww fuente_normal_Heebo">{{$reporte_contrato[0]->usuario}}</div></div>
            <br>
            <div class="dib mt-5 fuente_titulos_Heebo">No. de trabajadores: <div class="folio_valor dib vat fuente_normal_Heebo">{{count($reporte_contrato[0]->detalle)}}</div></div>
            <br>
            <div class="dib mt-5 fuente_titulos_Heebo">Folio: <div class="folio_valor dib vat ww fuente_normal_Heebo">{{$reporte_contrato[0]->folio}}</div></div>
        </div>
    </div>
    <div class="table">
        <table class="w100">
            <thead class="bb">
                <tr class="fuente_titulos_Heebo" style="font-size: 12px;">
                    <th colspan="1" style="text-align: left;">Nombre</th>
                    <th colspan="1">Fecha ingreso</th>
                    <th colspan="1" style="text-align: left;">Empresa/Sucursal</th>
                    <th colspan="1" style="text-align: left;">Dept/Puesto</th>
                    <th colspan="1">Sueldo diario</th>
                    <th colspan="1">Sueldo neto</th>
                    <th colspan="2" style="text-align: left;">Descripción</th>
                </tr>
            </thead>
            <tbody>
                @if (count($reporte_contrato[0]->detalle)>0)
                    @foreach ($reporte_contrato[0]->detalle as $trabajador)
                        <tr class="fuente_normal_Heebo text-center" style="font-size: 10px;">
                            <td colspan="1" style="text-align: left;">{{$trabajador->nombre.' '.$trabajador->apellido_paterno.' '.$trabajador->apellido_materno}}</td>
                            <td colspan="1">{{$trabajador->fecha_alta}}</td>
                            <td colspan="1" style="text-transform: uppercase;text-align: left;">{{$trabajador->empresa}}<br>{{$trabajador->sucursal}}</td>
                            <td colspan="1" style="text-transform: uppercase;text-align: left;">{{$trabajador->departamento}}<br>{{$trabajador->puesto}}</td>
                            <td colspan="1" style="text-transform: uppercase;">${{$trabajador->sueldo}}</td>
                            <td colspan="1">${{$trabajador->sueldo_neto}}</td>
                            <td colspan="2" style="text-align: left;">{{$trabajador->observacion}}</td>
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
            <p class="fuente_titulos_Heebo" style="font-size: 14px;">Firma</p>
            <div class="linea_firma"></div>
            <div class="nombre_firma fuente_textos_monospace" style="font-size: 12px;">{{$reporte_contrato[0]->usuario}}</div>
        </div>
        <div class="firma_rh dib text-center">
            <p class="fuente_titulos_Heebo">Firma</p>
            <div class="linea_firma"></div>
            <div class="nombre_firma fuente_textos_monospace" style="font-size: 12px;">RECURSOS HUMANOS</div>
        </div>
    </div>
</body>
</html>