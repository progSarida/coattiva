<?php
	/*require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
	include LIBRERIE . "/funzioni.php";

	include CLASSI . "/anagrafe.php";
	include CLASSI . "/comuni.php";*/
if (!session_id()) session_start();

include_once($_SESSION['_path']);
	include_once(ROOT."/_parameter.php");//dati database

	include(INC."/header.php");
	include_once(INC."/menu.php");
	include_once(CLS."/cls_DateTimeInLine.php");
	include_once(CLS."/cls_anagrafeUtils.php");

    $submenuPageNo = 5;

	$cls_date = new cls_DateTimeI("IT",false);
	$cls_anagr = new cls_anagr();

	if (!session_id()) session_start();

	if($_SESSION['username']==NULL)
	{
		header("Location:/gitco2/autenticazione/accesso_negato.php");
		die;
	}

	$a = $cls_help->getVar('a');
	$c = $cls_help->getVar('c');
	$p = $cls_help->getVar('p');
	$mode = $cls_help->getVar('mode');
	$servizio = $cls_help->getVar('servizio');
	//$sceltaLayout = "";

	$mode = "modifica";//ANNULLO CONSULTA

	if($mode=="consulta" || $mode==null)
	{
		$mode = "consulta";
		$readonly = " readonly ";
		$class = " sfondo_readonly ";
		$class_ric = " sfondo_readonly ";
	}
	else
	{
		$mode = "modifica";
		$readonly = "";
		$class_ric = " sfondo_ricerca ";
		$class = " sfondo_bianco ";
	}

//	$comune = new ente_gestito($c);
	$nome_comune = $a_enteAdmin["Denominazione"];//$comune->Nome;

	$nome_comune =($nome_comune==NULL?"":$nome_comune." [".$c."]");
	$nome_user = "Operatore: ".$_SESSION['username'];

	$QUERY = $cls_anagr->get_Query_Dati_Soggetto($p,$c);

	//echo $QUERY["Soggetto"];
	$anagr = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($QUERY["Soggetto"]),"utente");

	$id_utente 				= 	$anagr["ID"]; //$utente->ID;
	$genere_utente 			= 	$anagr["Genere"]; //$utente->Genere;
	$comune_id 				= 	$anagr["Comune_ID"]; //$utente->Comune_ID;

	if($genere_utente!='D')
	{
		$cognome_utente 	=	$anagr["Cognome"]; //$utente->Cognome;
		$nome_utente 		=	$anagr["Nome"]; //$utente->Nome;
		$CC_nascita			=	$anagr["CC_Nascita"]; //$utente->CC_Nascita;
		$paese_nasc_utente  =	$anagr["Paese_Nascita"]; //$utente->Paese_Nascita;
		if($paese_nasc_utente==null)
		{
			$paese_nasc_utente = "Italia";
		}
		$comune_nasc_utente =	$anagr["Comune_Nascita"]; //$utente->Comune_Nascita;
	 $provincia_nasc_utente	=	$anagr["Provincia_Nascita"]; //$utente->Provincia_Nascita;
		$data_nasc_utente	= $cls_date->Get_DateNewFormat($anagr["Data_Nascita"],"DB");//	from_mysql_date($utente->Data_Nascita);
		$data_morte_utente	=	$cls_date->Get_DateNewFormat($anagr["Data_Morte"],"DB"); //from_mysql_date($utente->Data_Morte);
		$CF					=	$anagr["Codice_Fiscale"]; //$utente->Codice_Fiscale;
	}
	else
	{
		$ditta				=	$anagr["Ditta"]; //$utente->Ditta;
		$PI					=	$anagr["Partita_Iva"]; //$utente->Partita_Iva;
		$prec_den_ditta		=	$anagr["Prec_Denom"]; //$utente->Prec_Denom;
		$anno_cambio_ditta	=	$anagr["Anno_Cambio_Denom"]; //$utente->Anno_Cambio_Denom;
	}

	$DATA = $cls_anagr->get_Data_Dettagli($p);
	//echo gettype($DATA);

	//print_r($DATA);

	//$dettagli_utente = $utente->Dettagli_Utente;
	//$type_det = gettype($dettagli_utente);

	$ID_dettagli = 0;
	$ID_ese = 1;
	$ID_sit = 1;
	$ID_con = 1;
	$ID_rag = 1;
	$ID_sot = 1;

	if($DATA["ID"]!= "")
	{
		$ID_dettagli = $DATA["ID"];
		$ID_ese = $DATA["Esenzione_ID"];//$dettagli_utente->Esenzione_ID;
		$esenzione = $DATA["Esenzione"];//$dettagli_utente->Esenzione;
		$ID_sit = $DATA["Situazione_ID"];//$dettagli_utente->Situazione_ID;
		$situazione = $DATA["Situazione"];//$dettagli_utente->Situazione;
		$ID_con = $DATA["Controllo_ID"];//$dettagli_utente->Controllo_ID;
		$controllo = $DATA["Controllo"];//$dettagli_utente->Controllo;
		$ID_rag = $DATA["Raggruppamento_ID"];//$dettagli_utente->Raggruppamento_ID;
		$raggruppamento = $DATA["Raggruppamento"];//$dettagli_utente->Raggruppamento;
		$ID_sot = $DATA["Sottoraggruppamento_ID"];//$dettagli_utente->Sottoraggruppamento_ID;
		$sottoraggruppamento = $DATA["Sottoraggruppamento"];//$dettagli_utente->Sottoraggruppamento;
	}
	else
	{
		$esenzione = null;
		$situazione = null;
		$controllo = null;
		$raggruppamento = null;
		$sottoraggruppamento = null;
	}

	//echo "<h1>".$esenzione." - ".$situazione." - ".$controllo." - ".$raggruppamento." - ".$sottoraggruppamento."</h1>";

	$ID_PAGE = $cls_anagr->get_ID_Move_Page($p,$a,$c,$anagr["Cognome"],$anagr["Ditta"],$anagr["ID"]);

	$pnext = $ID_PAGE["next"];//$utente->next;
	$pprev = $ID_PAGE["prev"];//$utente->prev;
	$next_alfa = $ID_PAGE["next_alfa"];//$utente->next_alfa;
	$prev_alfa = $ID_PAGE["prev_alfa"];//$utente->prev_alfa;

	/*$pnext = $utente->next;
	$pprev = $utente->prev;
	$next_alfa = $utente->next_alfa;
	$prev_alfa = $utente->prev_alfa;*/

	$ordinamento = $cls_help->getVar('ordinamento');
	if($ordinamento=='')	$ordinamento="ID";


	if( $ordinamento == "Nome" )
	{
		$prev_current = $prev_alfa;
		$next_current = $next_alfa;
	}
	else
	{
		$prev_current = $pprev;
		$next_current = $pnext;
	}

	if ($pnext==null) 	$pnext = 0;
	if ($pprev==null) 	$pprev = 0;
	if ($p==null)		$p=0;

	if($ID_dettagli==0)		{	$submit_name = "Insert";	}
	else					{	$submit_name = "Update";	}

