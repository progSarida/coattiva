<?php
include_once CLS."/cls_help.php";

class  cls_db{
    public $conn;
    public $help;
	function __construct(){
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
		$this->help = new cls_help();
		try{
			$this->connect();
		}
		catch (Exception $e) {
            if(DEBUG)
			    $this->help->ErrorAlert("danger",$this->ErrorReporting($e));
            else
                $this->help->ErrorAlert("danger","Errore di connessione. Contattare il Webmaster");
            die;
		}
	}

	function connect(){
		try{
			$this->conn = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
		}
		catch (mysqli_sql_exception $e){
			throw $e;
		}
	}

    function SetCharset($Charset){
        mysqli_set_charset( $this->conn, $Charset);
    }

	function ExecuteQuery($sql){
		try{
			try{
				return mysqli_query($this->conn, $sql);
			}
			catch (mysqli_sql_exception $e){
				throw $e;
			}
		}
		catch (Exception $e) {
            if(DEBUG)
                $this->help->ErrorAlert("danger",$this->ErrorReporting($e,$sql));
            else
                $this->help->ErrorAlert("danger","Errore query. Contattare il Webmaster");
            die;
		}
	}

	function SelectQuery($sql){
		return $this->ExecuteQuery($sql);
	}

	function Select($table, $where=null, $order=null, $limit=null){
		$sql = "SELECT * FROM $table";

		if(!is_null($where)) $sql .= " WHERE $where";
		if(!is_null($order)) $sql .= " ORDER BY $order";
		if(!is_null($limit)) $sql .= " LIMIT $limit";

		return $this->ExecuteQuery($sql);
	}

    function SelectArray($table, $where=null, $order=null, $limit=null){
        $result = $this->Select($table, $where, $order, $limit);
        return $this->getArray($result);
    }

    function getArrayLine($results){
        return $results->fetch_array(MYSQLI_ASSOC);
    }

    function getObjectLine($results){
        return $results->fetch_object();
    }

    function getResults($results, $resType = "array"){
        $a_res = array();
        if($resType=="array"){
            while($line = $this->getArrayLine($results))
                $a_res[] = $line;
        }
        else if($resType=="object"){
            while($line = $this->getObjectLine($results))
                $a_res[] = $line;
        }

        return $a_res;
    }

    function bind_array($sql,$types,$a_bind){

            $stmt = mysqli_prepare($this->conn,$sql);
            $bind_names[] = $types;
            for ($i=0; $i<=count($a_bind);$i++)
            {
                if(isset($a_bind[$i])){
                    $bind_name = 'bind' . $i;
                    $$bind_name = $a_bind[$i];
                    $bind_names[] = &$$bind_name;
                }
            }
            call_user_func_array(array($stmt,'bind_param'),$bind_names);
            return $stmt->execute();

    }

    function realEscapeString($string){
        return mysqli_real_escape_string($this->conn, $string);
    }

	function Insert($t, $aI){
		$sql = 'INSERT INTO '.$t;
		$qT = '(';
		$qI = ' VALUES(';
        $comma=', ';
		foreach ($aI as $F) :
            $value = $this->ValueWriting($F);
            if(is_null($value))
                $value = "null";

            $qT .= $F['field'].$comma;
            $qI .= $value.$comma;

		endforeach;
        $qT = substr($qT,0,-2).')';
        $qI = substr($qI,0,-2).');';

		$sql .=$qT.$qI;

//echo $sql."<br />";
//return false;
        $this->ExecuteQuery($sql);
		return mysqli_insert_id($this->conn);
	}

    function InsertFromKey($t, $aI){
        $sql = 'INSERT INTO '.$t;
        $qT = '(';
        $qI = ' VALUES(';
        $comma=', ';
        foreach ($aI as $key=>$value) :
            if(is_null($value))
                $value = "null";

            $qT .= $key.$comma;
            $qI .= "'".$value."'".$comma;

        endforeach;
        $qT = substr($qT,0,-2).')';
        $qI = substr($qI,0,-2).');';

        $sql .=$qT.$qI;

//echo $sql."<br />";
//return false;
        $this->ExecuteQuery($sql);
        return mysqli_insert_id($this->conn);
    }

    function UpdateFromKey($t, $aI, $w){
        $sql = 'UPDATE '.$t.' SET ';

        $writevalues = "";
        foreach ($aI as $key=>$value) :
            if(is_null($value))
                $value = "null";

            $writevalues.= $key."= '";
            $writevalues.= $value."'";
            $writevalues.= ', ';

        endforeach;
        if($writevalues!=""){
            $sql.= $writevalues;
            $sql = substr($sql,0,-2);
        }

        $sql.= ' WHERE '.$w.';';
//echo $sql."<br />";
//return false;
        $this->ExecuteQuery($sql);
    }

