<?php
include_once CLS . "/cls_DateTimeInLine.php";

class PignoramentoPressoTerzi extends InserimentoNelDb
{
    //public $id;

    public $Pignoramento_ID;
    public $CC;
    public $Terzo_ID;
    public $Azienda;
    public $Fonte_Dati;
    public $Tipo_Contratto_Lavoro;
    public $Note;
    public $Data_Costituzione_Ditta_Lavoro;
    public $Data_Ditta_Operativa_Lavoro;
    public $Data_Dipendenze_Lavoro;
    public $Tipo_Terzi;

    private $valorizzato = false;
   
    private $cls_date;

    function __construct($cls_db)
    {
        $this->cls_db = $cls_db;
        $this->cls_date = new cls_DateTimeI("DB", false); 
    }

    public function Leggi($id)
    {
        $sql = "select * from pignoramento_presso_terzi where ID = $id";
        $result = parent::SelectSQL($sql);
        if (count($result)==0)
        {
            $this->valorizzato = false;
            return;
        }
        else
        {
            $this->valorizzato = true;
        }

        $ret = $result[0];
        $object = json_decode(json_encode($ret));

        $this->Pignoramento_ID = $object->Pignoramento_ID;
        $this->CC = $object->CC;
        $this->Terzo_ID = $object->Terzo_ID;
        $this->Azienda = $object->Azienda;
        $this->Fonte_Dati = $object->Fonte_Dati;
        $this->Tipo_Contratto_Lavoro = $object->Tipo_Contratto_Lavoro;
        $this->Note = $object->Note;
        $this->Data_Costituzione_Ditta_Lavoro = $object->Data_Costituzione_Ditta_Lavoro;
        $this->Data_Ditta_Operativa_Lavoro = $object->Data_Ditta_Operativa_Lavoro;
        $this->Data_Dipendenze_Lavoro = $object->Data_Dipendenze_Lavoro;

    }

    public function Inserisci()
    {
        $date = function($d)
        {
            if(is_null($d) || ($d=="")) return null;
            return $this->cls_date->GetDateDB($d, "IT");
        };

        $a_dbParams = array(
            'table' => 'pignoramento_presso_terzi',
            'fields'=> array(
                array(  'name' => 'Pignoramento_ID',  'type' => 'int', 'value' => $this->Pignoramento_ID),
                array(  'name' => 'Terzo_ID',  'type' => 'int', 'value' => $this->Terzo_ID),
                array(  'name' => 'Tipo_Terzi',  'type' => 'string', 'value' => $this->Tipo_Terzi),
                array(  'name' => 'CC',        'type' => 'string', 'value' =>  $this->CC),
                array(  'name' => 'Azienda',        'type' => 'string', 'value' =>  $this->Azienda),
                array(  'name' => 'Fonte_Dati',        'type' => 'string', 'value' =>  $this->Fonte_Dati),
                array(  'name' => 'Tipo_Contratto_Lavoro',        'type' => 'string', 'value' =>  $this->Tipo_Contratto_Lavoro),
                array(  'name' => 'Note',        'type' => 'string', 'value' =>  $this->Note),
                array(  'name' => 'Data_Costituzione_Ditta_Lavoro',            'type' => 'date', 'value' =>$date($this->Data_Costituzione_Ditta_Lavoro)),
                array(  'name' => 'Data_Ditta_Operativa_Lavoro',            'type' => 'date', 'value' =>$date($this->Data_Ditta_Operativa_Lavoro)),
                array(  'name' => 'Data_Dipendenze_Lavoro',            'type' => 'date', 'value' =>$date($this->Data_Dipendenze_Lavoro)),
               
            )
        );

        $this->cls_db->DbSave( $a_dbParams);
    }

    public function Cancella($id)
    {
        $sql = "delete from pignoramento_presso_terzi where ID = $id";
        parent::DeleteSQL($sql);
    }

