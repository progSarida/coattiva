<?php

include_once($_SERVER['DOCUMENT_ROOT']."/gitco2/_path.php");
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
include_once(CLS."/cls_crypt.php");
include_once(CLS."/cls_paramUtils.php");

$cls_param = new cls_param();
$cls_crypt = new cls_crypt();


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

$nome_com = $a_enteAdmin["Denominazione"];

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];


$a_param_PEC = $cls_db->getArrayLine($cls_db->ExecuteQuery($cls_param->Get_Query_EMail($c , "PEC", $tipo_riscossione)));
$a_param_email = $cls_db->getArrayLine($cls_db->ExecuteQuery($cls_param->Get_Query_EMail($c , "email", $tipo_riscossione)));


if($a_param_PEC['Password']!="")
{
	$Password_PEC = $cls_crypt->decryptIt($a_param_PEC['Password']);
}
else
	$Password_PEC = "";


if($a_param_PEC['Password_Uscita']!="")
$Password_Uscita_PEC = $cls_crypt->decryptIt($a_param_PEC['Password_Uscita']);
else
	$Password_Uscita_PEC = "";

$disable_PEC = "";
$checked_PEC = "checked";
if($a_param_PEC['Autenticazione_Uscita']!="si")
{
	$disable_PEC = "disabled";
	$checked_PEC = "";
	$a_param_PEC['Nome_Utente_Uscita'] = $a_param_PEC['Nome_Utente'];
	$Password_Uscita_PEC = $Password_PEC;
}

if($a_param_email['Password']!="")
$Password_email = $cls_crypt->decryptIt($a_param_email['Password']);
else
	$Password_email = "";

if($a_param_email['Password_Uscita']!="")
$Password_Uscita_email = $cls_crypt->decryptIt($a_param_email['Password_Uscita']);
else
	$Password_Uscita_email = "";

