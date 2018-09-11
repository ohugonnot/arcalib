<?php

namespace AppBundle\Services;

class MoneticoService
{
    const MONETICOPAIEMENT_CTLHMAC = "V4.0.sha1.php--[CtlHmac%s%s]-%s";
    const MONETICOPAIEMENT_CTLHMACSTR = "CtlHmac%s%s";
    const MONETICOPAIEMENT_PHASE2BACK_RECEIPT = "version=2\ncdr=%s";
    const MONETICOPAIEMENT_PHASE2BACK_MACOK = "0";
    const MONETICOPAIEMENT_PHASE2BACK_MACNOTOK = "1\n";
    const MONETICOPAIEMENT_PHASE2BACK_FIELDS = "%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*";
    const MONETICOPAIEMENT_PHASE1GO_FIELDS = "%s*%s*%s%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s*%s";
    const MONETICOPAIEMENT_URLPAYMENT = "paiement.cgi";
    const MONETICOPAIEMENT_VERSION = "3.0";
    const MONETICOPAIEMENT_URLSERVER = "https://p.monetico-services.com/test/";

    public $sNumero;	// Numero du TPE - EPT Number (Ex : 1234567)
    public $sVersion;
    public $sCodeSociete;	// Code Societe - Company code (Ex : companyname)
    public $sLangue;	// Langue - Language (Ex : FR, DE, EN, ..)
    public $sUrlOK;		// Url de retour OK - Return URL OK
    public $sUrlKO;		// Url de retour KO - Return URL KO
    public $sUrlPaiement;	// Url du serveur de paiement - Payment Server URL (Ex : https://p.monetico-services.com/paiement.cgi)

    private $_sCle;
    private $_sUsableKey;


    public function __construct($MONETICOPAIEMENT_KEY, $MONETICOPAIEMENT_EPTNUMBER, $MONETICOPAIEMENT_COMPANYCODE, $MONETICOPAIEMENT_URLOK, $MONETICOPAIEMENT_URLKO, $MONETICOPAIEMENT_LANGUE)
    {
        $this->_sCle = $MONETICOPAIEMENT_KEY;
        $this->_sUsableKey = $this->_getUsableKey();

        $this->sLangue = $MONETICOPAIEMENT_LANGUE;
        $this->sVersion = self::MONETICOPAIEMENT_VERSION;
        $this->sNumero = $MONETICOPAIEMENT_EPTNUMBER;
        $this->sUrlPaiement = MoneticoService::MONETICOPAIEMENT_URLSERVER . MoneticoService::MONETICOPAIEMENT_URLPAYMENT;
        $this->sCodeSociete = $MONETICOPAIEMENT_COMPANYCODE;
        $this->sUrlOK = $MONETICOPAIEMENT_URLOK;
        $this->sUrlKO = $MONETICOPAIEMENT_URLKO;
    }

    public function createForm($sReference = '', $sMontant = 0, $sDevise = "EUR", $sEmail = "test@test.com", $sTexteLibre = "",  $sOptions = "")
    {
        $CtlHmac = sprintf(
            self::MONETICOPAIEMENT_CTLHMAC,
            $this->sVersion,
            $this->sNumero,
                $this->computeHmac(sprintf(
                    self::MONETICOPAIEMENT_CTLHMACSTR,
                    $this->sVersion,
                    $this->sNumero
                ))
        );

        $sDate = date("d/m/Y:H:i:s");

        $phase1go_fields = sprintf(
            self::MONETICOPAIEMENT_PHASE1GO_FIELDS,
            $this->sNumero,
            $sDate,
            $sMontant,
            $sDevise,
            $sReference,
            $sTexteLibre,
            $this->sVersion,
            $this->sLangue,
            $this->sCodeSociete,
            $sEmail,
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            "",
            $sOptions
        );

        $sMAC = $this->computeHmac($phase1go_fields);

        return [
            "oEpt" => $this,
            "reference" => $sReference,
            "montant" => $sMontant,
            "devise" => $sDevise,
            "textelibre" => $this->HtmlEncode($sTexteLibre),
            "date" => $sDate,
            "email" => $sEmail,
            "options" => $sOptions,
            "mac" => $sMAC,
            'CtlHmac', $CtlHmac,
            "data" => $phase1go_fields
        ];
    }


