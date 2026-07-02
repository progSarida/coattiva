<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
include_once(CLS."/cls_DateTime.php");
include_once(CLS."/cls_paramUtils.php");

$cls_param = new cls_param();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');
$tipo_riscossione = $cls_help->getVar('tipo_riscossione');

if($tipo_riscossione=="CDS")
	$titolo_riscossione = $tipo_riscossione."/AMMINISTRATIVA";
else
	$titolo_riscossione = $tipo_riscossione;

$tipo_documento = $cls_help->getVar('tipo_documento');

$nome_com = $a_enteAdmin["Denominazione"];

if($a_enteAdmin["Gestore_ID"]==0)
	$disabled = "disabled";
else
	$disabled = "";

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$a_para_Pag = $cls_db->getArrayLine($cls_db->ExecuteQuery($cls_param->Get_Query_Pagamento($c , $tipo_riscossione )));

$par_id = $a_para_Pag["ID"];
if($par_id==null) $par_id = 0;


$dataCambioConto = new cls_DateTime($a_para_Pag["Data_Cambio_Conto"],"DB");
$dataAutorizzazione1 = new cls_DateTime($a_para_Pag["Data_Autorizzazione_1"],"DB");
$dataAutorizzazione2 = new cls_DateTime($a_para_Pag["Data_Autorizzazione_2"],"DB");

$info1 = "SELEZIONE STEMMA DA VISUALIZZARE SUL BOLLETTINO PRINCIPALE\n\n";
$info2 = "SELEZIONE STEMMA DA VISUALIZZARE SUL BOLLETTINO SECONDARIO\n\n";

$info= "AUTOMATICO - se il gestore e' inserito nei parametri viene visualizzato lo stemma del gestore altrimenti lo stemma dell'ente.\n";
$info.= "ENTE - viene visualizzato lo stemma dell'ente gestito.\n";
$info.= "GESTORE - viene visualizzato lo stemma del gestore concessionario.\n";
$info.= "NESSUNO - non viene visualizzato alcuno stemma.";

$info1.= $info;
$info2.= $info;

$Sanzione = "";
$stringa = "";

if($a_para_Pag["ID"] != null)
{
	$stringa = $cls_param->gestione_conto_terzi($dataCambioConto->GetDate("IT"),$a_para_Pag["Conto_Terzi"]);
	$Sanzione = $a_para_Pag["Scadenza_Sanzione"];
}

?>

<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>

<!-- ********** CALENDARIO ********** -->
<script>

$(function() {

	 $( ".picker" ).datepicker();

	 });

</script>

<script>

function control_importo(bollettino, atto)
{
	if(bollettino==1){
		td_bollettino = $('input[name=tipo_bollettino]:radio:checked').val();
		if(atto==1)
			importo = $('[name=importo]:checked');
		else
			importo = $('[name=importo_pigno]:checked');
	}
	else{
		td_bollettino = $('input[name=tipo_bollettino_'+bollettino+']:radio:checked').val();
		if(atto==1)
			importo = $('[name=importo_'+bollettino+']:checked');
		else
			importo = $('[name=importo_'+bollettino+'_pigno]:checked');
	}

	messaggio = "";
	messaggio_2 = "";

	if(td_bollettino=="123"){
		messaggio = "TD 123: Attenzione e' stata selezionata la visualizzazione dell'importo per il bollettino";
		messaggio_2 = "Con questo tipo di bollettino non e' possibile prestampare l'importo ne avere l'autorizzazione da parte delle poste.";
	}
	else if(td_bollettino=="451"){
		messaggio = "TD 451: Attenzione e' stata selezionata la visualizzazione dell'importo per il bollettino";
		messaggio_2 = "In questo modello non e' previsto l'importo prestampato.";
	}
	else if(td_bollettino == "896"){
		importo.prop('checked',true);
	}

	if(importo.val()=="si")
	{
		if(bollettino==1){
			if(atto==1)
				messaggio_TD = messaggio+" PRINCIPALE degli ATTI.\n"+messaggio_2;
			else
				messaggio_TD = messaggio+" PRINCIPALE del PIGNORAMENTO.\n"+messaggio_2;
		}
		else{
			if(atto==1)
				messaggio_TD = messaggio+" SECONDARIO degli ATTI.\n"+messaggio_2;
			else
				messaggio_TD = messaggio+" SECONDARIO del PIGNORAMENTO.\n"+messaggio_2;
		}

		if(messaggio!="")
			alert(messaggio_TD);
	}
}

