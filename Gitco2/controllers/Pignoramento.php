<?php

require CONTROLLERS."/Ruolo.php";

class PignoramentoController extends RuoloController{

    public $Pignoramento_ID;

    public $a_pignoramento;
    public $a_speseaccessorie;
    public $a_notifiche;
    public $a_tariffe;
    public $a_IVG;
    public $a_veicolo;
    public $a_datori_lavoro;
    public $a_banche;
    

    public function __construct($partita_ID, $pignoramento_ID)
	{
		parent::__construct($partita_ID);

		$this->Pignoramento_ID = $pignoramento_ID;
        $this->setViews();

	}

    /**
    * * RENDERIZZA INTESTAZIONE PAGINA
    */
    public function showIntestazione(){
        return $this->returnView('pignoramento/intestazione');
    }

    /**
     * * RENDERIZZA TOTALI
     */
    public function showTotali(){
        return $this->returnView('pignoramento/totali');
    }

    /**
    * * RENDERIZZA SPESE ACCESSORIE
    */
    public function showSpeseAccessorie(){
        return $this->returnView('pignoramento/speseaccessorie');
    }

    /**
    * * RENDERIZZA NOTIFICHE
    */
    public function showNotifiche(){
        return $this->returnView('pignoramento/notifiche');
    }

    /**
     * * RENDERIZZA INFORMAZIONI
     */
    public function showInfo(){

        switch($this->a_pignoramento['DocumentTypeId']){
            case 6:
            case 22:
            case 25:
                return $this->showVeicolo();
                break;
            case 7:
                return $this->showDatoriLavoro();
                break;
            default:
            
                return null;
        }
        
    }

    /**
    * * RENDERIZZA VEICOLO
    */
    private function showVeicolo(){
        return $this->returnView('pignoramento/veicolo');
    }

    /**
    * * RENDERIZZA DATORI DI LAVORO
    */
    private function showDatoriLavoro(){
        return $this->returnView('pignoramento/datorilavoro');
    }


    /**
    * * CARICAMENTO DATI PER HTML
    */
    private function setViews(){

        $this->setPignoramento();
        
        $this->setTariffeCoazione(array("CC"=>$this->a_pignoramento['CC'],"DocumentTypeId"=>$this->a_pignoramento['DocumentTypeId']));
        $this->setSpeseAccessorie();
        
        switch($this->a_pignoramento['DocumentTypeId']){
            case 6:
                $this->setVeicolo();
                $this->setIVG(array('CC'=>$this->a_pignoramento['CC']));
                break;
            case 22:
            case 25:    
                $this->setVeicolo();
                break;
            case 7:
                $this->setDatoriLavoro();
                break;
            case 8:
                $this->setBanche();
                break;
        }

        $this->setNotifiche();
    }


