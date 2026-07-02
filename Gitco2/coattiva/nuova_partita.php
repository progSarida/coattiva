<?php
	/*
	require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
	include LIBRERIE . "/funzioni.php";
	
	include CLASSI . "/anagrafe.php";
	include CLASSI . "/comuni.php";
	include CLASSI . "/ruolo.php";
	include CLASSI . "/coazione.php";
	*/
    require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

    include_once(INC."/header.php");
    //include_once(INC."/menu.php");                                            // ?? uso questo o quello della pagina ??
    include_once(CLS."/cls_DateTimeInLine.php");
    include_once(CLS."/cls_help.php");

	if (!session_id()) session_start();
		
	if($_SESSION['username']==NULL)
	{
		header("Location:/gitco2/autenticazione/accesso_negato.php");
		die;
	}
	$cls_help = new cls_help();
	$a = $cls_help->getVar('a');
	$c = $cls_help->getVar('c');
	$p = $cls_help->getVar('p');                                                // p non viene passa dall'F6 di gestione_ruolo.php; $p non viene usato
	
	$layout = ""; 
	
	$ruolo_ID = $cls_help->getVar('ruolo');
	/*
	$ruolo = new ruolo($ruolo_ID, $c,null,false);                               // recupera dati ruolo dopo ricerca
	$numero_rate = $ruolo->Num_Rate;                                            //
	$numero_ruolo = $ruolo->Num_Ruolo;                                          //
	$data_fornitura = from_mysql_date($ruolo->Data_Fornitura);                  //
	$tipo_ruolo = $ruolo->Ruolo;                                                //
	if($tipo_ruolo!="")                                                         //
		$layout = "<script>$('#tipo_ruolo').val('".$tipo_ruolo."')</script>";   //
	$descrizione = $ruolo->Descrizione;                                         //
	
	$comune = new ente_gestito($c);                                             // recupera dati ente
	$nome_comune = $comune->Nome;                                               //
	$nome_comune =($nome_comune==NULL?"":$nome_comune." [".$c."]");             //

	$nome_user = "Operatore: ".$_SESSION['username'];                           // recupera operatore

	$class_ric = " sfondo_ricerca ";
	$class = " sfondo_bianco ";
	$class_calcolo = " sfondo_verde ";
	$disabled = "";
	$readonly = "";
	*/
		
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />

<title>Partita - Gestione</title>
	
	<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
	<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
	<style> .ui-datepicker { font-size:11px; } </style>
	
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>	
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>
  	
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>
  	
  	
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
	control = submit_buttons('Insert');
	if(control)
		$("#form_ruolo").submit();	
}

//F4
function cancella_form() 
{     
	return true;
}