function control_autorizzazione(td_bollettino, control, data)
{
	if(td_bollettino!="123" && td_bollettino!="")
	{
		if(control.val()=="")
		{
			alert("Inserire un'autorizzazione per il bollettino selezionato.");
			control.select();
			return false;
		}
		else if(data.val()=="")
		{
			alert("Inserire la data di autorizzazione per il bollettino selezionato.");
			data.select();
			return false;
		}
	}

	return true;
}

function control_bollettino(value)
{
	if(value==1)
	{
		td_bollettino = $('input[name=tipo_bollettino]:radio:checked').val();

		if(td_bollettino == "451" || td_bollettino == "674" || td_bollettino == "896")
		{
			$("#data_aut").addClass("validateCustom vld_Custom_r vld_Custom_date");
			$("#aut").addClass("validateCustom vld_Custom_r");
		}
		else{
			$("#data_aut").removeClass("validateCustom vld_Custom_r vld_Custom_date");
			$("#aut").removeClass("validateCustom vld_Custom_r");
		}
	}
	else
	{
		td_bollettino = $('input[name=tipo_bollettino_'+value+']:radio:checked').val();

		if(td_bollettino == "451" || td_bollettino == "674" || td_bollettino == "896")
		{
			$("#data_aut_2").addClass("validateCustom vld_Custom_r vld_Custom_date");
			$("#aut_2").addClass("validateCustom vld_Custom_r");
		}
		else{
			$("#data_aut_2").removeClass("validateCustom vld_Custom_r vld_Custom_date");
			$("#aut_2").removeClass("validateCustom vld_Custom_r");
		}
	}


	if(value==1)
	{
		control_importo(1, 1);
		control_importo(1, 2);
	}
	else
	{
		control_importo(2, 1);
		control_importo(2, 2);
	}

	return true;
}

</script>

<script>

function IBANChk(value)
{
	b = $('#iban_conto').val();

	if(b.length == 0)
		return true;

    if (b.length < 27 && value=="IT") { alert("La lunghezza dell'IBAN � minore di 27 caratteri"); return false; }
    else if(b.length < 5) { alert("La lunghezza dell'IBAN � minore di 5 caratteri"); return false; }

    s = b.substring(4) + b.substring(0, 4);
    for (i = 0, r = 0; i < s.length; i++ )
    {
        c = s.charCodeAt(i);
    	if (48 <= c && c <= 57)
        {
        	if (i == s.length-4 || i == s.length-3)
            {
                alert("Posizioni 1 e 2 dell'IBAN non possono contenere cifre");
                return false;
            }
            k = c - 48;
        }
        else if (65 <= c && c <= 90)
        {
        	if (value=="IT" && (( i == s.length-4 && c!=73) || (i == s.length-3 && c!=84)))
            {
                alert("L'IBAN Italiano deve iniziare con 'IT'");
                return false;
            }

            if (i == s.length-2 || i == s.length-1)
            {
                alert("Posizioni 3 e 4 dell'IBAN non possono contenere lettere");
                return false;
            }
            k = c - 55;
        }
        else
		{
    		alert("Nell'IBAN sono ammesse solo cifre e lettere maiuscole");
    		return false;
    	}
        if (k > 9)
            r = (100 * r + k) % 97;
        else
            r = (10 * r + k) % 97;
    }
    if (r != 1) { alert("Il codice di controllo dell'IBAN � errato"); return false; }

    return true;
}

</script>

<?php include(INC."/menu.php"); ?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>

//F3
switchMenuImg("F3");
F3_button = function()
{

	control = submit_buttons('Salva');
	if(control && validateForm()) {
		$("#submitButton").trigger("click");
	}
}

//F4
switchMenuImg("F4");
F4_button = function()
{
	control = submit_buttons('Delete');
	if(control) $("#submitButton").trigger("click");
}

