<?php



include_once($_SERVER['DOCUMENT_ROOT']."/gitco2/_path.php");
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
include_once(CLS."/cls_paramUtils.php");
include_once(CLS."/cls_DateTimeInLine.php");


$cls_param = new cls_param();
$cls_date = new cls_DateTimeI("IT",false);

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');



$nome_com = $a_enteAdmin["Denominazione"];//$comune->Nome;

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];



$a_param = $cls_db->ExecuteQuery($cls_param->Get_Query_Tributi($c));

$numRows = mysqli_num_rows($a_param);
//print_r($a_param);
?>



<!-- ********** CALENDARIO ********** -->
<script>

$(function() {

	 $( ".picker" ).datepicker();

	 });

</script>


<?php

include(INC."/menu.php");

?>
<!-- ********** GESTIONE LINK MENU ********** -->
<script>

//F3
switchMenuImg("F3");
F3_button = function()
{
	control = submit_buttons('Salva');
	if(control)
			$("#form_interessi_tributi").submit();
}


//F5
switchMenuImg("F5");
F5_button = function()
{
	location.href="gestione_interessi_tributi.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){
	if( modifica == 0 )
 {
	 location.href = "par_scorpori.php?&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
 }
 else
	 alert("salvare i dati o annullare prima di procedere");
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){
	if( modifica == 0 )
	{
		location.href = "par_annuali.php?tipo_riscossione=*****&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

//F11-F12 sono nel menu'


function new_tasso()
{
	$('.nuovo_tasso_interesse').show();
}

</script>



<!--<table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
	<tr>
		<td><font class="titolo font16 under_decor">Gestione interessi tributi</font></td>
	</tr>
</table>
<br>-->
<div class="row justify-content-md-center ">
	<div class="col col-md-auto text_center">
			<p class="titolo font16 under_decor">Gestione interessi tributi</p>
	</div>
</div>

<form name=form_interessi_tributi id=form_interessi_tributi method=post action="gestione_interessi_tributi_salva.php">

<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >
<input type=hidden name=num_Rows value=<?php echo $numRows; ?> >

<table class="table_interna text_center" border="0" cellspacing="2" cellpadding="0">
	<tr>
		<td class="text_left width25"><span class="color_titolo">Data inizio tasso</span></td>
		<td class="text_left width25"><span class="color_titolo">Data fine tasso</span></td>
		<td class="text_left width50" colspan=2><span class="color_titolo">Tasso di interesse</span></td>
	</tr>
	<tr>
		<td class="text_center" colspan=5><hr></td>
	</tr>

<?php	//CICLO INTERESSI TRIBUTI
//for($i=0;$i<count($arrayInteressi);$i++) {
$i=0;
while($row = mysqli_fetch_assoc($a_param)){

?>

	<tr>
		<td class="text_left width25">
		<input type=text class="text_center picker width50" name=data_inizio[<?php echo $i; ?>] value="<?php echo $cls_date->Get_DateNewFormat($row['Data_Inizio'],"DB"); ?>"></td>
		<td class="text_left width25">
		<input type=text class="text_center picker width50" name=data_fine[<?php echo $i; ?>] value="<?php echo $cls_date->Get_DateNewFormat($row['Data_Fine'],"DB"); ?>"></td>
		<td class="text_left width25">
		<input type=text class="text_right width30 corrige_numero" id=tasso_<?php echo $i; ?> name=tasso[<?php echo $i; ?>] value="<?php echo number_format($row['Tasso_Interessi'],2,",","."); ?>"> %
		<input type=hidden name=ID[<?php echo $i; ?>] value="<?php echo $row['ID']; ?>">
		</td>
		<td class="text_right width25">
		<?php if($i==$numRows-1)	{?>
			<a onMouseover="title='Nuovo tasso di interesse'" href="#" onClick="new_tasso();" style="text-decoration: none">
				<img src="/gitco2/immagini/plus.png" width=20 height=20 border=0>
			</a>
		<?php  }?>
		</td>
	</tr>

<?php $i++;}

if($numRows==0){?>

	<tr class="nuovo_tasso_interesse">
		<td class="text_left width25">
		<input type=text class="text_center picker width50" name=data_inizio[0] value=""></td>
		<td class="text_left width25">
		<input type=text class="text_center picker width50" name=data_fine[0] value=""></td>
		<td class="text_left width25">
		<input type=text class="text_right width30 corrige_numero" id=tasso_0 name=tasso[0] value=""> %
		<input type=hidden name=ID[0] value="">
		</td>
		<td class="text_right width25"></td>
	</tr>

<?php }
 else {
//echo "<h1>".count($arrayInteressi)." --- ".mysqli_num_rows($a_param)."</h1>";
	 ?>

	<tr class="nuovo_tasso_interesse" style="display:none;">
		<td class="text_left width25">
		<input type=text class="text_center picker width50" name=data_inizio[<?php echo $numRows; ?>] value=""></td>
		<td class="text_left width25">
		<input type=text class="text_center picker width50" name=data_fine[<?php echo $numRows; ?>] value=""></td>
		<td class="text_left width25">
		<input type=text class="text_right width30 corrige_numero" id=tasso_<?php echo $numRows; ?> name=tasso[<?php echo $numRows; ?>] value=""> %
		<input type=hidden name=ID[<?php echo $numRows; ?>] value="">
		</td>
		<td class="text_right width25"></td>
	</tr>
<?php } ?>
	<tr>
		<td class="text_center" colspan=5><hr></td>
	</tr>
	<tr>
		<td class="text_left" colspan=5>
			<span class="color_red">Lasciare vuota l'ultima "Data fine tasso" se non e' ancora stato definito un tasso successivo</span>
		</td>
	</tr>
	<tr>
		<td class="text_center" colspan=5><hr></td>
	</tr>

</table>


</form>
<?php include(INC."/footer.php"); ?>
<!--</td>
</tr>
</table>

</body>
</html>-->
