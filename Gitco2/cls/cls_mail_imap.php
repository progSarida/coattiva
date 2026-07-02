<?php
include_once($_SESSION['_path']);
include_once CLS . "/cls_LOG.php";


class Email_reader {

    // imap server connection
    public $conn;
    private $log;

    // inbox storage and inbox message count
    private $inbox_el;
    private $msg_cnt;

    // email login credentials
    private $server;
    private $user;
    private $pass;
    private $port; // adjust according to server settings

    private $folder;
    private $PathSaveFile;

    // connect to the server and get the inbox emails
    function __construct($par_email, $validazione = true) {

        $this->log = new LOG();

        $protocol = ""; 

        if($par_email!=null)
        {
            $cls_cript = new cls_crypt();

            if(isset($par_email->Nome_Utente))
                $this->user = $par_email->Nome_Utente;
            else if (isset($par_email->Username))
                $this->user = $par_email->Username; 

            $this->pass = $cls_cript->decryptIt($par_email->Password);
            
            if(isset($par_email->Server_Posta_Arrivo))
                $mailbox = $par_email->Server_Posta_Arrivo;
            else if (isset($par_email->InMailServer))
                $mailbox = $par_email->InMailServer;

            if(isset($par_email->Porta_Arrivo))
                $this->port = $par_email->Porta_Arrivo;
            else if (isset($par_email->InMailPort))
                $this->port = $par_email->InMailPort;

            if(isset($par_email->Protocollo_Arrivoo))
                $protocol = $par_email->Protocollo_Arrivo;
            else if (isset($par_email->InMailProtocol))
                $protocol = $par_email->InMailProtocol;

            if(isset($par_email->Sicurezza_Connessione))
                $encryption = $par_email->Sicurezza_Connessione;
            else if (isset($par_email->ConnectionSafety))
                $encryption = $par_email->ConnectionSafety;


        }

        $enc = '';

        if($this->port!=null)
            $enc.= ':'.$this->port;

        switch(strtolower($protocol))
        {
            case 'imap':		$enc.= '/imap';				break;
            case 'pop3':		$enc.= '/pop3';				break;
        }

        switch(strtolower($encryption))
        {
            case 'ssl':			$enc.= '/ssl';				break;
            case 'tls':			$enc.= '/tls';				break;
            case 'notls':		$enc.= '/notls';			break;
        }

        switch($validazione)
        {
            case false:			$enc.= '/novalidate-cert';	break;
            case true:			$enc.= '/validate-cert';	break;
        }

        $this->server = "{" . $mailbox . $enc . "}";

        try {
            $this->connect();
        }
        catch(Exception $ex)
        {
            throw new Exception("Connessione fallita");
        }


        //$this->inbox();
        $this->inbox_el = null;
        $this->msg_cnt = 0;
        $this->PathSaveFile = ROOT."/archivio/Posta_Elettronica";
    }

    // close the server connection
    function close() {
        $this->inbox_el = array();
        $this->msg_cnt = 0;

        imap_close($this->conn);
    }

    // open the server connection
    // the imap_open function parameters will need to be changed for the particular server
    // these are laid out to connect to a Dreamhost IMAP server
    function connect() {

        if($this->server == null || $this->server == "") {
            $this->log->error("Dati Server non compilati, impossibile connettersi alla mail!");
            throw new Exception("Connessione fallita");
        }
        if($this->user == null || $this->user == "") {
            $this->log->error("Dati Utente non compilati, impossibile connettersi alla mail!");
            throw new Exception("Connessione fallita");
        }
        if($this->pass == null || $this->pass == "") {
            $this->log->error("Dati Password utente non compilati, impossibile connettersi alla mail!");
            throw new Exception("Connessione fallita");
        }
        $this->conn = imap_open($this->server, $this->user, $this->pass);
        if(!$this->conn)
        {
            $this->log->error("Impossibile connettersi alla mail, Dati connessione alla mail errati --> Server: ".$this->server." -- User: ".$this->user." -- Password: ".$this->pass);
            throw new Exception("Connessione fallita");
        }
    }

