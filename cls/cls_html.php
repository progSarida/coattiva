<?php

class cls_html
{
    public function optionsFromArray ( $a_records, $valueField, $selectedValue=null, $textField=null )
    {
        if($textField==null)
            $textField = $valueField;

        $option = "";
        for($i=0;$i<count($a_records);$i++){

            $option .= "<option id='".($i+1)."' value='".$a_records[$i][$valueField]."' ";
            if($selectedValue == $a_records[$i][$valueField])
                $option.= "selected ";

            $option.= ">".$a_records[$i][$textField]."</option>\n";

        }

        return $option;
    }
}

?>