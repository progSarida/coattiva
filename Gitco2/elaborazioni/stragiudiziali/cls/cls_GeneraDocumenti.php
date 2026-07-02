<?php

include_once ELAB_STRAGIUDIZIALI."/cls/cls_Stragiudiziali.php";
include_once ELAB_STRAGIUDIZIALI."/cls/cls_ExcelIstituto.php";
include_once ELAB_STRAGIUDIZIALI."/cls/cls_PdfIstituto.php";

class GeneraDocumenti{


    private $cls_Stragiudiziali;
    private $cls_ExcelIstituto;
    private $cls_PdfIstituto;
    private $CC;
    private $tipo;
    private $denominazione;

    protected $cls_db;

    public $callbackUtente;
    public $callbackBanca;
    
    public $a_lastCreated;

    function __construct($db)
    {
        $this->cls_db = $db;
    }

    public function Inizializzazione($proc_id,$tipo,$tax_type,$CC,$form_type_id)
    {
        $this->CC = $CC;
        $this->tipo = $tipo;
        $this->cls_PdfIstituto = new PdfIstituto($this->cls_db);
        $this->cls_ExcelIstituto = new ExcelIstituto();

        $this->denominazione = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$CC."'"),"enti_gestiti");

        $this->cls_ExcelIstituto
            ->Set("CC",$CC)
            ->Set("proc_id",$proc_id)
            ->Set("tax_type",$tax_type)
            ->Set("tipo",$tipo)
            ->Set("denom_cc",$this->denominazione);
        
        $this->cls_PdfIstituto
            ->Set("CC",$CC)
            ->Set("proc_id",$proc_id)
            ->Set("tax_type",$tax_type)
            ->Set("form_type_id",$form_type_id)
            ->Set("tipo",$tipo)
            ->Set("denom_cc",$this->denominazione)
            ->Inizio();

        $this->cls_Stragiudiziali = new Stragiudiziali($this->cls_db);
        $this->cls_Stragiudiziali
            ->Set("proc_id",$proc_id)
            ->Struttura();
        return $this;    

    }

    private function GeneraExcel($print_type)
    {
        $this->cls_ExcelIstituto->InizioFoglio();
        $y=0;
        foreach($this->cls_Stragiudiziali->a_utenti as $key=>$utente){
            $this->cls_Stragiudiziali->PrendiAtti($utente);
            
            
            $this->cls_ExcelIstituto->PerOgniUtente(
                $utente
            );
            
            $y++;
            if(isset($this->callbackUtente))  
                call_user_func($this->callbackUtente,$y,count($this->cls_Stragiudiziali->a_utenti));
            
        }
        
        $this->cls_ExcelIstituto->FineFoglio();
        $this->cls_ExcelIstituto->SalvaSuDisco($print_type);
        $this->a_lastCreated["excel"] =  $this->cls_ExcelIstituto->lastCreated;
    }

    private function GeneraPdf($print_type)
    {
        
        $i=0;
        if($this->tipo == "Previdenziali")
            $collection = $this->cls_Stragiudiziali->a_previdenze;
        else
            $collection = $this->cls_Stragiudiziali->a_banche;
            
        foreach($collection as $key=>$istituto)
        {
            
            $infoIstituto = $istituto;

            $this->cls_Stragiudiziali->AggiungiResponsabileFirma($this->CC,$infoIstituto);
                   
            $this->cls_PdfIstituto
                ->Set("istituto",$infoIstituto)
                ->CreazionePdf($print_type,$i);
                
            //Crea copia di excel per ogni istituto
            $indice = $this->tipo == "Previdenziali" ? "Previdenza_Id" : "Banca_Id";
            if ($print_type!="temp") $this->cls_ExcelIstituto->CreaCopia($infoIstituto[$indice]);
            // come provvisorio posso crearne solo uno come esempio
            if ($print_type=="temp") break;
            $i++;
            
            if(isset($this->callbackBanca))  
            call_user_func($this->callbackBanca,$i,count($collection ));

        }
        $this->cls_PdfIstituto->Fine($print_type);
        $this->a_lastCreated["pdf"] =  $this->cls_PdfIstituto->lastCreated;
    }

    public function Genera($print_type)
    {
        $this->GeneraExcel($print_type);
        $this->GeneraPdf($print_type);
    }

}