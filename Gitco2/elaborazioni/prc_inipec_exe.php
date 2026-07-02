<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC."/headerAjax.php");
//include(INC."/menu.php");

include_once CLS . "/cls_DateTime.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/chilkat_9_5_0.php";
include_once CLS . "/cls_LOG.php";
include_once CLS . "/cli_function.php";

require CONFIG_ROOT."/_inipecServer.php";

$cls_utils = new cls_Utils();
$cls_db = new cls_db();
$log = new LOG();

function callInipecRequest($db, $utils , $log, $c, $a, $username, $password, $nomeFile, $tempDir)
{
    //echo $username." ".$password; die;
    $glob=new CkGlobal();
    $success = $glob->UnlockBundle(CHILKAT_CODICE_LICENZA);
    $log->info('DEBUG INIPEC UnlockBundle---->'.print_r($success, true));
    //trigger_error('DEBUG INIPEC UnlockBundle---->'.print_r($success, true),E_USER_NOTICE);

    $http = new CkHttp();
    $http->put_SessionLogFilename(SUPER_ROOT."/servizi.log");
    $soapXml = new CkXml();
    $soapXml->put_EmitXmlDecl(false);
    $soapXml->put_Tag('ws:richiestaRichiestaFornituraPec');
    $soapXml->AddAttribute('xmlns:ws', 'http://ws.fpec.gemo.infocamere.it');
    $soapXml->NewChild2('elencoCf|nomeDocumento', $nomeFile);
    $soapXml->NewChild2('elencoCf|tipoDocumento', 'zip');
    $include=$soapXml->NewChild('elencoCf|documento|inc:Include', null);
    $include->AddAttribute('xmlns:inc', INIPEC_NS);
    $include->AddAttribute('href', $nomeFile.'.zip');
    $soapXml->GetRoot2();
    $attributes=array('xmlns:soap'=>'http://schemas.xmlsoap.org/soap/envelope/');
    $attributes['xmlns:ws']=INIPEC_NS;
    $log->info("Richiesta inipec effettuata da $username con il file $nomeFile");
    //trigger_error("Richiesta inipec effettuata da $username con il file $nomeFile",E_USER_NOTICE);
    $soapXml = createSoapXml($soapXml,null,$attributes);
    $xmlBody = $soapXml->getXml();
    $req = new CkHttpRequest();
    $req->put_HttpVerb('POST');
    $req->put_Path('/fpec/ServizioFornituraPec');
    $req->put_ContentType("multipart/related; type='text/xml'");
    $req->AddHeader('SOAPAction', 'richiestaFornituraPec');
    $req->AddHeader('Authorization', "Basic ".base64_encode("{$username}:{$password}"));
    $req->AddStringForUpload2('', '', $xmlBody, 'utf-8', 'application/xop+xml; type=\'text/xml\'; charset=utf-8');
    $bd = new CkBinData();
    $success = $bd->LoadFile($tempDir .  $nomeFile . '.zip');
    $log->info('DEBUG INIPEC LoadFile---->'.print_r($success, true));
    //trigger_error('DEBUG INIPEC LoadFile---->'.print_r($success, true),E_USER_NOTICE);
    $req->AddBdForUpload($tempDir .  $nomeFile . '.zip', $tempDir . $nomeFile . '.zip',$bd,'application/zip; name='.$nomeFile . '.zip"');
    $req->AddSubHeader(1,'Content-ID','<'.$nomeFile . '.zip>');
    $req->AddSubHeader(1,'Content-Transfer-Encoding','Binary');
    $req->AddSubHeader(1,'Content-Disposition','attachment  name="'.$nomeFile . '.zip"; filename="'.$nomeFile . '.zip"');
    $xmlResponse = new CkXml();
    $log->info('DEBUG INIPEC new CkXml---->'.print_r($xmlResponse, true));
    //trigger_error('DEBUG INIPEC new CkXml---->'.print_r($xmlResponse, true),E_USER_NOTICE);
    $domain=splitUrl(INIPEC_URL)['domain'];
    $resp = $http->SynchronousRequest($domain, 443, true, $req);
    if ($http->get_LastMethodSuccess() !== true) {
        $log->error($http->lastErrorText());
        //trigger_error($http->lastErrorText(),E_USER_WARNING);
        echo $http->lastErrorText();
        exit();
    }

    $xmlResponse->LoadXml($resp->bodyStr());
    $log->info('DEBUG INIPEC LoadXml---->'.print_r($xmlResponse, true));
    $log->info('DEBUG INIPEC getXml---->'.print_r($xmlResponse->getXml(), true));
    $log->info('DEBUG INIPEC getChild 1---->'.print_r($xmlResponse->getChild(0), true));
    $log->info('DEBUG INIPEC getChild 2---->'.print_r($xmlResponse->getChild(0)->GetChild(0), true));
    $log->info('DEBUG INIPEC getChild 3---->'.print_r($xmlResponse->getChild(0)->GetChild(0)->GetChild(0), true));
    $log->info('DEBUG INIPEC getChild 4---->'.print_r($xmlResponse->getChild(0)->GetChild(0)->GetChild(0)->GetChild(1), true));
    $log->info('DEBUG INIPEC getChild 5---->'.print_r($xmlResponse->getChild(0)->GetChild(0)->GetChild(0)->GetChild(1)->GetChild(0), true));
    /*trigger_error('DEBUG INIPEC LoadXml---->'.print_r($xmlResponse, true),E_USER_NOTICE);
    trigger_error('DEBUG INIPEC getXml---->'.print_r($xmlResponse->getXml(), true),E_USER_NOTICE);
    trigger_error('DEBUG INIPEC getChild 1---->'.print_r($xmlResponse->getChild(0), true),E_USER_NOTICE);
    trigger_error('DEBUG INIPEC getChild 2---->'.print_r($xmlResponse->getChild(0)->GetChild(0), true),E_USER_NOTICE);
    trigger_error('DEBUG INIPEC getChild 3---->'.print_r($xmlResponse->getChild(0)->GetChild(0)->GetChild(0), true),E_USER_NOTICE);
    trigger_error('DEBUG INIPEC getChild 4---->'.print_r($xmlResponse->getChild(0)->GetChild(0)->GetChild(0)->GetChild(1), true),E_USER_NOTICE);
    trigger_error('DEBUG INIPEC getChild 5---->'.print_r($xmlResponse->getChild(0)->GetChild(0)->GetChild(0)->GetChild(1)->GetChild(0), true),E_USER_NOTICE);*/

    //$messageObject=new CLS_MESSAGE();



    if( $xmlResponse->getChild(0)->GetChild(0)->GetChild(0)->GetChild(1)->GetChild(0)==null){
        if($xmlResponse!=null) {
            $log->error($http->lastErrorText($xmlResponse->getXml()));
            //trigger_error($http->lastErrorText($xmlResponse->getXml()), E_USER_WARNING);
        }
        else {
            $log->warning("Risposta vuota");
            //trigger_error("Risposta vuota", E_USER_WARNING);
        }
        //$messageObject->addWarning("Impossibile effettuare la richiesta, provare più tardi");
        //$_SESSION['Message'] = $messageObject->getMessagesString();
        header("location: prc_inipec.php?c=".$c."&a=".$a."&error=2&msg=Impossibile effettuare la richiesta, provare più tardi!");
        die;
    }

    $idRichiesta = $xmlResponse->getChild(0)->GetChild(0)->GetChild(0)->GetChild(1)->GetChild(0)->getChildContent("idRichiesta");
    $esitoRichiesta =$xmlResponse->getChild(0)->GetChild(0)->GetChild(0)->GetChild(0)->getChildContent("esito");

    $save = new stdClass();
    $save->UserName = $username;
    $save->IdRichiesta = $idRichiesta;
    $save->EsitoRichiesta = $esitoRichiesta;
    $save->DataRichiesta = (new DateTime())->format("Y-m-d H:i:s");

    $db->DbSave($utils->GetObjectQuery($save,"ini_pec_request"));

    /*$insertRequest = array(
        array(
            'field' => 'UserName',
            'selector' => 'value',
            'type' => 'str',
            'value' => $username
        ),
        array(
            'field' => 'IdRichiesta',
            'selector' => 'value',
            'type' => 'int',
            'value' => $idRichiesta,
            'settype' => 'int'
        ),
        array(
            'field' => 'EsitoRichiesta',
            'selector' => 'value',
            'type' => 'str',
            'value' => $esitoRichiesta
        ),
        array(
            'field' => 'DataRichiesta',
            'selector' => 'value',
            'type' => 'str',
            'value' => (new DateTime())->format("Y-m-d H:i:s")
        )

    );
    $rs->insert("IniPecRequest", $insertRequest);*/
    return $idRichiesta;
}

