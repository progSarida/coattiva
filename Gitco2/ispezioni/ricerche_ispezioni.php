<?php
    if (!session_id()) session_start();

    if($_SESSION['username']==NULL)
    {
        header("Location:/gitco2/autenticazione/accesso_negato.php");
        die;
    }


	include_once($_SESSION['_path']);
	include_once(ROOT."/_parameter.php");//dati database

	include(INC."/header.php");
	include_once(INC."/menu.php");
	include_once(CLS."/cls_DateTimeInLine.php");
	include_once(CLS."/cls_GestionePartita.php");

	$cls_date = new cls_DateTimeI("IT",false);
	$cls_GP = new cls_GP();


	$a = $cls_help->getVar('a');
	$c = $cls_help->getVar('c');
	$p = $cls_help->getVar('p');
	$nuova_ispezione = $cls_help->getVar('nuova_ispezione');



	/*$comune = new ente_gestito($c);
	$nome_comune = $comune->Nome;

	$nome_comune =($nome_comune==NULL?"":$nome_comune." [".$c."]");
	$nome_user = "Operatore: ".$_SESSION['username'];*/

	//$utente = new utente($p,$c);
	$utente = $cls_GP->getDataPartita($p,$c,$a);


	$id_utente 				= 	$utente["ID"];
	$genere_utente 			= 	isset($utente["Genere"])?$utente["Genere"]:"";
	$comune_id 				=	$utente["Comune_ID"];
	$cognome_utente 		=	isset($utente["Cognome"])?$utente["Cognome"]:"";
	$nome_utente 			=	isset($utente["Nome"])?$utente["Nome"]:"";
	$ditta					=	isset($utente["Ditta"])?$utente["Ditta"]:"";

	$pnext = isset($utente["next"])?$utente["next"]:0;
	$pprev = isset($utente["prev"])?$utente["prev"]:0;
	$next_alfa = isset($utente["next_alfa"])?$utente["next_alfa"]:0;
	$prev_alfa = isset($utente["prev_alfa"])?$utente["prev_alfa"]:0;

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

	//$ispezione = new ispezioni(null, $c);
	//$array_ispezioni = $ispezione->array_ispezioni($p);
	//$num_ricerche = count($array_ispezioni);
	$query = "SELECT * FROM ispezioni WHERE Utente_ID = '".$p."'";
	$array_ispezioni = $cls_db->getResults($cls_db->ExecuteQuery($query));
	$num_ricerche = count($array_ispezioni);

	$options = "";
	for($i=0;$i<$num_ricerche;$i++)
	{
		if($i==$num_ricerche-1)
			$selected = "selected";
		else
			$selected = "";

		$options .= "<option $selected>".$array_ispezioni[$i]['Denominazione']."</option>";
	}

	$tipo = "";
	$contenuto = "";
	$note = "";
	$data_inserimento = date('Y-m-d');
	$data_ispezione = "";

if( $num_ricerche == 0 || $p==0 || $nuova_ispezione == 1 )
{
	$id_ispezione = 0;
}
else
{
	$tipo = $array_ispezioni[$num_ricerche-1]['Tipo'];
	$contenuto = $array_ispezioni[$num_ricerche-1]['Contenuto'];
	$note = $array_ispezioni[$num_ricerche-1]['Note'];
	$data_inserimento = $array_ispezioni[$num_ricerche-1]['Data_Inserimento'];
	$data_ispezione = $array_ispezioni[$num_ricerche-1]['Data_Ispezione'];
	$id_ispezione = $array_ispezioni[$num_ricerche-1]['ID'];
}
if($p!=0 && $id_ispezione==0)
	$nuova_ispezione = 1;
?>
<!-- GESTIONE MODALI -->

<!-- Inclusione modale ricerca utente -->
<?php include_once (ROOT."/search_modal/offcanvas/user_offcanvas.php"); ?>

