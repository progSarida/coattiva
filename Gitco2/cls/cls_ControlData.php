<?php
include_once($_SESSION['_path']);
include_once CLS . "/cls_LOG.php";
include_once CLS . "/cls_db.php";


class ControlData
{
    private $arrResult;
    private $table;
    private $arrayControl;
    private $log;
    private $db;

    public function __construct($arrayControl = array(), $table = "")
    {
        $this->arrResult = array();
        $this->table = $table;
        $this->arrayControl = $arrayControl;
        $this->log = new LOG();
        $this->db = new cls_db();
    }

    public function getJson()
    {
        if(count($this->arrResult) == 0)
            $this->arrResult[] = array("status" => "ok", "table" => $this->table);
        $return = json_encode($this->arrResult);
        if(!$return) {
            $tempError[] = array("field" => "sconosciuto", "text" => "sconosciuto", "code_error" => 10, "status" => "error", "table" => $this->table);
            return json_encode($tempError);
        }
        return $return;
    }

    public function checkString($val, $field)
    {
        $pattern = "/^[+-]{0,1}[0-9]+$/";
        $val = utf8_encode($val);
        if($val==null) return null;
        if (is_string($val) /*&& !preg_match($pattern, $val)*/) return $val;
        else {
            $this->arrResult[] = array("field" => $field, "text" => $val, "code_error" => 1,"status"=>"error", "table" => $this->table);
            return null;
        }
    }

    public function checkInteger($val, $field)
    {
        if($val==null) return $val;
        $pattern = "/^[+-]{0,1}[0-9]+$/";
        if (preg_match($pattern, $val)) return $val;
        else {
            $this->arrResult[] = array("field" => $field, "text" => $val, "code_error" => 2,"status"=>"error", "table" => $this->table);
            return null;
        }
    }

    public function checkDouble($val, $field)
    {
        if($val==null) return $val;
        $pattern = "/^[+-]{0,1}[0-9]+[.]{1}[0-9]+$/";
        if (preg_match($pattern, $val)) return $val;
        else {
            $this->arrResult[] = array("field" => $field, "text" => $val, "code_error" => 3,"status"=>"error", "table" => $this->table);
            return null;
        }
    }

    public function checkBool($val, $field)
    {
        if($val==null) return $val;
        if (is_bool($val)) return $val;
        else {
            $this->arrResult[] = array("field" => $field, "text" => $val, "code_error" => 4,"status"=>"error", "table" => $this->table);
            return null;
        }
    }

    public function checkStringMaxLength($val,$field,$max){
        //$pattern = "/^[\\a-zA-Z0-9èéàòùìç°,.-:_=?\"'\/%\$£\&\^!\|()\[\]*+#@;<>\t\n\v\f\r\s]+$/";
        $val = utf8_encode($val);
        if($this->checkString($val,$field) != null){
            if(strlen($val)>$max){
                $this->arrResult[] = array("field"=>$field,"text"=>utf8_encode($val),"code_error"=>5,"status"=>"error", "table" => $this->table);
                return null;
            }
            /*if(preg_match($pattern, $val)) return $val;
            else {
                $this->arrResult[] = array("field"=>$field,"text"=>utf8_encode($val),"code_error"=>9,"status"=>"error", "table" => $this->table);
                return null;
            }*/
            return $val;
        } return null;
    }

    public function checkFloatingNumber($val, $field, $precision, $scale)
    {
        if($val==null) return $val;
        if ($this->checkDouble($val, $field) != null) {
            $comma = $precision - $scale;
            $range = "0-9";
            if ($comma < 0) {
                $this->log->error("I valori di precisione e scala sono errati, valore non controllato!");
                return $val;
            }//NON CONTROLLO I DATI PERCHè MI è STATO INSERITO QUALCHE DATO ERRATO, RITORNO IL VALORE SENZA CHE SIA STATO CONTROLLATO
            else if ($comma == 0) {
                $comma = 1;
                $range = "0";
            }

            if (!strpos($val, ".") && !strpos($val, ",")) $pattern = "/^[0-9]{1," . $comma . "}$/";
            else $pattern = "/^[+-]{0,1}[" . $range . "]{1," . $comma . "}[.]{1}[0-9]{0," . $scale . "}$/";

            if (preg_match($pattern, $val)) return $val;
            else {
                $this->arrResult[] = array("field" => $field, "text" => $val, "code_error" => 6,"status"=>"error", "table" => $this->table);
                return null;
            }
        }
        return null;
    }

