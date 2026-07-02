<?php

trait Db{

    public static $page = "";
    public static $conn;

    public static function connectToDb(){
        try{
            return mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
        }
        catch (mysqli_sql_exception $e){
            throw $e;
        }
    }

    function SetCharset($Charset){
        mysqli_set_charset( self::connectToDb(), $Charset);
    }

    public static function ExecuteQuery($str_Sql){
        try {
            return mysqli_query(self::connectToDb(), $str_Sql);
        }
        catch (mysqli_sql_exception $e) {
            ErrorAlert("danger",self::ErrorReporting($e,$str_Sql));
            die;
        }
        catch (Exception $e) {
            ErrorAlert("danger",self::ErrorReporting($e,$str_Sql));
            die;
        }
    }

    public static function SelectQuery($sql){
        return self::ExecuteQuery($sql);
    }

    public static function Select($table, $where=null, $order=null, $limit=null){
        $sql = "SELECT * FROM $table";

        if(!is_null($where)) $sql .= " WHERE $where";
        if(!is_null($order)) $sql .= " ORDER BY $order";
        if(!is_null($limit)) $sql .= " LIMIT $limit";

        return self::ExecuteQuery($sql);
    }

    public static function Insert($t, $aI){
        $sql = 'INSERT INTO '.$t;
        $qT = '(';
        $qI = ' VALUES(';
        $comma=', ';
        foreach ($aI as $F) :
            $value = self::ValueWriting($F);
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
        self::ExecuteQuery($sql);
        return mysqli_insert_id(self::connectToDb());
    }

    public static function Update($t, $aI, $w){
        $sql = 'UPDATE '.$t.' SET ';

        $writevalues = "";
        foreach ($aI as $F) :
            $value = self::ValueWriting($F);
            if(is_null($value))
                $value = "null";

            // var_dump($value);

            $writevalues.= $F['field'].'=';
            $writevalues.= $value;
            $writevalues.= ', ';

        endforeach;
        if($writevalues!=""){
            $sql.= $writevalues;
            $sql = substr($sql,0,-2);
        }

        $sql.= ' WHERE '.$w.';';
// echo $sql."<br />";
//return false;
        return self::ExecuteQuery($sql);
    }

    public static function Delete($t, $w){
        $sql = 'DELETE FROM '.$t;
        $sql.= ' WHERE '.$w.';';

        self::ExecuteQuery($sql);
    }

    public static function ValueWriting(array $a_Value){
        $value = self::ValueSelector($a_Value);
        self::ErrorMsg($value,$a_Value);
        $value = self::ValueFormat($value,$a_Value['type']);
        self::ErrorMsg($value,$a_Value);
        return $value;
    }

    public static function ValueSelector(array $a_Value){
        // 'field', 'selector', 'type', 'value', 'settype'
        switch($a_Value['selector']){
            case "value":   $value = $a_Value['value']; break;
            case "field":   $value = $_REQUEST[$a_Value['field']];break;
            case "chkbox":  $value = (isset($_REQUEST[$a_Value['field']])) ? 1 : 0; break;
            default:
                $value = "ERR_SELECTOR";
        }

        if(isset($a_Value['settype']) && $value!="ERR_SELECTOR")
            return self::ValueSetType($value,$a_Value['settype']);
        else
            return $value;
    }

