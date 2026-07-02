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
		
		$this->SetFont('Arial', 'B', 12);
		$this->ln(8);
		$this->Cell(0, 5, "Elenco ingiunzioni elaborate" , 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}
	
	public function Footer() {

		$this->SetY(-15);
		$this->SetFont('helvetica', 'N', 6);
		$this->Cell(0, 5, "Pag. ".$this->getPage()." - ".date("d/m/Y H\hi:s"), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	
	}
	
}

$a = get_var('a');
$c = get_var('c');

$comune = new ente_gestito($c);
$nome_com = $comune->Nome;
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$gestore = $comune->Gestore;

//PREPARAZIONE ELENCO
$elenco_dir = crea_dir( ATTI ."/". $c . "/Ingiunzioni/Elenco_elaborazioni" );
$data_file = date('Y-m-d_H-i-s');

$file_elenco = $elenco_dir."/elenco_ing_elab_".$data_file.".pdf";
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

function fine(value)
{
	$( "#progressbar" ).progressbar({value: 100 });
	$( "#barlabel" ).text( value );
	$( "div#vedi_file" ).append("<input type=button name=avanti class=button_azzurro value='Elenco elaborazioni' onclick='mostra_file();'>");
}

function mostra_file()
{
	window.open('<?php echo $vedi_file; ?>');
	self.close();
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
		<font class="titolo font18 text_center">Elaborazione Ingiunzioni</font>
		
		<br><br>
		
		<div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>
		
		<br>
		
		<div id=vedi_file></div>
		
		</td>
	</tr>
</table>

<?php 

$prima_ingiunzione = get_var('prima_ingiunzione');
$data_elaborazione = from_mysql_date(get_var('data_elab'));
$data_calcolo = from_mysql_date(get_var('data_calcolo'));

$da_n_elenco  = get_var('da_n_elenco');
$a_n_elenco  = get_var('a_n_elenco');

$daco  = strtoupper(get_var('daco'));
$dano  = strtoupper(get_var('dano'));

$acog  = strtoupper(get_var('acog'));
$anom  = strtoupper(get_var('anom'));

$da_anno = get_var('da_anno');
$ad_anno = get_var('ad_anno');

$anno_elab = explode("/", $data_elaborazione);
$anno_elab = $anno_elab[2];

$tipo_partita = get_var('tipo_partita');

/**		SELEZIONE UTENTI 			*/
$query_utente = da_a_utente( $c , $daco, $acog, $dano, $anom );
$array_utenti = mysql_array( $query_utente );

/** 	SELEZIONE PARTITE			*/
//SELEZIONE ANNI
$where = "( Anno_Riferimento >= '".$da_anno."' AND Anno_Riferimento <= '".$ad_anno."' AND Flag_Blocco_Coazione != 'si' )";

$query_partita = da_a_partita( $c , $da_n_elenco , $a_n_elenco , $where );
$array_partite = mysql_array( $query_partita );

$num_partite = count($array_partite);
$num_utenti = count($array_utenti);

$pdf = new MYPDF("P", "mm", "A4", true, 'UTF-8', false);
//$pdf->setPrintHeader(false);
$pdf->SetMargins(10, 10, 10);
$pdf->setCellPaddings(2,1,2,1);

$styleDash = array('dash' => '6,6');
$styleRetta = array('dash' => '0');

$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 11);

$pdf->SetAutoPageBreak(false);
$pdf->Ln(10);

$array_width = array();
$array_intestaz = array();
						
$array_width[] = 10;	$array_intestaz[] = "ID";
$array_width[] = 18;	$array_intestaz[] = "Partita";
$array_width[] = 60;	$array_intestaz[] = "Utente";
$array_width[] = 80;	$array_intestaz[] = "Informazioni";
$array_width[] = 25;	$array_intestaz[] = "Dovuto";

$y1_vert = crea_riga($pdf , $array_width, $array_intestaz, "up_down" , $styleRetta);

flush();
ob_flush();

echo "<script>inizio();</script>";

