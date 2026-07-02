<?php

include_once(CLS."/cls_db.php");
include_once(CLS."/cls_help.php");
include_once(CLS."/cls_Utils.php");
include_once(CLS."/cls_DateTimeInLine.php");
include_once(CLS."/cls_LOG.php");


class BuildMotivationText
{
    private $Log;
    private $Isdebug;

    private $atti;
    private $pigno;

    private $CF;
    private $PI;
    private $via_id;
    private $via_cap_id;
    private $presso;
    private $data_morte;
    private $entrata;

    private $Partita_ID;
    private $Sgravio_ID;
    private $flagPignoAtto;

    private $placeholderTitlePigno;
    private $placeholderTitleAtto;

    private $flagFirma;

    private $arrayDocIdDefault;
    private $Text;
    private $flagIsSet;
    private $PrintHtml;
    private $saveObjectData;

    private $cls_db;
    private $cls_date;
    private $cls_utils;

    private $CountGlobal;

    private $a_docSort;
    private $a_htmlSort;
    private $motivazioniArr;

    public function __construct($partita_ID,$debug = false,$flagSave = false, $sgravio_ID=null, $showSgravioWarning = true)
    {
        $this->Log = new LOG();

        try {

            if(!isset($partita_ID))
                throw new Exception("Partita non inserita");
            if(!is_numeric($partita_ID))
                throw new Exception("Partita inserita errata, non è numerica");

            $this->cls_db = new cls_db();
            $this->cls_date = new cls_DateTimeI("IT",false);
            $this->cls_utils = new cls_Utils();

            $query = "SELECT DocumentTypeId, Text FROM motivazioni_sgravio";
            $TextDefault = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

            $query = "SELECT DocumentTypeId, Text FROM sgravi_documenti WHERE Partita_ID = ".$partita_ID;
            $TextSet = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

            $this->setArraySorting();

            $this->flagIsSet = false;
            $this->PrintHtml = array();
            $this->saveObjectData = array();
            $this->motivazioniArr = array();
            $this->Isdebug = $debug;
            $this->CountGlobal = 0;
            $this->placeholderTitlePigno = array("T2","T3","T4","T5","T6","T7","F1");
            $this->placeholderTitleAtto = array("T1","F1");
            $this->flagSave = $flagSave;
            $this->Partita_ID = $partita_ID;
            $this->Sgravio_ID = $sgravio_ID;
            $this->flagFirma = false;

            if(count($TextSet) == 0) {
                if($flagSave == false && $showSgravioWarning) {
                    echo '<div class="alert-danger" style="display: table; height: 50px;width:80%;margin-left:10%;border-radius: 12px;">
                              <div style="display: table-cell; vertical-align: middle;">
                                <div  style="text-align: center;">Non è possibile creare l\'elenco excell/pdf se non si salva il Discarico!</div>
                              </div>
                         </div>
                         <script>$(document).ready(function(){
                            switchMenuImg("F4");
                            F4_button = function(){}
                         });</script>';
                }
                $this->Text = $TextDefault;
            }
            else {

                $this->flagPignoAtto = "all";
                $this->Text = $TextSet;

                $arrTempAlDoc = array();
                for($i=0;$i<count($TextSet);$i++)
                    $arrTempAlDoc[] = $TextSet[$i]["DocumentTypeId"];
                $this->arrayDocIdDefault = $arrTempAlDoc;
                $this->flagIsSet = true;

                $this->build(array());
            }

        }
        catch(Exception $ex)
        {
            $this->Log->error($ex->getMessage());
        }
    }

