<?php
/**
 * User: lancio
 * Date: 05/06/14
 * Time: 23:18
 */

namespace Iscrizioni;

use Psr\Log\LoggerInterface;
use RedBean_Facade as R;

class Importer
{
    private $log;

    /**
     * @param $proxy
     * @param $all
     * @return array
     */
    public function __construct(LoggerInterface $log)
    {
        $this->log = $log;
    }

    public function importGruppiExtraAgesci($proxy, $all)
    {
        return $this->importGruppi($proxy, $all, false);
    }

    public function importCapiExtraAgesci($proxy, $all)
    {
        return $this->importCapi($proxy, $all, false);
    }

    public function importRagazziExtraAgesci($proxy, $all)
    {
        return $this->importRagazzi($proxy, $all, false);
    }

    public function importGruppi($proxy, $all, $agesci = true)
    {
        $method = "getGruppi";
        if (!$agesci) {
            $method .= "ExtraAgesci";
        }
        $i = 0;
        $running = true;
        while ($running) {

            $gruppi = call_user_func_array([$proxy,$method],[$i, 10]);

            if ($all) {
                $gruppi_estratti = count($gruppi);
                $i += $gruppi_estratti;
                if ($gruppi_estratti < 10) $running = false;
            } else {
                $running = false;
            }

            foreach ($gruppi as $gruppo) {
                $this->log->addInfo('Gruppo ', array('codice' => $gruppo->codice, 'nome' => $gruppo->nome, 'unita' => $gruppo->unita, 'regione' => $gruppo->regione));

                $gruppo_row = R::dispense('gruppi');
                $gruppo_row->idgruppo = $gruppo->codice;
                $gruppo_row->nome = $gruppo->nome;
                $gruppo_row->unita = $gruppo->unita;
                $gruppo_row->regione = $gruppo->regione;
                $id = R::store($gruppo_row);

            }

        }
        return true;
    }

    /**
     * @param $proxy
     * @param $all
     * @return array
     */
    public function importOneTeam($proxy, $all)
    {
        $i = 0;
        $running = true;
        while ($running) {

            $capiOne = $proxy->getCapiOneTeam($i, 10);

            if ($all) {
                $capi_estratti = count($capiOne);
                $i += $capi_estratti;
                if ($capi_estratti < 10) $running = false;
            } else {
                $running = false;
            }

            foreach ($capiOne as $capoOne) {

                $this->log->addInfo('Capo One Team ', json_decode(json_encode($capoOne), true));

                $oneteam_row = R::dispense('oneteam');
                $oneteam_row->codicecensimento = $capoOne->codicesocio;
                $oneteam_row->nome = $capoOne->nome;
                $oneteam_row->cognome = $capoOne->cognome;

                $oneteam_row->datanascita = $capoOne->datanascita;

                $eta_capo = getAge($capoOne->datanascita); //Formato 1988-01-31 YYYY-MM-GG
                $oneteam_row->eta = $eta_capo;

                $oneteam_row->sesso = $capoOne->sesso;

                $oneteam_row->periodopartecipazione = $capoOne->periodopartecipazione;

                $oneteam_row->pagato = $capoOne->pagato;
                $oneteam_row->modpagamento = $capoOne->modpagamento;

                $oneteam_row->colazione = $capoOne->colazione;

                $oneteam_row->alimentari = $capoOne->alimentari;

                if ($capoOne->intolleranzealimentari->presenti != 0) {
                    $oneteam_row->intolleranzealimentari = $capoOne->intolleranzealimentari->elenco;
                } else {
                    $oneteam_row->intolleranzealimentari = NULL;
                }

                if ($capoOne->allergiealimentari->presenti != 0) {
                    $oneteam_row->allergiealimentari = $capoOne->allergiealimentari->elenco;
                } else {
                    $oneteam_row->allergiealimentari = NULL;
                }

                if ($capoOne->allergiefarmaci->presenti != 0) {
                    $oneteam_row->allergiefarmaci = $capoOne->allergiefarmaci->elenco;
                } else {
                    $oneteam_row->allergiefarmaci = NULL;
                }

                if ($capoOne->disabilita->presenti != 0) {
                    $oneteam_row->sensoriali = $capoOne->disabilita->sensoriali;
                    $oneteam_row->psichiche = $capoOne->disabilita->psichiche;
                    $oneteam_row->lis = $capoOne->disabilita->lis;
                    $oneteam_row->fisiche = $capoOne->disabilita->fisiche;
                } else {
                    $oneteam_row->sensoriali = NULL;
                    $oneteam_row->psichiche = NULL;
                    $oneteam_row->lis = NULL;
                    $oneteam_row->fisiche = NULL;
                }

                if ($capoOne->patologie->presenti != 0) {
                    $oneteam_row->patologie = $capoOne->patologie->descrizione;
                } else {
                    $oneteam_row->patologie = NULL;
                }

                $id = R::store($oneteam_row);

            }
        }
        return true;
    }

