<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");
include(CLS."/cls_phpmailer.php");

include_once CLS . "/cls_ruolo.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_LOG.php";

$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");
$DocumentType = $cls_help->getVar("tipo");
$printer = $cls_help->getVar("stampatore");
$arrayMail = json_decode($cls_help->getVar("arrayTerzi"));

?>

<script>

    function startBar(){
        //alert("start");
        $('#progressbar').progressbar({
            value: false
        });
        $( "#barlabel" ).text("Inizio elaborazione...");
    }

    function updateBar(valore){
        //alert(valore);
        $( "#progressbar" ).progressbar({value: parseInt(valore) });
        $( "#barlabel" ).text( valore + "%" );
    }

    function noResultsBar(){
        $( "#progressbar" ).progressbar({value: 100 });
        $( "#barlabel" ).text("Nessun risultato trovato");
    }

    function endBar(value, value2){
        $( "#progressbar" ).progressbar({value: 100 });
        $( "#barlabel" ).text( value );

        if(value2!=""){
            sleep(1000);

            var id_json = JSON.stringify(value2, null, 2);
            /************************************** da decommentare *************************************************************/
            //window.location = "info_PEC.php?tipo=<?= $DocumentType; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>&idPigno="+id_json;
            //window.name = "Stampa";
            //window.open(value2,"Stampa");
        }
    }

    function startMerge(){

        $('#progressbar2').progressbar({
            value: false
        });
        $( "#barlabel2" ).text("Inizio elaborazione...");
    }

    function updateMerge(valore){
        $( "#progressbar2" ).progressbar({value: parseInt(valore) });
        $( "#barlabel2" ).text( valore + "%" );
    }

    function endMerge(value)
    {
        $( "#progressbar2" ).progressbar({value: 100 });
        $( "#barlabel2" ).text( value );
    }
</script>

<table class="table_interna text_center">
    <tr>
        <td><span class="titolo font18 text_center">Invio PEC</span></td>
    </tr>
    <tr>
        <td><div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div></td>
    </tr>
    <tr><td><br></td></tr>
    <tr>
        <td><div class="table_interna text_center" id="progressbar2" style="height:55px;"><div class="text_center" id="barlabel2"></div></div></td>
    </tr>
</table>

<?php

try{

$log = new LOG();

    flush();	ob_flush();
    echo "<script>startBar();</script>";
    flush();	ob_flush();		flush();	ob_flush();

$Utils = new cls_Utils();
$cls_ruolo = new cls_ruolo();
$cls_ruolo->getTypeDetails($DocumentType);


$query = "SELECT * FROM ufficio_giudiziario WHERE CC=\"".$c."\" AND Tipo='tribunale'";
$a_tribunale = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

$query = "SELECT * FROM ufficio_giudiziario WHERE CC=\"".$a_tribunale["CC_Ufficio"]."\" AND Tipo='istituto'";
$a_ivg = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));


$allID = "";

for($i = 0; $i < count($arrayMail); $i++){
    $allID .= $arrayMail[$i]->ID_Pigno;
    if($i < count($arrayMail)-1)
        $allID .= " OR ID = ";
}
$query = "SELECT * FROM v_pignoramento WHERE ID = ".$allID;
$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

//$a_sender = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT * FROM user_emails WHERE User_Id=".$_SESSION['aut_progr']));//1
//$a_recipient = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT * FROM printer WHERE ID=".$stampatore));
//$a_publicReceiving = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT * FROM gestore WHERE CC='".$c."'"));

//echo $_SESSION['aut_progr'];