    private function setArraySorting(){
        $this->a_docSort = array(
            23=>1,  2=>2, 4=>3, 12=>4, 8=>5,
            7=>6, 24=>7, 14=>8, 13=>9, 22=>10,
            25=>11, 26=>12, 27=>13, 6=>14, 31=>15,
            32=>16, 33=>17, 34=>18, 35=>19, 36=>20,
            28=>21, 29=>22, 37=>23, 30=>24, 0=>25
        );

        $this->a_htmlSort = array(
            "T1" => array("Type"=>"Title", "Position"=>1, "Title"=>"INGIUNZIONE"),
            23 => array("Type"=>"Doc", "Position"=>2, "InputId"=>"accertamento", "Title"=>"ACCERTAMENTO"),
            2 => array("Type"=>"Doc", "Position"=>3, "InputId"=>"ingiunzione", "Title"=>"INGIUNZIONE"),
            4 => array("Type"=>"Doc", "Position"=>4, "InputId"=>"avv_int", "Title"=>"AVVISO INTIMAZIONE"),
            12 => array("Type"=>"Doc", "Position"=>5, "InputId"=>"avv_mess_mora", "Title"=>"AVVISO MESSA IN MORA"),
            "T2" => array("Type"=>"Title", "Position"=>6, "Title"=>"PRESSO TERZI"),
            8 => array("Type"=>"Doc", "Position"=>7, "InputId"=>"pigno_banca_posta", "Title"=>"PIGNORAMENTO PRESSO BANCA/POSTA"),
            7 => array("Type"=>"Doc", "Position"=>8, "InputId"=>"pigno_datore_lavoro", "Title"=>"PIGNORAMENTO PRESSO DATORE DI LAVORO"),
            24 => array("Type"=>"Doc", "Position"=>9, "InputId"=>"pigno_istit_prev", "Title"=>"PIGNORAMENTO PRESSO ISTITUTI PREVIDENZIALI"),
            14 => array("Type"=>"Doc", "Position"=>10, "InputId"=>"pigno_terzi", "Title"=>"PIGNORAMENTO ALTRI TERZI (art. 72L 602/73)"),
            13 => array("Type"=>"Doc", "Position"=>11, "InputId"=>"pigno_inps", "Title"=>"PIGNORAMENTO PRESSO INPS"),
            "T3" => array("Type"=>"Title", "Position"=>12, "Title"=>"BENI MOBILI REGISTRATI - AUTO"),
            22 => array("Type"=>"Doc", "Position"=>13, "InputId"=>"preav_fermo", "Title"=>"PREAVVISO DI FERMO AMMINISTRATIVO"),
            25 => array("Type"=>"Doc", "Position"=>14, "InputId"=>"fermo", "Title"=>"FERMO AMMINISTRATIVO"),
            26 => array("Type"=>"Doc", "Position"=>15, "InputId"=>"preav_iscr_ip", "Title"=>"PREAVVISO DI ISCRIZIONE DI IPOTECA"),
            27 => array("Type"=>"Doc", "Position"=>16, "InputId"=>"ipoteca", "Title"=>"IPOTECA"),
            6 => array("Type"=>"Doc", "Position"=>17, "InputId"=>"pigno_beni_mob_reg", "Title"=>"PIGNORAMENTO AMMINISTRATIVO DEL VEICOLO (art. 513 C.p.C e ss.)"),
            "T4" => array("Type"=>"Title", "Position"=>18, "Title"=>"NATANTI (Beni registrati - art.643 e ss. del C.p.C.)"),
            31 => array("Type"=>"Doc", "Position"=>19, "InputId"=>"pigno_nat", "Title"=>"PIGNORAMENTO NATANTI"),
            32 => array("Type"=>"Doc", "Position"=>20, "InputId"=>"seq_cons_nat", "Title"=>"SEQUESTRO CONSERVATIVO NATANTI"),
            33 => array("Type"=>"Doc", "Position"=>21, "InputId"=>"ipot_nat", "Title"=>"IPOTECA NATANTI"),
            "T5" => array("Type"=>"Title", "Position"=>22, "Title"=>"AEREOMOBILI (Beni registrati - art. 1061 e segg. del Codice di Navigazione)"),
            34 => array("Type"=>"Doc", "Position"=>23, "InputId"=>"pigno_aero", "Title"=>"PIGNORAMENTO AEROMOBILI"),
            35 => array("Type"=>"Doc", "Position"=>24, "InputId"=>"seq_cons_aero", "Title"=>"SEQUESTRO CONSERVATIVO AEROMOBILI"),
            36 => array("Type"=>"Doc", "Position"=>25, "InputId"=>"ipot_aero", "Title"=>"IPOTECA AEROMOBILI"),
            "T6" => array("Type"=>"Title", "Position"=>26, "Title"=>"IMMOBILIARE"),
            28 => array("Type"=>"Doc", "Position"=>27, "InputId"=>"ipot_immob", "Title"=>"IPOTECA IMMOBILIARE"),
            29 => array("Type"=>"Doc", "Position"=>28, "InputId"=>"pigno_immob", "Title"=>"PIGNORAMENTO IMMOBILIARE"),
            "T7" => array("Type"=>"Title", "Position"=>29, "Title"=>"MOBILIARE"),
            37 => array("Type"=>"Doc", "Position"=>30, "InputId"=>"pigno_mob", "Title"=>"PIGNORAMENTO MOBILIARE (art. 518 C.p.C. e ss.)"),
            30 => array("Type"=>"Doc", "Position"=>31, "InputId"=>"pegno_mob", "Title"=>"PEGNO MOBILIARE (art. 2787 C.p.C.)"),
            "F1" => array("Type"=>"Firma", "Position"=>32, "Title"=>"FIRMA"),
            0 => array("Type"=>"Doc", "Position"=>33, "InputId"=>"firma_UR", "Title"=>"Firma (L'ufficiale della riscossione)"),
        );
    }