$a = $cls_help->getVar("a");
$c = $cls_help->getVar("c");
$cc_el = $cls_help->getVar("cc_el");
$cod_cat = $cls_help->getVar('codcat');
$last_el_id =  $cls_help->getVar('el');
$tipoatto = $cls_help->getVar('tipoatto');


$query = "SELECT UserName, Password, INIPECPasswordExpiration from ini_pec_processing where UserId=".$_SESSION['aut_progr'];
$username_pw = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"ini_pec_processing");
$passwordExpiration=$username_pw['INIPECPasswordExpiration'];

if($passwordExpiration!=null && strtotime(date('Y-m-d'))>strtotime($passwordExpiration)) {
    echo "<script>location.href = 'prc_inipec.php?c=".$c."&a=".$a."&cc_el=".$cc_el."&codcat=".$cod_cat."&el=".$last_el_id."&tipoatto=".$tipoatto."&msg=Password scaduta, per modificarla premere il lucchetto verde!&error=2' </script>";
    die;
}

$checkCom = $cls_help->getVar("checkbox");
$tipoSoggetto = $cls_help->getVar("tipoSoggHidden");
$limit = $cls_help->getVar("radio_limit");

if($tipoSoggetto == null)
    $tipoSoggetto = "E";

$username = $username_pw["UserName"];
$password = $username_pw["Password"];

