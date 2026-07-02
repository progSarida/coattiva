<?php
if (!session_id()) session_start();


include_once($_SESSION['_path']);
include_once(ROOT . "/_parameter.php"); //dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_LOG.php";
include_once CLS . "/cls_phpmailer.php";

$log = new LOG();
$db = new cls_db();
$help = new cls_help();

$id_proc = $help->getVar("id_proc");
$cod_cat = $help->getVar("cod_cat");
$body = $help->getVar("body");

$query_stragiudiziale = "	SELECT s.*, b_u.Request_Date AS Request_Date, b_u.Banca_ID AS Banca_ID,  e_g.Denominazione, b.PEC as PEC  
                            FROM      stragiudiziali AS s 
							    JOIN  banca_utente AS b_u ON b_u.Stragiudiziale_Id = s.Id
								JOIN  enti_gestiti as e_g ON  e_g.CC = s.CC
                                JOIN  banca as b on b.ID = s.Banca_Id and b.ID = b_u.Banca_ID
							WHERE Procedure_Id =" . $id_proc . " AND Data_Spedizione IS NULL 
                            AND   COALESCE(trim(b.PEC), '') <> '' 
                            AND   b.Tipo_Banca = 'sede' ";

$a_stragiudiziale = $db->getResults($db->ExecuteQuery($query_stragiudiziale)); //1
//var_dump($a_stragiudiziale);die;

if (count($a_stragiudiziale) > 0) {

    try {
        $a_params = array(
            "subject" => "",
            "body" => $body
        );

        $query = "  SELECT `Address` AS `Address`, PublicName AS PublicName, OutMailServer AS OutMailServer, OutMailPort AS OutMailPort, 
                        OutMailProtocol AS OutMailProtocol, ConnectionSafety AS ConnectionSafety, OutAuthentication AS OutAuthentication, 
                    OutUsername AS OutUsername, OutPassword AS OutPassword " .
            " FROM user_emails  " .
            " WHERE MailType = 'PEC' AND User_Id= " . $_SESSION['aut_progr'];

        $a_sender = $db->getArrayLine($db->ExecuteQuery($query));

        if (is_null($a_sender)) {
            $log->error('PARAMETRI EMAIL MITTENTE ' . $_SESSION['username'] . ' ASSENTI. CONTATTARE IT PER AGGIUNGERE I DATI');
            echo json_encode(['esito' => 'KO', 'message' => 'PARAMETRI EMAIL MITTENTE ' . $_SESSION['username'] . ' ASSENTI. CONTATTARE IT PER AGGIUNGERE I DATI']);
            die;
        }


        $stragiudiziale_Id = array();

        //var_dump($a_sender);
        //die;

        foreach ($a_stragiudiziale as $stragiudiziale) {

            $a_params['subject'] = "Richiesta dichiarazione stragiudiziale " . $cod_cat ."_".$stragiudiziale['Id']."_". str_replace("-", "", date('Y-m-d'));

            $cls_mail = new cls_phpmailer($a_sender);

            //$cls_mail->isSMTP();

            $cls_mail->SMTPDebug = 0;

            //var_dump($cls_mail->isError());die;
    
            //BLOCCATO INIO EMAIL
            $cls_mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );


    
            $cls_mail->mailCreation($a_params);

            $cls_mail->setFrom($a_sender['Address']);

            $cls_mail->isHTML(false);
            
            //$cls_mail->addAddress($a_sender['Address']);
            $cls_mail->addAddress($stragiudiziale['PEC']);

            $tax_type = !is_null($stragiudiziale["Tipo_Riscossione"]) ? $stragiudiziale["Tipo_Riscossione"] : 'COMPLETO';

            $path = STRAGIUDIZIALE . "/" . $stragiudiziale['Id'];

            $nameFile = "Stragiudiziale_Banca_" . $stragiudiziale['CC'] . "_" . $stragiudiziale['Banca_ID'] . "_" . $tax_type . "_" . $stragiudiziale["Request_Date"] . ".pdf";
            $filename = "Elenco_Stragiudiziale_Banca_" . $stragiudiziale['CC'] . "_" . $stragiudiziale["Banca_ID"] . "_" . $tax_type . "_" . $stragiudiziale["Request_Date"] . ".xlsx";


            $cls_mail->addAttachment($path . "/" . $nameFile);
            $cls_mail->addAttachment($path . "/" . $filename);

            //$cls_mail->preSend();
            //$message = $cls_mail->getSentMIMEMessage();
            //var_dump($message);die;

            $inviata = @$cls_mail->send();

            if (!$inviata) {
                $log->error("Errore di spedizione della mail " . $cls_mail->ErrorInfo);
            } else {
                $log->info("Mail con oggetto '" . $a_params['subject'] . "' inviata correttamente.\nDa mittente: ".$a_sender["Address"]);

                $query_update_stragiudiziali = " UPDATE stragiudiziali SET Data_Spedizione='" . date('Y-m-d') . "', Recipient_Pec  ='" . $a_sender['Address'] . "', Sender_Pec ='" . $a_sender['Address'] . "', Ricevuta_Accettazione = 'attesa', Ricevuta_Consegna = 'attesa', Oggetto_Email = '" . $a_params['subject'] . "'  WHERE Id = " . $stragiudiziale['Id'];

                if (!mysqli_query($db->conn, $query_update_stragiudiziali)) {
                    $log->error("Errore aggiornamenti non riusciti");
                } else {
                    $stragiudiziale_Id[] = $stragiudiziale['Id'];
                }
            }
        }

        $id_stra = implode(",", $stragiudiziale_Id);
        $query_update_b_u = " UPDATE banca_utente SET `Request_Date`='" . date('Y-m-d') . "' WHERE Stragiudiziale_Id IN (" . $id_stra . ")";
        mysqli_query($db->conn, $query_update_b_u);

        $query = "UPDATE procedures SET Procedure_Status_Id = 2 WHERE Id = ".$id_proc;
        mysqli_query($db->conn, $query);

        echo json_encode(['esito' => 'OK', 'message' => "OPERAZIONE COMPLETATA"]);
    } catch (Exception $ex) {

        echo json_encode(['esito' => 'KO', 'message' => 'ERRORE CATCH']);
    }
} else {
    echo json_encode(['esito' => 'KO', 'message' => 'DATI ASSENTI']);
}
