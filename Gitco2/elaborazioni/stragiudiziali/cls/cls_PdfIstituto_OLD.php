<?php
include_once CLS ."/traits.php";

class PdfIstituto
{
    private $cls_pdf;
    private $cls_ente;
    private $cls_text;
    private $cls_params;
    private $cls_db;
    
    private $a_resParams;
    private $a_switchParams;
    private $manager;
    private $managerCity;
    private $indirizzo;
    private $funz_resp;
    private $a_recipent;
    private $bloccoFirma;
    private $a_tributi;
    private $a_funzionari;
    private $arrPdfPathMerge;
    

    protected $istituto;
    protected $CC;
    protected $form_type_id;
    protected $proc_id;
    protected $tax_type;
    protected $tipo;

    public $lastCreated;

    use tSelectSQL;

    function __construct($db)
    {
        $this->cls_db = $db;
        
    }
    public function Set($variabile,$valore)
    {
        $this->{$variabile}= $valore;
        return $this;
    }

    private function CreaTributi()
    {
        $query = "
        SELECT distinct PT.Tipo as Tipo_Riscossione
        FROM gitco2.partita_procedure_pvt as PVT
        join partita_tributi as PT on PVT.Partita_Id = PT.ID
        Where Procedure_Id = $this->proc_id";
        
        $result = $this->SelectSQL($query);
        foreach($result as $item)
        {
            $this->a_tributi[] =$item["Tipo_Riscossione"];
        }
        
    } 
    private function CreaFunzionari()
    {
        $query = "SELECT  distinct Funzionario_Responsabile
        FROM  parametri_responsabili as pr 
        join  partita_tributi as PT
        on  pr.CC = PT.CC AND pr.Tipo_Riscossione = PT.Tipo
        join partita_procedure_pvt aS  PVT on  PVT.Partita_Id = PT.ID
        where PVT.Procedure_Id =  $this->proc_id
        ";
        $result = $this->SelectSQL($query);
        foreach($result as $item)
        {
            $this->a_funzionari[] =$item["Funzionario_Responsabile"];
        }
        
    } 
    
