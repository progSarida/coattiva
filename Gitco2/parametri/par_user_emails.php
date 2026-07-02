<?php


require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once(CLS."/cls_crypt.php");
$cls_crypt = new cls_crypt();

include(INC."/header.php");
include(INC."/menu.php");


$query = "SELECT * FROM email_config WHERE MailType='email'";
$a_configs['email'] = $cls_db->getResults($cls_db->ExecuteQuery($query),"array","Id");
$query = "SELECT * FROM email_config WHERE MailType='PEC'";
$a_configs['PEC'] = $cls_db->getResults($cls_db->ExecuteQuery($query),"array","Id");

$query = "SELECT * FROM user_emails WHERE User_Id=".$_SESSION['aut_progr']." ORDER BY MailType";
$a_emails = $cls_db->getResults($cls_db->ExecuteQuery($query),"array","MailType");

foreach($a_emails as $key=>$a_email){
	if($a_email['Password']!="")
		$a_emails[$key]['Password'] = $cls_crypt->decryptIt($a_email['Password']);
		
	$a_selection = array("value" => "Id", "firstOpt" => 1, "selected" => $a_email['Email_Config_Id'], "text" => array("[MailType]"," ","[Provider]"));
	$a_emails[$key]['OptConfig'] = $cls_html->getOptions($a_configs[$key], $a_selection);

}

// var_dump($a_emails);
// die;
?>


<script>

switchMenuImg("F3");
F3_button = function()
{
	control = submit_buttons('Salva');
	if(control)
		$('#f_user_emails').submit();
		
	

}

</script>

<form name=f_user_emails id=f_user_emails method=post action="par_user_emails_salva.php" style="margin-top: -1rem;">

	<input type=hidden name=c value=<?php echo $c; ?> >
	<input type=hidden name=a value=<?php echo $a; ?> >
	

