<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";
include CLASSI . "/comuni.php";
include CLASSI . "/ruolo.php";
include CLASSI . "/coazione.php";
include CLASSI . "/parametri.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}


$a = get_var('a');
$c = get_var('c');
$p = get_var('p');

$array_cronologici = get_var('array_crono');
$tipo_pigno = get_var('pigno_val');

$layout = "";

set_time_limit(100);

$pignoramento = array();
for($i=0;$i<count($array_cronologici);$i++)
{	
	$pignoramento[$i] = new pignoramento($array_cronologici[$i], $c);
	
	if($i==0)
	{
		$crono = $pignoramento[$i]->ultimo_id(date('Y'));
		$ultimo_proto = $pignoramento[$i]->ultimo_proto(date('Y'));
	}
}

$comune = new ente_gestito($c);
$nome_com = $comune->Nome;
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$parametri = new parametri_pignoramento( $c );
$tipo_prot = $parametri->Tipo_Protocollo;
$fisso_prot = $parametri->Fisso_Protocollo;

$titolo_pag = "Cronologici Pignoramenti";

if($tipo_pigno=="veicolo")	
	$desc_pignoramento = "Beni mobili registrati";
else if($tipo_pigno=="lavoro")
	$desc_pignoramento = "Presso datore di lavoro";
else if($tipo_pigno=="banca")
	$desc_pignoramento = "Presso banca";



?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Cronologici pignoramenti</title>

<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
<style> .ui-datepicker { font-size:11px; } </style>

<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery.bpopup.min.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>


<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>


<!-- ********** VARIABILI ********** -->
<script>

var tipo_pigno = "<?php echo $tipo_pigno ?>";
var pigno_val = null;
if( tipo_pigno == "veicolo")
{
	pigno_val = "veicolo";
}
else if(tipo_pigno == "lavoro")
{
	pigno_val = "lavoro";
}



</script>


<!-- ********** GESTIONE LINK MENU ********** -->
<script>

//F2
function cambia_F2()
{
	return true;
}

//F3
function salva_form() 
{
	control = submit_buttons('Update');
	if(control)
		$('#form_cronologici').submit();
}

//F4
function cancella_form() 
{     
	return true;
}

//F5
function annulla()
{
	return true;
}

//F6
function nuovo_F6()
{
	return true;
}

//F7-F8
function cambia_pag(value)
{
	return true;
}

//PAG GIU
function pag_prec()
{
	return true;
}

//PAG SU
function pag_suc()
{
	return true;
}

//F9
function ricerca_F9()
{
	return true;
}

//F10
function stampa_F10()
{
	return true;
}

//F11-F12 sono nel menu'


//******************************\\
//ALTRI LINK / FUNZIONI CHIAMATE\\

function control_crono(value)
{
	
	ultimo_crono = parseInt('<?php echo $crono; ?>');
	ultimo_proto = parseInt('<?php echo $ultimo_proto; ?>');
	
	for(var j=0;j<<?php echo count($array_cronologici); ?>;j++)
	{
		
		
		if($('#escludi_'+j).prop('checked')==true)
		{
			$('#proto_'+j).val('').prop('readonly', true).addClass('sfondo_grigio');
			$('#crono_'+j).val('').prop('readonly', true).addClass('sfondo_grigio');
			continue;
		}

		tipo_proto = $('#prototipo_'+j).val();
		if(tipo_proto == "progressivo")
		{
			$('#proto_'+j).prop('readonly', false).val(ultimo_proto).removeClass('sfondo_grigio');
			ultimo_proto++;
		}
		else if(tipo_proto == "fisso")
			$('#proto_'+j).prop('readonly', false).val('<?php echo $fisso_prot; ?>').removeClass('sfondo_grigio');
		else
			$('#proto_'+j).prop('readonly', false).removeClass('sfondo_grigio');
		
		$('#crono_'+j).prop('readonly', false).val(ultimo_crono).removeClass('sfondo_grigio');
		ultimo_crono++;			
				
	}
	
}

