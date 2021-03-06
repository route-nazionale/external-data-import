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

function excelFileParsing($inputFileName, $funMap, $from_row_number, $log, $desc,$sheetNumber = 0) {
    //  Read your Excel workbook
    try {
        $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($inputFileName);
    } catch (Exception $e) {
        die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '": ' . $e->getMessage());
    }

    //  Get worksheet dimensions
    $sheet = $objPHPExcel->getSheet($sheetNumber);
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();

    //  Loop through each row of the worksheet in turn
    $row_i = 0;
    for ($row_i = $from_row_number; $row_i <= $highestRow; $row_i++) { //skip prima riga
        try {

            //  Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row_i . ':' . $highestColumn . $row_i, NULL, TRUE, FALSE);

            $row = $rowData[0];

            $log->addInfo($desc.$row_i, json_decode(json_encode($row), true)  );

            call_user_func($funMap, $row);

        } catch (Exception $e) {
            die('Error reading file "' . pathinfo($inputFileName, PATHINFO_BASENAME) . '" row '.$row_i.' : ' . $e->getMessage());
        }
    }
}

function mapLaboratoriRS($row){

    if ( !empty($row[1]) ){

        $labrs_row = R::dispense('labrs');

        $labrs_row->gruppo    							= $row[2];

        if ( isset($row[19]) ) $labrs_row->code    		= $row[19];
        $labrs_row->strada    							= $row[4 ];
        $labrs_row->titolo								= $row[6 ];
        $labrs_row->sentiero							= $row[5 ];
        $labrs_row->obiettivo							= $row[7 ];

        $labrs_row->nome								= $row[12];
        $labrs_row->cognome								= $row[13];
        $labrs_row->cellulare								= $row[14];
        $labrs_row->email								= $row[15];

        $labrs_row->turnounodesc								= $row[16 ];
        $labrs_row->turnoduedesc								= $row[17 ];
        $labrs_row->turnotredesc								= $row[18 ];

        $id = R::store($labrs_row);

    }

}

function mapVincoliPersoneLaboratoriRS($row){

    if ( !empty($row[1]) ){

        $persone_laboratori_row = R::dispense('vincolilabrs');

        $persone_laboratori_row->tipo    		= $row[0];
        $persone_laboratori_row->nome    		= $row[1];
        $persone_laboratori_row->cognome    	= $row[2];
        $persone_laboratori_row->codicecensimento    	= $row[3];
        $persone_laboratori_row->nomegruppo    	= $row[4];
        $persone_laboratori_row->codicelab    	= $row[5];
        $persone_laboratori_row->turno    	= $row[6];

        $id = R::store($persone_laboratori_row);

    }

}