//F5
function annulla()
{
	location.href="nuova_partita.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
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
	
function RicercheDaId (value, rif)
{
		var valorediritorno = 0;
		var strDim = Dim_Alert(600, 300);
		
		switch(value)
		{
			case "utente":

				strDim = Dim_Alert(600, 300);
				var stringa = "/gitco2/anagrafe/modali/ricerca_alert_modale.php?richiesta=generale&c=<?php echo $c; ?>&a=<?php echo $a; ?>";		
				valorediritorno = window.showModalDialog(stringa,"", strDim);

				if(valorediritorno==null)
					break;
				
				$.ajax({  
					  type: "POST",  
					  async: false,
					  url: "/gitco2/coattiva/ajax/ajax_partita.php?c=<?php echo $c; ?>",
					  data: {	
						  		ajax: "nome",
						  		ID: valorediritorno,
							}, 
							
					  success: function(value) {
			
					  		nome = value;
					  }
				});					

				
				if(rif==1)
				{
					if(valorediritorno!=null)
					{
						$('#utente_nome').val(nome);
						$('#utente').val(valorediritorno);
					}
				}
				else if(rif==2)
				{
					if(valorediritorno!=null)
					{
						num++;
						
						$('#coo').val(valorediritorno);
						$('#coo_nome').val(nome);						
						
						//MODIFICO NOME CON IL NUMERO DI COINTESTATARIO INSERITO
						$('#coo').attr('name','coo'+num);
						$('#coo').attr('id','coo'+num);
						
						$('#coo_nome').attr('name','coo_nome'+num);
						$('#coo_nome').attr('id','coo_nome'+num);
										
						//AGGIUNGO NUOVA RIGA PER INSERIMENTO NUOVO COINTESTATARIO
						$('#mytable tr:last').after("<tr><td></td><td class=\"text_left\"><input type=hidden id=coo name=coo ><input class=\"<?php echo $class_ric; ?>\" type=text id=coo_nome name=coo_nome value=\"\" ondblclick=\"RicercheDaId('utente',2)\" size=20></td><td></td><td></td></tr>");
						$('#num').val(num);
					}
				}
				break;

			case "ruolo":

				strDim = Dim_Alert(600, 300);
				var stringa = "modali/ricerca_alert_modale.php?richiesta=gen_ruolo&c=<?php echo $c; ?>&a=<?php echo $a; ?>";		
				valorediritorno = window.showModalDialog(stringa,"", strDim);

				if(valorediritorno!=null)
				{
					$('#id_ruolo').val(valorediritorno.ID);
					$('#ruolo').val(valorediritorno.Descrizione);
					$('#data').val(valorediritorno.Data);
					$('#progr_ruolo').val(valorediritorno.Num_Ruolo);
					$('#tipo_ruolo').val(valorediritorno.Tipo);
					$('#num_rate').val(valorediritorno.Num_Rate);
				}
				

				break;
		}
}

$(function() {
	
	 $( ".picker" ).datepicker();

	 });
	 
</script>

<!-- ********** AJAX FORM / SUBMIT ********** -->
<script>
var num = 0;

$(document).ready(function(){

	$("#submit_click").click( salva_form );
	
	$('#form_ruolo').ajaxForm(
			
	function(value) {
		var array_ritorno = value.split(' ');
		if(array_ritorno[0]=='OK')
		{
			alert('Salvataggio effettuato correttamente!');
			top.location.href = "nuova_partita.php?ruolo="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
		}
		else if(array_ritorno[0]=='ERROR')
		{
			alert('Errore nel salvataggio della partita! '+value);
			top.location.href = "nuova_partita.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
		}
	});

});

</script>

</head>

<body class="sfondo_new_gitco" >  

<table class="table_azzurra text_center" style="height:7%;">
	<tr>
		<td width=1%><br></td>
		<td class="text_left"><font class="comune" ><!-- <?php echo $nome_comune ?> --></font></td>
		<td class="text_right"><font class="user" ><!-- <?php echo $nome_user ?> --></font></td>
		<td width=1%><br></td>
	</tr>
</table>

<table height=93% class="table_azzurra text_center" border=0>
<tr>
<td valign=top>
        
<!-- <?php include MENU . '/menu_generale.php'; ?> -->

<!--
<table class="table_interna text_center" border=0 cellspacing=4>
	<tr>
		<td align=center width=7%>
			<a onMouseover="title='Consultazione'" href="#" onClick="" style="background:#aaaaaa; text-decoration: none;">
			<img src="/gitco2/immagini/F2grey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td align=center width=7%>
			<a onMouseover="title='Modifica'" href="#" onClick="" style="background:#979797; text-decoration: none;">
			<img src="/gitco2/immagini/redF2grey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<input id="submit_click" type="image" title="Salva" src="/gitco2/immagini/Save-iconF3.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td align=center width=7% >
			<input id="delete_click" type="image" title="Elimina" src="/gitco2/immagini/delete-iconF4grey.png" style="width:47px; height:47px; border:0;" />
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Annulla'" href="#" onClick="annulla();" style="text-decoration: none;">
			<img src="/gitco2/immagini/undo.png" width=47 height=47 border=0>
			</a>
		</td>
		<td align=center width=7% >	
			<a onMouseover="title='Nuovo Record'" href="#" onClick="crea_partita();" style="text-decoration: none;">
			<img src="/gitco2/immagini/nuovogrey.png" width=45 height=45 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Pagina precedente'" href="#" onclick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciagiugrey.png" width=47 height=47 border=0>
			</a>
		</td>
		<td align=center width=7% >
			<a onMouseover="title='Pagina successiva'" href="#" onclick="" style="text-decoration: none;">
			<img src="/gitco2/immagini/frecciasugrey.png" width=47 height=47 border=0>
			</a>
		</td>
		<td width=7% align="center">
          	<a href="#" onMouseover="title='Record precedente F7'" onclick="">
          	<img src="/gitco2/immagini/FrecciaSgrey.png" width=42px height=42px border="0" alt="Utente precedente">
          	</a>
    	</td>
        <td width=7% align="center">
            <a href="#" onMouseover="title='Record successivo F8'" onclick="">
            <img src="/gitco2/immagini/FrecciaDgrey.png" width=42px height=42px border="0" alt="Utente successivo">
            </a>
        </td>
        <td width=5%></td>
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
		<td width=3%></td>
		<td width=7%>
			<a onMouseover="title='Home'" href="#" onClick="link('menu');" style="text-decoration: none;">
			<img src="/gitco2/immagini/home.png" width=60 height=50 border=0>
			</a>
		</td>
	</tr>
</table>
-->

<br>

<table class="table_interna text_center" border="0">
	<tr>
		<td align=center><font class="titolo font16 under_decor" >Gestione ruoli</font></td>
	</tr>	
</table>
<br>
<form id=form_ruolo name=form_ruolo action="inserimento_ruolo_salva.php" method=post>
<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >
<input type=hidden id=id_ruolo name=id_ruolo value="<?php echo $ruolo_ID; ?>" >


<table id=mytable class="table_interna text_center" border="0">
	<tr>
		<td class="text_left width30">Ruolo Descrizione *</td>
		<td class="text_left width30"><input type=text id=ruolo name=ruolo value="<?php echo $descrizione; ?>" size=30></td>
		<td class="text_left width15"><input type=button id=new_ruolo name=new_ruolo value="Cerca Ruolo" class="button_azzurro" onclick="RicercheDaId('ruolo',1);"></td>
		<td></td>
	</tr>
	<tr>
		<td class="text_left">Tipo Ruolo *</td>
		<td class="text_left">
			<select id=tipo_ruolo name=tipo_ruolo class="pwidth146">
				<option>--- seleziona il tipo ---</option>
				<option id=Ord value=Ordinario>Ordinario</option>
				<option id=Coa value=Coattivo>Coattivo</option>
			</select>
		</td>
		<td class="text_left"></td>
		<td></td>
	</tr>
	<tr>
		<td class="text_left">Data Fornitura *</td>
		<td class="text_left"><input class="text_center picker" type=text id=data name=data value="<?php echo $data_fornitura; ?>" size=9></td>
		<td class="text_center"></td>
		<td class="text_left"></td>
	</tr>
	<tr>
		<td class="text_left">Numero Ruolo</td>
		<td class="text_left"><input class="text_right" type=text class="text_right" id=num_ruolo name=num_ruolo ondblclick="RicercheDaId('ruolo',1);" name=progr_ruolo value="<?php echo $numero_ruolo; ?>" size=4	></td>
		<td class="text_left"></td>
		<td class="text_left"></td>
	</tr>
	<tr>
		<td class="text_left">Numero Rate</td>
		<td class="text_left"><input class="text_right" ondblclick="RicercheDaId('ruolo',1);" type=text id=num_rate name=num_rate value="<?php echo $numero_rate; ?>" size=4></td>
		<td class="text_left"></td>
		<td class="text_left"></td>
	</tr>
</table>

</form>

</td>
</tr>
</table>

<?php echo $layout; ?>

</body>
</html>