<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reporte del empleado</title>
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
            height: 240px;
        }
        .container-body-seguido-dos{
            border: 2px black solid;
            border-top: none;
        }
        .input{
            margin-bottom: 10px;
        }
        .display-block{
            display: inline-block;
        }
        .firma{
            height: 30px;
            border-bottom: 1px black solid;
        }
        .img_user{
            width: 100%;
            height: 100%;
        }
    </style>
</head>
<body>
    <header class="padre_header_uno">
        <div class="hijo_header">
            <img alt="" src="{{$reporte_empleado[0]->foto_empresa}}" class="logo_cliente">
        </div>
        <div class="hijo_header_dos fuente_titulos_Heebo titulo_grande">{{$reporte_empleado[0]->empresa}}</div>
    </header>
    <p class="fuente_titulos_Heebo" style="margin-left: 235px;">REPORTE DEL EMPLEADO</p>
    <div class="container-body padre_header">
        <div class="titulo_contenedor fuente_bold_monospace">DATOS GENERALES</div>
        <div class="cuerpo_contenedor">
            <div class="fotografia">
                <img  class="img_user" src="{{$reporte_empleado[0]->fotografia}}" alt="">
            </div>
            <div class="informacion_persona">
                <div class="input display-block" style="text-align: center;width: 90%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;">{{$reporte_empleado[0]->nombre}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Nombre completo</p>
                </div>
                <br>
                <div class="input display-block" style="text-align: center;width: 45%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;text-transform: uppercase;">{{$reporte_empleado[0]->rfc}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">RFC</p>
                </div>
                <div class="input display-block" style="text-align: center;width: 45%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;;text-transform: uppercase;">{{$reporte_empleado[0]->curp}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">CURP</p>
                </div>
                <br>
                <div class="input display-block" style="text-align: center;width: 45%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;">{{$reporte_empleado[0]->numero_seguro}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Imss</p>
                </div>
                <div class="input display-block" style="text-align: center;width: 45%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;">{{$reporte_empleado[0]->correo}}</div>
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
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;">{{$reporte_empleado[0]->calle}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Calle</p>
                </div>
                <div class="input display-block" style="text-align: center;width: 25%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;">{{$reporte_empleado[0]->numero_interior}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Número int.</p>
                </div>
                <div class="input display-block" style="text-align: center;width: 25%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;">{{$reporte_empleado[0]->numero_exterior}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Número ext.</p>
                </div>
                <div class="input display-block" style="text-align: center;width: 25%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;">{{$reporte_empleado[0]->cruzamiento_uno}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Cruzamiento int.</p>
                </div>
                <br>
                <div class="input display-block" style="text-align: center;width: 28%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;">{{$reporte_empleado[0]->cruzamiento_dos}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Cruzamiento ext.</p>
                </div>
                <div class="input display-block" style="text-align: center;width: 35%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;;text-transform: uppercase;">{{$reporte_empleado[0]->municipio}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Municipio</p>
                </div>
                <div class="input display-block" style="text-align: center;width: 35%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;;text-transform: uppercase;">{{$reporte_empleado[0]->estado}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Estado</p>
                </div>
                <br>
                <div class="input display-block" style="text-align: center;width: 48%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;;text-transform: uppercase;">{{$reporte_empleado[0]->colonia}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Colonia</p>
                </div>
                <div class="input display-block" style="text-align: center;width: 25%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;;text-transform: uppercase;">{{$reporte_empleado[0]->telefono}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Telefono</p>
                </div>
                <div class="input display-block" style="text-align: center;width: 25%;">
                    <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;;text-transform: uppercase;">{{$reporte_empleado[0]->telefono_dos}}</div>
                    <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Tel. secundario</p>
                </div>
            </div>
        </div>
    </div>
    <div class="container-body-seguido-dos">
        <div class="titulo_contenedor fuente_bold_monospace">DATOS DE CONTRATACIÓN </div>
        <div class="cuerpo_contenedor">
            <div class="input display-block" style="text-align: center;width: 23%;">
                <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;text-transform: uppercase;">{{$reporte_empleado[0]->nomina}}</div>
                <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Tipo nómina</p>
            </div>
            <div class="input display-block" style="text-align: center;width: 23%;">
                <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;">{{$reporte_empleado[0]->puesto}}</div>
                <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Puesto</p>
            </div>
            <div class="input display-block" style="text-align: center;width: 23%;">
                <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;">{{$reporte_empleado[0]->sucursal}}</div>
                <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Sucursal</p>
            </div>
            <div class="input display-block" style="text-align: center;width: 29%;">
                <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;"></div>
                <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Registro patronal</p>
            </div>
            <br>
            <div class="input display-block" style="text-align: center;width: 25%;">
                <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;text-transform: uppercase;">{{$reporte_empleado[0]->banco}}</div>
                <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Banco</p>
            </div>
            <div class="input display-block" style="text-align: center;width: 50%;">
                <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;text-transform: uppercase;height: auto;">{{$reporte_empleado[0]->tipocontrato}}</div>
                <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Tipo contrato</p>
            </div>
            <div class="input display-block" style="text-align: center;width: 23%;">
                <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;text-transform: uppercase;height: auto;">{{date("d-m-Y",strtotime($reporte_empleado[0]->fecha_ingreso))}}</div>
                <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Fecha de ingreso</p>
            </div>
            <br>
            <div class="input display-block" style="text-align: center;width: 25%;">
                <div type="text" class="firma fuente_normal_Heebo" style="margin: 0px;padding: 0px;text-transform: uppercase;height: auto;">{{date("d-m-Y",strtotime($reporte_empleado[0]->fecha_antiguedad))}}</div>
                <p class="fuente_textos_monospace" style="margin: 0px;padding: 0px;">Fecha de antiguedad</p>
            </div>
        </div>
    </div>
</body>
</html>