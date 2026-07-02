<?php 
include_once CLS."/cls_InserimentoManualeNotifica.php";
include_once CLS."/cls_paramNotifiche.php";
include_once CLS."/cls_LOG.php";
class InserimentoManualeCAD extends InserimentoManualeNotifica
{
    protected $CAD_Fronte = null;
    protected $CAD_Retro = null;
    protected $cc;

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
        //$this->cc = $a_result["CC_Comune"];
        $a_addfields = array(
            "CAD_Fronte" =>$this->CAD_Fronte,
            "CAD_Retro" =>$this->CAD_Retro,
            "Data_Importazione" =>$this->Data_Importazione,
            "Operatore" =>$this->Operatore,
            "Tipo_Atto" =>$this->Tipo_Atto,
            "Riferimento" =>$a_result["DocumentId"]
            ,"IsManual"=>1
        );
        $this->a_dati =  array(0=>array_merge($a_result,$a_addfields));
        return $this;
    }

    public function ControlloSeEsiste()
    {
        $log = new LOG();
        $query_notifiche_importate = "
            select * from notifiche_importate
            where DocumentId = $this->Atto_Id";
        $a_result = parent::SelectSQL($query_notifiche_importate);
        
        if(count($a_result)>0)
        {
            $cadRetro = $this->CAD_Retro ==null ? null : "'$this->CAD_Retro'";
            $query_update ="
                update notifiche_importate
                set CAD_Fronte = '$this->CAD_Fronte',
                CAD_Retro = $cadRetro,
                Data_Importazione = '$this->Data_Importazione',
                Operatore = '$this->Operatore'
                where DocumentId = $this->Atto_Id
            ";
            $log->info($query_update);
            parent::UpdateSQL($query_update);
            return true;
        };
        return false;
    }

    
    public function SalvaImmagini($path){
        if(!empty($this->cls_param) && !empty($this->cls_help))
        {   
            if (pathinfo($this->path_fronte, PATHINFO_EXTENSION)== "pdf")
            {
                $this->cls_param->SavePdf($this->path_fronte,$this->CAD_Fronte,$this->cc,$path);
                return $this;
            }

            if (file_exists($this->path_fronte))$this->cls_param->SaveImageNotifica($this->path_fronte,$this->CAD_Fronte,$this->cc,$path);
            if (file_exists($this->path_retro))$this->cls_param->SaveImageNotifica($this->path_retro,$this->CAD_Retro,$this->cc,$path);
        }
        return $this;
    }
}