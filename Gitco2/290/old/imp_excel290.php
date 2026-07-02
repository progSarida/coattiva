<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

if ($_SESSION['username']==NULL)
{
    header("Location:".WEB_ROOT."/autenticazione/accesso_negato.php");
    die;
}

include(INC."/header.php");
include(INC."/menu.php");

include_once(CLS."/cls_file.php");





$cls_file = new cls_file();
$dirPath = $cls_file->getWebPath(SUPER_ROOT."/archivio/Modelli");
$xlsFile = $dirPath."/MODELLO.xls";

?>

<script>

function abilitaconferma(){
	if ($("#tastosfoglia").val() != "")
		$("#tastoconferma").prop("disabled", false);
	else if ($("#tastosfoglia").val() == "")
		$("#tastoconferma").prop("disabled", true);
}


var xlsFile = "<?php echo $xlsFile; ?>";


function apri_file()
{
    window.open(xlsFile);
}


</script>
    <div class="row justify-content-md-center " style="margin-bottom: 3%; margin-top: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">Importazione Excel 290</span>
        </div>
    </div>
<div class="row">
    <div class="col-lg-6 col-lg-offset-3">
        <table class="table table-responsive" cellpadding=0 cellspacing=0 style="width: 100%">
            <colgroup>
                <col style="width: 30%;" >
                <col style="width: 70%;" >
            </colgroup>
            <thead>
            <tr>
                <th class="text_left"><p class="color_titolo">File</p></th>
                <th class="text_left"><p class="color_titolo">Descrizione</p></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td class="text_left">
                    <a href="#" onMouseover="title='Modello principale'" onclick="apri_file();" style="text-decoration: none;">
                        <img src="<?= IMMAGINIWEB; ?>/icon-excel.png" width=30px height=30px border="0">
                    </a>
                </td>
                <td class="text_left">
                    <b>Modello 290:</b> File Excel per modello 290 principale in formato .xls
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>


    <div class="row justify-content-md-center " style="margin-bottom: 3%; margin-top: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">Carica File</span>
        </div>
    </div>
<div>
    <form id=form_importazione name=form_importazione method="post" action="imp_excel290_exe.php" enctype="multipart/form-data">
        <input type="hidden" name="c" value="<?php echo $cls_help->getVar("c")?>">
        <input type="hidden" name="a" value="<?php echo $cls_help->getVar("a")?>">
        <input type="hidden" name="submit_file" value="1">

        <div class="row">
            <div class="col-lg-6 col-lg-offset-3">
                <div class="form-group">
                    <input type="file" accept=".xlsx,.xls" size="50" name="file_excel" id="tastosfoglia" class="form-control resize" onchange="abilitaconferma();">
                </div>
            </div>
        </div>
        <div class="row justify-content-md-center " style="margin-bottom: 3%; margin-top: 2%;">
            <div class="col col-md-auto text_center">
                <input type="submit" disabled class="btn btn-primary resize" size="10" id="tastoconferma" value="Conferma">
            </div>
        </div>
    </form>
</div>

				
<?php include(INC."/footer.php"); ?>