<?php
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/cls_LastAct.php";
include_once CLS . "/cls_VeicoliPreavviso.php";

class cls_textParameters
{
    public $a_var;
    public $html_body;
    public $html_replaced_body;
    public $a_subtexts;

    public $a_params;

    public $cls_help;
    private $cls_date;
    private $cls_db;
    public $document_type_id;
    private $log;

    public function __construct(){
        $this->cls_help = new cls_help();
        $this->cls_date = new cls_DateTimeI("IT",false);
        $this->cls_db = new cls_db();
    }

    public function setParamsArray($a_params, $type){
        $this->a_params[$type] = $a_params;
    }

    public function getParametersQuery ($cc, $formType){
        $query = "SELECT * FROM text_parameters WHERE CC=\"".$cc."\" AND Form_Type_ID=\"".$formType."\"";
        return $query;
    }

    public function getSubParametersQuery ($cc, $formType, $variable=null){
        $query = "SELECT * FROM subtext_parameters WHERE CC=\"".$cc."\" AND Form_Type_ID=\"".$formType."\"";
        if(!is_null($variable))
            $query.= " AND Variable='{{".$variable."}}'";
        return $query;
    }

    public function getSubParameterQuery ($cc, $formType,$subtext_type_id, $variable=null){
        $query = "SELECT * FROM subtext_parameters WHERE CC=\"".$cc."\" AND Form_Type_ID=\"".$formType."\" AND Type_ID=".$subtext_type_id;
        if(!is_null($variable))
            $query.= " AND Variable='{{".$variable."}}'";
        return $query;
    }

    

    public function setHtmlBody($body){
        $this->html_body = $body;
        while($pos = strpos($this->html_body,"<p>{{")){
            $this->html_body = str_replace("<p>{{","{{",$this->html_body);
            $this->html_body = str_replace("}}</p>","}}",$this->html_body);
        }
    }

    public function replaceBody($search, $value){
        $this->html_replaced_body = str_replace($search,$value,$this->html_replaced_body);
    }

    private function getSubtext(){
        $startPos = strpos($this->html_replaced_body,"{{");
        $endPos = strpos($this->html_replaced_body,"}}")+2;

        return substr($this->html_replaced_body,$startPos,$endPos-$startPos);
    }

    private function setSubtextParams($a_subtextParams){
        $a_settings = array(
            "NotificationType" => array(
                "subtext"=>array("NotificationReport"),
                "types"=>array("diretta"=>1,"riscossione"=>2,"giudiziario"=>3,"procedimento"=>4)
            ),
            "PrintTypeId" => array(
                "subtext"=>array("SendType"),
                "types"=>array(1=>1,2=>1,4=>3,6=>2)
            ),
            "DirittoRiscossione" => array(
                "subtext"=>array("DirittoRiscossione","TotaliComplessivi"),
                "types"=>array(0=>2,1=>1)
            ),
            "CDS_Altri" => array(
                "subtext"=>array("InfoCDS_Altri","PremessoCDS_Altri","TotaliCDS_Altri","RelataCDS_Altri"),
                "types"=>array(1=>1,2=>2)
            )
        );

        $a_temp = array();
        $a_temp["Info"] = 1;
        foreach($a_subtextParams as $keySubtext=>$a_subtextParam){
            foreach($a_settings[$keySubtext]['subtext'] as $settingSubtext){
                $a_temp[$settingSubtext] = $a_settings[$keySubtext]['types'][$a_subtextParam];
            }
        }
        return $a_temp;
    }

    public function filterSubtexts($a_subtexts, $a_subtextParams){
        $a_subtextParams = $this->setSubtextParams($a_subtextParams);

        $this->a_subtexts = array();
        foreach($a_subtexts as $a_subtext){
            foreach($a_subtextParams as $keyParam=>$setParam){
                if($a_subtext['Variable']=="{{".$keyParam."}}" && $a_subtext['Type_ID']==$setParam){
                    if($a_subtext['Variable']=="{{SendType}}"){
                        $a_subtext['Content'] = str_replace("<p>","",$a_subtext['Content']);
                        $a_subtext['Content'] = str_replace("</p>","",$a_subtext['Content']);
                    }
                    $this->a_subtexts[$a_subtext['Variable']] = $a_subtext['Content'];
                    break;
                }
            }
        }
    }