<?php 
	for($i=1;$i<=2;$i++){
		if($i==1)
			$key = "email";
		else
			$key = "PEC";	

		if(empty($a_emails[$key]["OptConfig"])){
			$a_selection = array("value" => "Id", "firstOpt" => 0, "selected" => null, "text" => array("[MailType]"," ","[Provider]"));
			$a_emails[$key]['OptConfig'] = $cls_html->getOptions($a_configs[$key], $a_selection);
		}

		?>
		<div class="row justify-content-md-center " style="margin-top: 1rem;margin-bottom:1rem;">
			<div class="col col-md-auto text_center">
					<p class="titolo font16"><?= ucfirst($key)." ".$_SESSION['username'] ?></p>
			</div>
		</div>

		<input type=hidden name="Params[<?=$key;?>][ID]" value=<?= isset($a_emails[$key]["ID"])? $a_emails[$key]["ID"] : '' ?> >
		<input type=hidden name="Params[<?=$key;?>][User_Id]" value=<?= $_SESSION['aut_progr'] ?> >
		<div class="row">
			<div class="col-lg-2 col-lg-offset-1 resize">Indirizzo mail</div>
			<div class="col-lg-4">
				<div class="form-group">
					<input class="form-control vld_emailReq resize" name="Params[<?=$key;?>][Address]"
					value="<?= isset($a_emails[$key]["Address"])? $a_emails[$key]["Address"] : '' ?>">
				</div>
			</div>
			<div class="col-lg-2 text_center resize">Nome visualizzato</div>
			<div class="col-lg-2">
				<div class="form-group">
					<input class="form-control vld_req resize" name="Params[<?=$key;?>][PublicName]" value="<?= isset($a_emails[$key]["PublicName"])? $a_emails[$key]["PublicName"] : '' ?>">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-2 col-lg-offset-1 resize">Nome utente</div>
			<div class="col-lg-4">
				<div class="form-group">
					<input class="form-control vld_req resize" name="Params[<?=$key;?>][Username]" value="<?= isset($a_emails[$key]["Username"])? $a_emails[$key]["Username"] : '' ?>">
				</div>
			</div>
			<div class="col-lg-2 text_center resize">Password</div>
			<div class="col-lg-2">
				<div class="form-group">
					<input type="password" class="form-control vld_req resize" name="Params[<?=$key;?>][Password]" value="<?= isset($a_emails[$key]["Password"])? $a_emails[$key]["Password"] : '' ?>">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-2 col-lg-offset-1 resize">Configurazione</div>
			<div class="col-lg-4">
				<div class="form-group">
					<select class="form-control config" name="Params[<?=$key;?>][Email_Config_Id]" data-type="<?=$key;?>">
						<?= $a_emails[$key]["OptConfig"]; ?>
					</select>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-2 col-lg-offset-1 resize">Server posta in arrivo</div>
			<div class="col-lg-2">
				<div class="form-group">
					<input class="form-control vld_req resize" id=<?=$key;?>_InMailServer readonly name="Params[<?=$key;?>][InMailServer]"
					value="<?=  isset($a_emails[$key]['InMailServer']) ? $a_emails[$key]['InMailServer'] : "" ?>">
				</div>
			</div>
			<div class="col-lg-1 resize">Protocollo</div>
			<div class="col-lg-1">
				<div class="form-group">
					<input class="form-control vld_req resize" style="width: 10rem;" id=<?=$key;?>_InMailProtocol readonly name="Params[<?=$key;?>][InMailProtocol]"
					value="<?=  isset($a_emails[$key]['InMailProtocol']) ? $a_emails[$key]['InMailProtocol'] : "" ?>">
				</div>
			</div>
			<div class="col-lg-1 resize">Porta</div>
			<div class="col-lg-1">
				<div class="form-group">
					<input class="form-control vld_req resize" style="width: 10rem;" id=<?=$key;?>_InMailPort readonly name="Params[<?=$key;?>][InMailPort]"
					value="<?=  isset($a_emails[$key]['InMailPort']) ? $a_emails[$key]['InMailPort'] : "" ?>">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-2 col-lg-offset-1 resize">Server posta in uscita</div>
			<div class="col-lg-2">
				<div class="form-group">
					<input class="form-control vld_req resize" id=<?=$key;?>_OutMailServer readonly name="Params[<?=$key;?>][OutMailServer]"
					value="<?=  isset($a_emails[$key]['OutMailServer']) ? $a_emails[$key]['OutMailServer'] : "" ?>">
				</div>
			</div>
			<div class="col-lg-1 resize">Protocollo</div>
			<div class="col-lg-1">
				<div class="form-group">
					<input class="form-control vld_req resize" style="width: 10rem;" id=<?=$key;?>_OutMailProtocol readonly name="Params[<?=$key;?>][OutMailProtocol]"
					value="<?=  isset($a_emails[$key]['OutMailProtocol']) ? $a_emails[$key]['OutMailProtocol'] : "" ?>">
				</div>
			</div>
			<div class="col-lg-1 resize">Porta</div>
			<div class="col-lg-1">
				<div class="form-group">
					<input class="form-control vld_req resize" style="width: 10rem;" id=<?=$key;?>_OutMailPort readonly name="Params[<?=$key;?>][OutMailPort]"
					value="<?=  isset($a_emails[$key]['OutMailPort']) ? $a_emails[$key]['OutMailPort'] : "" ?>">
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-2 col-lg-offset-1 resize">Sicurezza connessione</div>
			<div class="col-lg-2">
				<div class="form-group">
					<input class="form-control vld_req resize" readonly style="width: 10rem;" id=<?=$key;?>_ConnectionSafety name="Params[<?=$key;?>][ConnectionSafety]"
					value="<?= isset($a_emails[$key]["ConnectionSafety"])? $a_emails[$key]["ConnectionSafety"] : '' ?>">
				</div>
			</div>
			<div class="col-lg-3 resize">Utilizza autenticazione per Server di posta in uscita</div>
			<div class="col-lg-3">
				<div class="form-group">
					<input class="form-control vld_req resize" readonly style="width: 10rem;" id=<?=$key;?>_OutAuthentication name="Params[<?=$key;?>][OutAuthentication]"
					value="<?= isset($a_emails[$key]["OutAuthentication"])? $a_emails[$key]["OutAuthentication"] : '' ?>">
				</div>
			</div>
		</div>


		<?php
	}
?>

</form>

<script>

	var a_configs = <?= json_encode($a_configs); ?>;
	
	$(".config").on('change', function() {
		let configId = $(this).val();
		let type = $(this).data("type");
		if(a_configs[type][configId]!==undefined){
			$('#'+type+"_InMailServer").val(a_configs[type][configId]['InMailServer']);
			$('#'+type+"_InMailProtocol").val(a_configs[type][configId]['InMailProtocol']);
			$('#'+type+"_InMailPort").val(a_configs[type][configId]['InMailPort']);

			$('#'+type+"_OutMailServer").val(a_configs[type][configId]['OutMailServer']);
			$('#'+type+"_OutMailProtocol").val(a_configs[type][configId]['OutMailProtocol']);
			$('#'+type+"_OutMailPort").val(a_configs[type][configId]['OutMailPort']);

			$('#'+type+"_ConnectionSafety").val(a_configs[type][configId]['ConnectionSafety']);
			$('#'+type+"_OutAuthentication").val(a_configs[type][configId]['OutAuthentication']);
		}
		else{
			$('#'+type+"_InMailServer").val("");
			$('#'+type+"_InMailProtocol").val("");
			$('#'+type+"_InMailPort").val("");
			$('#'+type+"_OutMailProtocol").val("");
			$('#'+type+"_OutMailPort").val("");
			$('#'+type+"_OutMailServer").val("");
			$('#'+type+"_ConnectionSafety").val("");
			$('#'+type+"_OutAuthentication").val("");
		}
	});

	
</script>
<br>

<?php include(INC."/footer.php"); ?>