    /**
     * * PIGNORAMENTO
     */
    public function getPignoramento()
    {
        $query =  "SELECT PG.*, REPLACE(PG.Importi_Rate,',','.') AS Importi_Rate, IF(U.Genere='D',U.Ditta,concat(U.Cognome,' ',U.Nome)) AS Denominazione_Debitore, DT.Description AS DocumentType, DT.PrefixName, " .
        "PT.Flag_Blocco_Coazione, PT.Flag_Annullamento, PT.Motivo_Blocco, PT.Note_Blocco, PT.Utente_ID, " .
        "RES.CC_Indirizzo AS CC_Residenza, RES.Comune AS Comune_Residenza, PS.ID AS Pignoramento_Spese_ID, " . 
        "RICH.File AS File_Richiesta, ESIT.File AS File_Esito, BOLL.File AS File_Bollettini, NA.Tipo_Notifica, NA.ID AS ID_Notifica, DT.PrefixName,  ";

        for ($i = 1; $i <= 10; $i++)
            $query .= "PS.Tipo_Totale_" . $i . ", PS.Spesa_" . $i . "_ID, PS.Tipo_Spesa_" . $i . ", PS.Rimborso_" . $i . ", PS.Extra_Spesa_" . $i . ", ";

        $query .= "PS.Totale_Rimborso, PS.Incremento_Percentuale " .
        "FROM pignoramento_generale PG " .
        "JOIN document_type DT ON PG.DocumentTypeId=DT.Id " .
        "LEFT JOIN documento RICH ON RICH.ID=PG.ID_Richiesta_Rateizzazione " .
        "LEFT JOIN documento ESIT ON ESIT.ID=PG.ID_Esito_Rateizzazione " .
        "LEFT JOIN documento BOLL ON BOLL.ID=PG.ID_Bollettini_Rateizzazione " .
        "LEFT JOIN notifica_atto NA ON NA.Atto_Notificato_ID=PG.ID AND NA.Tipo_Atto_Notificato = 'pignoramento' " .
        "JOIN pignoramento_spese PS ON PS.Pignoramento_ID=PG.ID " .
        "JOIN partita_tributi PT ON PT.ID=PG.Partita_ID " .
        "JOIN utente U ON U.ID=PT.Utente_ID " .
        "JOIN indirizzo RES ON RES.Utente_ID=PT.Utente_ID AND RES.Tipo='res' " .
        "WHERE PG.ID=" . $this->Pignoramento_ID;

        $this->a_pignoramento = $this->getRow($query);
    }

    private function setTotali(){
        $this->a_pignoramento['Totali_SA'] = array(1=>0,2=>0,3=>0);
        for($i=1;$i<=10;$i++){
            if($this->a_pignoramento['Rimborso_'.$i]>0){
                if(!empty($this->a_pignoramento['Tipo_Totale_'.$i]))
                    $this->a_pignoramento['Totali_SA'][$this->a_pignoramento['Tipo_Totale_'.$i]]+= $this->a_pignoramento['Rimborso_'.$i];
            }
        }
        $this->a_pignoramento['Totale_Parziale'] = $this->a_pignoramento['Importo_Dovuto'] + $this->a_pignoramento['Totale_Spese_Notifica'];
        $this->a_pignoramento['Totale_1'] = $this->a_pignoramento['Totale_Parziale']+$this->a_pignoramento['Totali_SA'][1];
        $this->a_pignoramento['Totale_2'] = $this->a_pignoramento['Totale_1']+$this->a_pignoramento['Totali_SA'][2];
        if($this->a_pignoramento['Totale_2']>$this->a_pignoramento['Totale_1'])
            $this->a_pignoramento['ShowTotale2'] = 1;
        else    
            $this->a_pignoramento['ShowTotale2'] = 0;
        $this->a_pignoramento['Totale_3'] = $this->a_pignoramento['Totale_2']+$this->a_pignoramento['Totali_SA'][3];
        if($this->a_pignoramento['Totale_3']>$this->a_pignoramento['Totale_2'])
            $this->a_pignoramento['ShowTotale3'] = 1;
        else    
            $this->a_pignoramento['ShowTotale3'] = 0;
    }

    private function setPignoramento(){

        $this->getPignoramento();
        $this->setTotali();

        $a_render['pignoramento'] = $this->a_pignoramento;

        //* OPZIONI TOTALI RATEIZZAZIONE
        $a_totals = array( array("Id"=>1,"Name"=>"Totale 1"), array("Id"=>2,"Name"=>"Totale 2"), array("Id"=>3,"Name"=>"Totale 3") );
        $a_selection = array("value" => "Id", "firstOpt" => 1, "selected" => $this->a_pignoramento['Tipo_Totale_Rate'], "text" => array("[Name]"));
        $a_render['pignoramento']['optTipoTotaleRate'] = $this->html->getOptions($a_totals, $a_selection);

        $drop = $this->db->getResults($this->db->ExecuteQuery("SELECT Id, Name FROM tipo_scadenza_rate ORDER BY Order_Val ASC"));
        $opt = "";
        foreach($drop as $key => $value){
            $opt .= "<option value='".$value["Id"]."'>".$value["Name"]."</option>";
        }
        $a_render['pignoramento']['optTipoScadRate'] = $opt;

        $this->addRenderParams($a_render);
    }


