<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

//include_once INC . "/headerAjax.php";
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/cls_Stampe.php";
include_once CLS . "/cls_CoazioneUtils.php";
include_once CLS . "/cls_elaborazioniUtils.php";


class MYPDF extends TCPDF {
	
	public function Header() {
		
		$this->SetFont('Arial', 'B', 11);
		$this->ln(5);
		$this->Cell(0, 5, "Elenco Pignoramenti" , 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}
	
	public function Footer() {

		$this->SetY(-10);
		$this->SetFont('helvetica', 'N', 7);
		$this->Cell(0, 5, "Pag. ". ($this->getPage() + 1) ." - ".date("d/m/Y H\hi:s"), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	
	}
	
}

$cls_db = new cls_db();
$cls_help = new cls_help();
$utils = new cls_Utils();
$stampe = new cls_Stampe();
$date = new cls_DateTimeI("DB",false);
$coaz = new cls_Coazione();
$elab = new cls_elaborazioniUtils();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$_SESSION['progress'] = "0.00";
session_write_close();

$query = "SELECT * FROM enti_gestiti WHERE CC = '".$c."'";
$nome_com = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"enti_gestiti")["Denominazione"];

$a_docs = $cls_db->getResults($cls_db->ExecuteQuery("SELECT * FROM document_type WHERE TableTypeId=2 AND EnabledHtml=1"),"array","Id");

$query = "SELECT * FROM forma_giuridica_societa WHERE CC = '*****'";
$temp = $cls_db->getResults($cls_db->ExecuteQuery($query));
$results = array();

for($i=0; $i < count($temp); $i++)
{
    $results[$temp[$i]['ID']] = $temp[$i];
}

$array_forma = $results;
//var_dump($array_forma);

/*$forma = new forma_giuridica();
$array_forma = $forma->array_completo();*/

//PREPARAZIONE ELENCO
$elenco_dir = $utils->crea_dir( ATTI ."/". $c . "/Pignoramenti/Elenchi" );
$data_file = date('Y-m-d_H-i-s');

$file_elenco = $elenco_dir."/elenco_pignoramenti_".$data_file.".pdf";
$download = $file_elenco;

$vedi_file = SUPER_WEB_ROOT.$utils->mostra_file_path($download);

//COGNOME NOME
$daco  = strtoupper($cls_help->getVar('daco'));
$acog  = strtoupper($cls_help->getVar('acog'));
$dano  = strtoupper($cls_help->getVar('dano'));
$anom  = strtoupper($cls_help->getVar('anom'));

//PARTITA
$da_partita  = $cls_help->getVar('da_n_elenco');
$a_partita  = $cls_help->getVar('a_n_elenco');

//ANNI RIFERIMENTO
$da_anno = $cls_help->getVar('da_anno');
$ad_anno = $cls_help->getVar('ad_anno');

$from_cronoYear = $cls_help->getVar('from_cronoYear');
$to_cronoYear = $cls_help->getVar('to_cronoYear');

$paymentStatus = $cls_help->getVar('paymentStatus');

//TIPO_PIGNORAMENTO
$DocumentTypeId = $cls_help->getVar('DocumentTypeId');
$tipo_terzo = $cls_help->getVar('presso_terzi');
$tax_type = $cls_help->getVar('taxType');
$flag_notifica = $cls_help->getVar('FlagNotifica');

//DATA ELABORAZIONE
$data_elab = $cls_help->getVar('data_elab');
$da_elab = $cls_help->getVar('da_elab');
$a_elab = $cls_help->getVar('a_elab');

//DATA NOTIFICA
$data_notif = $cls_help->getVar('data_notif');
$da_notif = $cls_help->getVar('da_notif');
$a_notif = $cls_help->getVar('a_notif');

//DATA SPEDIZIONE
$data_spedizione = $cls_help->getVar('data_spedizione');
$da_sped = $cls_help->getVar('da_sped');
$a_sped = $cls_help->getVar('a_sped');

//DATA CONSEGNA
$data_consegna = $cls_help->getVar('data_consegna');
$da_cons = $cls_help->getVar('da_cons');
$a_cons = $cls_help->getVar('a_cons');

//UFFICIALE
$tipo_ufficiale = $cls_help->getVar('tipo_ufficiale');

// //STATI NOTIFICA ( modalita' - giacenza[ind validato] - anomalie )
// $modalita_notif = $cls_help->getVar('modalita');
// $stato_giacenza = $cls_help->getVar('giacenza');
// $indirizzo_validato = $cls_help->getVar('indirizzo_validato');
// $anomalie = $cls_help->getVar('anomalie');

//DATA STAMPA
$data_stampa = $cls_help->getVar('data_stampa');
$da_stampa = $cls_help->getVar('da_stampa');
$a_stampa = $cls_help->getVar('a_stampa');

//STATO STAMPA
$stato_stampa = $cls_help->getVar('stato_stampa');

//BLOCCO COAZIONE
$blocco = $cls_help->getVar('blocco');

//TRIBUNALE
$filtro_tribunale = $cls_help->getVar('tribunale');

//SALTA PAGINA
$filtro_salta = $cls_help->getVar('salta');

//ORDINAMENTO
$ordinamento = $cls_help->getVar('ordinamento');

//ANOMALIA
$select_anomalia = $cls_help->getVar('anomalia');

$par_annuali = $cls_db->getArrayLine($cls_db->ExecuteQuery("SELECT * FROM parametri_annuali WHERE CC='".$c."' ORDER BY Anno DESC LIMIT 1"));

/** 	SELEZIONE PIGNORAMENTI	*/
$campi_stati = array("PIG_GEN.Stato_Stampa", "PIG_GEN.Tipo_Ufficiale");
$valori_stati = array ($stato_stampa, $tipo_ufficiale);

$query_stati = $stampe->where_campi($campi_stati, $valori_stati);

$campi_array = array();
$array_da_data = array();
$array_a_data = array();
$date_vuote = array();
if($data_elab!="assente")
{
	$campi_array[] = "Data_Elaborazione";
	$array_da_data[] = $date->GetDateDB($da_elab,"IT");
	$array_a_data[] = $date->GetDateDB($a_elab,"IT");
}
else
	$date_vuote[] = "PIG_GEN.Data_Elaborazione";

if($data_notif!="assente")
{
	$campi_array[] = "Data_Notifica";
	$array_da_data[] = $date->GetDateDB($da_notif,"IT");
	$array_a_data[] = $date->GetDateDB($a_notif,"IT");
}
else
	$date_vuote[] = "PIG_GEN.Data_Notifica";

if($data_consegna!="assente")
{
	$campi_array[] = "Data_Consegna";
	$array_da_data[] = $date->GetDateDB($da_cons,"IT");
	$array_a_data[] = $date->GetDateDB($a_cons,"IT");
}
else
	$date_vuote[] = "PIG_GEN.Data_Consegna";

if($data_spedizione!="assente")
{
	$campi_array[] = "Data_Spedizione";
	$array_da_data[] = $date->GetDateDB($da_sped,"IT");
	$array_a_data[] = $date->GetDateDB($a_sped,"IT");
}
else
	$date_vuote[] = "PIG_GEN.Data_Spedizione";

if($data_stampa!="assente")
{
	$campi_array[] = "Data_Stampa";
	$array_da_data[] = $date->GetDateDB($da_stampa,"IT");
	$array_a_data[] = $date->GetDateDB($a_stampa,"IT");
}
else
	$date_vuote[] = "PIG_GEN.Data_Stampa";


$query = "";
for( $i=0; $i<count($date_vuote); $i++ )
{
    //$query .= "( ".$date_vuote[$i]." = null OR ".$date_vuote[$i]." = '0000-00-00' ) AND ";
	$query .= "( ".$date_vuote[$i]." IS null ) AND ";
}

$query .= "1";

$query_date_vuote = $query;//where_date_vuote($date_vuote);

$where_pigno = array();

$query = "";
for( $i=0 ; $i<count($array_da_data) ; $i++ )
{
    if( $array_da_data[$i] != null && $array_da_data[$i] != "" )
    {

        if($query!="")
            $query.= "AND ";

        $query.= "( PIG_GEN.".$campi_array[$i]." >= '".$array_da_data[$i]."' AND PIG_GEN.".$campi_array[$i]." <= '".$array_a_data[$i]."' ) ";

    }
}


if((int) $flag_notifica > 0){
	if($query!="")
        $query.= "AND ";

	switch((int) $flag_notifica){
		case 1:
			$query .= "PIG_GEN.Data_Notifica IS NULL ";
			break;
		case 2:
			$query .= "PIG_GEN.Data_Notifica IS NOT NULL ";
			break;
		default: break;
	}
}

if(!empty($from_cronoYear)){
	if($query!="")
        $query.= "AND ";

    $query.= "( PIG_GEN.Anno_Cronologico >= ".$from_cronoYear." ";
	if(!empty($to_cronoYear))
		$query.= " AND PIG_GEN.Anno_Cronologico <= ".$to_cronoYear." ";
	$query.= ") ";

}

if(!empty($da_partita)){
	if($query!="")
        $query.= "AND ";

    $query.= "( PIG_GEN.Partita_Comune_ID >= ".$da_partita." ";
	if(!empty($a_partita))
		$query.= " AND PIG_GEN.Partita_Comune_ID <= ".$a_partita." ";
	$query.= ") ";

}

if( $da_anno != null && $ad_anno != null ){
	if($query!="")
        $query.= "AND ";
	$query.= "PIG_GEN.Anno_Riferimento >= '".$da_anno."' AND PIG_GEN.Anno_Riferimento <= '".$ad_anno."' ";
}



if(!empty($daco)){
	if ($query != "")
			$query .= "AND ";
	if(!empty($dano))
		$query.= "( PIG_GEN.Cognome_Ditta > '".addslashes($daco)."' OR ( PIG_GEN.Cognome_Ditta = '".addslashes($daco)."' AND PIG_GEN.Nome>= '".addslashes($dano)."') ) ";
	else
		$query.= "PIG_GEN.Cognome_Ditta >= '".addslashes($daco)."' ";
	if(!empty($acog)){

		if(!empty($anom))
		$query.= "AND ( PIG_GEN.Cognome_Ditta < '".addslashes($acog)."' OR ( PIG_GEN.Cognome_Ditta = '".addslashes($acog)."' AND PIG_GEN.Nome<= '".addslashes($anom)."') ) ";
		else
			$query.= "AND PIG_GEN.Cognome_Ditta <= '".addslashes($acog)."' ";
	}
}

switch($paymentStatus){
	case "no":
		if ($query != "")
			$query .= "AND ";
		$query .= " PIG_GEN.TOTALE_PAGAMENTI is null ";
		break;
	case "partial":
	case "completed":
	case "yes":
		if ($query != "")
			$query .= "AND ";
		$query .= " PIG_GEN.TOTALE_PAGAMENTI > 0 ";
		break;

}

if($tax_type!=""){
    if($query!="")
        $query.= "AND ";
    //$query.= "PIG_GEN.Tipo = '".$tax_type."' ";
	$query.= "PIG_GEN.Tipo_Riscossione = '".$tax_type."' ";
}


$where_pigno[0] = $query;//selezione_date_query( "PIG_GEN", $campi_array , $array_da_data , $array_a_data );
$where_pigno[1] = $query_stati;
$where_pigno[2] = $query_date_vuote;

//$pignoramento = new pignoramento(null, $c);
$query_pignoramenti = $stampe->query_pignoramenti_docId($c, $DocumentTypeId, $ordinamento, $where_pigno);

//var_dump($query_pignoramenti);die;
$array_pignoramenti = $cls_db->getResults($cls_db->ExecuteQuery($query_pignoramenti));//mysql_array($query_pignoramenti);

if($_SESSION['username']=="mirkop"){
	// echo $query_pignoramenti;
	// die;
}


/**
	///////////////////////////////		PDF	    //////////////////////////////////
*/
    $pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);
	//$pdf = new MYPDF("P", "mm", "A4", true, 'UTF-8', false);
	//$pdf->setPrintHeader(false);
	$pdf->SetMargins(10, 10, 10);
	
	
	$styleDash = array('dash' => '6,6');
	$styleRetta = array('dash' => '0');
	
	$pdf->AddPage('L');
	$pdf->SetFont('Arial', 'B', 10);
	
	$dim_pag = $pdf->getPageDimensions();
	$larghezza_pag = $pdf->getPageWidth();
	$altezza_pag = $pdf->getPageHeight();	
	
	
	$pdf->SetAutoPageBreak(false);
	$pdf->Ln(5);
	
	$array_width = array();
	$array_intestaz_1 = array();
	$array_intestaz_2 = array();
	$array_intestaz_3 = array();
	$array_intestaz_4 = array();
	$array_intestaz_5 = array();
	
	$intestazione_variabile = "";
	$intestazione_variabile_2 = "Tribunale";
	if($DocumentTypeId==6)
	{
		$intestazione_variabile = "Targa";
		$intestazione_variabile_2 = "Tribunale";
	}
	
	if($DocumentTypeId==8){
		$intestazione_variabile = "Riscontri";
	}
		
	$array_width = array( 30 , 50 , 115 , ($larghezza_pag-225-20) , 30 );
	
	$array_intestaz_1[] = "Cronologico";				
	$array_intestaz_1[] = "COD-Utente";
	$array_intestaz_1[] = "Indirizzo";					
	$array_intestaz_1[] = "Data Elaborazione - Stampa";	
	$array_intestaz_1[] = $intestazione_variabile;						
	
	$array_intestaz_2[] = "Partita";
	$array_intestaz_2[] = "CF / PI";
	$array_intestaz_2[] = "Informazioni Cartella";
	$array_intestaz_2[] = "Data Consegna - Spedizione";
	$array_intestaz_2[] = $intestazione_variabile_2;
	
	$array_intestaz_3[] = "Pignoramento";
	$array_intestaz_3[] = "Dovuto Coazione";
	$array_intestaz_3[] = "Totale Dovuto ( Tot 1 - 2 - 3 )";
	$array_intestaz_3[] = "Data Pagamento";
	$array_intestaz_3[] = "Pagato";
	
	$array_intestaz_4[] = "ATTI PREC.";
	$array_intestaz_4[] = "";
	$array_intestaz_4[] = "";
	$array_intestaz_4[] = "";
	$array_intestaz_4[] = "";
	
	$array_intestaz_5[] = "ANOMALIE";
	$array_intestaz_5[] = "( Se presenti )";
	$array_intestaz_5[] = "";
	$array_intestaz_5[] = "";
	$array_intestaz_5[] = "";
	
	$array_align_1 = array("L","L","L","L","L");
	$array_align_2 = array("L","L","L","L","R");
	
	$pdf->setCellPaddings(2,1,2,0);
    $y1_vert = $pdf->setRow($array_intestaz_1,"up",$styleRetta,$array_align_1,0,$array_width);
	//$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_1, "up" , $styleRetta, $array_align_1);
	
	$pdf->setCellPaddings(2,0,2,0);
    $y1_vert = $pdf->setRow($array_intestaz_2,"no",$styleRetta,$array_align_1,0,$array_width);
	//$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_2, "no" , $styleRetta, $array_align_1);

    $y1_vert = $pdf->setRow($array_intestaz_3,"no",$styleRetta,$array_align_2,0,$array_width);
	//$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_3, "no" , $styleRetta, $array_align_2);
    $y1_vert = $pdf->setRow($array_intestaz_4,"no",$styleRetta,$array_align_2,0,$array_width);
	//$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_4, "no" , $styleRetta, $array_align_2);
	
	$pdf->setCellPaddings(2,0,2,1);
    $y1_vert = $pdf->setRow($array_intestaz_5,"down",$styleRetta,$array_align_2,0,$array_width);
	//$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_5, "down" , $styleRetta, $array_align_2);
	
