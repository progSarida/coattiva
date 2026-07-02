<?php
include_once ELAB_PIGNORAMENTI_LAVORO . "/crea_relata_originale.php";

//$RelataOriginale = new Class($cls_db)
class RelataOriginaleBanca extends RelataOriginale
{

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
		if($item["Tipo_Notifica"]=="debitore")
        	$query = "select * from v_anagrafe where Utente_ID = $id";
	    else
			$query = "select * from banca where ID = $id";		


        $result = $cls_db->getResults($cls_db->SelectQuery($query));
        
        if($item["Tipo_Notifica"]=="debitore")
            $a_recipientHeader=  $cls_registry->printHeaderTerzo($result[0]);
        else 
            $a_recipientHeader = $cls_registry->printHeaderBanca($result[0]);
		
        $cls_ruolo->setResultArray($item);
        $cls_ruolo->setDocAmounts($cls_ruolo->a_docDetails['DocumentTypeId'], $a_yearParams, "pignoramento");
       
        $cls_ruolo->a_result["Utente_ID_Partita"] = $item["Utente_Notifica_ID"];
        $cls_ruolo->a_result["Partita_ID"] = $this->partita_id;
        $this->cls_text->setRowVarsPignoTerzoRelataOriginale($cls_ruolo,$a_recipientHeader);
        
	}
}


?>