/**
 * GESTIONE F2 /////////////////////////////////////////
 */
	if($mode == "consulta")
	{
		if($p!=0)
		{
			$F2_path = "/gitco2/immagini/redF2.png";
			$F2_click = "blocco('".$utente->ID."')";
			$F2_title = "Modifica";
		}
		else
		{
			$F2_path = "/gitco2/immagini/redF2grey.png";
			$F2_click = "";
			$F2_title = "Modifica";
		}
	}
	else
	{
		$F2_path = "/gitco2/immagini/F2.png";
		$F2_click = "scelta_moda('cerca');";
		$F2_title = "Consultazione";

		/////////////////////////////////////////
		$F2_path = "/gitco2/immagini/F2grey.png";
		$F2_click = "";
		$F2_title = "";
		/////////////////////////////////////////
	}

	$dropOptions = $cls_anagr->Get_Drop_Dettagli();
/**
 * GESTIONE F2 /////////////////////////////////////////
 */
?>


<script>   /* -----------  VARIABILI JAVASCRIPT E SELEZIONI LAYOUT ----------- */
var stringaPHP = "&p=<?php echo $p; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
var modalita = '<?php echo $mode; ?>';
var uscita_utente = '0';
var utente_ID = '<?php echo $anagr["ID"]; ?>';
</script>

<script>    /* -----------  AJAX FORM SUBMIT ----------- */

$(document).ready(function(){

	$("#id_cerca").focus();

	$('#cerca_id').ajaxForm(
                function(value) {
                    var array_ritorno = value.split(' ');
			if(array_ritorno[0]=='NO')
			{
				alert('Codice utente non trovato!');
				top.location.href = "dettagli.php?mode=consulta&p="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
			}
			else
			{
        		top.location.href = "dettagli.php?mode=consulta&p="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
			}
        });

    });

