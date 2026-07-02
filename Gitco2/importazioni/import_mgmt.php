<?php

include_once($_SERVER['DOCUMENT_ROOT']."/gitco2/_path.php");
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");

$a_import['action'] = "import_transfers.php";
$a_import['title'] = "IMPORTAZIONE BONIFICI";
?>

    <script>
        switchMenuImg("F3");
        F3_button = function(){
            $('#import_form').submit();
        }

    </script>
<br>
    <span class="titolo"><?=$a_import['title'];?></span>
    <br><br>
    <form id="import_form" name="import_form" action="<?=$a_import['action'];?>" method=post enctype="multipart/form-data">
        <input type="hidden" name="c" value="<?=$c;?>">
        <input type="hidden" name="a" value="<?=$a;?>">

        <table class="table_interna text_center" border="0">
            <tr>
                <td>
                    <input type="file" size="50" name="upload_file" id="upload_file" onchange="">
                </td>
            </tr>
        </table>
    </form>

<?php

include(INC."/footer.php");

?>