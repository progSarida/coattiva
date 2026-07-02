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

//AGGIUNGERE SANZIONE DA INGIUNZIONE


class MYPDF extends TCPDF {
	
	public function Header() {
		
		$this->SetFont('Arial', 'B', 12);
		$this->ln(8);
		$this->Cell(0, 5, "Elenco avvisi intimazione" , 0, false, 'C', 0, '', 0, false, 'T', 'M');
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

//PREPARAZIONE ELENCO
$elenco_dir = crea_dir(  ATTI ."/". $c . "/Intimazione_ad_adempiere/Elenco_elaborazioni" );
$data_file = date('Y-m-d_H-i-s');

$file_elenco = $elenco_dir."/elenco_avv_intimaz_".$data_file.".pdf";
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
		<font class="titolo font18 text_center">Elaborazione Avvisi di intimazione ad adempiere</font>
		
		<br><br>
		
		<div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>
		
		<br>
		
		<div id=vedi_file></div>
		
		</td>
	</tr>
</table>

<?php 

$data_calcolo = from_mysql_date(get_var('data_calcolo'));

$da_n_elenco  = get_var('da_n_elenco');
$a_n_elenco  = get_var('a_n_elenco');

$daco  = strtoupper(get_var('daco'));
$dano  = strtoupper(get_var('dano'));

$acog  = strtoupper(get_var('acog'));
$anom  = strtoupper(get_var('anom'));

$da_anno = get_var('da_anno');
$ad_anno = get_var('ad_anno');

$tipo_partita = get_var('tipo_partita');

$danotif = get_var('da_data');
$anotif = get_var('a_data');

$data_elaborazione = from_mysql_date(get_var('data_elab'));
$anno_elab = explode("/", $data_elaborazione);
$anno_elab = $anno_elab[2];

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

$y1_vert = crea_riga($pdf , $array_width, $array_intestaz, "up_down" , $styleRetta );

flush();
ob_flush();

echo "<script>inizio();</script>";

flush();
ob_flush();
flush();
ob_flush();
sleep(2);

/**		SELEZIONE UTENTI 			*/
$query_utente = da_a_utente( $c , $daco, $acog, $dano, $anom );
$array_utenti = mysql_array( $query_utente );

/** 	SELEZIONE PARTITE			*/
//SELEZIONE ANNI
$where = "( Anno_Riferimento >= '".$da_anno."' AND Anno_Riferimento <= '".$ad_anno."' AND Flag_Blocco_Coazione != 'si' )";

$query_partita = da_a_partita( $c , $da_n_elenco , $a_n_elenco , $where );
$array_partite = mysql_array( $query_partita );

/** 	SELEZIONE Date Notifica		*/
$query_notifica = da_a_data( $c , "Avviso di intimazione ad adempiere" , "Data_Notifica" , to_mysql_date($danotif) , to_mysql_date($anotif) );
$array_notif = mysql_array( $query_notifica );

$num_atti = count($array_notif);
$num_utenti = count($array_utenti);
$num_partite = count($array_partite); 
$cont_result = 0;
	
	for( $l=0; $l < $num_atti; $l++ )
	{	
		echo "<script>update(".ceil($l*100/$num_atti).");</script>";
		
		flush();
		ob_flush();
		flush();
		ob_flush();
		
		for( $k=0; $k < $num_partite; $k++ )
		{
			if( $array_notif[$l]['Partita_ID'] == $array_partite[$k]['ID'])
			{		
				if($tipo_partita != "")
					if($array_partite[$k]['Tipo']!=$tipo_partita)
						continue;
			
			for( $j=0; $j<$num_utenti; $j++ )
			{
				if( $array_partite[$k]['Utente_ID'] == $array_utenti[$j]['ID'] )
				{
					set_time_limit(30);
					
					$parametri = new parametri_annuali($c, date('Y-m-d') , $array_partite[$k]['Tipo'] );
					$spese_not = $parametri->Spese_Notifica;
					$importo_minimo = $parametri->Importo_Minimo;
					
					
					$partita = new partita($array_partite[$k]['ID'], $c, $array_partite[$k]['Anno_Riferimento']);
					if($partita->ultimo_atto!=$array_notif[$l]['ID'])
						break;	
					
					if($partita->Tipo == "CDS")
					{
						if($partita->Utente->Data_Morte!=null && $partita->Utente->Data_Morte!="0000-00-00")
							break;
					}
					
					$ultimo_atto = $partita->Atto[count($partita->Atto)-1];
					$giacenza = $ultimo_atto->Stato_Notifica;
					$ind_validato = $ultimo_atto->Indirizzo_Validato;
					$anomalia = $ultimo_atto->Motivo_Notifica;
					$rielabora_flag = $ultimo_atto->Rielabora_Flag;
					$info_cart = $ultimo_atto->Info_Cartella;
					$data_interessi = $ultimo_atto->Data_Calcolo_Interessi;
						
					if($giacenza!=0 && $ind_validato!="si" && $rielabora_flag!="si")
						break;					 
					
					if($ultimo_atto->controlloPagamenti($partita->Tipo)!="ok")
						break;
					
					$array_pignoramenti = $ultimo_atto->controlloPignoramento();
					$pignoramento = null;
					if($array_pignoramenti!=null)
					{
						$pignoramento = $array_pignoramenti[(count($array_pignoramenti)-1)];
					
						if($pignoramento->Stato_Stampa == "Stampato" || $pignoramento->ID_Cronologico > 0)
						{
							break;
						}
					}
					
					$rimanenza = $ultimo_atto->dovuto_senza_pagamenti();
					if($rimanenza['importo']<$importo_minimo)
						break;
					$importo = $rimanenza['importo'];
					$interessi_prec = $rimanenza['interessi'];
						
					$spese_prec = $ultimo_atto->Spese_Precedenti + $ultimo_atto->Spese_Notifica + $ultimo_atto->CAN + $ultimo_atto->CAD;
					$sanzione = $ultimo_atto->Sanzione;
					$somma_spese_notifica_prec = $partita->Somma_Spese_Notifica - $ultimo_atto->Spese_Notifica - $ultimo_atto->CAN - $ultimo_atto->CAD;
						
					//SE CDS CONTROLLO IMPORTI CODICI TRIBUTO PER EVENTUALI CORREZIONI
					if($partita->Tipo == "CDS"){
						$spese_originarie = $partita->spese_originarie();
						$sanzione_originaria = $partita->sanzione_originaria();
						$magg_originaria = $partita->maggiorazione_originaria();
					
						if($ultimo_atto->Spese_Precedenti == $somma_spese_notifica_prec )
							$spese_prec += $spese_originarie;
					
						if( $sanzione_originaria + $spese_originarie + $somma_spese_notifica_prec < $ultimo_atto->Importo ){
							$importo -= $magg_originaria;
							$sanzione = $magg_originaria;
						}
					}
					
					mysql_query('BEGIN');
						
					$query = "SELECT MAX(Comune_ID) as Com FROM atto WHERE CC = '".$c."'";
					$comune_id = single_query($query);
					
					$query = "SELECT MAX(ID_Cronologico) as Com FROM atto WHERE CC = '".$c."' AND Anno_Cronologico = '".$anno_elab."' AND Atto = 'Avviso di intimazione ad adempiere'";
					$crono_id = single_query($query);
					
					$salva = new atto(null,$c);
					
					$salva->CC = $c;
					$salva->Comune_ID = $comune_id + 1;
					$salva->Partita_ID = $partita->ID;
					$salva->Anno_Cronologico = "0";
					$salva->ID_Cronologico = "0";
					$salva->Atto = "Avviso di intimazione ad adempiere";
					$salva->Info_Cartella = $ultimo_atto->Info_Cartella;
					$salva->Stato_Stampa = "Da stampare";
					$salva->Modalita_Stampa = "posta";
					$salva->Tipo_Ufficiale = "diretta";
					$salva->Data_Elaborazione = to_mysql_date($data_elaborazione);
					$salva->Data_Calcolo_Interessi = $ultimo_atto->Data_Calcolo_Interessi;
					$salva->Sanzione = $sanzione;
					$salva->Importo = $importo;
					$salva->Spese_Precedenti = $spese_prec;
					$salva->Spese_Notifica = $spese_not;
					$salva->Data_Decorrenza_Interessi = $ultimo_atto->Data_Decorrenza_Interessi;
					$salva->Interessi_Precedenti = $interessi_prec;
					$salva->Totale_Dovuto = $rimanenza['totale'] + $spese_not;
					$salva->Riferimento = $ultimo_atto->Riferimento;
					$salva->Note = $ultimo_atto->Comune_ID." (ID Atto di riferimento)";
					$control_salva = $salva->Insert(true);
					
					if($control_salva)
					{
						$id_avviso = mysql_insert_id();
						
						$query = "UPDATE atto SET Stato = 'Scaduto' WHERE CC = '".$c."' AND Comune_ID = '".$ultimo_atto->Comune_ID."'";
						mysql_query($query);
						
						if($pignoramento!=null && $id_avviso!=null)
						{
														
							$coeff_coazione_pre = new coefficiente_coazione("*****",$pignoramento->Importo_Dovuto);
							if($coeff_coazione_pre==null)	$coeff_coazione_pre=0;
							$coeff_coazione_post = new coefficiente_coazione("*****",$salva->Totale_Dovuto);
							if($coeff_coazione_post==null)	$coeff_coazione_post=0;
							
							$pignoramento->Atto_ID = $id_avviso;
							$pignoramento->Importo_Dovuto = $salva->Totale_Dovuto;
							
							if($coeff_coazione_pre!=$coeff_coazione_post)
							{
								$pignoramento_spese = new spese_pignoramento($pignoramento->ID, $c);
								$pignoramento_spese->Incremento_Percentuale = $coeff_coazione_post;
								$array_spese = $pignoramento_spese->spese_array();
								
								for($x_spesa=1;$x_spesa<11;$x_spesa++)
								{
									if($array_spese[$x_spesa]['ID']>0)
										if($array_spese[$x_spesa]['tipo_spesa']!="A KM")
										{
											$rimborso_pulito = $array_spese[$x_spesa]['rimborso'] / ( 1 + $coeff_coazione_pre/100 );
											
											$array_spese[$x_spesa]['rimborso'] = $rimborso_pulito * ( 1 + $coeff_coazione_post/100 );
										}
								}
								
								$pignoramento_spese->inserisci_spese_array($array_spese);
								$pignoramento_spese->Update($pignoramento_spese->ID);
								
								$pignoramento->Totale_Spese_Accessorie = $pignoramento_spese->Totale_Rimborso;
							}							
														
							$pignoramento->Totale_Dovuto = ($pignoramento->Importo_Dovuto + $pignoramento->Totale_Spese_Accessorie + $pignoramento->Totale_Spese_Notifica);
							
							$pignoramento->Update($pignoramento->ID);
							
						}
						
						mysql_query('COMMIT');
						
						$ing = new atto($id_avviso, $c);
						$ID_avv = $ing->Comune_ID;
						$ID_partita = $partita->Comune_ID;
						$utente = new utente( $array_partite[$k]['Utente_ID'] , $c );
						$nome_utente = $utente->Cognome.$utente->Ditta." ".$utente->Nome;
						
						$pdf->SetFont('Arial', '', 10);

						$array_value = array();
						
						$array_value[] = $ID_avv;
						$array_value[] = $ID_partita;						
						$array_value[] = $nome_utente;				
						$array_value[] = $ing->Note;						
						$array_value[] = conv_num($ing->Totale_Dovuto)." Euro";
						
						$y = crea_riga ( $pdf , $array_width, $array_value, "down", $styleDash );
						
						if( $y > 266 )
						{
							$y2_vert = $pdf->getY();
							
							crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);
							
							$pdf->AddPage();
							$pdf->Ln(10);
							
							$pdf->SetFont('Arial', 'B', 11);
							
							$y1_vert = crea_riga($pdf , $array_width, $array_intestaz, "up_down", $styleRetta );							
							
						}
						
						$cont_result++;
					
					}
					
					else
					{
						mysql_query('ROLLBACK');
					}

					break;
					
				}//CHIUSURA IF			
			
			}//CHIUSURA UTENTI
	
			break;
			
			}//CHIUSURA IF
			
		}//CHIUSURA PARTITE
	
	}//CHIUSURA ATTI
	
	$y2_vert = $pdf->getY();
	
	crea_linee ($pdf, $array_width, $y1_vert , $y2_vert, $styleDash);
	
	$pdf->Output( $file_elenco , 'F');
	
	if($num_atti == 0 || $cont_result == 0) 
	{
		unlink($file_elenco);
		echo "<script>nessun_risultato();</script>";
	}
	else	echo "<script>fine('Elaborazione completata');</script>";

?>

</body>
</html>