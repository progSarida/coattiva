<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";


include(INC."/header.php");
include(INC."/menu.php");

include_once CLS . "/cls_DateTime.php";

$a = $cls_help->getVar("a");
$c = $cls_help->getVar("c");
$cc_el = $cls_help->getVar("cc_el");
$cod_cat = $cls_help->getVar('codcat');
$last_el_id =  $cls_help->getVar('el');
$tipoatto = $cls_help->getVar('tipoatto');
$checkMissing = $cls_help->getVar("checkMissing");

$tipoSoggetto = $cls_help->getVar("tipoSogg");
if($tipoSoggetto == null)
    $tipoSoggetto = "E";

$query = "SELECT * FROM ini_pec_processing WHERE UserId = ".$_SESSION['aut_progr'];
$a_auth = $cls_db->getArrayLine($cls_db->ExecuteQuery($query),"ini_pec_processing");
if(empty($a_auth)){
    ?>
    <script>
        alert("Procedura non abilitata per l'utente <?= $_SESSION['username']; ?>");
        history.back();
    </script>
<?php
    die;
}

$query = "SELECT * FROM ini_pec_request WHERE EsitoRichiesta='true' and EsitoFornitura is null and UserName='".$a_auth['UserName']."'";
$resultRequest = $cls_db->getResults($cls_db->ExecuteQuery($query));

$CC = $cc_el==null?$c:$cc_el;

$query = "SELECT P.CF_PI, P.CC, P.Denominazione_Ente FROM v_partita P JOIN elaborations E ON E.Id=P.Elaboration_Id ";
$query.= "WHERE P.CF_PI!='00000000000' AND P.CF_PI!='' AND P.CF_PI is not null ";
$query.= "AND (DATEDIFF('".date('Y-m-d')."', P.InipecLoaded)>15 OR P.InipecLoaded is null)  ";
$query.= "AND E.Elaboration_Status_Id in (2,10) AND P.CC = '".$CC."'";

if($tipoSoggetto=='F')
    $query.="AND P.Genere != 'D' ";
else if($tipoSoggetto=='D')
    $query.="AND P.Genere = 'D' ";

$query.= "GROUP BY P.CC, P.CF_PI ORDER BY P.CC, P.CF_PI ";
//var_dump($query);

$a_inipec = $cls_db->getResults($cls_db->ExecuteQuery($query));

if($checkMissing=="yes" && count($a_inipec) == 0 && $cc_el != null){
    
    switch($tipoatto)
    {
        case 6 :
        case 7 :
        case 8 :
        case 13:
        case 14:
        case 24:
        case 29:
        case 31:
        case 34:
        case 37:
        case 42:
        case 22:
            echo "<script>top.location.href = '".ELAB_PIGNORAMENTI_WEB."/mgmt_pignoramenti.php?c=".$c."&a=".$a."&cc_el=".$cc_el."&codcat=".$cod_cat."&el=".$last_el_id."&tipoatto=".$tipoatto."';</script>";
            break;

        default:
            echo "<script>top.location.href = '".ELAB_ATTI_WEB."/mgmt_elaboration.php?c=".$c."&a=".$a."&cc_el=".$cc_el."&codcat=".$cod_cat."&el=".$last_el_id."&tipoatto=".$tipoatto."';</script>";
            break;
    }
    
    die;
}


$arr_result = array();
$arr_PI_CF = array();
$group = 0;

foreach ($a_inipec as $key=>$a_cf){
    if(!isset($arr_result[$group])){
        $arr_PI_CF[$a_cf['CC']][] = $a_cf["CF_PI"];

        $arr_result[$group] = array(
            "CC" => $a_cf['CC'],
            "Ente" => $a_cf['CC'] . " - " . $a_cf['Denominazione_Ente'],
            "NumRecord" => 1,
            "Limit" => ""
        );
    }
    else{
        if(array_search($a_cf["CF_PI"],$arr_PI_CF[$a_cf['CC']]) === false) {
            array_push($arr_PI_CF[$a_cf['CC']],$a_cf["CF_PI"]);
            $arr_result[$group]["NumRecord"]+= 1;

            if($arr_result[$group]["NumRecord"] % 200 == 0){
                $arr_result[$group]["Limit"] = "LIMIT ".($group * 200).", 200";
                $group++;
            }
        }
    }
}