    public function selectFolder($folder) {
        $result = imap_reopen($this->conn, $this->server . $folder);
        if($result === true)
            $this->folder = $folder;
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
    function get($msg_index=NULL) {
        if (count($this->inbox_el) <= 0) {
            return array();
        }
        elseif ( ! is_null($msg_index) && isset($this->inbox_el[$msg_index])) {
            return $this->inbox_el[$msg_index];
        }

        return $this->inbox_el[0];
    }

    public function length()
    {
        return $this->msg_cnt;
    }

    public function markDisposable($index)
    {
        imap_delete($this->conn, $this->inbox_el[$index]["id"], FT_UID);
    }

    public function Delete()
    {
        imap_expunge($this->conn);
    }

    // read the inbox
    function inbox() {
        $this->msg_cnt = imap_num_msg($this->conn);

        $in = array();
        for($i = 1; $i <= $this->msg_cnt; $i++) {
            $in[] = array(
                'index'     => $i,
                'header'    => imap_headerinfo($this->conn, $i),
                'body'      => imap_body($this->conn, $i),
                'structure' => imap_fetchstructure($this->conn, $i),
                'fetchbody'     => imap_fetchbody($this->conn, $i,'')
            );
        }

        $this->inbox_el = $in;
    }

    // read the inbox
    function inboxSelected($filter) {

        $ID_MAIL = imap_search($this->conn, $filter, SE_UID);
        

        if(gettype($ID_MAIL)!="array")
        {
            $this->msg_cnt = 0;
            $this->log->warning("Ricerca mail tramite oggetto fallita, nessun id delle mail corrispondenti ritornato");
            throw new Exception("Mail non trovata, nessun id mail ritornato");
        } 

        /* if(!is_array($ID_MAIL))
        {
            $this->msg_cnt = 0;
            $this->log->warning("Ricerca mail tramite oggetto fallita, nessun id delle mail corrispondenti ritornato");
            throw new Exception("Mail non trovata, nessun id mail ritornato");
        }*/


        $this->msg_cnt = count($ID_MAIL);

        $countLeave = 0;
        $in = array();
        for($i = 1; $i <= $this->msg_cnt; $i++) {

            if ($ID_MAIL[$i-1] == "")
            {
                $countLeave++;
                continue;
            }

            $msgno = imap_msgno($this->conn, $ID_MAIL[$i-1]);
            $uid = $ID_MAIL[$i-1];

            $in[] = array(
                'id'        => $uid,
                'header'    => imap_headerinfo($this->conn, $msgno),
                'body'      => imap_body($this->conn, $uid, FT_UID),
                'structure' => imap_fetchstructure($this->conn, $uid, FT_UID),
                'fetchbody'     => imap_fetchbody($this->conn, $msgno,'')
            );
        }

        $this->msg_cnt = $this->msg_cnt - $countLeave;
        $this->inbox_el = $in;
    }

    private function NomeFile()
    {
        $count = 1;
        while(file_exists($this->PathSaveFile."/Mail_".$count.".eml"))
            $count++;

        return "Mail_".$count.".eml";
    }

    public function CreateMailFile($index,$path=null)
    {
        if($path == null){
            $path = $this->PathSaveFile."/".$this->NomeFile();
        }

        $myfile = fopen($path, 'w');
        $testo = $this->inbox_el[$index]['fetchbody'];
        fwrite($myfile, $testo);
        fclose($myfile);
    }

    public function CreateDirMailFile($mail,$c, $tipo_mail, $ritorno = "web")
    {
         return $this->percorsoMail($c, $tipo_mail, $this->cartella_PEC($mail), $ritorno);
    }

    function cartella_PEC($mail)
    {
        $esplodi_oggetto = explode("_", $mail->Oggetto);

        if($esplodi_oggetto[count($esplodi_oggetto)-1] == "NOT".$mail->ID_Collegato)
        {
            $percorso_oggetto = $esplodi_oggetto[0];

            for($i=1;$i<count($esplodi_oggetto);$i++)
            {
                if($i==count($esplodi_oggetto)-1)
                    break;

                $percorso_oggetto.= "_".$esplodi_oggetto[$i];
            }

        }
        else
        {
            $percorso_oggetto = $mail->Oggetto;
        }

        //echo "<h1>".$percorso_oggetto."</h1>";
        return $percorso_oggetto;
    }

    function percorsoMail($c, $tipo_mail,$oggetto, $ritorno = "web")
    {
        $path = SUPER_ROOT."/archivio/Posta_Elettronica/".$c."/".$tipo_mail."/";
        $path.= $oggetto;

        if($ritorno=="server")
        {
            $this->PathSaveFile = $path;
            return $path;
        }
        else if($ritorno=="web")
        {
            $this->PathSaveFile = substr( $path , strpos( $path , "/archivio/" ));
            return substr( $path , strpos( $path , "/archivio/" ));
        }
    }

}

?>
