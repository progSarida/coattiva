<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC."/headerAjax.php");
//include(INC."/menu.php");

include_once CLS . "/cls_DateTime.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/chilkat_9_5_0.php";
//include_once "c:/wamp64/bin/php/php7.4.26/ext/chilkat_9_5_0.php";
include_once CLS . "/cls_LOG.php";
include_once CLS . "/cli_function.php";

define('CHILKAT_CODICE_LICENZA', 'OVUNQU.CB1082022_2YL7hddkj374');
define('INIPEC_URL', 'https://fpecws.infocamere.it/fpec/ServizioFornituraPec?wsdl');
define('INIPEC_NS', 'http://www.w3.org/2004/08/xop/include');

$cls_utils = new cls_Utils();
$cls_db = new cls_db();
$log = new LOG();

function callInipecDownload($rs,$utils,$username,$password,$idRichiesta,$messageObject,$c,$a,$cc_el,$cod_cat,$last_el_id,$tipoatto){
    $glob=new CkGlobal();
    $success = $glob->UnlockBundle(CHILKAT_CODICE_LICENZA);

    $http = new CkHttp();
    $http->put_SessionLogFilename(SUPER_ROOT."/servizi.log");
    $soapXml = new CkXml();
    $soapXml->put_EmitXmlDecl(false);
    $soapXml->put_Tag('ws:richiestaScaricoFornituraPec');
    $soapXml->AddAttribute('xmlns:ws', 'http://ws.fpec.gemo.infocamere.it');
    $soapXml->NewChild2('tokenRichiestaInfocamere|tipoRichiesta', 'FORNITURA_FPEC');
    $soapXml->NewChild2('tokenRichiestaInfocamere|idRichiesta',str_pad($idRichiesta,10,"0",STR_PAD_LEFT));
    $soapXml->GetRoot2();
    $attributes=array('xmlns:soap'=>'http://schemas.xmlsoap.org/soap/envelope/');
    $attributes['xmlns:ws']='http://ws.fpec.gemo.infocamere.it';
    $soapXml = createSoapXml($soapXml,null,$attributes);
    $xmlBody = $soapXml->getXml();
    $req = new CkHttpRequest();
    $req->put_HttpVerb('POST');
    $req->put_Path('/fpec/ServizioFornituraPec');
    $req->AddHeader('SOAPAction', 'scaricoFornituraPec');
    $req->AddHeader('Authorization', "Basic ".base64_encode($username.":".$password));
    $req->LoadBodyFromString($xmlBody,"utf-8");
    $xmlResponse = new CkXml();
    $resp = $http->SynchronousRequest('fpecws.infocamere.it', 443, true, $req);
    if ($http->get_LastMethodSuccess() === false) {
        echo $http->lastErrorText();
        exit();
    }
    $xmlResponse->LoadXml($resp->bodyStr());
    $mime = new CkMime();
    $respBody = new CkBinData();
    $resp->GetBodyBd($respBody);
    $success = $mime->LoadMimeBd($respBody);
    if($mime->get_LastMethodSuccess()===false)
        echo $mime->lastErrorText();
    $part1 = $mime->GetPart(1);
    $zipData = new CkBinData();
    if($part1==null){
        $messageObject->warning("Impossibile scaricare la richiesta, provare più tardi");
//        $_SESSION['Message'] = $messageObject->getMessagesString();
        header("location: prc_inipec.php?c=".$c."&a=".$a."&cc_el=".$cc_el."&codcat=".$cod_cat."&el=".$last_el_id."&tipoatto=".$tipoatto."&msg=Impossibile scaricare la richiesta, provare più tardi!&error=2");
        die;
    }
    $pathFILE = $utils->crea_dir(SUPER_ROOT."/archivio/inipec");

    $success = $part1->GetBodyBd($zipData);
    $success = $zipData->WriteFile($pathFILE."/response_{$username}_{$idRichiesta}.zip");
    $zip = new ZipArchive();
    $dataRows=array();
    if ($zip->open($pathFILE."/response_{$username}_{$idRichiesta}.zip", ZipArchive::CREATE)) {
        $esito =$xmlResponse->getChild(0)->GetChild(0)->GetChild(0)->GetChild(0)->getChildContent("esito");
        if($esito=='true')
            $esitoValue="OK";
        else
            $esitoValue="KO";

        $rs->Start_Transaction();
        $rs->Begin_Transaction();

        $query = "UPDATE ini_pec_request SET EsitoFornitura = '".$esitoValue."' WHERE IdRichiesta='".$idRichiesta."' and UserName='".$username."'";
        if(!$rs->ExecuteQuery($query)){
            $rs->Rollback();
            header("location: prc_inipec.php?c=".$c."&a=".$a."&cc_el=".$cc_el."&codcat=".$cod_cat."&el=".$last_el_id."&tipoatto=".$tipoatto."&msg=Errore, impossibile aggiornare esito sulla tabella ini_pec_request!&error=1");
        }

        $rs->End_Transaction();

        $fileName = $zip->getNameIndex(0);
        //die;
        $zip->extractTo($pathFILE);
        //$data = str_replace('"','',$zip->getFromIndex(0));
        $zip->close();

        if (($data = fopen($pathFILE."/".$fileName, "r")) !== FALSE) {
            $csv=array();
            $numRow = 0;
            while (($row = fgetcsv($data, 0, "~")) !== FALSE) {
                if($numRow>0)
                    $csv[] = $row;
                $numRow++;
            }
            fclose($data);
        }
        unlink($pathFILE."/".$fileName);

        /*$rows=explode(PHP_EOL,$data);
        $csv=array();
        foreach($rows as $row)
            array_push($csv,explode("~",$row));*/
        return $csv;
    }
}


