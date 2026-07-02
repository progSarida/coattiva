<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_LOG.php";
include_once CLS . "/cls_excel.php";

$db = new cls_db();
$cls_help = new cls_help();
$log = new LOG();

$last_el_id = $cls_help->getVar('last_el_id');
$elaborabili = $cls_help->getVar('elaborabili');

$jsonFilters = json_decode($cls_help->getVar('filters'), JSON_OBJECT_AS_ARRAY);
$a_filterField = array(
    "Tributo" => "Tipo_Riscossione",
    "Anomalia" => "Anomalia_ATTO",
    "Stato" => "PS_NOME"
);

$filterMsg = "";
$whereFilter = "";

foreach($jsonFilters as $filterName=>$a_filter){
    $checkFilter = 0;
    $filterMsg.="\n".$filterName.": ";

    $whereFilter.="AND ( ";
    foreach ($a_filter as $filterValue){
        if($checkFilter>0){
            $whereFilter.= " OR ";
            $filterMsg.= " o ";
        }
            
        if(!empty($filterValue)){
            $whereFilter.= $a_filterField[$filterName].' = "'.$filterValue.'"';
            $filterMsg.= $filterValue;
        }
        else{
            $whereFilter.= $a_filterField[$filterName].' = "" OR '.$a_filterField[$filterName].' is null';
            $filterMsg.= "Nessuna";
        }
            
        $checkFilter++;
    }
    $whereFilter.=" ) ";
}

if ($elaborabili==1)
{
    $whereFilter = " and flag_elaboration = 1 and Genere <> 'D' GROUP BY Utente_Comune_ID ";
}

$query_elenco_part =    "SELECT vcp.* " .
                        "FROM `v_check_partite` AS vcp " .
                        "WHERE Elaboration_Id = " . $last_el_id . " ".$whereFilter.
                        "ORDER BY Tipo_Riscossione ASC, Partita_ID DESC";

$results = $db->ExecuteQuery($query_elenco_part);
$utenti = $db->getResults($results);


