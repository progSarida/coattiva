<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once INC . "/headerAjax.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_html.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/cls_DateTime.php";
include_once CLS . "/cls_Soap_Visure.php";

include_once CLS . "/cls_LOG.php";

$log = new LOG();

$cls_help = new cls_help();
$cls_db = new cls_db();

$cls_utils = new cls_Utils();
$cls_date = new cls_DateTimeI("DB",false);

function initRequest($cls_db,$cls_utils,$c,$elaboration_id = null){

    $query = "SELECT MAX(IdRichiesta) AS ID FROM request_visures_aci WHERE CC = '".$c."' AND year = '".date("Y")."'";
    $IdReq = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

    if($IdReq == null) $IdReq = 1;
    else $IdReq = $IdReq["ID"] + 1;

    $save = new stdClass();
    $save->year = date("Y");
    $save->CC = $c;
    $save->date = date("Y-m-d");
    $save->IdRichiesta = $IdReq;
    //$save->number_request = $richiesteInviate;
    //$save->received_request = $richiesteRicevute;
    $save->elaboration_id = $elaboration_id;

    return $cls_db->DbSave($cls_utils->GetObjectQuery((array) $save,"request_visures_aci"));
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$last_el_id = $cls_help->getVar('el');
$codcat= $cls_help->getVar('codcat');
$tipoatto= $cls_help->getVar('tipoatto');

$query = "SELECT P.Utente_ID, U.Genere, U.Codice_Fiscale, U.Partita_Iva, U.Genere ".
        "FROM partita_tributi as P JOIN utente as U on P.Utente_ID = U.ID ".
        "WHERE P.Elaboration_Id= ".$last_el_id.
        " GROUP BY P.Utente_ID";

$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

?>

<script>
    function inizio()
    {
        $('#progressbar').progressbar({
            value: false
        });
        $( "#barlabel" ).text("Inizio elaborazione...");
    }

    function update(valore)
    {
        $( "#progressbar" ).progressbar({value: parseInt(valore) });
        $( "#barlabel" ).text( valore + "%" );
    }

    function nessun_risultato()
    {
        $( "#progressbar" ).progressbar({value: 100 });
        $( "#barlabel" ).text("Nessun risultato trovato");
    }

    function novpn()
    {
        $( "#progressbar" ).progressbar({value: 100 });
        $( "#barlabel" ).text("VPN NON CONNESSA!");
    }

    function busyvpn()
    {
        $( "#progressbar" ).progressbar({value: 100 });
        $( "#barlabel" ).text("VPN OCCUPATA!");
    }


    function fine(value)
    {
        $( "#progressbar" ).progressbar({value: 100 });
        $( "#barlabel" ).text( value );
        //$( "div#vedi_file" ).append("<div class='row' style='margin-top: 4%;'><div class='col-lg-2 col-lg-offset-1'><input type=button name=avanti class='btn btn-primary resize' value='Elenco elaborazioni' onclick='mostra_file();'></div></div>");
        location.href ='<?= ELAB_PIGNORAMENTI_WEB ?>/mgmt_pignoramenti.php?c=<?=$c;?>&a=<?=$a;?>&el=<?=$last_el_id;?>';
    }

    

</script>


<div class="row justify-content-md-center " style="margin-top: 1%;">
    <div class="col col-md-auto text_center">
        <span class="titolo font18 under_decor">Elaborazione visure ACI-PRA</span>
    </div>
</div>
<div class="row" style="margin-top: 3%;">
    <div class="col-lg-10 col-lg-offset-1">
        <div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div id=vedi_file></div>
    </div>
</div>


<?php


flush();
ob_flush();

echo "<script>inizio();</script>";

flush();
ob_flush();
flush();
ob_flush();
sleep(2);

$num_pignoramenti = count($result);

$query="SELECT * FROM semaforo WHERE Procedure_Type_Id = 6";
$a_semaforo = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
if(!empty($a_semaforo['Datetime'])){
    $datetime1 = new DateTime($a_semaforo['Datetime']); // 11 October 2013
    $datetime2 = new DateTime(date('Y-m-d H:i:s')); // 13 October 2013

    $interval = $datetime1->diff($datetime2);
    if($interval->i<10){
        echo "<script>busyvpn();</script>";
        ob_flush();ob_flush();
        die;
    }
    else{
        $query = "DELETE FROM semaforo WHERE Procedure_Type_Id = 6";
        $cls_db->ExecuteQuery($query);
    }
}

$query = "INSERT INTO semaforo (Procedure_Type_Id, Datetime, User_Id) ".
"VALUES (6, '". date('Y-m-d H:i:s')."', ".$_SESSION['aut_progr'].")";
$cls_db->ExecuteQuery($query);

require CONFIG_ROOT."/_aciServer.php";
if(strpos(shell_exec(ACICHECK_CMD),ACISERVERIP)){
    shell_exec(ACIVPN_KILL);
    sleep(2);
}

if(strpos(shell_exec(ACICHECK_CMD),ACISERVERIP)===false){
    shell_exec(ACIVPN_CMD);
    sleep(3);

    if(strpos(shell_exec(ACICHECK_CMD),ACISERVERIP)===false){
        echo "<script>novpn();</script>";
        ob_flush();ob_flush();
        die;
    }
}


// //TODO POSSIBILE PROBLEMA IN CASO DI ESECUZIONE CONTEMPORANEA DI PIU' OPERATORI (IMPROBABILE?)
// require CONFIG_ROOT."/_aciServer.php";
// shell_exec(ACIVPN_KILL);
// sleep(2);
// shell_exec(ACIVPN_CMD);
// sleep(5);
// if(strpos(shell_exec(ACICHECK_CMD),ACISERVERIP)===false){
//     echo "<script>novpn();</script>";
//     die;
// }

//$richiesteInviate = 0;
//$richiesteRicevute = 0;
$id_request = null;
if($num_pignoramenti > 0)
    $id_request = initRequest($cls_db,$cls_utils,$c,$last_el_id);

if($id_request !== false && $id_request !== null) {
    $queryInsert = "INSERT INTO request_visures_aci_detail (request_id,user_id,targa,tipo_veicolo,classe_veicolo) VALUES ";
}
$contatore = 0;
$soap = new SoapCMN();
set_time_limit(-1);

for( $i=0; $i < $num_pignoramenti; $i++ )
{
    
    echo "<script>update(".ceil($i*100/$num_pignoramenti).");</script>";

    flush();
    ob_flush();
    flush();
    ob_flush();

    //CONTROLLO DATA VISURA
    $query = "SELECT ID, Utente_ID, Data_Visura, Targa, ProgressivoVisura FROM veicoli where CC_Comune
     = '".$c."' AND Utente_ID = ".$result[$i]["Utente_ID"]." 
     AND Data_Visura = (SELECT MAX(Data_Visura) FROM veicoli WHERE CC_Comune = '".$c."'
     AND Utente_ID = ".$result[$i]["Utente_ID"].")";
    
    //! CAMBIO INDICE ARRAY CON TARGA PER INDIVIDUARE RAPIDAMENTE VEICOLI PRESENTI SU CUI FARE UPDATE
    $resultControlVisura = $cls_db->getResults($cls_db->ExecuteQuery($query),"array","Targa");

    $datePlussDay = "";

    $dataVisPrec = isset($resultControlVisura[0]["Data_Visura"])?$resultControlVisura[0]["Data_Visura"]:null;
    if(!is_null($dataVisPrec))
    {
        $datePlussDay = new cls_DateTime($dataVisPrec,"DB",false);
        $dataVisuraPrecedente = $datePlussDay->GetDate("IT");
        $number = $cls_help->getVar("min_day");
        if(!is_numeric($number)) $number = 0;
        $datePlussDay->AddDay($number);
    }
    else{
        $datePlussDay = new cls_DateTime(date("Y-m-d"),"DB",false);
        $dataVisuraPrecedente = "PRIMA VISURA";
        $datePlussDay->AddDay(-1);
    }

    if( $datePlussDay->CompareDate("DB","<",date("Y-m-d"))){

        $cls_db->Start_Transaction();
        $cls_db->Begin_Transaction();


        if($result[$i]["Genere"]!="D" && !empty($result[$i]["Codice_Fiscale"]))
        {
            //echo "<h1>".$result[$i]["Codice_Fiscale"]."</h1>";
            $var = (string)$result[$i]["Codice_Fiscale"];
            $log->info("Inzio Ricerca Codice Fiscale : ".$var);
            $resultSoap = $soap->SearchForCF(trim((string)$result[$i]["Codice_Fiscale"]));//"VRDGLC89A16D969M","TGLFNC75M23B963F"
            $log->info("Fine  Ricerca Codice Fiscale : ".$var);

            //$richiesteInviate++;
        }
        else if($result[$i]["Genere"]=="D" && !empty($result[$i]["Partita_Iva"]) && $result[$i]["Partita_Iva"] != "00000000000"){
            $var = (string)$result[$i]["Partita_Iva"];
            $log->info("Inzio Ricerca Partita Iva : ".$var);
            $resultSoap = $soap->SearchForPI($result[$i]["Partita_Iva"]);//"01338160995"
            $log->info("Fine Ricerca Partita Iva : ".$var);

            //$richiesteInviate++;
        }else{
            if($contatore == 0) $queryInsert .= " (".$id_request.", ".$result[$i]["Utente_ID"].", NULL, NULL, NULL ) ";
            else $queryInsert .= ", (".$id_request.", ".$result[$i]["Utente_ID"].", NULL, NULL, NULL ) ";
            $contatore++;
            continue;
        }
        
        //! CORREZIONE DATI DB CHE SONO STATI INSERITI COME VEICOLI DELL'UTENTE PRECEDENTE VISURATO POSITIVAMENTE
        if(empty($resultSoap->DatiRisposta->ElencoVeicoli->Veicolo)){
            $cls_db->ExecuteQuery("DELETE FROM veicoli WHERE Utente_ID=".$result[$i]["Utente_ID"]);

            if($contatore == 0) $queryInsert .= " (".$id_request.", ".$result[$i]["Utente_ID"].", NULL, NULL, NULL ) ";
            else $queryInsert .= ", (".$id_request.", ".$result[$i]["Utente_ID"].", NULL, NULL, NULL ) ";
            $contatore++;
            continue;
        }
            
        $obj_veicoli = $resultSoap->DatiRisposta->ElencoVeicoli->Veicolo;
        $a_veicoli = array();

        if(isset($obj_veicoli->Targa))
            $a_veicoli[0] = $obj_veicoli;
        else if(isset($obj_veicoli[0]))
            $a_veicoli = $obj_veicoli;


        
        foreach ($a_veicoli as $value) {

            //$richiesteRicevute++;
            if($contatore == 0) $queryInsert .= " (".$id_request.", ".$result[$i]["Utente_ID"].", '".$value->Targa."', '".$value->DatiTecnici->Tipo."', '".$value->DatiTecnici->ClasseVeicolo."' ) ";
            else $queryInsert .= ", (".$id_request.", ".$result[$i]["Utente_ID"].", '".$value->Targa."', '".$value->DatiTecnici->Tipo."', '".$value->DatiTecnici->ClasseVeicolo."' ) ";

            $contatore++;
            //! CONTROLLO SU INDICE TARGA PER IMPOSTARE UPDATE IN CASO DI ESISTENZA DELLA STESSA IN TABELLA
            if(!empty($resultControlVisura[$value->Targa]))
                $where = array("ID" => $resultControlVisura[$value->Targa]["ID"]);
            else
                $where = array();

            $save = new stdClass();

            $save->Data_Visura = date("Y-m-d");
            $save->Utente_ID = $result[$i]["Utente_ID"];
            $save->ProgressivoVisura = 1;
            $save->CC_Comune = $c;

            $save->ProgressivoLista = $value->ProgressivoLista;
            $save->ProvinciaCompetenza = $value->ProvinciaCompetenza;
            $save->Targa = $value->Targa;
            $save->SerieTarga = $value->SerieTarga;
            $save->StatoVeicolo = $value->StatoVeicolo;
            $save->Causale = $value->Causale;
            $save->FlagGiuridico = $value->FlagGiuridico;
            $save->DataPrimaImmatricolazione = $value->DataPrimaImmatricolazione;
            $save->CodiceUltimaFormalita = $value->CodiceUltimaFormalita;
            $save->DescrizioneUltimaFormalita = $value->DescrizioneUltimaFormalita;
            $save->DataUltimaFormalita = $value->DataUltimaFormalita;

            $save->Telaio = $value->DatiTecnici->Telaio;
            $save->Fabbrica = $value->DatiTecnici->Fabbrica;
            $save->Tipo = $value->DatiTecnici->Tipo;
            $save->Serie = $value->DatiTecnici->Serie;
            $save->ClasseVeicolo = $value->DatiTecnici->ClasseVeicolo;

            $save->Cognome = str_replace("\"","",$value->Soggetto->Cognome); //! Alle volte SOAP restituisce stringa sporca con carattere "
            $save->Nome = str_replace("\"","",$value->Soggetto->Nome);
            $save->DataNascita = $resultSoap->DatiRichiesta->DataNascita;
            //$result[$count]["ComuneNascita"] = $value->Soggetto->ComuneNascita;//
            $save->CodiceFiscale = $value->Soggetto->CodiceFiscale;
            $save->PartitaIva = $value->Soggetto->PartitaIva;
            if(empty($value->Soggetto->ProvinciaResidenza))
                $save->ProvinciaResidenza = "";
            else
                $save->ProvinciaResidenza = $value->Soggetto->ProvinciaResidenza;
            $save->CodiceRuoloSoggetto = $value->Soggetto->CodiceRuoloSoggetto;
            $save->DescrizioneRuoloSoggetto = $value->Soggetto->DescrizioneRuoloSoggetto;
            $save->DataRiferimentoRuoloSoggetto = $value->Soggetto->DataRiferimentoRuoloSoggetto;

            $check = true;
            if(count($where) > 0) 
                $check = $cls_db->DbSave($cls_utils->GetObjectQuery((array) $save,"veicoli",$where));
            else 
                $check = $cls_db->DbSave($cls_utils->GetObjectQuery((array) $save,"veicoli"));


            if(!$check)
            {
                $cls_db->Rollback();
                $error = 1;
                $msg = "Errore, impossibile inserire i dati";
                break;
            }
        }

        //! CANCELLAZIONE VEICOLI PRECEDENTI NON RILEVATI NELLA NUOVA VISURA E QUINDI DA SCOLLEGARE DALL'UTENTE
        $cls_db->ExecuteQuery("DELETE FROM veicoli WHERE Utente_ID=".$result[$i]["Utente_ID"]." AND Data_Visura<'".date("Y-m-d")."'");
        
        $cls_db->End_Transaction();
    }
}//CHIUSURA PIGNORAMENTI

$cls_db->Start_Transaction();
$cls_db->Begin_Transaction();

if($num_pignoramenti > 0){
    $check = $cls_db->ExecuteQuery($queryInsert);
    if(!$check)
        $cls_db->Rollback();
}

/*$query = "SELECT MAX(IdRichiesta) AS ID FROM request_visures_aci WHERE CC = '".$c."' AND year = '".date("Y")."'";
$IdReq = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

if($IdReq == null) $IdReq = 1;
else $IdReq = $IdReq["ID"] + 1;

$save = new stdClass();
$save->year = date("Y");
$save->CC = $c;
$save->date = date("Y-m-d");
$save->IdRichiesta = $IdReq;
$save->number_request = $richiesteInviate;
$save->received_request = $richiesteRicevute;
$save->elaboration_id = $last_el_id;

$cls_db->DbSave($cls_utils->GetObjectQuery((array) $save,"request_visures_aci"));*/


shell_exec(ACIVPN_KILL);
$query = "DELETE FROM semaforo WHERE Procedure_Type_Id = 6";
$check = $cls_db->ExecuteQuery($query);
if(!$check)
    $cls_db->Rollback();

$cls_db->End_Transaction();

if($num_pignoramenti == 0)
    echo "<script>nessun_risultato();</script>";
else	
    echo "<script>fine('Elaborazione completata');</script>";

include(INC."/footer.php");