if($DocumentType == "Ingiunzione")
{
    $cartella = "Ingiunzioni";
    //$prefisso = "Ingiunzione_";
}
else if($DocumentType == "Avviso di intimazione ad adempiere")
{
    $cartella = "Avvisi_di_intimazione";
    //$prefisso = "Avviso_di_intimazione_";
}
else if($DocumentType == "Sollecito di pagamento")
{
    $cartella = "Solleciti";
    //$prefisso = "Sollecito_";
}
else if($DocumentType == "Sollecito pre ingiunzione" || $DocumentType =="SOLL_PRE")
{
    $cartella = "Solleciti_Pre_Ingiunzione";
    //$prefisso = "sollecitoPreIngiunzione_";
}
else if($DocumentType == "Avviso di messa in mora" || $DocumentType =="AV_MORA")
{
    $cartella = "Avvisi_Messa_In_Mora";
    //$prefisso = "avvisoMessaInMora_";
}
else if($DocumentType == "Pignoramento di beni mobili registrati" || $DocumentType == "veicolo")
{
    $cartella = "Pignoramenti/Veicolo";
    //$prefisso = "PignoramentoVeicolo_";
}
else if($DocumentType == "Pignoramento presso banca" || $DocumentType == "banca")
{
    $cartella = "Pignoramenti/Presso_Terzi/Banca";
    //$prefisso = "PignoramentoBanca_";
}
else if($DocumentType == "Pignoramento presso datore di lavoro" || $DocumentType=="lavoro")
{
    $cartella = "Pignoramenti/Presso_Terzi/Datore_di_Lavoro";
    //$prefisso = "PignoramentoLavoro_";
}
else if($DocumentType == "Preavviso fermo" || $DocumentType=="preav_fermo")
{
    $cartella = "Pignoramenti/Preavviso_Fermo";
    //$prefisso = "PignoramentoLavoro_";
}



    $query = "SELECT * FROM enti_gestiti WHERE CC = '".$c."'";
    $comune = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"enti_gestiti");

