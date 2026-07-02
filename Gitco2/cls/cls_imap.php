<?php

class cls_imap {

    public $a_params;
    public $a_receipt;

    // imap server connection
    public $conn;

    // inbox storage and inbox message count
    private $a_emails = array();

    // email login credentials
    public $inboxConnection;
    public $inboxFolder;
    public $savePath;

    public $a_acceptanceReceipts = array(
        "AVVISO DI NON ACCETTAZIONE PER VIRUS",
        "AVVISO DI NON ACCETTAZIONE",
        "ACCETTAZIONE"
    );

    public $a_deliveryReceipts = array(
        "AVVISO DI MANCATA CONSEGNA PER SUP. TEMPO MASSIMO",
        "AVVISO DI MANCATA CONSEGNA PER VIRUS",
        "AVVISO DI MANCATA CONSEGNA",
        "CONSEGNA"
    );

    

    // connect to the server and get the inbox emails
    function __construct($a_params, $inboxFolder=null, $validation = true) {
        if(!is_array($a_params))
            return false;

        $this->validation = $validation;
        $this->setParams($a_params);

        try {
            $this->inboxFolder = $inboxFolder;
            $this->connect();
        }
        catch(Exception $ex){
            echo "CONNESSIONE FALLITA";
            die;
        }
    }


    public function setParams($a_params){
        $this->a_params = $a_params;
        $this->a_params['Password'] = $this->decryptPass($a_params['Password']);
    }


    public function setPecPath($cc){
        $this->savePath = EMAIL_ROOT."/".$cc."/PEC";
        if(!is_dir(EMAIL_ROOT."/".$cc))
            mkdir(EMAIL_ROOT."/".$cc);
        if(!is_dir($this->savePath))
            mkdir($this->savePath);
    }


    public function connect() {
        $this->a_emails = array();
        $this->setInboxConnection();

        if(empty($this->inboxConnection))
            throw new Exception("Connessione fallita! Dati Server non compilati");
        if(empty($this->a_params['Username']))
            throw new Exception("Connessione fallita! Dati Utente non compilati");
        if(empty($this->a_params['Password']))
            throw new Exception("Connessione fallita! Dati Password non compilati");
        
            try{
                $this->conn = imap_open($this->inboxConnection, $this->a_params['Username'], $this->a_params['Password']);
            }
            catch(Exception $ex){
                echo "ERRORE CONNESSIONE";
                die;
            }
        
    }

    public function setInboxConnection(){
        $this->inboxConnection = "{".$this->a_params['InMailServer'];
        if(!empty($this->a_params['InMailPort']))
            $this->inboxConnection.= ':'.$this->a_params['InMailPort'];

        switch(strtolower($this->a_params['InMailProtocol'])){
            case 'imap':		$this->inboxConnection.= '/imap';				break;
            case 'pop3':		$this->inboxConnection.= '/pop3';				break;
        }

        switch(strtolower($this->a_params['ConnectionSafety'])){
            case 'ssl':			$this->inboxConnection.= '/ssl';				break;
            case 'tls':			$this->inboxConnection.= '/tls';				break;
            case 'notls':		$this->inboxConnection.= '/notls';			    break;
        }

        switch($this->validation){
            case false:			$this->inboxConnection.= '/novalidate-cert';	break;
            case true:			$this->inboxConnection.= '/validate-cert';	    break;
        }
        $this->inboxConnection.= "}".$this->inboxFolder;
    }

    private function decryptPass($pass){
        $cls_cript = new cls_crypt();
        return $cls_cript->decryptIt($pass);
    }

    // close the server connection
    function close() {
        $this->a_emails = array();
        imap_close($this->conn);
    }

    public function selectFolder($folder) {
        $this->a_emails = array();
        $this->inboxFolder = $folder;
        $this->setInboxConnection();
        $result = imap_reopen($this->conn, $this->inboxConnection);
        return $result;
    }

    public function isConnected() {
        return $this->conn !== false;
    }

    public function getError() {
        return imap_last_error();
    }

    // move the message to a new folder
    function move($msg_index, $folder='INBOX.Processed') {
        // move on server
        imap_mail_move($this->conn, $msg_index, $folder);
        imap_expunge($this->conn);

        // re-read the inbox
        $this->inbox();
    }

    // get a specific message (1 = first email, 2 = second email, etc.)
    function get($msg_index=null) {
        return $this->a_emails[$msg_index];
    }

