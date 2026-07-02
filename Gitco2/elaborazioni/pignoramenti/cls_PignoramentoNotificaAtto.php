<?php
include_once CLS . "/cls_db.php";

class cls_PignoramentoNotificaAtto
{
    protected $cls_db;
    protected $param_CC;
    protected $param_elab_id;
    protected $columnNotificaAtto;
    protected $a_paramAnnuali;
    public $a_TipoUfficiale;

    protected function DebitorePEC ($o){
        if(!is_null($o->PEC) && ($o->PEC!=''))
        {
            $data = $o->InipecLoaded;
            if (is_null($data)) return false;
            $diff = 15; //15 giorni

            $now = time(); 
            $your_date = strtotime($data);
            $datediff = $now - $your_date;
            $days = round($datediff / (60 * 60 * 24))-1;
            if($days<=$diff) return true;

        }
        return false;
    }
    
    protected function TrovaSpeseNotifica($DocumentTypeId,$PrintType)
    {
        $end_key="";
        switch($DocumentTypeId)
        {
            case 22 : $end_key = "Cautelari";
                    break;
            default : $end_key = "Pignoramento";
        }

                //$key="Spese_Notifica_Cautelari";
        switch ($PrintType) {
            case 1:
            case 2:
                $key = "Spese_Notifica_".$end_key;
                break;
            /*case 2:
                $key = 'Spese_Raccomandata';
                break;*/
            case 3:
                $key = 'Spese_Postali';
                break;
            case 4:
                $key = 'Spese_Pec';
                break;
            case 6:
                $key = 'A_Mani_'.$end_key;
                break;    
        }
           

        return $this->a_paramAnnuali[$key];
    }
    protected function TipoUfficiale($o)
    {
        $result = "";
        if ($this->DebitorePEC($o))
        {
            
            $result =  $this->a_TipoUfficiale["DefaultPecTipoUfficiale"];
        }
        else
        {
            $result =  $this->a_TipoUfficiale["DefaultRaccomandataTipoUfficiale"];
        }
        return $result;
    }

    protected function TipoSpedizione($o)
    {
        $result = "";
        if ($this->DebitorePEC($o))
        {
            
            $result =  $this->a_TipoUfficiale["DefaultPecTipoStampa"];
        }
        else
        {
            $result =  $this->a_TipoUfficiale["DefaultRaccomandataTipoStampa"];
        }
        return $result;
    }
    protected function queryEstrazione()
    {
        $q =  "
        select 
        PG.CC,
        PG.DocumentTypeId,
        PG.ID as Atto_Notificato_ID,
        UT.ID as Utente_ID,
        UT.PEC,
        UT.InipecLoaded,
        IVG.PEC as PECIVG,
        EL.Elaboration_Status_Id,        
        PG.PrinterId
        from pignoramento_generale as PG 
        join partita_tributi as PT on PG.Partita_ID=PT.ID
        join utente as UT on PT.Utente_ID=UT.ID
        join elaborations as EL on EL.ID = PG.Elaboration_Id
        JOIN indirizzo as I ON PT.Utente_ID=I.Utente_ID
        left JOIN ufficio_giudiziario as TR ON I.CC_Indirizzo=TR.CC AND TR.Tipo = 'tribunale'
        left JOIN ufficio_giudiziario as IVG ON TR.CC_Ufficio=IVG.CC AND IVG.Tipo = 'istituto'
        Where EL.Id=".$this->param_elab_id." and PG.CC='".$this->param_CC."'
        group by Atto_Notificato_ID";
        
        return $q;
    }
   
    protected function SelectSQL($sql)
    {
        $ret= $this->cls_db->getResults(
            $this->cls_db->ExecuteQuery($sql)
        );
        return $ret;
    }
    
    private function GetColumn()
    {
        return $this->cls_db->getColumnDataTypes("notifica_atto");
    }
   