    public static function ValueSetType($value,$setType){
        if(is_null($value))
            return $value;
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
                if($value==0 || $value=="0" || $value=="") $value = 0.00;

                $value = str_replace(",",".",$value);
                $val = ToFloat($value);
            }
            else if($setType=="time"){
                if (strpos($value, ':') === false)
                    $val = null;
                else
                    $val = $value;
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

    public static function ValueFormat($value,$type){
        // var_dump($value);
        $ctrlCheck = self::ValueCheck($value,$type);
        if($ctrlCheck!==true)
            return $ctrlCheck;
        else if(is_null($value))
            return null;

        switch($type){
            case "str":
            case "json":
                return "'".addslashes($value)."'";
                break;
            case "time":
                if($value=="" || !isset($value) || is_null($value)){
                    return null;
                } else {
                    return "'".$value."'";
                }
                break;
            case "date":

                if (strpos($value, '/') !== false)
                    $value = DateInDB($value);


                if(b_ValidateDate($value))
                    return "'". $value ."'";
                else
                    return null;
                break;
            case "int":
                if($value=="") $value=0;
                return (int)$value;
                break;
            case "flt":
                if($value==0 || $value=="0" || $value=="") $value = 0.00;
                return (float)$value;
                break;
            case "year":
                return (int)$value;
                break;

            default:
                return "ERROR_TYPE*".$type." ".$value;
        }
    }
    public static function getArrayLine($results){
        return $results->fetch_array(MYSQLI_ASSOC);
    }

    public static function getObjectLine($results){
        return $results->fetch_object();
    }

    public static function getResults($results, $resType = "array", $setKey = false){
        $a_res = array();
        if($resType=="array"){
            while($line = self::getArrayLine($results)){
                if($setKey===false)
                    $a_res[] = $line;
                else
                    $a_res[$line[$setKey]] = $line;
            }
        }
        else if($resType=="object"){
            while($line = self::getObjectLine($results)){
                if($setKey===false)
                    $a_res[] = $line;
                else
                    $a_res[$line[$setKey]] = $line;
            }
        }

        return $a_res;
    }
    public static function ValueCheck($value,$type){
        if(is_null($value))
            return true;
    //    echo "**".$value." ".gettype($value)."**";
        switch($type){
            case "str":
                if(!self::TypeCheck($value,"string"))
                    return "ERROR_STRING*".$value;
                break;
            case "date":

                break;
            case "time":
                if(!self::TypeCheck($value,"string"))
                    return "ERROR_STRING*".$value;
                if(date('H:i', strtotime($value)) != $value && date('H:i:s', strtotime($value)) != $value)
                    return "ERROR_TIME*".$value;
                break;
            case "int":
                if(!self::TypeCheck($value,"integer"))
                    return "ERROR_INT*".$value;
                break;
            case "flt":
                if(!self::TypeCheck($value,"double"))
                    return "ERROR_FLOAT*".$value;
                break;
            case "year":
                if(!self::TypeCheck($value,"string") && !self::TypeCheck($value,"integer"))
                    return "ERROR_YEAR*".$value;
                break;
            case "json":
                if(!self::TypeCheck($value,"string"))
                    return "ERROR_STRING*".$value;
                return true;
            default:
                return "ERROR_TYPE*".$value;
        }
        return true;
    }

    public static function TypeCheck($value,$type){
        $getType = gettype($value);
        if($getType==$type)
            return true;
        else if($type=="double" && $getType=="float")
            return true;
        else
            return false;
    }

    public static function ErrorMsg($error, array $a_Value, $msgType = "danger"){
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
            ErrorAlert($msgType, self::ErrorReporting($e));
            die;
        }
    }

    public static function getTableValuesArray($tableName, $primaryKey = "Id"){
        $a_Sql = array();
        $a_TableTypeField = unserialize(TABLE_TYPE_FIELD);
        $rs_Result = self::SelectQuery('DESCRIBE '.$tableName);
        while ($r_Result = self::getArrayLine($rs_Result)){
            if($r_Result['Field'] != $primaryKey){
                // var_dump($r_Result['Field']);
                // var_dump($_REQUEST[$r_Result['Field']]);

                if(!array_key_exists($r_Result['Field'],$_REQUEST))
                    continue;
                else if($r_Result['Type']=="json")
                    $value = json_encode($_REQUEST[$r_Result['Field']]);
                else if(is_null($_REQUEST[$r_Result['Field']]) || trim($_REQUEST[$r_Result['Field']])=="")
                    $value = null;
                else if($r_Result['Type']=="tinyint" || $r_Result['Type']=="int")
                    $value = CheckValue($r_Result['Field'],'n');
                else if($r_Result['Type']=="decimal")
                    $value = CheckValue($r_Result['Field'],'f');
                else
                    $value = CheckValue($r_Result['Field'],'s');

                if($r_Result['Type']=="json")
                    $a_Sql[] = array( 'field'=>$r_Result['Field'], 'selector'=>'value', 'type'=>"json", 'value'=>$value );
                else{
                    $str_Type = (strpos($r_Result['Type'],'(')===false) ? $r_Result['Type'] : trim(substr($r_Result['Type'],0,strpos($r_Result['Type'],'(')));
                    // if(!is_null($value))
                        $setType = $a_TableTypeField[$str_Type];
                    // else
                    //     $setType = "null";

                    $a_Sql[] = array( 
                        'field'=>$r_Result['Field'], 
                        'selector'=>'value', 
                        'type'=>$setType, 
                        'value'=>$value, 
                        'settype'=>$setType 
                    );
                }
            }
        }
        return $a_Sql;
    }

