<?php

include_once CLS."/traits.php";

class PECIngiunzione
{
    protected $cls_db;
    protected $CC;
    protected $Comune_ID=null;
    protected $results=array();
    protected $path;
    protected $deliveryPath;
    protected $nodeliveryPath;

    use tSelectSQL;

    function Query()
    {
        $q = "
        select
        E.Acceptance_Receipt as Ricevuta_Accettazione,
        E.Delivery_Receipt as Ricevuta_Consegna,
        CONCAT(
            'PEC_',DT.PrefixName,'_',A.CC,'_',A.Anno_Cronologico,'_',A.ID_Cronologico,'_',A.Data_Stampa,'__CONSEGNA.eml'
        ) as Consegna,
        CONCAT(
            'PEC_',DT.PrefixName,'_',A.CC,'_',A.Anno_Cronologico,'_',A.ID_Cronologico,'_',A.Data_Stampa,'__AVVISODIMANCATACONSEGNAeml'
        ) as MancataConsegna
        from atto A join document_type DT on A.DocumentTypeId = DT.ID
        left join emails E on E.ID = A.Email_ID
        where A.CC='$this->CC' 
        and A.Partita_ID=$this->Comune_ID
        and not (A.Email_Id is null)
        ";
        return $q;
    }

    public function Esegui()
    {
        
      if (is_null($this->Comune_ID) || ($this->Comune_ID=="")) return;
      
      $this->results = $this->SelectSQL($this->Query());
      if ($this->IsResult())
      {
        $this->deliveryPath = $this->path."/".$this->results[0]["Consegna"];
        $this->nodeliveryPath = $this->path."/".$this->results[0]["MancataConsegna"];
      }
    }

    public function Set($variabile, $valore)
    {
        $this->{$variabile} = $valore;
        return $this;
    }

    public function GetNumber()
    {
        return count($this->results);
    }

    public function IsResult()
    {
        return $this->GetNumber()>0;
    }

    public function IsAccettato()
    {
        return $this->results[0]["Ricevuta_Accettazione"]=="1";
    }
    public function IsConsegnato()
    {
        return $this->results[0]["Ricevuta_Consegna"]=="1";
    }
    public function IsConsegnatoExist()
    {
        return $this->results[0]["Ricevuta_Consegna"]=="1";
    }
    public function HTMLConsegna()
    {
        if(!$this->IsResult()) return;
        
        $delivery = '<a title="Ricevuta di consegna" href="'.$this->deliveryPath.'" class="fa-solid fa-check fa-lg" style="color:darkgreen; cursor:pointer; margin-left: 5px;" download></a>';
        $nodelivery = '<a title="Ricevuta di mancata consegna" href="'.$this->nodeliveryPath.'" class="fa-solid fa-exclamation fa-lg" style="color:darkred; cursor:pointer; margin-left: 5px;" download></a>';

        if($this->IsConsegnato()) return $delivery; else return $nodelivery;
    }


}
?>