//$comune->Gestore_ID;
    if( $comune->Gestore_ID != 0 ) {
        $query = "SELECT * FROM gestore WHERE ID = '" . $comune->Gestore_ID . "'";
        $gestore = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"gestore");
    }
    else {
        $query = "SELECT * FROM gestore WHERE ID = '" . $comune->Info_ID . "'";
        $gestore = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"gestore");
    }

    if($gestore->Tipo == "Concessionario")
        $firma_PEC = "Il gestore del servizio riscossione ".$gestore->Denominazione;
    else
        $firma_PEC = "Il ".$gestore->Denominazione;

    $contatoreTerzi = 0;
    $msg = "";
    $error = 0;

    if(count($result)==0)
    {
        flush();	ob_flush();
        echo "<script>noResultsBar();</script>";
        flush();	ob_flush();		flush();	ob_flush();

        $error = 2;
        $log->warning("Nessun pignoramento trovato");
        throw new Exception("Nessun pignoramento trovato");
    }

    $query = "SELECT Indirizzo_Email AS Address, Nome_Visualizzato AS PublicName, Server_Posta_Uscita AS OutMailServer, Porta_Uscita AS OutMailPort, 
                    Protocollo_Uscita AS OutMailProtocol, Sicurezza_Connessione AS ConnectionSafety, Autenticazione_Uscita AS OutAuthentication, 
                    Nome_Utente_Uscita AS OutUsername, Password_Uscita AS OutPassword 
                    FROM parametri_email 
                    WHERE CC='".$c."' AND Tipo_Email = 'PEC' AND Tipo_Riscossione = 'GENERALE'";
    $a_sender = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

    if($a_sender == null)
    {
        $log->error("Mail di invio inesistente");
        die;
    }

    for($i=0; $i < count($result); $i++){


        $bar1 = (100*$i)/count($result);

        flush();	ob_flush();
        echo "<script>updateBar(".$bar1.");</script>";
        flush();	ob_flush();		flush();	ob_flush();




        if($result[$i]['Data_Stampa'] == null || $result[$i]['Data_Stampa'] == "")
        {
            $error = 1;
            throw new Exception("Errore, prima di eseguire l'invio delle PEC, eseguire la definitiva per generare i PDF!");
        }


        if($result[$i]["Tipo"] == "terzi" || $result[$i]["Tipo"] = "veicolo")
        {
            $query = "SELECT notifica_atto.ID as ID_Notifica_Atto, notifica_atto.*, pignoramento_presso_terzi.* FROM notifica_atto 
            LEFT JOIN pignoramento_presso_terzi on notifica_atto.ID_Collegamento = pignoramento_presso_terzi.ID  
            WHERE notifica_atto.Tipo_Atto_Notificato = 'pignoramento' AND notifica_atto.Atto_Notificato_ID = ".$result[$i]["ID"]." AND notifica_atto.CC = '".$c."'";
            //AND notifica_atto.Tipo_Notifica <> 'debitore'";
            $TERZI = $cls_db->getResults($cls_db->ExecuteQuery($query));
            //echo $query;

            //$query = "select ID from notifica_atto where Atto_Notificato_ID = ".$result[$i]["ID"]." AND Tipo_Notifica = 'debitore' AND Modalita_Stampa = 'pec'";
            //$PecDebitore = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

            flush();	ob_flush();
            echo "<script>startMerge();</script>";
            flush();	ob_flush();		flush();	ob_flush();

            for($x=0; $x < count($TERZI); $x++)
            {
                $bar2 = (100*$x)/count($TERZI);

                flush();	ob_flush();
                echo "<script>updateMerge(".$bar2.");</script>";
                flush();	ob_flush();		flush();	ob_flush();

                $flag = false;
                if($TERZI[$x]["Tipo_Terzi"]=="banca") {
                    $query = "SELECT * FROM banca WHERE ID = '" . $TERZI[$x]["Terzo_ID"] . "' AND CC = '*****'";
                    $TERZI[$x]["Dati_Terzo"] = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"banca");
                }
                else if($TERZI[$x]["Tipo_Terzi"]=="lavoro")
                {
                    $query = "SELECT * FROM v_utente WHERE Utente_ID = " . $TERZI[$x]["Terzo_ID"];
                    $TERZI[$x]["Dati_Terzo"] = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
                }

                 if($TERZI[$x]["Modalita_Stampa"] == "pec") {

                     $Nome_Ditta = $result[$i]["Cognome_Ditta"]." ".$result[$i]["Nome"];
                     $CF_PI = $result[$i]["CF_PI"];
                     $Password = $result[$i]["CF_PI"];



                     if($TERZI[$x]["Tipo_Notifica"] == "terzi" || $TERZI[$x]["Tipo_Notifica"] == "veicolo")
                     {
                         $body = "Allegato alla presente comunicazione trovate un documento esecutivo riferito ";
                         $body.= "a ".$Nome_Ditta." - ".$CF_PI."\n\n";
                         $body.= " protetto dalla password ".$Password.".\n\n";

                         $body.= "Trattasi di copia di documento precedentemente inviata.\n\n";

                         $body.= "Qualora il soggetto suindicato non intrattenesse con Codesta azienda ";
                         $body.= "rapporti di conto corrente, deposito titoli, oppure rapporti creditizi a diverso titolo ";
                         $body.= "ragione o causa, Vi preghiamo di comunicarcelo, stesso mezzo e di ritenere ";
                         $body.= "nulla la comunicazione inviatavi in allegato e quindi provvedere a cestinarla, ";
                         $body.= "senza aprirla, nel rispetto della privacy.\n\n";
                         $body.= "Distinti saluti. F.to ".$firma_PEC;

                         $tipo_copia = "copia_terzo_" . $contatoreTerzi;

                         switch($DocumentType)
                         {
                             case "banca":
                                 if(!isset($TERZI[$x]["Dati_Terzo"])) {
                                     $log->error("Dati incoretti in db Tabella banca, ID = " . $TERZI[$x]["Terzo_ID"]);$tipoMail = "NotFound";$PEC = "";
                                 }
                                 else {
                                     if ($TERZI[$x]["Dati_Terzo"]["PEC"] != null && $TERZI[$x]["Dati_Terzo"]["PEC"] != "") {$tipoMail = "PEC";$PEC = $TERZI[$x]["Dati_Terzo"]["PEC"];}
                                     else {$tipoMail = "NotFound";$PEC = "";}
                                 }
                                 break;
                             case "lavoro":
                                 if(!isset($TERZI[$x]["Dati_Terzo"])) {
                                     $log->error("Dati incoretti in db Tabella v_utente, Utente_ID = " . $TERZI[$x]["Terzo_ID"]);$tipoMail = "NotFound";$PEC = "";
                                 }
                                 else {
                                     if ($TERZI[$x]["Dati_Terzo"]["Utente_PEC"] != null && $TERZI[$x]["Dati_Terzo"]["Utente_PEC"] != "") {$tipoMail = "PEC";$PEC = $TERZI[$x]["Dati_Terzo"]["Utente_PEC"];}
                                     else {$tipoMail = "NotFound";$PEC = "";}
                                 }
                                 break;
                             case "preav_fermo":
                             case "veicolo":
                                 if($a_ivg["PEC"]!=null && $a_ivg["PEC"] != "") {$tipoMail = "PEC"; $PEC = $a_ivg["PEC"];}
                                 else {$tipoMail = "NotFound"; $PEC = "";}
                                 break;
                             default: $log->error("Dati relativi alla tipologia del pignoramento assenti o errati, (dati passati dall'url).");
                                    break;
                         }

                         $contatoreTerzi++;

                     }else if($TERZI[$x]["Tipo_Notifica"] == "debitore"){

                         $body = "Allegato alla presente comunicazione trovate un documento esecutivo riferito ";
                         $body.= "a ".$Nome_Ditta." - ".$CF_PI."\n\n";
                         $body.= "Qualora il documento suindicato non si riferisse a Voi, ";
                         $body.= "Vi preghiamo di comunicarcelo, stesso mezzo e di ritenere ";
                         $body.= "nulla la comunicazione inviatavi in allegato e quindi provvedere a cestinarla, ";
                         $body.= "senza aprirla, nel rispetto della privacy.\n\n";
                         $body.= "Distinti saluti. F.to ".$firma_PEC;

                         $tipo_copia = "copia_debitore";

                         $tipoMail = "PEC";
                         $PEC = $result[$i]["Utente_PEC"];
                         if($result[$i]["Utente_PEC"] == null || $result[$i]["Utente_PEC"] == "") {
                             $tipoMail = "Mail";
                             $PEC = $result[$i]["Utente_Email"];
                         }
                     }

                     if($PEC=="")
                     {
                         $log->error("Pec non trovata mail non inviata. Riferimenti: CC ".$c.", AnnoCron/IDCron: ".$result[$i]['Anno_Cronologico']."/".$result[$i]['ID_Cronologico'].", Data stampa ".$result[$i]['Data_Stampa']);
                         continue;
                     }



                     $cls_mail = new cls_phpmailer($a_sender);

                     //Variabile aggiunta da me in phpMailer, (nelle versioni nuove della libreria ci dovrebbe già essere)
                     $cls_mail->SMTPOptions = array(
                         'ssl' => array(
                             'verify_peer' => false,
                             'verify_peer_name' => false,
                             'allow_self_signed' => true
                         )
                     );

                     //$cls_mail->SMTPDebug = 2;

                     $fileName = $cls_ruolo->a_docDetails['finalFileName'] . $c . "_" . $result[$i]['Anno_Cronologico'] . "_" . $result[$i]['ID_Cronologico'] . "_" . $result[$i]['Data_Stampa'] . "_" . $tipo_copia . ".pdf";

                     $file = ATTI . "/" . $c . "/" . $cartella . "/STAMPE DEFINITIVE/" . $fileName;
                     //echo "<br>".$file."<br>";

                    if($tipoMail=="PEC") $ricevuta_consegna = "attesa";
                    else if($tipoMail=="Mail") $ricevuta_consegna = "no";

                     $ID_Collegato = $TERZI[$x]["ID_Notifica_Atto"];
/************************************************* elimina dopo test ***************************************************************************/
                     $AggiunteOggettoTest = "_Test_Programmatore_Sarida_Funzionalita_PEC";
/*********************************************************************** ***********************************************************************/

                     $Oggetto = "Pignoramento_".$c."_".$result[$i]['ID_Cronologico']."_".$result[$i]['Anno_Cronologico']."_".$result[$i]['Data_Stampa']."_".$tipo_copia."_NOT".$ID_Collegato.$AggiunteOggettoTest;


                     $query = "select * from notifica_atto as A join email_inviate as E on E.ID_Collegato = A.ID where A.Atto_Notificato_ID = ".$result[$i]["ID"];
                     $controlMail = $cls_db->getResults($cls_db->ExecuteQuery($query));

                     for($y = 0; $y < count($controlMail); $y++)
                         if($controlMail[$y]["Oggetto"] == $Oggetto)
                         {
                             $error = 2;
                             $log->warning("Mail ({$Oggetto}) già inviata.");
                             $msg = "Mail già inviata. ";
                             $flag = true;
                             break;
                         }

                     /******************************** da riabilitare *******************************************/
                     if($flag)
                        continue;
/***************************************************** ***********************************************************/
                     $a_params = array(
                         "subject" => $Oggetto,
                         "body" => $body
                     );

                     $cls_mail->mailCreation($a_params);

                     $cls_mail->addAddress($PEC);
                     //$cls_mail->addAddress("gianluca.virdis8901@gmail.com");

                     //echo "<br><br>".$PEC."<br><br>";
                     //die;
                     $cls_mail->addAttachment($file);
                if($PEC != "")
                     if(!$cls_mail->send()){

                         $error = 1;
                         $text = "";
                         if($PEC=="") $text = " Mail ricevente nulla.";
                         $log->error("Errore di spedizione della mail ({$Oggetto}).".$text."\n".$cls_mail->ErrorInfo);
                         //echo "<br>Errore di spedizione della mail ({$Oggetto}).".$text;
                         $msg = "Errore di spedizione della mail. ";
                         continue;

                         //echo "<script>window.location = document.referrer + '&error={$error}&msg={$msg}';</script>";
                     }
                     else{

                         $log->info("Mail con oggetto '{$Oggetto}' inviata correttamente");

                         $save = new stdClass();

                         $save->Partita_ID = $result[$i]["Partita_ID"];
                         $save->Utente_ID = $result[$i]["Utente_ID"];
                         $save->Oggetto = $Oggetto;
                         $save->CC = $c;
                         $save->Mail_Sorgente = $a_sender['Address'];
                         $save->Tipo_Sorgente = 'PEC';
                         $save->Mail_Destinatario = $PEC;
                         $save->Tipo_Destinatario = $tipoMail;
                         $save->Data_Invio = date("Y-m-d");
                         $save->Ricevuta_Accettazione = 'attesa';
                         $save->Ricevuta_Consegna = $ricevuta_consegna;
                         $save->Table_Collegata = 'notifica_atto';
                         $save->ID_Collegato = $ID_Collegato;

                         if(!$cls_db->DbSave($Utils->GetObjectQuery($save,"email_inviate")))
                         {
                             $error = 1;
                             $log->error("Errore salvataggio dati della mail ({$Oggetto}).");
                             $msg = "Errore salvataggio dati della mail. ";
                             continue;
                         }
                     }
                 }
            }
            flush();	ob_flush();
            echo "<script>endMerge('Mail inviata!');</script>";
            flush();	ob_flush();		flush();	ob_flush();

        }
    }
/******************* DA DECOMMENTARE ***********************/
    if($error!=0) throw new Exception($msg);
    else $msg = "Documenti inviati correttamente";
//die;
    flush();	ob_flush();
    echo "<script>endBar('Operazione completata', ".json_encode($arrayMail)." );</script>";
    flush();	ob_flush();		flush();	ob_flush();

    //echo "<script>window.location = document.referrer + '?error={$error}&msg={$msg}'</script>";
    echo "<script>window.location.href = 'gestione_stampe.php?printType=html&docType={$DocumentType}&c={$c}&a={$a}&error={$error}&msg={$msg}'</script>";
}
catch(Exception $ex)
{
    echo "<script>window.location ='gestione_stampe.php?printType=html&docType={$DocumentType}&c={$c}&a={$a}&error={$error}&msg={$ex->getMessage()}';</script>";
}

?>
