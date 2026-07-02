<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
include_once(CLS."/cls_paramUtils.php");
include_once(CLS."/cls_DateTimeInLine.php");

if($_SESSION['username']==NULL)
{
    header("Location:".WEB_ROOT."/autenticazione/accesso_negato.php");
    die;
}

$cls_param = new cls_param();
$cls_date = new cls_DateTimeI("IT",false);


$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');



$nome_com = $a_enteAdmin["Denominazione"];

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];



$a_param = $cls_db->ExecuteQuery($cls_param->Get_Query_Tributi($c));

$numRows = mysqli_num_rows($a_param);
?>
<!-- GV 23/06/2022 START -->
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<!-- GV 23/06/2022   END -->
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
	var num_Rows = $('[name="num_Rows"]').val();
	
	control = submit_buttons('Salva');
	if(control)

	if((num_Rows == '0') ){

		swal({
				title: "ATTENZIONE",
				text: "STAI INSERENDO DEI TASSI D\'INTERESSE DERIVANTI DA UN MODELLO",
				icon: "warning",
				buttons: true,
				dangerMode: true,
			})
			.then((willDelete) => {
			if (willDelete) {
					swal("L\'OPERAZIONE È ANDATA A BUON FINE", {
					icon: "success",
				});
				
					$("#btnSub").trigger("click");
				
			} else {
				swal("NIENTE DI FATTO");
			}
		});

	}else{
		
		$("#btnSub").trigger("click");
	}

	
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

switchMenuImg("F11");
F11_button = function(){

    $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/Gestione_Interessi_Tributi.pdf"; ?>");
    $("#helpModalLabel").empty().append("<b>Help Gestione Interessi Tributi</b>");
    $("#helpModal").modal('show');
}


function new_tasso()
{
	//$(#new_flag).val(true);
	$('.nuovo_tasso_interesse').show();
}

</script>


<div class="row justify-content-md-center ">
	<div class="col col-md-auto text_center">
			<p class="titolo font16 under_decor">Gestione interessi tributi</p>
	</div>
</div>

<form class="form-horizontal validate" name=form_interessi_tributi id=form_interessi_tributi method=post action="gestione_interessi_tributi_salva.php">

<input type=hidden name=c value=<?php echo $c; ?> >
<input type=hidden name=a value=<?php echo $a; ?> >
<input type=hidden name=num_Rows value=<?php echo $numRows; ?> >
<input type=hidden name=new_flag id=new_flag value=false >

<table class="table table-sm table-hover text_center" border="0" cellspacing="2" cellpadding="0" style="width: 70%;">
	<colgroup>
		<col class="width5" style="border-bottom: 2px solid #B0BBE8;">
		<col class="width30" style="border-bottom: 2px solid #B0BBE8;">
    <col class="width30" style="border-bottom: 2px solid #B0BBE8;">
		<col class="width30" style="border-bottom: 2px solid #B0BBE8;">
    <col class="width5" style="border-bottom: 2px solid #B0BBE8;">
	</colgroup>
	<thead>
	<tr>
		<td style="border-bottom: 2px solid #B0BBE8;"></td>
		<td class="text_center " style="border-bottom: 2px solid #B0BBE8;"><span class="color_titolo">Data inizio tasso</span></td>
		<td class="text_center " style="border-bottom: 2px solid #B0BBE8;"><span class="color_titolo">Data fine tasso</span></td>
		<td class="text_center " style="border-bottom: 2px solid #B0BBE8;"><span class="color_titolo">Tasso di interesse</span></td>
		<td style="border-bottom: 2px solid #B0BBE8;"></td>
	</tr>
</thead>
<tbody>