    public static function getMethode()
    {
        if ($_SERVER["REQUEST_METHOD"] == "GET")
            return $_GET;

        if ($_SERVER["REQUEST_METHOD"] == "POST")
            return $_POST;

        die ('Invalid REQUEST_METHOD (not GET, not POST).');
    }

    // ----------------------------------------------------------------------------
    // function HtmlEncode
    //
    // IN:  chaine a encoder / String to encode
    // OUT: Chaine encod�e / Encoded string
    //
    // Description: Encode special characters under HTML format
    //                           ********************
    //              Encodage des caract�res sp�ciaux au format HTML
    // ----------------------------------------------------------------------------
    public static function HtmlEncode($data)
    {
        $SAFE_OUT_CHARS = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890._-";
        $encoded_data = "";
        $result = "";
        for ($i=0; $i<strlen($data); $i++)
        {
            if (strchr($SAFE_OUT_CHARS, $data{$i})) {
                $result .= $data{$i};
            }
            else if (($var = bin2hex(substr($data,$i,1))) <= "7F"){
                $result .= "&#x" . $var . ";";
            }
            else
                $result .= $data{$i};

        }
        return $result;
    }

    private function _getUsableKey(){

        $hexStrKey  = substr($this->_sCle, 0, 38);
        $hexFinal   = "" . substr($this->_sCle, 38, 2) . "00";

        $cca0=ord($hexFinal);

        if ($cca0>70 && $cca0<97)
            $hexStrKey .= chr($cca0-23) . substr($hexFinal, 1, 1);
        else {
            if (substr($hexFinal, 1, 1)=="M")
                $hexStrKey .= substr($hexFinal, 0, 1) . "0";
            else
                $hexStrKey .= substr($hexFinal, 0, 2);
        }

        $this->_sUsableKey = pack("H*", $hexStrKey);
        return $this->_sUsableKey;
    }

    // ----------------------------------------------------------------------------
    //
    // Fonction / Function : computeHmac
    //
    // Renvoie le sceau HMAC d'une chaine de donn�es
    // Return the HMAC for a data string
    //
    // ----------------------------------------------------------------------------

    public function computeHmac($sData) {

        return strtolower(hash_hmac("sha1", $sData, $this->_getUsableKey()));

        // If you don't have PHP 5 >= 5.1.2 and PECL hash >= 1.1
        // you may use the hmac_sha1 function defined below
        //return strtolower($this->hmac_sha1($this->_sUsableKey, $sData));
    }

    // ----------------------------------------------------------------------------
    //
    // Fonction / Function : hmac_sha1
    //
    // RFC 2104 HMAC implementation for PHP >= 4.3.0 - Creates a SHA1 HMAC.
    // Eliminates the need to install mhash to compute a HMAC
    // Adjusted from the md5 version by Lance Rushing .
    //
    // Impl�mentation RFC 2104 HMAC pour PHP >= 4.3.0 - Cr�ation d'un SHA1 HMAC.
    // Elimine l'installation de mhash pour le calcul d'un HMAC
    // Adapt�e de la version MD5 de Lance Rushing.
    //
    // ----------------------------------------------------------------------------

    public function hmac_sha1 ($key, $data) {

        $length = 64; // block length for SHA1
        if (strlen($key) > $length) { $key = pack("H*",sha1($key)); }
        $key  = str_pad($key, $length, chr(0x00));
        $ipad = str_pad('', $length, chr(0x36));
        $opad = str_pad('', $length, chr(0x5c));
        $k_ipad = $key ^ $ipad ;
        $k_opad = $key ^ $opad;

        return sha1($k_opad  . pack("H*",sha1($k_ipad . $data)));
    }
}