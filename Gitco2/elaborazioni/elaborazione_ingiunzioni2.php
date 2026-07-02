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

$ingiunzione290 = get_var('ingiunzione290');
$modalitaStampa = get_var('modalita_stampa');

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

//echo $query_partita."<br>";
//echo $query_utente."<br>";

$num_partite = count($array_partite);
$num_utenti = count($array_utenti);

//$par_pagamento = new parametri_pagamento( $c, $tipo_partita );
//if(!$par_pagamento->ID>0 || !$par_pagamento->Scadenza_Sanzione>0){
//    alert("ATTENZIONE! La scadenza per la sanzione originaria non e' stata inserita nei Parametri pagamento per il tipo di riscossione ".$tipo_partita."!");
//    die;
//}

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

                $stato_stampa = "Da stampare";

				$partita = new partita($array_partite[$k]['ID'], $c, $array_partite[$k]['Anno_Riferimento']);

				$tributo = $partita->Tributo;
                $info_cart = $tributo[0]->Info_Cartella;

				$flag_blocco_diritto_riscossione = $partita->Flag_Blocco_Diritto_Riscossione;
				
				$utente = new utente( $array_partite[$k]['Utente_ID'] , $c );
				$nome_utente = $utente->Cognome.$utente->Ditta." ".$utente->Nome;

                if($utente->Data_Morte!=null && $utente->Data_Morte!="0000-00-00")
                    break;

                $parametri = new parametri_annuali($c, to_mysql_date($data_calcolo) , $partita->Tipo );
                $importo_min = $parametri->Importo_Minimo;
                $diritto_min = $parametri->Diritto_Riscossione_Minimo;
                $diritto_max = $parametri->Diritto_Riscossione_Massimo;

                $spese_not = $parametri->Spese_Notifica;
                if($ingiunzione290=="y"){
                    $spese_not = 0;
                    $diritto_min = 0;
                    $diritto_max = 0;
                }

                $interessi_prec = 0.00;
                $spese_not_precedenti = 0.00;
                $pagamenti_precedenti = 0.00;

                $importoInteressi = 0;
                $a_codici = $partita->totaleCodici();

                $rettifica_flag=null;
                if($partita->ultimo_atto > 0){
                    $ultimo_atto_valido = new atto($partita->ultimo_atto, $c);

                    if($ultimo_atto_valido->checkProcess("ingiunzione",Array("importo_minimo"=>$importo_min))===false)
                        break;

                    if($prima_ingiunzione == "si")
                        break;

                    $ultimo_atto = $partita->Atto[count($partita->Atto)-1];
                    $pagamenti_precedenti = $ultimo_atto->pagamenti_completi();
                    $rettifica_flag = $ultimo_atto->Rettifica_Flag;

                    if($rettifica_flag=="si"){

                        if(count($partita->Atto)==1 && $partita->Atto[0]->Atto=="Ingiunzione"){
                            $importoInteressi = $a_codici["IMPORTO_INTERESSI"];
                            $riferimento = 2;
                            $data_interessi = from_mysql_date($tributo[0]->Data_Decorrenza_Interessi);
                        }
                        else{
                            $riferimento = "";
                            for($i=count($partita->Atto)-1;$i>=0;$i--){
                                if($partita->Atto[$i]->Rettifica_Flag=="si" && $partita->Atto[$i-1]->Atto!="Sollecito di pagamento"
                                    && $partita->Atto[$i-1]->Atto!="Sollecito pre ingiunzione"){

                                    $atto_pre_rettifica = $partita->Atto[$i-1];
                                    break;

                                }
                            }

                            $data_interessi = from_mysql_date($atto_pre_rettifica->Data_Calcolo_Interessi);

                            $interessi_prec = $atto_pre_rettifica->Interessi_Precedenti+$atto_pre_rettifica->Interessi;
                            $spese_not_precedenti = $atto_pre_rettifica->Spese_Notifica_Precedenti+$atto_pre_rettifica->Spese_Notifica + $atto_pre_rettifica->CAN + $atto_pre_rettifica->CAD;

                            $totaleCheck = $a_codici["TOTALE"] + $interessi_prec + $spese_not_precedenti;
//                            if( number_format($totaleCheck,2)!=number_format($atto_pre_rettifica->Totale_Dovuto,2)){
//                                alert("L'ingiunzione della partita ".$partita->Comune_ID." del ".$partita->Anno_Riferimento." non verra' elaborata a causa di incoerenza dei dati!");
//                                break;
//                            }

                            if($partita->Tipo=="CDS"){
                                $importoInteressi = $a_codici["IMPORTO_INTERESSI"] + $spese_not_precedenti - $pagamenti_precedenti;
                            }
                            else{
                                if($totaleCheck-$pagamenti_precedenti<$a_codici["IMPORTO_INTERESSI"])
                                    $importoInteressi = $totaleCheck-$pagamenti_precedenti;
                                else
                                    $importoInteressi = $a_codici["IMPORTO_INTERESSI"];
                            }
                        }
                    }
                    else{
                        $riferimento = $ultimo_atto->Riferimento + 1;
                        $data_interessi = from_mysql_date($ultimo_atto->Data_Calcolo_Interessi);
                        if($ultimo_atto->Atto=="Avviso di messa in mora"){
                            if($data_interessi==null){
                                $obj_date = new DateTime(from_mysql_date($ultimo_atto->Data_Notifica));
                                $obj_date->modify("+15 days");
                                $data_interessi = $obj_date->format("Y-m-d");
                            }
                        }

                        $interessi_prec = $ultimo_atto->Interessi_Precedenti+$ultimo_atto->Interessi;
                        $spese_not_precedenti = $ultimo_atto->Spese_Notifica_Precedenti + $ultimo_atto->Spese_Notifica + $ultimo_atto->CAN + $ultimo_atto->CAD;

                        $totaleCheck = $a_codici["TOTALE"]+$spese_not_precedenti+$interessi_prec;

                        if( number_format($totaleCheck,2)!=number_format($ultimo_atto->Totale_Dovuto,2)){
                            alert("L'ingiunzione della partita ".$partita->Comune_ID." del ".$partita->Anno_Riferimento." non verra' elaborata a causa di incoerenza dei dati!");
                                break;
                        }

                        if($partita->Tipo=="CDS"){
                            $importoInteressi = $a_codici["IMPORTO_INTERESSI"] + $spese_not_precedenti - $pagamenti_precedenti;
                        }
                        else{
                            if($totaleCheck-$pagamenti_precedenti<$a_codici["IMPORTO_INTERESSI"])
                                $importoInteressi = $totaleCheck-$pagamenti_precedenti;
                            else
                                $importoInteressi = $a_codici["IMPORTO_INTERESSI"];
                        }
                    }
                }
                else{
                    $importoInteressi = $a_codici["IMPORTO_INTERESSI"];
                    $riferimento = 1;

                    $data_interessi = from_mysql_date($tributo[0]->Data_Decorrenza_Interessi);

                }

                $interessi = 0;
                if($partita->Flag_Blocco_Maggiorazioni!="si" && $parametri->Maggiorazione_Ingiunzione!="no")
                {
                    if($importoInteressi>0 && $ingiunzione290!="y"){
                        if($partita->Tipo == "CDS")
                        {
                            $interessi = calcola_interessi( $data_interessi, $data_calcolo, $importoInteressi );
                        }
                        else{

                            $interessi_tributi = new interessi_tributi($c);
                            $interessi_array = $interessi_tributi->calcola_interessi_tributi( $data_interessi, $data_calcolo, $importoInteressi );
                            $interessi = $interessi_tributi->totale_interessi_tributi($interessi_array);
                        }
                    }
                    else
                        $interessi = 0.00;

                }
                else{
                    $interessi = 0.00;
                }


                $interessi = number_format($interessi,2,".","");

                $totale_dovuto = $a_codici["TOTALE"] + $spese_not + $spese_not_precedenti + $interessi + $interessi_prec;

                $diritto_risc_min = 0.00;
                $diritto_risc_max = 0.00;

                if($flag_blocco_diritto_riscossione!="si" && $gestore->Tipo == "Concessionario")
                {
                    $importo_calcolo_diritto = $totale_dovuto - $pagamenti_precedenti;
                    $diritto_risc_min = $importo_calcolo_diritto * $diritto_min / 100;
                    $diritto_risc_max = $importo_calcolo_diritto * $diritto_max / 100;
                }

                $note = "Notifica num. ".$riferimento;

                mysql_query('BEGIN');

                $query = "SELECT MAX(Comune_ID) as Com FROM atto WHERE CC = '".$c."'";
                $comune_id = single_query($query);

                $query = "SELECT MAX(ID_Cronologico) as Com FROM atto WHERE CC = '".$c."' AND Anno_Cronologico = '".$anno_elab."' AND Atto = 'Ingiunzione' AND Cronologico_Vecchio!='si'";
                $crono_id = single_query($query);

                $salva = new atto(null,$c);

                $salva->CC = $c;
                $salva->Comune_ID = $comune_id + 1;
                $salva->Partita_ID = $partita->ID;

                $ID_cronologico = 0;
                $anno_cronologico = 0;
                if($ingiunzione290=="y"){
                    if($c=="A318"){
                        $anno_cronologico = 2018;
                        $infoExp = explode(" | ",$info_cart);
                        $ingExp = explode(" ",$infoExp[1]);
                        $cronoExp = explode("/",$ingExp[1]);
                        $ID_cronologico = $cronoExp[0];
                        $protocollo = $cronoExp[1];
                        $data_notifica = to_mysql_date($ingExp[4]);

                        $data_interessi = $data_notifica;
                        $data_calcolo = $data_notifica;
                        $stato_stampa = "Stampato";
                        $salva->Data_Stampa = $data_notifica;
                        $salva->Data_Notifica = $data_notifica;
                        $salva->Cronologico_Vecchio = "si";
                        $salva->Protocollo = $protocollo;

                        $info_cart = $infoExp[0];
                        $query = "UPDATE tributo JOIN partita_tributi ON partita_tributi.ID = tributo.Partita_ID ";
                        $query.= "SET Informazioni_Cartella = \"".$info_cart."\" WHERE partita_tributi.ID=".$partita->ID;

                    }
                    else if($c=="B256"){
                        $anno_cronologico = 2018;
                        $infoExp = explode(" ",$info_cart);
                        $cronoExp = explode("/",$infoExp[3]);
                        $ID_cronologico = $cronoExp[0];
                        if(isset($cronoExp[1]))
                            $protocollo = $cronoExp[1];
                        $data_notifica = to_mysql_date($infoExp[count($infoExp)-1]);

                        $data_interessi = to_mysql_date($tributo[0]->Data_Decorrenza_Interessi);
                        $data_calcolo = to_mysql_date($tributo[0]->Data_Decorrenza_Interessi);
                        $stato_stampa = "Stampato";
                        $salva->Data_Stampa = $data_notifica;
                        $salva->Data_Notifica = $data_notifica;
                        $salva->Cronologico_Vecchio = "si";
                        $salva->Protocollo = $protocollo;
                    }
                    else if($c=="L958"){
                        $anno_cronologico = 2018;
                        $infoExp = explode(" ",$info_cart);
                        $cronoExp = explode("/",$infoExp[2]);
                        $cronoExp[0] = str_replace("-","",$cronoExp[0]);
                        $arr = preg_split('/(?<=[0-9])(?=[a-z]+)/i',$cronoExp[0]);
                        $ID_cronologico = $arr[0];
                        if(isset($arr[1]))
                            $protocollo = $arr[1];
                        if(isset($arr[2]))
                            $protocollo.= $arr[2];
                        $data_notifica = to_mysql_date($infoExp[count($infoExp)-1]);

                        $data_interessi = to_mysql_date($tributo[0]->Data_Decorrenza_Interessi);
                        $data_calcolo = to_mysql_date($tributo[0]->Data_Decorrenza_Interessi);
                        $stato_stampa = "Stampato";
                        $salva->Data_Stampa = $data_notifica;
                        $salva->Data_Notifica = $data_notifica;
                        $salva->Cronologico_Vecchio = "si";
                        $salva->Protocollo = $protocollo;
                    }
                }

                $salva->ID_Cronologico = $ID_cronologico;
                $salva->Anno_Cronologico = $anno_cronologico;
                $salva->Data_Calcolo_Interessi = to_mysql_date($data_calcolo);
                $salva->Data_Decorrenza_Interessi = $data_interessi;
                $salva->Stato_Stampa = $stato_stampa;

                $salva->Atto = "Ingiunzione";
                $salva->Info_Cartella = $info_cart;

                $salva->Data_Elaborazione = to_mysql_date($data_elaborazione);

                if($rettifica_flag=="si"){
                    $salva->Modalita_Stampa = "ordinaria";
                    $salva->Tipo_Ufficiale = "rettifica";
                }
                else{
                    $salva->Modalita_Stampa = $modalitaStampa;
                    $salva->Tipo_Ufficiale = "diretta";
                }

                $salva->Riferimento = $riferimento;
                $salva->Note = $note;

                $salva->Spese_Notifica_Precedenti = $spese_not_precedenti;
                $salva->Spese_Notifica = $spese_not;


                $salva->Interessi = $interessi;
                $salva->Interessi_Precedenti = $interessi_prec;

                $salva->Diritto_Riscossione_Minimo = $diritto_risc_min;
                $salva->Diritto_Riscossione_Massimo = $diritto_risc_max;

                $salva->Totale_Dovuto = $totale_dovuto;


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