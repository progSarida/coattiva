<?php

if (!session_id()) session_start();


include_once($_SESSION['_path']);
include_once(ROOT . "/_parameter.php"); //dati database

include(INC . "/header.php");
include(INC . "/menu.php");



if ($_SESSION['username'] == NULL) {
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}


$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');

$auth = $_SESSION['aut_tipo'];

$query = "SELECT * FROM enti_gestiti ORDER by Denominazione ASC";
$e_entiGestiti = $cls_db->getResults($cls_db->ExecuteQuery($query), "array", "ID");
$a_selection = array("value" => "CC", "firstOpt" => 0, "selected" => $c, "text" => array("[Denominazione]", " - ", "[CC]"));
$optEnte = $cls_html->getOptions($e_entiGestiti, $a_selection);


?>
<style>
    fieldset.scheduler-border {
        border: 1px solid #356bc1;
        padding: 0 1.4em 1.4em 1.4em !important;
        margin: 0 0 1.5em 0 !important;
        -webkit-box-shadow: 0px 0px 0px 0px #000;
        box-shadow: 0px 0px 0px 0px #000;
    }

    legend.scheduler-border {
        font-size: 1.2em !important;
        font-weight: bold !important;
        text-align: left !important;
    }
</style>


<!-- ********** GESTIONE LINK MENU ********** -->
<script>
    //PAG GIU
    /* switchMenuImg("pagedown");
    pagedown_button = function(){
        pagina_menu('prev');
    } */

    //PAG SU
    /*  switchMenuImg("pageup");
     pageup_button = function(){
         pagina_menu('suc');
     } */

    //F11-F12 sono nel menu'
    /*  switchMenuImg("F11");
     F11_button = function(){
         $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT . "/archivio/help/Elaborazioni_Atti.pdf"; ?>");
         $("#helpModalLabel").empty().append("<b>Help Elaborazioni Atti</b>");
         $("#helpModal").modal('show');
     } */

    //******************************\\
    //ALTRI LINK / FUNZIONI CHIAMATE\\
    //CAMBIO PAGINA
</script>

<!-- ********** CALENDARIO ********** -->


<!-- ********** SUBMIT ********** -->



<div class="container">
    <div style="height: auto; display: flex; justify-content: center;">
        <div class="col">
            <form class="form-horizontal" id="form_new_utente" name="form_new_utente" action="salva_utente.php" method="post">
                <input type="hidden" name="c" value="<?php echo $c; ?>">
                <input type="hidden" name="a" value="<?php echo $a; ?>">
                <fieldset class="scheduler-border">
                    <legend style="width: auto;margin-left: auto; margin-right: auto;"><span class="titolo font16 under_decor">Crea Nuovo Utente</span></legend>
                    <div class="form-group">
                        <label class="control-label col-md-2" for="Username">Username</label>
                        <div class="col-md-10">
                            <input type="text" class="form-control validateCustom vld_Custom_r" id="username" name="username" aria-describedby="username" placeholder="username">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-2" for="email">Email </label>
                        <div class="col-md-10">
                            <input type="email" class="form-control" id="email_address" name="email_address" placeholder="pippo@example.com">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-2" for="Password">Password</label>
                        <div class="col-md-10">
                            <input type="password" class="form-control validateCustom vld_Custom_r" id="password-field" name="pswd" placeholder="Password">
                            <span toggle="#password-field" class="fa fa-fw fa-eye field-icon toggle-password"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-2" for="ente">Ente</label>
                        <div class="col-md-10">
                            <select class="form-select validateCustom vld_Custom_r" id="ente" name="ente" style="height:35px; width:75%;">
                                <option value=''>Seleziona ente </option>
                                <option value='****'>Tutti gli enti </option>
                                <?= $optEnte; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-2" for="exampleFormControlSelect1">Tipo</label>
                        <div class="col-md-10">
                            <select class="form-select validateCustom vld_Custom_r" id="auth" name="auth">
                                <option value=''>Seleziona </option>
                                <option value="1">Autorizzazione tutti i comuni</option>
                                <option value="2">Autorizzazione comune singolo</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary pull-right" style="margin-bottom:30px;margin-right:25px;">Salva</button>
                </fieldset>
            </form>
        </div>
    </div>
</div>


<script>
    $(".toggle-password").click(function() {
        $(this).toggleClass("fa-eye fa-eye-slash");
        var input = $($(this).attr("toggle"));
        if (input.attr("type") == "password") {
            input.attr("type", "text");
        } else {
            input.attr("type", "password");
        }
    });
</script>

<?php include(INC . "/footer.php"); ?>