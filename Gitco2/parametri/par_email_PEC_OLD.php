<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
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

$nome_com = $a_enteAdmin["Denominazione"];

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];


$a_param_PEC = $cls_db->getArrayLine($cls_db->ExecuteQuery($cls_param->Get_Query_EMail($c , "PEC", "GENERALE")));
$a_param_email = $cls_db->getArrayLine($cls_db->ExecuteQuery($cls_param->Get_Query_EMail($c , "email", "GENERALE")));

//echo $enc = $cls_crypt->encryptIt("Graziella2015");
//echo "<br>".$cls_crypt->decryptIt($enc);
if($a_param_PEC['Password']!="")
	$Password_PEC = $cls_crypt->decryptIt($a_param_PEC['Password']);
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

		$('#Nome_Utente_Uscita_'+value).addClass("validateCustom vld_Custom_r");
		$('#Password_Uscita_'+value).addClass("validateCustom vld_Custom_r");
	}
	else
	{
		$('#Nome_Utente_Uscita_'+value).prop('disabled',true);
		$('#Password_Uscita_'+value).prop('disabled',true);

		$('#Nome_Utente_Uscita_'+value).removeClass("validateCustom vld_Custom_r");
		$('#Password_Uscita_'+value).removeClass("validateCustom vld_Custom_r");
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
	{
		 if(validateForm())
		 	$("#btnSub").trigger("click");
	}

}

//F4
switchMenuImg("F4");
F4_button = function()
{
	control = submit_buttons('Delete');
	if(control)
	    $("#btnSub").trigger("click");
}

