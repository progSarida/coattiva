<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");
include_once(CLS."/cls_CoazioneUtils.php");
include_once(CLS."/cls_math.php");

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$cls_coazione = new cls_Coazione();
$cls_math = new cls_math();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

$tariffe = $cls_coazione->array_tariffe($c);

$una_tantum = $tariffe["Una_Tantum"];
$a_giorni = $tariffe["A_Giorni"];
$a_km = $tariffe["A_Km"];
?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>



//F5
switchMenuImg("F5");
F5_button = function(){
    location.href="lista_tariffe.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

//F11-F12 sono nel menu'

</script>
    <style>
        .tableFixHead thead th
        {
            position: sticky;
            top: 0;
            background-color: #ACB1E8;
        }
        .table thead > tr > th { border-bottom: none; }
        .table thead > tr > th { border-bottom: 1px solid black; }
        /*.table tbody > tr > td { rgb(153, 204, 255) background-color: rgb(153, 204, 255); }*/
    </style>

<!-- ********** FILTRO ********** -->
<script>

function filtro()
{
	value = $('#settore').val();
	value2 = $('#autorita').val();

	if(value=="Tutti" && value2=="Tutte")
	{
		$('#table_codici tr').show();	
	}
	else
	{
		$("#table_codici tr").hide();
		
		if(value=="Tutti")
		{
			$('.' + value2).show();
		}
		else if(value2=="Tutte")
		{
			$('.' + value).show();
		}
		else
		{
			$('.' + value + '.'+value2).show();
		}		
	}
}

</script>

<div class="row justify-content-md-center " style="margin-top: 1%;margin-bottom: 2%;">
    <div class="col col-md-auto text_center">
        <span class="titolo font16 under_decor">Lista tariffe</span>
    </div>
</div>

<div class="tableFixHead" style="overflow-y: auto; max-height: 63vh !important; width: 80%; margin-left: 10%; overflow-y: auto; display: block;">
<table class="table table-hover" cellspacing="4" cellpadding="0"  style="border-bottom: 1px solid black;border-right: 1px solid black;border-left: 1px solid black;">
    <colgroup>
        <col style="width: 30%">
        <col style="width: 20%">
        <col style="width: 50%">
    </colgroup>
<thead border="0" cellspacing=0 style="border-bottom: 2px solid #6963FF;">
	<tr >
        <th class="width15 text_left" ><b>Descrizione</b></br><b>Tipo</b></th>
    	<th class="width15" ><b>Tariffa</b></th>
    	<th class="width63 text_left" ><b>Tariffa fissa</b></th>
	</tr>
</thead>
	
<tbody id="table_codici" class="text_center table_interna" border="0" cellspacing=0>
<?php 


for($i=0;$i<count($una_tantum);$i++)
{      	
	
	$class_tipo = "UNA TANTUM";
		
?>

	<tr class=" <?php echo $class_tipo; ?> info" >
        <td class="text_left" ><?php echo $una_tantum[$i]['Descrizione']." ".$una_tantum[$i]['Deposito_Portata']; ?></b><br><?php echo $una_tantum[$i]['Tipo']; ?></td>
    	<td class="text_center" ><?php echo number_format($una_tantum[$i]['Importo'],2,",","."); ?> &euro;</td>
    	<td class="text_center"><br></td>
	</tr>

<?php 
} 
	
for($i=0;$i<count($a_giorni);$i++)
{

	$class_tipo = "A GIORNI";

	$tariffa_fissa = "";

	if($a_giorni[$i]['Importo_Fisso'] != null)	$tariffa_fissa = "Importo di ".$cls_math->conv_num(number_format($a_giorni[$i]['Importo_Fisso'],2))." &euro; per i primi ".$a_giorni[$i]['Km_Giorni_Importo_Fisso']." giorni";
	
	?>
	<tr class=" <?php echo $class_tipo; ?> info" >
    	<td class="text_left" ><b><?php echo $a_giorni[$i]['Descrizione']." ".$a_giorni[$i]['Deposito_Portata']; ?></b><br><?php echo $a_giorni[$i]['Tipo']; ?></td>
    	<td class="text_center" ><?php echo number_format($a_giorni[$i]['Importo'],2,",","."); ?> &euro;</td>
    	<td class="text_left" ><?php echo $tariffa_fissa; ?></td>
	</tr>

<?php 
} 
		
for($i=0;$i<count($a_km);$i++)
{

	$class_tipo = "A KM";

	$tariffa_fissa = "";
	
	if($a_km[$i]['Importo_Fisso'] != null)	$tariffa_fissa = "Importo di ".$cls_math->conv_num(number_format($a_km[$i]['Importo_Fisso'],2))." &euro; per i primi ".$a_km[$i]['Km_Giorni_Importo_Fisso']." km";

?>
	<tr class=" <?php echo $class_tipo; ?> info" >
    	<td class="text_left" ><b><?php echo $a_km[$i]['Descrizione']." ".$a_km[$i]['Deposito_Portata']; ?></b><br><?php echo $a_km[$i]['Tipo']; ?></td>
    	<td class="text_center" ><?php echo number_format($a_km[$i]['Importo'],2,",","."); ?> &euro;</td>
    	<td class="text_left" ><?php echo $tariffa_fissa; ?></td>
	</tr>

<?php 
} 
?>

</tbody>
</table>
</div>
<?php include(INC."/footer.php"); ?>