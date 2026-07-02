<?php
include_once CLS."/cls_InserimentoNelDB.php";
include_once CLS."/cls_paramNotifiche.php";

class InserimentoManualeNotifica extends InserimentoNelDb
{
    protected $Atto_Id;
    protected $Immagine_Fronte;
    protected $Immagine_Retro;
    protected $Data_Importazione;
    protected $Operatore;
    protected $cls_param;
    protected $cls_help;
    protected $path_fronte = null;
    protected $path_retro = null;
    protected $cc;
    protected $Tipo_Atto;
    protected $Tipo_File;
    
    public function Set($nome_variabile,$valore)
    {
        $this->{$nome_variabile}=$valore;
        return $this;
    }
    public function InserimentoDati()
    {
        if ($this->ControlloSeEsiste()) return $this;
        $this->table = "notifiche_importate";
        parent::Inserimento();
        return $this;
    }

    public function ControlloSeEsiste()
    {
        $query_notifiche_importate = "
            select * from notifiche_importate
            where DocumentId = $this->Atto_Id";
        $a_result = parent::SelectSQL($query_notifiche_importate);
        
        if(count($a_result)>0)
        {
            if($this->Tipo_File == "cad"){
                $query_update ="
                    update notifiche_importate
                    set CAD_Fronte = '$this->Immagine_Fronte',
                    CAD_Retro = '$this->Immagine_Retro',
                    Data_Importazione = '$this->Data_Importazione',
                    Operatore = '$this->Operatore'
                    where DocumentId = $this->Atto_Id
                ";
            }
            else{
                $query_update ="
                    update notifiche_importate
                    set Immagine_Fronte = '$this->Immagine_Fronte',
                    Immagine_Retro = '$this->Immagine_Retro',
                    Data_Importazione = '$this->Data_Importazione',
                    Operatore = '$this->Operatore'
                    where DocumentId = $this->Atto_Id
                ";
            }
            parent::UpdateSQL($query_update);
            return true;
        };
        return false;
    }

    public function PreparaRiga()
    {
        $query_atto = "select 
        A.ID as DocumentId,
        A.DocumentTypeId,
        A.PrintTypeId,
        A.FlowId,
        A.CC as CC_Comune
        from atto as A
        Where ID=$this->Atto_Id";
        
        $a_result = parent::SelectSQL($query_atto)[0];
        $this->cc = $a_result["CC_Comune"];

        if($this->Tipo_File == "cad"){
            $a_addfields = array(
                "CAD_Fronte" =>$this->Immagine_Fronte,
                "CAD_Retro" =>$this->Immagine_Retro,
                "Data_Importazione" =>$this->Data_Importazione,
                "Operatore" =>$this->Operatore,
                "Tipo_Atto" =>$this->Tipo_Atto,
                "Riferimento" =>$a_result["DocumentId"]
            );
        }
        else {
            $a_addfields = array(
                "Immagine_Fronte" =>$this->Immagine_Fronte,
                "Immagine_Retro" =>$this->Immagine_Retro,
                "Data_Importazione" =>$this->Data_Importazione,
                "Operatore" =>$this->Operatore,
                "Tipo_Atto" =>$this->Tipo_Atto,
                "Riferimento" =>$a_result["DocumentId"]
            );
        }

        
        $this->a_dati =  array(0=>array_merge($a_result,$a_addfields));
        return $this;
    }

    public function SalvaImmagini($path){
        if(!empty($this->cls_param) && !empty($this->cls_help))
        {   
            if (pathinfo($this->path_fronte, PATHINFO_EXTENSION)== "pdf")
            {
                $this->cls_param->SavePdf($this->path_fronte,$this->Immagine_Fronte,$this->cc,$path);
                return $this;
            }
            if (file_exists($this->path_fronte))$this->cls_param->SaveImageNotifica($this->path_fronte,$this->Immagine_Fronte,$this->cc,$path);
            if (file_exists($this->path_retro))$this->cls_param->SaveImageNotifica($this->path_retro,$this->Immagine_Retro,$this->cc,$path);
        }
        return $this;
    }

    public function AggiornaStatoNotificaAtto()
    {
        $query_update = "update atto set Stato_Notifica = 28 Where ID = $this->Atto_Id";
        parent::UpdateSQL($query_update);
        return $this;
    }


}