<?php	//CICLO INTERESSI TRIBUTI
$i=0;
while($row = mysqli_fetch_assoc($a_param)){

?>

	<tr class="info">
		<td style="border-bottom: 2px solid #B0BBE8;"></td>
		<td class="text_left " style="border-bottom: 2px solid #B0BBE8;">

			<div class="form-group">
					<input type="text" style="width: 50%;" class="text_center picker form-control vld_dateConf startDate" id="inizio_<?php echo $i;?>" name='data_inizio[<?php echo $i; ?>]' value="<?php echo $cls_date->Get_DateNewFormat($row['Data_Inizio'],"DB"); ?>">
			</div>

		</td>
		<td class="text_left " style="border-bottom: 2px solid #B0BBE8;">
			<div class="form-group">
				<input type=text style="width: 50%;" class="text_center picker form-control vld_dateConfNoReq endDate" id="fine_<?php echo $i;?>" name=data_fine[<?php echo $i; ?>] value="<?php echo $cls_date->Get_DateNewFormat($row['Data_Fine'],"DB"); ?>">
			</div>
		</td>
		<td style="border-bottom: 2px solid #B0BBE8;">

			<div class="form-group " style="float:left; display:block;width: 80%; padding-left: 40%;">
				<input type=text style="width: 80%;"  class="text_right corrige_numero form-control vld_decReq" id=tasso_<?php echo $i; ?> name=tasso[<?php echo $i; ?>] value="<?php echo number_format($row['Tasso_Interessi'],2,",","."); ?>">
			</div>
			<div style="float:left; display:block;">%</div>
			<input type=hidden name=ID[<?php echo $i; ?>] value="<?php echo $row['ID']; ?>">
		</td>
		<td class="text_right " style="border-bottom: 2px solid #B0BBE8;">
		<?php if($i==$numRows-1)	{?>
			<a onMouseover="title='Nuovo tasso di interesse'" href="#" onClick="new_tasso();" style="text-decoration: none">
				<img src="<?= IMMAGINIWEB; ?>/Plus.png" width=20 height=20 border=0>
			</a>
		<?php  }?>
		</td>
	</tr>

<?php $i++;}

	if($numRows==0){
	/** GV 23/06/2022 START */
?>
<script>
	swal({
			title: "ATTENZIONE!",
			text: "NON ESISTONO TASSI D\'INTERESSE. UTILIZZA IL SEGUENTE MODELLO PER CREARLI",
			icon: "warning",
		});
</script>

	
<?php
	$query_mod= "SELECT * FROM interessi_tributi WHERE CC = '****' ORDER BY Data_Inizio ASC";
	
	$a_param_mod = $cls_db->ExecuteQuery($query_mod);

	$numRowsMod = mysqli_num_rows($a_param_mod);

	if($numRowsMod > 0)
	{
		$i=0;
		while($row = mysqli_fetch_assoc($a_param_mod)){
?>
	<tr class="info">
		<td style="border-bottom: 2px solid #B0BBE8;"></td>
		<td class="text_left " style="border-bottom: 2px solid #B0BBE8;">

			<div class="form-group">
					<input type="text" style="width: 50%;" class="text_center picker form-control vld_dateConf startDate" id="inizio_<?php echo $i;?>" name='data_inizio[<?php echo $i; ?>]' value="<?php echo $cls_date->Get_DateNewFormat($row['Data_Inizio'],"DB"); ?>">
			</div>

		</td>
		<td class="text_left " style="border-bottom: 2px solid #B0BBE8;">
			<div class="form-group">
				<input type=text style="width: 50%;" class="text_center picker form-control vld_dateConfNoReq endDate" id="fine_<?php echo $i;?>" name=data_fine[<?php echo $i; ?>] value="<?php echo $cls_date->Get_DateNewFormat($row['Data_Fine'],"DB"); ?>">
			</div>
		</td>
		<td style="border-bottom: 2px solid #B0BBE8;">

			<div class="form-group " style="float:left; display:block;width: 80%; padding-left: 40%;">
				<input type=text style="width: 80%;"  class="text_right corrige_numero form-control vld_decReq" id=tasso_<?php echo $i; ?> name=tasso[<?php echo $i; ?>] value="<?php echo number_format($row['Tasso_Interessi'],2,",","."); ?>">
			</div>
			<div style="float:left; display:block;">%</div>
			<input type=hidden name=ID[<?php echo $i; ?>] value="<?php echo $row['ID']; ?>">
		</td>
		<td class="text_right " style="border-bottom: 2px solid #B0BBE8;">
		<?php if($i==$numRowsMod-1)	{?>
			<a onMouseover="title='Nuovo tasso di interesse'" href="#" onClick="new_tasso();" style="text-decoration: none">
				<img src="<?= IMMAGINIWEB; ?>/Plus.png" width=20 height=20 border=0>
			</a>
		<?php  }?>
		</td>
	</tr>

<?php
			$i++;
		}
	}
/** GV 23/06/2022   END */

}
 else {
	 ?>

	<tr class="nuovo_tasso_interesse info" style="display:none;">
		<td style="border-bottom: 2px solid #B0BBE8;"></td>
		<td class="text_left " style="border-bottom: 2px solid #B0BBE8;">
			<div class="form-group">
				<input type=text class="text_center picker form-control vld_dateConf startDate" name=data_inizio[<?php echo $numRows; ?>] value="" style="width: 50%;" />
			</div>
		<td class="text_left " style="border-bottom: 2px solid #B0BBE8;">
			<div class="form-group">
				<input type=text class="text_center picker form-control vld_dateConfNoReq endDate" name=data_fine[<?php echo $numRows; ?>] value="" style="width: 50%;" />
			</div>
		<td class="text_left " style="border-bottom: 2px solid #B0BBE8;">
			<div class="form-group " style="float:left; display:block; width: 80%; padding-left: 40%;">
				<input type=text class="text_right corrige_numero form-control vld_decReq" id=tasso_<?php echo $numRows; ?> name=tasso[<?php echo $numRows; ?>] value="" style="width: 80%;" />
			</div>
			<div style="float:left; display:block;">%</div>
		<input type=hidden name=ID[<?php echo $numRows; ?>] value="">
		</td>
		<td class="text_right " style="border-bottom: 2px solid #B0BBE8;"></td>
	</tr>
<?php } ?>
	<tr>
		<td class="text_center" colspan=5>
			<span class="color_red">Lasciare vuota l'ultima "Data fine tasso" se non e' ancora stato definito un tasso successivo</span>
		</td>
	</tr>
</tbody>
</table>

<div class="form-group">
		<button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
</div>

</form>
<?php include(INC."/footer.php"); ?>