    public function Update($id)
    {
        $date = function($d)
        {
            if(is_null($d) || ($d=="")) return null;
            return $this->cls_date->GetDateDB($d, "IT");
        };

        $a_dbParams = array(
            'table' => 'pignoramento_presso_terzi',
            'updateField' => array(
                array('name' => 'Id',  'type' => 'int', 'value' => $id),
            ),
            'fields'=> array(
                array(  'name' => 'Pignoramento_ID',  'type' => 'int', 'value' => $this->Pignoramento_ID),
                array(  'name' => 'Terzo_ID',  'type' => 'int', 'value' => $this->Terzo_ID),
                array(  'name' => 'Tipo_Terzi',  'type' => 'string', 'value' => $this->Tipo_Terzi),
                array(  'name' => 'CC',        'type' => 'string', 'value' =>  $this->CC),
                array(  'name' => 'Azienda',        'type' => 'string', 'value' =>  $this->Azienda),
                array(  'name' => 'Fonte_Dati',        'type' => 'string', 'value' =>  $this->Fonte_Dati),
                array(  'name' => 'Tipo_Contratto_Lavoro',        'type' => 'string', 'value' =>  $this->Tipo_Contratto_Lavoro),
                array(  'name' => 'Note',        'type' => 'string', 'value' =>  $this->Note),
                array(  'name' => 'Data_Costituzione_Ditta_Lavoro',            'type' => 'date', 'value' =>$date($this->Data_Costituzione_Ditta_Lavoro)),
                array(  'name' => 'Data_Ditta_Operativa_Lavoro',            'type' => 'date', 'value' =>$date($this->Data_Ditta_Operativa_Lavoro)),
                array(  'name' => 'Data_Dipendenze_Lavoro',            'type' => 'date', 'value' =>$date($this->Data_Dipendenze_Lavoro)),
               
            )
        );

        $this->cls_db->DbSave( $a_dbParams);
    }

    public function __invoke($pigno_id,$utente_id,$elaboration_id,$tipo_terzi = "lavoro")
    {
        
        $assegnazione_terzo = new AssegnazioneTerzoPvt($this->cls_db);
        $assegnazione_terzo->PrendiTerzi($utente_id,$elaboration_id);
        $collection =  $assegnazione_terzo->a_result;
        foreach($collection as $row)
        {
            $this->Pignoramento_ID = $pigno_id;
            foreach($row as $key=>$item)
            {
                if($key=="ID") continue;
                $this->$key = $item;
                $this->Tipo_Terzi = $tipo_terzi;
            }
            $this->Inserisci();
        }
    }
}

class AssegnazioneTerzoPvt extends InserimentoNelDb
{
    //public $id;

    public $Utente_ID;
    public $CC;
    public $Terzo_ID;
    public $Azienda;
    public $Fonte_Dati;
    public $Tipo_Contratto_Lavoro;
    public $Note;
    public $Data_Costituzione_Ditta_Lavoro;
    public $Data_Ditta_Operativa_Lavoro;
    public $Data_Dipendenze_Lavoro;
    public $Elaboration_Id;

    public $a_result;
    private $valorizzato = false;
    private $cls_date;

    function __construct($cls_db)
    {
        $this->cls_db = $cls_db;
        $this->cls_date = new cls_DateTimeI("DB", false); 
    }
    public function PrendiNomeCognome()
    {
        $id = $this->Terzo_ID;
        if(!isset($id)) return "";
        $sql = "select * from v_anagrafe where Utente_ID =$id";
        $result = parent::SelectSQL($sql);
        $result = $result[0];
        return rtrim($result["Cognome_Ditta"]." ".$result["Nome"]);
    }

    public function PrendiTerzi($Utente_ID,$Elaboration_Id)
    {
        $sql = "select * from terzo_pvt where Utente_ID = $Utente_ID";//and Elaboration_Id = $Elaboration_Id";
        $result = parent::SelectSQL($sql);
        $this->a_result = $result;
        return $result;
    }
    public function Leggi($id)
    {
        // $sql = "select * from terzo_pvt where ID = $id";
        // $result = parent::SelectSQL($sql);
        $result = $this->a_result;
        $ret = null;
        if (count($result)==0)
        {
            $this->valorizzato = false;
            return;
        }
        else
        {
            $find = false;
            foreach($result as $item)
            {
                if($item["ID"]==$id)
                {
                    $ret = $item;
                    $find = true;
                    break;
                }
            }
            if ($find)
                $this->valorizzato = true;
            else
            {
                $this->valorizzato = false;
                return;
            }
        }

        //$ret = $result[0];
        $object = json_decode(json_encode($ret));

        $this->Utente_ID = $object->Utente_ID;
        $this->CC = $object->CC;
        $this->Terzo_ID = $object->Terzo_ID;
        $this->Azienda = $object->Azienda;
        $this->Fonte_Dati = $object->Fonte_Dati;
        $this->Tipo_Contratto_Lavoro = $object->Tipo_Contratto_Lavoro;
        $this->Note = $object->Note;
        $this->Data_Costituzione_Ditta_Lavoro = $object->Data_Costituzione_Ditta_Lavoro;
        $this->Data_Ditta_Operativa_Lavoro = $object->Data_Ditta_Operativa_Lavoro;
        $this->Data_Dipendenze_Lavoro = $object->Data_Dipendenze_Lavoro;
        $this->Elaboration_Id = $object->Elaboration_Id;

    }


