<?php
require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";
include TCPDF . "/tcpdf.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/parametri.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/coazione.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

class MYPDF extends TCPDF {
	
public function Header() {
		
		$this->SetFont('Arial', 'B', 11);
		$this->ln(5);
		$this->Cell(0, 5, "Elenco Posizioni" , 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}
	
	public function Footer() {

		$this->SetY(-10);
		$this->SetFont('helvetica', 'N', 7);
		$this->Cell(0, 5, "Pag. ". ($this->getPage() + 1) ." - ".date("d/m/Y H\hi:s"), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	
	}
	
}

//TIPO ELENCO
$tipo_elenco = strtoupper(get_var('tipo_elenco'));

if($tipo_elenco=="EXCEL")
{

	include_once EXCEL.'/PHPExcel.php';

}



$a = get_var('a');
$c = get_var('c');

$comune = new ente_gestito($c);
$nome_com = $comune->Nome;
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$forma = new forma_giuridica();
$array_forma = $forma->array_completo();

$par_notifica = new parametri_notifica(null);
$par_notifica->array_notifica();

//PREPARAZIONE ELENCO
$elenco_dir = crea_dir( ATTI ."/". $c . "/Posizioni/Elenchi" );
$data_file = date('Y-m-d_H-i-s');

$nome_file = "elenco_posizioni_".$data_file;
if($tipo_elenco=="PDF")
	$nome_file.=".pdf";
else if($tipo_elenco=="EXCEL")
	$nome_file.=".xls";

$file_elenco = $elenco_dir."/".$nome_file;


$download = $file_elenco;

$vedi_file = mostra_file_path($download);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<link rel="shortcut icon"  href="/gitco2/immagini/gitco.png">
<title>Stampa Avviso</title>

<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
<style> .ui-datepicker { font-size:11px; } </style>


<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>

<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>

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

function fine_pdf(value)
{
	$( "#progressbar" ).progressbar({value: 100 });
	$( "#barlabel" ).text( value );
	
	sleep(1000);

	mostra_file();

}

function fine_excel(value)
{
	$( "#progressbar" ).progressbar({value: 100 });
	$( "#barlabel" ).text( value );
	
	sleep(1000);

	apri_file("<?php echo $file_elenco; ?>","<?php echo $nome_file; ?>");

}

function mostra_file()
{
	window.name = "Elenco";
	window.open('<?php echo $vedi_file; ?>',"Elenco");
}

function apri_file(value, value2)
{
	link="/gitco2/coattiva/modali/force-download.php?file="+value+"&filename="+value2;
	window.name="elenco";
	window.open(link,"elenco");
}
</script>

</head>

<body class="sfondo_new_gitco">

<table class="table_azzurra text_center" style="height:7%;">
<tr>
<td width=1%><br></td>
<td class="text_left"><font class="comune" ><?php echo $nome_comune ?></font></td>
		<td class="text_right"><font class="user" ><?php echo $nome_user ?></font></td>
		<td width=1%><br></td>
	</tr>
</table>

<table class="table_azzurra text_center" style="height:93%;">
	<tr>
		<td valign=top>
		<br><br><br>
		<font class="titolo font18 text_center">Elenco Posizioni</font>
		
		<br><br>
		
		<div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>
		
		<br>
		
		<div id=vedi_file></div>
		
		</td>
	</tr>
	<tr>
		<td>
		<p>ATTENZIONE: Potrebbe accadere che il download del file venga bloccato dal browser.
In tal caso sara' necessario sbloccare il download, seguendo le istruzioni che il browser visualizzera' sulla pagina,
e, successivamente, riavviare l'elaborazione dell'elenco.</p>
		</td>
	</tr>
</table>



<?php



//COGNOME NOME
$daco  = strtoupper(get_var('daco'));
$acog  = strtoupper(get_var('acog'));
$dano  = strtoupper(get_var('dano'));
$anom  = strtoupper(get_var('anom'));

//PARTITA
$da_partita  = get_var('da_n_elenco');
$a_partita  = get_var('a_n_elenco');

//ANNI RIFERIMENTO
$da_anno = get_var('da_anno');
$ad_anno = get_var('ad_anno');

$tipo_partita = get_var('tipo_partita');

//POSIZIONE
$posizione = get_var('posizione');

//PIGNORAMENTO
$pignoramento_filtro = get_var('pignoramento');

//STATO PAGAMENTO
$pagamento = get_var('pagamento');

//RATEIZZAZIONE
$rateizzazione = get_var('rateizzazione');
$stato_rateizzazione = get_var('stato_rateizzazione');

//BLOCCO COAZIONE
$blocco = get_var('blocco');

//ORDINAMENTO
$ordinamento = get_var('ordinamento');

flush();	ob_flush();

echo "<script>inizio();</script>";

flush();	ob_flush();		flush();	ob_flush();
sleep(2);


/**		SELEZIONE UTENTI 			*/
$query_utente = da_a_utente( $c , $daco, $acog, $dano, $anom );
$array_utenti = mysql_array( $query_utente );


/** 	SELEZIONE PARTITE			*/
$where_anno = null;
if( $da_anno != null && $ad_anno != null )
	$where_anno = "Anno_Riferimento >= '".$da_anno."' AND Anno_Riferimento <= '".$ad_anno."'";
	
$query_partita = da_a_partita( $c , $da_partita , $a_partita , $where_anno );
$array_partite = mysql_array( $query_partita );

$num_utenti = count($array_utenti);
$num_partite = count($array_partite);


/**
	///////////////////////////////		PDF	    //////////////////////////////////
*/
	
if($tipo_elenco=="PDF")
{
	$pdf = new MYPDF("P", "mm", "A4", true, 'UTF-8', false);
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
	
	$array_width = array( 25 , 50 , ($larghezza_pag-225-20) , 120 , 30 );
	
	$array_intestaz_1[] = "Partita";
	$array_intestaz_1[] = "Utente";
	$array_intestaz_1[] = "CF / PI";
	$array_intestaz_1[] = "Indirizzo";
	$array_intestaz_1[] = "Dovuto";	
	
	$array_intestaz_2[] = "Ultimo atto";
	$array_intestaz_2[] = "Notifica";
	$array_intestaz_2[] = "Pignoramento collegato";
	$array_intestaz_2[] = "Informazioni Cartella";
	$array_intestaz_2[] = "Pagato";
	
	$pdf->setCellPaddings(2,1,2,0);
	$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_1, "up" , $styleRetta);
	
	$pdf->setCellPaddings(2,0,2,1);
	$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_2, "down" , $styleRetta);
	
}