    /**
     * * SPESE ACCESSORIE PIGNORAMENTO
     */
    private function setSpeseAccessorie(){
        $this->a_speseaccessorie = array();

        $a_totals = array( array("Id"=>1,"Name"=>"Totale 1"), array("Id"=>2,"Name"=>"Totale 2"), array("Id"=>3,"Name"=>"Totale 3") );

        for($i=1;$i<=10;$i++){
            if(!($this->a_pignoramento['Rimborso_'.$i]>0))
                continue;

            $a_selectTotale = array("value" => "Id", "firstOpt" => 1, 
            "selected" => (int)$this->a_pignoramento['Tipo_Totale_'.$i], "text" => array("[Name]"));
            $a_selectSpesa = array("value" => "ID", "firstOpt" => 1, 
            "selected" => (int)$this->a_pignoramento['Spesa_'.$i."_ID"], "text" => array("[Descrizione]"," ","[Deposito_Portata]"));

            if($this->a_pignoramento['Spesa_'.$i."_ID"]>0 && !empty($this->a_pignoramento['Tipo_Totale_'.$i])){
                $a_tariffe = $this->a_tariffe;
                if(empty($this->a_tariffe[$this->a_pignoramento['Spesa_'.$i."_ID"]]))
                    $a_tariffe[$this->a_pignoramento['Spesa_'.$i."_ID"]] = $this->addTariff($this->a_pignoramento['Spesa_'.$i."_ID"]);

                $this->a_speseaccessorie[$i] = array(
                    "Tipo_Totale" => (int)$this->a_pignoramento['Tipo_Totale_'.$i],
                    "Tipo_Spesa" => $this->a_pignoramento['Tipo_Spesa_'.$i],
                    "Spesa_ID" => (int)$this->a_pignoramento['Spesa_'.$i."_ID"],
                    "Rimborso" => (float)$this->a_pignoramento['Rimborso_'.$i],
                    "Extra_Spesa" => (float)$this->a_pignoramento['Extra_Spesa_'.$i],
                    "optTariffa" =>$this->html->getOptions($a_tariffe,$a_selectSpesa),
                    "optTipoTotale" =>$this->html->getOptions($a_totals,$a_selectTotale)
                );

                $tariffa = $a_tariffe[$this->a_pignoramento['Spesa_'.$i."_ID"]];

                if($tariffa['Tipo']=="UNA TANTUM"){
                    $this->a_speseaccessorie[$i]['Importo_Base'] = $tariffa['Importo'];
                    $this->a_speseaccessorie[$i]['Giorni_Base'] = null;
                    $this->a_speseaccessorie[$i]['Importo_Extra'] = null;
                }
                else{
                    $this->a_speseaccessorie[$i]['Importo_Base'] = $tariffa['Importo_Fisso'];
                    $this->a_speseaccessorie[$i]['Giorni_Base'] = $tariffa['Km_Giorni_Importo_Fisso'];
                    $this->a_speseaccessorie[$i]['Importo_Extra'] = $tariffa['Importo'];
                }
            }
            else{
                $this->a_speseaccessorie[$i] = array(
                    "Tipo_Totale" => 0,
                    "Tipo_Spesa" => null,
                    "Spesa_ID" => 0,
                    "Rimborso" => 0,
                    "Extra_Spesa" => 0,
                    "optTariffa" =>null,
                    "optTipoTotale" =>null
                );
                $this->a_speseaccessorie[$i]['Importo_Base'] = null;
                $this->a_speseaccessorie[$i]['Giorni_Base'] = null;
                $this->a_speseaccessorie[$i]['Importo_Extra'] = null;
            } 
        }

        $a_render['spese'] = $this->a_speseaccessorie;
        $this->addRenderParams($a_render);
    }

