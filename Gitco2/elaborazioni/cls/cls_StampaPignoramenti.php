<?php

class cls_StampaPignoramenti
{
	public $DocumentTypeId;

	public $db;
	public $params;
	public $ente;
	public $authority;

	public $a_params;
	public $a_officialType;
	public $a_listSubtext;
	public $filters;

	public $text;
	public $notificationText;
	public $originalNotificationText;

	public $a_textPar;
	public $a_subTextPar;
	public $a_notTextPar;

	public function __construct($DocumentTypeId, $cls_db, $cls_params)
    {
		$this->db = $cls_db;
		$this->params = $cls_params;
		$this->DocumentTypeId = $DocumentTypeId;
		$this->authority = new cls_authority();
		$this->a_officialType = array("diretta"=>1,"riscossione"=>2,"giudiziario"=>3,"procedimento"=>4);
    }

	private function setFilters($filters){
		$this->filters = $filters;
	}

	public function setSubtext($textType, $a_subtextParams){
		$this->a_listSubtext = $a_subtextParams;
		$this->$textType->filterSubtexts($this->a_subTextPar, $a_subtextParams);
		$this->$textType->replaceSubtexts();     
	}

	public function setVars($textType, $cls_ruolo, $a_recipientHeader){
		$this->$textType->setRowVarsPignoramento($cls_ruolo, $a_recipientHeader);
		$this->$textType->replaceVariables($this->text->a_var);
	}


	public function setNotificationVars(){
		$this->notificationText->replaceVariables($this->text->a_var);
	}


	public function getText($filters, $a_subtextParams=null){
		$this->setFilters($filters);
		$this->setEnte();

		$text = new cls_textParameters();
		$text->document_type_id = $this->DocumentTypeId;

		$this->a_textPar = $this->getRow($text->getParametersQuery($this->filters['city'], $this->DocumentTypeId));
		$text->setHtmlBody($this->a_textPar['Content']);

		$this->text = $text;
		$this->setParams();

		$this->a_subTextPar = $this->getRows($text->getSubParametersQuery($this->filters['city'], $this->DocumentTypeId));
		if(!is_null($a_subtextParams))
			$this->setSubtext("text", $a_subtextParams);

		return $this->text;
	}

	public function getNotificationText($a_subtextParams=null){
		$notText = new cls_textParameters();
		$notText->document_type_id = $this->DocumentTypeId;

		//? RECUPERO TUTTI I SOTTOTESTI RELATIVI ALLE RELATE DI NOTIFICA
		$this->a_notTextPar  = $this->getRows($notText->getSubParametersQuery($this->filters['city'], $this->DocumentTypeId, "NotificationReport"),"Type_ID");
		//? IMPOSTO IL TESTO DELLA RELATA DI NOTIFICA CON QUELLO RELATIVO AL TIPO DI NOTIFICA (diretta, riscossione, giudiziario o responsabile)
		$notText->setHtmlBody( $this->a_notTextPar[ $this->a_officialType[ $this->filters["officialType"] ] ]['Content'] );

		$this->notificationText = $notText;
		if(!is_null($a_subtextParams))
			$this->setSubtext("notificationText", $a_subtextParams);

		return $this->notificationText;
	}

	public function getOriginalNotificationText($a_debitore, $terzi){
		$a_vars = $this->text->a_var;
		$html_relata = $this->notificationText->html_body;
		
		$a_vars['{Manager}'] = $this->text->a_var['{ManagerDenominazione}'];
		$a_vars['{Recipient}'] = $a_debitore['Recipient'];
		$a_vars['{RecipientAddress}'] = $a_debitore['RecipientAddress'];
		$a_vars['{RecipientPEC}'] = $a_debitore['RecipientPEC'];

		$html_text = '<p style="text-align: center;"><strong>RELAZIONE DI NOTIFICAZIONE</strong></p>';
		$html_text.= $this->getHtmlReplaced($this->getRelataSenzaTitolo($html_relata),$this->a_listSubtext,$a_vars);

		
		foreach($terzi as $terzo){
			$a_vars['{Recipient}'] = $terzo['Recipient'];
			$a_vars['{RecipientAddress}'] = $terzo['RecipientAddress'];
			$a_vars['{RecipientPEC}'] = $terzo['RecipientPEC'];

			$a_subtext = array(
				"PrintTypeId" => $terzo['PrintTypeId'],
				"NotificationType" => $terzo['NotificationType']
			);
			$html_relata = $this->a_notTextPar[ $this->a_officialType[ $terzo['NotificationType'] ] ]['Content'];
			$html_text.= $this->getHtmlReplaced($this->getRelataSenzaTitolo($html_relata),$a_subtext,$a_vars);
		}
		return $html_text;
	}

	public function createPdf($html, $filePath)
    {
		$file = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);
		