/**
	//////////////////////////////////////////////////////////////////////////////
*/
		
	$cont_result = 0;
	
	$parz_dovuto = 0.00;
	$parz_pagato = 0.00;
	
	$tot_gen_dovuto = 0.00;
	$tot_gen_pagato = 0.00;
	
	$dovuto_complessivo_atti_precedenti	= 0.00;
	$totale_complessivo_1				= 0.00;
	$totale_complessivo_2				= 0.00;
	$totale_complessivo_3				= 0.00;
	$totale_parziale = 0.00;
	
	$dovuto_gen_complessivo_atti_precedenti = 0.00;
	$tot_gen_complessivo_1				= 0.00;
	$tot_gen_complessivo_2				= 0.00;
	$tot_gen_complessivo_3				= 0.00;
	$totale_generale = 0.00;

	$tribunale_pagina = "";
	$control_pagine = 0;
	$ctrl_linea = "no";
	$cont = 0;
	$num_pignoramenti = count($array_pignoramenti);
	for( $l=0; $l < $num_pignoramenti; $l++ )//FOR PIGNORAMENTI
	{	
		set_time_limit(100);
		
		if(session_status() == PHP_SESSION_NONE)session_start();
		$_SESSION['progress'] = number_format(($l*100)/$num_pignoramenti ,2);
		session_write_close();
		
		if($blocco=="Si")
		{
			if($array_pignoramenti[$l]['Flag_Blocco_Coazione']!="si")
				break;
		}

		if($blocco=="No")
		{
			if($array_pignoramenti[$l]['Flag_Blocco_Coazione']=="si")
				break;
		}

		set_time_limit(30);

		/*$query = "SELECT * FROM partita_tributi WHERE ID = '".$array_partite[$k]['ID']."' AND CC = '".$c."'";
		if($a!=null)	$query.=" AND Anno_Riferimento = '".$array_partite[$k]['Anno_Riferimento']."'";
		$partita = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"partita_tributi");
		$query = "SELECT * FROM atto WHERE Partita_ID = '".$partita->ID."'";
		$partita->Atto = $cls_db->getResults($cls_db->ExecuteQuery($query),"object");*/
		$partita = $stampe->getDataPartita($array_pignoramenti[$l]['Partita_ID'],$c,$array_pignoramenti[$l]['Anno_Riferimento']);

		//$partita = new partita($array_partite[$k]['ID'], $c, $array_partite[$k]['Anno_Riferimento']);

		//$query = "SELECT * FROM pignoramento_generale WHERE ID = ".$array_pignoramenti[$l]['ID']." AND CC = '".$c."'";
		//$pignoramento = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"pignoramento_generale");//
		//$pignoramento = new pignoramento( $array_pignoramenti[$l]['ID'], $c );

		$pignoramento = $coaz->GetDataPigno($array_pignoramenti[$l]['ID'],$c,"object");

		$sommaImporti = 0;
		switch ($pignoramento->Tipo)
		{
			case "terzi":

				if($pignoramento->Tipo_Terzi=="banca"){
					for( $i=0; $i<count($pignoramento->Presso_Terzi); $i++ )
					{
						for( $y=0; $y<count($pignoramento->Presso_Terzi[$i]->Notifiche_Terzo); $y++ )
						{
							$sommaImporti += $pignoramento->Presso_Terzi[$i]->Notifiche_Terzo[$y]->Importo_Riscontro;
						}
					}
				}

				break;

		}

		//return $sommaImporti;

		$importi_riscontri = $sommaImporti;// $pignoramento->importiRiscontri();
		
		$ID_Com_Partita = $array_pignoramenti[$l]['Partita_Comune_ID'];
		$query = "SELECT * FROM utente WHERE ID = '".$array_pignoramenti[$l]['Utente_ID']."' AND CC_Comune = '".$c."'";
		$utente = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"utente");
		$utente = $elab->GetDataUtente($utente);
		//$utente = new utente( $array_partite[$k]['Utente_ID'] , $c );
		$query = "SELECT * FROM ufficio_giudiziario WHERE CC = '".$c."' AND Tipo = 'tribunale' LIMIT 1";
		$tribunale = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"ufficio_giudiziario");

		//$tribunale = new ufficio_giudiziario($utente->Residenza->CC_Indirizzo, "tribunale");
		$query = "SELECT * FROM ufficio_giudiziario WHERE CC = '".$tribunale->CC_Ufficio."' AND Tipo = 'istituto' LIMIT 1";
		$ufficio_vendite = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"ufficio_giudiziario");
		
		//CONTROLLO TRIBUNALE
		if($filtro_tribunale!=null)
		{
			if($tribunale->CC_Ufficio != $filtro_tribunale)
				break;
		}

		$indirizzo_utente = $stampe->righe_indirizzo($utente,"st");//$utente->righe_indirizzo();


		$forma_descr = "";
		if($utente->Forma_Giuridica!='')
		{
			$index_value = $utente->Forma_Giuridica;
			$forma_descr = isset($array_forma[$index_value])?$array_forma[$index_value]['Sigla']: null;
		}
			
		$nome_utente = $utente->Cognome.$utente->Ditta." ".$utente->Nome.$forma_descr;
		
		if( strlen($nome_utente) > 22 )
			$nome_utente = substr($nome_utente,0,21)."...";
		
		if( $utente->Genere=="D" )
			$CF_PI = $utente->Partita_Iva;
		else 
			$CF_PI = $utente->Codice_Fiscale;
									
		$info_cart = $partita->Tributo[0]->Info_Cartella;
		

		//CONTROLLI
		$anomalia = "";
		$control_anomalia = 0;

		$query = "SELECT * FROM atto WHERE ID = '".$pignoramento->Atto_ID."' AND CC = '".$c."'";
		$atto = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"atto");//new atto($pignoramento->Atto_ID, $c);
		if($atto->Data_Notifica=="" || $atto->Data_Notifica == null)
		{
			$anomalia.= $atto->Atto." n.".$atto->ID_Cronologico." del ".$atto->Anno_Cronologico." in attesa di verifica!  ;  ";
			$control_anomalia = 1;
		}
		
		if($pignoramento->Tipo=="veicolo")
		{						
			if($ufficio_vendite->Denominazione=="")
			{
				$anomalia.= "Parametri Tribunale / Istituto vendite giudiziarie ASSENTI!  ;  ";
				$control_anomalia = 1;
			}
			else if($ufficio_vendite->PEC=="")
			{
				if($pignoramento->Notifica_Istituto[0]->Modalita_Stampa=="pec")
				{
					$anomalia = "PEC Istituto vendite giudiziarie ASSENTE!  ;  ";
					$control_anomalia = 1;
				}
				else
					$anomalia = "PEC Istituto vendite giudiziarie ASSENTE ( Modalita' di Invio diversa da PEC )  ;  ";
			}
		
			if(!isset($pignoramento->Veicolo[0]))
			{
				$anomalia.= "Nessun VEICOLO presente nel pignoramento!  ;  ";
				$control_anomalia = 1;
			}
			else
			{
				for($y=0;$y<count($pignoramento->Veicolo);$y++)
				{
					if($pignoramento->Veicolo[$y]->Valore_Veicolo==0)
					{
			
						$anomalia.= strtoupper($pignoramento->Veicolo[$y]->Tipo_Veicolo)." ".$pignoramento->Veicolo[$y]->Marca_Veicolo." ";
						$anomalia.= $pignoramento->Veicolo[$y]->Modello_Veicolo." ";
						$anomalia.= "sprovvisto dell'indicazione del valore!  ;  ";
						$control_anomalia = 1;
						
					}
					
					if($pignoramento->Veicolo[$y]->Data_Visura==null)
					{
						$anomalia.= strtoupper($pignoramento->Veicolo[$y]->Tipo_Veicolo)." ".$pignoramento->Veicolo[$y]->Marca_Veicolo." ";
						$anomalia.= $pignoramento->Veicolo[$y]->Modello_Veicolo." ";
						$anomalia.= "sprovvisto della Data della Visura!  ;  ";
						$control_anomalia = 1;
					}
				}
			}

			if($control_anomalia==0 && $select_anomalia=="si")
				break;
		}
		
		if($select_anomalia=="no" && $control_anomalia==1)
		{
			break;
		}		
		
		if($filtro_salta=="tribunale")
		{
			if($tribunale->CC_Ufficio != $tribunale_pagina && $tribunale_pagina != "")
			{
				$y2_vert = $pdf->getY();
			
// 							crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);
			
			$array_width_fine = array();
			$array_fine_1 = array();
			$array_fine_2 = array();
			
			$array_width_fine = array( 70 , 40 , 40 , 40 , 40, $larghezza_pag - 230 - 20 );
	
			
			$array_fine_1[] = "PARZIALI DI PAGINA";		
			$array_fine_1[] = "Totale coazione";
			$array_fine_1[] = "Totale dovuto";//"Totale 1 complessivo";
			$array_fine_1[] = "";//"Totale 2 complessivo";
			$array_fine_1[] = "";//"Totale 3 complessivo";
			$array_fine_1[] = "Totale pagato";	
			
			$array_fine_2[] = "";
			$array_fine_2[] = number_format($dovuto_complessivo_atti_precedenti,2,",",".")." Euro";
			$array_fine_2[] = number_format($totale_parziale,2,",",".")." Euro";//number_format($totale_complessivo_1,2,",",".")." Euro";
			$array_fine_2[] = "";//number_format($totale_complessivo_2,2,",",".")." Euro";
			$array_fine_2[] = "";//number_format($totale_complessivo_3,2,",",".")." Euro";
			$array_fine_2[] = number_format($parz_pagato,2,",",".")." Euro";
			
			$array_align_fine = array("L","R","R","R","R","R");
			
			$dovuto_complessivo_atti_precedenti = 0.00;
			$totale_complessivo_1 = 0.00;
			$totale_complessivo_2 = 0.00;
			$totale_complessivo_3 = 0.00;
			$totale_parziale = 0.00;
			$parz_pagato = 0.00;
			
			$pdf->SetFont('Arial', 'B', 8);
			
			$pdf->setCellPaddings(2,1,2,0);
			$y = $pdf->setRow($array_fine_1,"up",$styleRetta,$array_align_fine,0,$array_width_fine);
			//$y = crea_riga($pdf , $array_width_fine, $array_fine_1 , "up" , $styleRetta , $array_align_fine );
			$pdf->setCellPaddings(2,0,2,1);
			$y = $pdf->setRow($array_fine_2,"down",$styleRetta,$array_align_fine,0,$array_width_fine);
			//$y = crea_riga($pdf , $array_width_fine, $array_fine_2 , "down" , $styleRetta, $array_align_fine );
			
				$pdf->AddPage();
				$pdf->Ln(5);
				
				$pdf->SetFont('Arial', 'B', 8);
				
				$pdf->setCellPaddings(2,1,2,0);
				$y1_vert = $pdf->setRow($array_intestaz_1,"up",$styleRetta,$array_align_1,0,$array_width);
				
				$pdf->setCellPaddings(2,0,2,0);
				$y1_vert = $pdf->setRow($array_intestaz_2,"no",$styleRetta,$array_align_1,0,$array_width);
				$y1_vert = $pdf->setRow($array_intestaz_3,"no",$styleRetta,$array_align_2,0,$array_width);
				$y1_vert = $pdf->setRow($array_intestaz_4,"no",$styleRetta,$array_align_2,0,$array_width);
				
				$pdf->setCellPaddings(2,0,2,1);
				$y1_vert = $pdf->setRow($array_intestaz_5,"down",$styleRetta,$array_align_2,0,$array_width);

				// $pdf->setCellPaddings(2,1,2,0);
				// $y1_vert = $pdf->setRow($array_intestaz_1,"up",$styleRetta,null,0,$array_width);
				// //$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_1, "up" , $styleRetta);
				
				// $pdf->setCellPaddings(2,0,2,1);
				// $y1_vert = $pdf->setRow($array_intestaz_2,"down",$styleRetta,null,0,$array_width);
				// //$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_2, "down" , $styleRetta);

				$ctrl_linea = "no";

			}
				
			$tribunale_pagina = $tribunale->CC_Ufficio;
		}

		$Dovuto_Atti_Precedenti = number_format($pignoramento->Importo_Dovuto,2,",",".");

		$pignoramento = $coaz->gestione_totali($pignoramento,"object");
		$TOTALI_ARRAY = $pignoramento->Totali_Array;

		if(!$array_pignoramenti[$l]['TOTALE_PAGAMENTI']>0)
			$array_pignoramenti[$l]['TOTALE_PAGAMENTI'] = 0.00;
		if(!$array_pignoramenti[$l]['Totale_Dovuto']>0)
			$checkTotale = 0.00;

		if($array_pignoramenti[$l]['Rate_Previste']>0){
			if($array_pignoramenti[$l]['Tipo_Totale_Rate']==1)
				$checkTotale = $pignoramento->Totali_Num[1];
			else if($array_pignoramenti[$l]['Tipo_Totale_Rate']==2)
				$checkTotale = $pignoramento->Totali_Num[2];
			else if($array_pignoramenti[$l]['Tipo_Totale_Rate']==3)
				$checkTotale = $pignoramento->Totali_Num[3];
		}
		else{
			if($pignoramento->Totali_Num[3]>0)
				$checkTotale = $pignoramento->Totali_Num[3];
			else if($pignoramento->Totali_Num[2]>0)
				$checkTotale = $pignoramento->Totali_Num[2];
			else
				$checkTotale = $pignoramento->Totali_Num[1];
		}

		if(is_null($array_pignoramenti[$l]['TOTALE_PAGAMENTI']))
			$array_pignoramenti[$l]['TOTALE_PAGAMENTI'] = 0;
		$continue = 0;
		switch($paymentStatus){
			case "incompleted":
				if(!($array_pignoramenti[$l]['TOTALE_PAGAMENTI']<=0) && 
				!($array_pignoramenti[$l]['TOTALE_PAGAMENTI']>0 && $checkTotale > $array_pignoramenti[$l]['TOTALE_PAGAMENTI']+$par_annuali['Importo_Minimo']))
					$continue = 1;
				break;
			case "partial":
				if(!($array_pignoramenti[$l]['TOTALE_PAGAMENTI']>0 && $checkTotale > $array_pignoramenti[$l]['TOTALE_PAGAMENTI']+$par_annuali['Importo_Minimo']))
					$continue = 1;
				break;
			case "completed":
				if($checkTotale > $array_pignoramenti[$l]['TOTALE_PAGAMENTI']+$par_annuali['Importo_Minimo'])
					$continue = 1;
				break;
		}
		if($continue==1)
			continue;

		$dovuto_complessivo_atti_precedenti	+= $pignoramento->Importo_Dovuto;
		$totale_parziale					+= $checkTotale;
		$totale_complessivo_1				+= $pignoramento->Totali_Num[1];
		$totale_complessivo_2				+= $pignoramento->Totali_Num[2];
		$totale_complessivo_3				+= $pignoramento->Totali_Num[3];
		$parz_pagato          				+= $array_pignoramenti[$l]['TOTALE_PAGAMENTI'];

		// echo $totale_complessivo_1." - ".$totale_complessivo_2." - ".$totale_complessivo_3."<br><br>";
		