//F5
switchMenuImg("F5");
F5_button = function()
{
	location.href="par_pagamento.php?tipo_riscossione=<?php echo $tipo_riscossione; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){
	if( modifica == 0 )
 {
	 location.href = "par_email.php?tipo_riscossione=<?php echo $tipo_riscossione; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
 }
 else
	 alert("salvare i dati o annullare prima di procedere");
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){
	if( modifica == 0 )
	{
		location.href =  "par_responsabili.php?tipo_riscossione=<?php echo $tipo_riscossione; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//F11-F12 sono nel menu'

</script>



<div class="row justify-content-md-center ">
	<div class="col col-md-auto text_center">
			<p class="titolo font16 under_decor">Parametri pagamento (<?php echo $titolo_riscossione; ?>)</p>
	</div>
</div>

<form class="form-horizontal validate" name=form_par_pagamento id=form_par_pagamento method=post action="par_pagamento_salva.php">

<input type="submit" class="submit" class="form-control" id="submitButton" style="display: none;">
<input type=hidden name=invia_submit id=invia_submit value="" >

<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >
<input type=hidden name=tipo_documento value=<?php echo $tipo_documento; ?> >
<input type=hidden name=tipo_riscossione value=<?php echo $tipo_riscossione; ?> >

<input type=hidden name=par_id 	id=par_id 	value="<?php echo $par_id; ?>"   	>


<div class="row justify-content-md-center ">
	<div class="col col-md-auto text_center">
			<b>Conto corrente</b>
	</div>
</div>
<div class="row" style="margin-top: 3%;">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-1 control-label resize text_left">Intestatario</label>
			<div class="col-lg-10 col-lg-offset-1">
					<input class="form-control vld_req resize" name=int_conto id=int_conto value="<?php echo $a_para_Pag["Intestatario_Conto"]; ?>">
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-3 control-label resize" style="text-align: left;">Tipo</label>
			<div class="col-lg-7 col-lg-offset-1">
				<select class="form-control vld_req resize" name=tipo_conto id=tipo_conto style="width: 60%;">
					<option></option>
					<option>Poste Italiane</option>
					<option>Banca</option>
				</select>
			</div>
		</div>
	</div>
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-3 control-label resize">Numero conto</label>
			<div class="col-lg-7 col-lg-offset-1">
					<input style="width: 78%;" class="form-control vld_req resize" name=num_conto id=num_conto	value="<?php echo $a_para_Pag["Numero_Conto"]; ?>" >
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-3 control-label resize" style="text-align: left;">IBAN</label>
			<div class="col-lg-7 col-lg-offset-1">
				<input class="form-control vld_req resize" style="width: 100%;" name=iban_conto id=iban_conto	value="<?php echo $a_para_Pag["IBAN"]; ?>" onchange="IBANChk('IT');">
			</div>
		</div>
	</div>
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-3 control-label resize">BIC/SWIFT</label>
			<div class="col-lg-7 col-lg-offset-1">
					<input style="width: 78%;" class="form-control vld_req resize" name=bic_conto id=bic_conto	value="<?php echo $a_para_Pag["BICSWIFT"]; ?>" >
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-3 control-label resize" style="text-align: left;">Conto terzi</label>
			<div class="col-lg-7 col-lg-offset-1">
				<select class="form-control vld_req resize" name=conto_terzi id=conto_terzi style="width: 40%;">
					<option value="no">No</option>
					<option value="si">Si</option>
				</select>
			</div>
		</div>
	</div>
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-3 control-label resize">Data cambio</label>
			<div class="col-lg-7 col-lg-offset-1">
					<input style="width: 78%;" class="form-control vld_dateReq resize picker" type="text" name=data_cambio id=data_cambio value="<?php echo $dataCambioConto->GetDate("IT"); ?>" >
			</div>
		</div>
	</div>
</div>
<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;"></div>
<div class="row justify-content-md-center " style="margin-top: 2%;">
	<div class="col col-md-auto text_center">
			<b>Bollettino</b>
	</div>
</div>

<div class="row" style="margin-top: 2%;">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-2 control-label resize" style="text-align: left;">PRINCIPALE</label>
			<div class="col-lg-8 resize">
				<input type="radio" name=tipo_bollettino value="" onclick="control_bollettino(1);" checked> Nessuno
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" name=tipo_bollettino value=123 onclick="control_bollettino(1);"> TD 123
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" name=tipo_bollettino value=451 onclick="control_bollettino(1);"> TD 451
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" name=tipo_bollettino value=674 onclick="control_bollettino(1);"> TD 674
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" name=tipo_bollettino value=896 onclick="control_bollettino(1);"> TD 896
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-3 control-label resize" style="text-align: left;">Autorizzazione</label>
			<div class="col-lg-7 col-lg-offset-1">
				<input class="form-control resize" style="width: 80%;" type="text" name=aut id=aut value="<?php echo $a_para_Pag["Autorizzazione_1"]; ?>">
			</div>
		</div>
	</div>
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-3 control-label resize">Data</label>
			<div class="col-lg-7 col-lg-offset-1">
				<input style="width: 78%;" class="form-control resize picker" type="text" name=data_aut id=data_aut value="<?php echo $dataAutorizzazione1->GetDate("IT"); ?>" >
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-2 control-label resize" style="text-align: left;">Importo</label>
			<div class="col-lg-4 resize text_left resize">
				<input type="radio" id=no_importo name=importo value="no" checked> Non stampato
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" id=si_importo name=importo value="si" onchange="control_importo(1,1);"> Stampato
			</div>
			<div class="col-lg-4 text_left resize"><b>[ Atti ]</b></div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-2 control-label resize" style="text-align: left;">Importo</label>
			<div class="col-lg-4 resize text_left resize">
				<input type="radio" id=no_importo_pigno name=importo_pigno value="no" checked> Non stampato
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" id=si_importo_pigno name=importo_pigno value="si" onchange="control_importo(1,2);"> Stampato
			</div>
			<div class="col-lg-4 text_left resize"><b>[ Pignoramenti ]</b></div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-2 control-label resize" style="text-align: left;">Stemma<img title="<?php echo $info1;?>" src="/gitco2/immagini/info.png" width=18 height=18 border=0></label>
			<div class="col-lg-8 resize text_left">
				<input type="radio" name=stemma value="" checked> Automatico
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" name=stemma value="ente" > Ente
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" name=stemma value="gestore" <?php echo $disabled; ?>> Gestore
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" name=stemma value="nessuno" > Nessuno
			</div>
		</div>
	</div>
</div>
<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;"></div>
<div class="row" style="margin-top: 2%;">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-2 control-label resize" style="text-align: left;">SECONDARIO</label>
			<div class="col-lg-8 resize">
				<input type="radio" name=tipo_bollettino_2 value="" onclick="control_bollettino(2);" checked> Nessuno
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" name=tipo_bollettino_2 value=123 onclick="control_bollettino(2);"> TD 123
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" name=tipo_bollettino_2 value=451 onclick="control_bollettino(2);"> TD 451
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" name=tipo_bollettino_2 value=674 onclick="control_bollettino(2);"> TD 674
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" name=tipo_bollettino_2 value=896 onclick="control_bollettino(2);"> TD 896
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-3 control-label resize" style="text-align: left;">Autorizzazione</label>
			<div class="col-lg-7 col-lg-offset-1">
				<input class="form-control resize" style="width: 80%;" type="text" name=aut_2 id=aut_2 value="<?php echo $a_para_Pag["Autorizzazione_2"]; ?>">
			</div>
		</div>
	</div>
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-3 control-label resize">Data</label>
			<div class="col-lg-7 col-lg-offset-1">
				<input style="width: 78%;" class="form-control resize picker" type="text" name=data_aut_2 id=data_aut_2 value="<?php echo $dataAutorizzazione2->GetDate("IT"); ?>" size=9>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-2 control-label resize" style="text-align: left;">Importo 2</label>
			<div class="col-lg-4 resize text_left resize">
				<input type="radio" id=no_importo_2 name=importo_2 value="no" checked> Non stampato</td>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" id=si_importo_2 name=importo_2 value="si" onchange="control_importo(2,1);"> Stampato
			</div>
			<div class="col-lg-4 text_left resize"><b>[ Atti ]</b></div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-2 control-label resize" style="text-align: left;">Importo 2</label>
			<div class="col-lg-4 resize text_left resize">
				<input type="radio" id=no_importo_pigno_2 name=importo_2_pigno value="no" checked> Non stampato
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" id=si_importo_pigno_2 name=importo_2_pigno value="si" onchange="control_importo(2,2);"> Stampato
			</div>
			<div class="col-lg-4 text_left resize"><b>[ Pignoramenti ]</b></div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-2 control-label resize" style="text-align: left;">Stemma 2<img title="<?php echo $info2;?>" src="/gitco2/immagini/info.png" width=18 height=18 border=0></label>
			<div class="col-lg-8 resize text_left">
				<input type="radio" name=stemma_2 value="" checked> Automatico
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" name=stemma_2 value="ente" > Ente
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" name=stemma_2 value="gestore" <?php echo $disabled; ?>> Gestore
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" name=stemma_2 value="nessuno" > Nessuno
			</div>
		</div>
	</div>
</div>

<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;"></div>

<div class="row justify-content-md-center " style="margin-top: 2%;">
	<div class="col col-md-auto text_center">
			<b>Termini di pagamento</b>
	</div>
</div>
<div class="row" style="margin-top: 2%;">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-5 control-label resize" style="text-align: left;">Scadenza sanzione originaria</label>
			<div class="col-lg-3 ">
				<input class="form-control vld_intReq resize" style="width: 30%" type="text" name=scadenza_sanzione id=scadenza_sanzione value="<?php echo $Sanzione; ?>" >
			</div>
			<div class="col-lg-4 resize">giorni dalla data di notifica dell'atto </div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-5 control-label resize" style="text-align: left;">Scadenza Ingiunzione</label>
			<div class="col-lg-3 ">
				<input class="form-control vld_intReq resize" style="width: 30%" type="text" name=scadenza_ingiunzione id=scadenza_ingiunzione value="<?php echo $a_para_Pag["Scadenza_Ingiunzione"]; ?>" >
			</div>
			<div class="col-lg-4 resize">giorni dalla data di notifica dell'atto </div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-5 control-label resize" style="text-align: left;">Scadenza Avviso di intimazione ad adempiere</label>
			<div class="col-lg-3 ">
				<input class="form-control vld_intReq resize" style="width: 30%" type="text" name=scadenza_avviso id=scadenza_avviso value="<?php echo $a_para_Pag["Scadenza_Avviso"]; ?>" >
			</div>
			<div class="col-lg-4 resize">giorni dalla data di notifica dell'atto </div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col col-lg-10 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-5 control-label resize" style="text-align: left;">Scadenza Pignoramento</label>
			<div class="col-lg-3 ">
				<input class="form-control vld_intReq resize" style="width: 30%" type="text" name=scadenza_pignoramento id=scadenza_pignoramento value="<?php echo $a_para_Pag["Scadenza_Pignoramento"]; ?>" >
			</div>
			<div class="col-lg-4 resize">giorni dalla data di notifica dell'atto </div>
		</div>
	</div>
</div>

</form>

<script type="text/javascript">

	$( window ).load(function() {

		$('#tipo_conto').val("<?php echo $a_para_Pag["Tipo_Conto"]; ?>");
		$('[name=stemma][value="<?php echo $a_para_Pag["Stemma"]; ?>"]').prop('checked',true);
		$('[name=stemma_2][value="<?php echo $a_para_Pag["Stemma_2"]; ?>"]').prop('checked',true);
		$('#conto_terzi').val("<?php echo $a_para_Pag["Conto_Terzi"]; ?>");

		$('[name=tipo_bollettino][value="<?php echo $a_para_Pag["Bollettino_1"]; ?>"]').prop('checked',true);
		$('[name=tipo_bollettino_2][value="<?php echo $a_para_Pag["Bollettino_2"]; ?>"]').prop('checked',true);
		$('[name=importo][value="<?php echo $a_para_Pag["Importo_1"]; ?>"]').prop('checked',true);
		$('[name=importo_2][value="<?php echo$a_para_Pag["Importo_2"]; ?>"]').prop('checked',true);

		$('[name=tipo_bollettino_pigno][value="<?php echo $a_para_Pag["Bollettino_1_Pignoramento"]; ?>"]').prop('checked',true);
		$('[name=tipo_bollettino_2_pigno][value="<?php echo $a_para_Pag["Bollettino_2_Pignoramento"]; ?>"]').prop('checked',true);
		$('[name=importo_pigno][value="<?php echo $a_para_Pag["Importo_1_Pignoramento"]; ?>"]').prop('checked',true);
		$('[name=importo_2_pigno][value="<?php echo $a_para_Pag["Importo_2_Pignoramento"]; ?>"]').prop('checked',true);


		var td_bollettino_iniz = $('input[name=tipo_bollettino]:radio:checked').val();

		if(td_bollettino_iniz == "451" || td_bollettino_iniz == "674" || td_bollettino_iniz == "896")
		{
			$("#data_aut").addClass("validateCustom vld_Custom_r vld_Custom_date");
			$("#aut").addClass("validateCustom vld_Custom_r");
		}

		td_bollettino_iniz = $('input[name=tipo_bollettino_2]:radio:checked').val();

		if(td_bollettino_iniz == "451" || td_bollettino_iniz == "674" || td_bollettino_iniz == "896")
		{
			$("#data_aut_2").addClass("validateCustom vld_Custom_r vld_Custom_date");
			$("#aut_2").addClass("validateCustom vld_Custom_r");
		}

	});
</script>s

<?php include(INC."/footer.php"); ?>