    public static function DbInsert($a_params){
        $sql = 'INSERT INTO '.$a_params['table'];
        $sqlFields = '(';
        $sqlValues = ' VALUES(';
        $comma = "";
        foreach ($a_params['fields'] as $field){
            $sqlFields .= $comma.$field['name'];
            $sqlValues .= $comma.self::checkFieldType($field);
            if($comma=='')
                $comma = ', ';
        }
        $sqlFields .= ')';
        $sqlValues .= ');';

        $sql .= $sqlFields.$sqlValues;

        //echo "<br><br>QUERY: ".$sql."<br>";

        if(!self::ExecuteQuery($sql))
            return false;
        else
            return mysqli_insert_id(self::connectToDb());
    }

    public static function DbUpdate($a_params){

        $flagWhere = true;
        $sql = 'UPDATE '.$a_params['table'].' SET ';

        $comma = "";
        foreach ($a_params['fields'] as $field){
            $sql.= $comma.$field['name']."=".self::checkFieldType($field);
            if($comma=='')
                $comma = ', ';
        }

        $sql.= ' WHERE ';
        foreach ($a_params['updateField'] as $update)
        {
            if(!isset($update['name'])) {
                $flagWhere = false;
                break;
            }
            if(!empty($update['operator']))
                $sql .= ' '.$update['operator'];
            $sql.= ' '.$update['name'].'='.self::checkFieldType($update);


        }

        if(!$flagWhere)
            $sql.= $a_params['updateField']['name'].'='.self::checkFieldType($a_params['updateField']).' ;';

        else $sql .= ' ;';
        //echo "<br><br>QUERY: ".$sql."<br>";
        return self::ExecuteQuery($sql);
    }

    public static function DbSave($a_params){
        if(!$a_params) return false;
        if(isset($a_params['updateField']))
            return self::DbUpdate($a_params);
        else
            return self::DbInsert($a_params);
    }

    public static function getColumnDataTypes($table){
        $results = self::ExecuteQuery("SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT FROM information_schema.columns WHERE table_name='".$table."'");
        $a_columns = self::getResults($results);
        $a_return = array();
        for($i=0;$i<count($a_columns);$i++){
            $a_return[$a_columns[$i]['COLUMN_NAME']] = array(
                "DATA_TYPE" => $a_columns[$i]['DATA_TYPE'],
                "COLUMN_TYPE" => $a_columns[$i]['COLUMN_TYPE'],
                "IS_NULLABLE" => $a_columns[$i]['IS_NULLABLE'],
                "COLUMN_DEFAULT" => $a_columns[$i]['COLUMN_DEFAULT'],
            );
        }
        return $a_return;
    }

    public static function GetObjectQuery($table, $a_values, $a_dataTypes = null, $a_where = null)
    {
        $a_params = array( 'table' => $table, 'fields'=> array() );
        if(is_null($a_dataTypes))
            $a_dataTypes = self::getColumnDataTypes($table);
        foreach($a_values as $field=>$value)
            $a_params['fields'][] = array('name' => $field, 'column_info' => $a_dataTypes[$field], 'value' =>$value);

        if(is_array($a_where)){
            $a_params['updateField'] = array();
            $cont = 0;
            foreach($a_where as $field=>$value){
                $a_params['updateField'][$cont] = array('name' => $field, 'column_info' => $a_dataTypes[$field], 'value' =>$value);
                if($cont>0)
                    $a_params['updateField'][$cont]['operator'] = "AND";
                $cont++;
            }

        }

        return $a_params;
    }

