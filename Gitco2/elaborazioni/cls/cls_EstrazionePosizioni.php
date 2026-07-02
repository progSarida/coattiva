<?php

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_LOG.php";
include_once CLS . "/cls_excel.php";

include_once CLS ."/traits.php";

class EstrazionePosizioni
{

    public $mod_st;
    public $filename;
    public $cls_db;
    public $rowCount;
    public $cls_help;

    use tSelectSQL;

    public function __construct($cls_help)
    {
        $this->mod_st = new PHPExcel();
        $this->cls_help = $cls_help;
        
    }

    public function toItalian($date)
    {
        return $this->cls_help->toItalianDate($date);
    }
    public function Intestazione()
    {

        $mod_st = $this->mod_st;
        $mod_st->setActiveSheetIndex(0);
        $mod_st->getActiveSheet()->setTitle("COMPILAZIONE");

        $creaCella = fn($colonna,$riga,$stringa) =>$mod_st
        ->getActiveSheet()->SetCellValue($colonna.$riga, $stringa)
        ->getColumnDimension($colonna)->setWidth(strlen($stringa)+4);

        
        $creaPrimaRiga = fn($colonna,$stringa) =>$creaCella($colonna,"1",$stringa);
        
        $creaPrimaRiga("A","ENTE CREDITORE");
        $creaPrimaRiga("C","DEBITORE");
        $creaPrimaRiga("K","INDIRIZZO ATTUALE"); //COMUNE DI RESIDENZA ATTUALE
        $creaPrimaRiga("L","INDIRIZZO ATTUALE");//INDIRIZZO DI RESIDENZA ATTUALE
        $creaPrimaRiga("M","CONFERMATO");
        $creaPrimaRiga("N","SIATEL O ANPR NUOVO INDIRIZZO");//Siatel o ANPR COMUNE DI RESIDENZA
        $creaPrimaRiga("O","SIATEL O ANPR");//Siatel o ANPR INDIRIZZO DI RESIDENZA
        $creaPrimaRiga("P","PROCEDURE DI PIGNORAMENTO ESPERIBILI/ESPERITE");
        $creaPrimaRiga("AJ","PREAVVISO DI FERMO AMMINISTRATIVO");
        $creaPrimaRiga("AK","IPOTECA (BENI IMMOBILI)");

        $creaSecondaRiga = fn($colonna,$stringa) =>$creaCella($colonna,"2",$stringa);
        
        $creaSecondaRiga("A","CC");
        $creaSecondaRiga("B","ENTE CREDITORE");
        $creaSecondaRiga("C","UTENTE ID");
        $creaSecondaRiga("D","TITOLO");
        $creaSecondaRiga("E","IMPORTO DEL DEBITO");
        $creaSecondaRiga("F","COGNOME/DENOMINAZIONE");
        $creaSecondaRiga("G","NOME");
        $creaSecondaRiga("H","CF/P.IVA");
        $creaSecondaRiga("I","COMUNE NASCITA");
        $creaSecondaRiga("J","DATA NASCITA");
        $creaSecondaRiga("K","RES./CON SEDE IN");
        $creaSecondaRiga("L","VIA/STRADA PIAZZA CIV./KM ETC.");
        $creaSecondaRiga("M","Inserire i valori SI/NO");
        $creaSecondaRiga("N","NUOVO INDIRIZZO RES./CON SEDE IN");
        $creaSecondaRiga("O","NUOVO INDIRIZZO VIA/STRADA PIAZZA CIV./KM ETC.");
        $creaSecondaRiga("P","DATORE DI LAVORO (DENOMINAZIONE)");
        $creaSecondaRiga("Q","DATORE DI LAVORO (P.IVA)");
        $creaSecondaRiga("R","DATORE DI LAVORO (C.F.)");
        $creaSecondaRiga("S","DATORE DI LAVORO (COMUNE SEDE)");
        $creaSecondaRiga("T","DATORE DI LAVORO (CAP SEDE) ");
        $creaSecondaRiga("U","DATORE DI LAVORO (VIA/PIAZZA/STRADA CIV. INT. KM. SC. ETC.)");
        $creaSecondaRiga("V","DATORE DI LAVORO (PEC)");
        $creaSecondaRiga("W","BANCA/POSTA DENOMINAZIONE");
        $creaSecondaRiga("X","BANCA/POSTA P.IVA");
        $creaSecondaRiga("Y","ISTITUTI PREVIDENZIALI");
        $creaSecondaRiga("Z","ALTRI TERZI FITTI PIGIONI ALTRI CREDITI");
        $creaSecondaRiga("AA","CREDITORE (DENOMINAZIONE)");
        $creaSecondaRiga("AB","CREDITORE (COMUNE SEDE)");
        $creaSecondaRiga("AC","CREDITORE (CAP SEDE)");
        $creaSecondaRiga("AD","CREDITORE (VIA/PIAZZA/STRADA CIV. INT. KM. SC. ETC.)");
        $creaSecondaRiga("AE","CREDITORE (TITOLO)");
        $creaSecondaRiga("AF","IMMOBILI (DESCRIZIONE)");
        $creaSecondaRiga("AG","IMMOBILI (TITOLO)");
        $creaSecondaRiga("AH","BENI MOBILI NON REGISTRATI");
        $creaSecondaRiga("AI","PEGNO EX ART. 2787 C.C.");
        $creaSecondaRiga("AJ","AUTOVEICOLI-NATANTI-AEROMOBILI");
        $creaSecondaRiga("AK","IMMOBILI REGISTRATI");

    }

