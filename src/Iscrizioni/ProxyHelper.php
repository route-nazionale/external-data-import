<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 07/05/14 - 22:25
 * 
 */

namespace Iscrizioni;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class ProxyHelper {

    private $baseUrl;
    private $currentToken;
    private $currentAESkey;
    private $log;
    private $privateKey;

    function __construct($baseUrl) {
        $this->baseUrl = $baseUrl;
        $log = new Logger('proxy');
        $handler = new StreamHandler('php://stdout',Logger::DEBUG);
        $log->pushHandler($handler);
        $this->log = $log;
    }

    public function setLogger(Logger $logger){
        $this->log = $logger;
    }

    public function login($user,$password)
    {
        $response = $this->remoteCall($this->baseUrl.'/login2/u/'.$user.'/p/'.$password);

        $token_json = json_decode($this->decodeRSA($response));

        if ( $token_json[0]->status == 'connected' ) {
            $this->log->addInfo('Received token '.$token_json[0]->token);
            $this->currentToken = $token_json[0]->token;
        } else {
            $this->log->addError($response);
        }

    }

    public function aesSetup()
    {
        $response = $this->remoteCall($this->baseUrl.'/getChiave/t/'.$this->currentToken);

        $aes_json = json_decode($this->decodeRSA($response));

        if ( $aes_json[0]->status == 'ok' ) {
            $this->log->addInfo('Received aes key '.$aes_json[0]->key);
            $this->currentAESkey = $aes_json[0]->key;
        } else {
            $this->log->addError($response);
        }

    }

    public function getGruppi($all = false){
        $response = $this->remoteCall($this->baseUrl.'/getGruppi/start/0/token/'.$this->currentToken);
        $gruppi = json_decode($this->decodeAES($response));

        $gruppi_totali = array();

        $gruppi_totali = array_merge($gruppi_totali,$gruppi[0]->gruppi);

        $i = count($gruppi_totali);
        while ( $all && $gruppi[0]->other == 'ok' ) {
            $x = $from + $i;
            $response = $this->remoteCall($this->baseUrl.'/getGruppi/start/'.$x.'/token/'.$this->currentToken);
            $gruppi = json_decode($this->decodeAES($response));
            $gruppi_totali = array_merge($gruppi_totali,$gruppi[0]->gruppi);
            $i = count($gruppi_totali);
        }

        return $gruppi_totali;
    }

    public function getRagazzi($from,$length){
        $response = $this->remoteCall($this->baseUrl.'/getRagazzi/start/'.$from.'/token/'.$this->currentToken);
        $ragazzi = json_decode($this->decodeAES($response));

        $ragazzi_totali = array();

        if ( count($ragazzi[0]->partecipanti[0]) > 0 ) {
            $ragazzi_totali = array_merge($ragazzi_totali,$ragazzi[0]->partecipanti);
        }

        $i = count($ragazzi_totali);
        while ( $ragazzi[0]->other == 'ok' && $i < $length ) {
            $x = $from + $i;
            $response = $this->remoteCall($this->baseUrl.'/getRagazzi/start/'.$x.'/token/'.$this->currentToken);
            $ragazzi = json_decode($this->decodeAES($response));

            if ( count($ragazzi[0]->partecipanti[0]) > 0 ) {
                $ragazzi_totali = array_merge($ragazzi_totali,$ragazzi[0]->partecipanti);
            }

            $i = count($ragazzi_totali);
        }

        $this->log->addDebug('Downloaded '.$i);

        return $ragazzi_totali;
    }

    public function getCapi($from,$length){
        $response = $this->remoteCall($this->baseUrl.'/getCapi/start/'.$from.'/token/'.$this->currentToken);
        $capi = json_decode($this->decodeAES($response));

        $capi_totali = array();

        if ( count($capi[0]->partecipanti[0]) > 0 ) {
            $capi_totali = array_merge($capi_totali,$capi[0]->partecipanti);
        }

        $i = count($capi_totali);
        while ( $capi[0]->other == 'ok' && $i < $length ) {

            $x = $from + $i;
            $response = $this->remoteCall($this->baseUrl.'/getCapi/start/'.$x.'/token/'.$this->currentToken);
            $capi = json_decode($this->decodeAES($response));

            if ( count($capi[0]->partecipanti[0]) > 0 ) {
                $capi_totali = array_merge($capi_totali,$capi[0]->partecipanti);
            }

            $i = count($capi_totali);
        }

        return $capi_totali;
    }

    public function setPrivateKey($pkey) {
        $this->privateKey = $pkey;
    }

    private function remoteCall($url){

        $this->log->addInfo('Call '.$url);

        $response = file_get_contents($url);
        return $response;
    }

    private function decodeAES($testo){

        $this->log->addDebug('Testo b64: '.$testo);
        $crypt_text = base64_decode($testo);
        //$this->log->addDebug('Testo binario: '.$crypt_text. ' size '.strlen($crypt_text));

        $iv = substr($crypt_text,0,32);
        $testoRidotto = substr($crypt_text,32);

        $cipher = new \Crypt_AES(CRYPT_AES_MODE_CBC);
        $cipher->setKey($this->currentAESkey);
        $cipher->setIV($iv);
        $decoded = $cipher->decrypt($testoRidotto);

        $this->log->addDebug('Testo pulito: '.$decoded);

        return $decoded;
    }

    private function decodeRSA($testo){

        $this->log->addDebug('Testo b64: '.$testo);
        $crypt_text = base64_decode($testo);
        //$this->log->addDebug('Testo binario: '.$crypt_text. ' size '.strlen($crypt_text));

        /*
        $passphrase = null;
        $res = openssl_get_privatekey($this->privateKey,$passphrase);

        if ( openssl_private_decrypt($crypt_text,$decoded,$res,OPENSSL_PKCS1_OAEP_PADDING) ) {
            $this->log->addInfo('String decrypt : '.$decoded);
            return $decoded;
        } else {
            // lets assume you just called an openssl function that failed
            while ($msg = openssl_error_string())
                $this->log->addError($msg);
        }
        */

        $rsa = new \Crypt_RSA();
        $rsa->loadKey($this->privateKey);

        $decoded = $rsa->decrypt($crypt_text);

        $this->log->addDebug('Testo pulito: '.$decoded);

        return $decoded;


    }


}