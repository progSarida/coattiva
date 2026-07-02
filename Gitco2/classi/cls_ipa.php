<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include_once LIBRERIE . "/funzioni.php";

class ipa
{
    public function options ( $field, $selected, $whereField = "", $whereValue = "" )
    {
        $query = "SELECT DISTINCT ".$field." ";
        $query.= "FROM ipa_lista ";
        if($whereField!="")
            $query.= "WHERE ".$whereField." = \"".$whereValue."\" ";
        $query.= "ORDER BY ".$field." ASC";

        $results = mysql_query($query);
        $a_options = array();
        while($line = mysql_fetch_array($results, MYSQL_ASSOC))
            $a_options[] = $line;

        $option = "";
        for($i=0;$i<count($a_options);$i++){
            if($selected==$a_options[$i][$field] && $selected!="")
                $option .= "<option selected id='".($i+1)."'>".$a_options[$i][$field]."</option>\n";
            else
                $option .= "<option id='".($i+1)."'>".$a_options[$i][$field]."</option>\n";
        }


        return $option;
    }

    public function lista_PEC ($regione="", $provincia=""){

        $query = "SELECT * FROM ipa_lista WHERE IstatTypeId = 23 AND AdminTypeId = 3 ";
        if($regione!="")
            $query.="AND Regione = \"".$regione."\" ";
        if($provincia!="")
            $query.="AND Provincia = \"".$provincia."\" ";

        $query.= "ORDER BY Regione ASC, Provincia ASC, Comune ASC";

        $results = mysql_query($query);
        $a_lista = array();
        while($line = mysql_fetch_array($results, MYSQL_ASSOC))
            $a_lista[] = $line;

        return $a_lista;
    }
}

?>