<script>
// Modali offcanvas
function openOfcanvas(id_off,rif) {
    // Reset campi input
    $('#user_name').val("");
    $('#user_cf').val("");
    // Reset spazi tabella
    $('#appendTableUser').empty();
    //flagAQjaxReserch = true;
    switch (id_off) {
        case 'userSearchModal':
            all_city = 'n';
            $("#ins_u_cf").hide();
            $("#ins_u_name").show();
            document.getElementById('check_u_name').checked = true;
            document.getElementById('check_u_cf').checked = false;
            $("#checkbox_c").hide();
            $('#userSearchModal').modal('show');
            break;
        default:
            alert("Ricerca non possibile");
            break;
    }
}

function initialId(tipo,val){
    value_ord = $('#ordinamento').val();

    var strDim = Dim_Alert(600, 300);

    switch(tipo)
    {
        case "user":
        case "cf":
            top.location.href="<?= WEB_ROOT; ?>/ispezioni/ricerche_ispezioni.php?p="+val['ID']+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&ordinamento="+value_ord;
            break;
    }
}
</script>


<!-- ********** GESTIONE LINK MENU ********** -->
<script>var nuova_ispezione = "<?php echo $nuova_ispezione?>";

//F3
switchMenuImg("F3");
F3_button = function()
{
	if( "0" == "<?php echo $p; ?>" )
		alert("Selezionare un utente!");
	else
	{
		if( nuova_ispezione == 1 )
	   		control = submit_buttons('Insert');
		else
			control = submit_buttons('Update');

	   	if(control)
	       	$("#btnSub").trigger("click");
	}
}


//F4
switchMenuImg("F4");
F4_button = function()
{
	if( "0" == "<?php echo $p; ?>" )
	{
		alert("Selezionare un utente!");
	}
	else
	{
  		control=submit_buttons('Delete');
   		if(control)
		   $("#btnSub").trigger("click");					// sostituito submit form, ora funziona
       	//$("#ricerche_form").submit();
	}
}

//F5
switchMenuImg("F5");
F5_button = function()
{
		location.href="ricerche_ispezioni.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&p=<?php echo $p; ?>";
}

//F6
switchMenuImg("F6");
F6_button = function()
{
	if( modifica == 0 )
	{
		location.href="ricerche_ispezioni.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&p=<?php echo $p; ?>&nuova_ispezione=1";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
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
		link = "ricerche_ispezioni.php?&p="+prev_utente+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&ordinamento="+value_ord;
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
		link = "ricerche_ispezioni.php?p="+next_utente+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&ordinamento="+value_ord;
		top.location.href = link;
	}
}

