<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
include(INC."/menu.php");
include_once(CLS."/cls_paramUtils.php");

$cls_help = new cls_help();
$cls_param = new cls_param();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');
$id_filiale = $cls_help->getVar('id_filiale');

$QUERY = $cls_param->Get_Query_Banca($id_filiale, "*****");

$a_param = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($QUERY["query"]),"banca");
$a_paramNextF = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($QUERY["next_F"]),"banca");
$a_paramPrevF = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($QUERY["prev_F"]),"banca");

$id_filiale = $a_param["ID"];
$denominazione_filiale = $a_param["Denominazione"];
$int = $a_param["Interno"];
$civ = $a_param["Civico"];
$forma_giuridica = $a_param["Forma_Giuridica"];

if( $int==0 ) $int="";
if( $civ==0 ) $civ="";

if($id_filiale>0)
	$id_sede = $a_param["ID_Collegamento"];
else
	$id_sede = $cls_help->getVar('id_sede');

	$QUERY_SEDE = $cls_param->Get_Query_Banca($id_sede, "*****");

	$a_param_sede = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($QUERY_SEDE["query"]),"banca");

$id_sede = $a_param_sede["ID"];


$forma_giuridica_sede = $a_param_sede["Forma_Giuridica"];

if($id_filiale<=0){
	$denominazione_filiale = $a_param_sede["Denominazione"];

}


function options_selezione( $array )
{
	$options = "";
	for($i=0;$i<count($array);$i++)
	{
		$options.= "<option value='".$array[$i]['ID']."'>".$array[$i]['Sigla']." - ".$array[$i]['Descrizione']."</option>";
	}

	return $options;
}

$options_individuale = options_selezione($cls_db->getResults($cls_db->ExecuteQuery($QUERY["I"])));
$options_persone = options_selezione($cls_db->getResults($cls_db->ExecuteQuery($QUERY["P"])));
$options_capitale = options_selezione($cls_db->getResults($cls_db->ExecuteQuery($QUERY["Cap"])));
$options_cooperativa = options_selezione($cls_db->getResults($cls_db->ExecuteQuery($QUERY["Coop"])));
$options_consortile = options_selezione($cls_db->getResults($cls_db->ExecuteQuery($QUERY["Cons"])));
$options_ente = options_selezione($cls_db->getResults($cls_db->ExecuteQuery($QUERY["Ente"])));

?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>

//F3
switchMenuImg("F3");
F3_button = function()
{
	if(control_cap==1)
	{
		alert('Filiale gia inserita per questo CAP. Modificare il CAP o cambiare comune.');
		return false;
	}

	/*campi = controllaCampi();
	if(campi)
	{*/
		control_salva = submit_buttons('Salva');
		if(control_salva && validateForm())
				$("#btnSub").trigger("click");
	//}
}


//F4
switchMenuImg("F4");
F4_button = function()
{
	control_salva = submit_buttons('Delete');
	if(control_salva)
			$("#btnSub").trigger("click");
}

//F5
switchMenuImg("F5");
F5_button = function()
{
	stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	stringa = "filiale.php?"+stringaPHP;
	   	top.location.href = stringa;
}


var next_tipo = "<?php echo $a_paramNextF["ID"]; ?>";
var prev_tipo = "<?php echo $a_paramPrevF["ID"]; ?>";