    public static function checkFieldType($field){
        switch(strtolower($field['column_info']['DATA_TYPE']))
        {
            case "int":
            case "tinyint":
            case "smallint":
            case "mediumint":
            case "bigint":
            case "year":
            case "decimal":
            case "float":
            case "double":
                $value = $field['value'];
                break;
            case "json":
                $value = "'".$field['value']."'";
                break;

            default :
                $value = '"'.$field['value'].'"';
                break;
        }

        if(is_null($field['value']) || trim($field['value'])==""){
            if($field['column_info']['IS_NULLABLE']=="NO" && !is_null($field['column_info']['COLUMN_DEFAULT'])){
                if(is_numeric($field['column_info']['COLUMN_DEFAULT']))
                    $value = $field['column_info']['COLUMN_DEFAULT'];
                else
                    $value = '"'.$field['column_info']['COLUMN_DEFAULT'].'"';
            }
            else if($field['column_info']['IS_NULLABLE']=="YES")
                $value = 'null';
            else
                $value = '""';
        }

        return $value;
    }

    public static function saveDbFiles($a_files, $table, $updateField, $updateId){
        foreach ($a_files as $a_file){
            if(empty($a_file['allowed']))
                $a_file['allowed'] = array();
            if(empty($a_file['fileName']))
                $a_file['fileName'] = null;

            if(is_array($_FILES[$a_file['key']]['name']))
                $a_upload_file = array(
                    "name" => $_FILES[$a_file['key']]['name'][0],
                    "tmp_name" => $_FILES[$a_file['key']]['tmp_name'][0]
                );
            else
                $a_upload_file = array(
                    "name" => $_FILES[$a_file['key']]['name'],
                    "tmp_name" => $_FILES[$a_file['key']]['tmp_name']
                );

            if(!empty($a_upload_file['name'])){
                $fileName = self::uploadFile($a_upload_file, $a_file['path'], $a_file['allowed'], $a_file['fileName']);
                if(!empty($fileName)){
                    $a_Sql[] = array( 'field'=>$a_file['field'], 'selector'=>'value', 'type'=>'str', 'value'=>$fileName);

                    $check = self::Update($table ,$a_Sql,$updateField."=".$updateId);
                    if(!$check)
                        return array(
                            'status' => false,
                            'table' => $table,
                            'update_field' => $updateField,
                            'update_id' => $updateId,
                            'upload_array' => $a_Sql
                        );
                }
            }
        }
        return array(
            'status' => true,
            'table' => $table,
            'update_field' => $updateField,
            'update_id' => $updateId,
            'upload_array' => true
        );
    }

    public static function uploadFile($a_file, $destinationPath, $a_allowed = array(), $fileName = null, $dbInfo=null){

        $extension = pathinfo($a_file['name'], PATHINFO_EXTENSION);

        //Se ho una limitazione dei tipi di file allora controllo che l'estensione del file sia nell'array
        //Problemi per la gestione dei punti all'interno del nome file
        if(count($a_allowed)==0 || (count($a_allowed)>0 && array_search($extension, $a_allowed)!==false))
            $checkExtension = true;
        else
            $checkExtension = false;

        if($checkExtension){
            if ($a_file['tmp_name'] != "") {
                if(is_null($fileName)){
                    $destinationName = $a_file['name'];
                    $fileName = str_replace(".".$extension,"",$destinationName);
                }
                else
                    $destinationName = $fileName."_".date('Y-m-d_H-i').".".$extension;

                if (!move_uploaded_file($a_file['tmp_name'], $destinationPath . "/" . $destinationName)){
                    echo "ERRORE UPLOAD: FILE ". $a_file['name']." NON CARICATO!";
                    die;
                }

                if(!empty($dbInfo)){
                    
                    $fields = "FileName, Title";
                    $values = '"'.$destinationName.'", "'.$fileName.'"';
                    foreach($dbInfo['fields'] as $field=>$value){
                        $fields.=", ".$field;
                        $values.=", ";
                        if(is_null($value))
                            $values.= "null";
                        else if(is_numeric($value))
                            $values.= $value;
                        else
                            $values.= '"'.$value.'"';
                    }
                    $query = "INSERT INTO ".$dbInfo['table']." (".$fields.") VALUES (".$values.")";
                    self::ExecuteQuery($query);
                }    

                return $destinationName;

            }
            else{
                echo "ERRORE UPLOAD: FILE TMP ".$a_file['name']." NON TROVATO!";
                die;
            }
        }
        return false;
    }

