<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reporte para el contratado</title>
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
            width: 30%;
            display: inline-block;
            height: 80px;
            margin-left: 10px;
            margin-bottom: -30px;
        }
        .hijo_header_dos{
            margin-top:0px;
            width: 65%;
            display: inline-block;
            text-align: center;
            margin-right: 10px;
            margin-bottom: -30px;
        }
        .logo_cliente{
            width: 100%;
            height: 100%;
        }
        .container-body{
            margin-top: 15px;
            height: 240px;
        }
        .titulo_contenedor{
            border-bottom: 2px black solid;
            background-color: rgba(167, 167, 167, 0.877);
            padding: 5px 5px;
            font-size: 14px;
        }
        .cuerpo_contenedor{
            padding: 10px 10px;
            position: relative;
        }
        .fotografia{
            width: 25%;
            height: 178px;
            border: 1px black solid;
            position: absolute;
            top: 10;
            left: 10;
            text-align: center;
            overflow: hidden;
        }
        .informacion_persona{
            width: 77%;
            position: absolute;
            top: 12;
            left: 150;
        }
        .container-body-seguido{
            border: 2px black solid;
            border-top: none;
        }
        .firma-contenedor{
            text-align: center;
            margin-top: 18px;
        }
        .hijo_firma{
            display: inline-block;
            width: 32%;
        }
        .firma{
            height: 30px;
            border-bottom: 1px black solid;
        }
        .display-block{
            display: inline-block;
        }
        .img_user{
            width: 100%;
            height: 100%;
        }
        .input div {
            padding: 0;
            margin-top: 0px;
            height: auto;
            word-wrap: break-word;
        }
        .table{
            width: 100%;
        }
        .table tr {
            text-align: center;
        }
        .input{
            margin-bottom: 10px;
        }
        .domicilio{
            border: 1px transparent solid;
        }
    </style>
