<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC."/header.php");
include(INC."/menu.php");
include_once(CLS."/cls_CoazioneUtils.php");


$cls_coazione = new cls_Coazione();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');
$cls_db = new cls_db();
$cls_html = new cls_html();

/*$comune = $cls_db->getArrayLineNull("SELECT * FROM enti_gestiti WHERE CC = '".$c."'","enti_gestiti");

$comune = new ente_gestito($c);
$nome_com = $comune->Nome;
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];*/

//$codici = new codice_tributo(null);
$array_codici = $cls_coazione->array_ordinato("Settore, Sottosettore, Codice_Tributo");
$opt_settori = $cls_coazione->settori(true);

$query = "SELECT DISTINCT Sottosettore FROM codice_tributo WHERE Sottosettore !='' ORDER BY Sottosettore";
$a_sottosettori = $cls_db->getResults($cls_db->ExecuteQuery($query));
$a_selection = array("value"=>"Sottosettore","firstOpt"=>0,"selected"=>"Tutti","text"=>array("[Sottosettore]"));
$opt_sottosettori = $cls_html->getOptions($a_sottosettori, $a_selection);
?>
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
<!-- ********** GESTIONE LINK MENU ********** -->
<script>

//F5
switchMenuImg("F5");
F5_button = function(){
    location.href="lista_codici_tributo.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}


//F11-F12 sono nel menu'
</script>

<!-- ********** FILTRO ********** -->
<script>

function filtro()
{
	settore = $('#settore').val();
    sottosettore = $('#sottosettore').val();
	autorita = $('#autorita').val();

	if(settore=="Tutti" && autorita=="Tutte" && sottosettore=="Tutti")
	{
		$('#table_codici tr').show();	
	}
	else
	{
		$("#table_codici tr").hide();

        if(settore=="Tutti" && autorita=="Tutte")
		{
			$('.' + sottosettore).show();
		}
        else if(sottosettore=="Tutti" && autorita=="Tutte")
		{
			$('.' + settore).show();
		}
        else if(sottosettore=="Tutti" && settore=="Tutti")
		{
			$('.' + autorita).show();
		}
        else if(settore=="Tutti")
		{
			$('.' + sottosettore+'.' + autorita).show();
		}
		else if(autorita=="Tutte")
		{
			$('.' + sottosettore+'.' + settore).show();
		}
		else
		{
			$('.' + settore + '.'+autorita+'.' + sottosettore).show();
		}		
	}
}

</script>

<div class="row justify-content-md-center " style="margin-top: 1%;margin-bottom: 2%;">
    <div class="col col-md-auto text_center">
        <span class="titolo font16 under_decor">Lista codici tributo</span>
    </div>
</div>

<div class="tableFixHead" style="overflow-y: auto; max-height: 64vh !important; width: 80%; margin-left: 10%; overflow-y: auto; display: block;">
<table class="table table-hover" cellspacing="4" cellpadding="0" style="border-bottom:1px solid black;border-right:1px solid black;border-left:1px solid black;">
    <colgroup>
        <col style="width: 10%">
        <col style="width: 10%">
        <col style="width: 10%">
        <col style="width: 10%">
        <col style="width: 40%">
        <col style="width: 20%">
    </colgroup>
    <thead border="0" cellspacing=0 style="border-bottom: 2px solid #6963FF;">
        <tr >
            <th class=" text_center"><b>Codice</b><br><br></th>
            <th class=" text_center"><b>Tipo</b><br><br></th>
            <th class=" text_center"><b>Settore</b><br>
                <select name=settore id=settore onchange="filtro();">
                    <option>Tutti</option>
                    <?php echo $opt_settori; ?>
                </select>
            </th>
            <th ><b>Sottosettore</b><br>
                <select name=sottosettore id=sottosettore onchange="filtro();">
                    <option>Tutti</option>
                    <?php echo $opt_sottosettori; ?>
                </select>
            </th>
            <th class=" text_left" ><b>Descrizione</b><br><br></th>
            <th class=" text_center" ><b>Autorita'</b><br>
                <select name=autorita id=autorita onchange="filtro();">
                    <option>Tutte</option>
                    <option value="giustizia">Giustizia ord.</option>
                    <option value="commissione">Comm. tributaria</option>
                </select>
            </th>
        </tr>
    </thead>

    <tbody class="table table-hover text_center" id="table_codici" border="0" cellspacing=0>
    <?php


    for($i=0;$i<count($array_codici);$i++)
    {

        switch($array_codici[$i]['Autorita_Ricorso'])
        {
            case "Giustizia ordinaria":		$class_autorita = "giustizia"; 		break;
            case "Commissione tributaria": 	$class_autorita = "commissione"; 	break;
            default:						$class_autorita = "";				break;
        }

    ?>

        <tr class=" <?php echo $array_codici[$i]['Settore']; ?> <?php echo $class_autorita; ?> <?php echo $array_codici[$i]['Sottosettore']; ?> info" >
            <td  ><?php echo $array_codici[$i]['Codice_Tributo']; ?></td>
            <td ><?php echo $array_codici[$i]['Tipo_Codice']; ?></td>
            <td ><?php echo $array_codici[$i]['Settore']; ?></td>
            <td ><?php echo $array_codici[$i]['Sottosettore']; ?></td>
            
            <td class=" text_left" ><?php echo $array_codici[$i]['Descrizione']; ?></td>
            <td class=" text_center" ><?php echo $array_codici[$i]['Autorita_Ricorso']; ?></td>
        </tr>

    <?php
        }
    ?>

    </tbody>
</table>
</div>

<?php include(INC."/footer.php"); ?>