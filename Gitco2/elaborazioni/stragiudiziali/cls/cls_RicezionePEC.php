<?php
include_once CLS ."/traits.php";

class IMapStraGiudiziali extends cls_imap
{
    public function setPecPath($proc_id){
        
        $cicla_path = function ($array,$index,&$path) use (&$cicla_path)
        {   
            if(count($array)>$index)
            {
                $path=$path.$array[$index]."/";
                if(!is_dir($path))
                    mkdir($path);
                $cicla_path($array,$index+1,$path);
            }
        };
        $path = EMAIL_ROOT."/Stragiudiziali/".$proc_id;
        $this->savePath = $path;

        $array = explode("/",$path);
        $p="";
        $cicla_path($array,0,$p);
      
    }
}
class RicezionePEC
{
    private $cls_db;
    private $a_emails;
    private $a_inbox;
    private $cls_imap;
    private $a_errors;

    public $proc_id;
    public $tipo;
    public $CC;
    public $callbackControllo;
    public $callbackUpdateBar;

    use tSelectSQL;

    function  __construct($cls_db){
        $this->cls_db = $cls_db;
    }

    private function CreaNomeFile($istituto_id)
    {
         $filename = "Stragiudiziale_$this->tipo"."_" . $this->CC . "_" . $istituto_id ;
         return $filename;
    }
    private function AggiornaDBEmails($id)
    {
        $cls_imap = $this->cls_imap;
        $cls_db = $this->cls_db;
        $query = "
        UPDATE emails SET
        ".ucfirst($cls_imap->a_receipt['type'])."_Receipt=".$cls_imap->a_receipt['status'].",
        ".ucfirst($cls_imap->a_receipt['type'])."_Datetime='".$cls_imap->a_receipt['date']."'
        WHERE Id=".$id;
        $cls_db->ExecuteQuery($query);
    }
    public function PrendiDatiMails()
    {
        if ($this->tipo=="Previdenziali")
        {
            $query="SELECT 
            s.Id,
            s.Procedure_Id,
            ee.PEC,
            ee.ID as Istituto_Id,
            s.Email_Id,
            e.Subject
            FROM gitco2.stragiudiziali as s join enti_esterni as ee on ee.ID = s.Previdenza_Id 
            join emails as e on e.Id=s.Email_id where s.Procedure_Id = $this->proc_id";
        }
        else
        {
            $query="SELECT 
            s.Id,
            s.Procedure_Id,
            b.PEC,
            b.ID as Istituto_Id,
            s.Email_Id,
            e.Subject
            FROM gitco2.stragiudiziali as s join banca as b on b.ID = s.Banca_Id 
            join emails as e on e.Id=s.Email_id where s.Procedure_Id = $this->proc_id";
        }
        
        $this->a_emails = $this->SelectSQL($query);
        
        return $this;

    }

    public function ControlloPresenzaMail()
    {
        if(count($this->a_emails)==0)
        {
            $msg = "Nessun Risulato Trovato!";
            call_user_func($this->callbackControllo,$msg,100);
        }
        return $this;
    }

    public function ControlloInbox()
    {
        $query = "  SELECT * FROM user_emails WHERE MailType = 'PEC' AND User_Id= " . $_SESSION['aut_progr'];
        $query = "SELECT * FROM user_emails WHERE MailType = 'PEC' AND User_Id=1"; //debug

        $this->a_inbox = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
        
        if (empty($this->a_inbox)) {
            $msg = "Parametri email " . $_SESSION['username'] . " assenti. Contattare l'IT";
            call_user_func($this->callbackControllo,$msg,11);
        }
        return $this;
    }

    public function IMap()
    {
        
        try {
            $this->cls_imap = new IMapStraGiudiziali($this->a_inbox,'INBOX');
            $this->cls_imap->setPecPath($this->proc_id);

            if ($this->cls_imap->isConnected() === false) {
                $msg = "Errore di connessione reader";
                call_user_func($this->callbackControllo,$msg,12);
            }
        }
        catch(Exception $ex)
        {
            $msg = "READER: ".$ex->getMessage();
            call_user_func($this->callbackControllo,$msg,13);
        }
        return $this;
    }

    public function CheckScaricoRicevute()
    {
        $cls_db=$this->cls_db;
        $proc_id = $this->proc_id;

        $query = "SELECT COUNT(E.Id) AS MissingReceiptNumber
        FROM emails E JOIN stragiudiziali str ON str.Email_Id=E.Id 
        WHERE str.Procedure_id=$proc_id AND E.Delivery_Receipt is null";
        $a_count = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
        
        if($a_count['MissingReceiptNumber']==0){
            $query = "UPDATE procedures  SET PecReceiptsFlag=1 WHERE Id=".$proc_id;
            $cls_db->ExecuteQuery($query);      
            $msg = "Scarico ricevute Pec completo!";
            call_user_func($this->callbackControllo,$msg,0);
            $error = 0;
        }
        else{
            $msg = "Scarico ricevute Pec effettuato! ".$a_count['MissingReceiptNumber']." pec ancora da verificare. Riprovare più tardi!";
            call_user_func($this->callbackControllo,$msg,2);
            $error = 2;
        }

        return $this;
        
    }

    private function CiclaImap($istituto_id,$email_id)
    {
        $cls_imap=$this->cls_imap;
        $cls_db = $this->cls_db;
        $baseFilename = $this->CreaNomeFile($istituto_id);

        for ($i = 0; $i < $cls_imap->getEmailNumRows(); $i++) {

            $cls_imap->checkPecReceipt($i);
            $cls_imap->savePecReceipt($i, $baseFilename, true);
    
            if(!is_null($cls_imap->a_receipt['status'])){
                if(is_file($cls_imap->a_receipt['file'])){
                   $this->AggiornaDBEmails($email_id);
                }
            }
    
        }
        return $this;
    }

    public function CiclaEmail()
    {
        $x=0;
        foreach($this->a_emails as $x=>$a_email){
            call_user_func($this->callbackUpdateBar,$x,count($this->a_emails));
            $checkMail = $this->cls_imap->inboxSelected('SUBJECT "'.$a_email['Subject'].'"');
    
            if(!$checkMail)
                continue;
            $istituto_id=$a_email["Istituto_Id"];
            $email_id = $a_email["Email_Id"];    
            $this->CiclaImap($istituto_id,$email_id);
            $x++;
        }
        return $this;
    }

    public function Fine()
    {
        $this->cls_imap->delete();
        $this->cls_imap->close();
        return $this;
    }

}


?>