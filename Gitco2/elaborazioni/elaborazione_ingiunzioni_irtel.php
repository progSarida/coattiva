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
$elenco_dir = crea_dir( ATTI ."/IRTEL/Ingiunzioni/Elenco_elaborazioni" );
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

$query_utente = "(SELECT ID, Nome, Cognome AS utente_cognome FROM utente ";
$query_utente.= "WHERE Cognome != '' ";
if($daco != null)
{
    $query_utente.= "AND ( ( Cognome > '".addslashes($daco)."' ) ";
    $query_utente.= "AND ( Cognome < '".addslashes($acog)."' ) ";
    $query_utente.= "OR ( Cognome = '".addslashes($daco)."' ";
    if($dano != null)
    {
        $query_utente.= "AND Nome >= '".addslashes($dano)."' ";
    }

    $query_utente.= ") OR ( Cognome = '".addslashes($acog)."' ";
    if($anom != null)
    {
        $query_utente.= "AND Nome <= '".addslashes($anom)."' ";
    }
    $query_utente.= ") ) ";
}

$query_utente.= " ) ";

$query_utente.= "UNION ";
$query_utente.= "(SELECT ID, Nome, Ditta AS utente_cognome FROM utente ";
$query_utente.= "WHERE Ditta != '' ";

if($daco != null)
{
    $query_utente.= "AND ( Ditta >= '".addslashes($daco)."' AND Ditta <= '".addslashes($acog)."' ) ";
}
$query_utente.= ") ";
$query_utente.= "ORDER BY utente_cognome ASC, Nome ASC";
$array_utenti = mysql_array( $query_utente );

/** 	SELEZIONE PARTITE			*/
//SELEZIONE ANNI
$where = "( partita_tributi.Anno_Riferimento >= '".$da_anno."' AND partita_tributi.Anno_Riferimento <= '".$ad_anno."' AND partita_tributi.Flag_Blocco_Coazione != 'si' )";

$query_partita = "SELECT partita_tributi.* FROM partita_tributi JOIN enti_gestiti ON enti_gestiti.CC = partita_tributi.CC AND enti_gestiti.Autorizzazione=3 ";
$query_partita.= "WHERE ".$where." ";
if($da_n_elenco != null)
    $query_partita.= "AND ( partita_tributi.Comune_ID >= '".$da_n_elenco."' AND partita_tributi.Comune_ID <= '".$a_n_elenco."' ) ";


