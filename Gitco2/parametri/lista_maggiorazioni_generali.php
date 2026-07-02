<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

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

/*$comune = new ente_gestito($c);
$nome_com = $a_enteAdmin["Denominazione"];
$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];*/

$query = "SELECT * FROM coefficiente_coazione WHERE CC = '".$c."' ORDER BY Credito_Minimo ASC";

$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

if(count($result) == 0){
    $query = "INSERT INTO coefficiente_coazione (CC, Percentuale, Credito_Minimo, Credito_Massimo) SELECT '".$c."',Percentuale, Credito_Minimo, Credito_Massimo FROM coefficiente_coazione WHERE CC = '*****';";
    $check = $cls_db->ExecuteQuery($query);

    $query = "SELECT * FROM coefficiente_coazione WHERE CC = '".$c."'";
    $result = $cls_db->getResults($cls_db->ExecuteQuery($query));
}




?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>


    //F5
    switchMenuImg("F5");
    F5_button = function()
    {
        location.href="lista_maggiorazioni_generali.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }

    //F6
    switchMenuImg("F6");
    F6_button = function()
    {
        location.href="par_maggiorazioni_coazione.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&id_magg=0";
    }

    //F11-F12 sono nel menu'

</script>

<!-- ********** FILTRO ********** -->
<script>

    function scelta_maggiorazione(value)
    {
        location.href="par_maggiorazioni_coazione.php?id_magg="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }

</script>


<div class="row justify-content-md-center " style="margin-bottom: 2%;">
    <div class="col col-md-auto text_center">
        <span class="titolo font16 under_decor">Lista maggiorazioni pignoramento</span>
    </div>
</div>

<div class="row">
    <div class="col-lg-10 col-lg-offset-1" >
        <table id="table_codici" class="text_center table_interna table table-hover" style="border-top: 2px solid #8F94FF; border-bottom: 2px solid #8F94FF;" cellspacing=0>
            <?php


            for($i=0;$i<count($result);$i++)
            {
                $text = "";
                if($result[$i]['Credito_Minimo'] > 0 && $result[$i]['Credito_Massimo'] > 0){
                    $text = "Da ". $result[$i]['Credito_Minimo']." A ".$result[$i]['Credito_Massimo'];
                }
                else if($result[$i]['Credito_Minimo'] > 0){
                    $text = "Da ". $result[$i]['Credito_Minimo'];
                }
                else{
                    $text = "Fino A ".$result[$i]['Credito_Massimo'];
                }
                ?>
                <tr class="info" style="border-bottom: 2px solid #8F94FF;">
                    <td class="width5 text_center">
                        <a onMouseover="title='Dettagli tariffa'" href="#" onclick="scelta_maggiorazione('<?php echo $result[$i]['ID']; ?>');" style="text-decoration: none;">
                            <img src="<?= IMMAGINIWEB; ?>/select.png" width=25 height=25 border=0>
                        </a>
                    </td>
                    <td class="width2 text_center"></td>
                    <td class="width91 text_left" ><div><b><?php echo $text; ?></b></div><div><?php echo "Maggiorazione del ".$result[$i]['Percentuale']."%"; ?></div></td>
                    <td class="width2 text_center"><br></td>
                </tr>

                <?php
            }
            ?>

        </table>
    </div>
</div>


<?php include(INC."/footer.php"); ?>

