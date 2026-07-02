<?php
include_once CLS . "/cls_DateTimeInLine.php";

class cls_textParameters
{
    public $a_var;
    public $html_body;
    public $html_replaced_body;

    public $cls_help;
    private $cls_date;

    public function __construct(){
        $this->cls_help = new cls_help();
        $this->cls_date = new cls_DateTimeI("IT",false);
    }

    public function getParametersQuery ($cc, $formType){
        $query = "SELECT * FROM text_parameters WHERE CC=\"".$cc."\" AND Form_Type_ID=\"".$formType."\"";
        return $query;
    }

    public function getSubParametersQuery ($cc, $formType){
        $query = "SELECT * FROM subtext_parameters WHERE CC=\"".$cc."\" AND Form_Type_ID=\"".$formType."\"";
        return $query;
    }

    public function getModelParametersQuery ($formType){
        $query = "SELECT * FROM text_parameters WHERE CC=\"*****\" AND Form_Type_ID=\"".$formType."\"";
        return $query;
    }

    public function replaceBody($search, $value){
        $this->html_replaced_body = str_replace($search,$value,$this->html_replaced_body);
    }

    public function replaceSubtext(array $a_subtexts, array $a_switchParams){

        //var_dump($a_subtexts);
        $count = 0;
        $arrayScarti = array();

        $this->html_replaced_body = $this->html_body;
        while(strpos($this->html_replaced_body,"{{")!==false){
            $flag = false;
            foreach($a_subtexts as $a_subtext){

                if(strpos($this->html_replaced_body,$a_subtext['Variable'])!==false){
                    switch($a_subtext['Variable']){
                        case "{{NotificationReport}}":
                            switch($a_switchParams['NotificationReport']){
                                case "diretta":
                                    if($a_subtext['Type_ID']==1) {
                                        $this->replaceBody($a_subtext['Variable'], $a_subtext['Content']);
                                        $flag = true;
                                    }
                                    break;
                                case "riscossione":
                                    if($a_subtext['Type_ID']==2) {
                                        $this->replaceBody($a_subtext['Variable'], $a_subtext['Content']);
                                        $flag = true;
                                    }
                                    break;
                                case "giudiziario":
                                    if($a_subtext['Type_ID']==3) {
                                        $this->replaceBody($a_subtext['Variable'], $a_subtext['Content']);
                                        $flag = true;
                                    }
                                    break;

                            }
                            break;
                        case "{{Relata}}":
                            switch($a_switchParams['Relata']){
                                case "riscossione":
                                    if($a_subtext['Type_ID']==1) {
                                        $this->replaceBody($a_subtext['Variable'], $a_subtext['Content']);
                                        $flag = true;
                                    }
                                    break;
                                case "giudiziario":
                                    if($a_subtext['Type_ID']==2) {
                                        $this->replaceBody($a_subtext['Variable'], $a_subtext['Content']);
                                        $flag = true;
                                    }
                                    break;

                            }
                            break;
                        case "{{SendType}}":
                            switch($a_switchParams['SendType']){
                                case 1:
                                case 2:
                                    if($a_subtext['Type_ID']==1) {
                                        $this->replaceBody($a_subtext['Variable'], $a_subtext['Content']);
                                        $flag = true;
                                    }
                                    break;
                                case 6:
                                    if($a_subtext['Type_ID']==2) {
                                        $this->replaceBody($a_subtext['Variable'], $a_subtext['Content']);
                                        $flag = true;
                                    }
                                    break;
                                case 7:
                                    if($a_subtext['Type_ID']==3) {
                                        $this->replaceBody($a_subtext['Variable'], $a_subtext['Content']);
                                        $flag = true;
                                    }
                                    break;

                            }
                            break;
                        case "{{Richiesta_Accolta_Negata}}":
                            switch($a_switchParams['Richiesta_Accolta_Negata']){
                                case "accolta":
                                    if($a_subtext['Type_ID']==1) {
                                        $this->replaceBody($a_subtext['Variable'], $a_subtext['Content']);
                                        $flag = true;
                                    }
                                    break;
                                case "negata":
                                    if($a_subtext['Type_ID']==2) {
                                        $this->replaceBody($a_subtext['Variable'], $a_subtext['Content']);
                                        $flag = true;
                                    }
                                    break;

                            }
                            break;
                        case "{{Info}}":
                            $this->replaceBody($a_subtext['Variable'],$a_subtext['Content']);
                            $flag = true;
                            break;

                        default: break;

                    }
                }

            }

            if(!$flag){
                $start = substr($this->html_replaced_body,0,strpos($this->html_replaced_body,"{{"));
                $end = substr($this->html_replaced_body,(strpos($this->html_replaced_body,"}}")+2),(strlen($this->html_replaced_body)-strpos($this->html_replaced_body,"}}")-2));
                $element = substr($this->html_replaced_body,strpos($this->html_replaced_body,"{{"),(strpos($this->html_replaced_body,"}}") - strpos($this->html_replaced_body,"{{") + 2));

                /*var_dump($start);
                echo "<h1>-------------- SEPARATORE ------------</h1>";
                var_dump($end);
                echo "<h1>-------------- SEPARATORE ------------</h1>";
                var_dump($element);*/

                $this->html_replaced_body = $start. "TempSubText_".$count.$end;
                $arrayScarti["TempSubText_".$count] = $element;

                $count++;
            }
        }

        if(count($arrayScarti) > 0)
        {
            foreach ($arrayScarti as $key => $value)
            {
                $this->replaceBody($key,$value);
            }
        }

    }