</script>

<script>

//F3
switchMenuImg("F3");
F3_button = function()
{
	control=submit_buttons('<?php echo $submit_name; ?>');
	if(control)
			$("#btnSub").trigger("click");
}

//F4
switchMenuImg("F4");
F4_button = function()
{
	control=submit_buttons('Delete');
		if(control)
			$("#btnSub").trigger("click");
}

//F5
switchMenuImg("F5");
F5_button = function()
{
	stringaPHP += "&mode=consulta";
stringa = "dettagli.php?"+stringaPHP;
		top.location.href = stringa;
}

//F6
switchMenuImg("F6");
F6_button = function()
{
    stringa = "dati_soggetto.php?mode=modifica&p=0&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    top.location.href = stringa;
}



//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){
	if (modifica==1)
	{
		alert('salvare i dati o annullare prima di procedere');
	}
	else
	{
		value_ord = $('#ordinamento').val();

			link = "domicilio.php?mode=<?php echo $mode; ?>&ordinamento="+value_ord+stringaPHP;
			top.location.href = link;

	}
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){
	if (modifica==1)
	{
		alert('salvare i dati o annullare prima di procedere');
	}
	else
	{
		value_ord = $('#ordinamento').val();
		if(modalita=="consulta" || utente_ID!=0)
		{
			link = "cambia_residenza.php?mode=<?php echo $mode; ?>&ordinamento="+value_ord+stringaPHP;
			top.location.href = link;
		}
	}
}

//F7
switchMenuImg("F7");
F7_button = function()
{
	if (modifica==1)
	{
		alert('salvare i dati o annullare prima di procedere');
	}
	else
	{
		value_ord = $('#ordinamento').val();
		link = "dettagli.php?mode=consulta&p="+prev_utente+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>&ordinamento="+value_ord;

		top.location.href = link;
	}
}

//F8
switchMenuImg("F8");
F8_button = function()
{
	if (modifica==1)
	{
		alert('salvare i dati o annullare prima di procedere');
	}
	else
	{
		value_ord = $('#ordinamento').val();
		link = "dettagli.php?mode=consulta&p="+next_utente+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>&ordinamento="+value_ord;

		top.location.href = link;
	}
}


</script>

<script>

//SCELTA MODALITA' LETTURA O SCRITTURA
function scelta_moda(value)
{
	if (modifica==1)
	{
		alert('salvare i dati o annullare prima di procedere');
	}
	else
	{
		value_ord = $('#ordinamento').val();

		if(value=="modifica")
   		{
   	   		if(utente_ID!=0)
			top.location.href = "dettagli.php?mode=modifica&ordinamento=" + value_ord + stringaPHP;
   		}
   		else
   		{
   			if(utente_ID!=0)
   			top.location.href = "dettagli.php?mode=consulta&ordinamento=" + value_ord + stringaPHP;
   		}
   	}
}

var prev_utente = "<?php echo $prev_current; ?>";
var next_utente = "<?php echo $next_current; ?>";

function ordinamento ()
{
   	value = $('#ordinamento').val();

   	if(value=="ID")
   	{
   	   	prev_utente = "<?php echo $pprev; ?>";
   		next_utente = "<?php echo $pnext; ?>";
   	}
   	else if(value=="Nome")
   	{
   		prev_utente = "<?php echo $prev_alfa; ?>";
   		next_utente = "<?php echo $next_alfa; ?>";
   	}
}
</script>

<script>

//CONTROLLO CAMPI
   	function controllaCampi (value)
   	{
   		return true;
	}

	function SettaID(field)
	{
		switch(field.name)
		{
			case "esenzione": document.getElementById("ese").value = field.value; break;
			case "situazione": document.getElementById("sit").value = field.value; break;
			case "controllo": document.getElementById("con").value = field.value; break;
			case "raggr": document.getElementById("rag").value = field.value; break;
			case "sottoraggr": document.getElementById("sot").value = field.value; break;
			default: break;
		}
	}
</script>



<script>

