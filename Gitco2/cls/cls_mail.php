<?php

require_once ( PHPMAILER );
include_once ( CLS."/cls_file.php" );

class cls_mail extends PHPMailer{

    /**
     * shipment parameter
     */
    public $a_shipment = array();

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

    public $a_countReceipt = array();

    public $sendReceipt;
    public $deliveryReceipt;
    public $anomalyReceipt;

    public $cls_file;
    /**
     * Constructor.
     * @param boolean $exceptions Should we throw external exceptions?
     */
    public function __construct(Array $a_shipment, $exceptions = false)
    {
        $this->exceptions = (boolean)$exceptions;
        $this->a_shipment 	= $a_shipment;
        $this->a_countReceipt["missedSend"] = $a_shipment['NoMissedSendReceipt'];
        $this->a_countReceipt["send"] = $a_shipment['NoSendReceipt'];
        $this->a_countReceipt["missedDelivery"] = $a_shipment['NoMissedDeliveryReceipt'];
        $this->a_countReceipt["delivery"] = $a_shipment['NoDeliveryReceipt'];
        $this->a_countReceipt["anomaly"] = $a_shipment['NoAnomalyReceipt'];

        $this->cls_file = new cls_file();

    }

    public function mailCreation($subject)
    {
        //$this->SMTPDebug = 2;

        $this->FromName 	= $this->a_shipment['PublicName'];
        $this->From     	= $this->a_shipment['Address'];
        $this->Host     	= $this->a_shipment['OutMailServer']; // SMTP server
        $this->Port 		= $this->a_shipment['OutMailPort'];

        if(strtolower($this->a_shipment['OutMailProtocol'])=="smtp")
        {
            $this->IsSMTP();  // telling the class to use SMTP
            $this->SMTPSecure 	= $this->a_shipment['ConnectionSafety'];
            if(strtolower($this->a_shipment['OutAuthentication'])=="y")
            {
                $this->SMTPAuth = true;
                $this->Username = $this->a_shipment['OutUsername'];
                $this->Password = $this->cls_file->decryptit($this->a_shipment['OutPassword']);
            }
        }

        $this->Subject  = $subject;
        $this->Body     = $this->a_shipment['MailBody'];
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

    public function unsetReceipt(){
        $this->sendReceipt = "";
        $this->deliveryReceipt = "";
        $this->anomalyReceipt = "";
    }

    public function checkSendReceipt($type){
        switch(strtoupper($type)){
            case "AVVISO DI MANCATA ACCETTAZIONE":
                $this->sendReceipt = "missed";
                $this->a_countReceipt['missedSend']++;
                break;
            case "ACCETTAZIONE":
                $this->sendReceipt = "received";
                $this->a_countReceipt['send']++;
                break;
        }
    }

    public function checkDeliveryReceipt($type){
        switch(strtoupper($type)){
            case "AVVISO DI MANCATA CONSEGNA":
                $this->deliveryReceipt = "missed";
                $this->a_countReceipt['missedDelivery']++;
                break;
            case "CONSEGNA":
                $this->deliveryReceipt = "received";
                $this->a_countReceipt['delivery']++;
                break;
        }
    }


    public function checkAnomaly($type){
        switch(strtoupper($type)){
            case "ANOMALIA MESSAGGIO":
                $this->anomalyReceipt = "received";
                $this->a_countReceipt['anomaly']++;
                break;
        }
    }

    /**
     * returns last imap error
     *
     * @return string error message
     */
    public function mailboxGetErrors() {
        return imap_errors();
    }



    public function getTextReceipt($value){

        switch($value){
            case "":
                return "--";
                break;
            case "received":
                return "<span>ricevuta</span>";
                break;
            case "missed":
                return "<span class='color_red'>mancata</span>";
                break;
            default:
                return "<span class='color_comune'>".$value." (sconosciuta)</span>";
                break;
        }
    }

    public function getTextAnomaly($value){
        switch($value){
            case "":
                return "--";
                break;
            case "received":
                return "<span class='color_red'>ricevuta</span>";
                break;
            default:
                return "<span class='color_comune'>".$value." (sconosciuta)</span>";
                break;
        }
    }


}


?>