$a = $cls_help->getVar("a");
$c = $cls_help->getVar("c");
$cc_el = $cls_help->getVar("cc_el");
$cod_cat = $cls_help->getVar('codcat');
$last_el_id =  $cls_help->getVar('el');
$tipoatto = $cls_help->getVar('tipoatto');

$CC = $cc_el==null?$c:$cc_el;

$query_par =  "   SELECT * FROM parametri_annuali WHERE CC = '" . $CC . "' AND Anno=" . date('Y');
$params_arr = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_par));

$query = "SELECT UserName, Password, INIPECPasswordExpiration from ini_pec_processing where UserId=".$_SESSION['aut_progr'];
$username_pw = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"ini_pec_processing");
$passwordExpiration = $username_pw['INIPECPasswordExpiration'];

if($passwordExpiration!=null && strtotime(date('Y-m-d'))>strtotime($passwordExpiration)) {
    echo "<script>location.href = 'prc_inipec.php?c=".$c."&a=".$a."&cc_el=".$cc_el."&codcat=".$cod_cat."&el=".$last_el_id."&tipoatto=".$tipoatto."&checkMissing=yes&msg=Password scaduta, per modificarla premere il lucchetto verde&error=2' </script>";
    //header("location: prc_inipec.php?c=".$c."&a=".$a."&msg=Password scaduta, modificarla presso http://telemaco.infocamere.it.&error=2");
    die;
}

$username = $username_pw["UserName"];
$password = $username_pw["Password"];

$query = "select * from ini_pec_request where EsitoRichiesta='true' and EsitoFornitura is null and UserName='{$username}' ";
$table_row = $cls_db->getResults($cls_db->ExecuteQuery($query));