    public function PrendiAtti($utente,$a_atti)
    {
        $a_atti = array_filter($a_atti, function($item) use($utente) {
            if ($item["Utente_ID"] == $utente["Utente_ID"])
                return $item;
        });

        $a_atti = array_values($a_atti);
        
        $fai_Tipo_atto = function($atto)
        {
            if(is_null($atto)) return "";
            return $atto["TIPO_ATTO"]." n.r. ".$atto["ID_Cronologico"]."/".$atto["Anno_Cronologico"];
        };

        $componi_array = function($index) use ($a_atti,&$res_atti,&$res_Totale_Dovuti,&$componi_array,$fai_Tipo_atto)
        {
                if(count($a_atti)>$index){
                    $res_atti[]=$fai_Tipo_atto($a_atti[$index]);
                    $res_Totale_Dovuti[]=$a_atti[$index]["Totale_Dovuto_ATTO"];
                    $componi_array($index+1);
                }
        };
        $componi_array(0);
        $utente["TIPI_ATTO"] = $res_atti;
        $utente["TOTALE_DOVUTI"] = $res_Totale_Dovuti; 
        
        return $utente;
    }
    public function SalvaUtente($utente,$i,$atti)
    {
        $rowsToAdd = 3;
        $i = $i+$rowsToAdd;
        $utente = $this->PrendiAtti($utente,$atti);
        $creaCellaLunghezzaFissa = fn($colonna,$riga,$stringa) =>$this->mod_st
        ->getActiveSheet()->SetCellValue($colonna.$riga, $stringa);

        $creaCellaLunghezzaFissa("A",$i,$utente["CC"]);
        $creaCellaLunghezzaFissa("B",$i,$utente["Denominazione_Ente"]);
        $creaCellaLunghezzaFissa("C",$i,$utente["Utente_ID"]);
        $creaCellaLunghezzaFissa("F",$i,$utente["Cognome_Ditta"]);
        $creaCellaLunghezzaFissa("G",$i,$utente["Nome"]);
        $creaCellaLunghezzaFissa("H",$i,$utente["CF_PI"]);
        $creaCellaLunghezzaFissa("I",$i,$utente["Comune_Nascita"]);
        $creaCellaLunghezzaFissa("J",$i,$this->toItalian($utente["Data_Nascita"]));
        $creaCellaLunghezzaFissa("K",$i,$utente["Res_Comune"]);
        $creaCellaLunghezzaFissa("L",$i,$utente["Res_Via"]." ".$utente["Res_Civico"]);
        $this->CreaDropDown('M',$i);


        $concat_atto = "";

        if (is_array($utente["TIPI_ATTO"]))
            foreach ($utente["TIPI_ATTO"] as $doc_type) {
                $concat_atto .= " - " . $doc_type;
            }
        
        $creaCellaLunghezzaFissa("D",$i,$concat_atto);
        $tot_dov = 0;
        
        if(is_array($utente["TOTALE_DOVUTI"]))
            foreach ($utente["TOTALE_DOVUTI"] as $tot_due) {
                $tot_dov += $tot_due;
            }
        
        $creaCellaLunghezzaFissa("E",$i,$tot_dov);
    }

