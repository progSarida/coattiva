<?php
include_once CLS."/cls_db.php";

class Giacenze 
{

    private $cls_db;
    
    protected $CC;
    protected $offset;
    protected $web_path_root;
    protected $path_root;
    protected $a_result = array();

    public function Set($nome_variabile,$valore)
    {
        $this->{$nome_variabile}=$valore;
        return $this;
    }

    
    function query($CC,$offset)
    {
        $q = "SELECT 
        PT.ID as Partita_ID_Link,
        A.ID as Atto_ID,
        A.CC,
        PT.Comune_ID as Partita_ID,
        U.Comune_ID as Utente_ID,
        U.Cognome,
        U.Nome,
        U.Ditta,
        A.Anno_Cronologico,
        A.ID_Cronologico,
        (Select Descrizione from parametri_notifica PN where PN.ID = A.Modalita_Notifica) as Modalita_Notifica,
        (Select Descrizione from parametri_notifica PN where PN.ID = A.Stato_Notifica) as Stato_Notifica,
        NI.Immagine_Fronte,
        NI.Immagine_Retro
        FROM atto A
        JOIN partita_tributi PT on PT.ID=A.Partita_ID
        JOIN utente U on U.ID = PT.Utente_ID
        JOIN notifiche_importate NI ON NI.DocumentId=A.ID AND NI.DocumentTypeId=A.DocumentTypeId
        WHERE A.ID = (
            SELECT MAX(ID) FROM atto WHERE Partita_ID=A.Partita_ID AND DocumentTypeId IN (2,4,12)
        )
        AND A.Modalita_Notifica IN (11,12) AND (A.Stato_Notifica=0 OR A.Stato_Notifica IS null)
        AND A.CC = '$CC'
        order by U.Cognome,U.Nome,U.Ditta
        limit 1 offset $offset
        
        ";
        
        return $q;
    }
    function __construct(cls_db $value)
    {
        $this->cls_db = $value;
    } 

    protected function SelectSQL($sql)
    {
        $ret= $this->cls_db->getResults(
            $this->cls_db->ExecuteQuery($sql)
        );
        return $ret;
    }

    public function Esegui()
    {
        $results_ = $this->SelectSQL($this->query($this->CC,$this->offset));
        if (count($results_)==0) return array();
        $results = $results_[0];
        $results["path_completo_immagine_fronte"] = $this->web_path_root."/".$this->CC."/".$results["Immagine_Fronte"];
        $results["path_completo_immagine_retro"] = $this->web_path_root."/".$this->CC."/".$results["Immagine_Retro"];
        $results["path_fisico_immagine_fronte"] = $this->path_root."/".$this->CC."/".$results["Immagine_Fronte"];
        $results["path_fisico_immagine_retro"] = $this->path_root."/".$this->CC."/".$results["Immagine_Retro"];
        $this->a_result = $results;
        return $this;
    }


    public function IsResult()
    {
        return $this->GetNumberNotifiche()>0;
    }
    public function IsFronteImmagine()
    {
        
        return file_exists($this->a_result["path_fisico_immagine_fronte"]);
    }
    public function IsRetroImmagine()
    {
        return file_exists($this->a_result["path_fisico_immagine_retro"]);
    }
    public function IsImmagini()
    {
        return  $this->IsFronteImmagine() && $this->IsRetroImmagine();
    }
    public function GetResult()
    {
        return $this->a_result;
    }

    public function IsDitta()
    {
        return $this->a_result["Ditta"]!=null;
    }

    public function GetParametriNotifiche()
    {
        $q = "select ID,Descrizione From parametri_notifica Where Tipo_Dato='stato'";
        return  $this->SelectSQL($q);
    }

    public function GetNumberNotifiche()
    {
        $q = "SELECT count(*) as quanti
        FROM atto A
        JOIN partita_tributi PT on PT.ID=A.Partita_ID
        JOIN notifiche_importate NI ON NI.DocumentId=A.ID AND NI.DocumentTypeId=A.DocumentTypeId
        WHERE A.ID = (
            SELECT MAX(ID) FROM atto WHERE Partita_ID=A.Partita_ID AND DocumentTypeId IN (2,4,12)
        )
        AND A.Modalita_Notifica IN (11,12) AND (A.Stato_Notifica=0 OR A.Stato_Notifica IS null)
        AND A.CC='$this->CC'";
        $res = $this->SelectSQL($q);
        return  $res[0]["quanti"];
    }
}

?>