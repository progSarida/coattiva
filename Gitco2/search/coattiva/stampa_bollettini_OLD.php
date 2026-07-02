<?php
/*require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/parametri.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/coazione.php";
include CLASSI . "/flussi.php";
include CLASSI . "/pdf_con_bollettino.php";
include CLASSI . "/numero_letterale.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}*/

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_ente.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_registry.php";
include_once CLS . "/cls_textParametersHtml.php";
include_once CLS . "/cls_parameters.php";
include_once CLS . "/cls_LOG.php";
include_once CLS . "/cls_Stampe.php";
include_once CLS . "/numero_letterale.php";
include_once CLS . "/cls_postal.php";
include_once CLS . "/cls_ruolo.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

$cls_help = new cls_help();
$cls_db = new cls_db();
$cls_stp = new cls_Stampe();
$cls_utils = new cls_Utils();
$cls_params = new cls_parameters();
$cls_date = new cls_DateTimeI("IT",false);
$log = new LOG();
$cls_registry = new cls_registry();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$ID_Atto = $cls_help->getVar('ID_Atto');
$stampa_select = strtoupper($cls_help->getVar('tipo_stampa'));

set_time_limit(100);

/*$comune = new ente_gestito($c);
$nome_com = $comune->Nome;
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];*/
/*$query = "SELECT * FROM enti_gestiti WHERE CC = '".$c."'";
$comune = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"enti_gestiti");//new ente_gestito($c);

if( $comune->Gestore_ID != 0 ) {
    $query = "SELECT * FROM gestore WHERE ID = '" . $comune->Gestore_ID . "'";
    $comune->Gestore = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"gestore");//new gestore($val['Gestore_ID']);
}
else {
    $query = "SELECT * FROM gestore WHERE ID = '" . $comune->Info_ID . "'";
    $comune->Gestore = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"gestore");//new gestore($val['Info_ID']);
}

$query = "SELECT * FROM gestore WHERE ID = '" . $comune->Ufficio_ID . "'";
$comune->Ufficio = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"gestore");

$stemmaComune = $comune->Stemma_1;

$gestore = $comune->Gestore;
$tipo_gestore = $gestore->Tipo;
$stemmaGestore = $gestore->Stemma;

if($tipo_gestore == "Concessionario")
{
	if($stemmaGestore!="")
		$image_file = $stemmaGestore;
	else
		$image_file = WEB_ROOT."/immagini/sarida_logo.png";
}
else
	$image_file = $stemmaComune;*/

$aggiunta_pigno = "";
//ATTO
if($ID_Atto!=null)
{
    $query = "SELECT * FROM v_atti WHERE Atto_ID = ".$ID_Atto." AND CC = '".$c."'";
	$atto = $cls_db->getObjectLine($cls_db->ExecuteQuery($query));//new atto($ID_Atto, $c);
	switch($atto->Atto)
	{
		case "Avviso di intimazione ad adempiere": $tipo_atto = "Avv. Intimazione"; break;
		default: $tipo_atto = $atto->Atto;		break;
	}
}
else 
{
	$ID_Atto = $cls_help->getVar('ID_Pigno');
    $query = "SELECT * FROM v_pignoramento WHERE ID = ".$ID_Atto." AND CC = '".$c."'";
	$atto = $cls_db->getObjectLine($cls_db->ExecuteQuery($query));//new pignoramento($ID_Atto, $c);
	$aggiunta_pigno = " pignoramento";
	switch($atto->Tipo)
	{
		case "veicolo": $tipo_atto = "Pignoramento beni mobili registrati"; break;
		case "terzi": 	
			switch($atto->Tipo_Terzi)
			{
				case "lavoro": $tipo_atto = "Pignoramento presso datore di lavoro"; break;
				case "banca":  $tipo_atto = "Pignoramento presso banca"; break;
			}
				break;
			
		default: $tipo_atto = $atto->Tipo;		break;
	}
}
$settore = isset($atto->Tipo)?$atto->Tipo:$atto->Tipo_Riscossione;

$query = "SELECT * FROM parametri_pagamento WHERE CC = '".$c."' AND Tipo_Riscossione = '".$settore."'";
$par_pagamento = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_pagamento");

$autorizzazione_1 = $cls_stp->testo_autorizzazione(1,$par_pagamento);//AUTORIZZAZIONE BOLLETTINO 1
$td_1 = $par_pagamento->Bollettino_1;//TD BOLLETTINO 1

//PARTITA
$ID_partita = $atto->Comune_ID;
$anno_rif = $atto->Anno_Riferimento;
$rif_atto = $ID_partita."/".$anno_rif;