$countRes = count($table_row);
for($i=0; $i < $countRes; $i++){
    if($table_row[$i]["IdRichiesta"]!=null){
        $csv = callInipecDownload($cls_db,$cls_utils,$username,$password,$table_row[$i]["IdRichiesta"],$log,$c,$a,$cc_el,$cod_cat,$last_el_id,$tipoatto);
        foreach($csv as $row){
            //echo "<br>trova pec <br>";
            //print_r($row);
            //die;
            if(!isset($row[2]))
                break;
            if($row[8]=='OK')
                $pec=$row[7];
            else if($row[18]=='OK')
                $pec=$row[17];
            else $pec=null;

            $codiceFiscale = str_replace('"', '',$row[2]);
            $pec = str_replace('"', '',$pec);
            //echo "<br><br>$pec - $codiceFiscale<br><br>";
            if (!empty($pec))
            {
                $cls_db->Start_Transaction();
                $cls_db->Begin_Transaction();

                $whereUtenti = "";
                $query = "SELECT ID, PEC FROM utente WHERE Codice_Fiscale = '".$codiceFiscale."' OR Partita_Iva = '".$codiceFiscale."'";
                $a_utenti = $cls_db->getResults($cls_db->ExecuteQuery($query));
                foreach($a_utenti as $a_utente){
                    if(!empty($a_utente['PEC']) && $a_utente['PEC']!=$pec){
                        $query = "INSERT INTO storico_pec (Utente_Id,Pec,Data_Cambio) 
                                VALUES (".$a_utente['ID'].",'".$a_utente['PEC']."','".date('Y-m-d')."')";

                        if (!$cls_db->ExecuteQuery($query)) {
                            $cls_db->Rollback();
                            header("location: prc_inipec.php?c=" . $c . "&a=" . $a . "&cc_el=".$cc_el."&codcat=".$cod_cat."&el=".$last_el_id."&tipoatto=".$tipoatto."&checkMissing=yes&msg=Errore, impossibile aggiornare lo storico!&error=1");
                        }
                    }

                    $query = "UPDATE utente SET PEC = '" . $pec . "', InipecLoaded='".date('Y-m-d')."' WHERE ID = " . $a_utente["ID"];
                    if (!$cls_db->ExecuteQuery($query)) {
                        $cls_db->Rollback();
                        header("location: prc_inipec.php?c=" . $c . "&a=" . $a . "&cc_el=".$cc_el."&codcat=".$cod_cat."&el=".$last_el_id."&tipoatto=".$tipoatto."&checkMissing=yes&msg=Errore, impossibile salvare la PEC sulla tabella utente!&error=1");
                    }

                    if($whereUtenti=="")
                        $whereUtenti.= $a_utente["ID"];
                    else
                        $whereUtenti.= ", ".$a_utente["ID"];
                }

                if(count($a_utenti)>0){
                    $query = "UPDATE ini_pec_request_pec SET Pec = '" . $pec . "' WHERE CodiceFiscale = '" . $codiceFiscale . "' AND IdRichiesta='".$table_row[$i]["IdRichiesta"]."'";
                    if (!$cls_db->ExecuteQuery($query)) {
                        $cls_db->Rollback();
                        header("location: prc_inipec.php?c=" . $c . "&a=" . $a . "&cc_el=".$cc_el."&codcat=".$cod_cat."&el=".$last_el_id."&tipoatto=".$tipoatto."&checkMissing=yes&msg=Errore, impossibile salvare la PEC sulla tabella ini_pec_request_pec!&error=1");
                    }

                    if(!empty($pec)){
                        $query_par =  "   SELECT * FROM parametri_annuali WHERE CC = '" . $cc_el . "' AND Anno=" . date('Y');
                        $params_arr = $cls_db->getArrayLine($cls_db->ExecuteQuery($query_par));
                        if ($tipoatto=22)
                        {
                            //Aggiorna spese atto PEC
                            $query = "update notifica_atto as NA
                            join pignoramento_generale as PG ON NA.Atto_Notificato_ID=PG.ID
                            join partita_tributi as PT ON PG.Partita_ID = PT.ID
                            Set PG.Spese_Notifica_Debitore= PG.Spese_Notifica_Debitore-NA.Spese_Notifica+".$params_arr['Spese_Pec'].",
                            PG.Totale_Spese_Notifica= PG.Totale_Spese_Notifica-NA.Spese_Notifica+".$params_arr['Spese_Pec'].",
                            PG.Totale_Dovuto= PG.Totale_Dovuto-NA.Spese_Notifica+".$params_arr['Spese_Pec'].",
                            NA.Spese_Notifica = ".$params_arr['Spese_Pec'].", NA.Printer_Id=1, NA.PrintTypeId=4, NA.Tipo_Ufficiale='riscossione'
                            where PT.Utente_ID IN(".$whereUtenti.") and NA.Tipo_Notifica=\"debitore\" and PG.Elaboration_Id = ".$last_el_id;

                        }
                        else
                        {
                        //var_dump($params_arr);die;
                            $query = "UPDATE atto A JOIN elaborations E ON A.Elaboration_Id=E.Id JOIN partita_tributi PT ON PT.ID=A.Partita_ID JOIN utente U ON U.ID=PT.Utente_ID ";
                            $query.= "SET A.PrinterId=1, A.PrintTypeId=4, A.Modalita_Stampa='pec', A.Tipo_Ufficiale='riscossione' ";
                            $query.= ", Totale_Dovuto=Totale_Dovuto-Spese_Notifica+".$params_arr['Spese_Pec']." , Spese_Notifica=".$params_arr['Spese_Pec']." ";
                            $query.= "WHERE E.Elaboration_Status_Id<=2 AND U.ID IN (".$whereUtenti.")";
                            $cls_db->ExecuteQuery($query);
                        }
                    }
                }

                $cls_db->End_Transaction();
            }
        }
    }
}

header("location: prc_inipec.php?c=".$c."&a=".$a."&cc_el=".$cc_el."&codcat=".$cod_cat."&el=".$last_el_id."&tipoatto=".$tipoatto."&checkMissing=yes&msg=Scaricamento dati completato correttamente!&error=0");