    private function InizializzaEnte()
    {
        $this->cls_params = new cls_parameters();
        $this->a_resParams = $this->cls_db->getResults(
            $this->cls_db->SelectQuery($this->cls_params->getRecordsQuery("responsabili", $this->CC)),
            "array",
            "Tipo_Riscossione"
        );
        $a_enteAdmin = $this->cls_db->getArrayLine( $this->cls_db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '".$this->CC."'") );
        
        $this->cls_ente = new cls_ente($a_enteAdmin);

        $this->cls_ente->setPrintHeader();
        $this->managerCity = $this->cls_ente->getCityManager();
        $via = isset($this->cls_ente->a_ente["Gestore_Via"])?$this->cls_ente->a_ente["Gestore_Via"]:null;
        $nr =  isset($this->cls_ente->a_ente["Gestore_Civico"])?$this->cls_ente->a_ente["Gestore_Civico"]:null;

        $this->indirizzo = "";

        if (!empty($via) && !empty($nr) && !is_null($via) && !is_null($nr))
            $this->indirizzo = $via . "  " . $nr;
        else
            $this->indirizzo = "VIA E CIVICI NON INSERITI";

        switch ($this->cls_ente->type) {
            case "Gestore":
                $this->manager = "Concessionario " . $this->cls_ente->a_ente[$this->cls_ente->type . '_Denominazione'];
                $this->a_switchParams["Completo_Parziale"] = "completo";
                break;
            default:
                $this->manager = $this->cls_ente->a_ente[$this->cls_ente->type . '_Denominazione'];
                $this->a_switchParams["Completo_Parziale"] = "parziale";
        }
        
        $this->CreaTributi();
        
        $this->bloccoFirma = $this->cls_params->getHtmlMultiSignature("{SignLegale}", $this->a_tributi, $this->a_resParams, $this->cls_ente->type);
        
    }


    private function StampaElencoTributi()
    {
        $result ="";
        foreach($this->a_tributi as $tributo)
        {
            $result.=$tributo.",";
        }
        return substr($result,0,strlen($result)-1);
    }

    private function ScegliTipo()
    {
        if($this->tipo == "Banca")
        {
            return "BANCHE/ISTITUTI DI CREDITO";
        }
        else
        {
            return "ENTI PREVIDENZIALI";
        }
    }

    private function ScegliTipoRapporto()
    {
        if($this->tipo == "Banca")
        {
            return "se titolare di rapporto di conto corrente indicandocene gli estremi e la capienza in rapporto all’entità del debito";
        }
        else
        {
            return "se titolare di pensione diversa da quella di invalidità";
        }
    }
    private function ControllaAbilitazione()
    {
        if(!empty($this->cls_ente->a_ente['Gestore_ID']))
        {
            if(!empty($this->cls_ente->a_ente['Gestore_Abilitazione']))
            {
                return $this->cls_ente->a_ente['Gestore_Abilitazione'];
            }
            else
            {
                //TODO bloccare stampa !! 
            }

        }
        return "";

    }
    private function InizializzaParametriTesto()
    {
        
        $this->cls_text = new cls_textParameters();
        $a_text = $this->cls_db->getArrayLine($this->cls_db->SelectQuery("SELECT * FROM text_parameters WHERE CC=\"".$this->CC."\" AND Form_Type_ID=\"".$this->form_type_id."\""));

        $a_subtext = $this->cls_db->getResults($this->cls_db->SelectQuery($this->cls_text->getSubParametersQuery($this->CC,$this->form_type_id)));
        $this->cls_text->html_body = isset($a_text['Content']) ? $a_text['Content'] : null;
        $this->cls_text->html_replaced_body = $this->cls_text->html_body;
        $this->cls_text->replaceSubtext($a_subtext,$this->a_switchParams);
        
        $cls_st = new cls_Stampe();
        $concessionario = $this->cls_params->a_signature["funzionario"]["name"];
        $this->cls_text->a_var = array(

            "{ENTE}" => strtoupper($this->cls_ente->a_ente['Info_Denominazione']),
            "{Manager}" => $this->manager,
            "{concessionario}"=>"",
            "{Concessionario}" =>$concessionario,
            "{managerContactDetails}" => $this->indirizzo,
            "{days}" => 30,
            "{PEC_GESTORE}" => $cls_st->GetPecGestore($this->CC),
            "{PARAMETRI FUNZIONARIO}" => $this->funz_resp,
            "{AbilitazioneConcessionario}" => $this->ControllaAbilitazione(),
            "{SignLegale}" =>  $this->bloccoFirma,
            "{Tributo}" => $this->StampaElencoTributi(),
            "{TIPO}" => $this->ScegliTipo(),
            "{TIPO_RAPPORTO}" => $this->ScegliTipoRapporto(),
        );
        
    }

    public function Inizio()
    {
        $this->InizializzaEnte();
        $this->InizializzaParametriTesto();
        return $this;
    }
    public function CreazionePdf($print_type,$contatoreRecord)
    {   
        $istituto = $this->istituto;
        
        $denominazione = !empty($istituto['Denominazione']) ? $istituto['Denominazione'] : "DENOMINAZIONE ASSENTE";
        $toponimo = !empty($istituto['Toponimo']) ? $istituto['Toponimo'] : "TOPONIMO ASSENTE";
        $civico = !empty($istituto['Civico']) ? $istituto['Civico'] : "CIVICO ASSENTE";
        $cap = !empty($istituto['Cap']) ? $istituto['Cap'] : "CAP ASSENTE";
        $prov = !empty($istituto['Provincia']) ? $istituto['Provincia'] : "PROVINCIA ASSENTE";
        $comune = !empty($istituto['Comune']) ? $istituto['Comune'] : "COMUNE ASSENTE";
        $this->funz_resp = !empty($istituto["Funzionario_Responsabile"]) ? $istituto["Funzionario_Responsabile"] : "RESPONSABILE ASSENTE";

        $this->a_recipent = array(
            'denomination' => array("",$denominazione),
            'addressRow' => array($toponimo . " " . $civico , $cap . " " . $comune . " " . $prov),
        );
        $this->cls_pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);

        $this->cls_pdf->setDocParams();
        $this->cls_pdf->SetAutoPageBreak(true);
        $this->cls_pdf->AddPage("P");
        $this->cls_pdf->setManagerHeader($this->cls_ente->a_header);
        $this->cls_pdf->setRecipientHeader($this->a_recipent);
        $this->cls_pdf->SetMargins(7.0, 10.0, 7.0);
        $this->cls_pdf->ln(0);
        $this->cls_pdf->SetFont('helvetica', '', 9);

        if($print_type == "temp") {
            $this->cls_pdf->temporaryPrinting();
            $this->cls_pdf->SetFont('helvetica', '', 9);
        }

        $this->CreaFunzionari();
        
        $this->cls_text->a_var["{PARAMETRI FUNZIONARIO}"] = implode(', ', $this->a_funzionari);
        $this->cls_text->replaceVariables($this->cls_text->a_var);

        
        $this->cls_pdf->writeHTML($this->cls_text->html_replaced_body, true, 0, true, 0);
        if($print_type == "temp") {
            $this->cls_pdf->temporaryPrinting();
            $this->cls_pdf->SetFont('helvetica', '', 9);
        }

        if($print_type != "temp") {
            $path = STRAGIUDIZIALE . "/" . $this->proc_id;
            if( !is_dir($path)) mkdir($path);
            {
                if ($this->tipo == "Previdenziali")
                    $nameFile = "Stragiudiziale_Previdenziali_" . $this->CC . "_" . $this->istituto['Previdenza_Id'] . ".pdf";
                else
                    $nameFile = "Stragiudiziale_Banca_" . $this->CC . "_" . $this->istituto['Banca_Id'] . ".pdf";
            }
        }
        else{
            $path = STRAGIUDIZIALE . "/temp";
            if( !is_dir($path)) mkdir($path);
            {
                if ($this->tipo == "Previdenziali")
                    $nameFile = "Stragiudiziale_Previdenziali_" . $this->CC . "_" . $this->istituto['Previdenza_Id'] . "_" . date('Y-m-d') . "_".$contatoreRecord.".pdf";
                else
                    $nameFile = "Stragiudiziale_Banca_" . $this->CC . "_" . $this->istituto['Banca_Id'] . "_" . date('Y-m-d') . "_".$contatoreRecord.".pdf";    
            }
        }

        $completePath = $path . "/" . $nameFile;
        
        $this->lastCreated = $completePath;
        
        $this->cls_pdf->Output($completePath, "F");
        $this->arrPdfPathMerge[] = $completePath;

        return $this;
    }

    public function Fine($print_type)
    {
        $this->cls_pdf = null;unset($this->cls_pdf);
        if($print_type == "temp")
        {
            $cls_merge = new cls_merge();
            $cls_merge->setFiles($this->arrPdfPathMerge);
            $cls_merge->concatFiles(false);

            $pdfPath = STRAGIUDIZIALE . "/temp";
            if( !is_dir($pdfPath)) mkdir($pdfPath);
            $cls_merge->Output($pdfPath . "/mergeStraPdf.pdf", "F");
            $this->lastCreated = STRAGIUDIZIALEWEB . "/temp" . "/mergeStraPdf.pdf";
        }
    }
}

?>