    protected function FaiRighe($riga)
    {
        $DebitorePEC = fn($o) =>$this->DebitorePEC($o);

        $objRiga = json_decode(json_encode ( $riga ));
        $debitore = new stdClass();
        $debitore->Utente_ID = $objRiga->Utente_ID;
        $debitore->CC = $objRiga->CC;
        $debitore->Atto_Notificato_ID = $objRiga->Atto_Notificato_ID;
        $debitore->Tipo_Atto_Notificato = "pignoramento";
        $debitore->Tipo_Notifica = "debitore";
        $debitore->PrintTypeId = $this->TipoSpedizione($objRiga);//!$DebitorePEC($objRiga) ? 1 : 4;
        $debitore->Modalita_Stampa = !$DebitorePEC($objRiga) ? "posta" : "pec";
        $debitore->Spedizione_PEC = !$DebitorePEC($objRiga) ? null: "si";
        $debitore->Spese_Notifica = !$DebitorePEC($objRiga) ? $this->TrovaSpeseNotifica($objRiga->DocumentTypeId,$this->TipoSpedizione($objRiga))
        : $this->a_paramAnnuali["Spese_Pec"] ;
        //$debitore->Tipo_Ufficiale = !$DebitorePEC($objRiga) ? "diretta":"riscossione";
        $debitore->Tipo_Ufficiale = $this->TipoUfficiale($objRiga);
        $debitore->Printer_Id = !$DebitorePEC($objRiga) ? 2:1;//$objRiga->PrinterId;
        $this->AggiornaPignoramentoGenerale($debitore->Spese_Notifica,$debitore->Atto_Notificato_ID);

        if ($objRiga->DocumentTypeId==6){
            $veicolo=clone $debitore;
            $veicolo->Tipo_Notifica = "veicolo";
            $veicolo->PrintTypeId = is_null($objRiga->PECIVG) ? 1 : 4;
            $veicolo->Modalita_Stampa = is_null($objRiga->PECIVG) ? "posta" : "pec";
            $veicolo->Spedizione_PEC = is_null($objRiga->PECIVG) ? null: "si";
            $veicolo->Spese_Notifica = is_null($objRiga->PECIVG) ? 0: $this->a_paramAnnuali["Spese_Pec"];
            //todo tipo ufficiale
            $array1 = json_decode(json_encode ( $debitore ) , true);
            $array2 = json_decode(json_encode ( $veicolo ) , true);

            return array($array1,$array2);
        }
        else
        {
            $array1 = json_decode(json_encode ( $debitore ) , true);
            return array($array1);
        }

    }
    function AggiornaPignoramentoGenerale($Spese_Notifica, $Atto_Notificato_Id)
    {
            $query = "Update pignoramento_generale as PG
                Set Spese_Notifica_Debitore = $Spese_Notifica,
                Totale_Spese_Notifica = Spese_Notifica_Terzi + Spese_Notifica_Debitore,
                Totale_Dovuto = Totale_Spese_Notifica + Importo_Dovuto + Totale_Spese_Accessorie
                where Id = $Atto_Notificato_Id
            ";
            $this->cls_db->ExecuteQuery($query);
    }

    function __construct(cls_db $value)
    {
        $this->cls_db = $value;
        $this->columnNotificaAtto = $this->GetColumn();
        $this->a_TipoUfficiale = array("DefaultPecTipoUfficiale"=>"riscossione","DefaultRaccomandataTipoUfficiale"=>"diretta","DefaultPecTipoStampa" => 6,"DefaultRaccomandataTipoStampa" => 2);
    } 

    private function Estrazione()
    {
       return $this->SelectSQL($this->queryEstrazione());
    }
    

    protected function Inserisci($a_datiestratti)
    {
        
        if (isset($a_datiestratti))
        {
            $faiRighe = fn($riga) => $this->FaiRighe($riga);
            $scriviRiga = fn($r) =>$this->cls_db->DbSave(
                $this->cls_db->GetObjectQuery("notifica_atto", $r, $this->columnNotificaAtto)
            );

            $arrayscrivi=function($r,$index) use ($scriviRiga,&$arrayscrivi){
                if($index<count($r))
                {
                    $scriviRiga($r[$index]);
                    $arrayscrivi($r,$index+1);
                };
            };
            array_map(
                function($t)use($arrayscrivi,$faiRighe){
                    $r = $faiRighe($t);
                    $arrayscrivi($r,0);
                }
                ,$a_datiestratti);
        };
    }

    public function __invoke($aparamAnnuali,$paramCC,$paramElabId)
    {
        $this->a_paramAnnuali = $aparamAnnuali;
        $this->param_CC = $paramCC;
        $this->param_elab_id = $paramElabId;
        $this->Inserisci($this->Estrazione());
    }
}

class cls_PignoramentoNotificatoTerzo extends cls_PignoramentoNotificaAtto
{