</head>
<body>
    <header class="padre_header_uno">
        <div class="hijo_header">
            <img alt="" src="{{$detalle_contratacion[0]->name_foto}}" class="logo_cliente">
        </div>
        <div class="hijo_header_dos fuente_titulos_Heebo titulo_grande">{{$detalle_contratacion[0]->cliente}}</div>
    </header>
    <p class="fuente_titulos_Heebo" style="margin-left: 230px;">REPORTE DE CONTRATACIÓN</p>
    <div class="container-body padre_header">
        <div class="titulo_contenedor fuente_bold_monospace">DATOS GENERALES</div>
        <div class="cuerpo_contenedor">
            <div class="fotografia">
                <img  class="img_user" src="{{$detalle_contratacion[0]->fotografia}}" alt="">
            </div>
            <div class="informacion_persona">
                <div class="input display-block" style="text-align: center;width: 90%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;">{{$detalle_contratacion[0]->nombre}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Nombre completo</p>
                </div>
                <br>
                <div class="input display-block" style="text-align: center;width: 45%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;text-transform: uppercase;">{{$detalle_contratacion[0]->rfc}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">RFC</p>
                </div>
                <div class="input display-block" style="text-align: center;width: 45%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;;text-transform: uppercase;">{{$detalle_contratacion[0]->curp}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">CURP</p>
                </div>
                <br>
                <div class="input display-block" style="text-align: center;width: 45%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;">{{$detalle_contratacion[0]->numero_seguro}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Imss</p>
                </div>
                <div class="input display-block" style="text-align: center;width: 45%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;">{{$detalle_contratacion[0]->correo}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Correo electrónico</p>
                </div>
            </div>
        </div>
    </div>
    <div class="container-body-seguido">
        <div class="titulo_contenedor fuente_bold_monospace">DOMICILIO</div>
        <div class="cuerpo_contenedor">
            <div class="domicilio">
                <div class="input display-block" style="text-align: center;width: 23%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;">{{$detalle_contratacion[0]->calle}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Calle</p>
                </div>
                <div class="input display-block" style="text-align: center;width: 25%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;">{{$detalle_contratacion[0]->numero_interior}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Número int.</p>
                </div>
                <div class="input display-block" style="text-align: center;width: 25%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;">{{$detalle_contratacion[0]->numero_exterior}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Número ext.</p>
                </div>
                <div class="input display-block" style="text-align: center;width: 25%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;">{{$detalle_contratacion[0]->cruzamiento_uno}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Cruzamiento int.</p>
                </div>
                <br>
                <div class="input display-block" style="text-align: center;width: 28%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;">{{$detalle_contratacion[0]->cruzamiento_dos}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Cruzamiento ext.</p>
                </div>
                <div class="input display-block" style="text-align: center;width: 35%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;;text-transform: uppercase;">{{$detalle_contratacion[0]->municipio}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Municipio</p>
                </div>
                <div class="input display-block" style="text-align: center;width: 35%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;;text-transform: uppercase;">{{$detalle_contratacion[0]->estado}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Estado</p>
                </div>
                <br>
                <div class="input display-block" style="text-align: center;width: 33%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;;text-transform: uppercase;">{{$detalle_contratacion[0]->colonia}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Colonia</p>
                </div>
                <div class="input display-block" style="text-align: center;width: 33%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;;text-transform: uppercase;">{{$detalle_contratacion[0]->telefono}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Telefono</p>
                </div>
                <div class="input display-block" style="text-align: center;width: 33%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;;text-transform: uppercase;">{{$detalle_contratacion[0]->telefono_dos}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Telefono secundario</p>
                </div>
            </div>
        </div>
    </div>
    <div class="container-body-seguido">
        <div class="titulo_contenedor fuente_bold_monospace">DATOS DE CONTRATACIÓN <div class="fuente_bold_monospace" style="float: right;">FECHA DE CONTRATACIÓN: {{date("d-m-Y",strtotime($detalle_contratacion[0]->fecha_alta))}}</div></div>
        <div class="cuerpo_contenedor">
            <table class="table">
                <thead style="border: 1px black solid;">
                    <tr class="fuente_titulos_Heebo">
                        <th colspan="1">EMPRESA</th>
                        <th colspan="1">DEPARTAMENTO</th>
                        <th colspan="1">PUESTO</th>
                    </tr>
                </thead>
                <tbody style="border-left: 1px black solid;border-bottom: 1px black solid;border-right: 1px black solid;">
                    <tr class="fuente_normal_Heebo" style="border: 1px rgb(70, 67, 67) solid;">
                        <td>{{$detalle_contratacion[0]->empresa}}</td>
                        <td>{{$detalle_contratacion[0]->departamento}}</td>
                        <td>{{$detalle_contratacion[0]->puesto}}</td>
                    </tr>
                </tbody>
            </table>
            <div style="width: 100%;margin-top: 8px;padding-left: 550px;">
                <div class="fuente_titulos_Heebo display-block">SUELDO: </div>
                <div class="fuente_normal_Heebo display-block">{{$detalle_contratacion[0]->sueldo}}</div>
            </div>
            <div class="fuente_titulos_Heebo" style="margin: 0px;padding: 0px 17px 0px 17px;margin-top: 5px;">Obervaciones:
                <textarea name="" style="height: 100px;"></textarea>
            </div>
            
        </div>
    </div>
    <div class="firma-contenedor">
        <div class="firma_empleado hijo_firma">
            <p class="fuente_titulos_Heebo">Firma</p>
            <p class="fuente_textos_monospace">{{$detalle_contratacion[0]->nombre}}</p>
            <div class="firma"></div>
        </div>
        <div class="firma_usuario hijo_firma">
            <p class="fuente_titulos_Heebo">Firma</p>
            <p class="fuente_textos_monospace">{{$detalle_contratacion[0]->usuario}}</p>
            <div class="firma"></div>
        </div>
        <div class="firma_rh hijo_firma">
            <p class="fuente_titulos_Heebo">Firma</p>
            <p class="fuente_textos_monospace">RECURSOS HUMANOS</p>
            <div class="firma"></div>
        </div>
    </div>
</body>
</html>
    