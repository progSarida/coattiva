<?php

require CONTROLLERS."/Controller.php";

class TariffeCoazioneController extends BaseController{

    public $navigation;
    public $CC;
    public $ID;

    public function __construct($cc, $id=null)
	{
		parent::__construct();
        $this->CC = $cc;
        $this->ID = $id;
        $this->setNavigation();

        $this->createTariffsCC();
		
	}


    public function getTariff(){
        $query = "SELECT * FROM tariffe_coazione WHERE ID = '".$this->ID."' AND CC = '".$this->CC."' ";
        $a_tariff = $this->getRow($query);
        if(!empty($a_tariff['DefaultJSON']))
            $json = json_decode($a_tariff['DefaultJSON']);
        if(!empty($json->DocumentList))
            $a_tariff['DocumentList'] = $json->DocumentList;
        else
            $a_tariff['DocumentList'] = array();
        if(!empty($json->Default))
            $a_tariff['Default'] = $json->Default;
        else
            $a_tariff['Default'] = array();
        
        
        return $a_tariff;
    }

    public function checkTariffInPignoramento(){
        $query = "SELECT P.DocumentTypeId FROM tariffe_coazione T JOIN pignoramento_spese PS ON ";
        for($i=1;$i<=10;$i++){
            if($i>1)
                $query.= "OR ";
            $query.="PS.Spesa_".$i."_ID = T.ID ";
        } 
        $query.= "JOIN pignoramento_generale P ON P.ID=PS.Pignoramento_ID ";
        $query.= "WHERE T.ID = '".$this->ID."' AND T.CC = '".$this->CC."' GROUP BY P.DocumentTypeId";
        $a_pignos = $this->getRows($query);
        $a_return = array();
        foreach($a_pignos as $pigno){
            $a_return[] = $pigno['DocumentTypeId'];
        }
        return $a_return;
    }

    public function DefaultJSONUpdateField($a_json){
        $defaultString = '';
        $docListString = '';
        foreach($a_json as $docId => $jsonData){

            if(!empty($jsonData['Tipototale'])){
                if($defaultString!="")
                    $defaultString.=", ";
                $defaultString.= 'JSON_OBJECT("Tipototale", '.$jsonData['Tipototale'].', "DocumentType", '.$docId.')';
            }

            if(!empty($jsonData['DocumentType'])){
                if($docListString!="")
                    $docListString.=", ";
                $docListString.= $jsonData['DocumentType'];
            }
        }

        $jsonString = 'JSON_OBJECT(';
        $jsonString.= '"Default", JSON_ARRAY('.$defaultString.'),';
        $jsonString.= '"DocumentList", JSON_ARRAY('.$docListString.')';
        $jsonString.= ')';

        return $jsonString;
    }


    public function getDocumentTypes(){
        $query = "SELECT * FROM document_type WHERE TableTypeId = 2 ORDER BY Id";
        return $this->getRows($query,"Id");
    }


    public function getTariffs(){
        $query = "SELECT * FROM tariffe_coazione WHERE CC = '".$this->CC."' ORDER BY Tipo DESC, ID ASC";
        
        return $this->getRows($query,"ID");
    }


    public function getUpdateTariffs($description, $depositoPortata){
        $query = "SELECT * FROM tariffe_coazione WHERE Descrizione=\"".$description."\" ";
        if($depositoPortata!="")
            $query.= "AND Deposito_Portata=\"".$depositoPortata."\"";
        else
            $query.= "AND (Deposito_Portata=\"\" OR Deposito_Portata is null)";

        return $this->getRows($query);
    }

    public function delete($id){
        $query = "DELETE FROM tariffe_coazione WHERE ID=".$id;
        return $this->runQuery($query);
    }
    
    public function deleteTariffs($description, $depositoPortata){
        if(empty($this->ID))
            return false;

        $query = "DELETE FROM tariffe_coazione WHERE Descrizione=\"".$description."\" ";
        if($depositoPortata!="")
            $query.= "AND Deposito_Portata=\"".$depositoPortata."\"";
        else
            $query.= "AND (Deposito_Portata=\"\" OR Deposito_Portata is null)";

        return $this->runQuery($query);
    }


    public function getInsertCC(){
        $query = "SELECT DISTINCT CC FROM tariffe_coazione";
        
        return $this->getRows($query);
    }


    public function countTariffs(){
        $query = "SELECT COUNT(*) AS RowCount FROM tariffe_coazione WHERE CC = '".$this->CC."'";
        
        return $this->getRow($query)['RowCount'];
    }


    private function createTariffsCC(){

        if($this->countTariffs()>0)
            return array("check"=>true,"msg"=>"Tariffe presenti in archivio");

        try{
            $this->db->Start_Transaction();
            $this->db->Begin_Transaction();

            $query = "CREATE TEMPORARY TABLE tmp_tariffe SELECT * from tariffe_coazione WHERE CC='*****'";
            $this->db->ExecuteQuery($query);
            $query = "ALTER TABLE tmp_tariffe drop ID, drop CC";
            $this->db->ExecuteQuery($query);
            $query = "INSERT INTO tariffe_coazione SELECT 0,'".$this->CC."',tmp_tariffe.* FROM tmp_tariffe";
            $this->db->ExecuteQuery($query);
            $query = "DROP TABLE tmp_tariffe";
            $this->db->ExecuteQuery($query);

            $this->db->End_Transaction();
            return array("check"=>true,"msg"=>"Tariffe nuove create");

        } catch (Exception $e) {
            $this->db->Rollback();
            return array("check"=>false,"msg"=>"Creazione tariffe fallita!");
        }
    }



    private function setNavigation(){
        if(!empty($this->ID)){
            $query = "SELECT NEXT_TC.ID AS next, PREV_TC.ID AS prev FROM tariffe_coazione TC
            LEFT JOIN tariffe_coazione PREV_TC ON PREV_TC.ID=(SELECT MAX(ID) FROM tariffe_coazione WHERE ID<TC.ID AND CC=TC.CC )
            LEFT JOIN tariffe_coazione NEXT_TC ON NEXT_TC.ID=(SELECT MIN(ID) FROM tariffe_coazione WHERE ID>TC.ID AND CC=TC.CC )
            WHERE TC.ID=".$this->ID;
        }
        else{
            $query = "SELECT MIN(ID) AS next, MAX(ID) AS prev FROM tariffe_coazione WHERE CC='".$this->CC."'";
        }

        $this->navigation = $this->getRow($query);
    
    }


}