    public function SetAtto(array $atti){

        try {



            $this->flagPignoAtto = "atto";

            if(count($atti)>0)
            {
                if(!array_key_exists("Info_Cartella", $atti[0])) throw new Exception("Campo Info_Cartella non presente nell'array");
                else if(!array_key_exists("ID_Cronologico", $atti[0])) throw new Exception("Campo ID_Cronologico non presente nell'array");
                else if(!array_key_exists("Anno_Cronologico", $atti[0])) throw new Exception("Campo Anno_Cronologico non presente nell'array");
                else if(!array_key_exists("Data_Notifica", $atti[0])) throw new Exception("Campo Data_Notifica non presente nell'array");
                else if(!array_key_exists("DescrizioneModalitaNotifica", $atti[0])) throw new Exception("Campo DescrizioneModalitaNotifica non presente nell'array");
                else if(!array_key_exists("DocumentTypeId", $atti[0])) throw new Exception("Campo DocumentTypeId non presente nell'array");
                else if(!array_key_exists("CC", $atti[0])) throw new Exception("Campo CC non presente nell'array");

                if($atti[0]["Info_Cartella"] == null && $atti[0]["ID_Cronologico"] == null && $atti[0]["Anno_Cronologico"] == null && $atti[0]["Data_Notifica"] == null && $atti[0]["DescrizioneModalitaNotifica"] == null && $atti[0]["DocumentTypeId"] == null && $atti[0]["CC"] == null){
                    $atti = array();
                }
            }

            $this->atti = $atti;
            //var_dump($atti);
            if(!$this->flagIsSet) {
                //$this->PrintHtml = array();
                $arrDocIdAtto = array(23, 2, 4, 12, 0);
                //if (count($this->atti) > 0) $this->arrayDocIdDefault = $this->deleteElementArray($arrDocIdAtto, 23);
                //else $this->arrayDocIdDefault = $arrDocIdAtto;
                $this->arrayDocIdDefault = $arrDocIdAtto;

                $this->CF = isset($atti[0]["Codice_Fiscale"])?$atti[0]["Codice_Fiscale"]:null;
                $this->PI = isset($atti[0]["Partita_Iva"])?$atti[0]["Partita_Iva"]:null;
                $this->via_id = isset($atti[0]["Via_ID"])?$atti[0]["Via_ID"]:null;
                $this->via_cap_id = isset($atti[0]["Via_Cap_ID"])?$atti[0]["Via_Cap_ID"]:null;
                $this->presso = isset($atti[0]["Presso"])?$atti[0]["Presso"]:null;
                $this->data_morte = isset($atti[0]["Data_Morte"])?$atti[0]["Data_Morte"]:null;
                $this->entrata = isset($atti[0]["Entrata"])?$atti[0]["Entrata"]:null;


                if(!$this->flagSave) $this->build($atti);
                else $this->saveDb($atti);
            }

            if(!is_array($this->pigno)){
                throw new Exception("Caricare prima i pignoramenti");
            }

        }
        catch(Exception $ex)
        {
            $this->Log->error($ex->getMessage());
            if($this->Isdebug == true)
                echo "<div class='alert-danger' style='width:80%;margin-left:10%;text-align: center;'>".$ex->getMessage()." - file ".$ex->getFile()." - riga".$ex->getLine()."</div>";
        }
    }

