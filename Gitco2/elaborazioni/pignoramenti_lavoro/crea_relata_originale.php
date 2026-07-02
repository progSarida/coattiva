<?php

//$RelataOriginale = new Class($cls_db)
class RelataOriginale
{
    public $partita_id;
    public $a_results;
    public $pdfRelata;
    public $testo;
	public $cls_db;
    public $cls_text; // assegno all'inizio
    public $a_subtext; // assegno all'inizio
    public $a_subtextParams; // assegno all'inizio
	public $finalFileRelataOriginale; //assegno dall'inizio
	public $filter;
	public $primo;
	public $ultimo;
	public $tipo;
	
    public function __construct($cls_db)
    {
        $this->pdfRelata = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);
		$this->cls_db = $cls_db;
    }
    private function getQuery()
    {
        $q =  "SELECT EL.*, E.CC, TT.Name AS Tipo_Riscossione, PGTS.*
				FROM elaboration_lists EL 
				join  v_pignoramento_terzi_stampa PGTS on PGTS.Elaboration_List_Id = EL.Id
				JOIN elaborations E ON E.Id=EL.Elaboration_Id 
				JOIN tax_type TT ON TT.Id = EL.TaxTypeId 
				where PGTS.Partita_ID =  ".$this->partita_id;
				
		
		return $q;

    }

	private function CreaFiltri($a_elab_list)
	{
		if($a_elab_list['PrintFlag']==1)
			$filter['printType'] = "final";
		else
			$filter['printType'] = "temp";
		$filter['city'] = $a_elab_list['CC'];
		$filter['last_el_id']  = $a_elab_list['Elaboration_Id'];
		$filter['PrinterId'] = $a_elab_list['PrinterId'];
		$filter['PrintTypeId'] = $a_elab_list['PrintTypeId'];
		$filter['doc_type_id'] = $a_elab_list['DocumentTypeId'];
		$filter['officialType'] = $a_elab_list['NotificationType'];
		$filter['taxType'] = $a_elab_list['Tipo_Riscossione'];

		$filter['finalDate'] = date('Y-m-d');
		$this->filter = $filter;
	}
	
	protected function Inizializza($item)
	{
        
        $cls_db = $this->cls_db;
		$filter = $this->filter;

		$cls_ruolo = new cls_ruolo();
		$cls_ruolo->getDocumentDetails($filter['doc_type_id'], $filter['PrintTypeId'], null, array("PrinterId" => $filter["PrinterId"]));
		
		$this->cls_text = new cls_textParameters();
        $this->cls_text->document_type_id = $filter['doc_type_id'];

		$a_tipo = array("diretta"=>1,"riscossione"=>2,"giudiziario"=>3,"procedimento"=>4);
		$type_id=$a_tipo[$filter['officialType']]; // da recuperare
		$a_textRelata  = $cls_db->getArrayLine($cls_db->SelectQuery($this->cls_text->getSubParameterQuery($filter['city'], $cls_ruolo->a_docDetails['DocumentTypeId'],$type_id)));
		$a_textRelata['Content'] = $this->TogliFirma($a_textRelata['Content']);
		$this->cls_text->setHtmlBody($a_textRelata['Content']);
		

		$this->a_subtext = $cls_db->getResults($cls_db->SelectQuery($this->cls_text->getSubParametersQuery($filter['city'], $cls_ruolo->a_docDetails['DocumentTypeId'])));
		$this->a_subtextParams = array(
			"NotificationType"      =>  $filter['officialType'],
			"PrintTypeId"           =>  $filter['PrintTypeId']
		);
		
		$a_entePrinting = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '" . $filter['city'] . "'"));
		$cls_ente= new cls_ente($a_entePrinting);
		$this->cls_text->setParamsArray($cls_ente->a_ente,'ente');
		
		$cls_params = new cls_parameters();

		$a_yearParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("annuali", $filter['city'])));
		$this->cls_text->setParamsArray($a_yearParams,'year');

		$a_appealParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("ricorso", $filter['city'])));
		$this->cls_text->setParamsArray($a_appealParams,'appeal');
		
		//PARAMETRI RESPONSABILI
		$a_responsibleParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("responsabili", $filter['city'], $filter['taxType'])));
		$cls_params->setArray("responsabili", $a_responsibleParams);
		$cls_params->getSignatures($cls_ente->type);
		$this->cls_text->setParamsArray($cls_params->a_signature,'responsibles');

		$cls_authority = new cls_authority();

		$a_gdp = $cls_db->getArrayLine($cls_db->SelectQuery($cls_authority->getRecordsQuery("giudice", $filter['city'])));
		$a_gdpContacts = $cls_authority->getContacts($a_gdp);
		$a_cgt = $cls_db->getArrayLine($cls_db->SelectQuery($cls_authority->getRecordsQuery("cort_giust_trib", $filter['city'])));
		$a_cgtContacts = $cls_authority->getContacts($a_cgt);
		$a_tribunale = $cls_db->getArrayLine($cls_db->SelectQuery($cls_authority->getRecordsQuery("tribunale", $filter['city'])));
		$a_tribunaleContacts = $cls_authority->getContacts($a_tribunale);
		$a_authority = array("CGT" => $a_cgtContacts['complete'], "GDP" => $a_gdpContacts['complete'], "Tribunale" => $a_tribunaleContacts['complete']);
		$this->cls_text->setParamsArray($a_authority,'authority');
		
		$cls_registry = new cls_registry();

       
		$a_generalParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("generali", $filter['city'], $filter['taxType'])));
		$cls_ente->setPrintHeader($filter['PrintTypeId'], $a_generalParams);
		//$placeDate = $cls_ente->getCityManager() . ", " . $this->cls_help->toItalianDate(date('Y-m-d'));

		$a_userPec = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT * FROM user_emails WHERE User_Id=".$_SESSION['aut_progr']." AND MailType='PEC'"));

		$this->cls_text->setParamsArray($a_userPec,'userPec');

		$this->cls_text->setParamsVar();
        $id = $item["Utente_Notifica_ID"];
        $query = "select * from v_anagrafe where Utente_ID = $id";
        $result = $cls_db->getResults($cls_db->SelectQuery($query));
        
        if($item["Tipo_Notifica"]=="terzo")
            $a_recipientHeader=  $cls_registry->printHeaderTerzo($result[0]);
        else 
            $a_recipientHeader = $cls_registry->printHeaderTerzo($result[0]);
		
        $cls_ruolo->setResultArray($item);
        $cls_ruolo->setDocAmounts($cls_ruolo->a_docDetails['DocumentTypeId'], $a_yearParams, "pignoramento");
       
        $cls_ruolo->a_result["Utente_ID_Partita"] = $item["Utente_Notifica_ID"];
        $cls_ruolo->a_result["Partita_ID"] = $this->partita_id;
        $this->cls_text->setRowVarsPignoTerzoRelataOriginale($cls_ruolo,$a_recipientHeader);
        
	}
    public function GeneraRelata()
    {
		$this->a_results = $this->cls_db->getResults($this->cls_db->SelectQuery($this->getQuery()));
		
        for ($i = 0; $i < count($this->a_results); $i++) {
			$this->primo = strlen($this->testo) == 0;
			$this->ultimo = ($i == count($this->a_results)-1);
			$item = $this->a_results[$i];
			$this->CreaFiltri($item);
			$this->Inizializza($item);
			$this->testo.=$this->CreaTesto($i);
        }
        $this->CreaFile();
		return $this->testo;
    }
	
	protected function TogliFirma($testo)
	{
		if ($this->ultimo) return $testo;
		
		$fine = strpos($testo,"<table");
		$testo = substr($testo,0,$fine);
		return $testo;
	}
	private function CreaTesto($i)
	{	
		$this->cls_text->filterSubtexts($this->a_subtext,$this->a_subtextParams);
		$this->cls_text->replaceSubtexts();
		$this->cls_text->replaceVariables($this->cls_text->a_var);
		return $this->TogliTitolo();
	}

    private function CreaFile()
    {
		if ($this->tipo!="final") return;
        $this->pdfRelata->setDocParams();
        $this->pdfRelata->SetAutoPageBreak(true);
        $this->pdfRelata->AddPage("P");
        $this->pdfRelata->SetMargins(7.0, 10.0, 7.0);
        $this->pdfRelata->ln(0);
        $this->pdfRelata->writeHTML($this->testo);
		$this->pdfRelata->Output($this->finalFileRelataOriginale, 'F');
    }
	
	protected function TogliTitolo()
	{
		$testo =  $this->cls_text->html_replaced_body;

        if (!$this->primo) $testo = str_replace("RELATA DI NOTIFICA","",$testo);
		
		return $testo;
	}
}


?>