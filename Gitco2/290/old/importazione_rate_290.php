<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/290.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/coazione.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = get_var('a');
$c = get_var('c');
$p = get_var('p');
$progr_n0 = get_var('id_n0');

$comune = new ente_gestito($c);
$nome_comune = $comune->Nome;

$nome_comune =($nome_comune==NULL?"":$nome_comune." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$numero_ruolo = get_var('numero_ruolo');
if($numero_ruolo==null)$numero_ruolo=0;

$duenovanta = new N0N9( $progr_n0 );
$num_N1 = $duenovanta->Record_N1;

flush();
ob_flush();

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<link rel="shortcut icon"  href="/gitco2/immagini/gitco.png">
<title>Ruolo Coattivo - Importazione rateizzazioni</title>

<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
<style> .ui-datepicker { font-size:11px; } </style>


<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>

<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>

<script>
$(document).ready(function(){

	$('#descr_ruolo').focus();

});

function inizio()
{
	$( "#form_importazione" ).hide();
	$('#progressbar').progressbar();
	$( "#barlabel" ).text("Inizio Importazione...");
}

function fine()
{
	$( "#progressbar" ).progressbar({value: 100 });
	$( "#barlabel" ).text("Fine Importazione!");
	
setTimeout(function() {
			
		$( "div#importazione" ).append("<input type=button name=ruolo class=button_azzurro value='Ritorna al ruolo' onclick='ruolo();'>");
	
	}, 1000);
}

function ruolo()
{
	location.href = "/gitco2/coattiva/gestione_ruolo.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

function successivo()
{
	location.href = "importazione_rate_290.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&id_n0=<?php echo $progr_n0; ?>&numero_ruolo=<?php echo ($numero_ruolo+1); ?>&posted=true";
}

function ruolo_next()
{
	$( "#progressbar" ).progressbar({value: 100 });
	$( "#barlabel" ).text("Ruolo <?php echo $numero_ruolo; ?> Importato con successo!");
	setTimeout(function() {
		
		$( "#progressbar" ).hide();
		$( "#form_importazione" ).show();
		
		successivo();
	
	}, 2000);
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
		
				<?php include MENU . '/menu_generale.php'; ?>      
<script>
var blocca_menu = 1;
</script>			
          
<table class="table_interna text_center" border=0 cellspacing=4>
	<tr>
		<td align=center width=7%>
			<a onMouseover="title='Modifica'" href="#" onClick="">
			<img src="/gitco2/immagini/redF2grey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<input id="submit_click" type="image" title="Salva" src="/gitco2/immagini/Save-iconF3grey.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td align=center width=7% >
			<input id="delete_click" type="image" title="Elimina" src="/gitco2/immagini/delete-iconF4grey.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Annulla'" href="#" onClick="annulla();" style="text-decoration: none;">
			<img src="/gitco2/immagini/undogrey.png" width=47 height=47 border=0>
			</a>
		</td>
		<td align=center width=7% >	
			<a onMouseover="title='Nuovo Record'" href="#" onClick="nuovo_F6();" style="text-decoration: none;">
			<img src="/gitco2/immagini/nuovogrey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Pagina precedente'" href="#" onclick="pag_prec();" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciagiugrey.png" width=47 height=47 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Pagina successiva'" href="#" onclick="pag_suc();" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciasugrey.png" width=47 height=47 border=0>
			</a>
		</td>
		<td width=7% align="center">
          	<a href="#" onMouseover="title='Record precedente F7'" onclick="cambia_pag('prev')">
          	<img src="/gitco2/immagini/FrecciaSgrey.png" width=42px height=42px border="0" alt="Utente precedente">
          	</a>
    	</td>
        <td width=7% align="center">
            <a href="#" onMouseover="title='Record successivo F8'" onclick="cambia_pag('suc')">
            <img src="/gitco2/immagini/FrecciaDgrey.png" width=42px height=42px border="0" alt="Utente successivo">
            </a>
        </td>
        <td width=11%></td>
        <td width=7% align="center">
          	<a href="#" onMouseover="title='Stampa'" onclick="">
          	<img src="/gitco2/immagini/printF10grey.png" width=50px height=50px border="0" ></a>
    	</td>
        <td width=3%></td>
    	<td align=center width=7% >
    			<a onMouseover="title='Help'" href="#" onClick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/helpgrey.png" width=50 height=50 border=0>
			</a>
		</td>
		<td width=2%></td>
		<td width=7%>
			<a onMouseover="title='Home'" href="#" onClick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/homegrey.png" width=60 height=50 border=0>
			</a>
		</td>
	</tr>
</table>
		
		<br><br>
		
		<font class="titolo font18 text_center">Controlli Importazione</font>
		
		<br><br>
		
		<div class="table_interna text_center" id="progressbar_ini" style="height:55px;"><div class="text_center" id="barlabel_ini"></div></div>
		
		<br><br><br><br>
		<font class="titolo font18 text_center">Importazione Rateizzazione Ingiunzioni File 290</font>
		<br><br><br>
		
		<form id=form_importazione name=form_importazione action="importazione_rate_290.php?posted=true" method=post accept-charset=utf-8>
		<input type=hidden name=id_n0 value="<?php echo $progr_n0; ?>">
		<input type="hidden" name="c" value="<?php echo $c?>">
		<input type="hidden" name="a" value="<?php echo $a?>">
		
		</form>
		
		<br><br>
		<div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>
		<br><br>
		<div id=importazione></div>
		</td>
	</tr>
</table>

<script>

$('#progressbar_ini').progressbar({value: 100 });
$( "#barlabel_ini" ).text("Controlli effettuati!");

</script>

<?php

$posted = get_var('posted');

if($posted==true)
{
	flush();
	ob_flush();
	flush();
	ob_flush();
	echo "<script>inizio();</script>";
	sleep(2);
	flush();
	ob_flush();
	flush();
	ob_flush();
	
	set_time_limit(100);
	
$descrizione_ruolo_N1 = get_var('descrizione_ruolo');

$duenovanta = new N0N9( $progr_n0 );
$data_fornitura = from_mysql_date($duenovanta->Data_Invio_Fornitura);

	$enne1 = $duenovanta->n1[$numero_ruolo];
	$id_ingiunzioni = "";
	//CICLO ANAGRAFICHE INTESTATARI N2
	for($y=0;$y<$duenovanta->Record_N2;$y++)
	{		
		$continua_ciclo = 0;
		$num_rata = 1;
		
		$importi_rate = "";
		$scadenze_rate = "";
		$numero_rate = 0;
		$data_richiesta_rate = "";
		
		set_time_limit(30);
		
		flush();
		ob_flush();
		echo "<script>$( \"#progressbar\" ).progressbar({value: " .intval($y*100/$duenovanta->Record_N2). " });$( \"#barlabel\" ).text(" .intval($y*100/$duenovanta->Record_N2). "+'%');</script>";
		flush();
		ob_flush();
			
		$enne2 = $enne1->n2[$y];
		
		$control_rate = 0;

		//CICLO INFO CONTABILI N4
		for($x=0;$x<$enne2->num_n4;$x++)
		{
			set_time_limit(30);
			$enne4 = $enne2->n4[$x];		
			$codice_tributo_N4 = $enne4->Codice_Tributo;
			if( $enne4->Tipo_Sanzione == "IN" )
			{
				if($codice_tributo_N4 == 5242)
				{
					$info_cartella = $enne4->Info_Cartella;	
					
					$query = "SELECT AT.ID FROM partita_tributi as PA , atto as AT ";
					$query.= "WHERE AT.Partita_ID = PA.ID AND AT.Info_Cartella = '".$info_cartella."' ";
					$query.= "AND AT.CC = '".$c."' ";
					$query.= "AND AT.Data_Calcolo_Interessi = '".$enne4->Data_Calcolo."' ";
					$query.= "AND AT.Atto = 'Ingiunzione'";
					
					$atto_ID = single_answer_query($query);
					$id_ingiunzioni.= $atto_ID." *** ";
					if($atto_ID==null)
						break;
					else 
					{
						$ingiunzione = new atto($atto_ID, $c);
						$partita = new partita($ingiunzione->Partita_ID,$c);
						$control_atto_next = 0;
						$control_data_notifica = "";
						for($i=0;$i<count($partita->Atto);$i++)
						{
							$atto_control = $partita->Atto[$i];
							
							if($control_atto_next==1)
							{
								$control_data_notifica = $atto_control->Data_Notifica;
								break;
							}
							else if($atto_control->ID == $atto_ID)
								$control_atto_next = 1;
						}
					}
				}
				
				if($codice_tributo_N4 == 6667)
				{
					
					$rateizzazione_N4 = $enne4->Rateizzazione;
					
					$estrai_rate = explode('*', $rateizzazione_N4);
					$esci_dal_ciclo = 0;
					for($i=0;$i<count($estrai_rate)-1;$i++)
					{
						
						if($control_rate==0)
						{
							$data_richiesta_rate = $estrai_rate[$i+1];
							if($data_richiesta_rate>$control_data_notifica && $control_data_notifica!="")
							{
								alert("Rateizzazione riferita all'avviso");
								$esci_dal_ciclo = 1;
								break;
							}
							
							$importi_rate.= conv_num(number_format($estrai_rate[$i],2));
							$scadenze_rate.= from_mysql_date($estrai_rate[$i+1]);
							$control_rate = 1;
						}
						else
						{
							$importi_rate.= "*".conv_num(number_format($estrai_rate[$i],2));;
							$scadenze_rate.= "*".from_mysql_date($estrai_rate[$i+1]);
						}
						
						$numero_rate++;						
						$i++;
						
					}
					
					if($esci_dal_ciclo==1)
						break;				
				}
				
				if( ($codice_tributo_N4 != 6667 || $x == $enne2->num_n4 - 1) && $control_rate == 1)
				{
				
					mysql_query('BEGIN');
						
					$ingiunzione->Rate_Previste = $numero_rate;
					$ingiunzione->Importi_Rate = $importi_rate;
					$ingiunzione->Scadenze_Rate = $scadenze_rate;
					$ingiunzione->Data_Richiesta_Rate = $data_richiesta_rate;
						
					$control_salva = $ingiunzione->Update($ingiunzione->ID,true);
				
					if( $control_salva )
					{
						mysql_query('COMMIT');
					}
					else
					{
						alert('NO_ING '.mysql_error());
						mysql_query('ROLLBACK');
					}
					break;
				}				
			}
		}	
		
	}//CHIUSURA FOR N2
	
	$numero_ruolo++;
	if($numero_ruolo == $duenovanta->Record_N1)
	{
		echo "<script>fine();</script>";
		alert($id_ingiunzioni);
	}
	else
	{
		echo "<script>ruolo_next();</script>";
	}
	
	
}
?>

</body>
</html>