if(utente_ID=="")
{
	if(prev_utente!="0")
   		$('#F7').attr("onMouseover","title='Ultimo record F7'");

	if(next_utente!="0")
		$('#F8').attr("onMouseover","title='Primo record F8'");
}
else
{
	if(prev_utente=="" && next_utente!="")
	{
   		$('#F7').attr("onMouseover","title='Nessun record F7 (Primo record selezionato)'");
		$('#F8').attr("onMouseover","title='Record successivo F8 (Primo record selezionato)'");
	}

	if(next_utente=="" && prev_utente!="")
	{
		$('#F7').attr("onMouseover","title='Record precedente F7 (Ultimo record selezionato)'");
		$('#F8').attr("onMouseover","title='Nessun record F8 (Ultimo record selezionato)'");
	}
}

</script>


<?php
$menuPageNumber = "Pag 5/7";
$pagina = "dettagli.php";
include_once(INC."/submenu_anagrafe.php");
include_once(INC."/pages_authorization.php");
?>

<form id=anagrafe_form class="form-horizontal validate" name=dettagli action="dettagli_salva.php" method=post >

<input name=a 				type=hidden value="<?php echo $a; ?>"			>
<input name=p 				type=hidden value="<?php echo $p; ?>"			>
<input name=c 				type=hidden value="<?php echo $c; ?>"			>
<input name=servizio 		type=hidden value="<?php echo $servizio; ?>"	>
<input name=comune_id		type=hidden value="<?php echo $comune_id; ?>"	>
<input name=ese  id=ese 	type=hidden value="<?php echo $ID_ese; ?>"		>
<input name=sit  id=sit 	type=hidden value="<?php echo $ID_sit; ?>"		>
<input name=con  id=con 	type=hidden value="<?php echo $ID_con; ?>"		>
<input name=rag  id=rag 	type=hidden value="<?php echo $ID_rag; ?>"		>
<input name=sot  id=sot 	type=hidden value="<?php echo $ID_sot; ?>"		>
<input name=invia_submit 	type=hidden	value=""	id=invia_submit			>

<div class="row justify-content-md-center " style="margin-top: 1%;">
	<div class="col col-md-auto text_center">
			<span class="titolo font16 under_decor">Dettagli</span>
	</div>
</div>

<div class="row">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Esenzione</label>
			<div class="col-lg-8">
				<select class="form-control resize" style="width: 70%;" name=esenzione id=esenzione onchange="SettaID(this);">
						<option value="0"></option>
						<?php echo $dropOptions["Esenz"]; ?>
				</select>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Situazione</label>
			<div class="col-lg-8">
				<select class="form-control resize" style="width: 70%;" name=situazione id=situazione onchange="SettaID(this);" >
						<option value="0"></option>
						<?php echo $dropOptions["Situaz"]; ?>
				</select>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Controllo</label>
			<div class="col-lg-8">
				<select class="form-control resize" style="width: 70%;" name=controllo id=controllo onchange="SettaID(this);">
						<option value="0"></option>
						<?php echo $dropOptions["Control"]; ?>
				</select>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Raggruppamento</label>
			<div class="col-lg-8">
				<select class="form-control resize" style="width: 70%;" name=raggr id=raggr onchange="SettaID(this);">
						<option value="0"></option>
						<?php echo $dropOptions["Raggr"]; ?>
				</select>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Sottoraggruppamento</label>
			<div class="col-lg-8">
				<select class="form-control resize" style="width: 70%;" name=sottoraggr id=sottoraggr onchange="SettaID(this);">
						<option value="0"></option>
						<?php echo $dropOptions["Sotto_Raggr"]; ?>
				</select>
			</div>
		</div>
	</div>
</div>

<div class="form-group">
	<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
</div>

</form>

<!--</td>
</tr>
</table>-->

<script>
$( document ).ready(function() {

	$('#ordinamento').val('<?= $ordinamento; ?>');

	//$("#esenzione").val("Ambasciata");
	//alert("<?php echo $esenzione; ?>");

	if("<?= $esenzione; ?>"!="") $("#esenzione").val("<?= $ID_ese; ?>");
	if("<?= $situazione; ?>"!="") $("#situazione").val("<?= $ID_sit; ?>");
	if("<?= $controllo; ?>"!="") $("#controllo").val("<?= $ID_con; ?>");
	if("<?= $raggruppamento; ?>"!="") $("#raggr").val("<?= $ID_rag; ?>");
	if("<?= $sottoraggruppamento; ?>"!="") $("#sottoraggr").val("<?= $ID_sot; ?>");

});
</script>

<?php include(INC."/footer.php"); ?>

<!--</body>
</html>-->
