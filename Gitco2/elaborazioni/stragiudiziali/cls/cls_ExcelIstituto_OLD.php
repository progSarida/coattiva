<?php
include_once CLS . "/XLSGenerator/src/SimpleXLSXGen.php";

class ExcelIstituto{

    protected $mod_st;
    protected $mod_pr;
    protected $rowCount;
    protected $configs;
    protected $istituto;
    protected $a_utenti;
    protected $proc_id;
    protected $CC;
    protected $tax_type;
    protected $log;
    protected $filepath;
    protected $tipo;

    public $lastCreated;

    function __construct()
    {
        $this->mod_st = new PHPExcel();
    }

    public function Set($variabile,$valore)
    {
        $this->{$variabile}= $valore;
        return $this;
    }

    public function InizioFoglio()
    {
       
        $istituto = $this->istituto;
        $mod_st = $this->mod_st;
        $intestazione_tab = "DATI RICHIESTI DAL GESTORE";

        $mod_st->setActiveSheetIndex(0);
        $this->rowCount = 3;

        $this->configs = "SI, NO";

        $mod_st->getActiveSheet()->SetCellValue('A1', $intestazione_tab)->getColumnDimension('A')->setWidth("70");

        $mod_st->getActiveSheet()->SetCellValue("K1", "DATI LA CUI COMPILAZIONE E' RISERVATA AL TERZO")->getColumnDimension('I')->setWidth("70");
        $mod_st->getActiveSheet()->SetCellValue("L1", "DESCRIZIONE DETTAGLIATA DEL RAPPORTO")->getColumnDimension('J')->setWidth("70");
        $mod_st->getActiveSheet()->SetCellValue('A2', "UTENTE ID")->getColumnDimension('A')->setWidth("50");
        $mod_st->getActiveSheet()->SetCellValue('B2', "CC")->getColumnDimension('B')->setWidth("50");
        $mod_st->getActiveSheet()->SetCellValue('C2', "COGNOME/DENOMINAZIONE")->getColumnDimension('C')->setWidth("50");
        $mod_st->getActiveSheet()->SetCellValue('D2', "NOME")->getColumnDimension('D')->setWidth("50");
        $mod_st->getActiveSheet()->SetCellValue('E2', "CF/P.IVA")->getColumnDimension('E')->setWidth("50");
        $mod_st->getActiveSheet()->SetCellValue('F2', "RES./CON SEDE IN")->getColumnDimension('F')->setWidth("50");
        $mod_st->getActiveSheet()->SetCellValue('G2', "VIA")->getColumnDimension('G')->setWidth("50");
        $mod_st->getActiveSheet()->SetCellValue('H2', "TITOLO")->getColumnDimension('H')->setWidth("350");
        $mod_st->getActiveSheet()->SetCellValue('I2', "IMPORTO DEL DEBITO")->getColumnDimension('I')->setWidth("50");
        $mod_st->getActiveSheet()->SetCellValue('J2', "ENTE CREDITORE")->getColumnDimension('J')->setWidth("50");
        $mod_st->getActiveSheet()->SetCellValue("K2", "IL SOGGETTO RISULTA AVERE RAPPORTI CON QUESTO ENTE/ISTITUTO: " )->getColumnDimension('K')->setWidth("90");
        $mod_st->getActiveSheet()->SetCellValue("L2", "INIZIO")->getColumnDimension('L')->setWidth("50");
        $mod_st->getActiveSheet()->SetCellValue("M2", "FINE")->getColumnDimension('M')->setWidth("50");
        $mod_st->getActiveSheet()->SetCellValue("N2", "IN ESSERE")->getColumnDimension('N')->setWidth("70");
        $mod_st->getActiveSheet()->SetCellValue("O2", "DESCRIZIONE/TIPOLOGIA")->getColumnDimension('O')->setWidth("70");
        $mod_st->getActiveSheet()->SetCellValue("P2", "ALTRI INTESTATARI")->getColumnDimension('P')->setWidth("70");
        $mod_st->getActiveSheet()->SetCellValue("Q2", "IBAN")->getColumnDimension('Q')->setWidth("50");
        $mod_st->getActiveSheet()->SetCellValue("R2", "IBAN (altro se esistente)")->getColumnDimension('Q')->setWidth("50");
        $mod_st->getActiveSheet()->SetCellValue("S2", "ALTRO IDENTIFICATIVO")->getColumnDimension('R')->setWidth("80");
        $mod_st->getActiveSheet()->SetCellValue("T2", "CAPIENZA/DISPONIBILITA'")->getColumnDimension('S')->setWidth("80");
        $mod_st->getActiveSheet()->SetCellValue("U2", "EVENTUALI LIMITI DI PIGNORABILITA' CONOSCIUTI DALL'ENTE")->getColumnDimension('T')->setWidth("100");
        $mod_st->getActiveSheet()->SetCellValue("V2", "PRECEDENTI PIGNORAMENTI/SEQUESTRI/CESSIONI -ULTERIORI INFORMAZIONI EX ART. 547 C.p.C.")->getColumnDimension('U')->setWidth("150");

        $mod_st->getActiveSheet()->getProtection()->setSheet(true);
        $this->mod_st = $mod_st;
        
       
    }