	function Update($t, $aI, $w){
		$sql = 'UPDATE '.$t.' SET ';

        $writevalues = "";
		foreach ($aI as $F) :
            $value = $this->ValueWriting($F);
            if(is_null($value))
                $value = "null";

            $writevalues.= $F['field'].'=';
            $writevalues.= $value;
            $writevalues.= ', ';

		endforeach;
        if($writevalues!=""){
            $sql.= $writevalues;
            $sql = substr($sql,0,-2);
        }

		$sql.= ' WHERE '.$w.';';
//echo $sql."<br />";
//return false;
        $this->ExecuteQuery($sql);
	}

	function Delete($t, $w){
		$sql = 'DELETE FROM '.$t;
		$sql.= ' WHERE '.$w.';';

        $this->ExecuteQuery($sql);
	}

    function ValueWriting(array $a_Value){
        $value = $this->ValueSelector($a_Value);
        $this->ErrorMsg($value,$a_Value);
        $value = $this->ValueFormat($value,$a_Value['type']);
        $this->ErrorMsg($value,$a_Value);
        return $value;
    }

	function ValueSelector(array $a_Value){
        // 'field', 'selector', 'type', 'value', 'settype'
        switch($a_Value['selector']){
            case "value":   $value = $a_Value['value']; break;
            case "field":   $value = $_REQUEST[$a_Value['field']];break;
            case "chkbox":  $value = (isset($_REQUEST[$a_Value['field']])) ? 1 : 0; break;
            default:
                $value = "ERR_SELECTOR";
        }

        if(isset($a_Value['settype']) && $value!="ERR_SELECTOR")
            return $this->ValueSetType($value,$a_Value['settype']);
        else
            return $value;
	}

    function ValueSetType($value,$setType){
        if(isset($value)){
            if($setType=="int"){
                if(is_numeric($value)){
                    $val = intval($value);
                }
                else{
                    $val=0;
                }
            }
            else if($setType=="flt"){
                $value = str_replace(",",".",$value);
                $val = ToFloat($value);
            }
            else if($setType=="time"){


                if (strpos($value, ':') === false) {
                    $val = null;
                }else{
                    $val = $value;
                }
            }
            else
                $val = $value;
        }
        else{
            if($setType=="int")
                $val = 0;
            else if($setType=="flt")
                $val = 0.00;
            else if($setType=="time")
                $val = null;
            else
                $val = "";

        }
        return $val;
    }

    function ValueFormat($value,$type){
        $ctrlCheck = $this->ValueCheck($value,$type);
        if($ctrlCheck!==true)
            return $ctrlCheck;
        else if(is_null($value))
            return null;

        switch($type){
            case "str":
                return "'".addslashes($value)."'";
                break;
            case "time":
                if($value=="" || !isset($value) || is_null($value)){
                    return null;
                } else {
                    return "'".addslashes($value)."'";
                }
                break;
            case "date":
                if($value=="0000-00-00" || $value=="" || !isset($value) || is_null($value))
                    return null;
                else
                    if (strpos($value, '/') !== false) {
                        $value = DateInDB($value);
                    }
                    return "'".addslashes($value)."'";
                break;
            case "int":
                if($value=="") $value=0;
                return number_format($value,0,".","");
                break;
            case "flt":
                if($value=="") $value=0.00;
                return number_format($value,2,".","");
                break;
            case "year":
                return $value;
                break;

            default:
                return "ERROR_TYPE*".$value;
        }
    }

    function ValueCheck($value,$type){
        if(is_null($value))
            return true;
//        echo "**".$value." ".gettype($value)."**";
        switch($type){
            case "str":
                if(!$this->TypeCheck($value,"string"))
                    return "ERROR_STRING*".$value;
                break;
            case "date":
                if($value=="" || !isset($value) || is_null($value)){
                    break;
                } else {
                    if (strpos($value, '/') !== false) {
                        $value = DateInDB($value);
                    }

                    if(!$this->TypeCheck($value,"string"))
                        return "ERROR_STRING*".$value;
                    if(date('Y-m-d', strtotime($value)) != $value)
                        return "ERROR_DATE*".$value;
                    break;

                }

            case "time":
                if(!$this->TypeCheck($value,"string"))
                    return "ERROR_STRING*".$value;
                if(date('H:i', strtotime($value)) != $value && date('H:i:s', strtotime($value)) != $value)
                    return "ERROR_TIME*".$value;
                break;
            case "int":
                if(!$this->TypeCheck($value,"integer"))
                    return "ERROR_INT*".$value;
                break;
            case "flt":
                if(!$this->TypeCheck($value,"double"))
                    return "ERROR_FLOAT*".$value;
                break;
            case "year":
                if(!$this->TypeCheck($value,"string") && !$this->TypeCheck($value,"integer"))
                    return "ERROR_YEAR*".$value;
                break;
            default:
                return "ERROR_TYPE*".$value;
        }
        return true;
    }

