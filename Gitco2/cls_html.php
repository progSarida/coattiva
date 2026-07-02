<?php

class cls_html
{
    public function optionsFromArray (array $a_records, $valueField, $selectedValue=null, $textField=null )
    {
        if($textField==null)
            $textField = $valueField;

        $option = "";
        for($i=0;$i<count($a_records);$i++){

            $option .= "<option id='".($i+1)."' value='".$a_records[$i][$valueField]."'";
            if($selectedValue == $a_records[$i][$valueField])
                $option.= " selected";

            $option.= ">";
            if(is_array($textField)){

                for($j=0;$j<count($textField);$j++){
                    $option.= $a_records[$i][$textField[$j]]." - ";
                }
                $option = substr($option,0,-3);
            }
            else{
                $option.= $a_records[$i][$textField];
            }

            $option.= "</option>";

        }

        return $option;
    }


    public function getOptions (array $a_records, array $a_selection )
    {
        /**
         *  firstOpt => 0 or 1
         *  value => field of $a_records to put in value tag
         *  selected => value selected
         *  text => array to insert in text option. Quando si ha un elemento stringa dentro le parentesi quadre
         *  questo va sostituito con l'elemento dell'array $a_records altrimenti si utilizza direttamente la stringa.
         */

        $option = "";
        if($a_selection['firstOpt']==1)
            $option = "<option></option>";
        for($i=0;$i<count($a_records);$i++){

            $option .= "<option value='".$a_records[$i][$a_selection['value']]."'";
            if($a_selection['selected'] == $a_records[$i][$a_selection['value']])
                $option.= " selected";

            $option.= ">";
            if(is_array($a_selection['text'])){
                $text = "";
                for($j=0;$j<count($a_selection['text']);$j++){
                    if(substr($a_selection['text'][$j],0,1)=="[" && substr($a_selection['text'][$j],strlen($a_selection['text'][$j])-1,1)=="]"){
                        $text.= $a_records[$i][substr($a_selection['text'][$j],1, strlen($a_selection['text'][$j])-2)];
                    }
                    else
                        $text.= $a_selection['text'][$j];
                }
                $option.= $text;
            }
            else{
                $option.= $a_records[$i][$a_selection['value']];
            }

            $option.= "</option>";

        }

        return $option;
    }

}

?>