//F9
F9_button = function(){
    RicercheDaId('utente',0);
    openOfcanvas('userSearchModal',0);
}
function ricerca_F9()
{
	if( modifica == 0 )
	{
		//RicercheDaId('utente',0);
        openOfcanvas('userSearchModal',0);
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//F11-F12 sono nel menu'


//******************************\\
//ALTRI LINK / FUNZIONI CHIAMATE\\
function ruolo (value)
{
	top.location.href="<?= WEB_ROOT; ?>/coattiva/gestione_ruolo.php?p="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
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

<!-- ********** ARRAY DA PHP ********** -->
<script>
var tipo = new Array();
var note = new Array();
var contenuto = new Array();
var data_inserimento = new Array();
var data_ispezione = new Array();
var id_ispezione = new Array();

<?php
for($y=0; $y<$num_ricerche; $y++)
{
	$contenuto_js = str_replace("\r\n"," ",$array_ispezioni[$y]['Contenuto']);
	$contenuto_js = str_replace("\n"," ",$contenuto_js);

	$note_js = $array_ispezioni[$y]['Note'];

	{
		$note_js = str_replace("\r\n"," ",$array_ispezioni[$y]['Note']);
		$note_js = str_replace("\n"," ",$note_js);
		//$note_js = str_replace("\r"," ",$note_js);
		//$array_ispezioni[$y]['Note'] = substr($array_ispezioni[$y]['Note'], 20, 21);
		//$array_ispezioni[$y]['Note'] = "AAA";
	}

?>

	tipo[<?php echo $y; ?>] = "<?php echo $array_ispezioni[$y]['Tipo']; ?>";
	note[<?php echo $y; ?>] = "<?php echo $note_js; ?>";
	contenuto[<?php echo $y; ?>] = "<?php echo $contenuto_js; ?>";
	data_inserimento[<?php echo $y; ?>] = "<?php echo $cls_date->Get_DateNewFormat($array_ispezioni[$y]['Data_Inserimento'],"DB"); ?>";
	data_ispezione[<?php echo $y; ?>] = "<?php echo $cls_date->Get_DateNewFormat($array_ispezioni[$y]['Data_Ispezione'],"DB"); ?>";
	id_ispezione[<?php echo $y; ?>] = "<?php echo $array_ispezioni[$y]['ID']; ?>";

<?php
}
?>

function cambia_ispezione(value)
{
	$('#tipo').val( tipo[value] );
	$('#note').val( note[value] );
	$('#contenuto').val( contenuto[value] );
	$('#id_ispezione').val( id_ispezione[value] );
	$('#data_inserimento').val( data_inserimento[value] );
	$('#data_ispezione').val( data_ispezione[value] );


}
</script>

<script>   /* -----------  VARIABILI JAVASCRIPT E SELEZIONI LAYOUT ----------- */
	var stringaPHP = "&p=<?php echo $p; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	var utente_ID = '<?php echo $utente["ID"]; ?>';

	function Dim_Alert ( sWidth, sHeight )
   	{
		setupPagina = "dialogWidth:" + sWidth + "px";
   		setupPagina += "; dialogHeight:" + sHeight + "px";
   		setupPagina += ";dialogLeft:80px;dialogTop:80px;";

   		return setupPagina;
   	}

	function RicercheDaId (value, rif)
   	{
		value_ord = $('#ordinamento').val();

   		var valorediritorno = 0;
   		var strDim = Dim_Alert(600, 300);

   		switch(value)
   		{
   			case "utente":

   				strDim = Dim_Alert(600, 300);
   				var stringa = "<?= WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=generale&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
   				valorediritorno = window.showModalDialog(stringa,"", strDim);

   				/*if(valorediritorno!=null)
   				{
   					top.location.href="ricerche_ispezioni.php?p="+valorediritorno+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>&ordinamento="+value_ord;
   				}*/

   				break;
   		}
   	}

    function callParent(valorediritorno){
        console.log(valorediritorno);
        if(valorediritorno!=null)
        {
            top.location.href="<?= WEB_ROOT; ?>/ispezioni/ricerche_ispezioni.php?p="+valorediritorno.p+"&c="+valorediritorno.c+"&a=<?php echo $a; ?>&ordinamento="+value_ord;
        }
    }

</script>

<!-- ********** CALENDARIO ********** -->
	<script>
	$( function() {

		 $( ".picker" ).datepicker();

		 } );

	</script>

<script>    /* -----------  AJAX FORM SUBMIT ----------- */

$(document).ready(function(){

	$('#id_cerca').focus();

	/*$("#submit_click").click( salva_form );

    $("#delete_click").click( cancella_form );

    $('#ricerche_form').ajaxForm(

                function(value) {
                    array_ritorno = value.split(' ');
                    switch(array_ritorno[0])
                    {
                    	case "Delete":

                        	if(array_ritorno[1]=='Si')
                        	{
                            	alert('Ricerca eliminata con successo!');
                            	annulla();
                        	}
                        	else
                        	{
                        		alert("Errore nel tentativo di eliminazione della Ricerca. ".array_ritorno[1]);
                        	}

                    	break;

                    	case "Update":

                    		if(array_ritorno[1]=='Si')
                        	{
                            	alert('Ricerca aggiornata con successo!');
                            	annulla();
                        	}
                        	else
                        	{
                        		alert("Errore nel tentativo di aggiornamento della Ricerca. ".array_ritorno[1]);
                        	}

                    	break;

						case "Insert":

                    		if(array_ritorno[1]=='Si')
                        	{
                            	alert('Ricerca inserita con successo!');
                            	annulla();
                        	}
                        	else
                        	{
                        		alert("Errore nel tentativo di inserimento della Ricerca. ".array_ritorno[1]);
                        	}

                    	break;

                    }

        });*/

	$('#cerca_id').ajaxForm(
                function(value) {
                    var array_ritorno = value.split(' ');
			if(array_ritorno[0]=='NO')
			{
				alert('Codice utente non trovato!');
				top.location.href = "ricerche_ispezioni.php?p="+array_ritorno[1]+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
			}
			else
			{
        		top.location.href = "ricerche_ispezioni.php?p="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
			}
        });

    });

</script>


<table align=center class=table_interna border=0 style="border:3px solid #6D95D5;">
	<tr>
		<td width=8% class="text_center">
			<a onMouseover="title='Cerca utente'" href="#" onclick="ricerca_F9();" style="text-decoration: none;">
			<img src="<?= IMMAGINIWEB; ?>/User Folder.png" width=47 height=47 border=0>
			</a>
		</td>
		<td width=16% class="text_center"><font class="titolo font18">ISPEZIONI</font><br><font class="titolo font14">Pag 1/1</font></td>
    	<td width=40% class="text_left">
            <em style="font-style : normal ;">
            <?php if($genere_utente!='D'){echo $cognome_utente." ".$nome_utente;}else{ echo $ditta; } ?></em>
        </td>
        <td width=14% class="text_center">
        	<font class="color_titolo font16">Ordinamento</font>
        	<select id=ordinamento name=ordinamento onchange="ordinamento();"><option value=ID>ID utente</option><option value=Nome>Alfabetico</option></select>
        </td>
        <td class="text_left width4"><input type=image src="<?= IMMAGINIWEB; ?>/select.png" style="width:25px; height:25px; border:0;" title="Gestione Ruolo" onclick="ruolo('<?php echo $p; ?>');">
        <td width=18% align=right>
		<form id=cerca_id method=post action=modali/ricerca_codice_result.php>
			<input type=hidden name=old_cod_contr value='<?php echo $comune_id; ?>'>
           	<input name=c type=hidden value='<?php echo $c; ?>'>
            <input name=a type=hidden value='<?php echo $a; ?>'>
		Utente ID &nbsp;
		<input id=id_cerca tabindex=1 class="valign_center text_right" type=text name=ric_cod_contr value='<?php echo $comune_id; ?>' size=3 onMouseover="title='Inserire il codice utente e premere Invio'">&nbsp;&nbsp;</form>

		</td>
</tr>
</table>

<form id=ricerche_form class="form-horizontal validate" name=ricerche_form method=post action="ricerche_ispezioni_salva.php">

<input name=c type=hidden value=<?php echo $c; ?>>
<input name=a type=hidden value=<?php echo $a; ?>>
<input name=p type=hidden value=<?php echo $p; ?>>
<input name=id_ispezione id=id_ispezione  type=hidden value="<?php echo $id_ispezione; ?>" >
<input name=invia_submit id=invia_submit  type=hidden value=""	>

<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><font class="titolo font16 under_decor">Ricerche</font></td>
	</tr>
</table>

<?php if($num_ricerche!=0)
{?>

<table class="text_center table_interna" cellspacing=0 border=0 style="border:1px solid black;">

<tr class="text_left riga_dispari" style="height:30px;" >
	<td class="width4"><br></td>
	<td class="width1"><br></td>
	<td class="text_center width20"><b>Data inserimento</b></td>
	<td class="width5"><br></td>
	<td class="text_center width20"><b>Data Ispezione</b></td>
	<td class="width5"><br></td>
	<td class="text_left width35"><b>Ispezione</b></td>
	<td class="width10"><br></td>
</tr>

<?php

for($i=0; $i<$num_ricerche; $i++)
{
	$y = $i;

	if ($y++ % 2)
		{$stile_riga = 'class="riga_dispari text_left"'	;	}
	else
		{$stile_riga = 'class="riga_pari text_left"'	;	}

?>

		<tr <?php echo $stile_riga; ?>>
			<td class="text_center width4">
			<input type=image src="<?= IMMAGINIWEB; ?>/select.png" style="width:25px; height:25px; border:0;" title="Dettagli Ispezione" onClick="cambia_ispezione('<?php echo $i; ?>');return false;"></td>
			<td class="width1"><br></td>
			<td class="text_center width20"><?php echo $cls_date->Get_DateNewFormat($array_ispezioni[$i]['Data_Inserimento'],"DB"); ?></td>
			<td class="width5"><br></td>
			<td class="text_center width20"><?php echo $cls_date->Get_DateNewFormat($array_ispezioni[$i]['Data_Ispezione'],"DB"); ?></td>
			<td class="width5"><br></td>
			<td class="text_left width35"><?php echo $array_ispezioni[$i]['Tipo']; ?></td>
			<td class="width10"><br></td>
		</tr>

	<?php }?>
	</table>

<?php }?>

<div class="row" style="margin-top: 1%; margin-bottom: 1%;">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Tipo</label>
			<div class="col-lg-8">
				<select name=tipo id=tipo class="form-control resize" tabindex=3>
					<option></option>
					<option>Anagrafe tributaria</option>
					<option>INPS</option>
					<option>Registro automobilistico</option>
					<option>Camera commercio</option>
					<option>Ricerca presso comuni</option>
					<option>Internet e altro</option>
					<option>Analisi aggredibilita</option>
					<option>Necessitano approfondimenti</option>
				</select>
			</div>
		</div>
	</div>
	<div class="col col-lg-7">
		<div class="form-group">
			<label class="col-lg-2 control-label resize" style="text-align: left;">Note</label>
			<div class="col-lg-10">
				<textarea tabindex=4 id=note name=note class="form-control resize" rows=2% style="max-width: 100%;" ><?php echo $note; ?></textarea>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-1 control-label resize" style="text-align: left;">Contenuto</label>
			<div class="col-lg-11">
				<textarea tabindex=5 id=contenuto name=contenuto class="form-control resize vld_req" style="max-width: 100%;" rows=15% ><?php echo $contenuto; ?></textarea>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-3 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Data inserimento</label>
			<div class="col-lg-8">
				<input tabindex=6 class="picker form-control resize text_center vld_date readonly" style="background-color: rgb(153, 204, 255); border: 2px solid black;" name=data_inserimento id=data_inserimento value="<?php echo $cls_date->Get_DateNewFormat($data_inserimento,"DB"); ?>" size=9>
			</div>
		</div>
	</div>
	<div class="col col-lg-3 col-lg-offset-4">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Data ispezione</label>
			<div class="col-lg-8">
				<input tabindex=6 onblur="focusCampo();" class="picker form-control text_center resize vld_date" name=data_ispezione id=data_ispezione value="<?php echo $cls_date->Get_DateNewFormat($data_ispezione,"DB"); ?>" size=9>
			</div>
		</div>
	</div>
</div>

    <div class="form-group">
        <button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
    </div>
</form>

<script tyepe="text/javascript">
$( document ).ready(function() {

	$('#ordinamento').val('<?= $ordinamento; ?>');

	if( "<?= $num_ricerche; ?>" != 0 && "<?= $p; ?>" !=0 && "<?= $nuova_ispezione; ?>" != 1)
	{
		$('#tipo').val('<?= $tipo; ?>');
	}

});
</script>

<?php include(INC."/footer.php"); ?>