    function TypeCheck($value,$type){
        $getType = gettype($value);
        if($getType==$type)
            return true;
        else if($type=="double" && $getType=="float")
            return true;
        else
            return false;
    }

    function ErrorMsg($error, array $a_Value, $msgType = "danger"){
        try{
            $errorExplode = explode("*",$error);
            $text = "Field: ". $a_Value['field']." - Type: ".$a_Value['type'];
            if($errorExplode[0]=="ERROR_SELECTOR")
                throw new Exception("ERROR SELECTOR! ".$text );
            else{
                switch($errorExplode[0]){
                    case "ERROR_TYPE":      $error_text = "ERROR TYPE! ";                 break;
                    case "ERROR_CHECK":     $error_text = "ERROR CHECK! ";                break;
                    case "ERROR_DATE":      $error_text = "ERROR DATE FORMAT! ";          break;
                    case "ERROR_TIME":      $error_text = "ERROR TIME FORMAT! ";          break;
                    case "ERROR_YEAR":      $error_text = "ERROR CHECK YEAR! ";           break;
                    case "ERROR_STRING":    $error_text = "ERROR CHECK STRING! ";         break;
                    case "ERROR_INT":       $error_text = "ERROR CHECK INTEGER! ";        break;
                    case "ERROR_FLOAT":     $error_text = "ERROR CHECK DOUBLE/FLOAT! ";   break;
                    default:                return true;
                }
                $error_text = $error_text.$text." - Value: ".$errorExplode[1]." - GetType: ".gettype($errorExplode[1]);
                throw new Exception($error_text);
            }
        }
        catch (Exception $e){
            $this->help->ErrorAlert($msgType, $this->ErrorReporting($e));
            die;
        }
    }

    function ErrorReporting($e,$query=null){
        $report = "<strong>ERROR REPORTING</strong><br>";
        if($query!=null)
            $report.= "<strong>QUERY: </strong>".$query."<br>";
        $msg = "<strong>MESSAGE: </strong>".$e->getMessage()."<br>";
        $code = "<strong>CODE: </strong>".$e->getCode()."<br>";
        $file = "<strong>FILE: </strong>".$e->getFile()."<br>";
        $line = "<strong>LINE: </strong>".$e->getLine()."<br>";

        return $report.$msg.$code.$file.$line;
    }



	function Start_Transaction(){
		$this->conn->autocommit(FALSE);
	}

    function Begin_Transaction(){
        $this->conn->begin_transaction();
    }

	function End_Transaction(){
		$this->conn->commit();
	}

    function Rollback(){
        $this->conn->rollback();
    }

    function LockTables($a_Tables){
        try{
            try{
                $comma = "";
                $str_Sql = "LOCK TABLES ";
                foreach($a_Tables as  $value){
                    $str_Sql .= $comma;
                    $str_Sql .= "$value ";
                    $comma = ",";
                }
                $str_Sql .= ";";

                return mysqli_query($this->conn, $str_Sql);
            }
            catch (mysqli_sql_exception $e){
                throw $e;
            }
        }
        catch (Exception $e) {
            if(DEBUG)
                $this->help->ErrorAlert("danger",$this->ErrorReporting($e,$str_Sql));
            else
                $this->help->ErrorAlert("danger","Errore query. Contattare il Webmaster");
            die;
        }
    }

    function UnlockTables(){
        try{
            try{
                $str_Sql = "UNLOCK TABLES;";
                return mysqli_query($this->conn, $str_Sql);
            }
            catch (mysqli_sql_exception $e){
                throw $e;
            }
        }
        catch (Exception $e) {
            if(DEBUG)
                $this->help->ErrorAlert("danger",$this->ErrorReporting($e,$str_Sql));
            else
                $this->help->ErrorAlert("danger","Errore query. Contattare il Webmaster");
            die;
        }
    }

	function __destruct(){
        if($this->conn)
		    mysqli_close($this->conn);
	}

	function lastInsertId(){
	    return mysqli_insert_id($this->conn);
    }

}

