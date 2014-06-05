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

$arguments->addOption(array('input-file','f'), array(
    'default' => getcwd().'/resources/${nomefile}.xlxs',
    'description' => 'Setta il file da caricare'));

$arguments->addFlag(array('production-mode', 'p'), 'Turn on production mode, default off');
$arguments->addFlag(array('import-ragazzi', 'r'), 'Turn on import ragazzi [API]');
$arguments->addFlag(array('import-capi', 'c'), 'Turn on import capi [API]');
$arguments->addFlag(array('import-capolaboratorio', 'l'), 'Turn on import capi laboratorio [API]');
$arguments->addFlag(array('import-extra', 'x'), 'Turn on import capi extra [API]');
$arguments->addFlag(array('import-oneteam', 'o'), 'Turn on import oneteam [API]');
$arguments->addFlag(array('import-oneteam-offline', 'd'), 'Turn on import oneteam offline [FILE]');
$arguments->addFlag(array('import-gruppi', 'g'), 'Turn on import gruppi [API]');

$arguments->addFlag(array('import-ragazzi-extra', 'R'), 'Turn on import ragazzi extra Agesci [API]');
$arguments->addFlag(array('import-capi-extra', 'C'), 'Turn on import capi extra Agesci [API]');
$arguments->addFlag(array('import-gruppi-extra', 'G'), 'Turn on import gruppi extra Agesci [API]');

$arguments->addFlag(array('import-external-lab', 'e'), 'Turn on import external lab [FILE]');
$arguments->addFlag(array('import-internal-lab', 'i'), 'Turn on import internal lab [FILE]');

$arguments->addFlag(array('import-internal-rs', 'b'), 'Turn on import internal rs lab [FILE]');
$arguments->addFlag(array('import-tavole-rs', 't'), 'Turn on import tavole rs lab [FILE]');
$arguments->addFlag(array('import-veglie-rs', 'n'), 'Turn on import veglie rs lab [FILE]');

$arguments->addFlag(array('import-subarea', 's'), 'Turn on import sub area [FILE]');
$arguments->addFlag(array('import-route', 'u'), 'Turn on import route definition [FILE]');
$arguments->addFlag(array('import-ragazzi-internazionale', 'z'), 'Turn on import world ragazzi[FILE]');
$arguments->addFlag(array('import-clan-lab', 'a'), 'Turn on import clan lab [FILE]');

$arguments->addFlag(array('genera-uid-partecipante', 'u'), 'Genera codice partecipante');
$arguments->addFlag(array('genera-guid-gruppo-virtuale', 'v'), 'Genera gruppo virtuale');

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
        $all = true; //demo mode OFF
        R::freeze(true);
    } else {
        $all = false; //demo mode ON
        R::freeze(false);
    }

    if (isset($arguments_parsed['input-file'])) {
        $filename = $arguments_parsed['input-file'];
    }

    $client = new \Iscrizioni\ClientApi($log);
    $proxy = new \Iscrizioni\ProxyHelper($client, $config['api']['base_url']);

    $apiImportsFlags = array(
        'import-gruppi' => 'importGruppi',
        'import-oneteam' => 'importOneTeam',
        'import-capolaboratorio' => 'importCapiLaboratorio',
        'import-extra' => 'importExtra',
        'import-capi' => 'importCapi',
        'import-ragazzi' => 'importRagazzi',
        'import-gruppi-extra' => 'importGruppiExtraAgesci',
        'import-capi-extra' => 'importCapiExtraAgesci',
        'import-ragazzi-extra' => 'importRagazziExtraAgesci',
    );

    if ($apiImports = array_intersect_key($apiImportsFlags, $arguments_parsed)) {

        if (!(file_exists($config['api']['key_path']))) {
            \cli\out('invalid private key : ' . $config['api']['key_path'] . "\n");
            exit - 1;
        }

        $fp = fopen($config['api']['key_path'], "r");
        $priv_key = fread($fp, 8192);
        fclose($fp);

        $proxy->setLogger($log);
        $client->setPrivateKey($priv_key);
        $proxy->login($config['api']['utente'], $config['api']['password']);
        $proxy->aesSetup();

        $importer = new \Iscrizioni\Importer($log);

        foreach( $apiImports as $flag => $method) {
            call_user_func_array([$importer, $method ], [$proxy, $all]);
        }
    }

    if (isset($arguments_parsed['import-internal-lab'])) {

        $inputFileName = 'interni.xlsx';
        if ( !empty($filename) ) {
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
                foreach ($row as $k => $v) {
                    //echo "Row: " . $row_i . "- Col: " . ($k + 1) . " = " . $v . "\n";
                }
                */

                if ( !empty($row[2]) ) {
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
        if ( !empty($filename) ) {
            $inputFileName = $filename;
        }

        excelFileParsing($inputFileName,'mapLaboratoriRS',2, $log, 'Lab Interno RS ');
    }

    if ( isset($arguments_parsed['import-tavole-rs']) ) {

        $inputFileName = 'tavolers.xlsx';
        if ( !empty($filename) ) {
            $inputFileName = $filename;
        }

        excelFileParsing($inputFileName,'mapTavoleRotondeRS',2, $log, 'Tavola Rotonda RS ');
    }

    if ( isset($arguments_parsed['import-veglie-rs']) ) {

        $inputFileName = 'vegliers.xlsx';
        if ( !empty($filename) ) {
            $inputFileName = $filename;
        }

        excelFileParsing($inputFileName,'mapVeglieRS',2, $log, 'Veglie RS ');
    }

    if ( isset($arguments_parsed['import-clan-lab']) ) {

        $inputFileName = 'laboratoriCodici.xlsx';
        if ( !empty($filename) ) {
            $inputFileName = $filename;
        }

        excelFileParsing($inputFileName,'mapVincoliCodici',2, $log, 'Vincoli Laboratorio Codici');
    }

    if ( isset($arguments_parsed['import-oneteam-offline']) ) {

        $inputFileName = 'gestioneOneTeam.xlsx';
        if ( !empty($filename) ) {
            $inputFileName = $filename;
        }

        excelFileParsing($inputFileName,'mapGestioneOneteam',2, $log, 'Gestione One Team gdoc');
    }

    if ( isset($arguments_parsed['import-ragazzi-internazionale']) ) {

        $inputFileName = 'stranieri.xlsx';
        if ( !empty($filename) ) {
            $inputFileName = $filename;
        }

        excelFileParsing($inputFileName,'mapStranieri',2, $log, 'Iscrizioni stranieri');

    }

    if (isset($arguments_parsed['import-external-lab'])) {

        $inputFileName = 'esterni.xlsx';
        if ( !empty($filename) ) {
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

                if ( !empty($row[4]) ) {
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
        if ( !empty($filename) ) {
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

                if ( !empty($row[0]) ) {
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
        if ( !empty($filename) ) {
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
                $gemellaggio_row->ordgruppo		     = $row[5];    // ordinale gruppo dove svolge servizio (decregione + ordinalegruppo = idgruppo)
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

    if (isset($arguments_parsed['genera-uid-partecipante'])) {















    }

    if (isset($arguments_parsed['genera-guid-gruppo-virtuale'])) {

        // codice virtuale clan
        $start = 0;
        $end = 9999;

    }

    /*
    if (isset($arguments_parsed['genera-codice-laboratorio-virtuale'])) {

        // codice virtuale clan
        $start = 0;
        $end = 9999;

    }
    */

} catch (\Exception $e) {
    \cli\out('Error : ' . $e->getMessage(). "\n");
    exit - 1;
}