    public function SetPigno(array $pigno){

        try {

            $this->flagPignoAtto = "pigno";

            if(count($pigno)>0)
            {
                if(!array_key_exists("ID_Cronologico", $pigno[0])) throw new Exception("Campo ID_Cronologico non presente nell'array");
                else if(!array_key_exists("Anno_Cronologico", $pigno[0])) throw new Exception("Campo Anno_Cronologico non presente nell'array");
                else if(!array_key_exists("Data_Notifica", $pigno[0])) throw new Exception("Campo Data_Notifica non presente nell'array");
                else if(!array_key_exists("DescrizioneModalitaNotifica", $pigno[0])) throw new Exception("Campo DescrizioneModalitaNotifica non presente nell'array");
                else if(!array_key_exists("DocumentTypeId", $pigno[0])) throw new Exception("Campo DocumentTypeId non presente nell'array");
                else if(!array_key_exists("DescrizioneMotivoNotifica", $pigno[0])) throw new Exception("Campo DescrizioneMotivoNotifica non presente nell'array");
                else if(!array_key_exists("DescrizioneStatoNotifica", $pigno[0])) throw new Exception("Campo DescrizioneStatoNotifica non presente nell'array");

                if($pigno[0]["ID_Cronologico"] == null && $pigno[0]["Anno_Cronologico"] == null && $pigno[0]["Data_Notifica"] == null && $pigno[0]["DescrizioneModalitaNotifica"] == null && $pigno[0]["DocumentTypeId"] == null && $pigno[0]["DescrizioneMotivoNotifica"] == null && $pigno[0]["DescrizioneStatoNotifica"] == null){
                    $pigno = array();
                }
            }

            $this->pigno = $pigno;

            if(!$this->flagIsSet) {
                //$this->PrintHtml = array();
                $arrDocIdPigno = array(8, 7, 24, 14, 13, 22, 25, 26, 27, 6, 28, 29, 37, 30, 31, 32, 33, 34, 35, 36);
                $this->arrayDocIdDefault = $arrDocIdPigno;

                if(!$this->flagSave) $this->build($pigno);
                else $this->saveDb($pigno);
            }

        }
        catch(Exception $ex)
        {
            $this->Log->error($ex->getMessage());
            if($this->Isdebug == true)
                echo "<div class='alert-danger' style='width:80%;margin-left:10%;text-align: center;'>".$ex->getMessage()." - file ".$ex->getFile()." - riga".$ex->getLine()."</div>";
        }
    }