if(count($arr_result) > 0) {
    if (isset($arr_result[$group])) {
        if ($arr_result[$group]["NumRecord"] % 200 != 0) {
            $arr_result[$group]["Limit"] = "LIMIT " . ($group * 200) . ", " . $arr_result[$group]["NumRecord"];
        }
    } else {
        if ($arr_result[$group - 1]["NumRecord"] % 200 != 0) {
            $arr_result[$group - 1]["Limit"] = "LIMIT " . (($group - 1) * 200) . ", " . $arr_result[$group - 1]["NumRecord"];
        } else {
            $arr_result[$group - 1]["Limit"] = "LIMIT " . (($group - 1) * 200) . ", 200";
        }
    }
}

?>

<script>
//F3
switchMenuImg("F3");
F3_button = function()
{
	control_salva = submit_buttons('Salva');
	if(control_salva)
			$("#btnSub").trigger("click");
}

//F5
switchMenuImg("F5");
F5_button = function()
{
	stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>&cc_el=<?= $cc_el ?>&codcat=<?= $cod_cat ?>&el=<?= $last_el_id ?>&tipoatto=<?= $tipoatto ?>";
	stringa = "prc_inipec.php?"+stringaPHP;
	   	top.location.href = stringa;
}

switchMenuImg("F11");
F11_button = function(){

    $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/IniPEC.pdf"; ?>");
    $("#helpModalLabel").empty().append("<b>Help IniPEC</b>");
    $("#helpModal").modal('show');

}

//PAG GIU
switchMenuImg("pagedown");
pagedown_button = function(){
	if( modifica == 0 )
 {
	 location.href = "stemma.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
 }
 else
	 alert("salvare i dati o annullare prima di procedere");
}

//PAG SU
switchMenuImg("pageup");
pageup_button = function(){
	if( modifica == 0 )
	{
		location.href = "gestore.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
	}
	else
		alert("salvare i dati o annullare prima di procedere");
}

function saveCredential(){
    if(validateForm())
        $.ajax(
        {
            url: "ajax/save_credential.php",
            method: 'POST',
            dataType: "json",
            data: {
                "old_user": $("#old_user").val(),
                "old_passw": $("#old_passw").val(),
                "user": $("#user").val(),
                "passw_1": $("#passw_1").val(),
                "passw_2": $("#passw_2").val()
            },
            success: function(resp) {

                if(resp.status=="OK") {
                    $("#messaggioRisultato").removeClass("alert-success alert-warning alert-danger");
                    $("#messaggioRisultato").addClass("alert-success");
                    $("#messaggioRisultato").empty().append(resp.message);
                    $("#messaggioRisultato").fadeIn();
                    setTimeout(function() {
                        $("#messaggioRisultato").fadeOut();
                    }, 8000);

                }
                else {
                    $("#messaggioRisultato").removeClass("alert-success alert-warning alert-danger");
                    $("#messaggioRisultato").addClass("alert-danger");
                    $("#messaggioRisultato").empty().append(resp.message);
                    $("#messaggioRisultato").fadeIn();
                    setTimeout(function() {
                        $("#messaggioRisultato").fadeOut();
                    }, 8000);

                }
            },
            error: function(resp){
                $("#messaggioRisultato").removeClass("alert-success alert-warning alert-danger");
                $("#messaggioRisultato").addClass("alert-danger");
                $("#messaggioRisultato").empty().append(resp.responseText);
                $("#messaggioRisultato").fadeIn();
                setTimeout(function() {
                    $("#messaggioRisultato").fadeOut();
                }, 8000);
            }
        });
}