    /**
     * * NOTIFICHE PIGNORAMENTO
     */
    private function getNotifiche(){

        $query = "SELECT NA.*, F.PrinterId, F.PrintTypeId, EL.NotificationType, IFNULL(EL.PrintDate,NA.Data_Stampa) AS PrintDate, F.CreationDate, ".
        "F.Id AS FlowId, F.CreationDate AS FlowDate, F.Number AS FlowNumber, F.Year AS FlowYear, F.FileName AS FlowFilename, F.UploadDate, ".
        "NI.Immagine_Fronte, NI.Immagine_Retro, NI.CAD_Fronte, NI.CAD_Retro ".
        "FROM notifica_atto NA LEFT JOIN elaboration_lists EL ON EL.ID=NA.Elaboration_List_Id ".
        "JOIN pignoramento_generale PG ON PG.ID=NA.Atto_Notificato_ID ".
        "LEFT JOIN pignoramento_presso_terzi PPT ON PPT.Pignoramento_ID = NA.Atto_Notificato_ID ".
        "LEFT JOIN flows F ON F.Id=EL.FlowId OR F.Id=PG.FlowId ".
        "LEFT JOIN notifiche_importate NI ON NI.DocumentId=PG.ID AND NI.DocumentTypeId=PG.DocumentTypeId ".
        "WHERE Atto_Notificato_ID = '".$this->Pignoramento_ID."' ". 
        "AND Tipo_Atto_Notificato = 'pignoramento' GROUP BY NA.ID ".
        "ORDER BY CASE WHEN NA.Tipo_Notifica = 'debitore' THEN 1 ELSE 2 END, NA.Tipo_Notifica ASC, NA.ID_Collegamento ASC";

        $this->a_notifiche = $this->getRows($query);
    }

    private function setNotifiche(){
        
        $this->getNotifiche();

        $a_printTypes = $this->getRows("SELECT * FROM print_type","Modalita_Stampa");
        $a_printers = $this->getRows("SELECT *  FROM printer","Id");
        $a_notificationTypes = $this->getRows("SELECT *  FROM notification_types","Name");
        $a_modalitaNot = $this->getRows("SELECT *  FROM parametri_notifica WHERE Tipo_Dato='modalita'");
        $a_statoNot = $this->getRows("SELECT *  FROM parametri_notifica WHERE Tipo_Dato='stato'");
        $a_motivoNot = $this->getRows("SELECT *  FROM parametri_notifica WHERE Tipo_Dato='motivo'");

        $countTerzi = 0;
        foreach($this->a_notifiche as $key=>$notifica){

            $notifica = $this->setOldNotification($notifica, $a_printTypes);
            if($notifica['Tipo_Notifica']=="terzo" || $notifica['Tipo_Notifica']=="terzi")
                $notifica = $this->setDocsNotifica($notifica,$countTerzi++);
            else
                $notifica = $this->setDocsNotifica($notifica);
                
            $notifica = $this->setDestinatario($notifica);

            $a_selection = array("value" => "Id", "firstOpt" => 1, "selected" => $notifica['PrintTypeId'], "text" => array("[Description]"));
            $notifica['optPrintTypes'] = $this->html->getOptions($a_printTypes, $a_selection);

            $a_selection = array("value" => "Id", "firstOpt" => 1, "selected" => $notifica['PrinterId'], "text" => array("[Name]"));
            $notifica['optPrinters'] = $this->html->getOptions($a_printers, $a_selection);

            $a_selection = array("value" => "Name", "firstOpt" => 1, "selected" => $notifica['NotificationType'], "text" => array("[Description]"));
            $notifica['optNotificationTypes'] = $this->html->getOptions($a_notificationTypes, $a_selection);

            $a_selection = array("value" => "ID", "firstOpt" => 1, "selected" => $notifica['Modalita_Notifica'], "text" => array("[Descrizione]"));
            $notifica['optModalitaNot'] = $this->html->getOptions($a_modalitaNot, $a_selection);

            $a_selection = array("value" => "ID", "firstOpt" => 1, "selected" => $notifica['Stato_Notifica'], "text" => array("[Descrizione]"));
            $notifica['optGiacenzaNot'] = $this->html->getOptions($a_statoNot, $a_selection);

            $a_selection = array("value" => "ID", "firstOpt" => 1, "selected" => $notifica['Motivo_Notifica'], "text" => array("[Descrizione]"));
            $notifica['optAnomaliaNot'] = $this->html->getOptions($a_motivoNot, $a_selection);

            $this->a_notifiche[$key] = $notifica;
        }

        $a_render['notifiche'] = $this->a_notifiche;
        $a_render['originale'] = $this->setDocsOriginale();
        $this->addRenderParams($a_render);
    }