function mapLaboratoriEsterni($row){

    if ( !empty($row[4]) ){

        $row_esterno = R::dispense('esterni');

        $row_esterno->X						= $row[0 ];	//0
        $row_esterno->info					= $row[1 ];	//Informazioni cronologiche
        $row_esterno->regione				= $row[2 ];	//Regione di appartenenza
        $row_esterno->associazione			= $row[3 ];	//Associazione/Ente/Cooperativa…proponente
        $row_esterno->stradacoraggio			= $row[4 ];	//Strada di Coraggio
        $row_esterno->titolo					= $row[5 ];	//Titolo laboratorio
        $row_esterno->fascieeta				= $row[6 ];	//Laboratorio adatto a ragazzi di:
        $row_esterno->materiali				= $row[7 ];	//Intendo spedire i materiali
        $row_esterno->quota					= $row[8 ];	//Quota €
        $row_esterno->codicesocio			= $row[9 ];	//codice socio
        $row_esterno->nome					= $row[10];	//	Nome
        $row_esterno->cognome				= $row[11];	//	Cognome
        $row_esterno->email					= $row[12];	//	E-mail
        $row_esterno->telefono				= $row[13];	//	Telefono/Cell
        $row_esterno->pernotto				= $row[14];	//	Pernottamento in
        $row_esterno->quota2				= $row[15];	//	Quota €
        $row_esterno->codicesocio2			= $row[16];	//	codice socio
        $row_esterno->nome2					= $row[17];	//	Nome
        $row_esterno->cognome2				= $row[18];	//	Cognome
        $row_esterno->email2					= $row[19];	//	E-mail
        $row_esterno->telefono2				= $row[20];	//	Telefono/Cell
        $row_esterno->pernotto2				= $row[21];	//	Pernottamento in
        $row_esterno->quota3					= $row[22];	//	3°animatore Quota €
        $row_esterno->codicesocio3			= $row[23];	//	3°animatore Codice_socio
        $row_esterno->nome3					= $row[24];	//	3°animatore nome
        $row_esterno->cognome3				= $row[25];	//	3°animatore cognome
        $row_esterno->email3					= $row[26];	//	3°animatore_mail
        $row_esterno->telefono3				= $row[27];	//	3°animatore cell.
        $row_esterno->accompagnatorenome		= $row[28];	//	OSPITE/ACCOMPAGNATORE NOME
        $row_esterno->accompagnatorecognome	= $row[29];	//	OSPITE/ACCOMPAGNATORE COGNOME
        $row_esterno->accompagnatoremail		= $row[30];	//	OSPITE/ACCOMPAGNATORE MAIL
        $row_esterno->accompagnatorecell		= $row[31];	//	OSPITE/ACCOMPAGNATORE CELL.
        $row_esterno->corrente				= $row[32];	//	corrente elettrica
        $row_esterno->vincoli				= $row[33];	//	vincoli di spazio
        $row_esterno->labvicini				= $row[34];	//	laboratori vicini
        $row_esterno->materialefornito		= $row[35];	//	fornire materiali
        $row_esterno->animatore3				= $row[36];	//	3° animatore
        $row_esterno->ospite					= $row[37];	//	ospite
        $row_esterno->limitepartecipanti		= $row[38];	//	limite partecipanti
        $row_esterno->animatoredisabile		= $row[39];	//	animatore disabile
        $row_esterno->accompagnatori			= $row[40];	//	accompagnatori
        $row_esterno->disabilita				= $row[41];	//	lab non adatto a disabili fisici

        $id = R::store($row_esterno);

    }

}

function mapLaboratoriInterni($row){

    if ( !empty($row[3]) ){

        $row_interni = R::dispense('interni');

        $row_interni->X							= $row[0 ]; //	0
        $row_interni->quota						= $row[1 ]; //	Quota €
        $row_interni->codicesocio				= $row[2 ]; //	codice_socio
        $row_interni->cognome					= $row[3 ]; //	Cognome
        $row_interni->nome  					= $row[4 ]; //	Nome
        $row_interni->residenza					= $row[5 ]; //	residenza
        $row_interni->prov						= $row[6 ]; //	prov
        $row_interni->telefono					= $row[7 ]; //	tel
        $row_interni->cellulare					= $row[8 ]; //	cell
        $row_interni->email						= $row[9 ]; //	email
        $row_interni->stradacoraggio			= $row[10]; //		strada_coraggio
        $row_interni->titolo					= $row[11]; //		laboratorio
        $row_interni->fascieeta					= $row[12]; //		fascia_eta
        $row_interni->materiali					= $row[13]; //		spedizione_materiali
        $row_interni->pernotto					= $row[14]; //		pernotto
        $row_interni->arrivo					= $row[15]; //		arrivo
        $row_interni->quota2					= $row[16]; //		Quota €
        $row_interni->codicesocio2				= $row[17]; //		Codice_socio
        $row_interni->nome2						= $row[18]; //		nome_altro_anim
        $row_interni->cognome2					= $row[19]; //		cognome_altro_anim
        $row_interni->email2					= $row[20]; //		e_mail
        $row_interni->telefono2					= $row[21]; //		telefono_altro_anim
        $row_interni->cellulare2				= $row[22]; //		cellulare_altro_anim
        $row_interni->pernotto2					= $row[23]; //		pernotto_2
        $row_interni->arrivo2					= $row[24]; //		arrivo_2
        $row_interni->DataProtocollo			= $row[25]; //		Data_Protocollo
        $row_interni->nomegruppo				= $row[26]; //		NOMEGRUPPO
        $row_interni->nomezona					= $row[27]; //		NOMEZONA
        $row_interni->nomereg					= $row[28]; //		NOMEREG
        $row_interni->quota3					= $row[29]; //		3°animatore Quota €
        $row_interni->codicesocio3				= $row[30]; //		3°animatore Codice_socio
        $row_interni->nome3						= $row[31]; //		3°animatore nome
        $row_interni->cognome3					= $row[32]; //		3°animatore cognome
        $row_interni->email3					= $row[33]; //		3°animatore_mail
        $row_interni->telefono3					= $row[34]; //		3°animatore cell.
        $row_interni->accompagnatorenome		= $row[35]; //		OSPITE/ACCOMPAGNATORE NOME
        $row_interni->accompagnatorecognome		= $row[36]; //		OSPITE/ACCOMPAGNATORE COGNOME
        $row_interni->accompagnatoremail		= $row[37]; //		OSPITE/ACCOMPAGNATORE MAIL
        $row_interni->accompagnatorecell		= $row[38]; //		OSPITE/ACCOMPAGNATORE CELL.
        $row_interni->corrente					= $row[39]; //		corrente elettrica
        $row_interni->vincoli					= $row[40]; //		vincoli di spazio
        $row_interni->labvicini					= $row[41]; //		laboratori vicini
        $row_interni->materialefornito			= $row[42]; //		fornire materiali
        $row_interni->XX						= $row[43]; //
        $row_interni->animatore3				= $row[44]; //		3° animatore
        $row_interni->ospite					= $row[45]; //		ospite
        $row_interni->limitepartecipanti		= $row[46]; //		limite partecipanti
        $row_interni->animatoredisabile			= $row[47]; //		animatore disabile
        $row_interni->accompagnatori			= $row[48]; //		accompagnatori
        $row_interni->disabilita				= $row[49]; //		lab non adatto a disabili fisici

        $id = R::store($row_interni);

    }

}