/*$whereCC = "";
$checkCity = 0;
foreach($_POST['radio_limit'] as $CityId){
    if($checkCity==0){
        $whereCC = "'".$CityId."'";
        $checkCity = 1;
    }
    else
        $whereCC.= ", '".$CityId."'";
}*/

$CC = $cc_el==null?$c:$cc_el;

$query = "SELECT DISTINCT P.CF_PI FROM v_partita P JOIN elaborations E ON E.Id=P.Elaboration_Id ";
$query.= "WHERE P.CF_PI!='00000000000' AND P.CF_PI!='' AND P.CF_PI is not null ";
$query.= "AND (DATEDIFF('".date('Y-m-d')."', P.InipecLoaded)>15 OR P.InipecLoaded is null)  ";
$query.= "AND P.CC = '".$CC."' AND E.Elaboration_Status_Id in (2,10) ";

if($tipoSoggetto=='F')
    $query.="AND P.Genere != 'D' ";
else if($tipoSoggetto=='D')
    $query.="AND P.Genere = 'D' ";

$query.= "GROUP BY P.CF_PI ORDER BY P.CF_PI ".$limit;
$a_inipec = $cls_db->getResults($cls_db->ExecuteQuery($query));
if(count($a_inipec)==0){
    echo "<script>location.href = 'prc_inipec.php?c=".$c."&a=".$a."&cc_el=".$cc_el."&codcat=".$cod_cat."&el=".$last_el_id."&tipoatto=".$tipoatto."&msg=Nessun utente valido trovato per la richiesta a IniPec. Contattare la assistenza per la verifica dei dati&error=2' </script>";
    die;
}

$txt_string = "";
foreach ($a_inipec as $key=>$a_cf){
    $txt_string .= $a_cf['CF_PI']."\n";
}

$nomeFile = $username . date('Ymdhis', time());
$pathFILE = $cls_utils->crea_dir(SUPER_ROOT."/archivio/temp");
$myfile = fopen($pathFILE . "/" . $nomeFile . ".txt", "w");
fwrite($myfile, $txt_string);
fclose($myfile);

$zip = new ZipArchive();
if ($zip->open($pathFILE . "/" . $nomeFile . '.zip', ZipArchive::CREATE)) {
    $zip->addFile($pathFILE . "/" . $nomeFile . ".txt", $nomeFile . ".txt");
    $zip->close();

    $idRichiesta = callInipecRequest($cls_db, $cls_utils, $log, $c, $a, $username, $password, $nomeFile, $pathFILE . "/");
    if(!$idRichiesta){
        echo "<script>location.href = 'prc_inipec.php?c=".$c."&a=".$a."&cc_el=".$cc_el."&codcat=".$cod_cat."&el=".$last_el_id."&tipoatto=".$tipoatto."&msg=Richiesta errata!&error=2' </script>";
        die;
    }

    $log->info("Richiesta inipec effettuata, idRichiesta: $idRichiesta");

    foreach ($a_inipec as $key=>$a_cf){
        $a_saveRequest = array(
            "CodiceFiscale" => $a_cf['CF_PI'],
            "IdRichiesta" => $idRichiesta,
            "UserName" => $username
        );

        if($cls_db->DbSave($cls_utils->GetObjectQuery($a_saveRequest,"ini_pec_request_pec")) === false){
            $log->error("Impossibile salvare a DB il CF: ".$a_saveRequest["CodiceFiscale"]);
            echo "<script>location.href = 'prc_inipec.php?c=".$c."&a=".$a."&cc_el=".$cc_el."&codcat=".$cod_cat."&el=".$last_el_id."&tipoatto=".$tipoatto."&msg=Salvataggio CF ".$a_saveRequest["CodiceFiscale"]." fallito!&error=2' </script>";
            die;
        }

        $query = "UPDATE utente SET InipecLoaded = '".date('Y-m-d')."' ";
        $query.= "WHERE Codice_Fiscale = '".$a_saveRequest["CodiceFiscale"]."' OR Partita_Iva = '".$a_saveRequest["CodiceFiscale"]."'";
        $cls_db->ExecuteQuery($query);

    }

    unlink($pathFILE . "/" . $nomeFile . '.zip');
    unlink($pathFILE . "/" . $nomeFile . '.txt');
}

header("location: prc_inipec.php?c=".$c."&a=".$a."&cc_el=".$cc_el."&codcat=".$cod_cat."&el=".$last_el_id."&tipoatto=".$tipoatto."&msg=Richiesta dati completata correttamente!&error=0");