/**
	//////////////////////////////////////////////////////////////////////////////
*/

if($tipo_elenco=="EXCEL")
{

	// Crea nuovo file Excel
	$oggettoExcel = new PHPExcel();
	
	// Imposta proprieta' documento
	$oggettoExcel->getProperties()->setCreator("Sarida S.r.l.")
	->setLastModifiedBy("Sarida S.r.l.")
	->setTitle("Elenco posizioni")
	->setSubject("Elenco posizioni");
	
	// Aggiunta intestazione
	$foglio1 = $oggettoExcel->setActiveSheetIndex(0);
	
	$foglio1
	->setCellValueByColumnAndRow(0,1,'Partita')
	->setCellValueByColumnAndRow(1,1,'Utente')
	->setCellValueByColumnAndRow(2,1,'CF / PI')
	->setCellValueByColumnAndRow(3,1,'Indirizzo')
	->setCellValueByColumnAndRow(4,1,'Ultimo Atto')
	->setCellValueByColumnAndRow(5,1,'Notifica')
	->setCellValueByColumnAndRow(6,1,"Pignoramento collegato")
	->setCellValueByColumnAndRow(7,1,'Informazioni Cartella')
	->setCellValueByColumnAndRow(8,1,'Dovuto')
	->setCellValueByColumnAndRow(9,1,'Pagato')
	->setCellValueByColumnAndRow(10,1,'Residuo');
	
	$num_colonna = 11;
	//DATORE DI LAVORO
	for($num_terzo=1;$num_terzo<4;$num_terzo++)
	{
		
		$foglio1->setCellValueByColumnAndRow($num_colonna,1,'Datore di lavoro DL_'.$num_terzo);	$num_colonna++;
		$foglio1->setCellValueByColumnAndRow($num_colonna,1,'Codice INPS DL_'.$num_terzo);	$num_colonna++;
		$foglio1->setCellValueByColumnAndRow($num_colonna,1,'Fonte dati DL_'.$num_terzo);	$num_colonna++;
		$foglio1->setCellValueByColumnAndRow($num_colonna,1,'Tipo Contratto DL_'.$num_terzo);	$num_colonna++;
		$foglio1->setCellValueByColumnAndRow($num_colonna,1,'Data Costituzione Ditta DL_'.$num_terzo);	$num_colonna++;
		$foglio1->setCellValueByColumnAndRow($num_colonna,1,'Data Ditta Operativa DL_'.$num_terzo);	$num_colonna++;
		$foglio1->setCellValueByColumnAndRow($num_colonna,1,"Data Dipendenza DL_".$num_terzo);	$num_colonna++;
		$foglio1->setCellValueByColumnAndRow($num_colonna,1,'Note DL_'.$num_terzo);	$num_colonna++;
	}
	
	//BANCA
	for($num_terzo=1;$num_terzo<4;$num_terzo++)
	{	
		$foglio1->setCellValueByColumnAndRow($num_colonna,1,'Banca BN_'.$num_terzo);	$num_colonna++;
		$foglio1->setCellValueByColumnAndRow($num_colonna,1,'Fonte dati BN_'.$num_terzo);	$num_colonna++;
		$foglio1->setCellValueByColumnAndRow($num_colonna,1,'Tipo Titolo BN_'.$num_terzo);	$num_colonna++;
		$foglio1->setCellValueByColumnAndRow($num_colonna,1,'Titolo BN_'.$num_terzo);	$num_colonna++;
		$foglio1->setCellValueByColumnAndRow($num_colonna,1,'Intestatario BN_'.$num_terzo);	$num_colonna++;
		$foglio1->setCellValueByColumnAndRow($num_colonna,1,"Coointestatari BN_".$num_terzo);	$num_colonna++;
		$foglio1->setCellValueByColumnAndRow($num_colonna,1,'Note BN_'.$num_terzo);	$num_colonna++;
	}
	
	//ISTITUTO PREVIDENZIALE
	for($num_terzo=1;$num_terzo<4;$num_terzo++)
	{
		$foglio1->setCellValueByColumnAndRow($num_colonna,1,'Istituto Previdenziale IP_'.$num_terzo);	$num_colonna++;
		$foglio1->setCellValueByColumnAndRow($num_colonna,1,'Fonte dati IP_'.$num_terzo);	$num_colonna++;
		$foglio1->setCellValueByColumnAndRow($num_colonna,1,'Categoria Pensione IP_'.$num_terzo);	$num_colonna++;
		$foglio1->setCellValueByColumnAndRow($num_colonna,1,'Libretto Pensione IP_'.$num_terzo);	$num_colonna++;
		$foglio1->setCellValueByColumnAndRow($num_colonna,1,'Note IP_'.$num_terzo);	$num_colonna++;
	}
	
	$ultima_colonna = $oggettoExcel->setActiveSheetIndex(0)->getHighestColumn();
	
	$intestazione = 'a1:bs1';	
	$style = array(
			'font' => array('bold' => true,),
			'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,),
	);
	$oggettoExcel->setActiveSheetIndex(0)->getStyle($intestazione)->applyFromArray($style);
	
	// Rinomina foglio di lavoro
	$oggettoExcel->getActiveSheet()->setTitle('Posizioni');
	
	$riga_Excel = 2;
	
	
}

	$control_pagine = 0;
	$cont_result = 0;
	
	$parz_dovuto = 0.00;
	$parz_pagato = 0.00;
	
	$tot_gen_dovuto = 0.00;
	$tot_gen_pagato = 0.00;
	
	$ctrl_linea = "no";
		
	for( $k=0; $k < $num_partite; $k++ )//FOR PARTITE
	{			
		echo "<script>update(".ceil($k*100/$num_partite).");</script>";
		
		flush();
		ob_flush();
		flush();
		ob_flush();
		
		if($tipo_partita != "")
			if($array_partite[$k]['Tipo']!=$tipo_partita)
				continue;
		
		if($blocco=="Si")
		{
			if($array_partite[$k]['Flag_Blocco_Coazione']!="si")
				continue;
		}
		
		if($blocco=="No")
		{
			if($array_partite[$k]['Flag_Blocco_Coazione']=="si")
				continue;
		}
		
		for( $j=0; $j<$num_utenti; $j++ )//FOR UTENTI
		{
			if( $array_partite[$k]['Utente_ID'] == $array_utenti[$j]['ID'] )//IF PARTITA/UTENTE
			{
				set_time_limit(30);
				
				//PARTITA
				$partita = new partita($array_partite[$k]['ID'], $c, $array_partite[$k]['Anno_Riferimento']);
				
				//CONTROLLO BLOCCO COAZIONE
				if($partita->Flag_Blocco_Coazione=="si")
					break;
				
				//UTENTE
				$utente = new utente( $array_partite[$k]['Utente_ID'] , $c );
				$indirizzo_utente = $utente->righe_indirizzo();
				$forma_descr = "";
				if($utente->Forma_Giuridica!='')
				{
					$index_value = $utente->Forma_Giuridica;
					if($index_value>0)
						$forma_descr = $array_forma[$index_value]['Sigla'];
				}
				
				$nome_utente_completo = $utente->Cognome.$utente->Ditta." ".$utente->Nome.$forma_descr;
				
				//LIMITAZIONE NOME UTENTE
				if( strlen($nome_utente_completo) > 22 )
					$nome_utente = substr($nome_utente_completo,0,21)."...";
				else 
					$nome_utente = $nome_utente_completo;
				
				//CF/PI
				if( $utente->Genere=="D" )
					$CF_PI = $utente->Partita_Iva;
				else
					$CF_PI = $utente->Codice_Fiscale;
				
				if($partita->ultimo_atto!=null){
					if($posizione=="vuota")
						break;
				}
				else if($posizione!="" && $posizione!="vuota"){
					break;
				}					
					
				//ATTO
				$atto = new atto($partita->ultimo_atto,$c);
				
				$pigno_presente = "no";
				$pigno_crono = "Nessuno";
				//CONTROLLO PRESENZA PIGNORAMENTI
				$pignoramento = new pignoramento(null, $c);
				$array_pignoramenti = $pignoramento->array_pignoramenti($partita->ID);
				for($conta_pigno=0;$conta_pigno<count($array_pignoramenti);$conta_pigno++)
				{
//					if($array_pignoramenti[$conta_pigno]['Atto_ID'] == $atto->ID)
//					{
						$pigno_crono = strtoupper($array_pignoramenti[$conta_pigno]['Tipo'])." - ";
						
						if($array_pignoramenti[$conta_pigno]['ID_Cronologico']==0)
							$pigno_crono.= "Crono da assegnare";
						else 
							$pigno_crono.= $array_pignoramenti[$conta_pigno]['ID_Cronologico']."/".$array_pignoramenti[$conta_pigno]['Anno_Cronologico'];
						
						$pigno_presente = "si";
                        $pignoramento = new pignoramento($array_pignoramenti[$conta_pigno]['ID'], $c);
//					}
				}
				
				if($pignoramento_filtro == "no" && $pigno_presente == "si")
					break;
				else if($pignoramento_filtro == "si" && $pigno_presente == "no")
					break;
				
				//CONTROLLO CRONOLOGICO
				if($atto->ID_Cronologico>0 && $atto->Anno_Cronologico>0)
				{
					if($posizione=="da assegnare")
						break;
				}
				else
				{
					if($posizione=="assegnato")
						break;
				}
				
				//CONTROLLO PRESENZA DATA STAMPA
				if(from_mysql_date($atto->Data_Stampa)==null)
				{
					if($posizione=="stampato")
						break;			
				}
				else
				{
					if($posizione=="da stampare")
						break;
			
					$data_stampa = from_mysql_date($atto->Data_Stampa);
				}

				$checkNotifica = $atto->check_notifica();
				//CONTROLLO PRESENZA DATA NOTIFICA
				if($checkNotifica=="n")//from_mysql_date($atto->Data_Notifica)==null)
				{
					if($posizione=="notificato")
						break;
					
					$notifica = "Assente";
					for($conta_par=0;$conta_par<count($par_notifica->Motivi);$conta_par++)
					{
						if($par_notifica->Motivi[$conta_par]['ID']==$atto->Motivo_Notifica)
							$notifica = $par_notifica->Motivi[$conta_par]['Descrizione'];
					}
					
				}
				else if($checkNotifica=="y")
				{
					if($posizione=="da notificare")
						break;
					
					$notifica = from_mysql_date($atto->Data_Notifica);
				}
				
				
				
				$totali_atto = $atto->dovuto_senza_pagamenti();
				
				//PARAMETRI ANNUALI
				$par_annuali = new parametri_annuali($c, $atto->Data_Elaborazione, $partita->Tipo);
				$importo_minimo = $par_annuali->Importo_Minimo;
				
				if($atto->ID!=null)
				{
					//CONTROLLO PAGAMENTI/RATEIZZAZIONI
					$control_pagamenti = $atto->controlloPagamenti($partita->Tipo);
					if($control_pagamenti!="ok")
						break;

					$a_rateizzazione = $atto->rateizzazione(array("importo_minimo"=>$importo_minimo));
					if($rateizzazione=="y"){
					    if($a_rateizzazione['rateizzazione']==false)
					        break;
                    }
                    else if($rateizzazione=="n"){
                        if($a_rateizzazione['rateizzazione']==true)
                            break;
                    }

                    if($rateizzazione!="n" && $stato_rateizzazione!=""){
					    if($stato_rateizzazione!=$a_rateizzazione['status'])
					        break;
                    }
				}
				
				$ID_ing = $atto->Comune_ID;
				$ID_partita = $partita->Comune_ID;
				$info_cart = $partita->Tributo[0]->Info_Cartella;

                $totale_dovuto = $pignoramento->Totale_Dovuto;
                if($totale_dovuto==0)
                    $totale_dovuto = $atto->Totale_Dovuto;
				$tot_pagamenti = $partita->getAllPayments();
				
				$importiCodici = $partita->importiCodiciTributo();
				
				if($atto->ID==null)
				{
					$totale_dovuto = $importiCodici['IMPORTO'] + $importiCodici['INTERESSI'] + $importiCodici['SPESE'] + $importiCodici['SANZIONE']-$importiCodici['PAGAMENTO'];
					$tot_pagamenti = 0;
				}
				
				if($tot_pagamenti == 0 )
				{
					if($pagamento!="Nessuno" && $pagamento!="Nessuno_Parziale" && $pagamento!="")
						break;
				}
				else if($tot_pagamenti<$totale_dovuto)
				{
					if($pagamento!="Parziale" && $pagamento!="Nessuno_Parziale" && $pagamento!="")
						break;
				}
				else if($tot_pagamenti>=$totale_dovuto)
				{
					if($pagamento!="Totale" && $pagamento!="")
						break;
				}
				
				$parz_dovuto += $totale_dovuto;
				$parz_pagato += $tot_pagamenti;
				
				$stati_atto = $atto->Stato_Esecuzione." / ".$atto->Stato_Stampa;
				if($atto->Stato != "")
					$stati_atto.=" / ".$atto->Stato;
							
				if($atto->Atto=="Ingiunzione")	$pref_atto = "ING. ";
				else if($atto->Atto=="Avviso di intimazione ad adempiere")
					$pref_atto = "AVV. ";
				else 
					$pref_atto = "";
				
				if($atto->ID!=null)
					$control_crono = $atto->ID_Cronologico."/".$atto->Anno_Cronologico;
				else 
					$control_crono = "Assente";
				
				if($control_crono == "0/0") $control_crono = "Da assegnare";
				else 
					$control_crono = $pref_atto.$control_crono;
				
				if($atto->Modalita_Stampa=="mani")
					$tipo_invio = "A mani";
				else 
					$tipo_invio = "Tramite ".ucfirst($atto->Modalita_Stampa);
				
				//PDF
				if($tipo_elenco=="PDF")
				{
					
					$pdf->SetFont('Arial', '', 8);
					
					$array_value_1 = array();
					$array_value_2 = array();
					
					$array_value_1[] = $ID_partita."/".$partita->Anno_Riferimento;
					$array_value_1[] = $nome_utente;
					$array_value_1[] = strtoupper($CF_PI);
					$array_value_1[] = $indirizzo_utente['Completo'];
					$array_value_1[] = conv_num(number_format($totale_dovuto,2))." Euro";
					
					$array_value_2[] = $control_crono;
					$array_value_2[] = $notifica;
					$array_value_2[] = $pigno_crono;
					$array_value_2[] = $info_cart;
					$array_value_2[] = conv_num(number_format($tot_pagamenti,2))." Euro";
					
					$array_align = array("L","L","L","L","R");
					
					$pdf->setCellPaddings(2,2,2,0);
					$y = crea_riga($pdf , $array_width, $array_value_1 , $ctrl_linea , $styleDash , $array_align );
						
					if($ctrl_linea == "no")	$ctrl_linea = "up";
					
					$pdf->setCellPaddings(2,0,2,2);
					
					if( $y > $altezza_pag - 40)
					{
						$y = crea_riga($pdf , $array_width, $array_value_2 , "no" , $styleDash, $array_align );
							
						$y2_vert = $pdf->getY();
							
						crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);
							
						$tot_gen_dovuto += $parz_dovuto;
						$tot_gen_pagato += $parz_pagato;
							
						$array_width_fine = array();
						$array_fine_1 = array();
						$array_fine_2 = array();
							
						$array_width_fine[] = 195;
						$array_width_fine[] = $larghezza_pag - 225 - 20;
						$array_width_fine[] = 30;
							
						$array_fine_1[] = "PARZIALI DI PAGINA";
						$array_fine_2[] = "";
						$array_fine_1[] = "Totali di pagina dovuti";
						$array_fine_2[] = "Totali di pagina pagati";
						$array_fine_1[] = conv_num(number_format($parz_dovuto,2))." Euro";
						$array_fine_2[] = conv_num(number_format($parz_pagato,2))." Euro";
							
						$parz_dovuto = 0.00;
						$parz_pagato = 0.00;
							
						$array_align_fine = array("L","L","R");
					
						$pdf->SetFont('Arial', 'B', 8);
							
						$pdf->setCellPaddings(2,1,2,0);
						$y = crea_riga($pdf , $array_width_fine, $array_fine_1 , "up" , $styleRetta , $array_align_fine );
						$pdf->setCellPaddings(2,0,2,1);
						$y = crea_riga($pdf , $array_width_fine, $array_fine_2 , "down" , $styleRetta, $array_align_fine );
							
						$control_pagine = 0;
						if($k<$num_partite-1)
						{
							$pdf->AddPage();
							$pdf->Ln(5);
					
							$pdf->SetFont('Arial', 'B', 10);
					
							$pdf->setCellPaddings(2,1,2,0);
							$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_1, "up" , $styleRetta);
					
							$pdf->setCellPaddings(2,0,2,1);
							$y1_vert = crea_riga($pdf , $array_width, $array_intestaz_2, "down" , $styleRetta);
					
					
							$ctrl_linea = "no";
						}
						else
							$control_pagine = 1;
					}
					else
						$y = crea_riga($pdf , $array_width, $array_value_2 , "no" , $styleDash, $array_align );
					
				}//PDF
				else if($tipo_elenco=="EXCEL")
				{
					
					// Aggiunta intestazione
					$oggettoExcel->setActiveSheetIndex(0)
					->setCellValueByColumnAndRow(0, $riga_Excel, $ID_partita."/".$partita->Anno_Riferimento)
					->setCellValueByColumnAndRow(1, $riga_Excel, $nome_utente_completo)
					->setCellValueByColumnAndRow(2, $riga_Excel, strtoupper($CF_PI))
					->setCellValueByColumnAndRow(3, $riga_Excel, $indirizzo_utente['Completo'])
					->setCellValueByColumnAndRow(4, $riga_Excel, $control_crono)
					->setCellValueByColumnAndRow(5, $riga_Excel, $notifica)
					->setCellValueByColumnAndRow(6, $riga_Excel, $pigno_crono)
					->setCellValueByColumnAndRow(7, $riga_Excel, $info_cart)
					->setCellValueByColumnAndRow(8, $riga_Excel, conv_num(number_format($totale_dovuto,2))." Euro")					
					->setCellValueByColumnAndRow(9, $riga_Excel, conv_num(number_format($tot_pagamenti,2))." Euro")
					->setCellValueByColumnAndRow(10, $riga_Excel, conv_num(number_format($totale_dovuto-$tot_pagamenti,2))." Euro");
					
					$riga_Excel++;
				}
				
										
				
				
				$cont_result++;
				
				break;		//Una partita pu� avere un solo intestatario per cui una volta trovato si pu� uscire dal ciclo degli utenti		
							
			}//CHIUSURA IF PARTITA/UTENTE

		}//CHIUSURA FOR UTENTI
			
	}//CHIUSURA PARTITE

	//PDF
	if($tipo_elenco=="PDF")
	{
		if($control_pagine==0)
		{
			$y2_vert = $pdf->getY();
			
			crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);
			
			$tot_gen_dovuto += $parz_dovuto;
			$tot_gen_pagato += $parz_pagato;
			
			$array_width_fine = array();
			$array_fine_1 = array();
			$array_fine_2 = array();
			
			$array_width_fine[] = 195;
			$array_width_fine[] = $larghezza_pag - 225 - 20;
			$array_width_fine[] = 30;
			
			$array_fine_1[] = "PARZIALI DI PAGINA";
			$array_fine_2[] = "";
			$array_fine_1[] = "Totali di pagina dovuti";
			$array_fine_2[] = "Totali di pagina pagati";
			$array_fine_1[] = conv_num(number_format($parz_dovuto,2))." Euro";
			$array_fine_2[] = conv_num(number_format($parz_pagato,2))." Euro";
			
			$parz_dovuto = 0.00;
			$parz_pagato = 0.00;
			
			$array_align_fine = array("L","L","R");
			
			$pdf->SetFont('Arial', 'B', 9);
			
			$pdf->setCellPaddings(2,2,2,0);
			$y = crea_riga($pdf , $array_width_fine, $array_fine_1 , "up" , $styleRetta , $array_align_fine );
			$pdf->setCellPaddings(2,0,2,2);
			$y = crea_riga($pdf , $array_width_fine, $array_fine_2 , "down" , $styleRetta, $array_align_fine );
		
		}
		
		$pdf->setPrintHeader(false);
		$pdf->addPage();
		$pdf->setPrintFooter(false);

        $sel_anno = "Dal ".$da_anno." al ".$ad_anno;

		if($daco != "")
			$sel_utente = "Da ".$daco." ".$dano." a ".$acog." ".$anom;
		else
			$sel_utente = "Nessun filtro";
		
		if($da_partita != "")
			$sel_partita = "Dalla partita contabile numero ".$da_partita." alla partita contabile numero ".$a_partita;
		else
			$sel_partita = "Nessun filtro";	

		if($tipo_partita!="")
            $sel_tributo = $tipo_partita;
		else
            $sel_tributo = "Tutti";

        $sel_pigno = "Tutti";
        if($pignoramento_filtro=="no")
            $sel_pigno = "Non presente";
        else if($pignoramento_filtro=="si")
            $sel_pigno = "Presente";

        $sel_pagamenti = "Tutti";
        if($pagamento!=""){
            $sel_pagamenti = $pagamento;
            if($pagamento=="Nessuno_Parziale")
                $sel_pagamenti = "Nessuno e parziale";
        }

        $sel_rateizzazione = "Tutte";
        if($rateizzazione=="y")
            $sel_rateizzazione = "Presente";
        else if($rateizzazione=="n")
            $sel_rateizzazione = "Non presente";

        $sel_status_rateizzazione = "Tutti";
        if($stato_rateizzazione=="ongoing")
            $sel_status_rateizzazione = "In corso";
        else if($stato_rateizzazione=="completed")
            $sel_status_rateizzazione = "Completa";
        else if($stato_rateizzazione=="expired")
            $sel_status_rateizzazione = "Scaduta";


		$sel_blocco = $blocco;
		
		if($posizione=="notificato")
			$sel_notifica = "Notificato";
		else if($posizione=="da notificare")
			$sel_notifica = "Da notificare";
		else if($posizione=="stampato")
			$sel_notifica = "Stampato";
		else if($posizione=="da stampare")
			$sel_notifica = "Da stampare";
		else if($posizione=="assegnato")
			$sel_notifica = "Cronologico assegnato";
		else if($posizione=="da assegnare")
			$sel_notifica = "Cronologico da assegnare";
		else if($posizione=="vuota")
			$sel_notifica = "Da elaborare";
		else 
			$sel_notifica = "Nessun filtro";
		
		$pdf->setCellPaddings(2,0,2,1);
		$pdf->ln(10);
		$pdf->SetFont('Arial', 'B', 18);
		$pdf->Cell(0, 0, "COMUNE DI ".strtoupper($nome_com) , 0, 1, 'C', 0, '', 0, false, 'T', 'M');
		$pdf->SetFont('Arial', '', 16);
		$pdf->Cell(0, 0, "ELENCO POSIZIONI SENZA PIGNORAMENTI COLLEGATI" , 0, 1, 'C', 0, '', 0, false, 'T', 'M');
		$pdf->ln(10);
		$pdf->SetFont('Arial', 'B', 14);
		$pdf->Cell(0, 0, "SELEZIONI" , 0, 1, 'L');
		
		$pdf->SetFont('Arial', '', 12);
		$pdf->Cell (80, 0, "UTENTE:", 0, 0, "L");
		$pdf->SetFont('Arial', 'I', 12);
		$pdf->Cell ( $larghezza_pag-100 , 5, $sel_utente , 0, 1, "L");

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell (80, 0, "ANNI:", 0, 0, "L");
        $pdf->SetFont('Arial', 'I', 12);
        $pdf->Cell ( $larghezza_pag-100 , 5, $sel_anno , 0, 1, "L");
		
		$pdf->SetFont('Arial', '', 12);
		$pdf->Cell (80, 0, "PARTITA:", 0, 0, "L");
		$pdf->SetFont('Arial', 'I', 12);
		$pdf->Cell ( $larghezza_pag-100 , 5, $sel_partita , 0, 1, "L");

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell (80, 0, "TRIBUTI:", 0, 0, "L");
        $pdf->SetFont('Arial', 'I', 12);
        $pdf->Cell ( $larghezza_pag-100 , 5, $sel_tributo , 0, 1, "L");
		
		$pdf->SetFont('Arial', '', 12);
		$pdf->Cell (80, 0, "POSIZIONE:", 0, 0, "L");
		$pdf->SetFont('Arial', 'I', 12);
		$pdf->Cell ( $larghezza_pag-100 , 5, $sel_notifica , 0, 1, "L");

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell (80, 0, "PIGNORAMENTO:", 0, 0, "L");
        $pdf->SetFont('Arial', 'I', 12);
        $pdf->Cell ( $larghezza_pag-100 , 5, $sel_pigno , 0, 1, "L");

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell (80, 0, "PAGAMENTI:", 0, 0, "L");
        $pdf->SetFont('Arial', 'I', 12);
        $pdf->Cell ( $larghezza_pag-100 , 5, $sel_pagamenti , 0, 1, "L");

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell (80, 0, "RATEIZZAZIONE:", 0, 0, "L");
        $pdf->SetFont('Arial', 'I', 12);
        $pdf->Cell ( $larghezza_pag-100 , 5, $sel_rateizzazione , 0, 1, "L");

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell (80, 0, "STATO RATEIZZAZIONE:", 0, 0, "L");
        $pdf->SetFont('Arial', 'I', 12);
        $pdf->Cell ( $larghezza_pag-100 , 5, $sel_status_rateizzazione , 0, 1, "L");
		
		$pdf->SetFont('Arial', '', 12);
		$pdf->Cell (80, 0, "BLOCCO COAZIONE:", 0, 0, "L");
		$pdf->SetFont('Arial', 'I', 12);
		$pdf->Cell ( $larghezza_pag-100 , 5, $sel_blocco , 0, 1, "L");
		
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
		$pdf->Cell (67, 0, "TOTALE DOVUTO:", 0, 0, "L");
		$pdf->SetFont('Arial', 'B', 12);
		$pdf->Cell ( 40 , 5, conv_num(number_format($tot_gen_dovuto,2))." Euro" , 0, 1, "R");
		
		$pdf->SetFont('Arial', '', 12);
		$pdf->Cell (67, 0, "TOTALE PAGATO:", 0, 0, "L");
		$pdf->SetFont('Arial', 'B', 12);
		$pdf->Cell ( 40 , 5, conv_num(number_format($tot_gen_pagato,2))." Euro" , 0, 1, "R");
		
		$pdf->movePage($pdf->PageNo(), 1);
		
		$pdf->Output( $file_elenco , 'F');
		
	}//PDF
	
	
	if($cont_result == 0) 
	{
		if($tipo_elenco=="PDF")
			unlink($file_elenco);
		
		echo "<script>nessun_risultato();</script>";
	}
	else	
	{
		if($tipo_elenco=="PDF")
			echo "<script>fine_pdf('Elaborazione completata');</script>";
		else 
		{
					
			// Salvataggio file .xls
			$oggettoWriter = PHPExcel_IOFactory::createWriter($oggettoExcel, 'Excel5');
			$oggettoWriter->save($file_elenco);
			
			echo "<script>fine_excel('Elaborazione completata');</script>";
		}
	}

?>

</body>
</html>