    public function checkPecReceipt($index){
        $this->a_receiptStatus = null;
        if(substr($this->a_emails[$index]["header"]->Subject,0,2)=="=?")
            $subject = iconv_mime_decode($this->a_emails[$index]["header"]->Subject,0,"UTF-8");
        else
            $subject = $this->a_emails[$index]["header"]->Subject;

        $this->a_receipt = $this->checkReceipt($subject);
        $this->a_receipt['date'] = date("Y-m-d H:i:s", strtotime($this->a_emails[$index]["header"]->MailDate));

    }

    public function savePecReceipt($index, $basename, $delete=false)
    {
        if(empty($this->a_receipt['status']))
            return false;

        $this->a_receipt['file'] = $this->savePath."/PEC_".$basename."__".str_replace(" ","",$this->a_receipt['text']).".eml";
        $myfile = fopen($this->a_receipt['file'], 'w');
        fwrite($myfile, $this->a_emails[$index]['fetchbody']);
        fclose($myfile);

        if($delete)
            if(file_exists($this->a_receipt['file']))
                $this->markDisposable($index);
        
    }

    public function checkSubjectPos($subject, $receipt){
        if(strpos(strtoupper($subject), strtoupper($receipt.":"))!==false)
            return true;
    }

    public function checkReceipt($subject){
        $a_status = $this->checkAcceptanceReceipt($subject);
        if(is_null($a_status['status']))
            $a_status = $this->checkDeliveryReceipt($subject);
        return $a_status;    
    }

    public function checkAcceptanceReceipt($subject){
        foreach($this->a_acceptanceReceipts as $key=>$receipt){
            if($this->checkSubjectPos($subject,$receipt)){
                if($key==count($this->a_acceptanceReceipts)-1)
                    return array("status"=>1,"text"=>$receipt, "type"=>"acceptance");
                else
                    return array("status"=>2,"text"=>$receipt, "type"=>"acceptance");
                break;
            }
        }
        return array("status"=>null);
    }

    public function checkDeliveryReceipt($subject){
        foreach($this->a_deliveryReceipts as $key=>$receipt){
            if($this->checkSubjectPos($subject,$receipt)){
                if($key==count($this->a_deliveryReceipts)-1)
                    return array("status"=>1,"text"=>$receipt, "type"=>"delivery");
                else
                    return array("status"=>2,"text"=>$receipt, "type"=>"delivery");
                break;
            }
        }
        return array("status"=>null);
    }

    public function getEmailNumRows()
    {
        return count($this->a_emails);
    }

    public function markDisposable($index)
    {
        imap_delete($this->conn, $this->a_emails[$index]["id"], FT_UID);
    }

    public function Delete()
    {
        imap_expunge($this->conn);
    }

    // read the inbox
    function inbox() {
        $this->a_emails = array();
        for($i = 1; $i <= imap_num_msg($this->conn); $i++) {
            $this->a_emails[] = array(
                'index'     => $i,
                'header'    => imap_headerinfo($this->conn, $i),
                'body'      => imap_body($this->conn, $i),
                'structure' => imap_fetchstructure($this->conn, $i),
                'fetchbody' => imap_fetchbody($this->conn, $i,'')
            );
        }
    }

    // read the inbox
    function inboxSelected($filter) {
        $this->a_emails = array();
        $a_uids = imap_search($this->conn, $filter, SE_UID);
        if(gettype($a_uids)!="array")
            return false;
            // throw new Exception("Mail non trovata, nessun id mail ritornato");

        $this->a_emails = array();
        foreach($a_uids as $uid){
            if (empty($uid))
                continue;
            $msgno = imap_msgno($this->conn, $uid);
            $this->a_emails[] = array(
                'id'        => $uid,
                'header'    => imap_headerinfo($this->conn, $msgno),
                'body'      => imap_body($this->conn, $uid, FT_UID),
                'structure' => imap_fetchstructure($this->conn, $uid, FT_UID),
                'fetchbody'     => imap_fetchbody($this->conn, $msgno,'')
            );
        }
        return true;
    }



    public function saveEMail($index, $basename, $filepath=null)
    {
        if(is_null($filepath))
            $filepath = $this->savePath."/".$basename;

        $myfile = fopen($filepath, 'w');
        fwrite($myfile, $this->a_emails[$index]['fetchbody']);
        fclose($myfile);
    }

}

?>
