<?php
include_once CLS ."/traits.php";

class Stragiudiziali{
    protected $cls_db;
    protected $proc_id;

    
    public $a_banche = array();
    public $a_previdenze = array();
    public  $a_utenti = array();

    use tSelectSQL;

    function  __construct($cls_db){
            $this->cls_db = $cls_db;
    }

    public function Set($variabile,$valore)
    {
        $this->{$variabile}= $valore;
        return $this;
    }

    private function PrendiBanche()
    {
        $query = "
        select 
        B.ID AS Banca_ID, 
        B.Denominazione AS Denominazione, 
        B.Toponimo AS Toponimo , 
        B.Civico AS Civico, 
        B.Cap AS Cap , 
        B.Provincia AS Provincia,  
        B.Comune 
        from stragiudiziali as STRA 
        join banca as B on STRA.Banca_ID = B.ID
        where Procedure_Id =  $this->proc_id;
        ";

        $this->a_banche = $this->SelectSQL($query);
        return $this;
    }

    private function PrendiPrevidenze()
    {
        $query = "
        select 
        E.ID as Previdenza_Id,
        E.Denominazione as Denominazione,
        E.Toponimo as Toponimo,
        E.Civico as Civico,
        E.Cap as Cap,
        E.Provincia as Provincia,
        E.Comune as Comune
        from enti_esterni E 
        join stragiudiziali S on E.ID = S.Previdenza_Id
        where E.Tipo = 'previdenza'
        and  Procedure_Id =  $this->proc_id;
        ";

        $this->a_previdenze = $this->SelectSQL($query);
        return $this;
    }

    private function PrendiUtenti()
    {
        $query = "SELECT
        ANA.Utente_ID,
        ANA.CC,
        ANA.Cognome_Ditta,
        ANA.Nome,
        ANA.CF_PI,
        ANA.Res_Comune,
        ANA.Res_Via,
        EG.Denominazione as Denominazione_Ente
        from v_anagrafe as ANA
        join partita_tributi as PT on PT.Utente_ID = ANA.Utente_ID
        join partita_procedure_pvt as PPP on PPP.Partita_ID = PT.ID
        join enti_gestiti as EG on EG.CC = PT.CC
        where PPP.Procedure_Id = $this->proc_id
        group by ANA.Utente_ID 
        ";

        $a_utenti = $this->SelectSQL($query);
        $this->Cicla($a_utenti,0,function($elem) use(&$a_ret){
            $a_ret[]=$elem;
        });
        $this->a_utenti = $a_ret;

    }

    private function AssegnaUtenti()
    {   //per ogni banca stessi utenti
        $this->PrendiUtenti();
    }
    protected function Cicla($array,$index,$callback)
    {
        if(count($array)>$index) {
            $callback($array[$index]);
            $this->Cicla($array,$index+1,$callback);
        }
    }
    public function Struttura()
    {
        
        $this->PrendiBanche();
        $this->PrendiPrevidenze();
        $this->AssegnaUtenti();
       
    }
    public function AggiungiResponsabileFirma($c,&$istituto)
    {
        $query = "
        SELECT  Funzionario_Responsabile
        FROM  parametri_responsabili as pr 
        join  partita_tributi as PT
        on  pr.CC = PT.CC AND pr.Tipo_Riscossione = PT.Tipo
        Where pr.CC='$c'
        group by pr.CC
        ";
        
        
        $istituto["Funzionario_Responsabile"] = $this->SelectSQL($query)[0]["Funzionario_Responsabile"];

    }
    public function PrendiAtti(&$utente)
    {
        $utente_id = $utente["Utente_ID"];
        $query = "
        SELECT *
        from v_stragiudiziali_banche_prendi_excel
        where Utente_ID = $utente_id;
        ";

        $a_atti = $this->SelectSQL($query);
        
        $fai_Tipo_atto = function($atto)
        {
            if(is_null($atto)) return "";
            return $atto["TIPO_ATTO"]." n.r.".$atto["ID_Cronologico"]."/".$atto["Anno_Cronologico"];
        };

        $componi_array = function($index) use ($a_atti,&$res_atti,&$res_Totale_Dovuti,&$componi_array,$fai_Tipo_atto)
        {
                if(count($a_atti)>$index){
                    $res_atti[]=$fai_Tipo_atto($a_atti[$index]);
                    $res_Totale_Dovuti[]=$a_atti[$index]["Totale_Dovuto_ATTO"];
                    $componi_array($index+1);
                }
        };
        $componi_array(0);
        $utente["TIPI_ATTO"] = $res_atti;
        $utente["TOTALE_DOVUTI"] = $res_Totale_Dovuti; 
        
    }
}

?>
       