function startSpiners(){
    //alert("show");
    //$("#caricamento_spiners").css("display","block");
    $("#caricamento_spiners").show();
}

function closeSpiner(){
    //alert("close");
    $("#caricamento_spiners").hide();
}

$(document).ready(function(){
    $(document).on('submit','form#f_inipec_download',function(){
        startSpiners();
    });

    $(document).on('submit','form#f_inipec_upload',function(){
        startSpiners();
    });
});

</script>
<style>
    .table_header_H {
        background-color: #0385FF;
        color: white;
        border-right: 1px solid white;
        border-bottom: 2px solid #0262BD;
        border-top: 2px solid #0262BD;
    }
    .table_body {
        background-color: #08C1FF;
        border-right: 1px solid white;
        border-bottom: 1px solid white !important;
    }
    .table_caption_button {
        background-color: #8795FF;
        border-bottom: 1px solid white;
    }

    #aggiorna_ut_psw {
        color: green;
    }
    #aggiorna_ut_psw:hover {
        color: darkgreen;
    }

    .back_spiners {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        background: rgba(0,0,0,0.80);
        z-index: 10000;
    }
</style>

<div class="back_spiners" id="caricamento_spiners">
    <div class="d-flex align-items-center text-info" style="width: 220px;position: fixed;top:45%;left:45%;text-align: center;">
        <div style="display: inline;"><img style="width: 100%; border-radius: 100px;opacity: 0.5;" src="<?= GIFWEB ?>/loading_3.gif"></div>
        <div id="text_spiners" style="display: inline;font-size: 18px;width:100%;text-align: center;font-weight: bold;">Loading...</div>
    </div>
</div>

<div class="row justify-content-md-center" style="margin-bottom: 3%;">
    <div class="col col-md-auto text_center">
        <span class="titolo font16 under_decor">Ini PEC</span>
    </div>
</div>

