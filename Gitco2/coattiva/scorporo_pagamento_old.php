<?php

	require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
	include LIBRERIE . "/funzioni.php";
	
	include CLASSI . "/anagrafe.php";
	include CLASSI . "/comuni.php";
	include CLASSI . "/classe_anni.php";
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
	
	$partita_ID = get_var('partita');
	
	$comune = new ente_gestito($c);
	$nome_comune = $comune->Nome;
	
	$nome_comune =($nome_comune==NULL?"":$nome_comune." [".$c."]");
	$nome_user = "Operatore: ".$_SESSION['username'];
	
	$layout = "<script>";
	
	$anni_gestiti = new anni_gestiti($c, null);
	
	if($c==null)
		$options_anni = null;
	else
	{
		$options_anni = $anni_gestiti->Options_Anni_Veloci($c, "COATTIVA", "pagamento");
	
		if($a!=null)
			$layout.="$('#select_anno_veloce option[value=".$a."]').attr('selected',true);";
	}
	
	$layout.= "</script>";
		
	$layout.= "<script>$('[tabindex=1]').focus();</script>";
	
	$partita = new partita($partita_ID, $c, $a);
	$prev = $partita->prev;
	$next = $partita->next;
	$ID_Partita = $partita->Comune_ID;
	$anno_riferimento = $partita->Anno_Riferimento;
	
	$readonly = "class='width70 corrige_numero'";
	if($partita->Tipo!="RIFIUTI" && $partita->Sottotipo!="TSRSU")
		$readonly = "class='width70 readonly' readonly";
	
	$utente = new utente($partita->Utente_ID,$c);
	
	$genere_utente 			= 	$utente->Genere;
	$cognome_utente 		=	$utente->Cognome;
	$nome_utente 			=	$utente->Nome;
	$ditta					=	$utente->Ditta;
	
	$query = "SELECT * FROM pagamento WHERE Partita_ID = '".$partita_ID."' AND CC='".$c."' AND Tipo_Atto = 'Precedenti'";
	$pag_prec = mysql_array($query);
	
	if(!isset($pag_prec[0])){
		$pag_prec[0]['ID'] = "";
		$pag_prec[0]['Importo'] = 0;
		$pag_prec[0]['Scorporo_Tributo'] = 0;
		$pag_prec[0]['Scorporo_Spese_Ricerca'] = 0;
		$pag_prec[0]['Scorporo_Spese_Notifica'] = 0;
		$pag_prec[0]['Scorporo_Interessi'] = 0;
		$pag_prec[0]['Scorporo_Eca'] = 0;
		$pag_prec[0]['Scorporo_Tributo_Provinciale'] = 0;
		$pag_prec[0]['Scorporo_Spese_Precedenti'] = 0;
		$pag_prec[0]['Scorporo_Spese_Accessorie'] = 0;
		$pag_prec[0]['Scorporo_Notifica_Pignoramento'] = 0;
		$pag_prec[0]['Scorporo_Diritto_Riscossione'] = 0;
	}
	
	$count = 0;

	$Totali_Importo = $pag_prec[0]['Importo'];
	$Totali_Scorporo_Tributo = $pag_prec[0]['Scorporo_Tributo'];
	$Totali_Scorporo_Spese_Ricerca = $pag_prec[0]['Scorporo_Spese_Ricerca'];
	$Totali_Scorporo_Spese_Notifica = $pag_prec[0]['Scorporo_Spese_Notifica'];
	$Totali_Scorporo_Interessi = $pag_prec[0]['Scorporo_Interessi'];
	$Totali_Scorporo_Eca = $pag_prec[0]['Scorporo_Eca'];
	$Totali_Scorporo_Tributo_Provinciale = $pag_prec[0]['Scorporo_Tributo_Provinciale'];
	$Totali_Scorporo_Spese_Precedenti = $pag_prec[0]['Scorporo_Spese_Precedenti'];
	$Totali_Scorporo_Spese_Accessorie = $pag_prec[0]['Scorporo_Spese_Accessorie'];
	$Totali_Scorporo_Notifica_Pignoramento = $pag_prec[0]['Scorporo_Notifica_Pignoramento'];
	$Totali_Scorporo_Diritto_Riscossione = $pag_prec[0]['Scorporo_Diritto_Riscossione'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Pagamenti - Gestione</title>
	
	<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
	<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
	<style> .ui-datepicker { font-size:11px; } </style>
	
	<link REL=StyleSheet HREF="/gitco2/css/image_magnifier.css" TYPE="text/css" MEDIA=screen>
	
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>	
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>
  	
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/image_magnifier.js"></script>

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
	verifica = control_scorpori();
	if(verifica===false){
		return false;	
	}
	
	control = submit_buttons($('#invia_submit').val());
	if(control)
    	$("#form_scorpori").submit();
}