$(document).ready(function(){
	
	$('#form_cronologici').ajaxForm(
						
	    function(value) {
	    	window.name = "cronologici";
	        var array_ritorno = value.split(' ');
		if(array_ritorno[0]=='OK')
		{		
			alert('Salvataggio effettuato correttamente!');
			
			window.close(link, "cronologici");
		}
		else if(array_ritorno[0]=='ERROR')
		{		
			alert("Errore: "+array_ritorno[1]);
			window.close(link, "cronologici");
		}
		else if(array_ritorno[0]=='NO')
		{		
			alert("Nessun cronologico assegnato!");
			window.close(link, "cronologici");
		}
		else
		{
			alert("Errore nella procedura");
			window.close(link, "cronologici");
		}
		
	});

$("#submit_click").click( salva_form );
	
	});

</script>

</head>
<body class="sfondo_new_gitco" >

<table class="table_azzurra text_center" style="height:7%;">
	<tr>
		<td class="width1"><br></td>
		<td class="text_left"><font class="comune" ><?php echo $nome_comune ?></font></td>
		<td class="text_right"><font class="user" ><?php echo $nome_user ?></font></td>
		<td class="width1"><br></td>
	</tr>
</table>

<table height=93% class="table_azzurra text_center" border=0>
<tr>
<td valign=top>

<?php include MENU . '/menu_generale.php'; ?>

<script>
var blocca_menu = 1;
</script>

<table class="table_interna text_center" border=0 cellspacing=4>
	<tr>
		<td class="text_center width7">
			<a onMouseover="title='Modifica'" href="#" onClick="">
			<img src="/gitco2/immagini/redF2grey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td class="text_center width7" >
			<input id="submit_click" type="image" title="Salva" src="/gitco2/immagini/Save-iconF3.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td class="text_center width7" >
			<input id="delete_click" type="image" title="Elimina" src="/gitco2/immagini/delete-iconF4grey.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td class="text_center width7" >
			<a onMouseover="title='Annulla'" href="#" onClick="annulla();" style="text-decoration: none;">
			<img src="/gitco2/immagini/undogrey.png" width=47 height=47 border=0>
			</a>
		</td>
		<td class="text_center width7" >	
			<a onMouseover="title='Nuovo Record'" href="#" onClick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/nuovogrey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td class="text_center width7" >
			<a onMouseover="title='Pagina precedente'" href="#" onclick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciagiugrey.png" width=47 height=47 border=0>
			</a>
		</td>
		<td class="text_center width7" >
			<a onMouseover="title='Pagina successiva'" href="#" onclick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciasugrey.png" width=47 height=47 border=0>
			</a>
		</td>
		<td class="text_center width7">
          	<a href="#" onMouseover=" title='Record precedente F7' " onclick=""><img src="/gitco2/immagini/FrecciaSgrey.png" width=42px height=42px border="0" alt="Utente precedente"></a>
		</td>
		<td class="text_center width7">
          	<a href="#" onMouseover=" title='Record successivo F8' " onclick=""><img src="/gitco2/immagini/FrecciaDgrey.png" width=42px height=42px border="0" alt="Utente successivo"></a>
        </td>
		<td class="text_center width11">
          	
        </td>
		<td class="text_center width7">
          	<a href="#" id="stampa_click" onMouseover=" title='Stampa F10' " onclick=""><img src="/gitco2/immagini/PrintF10grey.png" width=50px height=50px border="0" alt="Stampa Avviso"></a>
        </td>
		<td class="text_center width3">
          	
        </td>
		<td class="text_center width7" >
			<a onMouseover="title='Help'" href="#" onClick="window.open('/gitco2/help/intestazione.html','help','width=650,height=400,top=70,left=70,scrollbars=yes, menubar=yes');" style="text-decoration: none;">
			<img src="/gitco2/immagini/help.png" width=50 height=50 border=0>
			</a>
		</td>
		<td class="text_center width2"></td>
		<td class="text_center width7">
			<a onMouseover="title='Home'" href="#" onClick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/homegrey.png" width=60 height=50 border=0>
			</a>
		</td>
	</tr>
