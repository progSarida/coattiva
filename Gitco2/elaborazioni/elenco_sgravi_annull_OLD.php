<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
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


/*foreach (new DirectoryIterator(XLSSGRAVI."/".$c) as $file) {
    if ($file->isFile()) {
        //print $file->getFilename() . "\n";
        echo "<option value='".$file->getFilename()."'>".$file->getFilename()."</option>";
    }
}*/

?>

<script>
    //F5
    switchMenuImg("F5");
    F5_button = function(){
        location.href="elenco_sgravi_annull.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }

    //F10
    switchMenuImg("F10");
    F10_button = function(){
        $("#sgravio_annull_form").submit();
    }
</script>

<form id="sgravio_annull_form" name="sgravio_annull_form" action="stampa_sgravio_annull.php" method="post" >
    <input type=hidden name="c" value="<?php echo $c ?>" />
    <input type=hidden name="a" value="<?php echo $a ?>" />

    <div class="row justify-content-md-center " style="margin: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">Filtra sgravi annullamenti</span>
        </div>
    </div>

    <div class="row justify-content-md-center " style="margin: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">Selezione atti</span>
        </div>
    </div>

    <div class="row">
        <div class="col col-lg-3 col-lg-offset-1" >
            <label class="col-lg-6 control-label resize" style="text-align: left;">Sgravio</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="sgravio" name="sgravio" class="form-control resize" tabindex=1>
                        <option value=""></option>
                        <option value="si">SI</option>
                        <option value="no">NO</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-4 ">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Data sgravio da</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input class="form-control resize picker" type="text" id="sgravio_da" name="sgravio_da" value="" tabindex=2 >
                </div>
            </div>
        </div>
        <div class="col col-lg-3">
            <label class="col-lg-3 control-label resize" style="text-align: left;">a</label>
            <div class="form-group">
                <div class="col-lg-9">
                    <input class="form-control resize picker" type="text" id="sgravio_a" name="sgravio_a" value=""  tabindex=3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col col-lg-3 col-lg-offset-1">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Annullamento</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="annull" name="annull" class="form-control resize" tabindex=4>
                        <option value=""></option>
                        <option value="si">SI</option>
                        <option value="no">NO</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-4">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Data annullamento da</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input class="form-control resize picker" type="text" id="annull_da" name="annull_da" value=""  tabindex=5>
                </div>
            </div>
        </div>
        <div class="col col-lg-3">
            <label class="col-lg-3 control-label resize" style="text-align: left;">a</label>
            <div class="form-group">
                <div class="col-lg-9">
                    <input class="form-control resize picker" type="text" id="annull_a" name="annull_a" value=""  tabindex=6>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col col-lg-4 col-lg-offset-4">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Partita da</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input class="form-control resize" type="text" id="partita_da" name="partita_da" value=""  tabindex=7>
                </div>
            </div>
        </div>
        <div class="col col-lg-3">
            <label class="col-lg-3 control-label resize" style="text-align: left;">a</label>
            <div class="form-group">
                <div class="col-lg-9">
                    <input class="form-control resize" type="text" id="partita_a" name="partita_a" value=""  tabindex=8>
                </div>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top: 2%;">
        <div class="col col-lg-3 col-lg-offset-1">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Modalità stampa</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="printType" name="printType" tabindex=9 class="form-control resize">
                        <option value="temp">provvisoria</option>
                        <option value="final">definitiva</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-4">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Tipo stampa</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="printed" name="printed" tabindex=10 class="form-control resize">
                        <option value="no">da stampare</option>
                        <option value="si">stampati</option>
                        <option value="all">tutti</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top: 2%;">
        <div class="col col-lg-10 col-lg-offset-1">
            <div class="form-group">
                <label class="col-lg-2 control-label resize" style="text-align: left;">Descrizione Stampa</label>
                <div class="col-lg-10">
                    <textarea class="form-control resize" tabindex=11 id=note style="max-width: 100%;" name=note placeholder="es: Estrazione da partita 5 a 102 ..."></textarea>
                </div>
            </div>
        </div>
    </div>

    <!--<div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>-->

</form>

<?php include(INC."/footer.php"); ?>
