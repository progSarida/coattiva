<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
include(INC."/menu.php");
include_once(CLS."/cls_paramUtils.php");
include_once CLS . "/cls_db.php";

$cls_param = new cls_param();
$cls_db = new cls_db();

if($_SESSION['username']==NULL)
{
	header("Location:/gitco2/autenticazione/accesso_negato.php");
	die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

//$comune = new ente_gestito($c);
$nome_com = $a_enteAdmin["Denominazione"];
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$QUERY = $cls_param->Get_Query_Tariffe_Gen($c);

$una_tantum = $cls_db->getResults($cls_db->ExecuteQuery($QUERY["Una_Tantum"]));
$a_giorni = $cls_db->getResults($cls_db->ExecuteQuery($QUERY["A_Giorni"]));
$a_km = $cls_db->getResults($cls_db->ExecuteQuery($QUERY["A_Km"]));

?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>


//F5
switchMenuImg("F5");
F5_button = function()
{
	location.href="lista_tariffe_generali.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

//F6
switchMenuImg("F6");
F6_button = function()
{
	location.href="par_tariffe_coazione.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&id_tariffa=0";
}

//F11-F12 sono nel menu'

</script>

<!-- ********** FILTRO ********** -->
<script>

function scelta_tariffa(value)
{
	location.href="par_tariffe_coazione.php?id_tariffa="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

</script>


<div class="row justify-content-md-center " style="margin-bottom: 2%;">
	<div class="col col-md-auto text_center">
			<span class="titolo font16 under_decor">Lista tariffe pignoramento</span>
	</div>
</div>

<div class="row">
	<div class="col-lg-10 col-lg-offset-1" >
		<table id="table_codici" class="text_center table_interna table table-hover" style="border-top: 2px solid #8F94FF; border-bottom: 2px solid #8F94FF;" cellspacing=0>
		<?php


		for($i=0;$i<count($una_tantum);$i++)
		{

			$class_tipo = "UNA TANTUM";

		?>
			<tr class=" <?php echo $class_tipo; ?> info" style="border-bottom: 2px solid #8F94FF;">
		  	<td class="width5 text_center">
					<a onMouseover="title='Dettagli tariffa'" href="#" onclick="scelta_tariffa('<?php echo $una_tantum[$i]['ID']; ?>');" style="text-decoration: none;">
						<img src="<?= IMMAGINIWEB; ?>/select.png" width=25 height=25 border=0>
					</a>
				</td>
		  	<td class="width2 text_center"></td>
		    <td class="width91 text_left" ><div><b><?php echo $una_tantum[$i]['Descrizione']." ".$una_tantum[$i]['Deposito_Portata']; ?></b></div><div><?php echo $una_tantum[$i]['Tipo']; ?></div></td>
		  	<td class="width2 text_center"><br></td>
			</tr>

		<?php
		}

		for($i=0;$i<count($a_giorni);$i++)
		{

			$class_tipo = "A GIORNI";

			$tariffa_fissa = "";

			?>
			<tr class="<?php echo $class_tipo; ?> info" style="border-bottom: 2px solid #8F94FF;">
				<td class="width5 text_center">

				<a onMouseover="title='Dettagli tariffa'" href="#" onclick="scelta_tariffa('<?php echo $a_giorni[$i]['ID']; ?>');" style="text-decoration: none;">
					<img src="<?= IMMAGINIWEB; ?>/select.png" width=25 height=25 border=0>
				</a>

				</td>
		    	<td class="width2 text_center"></td>
		    	<td class="width91 text_left" ><div><b><?php echo $a_giorni[$i]['Descrizione']." ".$a_giorni[$i]['Deposito_Portata']; ?></b></div><div><?php echo $a_giorni[$i]['Tipo']; ?></div></td>
		    	<td class="width2 text_center"><br></td>
			</tr>

		<?php
		}

		for($i=0;$i<count($a_km);$i++)
		{

			$class_tipo = "A KM";

			$tariffa_fissa = "";
		?>
			<tr class="<?php echo $class_tipo; ?> info" style="border-bottom: 2px solid #8F94FF;">
				<td class="width5 text_center">

				<a onMouseover="title='Dettaglio tariffa'" href="#" onclick="scelta_tariffa('<?php echo $a_km[$i]['ID']; ?>');" style="text-decoration: none;">
					<img src="<?= IMMAGINIWEB; ?>/select.png" width=25 height=25 border=0>
				</a>

				</td>
				<td class="width2 text_center"></td>
		    	<td class="width91 text_left" ><div><b><?php echo $a_km[$i]['Descrizione']." ".$a_km[$i]['Deposito_Portata']; ?></b></div><div><?php echo $a_km[$i]['Tipo']; ?></div></td>
		    	<td class="width2 text_center"><br></td>
			</tr>

		<?php
		}
		?>

		</table>
	</div>
</div>


<?php include(INC."/footer.php"); ?>