    protected function queryEstrazione()
    {
        $q =  "
        select 
        PG.CC,
        PG.DocumentTypeId,
        PG.ID as Atto_Notificato_ID,
        UT.ID as Utente_ID,
        UT.PEC,
        UT.InipecLoaded,
        EL.Elaboration_Status_Id,        
        PG.PrinterId
        from pignoramento_generale as PG 
        join partita_tributi as PT on PG.Partita_ID=PT.ID
        join utente as UT on PT.Utente_ID=UT.ID
        join elaborations as EL on EL.ID = PG.Elaboration_Id
        JOIN indirizzo as I ON PT.Utente_ID=I.Utente_ID
        Where EL.Id=".$this->param_elab_id." and PG.CC='".$this->param_CC."'
        group by Atto_Notificato_ID";
        return $q;
    }

    protected function queryEstrazioneTerzi($id)
    {
        return "
        select         
        UT.ID as Utente_ID,
        UT.PEC,
        UT.InipecLoaded
        from utente as UT 
        Where UT.ID=$id";
    }

    private function Estrazione()
    {
       return $this->SelectSQL($this->queryEstrazione());
    }

    public function __invoke($aparamAnnuali,$paramCC,$paramElabId)//
    {
        $this->a_paramAnnuali = $aparamAnnuali;
        $this->param_CC = $paramCC;
        $this->param_elab_id = $paramElabId;
        parent::Inserisci($this->Estrazione());
        $this->AggiornaIdCollegamentoTerzo();
    }
    protected function queryTerzi($id)
    {
        return "
        select Terzo_ID
        from terzo_pvt
        where Utente_ID = $id
        ";
    }

    protected function EstrazioneIDTerzi($id)
    {
        
       return parent::SelectSQL($this->queryTerzi($id));
    }
    protected function EstrazioneTerzo($id)
    {
       return parent::SelectSQL($this->queryEstrazioneTerzi($id))[0];
    }

    protected function FaiRighe($riga)//
    {
        
        $DebitorePEC = fn($o) => $this->DebitorePEC($o);
        $objRiga = json_decode(json_encode ( $riga ));
        $debitore = new stdClass();
        $debitore->Utente_ID = $objRiga->Utente_ID;
        $debitore->CC = $objRiga->CC;
        $debitore->Atto_Notificato_ID = $objRiga->Atto_Notificato_ID;
        $debitore->Tipo_Atto_Notificato = "pignoramento";
        $debitore->Tipo_Notifica = "debitore";
        $debitore->PrintTypeId = $this->TipoSpedizione($objRiga);
        $debitore->Modalita_Stampa = !$DebitorePEC($objRiga) ? "posta" : "pec";
        $debitore->Spedizione_PEC = !$DebitorePEC($objRiga) ? null: "si";
        $debitore->Spese_Notifica = !$DebitorePEC($objRiga) ? $this->TrovaSpeseNotifica($objRiga->DocumentTypeId,$this->TipoSpedizione($objRiga)) 
        : $this->a_paramAnnuali["Spese_Pec"] ;
        $debitore->Tipo_Ufficiale = $this->TipoUfficiale($objRiga);
        $debitore->Printer_Id = !$DebitorePEC($objRiga) ? 2:1;//$objRiga->PrinterId;
        //$debitore->Utente_ID = $objRiga->Utente_ID;
        $this->AggiornaPignoramentoGenerale($debitore->Spese_Notifica,$debitore->Atto_Notificato_ID);
        if (($objRiga->DocumentTypeId==7) || ($objRiga->DocumentTypeId==8)){
            $array1 = json_decode(json_encode ( $debitore ) , true);
            $array2 = $this->TrovaTerzi($debitore,$objRiga->Utente_ID);
            $ret_array = array_merge(array($array1),$array2);
            return $ret_array;
        }
        else
        {
            $array1 = json_decode(json_encode ( $debitore ) , true);
            return array($array1);
        }

    }