$query_partita.= "ORDER BY Comune_ID ASC";
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

                $stato_stampa = "Da stampare";

				$partita = new partita($array_partite[$k]['ID'], $array_partite[$k]['CC'], $array_partite[$k]['Anno_Riferimento']);
				$tributo = $partita->Tributo;
                $info_cart = $tributo[0]->Info_Cartella;

				$flag_blocco_diritto_riscossione = $partita->Flag_Blocco_Diritto_Riscossione;
				
				$utente = new utente( $array_partite[$k]['Utente_ID'] , $array_partite[$k]['CC'] );
				$nome_utente = $utente->Cognome.$utente->Ditta." ".$utente->Nome;

                if($utente->Data_Morte!=null && $utente->Data_Morte!="0000-00-00")
                    break;

                $parametri = new parametri_annuali($array_partite[$k]['CC'], $data_calcolo , $partita->Tipo );
                $importo_min = $parametri->Importo_Minimo;
                $diritto_min = $parametri->Diritto_Riscossione_Minimo;
                $diritto_max = $parametri->Diritto_Riscossione_Massimo;

                $spese_not = $parametri->Spese_Notifica;

                $interessi_prec = 0.00;
                $spese_not_precedenti = 0.00;
                $pagamenti_precedenti = 0.00;

                $importoInteressi = 0;
                $a_codici = $partita->totaleCodici();

                $rettifica_flag=null;
                if($partita->ultimo_atto > 0){
                    $ultimo_atto_valido = new atto($partita->ultimo_atto, $array_partite[$k]['CC']);
                    if($ultimo_atto_valido->checkProcess("ingiunzione",Array("importo_minimo"=>$importo_min))===false)
                        break;

                    if($prima_ingiunzione == "si")
                        break;

                    $ultimo_atto = $partita->Atto[count($partita->Atto)-1];
                    $pagamenti_precedenti = $ultimo_atto->pagamenti_completi();
                    $rettifica_flag = $ultimo_atto->Rettifica_Flag;

                    if($rettifica_flag=="si"){

                        if(count($partita->Atto)==1){
                            $importoInteressi = $a_codici["IMPORTO_INTERESSI"];
                            $riferimento = 2;
                            $data_interessi = from_mysql_date($tributo[0]->Data_Decorrenza_Interessi);
                        }
                        else{
                            for($i=count($partita->Atto)-1;$i>=0;$i--){
                                if($partita->Atto[$i]->Rettifica_Flag=="si" && $partita->Atto[$i-1]->Atto!="Sollecito di pagamento"){
                                    $atto_pre_rettifica = $partita->Atto[$i-1];
                                    break;
                                }
                            }

                            $data_interessi = from_mysql_date($atto_pre_rettifica->Data_Calcolo_Interessi);
                            $interessi_prec = $atto_pre_rettifica->Interessi_Precedenti+$atto_pre_rettifica->Interessi;
                            $spese_not_precedenti = $atto_pre_rettifica->Spese_Notifica_Precedenti+$atto_pre_rettifica->Spese_Notifica + $atto_pre_rettifica->CAN + $atto_pre_rettifica->CAD;

                            $totaleCheck = $a_codici["TOTALE"] + $interessi_prec + $spese_not_precedenti;

                            if( number_format($totaleCheck,2)!=number_format($ultimo_atto->Totale_Dovuto,2)){
                                alert("L'ingiunzione della partita ".$partita->Comune_ID." del ".$partita->Anno_Riferimento." non verra' elaborata a causa di incoerenza dei dati!");
                                break;
                            }

                            if($partita->Tipo=="CDS"){
                                $importoInteressi = $a_codici["IMPORTO_INTERESSI"] + $spese_not_precedenti - $pagamenti_precedenti;
                            }
                            else{
                                if($totaleCheck-$pagamenti_precedenti()<$a_codici["IMPORTO_INTERESSI"])
                                    $importoInteressi = $totaleCheck-$pagamenti_precedenti;
                            }
                        }
                    }
                    else{
                        $riferimento = $ultimo_atto->Riferimento + 1;
                        $data_interessi = from_mysql_date($ultimo_atto->Data_Calcolo_Interessi);

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
                            if($totaleCheck-$pagamenti_precedenti()<$a_codici["IMPORTO_INTERESSI"])
                                $importoInteressi = $totaleCheck-$pagamenti_precedenti;
                        }
                    }
                }
                else{
                    $importoInteressi = $a_codici["IMPORTO_INTERESSI"];
                    $riferimento = 1;
                    $data_interessi = from_mysql_date($tributo[0]->Data_Decorrenza_Interessi);

                }

                if($partita->Flag_Blocco_Maggiorazioni!="si" && $parametri->Maggiorazione_Ingiunzione!="no")
                {
                    if($importoInteressi>0){
                        if($partita->Tipo == "CDS")
                        {
                            $interessi = calcola_interessi( $data_interessi, $data_calcolo, $importoInteressi );
                        }
                        else{

                            $interessi_tributi = new interessi_tributi($array_partite[$k]['CC']);
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

                $query = "SELECT MAX(Comune_ID) as Com FROM atto WHERE CC = '".$array_partite[$k]['CC']."'";
                $comune_id = single_query($query);

                $query = "SELECT MAX(ID_Cronologico) as Com FROM atto WHERE CC = '".$array_partite[$k]['CC']."' AND Anno_Cronologico = '".$anno_elab."' AND Atto = 'Ingiunzione'";
                $crono_id = single_query($query);

                $salva = new atto(null,$array_partite[$k]['CC']);

                $salva->CC = $array_partite[$k]['CC'];
                $salva->Comune_ID = $comune_id + 1;
                $salva->Partita_ID = $partita->ID;
                $salva->Anno_Cronologico = "0";
                $salva->Atto = "Ingiunzione";
                $salva->ID_Cronologico = "0";
                $salva->Info_Cartella = $info_cart;
                $salva->Stato_Stampa = $stato_stampa;
                $salva->Data_Elaborazione = to_mysql_date($data_elaborazione);
                $salva->Data_Calcolo_Interessi = to_mysql_date($data_calcolo);
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

                $salva->Spese_Notifica_Precedenti = $spese_not_precedenti;
                $salva->Spese_Notifica = $spese_not;
                $salva->Data_Decorrenza_Interessi = $data_interessi;

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

                    $ing = new atto($id_ingiunzione, $array_partite[$k]['CC']);
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