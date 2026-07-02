<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC . "/header.php");
include_once(INC . "/menu.php");

$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");

?>

<form action="leggi_excell_reimportazione_atti.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="c" value="<?= $c; ?>">
    <input type="hidden" name="a" value="<?= $a; ?>">

    <input type="file" name="file" >
    <button type="submit">clicca</button>
</form>