    public function Cancella($id)
    {
        $sql = "delete from terzo_pvt where ID = $id";
        parent::DeleteSQL($sql);
    }


    public function Update($id)
    {
        $date = function($d)
        {
            if(is_null($d) || ($d=="")) return null;
            return $this->cls_date->GetDateDB($d, "IT");
        };

        $a_dbParams = array(
            'table' => 'terzo_pvt',
            'updateField' => array(
                array('name' => 'Id',  'type' => 'int', 'value' => $id),
            ),
            'fields'=> array(
                array(  'name' => 'Utente_ID',  'type' => 'int', 'value' => $this->Utente_ID),
                array(  'name' => 'Terzo_ID',  'type' => 'int', 'value' => $this->Terzo_ID),
                array(  'name' => 'Elaboration_Id',  'type' => 'int', 'value' => $this->Elaboration_Id),
                array(  'name' => 'CC',        'type' => 'string', 'value' =>  $this->CC),
                array(  'name' => 'Azienda',        'type' => 'string', 'value' =>  $this->Azienda),
                array(  'name' => 'Fonte_Dati',        'type' => 'string', 'value' =>  $this->Fonte_Dati),
                array(  'name' => 'Tipo_Contratto_Lavoro',        'type' => 'string', 'value' =>  $this->Tipo_Contratto_Lavoro),
                array(  'name' => 'Note',        'type' => 'string', 'value' =>  $this->Note),
                array(  'name' => 'Data_Costituzione_Ditta_Lavoro',            'type' => 'date', 'value' =>$date($this->Data_Costituzione_Ditta_Lavoro)),
                array(  'name' => 'Data_Ditta_Operativa_Lavoro',            'type' => 'date', 'value' =>$date($this->Data_Ditta_Operativa_Lavoro)),
                array(  'name' => 'Data_Dipendenze_Lavoro',            'type' => 'date', 'value' =>$date($this->Data_Dipendenze_Lavoro)),
               
            )
        );

        $this->cls_db->DbSave( $a_dbParams);
    }

    public function Insert()
    {
        $date = function($d)
        {
            return $this->cls_date->GetDateDB($d, "IT");
        };
        
        $a_dbParams = array(
            'table' => 'terzo_pvt',
            'fields'=> array(
                array(  'name' => 'Utente_ID',  'type' => 'int', 'value' => $this->Utente_ID),
                array(  'name' => 'Terzo_ID',  'type' => 'int', 'value' => $this->Terzo_ID),
                array(  'name' => 'Elaboration_Id',  'type' => 'int', 'value' => $this->Elaboration_Id),
                array(  'name' => 'CC',        'type' => 'string', 'value' =>  $this->CC),
                array(  'name' => 'Azienda',        'type' => 'string', 'value' =>  $this->Azienda),
                array(  'name' => 'Fonte_Dati',        'type' => 'string', 'value' =>  $this->Fonte_Dati),
                array(  'name' => 'Tipo_Contratto_Lavoro',        'type' => 'string', 'value' =>  $this->Tipo_Contratto_Lavoro),
                array(  'name' => 'Note',        'type' => 'string', 'value' =>  $this->Note),
                array(  'name' => 'Data_Costituzione_Ditta_Lavoro',            'type' => 'date', 'value' =>$date($this->Data_Costituzione_Ditta_Lavoro)),
                array(  'name' => 'Data_Ditta_Operativa_Lavoro',            'type' => 'date', 'value' =>$date($this->Data_Ditta_Operativa_Lavoro)),
                array(  'name' => 'Data_Dipendenze_Lavoro',            'type' => 'date', 'value' =>$date($this->Data_Dipendenze_Lavoro)),
               
            )
        );

        $this->cls_db->DbSave( $a_dbParams);
    }



    public function Exist()
    {
        $query ="
        select ID
        from terzo_pvt
        where Elaboration_Id = $this->Elaboration_Id
        and CC = '$this->CC'
        and Utente_ID = $this->Utente_ID
        and Terzo_ID = $this->Terzo_ID
        ";
        $result = parent::SelectSQL($query);
        if (count($result)==0)
        {
            return 0;
        }
        return  $result[0]["ID"];
        
    }
}


?>