//$partita = new partita($atto->Partita_ID, $c );
/*$ID_partita = $atto->Comune_ID;
$anno_rif = $atto->Anno_Riferimento;
$settore = isset($atto->Tipo)?$atto->Tipo:$atto->Tipo_Riscossione;
$rif_atto = $ID_partita."/".$anno_rif;

//UTENTE
//$utente = new utente( $partita->Utente_ID , $c );
$query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$atto->Utente_ID."' AND Tipo = 'res'";
$atto->Residenza = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"indirizzo");
$atto->Residenza = $cls_stp->getToponimo($atto->Residenza,$c);

$query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$atto->Utente_ID."' AND Tipo = 'dom'";
$atto->Domicilio = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"indirizzo");
$atto->Domicilio = $cls_stp->getToponimo($atto->Domicilio,$c);
if($atto->Domicilio->ID==null){$atto->Domicilio = null;}

$query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$atto->Utente_ID."' AND Tipo = 'rec'";
$atto->Recapito = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"indirizzo");
$atto->Recapito = $cls_stp->getToponimo($atto->Recapito,$c);
if($atto->Recapito->ID==null) {$atto->Recapito = null;}


$nome_utente = $atto->Cognome_Ditta." ".$atto->Nome;
$utente_id = $atto->Comune_ID;
$indirizzo_destinatario = $cls_stp->righe_indirizzo_utente($atto);
$indirizzo_completo = $indirizzo_destinatario['Completo'];
$indirizzo_senza_provincia = $indirizzo_destinatario['Senza_Provincia'];

//PARAMETRI PAGAMENTO
$query = "SELECT * FROM parametri_pagamento WHERE CC = '".$c."' AND Tipo_Riscossione = '".$settore."'";
$par_pagamento = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"parametri_pagamento");
//$par_pagamento = new parametri_pagamento( $c, $settore );
$numeroContoCorrente = $par_pagamento->Numero_Conto;  //CONTO CORRENTE
$intestatarioConto = $par_pagamento->Intestatario_Conto;  //INTESTATARIO CONTO
$iban = $par_pagamento->IBAN;	//IBAN
$stemma = $par_pagamento->Stemma;
$stemma_2 = $par_pagamento->Stemma_2;
$autorizzazione_1 = $cls_stp->testo_autorizzazione(1,$par_pagamento);//AUTORIZZAZIONE BOLLETTINO 1
$td_1 = $par_pagamento->Bollettino_1;//TD BOLLETTINO 1
$ctrl_importo_1 = $par_pagamento->Importo_1;*/

/*********************************************** *************************************/
//ATTO
$tot_dovuto = $atto->Totale_Dovuto;

$num_rate = $atto->Rate_Previste;
$importo = explode("*",$atto->Importi_Rate);
$scadenza = explode("*",$atto->Scadenze_Rate);

//RIGA 1 CAUSALE BOLLETTINO
$riga1causale = $tipo_atto." n.".$atto->ID_Cronologico." del ".$atto->Anno_Cronologico." Rif.".$rif_atto;

$NW = new numero_letterale();
for($i=0;$i<count($importo);$i++)
{
	set_time_limit(30);

	$importo_let = number_format((float) $importo[$i],2,".","");
	$quinto_campo[] = $cls_stp->quinto_campo($atto,$i+1);
	$importo_letterale[] = $NW->converti_numero_bollettino($importo_let);
	$riga2causale[] = "SCADENZA PAGAMENTO RATA ".($i+1)." ENTRO IL ".$scadenza[$i];
}
/*************************************** ****************************************/
$a_enteAdmin = $cls_db->getArrayLine( $cls_db->SelectQuery("SELECT * FROM v_ente_gestito WHERE CC = '".$c."'") );
$a_paymentParams = $cls_db->getArrayLine($cls_db->SelectQuery($cls_params->getRecordsQuery("pagamento", $c, $atto->Tipo_Riscossione)));
if(!is_array($a_paymentParams)){
    $cls_help->alert("ATTENZIONE!!! Parametri di pagamento assenti per ".$atto->Tipo_Riscossione."!");
    echo "<script>window.close();</script>";
}

$cls_ente = new cls_ente($a_enteAdmin);
$cls_ente->setPrintHeader();
$cls_postal = new cls_postal($a_paymentParams);
$a_recipientHeader = $cls_registry->printHeader((array) $atto);

$cls_ruolo = new cls_ruolo();
$cls_ruolo->setResultArray((array) $atto);

$a_causal = $cls_ruolo->getReferences();

$importo = explode("*",$atto->Importi_Rate);
$scadenza = explode("*",$atto->Scadenze_Rate);


$cls_postal->setPostalParams($a_recipientHeader,$a_causal,$cls_ruolo->getPostalClient($a_enteAdmin['ID']));
$a_postal = array();
//$a_postal[2] = $cls_postal->getPostalArray(2,$cls_ente->logo,$atto->Totale_Dovuto + $atto->Diritto_Riscossione_Massimo);

/**
 ///////////////////////////////		PDF	    //////////////////////////////////
 */

$pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);
/*$pdf->SetAutoPageBreak(false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(0, 0, 0);*/

if($stampa_select == "PROVVISORIA")$printType = "temp";
else if($stampa_select == "DEFINITIVA") $printType = "final";



/*$pdf = new pdf_con_bollettino("P", "mm", "A4", true, 'UTF-8', false);
$pdf->SetAutoPageBreak(false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(0, 0, 0);
/**
 * 		//////////////	BOLLETTINO	//////////////
 */
