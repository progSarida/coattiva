<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

if ($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

include_once(CLS."/cls_elaborazioniUtils.php");
include_once(CLS."/cls_help.php");
include_once(CLS."/cls_db.php");

$cls_help = new cls_help();
$cls_elab = new cls_elaborazioniUtils();
$cls_db = new cls_db();

//alertAllGlobalVariables();
$tipo = $cls_help->getVar('tipo');
$comune = $cls_help->getVar('comune');
$crono = $cls_help->getVar('crono');
$anno = $cls_help->getVar('anno');
$tiporiscossione = $cls_help->getVar('tiporiscossione');
$datapagamento = $cls_help->getVar('datapagamento');
$docTypeId = $cls_help->getVar('docTypeId');

$myID = "";
$risposta = "";

if($tipo == 1)
{
    //$myAtto = new atto(null, $comune);
    $myID = $cls_elab->cercaIDdaCronoPagamento($docTypeId, $crono, $anno, $comune);
    //$risposta = array("myID" => $myID,"doctypeid" => $docTypeId);
}
else if($tipo == 2)
{
    $myID = $cls_elab->cercaIDdaCronoPagamentoPigno($docTypeId, $crono, $anno, $comune);
    //$risposta = array("myID" => $myID,"docType"=>$docTypeId);
} else echo json_encode(array("Errore"=>true));


if ($myID != "")
{
    if($tipo == 1)
    {
        $query = "SELECT * FROM atto WHERE ID = ".$myID." AND CC = '".$comune."'";
        $myAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");// new atto($myID, $comune);
        $tipo_atto = $myAtto->Atto;
    }
    else
    {
        $query = "SELECT * FROM pignoramento_generale WHERE ID = ".$myID." AND CC = '".$comune."'";
        $myAtto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pignoramento_generale");// new pignoramento($myID, $comune);
        $tipo_atto = $cls_elab->tipo_pignoramento($myAtto->Tipo,$myAtto->Tipo_Terzi);
    }

    $query = "SELECT ID FROM pagamento WHERE Atto_ID = '".$myAtto->ID."' AND Partita_ID = '".$myAtto->Partita_ID."' AND Tipo_Atto NOT LIKE 'Pignoramento%' ORDER BY Rata ASC";
    $pagamento_id = $cls_db->getResultsNull($cls_db->ExecuteQuery($query),"pagamento");

    for( $i=0; $i<count($pagamento_id); $i++)
    {
        $query = "SELECT * FROM pagamento WHERE ID = '".$pagamento_id[$i]['ID']."' AND CC = '".$comune."'";
        $myAtto->Pagamento[$i] = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pagamento");//new pagamento( $pagamento_id[$i]['ID'] , $c );
    }


    $query = "SELECT * FROM partita_tributi WHERE ID = '".$myAtto->Partita_ID."' AND CC = '".$comune."'";
    $myPartita = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"partita_tributi");// new partita($myAtto->Partita_ID, $comune);

    $query = "SELECT * FROM utente WHERE ID = '".$myPartita->Utente_ID."' AND CC_Comune = '".$comune."'";
    $myPartita->Utente = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"utente");//new utente($val['Utente_ID'], $c);

    $query = "SELECT ID FROM tributo WHERE Partita_ID = '".$myPartita->ID."' ORDER BY Codice_Tributo ASC";
    $tributo_id = $cls_db->getResultsNull($cls_db->ExecuteQuery($query),"tributo");// select_mysql_array("ID", "tributo","Partita_ID = '".$this->ID."'","Codice_Tributo");

    for( $i=0; $i<count($tributo_id); $i++) {
        $query = "SELECT * FROM tributo WHERE ID = '".$tributo_id[$i]['ID']."' AND CC = '".$comune."'";
        $myPartita->Tributo[$i] = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"tributo");//new tributo($tributo_id[$i]['ID'], $c);
    }

    $tipopag = $cls_elab->TipiPagamento($tipo_atto, "TIPODASCRITTA");
    //alert ($tipopag . " e " . $selected);

    $pagPresente = "NONPRESENTE_0";
    /*for ($i = 0; $i < count($myAtto->Pagamento); $i++)
    {
        $myPagamento = new pagamento($myAtto->Pagamento[$i]->ID, $myAtto->CC);
        if ($myPagamento->PagamentoGiaPresente() != null)
        {
            $pagPresente = "GIAPRESENTE" . "_" . $myPagamento->Rata;
            break;
        }
    }*/
    if (count($myAtto->Pagamento) > 0) $pagPresente = "GIAPRESENTE" . "_" . count($myAtto->Pagamento);

    $query = "SELECT * FROM parametri_pagamento WHERE CC = '".$myAtto->CC."' AND Tipo_Riscossione = '".$tiporiscossione."'";
    $myParametro = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_pagamento");//new parametri_pagamento($myAtto->CC, $tiporiscossione);
    $contoTerzi = $cls_elab->data_conto_terzi($datapagamento,$myParametro->Data_Cambio_Conto,$myParametro->Conto_Terzi);

    if ($myPartita->Utente->Cognome != "")
    {
        $utente = $myPartita->Utente->Cognome . " " . $myPartita->Utente->Nome;
    }
    else
    {
        $utente = $myPartita->Utente->Ditta;
    }

    /*$risposta = $pagPresente . "**" .
        $myAtto->ID . "**" .
        $myAtto->CC . "**" .
        $myAtto->Partita_ID . "**" .
        $myAtto->ID_Cronologico . "**" .
        $myAtto->Anno_Cronologico . "**" .
        $tipo_atto . "**" .
        $tipopag . "**" .
        $myPartita->Tributo[0]->Info_Cartella . "**" .
        $myAtto->Totale_Dovuto . "**" .
        $myAtto->Rate_Previste . "**" .
        count($myAtto->Pagamento) . "**" .
        $contoTerzi . "**" .
        $utente;*/

    $risposta = [
        "pagPresente" => $pagPresente,
        "ID" => $myAtto->ID,
        "CC" => $myAtto->CC,
        "Partita_ID" => $myAtto->Partita_ID,
        "ID_Cronologico" => $myAtto->ID_Cronologico,
        "Anno_Cronologico" =>  $myAtto->Anno_Cronologico,
        "tipo_atto" => $tipo_atto,
        "tipopag" => $tipopag,
        "Info_Cartella" => $myPartita->Tributo[0]->Info_Cartella,
        "Totale_Dovuto" => $myAtto->Totale_Dovuto,
        "Rate_Previste" => $myAtto->Rate_Previste,
        "NumPagamenti" => count($myAtto->Pagamento),
        "contoTerzi" => $contoTerzi,
        "utente" => $utente
    ];
}

echo json_encode($risposta);

?>
