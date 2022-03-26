<?php


class Sello {

    public static function sellar($datos)
    {
        //Variables globales del método
        $dat = $datos["datos"];
        $llaves = explode("|", $datos);
        $key = $datos['credenciales']["key"];
        $cer = $datos['credenciales']["cer"];
        $numcer = $datos['credenciales']["no_cer"];

        $private = openssl_pkey_get_private(file_get_contents($key));
        $certificado = str_replace(array('\n', '\r'), '', base64_encode(file_get_contents($cer)));

        $pruebaxml = new gen_xml();

        $cfdi = $pruebaxml->gen_doc($dat);
        //$cfdi='cachktest_0.xml';

        $xdoc = new DomDocument();
        $xdoc->loadXML($cfdi) or die("XML invalido");

        $XSL = new DOMDocument();
        $XSL->load('../utilerias/cadenaoriginal_3_2.xslt');

        $proc = new XSLTProcessor;
        $proc->importStyleSheet($XSL);

        $cadena_original = $proc->transformToXML($xdoc);
        $datos = explode("|", $cadena_original);
        $cadena_original = "";

        for ($i = 1; $i < count($datos); $i++) {
            $cadena_original = $cadena_original . "|" . trim($datos[$i]);
        }
        openssl_sign($cadena_original, $sig, $private);
        $sello = base64_encode($sig);

        $c = $xdoc->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/3', 'Comprobante')->item(0);
        $c->setAttribute('sello', $sello);
        $c->setAttribute('certificado', $certificado);
        $c->setAttribute('noCertificado', $numcer);
        $xmlsello = $xdoc->saveXML();
        file_put_contents($numcer . ".xml", $xmlsello);
        return $xmlsello;
    }
}
?>