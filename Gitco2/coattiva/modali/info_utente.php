<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

include CLASSI . "/anagrafe.php";
include CLASSI . "/ruolo.php";

$p = get_var('p');
$c = get_var('c');
$a = get_var('a');

$info_ID = get_var('info_id');

$utente = new utente($info_ID,$c);
	
	$id_utente 				= 	$utente->ID;
	$genere_utente 			= 	$utente->Genere;
	
	if($genere_utente!="D")
	{
		$layout = "<script>$('#tab_soggetto').show();$('#tab_ditta').hide();</script>";
	}
	else 
	{
		$layout = "<script>$('#tab_soggetto').hide();$('#tab_ditta').show();</script>";
	}
	
	$comune_id 				=	$utente->Comune_ID;
	

		$cognome_utente 	=	$utente->Cognome;
		$nome_utente 		=	$utente->Nome;
		$CC_nascita			=	$utente->CC_Nascita;
		$paese_nasc_utente  =	$utente->Paese_Nascita;
		if($paese_nasc_utente==null)
		{
			$paese_nasc_utente = "Italia";
		}
		$comune_nasc_utente =	$utente->Comune_Nascita;
	 	$provincia_nasc_utente	=	$utente->Provincia_Nascita;
		$data_nasc_utente	=	from_mysql_date($utente->Data_Nascita);
		$data_morte_utente	=	from_mysql_date($utente->Data_Morte);
		$CF					=	$utente->Codice_Fiscale;

		$ditta				=	$utente->Ditta;
		$PI					=	$utente->Partita_Iva;
		$prec_den_ditta		=	$utente->Prec_Denom;
		$anno_cambio_ditta	=	$utente->Anno_Cambio_Denom;


	$note_utente			=	$utente->Note;
	$cell_utente			=	$utente->Cellulare;
	$mail_utente			=	$utente->Mail;
	$pec_utente				=	$utente->PEC;
	$data_registrazione		=	from_mysql_date($utente->Data_Registrazione);
	if($data_registrazione==null)
	{
		$data_registrazione = date('d/m/Y');
	}
	
	$indirizzo_res			=	$utente->Residenza;
	if($indirizzo_res!=null)
	{
		$ID_res		 		= 	$indirizzo_res->ID;
		if($p==0)
		{
			$ID_via				=	1;
			$ID_via_cap			=	1;
		}
		else 
		{
			$ID_via				=	$indirizzo_res->Via_ID;
			$ID_via_cap			=	$indirizzo_res->Via_Cap_ID;
		}
		
		$CC_res				=	$indirizzo_res->CC_Indirizzo;
		$paese_res			=	$indirizzo_res->Paese;
		
		if($paese_res==null)
		{
			$paese_res = "Italia";
		}
		
		$comune_res			=	$indirizzo_res->Comune;
		$provincia_res		=	$indirizzo_res->Provincia;
		$frazione_res		=   $indirizzo_res->Frazione;
		$toponimo_res		=	$indirizzo_res->Toponimo->Nome;
		$civico_res			=	$indirizzo_res->Civico;
		$esponente_res		=	$indirizzo_res->Esponente;
		$CAP_res			=	$indirizzo_res->Cap;
		$interno_res		=	$indirizzo_res->Interno;
		$dettagli_res		=	$indirizzo_res->Dettagli;
		$telefono_res		=	$indirizzo_res->Telefono;
		$fax_res			=	$indirizzo_res->Fax;	
	$data_inizio_res_utente = 	from_mysql_date($indirizzo_res->Data_Inizio_Residenza);
	}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Ufficio giudiziario</title>
	
	<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
	<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
	<style> .ui-datepicker { font-size:10px; } </style>
	
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>
  	
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>
  	  
<body class="sfondo_new_gitco" >
  
<table height=93% class="table_modale text_center pwidth650" border=0>
	<tr>
		<td valign=top>  
  
  <br>

<div id=tab_soggetto>
<table align=center class="table_modale text_center pwidth600" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td width="42%"><hr></td>
		<td align="center"><p class="sezioni_tab">DATI UTENTE</p></td>
		<td width="42%"><hr></td>
	</tr>
</table>