$disable_email = "";
$checked_email = "checked";
if($a_param_email['Autenticazione_Uscita']!="si")
{
	$disable_email = "disabled";
	$checked_email = "";
	$a_param_email['Nome_Utente_Uscita'] = $a_param_email['Nome_Utente'];
	$Password_Uscita_email = $Password_email;
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

function control_auth(value)
{
	if($('#Autenticazione_Uscita_'+value).prop('checked') == true)
	{
		$('#Nome_Utente_Uscita_'+value).prop('disabled',false);
		$('#Password_Uscita_'+value).prop('disabled',false);
	}
	else
	{
		$('#Nome_Utente_Uscita_'+value).prop('disabled',true);
		$('#Password_Uscita_'+value).prop('disabled',true);
	}

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
	if(control)
	    $("#form_par_email").submit();
}

//F4
switchMenuImg("F4");
F4_button = function()
{
	control = submit_buttons('Delete');
	if(control)
	    $("#form_par_email").submit();
}

//F5
switchMenuImg("F5");
F5_button = function()
{
	clocation.href="par_email.php?tipo_riscossione=<?php echo $tipo_riscossione; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}


//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){
	if( modifica == 0 )
 {
	 location.href = "par_generali.php?tipo_riscossione=<?php echo $tipo_riscossione; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
 }
 else
	 alert("salvare i dati o annullare prima di procedere");
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){
	if( modifica == 0 )
	{
		location.href =  "par_pagamento.php?tipo_riscossione=<?php echo $tipo_riscossione; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//F11-F12 sono nel menu'

</script>


<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><font class="titolo font16 under_decor">Parametri autenticazione eMail (<?php echo $titolo_riscossione; ?>)</font></td>
	</tr>
</table>

<form name=form_par_email id=form_par_email method=post action="par_email_salva.php">

<input type=hidden name=invia_submit id=invia_submit value="" >

<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >
<input type=hidden name=ID_PEC value=<?php echo isset($a_param_PEC["ID"])? $a_param_PEC["ID"] : 0; ?> >
<input type=hidden name=ID_Mail value=<?php echo isset($a_param_email["ID"])? $a_param_email["ID"] : 0; ?> >
<input type=hidden name=tipo_riscossione value=<?php echo $tipo_riscossione; ?> >

<table class="table_interna text_center" border="0" cellspacing="2" cellpadding="0">
	<tr>
		<td class="text_center" colspan=10><b>PEC</b></td>
	</tr>
	<tr>
		<td class="text_center" colspan=10><hr></td>
	</tr>
	<tr>
		<td class="text_left width20" colspan=2>Indirizzo mail</td>
		<td class="text_left width40" colspan=4>
			<input class="width90" name=Indirizzo_Email_PEC id=Indirizzo_Email_PEC value="<?php echo isset($a_param_PEC['Indirizzo_Email']) ? $a_param_PEC['Indirizzo_Email'] : ""; ?>">
		</td>
		<td class="text_left width20" colspan=2>Nome visualizzato</td>
		<td class="text_left width20" colspan=2>
			<input class="width95" name=Nome_Visualizzato_PEC id=Nome_Visualizzato_PEC value="<?php echo isset($a_param_PEC['Nome_Visualizzato']) ? $a_param_PEC['Nome_Visualizzato'] : ""; ?>">
		</td>
	</tr>
	<tr>
		<td class="text_left width20" colspan=2>Nome utente</td>
		<td class="text_left width40" colspan=4>
			<input class="width90" name=Nome_Utente_PEC id=Nome_Utente_PEC value="<?php echo isset($a_param_PEC['Nome_Utente']) ? $a_param_PEC['Nome_Utente'] : ""; ?>">
		</td>
		<td class="text_left width20" colspan=2>Password</td>
		<td class="text_left width20" colspan=2>
			<input type="password" class="width95" name=Password_PEC id=Password_PEC value="<?php echo $Password_PEC; ?>">
		</td>
	</tr>
	<tr>
		<td class="text_left width20" colspan=2>Sicurezza connessione</td>
		<td class="text_left width20" colspan=2>
			<select class="width94" name=Sicurezza_Connessione_PEC id=Sicurezza_Connessione_PEC>
				<option>Nessuna</option>
				<option value="tls">STARTTLS</option>
				<option value="ssl">SSL/TLS</option>
			</select>
		</td>
		<td class="text_left width50" colspan=6></td>
	</tr>
	<tr>
		<td class="text_left width20" colspan=2>Server posta in arrivo</td>
		<td class="text_left width40" colspan=4>
			<input class="width90" name=Server_Posta_Arrivo_PEC id=Server_Posta_Arrivo_PEC value="<?php echo isset($a_param_PEC['Server_Posta_Arrivo']) ? $a_param_PEC['Server_Posta_Arrivo'] : ""; ?>">
		</td>
		<td class="text_center width10" colspan=1>Protocollo</td>
		<td class="text_center width10" colspan=1>
			<select class="width100" name=Protocollo_Arrivo_PEC id=Protocollo_Arrivo_PEC>
				<option></option>
				<option>POP3</option>
				<option>IMAP</option>
			</select>
		</td>
		<td class="text_center width10" colspan=1>Porta</td>
		<td class="text_left width10" colspan=1>
			<input class="width90" name=Porta_Arrivo_PEC id=Porta_Arrivo_PEC value="<?php echo isset($a_param_PEC['Porta_Arrivo']) ? $a_param_PEC['Porta_Arrivo'] : ""; ?>">
		</td>
	</tr>
	<tr>
		<td class="text_left width20" colspan=2>Server posta in uscita</td>
		<td class="text_left width40" colspan=4>
			<input class="width90" name=Server_Posta_Uscita_PEC id=Server_Posta_Uscita_PEC value="<?php echo isset($a_param_PEC['Server_Posta_Uscita']) ? $a_param_PEC['Server_Posta_Uscita'] : ""; ?>">
		</td>
		<td class="text_center width10" colspan=1>Protocollo</td>
		<td class="text_center width10" colspan=1>
			<select class="width100" name=Protocollo_Uscita_PEC id=Protocollo_Uscita_PEC>
				<option></option>
				<option>SMTP</option>
			</select>
		</td>
		<td class="text_center width10" colspan=1>Porta</td>
		<td class="text_left width10" colspan=1>
			<input class="width90" name=Porta_Uscita_PEC id=Porta_Uscita_PEC value="<?php echo isset($a_param_PEC['Porta_Uscita']) ? $a_param_PEC['Porta_Uscita'] : ""; ?>">
		</td>
	</tr>
	<tr>
		<td class="text_left width50" colspan=5>Utilizza autenticazione per Server di posta in uscita
			<input <?php echo $checked_PEC; ?> type=checkbox name=Autenticazione_Uscita_PEC id=Autenticazione_Uscita_PEC value="si" onchange="control_auth('PEC');">
		</td>
		<td class="text_left width50" colspan=5>
		</td>
	</tr>
	<tr>
		<td class="text_left width20" colspan=2>Nome utente</td>
		<td class="text_left width40" colspan=4>
			<input <?php echo $disable_PEC; ?> class="width90" name=Nome_Utente_Uscita_PEC id=Nome_Utente_Uscita_PEC value="<?php echo isset($a_param_PEC['Nome_Utente_Uscita']) ? $a_param_PEC['Nome_Utente_Uscita'] : ""; ?>">
		</td>
		<td class="text_left width20" colspan=2>Password</td>
		<td class="text_left width20" colspan=2>
			<input <?php echo $disable_PEC; ?> type="password" class="width95" name=Password_Uscita_PEC id=Password_Uscita_PEC value="<?php echo $Password_Uscita_PEC; ?>">
		</td>
	</tr>
	<tr>
		<td class="text_center" colspan=10><hr></td>
	</tr>
	<tr>
		<td class="text_center" colspan=10><b>Email</b></td>
	</tr>
	<tr>
		<td class="text_center" colspan=10><hr></td>
	</tr>
	<tr>
		<td class="text_left width20" colspan=2>Indirizzo mail</td>
		<td class="text_left width40" colspan=4>
			<input class="width90" name=Indirizzo_Email_email id=Indirizzo_Email_email value="<?php echo isset($a_param_email['Indirizzo_Email']) ? $a_param_email['Indirizzo_Email'] : ""; ?>">
		</td>
		<td class="text_left width20" colspan=2>Nome visualizzato</td>
		<td class="text_left width20" colspan=2>
			<input class="width95" name=Nome_Visualizzato_email id=Nome_Visualizzato_email value="<?php echo isset($a_param_email['Nome_Visualizzato']) ? $a_param_email['Nome_Visualizzato'] : ""; ?>">
		</td>
	</tr>
	<tr>
		<td class="text_left width20" colspan=2>Nome utente</td>
		<td class="text_left width40" colspan=4>
			<input class="width90" name=Nome_Utente_email id=Nome_Utente_email value="<?php echo isset($a_param_email['Nome_Utente']) ? $a_param_email['Nome_Utente'] : ""; ?>">
		</td>
		<td class="text_left width20" colspan=2>Password</td>
		<td class="text_left width20" colspan=2>
			<input type="password" class="width95" name=Password_email id=Password_email value="<?php echo $Password_email; ?>">
		</td>
	</tr>
	<tr>
		<td class="text_left width20" colspan=2>Sicurezza connessione</td>
		<td class="text_left width20" colspan=2>
			<select class="width94" name=Sicurezza_Connessione_email id=Sicurezza_Connessione_email>
				<option>Nessuna</option>
				<option value="tls">STARTTLS</option>
				<option value="ssl">SSL/TLS</option>
			</select>
		</td>
		<td class="text_left width50" colspan=6></td>
	</tr>
	<tr>
		<td class="text_left width20" colspan=2>Server posta in arrivo</td>
		<td class="text_left width40" colspan=4>
			<input class="width90" name=Server_Posta_Arrivo_email id=Server_Posta_Arrivo_email value="<?php echo isset($a_param_email['Server_Posta_Arrivo']) ? $a_param_email['Server_Posta_Arrivo'] : ""; ?>">
		</td>
		<td class="text_center width10" colspan=1>Protocollo</td>
		<td class="text_center width10" colspan=1>
			<select class="width100" name=Protocollo_Arrivo_email id=Protocollo_Arrivo_email>
				<option></option>
				<option>POP3</option>
				<option>IMAP</option>
			</select>
		</td>
		<td class="text_center width10" colspan=1>Porta</td>
		<td class="text_left width10" colspan=1>
			<input class="width90" name=Porta_Arrivo_email id=Porta_Arrivo_email value="<?php echo isset($a_param_email['Porta_Arrivo']) ? $a_param_email['Porta_Arrivo'] : ""; ?>">
		</td>
	</tr>
	<tr>
		<td class="text_left width20" colspan=2>Server posta in uscita</td>
		<td class="text_left width40" colspan=4>
			<input class="width90" name=Server_Posta_Uscita_email id=Server_Posta_Uscita_email value="<?php echo isset($a_param_email['Server_Posta_Uscita']) ? $a_param_email['Server_Posta_Uscita'] : ""; ?>">
		</td>
		<td class="text_center width10" colspan=1>Protocollo</td>
		<td class="text_center width10" colspan=1>
			<select class="width100" name=Protocollo_Uscita_email id=Protocollo_Uscita_email>
				<option></option>
				<option>SMTP</option>
			</select>
		</td>
		<td class="text_center width10" colspan=1>Porta</td>
		<td class="text_left width10" colspan=1>
			<input class="width90" name=Porta_Uscita_email id=Porta_Uscita_email value="<?php echo isset($a_param_email['Porta_Uscita']) ? $a_param_email['Porta_Uscita'] : ""; ?>">
		</td>
	</tr>
	<tr>
		<td class="text_left width50" colspan=5>Utilizza autenticazione per Server di posta in uscita
			<input <?php echo $checked_email; ?> type=checkbox name=Autenticazione_Uscita_email id=Autenticazione_Uscita_email value="si" onchange="control_auth('email');">
		</td>
		<td class="text_left width50" colspan=5>
		</td>
	</tr>
	<tr>
		<td class="text_left width20" colspan=2>Nome utente</td>
		<td class="text_left width40" colspan=4>
			<input <?php echo $disable_email; ?> class="width90" name=Nome_Utente_Uscita_email id=Nome_Utente_Uscita_email value="<?php echo isset($a_param_email['Nome_Utente_Uscita']) ? $a_param_email['Nome_Utente_Uscita'] : ""; ?>">
		</td>
		<td class="text_left width20" colspan=2>Password</td>
		<td class="text_left width20" colspan=2>
			<input <?php echo $disable_email; ?> type="password" class="width95" name=Password_Uscita_email id=Password_Uscita_email value="<?php echo $Password_Uscita_email; ?>">
		</td>
	</tr>
	<tr>
		<td class="text_center" colspan=10><hr></td>
	</tr>
</table>

</form>
<script type="text/javascript">
	$( window ).load(function() {
		$('#Sicurezza_Connessione_PEC').val("<?php echo $a_param_PEC['Sicurezza_Connessione']; ?>");
		$('#Protocollo_Arrivo_PEC').val("<?php echo $a_param_PEC['Protocollo_Arrivo']; ?>");
		$('#Protocollo_Uscita_PEC').val("<?php echo $a_param_PEC['Protocollo_Uscita']; ?>");
		$('#Sicurezza_Connessione_email').val("<?php echo $a_param_email['Sicurezza_Connessione']; ?>");
		$('#Protocollo_Arrivo_email').val("<?php echo $a_param_email['Protocollo_Arrivo']; ?>");
		$('#Protocollo_Uscita_email').val("<?php echo $a_param_email['Protocollo_Uscita']; ?>");
	});
</script>
<br>

<?php include(INC."/footer.php"); ?>
