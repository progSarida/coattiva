<?php
include_once CLS . "/cls_db.php";

class cls_PignoramentoSpese
{
    private $cls_db;
    private $param_CC;
    private $document_type_id;
    private $pigno_id;
    private $importo_dovuto;
    private $columnPignoramentoSpese;
    private $a_coefficienti;
    private $incremento_percentuale;
    private $rimborso_pignoramento;

    private function queryEstrazione()
    {
        $q= 
        "Select *
         from tariffe_coazione
         where CC='".$this->param_CC."' AND JSON_CONTAINS(DefaultJSon, '".$this->document_type_id."', '$.DocumentList')
         AND JSON_EXTRACT(DefaultJSon, '$.Default') IS NOT NULL
         ";
         
         return $q;
         //where CC='".$this->param_CC."' AND (DefaultJSon, '{\"DocumentType\":".$this->document_type_id."}', '$.Default')";
    }
   
    private function SelectSQL($sql)
    {
        $ret= $this->cls_db->getResults(
            $this->cls_db->ExecuteQuery($sql)
        );
        return $ret;
    }
    private function CaricamentoCoefficienteTariffe()
    {
        return $this->SelectSQL("select * FROM coefficiente_coazione where CC='".$this->param_CC."'");
        //return $this->SelectSQL("select * FROM coefficiente_coazione where CC='*****'");
    }
    private function GetColumn()
    {
        return $this->cls_db->getColumnDataTypes("pignoramento_spese");
    }
   
    private function CalcoloIncrementoPercentuale($importo)
    {
        $confronto = fn($pr,$min,$max) => (($pr<=$max) && ($pr>=$min));
        $ret = array_filter($this->a_coefficienti,
        function($t) use ($importo, $confronto){
            return $confronto($importo,$t["Credito_Minimo"],$t["Credito_Massimo"]);
        });
        $ret = array_values(array_filter($ret));
        
        if (empty($ret) || is_null($ret)) return 0;
        return $ret[0]["Percentuale"];

    }
    function __construct(cls_db $value)
    {
        $this->cls_db = $value;
        $this->columnPignoramentoSpese = $this->GetColumn();
    } 

    private function Tariffe()
    {
       return $this->SelectSQL($this->queryEstrazione());
    }

    
    

    private function transform($a_tariffe,&$out,$index)
    {
        
        $calcola_rimborso = function ($r){
            //“Rimborso_x” => Importo della tariffa + (incremento percentuale*valore tariffa/100)
            return $r["Importo"] + ($this->incremento_percentuale*$r["Importo"]/100);
        };
        
        $cerca_totale = function ($arr,$tipo,$index) use(&$cerca_totale)
        {
            if($index<count($arr))
            {
                if(strval($arr[$index]["DocumentType"])==$tipo)
                {   
                    $tipo_totale = $arr[$index]["Tipototale"];
                    return $tipo_totale;
                }
                else
                    return $cerca_totale($arr,$tipo,$index+1);
            }
            
        };
        $calcola_tipo_totale=function($r) use (&$cerca_totale){
            $arr = json_decode($r["DefaultJSON"], true);
            if(!isset($arr["Default"])) return null;
            $arrDefault = $arr["Default"];
            return $cerca_totale($arrDefault,$this->document_type_id,0);
        };
        
        if ($index<=count($a_tariffe))
        {
            //! CORREZIONE CARICAMENTO TARIFFA SOLO SE HA UN TOTALE DI DEFAULT
            $tipo_totale = $calcola_tipo_totale($a_tariffe[$index-1]);
            if(!empty($tipo_totale)){
                $out["Spesa_".$index."_ID"] = $a_tariffe[$index-1]["ID"];
                $out["Tipo_Spesa_".$index] = $a_tariffe[$index-1]["Tipo"];
                $out["Extra_Spesa_".$index] = 0;
                $rimborso = $calcola_rimborso($a_tariffe[$index-1]);
                $out["Rimborso_".$index] = $rimborso;
                $out["Tipo_Totale_".$index] = $tipo_totale;
                $this->rimborso_pignoramento +=$rimborso;
            }
            
            $this->transform($a_tariffe,$out,$index+1);
        }
        
        return $out;
    }
    
    

    function prendi_totale_dovuto($pigno_id)
    {
        $query="Select Totale_Dovuto from pignoramento_generale Where ID=".$pigno_id;
        $a_results = $this->cls_db->getResults($this->cls_db->SelectQuery($query));
        
        return $a_results[0]["Totale_Dovuto"];
    }