<div class="modal fade" id="credentialModal" tabindex="-1" aria-labelledby="credentialModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="width: 45% !important;height: 80vh !important;">
        <div class="modal-content" style="height: 80vh !important;">
            <div class="modal-header">
                <h5 class="modal-title" id="credentialModalLabel" style="color: blue;font-weight: bold;">Aggiorna credenziali</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="position: relative !important;bottom: 2vh !important;">
                    <span aria-hidden="true" ><i class="fa fa-times" aria-hidden="true"></i></span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12">
                        <p style="text-align: center;"><b>Inserire nuova password e user nella pagina web che si apre col bottone verde qui sotto. <p style="color: blue;text-align: center;">Successivamente reinserire le nuove credenziali anche nella form qui sotto per renderle accessibili anche per Gitco.<p><p style="color: red;text-align: center;">Avvertire ovunque del cambio password.<p></b></p>
                    </div>
                </div>
                <div class="row" style="z-index: 1;margin-bottom: 1%;">
                    <div class="col-lg-12">
                        <a style="width: 100%;" class="btn btn-success" href="http://telemaco.infocamere.it" target="_blank" onclick="$('#copertura').css('display','none');">Link cambio password iniPEC</a>
                    </div>
                </div>

                <div  style="z-index: 1;position: relative;width: 100%;height: 55vh;">
                    <div style="z-index: 10;position: absolute; height: 100%; width: 100%; background-color: rgba(71,71,71,0.37);border-radius: 5px;" id="copertura" >  </div>
                    <div class="row" style="padding-top: 3%;">
                        <div class="col-lg-12">
                            <p style="text-align: center;color: blue;font-weight: bold;">Dati prima della modifica</p>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 3%;">
                        <div class="col col-lg-12">
                            <label class="col-lg-4 control-label resize" style="text-align: left;">Username</label>
                            <div class="form-group">
                                <div class="col-lg-8">
                                    <input type="text" class="form-control resize validateCustom vld_Custom_r" id="old_user" name="old_user" value="" size=3 tabindex=1>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 1%;">
                        <div class="col col-lg-12">
                            <label class="col-lg-4 control-label resize" style="text-align: left;">Password</label>
                            <div class="form-group">
                                <div class="col-lg-8">
                                    <input type="password" class="form-control resize validateCustom vld_Custom_r" id="old_passw" name="old_passw" value="" size=3 tabindex=2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 3%;margin-bottom: 3%;">
                        <div class="col-lg-12">
                            <p style="text-align: center;color: blue;font-weight: bold;">Nuovi dati da inserire</p>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 3%;">
                        <div class="col col-lg-12">
                            <label class="col-lg-4 control-label resize" style="text-align: left;">Nuovo Username</label>
                            <div class="form-group">
                                <div class="col-lg-8">
                                    <input type="text" class="form-control resize validateCustom vld_Custom_r" id="user" name="user" value="" size=3 tabindex=3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 1%;">
                        <div class="col col-lg-12">
                            <label class="col-lg-4 control-label resize" style="text-align: left;">Nuova Password</label>
                            <div class="form-group">
                                <div class="col-lg-8">
                                    <input type="password" class="form-control resize validateCustom vld_Custom_r" id="passw_1" name="passw_1" value="" size=3 tabindex=4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 1%;">
                        <div class="col col-lg-12">
                            <label class="col-lg-4 control-label resize" style="text-align: left;">Ripeti Password</label>
                            <div class="form-group">
                                <div class="col-lg-8">
                                    <input type="password" class="form-control resize validateCustom vld_Custom_r" id="passw_2" name="passw_2" value="" size=3 tabindex=5>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 3%;">
                        <div class="col col-lg-12">
                            <button style="width: 100%;" class="btn btn-primary" type="button" onclick="saveCredential();">Salva</button>
                        </div>
                    </div>
                    <div style="position: absolute;bottom: 0;width:100%;margin-top: 1%;">
                        <div id="messaggioRisultato" class="alert alert-success" role="alert" style="text-align: center;display: none;">Nessuna richiesta precedente da scaricare</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-1 col-lg-offset-10"><i title="Aggiorna utente/password" style=" cursor: pointer;" id="aggiorna_ut_psw" class="fa fa-unlock fa-2x" data-toggle="modal" data-target="#credentialModal" aria-hidden="true"></i></div>
</div>

