<?php
class  CLS_DB2{

	function ExecuteQuery($sql){
        $check = mysql_query($sql);
		if($check!==false){
            return $check;
		}
		else{
            $this->ErrorDb("danger",$this->ErrorReporting($sql));

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

	function Insert($table, array $aI){
        $query = "INSERT INTO ".$table." ";

        $qT = '(';
        $qI = ' VALUES(';
        $comma=', ';
        foreach ($aI as $key=>$value) :
            if(is_null($value))
                $value = "null";

            $qT .= $key.$comma;
            if(is_string($value))
                $qI .=  "\"".$value."\"".$comma;
            else
                $qI .=  $value.$comma;

        endforeach;
        $qT = substr($qT,0,-2).')';
        $qI = substr($qI,0,-2).');';

        $query.= $qT.$qI;

        $this->ExecuteQuery($query);

		return mysql_insert_id();
	}

	function Update($table, array $aU, $where){
		$sql = 'UPDATE '.$table.' SET ';

        $writevalues = "";
		foreach ($aU as $key=>$value) :
            if(is_null($value))
                $value = "null";

            $writevalues .= $key."=";
            if(is_string($value))
                $writevalues .=  "\"".$value."\"";
            else
                $writevalues .=  $value;

            $writevalues.= ', ';

		endforeach;
        if($writevalues!=""){
            $sql.= $writevalues;
            $sql = substr($sql,0,-2);
        }

		$sql.= ' WHERE '.$where.';';
        $this->ExecuteQuery($sql);
	}

	function Delete( $table, $where){
		$sql = 'DELETE FROM '.$table;
		$sql.= ' WHERE '.$where.';';

        $this->ExecuteQuery($sql);
	}

    function ErrorReporting($query=null){
        $report = "<strong>ERROR REPORTING</strong><br>";
        if($query!=null)
            $report.= "<strong>QUERY: </strong>".$query."<br>";
        $msg = "<strong>MESSAGE: </strong>".mysql_error()."<br>";
        $number = "<strong>NUMBER: </strong>".mysql_errno()."<br>";


        return $report.$msg.$number;
    }

    function Begin_Transaction(){
        mysql_query("BEGIN");
    }

	function End_Transaction(){
        mysql_query("COMMIT");
	}

    function Rollback(){
        mysql_query("ROLLBACK");
    }

    function LockTables($a_Tables){
        try{
            $comma = "";
            $str_Sql = "LOCK TABLES ";
            foreach($a_Tables as  $value){
                $str_Sql .= $comma;
                $str_Sql .= "$value ";
                $comma = ",";
            }
            $str_Sql .= ";";

            return mysql_query($str_Sql);
        }
        catch (Exception $e) {

            ErrorDb("danger",$this->ErrorReporting($e,$str_Sql));

            die;
        }
    }

    function UnlockTables(){
        try{
            $str_Sql = "UNLOCK TABLES;";
            return mysql_query($str_Sql);
        }
        catch (Exception $e) {
            ErrorDb("danger",$this->ErrorReporting($e,$str_Sql));

            die;
        }
    }

	function __destruct(){
        mysql_close();
	}

    function ErrorDb($msgType,$msgText){
        // $msgType success(verde), info(azzurro), warning(giallo), danger(rosso)
        echo "<div class='alert alert-".$msgType."'>".$msgText."</div>";
        die;
    }

    function getArrayLine($results){
        return mysql_fetch_array($results, MYSQL_ASSOC);
    }

    function getArray($results){
        $a_get = array();
        while($line = $this->getArrayLine($results))
            $a_get[] = $line;

        return $a_get;
    }
}