    public function checkDate($val, $field, $format = "Y-m-d")
    {

        if($val==null) return $val;
        $flagSlasch = false;

        $pattern = $this->BuildRegularExpression($format);
        if (strpos($val, "/")) {
            $val = str_replace("/", "-", $val);
            $flagSlasch = true;
        }

        if (preg_match($pattern, $val)) {
            if ($flagSlasch)
                $val = str_replace("-", "/", $val);
            return $val;
        } else {
            if ($flagSlasch)
                $val = str_replace("-", "/", $val);
            $this->arrResult[] = array("field" => $field, "text" => $val, "code_error" => 7,"status"=>"error", "table" => $this->table);
            return null;
        }
    }

    public function setTable($table)
    {
        $this->table = $table;
        //$this->arrayControl = array();
        //$this->arrResult = array();
    }

    public function setArrayControl(array $arr)
    {
        $this->arrayControl = $arr;
    }

    public function Reset()
    {
        $this->table = "";
        $this->arrayControl = array();
        $this->arrResult = array();
    }

    public function checkEnum($val, $field, array $allEnumValue)
    {

        if($val==null) return $val;
        for ($i = 0; $i < count($allEnumValue); $i++) {
            if ($val == $allEnumValue[$i])
                return $val;
        }
        $this->arrResult[] = array("field" => $field, "text" => $val, "code_error" => 8,"status"=>"error", "table" => $this->table);
        return null;
    }

    private function BuildRegularExpression($Format)
    {
        $Format = str_replace("/", "-", $Format);
        $Format = str_replace(" ", "", $Format);
        $RegularExpression = "/^";
        for ($i = 0; $i < strlen($Format); $i++) {
            switch ($Format[$i]) {
                case "Y":
                    $RegularExpression .= "[0-9]{4}";
                    break;
                case "m":
                case "d":
                case "H":
                case "i":
                case "s":
                    $RegularExpression .= "[0-9]{2}";
                    break;
                case "-":
                    $RegularExpression .= "[-]{1}";
                    break;
                case ":":
                    $RegularExpression .= "[:]{1}";
                    break;
            }
        }
        $RegularExpression .= "$/";
        return $RegularExpression;
    }

    public function automaticCheck()
    {

        $result = $this->db->getResults($this->db->ExecuteQuery("EXPLAIN " . $this->table));

        $arrayReturn = array();

        foreach ($this->arrayControl as $Key => $value) {

            for ($i = 0; $i < count($result); $i++) {

                if ($Key == $result[$i]["Field"]) {

                    if ($this->contains($result[$i]["Type"],"int")) {

                        $arrayReturn[$Key] = $this->checkInteger($value, $Key);

                    } else if ($this->contains($result[$i]["Type"],"varchar")) {

                        $offset = strpos($result[$i]["Type"], ")") - (strpos($result[$i]["Type"], "(") + 1);
                        $max = substr($result[$i]["Type"], (strpos($result[$i]["Type"], "(") + 1), $offset);
                        $arrayReturn[$Key] = $this->checkStringMaxLength($value, $Key, $max);

                    } else if ($this->contains($result[$i]["Type"],"decimal")) {

                        $offset = strpos($result[$i]["Type"], ")") - (strpos($result[$i]["Type"], "(") + 1);
                        $strExtremes = substr($result[$i]["Type"], (strpos($result[$i]["Type"], "(") + 1), $offset);
                        $arrExtremes = explode(",", $strExtremes);
                        $arrayReturn[$Key] = $this->checkFloatingNumber($value, $Key, $arrExtremes[0], $arrExtremes[1]);

                    } else if ($this->contains($result[$i]["Type"],"enum")) {

                        $offset = strpos($result[$i]["Type"], ")") - (strpos($result[$i]["Type"], "(") + 1);
                        $strExtremes = substr($result[$i]["Type"], (strpos($result[$i]["Type"], "(") + 1), $offset);
                        $strExtremes = str_replace("'", "", $strExtremes);
                        $arrExtremes = explode(",", $strExtremes);
                        $arrayReturn[$Key] = $this->checkEnum($value, $Key, $arrExtremes);

                    } else if ($this->contains($result[$i]["Type"],"date")) {

                        $arrayReturn[$Key] = $this->checkDate($value, $Key);

                    } else {
                        $arrayReturn[$Key] = $value;
                        $this->log->error("Tipo non ancora definito, valore non controllato!");
                    }

                    break;

                }
            }
        }

        return $arrayReturn;
    }

    private function contains($str,$strCheck){
        $flag = false;
        if(strlen($strCheck)==0)
            return false;

        for($i=0; $i < strlen($str); $i++){
            if($str[$i] == $strCheck[0]){
                $flag = true;
                if((strlen($str)-$i) >= strlen($strCheck)){
                    for($x=$i,$z=0;$z<strlen($strCheck);$x++,$z++){
                        if($strCheck[$z] != $str[$x]){
                            $flag = false;
                            break;
                        }
                    }
                    if($flag == true)
                        return true;
                }
                else return false;
            }
        }
        return false;
    }


    private function alert($msg)
    {
        echo "<script>alert('" . $msg . "');</script>";
    }
}