function mapAnimatoriEsterni($row){

    if ( !empty($row[3]) ){

        $animatore_lab = R::dispense('animatorilab');

        $animatore_lab->num				=intval($row[0])+5000; //	n. ( va sommato 5000)
        $animatore_lab->codsocio		=$row[1]; //	codice socio
        $animatore_lab->nome			=$row[2]; //	Nome
        $animatore_lab->cognome			=$row[3]; //	Cognome
        $animatore_lab->email			=$row[4]; //	E-mail
        $animatore_lab->telefono		=$row[5]; //	Telefono/Cell
        $animatore_lab->pernotto		=$row[6]; //	Pernottamento in
        $animatore_lab->quota			=$row[7]; //	Quota €
        $animatore_lab->animatore		=$row[8]; //	animatore

        $id = R::store($animatore_lab);
    }

}

function mapAnimatoriInterni($row){

    if ( !empty($row[3]) ){

        $animatore_lab = R::dispense('animatorilab');

        $animatore_lab->num			=$row[0	]; //n.
        $animatore_lab->codsocio	=$row[1	]; //codice_socio
        $animatore_lab->cognome		=$row[2	]; //Cognome
        $animatore_lab->nome		=$row[3	]; //Nome
        $animatore_lab->residenza	=$row[4	]; //residenza
        $animatore_lab->prov		=$row[5	]; //prov
        $animatore_lab->telefono	=$row[6	]; //tel
        $animatore_lab->cell		=$row[7	]; //cell
        $animatore_lab->email		=$row[8	]; //email
        $animatore_lab->quota		=$row[9	]; //Quota €
        $animatore_lab->pernotto	=$row[10]; //pernotto
        $animatore_lab->arrivo		=$row[11]; //arrivo
        $animatore_lab->animatore	=$row[12]; //animatore

        $id = R::store($animatore_lab);

    }
}

function mapAnimatoriInterniDue($row){

    if ( !empty($row[3]) ){

        $animatore_lab = R::dispense('animatorilab');

        $animatore_lab->num			=$row[0	]; //n.
        $animatore_lab->codsocio	=$row[1	]; //Codice_socio
        $animatore_lab->cognome		=$row[2	]; //cognome_altro_anim
        $animatore_lab->nome		=$row[3	]; //nome_altro_anim
        $animatore_lab->email		=$row[4	]; //e_mail
        $animatore_lab->telefono	=$row[5	]; //telefono_altro_anim
        $animatore_lab->cell	    =$row[6	]; //cellulare_altro_anim
        $animatore_lab->quota		=$row[7	]; //Quota €
        $animatore_lab->pernotto	=$row[8	]; //pernotto_2
        $animatore_lab->arrivo		=$row[9	]; //arrivo_2
        $animatore_lab->animatore	=$row[10]; //animatore

        $id = R::store($animatore_lab);

    }
}

