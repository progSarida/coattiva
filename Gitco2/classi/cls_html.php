<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include_once LIBRERIE . "/funzioni.php";

class cls_html
{
    public function options ( $a_records, $valueField, $selectedValue=null, $textField=null )
    {
        if($textField==null)
            $textField = $valueField;

        $option = "";
        for($i=0;$i<count($a_records);$i++){
            if($selectedValue == $a_records[$i][$valueField])
                $option .= "<option selected id='".($i+1)."' value='".$a_records[$i][$valueField]."'>".$a_records[$i][$textField]."</option>\n";
            else
                $option .= "<option id='".($i+1)."' value='".$a_records[$i][$valueField]."'>".$a_records[$i][$textField]."</option>\n";
        }

        return $option;
    }
}

?>