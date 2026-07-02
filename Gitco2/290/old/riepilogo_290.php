<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
include(INC."/menu.php");
include_once(CLS."/cls_Utils.php");
include_once(CLS."/cls_DateTimeInLine.php");
include_once CLS . "/cls_CoazioneUtils.php";

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

$progr_n0 = $cls_help->getVar('id_n0');

set_time_limit(200);

$query = "SELECT * FROM 290_n0_n9 WHERE ID = '".$progr_n0."'";
$duenovanta = $cls_db->getObjectLineNull($cls_db->ExecuteQuery($query),"290_n0_n9");

//$duenovanta = new N0N9( $progr_n0 );
$num_N1 = $duenovanta->Record_N1;
$num_N2 = $duenovanta->Record_N2;
$num_N3 = $duenovanta->Record_N3;
$num_N4 = $duenovanta->Record_N4;

//$riepilogo = new riepilogo($progr_n0);

$query = "SELECT ID FROM 290_n1_n5 WHERE N0_ID = '".$progr_n0."'";
$n1_id = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"290_n1_n5")["ID"];// single_answer_query($query);

$query = "SELECT * FROM 290_n2 WHERE N1_ID = '".$n1_id."'";
$result = $cls_db->getResults($cls_db->ExecuteQuery($query));// safe_query($query);

$corretti = 0;
$omoN2corretti = 0;
$omoN3corretti = 0;
$scarti = 0;
$omoN2scarti = 0;
$omoN3scarti = 0;
$N3corretti = 0;
$N4corretti = 0;

for($x=0;$x<count($result);$x++)
{
    $n2_id = $result[$x]['ID'];
    $import = $result[$x]['Flag_Importazione'];
    $partita = $result[$x]['Flag_Partita'];

    if($partita == "si" )
    {
        switch($import)
        {
            case "ok": $corretti += 1; break;
            case "N2": $omoN2corretti += 1; break;
            case "N3": $omoN3corretti += 1; break;
        }
    }
    else if ($partita == "no")
    {
        switch($import)
        {
            case "Scarto": $scarti += 1; break;
            case "N2": $omoN2scarti += 1; break;
            case "N3": $omoN3scarti += 1; break;
        }
    }

    $query = "SELECT * FROM 290_n3 WHERE N2_ID = '".$n2_id."'";
    $N3control = $cls_db->getResults($cls_db->ExecuteQuery($query));// select_mysql_array("*", "290_n3", "N2_ID = '".$n2_id."'");

    for($j=0;$j<count($N3control);$j++)
    {
        if($N3control[$j]['Flag_Importazione'] == "ok")
            $N3corretti += 1;
    }

    $query = "SELECT * FROM 290_n4 WHERE N2_ID = '".$n2_id."'";
    $N4control = $cls_db->getResults($cls_db->ExecuteQuery($query));//select_mysql_array("*", "290_n4", "N2_ID = '".$n2_id."'");

    for($j=0;$j<count($N4control);$j++)
    {
        if($N4control[$j]['Flag_Importazione'] == "ok")
            $N4corretti += 1;
    }

}

$num_partite = $corretti + $omoN2corretti;

flush();
ob_flush();

?>

<script>

function ruolo()
{
	location.href = "../coattiva/gestione_ruolo.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

</script>

        <div class="row justify-content-md-center ">
            <div class="col col-md-auto text_center">
                <span class="titolo font18">Riepilogo Importazione</span>
            </div>
        </div>
        <div class="row justify-content-md-center " style="margin-bottom: 3%;">
            <div class="col col-md-auto text_center">
                <span class="titolo font16">290</span>
            </div>
        </div>

		<div class="row">
            <div class="col-lg-10 col-lg-offset-1">
                <table class="text_center table_interna" border=0>
                    <tr>
                        <td class="width30"></td>
                        <td class="text_left width30"><font class="color_red font16">Ruoli ( N1-N5 ) :</font></td>
                        <td class="text_right"><?php echo $num_N1; ?></td>
                        <td class="width30"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class="text_left"><font class="color_red font16">Intestatari ( N2 ) :</font></td>
                        <td class="text_right"><?php echo $num_N2; ?></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class="text_left"><font class="color_red font16">Coobbligati/Cointestatari ( N3 ) :</font></td>
                        <td class="text_right"><?php echo $num_N3; ?></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class="text_left"><font class="color_red font16">Record contabili ( N4 ) :</font></td>
                        <td class="text_right"><?php echo $num_N4; ?></td>
                        <td></td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="row justify-content-md-center " style="margin-top: 3%; margin-bottom: 3%;">
            <div class="col col-md-auto text_center">
                <span class="titolo font16">Dati Importati</span>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-10 col-lg-offset-1">
                <table class="text_center table_interna">
                    <tr>
                        <td class="text_left width15"></td>
                        <td class="text_left width25">Intestatari :</td>
                        <td class="text_left width10"><?php echo $corretti; ?></td>
                        <td class="text_left width30">Coobbligati/Cointestatari :</td>
                        <td class="text_left width10"><?php echo $N3corretti; ?></td>
                        <td class="text_left width10"></td>
                    </tr>
                    <tr>
                        <td class="text_left width15"></td>
                        <td class="text_left width25">Omonimie corrette :</td>
                        <td class="text_left width10"><?php echo $omoN2corretti; ?></td>
                        <td class="text_left width30">Omonimie corrette :</td>
                        <td class="text_left width10"><?php echo $omoN3corretti; ?></td>
                        <td class="text_left width10"></td>
                    </tr>
                    <tr>
                        <td class="text_left width15"></td>
                        <td class="text_left width25">Omonimie parziali :</td>
                        <td class="text_left width10"><?php echo $omoN2scarti; ?></td>
                        <td class="text_left width30">Omonimie parziali :</td>
                        <td class="text_left width10"><?php echo $omoN3scarti; ?></td>
                        <td class="text_left width10"></td>
                    </tr>
                    <tr>
                        <td class="text_left width15"></td>
                        <td class="text_left width25">Partite contabili :</td>
                        <td class="text_left width10"><?php echo $num_partite; ?></td>
                        <td class="text_left width30">Record contabili :</td>
                        <td class="text_left width10"><?php echo $N4corretti; ?></td>
                        <td class="text_left width10"></td>
                    </tr>
                </table>
                <div class="row" style="margin-top: 3%;">
                    <div class="col-lg-12">
                        <input type=button value="Gestione Ruolo" class="btn btn-primary text_center" onclick="ruolo();">
                    </div>
                </div>

            </div>
        </div>

<?php include(INC."/footer.php"); ?>