//F4
function cancella_form() 
{     
	control = submit_buttons('Delete');
	if(control)
		$("#form_scorpori").submit();
}

//F5
function annulla()
{
	location.href="scorporo_pagamento.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

//F6
function nuovo_F6()
{
	if( modifica == 0 )
	{
		location.href="scorporo_pagamento.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>&nuovo_pag=true";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//F7-F8
function cambia_pag(value)
{
	if( modifica == 0 )
	{
		if(value=="prev" || value=="suc")
		{
			if(value=="suc")
				value = "<?php echo $next; ?>";
			else
				value = "<?php echo $prev; ?>";
				
			location.href="scorporo_pagamento.php?partita="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
		
		}
	}
	else
		alert("salvare i dati o annullare prima di procedere");
	
}

//PAG GIU
function pag_prec()
{
	if( modifica == 0 )
	{
		location.href="pagamento.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
	location.href="ingiunzione.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

//PAG SU
function pag_suc()
{
	if( modifica == 0 )
	{
		location.href="ricorso.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//F9
function ricerca_F9()
{
	if( modifica == 0 )
	{
		RicercheDaId('utente',0);
	}
	else
		alert("salvare i dati o annullare prima di procedere");

}

//F10
function stampa_F10()
{
	return true;
}

//F11-F12 sono nel menu'


//******************************\\
//ALTRI LINK / FUNZIONI CHIAMATE\\
function ruolo (value)
{
	location.href="gestione_ruolo.php?p="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

function crea_partita()
{
	top.location.href = "nuova_partita.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

</script>


<!-- ********** CALENDARIO ********** -->
<script>

$(document).ready(function(){

	$('#data_pag').datepicker();

	});

</script>

<!-- ********** AGGIORNAMENTO PAGINA E CALCOLO ********** -->
<script>

function control_scorpori(){
	for(var i = 0; i<num_pagamenti;i++){
		verifica = control_somma_scorpori(i);
		if(verifica===false){
			return false;
		}
	}
}

function pulisci_scorpori(value){
	$('#tributo_'+value).val('0,00');
	$('#spese_ricerca_'+value).val('0,00');
	$('#spese_precedenti_'+value).val('0,00');
	$('#spese_notifica_'+value).val('0,00');
	$('#eca_'+value).val('0,00');
	$('#tributo_provinciale_'+value).val('0,00');
	$('#spese_accessorie_'+value).val('0,00');
	$('#spese_not_pigno_'+value).val('0,00');
	$('#interessi_'+value).val('0,00');
	$('#diritto_riscossione_'+value).val('0,00');
}

function control_somma_scorpori(value){
	somma = parseNumber($('#tributo_'+value).val())+parseNumber($('#spese_ricerca_'+value).val())+parseNumber($('#spese_precedenti_'+value).val())+parseNumber($('#spese_notifica_'+value).val());
	somma+= parseNumber($('#eca_'+value).val())+parseNumber($('#tributo_provinciale_'+value).val())+parseNumber($('#spese_accessorie_'+value).val())+parseNumber($('#spese_not_pigno_'+value).val());
	somma+= parseNumber($('#interessi_'+value).val())+parseNumber($('#diritto_riscossione_'+value).val());

	somma = number_format(somma,2);
	pagamento = parseNumber($('#importo_pagato_'+value).val());
	
	if(pagamento!=somma && somma>0){
		alert("ATTENZIONE! Il pagamento di "+pagamento+" Euro non coincide con la somma degli scorpori equivalente a "+somma+" Euro!");
		return false;
	}
	else
		return true;
}

function parseNumber(string){
	return parseFloat(string.replace(',','.').replace(' ',''));
}

function focus_index()
{
	$('[tabindex=1]').focus();
}

</script>


<!-- ********** MODALI ********** -->
<script>

function Dim_Alert ( sWidth, sHeight )
	{
		setupPagina = "dialogWidth:" + sWidth + "px";
		setupPagina += "; dialogHeight:" + sHeight + "px";
		setupPagina += ";dialogLeft:80px;dialogTop:80px;";

		return setupPagina;
	}

function callParent(valorediritorno) {
    if(valorediritorno!=null){
        switch(selectParent){
            case "utente":

                if(typeof valorediritorno !== 'string')
                    reopen('obj',valorediritorno);
                else
                    reopen('str',valorediritorno);

                break;
        }
    }
}

function reopen(type, value){
    if(type == 'obj')
        top.location.href="../scorporo_pagamento.php?mode=consulta&partita="+value.ID+"&c=<?php echo $c; ?>&a="+value.Anno;
    else if(type == 'str')
        top.location.href="../gestione_ruolo.php?mode=consulta&p="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

var selectParent = "";
var selectRif = "";

function RicercheDaId (value, rif)
{
    selectParent = value;
    selectRif = rif;
    var valorediritorno = 0;
    var strDim = Dim_Alert(600, 300);

    switch(value)
    {
        case "utente":

            strDim = Dim_Alert(800, 400);
            var stringa = "modali/ricerca_alert_modale.php?richiesta=generale&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
            valorediritorno = window.showModalDialog(stringa,"", strDim);

            break;
    }
}

</script>


<!-- ********** AJAX FORM / SUBMIT ********** -->
<script>

var invio_form = "Update";

$(document).ready(function(){
	
$('#cerca_id').ajaxForm(
			
	        function(value) {
	            var array_ritorno = value.split(' ');
		if(array_ritorno[0]=='NO')
		{
			alert('Codice partita non trovato!');
            annulla();
		}
		else
		{
			top.location.href = "gestione_partita.php?partita="+array_ritorno[0]+"&c=<?php echo $c; ?>&a="+array_ritorno[1];
		}
	});

$('#form_scorpori').ajaxForm(
		
    function(value) {
        var array_ritorno = value.split(' ');
        
	if(array_ritorno[0]=='OK')
	{	
		alert('Salvataggio effettuato correttamente!');
		top.location.href = "scorporo_pagamento.php?partita="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else if(array_ritorno[0]=='ERROR')
	{
		alert("Errore nel salvataggio degli scorpori dei pagamenti.");
		top.location.href = "scorporo_pagamento.php?partita="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else if(array_ritorno[0]=='DELETE')
	{
		alert("Pagamento eliminato correttamente.");
		top.location.href = "scorporo_pagamento.php?partita="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert(value);
	
});

$("#submit_click").click( salva_form );

$("#delete_click").click( cancella_form );


});

</script>
    <script>
        alert("Attenzione!! Non e' possibile effettuare importazioni o elaborazioni! Il sistema e' temporaneamente sospeso per manutenzione");
        window.history.back();
    </script>
</head>

<body class="sfondo_new_gitco" >  

<table class="table_azzurra text_center" style="height:7%;">
	<tr>
		<td width=1%><br></td>
		<td class="text_left">
			<font class="comune" ><?php echo $nome_comune; ?> <?php echo $options_anni; ?></font>
		</td>
		<td class="text_right"><font class="user" ><?php echo $nome_user ?></font></td>
		<td width=1%><br></td>
	</tr>
</table>

<table height=93% class="table_azzurra text_center" border=0>
<tr>
<td valign=top>

<?php include MENU . '/menu_generale.php'; ?> 
                
<table class="table_interna text_center" border=0 cellspacing=4>
	<tr>
		<td align=center width=7%>
			<a onMouseover="title='Modifica'" href="#" onClick="">
			<img src="/gitco2/immagini/redF2grey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<input id="submit_click" type="image" title="Salva" src="/gitco2/immagini/Save-iconF3.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td align=center width=7% >
			<input id="delete_click" type="image" title="Elimina" src="/gitco2/immagini/delete-iconF4.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Annulla'" href="#" onClick="annulla();" style="text-decoration: none;">
			<img src="/gitco2/immagini/undo.png" width=47 height=47 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Nuovo Record'" href="#" onClick="nuovo_F6();" style="text-decoration: none;">
			<img src="/gitco2/immagini/nuovo.png" width=45 height=45 border=0>
			</a>
		</td>
		
		<td align=center width=7% >
			<a onMouseover="title='Pagina precedente'" href="#" onclick="pag_prec();" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciagiu.png" width=47 height=47 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Pagina successiva'" href="#" onclick="pag_suc();" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciasu.png" width=47 height=47 border=0>
			</a>
		</td>
		<td width=7% align="center">
          	<a href="#" onMouseover="title='Record precedente F7'" onclick="cambia_pag('prev')">
          	<img src="/gitco2/immagini/FrecciaS.png" width=42px height=42px border="0" alt="Utente precedente">
          	</a>
    	</td>
        <td width=7% align="center">
            <a href="#" onMouseover="title='Record successivo F8'" onclick="cambia_pag('suc')">
            <img src="/gitco2/immagini/FrecciaD.png" width=42px height=42px border="0" alt="Utente successivo">
            </a>
        </td>
        <td width=11%></td>
        <td width=7% align="center">
          	<a href="#" onMouseover="title='Stampa'" onclick="">
          	<img src="/gitco2/immagini/printF10grey.png" width=50 height=50 border="0" ></a>
    	</td>
        <td width=3%></td>
    	<td align=center width=7% >
    			<a onMouseover="title='Help'" href="#" onClick="window.open('/gitco2/help/intestazione.html','help','width=650,height=400,top=70,left=70,scrollbars=yes, menubar=yes');" style="text-decoration: none;">
			<img src="/gitco2/immagini/help.png" width=50 height=50 border=0>
			</a>
		</td>
		<td width=2%></td>
		<td width=7%>
			<a onMouseover="title='Home'" href="#" onClick="link('menu');" style="text-decoration: none;">
			<img src="/gitco2/immagini/home.png" width=60 height=50 border=0>
			</a>
		</td>
	</tr>
</table>

<table class="table_interna text_center" border=0 style="border:3px solid #6D95D5;">
	<tr>
		<td width=8% class="text_center">
			<a onMouseover="title='Cerca utente/partita'" href="#" onClick="RicercheDaId('utente',0);" style="text-decoration: none;">
			<img src="/gitco2/immagini/User Folder.png" width=47 height=47 border=0>
			</a>
		</td>
		<td width=15% class="text_center"><font class="titolo font18">PARTITA</font><font class="titolo font14"><br> Pag 3/7</font></td>
    	<td colspan=5 width=55% align=center>
            <em style="background-color:rgb(251,255,208);font-style : normal ;">
            <?php if($genere_utente!='D'){echo $cognome_utente." ".$nome_utente;}else{ echo $ditta; } ?> 
            </em>
        	<td class="text_left"><input type=image src="/gitco2/immagini/select.png" style="width:25px; height:25px; border:0;" title="Gestione Ruolo" onclick="ruolo('<?php echo $partita->Utente_ID; ?>');">
        </td>
		<td width=22% class="text_right">
		<form id=cerca_id method=post action=modali/ricerca_partita.php>
			<input type=hidden name=old_cod_contr value='<?php echo $ID_Partita; ?>'>
           	<input name=c type=hidden value='<?php echo $c; ?>'>
            <input name=a type=hidden value='<?php echo $a; ?>'>
		
			Partita ID &nbsp;
		
			<input id=id_cerca tabindex=1 class="valign_center text_right" type=text name=ric_cod_contr value='<?php echo $ID_Partita; ?>' size=3 onMouseover="title='Inserire il codice utente e premere Invio'">&nbsp;&nbsp;</form>
		</td>
	</tr>
</table>

<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td class="width20"><a href="gestione_partita.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>" ><font class="titolo font16">Codici tributo</font></a></td>
		<td class="width20"><a href="ingiunzione.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>" ><font class="titolo font16">Ingiunzione</font></a></td>
		<td class="width20"><a href="pagamento.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>" ><font class="titolo font16">Pagamenti</font></a></td>
		<td class="width20"><font class="titoletto font16 under_decor">Scorpori</font></td>
		<td class="width20"><a href="ricorso.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>" ><font class="titolo font16">Ricorsi</font></a></td>
		<td class="width20"><a href="coazione.php?partita=<?php echo $partita_ID; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>" style="text-decoration: none;"><font class="titolo font15"><i>Coazione</i></font> <img alt="" src="/gitco2/immagini/forward.png" style="width:12px; height:12px; border:0;"></a></td>
	</tr>
</table>

<form id=form_scorpori name=form_scorpori action="scorporo_pagamento_salva.php" method=post>
<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >
<input type=hidden name=p value=<?php echo $p; ?> >
<input type=hidden name=partita value=<?php echo $partita_ID; ?> >
<input name=invia_submit  id=invia_submit	type=hidden	value="Update">

<table class="text_center table_interna" cellspacing=0 border=0 >
<tr>
	<td class="text_left" colspan=15><hr></td>
</tr>
<tr class="riga_pari text_left" style="height:30px;">
	<td><br></td>
	<td class="text_left titolo" colspan=13><b>PAGAMENTI PRECEDENTI</b></td>
	<td><br></td>	
</tr>
<tr class="riga_dispari text_left">
	<td class="width2"><br></td>
	<td class="text_center width6"></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Pagato</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Tributo</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Sp. Ricerca</b></td>
	<td class="width1"><br></td>	
	<td class="text_center width14"><b>Spese Acc.</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Diritto risc.</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Eca</b></td>
	<td class="width2"><br></td>
</tr>
<tr class="riga_dispari text_left">
	<td class="width2"><br></td>
	<td class="text_center width6"></td>
	<td class="width1"><br></td>
	<td class="text_center width14"></td>
	<td class="width2"><br></td>
	<td class="text_center width14"><b>Sp. Notifica</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Sp. Prec.</b></td>
	<td class="width1"><br></td>	
	<td class="text_center width14"><b>Sp.Not.Pigno.</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Interessi</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Trib. prov.</b></td>
	<td class="width1"><br></td>
</tr>
<tr>
	<td><input type=hidden id="id_pagamento_<?php echo $count; ?>" name="id_pagamento[<?php echo $count; ?>]" value="<?php echo $pag_prec[0]['ID']; ?>"><br></td>
	<td class="text_center"></td>
	<td><br></td>
	<td class="text_center"><input id="importo_pagato_<?php echo $count; ?>" name="importo_pagato[<?php echo $count; ?>]" class="width70 sfondo_red" value="<?php echo number_format($pag_prec[0]['Importo'],2,",","."); ?>"> &euro;</td>
	<td><br></td>
	<td class="text_center"><input id="tributo_<?php echo $count; ?>" name="tributo[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pag_prec[0]['Scorporo_Tributo'],2,",","."); ?>"> &euro;</td>
	<td><br></td>	
	<td class="text_center"><input id="spese_ricerca_<?php echo $count; ?>" name="spese_ricerca[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pag_prec[0]['Scorporo_Spese_Ricerca'],2,",","."); ?>"> &euro;</td>
	<td><br></td>
	<td class="text_center"><input id="spese_accessorie_<?php echo $count; ?>" name="spese_accessorie[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pag_prec[0]['Scorporo_Spese_Accessorie'],2,",","."); ?>"> &euro;</td>
	<td><br></td>
	<td class="text_center"><input id="diritto_riscossione_<?php echo $count; ?>" name="diritto_riscossione[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pag_prec[0]['Scorporo_Diritto_Riscossione'],2,",","."); ?>"> &euro;</td>
	<td><br></td>
	<td class="text_center"><input <?php echo $readonly; ?> id="eca_<?php echo $count; ?>" name="eca[<?php echo $count; ?>]" value="<?php echo number_format($pag_prec[0]['Scorporo_Eca'],2,",","."); ?>"> &euro;</td>
	<td><br></td>
</tr>
<tr>
	<td><br></td>
	<td class="text_center"><br></td>
	<td><br></td>
	<td class="text_center"><br></td>
	<td><br></td>
	<td class="text_center"><input id="spese_notifica_<?php echo $count; ?>" name="spese_notifica[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pag_prec[0]['Scorporo_Spese_Notifica'],2,",","."); ?>"> &euro;</td>
	<td><br></td>
	<td class="text_center"><input id="spese_precedenti_<?php echo $count; ?>" name="spese_precedenti[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pag_prec[0]['Scorporo_Spese_Precedenti'],2,",","."); ?>"> &euro;</td>
	<td><br></td>
	<td class="text_center"><input id="spese_not_pigno_<?php echo $count; ?>" name="spese_not_pigno[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pag_prec[0]['Scorporo_Notifica_Pignoramento'],2,",","."); ?>"> &euro;</td>
	<td><br></td>	
	<td class="text_center"><input id="interessi_<?php echo $count; ?>" name="interessi[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pag_prec[0]['Scorporo_Interessi'],2,",","."); ?>"> &euro;</td>
	<td><br></td>
	<td class="text_center"><input <?php echo $readonly; ?> id="tributo_provinciale_<?php echo $count; ?>" name="tributo_provinciale[<?php echo $count; ?>]" value="<?php echo number_format($pag_prec[0]['Scorporo_Tributo_Provinciale'],2,",","."); ?>"> &euro;</td>
	<td><br></td>		
	<td class="text_center"></td>
	<td><br></td>
</tr>
<tr>
	<td class="text_left" colspan=15><hr></td>
</tr>
<?php
$count++;
for($i=0; $i<count($partita->Atto); $i++)
{		
	if(count($partita->Atto[$i]->Pagamento)==0)
		continue;
?>	

<tr class="riga_pari text_left" style="height:30px;">
	<td><br></td>
	<td class="text_left titolo" colspan=13><b><?php echo strtoupper($partita->Atto[$i]->Atto." n.".$partita->Atto[$i]->ID_Cronologico." del ".$partita->Atto[$i]->Anno_Cronologico); ?></b></td>
	<td><br></td>	
</tr>
<tr class="riga_dispari text_left">
	<td class="width2"><br></td>
	<td class="text_center width6"><b>Rata</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Pagato</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Tributo</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Sp. Ricerca</b></td>
	<td class="width1"><br></td>	
	<td class="text_center width14"><b>Spese Acc.</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Diritto risc.</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Eca</b></td>
	<td class="width2"><br></td>
</tr>
<tr class="riga_dispari text_left">
	<td class="width2"><br></td>
	<td class="text_center width6"></td>
	<td class="width1"><br></td>
	<td class="text_center width14"></td>
	<td class="width2"><br></td>
	<td class="text_center width14"><b>Sp. Notifica</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Sp. Prec.</b></td>
	<td class="width1"><br></td>	
	<td class="text_center width14"><b>Sp.Not.Pigno.</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Interessi</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Trib. prov.</b></td>
	<td class="width1"><br></td>
</tr>

	<?php 

if(count($partita->Atto[$i]->Pagamento)>0){
	for($y=0;$y<count($partita->Atto[$i]->Pagamento);$y++){
		$pagamento = $partita->Atto[$i]->Pagamento[$y];
		
		$Totali_Importo+= $pagamento->Importo;
		$Totali_Scorporo_Tributo+= $pagamento->Scorporo_Tributo;
		$Totali_Scorporo_Spese_Ricerca+= $pagamento->Scorporo_Spese_Ricerca;
		$Totali_Scorporo_Spese_Notifica+= $pagamento->Scorporo_Spese_Notifica;
		$Totali_Scorporo_Interessi+= $pagamento->Scorporo_Interessi;
		$Totali_Scorporo_Eca+= $pagamento->Scorporo_Eca;
		$Totali_Scorporo_Tributo_Provinciale+= $pagamento->Scorporo_Tributo_Provinciale;
		$Totali_Scorporo_Spese_Precedenti+= $pagamento->Scorporo_Spese_Precedenti;
		$Totali_Scorporo_Spese_Accessorie+= $pagamento->Scorporo_Spese_Accessorie;
		$Totali_Scorporo_Notifica_Pignoramento+= $pagamento->Scorporo_Notifica_Pignoramento;
		$Totali_Scorporo_Diritto_Riscossione+= $pagamento->Scorporo_Diritto_Riscossione;
		
		if($pagamento->Totale_Rate>0)
			$tot_rate = "/".$pagamento->Totale_Rate;
		else 
			$tot_rate = "";
			
		?>
		<tr>
			<td><input type=hidden id="id_pagamento_<?php echo $count; ?>" name="id_pagamento[<?php echo $count; ?>]" value="<?php echo $pagamento->ID; ?>"><br></td>
			<td class="text_center"><b><span class="color_titolo"><?php echo $pagamento->Rata.$tot_rate; ?></span></b></td>
			<td><br></td>
			<td class="text_center"><input readonly id="importo_pagato_<?php echo $count; ?>" name="importo_pagato[<?php echo $count; ?>]" class="width70 sfondo_azzurro" value="<?php echo number_format($pagamento->Importo,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input id="tributo_<?php echo $count; ?>" name="tributo[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Tributo,2,",","."); ?>"> &euro;</td>
			<td><br></td>	
			<td class="text_center"><input id="spese_ricerca_<?php echo $count; ?>" name="spese_ricerca[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Spese_Ricerca,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input id="spese_accessorie_<?php echo $count; ?>" name="spese_accessorie[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Spese_Accessorie,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input id="diritto_riscossione_<?php echo $count; ?>" name="diritto_riscossione[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Diritto_Riscossione,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input <?php echo $readonly; ?> id="eca_<?php echo $count; ?>" name="eca[<?php echo $count; ?>]" value="<?php echo number_format($pagamento->Scorporo_Eca,2,",","."); ?>"> &euro;</td>
			<td><br></td>
		</tr>
		<tr>
			<td><br></td>
			<td class="text_center" colspan=3>
				<input class="width90 sfondo_red" type=button name="azzera" value="Azzera scorpori" onclick="pulisci_scorpori('<?php echo $count; ?>');">
			</td>
			<td><br></td>
			<td class="text_center"><input id="spese_notifica_<?php echo $count; ?>" name="spese_notifica[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Spese_Notifica,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input id="spese_precedenti_<?php echo $count; ?>" name="spese_precedenti[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Spese_Precedenti,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input id="spese_not_pigno_<?php echo $count; ?>" name="spese_not_pigno[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Notifica_Pignoramento,2,",","."); ?>"> &euro;</td>
			<td><br></td>	
			<td class="text_center"><input id="interessi_<?php echo $count; ?>" name="interessi[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Interessi,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input <?php echo $readonly; ?> id="tributo_provinciale_<?php echo $count; ?>" name="tributo_provinciale[<?php echo $count; ?>]" value="<?php echo number_format($pagamento->Scorporo_Tributo_Provinciale,2,",","."); ?>"> &euro;</td>
			<td><br></td>		
			<td class="text_center"></td>
			<td><br></td>
		</tr>
		<tr>
			<td class="text_left" colspan=15><hr></td>
		</tr>

	<?php $count++; }
}
}
for($i=0; $i<count($partita->Pignoramento); $i++)
{		
	if(count($partita->Pignoramento[$i]->Pagamento)==0)
		continue;		
?>	

<tr class="riga_pari text_left" style="height:30px;">
	<td class="width1"><br></td>
	<td class="text_left titolo" colspan=13><b><?php echo strtoupper("Pignoramento ".$partita->Pignoramento[$i]->Tipo." n.".$partita->Pignoramento[$i]->ID_Cronologico." del ".$partita->Pignoramento[$i]->Anno_Cronologico); ?></b></td>
	<td><br></td>	
</tr>
<tr class="riga_dispari text_left">
	<td class="width2"><br></td>
	<td class="text_center width6"><b>Rata</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Pagato</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Tributo</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Sp. Ricerca</b></td>
	<td class="width1"><br></td>	
	<td class="text_center width14"><b>Spese Acc.</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Diritto risc.</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Eca</b></td>
	<td class="width2"><br></td>
</tr>
<tr class="riga_dispari text_left">
	<td class="width2"><br></td>
	<td class="text_center width6"></td>
	<td class="width1"><br></td>
	<td class="text_center width14"></td>
	<td class="width2"><br></td>
	<td class="text_center width14"><b>Sp. Notifica</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Sp. Prec.</b></td>
	<td class="width1"><br></td>	
	<td class="text_center width14"><b>Sp.Not.Pigno.</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Interessi</b></td>
	<td class="width1"><br></td>
	<td class="text_center width14"><b>Trib. prov.</b></td>
	<td class="width1"><br></td>
</tr>
	<?php 

if(count($partita->Pignoramento[$i]->Pagamento)>0){
	for($y=0;$y<count($partita->Pignoramento[$i]->Pagamento);$y++){
		$pagamento = $partita->Pignoramento[$i]->Pagamento[$y];
		
		$Totali_Importo+= $pagamento->Importo;
		$Totali_Scorporo_Tributo+= $pagamento->Scorporo_Tributo;
		$Totali_Scorporo_Spese_Ricerca+= $pagamento->Scorporo_Spese_Ricerca;
		$Totali_Scorporo_Spese_Notifica+= $pagamento->Scorporo_Spese_Notifica;
		$Totali_Scorporo_Interessi+= $pagamento->Scorporo_Interessi;
		$Totali_Scorporo_Eca+= $pagamento->Scorporo_Eca;
		$Totali_Scorporo_Tributo_Provinciale+= $pagamento->Scorporo_Tributo_Provinciale;
		$Totali_Scorporo_Spese_Precedenti+= $pagamento->Scorporo_Spese_Precedenti;
		$Totali_Scorporo_Spese_Accessorie+= $pagamento->Scorporo_Spese_Accessorie;
		$Totali_Scorporo_Notifica_Pignoramento+= $pagamento->Scorporo_Notifica_Pignoramento;
		$Totali_Scorporo_Diritto_Riscossione+= $pagamento->Scorporo_Diritto_Riscossione;
		
		if($pagamento->Totale_Rate>0)
			$tot_rate = "/".$pagamento->Totale_Rate;
		else 
			$tot_rate = "";
			
		?>
		<tr>
			<td><input type=hidden id="id_pagamento_<?php echo $count; ?>" name="id_pagamento[<?php echo $count; ?>]" value="<?php echo $pagamento->ID; ?>"><br></td>
			<td class="text_center"><b><span class="color_titolo"><?php echo $pagamento->Rata.$tot_rate; ?></span></b></td>
			<td><br></td>
			<td class="text_center"><input readonly id="importo_pagato_<?php echo $count; ?>" name="importo_pagato[<?php echo $count; ?>]" class="width70 sfondo_azzurro" value="<?php echo number_format($pagamento->Importo,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input id="tributo_<?php echo $count; ?>" name="tributo[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Tributo,2,",","."); ?>"> &euro;</td>
			<td><br></td>	
			<td class="text_center"><input id="spese_ricerca_<?php echo $count; ?>" name="spese_ricerca[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Spese_Ricerca,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input id="spese_accessorie_<?php echo $count; ?>" name="spese_accessorie[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Spese_Accessorie,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input id="diritto_riscossione_<?php echo $count; ?>" name="diritto_riscossione[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Diritto_Riscossione,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input <?php echo $readonly; ?> id="eca_<?php echo $count; ?>" name="eca[<?php echo $count; ?>]" value="<?php echo number_format($pagamento->Scorporo_Eca,2,",","."); ?>"> &euro;</td>
			<td><br></td>
		</tr>
		<tr>
			<td><br></td>
			<td class="text_center" colspan=3>
				<input class="width90 sfondo_red" type=button name="azzera" value="Azzera scorpori" onclick="pulisci_scorpori('<?php echo $count; ?>');">
			</td>
			<td><br></td>
			<td class="text_center"><input id="spese_notifica_<?php echo $count; ?>" name="spese_notifica[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Spese_Notifica,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input id="spese_precedenti_<?php echo $count; ?>" name="spese_precedenti[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Spese_Precedenti,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input id="spese_not_pigno_<?php echo $count; ?>" name="spese_not_pigno[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Notifica_Pignoramento,2,",","."); ?>"> &euro;</td>
			<td><br></td>	
			<td class="text_center"><input id="interessi_<?php echo $count; ?>" name="interessi[<?php echo $count; ?>]" class="width70 corrige_numero" value="<?php echo number_format($pagamento->Scorporo_Interessi,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input <?php echo $readonly; ?> id="tributo_provinciale_<?php echo $count; ?>" name="tributo_provinciale[<?php echo $count; ?>]" value="<?php echo number_format($pagamento->Scorporo_Tributo_Provinciale,2,",","."); ?>"> &euro;</td>
			<td><br></td>		
			<td class="text_center"></td>
			<td><br></td>
		</tr>
		<tr>
			<td class="text_left" colspan=15><hr></td>
		</tr>
	<?php $count++; }
}
}?>

		<tr class="riga_pari text_left" style="height:30px;">
			<td class="width1"><br></td>
			<td class="text_left color_red" colspan=13><b>TOTALI</b></td>
			<td><br></td>	
		</tr>
		<tr class="riga_dispari text_left">
			<td class="width2"><br></td>
			<td class="text_center width6"></td>
			<td class="width1"><br></td>
			<td class="text_center width14"><b>Pagato</b></td>
			<td class="width1"><br></td>
			<td class="text_center width14"><b>Tributo</b></td>
			<td class="width1"><br></td>
			<td class="text_center width14"><b>Sp. Ricerca</b></td>
			<td class="width1"><br></td>	
			<td class="text_center width14"><b>Spese Acc.</b></td>
			<td class="width1"><br></td>
			<td class="text_center width14"><b>Diritto risc.</b></td>
			<td class="width1"><br></td>
			<td class="text_center width14"><b>Eca</b></td>
			<td class="width2"><br></td>
		</tr>
		<tr class="riga_dispari text_left">
			<td class="width2"><br></td>
			<td class="text_center width6"></td>
			<td class="width1"><br></td>
			<td class="text_center width14"></td>
			<td class="width2"><br></td>
			<td class="text_center width14"><b>Sp. Notifica</b></td>
			<td class="width1"><br></td>
			<td class="text_center width14"><b>Sp. Prec.</b></td>
			<td class="width1"><br></td>	
			<td class="text_center width14"><b>Sp.Not.Pigno.</b></td>
			<td class="width1"><br></td>
			<td class="text_center width14"><b>Interessi</b></td>
			<td class="width1"><br></td>
			<td class="text_center width14"><b>Trib. prov.</b></td>
			<td class="width1"><br></td>
		</tr>
		<tr>
			<td><br></td>
			<td class="text_center"></td>
			<td><br></td>
			<td class="text_center"><input readonly id="importo_pagato_totali" class="width70 sfondo_red" value="<?php echo number_format($Totali_Importo,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input readonly id="tributo_totali" name="tributo[totali]" class="width70 sfondo_red" value="<?php echo number_format($Totali_Scorporo_Tributo,2,",","."); ?>"> &euro;</td>
			<td><br></td>	
			<td class="text_center"><input readonly id="spese_ricerca_totali" name="spese_ricerca[totali]" class="width70 sfondo_red" value="<?php echo number_format($Totali_Scorporo_Spese_Ricerca,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input readonly id="spese_accessorie_totali" name="spese_accessorie[totali]" class="width70 sfondo_red" value="<?php echo number_format($Totali_Scorporo_Spese_Accessorie,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input readonly id="diritto_riscossione_totali" name="diritto_riscossione[totali]" class="width70 sfondo_red" value="<?php echo number_format($Totali_Scorporo_Diritto_Riscossione,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input readonly id="eca_totali" class="width70 sfondo_red" value="<?php echo number_format($Totali_Scorporo_Eca,2,",","."); ?>"> &euro;</td>
			<td><br></td>
		</tr>
		<tr>
			<td><br></td>
			<td class="text_center" colspan=3></td>
			<td><br></td>
			<td class="text_center"><input readonly id="spese_notifica_totali" class="width70 sfondo_red" value="<?php echo number_format($Totali_Scorporo_Spese_Notifica,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input readonly id="spese_precedenti_totali" class="width70 sfondo_red" value="<?php echo number_format($Totali_Scorporo_Spese_Precedenti,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input readonly id="spese_not_pigno_totali" class="width70 sfondo_red" value="<?php echo number_format($Totali_Scorporo_Notifica_Pignoramento,2,",","."); ?>"> &euro;</td>
			<td><br></td>	
			<td class="text_center"><input readonly id="interessi_totali" class="width70 sfondo_red" value="<?php echo number_format($Totali_Scorporo_Interessi,2,",","."); ?>"> &euro;</td>
			<td><br></td>
			<td class="text_center"><input readonly id="tributo_provinciale_totali" class="width70 sfondo_red" value="<?php echo number_format($Totali_Scorporo_Tributo_Provinciale,2,",","."); ?>"> &euro;</td>
			<td><br></td>		
			<td class="text_center"></td>
			<td><br></td>
		</tr>
		<tr>
			<td class="text_left" colspan=15><br></td>
		</tr>
	</table>
	
</form>

</td>
</tr>
</table>

<script>var num_pagamenti = <?php echo $count; ?>;</script>
<?php echo $layout; ?>

</body>
</html>