    protected function TrovaTerzi($debitore,$id)
    {
        
        $ret = array();
        $collectionID = $this->EstrazioneIDTerzi($id);
        $DebitorePEC = fn($o) =>$this->DebitorePEC($o);
        $TotaleSpeseNotificaTerzi = 0;
        foreach($collectionID as $terzo_id)
        {
            $riga = $this->EstrazioneTerzo($terzo_id["Terzo_ID"]);
            $objRiga = json_decode(json_encode ( $riga ));
            $terzo =clone $debitore;
            $terzo->Utente_ID = $terzo_id["Terzo_ID"];
            $terzo->Tipo_Notifica = "terzi";
            $terzo->PrintTypeId = !$DebitorePEC($objRiga) ? 1 : 4;
            $terzo->Modalita_Stampa = !$DebitorePEC($objRiga) ? "posta" : "pec";
            $terzo->Spedizione_PEC = !$DebitorePEC($objRiga) ? null: "si";
            $terzo->Spese_Notifica = !$DebitorePEC($objRiga) ? $this->a_paramAnnuali["Spese_Notifica_Pignoramento"] 
            : $this->a_paramAnnuali["Spese_Pec"] ;
            $terzo->Tipo_Ufficiale = $this->TipoUfficiale($objRiga);
            $terzo->Printer_Id = !$DebitorePEC($objRiga) ? 2:1;//$objRiga->PrinterId;
            
            //$terzo->Utente_ID = $objRiga->Utente_ID;
            $array1 = json_decode(json_encode ( $terzo ) , true);
            $ret[]=$array1;
            $TotaleSpeseNotificaTerzi+=$terzo->Spese_Notifica;
        }
        $this->AggiornaPignoramentoGeneraleTerzi($TotaleSpeseNotificaTerzi,$terzo->Atto_Notificato_ID);
        return $ret;
    }

    public function AggiornaPignoramentoGeneraleTerzi($Spese_Notifica, $Atto_Notificato_Id)
    {
            $query = "Update pignoramento_generale as PG
                Set Spese_Notifica_Terzi =  $Spese_Notifica,
                Totale_Spese_Notifica = Spese_Notifica_Debitore + Spese_Notifica_Terzi,
                Totale_Dovuto = Totale_Spese_Notifica + Importo_Dovuto + Totale_Spese_Accessorie
                where Id = $Atto_Notificato_Id
            ";
            $this->cls_db->ExecuteQuery($query);
    }

    public function AggiornaIdCollegamentoTerzo()
    {
        $query = "
            update notifica_atto NA 
            join pignoramento_presso_terzi PPT on 
            PPT.Terzo_ID = NA.Utente_ID and PPT.Pignoramento_ID = NA.Atto_Notificato_ID
            Set NA.ID_Collegamento = PPT.ID;";
        $this->cls_db->ExecuteQuery($query);    
            
    }
}

class cls_PignoramentoNotificatoBanca extends cls_PignoramentoNotificatoTerzo
{
    protected function queryTerzi($id)
    {
        
        $q =  "
        select Terzo_ID
        from banche_pvt
        where Utente_ID = $id
        ";
        
        return $q;
        
    }
    
    protected function TrovaTerzi($debitore,$id)
    {
        
        $ret = array();
        $collectionID = $this->EstrazioneIDTerzi($id);
        $TotaleSpeseNotificaTerzi = 0;
        $check = 0;
        foreach($collectionID as $terzo_id)
        {
            $terzo =clone $debitore;
            $terzo->Utente_ID = $terzo_id["Terzo_ID"];
            $terzo->Tipo_Notifica = "banca";
            $terzo->PrintTypeId =  4;
            $terzo->Modalita_Stampa =  "pec";
            $terzo->Spedizione_PEC = "si";
            if($check==1)
                $terzo->Spese_Notifica =  0;
            else{
                //? INSERIMENTO SPESE PEC BANCA SOLO SULLA PRIMA BANCA
                $terzo->Spese_Notifica =  $this->a_paramAnnuali["Spese_Pec_Banca"];
                $check = 1;
                $TotaleSpeseNotificaTerzi+= $this->a_paramAnnuali["Spese_Pec_Banca"];
            }

            $terzo->Tipo_Ufficiale = $this->a_TipoUfficiale["DefaultPecTipoUfficiale"];
            $terzo->Printer_Id = 1;//$objRiga->PrinterId;
            
            //$terzo->Utente_ID = $objRiga->Utente_ID;
            $array1 = json_decode(json_encode ( $terzo ) , true);
            $ret[]=$array1;
            
        }
        $this->AggiornaPignoramentoGeneraleTerzi($TotaleSpeseNotificaTerzi,$terzo->Atto_Notificato_ID);
        return $ret;
    }
}
?>