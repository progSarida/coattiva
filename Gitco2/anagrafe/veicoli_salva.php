<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(CLS."/cls_help.php");
include(CLS."/cls_db.php");
include(CLS."/cls_Utils.php");
include_once CLS . "/cls_storico.php";													

$storico = new storico('storicoAnagrafe','2');
$cls_help = new cls_help();
$cls_Utils = new cls_Utils();
$cls_db = new cls_db();



$Data_Visura = $cls_help->getVar("Data_Visura");
$Utente_ID = $cls_help->getVar("Utente_ID");
$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$mode = $cls_help->getVar('mode');
$servizio = $cls_help->getVar('servizio');


$Targa = $cls_help->getVar("Targa");
$ProgressivoLista = $cls_help->getVar("ProgressivoLista");
$ProvinciaCompetenza = $cls_help->getVar("ProvinciaCompetenza");
$SerieTarga = $cls_help->getVar("SerieTarga");
$StatoVeicolo = $cls_help->getVar("StatoVeicolo");
$Causale = $cls_help->getVar("Causale");
$FlagGiuridico = $cls_help->getVar("FlagGiuridico");
$DataPrimaImmatricolazione = $cls_help->getVar("DataPrimaImmatricolazione");
$CodiceUltimaFormalita = $cls_help->getVar("CodiceUltimaFormalita");
$DescrizioneUltimaFormalita = $cls_help->getVar("DescrizioneUltimaFormalita");
$DataUltimaFormalita = $cls_help->getVar("DataUltimaFormalita");

$Telaio = $cls_help->getVar("Telaio");
$Fabbrica = $cls_help->getVar("Fabbrica");
$Tipo = $cls_help->getVar("Tipo");
$Serie = $cls_help->getVar("Serie");
$ClasseVeicolo = $cls_help->getVar("ClasseVeicolo");

$Cognome = $cls_help->getVar("Cognome");
$Nome = $cls_help->getVar("Nome");
$DataNascita = $cls_help->getVar("DataNascita");
$CodiceFiscale = $cls_help->getVar("CodiceFiscale");
$PartitaIva = $cls_help->getVar("PartitaIva");
$ProvinciaResidenza = $cls_help->getVar("ProvinciaResidenza");
$CodiceRuoloSoggetto = $cls_help->getVar("CodiceRuoloSoggetto");
$DescrizioneRuoloSoggetto = $cls_help->getVar("DescrizioneRuoloSoggetto");
$DataRiferimentoRuoloSoggetto = $cls_help->getVar("DataRiferimentoRuoloSoggetto");

$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );

$query = "SELECT ID, Data_Visura, Targa, ProgressivoVisura FROM veicoli where Utente_ID = ".$Utente_ID." AND Data_Visura = (SELECT MAX(Data_Visura) FROM veicoli WHERE Utente_ID = ".$Utente_ID.") AND ProgressivoVisura = (SELECT MAX(ProgressivoVisura) FROM veicoli WHERE Utente_ID = ".$Utente_ID." AND Data_Visura = (SELECT MAX(Data_Visura) FROM veicoli WHERE Utente_ID = ".$Utente_ID."))";
$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

$storico_query = "SELECT Nome, Cognome, Ditta, Genere, Comune_ID FROM utente where ID = '".$Utente_ID."'";
$utente = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($storico_query),"utente");
$msg_utente = "";
if($utente["Genere"] == "D")
	$msg_utente = $utente["Ditta"];
else
	$msg_utente = $utente["Cognome"]." ". $utente["Nome"];

//$query = "SELECT MAX(Data_Visura) as DataVisuraPrec FROM veicoli WHERE Utente_ID = ".$Utente_ID;
//$result = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"veicoli");

$dataVisPrec = isset($result[0]["Data_Visura"])?$result[0]["Data_Visura"]:null;

$progressivo = isset($result[0]["ProgressivoVisura"])?($result[0]["ProgressivoVisura"]+1):1;

$flagOK = false;


