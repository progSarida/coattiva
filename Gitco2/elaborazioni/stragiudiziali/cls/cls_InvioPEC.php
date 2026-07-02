<?php
include_once CLS ."/traits.php";

class InvioPEC
{
    private $status;
    private $cls_db;
    private $a_results;
    private $a_sender;
    private $a_emailsDT;
    private $a_params;
    private $cls_text;
    private $cls_mail;
    private $checkFailedSending;

    private $a_errors;
    public $proc_id;
    public $CC;
    public $callbackControllo;
    public $callbackUpdateBar;
    public $tipo;

    use tSelectSQL;

    function  __construct($cls_db){
        $this->cls_db = $cls_db;
    }
    protected function CreaNomeFile($istituto)
    {
        
        $path = STRAGIUDIZIALE . "/" . $this->proc_id;
        $excel = "Elenco_Stragiudiziale_$this->tipo"."_" . $this->CC ."_" . $istituto .".xlsx";
        $pdf = "Stragiudiziale_$this->tipo"."_" . $this->CC . "_" . $istituto . ".pdf";

        return array("excel"=> $path."/".$excel,"pdf"=>$path."/".$pdf);
    }
    private function PreparaMail(Array $a_params,$PEC,$excelpath,$pdfpath)
    {
       
        $this->cls_mail = new cls_phpmailer($this->a_sender);
        $this->cls_mail->SMTPDebug = 0; //2 debug
        $this->cls_mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $this->cls_mail->mailCreation($a_params);
        $this->cls_mail->setFrom($this->a_sender['Address']);
        $this->cls_mail->isHTML(true);
        $this->cls_mail->addAddress($PEC); 
        $this->cls_mail->addAttachment($excelpath);
        $this->cls_mail->addAttachment($pdfpath);
    }

    private function SalvaMail(Array $a_params,$PEC,$ID)
    {
        $a_email = array(
            "CC" => $this->CC,
            "Type"=>"pec",
            "Sending_Date" => date("Y-m-d"),
            "Sender" => $this->a_sender['Address'],
            "Recipient" => $PEC,
            "Subject" => $a_params['subject'],
            "Body" => htmlspecialchars($a_params['body'])
        );
        $sender = $this->a_sender['Address'];
        $data_spedizione=date("Y-m-d");

        $emailId = $this->cls_db->DbSave($this->cls_db->GetObjectQuery("emails", $a_email, $this->a_emailsDT));
        $query = "UPDATE stragiudiziali SET Email_Id = ".$emailId.", Sender_Pec = '$sender',
         Data_Spedizione = '$data_spedizione' WHERE ID=".$ID;
        $this->cls_db->ExecuteQuery($query);

    }
    public function PrendiDati()
    {
        if ($this->tipo = "Previdenziali")
        {
            $query="SELECT 
            s.Id,
            s.Procedure_Id,
            e.PEC,
            e.ID as Previdenza_Id
            FROM gitco2.stragiudiziali as s join enti_esterni as  e on e.ID = s.Previdenza_Id where s.Procedure_Id = $this->proc_id";
            
        }
        else
        {
            $query="SELECT 
            s.Id,
            s.Procedure_Id,
            b.PEC,
            b.ID as Banca_Id
            FROM gitco2.stragiudiziali as s join banca as b on b.ID = s.Banca_Id where s.Procedure_Id = $this->proc_id";
        }
        $this->a_results = $this->SelectSQL($query);
        return $this;

    }

    public function ControlloPresenzaDati()
    {
        if(count($this->a_results)==0)
        {
            $msg = "Invio bloccato. Dati assenti!";
            $error = 1;
            $extraGet = "&error=".$error."&msg=".$msg;
            call_user_func($this->callbackControllo,$extraGet);
        }
        return $this;
    }

    public function ControlloEmailMittente()
    {
        $this->a_emailsDT = $this->cls_db->getColumnDataTypes("emails"); //da spostare per pulizia
        $query = "  SELECT * FROM user_emails WHERE MailType = 'PEC' AND User_Id= " . $_SESSION['aut_progr'];
        //per il debug metto 1 
        $query = "  SELECT * FROM user_emails WHERE MailType = 'PEC' AND User_Id= 1";

        $this->a_sender = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
        if (empty($this->a_sender)) {
            $msg = "Invio bloccato. Parametri email mittente " . $_SESSION['username'] . " assenti. Contattare IT";
            call_user_func($this->callbackControllo,$msg);
        }
        return $this;
    }

    public function ControllaClausolaRiservatezza()
    {
       
        $this->cls_text = new cls_textParameters();
        $a_text = $this->cls_db->getArrayLine(
            $this->cls_db->SelectQuery
            ("SELECT * FROM text_parameters WHERE CC='".$this->CC."' AND Form_Type_ID=41")
        );
        
        if (empty($a_text)) {
            $msg = "Invio bloccato. Clausola di riservatezza assente. Salvare testo personalizzato per il comune.";
            $error = 1;
            $extraGet = "&error=".$error."&msg=".$msg;
            call_user_func($this->callbackControllo,$extraGet);
        }
        $this->cls_text->setHtmlBody($a_text['Content']);
        return $this;
    }

    public function Spedisci($row)
    {
        $dati["CC"] = $this->CC;
        $dati["Istituto_Id"] = $this->tipo == "Previdenziali" ? $row["Previdenza_Id"] : $row["Banca_Id"];
        $dati["PEC"] = $row["PEC"];
        $dati["ID"] = $row["Id"];
        //debug
        $dati["PEC"]  = "ufficialedellariscossione@pec.it";
        $prendi_nome_comune = function($cc)
        {
            $q = "select Com_Nome from comuni_lista where Com_Codice_Catastale = '$cc'";;
            $result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($q));
            
            return $result["Com_Nome"];
        };
        //Sostituisce la precedente emessa per errore di sistema.(testo da mettere in caso di errore spedizione)
        //debug
        $a_params['subject'] = "IGNORARE PROVA!!.Richiesta per stragiudiziali comune : ".$prendi_nome_comune($dati["CC"]);
        $a_params['body'] = $this->cls_text->html_body;
        $a_file = $this->CreaNomeFile(($dati["Istituto_Id"]));
        
        $this->PreparaMail($a_params,$dati["PEC"],$a_file["excel"],$a_file["pdf"]);

        //spedisci
        if (!$this->cls_mail->send()) {
            if($this->checkFailedSending==0)
                $this->checkFailedSending = 1;
            $this->a_errors = ($this->cls_mail->ErrorInfo);
        } else {
             
             $this->SalvaMail($a_params,$dati["PEC"],$dati["ID"]);
        }
    }

    public function CiclaMail()
    {
        $totale = count($this->a_results);
        $i=0;
        foreach($this->a_results as $row)
        {
            $this->Spedisci($row);
            call_user_func($this->callbackUpdateBar,$i,$totale);
            $i++;
            break; //debug
        }
        return $this;
    }

    public function Errori()
    {
        if ($this->checkFailedSending==0)
        {
            $query = "UPDATE procedures SET SendingPecFlag=1 WHERE ID=".$this->proc_id;
            $this->cls_db->ExecuteQuery($query);
            return $ret = array("flag" =>0,"count"=>0,"lista" => array());
        }
        return $ret = array("flag" =>1,"count"=>count($this->a_errors),"lista" => $this->a_errors);
    }
}
?>