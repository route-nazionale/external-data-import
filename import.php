#!/usr/bin/php
<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 07/05/14 - 21:50
 *
 */

require 'vendor/autoload.php';
require 'config.php';

use RedBean_Facade as R;
use Monolog\Logger;


function getAge($birthday)
{
    $datetime1 = new DateTime($birthday);
    $datetime2 = new DateTime(date('Y-m-d'));
    $diff = $datetime1->diff($datetime2);

    return $diff->format('%y');
}


function excelFileParsing($inputFileName, $funMap, $from_row_number, $log, $desc) {
    //  Read your Excel workbook
    try {
        $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($inputFileName);
    } catch (Exception $e) {
        die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
    }

    //  Get worksheet dimensions
    $sheet = $objPHPExcel->getSheet(0);
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();

    //  Loop through each row of the worksheet in turn
    for ($row_i = $from_row_number; $row_i <= $highestRow; $row_i++) { //skip prima riga
        try {

            //  Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row_i . ':' . $highestColumn . $row_i, NULL, TRUE, FALSE);

            $row = $rowData[0];

            $log->addInfo($desc.$row_i, json_decode(json_encode($row), true)  );

            call_user_func($funMap, $row);

        } catch (Exception $e) {
            die('Error reading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }
    }
}

function mapLaboratoriRS($row){

    if ( !empty($row[1]) ){

        $labrs_row = R::dispense('labrs');
        $labrs_row->tipo    							= $row[0 ];
        $labrs_row->nome								= $row[1 ];
        $labrs_row->cognome								= $row[2 ];
        $labrs_row->codicesocio 						= $row[3 ];

        list($turnoS) = explode(" ",$row[6]);
        $turno = intval($turnoS);

        $labrs_row->turno								= $turno;
        $labrs_row->turnodesc								= $row[6 ];

        $id = R::store($labrs_row);

    }

}

function mapTavoleRotondeRS($row){

    if ( !empty($row[0]) && !empty($row[1]) ){

        $tavolers_row = R::dispense('tavolers');
        $tavolers_row->code    							= $row[1 ];
        $tavolers_row->stradadicoraggio                 = $row[3 ];
        $tavolers_row->nomecognome							= $row[6 ];
        $tavolers_row->telefono							= $row[7 ];
        $tavolers_row->cellulare							= $row[8 ];
        $tavolers_row->email							= $row[9 ];

        $tavolers_row->titolo                        = $row[11 ];
        $tavolers_row->obiettivi                        = $row[12 ];

        list($idgruppo, $idunita) =  explode(" ",$row[14 ]);

        $idunita = str_replace(')','',str_replace('(','',$idunita));

        $tavolers_row->idgruppo 						= trim($idgruppo);
        $tavolers_row->idunita                          = trim($idunita);
        $tavolers_row->nomeclan						    = $row[15 ];

        $id = R::store($tavolers_row);

    }

}

function mapVeglieRS($row){

    if ( !empty($row[0]) ){

        $vegliers_row = R::dispense('vegliers');

        $vegliers_row->stradadicoraggio                 = $row[2 ];

        $vegliers_row->nomecognome							= $row[5 ];
        $vegliers_row->telefono							= $row[6 ];
        $vegliers_row->cellulare							= $row[7 ];


        //TODO: DA PULIRE IL DATO??
        $vegliers_row->email							= $row[8 ];

        $vegliers_row->titolo                        = $row[10 ];
        $vegliers_row->obiettivi                        = $row[11 ];

        list($idgruppo, $idunita) =  explode(" ",$row[13 ],2);

        $idunita = str_replace(')','',str_replace('(','',$idunita));

        $vegliers_row->idgruppo 						= trim($idgruppo);
        $vegliers_row->idunita                          = trim($idunita);
        $vegliers_row->nomeclan						    = $row[14 ];

        $id = R::store($vegliers_row);

    }

}

$strict = in_array('--strict', $_SERVER['argv']);
$arguments = new \cli\Arguments(compact('strict'));

$arguments->addFlag(array('verbose', 'v'), 'Turn on verbose output');
$arguments->addFlag('version', 'Display the version');
$arguments->addFlag(array('quiet', 'q'), 'Disable all output');
$arguments->addFlag(array('help', 'h'), 'Show this help screen');

$arguments->addOption(array('input-file','f'), array(
    'default' => getcwd().'/resources/${nomefile}.xlxs',
    'description' => 'Setta il file da caricare'));

$arguments->addFlag(array('production-mode', 'p'), 'Turn on production mode, default off');
$arguments->addFlag(array('import-ragazzi', 'r'), 'Turn on import ragazzi [API]');
$arguments->addFlag(array('import-capi', 'c'), 'Turn on import capi [API]');
$arguments->addFlag(array('import-capolaboratorio', 'l'), 'Turn on import capi laboratorio [API]');
$arguments->addFlag(array('import-extra', 'x'), 'Turn on import capi extra [API]');
$arguments->addFlag(array('import-oneteam', 'o'), 'Turn on import oneteam [API]');
$arguments->addFlag(array('import-gruppi', 'g'), 'Turn on import gruppi [API]');
$arguments->addFlag(array('import-external-lab', 'e'), 'Turn on import external lab [FILE]');
$arguments->addFlag(array('import-internal-lab', 'i'), 'Turn on import internal lab [FILE]');

$arguments->addFlag(array('import-internal-rs', 'b'), 'Turn on import internal rs lab [FILE]');
$arguments->addFlag(array('import-tavole-rs', 't'), 'Turn on import tavole rs lab [FILE]');
$arguments->addFlag(array('import-veglie-rs', 'n'), 'Turn on import veglie rs lab [FILE]');

$arguments->addFlag(array('import-subarea', 's'), 'Turn on import sub area [FILE]');
$arguments->addFlag(array('import-route', 'u'), 'Turn on import route definition [FILE]');

// TODO
$arguments->addFlag(array('import-ragazzi-internazionale', 'z'), 'Turn on import world ragazzi[FILE]');
$arguments->addFlag(array('import-clan-lab', 'a'), 'Turn on import clan lab [FILE]');

$arguments->parse();
if ($arguments['help']) {
    echo $arguments->getHelpScreen();
    echo "\n\n";
}

$arguments_parsed = $arguments->getArguments();

if (isset($arguments_parsed['verbose'])) {
    define("VERBOSE", true);
} else {
    define("VERBOSE", false);
}

if (isset($arguments_parsed['quiet'])) {
    define("QUIET", true);
} else {
    define("QUIET", false);
}

// create a log channel
$log = new Logger('rescue-script');
if (VERBOSE) {
    \cli\out($arguments->asJSON() . "\n");

    if (!QUIET) {
        $handler = new \Monolog\Handler\StreamHandler('php://stdout', Logger::DEBUG);
        $log->pushHandler($handler);
    }
    $handler = new \Monolog\Handler\StreamHandler($config['log']['filename'], Logger::DEBUG);
    $log->pushHandler($handler);
} else {
    if (!QUIET) {
        $handler = new \Monolog\Handler\StreamHandler($config['log']['filename'], Logger::INFO);
        $log->pushHandler($handler);
    } else {
        $handler = new \Monolog\Handler\StreamHandler($config['log']['filename'], Logger::WARNING);
        $log->pushHandler($handler);
    }
}

try {

    $dsn = 'mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['database'];
    $username = $config['db']['user'];
    $password = $config['db']['password'];

    // setto il database di default
    R::setup($dsn, $username, $password);

    if (isset($arguments_parsed['production-mode'])) {
        $all = true; //demo mode
        R::freeze(true);
    } else {
        $all = false; //demo mode
        R::freeze(false);
    }


    if (isset($arguments_parsed['input-file'])) {
        $filename = $arguments_parsed['input-file'];
    }

    $proxy = new \Iscrizioni\ProxyHelper($config['base_url']);




    if (isset($arguments_parsed['import-ragazzi']) || isset($arguments_parsed['import-oneteam']) || isset($arguments_parsed['import-extra']) || isset($arguments_parsed['import-capolaboratorio']) || isset($arguments_parsed['import-capi']) || isset($arguments_parsed['import-gruppi'])) {

        if (!(file_exists($config['key_path']))) {
            \cli\out('invalid private key : ' . $config['key_path'] . "\n");
            exit - 1;
        }

        $fp = fopen($config['key_path'], "r");
        $priv_key = fread($fp, 8192);
        fclose($fp);

        $proxy->setLogger($log);
        $proxy->setPrivateKey($priv_key);
        $proxy->login($config['utente'], $config['password']);
        $proxy->aesSetup();

    }

    if (isset($arguments_parsed['import-gruppi'])) {

        $i = 0;
        $running = true;
        while($running){

            $gruppi = $proxy->getGruppi($i,10);

            if ($all) {
                $gruppi_estratti = count($gruppi);
                $i += $gruppi_estratti;
                if ( $gruppi_estratti < 10 ) $running = false;
            } else {
                $running = false;
            }

            foreach ($gruppi as $gruppo) {
                $log->addInfo('Gruppo ', array('codice' => $gruppo->codice, 'nome' => $gruppo->nome, 'unita' => $gruppo->unita, 'regione' => $gruppo->regione));

                $gruppo_row = R::dispense('gruppi');
                $gruppo_row->idgruppo 		= $gruppo->codice;
                $gruppo_row->nome 			= $gruppo->nome;
                $gruppo_row->unita 			= $gruppo->unita;
                $gruppo_row->regione 		= $gruppo->regione;
                $id = R::store($gruppo_row);

            }

        }
    }

    if (isset($arguments_parsed['import-oneteam'])) {

        $i = 0;
        $running = true;
        while($running){

            $capiOne = $proxy->getCapiOneTeam($i,10);

            if ($all) {
                $capi_estratti = count($capiOne);
                $i += $capi_estratti;
                if ( $capi_estratti < 10 ) $running = false;
            } else {
                $running = false;
            }

            foreach ($capiOne as $capoOne) {

                $log->addInfo('Capo One Team ', json_decode(json_encode($capoOne), true) );

                $oneteam_row = R::dispense('oneteam');
                $oneteam_row->codicecensimento	= $capoOne->codicesocio;
                $oneteam_row->nome				= $capoOne->nome;
                $oneteam_row->cognome			= $capoOne->cognome;

                $oneteam_row->datanascita       = $capoOne->datanascita;

                $eta_capo = getAge($capoOne->datanascita);//Formato 1988-01-31 YYYY-MM-GG
                $oneteam_row->eta				= $eta_capo;

                $oneteam_row->sesso             = $capoOne->sesso;

                $oneteam_row->periodopartecipazione             = $capoOne->periodopartecipazione;

                $oneteam_row->pagato             = $capoOne->pagato;
                $oneteam_row->modpagamento             = $capoOne->modpagamento;

                $oneteam_row->colazione = $capoOne->colazione;

                $oneteam_row->alimentari = $capoOne->alimentari;

                if ( $capoOne->intolleranzealimentari->presenti != 0 ){
                    $oneteam_row->intolleranzealimentari = $capoOne->intolleranzealimentari->elenco;
                } else {
                    $oneteam_row->intolleranzealimentari = NULL;
                }

                if ( $capoOne->allergiealimentari->presenti != 0 ){
                    $oneteam_row->allergiealimentari = $capoOne->allergiealimentari->elenco;
                } else {
                    $oneteam_row->allergiealimentari = NULL;
                }

                if ( $capoOne->allergiefarmaci->presenti != 0 ){
                    $oneteam_row->allergiefarmaci = $capoOne->allergiefarmaci->elenco;
                } else {
                    $oneteam_row->allergiefarmaci = NULL;
                }

                if ( $capoOne->disabilita->presenti != 0 ){
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

                if ( $capoOne->patologie->presenti != 0 ){
                    $oneteam_row->patologie = $capoOne->patologie->descrizione;
                } else {
                    $oneteam_row->patologie = NULL;
                }

                $id = R::store($oneteam_row);

            }
        }

    }

    if (isset($arguments_parsed['import-capolaboratorio'])) {

        $i = 0;
        $running = true;
        while($running){

            $capiLaboratorio = $proxy->getCapiLaboratorio($i,10);

            if ($all) {
                $capi_estratti = count($capiLaboratorio);
                $i += $capi_estratti;
                if ( $capi_estratti < 10 ) $running = false;
            } else {
                $running = false;
            }

            foreach ($capiLaboratorio as $capoLaboratorio) {

                $log->addInfo('Capo Laboratorio ', json_decode(json_encode($capoLaboratorio), true) );

                $laboratorio_row = R::dispense('capolaboratorio');
                $laboratorio_row->codicecensimento	= $capoLaboratorio->codicesocio;
                $laboratorio_row->nome				= $capoLaboratorio->nome;
                $laboratorio_row->cognome			= $capoLaboratorio->cognome;

                $laboratorio_row->datanascita       = $capoLaboratorio->datanascita;

                $eta_capo = getAge($capoLaboratorio->datanascita);//Formato 1988-01-31 YYYY-MM-GG
                $laboratorio_row->eta				= $eta_capo;

                $laboratorio_row->sesso             = $capoLaboratorio->sesso;

                $laboratorio_row->periodopartecipazione             = $capoLaboratorio->periodopartecipazione;

                $laboratorio_row->pagato             = $capoLaboratorio->pagato;
                $laboratorio_row->modpagamento             = $capoLaboratorio->modpagamento;

                $laboratorio_row->colazione = $capoLaboratorio->colazione;

                $laboratorio_row->alimentari = $capoLaboratorio->alimentari;

                if ( $capoLaboratorio->intolleranzealimentari->presenti != 0 ){
                    $laboratorio_row->intolleranzealimentari = $capoLaboratorio->intolleranzealimentari->elenco;
                } else {
                    $laboratorio_row->intolleranzealimentari = NULL;
                }

                if ( $capoLaboratorio->allergiealimentari->presenti != 0 ){
                    $laboratorio_row->allergiealimentari = $capoLaboratorio->allergiealimentari->elenco;
                } else {
                    $laboratorio_row->allergiealimentari = NULL;
                }

                if ( $capoLaboratorio->allergiefarmaci->presenti != 0 ){
                    $laboratorio_row->allergiefarmaci = $capoLaboratorio->allergiefarmaci->elenco;
                } else {
                    $laboratorio_row->allergiefarmaci = NULL;
                }

                if ( $capoLaboratorio->disabilita->presenti != 0 ){
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

                if ( $capoLaboratorio->patologie->presenti != 0 ){
                    $laboratorio_row->patologie = $capoLaboratorio->patologie->descrizione;
                } else {
                    $laboratorio_row->patologie = NULL;
                }

                $id = R::store($laboratorio_row);

            }
        }

    }

    if (isset($arguments_parsed['import-extra'])) {

        $i = 0;
        $running = true;
        while($running){

            $capiExtra = $proxy->getCapiExtra($i,10);

            if ($all) {
                $capi_estratti = count($capiExtra);
                $i += $capi_estratti;
                if ( $capi_estratti < 10 ) $running = false;
            } else {
                $running = false;
            }

            foreach ($capiExtra as $capoExtra) {

                $log->addInfo('Capo Extra ', json_decode(json_encode($capoExtra), true) );

                $extra_row = R::dispense('capoextra');
                $extra_row->codicecensimento	= $capoExtra->codicesocio;
                $extra_row->nome				= $capoExtra->nome;
                $extra_row->cognome			= $capoExtra->cognome;

                $extra_row->datanascita       = $capoExtra->datanascita;

                $eta_capo = getAge($capoExtra->datanascita);//Formato 1988-01-31 YYYY-MM-GG
                $extra_row->eta				= $eta_capo;

                $extra_row->sesso             = $capoExtra->sesso;

                $extra_row->periodopartecipazione             = $capoExtra->periodopartecipazione;

                $extra_row->pagato             = $capoExtra->pagato;
                $extra_row->modpagamento             = $capoExtra->modpagamento;

                $extra_row->colazione = $capoExtra->colazione;

                $extra_row->alimentari = $capoExtra->alimentari;

                if ( $capoExtra->intolleranzealimentari->presenti != 0 ){
                    $extra_row->intolleranzealimentari = $capoExtra->intolleranzealimentari->elenco;
                } else {
                    $extra_row->intolleranzealimentari = NULL;
                }

                if ( $capoExtra->allergiealimentari->presenti != 0 ){
                    $extra_row->allergiealimentari = $capoExtra->allergiealimentari->elenco;
                } else {
                    $extra_row->allergiealimentari = NULL;
                }

                if ( $capoExtra->allergiefarmaci->presenti != 0 ){
                    $extra_row->allergiefarmaci = $capoExtra->allergiefarmaci->elenco;
                } else {
                    $extra_row->allergiefarmaci = NULL;
                }

                if ( $capoExtra->disabilita->presenti != 0 ){
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

                if ( $capoExtra->patologie->presenti != 0 ){
                    $extra_row->patologie = $capoExtra->patologie->descrizione;
                } else {
                    $extra_row->patologie = NULL;
                }

                $id = R::store($extra_row);

            }
        }

    }

    if (isset($arguments_parsed['import-capi'])) {

        $i = 0;
        $running = true;
        while($running){

            $capi = $proxy->getCapi($i,10);

            //echo count($capi)."\n";

            if ($all) {
                $capi_estratti = count($capi);
                $i += $capi_estratti;
                if ( $capi_estratti < 10 ) $running = false;
            } else {
                $running = false;
            }

            foreach ($capi as $capo) {

                $log->addInfo('Capo ', json_decode(json_encode($capo), true) );

                $capo_row = R::dispense('capo');
                $capo_row->codicecensimento	= $capo->codicesocio;
                $capo_row->nome				= $capo->nome;
                $capo_row->cognome			= $capo->cognome;
                $capo_row->idgruppo			= $capo->gruppo;
                $capo_row->idunitagruppo     = $capo->unita;

                $capo_row->datanascita       = $capo->datanascita;

                $eta_capo = getAge($capo->datanascita);//Formato 1988-01-31 YYYY-MM-GG
                $capo_row->eta				= $eta_capo;

                $capo_row->sesso             = $capo->sesso;

                $recapiti = $capo->recapiti;

                foreach($recapiti as $recapito) {

                    if  ( !is_object($recapito)) {
                        $log->addError('recapito invalido ',array('codice censimento' => $capo->codicesocio));
                    } else {

                        $log->addInfo("\t".'Recapito ', array('tipo' => $recapito->tipo, 'valore' => $recapito->valore));
                        if ( $recapito->tipo == 'email' ){
                            $capo_row->email = $recapito->valore;
                        }
                        if ( $recapito->tipo == 'cellulare' ) {
                            $capo_row->cellulare = $recapito->valore;
                        }
                        if ( $recapito->tipo == 'abitazione' ) {
                            if  ( is_numeric($recapito->valore) ) $capo_row->abitazione = $recapito->valore;
                        }

                    }

                }

                $residenza = $capo->residenza;
                $log->addInfo("\t".'Residenza', array('citta' => $residenza->citta));
                $capo_row->indirizzo    = $residenza->indirizzo;
                $capo_row->cap          = $residenza->cap;
                $capo_row->citta        = $residenza->citta;
                $capo_row->provincia    = $residenza->provincia;

                $capo_row->ruolo = $capo->ruolo;

                $capo_row->colazione = $capo->colazione;

                $capo_row->alimentari = $capo->alimentari;

                if ( $capo->intolleranzealimentari->presenti != 0 ){
                    $capo_row->intolleranzealimentari = $capo->intolleranzealimentari->elenco;
                } else {
                    $capo_row->intolleranzealimentari = NULL;
                }

                if ( $capo->allergiealimentari->presenti != 0 ){
                    $capo_row->allergiealimentari = $capo->allergiealimentari->elenco;
                } else {
                    $capo_row->allergiealimentari = NULL;
                }

                if ( $capo->allergiefarmaci->presenti != 0 ){
                    $capo_row->allergiefarmaci = $capo->allergiefarmaci->elenco;
                } else {
                    $capo_row->allergiefarmaci = NULL;
                }

                if ( $capo->disabilita->presenti != 0 ){
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

                if ( $capo->patologie->presenti != 0 ){
                    $capo_row->patologie = $capo->patologie->descrizione;
                } else {
                    $capo_row->patologie = NULL;
                }

                try {
                    $id = R::store($capo_row);
                } catch(Exception $e){
                    $log->addError($e->getMessage(), array('codice socio' => $capo->codicesocio));
                }

            }
        }

    }

    if (isset($arguments_parsed['import-ragazzi'])) {

        $i = 0;
        $running = true;
        while($running){

            $ragazzi = $proxy->getRagazzi($i,20);

            if ($all) {
                $ragazzi_estratti = count($ragazzi);
                $i += $ragazzi_estratti;
                if ( $ragazzi_estratti < 10 ) $running = false;
            } else {
                $running = false;
            }

            foreach ($ragazzi as $ragazzo) {

                $log->addInfo('Ragazzo ', json_decode(json_encode($ragazzo), true) );

                $ragazzo_row = R::dispense('ragazzo');
                $ragazzo_row->codicecensimento	= $ragazzo->codicesocio;
                $ragazzo_row->nome				= $ragazzo->nome;
                $ragazzo_row->cognome			= $ragazzo->cognome;

                $ragazzo_row->sesso             = $ragazzo->sesso;

                $ragazzo_row->datanascita       = $ragazzo->datanascita;

                $eta_ragazzo = getAge($ragazzo->datanascita);//Formato 1988-01-31 YYYY-MM-GG
                $ragazzo_row->eta				= $eta_ragazzo;

                $ragazzo_row->idgruppo			= $ragazzo->gruppo;
                $ragazzo_row->idunitagruppo     = $ragazzo->unita;

                switch($eta_ragazzo){
                    case 13:
                    case 14:
                    case 15:
                    case 16:
                    case 17:
                        $ragazzo_row->novizio			= 1; //da ricavare in base all'eta (16-17)
                        break;
                    case 18:
                    case 19:
                    case 20:
                    case 21:
                        $ragazzo_row->novizio			= 0; //da ricavare in base all'eta (16-17)
                        break;
                    default:
                        \cli\out('['.$ragazzo->codicesocio.']'.'invalid age : ' . $eta_ragazzo . "\n");
                        $log->addError('['.$ragazzo->codicesocio.']'.'invalid age : ' . $eta_ragazzo.' nato il '.$ragazzo->datanascita);
                        $ragazzo_row->novizio			= 0;
                        break;
                }

                $ragazzo_row->stradadicoraggio1	= 0;
                $ragazzo_row->stradadicoraggio2	= 0;
                $ragazzo_row->stradadicoraggio3	= 0;
                $ragazzo_row->stradadicoraggio4	= 0;
                $ragazzo_row->stradadicoraggio5	= 0;

                switch($ragazzo->strada1){
                    case 1:
                        $ragazzo_row->stradadicoraggio1	= 1;
                        break;
                    case 2:
                        $ragazzo_row->stradadicoraggio2	= 1;
                        break;
                    case 3:
                        $ragazzo_row->stradadicoraggio3	= 1;
                        break;
                    case 4:
                        $ragazzo_row->stradadicoraggio4	= 1;
                        break;
                    case 5:
                        $ragazzo_row->stradadicoraggio5	= 1;
                        break;
                    default:
                        \cli\out('invalid data strada1 : ' . $ragazzo->strada1 . "\n");
                        exit - 1;
                        break;
                }

                switch($ragazzo->strada2){
                    case 1:
                        $ragazzo_row->stradadicoraggio1	= 1;
                        break;
                    case 2:
                        $ragazzo_row->stradadicoraggio2	= 1;
                        break;
                    case 3:
                        $ragazzo_row->stradadicoraggio3	= 1;
                        break;
                    case 4:
                        $ragazzo_row->stradadicoraggio4	= 1;
                        break;
                    case 5:
                        $ragazzo_row->stradadicoraggio5	= 1;
                        break;
                    default:
                        \cli\out('invalid data strada2 : ' . $ragazzo->strada2 . "\n");
                        exit - 1;
                        break;
                }

                switch($ragazzo->strada3){
                    case 1:
                        $ragazzo_row->stradadicoraggio1	= 1;
                        break;
                    case 2:
                        $ragazzo_row->stradadicoraggio2	= 1;
                        break;
                    case 3:
                        $ragazzo_row->stradadicoraggio3	= 1;
                        break;
                    case 4:
                        $ragazzo_row->stradadicoraggio4	= 1;
                        break;
                    case 5:
                        $ragazzo_row->stradadicoraggio5	= 1;
                        break;
                    default:
                        \cli\out('invalid data strada3 : ' . $ragazzo->strada3 . "\n");
                        exit - 1;
                        break;
                }


                $ragazzo_row->colazione = $ragazzo->colazione;

                $ragazzo_row->alimentari = $ragazzo->alimentari;

                if ( $ragazzo->intolleranzealimentari->presenti != 0 ){
                    $ragazzo_row->intolleranzealimentari = $ragazzo->intolleranzealimentari->elenco;
                } else {
                    $ragazzo_row->intolleranzealimentari = NULL;
                }

                if ( $ragazzo->allergiealimentari->presenti != 0 ){
                    $ragazzo_row->allergiealimentari = $ragazzo->allergiealimentari->elenco;
                } else {
                    $ragazzo_row->allergiealimentari = NULL;
                }

                if ( $ragazzo->allergiefarmaci->presenti != 0 ){
                    $ragazzo_row->allergiefarmaci = $ragazzo->allergiefarmaci->elenco;
                } else {
                    $ragazzo_row->allergiefarmaci = NULL;
                }

                if ( $ragazzo->disabilita->presenti != 0 ){
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

                if ( $ragazzo->patologie->presenti != 0 ){
                    $ragazzo_row->patologie = $ragazzo->patologie->descrizione;
                } else {
                    $ragazzo_row->patologie = NULL;
                }

                try {
                    $id = R::store($ragazzo_row);
                } catch(Exception $e){
                    $log->addError($e->getMessage(), array('codice socio' => $ragazzo->codicesocio));
                }

            }
        }
    }

    if (isset($arguments_parsed['import-internal-lab'])) {

        $inputFileName = 'interni.xlsx';
        if ( !empty($filename) ){
            $inputFileName = $filename;
        }

        //  Read your Excel workbook
        try {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch (Exception $e) {
            die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }

        //  Get worksheet dimensions
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        //$highestColumn = $sheet->getHighestColumn();

        //  Loop through each row of the worksheet in turn
        for ($row_i = 2; $row_i <= $highestRow; $row_i++) { //skip riga 1
            try {

                $rowData = $sheet->rangeToArray('A' . $row_i . ':' . 'AT' . $row_i, NULL, TRUE, FALSE);


                /*
                $rowData = $sheet->rangeToArray('A' . $row_i . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
                */

                $row = $rowData[0];

                /*
                foreach ($row as $k => $v){
                    //echo "Row: " . $row_i . "- Col: " . ($k + 1) . " = " . $v . "\n";
                }
                */


                if ( !empty($row[2]) ){
                    $log->addInfo('Lab Interno '.$row_i, array('codicesocio' => $row[2], 'cognome' => $row[3], 'nome' => $row[4] , 'sesso' => $row[5] ));

                    $interni_row = R::dispense('interni');
                    $interni_row->quota								    = $row[1 ]; //Quota <E2><82><AC>
                    $interni_row->codicesocio						    = $row[2 ]; //codice_socio
                    $interni_row->cognome      							= $row[3 ]; //Cognome
                    $interni_row->nome      							= $row[4 ]; //Nome
                    $interni_row->sesso      							= $row[5 ]; //Sesso
                    $interni_row->luogonascita      				    = $row[6 ]; //Luogo_Nasc.
                    $interni_row->datanascita      						= $row[7 ]; //Data_Nasc.
                    $interni_row->eta     							    = $row[8 ]; //Eta
                    $interni_row->indirizzo      						= $row[9 ]; //indirizzo
                    $interni_row->cap      							    = $row[10]; //cap
                    $interni_row->residenza      						= $row[11]; //residenza
                    $interni_row->prov      							= $row[12]; //prov
                    $interni_row->tel      							    = $row[13]; //tel
                    $interni_row->cell      							= $row[14]; //cell
                    $interni_row->email      							= $row[15]; //email
                    $interni_row->email2      							= $row[16]; //email2
                    $interni_row->proponente      						= $row[17]; //proponente
                    $interni_row->stradacoraggio      					= $row[18]; //strada_coraggio
                    $interni_row->laboratorio      						= $row[19]; //laboratorio
                    $interni_row->obiettivolab							= $row[20]; //obiettivo_lab
                    $interni_row->orgoutputfin      					= $row[21]; //org_output_fin
                    $interni_row->fasciaeta      						= $row[22]; //fascia_eta
                    $interni_row->materiali      						= $row[23]; //materiali
                    $interni_row->spedizionemateriali      				= $row[24]; //spedizione_materiali
                    $interni_row->esigenze      						= $row[25]; //esigenze
                    $interni_row->pernotto      						= $row[26]; //pernotto
                    $interni_row->arrivo      							= $row[27]; //arrivo

                    $interni_row->codicesocioaltroanim      			= $row[29]; //Codice_socio_nome_altro_anim
                    $interni_row->nomealtroanim      				    = $row[30]; //nome_altro_anim
                    $interni_row->emailaltroanim      					= $row[31]; //e_mail
                    $interni_row->telefonoaltroanim      				= $row[32]; //telefono
                    $interni_row->pernottoaltroanim      				= $row[33]; //pernotto_2
                    $interni_row->arrivoaltroanim     					= $row[34]; //arrivo_2
                    $interni_row->dataprotocolloaltroanim      			= $row[35]; //Data_Protocollo
                    $interni_row->nomegruppo      						= $row[36]; //NOMEGRUPPO
                    $interni_row->nomezona     							= $row[37]; //NOMEZONA
                    $interni_row->nomereg      							= $row[38]; //NOMEREG

                    $interni_row->alimentazione      					= $row[43]; //SPECIFICHE ALIMENTAZIONE
                    $interni_row->colazione      						= $row[44]; //SPECIFICHE COLAZIONE (LATTE/T<C3><A8>)
                    $interni_row->note      							= $row[45]; //NOTE EMI

                    $id = R::store($interni_row);

                }

            } catch (Exception $e) {
                die('Error reading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
            }
        }

    }


    if ( isset($arguments_parsed['import-internal-rs']) ) {

        $inputFileName = 'labrs.xlsx';
        if ( !empty($filename) ){
            $inputFileName = $filename;
        }

        excelFileParsing($inputFileName,'mapLaboratoriRS',2, $log, 'Lab Interno RS ');

    }

    if ( isset($arguments_parsed['import-tavole-rs']) ) {

        $inputFileName = 'tavolers.xlsx';
        if ( !empty($filename) ){
            $inputFileName = $filename;
        }

        excelFileParsing($inputFileName,'mapTavoleRotondeRS',2, $log, 'Tavola Rotonda RS ');

    }

    if ( isset($arguments_parsed['import-veglie-rs']) ) {

        $inputFileName = 'vegliers.xlsx';
        if ( !empty($filename) ){
            $inputFileName = $filename;
        }

        excelFileParsing($inputFileName,'mapVeglieRS',2, $log, 'Veglie RS ');

    }


    if (isset($arguments_parsed['import-external-lab'])) {

        $inputFileName = 'esterni.xlsx';
        if ( !empty($filename) ){
            $inputFileName = $filename;
        }

        //  Read your Excel workbook
        try {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch (Exception $e) {
            die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }

        //  Get worksheet dimensions
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        //$highestColumn = $sheet->getHighestColumn();

        //  Loop through each row of the worksheet in turn
        for ($row_i = 2; $row_i <= $highestRow; $row_i++) { //skip prima riga
            try {

                //  Read a row of data into an array
                $rowData = $sheet->rangeToArray('A' . $row_i . ':' . 'AI' . $row_i, NULL, TRUE, FALSE);

                $row = $rowData[0];

                if ( !empty($row[4]) ){
                    $log->addInfo('Lab Esterno '.$row_i, array('titolo' => $row[4], 'aec' => $row[2], 'codicesocio' => $row[13] , 'email' => $row[16] ));

                    $esterni_row = R::dispense('esterni');
                    $esterni_row->cronologia							= $row[0 ];									//Informazioni cronologiche
                    $esterni_row->regione								= $row[1 ];									//Regione di appartenenza
                    $esterni_row->aec									= $row[2 ];									//Associazione/Ente/Cooperativa<E2><80><A6>proponente
                    $esterni_row->stradacoraggio						= $row[3 ];									//Strada di Coraggio
                    $esterni_row->titolo								= $row[4 ];									//Titolo laboratorio
                    $esterni_row->obiettivo								= $row[5 ];									//Obiettivo
                    $esterni_row->info									= $row[6 ];									//Organizzazione/struttura del laboratorio ed eventuale output finale prodotto
                    $esterni_row->limiti								= $row[7 ];									//Laboratorio adatto a ragazzi di:
                    $esterni_row->materiali								= $row[8 ];									//Materiali propri utilizzati

                    $esterni_row->esigenze								= $row[10];									// Eventuali esigenze particolari

                    $esterni_row->quota									= $row[12];									// Quota <E2><82><AC>
                    $esterni_row->codicesocio							= $row[13];									// codice socio
                    $esterni_row->nome									= $row[14];									// Nome
                    $esterni_row->cognome								= $row[15];									// Cognome
                    $esterni_row->email									= $row[16];									// E-mail
                    $esterni_row->telefono								= $row[17];									// Telefono/Cell
                    $esterni_row->pernotto								= $row[18];									// Pernottamento in
                    $esterni_row->dlgs196								= $row[19];									// Autorizzo il trattamento dei miei dati personali ai sensi del Dlgs 196 del 30 giugno 2003.
                    $esterni_row->altroanim								= $row[20];									// C'<C3><A8> un altro animatore?

                    $esterni_row->codicesocioaltroanim					= $row[22];									// codice socio
                    $esterni_row->nomealtroanim							= $row[23];									// Nome
                    $esterni_row->cognomealtroanim						= $row[24];									// Cognome
                    $esterni_row->emailaltroanim						= $row[25];									// E-mail
                    $esterni_row->telefonoaltroanim						= $row[26];									// Telefono/Cell
                    $esterni_row->pernottoaltroanim						= $row[27];									// Pernottamento in
                    $esterni_row->dlgs196altroanim						= $row[28];									// Autorizzo il trattamento dei miei dati personali ai sensi del Dlgs 196 del 30 giugno 2003.
                    $esterni_row->note									= $row[29];									// NOTE PER SEGRETERIA

                    $esterni_row->alloggio								= $row[31];									// SPECIFICHE ALLOGGIO

                    $esterni_row->colazione								= $row[33];									// SPECIFICHE COLAZIONE (Latte/Tea)
                    $esterni_row->note2									= $row[34];									// NOTE PER AREA EVENTI

                    $id = R::store($esterni_row);

                    /*
                    foreach ($rowData[0] as $k => $v)
                        echo "Row: " . $row . "- Col: " . ($k) . " = " . $v . "\n";
                    */
                }

            } catch (Exception $e) {
                die('Error reading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
            }
        }
        /*
         *
        */

    }

    if (isset($arguments_parsed['import-subarea'])) {

        $inputFileName = 'quartieri.xlsx';
        if ( !empty($filename) ){
            $inputFileName = $filename;
        }

        //  Read your Excel workbook
        try {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch (Exception $e) {
            die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }

        //  Get worksheet dimensions
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        //$highestColumn = $sheet->getHighestColumn();

        //  Loop through each row of the worksheet in turn
        for ($row_i = 5; $row_i <= $highestRow; $row_i++) { //skip prima riga
            try {

                //  Read a row of data into an array
                $rowData = $sheet->rangeToArray('A' . $row_i . ':' . 'X' . $row_i, NULL, TRUE, FALSE);

                $row = $rowData[0];

                if ( !empty($row[0]) ){
                    $log->addInfo('Quartiere '.$row_i, array('quartiere' => $row[0], 'aec' => $row[2], 'route' => $row[1]));

                    $quartiere_row = R::dispense('quartiere');
                    $quartiere_row->quartiere	  = $row[0];	//Quartiere

                    $route_number = str_replace('Route ','',$row[1]);

                    $quartiere_row->route	  = intval($route_number,10);	//Route
                    $id = R::store($quartiere_row);
                }

                /*
                foreach ($rowData[0] as $k => $v)
                    echo "Row: " . $row_i . "- Col: " . ($k) . " = " . $v . "\n";
                */

            } catch (Exception $e) {
                die('Error reading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
            }
        }

    }

    if (isset($arguments_parsed['import-route'])) {

        $inputFileName = 'route.ods';
        if ( !empty($filename) ){
            $inputFileName = $filename;
        }

        //  Read your Excel workbook
        try {
            $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($inputFileName);
        } catch (Exception $e) {
            die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }

        //  Get worksheet dimensions
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        //$highestColumn = $sheet->getHighestColumn();

        //  Loop through each row of the worksheet in turn
        for ($row_i = 2; $row_i <= $highestRow; $row_i++) { //skip prima riga
            try {

                //  Read a row of data into an array
                $rowData = $sheet->rangeToArray('A' . $row_i . ':' . 'P' . $row_i, NULL, TRUE, FALSE);

                $row = $rowData[0];

                $log->addInfo('Route Definition '.$row_i, array('route' => $row[1], 'idgruppo' => $row[5], 'idunita' => $row[6]));

                $gemellaggio_row = R::dispense('gemellaggio');
                $gemellaggio_row->area			     = $row[0 ];   // area

                $route_number = str_replace('Route ','',$row[1]);
                $gemellaggio_row->route			     = intval($route_number,10);	//Route

                $gemellaggio_row->codicesocio	     = $row[2];    // codice_socio
                $gemellaggio_row->regione			 = $row[3];    // Regione_dove_svolge_servizio
                $gemellaggio_row->gruppo			 = $row[4];    // Gruppo_dove_svolge_servizio
                $gemellaggio_row->idgruppo		     = $row[5];    // ordinale gruppo dove svolge servizio
                $gemellaggio_row->idunita			 = $row[6];    // unita_servizio
                $gemellaggio_row->cognome			 = $row[7];    // Cognome
                $gemellaggio_row->nome			     = $row[8];    // Nome
                $gemellaggio_row->email			     = $row[9];    // e-mail
                $gemellaggio_row->telefono		     = $row[10];   // Telefono
                $gemellaggio_row->cell			     = $row[11];   // Cell

                $gemellaggio_row->gemellato			 = $row[14];   // gemellato con FAMOSA COLONNA 0

                $id = R::store($gemellaggio_row);
                /*
                foreach ($rowData[0] as $k => $v)
                    echo "Row: " . $row_i . "- Col: " . ($k) . " = " . $v . "\n";
                */

            } catch (Exception $e) {
                die('Error reading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
            }
        }

    }

} catch (Exception $e){
    \cli\out('Error : ' . $e->getMessage(). "\n");
    exit - 1;
}