flush();
ob_flush();
flush();
ob_flush();
sleep(2);
		
	$id_ingiunzione = array();
	$partita_ok = array();
	$partita_ko = array();
	
	$cont_result = 0;
	for( $k=0; $k < $num_partite; $k++ )
	{	
		echo "<script>update(".ceil($k*100/$num_partite).");</script>";
		
		flush();
		ob_flush();
		flush();
		ob_flush();
		
		if($tipo_partita != "")
			if($array_partite[$k]['Tipo']!=$tipo_partita)
				continue;
		
		for( $j=0; $j < $num_utenti; $j++ )
		{			
			if($array_partite[$k]['Utente_ID'] == $array_utenti[$j]['ID'])
			{				
				set_time_limit(30);
								
				$partita = new partita($array_partite[$k]['ID'], $c, $array_partite[$k]['Anno_Riferimento']);
				$tributo = $partita->Tributo;
				$flag_blocco_maggiorazione = $partita->Flag_Blocco_Maggiorazioni;
				$flag_blocco_diritto_riscossione = $partita->Flag_Blocco_Diritto_Riscossione;
				
				$utente = new utente( $array_partite[$k]['Utente_ID'] , $c );
				$nome_utente = $utente->Cognome.$utente->Ditta." ".$utente->Nome;
				
				$ultimo_atto_id = $partita->ultimo_atto;
				$ultimo_atto = new atto($ultimo_atto_id, $c);
				$pignoramento = $ultimo_atto->controlloPignoramento();
				if($pignoramento!=null)
				{
// 					alert('pigno');
					break;
				}
				
				if($ultimo_atto->controlloPagamenti($partita->Tipo)!="ok" && count ( $partita->Atto ) > 0)
				{
// 					alert($ultimo_atto->controlloPagamenti($partita->Tipo));
					break;
				}
				
				$stato_stampa = "Da stampare";
				
				$importo = 0.00;
				$sanzione = 0.00;
				$interessi_prec = 0.00;
				$spese_prec = 0.00;
                $addizionale = 0.00;
				
				$parametri = new parametri_annuali($c, $data_calcolo , $partita->Tipo );
				$spese_not = $parametri->Spese_Notifica;
				$importo_min = $parametri->Importo_Minimo;
				$diritto_min = $parametri->Diritto_Riscossione_Minimo;
				$diritto_max = $parametri->Diritto_Riscossione_Massimo;
				
				$codice_tributo = new codice_tributo(null);
				if( count ( $partita->Atto ) == 0 )
				{					
					if(!isset($tributo[0]))
						break;
											
					$riferimento = 1;
					$info_cart = $tributo[0]->Info_Cartella;
					$data_interessi = $tributo[0]->Data_Decorrenza_Interessi;
					$rettifica_flag = null;
					for($i=0;$i<count($tributo);$i++)
					{
						$tipo_tributo = $codice_tributo->tipo_tributo($tributo[$i]->Codice_Tributo);
// 						alert($tributo[$i]->Imposta." ".$tipo_tributo." ".$tributo[$i]->Codice_Tributo);
						
						if($tipo_tributo=="IMPORTO" || $tipo_tributo=="SPESE")
							$importo += $tributo[$i]->Imposta;
						else if($tipo_tributo=="SANZIONE" || $tipo_tributo=="MAGGIORAZIONE")
							$sanzione += $tributo[$i]->Imposta;
						else if($tipo_tributo=="INTERESSI")
							$interessi_prec += $tributo[$i]->Imposta;
						else if($tipo_tributo=="PAGAMENTO")
							$importo -= $tributo[$i]->Imposta;
						else if($tipo_tributo=="ADDIZIONALE")
							$addizionale += $tributo[$i]->Imposta;
						
						if( $tributo[$i]->Codice_Tributo == 5242 && $tributo[$i]->Tipo_Sanzione == "VE")
							$importo-= $tributo[$i]->Pagamenti_Associati;
					}
					
					$importo = number_format($importo,2,".","");
					if($importo<=12)
						break;
				}
				else
				{	
					$control_break = 0;
					if($prima_ingiunzione == "si")
						break;
						
					//CICLO ATTI PARTITA
					//CONTROLLO ULTIMO ATTO USCITO TRA INGIUNZIONE E AVVISO
					//VENGONO ESCLUSI I SOLLECITI DI PAGAMENTO
					for( $i = count($partita->Atto); $i>0; $i-- )
					{
						$atto_current = $partita->Atto[$i-1];
						$tipo_ultimo_atto = $atto_current->Atto;
						if($tipo_ultimo_atto=="Sollecito di pagamento")
							continue;
							
						switch($tipo_ultimo_atto)
						{
							case "Ingiunzione":							$data_control = date("Y-m-d" , strtotime( date('Y-m-d')."-1 year" ));	break;						
							case "Avviso di intimazione ad adempiere":	$data_control = date("Y-m-d" , strtotime( date('Y-m-d')."-6 month" ));	break;
						}
							
						$data_not_ultima_ing = to_mysql_date($atto_current->Data_Notifica);
						$anomalia = $atto_current->Motivo_Notifica;
						$rielabora_flag = $atto_current->Rielabora_Flag;
						$rettifica_flag = $atto_current->Rettifica_Flag;
						$giacenza = $atto_current->Stato_Notifica;
						$ind_validato = $atto_current->Indirizzo_Validato;
						
						if($rielabora_flag!='si' && $rettifica_flag!="si")
						{
							if( $data_not_ultima_ing > $data_control && $data_not_ultima_ing!=null){
								$control_break = 1;
								break;
							}
							else if($giacenza!=0 && $ind_validato!="si" && $data_not_ultima_ing!=null){
								$control_break = 1;
								break;
							}
							else if($data_not_ultima_ing==null){
								$control_break = 1;
								break;
							}
						}
						
						if($rettifica_flag=="si" && from_mysql_date($atto_current->Data_Notifica)==null){
							$control_break = 1;
							break;
						}
							
						if($rettifica_flag=="si" && $i-2>=0)
							$atto_prec_rettifica = $partita->Atto[$i-2];
						else 
							$atto_prec_rettifica = null;
						
						break;
					}
					
					//CONTROLLO CICLO PRECEDENTE
					if($control_break==1)
						break;
					
					$ultimo_atto = $partita->Atto[count($partita->Atto)-1];
					$riferimento = $ultimo_atto->Riferimento + 1;
					$info_cart = $ultimo_atto->Info_Cartella;
					$data_interessi = $ultimo_atto->Data_Calcolo_Interessi;
					
					$rimanenza = $ultimo_atto->dovuto_senza_pagamenti();
					if($rimanenza['importo']<$importo_min)
						break;
					$importo = $rimanenza['importo'];
					$interessi_prec = $rimanenza['interessi'];
					
					$spese_prec = $ultimo_atto->Spese_Precedenti + $ultimo_atto->Spese_Notifica + $ultimo_atto->CAN + $ultimo_atto->CAD;
					$sanzione = $ultimo_atto->Sanzione;	

					if($rettifica_flag=="si"){
						
						$importo = 0;
						$sanzione = 0;
						$interessi_prec = 0;
						$addizionale = 0;
						$spese_prec = 0;
						
						if(count($partita->Atto)==1){
							$riferimento = 2;
							$data_interessi = $tributo[0]->Data_Decorrenza_Interessi;
							for($i=0;$i<count($tributo);$i++)
							{
								$tipo_tributo = $codice_tributo->tipo_tributo($tributo[$i]->Codice_Tributo);
								
								if($tipo_tributo=="IMPORTO" || $tipo_tributo=="SPESE")
									$importo += $tributo[$i]->Imposta;
								else if($tipo_tributo=="SANZIONE" || $tipo_tributo=="MAGGIORAZIONE")
									$sanzione += $tributo[$i]->Imposta;
								else if($tipo_tributo=="INTERESSI")
									$interessi_prec += $tributo[$i]->Imposta;
								else if($tipo_tributo=="PAGAMENTO")
									$importo -= $tributo[$i]->Imposta;
								else if($tipo_tributo=="ADDIZIONALE")
                                    $addizionale += $tributo[$i]->Imposta;
						
								if( $tributo[$i]->Codice_Tributo == 5242 && $tributo[$i]->Tipo_Sanzione == "VE")
									$importo-= $tributo[$i]->Pagamenti_Associati;
							}		
							
						}
						else{
							$riferimento = $atto_prec_rettifica->Riferimento + 2;
							$info_cart = $partita->Tributo[0]->Info_Cartella;
							$data_interessi = $atto_prec_rettifica->Data_Calcolo_Interessi;
							$rimanenza_prec = $atto_prec_rettifica->dovuto_senza_pagamenti();
							if($rimanenza_prec['importo']<$importo_min)
								break;
							$importo = $rimanenza_prec['importo'];
							$interessi_prec = $rimanenza_prec['interessi'];
								
							$spese_prec = $atto_prec_rettifica->Spese_Precedenti + $atto_prec_rettifica->Spese_Notifica + $atto_prec_rettifica->CAN + $atto_prec_rettifica->CAD;
							$sanzione = $atto_prec_rettifica->Sanzione;
						}
					}
					
		
				}			
							
				if($flag_blocco_maggiorazione!="si")
				{
					if($partita->Tipo == "CDS")
					{
						$interessi = calcola_interessi( $data_interessi, $data_calcolo, $importo+$sanzione );
						$magg_ing = $parametri->Maggiorazione_Ingiunzione;
						if($magg_ing == "no")	$interessi = 0.00;
						
						//SE CDS E PRIMA INGIUNZIONE CALCOLO INTERESSI SOLO SE PRESENTE MAGGIORAZIONE ($interessi_prec) ALTRIMENTI AZZERO
						if( count ( $partita->Atto ) == 0 ){
							if($interessi_prec > 0){
								$interessi_prec = 0;
							}
//							else{
//								$interessi = 0;
//								$partita->Flag_Blocco_Maggiorazioni == "si";
//								$partita->Update($partita->ID);
//							}
						}

						if($utente->Data_Morte!=null && $utente->Data_Morte!="0000-00-00")
							break;
					}
					else{
						$interessi_tributi = new interessi_tributi($c);
						$interessi_array = $interessi_tributi->calcola_interessi_tributi( $data_interessi, $data_calcolo, $importo+$sanzione );
						$interessi = $interessi_tributi->totale_interessi_tributi($interessi_array);

						if($utente->Data_Morte!=null && $utente->Data_Morte!="0000-00-00")
							$sanzione = 0;
					}
				}
				else
					$interessi = 0.00;
				
				$diritto_risc_min = 0.00;
				$diritto_risc_max = 0.00;
				if( count ( $partita->Atto ) == 0 )
				{
					if($flag_blocco_diritto_riscossione!="si" && $gestore->Tipo == "Concessionario")
					{
						$importo_calcolo_diritto = $importo + $sanzione + $addizionale + $spese_not + $interessi + $interessi_prec;
						
						$diritto_risc_min = $importo_calcolo_diritto * $diritto_min / 100;
						$diritto_risc_max = $importo_calcolo_diritto * $diritto_max / 100;

//						alert($importo." - ".$diritto_risc_min." - ".$diritto_risc_max);
					}

				}
				else
				{
					if($rettifica_flag=="si"){
						if(count ( $partita->Atto ) == 1){
							if($flag_blocco_diritto_riscossione!="si" && $gestore->Tipo == "Concessionario")
							{
								$importo_calcolo_diritto = $importo + $sanzione + $addizionale + $spese_not + $interessi + $interessi_prec;
							
								$diritto_risc_min = $importo_calcolo_diritto * $diritto_min / 100;
								$diritto_risc_max = $importo_calcolo_diritto * $diritto_max / 100;
							}
						}
						else{
							if($atto_prec_rettifica->Diritto_Riscossione_Minimo>0 && $atto_prec_rettifica->Diritto_Riscossione_Massimo>0)
								$importo_calcolo_diritto = $interessi + $spese_not;
							else
								$importo_calcolo_diritto = $importo + $sanzione + $addizionale + $spese_not + $interessi + $interessi_prec;
							
							$diritto_risc_min = $atto_prec_rettifica->Diritto_Riscossione_Minimo + ( $importo_calcolo_diritto * $diritto_min / 100 );
							$diritto_risc_max = $atto_prec_rettifica->Diritto_Riscossione_Massimo + ( $importo_calcolo_diritto * $diritto_max / 100 );
						}
					}
					else if($flag_blocco_diritto_riscossione!="si" && $gestore->Tipo == "Concessionario")
					{
						if($ultimo_atto->Diritto_Riscossione_Minimo>0 && $ultimo_atto->Diritto_Riscossione_Massimo>0)
							$importo_calcolo_diritto = $interessi + $spese_not;							
						else 
							$importo_calcolo_diritto = $importo + $sanzione + $addizionale + $spese_not + $interessi + $interessi_prec;
						
						$diritto_risc_min = $ultimo_atto->Diritto_Riscossione_Minimo + ( $importo_calcolo_diritto * $diritto_min / 100 );
						$diritto_risc_max = $ultimo_atto->Diritto_Riscossione_Massimo + ( $importo_calcolo_diritto * $diritto_max / 100 );
					}
				}
				
				$totale_dovuto = $interessi + $importo + $sanzione + $addizionale + $spese_not + $interessi_prec;
				$note = "Notifica num. ".$riferimento;
				
				mysql_query('BEGIN');
				
				$query = "SELECT MAX(Comune_ID) as Com FROM atto WHERE CC = '".$c."'";
				$comune_id = single_query($query);
				
				$query = "SELECT MAX(ID_Cronologico) as Com FROM atto WHERE CC = '".$c."' AND Anno_Cronologico = '".$anno_elab."' AND Atto = 'Ingiunzione'";
				$crono_id = single_query($query);
				
				$salva = new atto(null,$c);
				
				$salva->CC = $c;
				$salva->Comune_ID = $comune_id + 1;
				$salva->Partita_ID = $partita->ID;
				$salva->Anno_Cronologico = "0";
				$salva->Atto = "Ingiunzione";
				$salva->ID_Cronologico = "0";
				$salva->Info_Cartella = $info_cart;
				$salva->Stato_Stampa = $stato_stampa;
				$salva->Data_Elaborazione = to_mysql_date($data_elaborazione);
				
				$salva->Data_Calcolo_Interessi = to_mysql_date($data_calcolo);
				$salva->Importo = $importo;
				$salva->Addizionale = $addizionale;
				$salva->Sanzione = $sanzione;
				$salva->Spese_Precedenti = $spese_prec;
				$salva->Spese_Notifica = $spese_not;
				$salva->Data_Decorrenza_Interessi = $data_interessi;

				$salva->Interessi = $interessi;
				$salva->Interessi_Precedenti = $interessi_prec;
				$salva->Diritto_Riscossione_Minimo = $diritto_risc_min;
				$salva->Diritto_Riscossione_Massimo = $diritto_risc_max;
				$salva->Totale_Dovuto = $totale_dovuto;
				
				if($rettifica_flag=="si"){
					$salva->Modalita_Stampa = "ordinaria";
					$salva->Tipo_Ufficiale = "rettifica";
				}
				else{
					$salva->Modalita_Stampa = "posta";
					$salva->Tipo_Ufficiale = "diretta";
				}				
				
				$salva->Riferimento = $riferimento;
				$salva->Note = $note;
				$control_salva = $salva->Insert(true);
				
				if($control_salva)
				{
					$id_ingiunzione = mysql_insert_id();
					
					mysql_query('COMMIT');
					
					$ing = new atto($id_ingiunzione, $c);
					$ID_ing = $ing->Comune_ID;
					$ID_partita = $partita->Comune_ID;
					
					
					$pdf->SetFont('Arial', '', 10);

					$array_value = array();
					
					$array_value[] = $ID_ing;	
					$array_value[] = $ID_partita;						
					$array_value[] = $nome_utente;					
					$array_value[] = $info_cart;						
					$array_value[] = number_format($totale_dovuto,2,",",".");
					
					$y = crea_riga($pdf , $array_width, $array_value, "down" , $styleDash);
					
					if( $y > 266 )
					{
						
						$y2_vert = $pdf->getY();
						
						crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);
						
						$pdf->AddPage();
						$pdf->Ln(10);
						
						$pdf->SetFont('Arial', 'B', 11);
						
						$y1_vert = crea_riga($pdf , $array_width, $array_intestaz , "up_down" , $styleRetta);							
						
					}
					
					$cont_result++;
					
				}
				else
				{
					mysql_query('ROLLBACK');
				}
					
				break;
			}				
				
		}
	
	}
	
	$y2_vert = $pdf->getY();
	
	crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);
	
	$pdf->Output( $file_elenco , 'F');
	
	
	if($cont_result == 0) 
	{
		unlink($file_elenco);
		echo "<script>nessun_risultato();</script>";
	}
	else	echo "<script>fine('Elaborazione completata');</script>";

?>

</body>
</html>