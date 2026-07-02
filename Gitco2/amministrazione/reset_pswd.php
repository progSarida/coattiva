	<?php

	if (!session_id()) session_start();


	include_once($_SESSION['_path']);
	include_once(ROOT . "/_parameter.php"); //dati database

	include(INC . "/header.php");
	include(INC . "/menu.php");

	if ($_SESSION['username'] == NULL) {
		header("Location:/gitco2/autenticazione/accesso_negato.php");
		die;
	}

	$a = $cls_help->getVar('a');
	$c = $cls_help->getVar('c');
	$p = $cls_help->getVar('p');

	$auth = $_SESSION['aut_tipo'];

	$query_user = "SELECT * FROM autenticazione ORDER by User ASC";
	$a_user = $cls_db->getResults($cls_db->ExecuteQuery($query_user), "array", "ID");
	$a_selection = array("value" => "User", "firstOpt" => 0, "selected" => $auth, "text" => array("[User]"));
	$optUser = $cls_html->getOptions($a_user, $a_selection);
	?>

	<style>
		fieldset.scheduler-border {


			border: 1px solid #356bc1;
			padding: 0 1.4em 1.4em 1.4em !important;
			margin: 0 0 1.5em 0 !important;
			-webkit-box-shadow: 0px 0px 0px 0px #000;
			box-shadow: 0px 0px 0px 0px #000;
		}

		legend.scheduler-border {
			font-size: 1.2em !important;
			font-weight: bold !important;
			text-align: left !important;
		}
	</style>

	<div class="container">
		<div style="height: auto; display: flex; justify-content: center;">
			<div class="col">
				<form class="form-horizontal" id="form_new_utente" name="form_new_utente" action="update_pswd.php" method="post">
					<input type="hidden" name="c" value="<?php echo $c; ?>">
					<input type="hidden" name="a" value="<?php echo $a; ?>">
					<fieldset class="scheduler-border">
						<legend style="width: auto;margin-left: auto; margin-right: auto;"><span class="titolo font16 under_decor">Reset Password</span></legend>
						<div class="form-group">
							<label class="control-label col-md-3" for="email">Username </label>
							<div class="col-md-9">
								<select class="form-select validateCustom vld_Custom_r" id="username" name="username" style="height:35px; width:75%;">
									<option value=''>Seleziona </option>
									<?= $optUser; ?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-3" for="Password">Password</label>
							<div class="col-md-9">
								<input type="password" class="form-control validateCustom vld_Custom_r" id="password-field" name="pswd">
								<span toggle="#password-field" class="fa fa-fw fa-eye field-icon toggle-password"></span>
							</div>
						</div>
						<button type="submit" class="btn btn-primary pull-right" style="margin-bottom:5px;margin-right:10px;">Salva</button>
					</fieldset>
				</form>
			</div>
		</div>
	</div>
	<script>
		$(".toggle-password").click(function() {

			$(this).toggleClass("fa-eye fa-eye-slash");
			var input = $($(this).attr("toggle"));
			if (input.attr("type") == "password") {
				input.attr("type", "text");
			} else {
				input.attr("type", "password");
			}
		});
	</script>
	<?php include(INC . "/footer.php"); ?>