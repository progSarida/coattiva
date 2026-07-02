<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
include_once(INC."/menu.php");

$newCC = $cls_help->getVar('newCC');
$servizio = $cls_help->getVar('servizio');


$nome_com = $a_enteAdmin["Denominazione"];

if($newCC == null )	$newCC = $c;
else
{
	$query = "SELECT * FROM enti_gestiti WHERE CC = '".$newCC."'";
	$result = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));
	$nome_com = $result["Denominazione"];
}

$query = "SELECT Anno FROM anni_gestiti WHERE CC_Anno = '".$newCC."' ORDER BY Anno DESC";
$anni_gestiti = $cls_db->getResults($cls_db->ExecuteQuery($query));

?>

<!-- ********** VARIABILI ********** -->
<script>
var num_anni = new Array();

<?php
for($y=0; $y<count($anni_gestiti); $y++)
{
?>

	num_anni[<?php echo $y; ?>] = "<?php echo $anni_gestiti[$y]['Anno']; ?>";

<?php
}
?>

/*function controlla_anni()
{
	control_anno = $('#anno').val();

	for(var y=0 ; y<num_anni.length;y++)
	{
		if( num_anni[y] == control_anno )
		{
			return false;
		}
	}

	if(control_anno > 1900)
		return true;
	else
	{
		return false;
	}
}*/

</script>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>

//F3
switchMenuImg("F3");
F3_button = function()
{
	//ctrl = controlla_anni();
	ctrl = validateForm();
	if(ctrl)
	{
		control = submit_buttons('Salva');
		if(control)
			$('#btnSub').trigger("click");
	}
//	else
	//	alert('Errore!! Anno di gestione presente in archivio o non corretto.');
}

//F5
switchMenuImg("F5");
F5_button = function()
{
		location.href="crea_anno.php?servizio=<?php echo $servizio; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){

		link = "crea_comune.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
		top.location.href = link;
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){

	link = "elimina_comune.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&servizio=<?php echo $servizio; ?>";
	top.location.href = link;
}

//F11-F12 sono nel menu'

</script>

<div class="row justify-content-md-center " style="margin-top: 1%; margin-bottom: 2%;">
	<div class="col col-md-auto text_center">
			<span class="titolo font16 under_decor">Creazione anno</span>
	</div>
</div>

<form name=form_crea id=form_crea class="form-horizontal validate" method=post action="crea_anno_salva.php">

<input type=hidden name=invia_submit id=invia_submit value="" >

<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >
<input type=hidden name=CC_ente id=CC_ente value="<?php echo $newCC; ?>" >
<input type=hidden name=servizio value=<?php echo $servizio; ?> >

<div class="row">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Ente</label>
			<div class="col-lg-8">
				<input class="form-control resize" style="background-color: rgb(153, 204, 255); border: 2px solid black;" type=text name=ente readonly id=ente size=25 value="<?php echo $nome_com; ?>" tabindex=1>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col col-lg-5 col-lg-offset-1">
		<div class="form-group">
			<label class="col-lg-4 control-label resize" style="text-align: left;">Selezione anno</label>
			<div class="col-lg-8">
				<input name=anno id=anno class="form-control resize validateCustom vld_Custom_CustAnno" size=3 tabindex=2>
			</div>
		</div>
	</div>
</div>

<div class="form-group">
	<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
</div>

</form>

<?php include(INC."/footer.php"); ?>
