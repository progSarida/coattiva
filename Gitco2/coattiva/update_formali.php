<?php

include_once($_SERVER['DOCUMENT_ROOT']."/gitco2/_path.php");
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");
//include("_path.php");
//include(INC."/parameter.php");
//include(CLS."/cls_db.php");
//include(INC."/function.php");
//include(INC."/header.php");
$cls_db = new cls_db();

echo $_POST['note_verbali'];

    $formid = $_POST['formid'];
    $lan = $_POST['lan'];
    $city = $_POST['city'];
    $exept = array("</row>","</main_part>","</paymenttype>","</payment>","</page>");
    $replace = str_replace($exept,'',$_POST['note_verbali']);
    $replace = str_replace("<paymenttype>","<PAYMENTTYPE>",$replace);
    $replace = str_replace("<payment>","<PAYMENT>",$replace);

    $cls_db->ExecuteQuery("UPDATE Form SET Content='".stripslashes($replace)."' WHERE FormTypeId = '$formid' AND CityId = '$city' AND  LanguageId = '$lan'");

//    if ($formid == '2') {
//        header('Location: ./tbl_form.php?Search_FormType='.$formid.'&LangId='.$lan.'');
//    } else {
//        header('Location: ./tbl_form.php?Search_FormType='.$formid.'');
//    }


?>