<div class="row" style="margin-top: 3%;">
    <div class="col-lg-10 col-lg-offset-1" style="border: 2px solid blue;background-color:/*#53D9F0*/ #9ccff4;">
        <form class="form-horizontal validate" name=form_tipo id=form_tipo method=post action="prc_inipec.php">

            <input type=hidden name=c value=<?php echo $c; ?> >
            <input type=hidden name=cc_el value=<?php echo $cc_el; ?> >
            <input type=hidden name=a value=<?php echo $a; ?> >
            <input type=hidden name=codcat value=<?php echo $cod_cat; ?> >
            <input type=hidden name=el value=<?php echo $last_el_id; ?> >
            <input type=hidden name=tipoatto value=<?php echo $tipoatto; ?> >

            <div class="row" style="margin-top: 3%;">
                <div class="col col-lg-6 col-lg-offset-3">
                    <div class="form-group">
                        <label class="col-lg-4 control-label resize" style="text-align: left;">Tipo soggetto</label>
                        <div class="col-lg-8">
                            <select id=tipoSogg name=tipoSogg class="resize form-control vld_req" onchange="$('#submitType').trigger('click');" style="width: 100%;">
                                <option value="E" <?= $tipoSoggetto == "E"?"selected":""; ?>>ENTRAMBE</option>
                                <option value="D" <?= $tipoSoggetto == "D"?"selected":""; ?>>DITTA</option>
                                <option value="F" <?= $tipoSoggetto == "F"?"selected":""; ?>>FISICA</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <button style="display: none;" type="submit" id="submitType"></button>
        </form>

        <form class="form-horizontal validate" name=f_inipec_download id=f_inipec_download method=post action="prc_inipec_download_exe.php">

            <input type=hidden name=c value=<?php echo $c; ?> >
            <input type=hidden name=cc_el value=<?php echo $cc_el; ?> >
            <input type=hidden name=a value=<?php echo $a; ?> >
            <input type=hidden name=codcat value=<?php echo $cod_cat; ?> >
            <input type=hidden name=el value=<?php echo $last_el_id; ?> >
            <input type=hidden name=tipoatto value=<?php echo $tipoatto; ?> >
            <input name="action" value="import" type="hidden">

            <?php if(count($resultRequest) > 0) { ?>
            <div class="row">
                <div class="col-lg-10 col-lg-offset-1">
                    <div class="alert alert-warning" role="alert" style="text-align: center;">Richiesta precedente da scaricare presente sul server</div>
                </div>
            </div>
            <div class="row" style="padding-bottom: 2%;">
                <div class="col col-lg-10 col-lg-offset-1">
                    <button type="submit" id="download_Inipec" class="btn btn-success" style="width:100%;">Scarica dati INIPEC</button>
                </div>
            </div>
            <?php } else {?>
                <div class="row" style="padding-bottom: 2%;">
                    <div class="col-lg-10 col-lg-offset-1">
                        <div class="alert alert-success" role="alert" style="text-align: center;">Nessuna richiesta precedente da scaricare</div>
                    </div>
                </div>
            <?php } ?>
        </form>
        <form name="f_inipec_upload" id="f_inipec_upload" action="prc_inipec_exe.php" method="post" style="padding-top: 2%; border-top: 2px solid blue;">
            <input type="hidden" name="P" value="prc_inipec.php" />
            <input type=hidden name=c value=<?php echo $c; ?> >
            <input type=hidden name=cc_el value=<?php echo $cc_el; ?> >
            <input type=hidden name=a value=<?php echo $a; ?> >
            <input type=hidden name=tipoSoggHidden value=<?php echo $tipoSoggetto; ?> >
            <input type=hidden name=codcat value=<?php echo $cod_cat; ?> >
            <input type=hidden name=el value=<?php echo $last_el_id; ?> >
            <input type=hidden name=tipoatto value=<?php echo $tipoatto; ?> >

            <div class="col-lg-10 col-lg-offset-1">
                <div class="table_header_H col-sm-2" style="border-left: 2px solid #0262BD;">ID</div>
                <div class="table_header_H col-sm-2">Richiesta</div>
                <div class="table_header_H col-sm-5">Ente</div>
                <div class="table_header_H col-sm-3" style="border-right: 2px solid #0262BD;">Numero record</div>
            </div>
            <?php $count = 0; foreach($arr_result as $value){
                ++$count;
                ?>
                <div class="col-lg-10 col-lg-offset-1">
                    <div class="table_body col-sm-2" style="border-left: 2px solid #0262BD;" ><?= $count; ?></div>
                    <div class="table_caption_button col-sm-2" style="text-align:center">
                        <input type="radio" name="radio_limit" value="<?= $value['Limit']; ?>" />
                    </div>
                    <div class="table_body col-sm-5"><?= $value['Ente']; ?></div>
                    <div class="table_body col-sm-3" style="border-right: 2px solid #0262BD;"><?= $value['NumRecord']; ?></div>
                </div>
            <?php } if($count > 0) { ?>
                <div class="row" style="margin-bottom: 3%;">
                    <div class="col col-lg-10 col-lg-offset-1">
                        <button type="submit" id="upload_Inipec" class="btn btn-success" style="width:100%;margin-top: 2%;">Carica richiesta PEC</button>
                    </div>
                </div>
            <?php } else { ?>
            <div class="col-lg-10 col-lg-offset-1" style="margin-bottom: 3%;">
                <div class="table_body col-sm-12" style="border-left: 2px solid #0262BD;border-right: 2px solid #0262BD;text-align: center;color: red;font-weight: bold;" >Nessuna richiesta da inviare</div>
            </div>
            <?php } ?>
        </form>
    </div>
</div>

<?php  include INC . "/footer.php"; ?>
