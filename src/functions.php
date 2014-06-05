<?php
/**
 * User: lancio
 * Date: 05/06/14
 * Time: 23:04
 */

function generaCodiceCensitoAgesci($organizzazione,$codicegruppo,$codiceCensimento)
{
    $codice = array();

    // categoria
    switch ($organizzazione) {
        case 'Partecipante Adulto':
            $codice[0] = 'B';
            $codice[1] = 'B';
            break;
        case 'Partecipante Giovane':
            $codice[0] = 'A';
            $codice[1] = 'B';
            break;
        case 'kinderheim':
            $codice[0] = 'A';
            $codice[1] = 'A';
            break;
        default:
            $codice[0] = 'X';
            $codice[1] = 'X';
            break;
    }

    // codice virtuale clan
    $codice[2] = 0;
    $codice[3] = 0;
    $codice[4] = 0;
    $codice[5] = 0;

    // codice virtuale personale
    $codice[6] = 0;
    $codice[7] = 0;
    $codice[8] = 0;
    $codice[9] = 0;
    $codice[10] = 0;
    $codice[11] = 0;
    $codice[12] = 0;

    // versione [cambiera' quando saremo all'evento nelle ristampe]
    $codice[13] = 0;
}

function getAge($birthday)
{
    $datetime1 = new DateTime($birthday);
    $datetime2 = new DateTime(date('Y-m-d'));
    $diff = $datetime1->diff($datetime2);

    return $diff->format('%y');
}

function excelFileParsing($inputFileName, $funMap, $from_row_number, $log, $desc)
{
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

function mapLaboratoriRS($row)
{
    if ( !empty($row[1]) ) {

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

function mapTavoleRotondeRS($row)
{
    if ( !empty($row[0]) && !empty($row[1]) ) {

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

function mapVeglieRS($row)
{
    if ( !empty($row[0]) ) {

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

function mapVincoliCodici($row)
{
    if ( !empty($row[0]) ) {

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

function mapStranieri($row)
{
    if ( !empty($row[2]) ) {

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

function mapGestioneOneteam($row)
{
    if ( !empty($row[5]) ) {

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
