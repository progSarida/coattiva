<?php

require_once ( PHPMAILER );
include_once ( CLS."/cls_crypt.php" );

class cls_phpmailer extends PHPMailer{

    /**
     * shipment parameter
     */
    public $a_sender = array();

    /**
     * message parts
     */
    public $a_msg = array();

    /**
     * imap connection
     */
    public $imap = false;

    /**
     * mailbox url string
     */
    private $mailbox = "";

    /**
     * currentfolder
     */
    public $folder = "INBOX";

    public $cls_crypt;
    /**
     * Constructor.
     * @param boolean $exceptions Should we throw external exceptions?
     */
    public function __construct(Array $a_sender, $exceptions = false,$debug = false)
    {
        if($debug != false)
            $this->SMTPDebug = $debug;
        $this->exceptions = (boolean)$exceptions;
        $this->a_sender 	= $a_sender;
        $this->cls_crypt = new cls_crypt();
    }

    public function mailCreation($a_params)
    {
        //$this->SMTPDebug = 2;

        $this->FromName 	= $this->a_sender['PublicName'];
        $this->From     	= $this->a_sender['Address'];
        $this->Host     	= $this->a_sender['OutMailServer']; // SMTP server
        $this->Port 		= (int) $this->a_sender['OutMailPort'];

        if(strtolower($this->a_sender['OutMailProtocol'])=="smtp")
        {
            $this->IsSMTP();  // telling the class to use SMTP

            if($this->a_sender['ConnectionSafety']!=null) {
                $this->SMTPSecure = $this->a_sender['ConnectionSafety'];
            }
            if(strtolower($this->a_sender['OutAuthentication'])=="si"||strtolower($this->a_sender['OutAuthentication'])=='y')
            {
                $this->SMTPAuth = true;
                $this->Username = $this->a_sender['OutUsername'];
                $this->Password = $this->cls_crypt->decryptit($this->a_sender['OutPassword']);
            }
        }

       // var_dump($this->a_sender);

        $this->Subject  = $a_params['subject'];
        $this->Body     = $a_params['body'];

        //echo "<br><br>".$this->Password."<br><br>";
        //var_dump($a_params);
    }

    public function mailboxOpening($validation = true) {

        $enc = '';

        if($this->a_shipment['InMailPort']!=null)
            $enc.= ':'.$this->a_shipment['InMailPort'];

        switch(strtolower($this->a_shipment['InMailProtocol']))
        {
            case 'imap':		$enc.= '/imap';				break;
            case 'pop3':		$enc.= '/pop3';				break;
        }

        switch(strtolower($this->a_shipment['ConnectionSafety']))
        {
            case 'ssl':			$enc.= '/ssl';				break;
            case 'tls':			$enc.= '/tls';				break;
            case 'notls':		$enc.= '/notls';			break;
        }

        switch($validation)
        {
            case false:			$enc.= '/novalidate-cert';	break;
            case true:			$enc.= '/validate-cert';	break;
        }


        $this->mailbox = "{" . $this->a_shipment['InMailServer'] . $enc . "}";
        $this->imap = imap_open($this->mailbox, $this->a_shipment['Username'], $this->cls_file->decryptIt($this->a_shipment['Password']));
    }


    /**
     * returns true after successfull connection
     *
     * @return bool true on success
     */
    public function mailboxIsConnected() {
        return $this->imap !== false;
    }

    /**
     * select given folder
     *
     * @return bool successfull opened folder
     * @param $folder name
     */
    public function mailboxSelectFolder($folder) {
        $result = imap_reopen($this->imap, $this->mailbox . $folder);
        if($result === true)
            $this->folder = $folder;
        return $result;
    }

    public function getMsgParts($uid){

        $this->a_msg['Uid'] = $uid;
        $this->a_msg['Msgno'] = imap_msgno($this->imap, $this->a_msg['Uid']);
        $this->a_msg['Header'] = imap_headerinfo($this->imap, $this->a_msg['Msgno']);
        $this->a_msg['Body'] = imap_body($this->imap, $this->a_msg['Uid'], FT_UID);
        $this->a_msg['Fetchbody'] = imap_fetchbody($this->imap, $this->a_msg['Msgno'],'');
        $this->a_msg['Structure'] = imap_fetchstructure($this->imap, $this->a_msg['Uid'], FT_UID);

        if(substr($this->a_msg['Header']->Subject,0,2)=="=?")
            $this->a_msg['Header']->Subject = iconv_mime_decode($this->a_msg['Header']->Subject,0,"UTF-8");
    }

    function getReceiptFileName($subject, $denomination, $path){
        $subjectParts = explode(":", $subject);
        $this->a_msg['ReceiptType'] = strtoupper(trim($subjectParts[0]));
        $subjectSubParts = explode("[", $subjectParts[1]);
        $this->a_msg['SubjectRef'] = substr($subjectSubParts[1],0,-1);

        $denomination = str_replace(" ","-", $denomination);
        $this->a_msg['ReceiptFileName'] = $this->a_msg['SubjectRef']."_".str_replace(" ","-", $this->a_msg['ReceiptType']).".eml";
        $this->a_msg['ReceiptFile'] = $path.$this->a_msg['ReceiptFileName'];
    }

    function deleteMail(){
        if(strtolower($this->a_shipment['InMailProtocol']) == "imap")
            if(file_exists($this->a_msg['ReceiptFile']))
                return imap_delete($this->imap, $this->a_msg['Uid'], FT_UID);//CANCELLA DEFINITIVO
    }

    function expungeMail(){
        imap_expunge($this->imap);
    }


    /**
     * returns last imap error
     *
     * @return string error message
     */
    public function mailboxGetErrors() {
        return imap_errors();
    }


}


?>