    /**
     * @param $proxy
     * @param $all
     * @return array
     */
    public function importCapiLaboratorio($proxy, $all)
    {
        $i = 0;
        $running = true;

        while ($running) {

            $capiLaboratorio = $proxy->getCapiLaboratorio($i, 10);

            if ($all) {
                $capi_estratti = count($capiLaboratorio);
                $i += $capi_estratti;
                if ($capi_estratti < 10) $running = false;
            } else {
                $running = false;
            }

            foreach ($capiLaboratorio as $capoLaboratorio) {

                $this->log->addInfo('Capo Laboratorio ', json_decode(json_encode($capoLaboratorio), true));

                $laboratorio_row = R::dispense('capolaboratorio');
                $laboratorio_row->codicecensimento = $capoLaboratorio->codicesocio;
                $laboratorio_row->nome = $capoLaboratorio->nome;
                $laboratorio_row->cognome = $capoLaboratorio->cognome;

                $laboratorio_row->datanascita = $capoLaboratorio->datanascita;

                $eta_capo = getAge($capoLaboratorio->datanascita); //Formato 1988-01-31 YYYY-MM-GG
                $laboratorio_row->eta = $eta_capo;

                $laboratorio_row->sesso = $capoLaboratorio->sesso;

                $laboratorio_row->periodopartecipazione = $capoLaboratorio->periodopartecipazione;

                $laboratorio_row->pagato = $capoLaboratorio->pagato;
                $laboratorio_row->modpagamento = $capoLaboratorio->modpagamento;

                $laboratorio_row->colazione = $capoLaboratorio->colazione;

                $laboratorio_row->alimentari = $capoLaboratorio->alimentari;

                if ($capoLaboratorio->intolleranzealimentari->presenti != 0) {
                    $laboratorio_row->intolleranzealimentari = $capoLaboratorio->intolleranzealimentari->elenco;
                } else {
                    $laboratorio_row->intolleranzealimentari = NULL;
                }

                if ($capoLaboratorio->allergiealimentari->presenti != 0) {
                    $laboratorio_row->allergiealimentari = $capoLaboratorio->allergiealimentari->elenco;
                } else {
                    $laboratorio_row->allergiealimentari = NULL;
                }

                if ($capoLaboratorio->allergiefarmaci->presenti != 0) {
                    $laboratorio_row->allergiefarmaci = $capoLaboratorio->allergiefarmaci->elenco;
                } else {
                    $laboratorio_row->allergiefarmaci = NULL;
                }

                if ($capoLaboratorio->disabilita->presenti != 0) {
                    $laboratorio_row->sensoriali = $capoLaboratorio->disabilita->sensoriali;
                    $laboratorio_row->psichiche = $capoLaboratorio->disabilita->psichiche;
                    $laboratorio_row->lis = $capoLaboratorio->disabilita->lis;
                    $laboratorio_row->fisiche = $capoLaboratorio->disabilita->fisiche;
                } else {
                    $laboratorio_row->sensoriali = NULL;
                    $laboratorio_row->psichiche = NULL;
                    $laboratorio_row->lis = NULL;
                    $laboratorio_row->fisiche = NULL;
                }

                if ($capoLaboratorio->patologie->presenti != 0) {
                    $laboratorio_row->patologie = $capoLaboratorio->patologie->descrizione;
                } else {
                    $laboratorio_row->patologie = NULL;
                }

                $id = R::store($laboratorio_row);

            }
        }
        return true;
    }