    public static function uploadFiles($destinationPath, $dbInfo=null, $a_allowed = array(), $fileKey = 'file', $fileName = null){
        $a_uploadedFiles = array();
        if(isset($_FILES[$fileKey]) && $_FILES[$fileKey]['name'][0]!=""){
            if (!is_dir($destinationPath))
                mkdir($destinationPath);

            $filesNumber = count($_FILES[$fileKey]['name']);
            for( $i=0 ; $i < $filesNumber ; $i++ ) {
                $a_file = array(
                    "name" => $_FILES[$fileKey]['name'][$i],
                    "tmp_name" => $_FILES[$fileKey]['tmp_name'][$i],
                );
                $destinationName = self::uploadFile($a_file, $destinationPath, $a_allowed, $fileName, $dbInfo);
                if($destinationName!==false){
                    $a_uploadedFiles[] = $destinationName;
                }                
            }
        }
        return $a_uploadedFiles;
    }



    public static function ErrorReporting($e,$query=null){


        if(DEBUG){
            $report = "<strong>ERROR REPORTING MG</strong><br>";
            if($query!=null)
                $report.= '<strong>QUERY: </strong>'. $query .'<br>';
            $report.= '<strong>MESSAGE: </strong>'. $e->getMessage() .'<br><strong>CODE: </strong>'. $e->getCode() .'<br><strong>FILE: </strong><br>';
        }
        else{
            $report = 'Errore query. Contattare il Webmaster';
        }
        $str_Report = str_replace("<strong>","", $report);
        $str_Report = str_replace("</strong>","", $str_Report);
        $str_Report = str_replace("<br>","\n", $str_Report);

        $token = "1014491703:AAE5Es6pWfG35FOpkmTGYO6NRbWNrbabDoo";
        $chatIds = array("304222168");
        foreach($chatIds as $chatId) {
            // Send Message To chat id

            $data = [
                'text' => $str_Report,
                'chat_id' => $chatId
            ];

//            file_get_contents("https://api.telegram.org/bot$token/sendMessage?" . http_build_query($data) );

        }

        return $report;
    }

    public static function Start_Transaction(){
        self::connectToDb()->autocommit(FALSE);
    }

    public static function Begin_Transaction(){
        self::connectToDb()->begin_transaction();
    }

    public static function End_Transaction(){
        self::connectToDb()->commit();
    }

    public static function Rollback(){
        self::connectToDb()->rollback();
    }

    public static function LastId(){
        return mysqli_insert_id(self::connectToDb());
    }

    public static function LockTables($a_Tables){
        try{
            $comma = "";
            $str_Sql = "LOCK TABLES ";
            foreach($a_Tables as  $value){
                $str_Sql .= $comma;
                $str_Sql .= "$value ";
                $comma = ",";
            }
            $str_Sql .= ";";

            return mysqli_query(self::connectToDb(), $str_Sql);
        }
        catch (mysqli_sql_exception $e){
            ErrorAlert("danger",self::ErrorReporting($e,$str_Sql));
            die;
        }
        catch (Exception $e) {
            ErrorAlert("danger",self::ErrorReporting($e,$str_Sql));
            die;
        }
    }

    public static function UnlockTables(){

        try{
            $str_Sql = "UNLOCK TABLES;";
            return mysqli_query(self::connectToDb(), $str_Sql);
        }
        catch (mysqli_sql_exception $e){
            ErrorAlert("danger",self::ErrorReporting($e,$str_Sql));
            die;
        }
        catch (Exception $e) {
            ErrorAlert("danger",self::ErrorReporting($e,$str_Sql));
            die;
        }
    }

    function __destruct(){
        if(self::connectToDb())
            mysqli_close(self::connectToDb());
    }
}