    private function setOldNotification($notifica, $a_printTypes){
        if(empty($notifica['Elaboration_List_Id'])){
            if(empty($notifica['PrintTypeId']))
                $notifica['PrintTypeId'] = $a_printTypes[$notifica['Modalita_Stampa']]['Id'];
            if(empty($notifica['PrinterId'])){
                if(in_array($notifica['PrintTypeId'], array(4,6)))
                    $notifica['PrinterId'] = 1;
                else    
                    $notifica['PrinterId'] = $this->a_pignoramento['PrinterId'];
            }
            if(empty($notifica['NotificationType'])){
                if(in_array($notifica['PrintTypeId'], array(4)))
                    $notifica['NotificationType'] = "diretta";
                else    
                    $notifica['NotificationType'] = $this->a_pignoramento['Tipo_Ufficiale'];
            }
            
            if($notifica['Tipo_Notifica']=="debitore"){
                if(empty($notifica['PrintDate']))
                    $notifica['PrintDate'] = $this->a_pignoramento['Data_Stampa'];

                if(empty($notifica['FlowId']) && !empty($this->a_pignoramento['FlowId'])){
                    $a_flow = $this->getRow("SELECT * FROM flows WHERE Id=".$this->a_pignoramento['FlowId']);
                    $notifica['FlowId'] = $this->a_pignoramento['FlowId'];
                    $notifica['FlowNumber'] = $a_flow['Number'];
                    $notifica['FlowYear'] = $a_flow['Year'];
                    $notifica['FlowDate'] = $a_flow['CreationDate'];
                    $notifica['FlowUploadDate'] = $a_flow['UploadDate'];
                }
                
            }
                
        }
        
        return $notifica;
    }

    private function setDestinatario($notifica){
        $notifica['Destinatario'] = "";

        if($notifica['Tipo_Notifica']=="debitore"){
            $notifica['Destinatario'] = $this->a_pignoramento['Denominazione_Debitore'];
            $notifica['Tipo_Destinatario'] = "DEBITORE";
        }
            
        switch($this->a_pignoramento['DocumentTypeId']){
            case 7:
                if(!empty($notifica['ID_Collegamento'])){
                    $notifica['Destinatario'] = $this->a_datori_lavoro[$notifica['ID_Collegamento']]['Denominazione_Terzo'];
                    $notifica['Tipo_Destinatario'] = "DATORE DI LAVORO";
                }
                    
                break;
            case 8:
                if(!empty($notifica['ID_Collegamento'])){
                    $notifica['Destinatario'] = $this->a_banche[$notifica['ID_Collegamento']]['Denominazione_Terzo'];
                    $notifica['Tipo_Destinatario'] = "BANCA";
                }
                    

                break;
            case 6:
                if($notifica['Tipo_Notifica']=="veicolo"){
                    $notifica['Tipo_Destinatario'] = "IVG";
                    $notifica['Destinatario'] = strtoupper($this->a_IVG['Comune']);
                }
                    
                
                break;
        }
        return $notifica;
    }

    public function getUtente(){

    }

    public function getBanca(){

    }

