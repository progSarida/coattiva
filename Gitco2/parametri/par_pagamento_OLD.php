<?php
include_once($_SERVER['DOCUMENT_ROOT']."/gitco2/_path.php");
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
		control = $('#aut');
		data = $('#data_aut');
	}
	else
	{
		td_bollettino = $('input[name=tipo_bollettino_'+value+']:radio:checked').val();
		control = $('#aut_'+value);
		data = $('#data_aut_'+value);
	}

	autorizzazione = control_autorizzazione(td_bollettino, control, data);
	if(autorizzazione === false)
		return false;

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
	control_1 = control_bollettino(1);
	if(!control_1){
		return false;
	}

	control_2 = control_bollettino(2);
	if(!control_2){
		return false;
	}

	control = submit_buttons('Salva');
	validateForm();
	if(control) $("#submitButton").trigger("click");//document.form_par_pagamento.submit();
	    //valida();
}

//F4
switchMenuImg("F4");
F4_button = function()
{
	control = submit_buttons('Delete');
	if(control) $("#submitButton").trigger("click");
	    //valida();
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


<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><font class="titolo font16 under_decor">Parametri pagamento (<?php echo $titolo_riscossione; ?>)</font></td>
	</tr>
</table>
<br>

<form name=form_par_pagamento id=form_par_pagamento method=post action="par_pagamento_salva.php">

<input type="submit" class="submit" class="form-control" id="submitButton" style="display: none;">
<input type=hidden name=invia_submit id=invia_submit value="" >

<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >
<input type=hidden name=tipo_documento value=<?php echo $tipo_documento; ?> >
<input type=hidden name=tipo_riscossione value=<?php echo $tipo_riscossione; ?> >

<input type=hidden name=par_id 	id=par_id 	value="<?php echo $par_id; ?>"   	>

<table class="table_interna text_center" border="0" cellspacing="2" cellpadding="0">
	<tr>
		<td class="text_center" colspan=3><b>Conto corrente</b></td>
	</tr>
</table>
<table class="table_interna text_center" border="0" cellspacing="4" cellpadding="0">
	<tr>
		<td class="text_center" colspan=5><hr></td>
	</tr>
	<tr>
		<td class="text_left width15">Intestatario</td>
		<td class="text_left width85" colspan=4>
			<input class="width99" name=int_conto id=int_conto value="<?php echo $a_para_Pag["Intestatario_Conto"]; ?>">
		</td>
	</tr>
	<tr>
		<td class="text_left width15">Tipo</td>
		<td class="text_left width45" colspan=2>
			<select name=tipo_conto id=tipo_conto>
				<option></option>
				<option>Poste Italiane</option>
				<option>Banca</option>
			</select>
		</td>
		<td class="text_left width15">Numero conto</td>
		<td class="text_left width25"><input class="width97" name=num_conto id=num_conto	value="<?php echo $a_para_Pag["Numero_Conto"]; ?>" ></td>
	</tr>
	<tr>
		<td class="text_left width15">IBAN</td>
		<td class="text_left width45" colspan=2>
			<input class="width87" name=iban_conto id=iban_conto	value="<?php echo $a_para_Pag["IBAN"]; ?>" onchange="IBANChk('IT');">
		</td>
		<td class="text_left width15">BIC/SWIFT</td>
		<td class="text_left width25"><input class="width97" name=bic_conto id=bic_conto	value="<?php echo $a_para_Pag["BICSWIFT"]; ?>" ></td>
	</tr>
	<tr>
		<td class="text_left width15">Conto terzi</td>
		<td class="text_left width10">
			<select name=conto_terzi id=conto_terzi>
				<option value="no">No</option>
				<option value="si">Si</option>
			</select>
		</td>
		<td class="text_center width35">Data cambio <input class="width35 text_center picker" type="text" name=data_cambio id=data_cambio value="<?php echo $dataCambioConto->GetDate("IT"); ?>" ></td>
		<td class="text_left width40" colspan=2><?php echo $stringa; ?></td>
	</tr>
	<tr>
		<td class="text_center" colspan=5><hr></td>
	</tr>
</table>

<table class="table_interna text_center" border="0" cellspacing="4" cellpadding="0">
	<tr>
		<td class="text_center" colspan=5><b>Bollettino</b></td>
	</tr>
	<tr>
		<td class="text_center" colspan=5><hr></td>
	</tr>
	<tr>
		<td class="text_left width20">PRINCIPALE</td>
		<td class="text_left width80" colspan=4>
			<input type="radio" name=tipo_bollettino value="" onclick="control_bollettino(1);" checked> Nessuno
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name=tipo_bollettino value=123 onclick="control_bollettino(1);"> TD 123
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name=tipo_bollettino value=451 onclick="control_bollettino(1);"> TD 451
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name=tipo_bollettino value=674 onclick="control_bollettino(1);"> TD 674
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name=tipo_bollettino value=896 onclick="control_bollettino(1);"> TD 896
		</td>
	</tr>
	<tr>
		<td class="text_left width20">Autorizzazione</td>
		<td class="text_left width40" colspan=2><input class="width100" type="text" name=aut id=aut value="<?php echo $a_para_Pag["Autorizzazione_1"]; ?>"></td>
		<td class="text_center width20">Data</td>
		<td class="text_left width20"><input class="text_center picker" type="text" name=data_aut id=data_aut value="<?php echo $dataAutorizzazione1->GetDate("IT"); ?>" size=9></td>
	</tr>
	<tr>
		<td class="text_center" colspan=5><hr></td>
	</tr>
	<tr>
		<td class="text_left width20">Importo</td>
		<td class="text_left width20" ><input type="radio" id=no_importo name=importo value="no" checked> Non stampato</td>
		<td class="text_left width20" ><input type="radio" id=si_importo name=importo value="si" onchange="control_importo(1,1);"> Stampato</td>
		<td class="text_left width20"><b>[ Atti ]</b></td>
		<td class="text_right width20"></td>
	</tr>
	<tr>
		<td class="text_left width20">Importo</td>
		<td class="text_left width20" ><input type="radio" id=no_importo_pigno name=importo_pigno value="no" checked> Non stampato</td>
		<td class="text_left width20" ><input type="radio" id=si_importo_pigno name=importo_pigno value="si" onchange="control_importo(1,2);"> Stampato</td>
		<td class="text_left width20"><b>[ Pignoramenti ]</b></td>
		<td class="text_right width20"></td>
	</tr>
	<tr>
		<td class="text_center" colspan=5><hr></td>
	</tr>
	<tr>
		<td class="text_left width20">Stemma
		<img title="<?php echo $info1;?>" src="/gitco2/immagini/info.png" width=18 height=18 border=0>
		</td>
		<td class="text_left width20" ><input type="radio" name=stemma value="" checked> Automatico</td>
		<td class="text_left width20" ><input type="radio" name=stemma value="ente" > Ente</td>
		<td class="text_left width20" ><input type="radio" name=stemma value="gestore" <?php echo $disabled; ?>> Gestore</td>
		<td class="text_left width20" ><input type="radio" name=stemma value="nessuno" > Nessuno</td>
	</tr>
	<tr>
		<td class="text_center" colspan=5><hr></td>
	</tr>
	<tr>
		<td class="text_left width20">SECONDARIO</td>
		<td class="text_left width80" colspan=4>
			<input type="radio" name=tipo_bollettino_2 value="" onclick="control_bollettino(2);" checked> Nessuno
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name=tipo_bollettino_2 value=123 onclick="control_bollettino(2);"> TD 123
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name=tipo_bollettino_2 value=451 onclick="control_bollettino(2);"> TD 451
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name=tipo_bollettino_2 value=674 onclick="control_bollettino(2);"> TD 674
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name=tipo_bollettino_2 value=896 onclick="control_bollettino(2);"> TD 896
		</td>
	</tr>
	<tr>
		<td class="text_left width20">Autorizzazione</td>
		<td class="text_left width40" colspan=2><input class="width100" type="text" name=aut_2 id=aut_2 value="<?php echo $a_para_Pag["Autorizzazione_2"]; ?>"></td>
		<td class="text_center width20">Data</td>
		<td class="text_left width20"><input class="text_center picker" type="text" name=data_aut_2 id=data_aut_2 value="<?php echo $dataAutorizzazione2->GetDate("IT"); ?>" size=9></td>
	</tr>
	<tr>
		<td class="text_center" colspan=5><hr></td>
	</tr>
	<tr>
		<td class="text_left width20">Importo 2</td>
		<td class="text_left width20" ><input type="radio" id=no_importo_2 name=importo_2 value="no" checked> Non stampato</td>
		<td class="text_left width20" ><input type="radio" id=si_importo_2 name=importo_2 value="si" onchange="control_importo(2,1);"> Stampato</td>
		<td class="text_left width20"><b>[ Atti ]</b></td>
		<td class="text_center width20"></td>
	</tr>
	<tr>
		<td class="text_left width20">Importo 2</td>
		<td class="text_left width20" ><input type="radio" id=no_importo_pigno_2 name=importo_2_pigno value="no" checked> Non stampato</td>
		<td class="text_left width20" ><input type="radio" id=si_importo_pigno_2 name=importo_2_pigno value="si" onchange="control_importo(2,2);"> Stampato</td>
		<td class="text_left width20"><b>[ Pignoramenti ]</b></td>
		<td class="text_center width20"></td>
	</tr>
	<tr>
		<td class="text_center" colspan=5><hr></td>
	</tr>
	<tr>
		<td class="text_left width20">
		Stemma 2
		<img title="<?php echo $info2;?>" src="/gitco2/immagini/info.png" width=18 height=18 border=0>

		</td>
		<td class="text_left width20" ><input type="radio" name=stemma_2 value="" checked> Automatico</td>
		<td class="text_left width20" ><input type="radio" name=stemma_2 value="ente" > Ente</td>
		<td class="text_left width20" ><input type="radio" name=stemma_2 value="gestore" <?php echo $disabled; ?>> Gestore</td>
		<td class="text_left width20" ><input type="radio" name=stemma_2 value="nessuno" > Nessuno</td>
	</tr>
	<tr>
		<td class="text_center" colspan=5><hr></td>
	</tr>
</table>

<table id=scadenze_atti class="table_interna text_center" border="0" cellspacing="4" cellpadding="0">
	<tr>
		<td class="text_center" colspan=3><b>Termini di pagamento</b></td>
	</tr>
	<tr>
		<td class="text_center" colspan=3><hr></td>
	</tr>
	<tr>
		<td class="text_left width35">Scadenza sanzione originaria</td>
		<td class="text_center width35">giorni dalla data di notifica dell'atto </td>
		<td class="text_left width30"><input class="text_right validate val_n val_r" size=5 type="text" name=scadenza_sanzione id=scadenza_sanzione value="<?php echo $Sanzione; ?>" ></td>
	</tr>
	<tr>
		<td class="text_left width35">Scadenza Ingiunzione</td>
		<td class="text_center width35">giorni dalla data di notifica dell'atto </td>
			<td class="text_left width30"> <input class="text_right validate val_n val_r" size=5 type="text" name=scadenza_ingiunzione id=scadenza_ingiunzione value="<?php echo $a_para_Pag["Scadenza_Ingiunzione"]; ?>" ></td>
	</tr>
	<tr>
		<td class="text_left width35">Scadenza Avviso di intimazione ad adempiere</td>
		<td class="text_center width35">giorni dalla data di notifica dell'atto </td>
		<td class="text_left width30" id=scadenza_avviso1><input class="text_right validate val_n val_r" size=5 type="text" name=scadenza_avviso id=scadenza_avviso value="<?php echo $a_para_Pag["Scadenza_Avviso"]; ?>" ></td>
	</tr>
	<tr>
		<td class="text_left width35">Scadenza Pignoramento</td>
		<td class="text_center width35"> giorni dalla data di notifica dell'atto </td>
		<td class="text_left width30"><input class="text_right validate val_n val_r" size=5 type="text" name=scadenza_pignoramento id=scadenza_pignoramento value="<?php echo $a_para_Pag["Scadenza_Pignoramento"]; ?>" ></td>
	</tr>
	<tr>
		<td class="text_center" colspan=3><hr></td>
	</tr>
</table>

</form>

<script type="text/javascript">

function validateForm()
{
	//var nome = document.form_par_pagamento;
	$(".error").remove();

	InizializzaAttributi();

	var rec=document.getElementsByClassName('validate');

  for (var i = 0; i<rec.length; i++) {
			if (!rec[i].checkValidity())
			{
				if(rec[i].id=="")
				{
					var br = document.createElement("br");
					var newNode = document.createElement("span");
					newNode.innerHTML = rec[i].validationMessage;
					newNode.style.color = "red";
					newNode.class = "error";

					var parent = rec[i].parentNode;
					parent.appendChild(br);
					parent.appendChild(newNode);
				}
				else $("#"+rec[i].id).after("</br><span class='error' style='color: red;'>"+rec[i].validationMessage+"</span>");

				//parent.insertBefore(newNode,rec[i]);
				//$("#"+rec[i].id).after("</br><span class='error' style='color: red;'>"+rec[i].validationMessage+"</span>");
			}
		}
}

function InizializzaAttributi(){

	$('.val_n').each(function() {
  // verifico che abbiano la classe sponsor
  //if ($(this).is('.sponsor')) {
    $(this).attr('pattern','[0-9]+');
	});

	$('.val_d').each(function() {
			$(this).attr('pattern','[0-9]+[.,]{1}[0-9]{2}');
	});

	$('.val_r').each(function() {
			$(this).attr('required','required');
	});
}

/*$(document).ready(function() {
    $('#form_par_pagamento').bootstrapValidator({
        message: 'This value is not valid',
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
            username: {
                message: 'The username is not valid',
                validators: {
                    notEmpty: {
                        message: 'The username is required and cannot be empty'
                    },
                    stringLength: {
                        min: 6,
                        max: 30,
                        message: 'The username must be more than 6 and less than 30 characters long'
                    },
                    regexp: {
                        regexp: /^[a-zA-Z0-9_]+$/,
                        message: 'The username can only consist of alphabetical, number and underscore'
                    }
                }
            },
            email: {
                validators: {
                    notEmpty: {
                        message: 'The email is required and cannot be empty'
                    },
                    emailAddress: {
                        message: 'The input is not a valid email address'
                    }
                }
            }
        }
    });
});*/

/*$().ready(function() {
    // Selezione form e definizione dei metodi di validazione
    $("#form_par_pagamento").validate({
        // Definiamo le nostre regole di validazione
        rules : {
            // login - nome del campo di input da validare
            scadenza_sanzione : {
              // Definiamo il campo login come obbligatorio
              required : true
            },
            scadenza_ingiunzione : {
                required : true,
                // Definiamo il campo email come un campo di tipo email
                //email : true
            },
            scadenza_avviso : {
                required : true,
                // Definiamo il campo email come un campo di tipo email
                //email : true
            },
            scadenza_pignoramento : {
                required : true,
                // Definiamo il campo email come un campo di tipo email
                //email : true
            }
        },
        // Personalizzimao i mesasggi di errore
        messages: {
            scadenza_sanzione: "<span style='color: red;'>Inserisci la scadenza della sanzione</span>",
            scadenza_ingiunzione: "<span style='color: red;'>Inserisci la scadenza dell'ingiunzione </span>",
						scadenza_avviso: "<span style='color: red;'>Inserisci la scadenza dell'avviso </span>",
						scadenza_pignoramento: "<span style='color: red;'>Inserisci la scadenza del pignoramento </span>"
        },
        // Settiamo il submit handler per la form
        submitHandler: function(form) {
            form.submit();
        }
    });
});*/

/*$('#form_par_pagamento').bootstrapValidator({
            live: 'disabled',
            fields: {
                frm_field_required: {
                    selector: '.frm_field_required',
                    validators: {
                        notEmpty: {
                            message: 'Richiesto'
                        }
                    }
                },

                frm_field_numeric: {
                    selector: '.frm_field_numeric',
                    validators: {
                        numeric: {
                            message: 'Numero'
                        }
                    }
                },

                frm_field_currency: {
                    selector: '.frm_field_currency',
                    validators: {
                        numeric: {
                            message: 'Euro'
                        }
                    }
                },

                frm_field_date: {
                    selector: '.frm_field_date',
                    validators: {
                        date: {
                            format: 'DD/MM/YYYY',
                            message: 'Data non valida'
                        }
                    }
                },
            }
        });*/



/*function valida()
{
	var scad_sanz = document.form_par_pagamento.scadenza_sanzione.value;
	var scad_ing = document.form_par_pagamento.scadenza_ingiunzione.value;
	var scad_avv = document.form_par_pagamento.scadenza_avviso.value;
	var scad_pignor = document.form_par_pagamento.scadenza_pignoramento.value;

	if ((isNaN(scad_sanz)) || (scad_sanz == "") || (scad_sanz == "undefined")) {
			ShowAlert(1,"Devi inserire la scadenza della sanzione, attenzione deve essere numerica!");
      document.form_par_pagamento.scadenza_sanzione.value = "";
      document.form_par_pagamento.scadenza_sanzione.focus();
      return false;
	}
	if ((isNaN(scad_ing)) || (scad_ing == "") || (scad_ing == "undefined")) {
			ShowAlert(1,"Devi inserire la scadenza dell'ingiunzione, attenzione deve essere numerica!");
			document.form_par_pagamento.scadenza_ingiunzione.value = "";
			document.form_par_pagamento.scadenza_ingiunzione.focus();
			return false;
	}
	if ((isNaN(scad_avv)) || (scad_avv == "") || (scad_avv == "undefined")) {
			ShowAlert(1,"Devi inserire la scadenza dell'avviso, attenzione deve essere numerico!");
			document.form_par_pagamento.scadenza_avviso.value = "";
			document.form_par_pagamento.scadenza_avviso.focus();
			return false;
	}
	if ((isNaN(scad_pignor)) || (scad_pignor == "") || (scad_pignor == "undefined")) {
			ShowAlert(1,"Devi inserire la scadenza del pignoramento, attenzione deve essere numerico!");
			document.form_par_pagamento.scadenza_pignoramento.value = "";
			document.form_par_pagamento.scadenza_pignoramento.focus();
			return false;
	}

		document.form_par_pagamento.action = "par_pagamento_salva.php";
	  document.form_par_pagamento.submit();
}*/

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
	});
</script>


<!--<script>
    $(function () {

        $('.frm_field_currency').bootstrapValidator({
            live: 'disabled',
            fields: {
                frm_field_currency: {
                    selector: '.frm_field_currency',
                    validators: {
                        numeric: {
                            message: 'Valuta'
                        }
                    }
                },

            }
        });
        $('.frm_field_required').bootstrapValidator({
            live: 'disabled',
            fields: {
                frm_field_required: {
                    selector: '.frm_field_required',
                    validators: {
                        notEmpty: {
                            message: 'Richiesto'
                        }
                    }
                },
            }
        });
        $('.frm_field_numeric').bootstrapValidator({
            live: 'disabled',
            fields: {
                frm_field_numeric: {
                    selector: '.frm_field_numeric',
                    validators: {
                        numeric: {
                            message: 'Numero'
                        }
                    }
                },
            }
        });
    });
</script>-->

<?php include(INC."/footer.php"); ?>