    public function FineFoglio()
    {
        $rowCount_dic = $this->rowCount + 3;
        $mod_st = $this->mod_st;
        $modulo = "  \n \n \n DICHIARAZIONE SOSTITUTIVA DELL’ATTO DI NOTORIETA’ \n " .
            "(Art.47 del D.P.R. 28 dicembre 2000, n. 445)\n \n " .
            " Il/la sottoscritto/a _________________________________________________________________ \n " .
            " nato/a a ______________________________________________________________(__________) il ______________________________________________________________________________ \n" .
            "  codice fiscale ____________________________________________________________________ \n" .
            " residente in ___________________________________________________________(__________) \n" .
            " Via/Piazza/, ecc. ______________________________________________________n. __________ \n" .
            " (se la dichiarazione è resa da società) in qualità di ________________________________________ \n" .
            "  della società _____________________________________________________________________ \n" .
            "codice fiscale ________________________________ P.IVA_______________________________ \n" .
            " con sede legale in ______________________________________________________(__________)\n" .
            " Via/Piazza/, ecc. ______________________________________________________n. __________ \n" .
            " con riferimento alle suindicate richieste di dichiarazione stragiudiziale, consapevole delle sanzioni penali comminate in caso di dichiarazioni non veritiere e falsità negli atti, richiamate dall'art. 76 del D.P.R. 445 del 28 dicembre 2000, sotto la propria responsabilità, dichiara che le notizie riportate nella presente dichiarazione sono reali, ed ai sensi dell’art. 38 del D.P.R. 445/2000, allega copia di un documento di identità in corso di validità. \n" .
            " _______________________li, ____________  ______________________________ \n" .
            " (firma del dichiarante) ";


        $mod_st->getActiveSheet()->SetCellValue('A' . $rowCount_dic, $modulo)->getRowDimension($rowCount_dic)->setRowHeight(220);

        $mod_st->getActiveSheet()->mergeCells('A1:K1');

        $mod_st->getActiveSheet()->mergeCells('A' . ($rowCount_dic) . ':K' . ($rowCount_dic));

        $mod_st->getActiveSheet()->setTitle("COMPILAZIONE");

        $mod_st->createSheet();

        $mod_st->setActiveSheetIndex(1);

        $mod_st->getActiveSheet()->SetCellValue('A1', "DATI LA CUI COMPILAZIONE E' RISERVATA AL TERZO")->getColumnDimension('A')->setWidth("70");

        $mod_st->getActiveSheet()->mergeCells('A1:E1');

        $mod_st->getActiveSheet()->SetCellValue('A2', 'ISTRUZIONI PER LA COMPILAZIONE')->getColumnDimension('A')->setWidth("70");
        $mod_st->getActiveSheet()->SetCellValue('A3', 'IL SOGGETTO RISULTA AVERE RAPPORTI CON QUESTO ENTE/ISTITUTO-INDICARE SI/NO')->getColumnDimension('A')->setWidth("70");
        $mod_st->getActiveSheet()->SetCellValue('A4', 'INIZIO-INDICARE LA DATA DI INIZIO DEL RAPPORTO')->getColumnDimension('A')->setWidth("70");
        $mod_st->getActiveSheet()->SetCellValue('A5', 'FINE-INDICARE LA DATA DI FINE DEL RAPPORTO')->getColumnDimension('A')->setWidth("70");
        $mod_st->getActiveSheet()->SetCellValue('A6', 'IN ESSERE-INDICARE SI/NO E\' UN CAMPO ALTERNATIVO ALL\'INDICAZIONE DELLA DATA DI INIZIO/FINE DEL RAPPORTO')->getColumnDimension('A')->setWidth("70");
        $mod_st->getActiveSheet()->SetCellValue('A7', 'DESCRIZIONE/TIPOLOGIA-INDICARE LA DESCRIZIONE DEL RAPPORTO: ES. CONTO CORRENTE/LIBRETTO/BUONI FRUTTIFERI/PENSIONE')->getColumnDimension('A')->setWidth("70");
        $mod_st->getActiveSheet()->SetCellValue('A8', 'ALTRI INTESTATARI-INDICARE ALTRI INTESTATARI DEL RAPPORTO SE PRESENTI, ES. COINTESTATARIO DEL CONTO CORRENTE')->getColumnDimension('A')->setWidth("70");
        $mod_st->getActiveSheet()->SetCellValue('A9', 'IBAN-INDICARE GLI ESTREMI NEL CASO IN CUI IL SOGGETTO SIA  TITOLARE DI RAPPORTI DI CONTO CORRENTE. NON INDICARE NEL CASO IN CUI IL SOGGETTO SIA TITOLARE DI PENSIONE')->getColumnDimension('A')->setWidth("70");
        $mod_st->getActiveSheet()->SetCellValue('A10', 'ALTRO IDENTIFICATIVO-INDICARE GLI IDENTIFICATIVI DEL RAPPORTO, ES. GLI ESTREMI DEL LIBRETTO. NON INDICARE NEL CASO IN CUI IL SOGGETTO SIA TITOLARE DI PENSIONE')->getColumnDimension('A')->setWidth("70");
        $mod_st->getActiveSheet()->SetCellValue('A11', 'CAPIENZA/DISPONIBILITA-INDICARE LA CAPIENZA DEL CONTO/DEL LIBRETTO/DEI BUONI FRUTTIFERI O DELL\'IMPORTO DELLA PENSIONE DISPONIBILE')->getColumnDimension('A')->setWidth("70");
        $mod_st->getActiveSheet()->SetCellValue('A12', 'EVENTUALI LIMITI DI PIGNORABILITA\' CONOSCIUTI DALL\'ENTE-INDICARE SE CONOSCIUTI, COME NEL CASO DI EROGAZIONE DI PENSIONE DI INVALIDITA')->getColumnDimension('A')->setWidth("70");
        $mod_st->getActiveSheet()->SetCellValue('A13', 'PRECEDENTI PIGNORAMENTI/SEQUESTRI/CESSIONI- SPECIFICARE (EX ART. 547 C.P.C.) I SEQUESTRI, PIGNORAMENTI, CESSIONI, PRECEDENTEMENTE NOTIFICATI E CHE AVETE ACCETTATO IN MODO DA COMPRENDERE LA PRIORITA DELLA RICHIESTA')->getColumnDimension('A')->setWidth("70");
        $mod_st->getActiveSheet()->setTitle("ISTRUZIONI");
        $this->mod_st = $mod_st;
    }