</table>

<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><font class="titolo font16 under_decor"><?php echo $titolo_pag; ?></font></td>
	</tr>
</table>
		
<form id=form_cronologici name=form_cronologici action="cronologici_pignoramento_salva.php" method=post>
<input name=invia_submit  id=invia_submit	type=hidden	value="" >

<input type=hidden name=c value="<?php echo $c; ?>" >
<input type=hidden name=a value="<?php echo $a; ?>" >
<input type=hidden name=p value="<?php echo $p; ?>" >
		
<?php if(count($pignoramento)!=0)
{?>

<table class="text_center table_interna" cellspacing=0 border=0 style="border:1px solid black;">

<tr class="text_left riga_dispari" style="height:30px;" >
	
	<td class="width1"><br></td>
	<td class="text_left width30"><b>Pignoramento</b></td>
	<td class="width1"><br></td>
	<td class="text_center width10"><b>Totale (&euro;)</b></td>
	<td class="width1"><br></td>
	<td class="text_left width20"><b>Utente</b></td>
	<td class="width1"><br></td>
	<td class="width15 text_center"><b>Protocollo</b></td>
	<td class="width1"><br></td>
	<td class="width20 text_center"><b>Cronologico/Anno</b></td>
</tr>

<?php
$forma = new forma_giuridica();
$array_forma = $forma->array_completo();

for($i=0; $i<count($pignoramento); $i++)
{		
	$partita = new partita($pignoramento[$i]->Partita_ID, $c);
	$atto = new atto($pignoramento[$i]->Atto_ID, $c);
	$desc_atto = "PARTITA N.".$partita->Comune_ID."/".$partita->Anno_Riferimento;
	$desc_atto.= " - Riferito ad: ".$atto->Atto." n.".$atto->ID_Cronologico." del ".$atto->Anno_Cronologico;
	
	
	$utente = $partita->Utente;
	$forma_descr = "";	
	
	if($utente->Forma_Giuridica!='')
	{
		$index_value = $utente->Forma_Giuridica;
		$forma_descr = $array_forma[$index_value]['Sigla'];
	}
		
	$nome_utente = $utente->Cognome.$utente->Ditta." ".$utente->Nome.$forma_descr;
	
	$proto = "";
	if($tipo_prot == "progressivo")
		$proto = $ultimo_proto + $i;
	else if($tipo_prot == "fisso")
		$proto = $fisso_prot;
	
	$y = $i;
	
	if ($y++ % 2)
		{$stile_riga = 'class="riga_dispari text_left pheight30"'	;	}
	else
		{$stile_riga = 'class="riga_pari text_left pheight30"'	;	}
		
		flush(); ob_flush();
		
?>	
		
		<tr <?php echo $stile_riga; ?>>
			
			<td class="width1"><input type=hidden id="id_<?php echo $i; ?>" name="id[]" value="<?php echo $pignoramento[$i]->ID; ?>" ></td>
			<td class="text_left width30"><?php echo $desc_pignoramento; ?></td>
			<td class="width1"><br></td>
			<td class="text_center width10"><?php echo conv_num(number_format($pignoramento[$i]->Totale_Dovuto,2)); ?></td>
			<td class="width1"><br></td>
			<td class="text_left width20"><?php echo $nome_utente; ?></td>
			<td class="width1"><br></td>
			<td class="text_center width15" >
				<input type=hidden id="prototipo_<?php echo $i; ?>" name="prototipo[]" value="<?php echo $tipo_prot; ?>" >
				<input type="text" id="proto_<?php echo $i; ?>" name="proto[]"  value="<?php echo $proto; ?>" size=7>
			</td>
			<td class="width1"><br></td>
			<td class="text_center width20" >
			<input type="text" class="text_right" id="crono_<?php echo $i; ?>" name="crono[<?php echo $i; ?>]"  value=<?php echo $crono; ?> size=7>
			/
			<input type="text" class="text_right sfondo_readonly" id="anno_<?php echo $i; ?>" name="anno[<?php echo $i; ?>]" value=<?php echo date('Y'); ?> size=4 readonly>
			</td>
			
		</tr>
		<tr <?php echo $stile_riga; ?>>
			
			<td class="width1"><br></td>
			<td class="text_left" colspan=7><font class="font14 titolo"><?php echo $desc_atto; ?></font></td>
			<td class="width1"><br></td>
			<td class="text_center width20">
			<input type="checkbox" id="escludi_<?php echo $i; ?>" name="escludi[]" value=si onclick="control_crono('<?php echo $i; ?>');" > <font id="escludi_titolo_<?php echo $i; ?>" class="font14 titolo">ESCLUDI</font>
			</td>
		</tr>
<?php 	
	//CONTROLLI
	$anomalia = "";
	$control_anomalia = 0;
	
	$atto = new atto($pignoramento[$i]->Atto_ID, $c);
	if(from_mysql_date($atto->Data_Notifica)=="")
	{
		$anomalia.= $atto->Atto." n.".$atto->ID_Cronologico." del ".$atto->Anno_Cronologico." in attesa di verifica!<br>";
		$control_anomalia = 1;
	}	

	if($pignoramento[$i]->Totale_Spese_Notifica>50)
	{
		$anomalia.= "Le spese di notifica sono maggiori di 50 Euro!<br>";
	}
	
	if($tipo_pigno=="lavoro")
	{
		$terzi = $pignoramento[$i]->Presso_Terzi;
		
		for($k=0;$k<count($terzi);$k++)
		{
			$notifica_terzo = $terzi[$k]->Notifica;
			$dati_terzo = $terzi[$k]->Dati_Terzo;
			
			if($dati_terzo->Genere=="D")
				$denom_terzo = $dati_terzo->Ditta;
			else 
				$denom_terzo = $dati_terzo->Cognome." ".$dati_terzo->Nome;
			
			if($dati_terzo->ID==null)
			{
				$anomalia.= "TERZI da pignorare ASSENTI!<br>";
				$control_anomalia = 1;
			}
			else if($notifica_terzo->ID!=null)
			{
				if($dati_terzo->PEC=="")
				{
					if( $notifica_terzo->Modalita_Stampa=="pec")
					{				
						$anomalia.= "PEC del terzo pignorato ".$denom_terzo." ASSENTE!<br>";
						$control_anomalia = 1;
					}
					else
						$anomalia.= "PEC del terzo pignorato ".$denom_terzo." ASSENTE ( Modalita' di Invio diversa da PEC )<br>";
				}
			}
			
		}
	}
	
	if($tipo_pigno=="banca")
	{
		$terzi = $pignoramento[$i]->Presso_Terzi;
		
		for($k=0;$k<count($terzi);$k++)
		{
			$notifica_terzo = $terzi[$k]->Notifica;
			$dati_terzo = $terzi[$k]->Dati_Terzo;
			$denom_terzo = $dati_terzo->Denominazione." ".$dati_terzo->Sigla_Forma_Giuridica;
					
			if($dati_terzo->ID==null)
			{
				$anomalia.= "TERZI da pignorare ASSENTI!<br>";
				$control_anomalia = 1;
			}
			else if($notifica_terzo->ID!=null)
			{
				if($dati_terzo->PEC=="")
				{
					if( $notifica_terzo->Modalita_Stampa=="pec")
					{
						$anomalia.= "PEC del terzo pignorato ".$denom_terzo." ASSENTE!<br>";
						$control_anomalia = 1;
					}
					else
						$anomalia.= "PEC del terzo pignorato ".$denom_terzo." ASSENTE ( Modalita' di Invio diversa da PEC )<br>";
				}
			}
			else if($dati_terzo->Password==null || $dati_terzo->Password=="")
			{
				$anomalia.= "Password del terzo pignorato ".$denom_terzo." ASSENTE!<br>";
				$control_anomalia = 1;
			}
		
		}
	}
	
	if($tipo_pigno=="veicolo")
	{
		$tribunale = new ufficio_giudiziario($utente->Residenza->CC_Indirizzo, "tribunale");
		$ufficio_vendite = new ufficio_giudiziario($tribunale->CC_Ufficio, "istituto");
		
		if($ufficio_vendite->Denominazione=="")
		{
			$anomalia.= "Parametri Tribunale / Istituto vendite giudiziarie ASSENTI!<br>";
			$control_anomalia = 1;
		}
		else if($ufficio_vendite->PEC=="")
		{
			if($pignoramento[$i]->Notifica_Istituto[0]->Modalita_Stampa=="pec")
			{
				if($ufficio_vendite->Mail=="")
				{
					$anomalia.= "PEC e mail Istituto vendite giudiziarie ASSENTI!<br>";
					$control_anomalia = 1;
				}
				else 
					$anomalia.= "PEC Istituto vendite giudiziarie ASSENTE, Mail Istituto vendite giudiziarie presente.<br>";
			}
			else 
				$anomalia.= "PEC Istituto vendite giudiziarie ASSENTE ( Modalita' di Invio diversa da PEC )<br>";
		}
		
		if(!isset($pignoramento[$i]->Veicolo[0]))
		{
			$anomalia.= "Nessun VEICOLO presente nel pignoramento!<br>";
			$control_anomalia = 1;
		}
		else 
		{
			for($y=0;$y<count($pignoramento[$i]->Veicolo);$y++)
			{
				if($pignoramento[$i]->Veicolo[$y]->Valore_Veicolo==0)
				{
				
					$anomalia.= strtoupper($pignoramento[$i]->Veicolo[$y]->Tipo_Veicolo)." ".$pignoramento[$i]->Veicolo[$y]->Marca_Veicolo." ";
					$anomalia.= $pignoramento[$i]->Veicolo[$y]->Modello_Veicolo." ";
					$anomalia.= "sprovvisto dell'indicazione del valore!<br>";
					$control_anomalia = 1;
				}
				
				if(from_mysql_date($pignoramento[$i]->Veicolo[$y]->Data_Visura)==null)
				{
					$anomalia.= strtoupper($pignoramento[$i]->Veicolo[$y]->Tipo_Veicolo)." ".$pignoramento[$i]->Veicolo[$y]->Marca_Veicolo." ";
					$anomalia.= $pignoramento[$i]->Veicolo[$y]->Modello_Veicolo." ";
					$anomalia.= "sprovvisto della Data della Visura!<br>";
					$control_anomalia = 1;
				}
			}
		}
		
		
						
	}	
	
	if($anomalia!="")
	{?>
		
		<tr id="tr_anomalia_<?php echo $i; ?>" class="sfondo_rosso text_left pheight30">
			<td class="width1"><br></td>
			<td class="text_left" colspan=9><font class="font14"><b><?php echo $anomalia; ?></b></font></td>
		</tr>
				
<?php 
		$layout.="<script>$('#escludi_".$i."').prop('checked',true);control_crono('".$i."');</script>";
		
		if($control_anomalia==1)
			$layout.="<script>$('#escludi_".$i."').hide();$('#escludi_titolo_".$i."').hide();</script>";
	}
	
$crono++;}?>

	</table>

<?php }?>

</form>

<?php echo $layout; ?>

<br>
</td>
</tr>
</table>

</body>
</html>