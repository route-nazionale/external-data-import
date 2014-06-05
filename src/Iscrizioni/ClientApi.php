<?php
/**
 * User: lancio
 * Date: 05/06/14
 * Time: 22:33
 */

namespace Iscrizioni;

use Psr\Log\LoggerInterface;

class ClientApi
{
    private $decoder = "RSA";
    private $logger;
    private $privateKey;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function setPrivateKey($privateRSAkey)
    {
        $this->privateKey = $privateRSAkey;
    }

    public function remoteCall($url)
    {
        $this->logger->addInfo('Call '.$url);

        $response = file_get_contents($url);

        $token_json = json_decode($this->decode($response));

        return $token_json;
    }

    public function setDecoder($decoder)
    {
        $this->decoder = $decoder;
        return $this;
    }

    private function decode($response)
    {
        switch ($this->decoder) {
            case 'RSA':
                $content = $this->decodeRsa($response);
                break;
            case 'AES':
                $content = $this->decodeAES($response);
                break;
            default:
                $content = $response;
                break;
        }

        return $content;
    }

    private function decodeAES($testo)
    {
        $this->logger->addDebug('Testo b64: '.$testo);
        $crypt_text = base64_decode($testo);
        //$this->logger->addDebug('Testo binario: '.$crypt_text. ' size '.strlen($crypt_text));

        $iv = substr($crypt_text,0,32);
        $testoRidotto = substr($crypt_text,32);

        $cipher = new \Crypt_AES(CRYPT_AES_MODE_CBC);
        $cipher->setKey($this->currentAESkey);
        $cipher->setIV($iv);
        $decoded = $cipher->decrypt($testoRidotto);

        $this->logger->addDebug('Testo pulito: '.$decoded);

        return $decoded;
    }

    private function decodeRSA($testo)
    {
        $this->logger->addDebug('Testo b64: '.$testo);
        $crypt_text = base64_decode($testo);

        $rsa = new \Crypt_RSA();
        $rsa->loadKey($this->privateKey);

        $decoded = $rsa->decrypt($crypt_text);

        $this->logger->addDebug('Testo pulito: '.$decoded);

        return $decoded;
    }

    public function setAESkey($currentAESkey )
    {
        $this->currentAESkey = $currentAESkey;
    }

}
