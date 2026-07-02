<?php

include_once(CLS."/cls_db.php");
include_once(CLS."/cls_help.php");
include_once(CLS."/cls_DateTimeInLine.php");
include_once(CLS."/cls_LOG.php");


class BuildMotivationText
{
    private $Log;
    private $Isdebug;

    private $atti;
    private $pigno;
    private $flagPignoAtto;

    private $arrayDocIdDefault;
    private $Text;
    private $flagIsSet;
    private $PrintHtml;

    private $cls_db;
    private $cls_date;

    private $CountGlobal;

    public function __construct($partita_ID,$debug = false)
    {
        $this->Log = new LOG();

        try {

            if(!isset($partita_ID))
                throw new Exception("Partita non inserita");
            if(!is_numeric($partita_ID))
                throw new Exception("Partita inserita errata, non è numerica");

            $this->cls_db = new cls_db();
            $this->cls_date = new cls_DateTimeI("IT",false);

            $query = "SELECT DocumentTypeId, Text FROM motivazioni_sgravio";
            $TextDefault = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

            $query = "SELECT DocumentTypeId, Text FROM sgravi_documenti WHERE Partita_ID = ".$partita_ID;
            $TextSet = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

            $this->flagIsSet = false;
            $this->PrintHtml = array();
            $this->Isdebug = $debug;
            $this->CountGlobal = 0;

            if(count($TextSet) == 0) {
                echo '<div class="alert-danger" style="display: table; height: 50px;width:80%;margin-left:10%;border-radius: 12px;">
                  <div style="display: table-cell; vertical-align: middle;">
                    <div  style="text-align: center;">Non è possibile creare l\'elenco excell se non si salva!</div>
                  </div>
                </div>';
                $this->Text = $TextDefault;
            }
            else {
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

                if($atti[0]["Info_Cartella"] == null && $atti[0]["ID_Cronologico"] == null && $atti[0]["Anno_Cronologico"] == null && $atti[0]["Data_Notifica"] == null && $atti[0]["DescrizioneModalitaNotifica"] == null && $atti[0]["DocumentTypeId"] == null){
                    $atti = array();
                }
            }

            $this->atti = $atti;

            if(!$this->flagIsSet) {
                //$this->PrintHtml = array();
                $arrDocIdAtto = array(23, 2, 4, 12);
                //if (count($this->atti) > 0) $this->arrayDocIdDefault = $this->deleteElementArray($arrDocIdAtto, 23);
                //else $this->arrayDocIdDefault = $arrDocIdAtto;
                $this->arrayDocIdDefault = $arrDocIdAtto;
                $this->build($atti);
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
                $arrDocIdPigno = array(8, 7, 24, 14, 22, 25, 26, 27, 6, 28, 29, 37, 30, 31, 32, 33, 34, 35, 36);
                $this->arrayDocIdDefault = $arrDocIdPigno;
                $this->build($pigno);
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
            if(!$this->flagIsSet)
                for($i=0; $i<count($atti); $i++)
                {
                    //var_dump("AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA");
                    if($this->flagPignoAtto == "atto") {
                        if ($atti[$i]["Tipo"] != "PATRIMONIALE" && $atti[$i]["Tipo"] != "RIFIUTI") {
                            $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault, 12);
                            //continue;
                        }
                    }
                    switch($atti[$i]["DocumentTypeId"]){
                        case 23:

                            $BuildText = $this->buildText($this->flagIsSet,23,$this->Text,$atti[$i]);
                            $this->PrintHtml[] = $this->printTextArea("ACCERTAMENTO",$BuildText,"accertamento",1,23);
                            $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault,23);
                            break;
                        case 2:
                            $BuildText = $this->buildText($this->flagIsSet,2,$this->Text,$atti[$i]);
                            $this->PrintHtml[] = $this->printTextArea("INGIUNZIONE",$BuildText,"ingiunzione",3,2);
                            $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault,2);
                            break;
                        case 4:
                            $BuildText = $this->buildText($this->flagIsSet,4,$this->Text,$atti[$i]);
                            $this->PrintHtml[] = $this->printTextArea("AVVISO INTIMAZIONE",$BuildText,"avv_int",4,4);
                            $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault,4);
                            break;
                        case 12:

                            if($this->flagPignoAtto == "atto") {
                                if ($atti[$i]["Tipo"] != "PATRIMONIALE" && $atti[$i]["Tipo"] != "RIFIUTI") {
                                    //$this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault, 12);
                                    continue;
                                }
                            }
                            $BuildText = $this->buildText($this->flagIsSet,12,$this->Text,$atti[$i]);
                            $this->PrintHtml[] = $this->printTextArea("AVVISO MESSA IN MORA",$BuildText." TIPO: ".$atti[$i]["Tipo"],"avv_mess_mora",2,12);
                            $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault,12);
                            break;
                        case 8:
                            $BuildText = $this->buildText($this->flagIsSet,8,$this->Text,$atti[$i]);
                            $this->PrintHtml[] = $this->printTextArea("PIGNORAMENTO PRESSO BANCA/POSTA",$BuildText,"pigno_banca_posta",5,8);
                            $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault,8);
                            break;
                        case 7:
                            $BuildText = $this->buildText($this->flagIsSet,7,$this->Text,$atti[$i]);
                            $this->PrintHtml[] = $this->printTextArea("PIGNORAMENTO PRESSO DATORE DI LAVORO",$BuildText,"pigno_datore_lavoro",6,7);
                            $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault,7);
                            break;
                        case 24:
                            $BuildText = $this->buildText($this->flagIsSet,24,$this->Text,$atti[$i]);
                            $this->PrintHtml[] = $this->printTextArea("PIGNORAMENTO PRESSO ISTITUTI PREVIDENZIALI",$BuildText,"pigno_istit_prev",7,24);
                            $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault,24);
                            break;
                        case 14:
                            $BuildText = $this->buildText($this->flagIsSet,14,$this->Text,$atti[$i]);
                            $this->PrintHtml[] = $this->printTextArea("PIGNORAMENTO PRESSO TERZI",$BuildText,"pigno_terzi",8,14);
                            $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault,14);
                            break;
                        case 22:
                            $BuildText = $this->buildText($this->flagIsSet,22,$this->Text,$atti[$i]);
                            $this->PrintHtml[] = $this->printTextArea("PREAVVISO DI FERMO AMMINISTRATIVO",$BuildText,"preav_fermo",9,22);
                            $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault,22);
                            break;
                        case 25:
                            $BuildText = $this->buildText($this->flagIsSet,25,$this->Text,$atti[$i]);
                            $this->PrintHtml[] = $this->printTextArea("PREAVVISO DI FERMO",$BuildText,"fermo",10,25);
                            $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault,25);
                            break;
                        case 26:
                            $BuildText = $this->buildText($this->flagIsSet,26,$this->Text,$atti[$i]);
                            $this->PrintHtml[] = $this->printTextArea("PREAVVISO DI ISCRIZIONE DI IPOTECA",$BuildText,"preav_iscr_ip",11,26);
                            $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault,26);
                            break;
                        case 27:
                            $BuildText = $this->buildText($this->flagIsSet,27,$this->Text,$atti[$i]);
                            $this->PrintHtml[] = $this->printTextArea("IPOTECA",$BuildText,"ipoteca",12,27);
                            $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault,27);
                            break;
                        case 6:
                            $BuildText = $this->buildText($this->flagIsSet,6,$this->Text,$atti[$i]);
                            $this->PrintHtml[] = $this->printTextArea("PIGNORAMENTO BENI MOBILI REGISTRATI",$BuildText,"pigno_beni_mob_reg",13,6);
                            $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault,6);
                            break;
                        case 28:
                            $BuildText = $this->buildText($this->flagIsSet,28,$this->Text,$atti[$i]);
                            $this->PrintHtml[] = $this->printTextArea("IPOTECA IMMOBILIARE",$BuildText,"ipot_immob",14,28);
                            $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault,28);
                            break;
                        case 29:
                            $BuildText = $this->buildText($this->flagIsSet,29,$this->Text,$atti[$i]);
                            $this->PrintHtml[] = $this->printTextArea("PIGNORAMENTO IMMOBILIARE",$BuildText,"pigno_immob",15,29);
                            $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault,29);
                            break;
                        case 37:
                            $BuildText = $this->buildText($this->flagIsSet,37,$this->Text,$atti[$i]);
                            $this->PrintHtml[] = $this->printTextArea("PIGNORAMENTO MOBILIARE",$BuildText,"pigno_mob",16,37);
                            $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault,37);
                            break;
                        case 30:
                            $BuildText = $this->buildText($this->flagIsSet,30,$this->Text,$atti[$i]);
                            $this->PrintHtml[] = $this->printTextArea("PEGNO MOBILIARE",$BuildText,"pegno_mob",17,30);
                            $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault,30);
                            break;
                        case 31:
                            $BuildText = $this->buildText($this->flagIsSet,31,$this->Text,$atti[$i]);
                            $this->PrintHtml[] = $this->printTextArea("PIGNORAMENTO NATANTI",$BuildText,"pigno_nat",18,31);
                            $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault,31);
                            break;
                        case 32:
                            $BuildText = $this->buildText($this->flagIsSet,32,$this->Text,$atti[$i]);
                            $this->PrintHtml[] = $this->printTextArea("SEQUESTRO CONSERVATIVO NATANTI",$BuildText,"seq_cons_nat",19,32);
                            $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault,32);
                            break;
                        case 33:
                            $BuildText = $this->buildText($this->flagIsSet,33,$this->Text,$atti[$i]);
                            $this->PrintHtml[] = $this->printTextArea("IPOTECA NATANTI",$BuildText,"ipot_nat",20,33);
                            $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault,33);
                            break;
                        case 34:
                            $BuildText = $this->buildText($this->flagIsSet,34,$this->Text,$atti[$i]);
                            $this->PrintHtml[] = $this->printTextArea("PIGNORAMENTO AEROMOBILI",$BuildText,"pigno_aero",21,34);
                            $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault,34);
                            break;
                        case 35:
                            $BuildText = $this->buildText($this->flagIsSet,35,$this->Text,$atti[$i]);
                            $this->PrintHtml[] = $this->printTextArea("SEQUESTRO CONSERVATIVO AEROMOBILI",$BuildText,"seq_cons_aero",22,35);
                            $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault,35);
                            break;
                        case 36:
                            $BuildText = $this->buildText($this->flagIsSet,36,$this->Text,$atti[$i]);
                            $this->PrintHtml[] = $this->printTextArea("IPOTECA AEROMOBILI",$BuildText,"ipot_aero",23,36);
                            $this->arrayDocIdDefault = $this->deleteElementArray($this->arrayDocIdDefault,36);
                            break;
                        default:  throw new Exception("DocumentTypeId = ".$atti[$i]["DocumentTypeId"]." non presente nello switch della classe BuildMotivationText");
                    }
                }

            //var_dump($this->arrayDocIdDefault);

            for($i=0;$i<count($this->arrayDocIdDefault);$i++){
                //var_dump("BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB");
                switch($this->arrayDocIdDefault[$i]){
                    case 23:
                        $BuildText = $this->buildText($this->flagIsSet,23,$this->Text);
                        $this->PrintHtml[] = $this->printTextArea("ACCERTAMENTO",$BuildText,"accertamento",1,23);
                        //var_dump($this->PrintHtml);
                        break;
                    case 2:
                        $BuildText = $this->buildText($this->flagIsSet,2,$this->Text);
                        $this->PrintHtml[] = $this->printTextArea("INGIUNZIONE",$BuildText,"ingiunzione",3,2);
                        break;
                    case 4: $BuildText = $this->buildText($this->flagIsSet,4,$this->Text);
                        $this->PrintHtml[] = $this->printTextArea("AVVISO INTIMAZIONE",$BuildText,"avv_int",4,4);
                        break;
                    case 12:

                        if($this->Text!="") {
                            $BuildText = $this->buildText($this->flagIsSet, 12, $this->Text);
                            $this->PrintHtml[] = $this->printTextArea("AVVISO MESSA IN MORA", $BuildText, "avv_mess_mora", 2, 12);
                        }
                        break;
                    case 8:
                        $BuildText = $this->buildText($this->flagIsSet,8,$this->Text);
                        $this->PrintHtml[] = $this->printTextArea("PIGNORAMENTO PRESSO BANCA/POSTA",$BuildText,"pigno_banca_posta",5,8);
                        break;
                    case 7:
                        $BuildText = $this->buildText($this->flagIsSet,7,$this->Text);
                        $this->PrintHtml[] = $this->printTextArea("PIGNORAMENTO PRESSO DATORE DI LAVORO",$BuildText,"pigno_datore_lavoro",6,7);
                        break;
                    case 24:
                        $BuildText = $this->buildText($this->flagIsSet,24,$this->Text);
                        $this->PrintHtml[] = $this->printTextArea("PIGNORAMENTO PRESSO ISTITUTI PREVIDENZIALI",$BuildText,"pigno_istit_prev",7,24);
                        break;
                    case 14:
                        $BuildText = $this->buildText($this->flagIsSet,14,$this->Text);
                        $this->PrintHtml[] = $this->printTextArea("PIGNORAMENTO PRESSO TERZI",$BuildText,"pigno_terzi",8,14);
                        break;
                    case 22:
                        $BuildText = $this->buildText($this->flagIsSet,22,$this->Text);
                        $this->PrintHtml[] = $this->printTextArea("PREAVVISO DI FERMO AMMINISTRATIVO",$BuildText,"preav_fermo",9,22);
                        break;
                    case 25:
                        $BuildText = $this->buildText($this->flagIsSet,25,$this->Text);
                        $this->PrintHtml[] = $this->printTextArea("PREAVVISO DI FERMO",$BuildText,"fermo",10,25);
                        break;
                    case 26:
                        $BuildText = $this->buildText($this->flagIsSet,26,$this->Text);
                        $this->PrintHtml[] = $this->printTextArea("PREAVVISO DI ISCRIZIONE DI IPOTECA",$BuildText,"preav_iscr_ip",11,26);
                        break;
                    case 27:
                        $BuildText = $this->buildText($this->flagIsSet,27,$this->Text);
                        $this->PrintHtml[] = $this->printTextArea("IPOTECA",$BuildText,"ipoteca",12,27);
                        break;
                    case 6:
                        $BuildText = $this->buildText($this->flagIsSet,6,$this->Text);
                        $this->PrintHtml[] = $this->printTextArea("PIGNORAMENTO BENI MOBILI REGISTRATI",$BuildText,"pigno_beni_mob_reg",13,6);
                        break;
                    case 28:
                        $BuildText = $this->buildText($this->flagIsSet,28,$this->Text);
                        $this->PrintHtml[] = $this->printTextArea("IPOTECA IMMOBILIARE",$BuildText,"ipot_immob",14,28);
                        break;
                    case 29:
                        $BuildText = $this->buildText($this->flagIsSet,29,$this->Text);
                        $this->PrintHtml[] = $this->printTextArea("PIGNORAMENTO IMMOBILIARE",$BuildText,"pigno_immob",15,29);
                        break;
                    case 37:
                        $BuildText = $this->buildText($this->flagIsSet,37,$this->Text);
                        $this->PrintHtml[] = $this->printTextArea("PIGNORAMENTO MOBILIARE",$BuildText,"pigno_mob",16,37);
                        break;
                    case 30:
                        $BuildText = $this->buildText($this->flagIsSet,30,$this->Text);
                        $this->PrintHtml[] = $this->printTextArea("PEGNO MOBILIARE",$BuildText,"pegno_mob",17,30);
                        break;
                    case 31:
                        $BuildText = $this->buildText($this->flagIsSet,31,$this->Text);
                        $this->PrintHtml[] = $this->printTextArea("PIGNORAMENTO NATANTI",$BuildText,"pigno_nat",18,31);
                        break;
                    case 32:
                        $BuildText = $this->buildText($this->flagIsSet,32,$this->Text);
                        $this->PrintHtml[] = $this->printTextArea("SEQUESTRO CONSERVATIVO NATANTI",$BuildText,"seq_cons_nat",19,32);
                        break;
                    case 33:
                        $BuildText = $this->buildText($this->flagIsSet,33,$this->Text);
                        $this->PrintHtml[] = $this->printTextArea("IPOTECA NATANTI",$BuildText,"ipot_nat",20,33);
                        break;
                    case 34:
                        $BuildText = $this->buildText($this->flagIsSet,34,$this->Text);
                        $this->PrintHtml[] = $this->printTextArea("PIGNORAMENTO AEROMOBILI",$BuildText,"pigno_aero",21,34);
                        break;
                    case 35:
                        $BuildText = $this->buildText($this->flagIsSet,35,$this->Text);
                        $this->PrintHtml[] = $this->printTextArea("SEQUESTRO CONSERVATIVO AEROMOBILI",$BuildText,"seq_cons_aero",22,35);
                        break;
                    case 36:
                        $BuildText = $this->buildText($this->flagIsSet,36,$this->Text);
                        $this->PrintHtml[] = $this->printTextArea("IPOTECA AEROMOBILI",$BuildText,"ipot_aero",23,36);
                        break;
                    default: throw new Exception("DocumentTypeId = ".$atti[$i]["DocumentTypeId"]." non presente nello switch della classe BuildMotivationText");
                }
            }

            //var_dump($this->PrintHtml);
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

    public function GetHtml()
    {
        $Html = "";
        try {
            //var_dump($this->PrintHtml);
            $this->ordina();

            for($i=0;$i<count($this->PrintHtml);$i++)
            {
                if(!isset($this->PrintHtml[$i]->value))
                    throw new Exception("Assegrare il testo dell'html");
                $Html .= $this->PrintHtml[$i]->value;
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

    private function ordina(){
        try {
            for($i=0;$i<count($this->PrintHtml)-1;$i++){
                for($x=$i+1;$x<count($this->PrintHtml);$x++)
                {
                    if(!isset($this->PrintHtml[$i]->position)||!isset($this->PrintHtml[$x]->position))
                        throw new Exception("Assegnare un valore per la posizione del div");
                    else if(!is_numeric($this->PrintHtml[$i]->position)||!is_numeric($this->PrintHtml[$x]->position))
                        throw new Exception("La posizione della parte di html deve essere un valore numerico");

                    if($this->PrintHtml[$i]->position > $this->PrintHtml[$x]->position)
                    {
                        $objHtmlTemp = $this->PrintHtml[$x];
                        $this->PrintHtml[$x] = $this->PrintHtml[$i];
                        $this->PrintHtml[$i] = $objHtmlTemp;
                        $i--;
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

    }

    private function deleteElementArray(array $array,$element,$key=null)
    {
        try {
            for($i=0;$i<count($array);$i++){
                if($key != null)
                {
                    if($array[$i][$key]==$element){
                        array_splice($array, $i, 1);
                        break;
                    }
                }
                else
                {
                    if($array[$i]==$element){
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

    private function buildText($flagIsSet,$flag,$arrText,$atto = array()){

        try {
            $BuildText = "";
            if($flagIsSet){
                for($x=0;$x<count($arrText);$x++){
                    if($arrText[$x]["DocumentTypeId"] == $flag) {
                        $BuildText = $arrText[$x]["Text"];
                        //array_splice($array, $x, 1);
                        $this->Text = $this->deleteElementArray($this->Text,$flag,"DocumentTypeId");
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
                        if($atto["Data_Notifica"]!=null && $atto["DescrizioneMotivoNotifica"]==null) $BuildText .= "Procedura attivata; Notificata in data ".$this->cls_date->Get_DateNewFormat($atto["Data_Notifica"], "DB");
                        else $BuildText .= "Procedura attivata; Non notificata ";
                        $BuildText .= " ".$atto["DescrizioneModalitaNotifica"] . " " . $atto["DescrizioneStatoNotifica"] . " " . $atto["DescrizioneMotivoNotifica"] . " con pagamento pari a € " . $atto["TotalePagamenti"];
                    }
                    else $BuildText = $atto["ID_Cronologico"] . "/" . $atto["Anno_Cronologico"] . " Data di notifica " . $this->cls_date->Get_DateNewFormat($atto["Data_Notifica"], "DB") . ", modalità " . $atto["DescrizioneModalitaNotifica"];
                }
                else {
                    for($x=0;$x<count($arrText);$x++){
                        if($arrText[$x]["DocumentTypeId"] == $flag) {
                            $BuildText = $arrText[$x]["Text"];
                            break;
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

    private function printTextArea($title,$Text,$idName,$position,$docType)
    {
        $objHtml = new StdClass();
        try {
            if(!is_numeric($position)) throw new Exception("La posizione deve essere un valore numerico");

            $objHtml->position = $position;
            $objHtml->value =  '
                                <input type="hidden" name="DocumentTypeId_'.$this->CountGlobal.'" value="'.$docType.'">
                                <input type="hidden" name="TextValue_'.$this->CountGlobal.'" id="TextValue_'.$this->CountGlobal.'" value="'.$Text.'">
                                <input type="hidden" name="CountGlobal[]" value="'.$this->CountGlobal.'">
                                <div class="row">
                                    <div class="col col-lg-10 col-lg-offset-1">
                                        <div class="form-group">
                                            <label class="col-lg-4 control-label resize" style="text-align: left;">'.$title.'</label>
                                            <div class="col-lg-8">
                                                <textarea style="max-width: 100%;" class="form-control resize" name="'.$idName.'" id="'.$idName.'" onchange="AggiornaText(this,\'TextValue_'.$this->CountGlobal.'\');" >'.$Text.'</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>';

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
}