    /**
     * * FILE PATHS
     */

    public function getPdfName($notificaId=null, $suffix="Copia_debitore", bool $firmaDigitale = false){
        if($firmaDigitale===true)
            $signed = "_signed";
        else
            $signed = "";
        if(!empty($notificaId))
            $notificaId.="_";

        return $this->a_pignoramento['PrefixName']."_".
        $this->a_pignoramento['CC']."_".
        $this->a_pignoramento['Anno_Cronologico']."_".
        $this->a_pignoramento['ID_Cronologico']."_".
        $notificaId
        .$suffix.
        $signed.".pdf";
    }

    public function getPdfRoot($type="ROOT"){
        if($type=="ROOT")
            $path = PIGNORAMENTI;
        else if($type=="WEB")
            $path = PIGNORAMENTI_WEB;
        return $path."/".$this->Pignoramento_ID;
    }

    public function getOldPdfName($suffix="copia_debitore",$IdNotifica=null){
        $suffix = strtolower($suffix);
        $file = "";
        switch($this->a_pignoramento['DocumentTypeId']){
            case 6:
                $file.= "Pignoramento_veicolo";
                break;
            case 7:
                $file.= "Pignoramento_presso_lavoro";
                break;
            case 8:
                $file.= "Pignoramento_presso_banca";
                break;
        }

        $file.= "_".$this->a_pignoramento['CC']."_".
        $this->a_pignoramento['Anno_Cronologico']."_".
        $this->a_pignoramento['ID_Cronologico']."_".
        $this->a_pignoramento['Data_Stampa']."_"
        .$suffix;
        
        if(!is_null($IdNotifica))
            $file.= "_".$IdNotifica;

        if(substr($suffix,0,3)=="rel" && $suffix!="rel_originale")
            $file.= "_0";

        return $file.".pdf";
        
    }

    public function getOldFileRoot($type = "ROOT", $dir = "STAMPE DEFINITIVE"){
        if($type=="ROOT")
            $path = ATTI;
        else if($type=="WEB")
            $path = ATTI_WEB;
        $path.= "/".$this->a_pignoramento['CC']."/Pignoramenti";
        switch($this->a_pignoramento['DocumentTypeId']){
            case 6:
                return $path."/Veicolo/".$dir;
                break;
            case 7:
                return $path."/Presso_Terzi/Datore_di_Lavoro/".$dir;
                break;
            case 8:
                return $path."/Presso_Terzi/Banca/".$dir;
                break;

            default:
                return $path;
        }
        
    }

    public function getFlowPaths($flowId, $filename){
        if(is_file(FLUSSI."/".$flowId."/".$filename)){
            return array(
                "root"=>FLUSSI."/".$flowId."/".$filename, 
                "webRoot"=>FLUSSI_WEB."/".$flowId."/".$filename
            );
        }
        else{

            // if(is_file($this->getOldFileRoot("ROOT","FLUSSI")."/".$filename)){
                return array(
                    "root"=>$this->getOldFileRoot("ROOT","FLUSSI")."/".$filename, 
                    "webRoot"=>$this->getOldFileRoot("WEB","FLUSSI")."/".$filename
                );
            // }

            return array("root"=>false, "webRoot"=>false);
        }
            

    }