//F5
switchMenuImg("F5");
F5_button = function()
{
	location.href="par_email_PEC.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}


//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){
	if( modifica == 0 )
 {
	 location.href = "par_annuali.php?tipo_riscossione=*****&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
 }
 else
	 alert("salvare i dati o annullare prima di procedere");
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){
	if( modifica == 0 )
	{
		location.href =  "par_ricorso.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//F11-F12 sono nel menu'

</script>
<style>

</style>

<form class="form-horizontal validate" name=form_par_email id=form_par_email method=post action="par_email_PEC_salva.php">

	<input type=hidden name=invia_submit id=invia_submit value="" >

	<input type=hidden name=c value=<?php echo $c; ?> >
	<input type=hidden name=a value=<?php echo $a; ?> >
	<input type=hidden name=ID_PEC value=<?php echo isset($a_param_PEC["ID"])? $a_param_PEC["ID"] : 0; ?> >
	<input type=hidden name=ID_Mail value=<?php echo isset($a_param_email["ID"])? $a_param_email["ID"] : 0; ?> >
	<input type=hidden name=tipo_riscossione value="GENERALE" >

	<div class="row justify-content-md-center ">
		<div class="col col-md-auto text_center">
				<p class="titolo font16 under_decor">Parametri autenticazione eMail - PEC</p>
		</div>
	</div>
	<div class="row justify-content-md-center ">
		<div class="col col-md-auto text_center">
				<b>PEC</b>
		</div>
	</div>
	<div class="row" style="padding-top: 2%;">
		<div class="col-lg-2 col-lg-offset-1 resize">Indirizzo mail</div>
		<div class="col-lg-4">
			<div class="form-group">
					<input class="form-control vld_emailReq resize" name=Indirizzo_Email_PEC id=Indirizzo_Email_PEC value="<?php echo isset($a_param_PEC['Indirizzo_Email']) ? $a_param_PEC['Indirizzo_Email'] : ""; ?>">
			</div>
		</div>
		<div class="col-lg-2 text_center resize">Nome visualizzato</div>
		<div class="col-lg-2">
			<div class="form-group">
				<input class="form-control vld_req resize" name=Nome_Visualizzato_PEC id=Nome_Visualizzato_PEC value="<?php echo isset($a_param_PEC['Nome_Visualizzato']) ? $a_param_PEC['Nome_Visualizzato'] : ""; ?>">
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-2 col-lg-offset-1 resize">Nome utente</div>
		<div class="col-lg-4">
			<div class="form-group">
				<input class="form-control vld_req resize" name=Nome_Utente_PEC id=Nome_Utente_PEC value="<?php echo isset($a_param_PEC['Nome_Utente']) ? $a_param_PEC['Nome_Utente'] : ""; ?>">
			</div>
		</div>
		<div class="col-lg-2 text_center resize">Password</div>
		<div class="col-lg-2">
			<div class="form-group">
				<input type="password" class="form-control vld_req resize" name=Password_PEC id=Password_PEC value="<?php echo $Password_PEC; ?>">
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-2 col-lg-offset-1 resize">Sicurezza connessione</div>
		<div class="col-lg-9">
			<div class="form-group">
				<select class="form-control vld_req resize" name=Sicurezza_Connessione_PEC id=Sicurezza_Connessione_PEC style="width: 25%;">
					<option>Nessuna</option>
					<option value="tls">STARTTLS</option>
					<option value="ssl">SSL/TLS</option>
				</select>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-2 col-lg-offset-1 resize">Server posta in arrivo</div>
		<div class="col-lg-4">
			<div class="form-group">
				<input class="form-control vld_req resize" name=Server_Posta_Arrivo_PEC id=Server_Posta_Arrivo_PEC value="<?php echo isset($a_param_PEC['Server_Posta_Arrivo']) ? $a_param_PEC['Server_Posta_Arrivo'] : ""; ?>">
			</div>
		</div>
		<div class="col-lg-1 resize">Protocollo</div>
		<div class="col-lg-1">
			<div class="form-group">
				<select class="form-control vld_req resize" name=Protocollo_Arrivo_PEC id=Protocollo_Arrivo_PEC>
					<option></option>
					<option>POP3</option>
					<option>IMAP</option>
				</select>
			</div>
		</div>
		<div class="col-lg-1 resize">Porta</div>
		<div class="col-lg-1">
			<div class="form-group">
				<input class="form-control vld_intReq resize" name=Porta_Arrivo_PEC id=Porta_Arrivo_PEC value="<?php echo isset($a_param_PEC['Porta_Arrivo']) ? $a_param_PEC['Porta_Arrivo'] : ""; ?>">
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-2 col-lg-offset-1 resize">Server posta in uscita</div>
		<div class="col-lg-4">
			<div class="form-group">
				<input class="form-control vld_req resize" name=Server_Posta_Uscita_PEC id=Server_Posta_Uscita_PEC value="<?php echo isset($a_param_PEC['Server_Posta_Uscita']) ? $a_param_PEC['Server_Posta_Uscita'] : ""; ?>">
			</div>
		</div>
		<div class="col-lg-1 resize">Protocollo</div>
		<div class="col-lg-1">
			<div class="form-group">
				<select class="form-control vld_req resize" name=Protocollo_Uscita_PEC id=Protocollo_Uscita_PEC>
					<option></option>
					<option>SMTP</option>
				</select>
			</div>
		</div>
		<div class="col-lg-1 resize">Porta</div>
		<div class="col-lg-1">
			<div class="form-group">
				<input class="form-control vld_intReq resize" name=Porta_Uscita_PEC id=Porta_Uscita_PEC value="<?php echo isset($a_param_PEC['Porta_Uscita']) ? $a_param_PEC['Porta_Uscita'] : ""; ?>">
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-10 col-lg-offset-1">
			<div class="form-group resize">
				<label class="col-lg-6 control-label">Utilizza autenticazione per Server di posta in uscita</label>
				<div class="checkbox col-lg-6">
					<input <?php echo $checked_PEC; ?> type=checkbox name=Autenticazione_Uscita_PEC id=Autenticazione_Uscita_PEC value="si" onchange="control_auth('PEC');">
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-2 col-lg-offset-1 resize">Nome utente</div>
		<div class="col-lg-4">
			<div class="form-group">
				<input <?php echo $disable_PEC; ?> class="form-control resize" name=Nome_Utente_Uscita_PEC id=Nome_Utente_Uscita_PEC value="<?php echo isset($a_param_PEC['Nome_Utente_Uscita']) ? $a_param_PEC['Nome_Utente_Uscita'] : ""; ?>">
			</div>
		</div>
		<div class="col-lg-2 text_center resize">Password</div>
		<div class="col-lg-2">
			<div class="form-group">
				<input <?php echo $disable_PEC; ?> type="password" class="form-control resize" name=Password_Uscita_PEC id=Password_Uscita_PEC value="<?php echo $Password_Uscita_PEC; ?>">
			</div>
		</div>
	</div>


	<div class="row justify-content-md-center " style="padding-top: 3%;">
		<div class="col col-md-auto text_center">
				<b>Email</b>
		</div>
	</div>


	<div class="row" style="padding-top: 2%;">
		<div class="col-lg-2 col-lg-offset-1 resize">Indirizzo mail</div>
		<div class="col-lg-4">
			<div class="form-group">
				<input class="form-control vld_emailReq resize" name=Indirizzo_Email_email id=Indirizzo_Email_email value="<?php echo isset($a_param_email['Indirizzo_Email']) ? $a_param_email['Indirizzo_Email'] : ""; ?>">
			</div>
		</div>
		<div class="col-lg-2 text_center resize">Nome visualizzato</div>
		<div class="col-lg-2">
			<div class="form-group">
				<input class="form-control vld_req resize" name=Nome_Visualizzato_email id=Nome_Visualizzato_email value="<?php echo isset($a_param_email['Nome_Visualizzato']) ? $a_param_email['Nome_Visualizzato'] : ""; ?>">
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-2 col-lg-offset-1 resize">Nome utente</div>
		<div class="col-lg-4">
			<div class="form-group">
				<input class="form-control vld_req resize" name=Nome_Utente_email id=Nome_Utente_email value="<?php echo isset($a_param_email['Nome_Utente']) ? $a_param_email['Nome_Utente'] : ""; ?>">
			</div>
		</div>
		<div class="col-lg-2 text_center resize">Password</div>
		<div class="col-lg-2">
			<div class="form-group">
				<input type="password" class="form-control vld_req resize" name=Password_email id=Password_email value="<?php echo $Password_email; ?>">
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-2 col-lg-offset-1 resize">Sicurezza connessione</div>
		<div class="col-lg-9">
			<div class="form-group">
				<select class="form-control vld_req resize" name=Sicurezza_Connessione_email id=Sicurezza_Connessione_email style="width: 25%;">
					<option>Nessuna</option>
					<option value="tls">STARTTLS</option>
					<option value="ssl">SSL/TLS</option>
				</select>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-2 col-lg-offset-1 resize">Server posta in arrivo</div>
		<div class="col-lg-4">
			<div class="form-group">
				<input class="form-control vld_req resize" name=Server_Posta_Arrivo_email id=Server_Posta_Arrivo_email value="<?php echo isset($a_param_email['Server_Posta_Arrivo']) ? $a_param_email['Server_Posta_Arrivo'] : ""; ?>">
			</div>
		</div>
		<div class="col-lg-1 resize">Protocollo</div>
		<div class="col-lg-1">
			<div class="form-group">
				<select class="form-control vld_req resize" name=Protocollo_Arrivo_email id=Protocollo_Arrivo_email>
					<option></option>
					<option>POP3</option>
					<option>IMAP</option>
				</select>
			</div>
		</div>
		<div class="col-lg-1 resize">Porta</div>
		<div class="col-lg-1">
			<div class="form-group">
				<input class="form-control vld_intReq resize" name=Porta_Arrivo_email id=Porta_Arrivo_email value="<?php echo isset($a_param_email['Porta_Arrivo']) ? $a_param_email['Porta_Arrivo'] : ""; ?>">
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-2 col-lg-offset-1 resize">Server posta in uscita</div>
		<div class="col-lg-4">
			<div class="form-group">
				<input class="form-control vld_req resize" name=Server_Posta_Uscita_email id=Server_Posta_Uscita_email value="<?php echo isset($a_param_email['Server_Posta_Uscita']) ? $a_param_email['Server_Posta_Uscita'] : ""; ?>">
			</div>
		</div>
		<div class="col-lg-1 resize">Protocollo</div>
		<div class="col-lg-1">
			<div class="form-group">
				<select class="form-control vld_req resize" name=Protocollo_Uscita_email id=Protocollo_Uscita_email>
					<option></option>
					<option>SMTP</option>
				</select>
			</div>
		</div>
		<div class="col-lg-1 resize">Porta</div>
		<div class="col-lg-1">
			<div class="form-group">
				<input class="form-control vld_intReq resize" name=Porta_Uscita_email id=Porta_Uscita_email value="<?php echo isset($a_param_email['Porta_Uscita']) ? $a_param_email['Porta_Uscita'] : ""; ?>">
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-10 col-lg-offset-1">
			<div class="form-group resize">
				<label class="col-lg-6 control-label">Utilizza autenticazione per Server di posta in uscita</label>
				<div class="checkbox col-lg-6">
					<input <?php echo $checked_email; ?> type=checkbox name=Autenticazione_Uscita_email id=Autenticazione_Uscita_email value="si" onchange="control_auth('email');">
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-2 col-lg-offset-1 resize">Nome utente</div>
		<div class="col-lg-4">
			<div class="form-group">
				<input <?php echo $disable_email; ?> class="form-control resize" name=Nome_Utente_Uscita_email id=Nome_Utente_Uscita_email value="<?php echo isset($a_param_email['Nome_Utente_Uscita']) ? $a_param_email['Nome_Utente_Uscita'] : ""; ?>">
			</div>
		</div>
		<div class="col-lg-2 text_center resize">Password</div>
		<div class="col-lg-2">
			<div class="form-group">
				<input <?php echo $disable_email; ?> type="password" class="form-control resize" name=Password_Uscita_email id=Password_Uscita_email value="<?php echo $Password_Uscita_email; ?>">
			</div>
		</div>
	</div>
	<div class="form-group">
			<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
	</div>

</form>
<script type="text/javascript">
	$( window ).load(function() {
		$('#Sicurezza_Connessione_PEC').val("<?php echo $a_param_PEC['Sicurezza_Connessione']; ?>");
		$('#Protocollo_Arrivo_PEC').val("<?php echo $a_param_PEC['Protocollo_Arrivo']; ?>");
		$('#Protocollo_Uscita_PEC').val("<?php echo $a_param_PEC['Protocollo_Uscita']; ?>");
		$('#Sicurezza_Connessione_email').val("<?php echo $a_param_email['Sicurezza_Connessione']; ?>");
		$('#Protocollo_Arrivo_email').val("<?php echo $a_param_email['Protocollo_Arrivo']; ?>");
		$('#Protocollo_Uscita_email').val("<?php echo $a_param_email['Protocollo_Uscita']; ?>");

		if($('#Autenticazione_Uscita_PEC').prop('checked') == true)
		{
			$('#Nome_Utente_Uscita_PEC').addClass("validateCustom vld_Custom_r");
			$('#Password_Uscita_PEC').addClass("validateCustom vld_Custom_r");
		}

		if($('#Autenticazione_Uscita_email').prop('checked') == true)
		{
			$('#Nome_Utente_Uscita_email').addClass("validateCustom vld_Custom_r");
			$('#Password_Uscita_email').addClass("validateCustom vld_Custom_r");
		}
	});
</script>
<br>

<?php include(INC."/footer.php"); ?>