<table align=center class="table_modale text_center pwidth600" border="0" cellspacing="3" cellpadding="0">
	<tr>
		<td class="text_left width20">Cognome</td>
    	<td class="text_left width30"><b><?php echo $cognome_utente; ?></b></td>
		<td class="text_left width20">Nome</td>
		<td class="text_left width30"><b><?php echo $nome_utente; ?></b></td>	
	</tr>
	<tr>
		<td class=text_left >Stato nascita</td>
		<td class=text_left ><b><?php echo $paese_nasc_utente; ?></b></td>
		<td class=text_left >Comune nascita</td>
		<td class=text_left ><b><?php echo $comune_nasc_utente; ?></b>
		&nbsp;&nbsp;Prov. <b><?php echo $provincia_nasc_utente; ?></b></td>
	</tr>
	<tr>
		<td class=text_left >Data nascita</td>
		<td class=text_left><b><?php echo $data_nasc_utente; ?></b></td>
		<td class=text_left >Data morte </td>
		<td class=text_left><b><?php echo $data_morte_utente; ?></b></td>

	</tr>
	<tr>
		<td class=text_left >Codice Fiscale</td>
	    <td class=text_left><b><?php echo $CF; ?></b></td>    	    
		<td colspan=2 align=right></td>
	</tr>
</table>
</div>

<div id=tab_ditta>
<table align=center class="table_modale text_center pwidth600" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td width=42%><hr></td>
		<td align="center"><p class="sezioni_tab">DATI DITTA</p></td>
		<td width=42%><hr></td>
	</tr>
</table>
<table align=center class="table_modale text_center pwidth600" border="0" cellspacing="3" cellpadding="0">
	<tr>
		<td class="text_left width20">Ditta</td>
    	<td class="text_left width30"><b><?php echo $ditta; ?></b></td>
		<td class="text_left width20">Partita Iva</td>
	    <td class="text_left width30"><b><?php echo $PI; ?></b></td>   
	</tr>
	<tr>
		<td class=text_left>Prec. denomin.</td>
    	<td class=text_left><b><?php echo $prec_den_ditta; ?></b></td>
    	<td class=text_left>Anno cambio</td>
    	<td class=text_left><b><?php echo $anno_cambio_ditta; ?></b></td>   	
   	</tr>
</table>
</div>

<table align=center class="table_modale text_center pwidth600" border="0" cellspacing="3" cellpadding="0">
	<tr>
		<td width=42%><hr></td>
		<td align="center"><p class="sezioni_tab">RESIDENZA</p></td>
		<td width=42%><hr></td>
	</tr>
</table>

<table align=center class="table_modale text_center pwidth600" border="0" cellspacing="3" cellpadding="0">
	<tr>
		<tr>
		<td class="text_left width20" >Stato</td>
		<td class="text_left width30" ><b><?php echo $paese_res; ?></b></td>
		<td class="text_left width20" >Comune</td>
		<td class="text_left width30" ><b><?php echo $comune_res; ?></b>
		&nbsp;&nbsp;Prov. <b><?php echo $provincia_res; ?></b></td>
	</tr>
	<tr>
        <td class=text_left>Fraz./Circoscriz.</td>
        <td class=text_left><b><?php echo $frazione_res; ?></b></td>
		<td class=text_left>CAP</td>
		<td class=text_left><b><?php echo $CAP_res; ?></b></td>
	</tr>
	<tr>
		<td class=text_left>Indirizzo</td>
		<td class=text_left><b><?php echo $toponimo_res; ?></b></td>
		<td class=text_left colspan=2>Civ.&nbsp; 
		<b><?php echo $civico_res; ?></b>&nbsp;&nbsp;&nbsp;
		 Esp.&nbsp; 
		<b><?php echo $esponente_res; ?></b>&nbsp;&nbsp;
		 Int.&nbsp;
		<b><?php echo $interno_res; ?></b>&nbsp;&nbsp;&nbsp;
		Dettagli&nbsp;
		<b><?php echo $dettagli_res; ?></b>
	</tr>
	<tr>
		<td class=text_left>Telefono</td>
	    <td class=text_left><b><?php echo $telefono_res; ?></b></td>
	    <td class=text_left>Fax</td>
	    <td class=text_left><b><?php echo $fax_res; ?></b></td>
	</tr>
	<tr>
		<td class=text_left>Cellulare</td>
		<td class=text_left><b><?php echo $cell_utente; ?></b></td>
		<td class=text_left>Email</td>
		<td class=text_left><b><?php echo $mail_utente; ?></b></td>
	</tr>
	<tr>
		<td class=text_left>Data Inizio Res.</td>
		<td class=text_left><b><?php echo $data_inizio_res_utente; ?></b></td>
		<td class=text_left>PEC</td>
		<td class=text_left><b><?php echo $pec_utente; ?></b></td>
	</tr>
</table>
		</td>
	</tr>
</table>

<?php echo $layout; ?>

</body>
</html>