    public function replaceSubtexts(){
        
        $this->html_replaced_body = $this->html_body;
        while(strpos($this->html_replaced_body,"{{")!==false){
            $subtext = $this->getSubtext();
            $this->replaceBody($subtext,$this->a_subtexts[$subtext]);
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

    public function setResponsibleSignaturesVar($a_signatures){
        $this->a_var["{SignLegale}"] = $this->setHtmlSignature($a_signatures['funzionario']);
        $this->a_var["{SignRespProcedimento}"] = $this->setHtmlSignature($a_signatures['procedimento']);
        $this->a_var["{SignRespRichieste}"] = $this->setHtmlSignature($a_signatures['richieste']);
        $this->a_var["{SignUfficiale}"] = $this->setHtmlSignature($a_signatures['ufficiale']);
        $this->a_var["{SignFunzRiscossione}"] = $this->setHtmlSignature($a_signatures['funz_riscossione']);

        if(!empty($a_signatures['funzionario']['name']))
            $this->a_var["{LegaleRappresentante}"] = $a_signatures['funzionario']['name'];
        else
            $this->a_var["{LegaleRappresentante}"] = "<span style='color:darkred;'>Funzionario responsabile/legale rappresentante assente!!!</span>";
        if(!empty($a_signatures['procedimento']['name']))
            $this->a_var["{RespProcedimento}"] = $a_signatures['procedimento']['name'];
        else
            $this->a_var["{RespProcedimento}"] = "<span style='color:darkred;'>Responsabile del procedimento assente!!!</span>";
        if(!empty($a_signatures['richieste']['name']))
            $this->a_var["{RespRichieste}"] = $a_signatures['richieste']['name'];
        else
            $this->a_var["{RespRichieste}"] = "<span style='color:darkred;'>Responsabile richieste assente!!!</span>";
        if(!empty($a_signatures['ufficiale']['name']))
            $this->a_var["{UfficialeRiscossione}"] = $a_signatures['ufficiale']['name'];
        else
            $this->a_var["{UfficialeRiscossione}"] = "<span style='color:darkred;'>Ufficiale riscossione assente!!!</span>";
        if(!empty($a_signatures['funz_riscossione']['name']))
            $this->a_var["{FunzionarioRiscossione}"] = $a_signatures['funz_riscossione']['name'];
        else
            $this->a_var["{FunzionarioRiscossione}"] = "<span style='color:darkred;'>Funzionario riscossione assente!!!</span>";
    }

    public function setResponsibleSignaturesVarLAB($a_signatures){
        $this->a_var["{SignLegale}"] = isset($a_signatures['funzionario']['name'])?$a_signatures['funzionario']['name']:"Firma assente!";//$this->setHtmlSignature($a_signatures['funzionario']);
        $this->a_var["{SignRespProcedimento}"] = isset($a_signatures['procedimento']['name'])?$a_signatures['procedimento']['name']:"Firma assente!";//$this->setHtmlSignature($a_signatures['procedimento']);
        $this->a_var["{SignRespRichieste}"] = isset($a_signatures['richieste']['name'])?$a_signatures['richieste']['name']:"Firma assente!";//$this->setHtmlSignature($a_signatures['richieste']);
        $this->a_var["{SignUfficiale}"] = isset($a_signatures['ufficiale']['name'])?$a_signatures['ufficiale']['name']:"Firma assente!";//$this->setHtmlSignature($a_signatures['ufficiale']);
        $this->a_var["{SignFunzRiscossione}"] = isset($a_signatures['funz_riscossione']['name'])?$a_signatures['funz_riscossione']['name']:"Firma assente!";//$this->setHtmlSignature($a_signatures['funz_riscossione']);

        if(!empty($a_signatures['funzionario']['name']))
            $this->a_var["{LegaleRappresentante}"] = $a_signatures['funzionario']['name'];
        else
            $this->a_var["{LegaleRappresentante}"] = "<span style='color:darkred;'>Funzionario responsabile/legale rappresentante assente!!!</span>";
        if(!empty($a_signatures['procedimento']['name']))
            $this->a_var["{RespProcedimento}"] = $a_signatures['procedimento']['name'];
        else
            $this->a_var["{RespProcedimento}"] = "<span style='color:darkred;'>Responsabile del procedimento assente!!!</span>";
        if(!empty($a_signatures['richieste']['name']))
            $this->a_var["{RespRichieste}"] = $a_signatures['richieste']['name'];
        else
            $this->a_var["{RespRichieste}"] = "<span style='color:darkred;'>Responsabile richieste assente!!!</span>";
        if(!empty($a_signatures['ufficiale']['name']))
            $this->a_var["{UfficialeRiscossione}"] = $a_signatures['ufficiale']['name'];
        else
            $this->a_var["{UfficialeRiscossione}"] = "<span style='color:darkred;'>Ufficiale riscossione assente!!!</span>";
        if(!empty($a_signatures['funz_riscossione']['name']))
            $this->a_var["{FunzionarioRiscossione}"] = $a_signatures['funz_riscossione']['name'];
        else
            $this->a_var["{FunzionarioRiscossione}"] = "<span style='color:darkred;'>Funzionario riscossione assente!!!</span>";
    }

    private function setHtmlSignature($signature){
        if(isset($signature['type'])){
            if($signature['type']=="file"){
                $cls_file = new cls_file();
                $imgDim = $cls_file->imageSize($signature['filePath'],140,45);

                $htmlSign= "<img src=\"".$signature['fileWebPath']."\" style=\"width: ".$imgDim[0]."px; height: ".$imgDim[1]."px;\" /><br>";
                $htmlSign.= "<span>".strtoupper($signature['name'])."</span>";

            }
            else if($signature['type']=="text"){
                $htmlSign= "<span>".$signature['replacementText']."</span><br>";
                $htmlSign.= "<span>".strtoupper($signature['name'])."</span>";
            }
        }
        else{
            $htmlSign = "<span style='color:darkred;'>!!!FIRMA ASSENTE!!!</span><br>";
        }
        return $htmlSign;
    }

    public function setPaymentParamsVar($a_params){
        $this->a_var["{AccountHolder}"] = $a_params['Intestatario_Conto'];
        $this->a_var["{CCP_Accountholder}"] = $a_params['Intestatario_Conto'];
        $this->a_var["{AccountNumber}"] = $a_params['Numero_Conto'];
        $this->a_var["{CCP_Number}"] = $a_params['Numero_Conto'];

        $this->a_var["{FineDays}"] = $a_params['Scadenza_Sanzione'];
        $this->a_var["{IngiunzioneExpireDays}"] = $a_params['Scadenza_Ingiunzione'];
        $this->a_var["{AvvisoIntimazioneDays}"] = $a_params['Scadenza_Avviso'];
        $this->a_var["{PignoramentoExpireDays}"] = $a_params['Scadenza_Pignoramento'];
        $this->a_var["{CautelariExpireDays}"] = $a_params['Scadenza_Cautelari'];
        $this->a_var["{IBAN}"] = " (IBAN ".$a_params['IBAN'].")";
        $this->a_var["{IBAN_1}"] = $a_params['IBAN'];
        if($a_params['Tipo_Conto']=="Poste Italiane")
            $this->a_var["{SmarrimentoCCP}"] = "utilizzando l'allegato bollettino di c.c.p.. In caso di smarrimento od inutilizzabilita' del bollettino allegato, il pagamento potrà essere eseguito utilizzando un altro bollettino di c.c.p. disponibile presso gli uffici postali.";
        else
            $this->a_var["{SmarrimentoCCP}"] = "";
    }

    public function setYearParamsVar($a_params){

        $this->a_var["{SpeseNotAtto}"] = number_format($a_params['Spese_Notifica'],2,",",".");
        $this->a_var["{SpeseNotPignoramento}"] = number_format($a_params['Spese_Notifica_Pignoramento'],2,",",".");
        $this->a_var["{SpeseNotCautelari}"] = number_format($a_params['Spese_Notifica_Cautelari'],2,",",".");

        $this->a_var["{SpeseAManiAtto}"] = number_format($a_params["A_Mani"],2,",",".");
        $this->a_var["{SpeseAManiPignoramento}"] = number_format($a_params["A_Mani_Pignoramento"],2,",",".");
        $this->a_var["{SpeseAManiCautelari}"] = number_format($a_params["A_Mani_Cautelari"],2,",",".");

        $this->a_var["{SpesePostali}"] = number_format($a_params['Spese_Postali'],2,",",".");
        $this->a_var["{SpeseAR}"] = number_format($a_params['Spese_Raccomandata'],2,",",".");
        $this->a_var["{SpeseAG}"] = number_format($a_params['Spese_Postali_AG'],2,",",".");

        $this->a_var["{CAD}"] = number_format($a_params['CAD'],2,",",".");
        $this->a_var["{CAN}"] = number_format( $a_params['CAN'],2,",",".");

        $this->a_var["{SpesePEC}"] = number_format($a_params["Spese_Pec"],2,",",".");
        $this->a_var["{SpesePECBanca}"] = number_format(isset($a_params["Spese_Pec_Banca"])?$a_params["Spese_Pec_Banca"]:0,2,",",".");

        $this->a_var["{GiorniDirittoRiscossione}"] = $a_params['Giorni_Diritto'];
        $this->a_var["{PercMinDirittoRiscossione}"] = number_format($a_params['Diritto_Riscossione_Minimo'],2,",",".");
        $this->a_var["{PercMaxDirittoRiscossione}"] = number_format($a_params['Diritto_Riscossione_Massimo'],2,",",".");

    }

    public function setUserPec($a_pec){
        $this->a_var["{PecOperatore}"] = $a_pec['Address'];
    }

    public function setEnteVar($a_ente){

        $this->a_var["{CC}"] = $a_ente['CC'];
        $this->a_var["{Ente}"] = $a_ente['Ente_Denominazione'];

        $a_types = array("Manager","Info","Gestore","Ufficio");
        foreach($a_types as $type){
            switch($type){
                case "Manager":     $enteType = $a_ente['Manager_Tipo']."_";
                    if($a_ente['Manager_Tipo']=="Gestore"){
                        $this->a_var["{".$type."Denominazione}"] = "Concessionario ".$a_ente[$enteType.'Denominazione'];
                        $this->a_var["{".$type."Abilitazione}"] = $a_ente[$enteType.'Abilitazione'];
                    }
                    else{
                        $this->a_var["{".$type."Denominazione}"] = $a_ente[$enteType.'Denominazione'];
                        $this->a_var["{".$type."Abilitazione}"] = "";
                    }
                        
                    $this->a_var["{".$type."Address}"] = $a_ente[$enteType.'Address'];
                    
                    break;
                default:
                    $enteType = $type."_";
                    $this->a_var["{".$type."Denominazione}"] = ucwords(strtolower($a_ente[$enteType.'Denominazione']));
                    break;
            }

            $this->a_var["{".$type."CF}"] = $a_ente[$enteType.'CF'];
            $this->a_var["{".$type."PI}"] = $a_ente[$enteType.'PI'];
            $this->a_var["{".$type."Paese}"] = $a_ente[$enteType.'Paese'];
            $this->a_var["{".$type."Comune}"] = $a_ente[$enteType.'Comune'];
            $this->a_var["{".$type."Provincia}"] = $a_ente[$enteType.'Provincia'];
            $this->a_var["{".$type."Frazione}"] = $a_ente[$enteType.'Frazione'];
            $this->a_var["{".$type."Via}"] = $a_ente[$enteType.'Via'];
            $this->a_var["{".$type."Civico}"] = $a_ente[$enteType.'Civico'];
            $this->a_var["{".$type."Esponente}"] = $a_ente[$enteType.'Esponente'];
            $this->a_var["{".$type."Interno}"] = $a_ente[$enteType.'Interno'];
            $this->a_var["{".$type."Dettagli}"] = $a_ente[$enteType.'Dettagli'];
            $this->a_var["{".$type."Cap}"] = $a_ente[$enteType.'Cap'];
            $this->a_var["{".$type."Address}"] = $a_ente[$enteType.'Address'];

            $this->a_var["{".$type."Fax}"] = $a_ente[$enteType.'Fax'];
            $this->a_var["{".$type."Phone}"] = $a_ente[$enteType.'Telefono'];
            $this->a_var["{".$type."Email}"] = $a_ente[$enteType.'Mail'];
            $this->a_var["{".$type."Pec}"] = $a_ente[$enteType.'PEC'];

            $this->a_var["{".$type."Sito}"] = $a_ente[$enteType.'Interno'];
            $this->a_var["{".$type."Orario}"] = $a_ente[$enteType.'Dettagli'];

            if($type=="Gestore")
                $this->a_var["{".$type."Abilitazione}"] = $a_ente[$enteType.'Abilitazione'];
        }
    }

    public function setAppealParamsVar($a_params){

        $this->a_var["{CGTDays}"] = $a_params['Termini_Corte_Giustizia_Tributaria'];
        $this->a_var["{GiustiziaDays}"] = $a_params['Termini_Giustizia_Ordinaria'];

    }

    public function setAuthorityParamsVar($a_params){
        foreach($a_params as $authority=>$a_authority){
            $this->a_var["{".$authority."}"] = $a_authority;
        }
    }

    public function setParamsVar(){
        foreach ($this->a_params as $type=>$params){
            switch($type){
                case "ente":              $this->setEnteVar($params);                       break;
                case "responsibles":      $this->setResponsibleSignaturesVar($params);      break;
                case "payment":           $this->setPaymentParamsVar($params);              break;
                case "year":              $this->setYearParamsVar($params);                 break;
                case "appeal":            $this->setAppealParamsVar($params);               break;
                case "authority":         $this->setAuthorityParamsVar($params);            break;
                case "userPec":           $this->setUserPec($params);                  break;
            }
        }
        
    }
    public function setParamsVarLAB(){
        foreach ($this->a_params as $type=>$params){
            switch($type){
                case "ente":              $this->setEnteVar($params);                       break;
                case "responsibles":      $this->setResponsibleSignaturesVarLAB($params);      break;
                case "payment":           $this->setPaymentParamsVar($params);              break;
                case "year":              $this->setYearParamsVar($params);                 break;
                case "appeal":            $this->setAppealParamsVar($params);               break;
                case "authority":         $this->setAuthorityParamsVar($params);            break;
                case "userPec":           $this->setUserPec($params);                  break;
            }
        }
        
    } 
    public function setRowVars(cls_ruolo $cls_ruolo, array $a_recipientHeader){

        //var_dump($cls_ruolo);die;

        if($cls_ruolo->a_result['TableTypeId']==1 && $cls_ruolo->a_result['Atto_Rettificato']==1)
            $docType = "RETTIFICA ".$cls_ruolo->a_result['RettificaDetails'];
        else
            $docType = strtoupper($cls_ruolo->a_result['DocumentType']);

        if($cls_ruolo->a_result['TableTypeId']==1)
            $Type = "atto";
        else
            $Type = "pigno";

        if($cls_ruolo->a_result['Tipo_Riscossione']=="CDS")
            $recipientType = "trasgressore";
        else
            $recipientType = "contribuente";

        if($cls_ruolo->a_result['DocumentTypeId'] == 11)            
            $amountListLAB = $cls_ruolo->getHtmlAmountsSollLAB();                                                       // sollecito di pagamento [11]
        else if ($cls_ruolo->a_result['DocumentTypeId'] == 3)        
            $amountListLAB = $cls_ruolo->getHtmlAmountsSoll160LAB();                                                    // sollecito di pagamento L.160 [3]
        else if ($cls_ruolo->a_result['DocumentTypeId'] == 4)        
            $amountListLAB = $cls_ruolo->getHtmlAmountsComplyLAB();                                                     // avviso di intimazione ad adempiere [4])
        else
            $amountListLAB = $cls_ruolo->getHtmlAmountsLAB($Type);                                                      // ingiunzione (2)

        $a_var = array(
            "{Recipient}"=> implode(" ",$a_recipientHeader['denomination']),
            "{ActualDate}" => date("d/m/Y"),
            "{RecipientAddress}" => $a_recipientHeader['address'],
            "{RecipientID}" => $cls_ruolo->a_result['Utente_Comune_ID'],
            "{RecipientType}" => $recipientType,
            "{RecipientPEC}" => $cls_ruolo->a_result['Utente_PEC'],
            "{AllDataLAB}" => $a_recipientHeader['data_lab'],
            "{DenomUfficioTributi}" => $a_recipientHeader['denom_ufficio_tributi'],
            "{ViaUfficioTributi}" => $a_recipientHeader['via_ufficio_tributi'],

            "{EMailCom}" => $a_recipientHeader['email_com'],
            "{PECCom}" => $a_recipientHeader['pec_com'],
            "{TelCom}" => $a_recipientHeader['telefono_com'],
            "{EMailLAB}" => $a_recipientHeader['email_lab'],
            "{PECLAB}" => $a_recipientHeader['pec_lab'],
            "{TelLAB}" => $a_recipientHeader['telefono_lab'],
            "{EMailUffTrib}" => $a_recipientHeader['email_uff_trib'],
            "{PECUffTrib}" => $a_recipientHeader['pec_uff_trib'],
            "{TelUffTrib}" => $a_recipientHeader['telefono_uff_trib'],

            "{PartitaID}" => $cls_ruolo->a_result['Comune_ID'] ,
            "{PartitaYear}" => $cls_ruolo->a_result['Anno_Riferimento'] ,
            "{TaxType}" => $cls_ruolo->getTaxType($cls_ruolo->a_result['Tipo_Riscossione']),
            "{InfoCartella}" => "'".$cls_ruolo->a_result['Info_Cartella']."'",

            "{DocType}" => $docType,
            "{CronoID}" => $cls_ruolo->a_result['ID_Cronologico'],
            "{CronoYear}" => $cls_ruolo->a_result['Anno_Cronologico'],
            "{Crono}" => $cls_ruolo->a_result['ID_Cronologico']."/".$cls_ruolo->a_result['Anno_Cronologico'],
            "{AmountDue}" => number_format($cls_ruolo->a_result['Totale_Dovuto'],2,",",".")." Euro",
            "{InfoAtto}" => $cls_ruolo->getPreviousActs(),

            "{AmountsList}" => $cls_ruolo->getHtmlAmounts($Type),
            "{AmountsListLine}" => $cls_ruolo->getHtmlAmountsLine($Type),

            "{PaymentReferences}" => $cls_ruolo->getReferences($Type),
            "{Reference}" => $cls_ruolo->a_result["Partita_ID"]."/".$cls_ruolo->a_result["Anno_Riferimento"],

            "{User}" => strtoupper($a_recipientHeader["Cognome_Ditta"]),
            "{CF_PI}" => $a_recipientHeader["CF_PI_VAR"],
            "{Comune_SedeLegale}" => strtoupper($a_recipientHeader["addressCity"]),
            "{Via_SedeLegale}" => $a_recipientHeader["Via_SedeLegale"],
            "{total_injunction}" => number_format($cls_ruolo->getInjunctionTotal()+$cls_ruolo->a_result['Diritto_Riscossione_Minimo'],2,",","."),
            "{AmountsListLAB}" => $amountListLAB,
            "{TotSoll}" => isset($cls_ruolo->solict_values['tot'])?number_format($cls_ruolo->solict_values['tot'],2,",","."):"",
            "{SpeseSoll}" => isset($cls_ruolo->solict_values['spese_sped'])?$cls_ruolo->solict_values['spese_sped']:"",
            "{AmountsListNoCDSLAB}" => $cls_ruolo->getHtmlAmountsNoCDSLAB($Type),
            "{Common_Address}" => $a_recipientHeader['Indirizzo_Comune'],
            "{Dati_Nascita_Utente}" => $a_recipientHeader['Dati_Nascita_Utente'],
            "{Comune_Corte_Tributaria}" => $a_recipientHeader['corte_trib'],
            "{ManagerCom}" => $a_recipientHeader['manager_com'],
            "{OfficeTimes}" => $cls_ruolo->officeTimes(),
            "{Determinazione}" => $cls_ruolo->getDeterminationData(),
            //"{Determinazione}" => $cls_ruolo->getDeterminationDataDB(),
            "{QrCode}" => IMMAGINI."/qr-code.png"
        );

        $this->a_var = array_merge($this->a_var,$a_var);
    }    

    function CreaHeader($a_result)
    {
        $result = "Utente:".$a_result["Utente_Comune_ID"]."/".$a_result["CC"]." - ".
        "Partita:".$a_result["Comune_ID"]."/".$a_result["Anno_Riferimento"]." - ".
        "ING:".$a_result["Protocollo"]."/".$a_result["Anno_Protocollo"]." - ".
        $a_result["Nome_Comune"];
        return $result;
        
    }

    function CreateUser($a_result)
    {
        $result="";
        if ($a_result["User"]="")
        {
            $result=$a_result["Ditta"];
        }
        else
        {
            $result=$a_result["User"];
        }
        return $result;
    }
    function CreateCFPI($a_result)
    {
        $result="";
        if ($a_result["User"]="")
        {
            $result=$a_result["Partita_Iva"];
        }
        else
        {
            $result=$a_result["Codice_Fiscale"];
        }
        return $result;
    }

    public function tutti_gli_atti_notificati($Partita_ID, $a_params=null,&$infoAtto = null)
    {
        $NoNotifica = function($s)
            {
                $s = strtoupper($s);
                $keys = array("SOLLECITO");
                $found =(explode(" ",$s));
                foreach($keys as $key)
                {
					if (in_array($key, $found))
                    	return true;
                }
                return false;
                
            };

        $date = new cls_DateTimeI("IT",false);
        
        $query = "SELECT * FROM atto WHERE Partita_ID = ".$Partita_ID." AND (( Data_Notifica is not null AND DocumentTypeId != 3 AND DocumentTypeId !=11 ) ".
        "OR ( DocumentTypeId=3 OR DocumentTypeId=11 ))";
        if(!empty($a_params['ElabType']) && !empty($a_params['ID'])){
            if($a_params['ElabType']=="atto")
                $query.=" AND ID!=".$a_params['ID'];
        }
        $result = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
        
        $atti_notificati = "";
        $aCapo = "";
        for($i=count($result)-1, $x=1 ; $i>=0; $i--, $x++)
        {
            $atto = $result[$i];

            $numeroatto = $atto["ID_Cronologico"]." DEL ".$atto["Anno_Cronologico"];
            if($i>0) $aCapo = "<br>";
            else $aCapo = "";
            $notifica = $NoNotifica($atto["Atto"]) ? "" : " NOTIFICATO IL ".$this->cls_date->Get_DateNewFormat($atto["Data_Notifica"],"DB");
            $atti_notificati .= $x.") ".strtoupper($atto["Atto"]." N. ".$numeroatto.$notifica).$aCapo;

            if($x==1) $infoAtto = strtoupper($atto["Atto"]." N. ".$numeroatto." NOTIFICATO IL ".$this->cls_date->Get_DateNewFormat($atto["Data_Notifica"],"DB"));
           
            $query = "SELECT D.Description AS Descrizione_Pigno ,N.Data_Notifica ,P.* 
                        FROM pignoramento_generale as P 
                        JOIN document_type as D on D.Id = P.DocumentTypeId 
                        JOIN notifica_atto as N on P.ID = N.Atto_Notificato_ID AND N.Data_Notifica is not null AND N.Tipo_Notifica='debitore'
                        where P.Atto_ID = ".$atto["ID"];
            if(!empty($a_params['ElabType']) && !empty($a_params['ID'])){
                if($a_params['ElabType']=="pignoramento")
                    $query.=" AND P.ID!=".$a_params['ID'];
            }
            $resultPigno = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

            //echo $query;
            //die;
            $aCapo = "<br>";
            
            
            foreach($resultPigno as $keyPigno=>$a_pigno){
                $notifica = $NoNotifica($a_pigno["Descrizione_Pigno"]) ? "" : " NOTIFICATO IL ".$this->cls_date->Get_DateNewFormat($a_pigno["Data_Notifica"],"DB");
                $atti_notificati .= $x.".".($keyPigno+1).") ".strtoupper($a_pigno["Descrizione_Pigno"]." N. ".$a_pigno["ID_Cronologico"]." DEL ".
                $a_pigno["Anno_Cronologico"].$notifica).$aCapo;
            }
        }

        return $atti_notificati;
    }

    public function setRowVarsPignoramento(cls_ruolo $cls_ruolo,array $a_recipientHeader){
        switch($this->document_type_id)
        {
            case "7": return $this->setRowVarsPignoTerzo($cls_ruolo,$a_recipientHeader);
            case "8": return $this->setRowVarsPignoBanca($cls_ruolo,$a_recipientHeader); //todo
            case "22" : return $this->setRowVarsPreavvisoPigno($cls_ruolo,$a_recipientHeader);
        }
    }

    public function setRowVarsPignoramentoLAB(cls_ruolo $cls_ruolo,array $a_recipientHeader,array $allBank){
        switch($this->document_type_id)
        {
            case "7": return $this->setRowVarsPignoTerzo($cls_ruolo,$a_recipientHeader);
            case "8": return $this->setRowVarsPignoBancaLAB($cls_ruolo,$a_recipientHeader,$allBank); //todo
            case "22" : return $this->setRowVarsPreavvisoPigno($cls_ruolo,$a_recipientHeader);
        }
    }
    public function setRowVarsPreavvisoPigno(cls_ruolo $cls_ruolo,array $a_recipientHeader){
        $docType = "preavviso di fermo amministrativo";

        $Type="pigno";
        $a_last=LastAct::GetLastActByPartita($this->cls_db,$cls_ruolo->a_result["Partita_ID"]);
        $a_var = array(
            "{LastDocumentType}"=>$a_last["LastDocumentType"],
            "{LastCronologico}"=>$a_last["LastIdCronologico"]."/".$a_last["LastAnnoCronologico"],
            "{LastDataNotifica}"=>$this->cls_date->Get_DateNewFormat($a_last["LastDataNotifica"],"DB"),
            "{LastTotaleDovuto}"=>number_format($cls_ruolo->a_result["Importo_Atto"],2,",","."),
            "{InfoCartella}"=>$a_last["InfoCartella"],
            "{VeicoliDetenuti}"=>VeicoliPreavviso::GetHTMLTable($this->cls_db,$cls_ruolo->a_result["Utente_ID_Partita"],$cls_ruolo->a_result["Veicolo_ID"]),
            "{VeicoloFermo}"=>VeicoliPreavviso::VeicoloFermoHTML($cls_ruolo->a_result["Tipo_Veicolo"],$cls_ruolo->a_result["Targa_Veicolo"],$cls_ruolo->a_result["Marca_Veicolo"],$cls_ruolo->a_result["Modello_Veicolo"]),
            "{AttiNotificati}"=>$this->tutti_gli_atti_notificati($cls_ruolo->a_result['Partita_ID'], array("ElabType"=>"pignoramento","ID"=>$cls_ruolo->a_result['ID'])),
            "{RecipientID}" => $cls_ruolo->a_result['Utente_Comune_ID'],
            "{PartitaID}" => $cls_ruolo->a_result['Comune_ID'] ,
            "{PartitaYear}" => $cls_ruolo->a_result['Anno_Riferimento'] ,
            "{DocType}"=>$docType,
            "{CronoID}"=>$cls_ruolo->a_result["ID_Cronologico"],
            "{CronoYear}"=>$cls_ruolo->a_result["Anno_Cronologico"], 
            "{AmountsList}" => $cls_ruolo->getHtmlAmounts($Type),
            "{AmountsListLine}" => $cls_ruolo->getHtmlAmountsLine($Type),
            "{Reference}" => $cls_ruolo->a_result["Partita_ID"]."/".$cls_ruolo->a_result["Anno_Riferimento"],
            "{Recipient}"=> implode(" ",$a_recipientHeader['denomination']),
            "{RecipientPEC}" => $cls_ruolo->a_result['Utente_PEC'],
            "{Manager}"=>$this->a_var["{ManagerDenominazione}"], //dentro la relata
            
           
        );

        $this->a_var = array_merge($this->a_var,$a_var);
        
    }

    private function getUserDataLAB($data){
        $str = "";
        if(isset($data["recipient"])) $str .= $data["recipient"]."<br>";
        if(isset($data["addressRow"])){
            for($i=0; $i< count($data["addressRow"]); $i++){
                if(!empty($data["addressRow"][$i]))
                    $str .= $data["addressRow"][$i]."<br>";
            }
        }
        if(isset($data["CF_PI"])) $str .= $data["CF_PI"];

        return $str;
    }

    public function setRowVarsPignoTerzo(cls_ruolo $cls_ruolo,array $a_recipientHeader){

        $docType = "Pignoramento del terzo (lavoro)";
        $infoAtto = "";
        $Type="pigno";
        $a_last=LastAct::GetLastActByPartita($this->cls_db,$cls_ruolo->a_result["Partita_ID"]);
        $lastAct = function () use ($a_last)
        {
            return $a_last["LastDocumentType"]." n.".$a_last["LastIdCronologico"]." del ".$a_last["LastAnnoCronologico"]
            ." notificata il ".$this->cls_date->Get_DateNewFormat($a_last["LastDataNotifica"],"DB");
        };
        $a_var = array(
            "{LastDocumentType}"=>$a_last["LastDocumentType"],
            "{LastCronologico}"=>$a_last["LastIdCronologico"]."/".$a_last["LastAnnoCronologico"],
            "{LastDataNotifica}"=>$this->cls_date->Get_DateNewFormat($a_last["LastDataNotifica"],"DB"),
            "{LastTotaleDovuto}"=>number_format($cls_ruolo->a_result["Importo_Atto"],2,",","."),
            "{InfoCartella}"=>$a_last["InfoCartella"],
            "{ActsNotified}"=>$this->tutti_gli_atti_notificati($cls_ruolo->a_result['Partita_ID'], array("ElabType"=>"pignoramento","ID"=>$cls_ruolo->a_result['ID']),$infoAtto),
            "{RecipientID}" => $cls_ruolo->a_result['Utente_Comune_ID'],
            "{PartitaID}" => $cls_ruolo->a_result['Comune_ID'] ,
            "{PartitaYear}" => $cls_ruolo->a_result['Anno_Riferimento'] ,
            "{DocType}"=>$docType,
            "{CronoID}"=>$cls_ruolo->a_result["ID_Cronologico"],
            "{CronoYear}"=>$cls_ruolo->a_result["Anno_Cronologico"], 
            "{AmountsList}" => $cls_ruolo->getHtmlAmounts($Type),
            "{AmountsListLine}" => $cls_ruolo->getHtmlAmountsLine($Type),
            "{Reference}" => $cls_ruolo->a_result["Partita_ID"]."/".$cls_ruolo->a_result["Anno_Riferimento"],
            "{Recipient}"=> implode(" ",$a_recipientHeader['denomination']),
            "{RecipientPEC}" => $cls_ruolo->a_result['Utente_PEC'],
            "{managerOffice}" =>$this->a_var["{ManagerAddress}"] ,
            "{User}" => $cls_ruolo->a_result['User'],
            "{UserResidence}" => $cls_ruolo->a_result['Indirizzo_Debitore'],
            "{CFPI}" => $cls_ruolo->a_result['CF_PI'],
            "{UserCode}" =>$cls_ruolo->a_result['Utente_Comune_ID']." / ".$cls_ruolo->a_result['CC'],
            "{Terzi}" =>$cls_ruolo->a_result['Terzi'], 
            "{TerziProTempore}" =>$cls_ruolo->a_result['Terzi'],
            "{PrintType}"=> "",//$cls_ruolo->a_result['PrintType'],
            "{InfoAtto}" => $infoAtto, //non corretto
            "{Crono}"=> $cls_ruolo->a_result["ID_Cronologico"],
            "{CompleteInjunction}"=>$lastAct(),
            "{Manager}"=>$this->a_var["{ManagerDenominazione}"], //dentro la relata
            "{ProcessingDate}"=>$this->cls_date->Get_DateNewFormat($cls_ruolo->a_result["Data_Calcolo_Interessi"],"DB")
        );
        
        $this->a_var = array_merge($this->a_var,$a_var);
        
    }

    public function setRowVarsPignoBanca(cls_ruolo $cls_ruolo,array $a_recipientHeader){

        $docType = "Pignoramento del terzo (banca)";
        $infoAtto = "";
        $Type="pigno";
        $a_last=LastAct::GetLastActByPartita($this->cls_db,$cls_ruolo->a_result["Partita_ID"]);
        $lastAct = function () use ($a_last)
        {
            return $a_last["LastDocumentType"]." n.".$a_last["LastIdCronologico"]." del ".$a_last["LastAnnoCronologico"]
            ." notificata il ".$this->cls_date->Get_DateNewFormat($a_last["LastDataNotifica"],"DB");
        };
        $a_var = array(
            "{LastDocumentType}"=>$a_last["LastDocumentType"],
            "{LastCronologico}"=>$a_last["LastIdCronologico"]."/".$a_last["LastAnnoCronologico"],
            "{LastDataNotifica}"=>$this->cls_date->Get_DateNewFormat($a_last["LastDataNotifica"],"DB"),
            "{LastTotaleDovuto}"=>number_format($cls_ruolo->a_result["Importo_Atto"],2,",","."),
            "{InfoCartella}"=>$a_last["InfoCartella"],
            "{ActsNotified}"=>$this->tutti_gli_atti_notificati($cls_ruolo->a_result['Partita_ID'], array("ElabType"=>"pignoramento","ID"=>$cls_ruolo->a_result['ID']),$infoAtto),
            "{RecipientID}" => $cls_ruolo->a_result['Utente_Comune_ID'],
            "{PartitaID}" => $cls_ruolo->a_result['Comune_ID'] ,
            "{PartitaYear}" => $cls_ruolo->a_result['Anno_Riferimento'] ,
            "{DocType}"=>$docType,
            "{CronoID}"=>$cls_ruolo->a_result["ID_Cronologico"],
            "{CronoYear}"=>$cls_ruolo->a_result["Anno_Cronologico"], 
            "{AmountsList}" => $cls_ruolo->getHtmlAmounts($Type),
            "{AmountsListLine}" => $cls_ruolo->getHtmlAmountsLine($Type),
            "{Reference}" => $cls_ruolo->a_result["Partita_ID"]."/".$cls_ruolo->a_result["Anno_Riferimento"],
            "{Recipient}"=> implode(" ",$a_recipientHeader['denomination']),
            "{RecipientPEC}" => $cls_ruolo->a_result['Recipient_PEC'],
            "{managerOffice}" =>$this->a_var["{ManagerAddress}"] ,
            "{User}" => $cls_ruolo->a_result['User'],
            "{UserResidence}" => $cls_ruolo->a_result['Indirizzo_Debitore'],
            "{CFPI}" => $cls_ruolo->a_result['CF_PI'],
            "{UserCode}" =>$cls_ruolo->a_result['Utente_Comune_ID']." / ".$cls_ruolo->a_result['CC'],
            "{Terzi}" =>$cls_ruolo->a_result['Terzi'], 
            "{TerziProTempore}" =>$cls_ruolo->a_result['Terzi'],
            "{PrintType}"=> "",//$cls_ruolo->a_result['PrintType'],
            "{InfoAtto}" => $infoAtto, //non corretto
            "{Crono}"=> $cls_ruolo->a_result["ID_Cronologico"],
            "{CompleteInjunction}"=>$lastAct(),
            "{LastAct}"=>$lastAct(),
            "{Manager}"=>$this->a_var["{ManagerDenominazione}"], //dentro la relata
            "{ProcessingDate}"=>$this->cls_date->Get_DateNewFormat($cls_ruolo->a_result["Data_Calcolo_Interessi"],"DB"),
            "{PrintDate}"=>date('d/m/Y')
        );
        
        $this->a_var = array_merge($this->a_var,$a_var);
        
    }

    public function setRowVarsPignoBancaLAB(cls_ruolo $cls_ruolo,array $a_recipientHeader,array $allBank){

        $cls_ruolo->getDataAct();

        $docType = "Pignoramento del terzo (banca)";
        $infoAtto = "";
        $Type="pigno";
        $a_last=LastAct::GetLastActByPartita($this->cls_db,$cls_ruolo->a_result["Partita_ID"]);
        $lastAct = function () use ($a_last)
        {
            return $a_last["LastDocumentType"]." n.".$a_last["LastIdCronologico"]." del ".$a_last["LastAnnoCronologico"]
            ." notificata il ".$this->cls_date->Get_DateNewFormat($a_last["LastDataNotifica"],"DB");
        };

        $a_var = array(
            "{LastDocumentType}"=>$a_last["LastDocumentType"],
            "{LastCronologico}"=>$a_last["LastIdCronologico"]."/".$a_last["LastAnnoCronologico"],
            "{LastDataNotifica}"=>$this->cls_date->Get_DateNewFormat($a_last["LastDataNotifica"],"DB"),
            "{LastTotaleDovuto}"=>number_format($cls_ruolo->a_result["Importo_Atto"],2,",","."),
            "{InfoCartella}"=>$a_last["InfoCartella"],
            "{ActsNotified}"=>$this->tutti_gli_atti_notificati($cls_ruolo->a_result['Partita_ID'], array("ElabType"=>"pignoramento","ID"=>$cls_ruolo->a_result['ID']),$infoAtto),
            "{RecipientID}" => $cls_ruolo->a_result['Utente_Comune_ID'],
            "{PartitaID}" => $cls_ruolo->a_result['Comune_ID'] ,
            "{PartitaYear}" => $cls_ruolo->a_result['Anno_Riferimento'] ,
            "{DocType}"=>$docType,
            "{CronoID}"=>$cls_ruolo->a_result["ID_Cronologico"],
            "{CronoYear}"=>$cls_ruolo->a_result["Anno_Cronologico"], 
            "{AmountsList}" => $cls_ruolo->getHtmlAmountsLAB($Type),
            "{AmountsListLine}" => $cls_ruolo->getHtmlAmountsLine($Type),
            "{Reference}" => $cls_ruolo->a_result["Partita_ID"]."/".$cls_ruolo->a_result["Anno_Riferimento"],
            "{Recipient}"=> implode(" ",$a_recipientHeader['denomination']),
            "{RecipientPEC}" => $cls_ruolo->a_result['Recipient_PEC'],
            "{managerOffice}" =>$this->a_var["{ManagerAddress}"] ,
            "{User}" => $cls_ruolo->a_result['User'],
            "{UserResidence}" => $cls_ruolo->a_result['Indirizzo_Debitore'],
            "{CFPI}" => $cls_ruolo->a_result['CF_PI'],
            "{UserCode}" =>$cls_ruolo->a_result['Utente_Comune_ID']." / ".$cls_ruolo->a_result['CC'],
            "{Terzi}" =>$cls_ruolo->a_result['Terzi'], 
            "{TerziProTempore}" =>$cls_ruolo->a_result['Terzi'],
            "{PrintType}"=> "",//$cls_ruolo->a_result['PrintType'],
            "{InfoAtto}" => $infoAtto, //non corretto
            "{Crono}"=> $cls_ruolo->a_result["ID_Cronologico"],
            "{CompleteInjunction}"=>$lastAct(),
            "{LastAct}"=>$lastAct(),
            "{Manager}"=>$this->a_var["{ManagerDenominazione}"], //dentro la relata
            "{ProcessingDate}"=>$this->cls_date->Get_DateNewFormat($cls_ruolo->a_result["Data_Calcolo_Interessi"],"DB"),
            "{PrintDate}"=>date('d/m/Y'),
            "{AllBank}" => $this->getHtmlAllBank($allBank),
            "{UserData}" => $this->getUserDataLAB($a_recipientHeader),
            "{LastActSummaryLAB}" => $cls_ruolo->getHtmlAmountsSoll160PignoLAB(),
            "{TributeTable}" => $cls_ruolo->getTributeTable(),
            "{CF_PI}" => $a_recipientHeader['CF_PI'],
            "{Cognome_Ditta}" => $a_recipientHeader['Cognome_Ditta'],
            "{Via_SedeLegale}" => $a_recipientHeader['Via_SedeLegale'],
            "{Dati_Nascita_Utente}" => $a_recipientHeader['Dati_Nascita_Utente'],
            "{Total_Pigno}" => $cls_ruolo->totalePignoramento(),
            "{Total_Pigno_Alpha}" => $cls_ruolo->totalePignoramentoAlfabetico(),
            "{EMailLAB}" => $a_recipientHeader['email_lab'],
            "{PECLAB}" => $a_recipientHeader['pec_lab'],
            "{TelLAB}" => $a_recipientHeader['telefono_lab'],
            "{ActualDate}" => date("d/m/Y"),
            "{ManagerCom}"=>$a_recipientHeader['manager_com']
        );
        
        $this->a_var = array_merge($this->a_var,$a_var);
        
    }

    private function getHtmlAllBank($allBank){
        $result = "";
        $count = 0;
        foreach($allBank as $key => $value){
            if($count == 0) $result .= "<b>".$value["Denominazione"]."</b><br>con sede in ";
            else $result .= "<br><b>".$value["Denominazione"]."</b><br>con sede in ";
            if(isset($value["Via"])) $result .= $value["Via"];
            if(isset($value["Civico"])) $result .= ", ".$value["Civico"];
            if(isset($value["Comune"])) $result .= " ".$value["Comune"];
            if(isset($value["Provincia"])) $result .= " (".$value["Provincia"].")";
            if(isset($value["Cap"])) $result .= " ".$value["Cap"];

            $count++;
        }

        return $result;
    }

    public function setRowVarsPignoTerzoRelataOriginale(cls_ruolo $cls_ruolo,array $a_recipientHeader){

        $a_var = array(
            "{Manager}"=>$this->a_var["{ManagerDenominazione}"], //dentro la relata
            "{Recipient}"=> implode(" ",$a_recipientHeader['denomination']),
            "{RecipientPEC}" => $cls_ruolo->a_result['Utente_PEC'],

            
        );
        
        $this->a_var = array_merge($this->a_var,$a_var);
        
    }
    //OLD DEPRECATED
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
                                case "procedimento":
                                    if($a_subtext['Type_ID']==4) {
                                        $this->replaceBody($a_subtext['Variable'], $a_subtext['Content']);
                                        $flag = true;
                                    }
                                    break;

                            }
                            break;
                        case "{{TotaliComplessivi}}":
                            switch($a_switchParams['TotaliComplessivi']){
                                case 1:
                                    if($a_subtext['Type_ID']==1) {
                                        $this->replaceBody($a_subtext['Variable'], $a_subtext['Content']);
                                        $flag = true;
                                    }
                                    break;
                                case 0:
                                    if($a_subtext['Type_ID']==2) {
                                        $this->replaceBody($a_subtext['Variable'], $a_subtext['Content']);
                                        $flag = true;
                                    }
                                    break;
                            }
                            break;
                        case "{{DirittoRiscossione}}":
                            switch($a_switchParams['DirittoRiscossione']){
                                case 1:
                                    if($a_subtext['Type_ID']==1) {
                                        $this->replaceBody($a_subtext['Variable'], $a_subtext['Content']);
                                        $flag = true;
                                    }
                                    break;
                                case 0:
                                    if($a_subtext['Type_ID']==2) {
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
                        case "{{Concessionario}}":
                            switch($a_switchParams['Completo_Parziale']){
                                case "completo":
                                    if($a_subtext['Type_ID']==1) {
                                        $this->replaceBody($a_subtext['Variable'], $a_subtext['Content']);
                                        $flag = true;
                                    }
                                    break;
                                case "parziale":
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
    //OLD DEPRECATED
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
            "{ActualDate}" => date("d/m/Y"),
            "{Ente}" => $cls_ente->getCityDenomination(),
            "{ManagerFax}" => $cls_ente->a_ente[$cls_ente->type.'_Fax'],
            "{ManagerPhone}" => $cls_ente->a_ente[$cls_ente->type.'_Telefono'],
            "{ManagerEmail}" => $cls_ente->a_ente[$cls_ente->type.'_Mail'],
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
            "{IBAN_1}"=> $a_paymentParams['IBAN'],
            "{AGFee}" => $a_yearParams['Spese_Postali_AG'],
            "{CAD}" => $a_yearParams['CAD'],
            "{CAN}" => $a_yearParams['CAN'],
            "{NotificationFee}" => $a_yearParams['Spese_Notifica'],
            "{ChargeDays}" => $a_yearParams['Giorni_Diritto'],
            "{SignLegale}" => $cls_params->getHtmlSignature("{SignLegale}"),
            "{SignRespProcedimento}" => $cls_params->getHtmlSignature("{SignRespProcedimento}"),
            "{SignRespRichieste}" => $cls_params->getHtmlSignature("{SignRespRichieste}"),
            "{SignUfficiale}" => $cls_params->getHtmlSignature("{SignUfficiale}"),
            "{CGTDays}" => $a_appealParams['Termini_Corte_Giustizia_Tributaria'],
            "{GiustiziaDays}" => $a_appealParams['Termini_Giustizia_Ordinaria'],
            "{CGT}" => $a_authority['CGT'],
            "{GDP}" => $a_authority['GDP'],
            "{Tribunale}" => $a_authority['Tribunale'],
            "{OrganizationAddress}" => $forAvInt["indirizzoEnte"],
            "{OfficialExpenses}" => $a_yearParams["A_Mani"]." Euro",
            "{DaysPayment}" => 30,
            "{CCP_Number}" => $a_paymentParams['Numero_Conto'],
            "{CCP_Accountholder}" => $a_paymentParams['Intestatario_Conto'],
            "{OfficeContactDetails}" => $cls_ente->getContactsManager(),
            "{ExpensesNotificationSeizure}" => $forAvInt["Spese_Notifica_Pignoramento"],
            "{ExpensesJudicialActs}" => $forAvInt["Spese_Postali_AG"],
            "{ExpenditureEstimateAssets}" => $forAvInt["ExpenditureEstimateAssets"],
            "{ResponsibleOfficer}" => $cls_params->a_responsabili["Funzionario_Responsabile"],
            "{IVG}" => isset($a_authority["IVG"])?$a_authority["IVG"]:null,

        );
    }
    //OLD DEPRECATED
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

        if($cls_ruolo->a_result['Tipo_Riscossione']=="CDS")
            $recipientType = "trasgressore";
        else
            $recipientType = "contribuente";

        $a_var = array(
            "{Recipient}"=> implode(" ",$a_recipientHeader['denomination']),
            "{RecipientAddress}" => $a_recipientHeader['address'],
            "{RecipientID}" => $cls_ruolo->a_result['Utente_Comune_ID'],
            "{RecipientType}" => $recipientType,

            "{PartitaID}" => $cls_ruolo->a_result['Comune_ID'] ,
            "{PartitaYear}" => $cls_ruolo->a_result['Anno_Riferimento'] ,
            "{TaxType}" => $cls_ruolo->getTaxType($cls_ruolo->a_result['Tipo_Riscossione']),
            "{InfoCartella}" => "'".$cls_ruolo->a_result['Info_Cartella']."'",

            "{DocType}" => $docType,
            "{CronoID}" => $cls_ruolo->a_result['ID_Cronologico'],
            "{CronoYear}" => $cls_ruolo->a_result['Anno_Cronologico'],
            "{Crono}" => $cls_ruolo->a_result['ID_Cronologico']."/".$cls_ruolo->a_result['Anno_Cronologico'],
            "{AmountDue}" => number_format($cls_ruolo->a_result['Totale_Dovuto'],2,",",".")." Euro",
            "{InfoAtto}" => $cls_ruolo->getPreviousActs(),

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

        );

        if($Type != "atto"){
            $a_pignoVar = array(
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
            $a_var = array_merge($a_var, $a_pignoVar);
        }

        $this->a_var = array_merge($this->a_var,$a_var);
    }
}

?>