    function aggiorna_pignoramento_generale($totale_rimborso,$totale_dovuto,$a_tot_spese_accessorie)
    {
        //aggiungere anche totale_spese_notifica
        $a_dbParams = array(
            'table' => 'pignoramento_generale',
            'updateField' => array(
                array('name' => 'Id',  'type' => 'int', 'value' => $this->pigno_id),
            ),
            'fields'=> array(
                array(  'name' => 'Totale_Spese_Accessorie',  'type' => 'flt', 'value' => $totale_rimborso),
                array(  'name' => 'Totale_Dovuto',        'type' => 'flt', 'value' => $totale_dovuto+$totale_rimborso),
                array(  'name' => 'Spese_Accessorie_1',   'type' => 'flt', 'value' => $a_tot_spese_accessorie[1]),
                array(  'name' => 'Spese_Accessorie_2',   'type' => 'flt', 'value' => $a_tot_spese_accessorie[2]),
                array(  'name' => 'Spese_Accessorie_3',   'type' => 'flt', 'value' => $a_tot_spese_accessorie[3]),
            )
        );

        $this->cls_db->DbSave( $a_dbParams);
    }

    private function Inserisci($a_tariffe)
    {
        $out=array();
        $this->rimborso_pignoramento =0;
        $out["CC"] = $this->param_CC;
        $out["Pignoramento_ID"] = $this->pigno_id;
        $out["Incremento_Percentuale"] = $this->incremento_percentuale;
        
        
        if (isset($a_tariffe))
        {
            $this->transform($a_tariffe,$out,1);
            
            $out["Totale_Rimborso"] = $this->rimborso_pignoramento;
            $this->cls_db->DbSave(
                $this->cls_db->GetObjectQuery("pignoramento_spese",
               $out, $this->columnPignoramentoSpese)
            );
            $totale_dovuto = $this->prendi_totale_dovuto($this->pigno_id);
            $totali_spese_accessorie = $this->getTotaliSpeseAccessorie($this->pigno_id);
            $this->aggiorna_pignoramento_generale($this->rimborso_pignoramento,$totale_dovuto, $totali_spese_accessorie);
        }
    }

    public function getTotaliSpeseAccessorie($pigno_id){
        $a_row = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery("SELECT * FROM pignoramento_spese WHERE Pignoramento_ID=".$pigno_id));

        $a_tot = array(1=>0,2=>0,3=>0);
        for($i=1;$i<=10;$i++){
            if(!empty($a_row['Tipo_Totale_'.$i]))
                $a_tot[$a_row['Tipo_Totale_'.$i]]+=$a_row['Rimborso_'.$i];
        }

        if($a_tot[2]>0)
            $a_tot[2]+=$a_tot[1];
        if($a_tot[3]>0)
            $a_tot[3]+=$a_tot[2];

        return $a_tot;
    }

    public function __invoke($paramCC,$pignoId,$documentTypeId,$importoDovuto)
    {
        $this->param_CC = $paramCC;
        $this->pigno_id = $pignoId;
        $this->document_type_id=$documentTypeId;
        $this->importo_dovuto = $importoDovuto;
        $this->a_coefficienti = $this->CaricamentoCoefficienteTariffe();
        $this->incremento_percentuale = $this->CalcoloIncrementoPercentuale($importoDovuto);
        $this->Inserisci($this->Tariffe());
    }

    public static function InserisciTariffeSeMancantiPerCC(cls_db $cls_db, $CC)
    {   
        self::Duplica($cls_db,$CC,"tariffe_coazione");

    }

    public static function InserisciCoefficientiSeMancantiPerCC(cls_db $cls_db, $CC)
    {   
        self::Duplica($cls_db,$CC,"coefficiente_coazione");

    }
    public static function Duplica(cls_db $cls_db, $CC,$table_name)
    {   
       
        
        $ret= $cls_db->getResults(
            $cls_db->ExecuteQuery("select * from $table_name Where CC='$CC'")
        );
        
        
        if (count($ret)>0) return;

        //prendo colonne
        $colonne = $cls_db->getColumnDataTypes($table_name);
        
        //carico righe
        $a_result=$cls_db->getResults(
            $cls_db->ExecuteQuery("select * from $table_name Where CC='*****'")
        );
        
        //funzione salva riga con comune assente
        $salva = function ($row) use ($cls_db,$colonne,$CC,$table_name){
            $row["ID"]=null;
            $row["CC"]=$CC;
            $cls_db->DbSave(
            $cls_db->GetObjectQuery($table_name,
           $row, $colonne)
            );
        };
        
        array_map(function ($r) use ($salva) {$salva($r);},$a_result);
        
    }
    
}

?>