    private function build($atti){

        try {
            if(!$this->flagIsSet) {
                $last_act = false;
                for ($i = 0; $i < count($atti); $i++) {

                    $docTypeId = (int)$atti[$i]["DocumentTypeId"];
                    if (isset($this->a_htmlSort[$docTypeId])) {
                        if ($docTypeId==12 && $this->flagPignoAtto == "atto") {
                            if ($atti[$i]["Tipo"] != "PATRIMONIALE" && $atti[$i]["Tipo"] != "RIFIUTI"){
                                $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault, $docTypeId);
                                continue;
                            }
                        }

                        if($i == count($atti) - 1)
                            $last_act = true;

                        $BuildText = $this->buildText($this->flagIsSet, $docTypeId, $this->Text, $atti[$i],$last_act);
                        $this->PrintHtml[] = $this->printTextArea($BuildText, $docTypeId, $atti[$i]['ID']);
                        $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault, $docTypeId);
                    }
                    else {
                        if ($docTypeId == 3 || $docTypeId == 11)
                            continue;
                        else
                            throw new Exception("DocumentTypeId = " . $docTypeId . " non presente nello switch della classe BuildMotivationText della partita ".$this->Partita_ID);
                    }
                }

                if($this->flagPignoAtto=="atto" && count($atti) > 0) {
                    $query = "SELECT * FROM parametri_responsabili WHERE CC = '" . $atti[0]["CC"] . "' AND Tipo_Riscossione = '" . $atti[0]["Tipo"] . "'";
                    $result = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"parametri_responsabili");
                    $BuildText = "L'ufficiale della riscossione F.to ". $result["Ufficiale_Riscossione"];
                    $this->PrintHtml[] = $this->printTextArea($BuildText, 0);
                    $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault, 0);
                }
            }

            if($this->flagPignoAtto=="atto")
                $this->arrayDocIdDefault = array_merge($this->arrayDocIdDefault, $this->placeholderTitleAtto);
            else if($this->flagPignoAtto=="pigno")
                $this->arrayDocIdDefault = array_merge($this->arrayDocIdDefault, $this->placeholderTitlePigno);
            else if($this->flagPignoAtto=="all")
                $this->arrayDocIdDefault = array_merge($this->arrayDocIdDefault, $this->placeholderTitlePigno, $this->placeholderTitleAtto);

            for($i=0;$i<count($this->arrayDocIdDefault);$i++) {
                $docTypeId = $this->arrayDocIdDefault[$i];
                if (isset($this->a_htmlSort[$docTypeId])) {

                    switch ($this->a_htmlSort[$docTypeId]['Type']) {
                        case "Doc":
                            $BuildText = $this->buildText($this->flagIsSet, $docTypeId, $this->Text);
                            $this->PrintHtml[] = $this->printTextArea($BuildText, $docTypeId);
                            break;
                        case "Title":
                            $this->PrintHtml[] = $this->printTytle($docTypeId);
                            break;
                        case "Firma":
                            if (!$this->flagFirma) {
                                $this->PrintHtml[] = $this->printTytle($docTypeId);
                                $this->flagFirma = true;
                            }
                            break;
                    }
                }
                else {
                    if ($docTypeId == 3 || $docTypeId == 11)
                        continue;
                    else
                        throw new Exception("DocumentTypeId = " . $docTypeId . " non presente nello switch della classe BuildMotivationText della partita " . $this->Partita_ID);
                }
            }
        }
        catch(Exception $ex)
        {
            $this->Log->error($ex->getMessage());
            if($this->Isdebug == true)
                echo "<div class='alert-danger' style='width:80%;margin-left:10%;text-align: center;'>".$ex->getMessage()." - file ".$ex->getFile()." - riga".$ex->getLine()."</div>";
        }
    }

    private function saveDb($atti){

        try {
            if($this->flagIsSet){
                $this->Log->error("La partita ".$this->Partita_ID." ha già l'elenco dello sgravio salvato!");
                return true;
            }

            $last_act = false;
            for ($i = 0; $i < count($atti); $i++) {
                if(!empty($this->a_docSort[$atti[$i]["DocumentTypeId"]])){
                    if ($atti[$i]["DocumentTypeId"]==12 && $this->flagPignoAtto == "atto") {
                        if ($atti[$i]["Tipo"] != "PATRIMONIALE" && $atti[$i]["Tipo"] != "RIFIUTI"){
                            $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault, 12);
                            continue;
                        }
                    }


                    if($i == count($atti) - 1)
                        $last_act = true;

                    $BuildText = $this->buildText($this->flagIsSet, $atti[$i]["DocumentTypeId"], $this->Text,$atti[$i],$last_act);
                    $this->saveObjectData[] = $this->buildObjectQuery($this->Partita_ID, $BuildText, $atti[$i]["DocumentTypeId"], $atti[$i]['ID']);
                    $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault, (int)$atti[$i]["DocumentTypeId"]);
                }
                else{
                    if($atti[$i]["DocumentTypeId"]==3 || $atti[$i]["DocumentTypeId"]==11){
                        continue;
                    }
                    else
                        throw new Exception("DocumentTypeId = " . $atti[$i]["DocumentTypeId"] . " non presente nello switch della classe BuildMotivationText della partita ".$this->Partita_ID);
                }
            }

            if($this->flagPignoAtto=="atto" && !empty($atti[0]["CC"])) {
                $query = "SELECT * FROM parametri_responsabili WHERE CC = '" . $atti[0]["CC"] . "' AND Tipo_Riscossione = '" . $atti[0]["Tipo"] . "'";
                $result = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"parametri_responsabili");
                $BuildText = "L'ufficiale della riscossione F.to ". $result["Ufficiale_Riscossione"];
                $this->saveObjectData[] = $this->buildObjectQuery($this->Partita_ID, $BuildText, 0);
                $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault, 0);
            }

            for($i=0;$i<count($this->arrayDocIdDefault);$i++){
                if(isset($this->a_docSort[$this->arrayDocIdDefault[$i]])){
                    $BuildText = $this->buildText($this->flagIsSet, $this->arrayDocIdDefault[$i], $this->Text);
                    $this->saveObjectData[] = $this->buildObjectQuery($this->Partita_ID, $BuildText, $this->arrayDocIdDefault[$i]);
                }
                else{
                    if($this->arrayDocIdDefault[$i]==3 || $this->arrayDocIdDefault[$i]==11){
                        continue;
                    }
                    else
                        throw new Exception("DocumentTypeId = ".$this->arrayDocIdDefault[$i]." non presente nello switch della classe BuildMotivationText della partita ".$this->Partita_ID);
                }
            }
        }
        catch(Exception $ex)
        {
            $this->Log->error($ex->getMessage());
            if($this->Isdebug == true)
                echo "<div class='alert-danger' style='width:80%;margin-left:10%;text-align: center;'>".$ex->getMessage()." - file ".$ex->getFile()." - riga".$ex->getLine()."</div>";
        }
    }

    public function IsDebug(bool $value)
    {
        $this->Isdebug = $value;
    }

    public function Reset(){
        $this->PrintHtml = array();
    }

    private function buildObjectQuery($Partita_ID,$Text, $docType, $docId = null){

        $objQuery = new StdClass();

        try {
            $objQuery->position = $this->a_docSort[$docType];

            $save = new stdClass();
            $save->Sgravio_ID = $this->Sgravio_ID;
            $save->Partita_ID = $Partita_ID;
            $save->DocumentTypeId = $docType;
            $save->Text = $Text;
            $save->DocumentId = $docId;
            $objQuery->value = $this->cls_utils->GetObjectQuery($save,"sgravi_documenti");

            return $objQuery;
        }
        catch(Exception $ex)
        {
            $this->Log->error($ex->getMessage());
            if($this->Isdebug == true)
                echo "<div class='alert-danger' style='width:80%;margin-left:10%;text-align: center;'>".$ex->getMessage()." - file ".$ex->getFile()." - riga".$ex->getLine()."</div>";
        }
    }

    public function SaveAllOnDB(){

        $obj = $this->ordina($this->saveObjectData);
        try {
            $count = count($obj);
            $this->cls_db->Start_Transaction();
            $this->cls_db->Begin_Transaction();

            for($i=0 ; $i < $count ; $i++){
                if(!$this->cls_db->DbSave($obj[$i]->value)){
                    throw new Exception("Errore nel salvataggio della partita: ".$this->Partita_ID);
                }
            }

            $this->cls_db->End_Transaction();
        }
        catch(Exception $ex)
        {
            $this->Log->error($ex->getMessage());
            if($this->Isdebug == true)
                echo "<div class='alert-danger' style='width:80%;margin-left:10%;text-align: center;'>".$ex->getMessage()." - file ".$ex->getFile()." - riga".$ex->getLine()."</div>";
        }
    }

    public function GetHtml()
    {
        $Html = "";
        try {
            //var_dump($this->PrintHtml);
            $obj = $this->ordina($this->PrintHtml);

            for($i=0;$i<count($obj);$i++)
            {
                if(!isset($obj[$i]->value))
                    throw new Exception("Assegrare il testo dell'html");
                $Html .= $obj[$i]->value;
            }
        }
        catch(Exception $ex)
        {
            $this->Log->error($ex->getMessage());
            if($this->Isdebug == true)
                echo "<div class='alert-danger' style='width:80%;margin-left:10%;text-align: center;'>".$ex->getMessage()." - file ".$ex->getFile()." - riga".$ex->getLine()."</div>";
        }

        return $Html;
    }

    private function ordina($object){
        try {
            for($i=0;$i<count($object)-1;$i++){
                for($x=$i+1;$x<count($object);$x++)
                {
                    if(!isset($object[$i]->position)||!isset($object[$x]->position))
                        throw new Exception("Assegnare un valore per la posizione del div");
                    else if(!is_numeric($object[$i]->position)||!is_numeric($object[$x]->position))
                        throw new Exception("La posizione della parte di html deve essere un valore numerico");

                    if($object[$i]->position > $object[$x]->position)
                    {
                        $objHtmlTemp = $object[$x];
                        $object[$x] = $object[$i];
                        $object[$i] = $objHtmlTemp;
                        $i--;
                        break;
                    }
                }
            }

            return $object;
        }
        catch(Exception $ex)
        {
            $this->Log->error($ex->getMessage());
            if($this->Isdebug == true)
                echo "<div class='alert-danger' style='width:80%;margin-left:10%;text-align: center;'>".$ex->getMessage()." - file ".$ex->getFile()." - riga".$ex->getLine()."</div>";
        }

    }

    private function deleteElementArray(array $array, $element, $key=null)
    {
        try {
            for($i=0;$i<count($array);$i++){
                if(!is_null($key))
                {
                    if($array[$i][$key]===$element){
                        array_splice($array, $i, 1);
                        break;
                    }
                }
                else
                {
                    if($array[$i]===$element){
                        array_splice($array, $i, 1);
                        break;
                    }
                }
            }
        }
        catch(Exception $ex)
        {
            $this->Log->error($ex->getMessage());
            if($this->Isdebug == true)
                echo "<div class='alert-danger' style='width:80%;margin-left:10%;text-align: center;'>".$ex->getMessage()." - file ".$ex->getFile()." - riga".$ex->getLine()."</div>";
        }

        return $array;
    }

    private function buildText($flagIsSet,$flag,$arrText,$atto = array(),$flag_last_act = null){

        try {
            $BuildText = "";
            if($flagIsSet){
                for($x=0;$x<count($arrText);$x++){
                    if($arrText[$x]["DocumentTypeId"] == $flag) {
                        $BuildText = $arrText[$x]["Text"];
                        //array_splice($array, $x, 1);

                        $this->Text = $this->deleteElementArray($this->Text,(int)$flag,"DocumentTypeId");
                        break;
                    }
                }
            }
            else{
                if(count($atto)>0) {

                    if($this->flagPignoAtto=="pigno") {
                        if($atto["TotalePagamenti"] == null)
                            $atto["TotalePagamenti"] = "0.00";

                        $BuildText = "";
                        if($atto["Data_Notifica"]!=null && $atto["DescrizioneMotivoNotifica"]==null) {
                            $BuildText .= "Procedura attivata; ";
                            $BuildText.= $atto["ID_Cronologico"] . "/" . $atto["Anno_Cronologico"];
                            if($atto["Data_Notifica"]!=null)
                                $BuildText .= " Notificato in data " . $this->cls_date->Get_DateNewFormat($atto["Data_Notifica"], "DB");
                        }
                        else
                            $BuildText .= "Procedura attivata; ".$atto["ID_Cronologico"] . "/" . $atto["Anno_Cronologico"]." Non notificato";

                        if(!empty($atto["DescrizioneModalitaNotifica"]))
                            $BuildText .= " ".$atto["DescrizioneModalitaNotifica"];
                        if(!empty($atto["DescrizioneStatoNotifica"]))
                            $BuildText .= " ".$atto["DescrizioneStatoNotifica"];
                        if(!empty($atto["DescrizioneMotivoNotifica"]))
                            $BuildText .= " ".$atto["DescrizioneMotivoNotifica"];

                        if($atto["TotalePagamenti"] > 0)
                            $BuildText .= " con pagamento pari a € " . $atto["TotalePagamenti"];
                    }
                    else {

                        $BuildText = $atto["ID_Cronologico"] . "/" . $atto["Anno_Cronologico"];
                        if($atto["Data_Notifica"]!=null)
                            $BuildText .= " Data di notifica " . $this->cls_date->Get_DateNewFormat($atto["Data_Notifica"], "DB");
                        if($atto["DescrizioneModalitaNotifica"]!=null && $atto["DescrizioneModalitaNotifica"]!="")
                            $BuildText .= ", modalità " . $atto["DescrizioneModalitaNotifica"];

                        if($this->entrata == "CDS" && $this->data_morte != null && $flag_last_act == true){
                            //if($flag_last_act == true){
                                $BuildText .= "\nDecesso debitore in data " . $this->cls_date->Get_DateNewFormat($this->data_morte, "DB");
                            //}
                            /*else if($this->presso == null){
                                $BuildText .= "\nDebitore deceduto. Procedura non eseguibile per mancata comunicazione erede.";
                            }*/
                        }
                        else if($this->entrata != "CDS" && $this->data_morte != null && $flag_last_act == true && $this->presso == null){
                            $BuildText .= "\nDebitore deceduto. Procedura interrotta per mancata comunicazione erede.";
                        }

                    }
                }
                else {
                    for($x=0;$x<count($arrText);$x++){

                        if ($flag == 23){
                            $BuildText = "";
                            $check = false;
                            if($this->CF == null && $this->PI == null){
                                $BuildText .= "Codice Fiscale/Partita Iva assente o errato. ";
                                $check = true;
                            }
                            if(($this->via_id == 1 || $this->via_id == null || $this->via_id == "") && ($this->via_cap_id == 1 || $this->via_cap_id == null || $this->via_cap_id == "")){
                                $BuildText .= "Indirizzo assente o inesatto. ";
                                $check = true;
                            }
                            if(is_array($this->pigno) && is_array($this->atti))
                                if(count($this->pigno) == 0 && count($this->atti) == 0){
                                    $BuildText .= "Non emesso.";
                                    $check = true;
                                }

                            if(!$check)
                                $BuildText = "Procedura eseguita.";

                            break;
                        }
                        else{
                            if($arrText[$x]["DocumentTypeId"] == $flag) {
                                $BuildText = $arrText[$x]["Text"];
                                break;
                            }
                        }
                    }
                }
            }
        }
        catch(Exception $ex)
        {
            $this->Log->error($ex->getMessage());
            if($this->Isdebug == true)
                echo "<div class='alert-danger' style='width:80%;margin-left:10%;text-align: center;'>".$ex->getMessage()." - file ".$ex->getFile()." - riga".$ex->getLine()."</div>";
        }

        return $BuildText;
    }

    private function printTextArea($Text, $docType, $docId=null)
    {
        $objHtml = new StdClass();
        try {
            if(!is_numeric($this->a_htmlSort[$docType]['Position'])) throw new Exception("La posizione deve essere un valore numerico");

            $objHtml->position = $this->a_htmlSort[$docType]['Position'];
            $objHtml->value =  '
                                <input type="hidden" name="DocumentTypeId_'.$this->CountGlobal.'" value="'.$docType.'">
                                <input type="hidden" name="DocumentId_'.$this->CountGlobal.'" value="'.$docId.'">
                                <input type="hidden" name="TextValue_'.$this->CountGlobal.'" id="TextValue_'.$this->CountGlobal.'" value="'.$Text.'">
                                <input type="hidden" name="CountGlobal[]" value="'.$this->CountGlobal.'">
                                <div class="row">
                                    <div class="col col-lg-10 col-lg-offset-1">
                                        <div class="form-group">
                                            <label class="col-lg-4 control-label resize" style="text-align: left;">
                                                '.$this->a_htmlSort[$docType]['Title'].'
                                            </label>
                                            <div class="col-lg-8">
                                                <textarea style="max-width: 100%;" class="form-control resize" onchange="AggiornaText(this,\'TextValue_'.$this->CountGlobal.'\');"
                                                name="'.$this->a_htmlSort[$docType]['InputId'].'" id="'.$this->a_htmlSort[$docType]['InputId'].'">'.$Text.'</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>';

            $this->motivazioniArr[] = array('DocumentTypeId' => $docType, 'Text' => $Text);
            $this->CountGlobal++;
        }
        catch(Exception $ex)
        {
            $this->Log->error($ex->getMessage());
            if($this->Isdebug == true)
                echo "<div class='alert-danger' style='width:80%;margin-left:10%;text-align: center;'>".$ex->getMessage()." - file ".$ex->getFile()." - riga".$ex->getLine()."</div>";
        }

        return $objHtml;
    }

    /**
     * Restituisce i testi delle motivazioni nell'ordine canonico (come sgravi_documenti
     * in modalità definitiva), senza scrivere nulla sul DB. Utile per popolare la colonna
     * Excel in modalità provvisoria (flagSave=false).
     * @return array  Array di ['DocumentTypeId' => int, 'Text' => string]
     */
    public function GetMotivazioniArray() {
        $arr = $this->motivazioniArr;
        $sort = $this->a_docSort;
        usort($arr, function ($x, $y) use ($sort) {
            $px = isset($sort[$x['DocumentTypeId']]) ? $sort[$x['DocumentTypeId']] : 999;
            $py = isset($sort[$y['DocumentTypeId']]) ? $sort[$y['DocumentTypeId']] : 999;
            return $px <=> $py;
        });
        return $arr;
    }

    private function printTytle($docType){
        $objHtml = new StdClass();
        try{
            $objHtml->position = $this->a_htmlSort[$docType]['Position'];
            $objHtml->value = '
                <div class="row" style="margin-top: 1%;">
                    <div class="col col-lg-10 col-lg-offset-1">
                        <p style="color: #0a53be;"><b>'.$this->a_htmlSort[$docType]['Title'].'</b></p>
                    </div>
                </div>
            ';
        }
        catch(Exception $ex){

        }

        return $objHtml;
    }
}