<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

include(INC."/header.php");
//include(CLS."/cls_db.php");
include(INC."/menu.php");
include_once(CLS."/cls_paramUtils.php");

$cls_param = new cls_param();

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');
$id_magg = $cls_help->getVar('id_magg');

$nome_com = $a_enteAdmin["Denominazione"];

$nome_comune =($nome_com==NULL?"":$nome_com." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$query = "SELECT * FROM coefficiente_coazione WHERE ID = '".$id_magg."'";

$result = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"coefficiente_coazione");

$array_descrizione = $cls_param->Get_Data_Tariffa_Coazione($c,$id_magg);
$pignoramento = "";

$QueryNextPrec = "SELECT * FROM coefficiente_coazione WHERE CC = '".$c."' ORDER BY Credito_Minimo ASC";

$cls_db = new cls_db();
$nextPrec = $cls_db->getResults($cls_db->ExecuteQuery($QueryNextPrec));

$next = null;
$prec = null;
for ($i=0; $i < count($nextPrec); $i++){
    if($nextPrec[$i]["ID"] == $id_magg) {
        $prec = isset($nextPrec[$i-1]["ID"])?$nextPrec[$i-1]["ID"]:$id_magg;
        $next = isset($nextPrec[$i+1]["ID"])?$nextPrec[$i+1]["ID"]:$id_magg;
    }
}

if($next == null && $prec == null)
    $prec = $next = isset($nextPrec[0]["ID"])?$nextPrec[0]["ID"]:null;





?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>

    var id_magg = "<?php echo $id_magg; ?>";

    //F3
    switchMenuImg("F3");
    F3_button = function()
    {
        var control = false;

        if(id_magg == "0"){ control = submit_buttons('Insert');}
        else { control = submit_buttons('Update'); }

        if(control/* && validateForm()*/)
            $("#btnSub").trigger("click");
    }

    //F4
    switchMenuImg("F4");
    F4_button = function()
    {
        var control = submit_buttons('Delete');
        if(control)
            $("#btnSub").trigger("click");
    }

    //F5
    switchMenuImg("F5");
    F5_button = function()
    {
        location.href="par_maggiorazioni_coazione.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&id_magg=<?php echo $id_magg; ?>";
    }

    //F6
    switchMenuImg("F6");
    F6_button = function()
    {
        location.href="par_maggiorazioni_coazione.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&id_magg=0";
    }

    //F11-F12 sono nel menu'
    //F7
    switchMenuImg("F7");
    F7_button = function()
    {
        location.href="par_maggiorazioni_coazione.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&id_magg=<?= $prec; ?>";
    }
    //F8
    switchMenuImg("F8");
    F8_button = function()
    {
        location.href="par_maggiorazioni_coazione.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&id_magg=<?= $next; ?>";
    }

</script>


<div class="row justify-content-md-center ">
    <div class="col col-md-auto text_center">
        <p class="titolo font16 under_decor">Gestione maggiorazione</p>
    </div>
</div>


<form name=form_par_tariffe class="form-horizontal validate" id=form_par_tariffe method=post action="par_maggiorazione_coazione_salva.php">

    <input type=hidden name=invia_submit 	id=invia_submit 	value=""  			>

    <input type=hidden name=c 					value=<?php echo $c; ?> 			>
    <input type=hidden name=a 					value=<?php echo $a; ?> 			>
    <input type=hidden name=magg_id 	id=magg_id 	value="<?php echo $id_magg; ?>"   	>


    <div class="row">
        <div class="col col-lg-5 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-5 control-label resize font16" style="text-align: left;">Importo da &euro;</label>
                <div class="col-lg-7 ">
                    <input class="form-control resize vld_decReq" id=credito_minimo name=credito_minimo value="<?= $result["Credito_Minimo"]; ?>">
                </div>
            </div>
        </div>
        <div class="col col-lg-5">
            <div class="form-group">
                <label class="col-lg-5 control-label resize text_left" style="text-align: left;">Importo a &euro;</label>
                <div class="col-lg-7">
                    <input class="form-control resize vld_dec" id=credito_massimo name=credito_massimo value="<?= $result["Credito_Massimo"]; ?>">
                </div>
            </div>
        </div>
    </div>
    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%; margin-bottom: 1%;"></div>
    <div class="row">
        <div class="col col-lg-5 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-5 control-label resize text_left">Percentuale magg. (%)</label>
                <div class="col-lg-7">
                    <input class="form-control resize vld_int" id=percentuale name=percentuale value="<?= $result["Percentuale"]; ?>" >
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <button type="submit" id="btnSub" class="btn btn-primary" name="signup" style="display: none;" value="Submit"></button>
    </div>


</form>

<!--<script src="<?= JS ?>/myValidator.js" type="text/javascript"></script>

<script>
    $(document).ready(function(){
        addClass("credito_minimo","vld_Custom_r");
        //addClass("credito_minimo","vld_Custom_d");
        //addClass("credito_massimo","vld_Custom_d");
        addClass("percentuale","vld_Custom_r");
        addClass("percentuale","vld_Custom_nf");
    });
</script>-->

<?php include(INC."/footer.php"); ?>