// 						alert($totale_complessivo_1." - ".$totale_complessivo_2." - ".$totale_complessivo_3);
		$dovuto_gen_complessivo_atti_precedenti	+= $pignoramento->Importo_Dovuto;
		$totale_generale					+= $checkTotale;
		$tot_gen_complessivo_1				+= $pignoramento->Totali_Num[1];
		$tot_gen_complessivo_2				+= $pignoramento->Totali_Num[2];
		$tot_gen_complessivo_3				+= $pignoramento->Totali_Num[3];
		$tot_gen_pagato 					+= $array_pignoramenti[$l]['TOTALE_PAGAMENTI'];
		// echo $tot_gen_complessivo_1." - ".$tot_gen_complessivo_2." - ".$tot_gen_complessivo_3."<br><br>";

		$atti_precedenti = "";
		$array_atti = $stampe->tutti_gli_atti_notificati_pigno($partita);
		
		for($conta_atti=count($array_atti)-1;$conta_atti>=0;$conta_atti--)
		{
			$atti_precedenti.= $array_atti[$conta_atti]."  ;  ";
		}

		$date->changeFormat("IT");
		
		$data_elaborazione = $date->Get_DateNewFormat($pignoramento->Data_Elaborazione,"DB");
		if($data_elaborazione==null)	$data_elaborazione = "Assente";
		
		$data_consegna = $date->Get_DateNewFormat($pignoramento->Data_Consegna,"DB");
		if($data_consegna==null)	$data_consegna = "Assente";
		
		$data_stampa = $date->Get_DateNewFormat($pignoramento->Data_Stampa,"DB");
		if($data_stampa==null)	$data_stampa = "Assente";
		
		$data_spedizione = $date->Get_DateNewFormat($pignoramento->Data_Spedizione,"DB");
		if($data_spedizione==null)	$data_spedizione = "Assente";
		
		$info_variabile = "";
		$info_variabile_2 = strtoupper($tribunale->Comune);
		$tipo_pigno_visual = $pignoramento->DocumentNotes;
		if($pignoramento->Tipo == "veicolo")
		{
			if(isset($pignoramento->Veicolo[0]))
				$info_variabile = strtoupper($pignoramento->Veicolo[0]->Targa_Veicolo);
			else
				$info_variabile = "Assente";

			$info_variabile_2 = strtoupper($tribunale->Comune);
		}

		if($pignoramento->Tipo_Terzi == "banca"){
			$info_variabile = number_format($importi_riscontri,2,",",".")." Euro";
		}

		

		
		
		$pdf->SetFont('Arial', '', 8);

		$array_value_1 = array();
		$array_value_2 = array();
		$array_value_3 = array();
		
		$array_value_1[] = $pignoramento->ID_Cronologico."/".$pignoramento->Anno_Cronologico;
		$array_value_1[] = "(".$utente->Comune_ID.") ".$nome_utente;
		$array_value_1[] = $indirizzo_utente['Completo'];						
		$array_value_1[] = $data_elaborazione." - ".$data_stampa;
		$array_value_1[] = $info_variabile;
		
		$array_value_2[] = "Partita ".$ID_Com_Partita."/".$partita->Anno_Riferimento;
		$array_value_2[] = strtoupper($CF_PI);
		$array_value_2[] = $info_cart;
		$array_value_2[] = $data_consegna." - ".$data_spedizione;
		$array_value_2[] = $info_variabile_2;
		
		$array_value_3[] = $tipo_pigno_visual;
		$array_value_3[] = $Dovuto_Atti_Precedenti." Euro";
		$array_value_3[] = number_format($checkTotale,2,",",".")." Euro (1: ".$TOTALI_ARRAY[1]." Euro - 2: ".$TOTALI_ARRAY[2]." Euro - 3: ".$TOTALI_ARRAY[3]." Euro )";
		$array_value_3[] = "Assente";
		if(!empty($array_pignoramenti[$l]['TOTALE_PAGAMENTI']))
			$totPagato = number_format($array_pignoramenti[$l]['TOTALE_PAGAMENTI'],2,",","");
		else
			$totPagato = "0,00";
		$array_value_3[] = $totPagato." Euro";
		
		$pdf->setCellPaddings(2,2,2,0);
		$y = $pdf->setRow($array_value_1,$ctrl_linea,$styleDash,$array_align_1,0,$array_width);
		//$y = crea_riga($pdf , $array_width, $array_value_1 , $ctrl_linea , $styleDash , $array_align_1 );
		
		if($ctrl_linea == "no")	$ctrl_linea = "up";
		
		if( $y > $altezza_pag - 60)
		{
			$pdf->setCellPaddings(2,0,2,1);
			$y = $pdf->setRow($array_value_2,"no",$styleDash,$array_align_1,0,$array_width);
			//$y = crea_riga($pdf , $array_width, $array_value_2 , "no" , $styleDash, $array_align_1 );
			$pdf->SetFont('Arial', 'B', 8);
			$y = $pdf->setRow($array_value_3,"no",$styleDash,$array_align_2,0,$array_width);
			//$y = crea_riga($pdf , $array_width, $array_value_3 , "no" , $styleDash, $array_align_2 );
			
			$pdf->SetFont('Arial', 'B', 8);
			$pdf->Cell( 30 , 0, "ATTI:" );
			$pdf->SetFont('Arial', '', 8);
			$pdf->setCellPaddings(2,1,2,1);
			$pdf->MultiCell( 0 , 0, $atti_precedenti , 0 , "L" , 0 , 1 , '' , '' , true );
				
			if($anomalia != "")
			{
			
				$pdf->setCellPaddings(2,1,2,2);
				$pdf->SetFont('Arial', 'B', 8);
				$pdf->Cell( 30 , 0, "ANOMALIE:" );
				$pdf->SetFont('Arial', '', 8);
				$pdf->setCellPaddings(2,1,2,2);
				$pdf->MultiCell( 0 , 0, $anomalia , 0 , "L" , 0 , 1 , '' , '' , true );
			
			}
			
			
			$y2_vert = $pdf->getY();
				
			// 							crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);
				
			$array_width_fine = array();
			$array_fine_1 = array();
			$array_fine_2 = array();
				
			$array_width_fine = array( 70 , 40 , 40 , 40 , 40, $larghezza_pag - 230 - 20 );
				
			$array_fine_1[] = "PARZIALI DI PAGINA";		
			$array_fine_1[] = "Totale coazione";
			$array_fine_1[] = "Totale dovuto";//"Totale 1 complessivo";
			$array_fine_1[] = "";//"Totale 2 complessivo";
			$array_fine_1[] = "";//"Totale 3 complessivo";
			$array_fine_1[] = "Totale pagato";	
			
			$array_fine_2[] = "";
			$array_fine_2[] = number_format($dovuto_complessivo_atti_precedenti,2,",",".")." Euro";
			$array_fine_2[] = number_format($totale_parziale,2,",",".")." Euro";//number_format($totale_complessivo_1,2,",",".")." Euro";
			$array_fine_2[] = "";//number_format($totale_complessivo_2,2,",",".")." Euro";
			$array_fine_2[] = "";//number_format($totale_complessivo_3,2,",",".")." Euro";
			$array_fine_2[] = number_format($parz_pagato,2,",",".")." Euro";
				
			$array_align_fine = array("L","R","R","R","R","R");
				
			$dovuto_complessivo_atti_precedenti = 0.00;
			$totale_complessivo_1 = 0.00;
			$totale_complessivo_2 = 0.00;
			$totale_complessivo_3 = 0.00;
			$parz_pagato = 0.00;
			$totale_parziale = 0.00;
				
			$pdf->SetFont('Arial', 'B', 8);
				
			$pdf->setCellPaddings(2,1,2,0);
			$y = $pdf->setRow($array_fine_1,"up",$styleRetta,$array_align_fine,0,$array_width_fine);
			//$y = crea_riga($pdf , $array_width_fine, $array_fine_1 , "up" , $styleRetta , $array_align_fine );
			$pdf->setCellPaddings(2,0,2,1);
			$y = $pdf->setRow($array_fine_2,"down",$styleRetta,$array_align_fine,0,$array_width_fine);
			//$y = crea_riga($pdf , $array_width_fine, $array_fine_2 , "down" , $styleRetta, $array_align_fine );
				
			$control_pagine = 0;
			if($l<$num_pignoramenti-1)
			{
				$pdf->AddPage();
				$pdf->Ln(5);
			
				$pdf->SetFont('Arial', 'B', 8);
			
				// $pdf->setCellPaddings(2,1,2,0);
				// $y1_vert = $pdf->setRow($array_intestaz_1,"up",$styleRetta,null,0,$array_width);
				// //$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_1, "up" , $styleRetta);
			
				// $pdf->setCellPaddings(2,0,2,1);
				// $y1_vert = $pdf->setRow($array_intestaz_2,"down",$styleRetta,null,0,$array_width);
				// //$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_2, "down" , $styleRetta);
			
				$pdf->setCellPaddings(2,1,2,0);
				$y1_vert = $pdf->setRow($array_intestaz_1,"up",$styleRetta,$array_align_1,0,$array_width);
				
				$pdf->setCellPaddings(2,0,2,0);
				$y1_vert = $pdf->setRow($array_intestaz_2,"no",$styleRetta,$array_align_1,0,$array_width);
				$y1_vert = $pdf->setRow($array_intestaz_3,"no",$styleRetta,$array_align_2,0,$array_width);
				$y1_vert = $pdf->setRow($array_intestaz_4,"no",$styleRetta,$array_align_2,0,$array_width);
				
				$pdf->setCellPaddings(2,0,2,1);
				$y1_vert = $pdf->setRow($array_intestaz_5,"down",$styleRetta,$array_align_2,0,$array_width);

				$ctrl_linea = "no";
			
			}
			else
				$control_pagine = 1;
		}
		else
		{
			$pdf->setCellPaddings(2,0,2,1);
			$y = $pdf->setRow($array_value_2,"no",$styleDash,$array_align_1,0,$array_width);
			//$y = crea_riga($pdf , $array_width, $array_value_2 , "no" , $styleDash, $array_align_1 );
			$pdf->SetFont('Arial', 'B', 8);
			$y = $pdf->setRow($array_value_3,"no",$styleDash,$array_align_2,0,$array_width);
			//$y = crea_riga($pdf , $array_width, $array_value_3 , "no" , $styleDash, $array_align_2 );
			
			$pdf->setCellPaddings(2,1,2,1);
			$pdf->SetFont('Arial', 'B', 8);
			$pdf->Cell( 30 , 0, "ATTI:" );
			$pdf->SetFont('Arial', '', 8);
			$pdf->setCellPaddings(2,1,2,1);
			$pdf->MultiCell( 0 , 0, $atti_precedenti , 0 , "L" , 0 , 1 , '' , '' , true );
			
			if($anomalia != "")
			{

				$pdf->setCellPaddings(2,1,2,2);
				$pdf->SetFont('Arial', 'B', 8);
				$pdf->Cell( 30 , 0, "ANOMALIE:" );
				$pdf->SetFont('Arial', '', 8);
				$pdf->setCellPaddings(2,1,2,2);
				$pdf->MultiCell( 0 , 0, $anomalia , 0 , "L" , 0 , 1 , '' , '' , true );
			
			}
		}
		
		$cont_result++;
			
	}//CHIUSURA PIGNORAMENTI
	
 	if($control_pagine==0)
	{
		$y2_vert = $pdf->getY();
		
		// 		crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);
		
		$array_width_fine = array();
		$array_fine_1 = array();
		$array_fine_2 = array();
		
		$array_width_fine = array( 70 , 40 , 40 , 40 , 40, $larghezza_pag - 230 - 20 );
					
		$array_fine_1[] = "PARZIALI DI PAGINA";		
		$array_fine_1[] = "Totale coazione";
		$array_fine_1[] = "Totale dovuto";//"Totale 1 complessivo";
		$array_fine_1[] = "";//"Totale 2 complessivo";
		$array_fine_1[] = "";//"Totale 3 complessivo";
		$array_fine_1[] = "Totale pagato";	
		
		$array_fine_2[] = "";
		$array_fine_2[] = number_format($dovuto_complessivo_atti_precedenti,2,",",".")." Euro";
		$array_fine_2[] = number_format($totale_parziale,2,",",".")." Euro";//number_format($totale_complessivo_1,2,",",".")." Euro";
		$array_fine_2[] = "";//number_format($totale_complessivo_2,2,",",".")." Euro";
		$array_fine_2[] = "";//number_format($totale_complessivo_3,2,",",".")." Euro";
		$array_fine_2[] = number_format($parz_pagato,2,",",".")." Euro";
		
		$array_align_fine = array("L","R","R","R","R","R");
		
		$dovuto_complessivo_atti_precedenti = 0.00;
		$totale_complessivo_1 = 0.00;
		$totale_complessivo_2 = 0.00;
		$totale_complessivo_3 = 0.00;
		$parz_pagato = 0.00;
		$totale_parziale = 0.00;

		$pdf->SetFont('Arial', 'B', 9);
		
		$pdf->setCellPaddings(2,2,2,0);
        $y = $pdf->setRow($array_fine_1,"up",$styleRetta,$array_align_fine,0,$array_width_fine);
		//$y = crea_riga($pdf , $array_width_fine, $array_fine_1 , "up" , $styleRetta , $array_align_fine );
		$pdf->setCellPaddings(2,0,2,2);
        $y = $pdf->setRow($array_fine_2,"down",$styleRetta,$array_align_fine,0,$array_width_fine);
		//$y = crea_riga($pdf , $array_width_fine, $array_fine_2 , "down" , $styleRetta, $array_align_fine );
 	}
	
	$pdf->setPrintHeader(false);
	$pdf->addPage();	
	$pdf->setPrintFooter(false);
	
	if($daco != "")
		$sel_utente = "Da ".$daco." ".$dano." a ".$acog." ".$anom;
	else
		$sel_utente = "Nessun filtro";
	
	if($da_partita != "")
		$sel_partita = "Dalla partita contabile numero ".$da_partita." alla partita contabile numero ".$a_partita;
	else
		$sel_partita = "Nessun filtro";

    if($tax_type != "")
        $sel_tax = $tax_type;
    else
        $sel_tax = "Nessun filtro";

	if( $da_anno != null && $ad_anno != null ){	
		$sel_anno_rif = "Dal ".$da_anno." al ".$ad_anno;
	}
	else
		$sel_anno_rif = "Nessun filtro";
	
	if( $from_cronoYear != null && $to_cronoYear != null ){	
		$sel_anno_crono = "Dal ".$from_cronoYear." al ".$to_cronoYear;
	}
	else
		$sel_anno_crono = "Nessun filtro";

	switch($paymentStatus){
		case "yes":
			$sel_payment = "Pagamenti presenti (completi e/o parziali)";
			break;
		case "no":
			$sel_payment = "Nessun pagamento";
			break;
		case "incompleted":
			$sel_payment = "Pagamenti incompleti (parziali e/o assenti)";
			break;
		case "completed":
			$sel_payment = "Pagamenti completi";
			break;
		case "partial":
			$sel_payment = "Pagamenti parziali";
			break;
		default:
			$sel_payment = "Nessun filtro";
			break;
	}
		

	if(empty($DocumentTypeId))
		$sel_pigno = "";
	else
		$sel_pigno = $a_docs[$DocumentTypeId]['Description'];
	
	if($da_elab != "")
		$sel_elab = "Dal ".$da_elab." al ".$a_elab;
	else if($data_elab == "assente")
		$sel_elab = "Assente";
	else
		$sel_elab = "Nessun filtro";
	
	if($da_notif != "")
		$sel_notif = "Dal ".$da_notif." al ".$a_notif;
	else if($data_notif == "assente")
		$sel_notif = "Assente";
	else
		$sel_notif = "Nessun filtro";
	
	if($select_anomalia == "")
		$sel_anomalie = "Nessun filtro";
	else if($select_anomalia == "si")
		$sel_anomalie = "Solo anomalie";
	else if($select_anomalia == "no")
		$sel_anomalie = "Nessuna";
	
	if($da_stampa != "")
		$sel_stampa = "Dal ".$da_stampa." al ".$a_stampa;
	else if($data_stampa == "assente")
		$sel_stampa = "Assente";
	else
		$sel_stampa = "Nessun filtro";
	
	
	if ($stato_stampa != "" )
		$sel_stato_stampa = $stato_stampa;
	else
		$sel_stato_stampa = "Nessun filtro";

	if((int) $flag_notifica == 1)
		$sel_flag_notifica = "Assente";
	else if((int)$flag_notifica == 2)
		$sel_flag_notifica = "Presente";
	else
		$sel_flag_notifica = "Nessun filtro";
	
	$sel_blocco = $blocco;
	
	$pdf->setCellPaddings(2,0,2,1);
	$pdf->ln(10);
	$pdf->SetFont('Arial', 'B', 18);
	$pdf->Cell(0, 0, "COMUNE DI ".strtoupper($nome_com) , 0, 1, 'C', 0, '', 0, false, 'T', 'M');
	$pdf->SetFont('Arial', '', 16);
	$pdf->Cell(0, 0, "ELENCO PIGNORAMENTI" , 0, 1, 'C', 0, '', 0, false, 'T', 'M');
	$pdf->ln(10);
	$pdf->SetFont('Arial', 'B', 14);
	$pdf->Cell(0, 0, "SELEZIONI" , 0, 1, 'L');

	$pdf->SetFont('Arial', '', 12);
    $pdf->Cell (80, 0, "ANNO RIF PARTITA:", 0, 0, "L");
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->Cell ( $larghezza_pag-100 , 5, $sel_anno_rif , 0, 1, "L");

	$pdf->SetFont('Arial', '', 12);
    $pdf->Cell (80, 0, "ANNO CRONOLOGICO:", 0, 0, "L");
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->Cell ( $larghezza_pag-100 , 5, $sel_anno_crono , 0, 1, "L");

	$pdf->SetFont('Arial', '', 12);
    $pdf->Cell (80, 0, "STATO PAGAMENTO:", 0, 0, "L");
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->Cell ( $larghezza_pag-100 , 5, $sel_payment , 0, 1, "L");

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell (80, 0, "TIPO RISCOSSIONE:", 0, 0, "L");
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->Cell ( $larghezza_pag-100 , 5, $sel_tax , 0, 1, "L");

	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (80, 0, "TIPO PIGNORAMENTO:", 0, 0, "L");
	$pdf->SetFont('Arial', 'I', 12);
	$pdf->Cell ( $larghezza_pag-100 , 5, $sel_pigno , 0, 1, "L");
	
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (80, 0, "UTENTE:", 0, 0, "L");
	$pdf->SetFont('Arial', 'I', 12);
	$pdf->Cell ( $larghezza_pag-100 , 5, $sel_utente , 0, 1, "L");
	
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (80, 0, "PARTITA:", 0, 0, "L");
	$pdf->SetFont('Arial', 'I', 12);
	$pdf->Cell ( $larghezza_pag-100 , 5, $sel_partita , 0, 1, "L");
		
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (80, 0, "DATA DI ELABORAZIONE:", 0, 0, "L");
	$pdf->SetFont('Arial', 'I', 12);
	$pdf->Cell ( $larghezza_pag-100 , 5, $sel_elab , 0, 1, "L");
	
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (80, 0, "DATA DI STAMPA:", 0, 0, "L");
	$pdf->SetFont('Arial', 'I', 12);
	$pdf->Cell ( $larghezza_pag-100 , 5, $sel_stampa , 0, 1, "L");
	
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (80, 0, "DATA DI NOTIFICA:", 0, 0, "L");
	$pdf->SetFont('Arial', 'I', 12);
	$pdf->Cell ( $larghezza_pag-100 , 5, $sel_notif , 0, 1, "L");
	
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (80, 0, "STATO DI STAMPA:", 0, 0, "L");
	$pdf->SetFont('Arial', 'I', 12);
	$pdf->Cell ( $larghezza_pag-100 , 5, $sel_stato_stampa , 0, 1, "L");
	
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (80, 0, "BLOCCO COAZIONE:", 0, 0, "L");
	$pdf->SetFont('Arial', 'I', 12);
	$pdf->Cell ( $larghezza_pag-100 , 5, $sel_blocco , 0, 1, "L");
	
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (80, 0, "ANOMALIE:", 0, 0, "L");
	$pdf->SetFont('Arial', 'I', 12);
	$pdf->Cell ( $larghezza_pag-100 , 5, $sel_anomalie , 0, 1, "L");

	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (80, 0, "PIGNORAMENTO NOTIFICATO:", 0, 0, "L");
	$pdf->SetFont('Arial', 'I', 12);
	$pdf->Cell ( $larghezza_pag-100 , 5, $sel_flag_notifica , 0, 1, "L");
	
	$pdf->ln(10);
	$pdf->SetFont('Arial', 'B', 14);
	$pdf->Cell(0, 0, "RIEPILOGO" , 0, 1, 'L');
	
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (67, 0, "NUMERO PAGINE:", 0, 0, "L");
	$pdf->SetFont('Arial', 'B', 12);
	$pdf->Cell ( 23.5 , 5, $pdf->PageNo() , 0, 1, "R");
	
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (67, 0, "NUMERO ATTI:", 0, 0, "L");
	$pdf->SetFont('Arial', 'B', 12);
	$pdf->Cell ( 23.5 , 5, $cont_result , 0, 1, "R");
	
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (67, 0, "TOTALE COAZIONE:", 0, 0, "L");
	$pdf->SetFont('Arial', 'B', 12);
	$pdf->Cell ( 40 , 5, number_format($dovuto_gen_complessivo_atti_precedenti,2,",",".")." Euro" , 0, 1, "R");
	
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (67, 0, "TOTALE DOVUTO:", 0, 0, "L");
	$pdf->SetFont('Arial', 'B', 12);
	$pdf->Cell ( 40 , 5, number_format($totale_generale,2,",",".")." Euro" , 0, 1, "R");
	
	// $pdf->SetFont('Arial', '', 12);
	// $pdf->Cell (67, 0, "TOTALE 2:", 0, 0, "L");
	// $pdf->SetFont('Arial', 'B', 12);
	// $pdf->Cell ( 40 , 5, number_format($tot_gen_complessivo_2,2,",",".")." Euro" , 0, 1, "R");
	
	// $pdf->SetFont('Arial', '', 12);
	// $pdf->Cell (67, 0, "TOTALE 3:", 0, 0, "L");
	// $pdf->SetFont('Arial', 'B', 12);
	// $pdf->Cell ( 40 , 5, number_format($tot_gen_complessivo_3,2,",",".")." Euro" , 0, 1, "R");
	
	$pdf->SetFont('Arial', '', 12);
	$pdf->Cell (67, 0, "TOTALE PAGATO:", 0, 0, "L");
	$pdf->SetFont('Arial', 'B', 12);
	$pdf->Cell ( 40 , 5, number_format($tot_gen_pagato,2,",",".")." Euro" , 0, 1, "R");
	
	$pdf->movePage($pdf->PageNo(), 1);
	$pdf->Output( $file_elenco , 'F');
	
	
	if($cont_result == 0) 
	{
		if(session_status() == PHP_SESSION_NONE)session_start();
		$_SESSION['progress'] = "100";
		session_write_close();
		
		echo json_encode([
			"error" => 2,
			"msg" => "Nessun risultato trovato!"
		]);
		
		die;
	}

	$file = $vedi_file;

	if(session_status() == PHP_SESSION_NONE)session_start();

	echo json_encode([
		"path" => $file,
		"error" => 0,
		"msg" => "File stampato correttamente!"
	]);