function mapDecodeAnimatoriEsterniLab($row){

    if ( !empty($row[3]) ){

        $lab_esterno = R::dispense('laboratoriosum');

        $lab_esterno->num				=$row[0]; //	n. (giusto gia 5000)
        $lab_esterno->regass			=$row[1]; //	Regione di appartenenza
        $lab_esterno->associazione		=$row[2]; //	Associazione/Ente/Cooperativa…proponente
        $lab_esterno->strada			=$row[3]; //	Strada di Coraggio
        $lab_esterno->lab				=$row[4]; //	Titolo laboratorio
        $lab_esterno->fasciaeta			=$row[5]; // 	Laboratorio adatto a ragazzi di:
        $lab_esterno->materialiped		=$row[6]; //	Intendo spedire i materiali
        $lab_esterno->tipo = 'esterno';

        $id = R::store($lab_esterno);

    }

}

function mapDecodeAnimatoriInterniLab($row){

    if ( !empty($row[3]) ){

        $lab_interno = R::dispense('laboratoriosum');

        $lab_interno->num				=$row[0];	//n.
        $lab_interno->strada			=$row[1];	//strada_coraggio
        $lab_interno->lab				=$row[2];	//laboratorio
        $lab_interno->fasciaeta		=$row[3];	//fascia_eta
        $lab_interno->materialiped	=$row[4];	//spedizione_materiali
        $lab_interno->tipo = 'interno';

        $id = R::store($lab_interno);

    }

}