    /**
     * @param $proxy
     * @param $all
     * @return array
     */
    public function importExtra($proxy, $all)
    {
        $i = 0;
        $running = true;
        while ($running) {

            $capiExtra = $proxy->getCapiExtra($i, 10);

            if ($all) {
                $capi_estratti = count($capiExtra);
                $i += $capi_estratti;
                if ($capi_estratti < 10) $running = false;
            } else {
                $running = false;
            }

            foreach ($capiExtra as $capoExtra) {

                $this->log->addInfo('Capo Extra ', json_decode(json_encode($capoExtra), true));

                $extra_row = R::dispense('capoextra');
                $extra_row->codicecensimento = $capoExtra->codicesocio;
                $extra_row->nome = $capoExtra->nome;
                $extra_row->cognome = $capoExtra->cognome;

                $extra_row->datanascita = $capoExtra->datanascita;

                $eta_capo = getAge($capoExtra->datanascita); //Formato 1988-01-31 YYYY-MM-GG
                $extra_row->eta = $eta_capo;

                $extra_row->sesso = $capoExtra->sesso;

                $extra_row->periodopartecipazione = $capoExtra->periodopartecipazione;

                $extra_row->pagato = $capoExtra->pagato;
                $extra_row->modpagamento = $capoExtra->modpagamento;

                $extra_row->colazione = $capoExtra->colazione;

                $extra_row->alimentari = $capoExtra->alimentari;

                if ($capoExtra->intolleranzealimentari->presenti != 0) {
                    $extra_row->intolleranzealimentari = $capoExtra->intolleranzealimentari->elenco;
                } else {
                    $extra_row->intolleranzealimentari = NULL;
                }

                if ($capoExtra->allergiealimentari->presenti != 0) {
                    $extra_row->allergiealimentari = $capoExtra->allergiealimentari->elenco;
                } else {
                    $extra_row->allergiealimentari = NULL;
                }

                if ($capoExtra->allergiefarmaci->presenti != 0) {
                    $extra_row->allergiefarmaci = $capoExtra->allergiefarmaci->elenco;
                } else {
                    $extra_row->allergiefarmaci = NULL;
                }

                if ($capoExtra->disabilita->presenti != 0) {
                    $extra_row->sensoriali = $capoExtra->disabilita->sensoriali;
                    $extra_row->psichiche = $capoExtra->disabilita->psichiche;
                    $extra_row->lis = $capoExtra->disabilita->lis;
                    $extra_row->fisiche = $capoExtra->disabilita->fisiche;
                } else {
                    $extra_row->sensoriali = NULL;
                    $extra_row->psichiche = NULL;
                    $extra_row->lis = NULL;
                    $extra_row->fisiche = NULL;
                }

                if ($capoExtra->patologie->presenti != 0) {
                    $extra_row->patologie = $capoExtra->patologie->descrizione;
                } else {
                    $extra_row->patologie = NULL;
                }

                $id = R::store($extra_row);
            }
        }
        return true;
    }

    /**
     * @param $proxy
     * @param $all
     * @return array
     */
    public function importCapi($proxy, $all, $agesci = true)
    {
        $method = "getCapi";
        if (!$agesci) {
            $method .= "ExtraAgesci";
        }
        $i = 0;
        $running = true;
        while ($running) {

            $capi = call_user_func_array([$proxy,$method],[$i, 10]);

            //echo count($capi)."\n";

            if ($all) {
                $capi_estratti = count($capi);
                $i += $capi_estratti;
                if ($capi_estratti < 10) $running = false;
            } else {
                $running = false;
            }

            foreach ($capi as $capo) {

                $this->log->addInfo('Capo ', json_decode(json_encode($capo), true));

                $capo_row = R::dispense('capo');
                $capo_row->codicecensimento = $capo->codicesocio;
                $capo_row->nome = $capo->nome;
                $capo_row->cognome = $capo->cognome;
                $capo_row->idgruppo = $capo->gruppo;
                $capo_row->idunitagruppo = $capo->unita;

                $capo_row->datanascita = $capo->datanascita;

                $eta_capo = getAge($capo->datanascita); //Formato 1988-01-31 YYYY-MM-GG
                $capo_row->eta = $eta_capo;

                $capo_row->sesso = $capo->sesso;

                $recapiti = $capo->recapiti;

                foreach ($recapiti as $recapito) {

                    if (!is_object($recapito)) {
                        $this->log->addError('recapito invalido ', array('codice censimento' => $capo->codicesocio));
                    } else {

                        $this->log->addInfo("\t" . 'Recapito ', array('tipo' => $recapito->tipo, 'valore' => $recapito->valore));
                        if ($recapito->tipo == 'email') {
                            $capo_row->email = $recapito->valore;
                        }
                        if ($recapito->tipo == 'cellulare') {
                            $capo_row->cellulare = $recapito->valore;
                        }
                        if ($recapito->tipo == 'abitazione') {
                            if (is_numeric($recapito->valore)) $capo_row->abitazione = $recapito->valore;
                        }
                    }
                }

                $residenza = $capo->residenza;
                $this->log->addInfo("\t" . 'Residenza', array('citta' => $residenza->citta));
                $capo_row->indirizzo = $residenza->indirizzo;
                $capo_row->cap = $residenza->cap;
                $capo_row->citta = $residenza->citta;
                $capo_row->provincia = $residenza->provincia;

                $capo_row->ruolo = $capo->ruolo;

                $capo_row->colazione = $capo->colazione;

                $capo_row->alimentari = $capo->alimentari;

                if ($capo->intolleranzealimentari->presenti != 0) {
                    $capo_row->intolleranzealimentari = $capo->intolleranzealimentari->elenco;
                } else {
                    $capo_row->intolleranzealimentari = NULL;
                }

                if ($capo->allergiealimentari->presenti != 0) {
                    $capo_row->allergiealimentari = $capo->allergiealimentari->elenco;
                } else {
                    $capo_row->allergiealimentari = NULL;
                }

                if ($capo->allergiefarmaci->presenti != 0) {
                    $capo_row->allergiefarmaci = $capo->allergiefarmaci->elenco;
                } else {
                    $capo_row->allergiefarmaci = NULL;
                }

                if ($capo->disabilita->presenti != 0) {
                    $capo_row->sensoriali = $capo->disabilita->sensoriali;
                    $capo_row->psichiche = $capo->disabilita->psichiche;
                    $capo_row->lis = $capo->disabilita->lis;
                    $capo_row->fisiche = $capo->disabilita->fisiche;
                } else {
                    $capo_row->sensoriali = NULL;
                    $capo_row->psichiche = NULL;
                    $capo_row->lis = NULL;
                    $capo_row->fisiche = NULL;
                }

                if ($capo->patologie->presenti != 0) {
                    $capo_row->patologie = $capo->patologie->descrizione;
                } else {
                    $capo_row->patologie = NULL;
                }

                try {
                    $id = R::store($capo_row);
                } catch (Exception $e) {
                    $this->log->addError($e->getMessage(), array('codice socio' => $capo->codicesocio));
                    continue;
                }
            }
        }
        return true;
    }

