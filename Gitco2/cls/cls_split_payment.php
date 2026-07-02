<?php

class cls_split_payment{

    public function getTypesQuery(){
        return "SELECT DISTINCT * FROM split_payment";
    }

    public function getParametersQuery($c){
        return "SELECT * FROM split_payment_parameters WHERE cc = '".$c."' OR cc = '****' ORDER BY cc DESC, id DESC LIMIT 1";
    }

    public function getParametersFromIdQuery($id){
        return "SELECT * FROM split_payment_parameters WHERE id = '".$id."'";
    }

    public function getParametersIdQuery($c){
        $query = "SELECT split_payment_parameters.id FROM split_payment_parameters ";
        $query.= "LEFT JOIN partita_tributi ON partita_tributi.Split_Parameters_ID = split_payment_parameters.id ";
        $query.= "WHERE split_payment_parameters.cc = '".$c."' AND partita_tributi.Split_Parameters_ID is null GROUP BY split_payment_parameters.id ";
        $query.= "ORDER BY split_payment_parameters.cc DESC, split_payment_parameters.id DESC LIMIT 1";
        return $query;
    }

    public function getLineByPriority(array $a_line){
        $a_return = array();
        asort($a_line);
        $count = 0;
        foreach ($a_line as $key=>$value){
            if(strpos($key,"type")!==false && $value>0){
                preg_match_all('!\d+!', $key, $matches);
                $splitNumber = $matches[0][0];
                $a_return[$count]['header'] = $a_line['split'.$splitNumber];
                $a_return[$count]['type'] = $a_line['split'.$splitNumber.'_type'];
                $a_return[$count]['categories'] = unserialize($a_line['split'.$splitNumber.'_categories']);
                $a_return[$count]['split_number'] = $splitNumber;
                $count++;
            }
        }
        return $a_return;
    }

    public function splitAmount(array $a_codiciTributo, $due=true){
        $arrayRitorno = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0, 7=>0, 8=>0, 9=>0, 10=>0, 11=>0, 12=>0, 13=>0, 14=>0, 15=>0, 16=>0);
        if($due===true){
            for ($i = 0; $i < count($a_codiciTributo['Codici']); $i++)
                $arrayRitorno[$a_codiciTributo['Codici'][$i]]+= $a_codiciTributo['Importi'][$i];
        }

        return $arrayRitorno;
    }

}


?>