    private function setFilePaths($tipoFile, $tipoNot, $IdNotifica=null, $signed=false, $oldTerzoCounter=null){
        if($tipoNot=="terzi")
            $tipoNot = "terzo";

        if($tipoFile=="copia" && $tipoNot=="originale")
            $suffix = ucfirst($tipoNot);
        else
            $suffix = ucfirst($tipoFile)."_".$tipoNot;

        if(is_file($this->getPdfRoot()."/".$this->getPdfName($IdNotifica,$suffix,$signed)))
            return array(
                "root"=>$this->getPdfRoot()."/".$this->getPdfName($IdNotifica,$suffix,$signed), 
                "webRoot"=>$this->getPdfRoot("WEB")."/".$this->getPdfName($IdNotifica,$suffix,$signed)
            );
        else{
            if($signed===true)
                return array("root"=>false, "webRoot"=>false);

            if($tipoFile=="copia" && $tipoNot=="veicolo")
                $suffix = "copia_istituto";
            else if($tipoFile=="relata" && $tipoNot=="veicolo")
                $suffix = "relata_istituto";

            $a_suffix = explode("_",$suffix);
            if(strtolower($a_suffix[0])=="relata")
                $a_suffix[0] = "rel";
            $suffix = implode("_",$a_suffix);
                
            if(is_file($this->getOldFileRoot()."/".$this->getOldPdfName($suffix,$oldTerzoCounter)))
                return array(
                    "root"=>$this->getOldFileRoot()."/".$this->getOldPdfName($suffix,$oldTerzoCounter), 
                    "webRoot"=>$this->getOldFileRoot("WEB")."/".$this->getOldPdfName($suffix,$oldTerzoCounter)
                );
            else
                return array("root"=>false, "webRoot"=>false);
        }
    }

    private function setDocsNotifica($notifica, $oldTerzoCounter = null){
        $notifica['pdf']['copia'] = $this->setFilePaths("copia", $notifica['Tipo_Notifica'], $notifica['ID'],false, $oldTerzoCounter);
        $notifica['pdf']['checkrelata'] = true;
        switch($this->a_pignoramento['DocumentTypeId']){
            case 22:
                if($notifica['NotificationType'] == "diretta" || $notifica['PrinterId']==1)
                    $notifica['pdf']['checkrelata'] = false;
                break;
            default:
                $notifica['pdf']['relata'] = $this->setFilePaths("relata", $notifica['Tipo_Notifica'], $notifica['ID'], false, $oldTerzoCounter);
                $notifica['pdf']['signedrelata'] = $this->setFilePaths("relata", $notifica['Tipo_Notifica'], $notifica['ID'],true);
        }

        $notifica['flowzip'] = $this->getFlowPaths($notifica['FlowId'],$notifica['FlowFilename']);

        return $notifica;
    }

    private function setDocsOriginale(){
        switch($this->a_pignoramento['DocumentTypeId']){
            case 22:
                $file['check'] = false;
                break;
            default:
                $file['check'] = true;
        }

        $file['pdf'] = $this->setFilePaths("copia","originale");
        $file['pdfrelata'] = $this->setFilePaths("relata","originale");

        return $file;
    }

    /**
     * * IVG
     */
    private function getIVG($params){
        $query = "SELECT IVG.*, FG.Sigla AS Sigla_Forma_Giuridica FROM ufficio_giudiziario IVG JOIN ufficio_giudiziario TRIB ON IVG.CC=TRIB.CC_Ufficio ".
        " LEFT JOIN forma_giuridica_societa FG ON FG.ID=IVG.Forma_Giuridica ".
        "WHERE TRIB.CC = '" . $params['CC'] . "' AND TRIB.Tipo = 'tribunale' AND IVG.Tipo='istituto' LIMIT 1";

        $this->a_IVG = $this->getRow($query);
    }

    private function setIVG($params){
        $this->getIVG($params);
        $a_render['ivg'] = $this->a_IVG;
        $this->addRenderParams($a_render);
    }


    /**
     * * VEICOLO PIGNORAMENTO
     */
    public function getVeicolo(){
        $query = "SELECT * FROM pignoramento_veicolo WHERE Pignoramento_ID = ".$this->Pignoramento_ID;
        $this->a_veicolo = $this->getRow($query);
    }