if($Data_Visura == $dataVisPrec) {
    for ($i = 0; $i < count($Targa); $i++) {
        $flagOK = false;
        for ($x = 0; $x < count($result); $x++) {
            if ($result[$x]["Targa"] == $Targa[$i]) {
                $flagOK = true;
                //break;
            }
        }
        if(!$flagOK){
            break;
        }
    }
}
else $flagOK = true;

if(count($result)!=count($Targa))
    $flagOK = false;

$salva = array();
$msg = "Dati salvati correttamente";
$error = 0;
if($Data_Visura != $dataVisPrec || !$flagOK)
{
    $cls_db->Start_Transaction();
    $cls_db->Begin_Transaction();

    if($Data_Visura != $dataVisPrec)
        $progressivo = 1;

    $where = array();
    for($i = 0; $i<count($Targa); $i++){

        for($x=0; $x<count($result); $x++)
        {
            if($result[$x]["Targa"] == $Targa[$i]){
                $where = array("ID" => $result[$x]["ID"]);
                break;
            }
        }

        $salva["Data_Visura"] = $Data_Visura;
        $salva["Utente_ID"] = $Utente_ID;
        $salva["ProgressivoVisura"] = $progressivo;
        $salva["CC_Comune"] = $c;

        $salva["ProgressivoLista"] = $ProgressivoLista[$i];
        $salva["ProvinciaCompetenza"] = $ProvinciaCompetenza[$i];
        $salva["Targa"] = $Targa[$i];
        $salva["SerieTarga"] = $SerieTarga[$i];
        $salva["StatoVeicolo"] = $StatoVeicolo[$i];
        $salva["Causale"] = $Causale[$i];
        $salva["FlagGiuridico"] = $FlagGiuridico[$i];
        $salva["DataPrimaImmatricolazione"] = $DataPrimaImmatricolazione[$i];
        $salva["CodiceUltimaFormalita"] = $CodiceUltimaFormalita[$i];
        $salva["DescrizioneUltimaFormalita"] = $DescrizioneUltimaFormalita[$i];
        $salva["DataUltimaFormalita"] = $DataUltimaFormalita[$i];

        $salva["Telaio"] = $Telaio[$i];
        $salva["Fabbrica"] = $Fabbrica[$i];
        $salva["Tipo"] = $Tipo[$i];
        $salva["Serie"] = $Serie[$i];
        $salva["ClasseVeicolo"] = $ClasseVeicolo[$i];

        $salva["Cognome"] = $Cognome[$i];
        $salva["Nome"] = $Nome[$i];
        $salva["DataNascita"] = $DataNascita[$i];
        $salva["CodiceFiscale"] = $CodiceFiscale[$i];
        $salva["PartitaIva"] = $PartitaIva[$i];
        $salva["ProvinciaResidenza"] = $ProvinciaResidenza[$i];
        $salva["CodiceRuoloSoggetto"] = $CodiceRuoloSoggetto[$i];
        $salva["DescrizioneRuoloSoggetto"] = $DescrizioneRuoloSoggetto[$i];
        $salva["DataRiferimentoRuoloSoggetto"] = $DataRiferimentoRuoloSoggetto[$i];

        $check = true;
        if(count($where) > 0) $check = $cls_db->DbSave($cls_Utils->GetObjectQuery($salva,"veicoli",$where));
        else $check = $cls_db->DbSave($cls_Utils->GetObjectQuery($salva,"veicoli"));


        if(!$check)
        {
            $cls_db->Rollback();
            $error = 1;
            $msg = "Errore, impossibile inserire i dati";
            break;
        }

        $where = array();
    }

    $cls_db->End_Transaction();
}
else{
    $error = 2;
    $msg = "Visura già effettuata, e salvata";
}

if($error == 0)
    $storico->insRow('I', "Inserimento visura utente ".$msg_utente." (".$utente['Comune_ID'].") per ".$ente['Denominazione']."[".$c."]");

header("Location: Veicoli.php?Visura=no&mode={$mode}&p={$Utente_ID}&c={$c}&a={$a}&servizio={$servizio}&error={$error}&msg={$msg}");