    public function PerOgniUtente($utente)
    {
        
        $mod_st = $this->mod_st;
        $rowCount = $this->rowCount;
        $mod_st->getActiveSheet()->SetCellValue('A' . $rowCount, $utente["Utente_ID"]);
        $mod_st->getActiveSheet()->SetCellValue('B' . $rowCount, $utente["CC"]);
        $mod_st->getActiveSheet()->SetCellValue('C' . $rowCount, $utente["Cognome_Ditta"]);
        $mod_st->getActiveSheet()->SetCellValue('D' . $rowCount, $utente["Nome"]);
        $mod_st->getActiveSheet()->SetCellValue('E' . $rowCount, $utente["CF_PI"]);
        $mod_st->getActiveSheet()->SetCellValue('F' . $rowCount, $utente["Res_Comune"]);
        $mod_st->getActiveSheet()->SetCellValue('G' . $rowCount, $utente["Res_Via"]);
        
        $concat_atto = "";

        foreach ($utente["TIPI_ATTO"] as $doc_type) {
            $concat_atto .= " - " . $doc_type;
        }

        $mod_st->getActiveSheet()->SetCellValue('H' . $rowCount, $concat_atto);

        $tot_dov = 0;

        foreach ($utente["TOTALE_DOVUTI"] as $tot_due) {
            $tot_dov += $tot_due;
        }

        $mod_st->getActiveSheet()->SetCellValue('I' . $rowCount, $tot_dov);

        $mod_st->getActiveSheet()->SetCellValue('J' . $rowCount, $utente["Denominazione_Ente"]);
        $mod_st->getActiveSheet()->getStyle('K' . ($rowCount) . ':U' . ($rowCount))->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
        $objValidation = $mod_st->getActiveSheet()->getCell('K' . $rowCount)->getDataValidation();
        $objValidation->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
        $objValidation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
        $objValidation->setAllowBlank(false);
        $objValidation->setShowInputMessage(true);
        $objValidation->setShowErrorMessage(true);
        $objValidation->setShowDropDown(true);
        $objValidation->setErrorTitle('Input error');
        $objValidation->setError('IL valore non è presente nella lista.');
        $objValidation->setPromptTitle('Seleziona');
        $objValidation->setPrompt('Seleziona un valore dalla drop-down list.');
        $objValidation->setFormula1('"' . $this->configs . '"');
        $mod_st->getActiveSheet()->SetCellValue('L' . $rowCount, "");
        $mod_st->getActiveSheet()->SetCellValue('M' . $rowCount, "");
        $mod_st->getActiveSheet()->SetCellValue('N' . $rowCount, "");
        $mod_st->getActiveSheet()->SetCellValue('O' . $rowCount, "");
        $mod_st->getActiveSheet()->SetCellValue('P' . $rowCount, "");
        $mod_st->getActiveSheet()->SetCellValue('Q' . $rowCount, "");
        $mod_st->getActiveSheet()->SetCellValue('R' . $rowCount, "");
        $mod_st->getActiveSheet()->SetCellValue('S' . $rowCount, "");
        $mod_st->getActiveSheet()->SetCellValue('T' . $rowCount, "");
        $mod_st->getActiveSheet()->SetCellValue('U' . $rowCount, "");

        $rowCount++;
        $this->mod_st = $mod_st;
        $this->rowCount = $rowCount;
        
    }