function mapTavoleRotondeRS($row){

    if ( !empty($row[1]) ){

        $tavolers_row = R::dispense('tavolers');
        $tavolers_row->code 					        = "DADEFINIRE";
        $tavolers_row->segreteria 					    = $row[1 ];
        $tavolers_row->turno                            = $row[2 ];
        $tavolers_row->quartiere                        = $row[3 ];

        $tavolers_row->regione                          = $row[4 ];

        $tavolers_row->stradadicoraggio                 = $row[5 ];
        $tavolers_row->titolo                           = $row[7 ];
        $tavolers_row->descrizione                      = $row[8 ];

        $tavolers_row->nomegruppi                        = $row[9 ];
        $tavolers_row->codcensrif                        = $row[10];

        $tavolers_row->nomerif                           = $row[11];
        $tavolers_row->telrif                           = $row[12];
        $tavolers_row->cellrif                          = $row[13];
        $tavolers_row->mailrif                          = $row[14];

        //F 0279 T1
        list($lettera,$ordinale,$idunita) =  explode(" ",$row[19]);
        $idgruppo = $lettera.$ordinale;
        $tavolers_row->idgruppo 						= trim($idgruppo);
        $tavolers_row->idunita                          = trim($idunita);

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

function mapVincoliCodici($row){

    if ( !empty($row[0]) ){

        $vincoli_row = R::dispense('vincoli');

        $vincoli_row->codice        = $row[2];
        $vincoli_row->turn1 = 'CODICI';
        $vincoli_row->turn2 = 'CODICI';
        $vincoli_row->turn3 = 'CODICI';
        $vincoli_row->cognome = $row[3];
        $vincoli_row->nome = $row[4];
        $vincoli_row->cellulare = $row[6];

        $id = R::store($vincoli_row);

    }

}

function mapStranieri($row){

    if ( !empty($row[2]) ){

        $stranieri_row = R::dispense('stranieri');


        $stranieri_row->route        = $row[2];
        $stranieri_row->country        = $row[3];
        $stranieri_row->association     = $row[4];
        $stranieri_row->groupname     = $row[5];

        $stranieri_row->surname     = $row[7];
        $stranieri_row->name     = $row[8];

        $stranieri_row->dtnascita     = $row[9];
        $stranieri_row->gender     = $row[13];
        $stranieri_row->number = $row[14];
        $stranieri_row->cellphone = $row[15];

        $stranieri_row->emergencyname = $row[17];
        $stranieri_row->emergencycell = $row[18];

        $stranieri_row->language = $row[20];

        $stranieri_row->esigenzealim = $row[25];
        $stranieri_row->foodallergy = $row[26];
        $stranieri_row->foodallergy2 = $row[27];

        $stranieri_row->medic = $row[28];
        $stranieri_row->mobilita = $row[29];
        $stranieri_row->medicalcond = $row[30];

        $id = R::store($stranieri_row);

    }

}

function mapGestioneOneteam($row){


    if ( !empty($row[5]) ){

        $gestioneoneteam_row = R::dispense('gestioneoneteam');

        $gestioneoneteam_row->regione          = $row[6] ;   //NOMEREG
        $gestioneoneteam_row->codicesocio          = $row[7] ;   //codice_socio
        $gestioneoneteam_row->cognome          = $row[11];   // Cognome
        $gestioneoneteam_row->nome          = $row[12];   // Nome
        $gestioneoneteam_row->sesso          = $row[18];   // Sesso
        $gestioneoneteam_row->luogonascita          = $row[19];   // Luogo_Nasc.
        $gestioneoneteam_row->datanascita          = $row[20];   // Data_Nasc.
        $gestioneoneteam_row->eta          = $row[21];   // Eta
        $gestioneoneteam_row->cell          = $row[27];   // cell
        $gestioneoneteam_row->email          = $row[28];   // email
        $gestioneoneteam_row->ae          = $row[32];   // AE

        $id = R::store($gestioneoneteam_row);

    }
}

function mapCapiClanStranieriFormazione($row){

    if ( !empty($row[0]) ){

        $cc_row = R::dispense('capiclanformazione');

        $cc_row->route                 = $row[0 ];
        $cc_row->cc                 = $row[1 ];
        $cc_row->cc2                 = $row[2 ];
        $cc_row->email                 = $row[3 ];
        $cc_row->email2                 = $row[4 ];
        $cc_row->clantutor              = $row[5 ];
        $cc_row->clanospitante          = $row[6 ];
        $cc_row->clanospitati           = $row[7 ];
        $cc_row->add                    = $row[8 ];
        $cc_row->n                      = $row[9 ];
        $cc_row->entrata                = $row[10];
        $cc_row->uscita                 = $row[11];
        $cc_row->idunita                = $row[12];

        $id = R::store($cc_row);

    }
}

function mapKinderheim($row){

    if ( !empty($row[0]) ){

        $kk_row = R::dispense('kinder');

        $kk_row->nome						= $row[1 ];
        $kk_row->cognome					= $row[2 ];
        $kk_row->datan						= $row[3 ];
        $kk_row->eta						= $row[4 ];
        $kk_row->dal						= $row[5 ];
        $kk_row->al							= $row[6 ];
        $kk_row->presenzacampo				= $row[7 ];
        $kk_row->codcensimento				= $row[8 ];
        $kk_row->cognome1					= $row[9 ];
        $kk_row->nome1						= $row[10];
        $kk_row->codcensimento2				= $row[11];
        $kk_row->cognome2					= $row[12];
        $kk_row->nome2						= $row[13];
        $kk_row->email						= $row[14];
        $kk_row->cellulare					= $row[15];
        $kk_row->ruologenitori				= $row[16];
        /*
        $kk_row->soggiornogenitori			= $row[17];
        $kk_row->dietaalimentare			= $row[18];
        $kk_row->esigenzealimentari			= $row[19];
        $kk_row->allergiealimentari			= $row[20];
        $kk_row->descallergiealimentari		= $row[21];
        $kk_row->intolleranzealimentari		= $row[22];
        $kk_row->descintolleranzealimentari	= $row[23];
        $kk_row->allergiefarmaci			= $row[24];
        $kk_row->descallergiefarmaci		= $row[25];
        $kk_row->disabilita					= $row[26];
        $kk_row->patologie					= $row[27];
        $kk_row->attenzioni					= $row[28];

        $kk_row->privacy					= $row[30];
        $kk_row->schedainformativa			= $row[31];
        */

        $id = R::store($kk_row);

    }


}

function mapCapiSpalla($row){

    if ( !empty($row[0]) ){

        $kk_row = R::dispense('capispalla');

        $kk_row->nome						= $row[0 ];
        $kk_row->cognome					= $row[1 ];
        $kk_row->codicecensimento           = $row[2 ];
        $kk_row->dtnascita                  = $row[3 ];
        $kk_row->regione                    = $row[4 ];
        $kk_row->gruppo                     = $row[5 ];
        $kk_row->tipo                       = $row[6 ]; //tr o lab
        $kk_row->lingue                     = $row[7 ];

        $id = R::store($kk_row);

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
$arguments->addFlag(array('import-oneteam-offline', 'd'), 'Turn on import oneteam offline [FILE]');
$arguments->addFlag(array('import-gruppi', 'g'), 'Turn on import gruppi [API]');

$arguments->addFlag(array('import-external-lab', 'e'), 'Turn on import external lab [FILE]');
$arguments->addFlag(array('import-internal-lab', 'i'), 'Turn on import internal lab [FILE]');

$arguments->addFlag(array('import-kinderheim', 'B'), 'Turn on import kinderheim [FILE]');

$arguments->addFlag(array('import-internal-rs', 'b'), 'Turn on import internal rs lab [FILE]');
$arguments->addFlag(array('import-vincoli-rs', 'k'), 'Turn on import constraints rs lab [FILE]');
$arguments->addFlag(array('import-tavole-rs', 't'), 'Turn on import tavole rs lab [FILE]');
$arguments->addFlag(array('import-tavole-rs-v2', 'X'), 'Turn on import tavole rs lab V2 [FILE]');
$arguments->addFlag(array('import-veglie-rs', 'n'), 'Turn on import veglie rs lab [FILE]');

$arguments->addFlag(array('import-subarea', 's'), 'Turn on import sub area [FILE]');
$arguments->addFlag(array('import-route', 'u'), 'Turn on import route definition [FILE]');
$arguments->addFlag(array('import-ragazzi-internazionale', 'z'), 'Turn on import world ragazzi[FILE]');
$arguments->addFlag(array('import-cc-internazionale', 'Z'), 'Turn on import world capi clan [FILE]');
$arguments->addFlag(array('import-clan-lab', 'a'), 'Turn on import clan lab [FILE]');

$arguments->addFlag(array('import-animatori-lab-interni', 'I'), 'Turn on import animatori lab [FILE]');
$arguments->addFlag(array('import-animatori-lab-esterni', 'E'), 'Turn on import animatori lab [FILE]');

$arguments->addFlag(array('import-capi-spalla', 'C'), 'Turn on import capi spalla [FILE]');


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

        //AGESCI
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

        //EXTRA
        $i = 0;
        $running = true;
        while($running){

            $gruppi = $proxy->getGruppiExtraAgesci($i,10);

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

        // EXTRA
        $i = 0;
        $running = true;
        while($running){

            $capi = $proxy->getCapiExtraAgesci($i,10);

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

                /*
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
                */

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
                        $log->addError('['.$ragazzo->codicesocio.']'.'strada di coraggio 1 non valida : ' . $ragazzo->strada1);
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
                        $log->addError('['.$ragazzo->codicesocio.']'.'strada di coraggio 2 non valida : ' . $ragazzo->strada2);
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
                        $log->addError('['.$ragazzo->codicesocio.']'.'strada di coraggio 3 non valida : ' . $ragazzo->strada3);
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

        //EXTRA
        $i = 0;
        $running = true;
        while($running){

            $ragazzi = $proxy->getRagazziExtraAgesci($i,20);

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

                // gli esterni non hanno sesso...
                // $ragazzo_row->sesso             = $ragazzo->sesso;

                $ragazzo_row->datanascita       = $ragazzo->datanascita;

                $eta_ragazzo = getAge($ragazzo->datanascita);//Formato 1988-01-31 YYYY-MM-GG
                $ragazzo_row->eta				= $eta_ragazzo;

                $ragazzo_row->idgruppo			= $ragazzo->gruppo;
                $ragazzo_row->idunitagruppo     = $ragazzo->unita;

                /*
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
                */

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
                        $log->addError('['.$ragazzo->codicesocio.']'.'strada di coraggio 1 non valida : ' . $ragazzo->strada1);
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
                        $log->addError('['.$ragazzo->codicesocio.']'.'strada di coraggio 2 non valida : ' . $ragazzo->strada2);
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
                        $log->addError('['.$ragazzo->codicesocio.']'.'strada di coraggio 3 non valida : ' . $ragazzo->strada3);
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

        excelFileParsing($inputFileName,'mapLaboratoriInterni',2, $log, 'Lab Interno ');

    }

    if (isset($arguments_parsed['import-capi-spalla'])) {

        $inputFileName = 'capispalla.xlsx';
        if ( !empty($filename) ){
            $inputFileName = $filename;
        }

        excelFileParsing($inputFileName,'mapCapiSpalla',2, $log, 'Capi spalla');

    }




    if ( isset($arguments_parsed['import-internal-rs']) ) {

        $inputFileName = 'labrs.xlsx';
        if ( !empty($filename) ){
            $inputFileName = $filename;
        }

        excelFileParsing($inputFileName,'mapLaboratoriRS',2, $log, 'Lab Interno RS ');

    }

    if ( isset($arguments_parsed['import-vincoli-rs']) ) {

        $inputFileName = 'vincolilabrs.xlsx';
        if ( !empty($filename) ){
            $inputFileName = $filename;
        }

        excelFileParsing($inputFileName,'mapVincoliPersoneLaboratoriRS',2, $log, 'Vincoli lab RS');

    }

    if ( isset($arguments_parsed['import-tavole-rs-v2']) ) {

        $inputFileName = 'tavolersV2.xlsx';
        if ( !empty($filename) ){
            $inputFileName = $filename;
        }

        excelFileParsing($inputFileName,'mapTavoleRotondeRSv2',2, $log, 'Tavola Rotonda RS v2');

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

    if ( isset($arguments_parsed['import-clan-lab']) ) {

        $inputFileName = 'laboratoriCodici.xlsx';
        if ( !empty($filename) ){
            $inputFileName = $filename;
        }

        excelFileParsing($inputFileName,'mapVincoliCodici',2, $log, 'Vincoli Laboratorio Codici');

    }

    if ( isset($arguments_parsed['import-oneteam-offline']) ) {

        $inputFileName = 'gestioneOneTeam.xlsx';
        if ( !empty($filename) ){
            $inputFileName = $filename;
        }

        excelFileParsing($inputFileName,'mapGestioneOneteam',2, $log, 'Gestione One Team gdoc');

    }

    if ( isset($arguments_parsed['import-ragazzi-internazionale']) ) {

        $inputFileName = 'stranieri.xlsx';
        if ( !empty($filename) ){
            $inputFileName = $filename;
        }

        excelFileParsing($inputFileName,'mapStranieri',2, $log, 'Iscrizioni stranieri');

    }

    if ( isset($arguments_parsed['import-cc-internazionale']) ) {

        $inputFileName = 'ccstranieri.xlsx';
        if ( !empty($filename) ){
            $inputFileName = $filename;
        }

        excelFileParsing($inputFileName,'mapCapiClanStranieriFormazione',2, $log, 'Iscrizioni cc stranieri');

    }

    if (isset($arguments_parsed['import-external-lab'])) {

        $inputFileName = 'esterni.xlsx';
        if ( !empty($filename) ){
            $inputFileName = $filename;
        }

        excelFileParsing($inputFileName,'mapLaboratoriEsterni',2, $log, 'Lab Esterno ');

    }

    if (isset($arguments_parsed['import-kinderheim'])) {

        $inputFileName = 'kinderheim.xlsx';
        if ( !empty($filename) ){
            $inputFileName = $filename;
        }

        excelFileParsing($inputFileName,'mapKinderheim',2, $log, 'Kinderheim ');

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
                $gemellaggio_row->ordgruppo		     = $row[5];    // ordinale gruppo dove svolge servizio (decregione + ordinalegruppo = idgruppo)
                $gemellaggio_row->idunita			 = $row[6];    // unita_servizio

                if ( $row[3] == 'WW' ) { // gruppi stranieri ordinale prefissato
                    $gemellaggio_row->ordgruppo = 99001;
                    $gemellaggio_row->idunita = 'TXX';
                }


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



    if (isset($arguments_parsed['import-animatori-lab-interni'])) {

        $inputFileName = 'animatoriInterni.xlsx';
        if ( !empty($filename) ){
            $inputFileName = $filename;
        }

        excelFileParsing($inputFileName,'mapDecodeAnimatoriInterniLab',2, $log, 'Titoli',0);

        excelFileParsing($inputFileName,'mapAnimatoriInterni',2, $log, 'Lab primo animatore interno ',1);

        excelFileParsing($inputFileName,'mapAnimatoriInterniDue',2, $log, 'Lab secondo animatore interno ',2);

    }

    if (isset($arguments_parsed['import-animatori-lab-esterni'])) {

        $inputFileName = 'animatoriEsterni.xlsx';
        if ( !empty($filename) ){
            $inputFileName = $filename;
        }

        excelFileParsing($inputFileName,'mapDecodeAnimatoriEsterniLab',2, $log, 'Titoli',0);

        excelFileParsing($inputFileName,'mapAnimatoriEsterni',2, $log, 'Lab primo animatore interno ',1);

        excelFileParsing($inputFileName,'mapAnimatoriEsterni',2, $log, 'Lab secondo animatore interno ',2);

    }




} catch (Exception $e){
    \cli\out('Error : ' . $e->getMessage(). "\n");
    exit - 1;
}