if( $autorizzazione_1!=false || $td_1=="123" )
{

    //var_dump($a_paymentParams);
    for($i=0; $i<$num_rate; $i++) {

        $a_postal = $cls_postal->getPostalArray(1,$cls_ente->logo,(float) str_replace(",",".",$importo[$i]));
        $a_postal["causalRow1"] = $riga1causale;
        $a_postal["causalRow2"] = $riga2causale[$i];
        $a_postal["clientCode"] = $quinto_campo[$i];
        $a_postal["barCode_clientCode"] = "<".$quinto_campo[$i].">";
        //var_dump($a_postal[0]);
        $pdf->setSinglePostalBill($a_postal, $i, $printType);
    }
/*for($i=0;$i<count($importo);$i++)
{
	set_time_limit(30);
	
	$pdf->AddPage('L');
if($stampa_select == "PROVVISORIA")
	$pdf->stampa_provvisoria();
	
	$pdf->crea_bollettino();
	if($stemma == "")
		$pdf->logo_bollettino($image_file);
	else if($stemma == "ente")
		$pdf->logo_bollettino($stemmaComune);
	else if($stemma == "gestore")
		$pdf->logo_bollettino($stemmaGestore);
	$pdf->scelta_td_bollettino($td_1, $quinto_campo[$i] , $importo[$i] , "si" , $numeroContoCorrente );
	$pdf->iban_bollettino($iban);
	$pdf->intestatario_bollettino($intestatarioConto);
	$pdf->causale_bollettino($riga1causale, $riga2causale[$i]);
	$pdf->zona_cliente_bollettino($nome_utente, $indirizzo_destinatario);
	$pdf->autorizzazione_bollettino($autorizzazione_1);
	
	$i++; 
	
	if($i<count($importo))
	{
		$pdf->crea_bollettino_inverso();
		if($stemma_2 == "")
			$pdf->logo_bollettino($image_file,'due');
		else if($stemma_2 == "ente")
			$pdf->logo_bollettino($stemmaComune,'due');
		else if($stemma_2 == "gestore")
			$pdf->logo_bollettino($stemmaGestore,'due');
		$pdf->scelta_td_bollettino($td_1, $quinto_campo[$i] , $importo[$i] , "si" , $numeroContoCorrente, 'due');
		$pdf->iban_bollettino($iban,'due');
		$pdf->intestatario_bollettino($intestatarioConto,'due');
		$pdf->causale_bollettino($riga1causale, $riga2causale[$i],'due');
		$pdf->zona_cliente_bollettino($nome_utente, $indirizzo_destinatario,'due');
		$pdf->autorizzazione_bollettino($autorizzazione_1,'due');
	}
}*/
	
    $path = $cls_utils->crea_dir( ATTI ."/". $c . "/Documenti" );
    $nome_file = "Bollettini_rate_".$c."_".$atto->ID_Cronologico."_".$atto->Anno_Cronologico."_".date("Y-m-d_H-i").".pdf";



    if($stampa_select == "PROVVISORIA")
    {
        $pdf->Output( $nome_file , 'I');
        die;
    }
    else if($stampa_select == "DEFINITIVA")
        $pdf->Output( $path."/".$nome_file , 'F');


    $query = "SELECT MAX(Comune_ID) as Com FROM documento WHERE CC = '".$c."'";
    $result = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
    $comune_id = isset($result["Com"])?$result["Com"]:0;

    $salva = new stdClass();//new documento( null , $c );

    $salva->CC = $c;
    $salva->Comune_ID = $comune_id + 1;
    $salva->File = $nome_file;
    $salva->Utente_ID = $atto->Utente_ID;
    $salva->Tipo = "Inviato";
    $salva->Atto = "Bollettini rate".$aggiunta_pigno;
    $salva->Data_Creazione = date("Y-m-d");
    $salva->Informazioni_Aggiuntive = "Bollettini rate ".$tipo_atto;
    $salva->Oggetto = "";
    $salva->Contenuto = "";
    $salva->Data_Stampa = date("Y-m-d");

    $cls_db->Start_Transaction();
    $cls_db->Begin_Transaction();

    //$control_salva = $salva->Insert( true );

    $control_salva = $cls_db->DbSave($cls_utils->GetObjectQuery((array) $salva,"documento"));

    if( $control_salva )
    {
        $id_documento = $control_salva;
        $salva = new stdClass();

        $salva->ID_Bollettini_Rateizzazione = $id_documento;
        $control_salva = $cls_db->DbSave($cls_utils->GetObjectQuery((array) $salva,"atto", array("ID" => $ID_Atto)));//$atto->Update($ID_Atto);

        if( $control_salva )
        {
            $cls_db->End_Transaction();
            $log->info("File ".$nome_file." creato come stampa definitiva, aggiornamento DB completato.\nTabella atto ID = ".$ID_Atto."\nTabella documento ID = ".$id_documento);
            $pdf->Output( $path."/".$nome_file );
        }
        else
        {
            $cls_db->Rollback();
            $log->error("Aggiornamento tabella atto fallita per id = ".$ID_Atto);
        }

    }
    else
    {
        $cls_db->Rollback();
        $log->error("Inserimento tabella documento fallita");
    }
}
	
?>