//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){
	if( modifica == 0 )
 {
	 location.href = "filiale.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&id_filiale="+next_tipo;
 }
 else
	 alert("salvare i dati o annullare prima di procedere");
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){
	if( modifica == 0 )
	{
		location.href = "filiale.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&id_filiale="+prev_tipo;
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}


//F11-F12 sono nel menu'


//******************************\\
//ALTRI LINK / FUNZIONI CHIAMATE\\

</script>

<!-- ********** MODALI AJAX ********** -->
<script>

function Dim_Alert ( sWidth, sHeight )
{
	setupPagina = "dialogWidth:" + sWidth + "px";
	setupPagina += "; dialogHeight:" + sHeight + "px";
	setupPagina += ";dialogLeft:80px;dialogTop:80px;";

		return setupPagina;
}

function callParent(valorediritorno){
    switch(selectParent){
        case "comune":

            if( valorediritorno!=null && valorediritorno!=undefined )
            {
                cap = valorediritorno.cap;
                for(var contatore=0;contatore<2;contatore++)
                {
                    cap = cap.replace("x", "0");
                }

                $('#comune_id').val(valorediritorno.comune);
                $('#prov_id').val(valorediritorno.prov_sigla);
                $('#cap_id').val(cap);
                $('#CC_id').val(valorediritorno.CC);

                let event = new Event("change");
                document.getElementById("comune_id").dispatchEvent(event);
                document.getElementById("prov_id").dispatchEvent(event);
            }

            verifica_cap(cap);

            break;
        case "banca":
            if( valorediritorno!=null && valorediritorno!=undefined )
            {
                if(valorediritorno.Tipo_banca == "sede")
                {
                    id_sede = valorediritorno.ID;
                    id_filiale = 0;
                }
                else
                {
                    id_sede = valorediritorno.ID_Collegamento;
                    id_filiale = valorediritorno.ID;
                }

                stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>";
                stringa = "<?= WEB_ROOT;?>/parametri/filiale.php?"+stringaPHP+"&id_sede="+id_sede+"&id_filiale="+id_filiale;
                top.location.href = stringa;
            }

            break;
    }

}

var selectParent = "";
function cerca_comune()
{
    selectParent = "comune";

	var stringa = "<?= WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricComune";

	openWindowSearch(stringa,{width:600, height:400, left:(($(window).width()/2)-300), top:(($(window).height()/2)-200)});
}

var control_cap = 0;
function verifica_cap(cap)
{
	if(cap=="cap")
		cap = $('#cap_id').val();

	$.post("ajax/ajax_parametri.php?c=<?php echo $c; ?>" ,

    	   	{ 'ajax': 'cap_filiale' ,
	   		  'cap': cap,
   			  'ID_sede': '<?php echo $id_sede; ?>' },

			function (value) {

   				var array_ritorno = value.split('**');

   				if(array_ritorno[0]=="presente")
   				{
					alert('Filiale gia inserita per questo CAP. Modificare il CAP o cambiare comune.');
					control_cap = 1;
   	   			}
   				else
   				{
   					control_cap = 0;
   				}

		});
}

function ricerca_banca(value)
{
    selectParent = "banca";

	var stringa = "<?= WEB_ROOT; ?>/search/banche/ricerca_banche.php?richiesta=singola&a=<?php echo $a;?>&c=*****";
	if(value=="filiale")
	{
		stringa+="&tipo=filiale&denominazione="+$('#denom_id').val();
	}
	else if(value=="sede")
	{
		stringa+="&tipo=sede";
	}

	openWindowSearch(stringa,{width:1200, height:400, left:(($(window).width()/2)-600), top:(($(window).height()/2)-200)});

}

function controllaCampi ()
{
	var comune_id = $('#comune_id').val();
	var prov_id = $('#prov_id').val();

	comune_id= obbligatorio(comune_id,"Comune");				if( comune_id!=true )		return false;
	prov_id = obbligatorio(prov_id,"Provincia");			if( prov_id!=true )		return false;

	return true;
}

function cambia_title(value)
{
	testo = $('#'+value+ ' option:selected').text();
	$('#'+value).attr('title',testo);
}

</script>

<div class="row justify-content-md-center " style="margin-bottom: 2%;">
	<div class="col col-md-auto text_center">
			<span class="titolo font16 under_decor">Filiale banca</span>
	</div>
</div>

<form class="form-horizontal validate" name=form_sede id=form_sede method=post action="banca_salva.php">

<input type=hidden name=invia_submit 				value=""	id=invia_submit  	>
<input type=hidden name=id_sede  					value="<?php echo $id_sede; ?>" >
<input type=hidden name=id_filiale id=id_filiale  	value="<?php echo $id_filiale; ?>" >
<input type=hidden name="tipo_banca" value="filiale" >

<input type=hidden name=c 		value=<?php echo $c; ?> >
<input type=hidden name=a 		value=<?php echo $a; ?> >
<input type=hidden name=CC		id=CC_id value="<?php echo $a_param["CC_Sede"]; ?>">

<div class="row">
	<div class="col col-lg-4 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">SEDE</label>
			<div class="col-lg-8">
				<input class="text_left form-control resize vld_req" id=denom_sede_id name=denom_sede value="<?php echo $a_param_sede["Denominazione"]; ?>" >
			</div>
		</div>
	</div>
	<div class="col col-lg-3">
		<div class="form-group">
			<div class="col-lg-12">
				<select id=forma_giuridica_sede class="form-control resize vld_req" name=forma_giuridica_sede onchange="cambia_title('forma_giuridica_sede');">
					<option></option>
					<optgroup label="Impresa individuale"><?php echo $options_individuale; ?></optgroup>
					<optgroup label="Societa' di persone"><?php echo $options_persone; ?></optgroup>
					<optgroup label="Societa' di capitale"><?php echo $options_capitale; ?></optgroup>
					<optgroup label="Societa' cooperativa"><?php echo $options_cooperativa; ?></optgroup>
					<optgroup label="Societa' consortile"><?php echo $options_consortile; ?></optgroup>
					<optgroup label="Ente"><?php echo $options_ente; ?></optgroup>
				</select>
			</div>
		</div>
	</div>
	<div class="col col-lg-2">
		<div class="form-group">
			<div class="col-lg-10 col-lg-offset-2">
				<button class="btn btn-primary form-control resize" type=button id=cerca_banca name=cerca_banca value="Sede / Filiale" onclick="ricerca_banca('');">Sede / Filiale</button>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-4 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Partita Iva *</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_PIReq" id=PI_id name=PI value="<?php echo $a_param_sede["Partita_Iva"]; ?>" >
			</div>
		</div>
	</div>
	<div class="col col-lg-4">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Codice Fiscale</label>
			<div class="col-lg-8">
				<input class="form-control resize vld_CF" id=CF_id name=CF value="<?php echo $a_param_sede["Codice_Fiscale"]; ?>" >
			</div>
		</div>
	</div>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%;"></div>

<?php if(is_numeric($id_sede)): ?>

	<div class="row">
		<div class="col col-lg-5 col-lg-offset-1">
			<div class="form-group">
				<label class="col-lg-2 control-label resize" style="text-align: left;">FILIALE</label>
				<div class="col-lg-10">
					<input class="form-control resize vld_req" style="width: 95.5%;float:right;" id=denom_id name=denom value="<?php echo $denominazione_filiale; ?>" >
				</div>
			</div>
		</div>
		<div class="col col-lg-3">
			<div class="form-group">
				<div class="col-lg-12">
					<select id=forma_giuridica class="form-control resize vld_req" name=forma_giuridica onchange="cambia_title('forma_giuridica');">
						<option></option>
						<optgroup label="Impresa individuale"><?php echo $options_individuale; ?></optgroup>
						<optgroup label="Societa' di persone"><?php echo $options_persone; ?></optgroup>
						<optgroup label="Societa' di capitale"><?php echo $options_capitale; ?></optgroup>
						<optgroup label="Societa' cooperativa"><?php echo $options_cooperativa; ?></optgroup>
						<optgroup label="Societa' consortile"><?php echo $options_consortile; ?></optgroup>
						<optgroup label="Ente"><?php echo $options_ente; ?></optgroup>
					</select>
				</div>
			</div>
		</div>
		<div class="col col-lg-2">
			<div class="form-group">
				<div class="col-lg-10 col-lg-offset-2">
					<button class="btn btn-primary form-control resize" type=button id=cerca_banca name=cerca_banca value="Filiale" onclick="ricerca_banca('filiale');">Filiale</button>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col col-lg-3 col-lg-offset-1">
			<div class="form-group">
				<label class="col-lg-4 control-label resize" style="text-align: left;">Comune *</label>
				<div class="col-lg-8">
					<input class="form-control resize validateCustom vld_Custom_r" style="background-color: rgb(153, 204, 255); border: 2px solid black;" readonly tabindex=1 name=comune id=comune_id value="<?php echo $a_param["Comune"]; ?>" onclick="cerca_comune('tribunale');">
				</div>
			</div>
		</div>
		<div class="col col-lg-2">
			<div class="form-group">
				<label class="col-lg-4 control-label resize" style="text-align: left;">Prov *</label>
				<div class="col-lg-8">
					<input class="form-control resize validateCustom vld_Custom_r" style="background-color: #97CFDD; border: 2px solid black; width: 50%;" readonly tabindex=2 id=prov_id name=prov value="<?php echo  $a_param["Provincia"]; ?>">
				</div>
			</div>
		</div>
		<div class="col col-lg-2">
			<div class="form-group">
				<label class="col-lg-4 control-label resize" style="text-align: left;">CAP</label>
				<div class="col-lg-8">
					<input class="form-control resize vld_int" tabindex=3 id=cap_id name=cap size=4 value="<?php echo  $a_param["Cap"]; ?>" onchange="verifica_cap('cap');">
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col col-lg-3 col-lg-offset-1">
			<div class="form-group">
				<label class="col-lg-4 control-label resize" style="text-align: left;">Indirizzo *</label>
				<div class="col-lg-8">
					<input id=via class="form-control resize vld_req" name=via type=text value="<?php echo $a_param["Toponimo"]; ?>" tabindex=5>
				</div>
			</div>
		</div>
		<div class="col col-lg-2">
			<div class="form-group">
				<label class="col-lg-4 control-label resize" style="text-align: left;">Civ *</label>
				<div class="col-lg-8">
					<input type="text" id=civico class="form-control resize vld_intReq" style="width:80%;" name="civico" value="<?php echo $civ; ?>" size=2 tabindex=6>
				</div>
			</div>
		</div>
		<div class="col col-lg-1">
			<div class="form-group">
				<label class="col-lg-4 control-label resize" style="text-align: left;">Esp.</label>
				<div class="col-lg-8">
					<input type="text" id=esponente class="form-control resize vld_esp" name="esponente" value="<?php echo $a_param["Esponente"]; ?>" size=2 tabindex=7>
				</div>
			</div>
		</div>
		<div class="col col-lg-1">
			<div class="form-group">
				<label class="col-lg-4 control-label resize" style="text-align: left;">Int.*</label>
				<div class="col-lg-8">
					<input type="text" id=interno class="form-control resize vld_intReq" name="interno" value="<?php echo $int; ?>" size=2 tabindex=8>
				</div>
			</div>
		</div>
		<div class="col col-lg-3">
			<div class="form-group">
				<label class="col-lg-4 control-label resize" style="text-align: left;">Dettagli</label>
				<div class="col-lg-8">
					<input type="text" id=dettagli class="form-control resize" name="dettagli" value="<?php echo $a_param["Dettagli"]; ?>" tabindex=9>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col col-lg-3 col-lg-offset-1">
			<div class="form-group">
				<label class="col-lg-4 control-label resize" style="text-align: left;">Telefono</label>
				<div class="col-lg-8">
					<input class="form-control resize vld_tel" id=tel_id name=tel value="<?php echo $a_param["Telefono"]; ?>" tabindex=10>
				</div>
			</div>
		</div>
		<div class="col col-lg-3">
			<div class="form-group">
				<label class="col-lg-4 control-label resize" style="text-align: left;">Fax</label>
				<div class="col-lg-8">
					<input class="form-control resize vld_tel" id=fax_id name=fax value="<?php echo $a_param["Fax"]; ?>" tabindex=11>
				</div>
			</div>
		</div>
		<div class="col col-lg-4">
			<div class="form-group">
				<label class="col-lg-4 control-label resize" style="text-align: left;">Sito</label>
				<div class="col-lg-8">
					<input class="form-control resize vld_Sito" id=sito_id name=sito value="<?php echo $a_param["Sito"]; ?>" tabindex=12>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col col-lg-3 col-lg-offset-1">
			<div class="form-group">
				<label class="col-lg-4 control-label resize" style="text-align: left;">Email</label>
				<div class="col-lg-8">
					<input class="form-control resize vld_email" id=email_id name=email value="<?php echo $a_param["Mail"]; ?>" tabindex=13>
				</div>
			</div>
		</div>
		<div class="col col-lg-3">
			<div class="form-group">
				<label class="col-lg-4 control-label resize" style="text-align: left;">PEC</label>
				<div class="col-lg-8">
					<input title="Inserimento PEC obbligatorio" class="form-control resize vld_email" id=pec_id name=PEC value="<?php echo $a_param["PEC"]; ?>" tabindex=14>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col col-lg-10 col-lg-offset-1">
			<div class="form-group">
				<label class="col-lg-1 control-label resize" style="text-align: left;">Orario</label>
				<div class="col-lg-11">
					<textarea class="form-control resize" style="max-width: 100%;" id=orario_id name=orario rows=3><?php echo $a_param["Orario"]; ?></textarea>
				</div>
			</div>
		</div>
	</div>

<?php else : ?>

	<div class="row justify-content-md-center " style="margin-bottom: 2%;">
		<div class="col col-md-auto text_center">
				<span style="color: red;"><b>Selezionare una sede per poter inserire/modificare una filiale.</b></span>
		</div>
	</div>

<?php endif; ?>

<div class="form-group">
	<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
</div>

</form>


<script type="text/javascript">
$( window ).load(function() {

	$('#forma_giuridica').val('<?php echo $forma_giuridica;?>');cambia_title('forma_giuridica');
	$('#forma_giuridica_sede').val('<?php echo $forma_giuridica_sede; ?>');cambia_title('forma_giuridica_sede');;

	if("<?php echo $id_filiale; ?>"<=0){
		$('#forma_giuridica').val('<?php echo $forma_giuridica_sede; ?>');cambia_title('forma_giuridica');
	}

});
</script>

<?php include(INC."/footer.php"); ?>