if (isset($utenti)) {

    $filename = "Lista utenti_" . $last_el_id . ".xlsx";
    if ($elaborabili==1) $filename = "Lista utenti elaborabili_" . $last_el_id . ".xlsx";
    $intestazione_tab = "DATI UTENTE";

    $mod_st = new PHPExcel();
    $mod_st->setActiveSheetIndex(0);
    $rowCount = 3;

    if ($elaborabili==1)
    {
        $mod_st->getActiveSheet()->SetCellValue('A1', $intestazione_tab)->getColumnDimension('A')->setWidth("70");
        $mod_st->getActiveSheet()->SetCellValue('A2', "CODICE CATASTALE")->getColumnDimension('A')->setWidth("30");
        $mod_st->getActiveSheet()->SetCellValue('B2', "COMUNE UTENTE ID")->getColumnDimension('B')->setWidth("30");
        $mod_st->getActiveSheet()->SetCellValue('C2', "GENERE")->getColumnDimension('C')->setWidth("20");
        $mod_st->getActiveSheet()->SetCellValue('D2', "COGNOME/DENOMINAZIONE")->getColumnDimension('D')->setWidth("80");
        $mod_st->getActiveSheet()->SetCellValue('E2', "NOME")->getColumnDimension('E')->setWidth("50");
        $mod_st->getActiveSheet()->SetCellValue('F2', "CF/P.IVA")->getColumnDimension('F')->setWidth("50");
        $mod_st->getActiveSheet()->SetCellValue('G2', "INDIRIZZO")->getColumnDimension('G')->setWidth("60");
        $mod_st->getActiveSheet()->SetCellValue('H2', "NUMERO")->getColumnDimension('H')->setWidth("20");
        $mod_st->getActiveSheet()->SetCellValue('I2', "ESPONENTE")->getColumnDimension('I')->setWidth("20");
        $mod_st->getActiveSheet()->SetCellValue('J2', "INTERNO")->getColumnDimension('J')->setWidth("20");
        $mod_st->getActiveSheet()->SetCellValue('K2', "PRESSO")->getColumnDimension('K')->setWidth("100");
    }
    else
    {
        $mod_st->getActiveSheet()->SetCellValue('A1', $intestazione_tab)->getColumnDimension('A')->setWidth("70");
        $mod_st->getActiveSheet()->SetCellValue('A2', "CODICE CATASTALE")->getColumnDimension('A')->setWidth("30");
        $mod_st->getActiveSheet()->SetCellValue('B2', "COMUNE UTENTE ID")->getColumnDimension('B')->setWidth("30");
        $mod_st->getActiveSheet()->SetCellValue('C2', "GENERE")->getColumnDimension('C')->setWidth("20");
        $mod_st->getActiveSheet()->SetCellValue('D2', "COGNOME/DENOMINAZIONE")->getColumnDimension('D')->setWidth("80");
        $mod_st->getActiveSheet()->SetCellValue('E2', "NOME")->getColumnDimension('E')->setWidth("50");
        $mod_st->getActiveSheet()->SetCellValue('F2', "CF/P.IVA")->getColumnDimension('F')->setWidth("50");
        $mod_st->getActiveSheet()->SetCellValue('G2', "Nr PARTITA")->getColumnDimension('G')->setWidth("20");
        $mod_st->getActiveSheet()->SetCellValue("H2", "TRIBUTO")->getColumnDimension('H')->setWidth("30");
        $mod_st->getActiveSheet()->SetCellValue("I2", "INFO CARTELLA")->getColumnDimension('I')->setWidth("100");
        $mod_st->getActiveSheet()->SetCellValue("j2", "ANOMALIA")->getColumnDimension('j')->setWidth("70");
        $mod_st->getActiveSheet()->SetCellValue("K2", "STATO")->getColumnDimension('K')->setWidth("40");
        $mod_st->getActiveSheet()->SetCellValue('L2', "INDIRIZZO")->getColumnDimension('L')->setWidth("60");
        $mod_st->getActiveSheet()->SetCellValue('M2', "NUMERO")->getColumnDimension('M')->setWidth("20");
        $mod_st->getActiveSheet()->SetCellValue('N2', "ESPONENTE")->getColumnDimension('N')->setWidth("20");
        $mod_st->getActiveSheet()->SetCellValue('O2', "INTERNO")->getColumnDimension('O')->setWidth("20");
        $mod_st->getActiveSheet()->SetCellValue('P2', "PRESSO")->getColumnDimension('P')->setWidth("100");
        $mod_st->getActiveSheet()->SetCellValue('Q2', "ELABORAZIONE")->getColumnDimension('Q')->setWidth("30");
    }
    
    $styleArray = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
        'borders' => array(
            'outline' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,

            )
        )
    );

    foreach ($utenti as $utente) {

        $elab = $utente["flag_elaboration"] == 1 ? 'si' : 'no';
        if($elaborabili==1)
        {
            $mod_st->getActiveSheet()->SetCellValue('A' . $rowCount, $utente["CC"]);
            $mod_st->getActiveSheet()->SetCellValue('B' . $rowCount, $utente["Utente_Comune_ID"]);
            $mod_st->getActiveSheet()->SetCellValue('C' . $rowCount, $utente["Genere"]);
            $mod_st->getActiveSheet()->SetCellValue('D' . $rowCount, $utente["Cognome_Ditta"]);
            $mod_st->getActiveSheet()->SetCellValue('E' . $rowCount, $utente["Nome"]);
            $mod_st->getActiveSheet()->SetCellValue('F' . $rowCount, $utente["CF_PI"]);
            $mod_st->getActiveSheet()->SetCellValue('G' . $rowCount, $utente["Res_Via"]);
            $mod_st->getActiveSheet()->SetCellValue('H' . $rowCount, $utente["Res_Civico"]);
            $mod_st->getActiveSheet()->SetCellValue('I' . $rowCount, $utente["Res_Esponente"]);
            $mod_st->getActiveSheet()->SetCellValue('J' . $rowCount, $utente["Res_Interno"]);
            $mod_st->getActiveSheet()->SetCellValue('K' . $rowCount, $utente["Res_Presso"]);
        }
        else
        {
            $mod_st->getActiveSheet()->SetCellValue('A' . $rowCount, $utente["CC"]);
            $mod_st->getActiveSheet()->SetCellValue('B' . $rowCount, $utente["Utente_Comune_ID"]);
            $mod_st->getActiveSheet()->SetCellValue('C' . $rowCount, $utente["Genere"]);
            $mod_st->getActiveSheet()->SetCellValue('D' . $rowCount, $utente["Cognome_Ditta"]);
            $mod_st->getActiveSheet()->SetCellValue('E' . $rowCount, $utente["Nome"]);
            $mod_st->getActiveSheet()->SetCellValue('F' . $rowCount, $utente["CF_PI"]);
            $mod_st->getActiveSheet()->SetCellValue('G' . $rowCount, $utente["Comune_ID"]);
            $mod_st->getActiveSheet()->SetCellValue('H' . $rowCount, $utente["Tipo_Riscossione"]);
            $mod_st->getActiveSheet()->SetCellValue('I' . $rowCount, $utente["Info_Cartella"]);
            $mod_st->getActiveSheet()->SetCellValue('J' . $rowCount, $utente["Anomalia_ATTO"]);
            $mod_st->getActiveSheet()->SetCellValue('K' . $rowCount, $utente["PS_NOME"]);
            $mod_st->getActiveSheet()->SetCellValue('L' . $rowCount, $utente["Res_Via"]);
            $mod_st->getActiveSheet()->SetCellValue('M' . $rowCount, $utente["Res_Civico"]);
            $mod_st->getActiveSheet()->SetCellValue('N' . $rowCount, $utente["Res_Esponente"]);
            $mod_st->getActiveSheet()->SetCellValue('O' . $rowCount, $utente["Res_Interno"]);
            $mod_st->getActiveSheet()->SetCellValue('P' . $rowCount, $utente["Res_Presso"]);
            $mod_st->getActiveSheet()->SetCellValue('Q' . $rowCount, $elab);
        }
        

        $rowCount++;
    }

    $mod_st->getActiveSheet()->mergeCells('A1:Q1');


    $mod_st->getDefaultStyle()->applyFromArray($styleArray);
    $mod_st->getActiveSheet()->getStyle("A1")->getFont()->setBold(true);
    $mod_st->getActiveSheet()->getStyle("A2:Q2")->getFont()->setBold(true);
    $mod_st->getActiveSheet()->getStyle('A1:Q' . ($rowCount - 1))->applyFromArray(
        array('borders' => array(
            'allborders'    => array('style' => PHPExcel_Style_Border::BORDER_THIN)
        ))
    );
    $mod_st->getActiveSheet()->getStyle('A1:Q' . ($rowCount - 1))->getAlignment()->setWrapText(true);
    $mod_st->getActiveSheet()->setTitle("DATI UTENTE");
    $objWriter = PHPExcel_IOFactory::createWriter($mod_st, 'Excel2007');

    ob_start();
    $objWriter->save("php://output");
    $xlsData = ob_get_contents();

    ob_end_clean();
    $obj = array(
        'esito' => 'OK',
        'message' => 'File Excel creato'.$filterMsg,
        'nome_file' => $filename,
        "data" => null
    );
    $obj["data"] = "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64," . base64_encode($xlsData);

    $json = json_encode($obj);

    echo $json;
    return;
} else {
    echo json_encode(['esito' => 'KO', 'message' => 'DATI ASSENTI']);
    return;
}
