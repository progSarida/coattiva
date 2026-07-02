<?php

class cls_query
{

    function getWhereFromFilters($filter)
    {
        $where = "";

        //NUMERO PARTITE
        if (isset($filter['from_taxRecord'])) {
            if($filter['from_taxRecord']>0){
                if ($where != "")
                    $where .= "AND ";

                $where .= "( Comune_ID >= " . $filter['from_taxRecord'] . " ";
                if ($filter['to_taxRecord'] > 0)
                    $where .= "AND Comune_ID <= " . $filter['to_taxRecord'] . " ";
                $where .= ") ";
            }
        }

        //ANNO PARTITE
        if (isset($filter['from_taxYear'])) {
            if($filter['from_taxYear']>0){
                if ($where != "")
                    $where .= "AND ";

                $where .= "( Anno_Riferimento >= " . $filter['from_taxYear'] . " ";
                if ($filter['to_taxYear'] > 0)
                    $where .= "AND Anno_Riferimento <= " . $filter['to_taxYear'] . " ";
                $where .= ") ";
            }
        }

        if (isset($filter['taxType'])) {
            if ($filter['taxType'] !="") {
                if ($where != "")
                    $where .= "AND ";
                $where .= " Tipo_Riscossione = \"" . $filter['taxType'] . "\" ";
            }
        }

        if (isset($filter['notification'])) {
            if ($filter['notification'] !="") {
                if ($where != "")
                    $where .= "AND ";

                if($filter['notification']=="y")
                    $where .= " Notifica_ID is not null ";
                else
                    $where .= " Notifica_ID is null ";
            }
        }

        if (isset($filter['taxStopFlag'])) {
            if($filter['taxStopFlag']!=""){
                if ($where != "")
                    $where .= "AND ";

                if($filter['taxStopFlag']=="si")
                    $where .= " Flag_Blocco_Coazione = \"si\" ";
                else
                    $where .= " Flag_Blocco_Coazione!= \"si\" ";
            }
        }

        //DENOMINAZIONE UTENTI
        if (isset($filter['from_surname'])) {
            if($filter['from_surname']!=""){
                if ($where != "")
                    $where .= "AND ";

                $where .= "( ((Cognome_Ditta = \"" . $filter['from_surname'] . "\" ";
                if ($filter['from_name'] != "")
                    $where .= "AND Nome >=\"" . $filter['from_name'] . "\"";

                $where .= ") OR Cognome_Ditta > \"" . $filter['from_surname'] . "\" ) ";

                if ($filter['to_surname'] != "") {
                    $where .= "AND ((Cognome_Ditta = \"" . $filter['to_surname'] . "\" ";
                    if ($filter['from_name'] != "")
                        $where .= "AND Nome <=\"" . $filter['to_name'] . "\"";

                    $where .= ") OR Cognome_Ditta < \"" . $filter['to_surname'] . "\" ) ";
                }

                $where .= " ) ";
            }
        }

        return $where;
    }

    function getOrder($sort)    {

        $order = "";
        switch($sort){
            case "partita":
                $order = "CC ASC, Comune_ID ASC";
                break;
            case "utente":
                $order = "CC ASC, Cognome_Ditta ASC, Nome ASC";
                break;
        }
        return $order;
    }

    public function getFiltersDescription($filter){
        $a_return = array();
        $i=0;
        if(isset($filter['from_surname'])){
            if($filter['from_surname']!=""){
                $a_return[$i]['label'] = "UTENTE";
                $a_return[$i]['value'] = "Da ".$filter['from_surname'];
                if(isset($filter['from_name']))
                    if($filter['from_name']!="")
                        $a_return[$i]['value'].= " ".$filter['from_name'];
                if(isset($filter['to_surname']))
                    if($filter['to_surname']!="")
                        $a_return[$i]['value'].= " a ".$filter['to_surname'];
                if(isset($filter['to_name']))
                    if($filter['to_name']!="")
                        $a_return[$i]['value'].= " ".$filter['to_name'];

                $i++;
            }
        }

        if(isset($filter['from_taxRecord'])){
            if($filter['from_taxRecord']!=""){
                $a_return[$i]['label'] = "PARTITE";
                $a_return[$i]['value'] = "Da ".$filter['from_taxRecord'];
                if(isset($filter['to_taxRecord']))
                    if($filter['to_taxRecord']!="")
                    $a_return[$i]['value'].= " a ".$filter['to_taxRecord'];

                $i++;
            }
        }

        if(isset($filter['from_taxYear'])){
            if($filter['from_taxYear']!=""){
                $a_return[$i]['label'] = "ANNI";
                $a_return[$i]['value'] = "Dal ".$filter['from_taxYear'];
                if(isset($filter['to_taxYear']))
                    if($filter['to_taxYear']!="")
                        $a_return[$i]['value'].= " al ".$filter['to_taxYear'];

                $i++;
            }
        }

        if(isset($filter['taxType'])){
            if($filter['taxType']!=""){
                $a_return[$i]['label'] = "TIPO RISCOSSIONE";
                $a_return[$i]['value'] = $filter['taxType'];

                $i++;
            }
        }

        if(isset($filter['taxStopFlag'])){
            if($filter['taxStopFlag']!=""){
                $a_return[$i]['label'] = "BLOCCO RISCOSSIONE";
                $a_return[$i]['value'] = strtoupper($filter['taxStopFlag']);

                $i++;
            }
        }

        if(isset($filter['notification'])){
            if($filter['notification']!=""){
                $a_return[$i]['label'] = "NOTIFICHE";

                if($filter['notification']=="y")
                    $notification = "PRESENTI";
                else
                    $notification = "ASSENTI";
                $a_return[$i]['value'] = $notification;

                $i++;
            }
        }

        if(isset($filter['sort'])){
            if($filter['sort']!=""){
                $a_return[$i]['label'] = "ORDINAMENTO";
                $a_return[$i]['value'] = $filter['sort'];

                $i++;
            }
        }
        return $a_return;
    }
}


?>