    private function setVeicolo(){
        $this->getVeicolo();
        $a_render['veicolo'] = $this->a_veicolo;

        //* OPZIONI TIPOLOGIE VEICOLO
        $query = "SELECT DISTINCT LOWER(SerieTarga) AS Tipo_Value, UPPER(SerieTarga) AS Tipo_Text  FROM veicoli ORDER BY SerieTarga ASC";
        $a_selection = array("value" => "Tipo_Value", "firstOpt" => 0, "selected" => $this->a_veicolo['Tipo_Veicolo'], "text" => array("[Tipo_Text]"));
        $a_render['veicolo']['optTipoVeicolo'] = $this->html->getOptions($this->getRows($query), $a_selection);

        //* OPZIONI FONTE DATI VISURA
        $a_totals = array( array("Id"=>"pra","Name"=>"ACI/PRA"), array("Id"=>"mctc","Name"=>"MCTC") );
        $a_selection = array("value" => "Id", "firstOpt" => 1, "selected" => $this->a_veicolo['Fonte_Dati'], "text" => array("[Name]"));
        $a_render['veicolo']['optFonteDati'] = $this->html->getOptions($a_totals, $a_selection);

        $this->addRenderParams($a_render);
    }

    /**
    * * BANCHE PIGNORAMENTO
    */
    public function getBanche(){
        $query = "SELECT PPT.*, concat(B.Denominazione) as Denominazione_Terzo FROM pignoramento_presso_terzi PPT ".
        "LEFT JOIN banca B ON B.ID=PPT.Terzo_ID WHERE PPT.Pignoramento_ID = ".$this->Pignoramento_ID." ORDER BY ID";
        $this->a_banche = $this->getRows($query, "ID");
    }

    private function setBanche(){
        $this->getBanche();
        $a_render['pignoramenti_banca'] = $this->a_banche;

        $this->addRenderParams($a_render);
    }

    /**
     * * DATORI LAVORO PIGNORAMENTO
     */
    public function getDatoriLavoro(){
        $query = "SELECT PPT.*, concat(U.Cognome,U.Ditta,' ',U.Nome) as Denominazione_Terzo FROM pignoramento_presso_terzi PPT ".
        "LEFT JOIN utente U ON U.ID=PPT.Terzo_ID WHERE PPT.Pignoramento_ID = ".$this->Pignoramento_ID." ORDER BY ID";
        $this->a_datori_lavoro = $this->getRows($query, "ID");
    }

    private function setDatoriLavoro(){
        $this->getDatoriLavoro();
        $a_render['pignoramenti_datore_lavoro'] = $this->a_datori_lavoro;
        $query = "SELECT * FROM contratti_lavoro";
        $a_contracts = $this->getRows($query);
        foreach($this->a_datori_lavoro as $pignoId=>$datoreLavoro){
            //* OPZIONI TIPOLOGIE CONTRATTO
            //TODO AGGIUNGI TUTTI I CONTRATTI
            
            $a_selection = array("value" => "Name", "firstOpt" => 0, "selected" => $datoreLavoro['Tipo_Contratto_Lavoro'], "text" => array("[Description]"));
            $a_render['pignoramenti_datore_lavoro'][$pignoId]['optContratti'] = $this->html->getOptions($a_contracts, $a_selection);
        }

        $this->addRenderParams($a_render);
    }


    /**
     * * TARIFFE COAZIONE
     */
    public function getTariffeCoazione($params){
        
        $query = "SELECT * FROM tariffe_coazione ".
        "WHERE CC='".$params['CC']."' AND ".$params['DocumentTypeId']." MEMBER OF(DefaultJSON->>'$.DocumentList') ".
        "ORDER BY Tipo ASC, Descrizione ASC";

        //TODO PROBLEMA CARICAMENTO VECCHIE TARIFFE NON PIU' ASSOCIATE AL TIPO DI PIGNORAMENTO
        
        $this->a_tariffe = $this->getRows($query,"ID");
    }

    private function setTariffeCoazione($params){
        $this->getTariffeCoazione($params);
        
        $a_render['tariffe'] = $this->a_tariffe;
        $this->addRenderParams($a_render);
    }

    private function addTariff($id){
        $query = "SELECT * FROM tariffe_coazione WHERE ID=".$id;
        return $this->getRow($query);
    }



}