    public function SalvaSuDisco($print_type,$contatoreRecord = 0)
    {
        $istituto = $this->istituto;
        $mod_st = $this->mod_st;
        $c = $this->CC;
        $lastId_stragiudiziali = $this->proc_id;
        $tax_type = $this->tax_type;
        $objWriter = PHPExcel_IOFactory::createWriter($mod_st, 'Excel2007');
        
       // $filename = "Elenco_Stragiudiziale_Banca_" . $c . "_" . $istituto["Banca_ID"]. ".xlsx";
        $filename = "Elenco_Stragiudiziale_$this->tipo"."_" . $c ."_%placeholder%.xlsx";
        if($print_type != "temp") {
            $path = STRAGIUDIZIALE . "/" . $lastId_stragiudiziali;
            if( !is_dir($path)) mkdir($path);
            //$path = $utils->crea_dir(STRAGIUDIZIALE . "/" . $lastId_stragiudiziali);
            
        }
        else{
            $path = STRAGIUDIZIALE . "/temp";
            if( !is_dir($path)) mkdir($path);
            //$path = $utils->crea_dir(STRAGIUDIZIALE . "/temp");
            //$nameFile = "Stragiudiziale_Banca_" . $c . "_" . $istituto['Banca_ID'] . "_" . $tax_type . "_" . date('Y-m-d') . "_".$contatoreRecord.".pdf";
            $filename = "tempBankExcell.xlsx";
        }
        
        $objWriter->save(str_replace(__FILE__, $path . "/" . $filename, __FILE__));
        
        $this->filepath = $path . "/" . $filename;
        $this->lastCreated = STRAGIUDIZIALEWEB . "/temp". $filename;
        
    }

    public function CreaCopia($istituto_id)
    {
        $file = $this->filepath;
        $newfile = str_replace("%placeholder%",$istituto_id,$file);
        copy($file, $newfile);

    }
}