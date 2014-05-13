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


$strict = in_array('--strict', $_SERVER['argv']);
$arguments = new \cli\Arguments(compact('strict'));

$arguments->addFlag(array('verbose', 'v'), 'Turn on verbose output');
$arguments->addFlag('version', 'Display the version');
$arguments->addFlag(array('quiet', 'q'), 'Disable all output');
$arguments->addFlag(array('help', 'h'), 'Show this help screen');

$arguments->addFlag(array('production-mode', 'p'), 'Turn on production mode, default off');
$arguments->addFlag(array('import-ragazzi', 'r'), 'Turn on import ragazzi');
$arguments->addFlag(array('import-gruppi', 'g'), 'Turn on import gruppi');
$arguments->addFlag(array('import-external-lab', 'e'), 'Turn on import external lab');
$arguments->addFlag(array('import-internal-lab', 'i'), 'Turn on import internal lab');
$arguments->addFlag(array('import-subarea', 's'), 'Turn on import sub area');

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
    R::freeze(false);

    if (isset($arguments_parsed['production-mode'])) {
        $all = true; //demo mode
    } else {
        $all = false; //demo mode
    }

    $proxy = new \Iscrizioni\ProxyHelper($config['base_url']);

    if (isset($arguments_parsed['import-ragazzi']) || isset($arguments_parsed['import-gruppi'])) {

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

        $gruppi = $proxy->getGruppi($all);

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

    if (isset($arguments_parsed['import-ragazzi'])) {

        $i = 0;
        $running = true;
        while($running){

            $ragazzi = $proxy->getRagazzi($i,50);

            if ($all) {
                $ragazzi_estratti = count($ragazzi);
                $i += $ragazzi_estratti;
                if ( $ragazzi_estratti < 50 ) $running = false;
            } else {
                $running = false;
            }

            foreach ($ragazzi as $ragazzo) {
                $log->addInfo('Ragazzo ', array('codicesocio' => $ragazzo->codicesocio, 'gruppo' => $ragazzo->gruppo, 'unita' => $ragazzo->unita, 'strada1' => $ragazzo->strada1, 'strada2' => $ragazzo->strada2, 'strada3' => $ragazzo->strada3));

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
                        \cli\out('invalid age : ' . $eta_ragazzo . "\n");
                        exit - 1;
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

                $id = R::store($ragazzo_row);

            }
        }
    }

    if (isset($arguments_parsed['import-internal-lab'])) {

        $inputFileName = 'interni.xlsx';
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
        for ($row = 1; $row <= $highestRow; $row++) {
            try {
                //  Read a row of data into an array
                $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
                foreach ($rowData[0] as $k => $v)
                    echo "Row: " . $row . "- Col: " . ($k + 1) . " = " . $v . "\n";
            } catch (Exception $e) {
                die('Error reading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
            }
        }

    }

    if (isset($arguments_parsed['import-external-lab'])) {

        $inputFileName = 'esterni.xlsx';
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
        for ($row = 1; $row <= $highestRow; $row++) {
            try {
                //  Read a row of data into an array
                $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
                foreach ($rowData[0] as $k => $v)
                    echo "Row: " . $row . "- Col: " . ($k + 1) . " = " . $v . "\n";
            } catch (Exception $e) {
                die('Error reading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
            }
        }

    }


    if (isset($arguments_parsed['import-subarea'])) {

        /*
        // QUARTIERI
            $Reader = new SpreadsheetReader('quartieri.xlsx');
            $Sheets = $Reader -> Sheets();

            $Reader -> ChangeSheet(0);

            $totale_sottocampo = array();

            foreach ($Reader as $Row)
            {
                //$quartiere_row = R::dispense('quartiere');
                $quartiere_row = new \stdClass;
                if (!empty($Row[0]) && is_numeric($Row[0]) ) {
                    $quartiere_row->sottocampo      = intval($Row[0]);
                    $quartiere_row->gemellaggio     = intval(str_replace('Route ','',$Row[1]));  // IN STAMPA DEVE AVER IL FORMATO 3 CIFRE str_pad($input, 3, '0', STR_PAD_LEFT);
                    $quartiere_row->totale          = intval($Row[22]);
                    if ( isset($totale_sottocampo[$quartiere_row->sottocampo]) ) {
                        $totale_sottocampo[$quartiere_row->sottocampo] = $totale_sottocampo[$quartiere_row->sottocampo] + $quartiere_row->totale;
                    } else {
                        $totale_sottocampo[$quartiere_row->sottocampo] = array();
                        $totale_sottocampo[$quartiere_row->sottocampo] = $quartiere_row->totale;
                    }
                    //var_dump($quartiere_row);
                }
            }

            print_r($totale_sottocampo);
        */

    }

} catch (Exception $e){
    \cli\out('Error : ' . $e->getMessage(). "\n");
    exit - 1;
}