    /**
     * @param $proxy
     * @param $all
     */
    public function importRagazzi($proxy, $all, $agesci = true)
    {
        $method = "getRagazzi";
        if (!$agesci) {
            $method .= "ExtraAgesci";
        }

        $i = 0;
        $running = true;
        while ($running) {

            $ragazzi = call_user_func_array([$proxy,$method],[$i, 10]);

            if ($all) {
                $ragazzi_estratti = count($ragazzi);
                $i += $ragazzi_estratti;
                if ($ragazzi_estratti < 10) $running = false;
            } else {
                $running = false;
            }

            foreach ($ragazzi as $ragazzo) {

                $this->log->addInfo('Ragazzo ', json_decode(json_encode($ragazzo), true));

                $ragazzo_row = R::dispense('ragazzo');
                $ragazzo_row->codicecensimento = $ragazzo->codicesocio;
                $ragazzo_row->nome = $ragazzo->nome;
                $ragazzo_row->cognome = $ragazzo->cognome;

                if($agesci) {
                    $ragazzo_row->sesso = $ragazzo->sesso;
                }
                $ragazzo_row->datanascita = $ragazzo->datanascita;

                $eta_ragazzo = getAge($ragazzo->datanascita); //Formato 1988-01-31 YYYY-MM-GG
                $ragazzo_row->eta = $eta_ragazzo;

                $ragazzo_row->idgruppo = $ragazzo->gruppo;
                $ragazzo_row->idunitagruppo = $ragazzo->unita;

                switch ($eta_ragazzo) {
                    case 13:
                    case 14:
                    case 15:
                    case 16:
                    case 17:
                        $ragazzo_row->novizio = 1; //da ricavare in base all'eta (16-17)
                        break;
                    case 18:
                    case 19:
                    case 20:
                    case 21:
                        $ragazzo_row->novizio = 0; //da ricavare in base all'eta (16-17)
                        break;
                    default:
                        \cli\out('[' . $ragazzo->codicesocio . ']' . 'invalid age : ' . $eta_ragazzo . "\n");
                        $this->log->addError('[' . $ragazzo->codicesocio . ']' . 'invalid age : ' . $eta_ragazzo . ' nato il ' . $ragazzo->datanascita);
                        $ragazzo_row->novizio = 0;
                        break;
                }

                $ragazzo_row->stradadicoraggio1 = 0;
                $ragazzo_row->stradadicoraggio2 = 0;
                $ragazzo_row->stradadicoraggio3 = 0;
                $ragazzo_row->stradadicoraggio4 = 0;
                $ragazzo_row->stradadicoraggio5 = 0;

                switch ($ragazzo->strada1) {
                    case 1:
                        $ragazzo_row->stradadicoraggio1 = 1;
                        break;
                    case 2:
                        $ragazzo_row->stradadicoraggio2 = 1;
                        break;
                    case 3:
                        $ragazzo_row->stradadicoraggio3 = 1;
                        break;
                    case 4:
                        $ragazzo_row->stradadicoraggio4 = 1;
                        break;
                    case 5:
                        $ragazzo_row->stradadicoraggio5 = 1;
                        break;
                    default:
                        \cli\out('invalid data strada1 : ' . $ragazzo->strada1 . "\n");
                        $this->log->addError('[' . $ragazzo->codicesocio . ']' . 'strada di coraggio 1 non valida : ' . $ragazzo->strada1);
                        break;
                }

                switch ($ragazzo->strada2) {
                    case 1:
                        $ragazzo_row->stradadicoraggio1 = 1;
                        break;
                    case 2:
                        $ragazzo_row->stradadicoraggio2 = 1;
                        break;
                    case 3:
                        $ragazzo_row->stradadicoraggio3 = 1;
                        break;
                    case 4:
                        $ragazzo_row->stradadicoraggio4 = 1;
                        break;
                    case 5:
                        $ragazzo_row->stradadicoraggio5 = 1;
                        break;
                    default:
                        \cli\out('invalid data strada2 : ' . $ragazzo->strada2 . "\n");
                        $this->log->addError('[' . $ragazzo->codicesocio . ']' . 'strada di coraggio 2 non valida : ' . $ragazzo->strada2);
                        break;
                }

                switch ($ragazzo->strada3) {
                    case 1:
                        $ragazzo_row->stradadicoraggio1 = 1;
                        break;
                    case 2:
                        $ragazzo_row->stradadicoraggio2 = 1;
                        break;
                    case 3:
                        $ragazzo_row->stradadicoraggio3 = 1;
                        break;
                    case 4:
                        $ragazzo_row->stradadicoraggio4 = 1;
                        break;
                    case 5:
                        $ragazzo_row->stradadicoraggio5 = 1;
                        break;
                    default:
                        \cli\out('invalid data strada3 : ' . $ragazzo->strada3 . "\n");
                        $this->log->addError('[' . $ragazzo->codicesocio . ']' . 'strada di coraggio 3 non valida : ' . $ragazzo->strada3);
                        break;
                }

                $ragazzo_row->colazione = $ragazzo->colazione;

                $ragazzo_row->alimentari = $ragazzo->alimentari;

                if ($ragazzo->intolleranzealimentari->presenti != 0) {
                    $ragazzo_row->intolleranzealimentari = $ragazzo->intolleranzealimentari->elenco;
                } else {
                    $ragazzo_row->intolleranzealimentari = NULL;
                }

                if ($ragazzo->allergiealimentari->presenti != 0) {
                    $ragazzo_row->allergiealimentari = $ragazzo->allergiealimentari->elenco;
                } else {
                    $ragazzo_row->allergiealimentari = NULL;
                }

                if ($ragazzo->allergiefarmaci->presenti != 0) {
                    $ragazzo_row->allergiefarmaci = $ragazzo->allergiefarmaci->elenco;
                } else {
                    $ragazzo_row->allergiefarmaci = NULL;
                }

                if ($ragazzo->disabilita->presenti != 0) {
                    $ragazzo_row->sensoriali = $ragazzo->disabilita->sensoriali;
                    $ragazzo_row->psichiche = $ragazzo->disabilita->psichiche;
                    $ragazzo_row->lis = $ragazzo->disabilita->lis;
                    $ragazzo_row->fisiche = $ragazzo->disabilita->fisiche;
                } else {
                    $ragazzo_row->sensoriali = NULL;
                    $ragazzo_row->psichiche = NULL;
                    $ragazzo_row->lis = NULL;
                    $ragazzo_row->fisiche = NULL;
                }

                if ($ragazzo->patologie->presenti != 0) {
                    $ragazzo_row->patologie = $ragazzo->patologie->descrizione;
                } else {
                    $ragazzo_row->patologie = NULL;
                }

                try {
                    $id = R::store($ragazzo_row);
                } catch (Exception $e) {
                    $this->log->addError($e->getMessage(), array('codice socio' => $ragazzo->codicesocio));
                }
            }
        }
    }
}
