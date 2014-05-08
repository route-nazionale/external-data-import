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

$strict = in_array('--strict', $_SERVER['argv']);
$arguments = new \cli\Arguments(compact('strict'));

$arguments->addFlag(array('verbose', 'v'), 'Turn on verbose output');
$arguments->addFlag('version', 'Display the version');
$arguments->addFlag(array('quiet', 'q'), 'Disable all output');
$arguments->addFlag(array('help', 'h'), 'Show this help screen');

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

$dsn = 'mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['database'];
$username = $config['db']['user'];
$password = $config['db']['password'];

// setto il database di default
R::setup($dsn, $username, $password);

$all = false; //demo mode
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
        $gruppo_row->nome 			= $gruppo->nome;
        $gruppo_row->sottocampo 	= -1;
        $gruppo_row->idgruppo 		= $gruppo->codice;
        $gruppo_row->gemellaggio 	= -1;
        $id = R::store($gruppo_row);

    }
}

if (isset($arguments_parsed['import-ragazzi'])) {
    $ragazzi = $proxy->getRagazzi($all);
    foreach ($ragazzi as $ragazzo) {
        $log->addInfo('Ragazzo ', array('codicesocio' => $ragazzo->codicesocio, 'gruppo' => $ragazzo->gruppo, 'unita' => $ragazzo->unita, 'strada1' => $ragazzo->strada1, 'strada2' => $ragazzo->strada2, 'strada3' => $ragazzo->strada3));

        $ragazzo_row = R::dispense('ragazzo');
        $ragazzo_row->codicecensimento	= $ragazzo->codicesocio;
        $ragazzo_row->nome				= $ragazzo->nome;
        $ragazzo_row->cognome			= $ragazzo->cognome;
        $ragazzo_row->eta				= 0;
        $ragazzo_row->idgruppo			= $ragazzo->gruppo;
        $ragazzo_row->handicap			= 0;
        $ragazzo_row->novizio			= 0;

        // selettore sulle $ragazzo->strada3

        $ragazzo_row->stradadicoraggio1	= 0;
        $ragazzo_row->stradadicoraggio2	= 0;
        $ragazzo_row->stradadicoraggio3	= 0;
        $ragazzo_row->stradadicoraggio4	= 0;
        $ragazzo_row->stradadicoraggio5	= 0;

        $id = R::store($ragazzo_row);

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