    public function Stile()
    {
        $mod_st = $this->mod_st;
        $rowCount = $this->rowCount;

        $styleArray = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            ),
            'borders' => array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
    
                )
            )
        );
        $mod_st->setActiveSheetIndex(0);
        $mod_st->getDefaultStyle()->applyFromArray($styleArray);
        
        $mod_st->getActiveSheet()->getStyle("A1:AK1")->getFont()->setBold(true);
        
        $mod_st->getActiveSheet()->getStyle("A2:AK2")->getFont()->setBold(true);
        
        $mod_st->getActiveSheet()->getStyle('A1:AK' . ($rowCount + 2))->applyFromArray(
            array('borders' => array(
                'allborders'    => array('style' => PHPExcel_Style_Border::BORDER_THIN)
            ))
        );
        
        $mod_st->getActiveSheet()->getStyle('A1:AK' . ($rowCount + 2))->getAlignment()->setWrapText(true);
        //$mod_st->getActiveSheet()->setTitle("DATI UTENTE");

        
        //allargo tutte le colonne
        
        $sheet = $mod_st->getActiveSheet();
        $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);
        /** @var PHPExcel_Cell $cell */
        foreach ($cellIterator as $cell) {
            $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
            

        }
        
        //euro const FORMAT_CURRENCY_EUR_SIMPLE        = '[$EUR ]#,##0.00_-';
        $mod_st->getActiveSheet()->getStyle('E3:E' . ($rowCount + 2))
            ->getNumberFormat()
            ->setFormatCode('[$€ ]#,##0.00_-');

        //totali a destra
        $mod_st->getActiveSheet()->getStyle('E3:E' . ($rowCount + 2))->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);   
        
        $mod_st->setActiveSheetIndex(1);

    }

    function CreaDropDown($colonna,$i)
    {
        $objValidation = $this->mod_st->getActiveSheet()->getCell($colonna.$i)->getDataValidation();
        $objValidation->setType( PHPExcel_Cell_DataValidation::TYPE_LIST );
        $objValidation->setErrorStyle( PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
        $objValidation->setAllowBlank(false);
        $objValidation->setShowInputMessage(true);
        $objValidation->setShowErrorMessage(true);
        $objValidation->setShowDropDown(true);
        $objValidation->setErrorTitle('ERRORE !');
        $objValidation->setError('Valore non previsto.');
        $objValidation->setPromptTitle('Scegli un valore');
        $objValidation->setPrompt('Scegli un valore dalla lista');
        $objValidation->setFormula1('"SI,NO"');

    }
    
    public function Istruzioni()
    {
        $istruzioni = array(
            "INDIRIZZO ATTUALE, CAMPI RES./CON SEDE IN …..VIA:",
            "Se l’indirizzo presente in SIATEL coincide con quello indicato nel file indicare SI o nel campo CONFERMATO. ",
            "Nel caso in cui venga indicato NO, si deve procedere all’inserimento del dato nei campi NUOVO INDIRIZZO RES./CON SEDE e VIA/STRADA/PIAZZA CIV. KM ETC ",
            "",
            "PROCEDURE DI PIGNORAMENTO",
            "Con riferimento ai campi relativi al pignoramento presso il DATORE DI LAVORO:",
            "Inserire i dati del datore di lavoro reperiti tramite il SIATEL, mediante analisi del sostituto di imposta indicato nel modello 730, ",
            "indicando ragione sociale partita iva e/o codice fiscale indirizzo, civ./km/etc. PEC, ovvero quanto reperito.",
            "",
            "Con riferimento ai campi relativi al pignoramento presso BANCA/POSTA ",
            "Inserire la denominazione e la partita iva della banca/posta SE reperiti.",
            "A tutte le banche/alla posta la procedura prevede già l’invio massivo di una richiesta di resa di ",
            "informazioni stragiudiziali (75-bis del D.P.R. 29 settembre 1973, n° 602).",
            "",
            "Con riferimento ai campi relativi al pignoramento presso ISTITUTI PREVIDENZIALI",
            "Inserire la denominazione e la partita iva dell’Istituto previdenziale ",
            "SE reperito tramite il SIATEL mediante analisi del sostituto di imposta indicato nel modello 730, ",
            "indicando ragione sociale partita iva e/o codice fiscale indirizzo, civ./km/etc. PEC, ovvero quanto reperito.",
            "",
            "Con riferimento ai campi relativi al pignoramento di ALTRI TERZI FITTI PIGIONI ALTRI CREDITI",
            "Inserire quanto noto o quanto eventualmente reperito tramite il SIATEL o tramite le comunicazioni antimafia inviate all’ufficio competente ",
            "o altre banche dati in possesso dell’ufficio tributi.",
            "",
            "Con riferimento ai campi relativi al pignoramento di IMMOBILI",
            "Verificare prima il valore del debito e se superiore al limite per il quale si rende attuabile ",
            "il pignoramento (€ 120.000,00 al 01/01/2023), inserire gli eventuali immobili/terreni detenuti a diverso titolo dal debitore ",
            "ed il relativo titolo, ",
            "SE reperiti tramite banca dati IMU-TARI o SISTER o altre banche dati in possesso dell’ufficio tributi.",
            "",
            "BENI MOBILI NON REGISTRATI",
            "Campo generalmente riservato all’Ufficiale della riscossione",
            "",
            "PEGNO",
            "Compilazione del campo non necessaria",
            "",
            "",
            "PROCEDURE CAUTELARI",
            "",
            "PREAVVISO DI FERMO AMMINISTRATIVO",
            "Campo del campo non necessaria in quanto per il preavviso di fermo amministrativo, ",
            "operante in genere su autoveicoli viene eseguito mediante interrogazione massiva alle banche dati dell’ACI ",
            "",
            "IPOTECA",
            "Verificare prima il valore del debito e se superiore al limite per il quale si rende attuabile l’ipoteca (€ 20.000,00 al 01/01/2023), ",
            "inserire gli eventuali immobili/terreni detenuti a diverso titolo dal debitore ed il relativo titolo, ",
            "SE reperiti tramite banca dati IMU-TARI o SISTER o altre banche dati in possesso dell’ufficio tributi."
        );
        
        $mod_st = $this->mod_st;
        $mod_st->createSheet(1);
        $mod_st->setActiveSheetIndex(1);
        $mod_st->getActiveSheet()->setTitle("ISTRUZIONI");
        $i = 0;
        $lunghezza_riga = 0;
        foreach($istruzioni as $riga)
        {
            $y = $i+1;
            $mod_st->getActiveSheet()->SetCellValue("A$y", $istruzioni[$i]);
            $lunghezza_riga_attuale = strlen($istruzioni[$i]);
            if ($lunghezza_riga_attuale>$lunghezza_riga) $lunghezza_riga = $lunghezza_riga_attuale;
            $i++;
        }
        
        $mod_st->getActiveSheet()->getColumnDimension("A")->setWidth($lunghezza_riga);
        $this->FaiBold("A5","A37");

    }

    function FaiBold()
    {
        $args = func_get_args();
        foreach($args as $arg)
            $this->mod_st->getActiveSheet()->getStyle($arg)->getFont()->setBold(true);
    }
    
}