    public function replaceVariables(array $a_var){

        foreach($a_var as $key=>$value){
            $checkVar = 0;
            while($checkVar==0){
                if (strpos($this->html_replaced_body, $key) !== false) {
                    $this->replaceBody($key,$value);
                }
                else
                    $checkVar=1;
            }
        }
    }

    public function set_varArray(cls_ente $cls_ente, array $a_paymentParams, array $a_yearParams, cls_parameters $cls_params, array $a_appealParams, array $a_authority, array $forAvInt = array()){
        switch($cls_ente->type){
            case "Gestore":
                $manager = "Concessionario ".$cls_ente->a_ente[$cls_ente->type.'_Denominazione'];
                break;
            default:
                $manager = $cls_ente->a_ente[$cls_ente->type.'_Denominazione'];
        }
        $this->a_var = array(
            "{CC}"=>$cls_ente->a_ente['CC'],
            "{Ente}" => $cls_ente->getCityDenomination(),
            "{ManagerFax}" => $cls_ente->a_ente[$cls_ente->type.'_Fax'],
            "{ManagerPhone}" => $cls_ente->a_ente[$cls_ente->type.'_Telefono'],
            "{ManagerPec}" => $forAvInt["ManagerPec"],
            "{Manager}" => $manager,
            "{managerOffice}" => $forAvInt["managerOffice"]['Completo'],
            "{managerContactDetails}" => $forAvInt["managerContactDetails"],
            "{AccountHolder}" => $a_paymentParams['Intestatario_Conto'],
            "{AccountNumber}" => $a_paymentParams['Numero_Conto'],
            "{FineDays}" => $a_paymentParams['Scadenza_Sanzione'],
            "{IngiunzioneExpireDays}" => $a_paymentParams['Scadenza_Ingiunzione'],
            "{AvvisoIntimazioneDays}" => $a_paymentParams['Scadenza_Avviso'],
            "{IBAN}"=> " (IBAN ".$a_paymentParams['IBAN'].")",
            "{AGFee}" => $a_yearParams['Spese_Postali_AG'],
            "{CAD}" => $a_yearParams['CAD'],
            "{CAN}" => $a_yearParams['CAN'],
            "{NotificationFee}" => $a_yearParams['Spese_Notifica'],
            "{ChargeDays}" => $a_yearParams['Giorni_Diritto'],
            "{SignLegale}" => $cls_params->getHtmlSignature("{SignLegale}"),
            "{SignRespProcedimento}" => $cls_params->getHtmlSignature("{SignRespProcedimento}"),
            "{SignRespRichieste}" => $cls_params->getHtmlSignature("{SignRespRichieste}"),
            "{SignUfficiale}" => $cls_params->getHtmlSignature("{SignUfficiale}"),
            "{CTPDays}" => $a_appealParams['Termini_Commissione_Tributaria_Provinciale'],
            "{GiustiziaDays}" => $a_appealParams['Termini_Giustizia_Ordinaria'],
            "{CTP}" => $a_authority['CTP'],
            "{GDP}" => $a_authority['GDP'],
            "{Tribunale}" => $a_authority['Tribunale'],
            "{OrganizationAddress}" => $forAvInt["indirizzoEnte"],
            "{OfficialExpenses}" => $a_yearParams["A_Mani"]." Euro",
            "{DaysPayment}" => 30,
            "{CCP_Number}" => $a_paymentParams['Numero_Conto'],
            "{CCP_Accountholder}" => $a_paymentParams['Intestatario_Conto'],
            "{CTP_Days}"=>$a_appealParams['Termini_Commissione_Tributaria_Provinciale'],
            "{GDP_Days}"=>$a_appealParams['Termini_Giustizia_Ordinaria'],
            "{ContactDetails_CTP}" =>$forAvInt["ctpContacts"],
            "{ContactDetails_GDP}" =>$forAvInt["gdpContacts"],
            "{OfficeContactDetails}" => $cls_ente->getContactsManager(),
            "{ExpensesNotificationSeizure}" => $forAvInt["Spese_Notifica_Pignoramento"],
            "{ExpensesJudicialActs}" => $forAvInt["Spese_Postali_AG"],
            "{ExpenditureEstimateAssets}" => $forAvInt["ExpenditureEstimateAssets"],
            "{ResponsibleOfficer}" => $cls_params->a_responsabili["Funzionario_Responsabile"],
            "{IVG}" => isset($a_authority["IVG"])?$a_authority["IVG"]:null,

        );
    }
    public function set_varArrayRow(cls_ruolo $cls_ruolo, array $a_recipientHeader, array $a_yearParams,array $a_recipientVariablesRow, $Type = "atto"){

       // var_dump($cls_ruolo->a_result);
        switch($Type)
        {
            case "atto":
                $docType = strtoupper($cls_ruolo->a_result['Atto']);
                if($cls_ruolo->a_result['Atto_Rettificato']==1)
                    $docType = "RETTIFICA ".$cls_ruolo->a_result['RettificaDetails'];
                break;
            case "pigno": $docType = strtoupper($cls_ruolo->a_result['Nome_Pignoramento']); break;
            default: $docType = "";
        }

        if($Type == "atto")
        {
            $min = $a_yearParams['Diritto_Riscossione_Minimo'];
            $max = $a_yearParams['Diritto_Riscossione_Massimo'];
        }
        else{
            $min = $a_recipientVariablesRow['MinMaxPigno']["Riscossione_Min"];
            $max = $a_recipientVariablesRow['MinMaxPigno']["Riscossione_Max"];
        }

  //     var_dump($cls_ruolo->a_result['Data_Notifica']);
//die;
        if($Type == "atto"){
            $a_var = array(

                "{Recipient}"=> implode(" ",$a_recipientHeader['denomination']),
                "{RecipientAddress}" => $a_recipientHeader['address'],
                "{DocType}" => $docType,
                "{CronoID}" => $cls_ruolo->a_result['ID_Cronologico'],
                "{CronoYear}" => $cls_ruolo->a_result['Anno_Cronologico'],
                "{Crono}" => $cls_ruolo->a_result['ID_Cronologico']."/".$cls_ruolo->a_result['Anno_Cronologico'],
                "{InfoCartella}" => "'".$cls_ruolo->a_result['Info_Cartella']."'",
                "{AmountDue}" => number_format($cls_ruolo->a_result['Totale_Dovuto'],2,",",".")." Euro",
                "{PartitaID}" => $cls_ruolo->a_result['Comune_ID'] ,
                "{PartitaYear}" => $cls_ruolo->a_result['Anno_Riferimento'] ,
                "{RecipientID}" => $cls_ruolo->a_result['Utente_Comune_ID'],
                "{TaxType}" => $cls_ruolo->getTaxType($cls_ruolo->a_result['Tipo_Riscossione']),
                "{AmountsList}" => $cls_ruolo->getHtmlAmounts($Type),
                "{AmountsListLine}" => $cls_ruolo->getHtmlAmountsLine($Type),
                "{VerbalInformation}" => $a_recipientVariablesRow["info"],
                "{AmountWithoutCharges}" => $a_recipientVariablesRow["ImportoSenzaSpese"],
                "{PaymentReferences}" => $cls_ruolo->getReferences($Type),
                "{Reference}" => $cls_ruolo->a_result["Partita_ID"]."/".$cls_ruolo->a_result["Anno_Riferimento"],
                "{Payments}" => $a_recipientVariablesRow["totalePagamenti"],
                "{TotalDue1}" => $a_recipientVariablesRow["TotalePag1"],
                "{TotalDue2}" => $a_recipientVariablesRow["TotalePag2"],
                "{TotalDue3}" => $a_recipientVariablesRow["TotalePag3"],
                "{InfoAtto}" => $a_recipientVariablesRow["InfoAtto"],
                //"{User}" =>$a_recipientVariablesRow["User"],
                "{ReasonForPayment}" => "Avv. Intimazione n.".$cls_ruolo->a_result['ID_Cronologico']." del ".$cls_ruolo->a_result['Anno_Cronologico']." Rif.".$cls_ruolo->a_result["Partita_ID"]."/".$cls_ruolo->a_result["Anno_Riferimento"],
                //"{CFPI}" => $a_recipientVariablesRow["CF_PI"],
                //"{UserResidence}" => $a_recipientVariablesRow["UserResidence"],
                //"{UserCode}" => $a_recipientVariablesRow["UserCode"],
                "{ProcessingDate}" => $this->cls_date->Get_DateNewFormat($cls_ruolo->a_result['Data_Elaborazione'],"DB"),
                //"{DateVehicleRegistrationCertificate}" => $a_recipientVariablesRow["Data_Visura"],
                //"{DataSource}" => $a_recipientVariablesRow["Fonte_Dati"],
                //"{VehicleType}" => $a_recipientVariablesRow["Tipo_Veicolo"],
                //"{VehicleBrand}" => $a_recipientVariablesRow["Marca_Veicolo"],
                //"{VehicleModel}" => $a_recipientVariablesRow["Modello_Veicolo"],
                //"{VehicleLicensePlate}" => $a_recipientVariablesRow["Targa_Veicolo"],
                //"{ActsNotified}" => $a_recipientVariablesRow["attiNot"],
                //"{AttoPrec}" => $a_recipientVariablesRow["AttoPrec"],
                //"{OfficialText}" => $a_recipientVariablesRow["OfficialText"],
                "{CompleteInjunction}" => "Ingiunzione n.".$cls_ruolo->a_result['ID_Cronologico']." del ".$cls_ruolo->a_result['Anno_Cronologico']." notificata il ".$this->cls_date->Get_DateNewFormat($cls_ruolo->a_result['Data_Notifica'],"DB"),
                //"{Terzi}" => $a_recipientVariablesRow["Terzi"],
                //"{TerziProTempore}" => $a_recipientVariablesRow["TerziProTempore"],
                //"{SendType}" => $a_recipientVariablesRow["SendType"],
                //"{CommonCourt}" => $a_recipientVariablesRow["CommonCourt"],
                //"{PrintType}" => $a_recipientVariablesRow["PrintType"],
                //"{ChargeMax}" => $max,
                "{ChargeMin}" => $min,
                "{PrintDate}" => $this->cls_date->Get_DateNewFormat($cls_ruolo->a_result['Data_Stampa'],"DB")==null?date("d/m/Y"):$this->cls_date->Get_DateNewFormat($cls_ruolo->a_result['Data_Stampa'],"DB"),
                //"{HeaderData}" => "( Utente: ".$cls_ruolo->a_result['Comune_ID']."/".$cls_ruolo->a_result['CC']." - Partita: ".$cls_ruolo->a_result['Partita_ID']."/".$cls_ruolo->a_result['Anno_Riferimento']." - ING. ".$cls_ruolo->a_result['ID_Cronologico']."/".$cls_ruolo->a_result['Anno_Cronologico']." - COMUNE DI ".strtoupper($a_recipientVariablesRow["NomeComune"])." )",
                //"{ForeclosedVehicles}" => $a_recipientVariablesRow["ForeclosedVehicles"],
                "{NotificationDate}" => $this->cls_date->Get_DateNewFormat($cls_ruolo->a_result['Data_Notifica'],"DB")
            );
        }
        else{
            $a_var = array(

                "{Recipient}"=> implode(" ",$a_recipientHeader['denomination']),
                "{RecipientAddress}" => $a_recipientHeader['address'],
                "{DocType}" => $docType,
                "{CronoID}" => $cls_ruolo->a_result['ID_Cronologico'],
                "{CronoYear}" => $cls_ruolo->a_result['Anno_Cronologico'],
                "{Crono}" => $cls_ruolo->a_result['ID_Cronologico']."/".$cls_ruolo->a_result['Anno_Cronologico'],
                "{InfoCartella}" => "'".$cls_ruolo->a_result['Info_Cartella']."'",
                "{AmountDue}" => number_format($cls_ruolo->a_result['Totale_Dovuto'],2,",",".")." Euro",
                "{PartitaID}" => $cls_ruolo->a_result['Comune_ID'] ,
                "{PartitaYear}" => $cls_ruolo->a_result['Anno_Riferimento'] ,
                "{RecipientID}" => $cls_ruolo->a_result['Utente_Comune_ID'],
                "{TaxType}" => $cls_ruolo->getTaxType($cls_ruolo->a_result['Tipo_Riscossione']),
                "{AmountsList}" => $cls_ruolo->getHtmlAmounts($Type),
                "{AmountsListLine}" => $cls_ruolo->getHtmlAmountsLine($Type),
                "{VerbalInformation}" => $a_recipientVariablesRow["info"],
                "{AmountWithoutCharges}" => $a_recipientVariablesRow["ImportoSenzaSpese"],
                "{PaymentReferences}" => $cls_ruolo->getReferences($Type),
                "{Reference}" => $cls_ruolo->a_result["Partita_ID"]."/".$cls_ruolo->a_result["Anno_Riferimento"],
                "{Payments}" => $a_recipientVariablesRow["totalePagamenti"],
                "{TotalDue1}" => $a_recipientVariablesRow["TotalePag1"],
                "{TotalDue2}" => $a_recipientVariablesRow["TotalePag2"],
                "{TotalDue3}" => $a_recipientVariablesRow["TotalePag3"],
                "{InfoAtto}" => $a_recipientVariablesRow["InfoAtto"],
                "{User}" =>$a_recipientVariablesRow["User"],
                "{ReasonForPayment}" => "Avv. Intimazione n.".$cls_ruolo->a_result['ID_Cronologico']." del ".$cls_ruolo->a_result['Anno_Cronologico']." Rif.".$cls_ruolo->a_result["Partita_ID"]."/".$cls_ruolo->a_result["Anno_Riferimento"],
                "{CFPI}" => $a_recipientVariablesRow["CF_PI"],
                "{UserResidence}" => $a_recipientVariablesRow["UserResidence"],
                "{UserCode}" => $a_recipientVariablesRow["UserCode"],
                "{ProcessingDate}" => $this->cls_date->Get_DateNewFormat($cls_ruolo->a_result['Data_Elaborazione'],"DB"),
                "{DateVehicleRegistrationCertificate}" => $a_recipientVariablesRow["Data_Visura"],
                "{DataSource}" => $a_recipientVariablesRow["Fonte_Dati"],
                "{VehicleType}" => $a_recipientVariablesRow["Tipo_Veicolo"],
                "{VehicleBrand}" => $a_recipientVariablesRow["Marca_Veicolo"],
                "{VehicleModel}" => $a_recipientVariablesRow["Modello_Veicolo"],
                "{VehicleLicensePlate}" => $a_recipientVariablesRow["Targa_Veicolo"],
                "{ActsNotified}" => $a_recipientVariablesRow["attiNot"],
                "{AttoPrec}" => $a_recipientVariablesRow["AttoPrec"],
                "{OfficialText}" => $a_recipientVariablesRow["OfficialText"],
                "{CompleteInjunction}" => "Ingiunzione n.".$cls_ruolo->a_result['ID_Cronologico']." del ".$cls_ruolo->a_result['Anno_Cronologico']." notificata il ".$this->cls_date->Get_DateNewFormat($cls_ruolo->a_result['Data_Notifica'],"DB"),
                "{Terzi}" => $a_recipientVariablesRow["Terzi"],
                "{TerziProTempore}" => $a_recipientVariablesRow["TerziProTempore"],
                "{SendType}" => $a_recipientVariablesRow["SendType"],
                "{CommonCourt}" => $a_recipientVariablesRow["CommonCourt"],
                "{PrintType}" => $a_recipientVariablesRow["PrintType"],
                "{ChargeMax}" => $max,
                "{ChargeMin}" => $min,
                "{PrintDate}" => $this->cls_date->Get_DateNewFormat($cls_ruolo->a_result['Data_Stampa'],"DB")==null?date("d/m/Y"):$this->cls_date->Get_DateNewFormat($cls_ruolo->a_result['Data_Stampa'],"DB"),
                "{HeaderData}" => "( Utente: ".$cls_ruolo->a_result['Comune_ID']."/".$cls_ruolo->a_result['CC']." - Partita: ".$cls_ruolo->a_result['Partita_ID']."/".$cls_ruolo->a_result['Anno_Riferimento']." - ING. ".$cls_ruolo->a_result['ID_Cronologico']."/".$cls_ruolo->a_result['Anno_Cronologico']." - COMUNE DI ".strtoupper($a_recipientVariablesRow["NomeComune"])." )",
                "{ForeclosedVehicles}" => $a_recipientVariablesRow["ForeclosedVehicles"],
                "{NotificationDate}" => $this->cls_date->Get_DateNewFormat($cls_ruolo->a_result['Data_Notifica'],"DB")
            );
        }

        $this->a_var = array_merge($this->a_var,$a_var);
    }
}

?>