<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 07/05/14 - 22:25
 *
 */

namespace Iscrizioni;

use Monolog\Logger;
use Psr\Log\LoggerInterface;

class ProxyHelper
{
    private $baseUrl;
    private $client;
    private $currentToken;
    private $logger;

    public function __construct(ClientApi $client, $baseUrl)
    {
        $this->baseUrl = $baseUrl;
        $this->client = $client;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    public function login($user,$password)
    {
        $this->client->setDecoder("RSA");
        $token_json = $this->client->remoteCall($this->baseUrl.'/login2/u/'.$user.'/p/'.$password);

        if ($token_json[0]->status == 'connected') {
            $this->logger->addInfo('Received token '.$token_json[0]->token);
            return $this->currentToken = $token_json[0]->token;
        } else {
            $this->logger->addError($token_json);
        }

        return false;
    }

    public function aesSetup()
    {
        $this->client->setDecoder("RSA");
        $aes_json = $this->client->remoteCall($this->baseUrl.'/getChiave/t/'.$this->currentToken);

        if ($aes_json[0]->status == 'ok') {
            $this->logger->addInfo('Received aes key '.$aes_json[0]->key);
            $currentAESkey = $aes_json[0]->key;

            $this->client->setAESkey($currentAESkey );
        } else {
            $this->logger->addError($aes_json);
        }
    }

    private function remote_totali($from,$length,$getter)
    {
        $this->client->setDecoder("AES");
        $remoteObjects = $this->client->remoteCall($this->baseUrl.'/'.$getter.'/start/'.$from.'/token/'.$this->currentToken);

        $remote_totali = array();

        if ( count($remoteObjects[0]->partecipanti[0]) > 0 ) {
            $remote_totali = array_merge($remote_totali,$remoteObjects[0]->partecipanti);
        }

        $i = count($remote_totali);
        while ($remoteObjects[0]->other == 'ok' && $i < $length) {

            $x = $from + $i;
            $remoteObjects = $this->client->remoteCall($this->baseUrl.'/'.$getter.'/start/'.$x.'/token/'.$this->currentToken);

            if ( count($remoteObjects[0]->partecipanti[0]) > 0 ) {
                $remote_totali = array_merge($remote_totali,$remoteObjects[0]->partecipanti);
            }

            $i = count($remote_totali);
        }

        return $remote_totali;
    }

    public function getGruppi($from,$length)
    {
        $this->client->setDecoder("AES");
        $remoteObjects = $this->client->remoteCall($this->baseUrl.'/getGruppi/start/'.$from.'/token/'.$this->currentToken);

        $remote_totali = array();

        if ( count($remoteObjects[0]->gruppi[0]) > 0 ) {
            $remote_totali = array_merge($remote_totali,$remoteObjects[0]->gruppi);
        }

        $i = count($remote_totali);
        while ($remoteObjects[0]->other == 'ok' && $i < $length) {

            $x = $from + $i;
            $remoteObjects = $this->client->remoteCall($this->baseUrl.'/getGruppi/start/'.$x.'/token/'.$this->currentToken);

            if ( count($remoteObjects[0]->gruppi[0]) > 0 ) {
                $remote_totali = array_merge($remote_totali,$remoteObjects[0]->gruppi);
            }

            $i = count($remote_totali);
        }

        return $remote_totali;
    }

    public function getGruppiExtraAgesci($from,$length)
    {
        $this->client->setDecoder("AES");
        $remoteObjects = $this->client->remoteCall($this->baseUrl.'/getGruppiExtraAgesci/start/'.$from.'/token/'.$this->currentToken);

        $remote_totali = array();

        if ( count($remoteObjects[0]->gruppi[0]) > 0 ) {
            $remote_totali = array_merge($remote_totali,$remoteObjects[0]->gruppi);
        }

        $i = count($remote_totali);
        while ($remoteObjects[0]->other == 'ok' && $i < $length) {

            $x = $from + $i;
            $response = $this->client->remoteCall($this->baseUrl.'/getGruppiExtraAgesci/start/'.$x.'/token/'.$this->currentToken);
            $remoteObjects = json_decode($this->decodeAES($response));

            if ( count($remoteObjects[0]->gruppi[0]) > 0 ) {
                $remote_totali = array_merge($remote_totali,$remoteObjects[0]->gruppi);
            }

            $i = count($remote_totali);
        }

        return $remote_totali;
    }

    public function getRagazzi($from,$length)
    {
        return $this->remote_totali($from,$length,'getRagazzi');
    }

    public function getCapi($from,$length)
    {
        return $this->remote_totali($from,$length,'getCapi');
    }

    public function getCapiExtra($from,$length)
    {
        return $this->remote_totali($from,$length,'getCapiExtra');
    }

    public function getCapiOneTeam($from,$length)
    {
        return $this->remote_totali($from,$length,'getCapiOneTeam');
    }

    public function getCapiLaboratorio($from,$length)
    {
        return $this->remote_totali($from,$length,'getCapiLaboratori');
    }

    public function getRagazziExtraAgesci($from,$length)
    {
        return $this->remote_totali($from,$length,'getRagazziExtraAgesci');
    }

    public function getCapiExtraAgesci($from,$length)
    {
        return $this->remote_totali($from,$length,'getCapiExtraAgesci');
    }

}
