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

class ClientAgesci
{
    private $proxyHelper;

    public function __construct($baseUrl)
    {
        $this->proxyHelper = new ProxyHelper($baseUrl);
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getGruppi($from,$length)
    {
        $response = $this->proxyHelper->remoteCall($this->baseUrl.'/getGruppi/start/'.$from.'/token/'.$this->currentToken);
        $remoteObjects = json_decode($this->proxyHelper->decodeAES($response));

        $remote_totali = array();

        if ( count($remoteObjects[0]->gruppi[0]) > 0 ) {
            $remote_totali = array_merge($remote_totali,$remoteObjects[0]->gruppi);
        }

        $i = count($remote_totali);
        while ($remoteObjects[0]->other == 'ok' && $i < $length) {

            $x = $from + $i;
            $response = $this->proxyHelper->remoteCall($this->baseUrl.'/getGruppi/start/'.$x.'/token/'.$this->currentToken);
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

    public function setPrivateKey($pkey)
    {
        $this->privateKey = $pkey;
    }

    private function remote_totali($from,$length,$getter)
    {
        $response = $this->remoteCall($this->baseUrl.'/'.$getter.'/start/'.$from.'/token/'.$this->currentToken);
        $remoteObjects = json_decode($this->decodeAES($response));

        $remote_totali = array();

        if ( count($remoteObjects[0]->partecipanti[0]) > 0 ) {
            $remote_totali = array_merge($remote_totali,$remoteObjects[0]->partecipanti);
        }

        $i = count($remote_totali);
        while ($remoteObjects[0]->other == 'ok' && $i < $length) {

            $x = $from + $i;
            $response = $this->remoteCall($this->baseUrl.'/'.$getter.'/start/'.$x.'/token/'.$this->currentToken);
            $remoteObjects = json_decode($this->decodeAES($response));

            if ( count($remoteObjects[0]->partecipanti[0]) > 0 ) {
                $remote_totali = array_merge($remote_totali,$remoteObjects[0]->partecipanti);
            }

            $i = count($remote_totali);
        }

        return $remote_totali;

    }

    public function getGruppiExtraAgesci($from,$length)
    {
        $response = $this->remoteCall($this->baseUrl.'/getGruppiExtraAgesci/start/'.$from.'/token/'.$this->currentToken);
        $remoteObjects = json_decode($this->decodeAES($response));

        $remote_totali = array();

        if ( count($remoteObjects[0]->gruppi[0]) > 0 ) {
            $remote_totali = array_merge($remote_totali,$remoteObjects[0]->gruppi);
        }

        $i = count($remote_totali);
        while ($remoteObjects[0]->other == 'ok' && $i < $length) {

            $x = $from + $i;
            $response = $this->remoteCall($this->baseUrl.'/getGruppiExtraAgesci/start/'.$x.'/token/'.$this->currentToken);
            $remoteObjects = json_decode($this->decodeAES($response));

            if ( count($remoteObjects[0]->gruppi[0]) > 0 ) {
                $remote_totali = array_merge($remote_totali,$remoteObjects[0]->gruppi);
            }

            $i = count($remote_totali);
        }

        return $remote_totali;

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
