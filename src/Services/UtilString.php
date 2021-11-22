<?php

namespace App\Service;

use function Symfony\Component\String\b;
use function Symfony\Component\String\u;

class UtilString
{
    private $keysAnull = [];
    const PUT_SUSTITUT = '***';
    const ACENTUADAS = array('á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú');
    const SIN_ACENTUADAS = array('a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U');
    const CONVERT_TO   = array('__a__', '__e__', '__i__', '__o__', '__u__', '__A__', '__E__', '__I__', '__O__', '__U__', '__n__',  '__N__');

    public function toConsole(string $txt): string
    {
        return utf8_decode($txt);
    }

    /** */
    public function toUsername(string $txt): string
    {
        $txt = b($txt)->toUnicodeString('ISO-8859-1');
        $txt = $this->quitarAcentos($txt);
        $txt = $this->scapeCaracteres($txt);
        $txt = $this->trimSpacer($txt);
        $txt = $this->toLower($txt);
        $txt = $this->restaurarCaracteres($txt);
        return $txt;
    }

    /** */
    public function toPassword(string $txt): string
    {
        $txt = b($txt)->toUnicodeString('ISO-8859-1');
        $txt = $this->quitarAcentos($txt);
        $txt = $this->scapeCaracteres($txt);
        $txt = $this->trimSpacer($txt);
        $txt = $this->restaurarCaracteres($txt);
        return $txt;
    }

    /** */
    public function toKeyJson(string $txt)
    {
        $txt = strtolower($txt);
        $txt = $this->trimSpacer($txt, '_');
        $txt = $this->quitarAcentos($txt);
        return trim($txt);
    }

    /** */
    public function quitarAcentos(string $txt): string
    {
        return  str_replace($this::ACENTUADAS, $this::SIN_ACENTUADAS, $txt);
    }

    /** */
    public function initConfigForQuitarProhividas(array $morePals = [])
    {
        $rota = count($morePals);
        $morePalsNews = [];
        for ($i=0; $i < $rota; $i++) { 
            if(strlen($morePals[$i]) > 3) {
                $txt = trim( mb_strtolower($morePals[$i], 'UTF-8') );
                $txtSa = $this->quitarAcentos($txt);
                $morePalsNews[] = $txt;
                $morePalsNews[] = $txtSa;
            }
        }
        if(count($this->keysAnull) == 0) {
            $txtKeys = file_get_contents('data/keys_anull.txt');
            $this->keysAnull = explode(',', $txtKeys);
            $this->keysAnull = array_merge($this->keysAnull, $morePalsNews);
            $txtKeys = null;
        }
    }

    /** */
    public function quitarPalabrasProhividas(string $parrafo = '') :string
    {
        if($parrafo == '') {
            $parrafo = $this->getParrafoEjemplo();
        }
        $parrafo = strtolower($parrafo);
        $partes = explode(' ', $parrafo);

        $hasComa = false;
        $rota = count($partes);
        $newPals = [];
        $cantProhividas = 0;
        if($rota > 0) {
            for ($i=0; $i < $rota; $i++) { 
                $hasComa = false;
                if(strpos($partes[$i], ',') !== false) {
                    $hasComa = true;
                    $partes[$i] = str_replace(',', '', $partes[$i]);
                }
                $partes[$i] = trim($partes[$i]);
                $partes[$i] = $this->quitarProhividas($partes[$i]);
                if($partes[$i] != $this::PUT_SUSTITUT) {
                    if(strlen($partes[$i]) > 0) {
                        $newPals[] = ($hasComa) ? $partes[$i].',' : $partes[$i];
                    }
                }else{
                    $cantProhividas++;
                }
            }
        }

        $txt = implode(' ', $newPals);
        $txt = ucfirst($txt);
        if($cantProhividas > 0) {
            $txt = '***['.$cantProhividas.'] ' . $txt;
        }
        return $txt;
    }

    ///
    private function quitarProhividas(string $txt): string
    {
        $txtCopy = $txt;
        if(strlen($txt) > 3) {

            // Numeros de telefono
            $txtCopy = u($txtCopy)->replaceMatches('/[^A-Za-z0-9]++/', '')->toString();
            if(strlen($txtCopy) > 6) {
                $txtCopy = u($txtCopy)->match('/(\d+)/');
                if(count($txtCopy) > 0) {
                    return $this::PUT_SUSTITUT;
                }
            }

            // URLS
            $txtCopy = $txt;
            if(u($txtCopy)->containsAny(['http', 'https'])) {
                return $this::PUT_SUSTITUT;               
            }
            if(u($txtCopy)->containsAny('www')) {
                return $this::PUT_SUSTITUT;               
            }
            if(u($txtCopy)->containsAny('@')) {
                return $this::PUT_SUSTITUT;               
            }
            $txtCopy = u($txtCopy)->match('/\.(\w{3})/');
            if(count($txtCopy) > 0) {
                return $this::PUT_SUSTITUT;
            }

            // Quitar especiales.
            $txtCopy = $txt;
            if(in_array($txtCopy, $this->keysAnull)) {
                return $this::PUT_SUSTITUT;
            }
        }
        return $txt;
    }

    /** */
    private function scapeCaracteres(string $txt)
    {   
        $caracteresDeEscape = $this->setMasCaracteres();
        return str_replace($caracteresDeEscape['noPermitidas'], $caracteresDeEscape['convertTo'], $txt);
    }

    /** */
    private function restaurarCaracteres(string $txt)
    {
        $caracteresDeEscape = $this->setMasCaracteres();
        return str_replace($caracteresDeEscape['convertTo'], $caracteresDeEscape['noPermitidas'], $txt);
    }

    /** */
    private function setMasCaracteres() {
        
        $noPermitidas = $this::ACENTUADAS;
        $noPermitidas[] = 'ñ';
        $noPermitidas[] = 'Ñ';

        return [
            'noPermitidas' => $noPermitidas,
            'convertTo' => $this::CONVERT_TO
        ];
    }

    /** */
    private function trimSpacer(string $txt, string $glue = ''): string
    {
        $txt = str_replace(' ', '', $txt);
        $txt = trim($txt);
        return $txt;
    }

    /** */
    private function toLower(string $txt): string
    {
        return strtolower($txt);
    }

    /** */
    private function getParrafoEjemplo() :string {

        $parrafo = 'estas son notas que sirven para hacer un peell de '.
        'cosas que TENGAN UN NUMERO DE telefono como 3334567898 o con el '.
        'formato de 33-34-55-67-89 o 3456-7890 '.
        'al igual necesito quitar los enlaces que digan http://www.mi-pagina.com '.
        'o solo www.mi-pagina.com, o simplemente asi mi-pagina.com '.
        'no dejar pasar un whats o whatsapp como tampoco un facebook o simplemente face '.
        'instagram o otras redes sociales COMO twitter, messanger, al igual que palabras '.
        'inbox o email por lo tanto eliminar tambien cosas con @ por ejemplo aldo.gdl@hotmail.com '.
        'o la palabra chat no TENDRÍA QUE VENIR, asi como la palabra mensaje entre otras. '.
        'imaginemos que la refaccionaria tres hermanos manda este mensaje y les dice que '.
        'se contacten con aldo o juan jimenez o con acento jiménez ';
        return $parrafo;
    }
}