        $file->setDocParams();
        $file->SetAutoPageBreak(true);
        $file->AddPage("P");
        $file->SetMargins(7.0, 10.0, 7.0);
		$file->SetAutoPageBreak(true,10);
        $file->ln(0);
        $file->writeHTML($html);
		$file->Output($filePath, 'F');

    }

	private function getHtmlReplaced($html, $a_subtext, $a_vars){
		$text = new cls_textParameters();
		$text->document_type_id = $this->DocumentTypeId;
		$text->setHtmlBody($html);
		$text->filterSubtexts($this->a_subTextPar, $a_subtext );
		$text->replaceSubtexts();
		$text->replaceVariables($a_vars);
		return $text->html_replaced_body;
	}

	public function getRelataSenzaTitolo($html_relata){
		if(empty($html_relata))
			return "";

		$a_text = explode("</p>",$html_relata);
		$a_text2 = explode(">",$a_text[count($a_text)-2]);
		return "<p>".$a_text2[1]."</p>";
	}


	public function getBanche($docId){
		
		$query = "SELECT NA.PrintTypeId, NA.Tipo_Ufficiale AS NotificationType, BAN.*, BAN.Denominazione AS Recipient, BAN.PEC AS RecipientPEC, ".
		"CONCAT(BAN.Toponimo, ', ', BAN.Civico,' - ',BAN.Cap,' ',BAN.Comune,' ',BAN.Provincia) AS RecipientAddress FROM banca AS BAN ".
		"JOIN pignoramento_presso_terzi AS PPT ON BAN.ID = PPT.Terzo_ID ".
		"JOIN notifica_atto AS NA ON NA.ID_Collegamento=PPT.ID ".
		"WHERE PPT.Pignoramento_ID=".$docId;

		return $this->getRows($query);
	}




	private function setEnte(){
		$a_entePrinting = $this->getRow("SELECT * FROM v_ente_gestito WHERE CC = '" . $this->filters['city'] . "'");
		$this->ente = new cls_ente($a_entePrinting);
	}

	//? SETTING PARAMETRI GENERALI PER TESTO
	private function setParams(){
		$this->setEnteParams();
		$this->setYearParams();
		$this->setAppealParams();
		$this->setPaymentParams();
		$this->setResponsibleParams();
		$this->setAuthorityParams();
		$this->setGeneralParams();
		$this->setUserPecParams();

		$this->text->setParamsVar();
	}

	private function setEnteParams(){
		$this->text->setParamsArray($this->ente->a_ente,'ente');
	}

	private function setYearParams(){
		$this->a_params['year'] = $this->getRow($this->params->getRecordsQuery("annuali", $this->filters['city']));
		$this->text->setParamsArray($this->a_params['year'],'year');
	}

	private function setAppealParams(){
		$this->a_params['appeal'] = $this->getRow($this->params->getRecordsQuery("ricorso", $this->filters['city']));
		$this->text->setParamsArray($this->a_params['appeal'],'appeal');
	}

	private function setPaymentParams(){
		$this->a_params['payment'] = $this->getRow($this->params->getRecordsQuery("pagamento", $this->filters['city']));
		$this->text->setParamsArray($this->a_params['payment'],'payment');
	}

	private function setResponsibleParams(){
		$this->a_params['responsibles'] = $this->getRow($this->params->getRecordsQuery("responsabili", $this->filters['city'], $this->filters['taxType']));
		$this->params->setArray("responsabili", $this->a_params['responsibles']);
		$this->params->getSignatures($this->ente->type);
		$this->text->setParamsArray($this->params->a_signature,'responsibles');
	}

	private function setAuthorityParams(){
		$a_gdp = $this->getRow($this->authority->getRecordsQuery("giudice", $this->filters['city']));
		$a_gdpContacts = $this->authority->getContacts($a_gdp);
		$a_cgt = $this->getRow($this->authority->getRecordsQuery("cort_giust_trib", $this->filters['city']));
		$a_cgtContacts = $this->authority->getContacts($a_cgt);
		$a_tribunale = $this->getRow($this->authority->getRecordsQuery("tribunale", $this->filters['city']));
		$a_tribunaleContacts = $this->authority->getContacts($a_tribunale);
		$a_authority = array("CGT" => $a_cgtContacts['complete'], "GDP" => $a_gdpContacts['complete'], "Tribunale" => $a_tribunaleContacts['complete']);
		$this->text->setParamsArray($a_authority,'authority');
	}

	private function setGeneralParams(){
		$this->a_params['general'] = $this->getRow($this->params->getRecordsQuery("generali", $this->filters['city'], $this->filters['taxType']));
		$this->ente->setPrintHeader($this->filters['PrintTypeId'], $this->a_params['general']);
	}

	private function setUserPecParams(){
		$a_userPec = $this->getRow("SELECT * FROM user_emails WHERE User_Id=".$_SESSION['aut_progr']." AND MailType='PEC'");
		$this->text->setParamsArray($a_userPec,'userPec');

	}
	//? FINE PARAMETRI




	public function getRows($query, $setKey=false){
		return $this->db->getResults($this->db->ExecuteQuery($query),"array",$setKey);
	}

	public function getRow($query){
		return $this->db->getArrayLine($this->db->ExecuteQuery($query));
	}
}


?>