<?php
include_once CLS."/cls_db.php";
include_once CLS."/cls_Stampe.php";
include_once CLS."/ConvertNumberToString.php";


class cls_ruolo{

    public $a_result;
    public $a_codiciTributo;
    public $a_annualTribute;
    public $a_amounts;
    public $a_docDetails;

    public $a_instalment;

    private $a_interessi_tributi;
    private $a_last_act_png = null;

    public $solict_values;

    private $cls_db;
    private $cls_stp;

    public function __construct()
    {
        $this->cls_db = new cls_db();
        $this->cls_stp = new cls_Stampe();
        $this->cls_convert_number = new ConvertNumberToString(".","",true,"/");
    }

    public function getPrevQueryItemID($id, $c, $a){
        if($id>0)
            $idPrevWhere = "ID < ".$id." AND ";
        else
            $idPrevWhere = " ";

        return "SELECT ID FROM partita_tributi WHERE ".$idPrevWhere." CC='".$c."' AND Anno_Riferimento='".$a."' AND Is_Discharged=0 ORDER BY ID DESC LIMIT 1";
    }

    public function getNextQueryItemID($id, $c, $a){
        if($id>0)
            $idPrevWhere = "ID > ".$id." AND ";
        else
            $idPrevWhere = " ";

        return "SELECT ID FROM partita_tributi WHERE ".$idPrevWhere." CC='".$c."' AND Anno_Riferimento='".$a."' AND Is_Discharged=0 ORDER BY ID ASC LIMIT 1";
    }

    public function getPartitaFromInfoCartella_query($cc, $infoCartella){
        $query = "SELECT Partita_ID FROM tributo WHERE CC = '".$cc."' ";
        $query.= "AND Info_Cartella = \"".$infoCartella."\" LIMIT 1";

        return $query;
    }

    public function getLastRuolo_query ($cc){
        $query = "SELECT * FROM ruolo WHERE CC = '".$cc."' ORDER BY Comune_ID DESC LIMIT 1";
        return $query;
    }

    public function getLastPartita_query ($cc){
        $query = "SELECT * FROM partita_tributi WHERE CC = '".$cc."' AND Is_Discharged=0 ORDER BY Comune_ID DESC LIMIT 1";
        return $query;
    }

    public function setResultArray (array $a_result){
        $this->a_result = $a_result;
    }

    public function splitCodiciTributo(){
        $this->a_codiciTributo = null;

        $a_codiciTributo['Codice'] = explode("*",$this->a_result['Codici_Tributo']);
        $a_codiciTributo['Importo'] = explode("*",$this->a_result['Importi_Codici_Tributo']);
        $a_codiciTributo['Testo'] = explode("*",$this->a_result['Testi_Codici']);
        $a_codiciTributo['Scorporo_ID'] = explode("*",$this->a_result['Codici_Scorporo']);
        $a_codiciTributo['Categoria'] = explode("*",$this->a_result['Categorie_Scorporo']);
        $a_codiciTributo['Anno'] = explode("*",$this->a_result['Anni_Tributo']);
        $a_codiciTributo['Descrizioni_Codice'] = explode("*",$this->a_result['Descrizioni_Codice']);
        $a_codiciTributo['Info_Cartella'] = $this->a_result['Info_Cartella'];
        $a_codiciTributo['Data_Decorrenza_Interessi'] = $this->a_result['Partita_Data_Decorrenza'];

        //var_dump($a_codiciTributo['Info_Cartelle']);die;
        for($i=0;$i<count($a_codiciTributo['Codice']);$i++){

            $data = explode("-",$a_codiciTributo['Data_Decorrenza_Interessi']);

            $this->a_codiciTributo[$i]['Codice'] = $a_codiciTributo['Codice'][$i];
            $this->a_codiciTributo[$i]['Importo'] = $a_codiciTributo['Importo'][$i];
            $this->a_codiciTributo[$i]['Testo'] = $a_codiciTributo['Testo'][$i];
            $this->a_codiciTributo[$i]['Scorporo_ID'] = $a_codiciTributo['Scorporo_ID'][$i];
            $this->a_codiciTributo[$i]['Anno'] = $a_codiciTributo['Anno'][$i];
            $this->a_codiciTributo[$i]['Descrizioni_Codice'] = $a_codiciTributo['Descrizioni_Codice'][$i];
            $this->a_codiciTributo[$i]['Info_Cartelle'] = $a_codiciTributo['Info_Cartella'];
            $this->a_codiciTributo[$i]['Date_Decorrenze_Interessi'] = $data[2]."/".$data[1]."/".$data[0];
            if(isset($a_codiciTributo['Categoria'][$i]))
                $this->a_codiciTributo[$i]['Categoria'] = $a_codiciTributo['Categoria'][$i];
            else
                $this->a_codiciTributo[$i]['Categoria'] = "";
        }
    }

    public function getTributeCodesForPrint(){
        $this->splitCodiciTributo();
        $this->a_annualTribute = array();
        foreach($this->a_codiciTributo as $index=>$a_code){
            $this->a_annualTribute[$a_code['Codice']]['Scorporo_ID'] = $a_code['Scorporo_ID'];
            $this->a_annualTribute[$a_code['Codice']]['Testo'] = $a_code['Testo'];
            $this->a_annualTribute[$a_code['Codice']]['Categoria'] = $a_code['Categoria'];
            $this->a_annualTribute[$a_code['Codice']]['Descrizioni_Codice'] = $a_code['Descrizioni_Codice'];
            $this->a_annualTribute[$a_code['Codice']]['Info_Cartelle'] = $a_code['Info_Cartelle'];
            $this->a_annualTribute[$a_code['Codice']]['Date_Decorrenze_Interessi'] = $a_code['Date_Decorrenze_Interessi'];
            $this->a_annualTribute[$a_code['Codice']]['Anno'] = $a_code['Anno'];
            if(isset($this->a_annualTribute[$a_code['Codice']]['Anni'][$a_code['Anno']]))
                $this->a_annualTribute[$a_code['Codice']]['Anni'][$a_code['Anno']]+= $a_code['Importo'];
            else
                $this->a_annualTribute[$a_code['Codice']]['Anni'][$a_code['Anno']] = (float)$a_code['Importo'];
            if(isset($this->a_annualTribute[$a_code['Codice']]['Totale']))
                $this->a_annualTribute[$a_code['Codice']]['Totale']+= $a_code['Importo'];
            else
                $this->a_annualTribute[$a_code['Codice']]['Totale'] = $a_code['Importo'];
        }

        foreach($this->a_annualTribute as $codice=>$a_codice){
            foreach($a_codice as $key=>$value){
                if($key=="Anni"){
                    $this->a_annualTribute[$codice]['Testo'].= " [";
                    $cont = 0;
                    foreach ($value as $anno=>$importo){
                        if($cont>0)
                            $this->a_annualTribute[$codice]['Testo'].= " -";
                        $this->a_annualTribute[$codice]['Testo'].= " ".$anno." ( ".number_format($importo,2,",","")." &euro; )";
                        $cont++;
                    }

                    $this->a_annualTribute[$codice]['Testo'].= " ]";
                }
            }
        }

//        foreach($this->a_annualTribute as $codiceKey=>$a_codice){
//            echo "<br>".$codiceKey.":<br>";
//            foreach($a_codice as $key=>$codice){
//                echo $key." - ";
//                if(is_array($codice))
//                    var_dump($codice);
//                else
//                    echo $codice;
//                echo "<br>";
//            }
//        }
    }

    public function getPreviousActs(){
        if(!is_null($this->a_result)){
            $query = "SELECT * FROM atto WHERE Partita_ID = ".$this->a_result['Partita_ID']." AND ID<".$this->a_result['Atto_ID']." AND Data_Stampa is not null ORDER BY Data_Elaborazione DESC";
            $a_acts = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

            //var_dump($a_acts);die;

            $str_acts = "";
            foreach ($a_acts as $a_act){
                $str_act = $a_act['Atto']." N. ".$a_act['ID_Cronologico']." DEL ".$a_act['Anno_Cronologico'];
                if($a_act['DocumentTypeId']==2 || $a_act['DocumentTypeId']==4 || $a_act['DocumentTypeId']==12){
                    if(is_null($a_act['Data_Notifica']))
                        continue;
                    else{
                        $a_date = explode("-",$a_act['Data_Notifica']);
                        if(count($a_date)==3)
                            $date = $a_date[2]."/".$a_date[1]."/".$a_date[0];
                        if($a_act['DocumentTypeId']==2)
                            $str_act.= " NOTIFICATA IL ".$date;
                        else
                            $str_act.= " NOTIFICATO IL ".$date;
                    }
                }
                else if($a_act['DocumentTypeId']==32 || $a_act['DocumentTypeId']==11){
                    $a_date = explode("-",$a_act['Data_Stampa']);
                    if(count($a_date)==3)
                        $date = $a_date[2]."/".$a_date[1]."/".$a_date[0];

                    $str_act.= " SPEDITO IL ".$date;
                }
                else
                    continue;

                if($str_acts!="")
                    $str_acts.= ", ";
                $str_acts.= $str_act;
            }
            return $str_acts;
        }
        else
            return "";
    }

    public function getInjunctionTotal(){
        $codiciPayment = 0.00;
        $total = 0;

        $a_oneri['perc_min'] = 3;
        $a_oneri['perc_max'] = 6;
        $a_oneri['days_limit'] = 60;

        //var_dump($this->a_result);die;

        foreach ($this->a_annualTribute as $codice=>$a_codice){
            if($a_codice['Scorporo_ID']==0){
                $codiciPayment+= $a_codice['Totale'];
            }
            else{
                if($a_codice['Totale']>0.00){
                    $total += $a_codice['Totale'];
                }

            }
        }
        
        $tot_pagamenti = $this->a_result['Totale_Pagato']+$codiciPayment;

        //var_dump($this->a_result['Totale_Dovuto']-$this->a_result['Totale_Pagato']+$codiciPayment);die;
        // return number_format($this->a_result['Totale_Dovuto']-$this->a_result['Totale_Pagato']-$codiciPayment,2,",",".");
        return ($this->a_result['Totale_Dovuto']-$this->a_result['Totale_Pagato']-$codiciPayment);

        /*if($this->a_result['Spese_Notifica_Precedenti']>0){
            $total += $this->a_result['Spese_Notifica_Precedenti'];
        }
        if($this->a_result['Interessi']>0 || $this->a_result['Interessi_Precedenti']>0){
            $total += $this->a_result['Interessi']+$this->a_result['Interessi_Precedenti'];
        }
        if($this->a_result['Spese_Notifica']>0){
            $total += $this->a_result['Spese_Notifica'];
        }
        if($tot_pagamenti>0){
            $total -= $tot_pagamenti;
        }

        if($this->a_result['Diritto_Riscossione_Minimo']>0){

            $total = $total+$this->a_result['Diritto_Riscossione_Minimo'];
        }

        return number_format($total,2,",",".");*/

    }

    public function setTributeInterest(array $interessi){
        $this->a_interessi_tributi = $interessi;
    }

    public function setPrintAmounts($docType, $a_yearParams = null, $tipo = "atto"){
        $this->getTributeCodesForPrint();
        //var_dump($docType);
        //echo "<h1>second --> ".$a_yearParams."</h1>";

        if(!is_null($this->a_result) && $this->a_result['Tipo_Riscossione']=="CDS"){
            $str_interessi = "Maggiorazione del 10% semestrale";
            if(!is_null($this->a_result['Partita_Data_Decorrenza'])){
                $a_date = explode("-",$this->a_result['Partita_Data_Decorrenza']);
                if(count($a_date)==3)
                    $str_interessi.= " calcolata dal ".$a_date[2]."/".$a_date[1]."/".$a_date[0];
            }
        }
        else
            $str_interessi = "Interessi";

        $countAmounts = 0;
        //$countAmountsTrib = 0;
        $codiciPayment = 0.00;
        $total = 0;
        $this->a_amounts = null;

        if($tipo == "atto")
        {
            foreach ($this->a_annualTribute as $codice=>$a_codice){
                if($a_codice['Scorporo_ID']==0){
                    $codiciPayment+= $a_codice['Totale'];
                }
                else{
                    if($a_codice['Totale']>0.00){
                        $total += $a_codice['Totale'];

                        $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                        $this->a_amounts['single'][$countAmounts]['label'] = $a_codice['Testo'];
                        $this->a_amounts['single'][$countAmounts]['amount'] = number_format($a_codice['Totale'],2,",",".");

                        $countAmountsTrib++;
                    }

                }
            }
        }

        if($tipo == "atto")
            $tot_pagamenti = $this->a_result['Totale_Pagato']+$codiciPayment;

        if($a_yearParams==null){
            $a_oneri['perc_min'] = 3;
            $a_oneri['perc_max'] = 6;
            $a_oneri['days_limit'] = 60;
        }
        else{
            $a_oneri['perc_min'] = $a_yearParams['Diritto_Riscossione_Minimo'];
            $a_oneri['perc_max'] = $a_yearParams['Diritto_Riscossione_Massimo'];
            $a_oneri['days_limit'] = $a_yearParams['Giorni_Diritto'];
        }

        //echo "<h1>Tipo = ".$docType."</h1>";


        switch($docType){
            case "Sollecito pre ingiunzione":
                if($this->a_result['Spese_Notifica_Precedenti']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Spese postali/notifica/ricerca dei precedenti atti di accertamento";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Spese_Notifica_Precedenti'],2,",",".");
                    $total += $this->a_result['Spese_Notifica_Precedenti'];
                    $countAmounts++;
                }
                if($this->a_result['Spese_Notifica']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Spese postali/notifica del presente Sollecito di pagamento";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Spese_Notifica'],2,",",".");
                    $total += $this->a_result['Spese_Notifica'];
                    $countAmounts++;
                }
                if($tot_pagamenti>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "-";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Pagamenti dei precedenti atti di accertamento";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($tot_pagamenti,2,",",".");
                    $total -= $tot_pagamenti;
                }

                $this->a_amounts['total'][0]['operator'] = "=";
                $this->a_amounts['total'][0]['label'] = "Differenza da versare";
                $this->a_amounts['total'][0]['amount'] = number_format($total,2,",",".");
                break;

            case "Avviso di messa in mora":
                if($this->a_result['Spese_Notifica_Precedenti']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Spese postali/notifica/ricerca dei precedenti atti di accertamento";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Spese_Notifica_Precedenti'],2,",",".");
                    $total += $this->a_result['Spese_Notifica_Precedenti'];
                    $countAmounts++;
                }
                if($this->a_result['Interessi']>0 || $this->a_result['Interessi_Precedenti']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Interessi";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Interessi']+$this->a_result['Interessi_Precedenti'],2,",",".");
                    $total += $this->a_result['Spese_Notifica'];
                    $countAmounts++;
                }
                if($this->a_result['Spese_Notifica']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Spese postali/notifica del presente Avviso di messa in mora";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Spese_Notifica'],2,",",".");
                    $total += $this->a_result['Spese_Notifica'];
                    $countAmounts++;
                }
                if($tot_pagamenti>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "-";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Pagamenti dei precedenti atti di accertamento";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($tot_pagamenti,2,",",".");
                    $total -= $tot_pagamenti;
                }

                $this->a_amounts['total'][0]['operator'] = "=";
                $this->a_amounts['total'][0]['label'] = "Totale dovuto";
                $this->a_amounts['total'][0]['amount'] = number_format($total,2,",",".");
                break;

            case "Ingiunzione":
                if($this->a_result['Spese_Notifica_Precedenti']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Spese postali/notifica/ricerca dei precedenti atti di accertamento";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Spese_Notifica_Precedenti'],2,",",".");
                    $total += $this->a_result['Spese_Notifica_Precedenti'];
                    $countAmounts++;
                }
                if($this->a_result['Interessi']>0 || $this->a_result['Interessi_Precedenti']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = $str_interessi;
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Interessi']+$this->a_result['Interessi_Precedenti'],2,",",".");
                    $total += $this->a_result['Interessi']+$this->a_result['Interessi_Precedenti'];
                    $countAmounts++;
                }
                if($this->a_result['Spese_Notifica']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Spese postali/notifica della presente Ingiunzione";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Spese_Notifica'],2,",",".");
                    $total += $this->a_result['Spese_Notifica'];
                    $countAmounts++;
                }
                if($tot_pagamenti>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "-";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Pagamenti dei precedenti atti di accertamento";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($tot_pagamenti,2,",",".");
                    $total -= $tot_pagamenti;
                }

                if($this->a_result['Diritto_Riscossione_Minimo']>0){
                    $this->a_amounts['total'][0]['operator'] = "=";
                    $this->a_amounts['total'][0]['label'] = "TOTALE COMPLESSIVO (1) [Oneri di Riscossione al ".$a_oneri['perc_min']."% pagamento entro ".$a_oneri['days_limit']." giorni]";
                    $this->a_amounts['total'][0]['amount'] = number_format($total+$this->a_result['Diritto_Riscossione_Minimo'],2,",",".");

                    $this->a_amounts['total'][1]['operator'] = "=";
                    $this->a_amounts['total'][1]['label'] = "TOTALE COMPLESSIVO (2) [Oneri di Riscossione al ".$a_oneri['perc_max']."% pagamento oltre ".$a_oneri['days_limit']." giorni]";
                    $this->a_amounts['total'][1]['amount'] = number_format($total+$this->a_result['Diritto_Riscossione_Massimo'],2,",",".");
                }
                else{
                    $this->a_amounts['total'][0]['operator'] = "=";
                    $this->a_amounts['total'][0]['label'] = "Totale dovuto";
                    $this->a_amounts['total'][0]['amount'] = number_format($total,2,",",".");
                }

                break;

            case "banca":
            case "lavoro":
            case "preav_fermo":
            case "veicolo":

                $countAmounts = 0;
                $query = "SELECT * FROM pignoramento_spese WHERE Pignoramento_ID = ".$this->a_result['ID']." AND CC = '".$this->a_result['CC']."'";
                $spese = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"pignoramento_spese");

                $query = "SELECT * FROM atto WHERE ID = ".$this->a_result['Atto_ID']." AND CC = '".$this->a_result['CC']."'";
                $atto_pignoramento = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"atto");
                $pagamenti_atto = number_format($this->cls_stp->totale_pagamenti($atto_pignoramento),2,",","");

                $spese_array = $this->cls_stp->spese_array($spese);
                $TOTALI_ARRAY = $this->cls_stp->totali_spese($spese);

                $this->a_amounts['single'][0][$countAmounts]['operator'] = "";
                $this->a_amounts['single'][0][$countAmounts]['label'] = "Ripresa debito precedente";
                $this->a_amounts['single'][0][$countAmounts]['amount'] = number_format($this->a_result['Importo_Dovuto'],2,",","");
                $countAmounts++;


                $this->a_amounts['single'][0][$countAmounts]['operator'] = "-";
                $this->a_amounts['single'][0][$countAmounts]['label'] = "Eventuale importo pagato successivamente alla notifica degli atti ingiuntivi e intimativi";
                $this->a_amounts['single'][0][$countAmounts]['amount'] = $pagamenti_atto;

                $Debito_Precedente = (float) $this->a_result["Importo_Dovuto"] - (float) $pagamenti_atto;
                $this->a_amounts['total'][0][0]['operator'] = "=";
                $this->a_amounts['total'][0][0]['label'] = "Totale debito precedente";
                $this->a_amounts['total'][0][0]['amount'] = number_format($Debito_Precedente,2,",",".");
                //($this->a_amounts['single']);

                $countAmounts = 0;
                for($x_spesa=1;$x_spesa<count($spese_array)+1;$x_spesa++)
                {
                    if($spese_array[$x_spesa]['tipo_totale']==1)
                    {
                        $query_tariffa = "SELECT Descrizione FROM tariffe_coazione WHERE ID = '".$spese_array[$x_spesa]['ID']."'";
                        $descrizione_tariffa = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query_tariffa),"tariffe_coazione")["Descrizione"];

                        $this->a_amounts['single'][1][$countAmounts]['operator'] = "+";
                        $this->a_amounts['single'][1][$countAmounts]['label'] = $descrizione_tariffa;
                        $this->a_amounts['single'][1][$countAmounts]['amount'] = number_format($spese_array[$x_spesa]['rimborso'],2,",",".");

                        $countAmounts++;
                    }
                }

                $this->a_amounts['single'][1][$countAmounts]['operator'] = "+";
                $this->a_amounts['single'][1][$countAmounts]['label'] = "Spese postali/diritti di notifica";
                //$this->a_amounts['single'][1][$countAmounts]['amount'] = number_format($this->a_result["Spese_Notifica"],2,",",".");//modificato i
                $this->a_amounts['single'][1][$countAmounts]['amount'] = number_format($this->a_result["Totale_Spese_Notifica"],2,",",".");

                $partial_1 = $this->a_result["Totale_Spese_Notifica"] + $Debito_Precedente;
                $this->a_amounts['total'][1][0]['operator'] = "=";
                $this->a_amounts['total'][1][0]['label'] = "TOTALE 1";
                $this->a_amounts['total'][1][0]['amount'] = number_format($TOTALI_ARRAY["spese_accessorie"][1] + $partial_1,2,",",".");

                $countAmounts = 0;
                if($TOTALI_ARRAY[2]!=0)
                {
                    for($x_spesa=1;$x_spesa<count($spese_array)+1;$x_spesa++)
                    {
                        if($spese_array[$x_spesa]['tipo_totale']==2)
                        {
                            $query_tariffa = "SELECT Descrizione FROM tariffe_coazione WHERE ID = '".$spese_array[$x_spesa]['ID']."'";
                            $descrizione_tariffa = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query_tariffa),"tariffe_coazione")["Descrizione"];

                            $this->a_amounts['single'][2][$countAmounts]['operator'] = "+";
                            $this->a_amounts['single'][2][$countAmounts]['label'] = $descrizione_tariffa;
                            $this->a_amounts['single'][2][$countAmounts]['amount'] = number_format($spese_array[$x_spesa]['rimborso'],2,",",".");

                            $countAmounts++;
                        }
                    }

                    $this->a_amounts['total'][2][0]['operator'] = "=";
                    $this->a_amounts['total'][2][0]['label'] = "TOTALE 2";
                    $this->a_amounts['total'][2][0]['amount'] = number_format($TOTALI_ARRAY["spese_accessorie"][2] + $partial_1,2,",",".");
                }

                $countAmounts = 0;
                if($TOTALI_ARRAY[3]!=0)
                {
                    for($x_spesa=1;$x_spesa<count($spese_array)+1;$x_spesa++)
                    {
                        if($spese_array[$x_spesa]['tipo_totale']==3)
                        {
                            $query_tariffa = "SELECT Descrizione FROM tariffe_coazione WHERE ID = '".$spese_array[$x_spesa]['ID']."'";
                            $descrizione_tariffa = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query_tariffa),"tariffe_coazione")["Descrizione"];

                            $this->a_amounts['single'][3][$countAmounts]['operator'] = "+";
                            $this->a_amounts['single'][3][$countAmounts]['label'] = $descrizione_tariffa;
                            $this->a_amounts['single'][3][$countAmounts]['amount'] = number_format($spese_array[$x_spesa]['rimborso'],2,",",".");

                            $countAmounts++;
                        }
                    }

                    $this->a_amounts['total'][3][0]['operator'] = "=";
                    $this->a_amounts['total'][3][0]['label'] = "TOTALE 3";
                    $this->a_amounts['total'][3][0]['amount'] = number_format($TOTALI_ARRAY["spese_accessorie"][3] + $partial_1,2,",",".");
                }

                break;

            default:
                $this->a_amounts['total'] = array();
                $this->a_amounts['single'] = array();

                break;
        }
    }

    public function getHtmlAmountsComplyLAB(){                                                                                                          // avviso di intimazione ad adempiere
        if(!is_null($this->a_result)){

            $query = "SELECT * FROM atto WHERE Partita_ID = ".$this->a_result['Partita_ID']." AND Data_Stampa = (SELECT MAX(Data_Stampa) FROM atto WHERE Partita_ID = ".$this->a_result['Partita_ID']." AND ID < ".$this->a_result['Atto_ID'].")";
            $a_act = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query))[0];                                                                 // recupero ultimo atto emesso

            $query_ente = "SELECT * FROM enti_gestiti WHERE CC = '".$a_act['CC']."'";                                                                   // recupero dati ente per tabella riassuntiva
            if(strpos($a_act['CC'],'U') === false)
                $nome_ente = "Comune di ".$this->cls_db->getResults($this->cls_db->ExecuteQuery($query_ente))[0]['Denominazione'];
            else
                $nome_ente = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query_ente))[0]['Denominazione'];

            $tot_pag = floatval(str_replace(",",".",'0.00'));                                                                                           // recupero pagamenti partita
            $query_pag = "SELECT *, SUM(Importo) AS tot_pag FROM pagamento WHERE Partita_ID = ".$this->a_result['Partita_ID']." ";   
            $pag = floatval(str_replace(",",".",$this->cls_db->getResults($this->cls_db->ExecuteQuery($query_pag))[0]['tot_pag']));    
            if($pag != null)    
                $tot_pag = $pag;   

            $query_pag = "SELECT Tipo FROM partita_tributi WHERE ID = ".$this->a_result['Partita_ID']." ";                                              // recupero tipo entrata
            $tipo = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query_pag))[0]['Tipo'];
            $tipo_entrata = $this->getTaxType($tipo);                                                                                                   

            $query_ann_params = "SELECT * FROM `parametri_annuali` where CC = '".$a_act['CC']."' and Anno = ".date("Y")."";                             // recupero parametri annuali ente
            $a_ann_params = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query_ann_params))[0];

            $totale_codici = floatval(str_replace(",",".",$a_act['Totale_Dovuto'])) - floatval(str_replace(",",".",$a_act['Spese_Notifica_Precedenti'])) - floatval(str_replace(",",".",$a_act['Spese_Notifica'])) - floatval(str_replace(",",".",$a_act['Interessi_Precedenti'])) - floatval(str_replace(",",".",$a_act['Interessi']));
            $residuo = $totale_codici - $tot_pag;
            $residuo = floatval($residuo);

            // $interessi = 0;
            // $interessi_prec = 0;

            $interessi = floatval(str_replace(",",".",$a_act['Interessi']));
            $interessi_prec = floatval(str_replace(",",".",$a_act['Interessi_Precedenti']));

            // $aggio = floatval(str_replace(",",".",$a_act['Diritto_Riscossione_Massimo']));
            $aggio = ($residuo + $interessi + $interessi_prec) * 0.06;
            $spese_post = floatval(str_replace(",",".",$a_act['Spese_Notifica']));
            $spese_post_prec = floatval(str_replace(",",".",$a_act['Spese_Notifica_Precedenti']));
        
            $totale = $interessi + $interessi_prec + $aggio + $spese_post + $spese_post_prec + $totale_codici - $tot_pag;                               // totale atto precedente

            $this->solict_values['spese_sped'] = number_format(floatval(str_replace(",",".",$a_ann_params['Spese_Raccomandata'])), 2, ',', '.');        // valori da inserire nelle variabili del teso dinamico
            // var_dump($this->solict_values['spese_sped']);var_dump($totale);die;
            $this->solict_values['tot'] = $totale + floatval(str_replace(",",".",$a_ann_params['Spese_Raccomandata']));

            $amountsTable = "";
            
            //floatval(str_replace(",",".",$temp1));

            $amountsTable .= "<div><table style=\"width:100%;border:1px solid black;\" >";                                                              // riquadro riepilogo

            $amountsTable .= "<tbody>
                                <tr>
                                    <td style=\"width: 10%;text-align:center;\"><b>Ente</b></td>
                                    <td style=\"width: 10%;text-align:center;\">".$a_act['CC']."</td>
                                    <td style=\"width: 80%;text-align:left;\">".$nome_ente."</td>            
                                </tr>";

            $amountsTable .= "  <tr>
                                    <td style=\"width: 17%;text-align:center;\"><b>Numero</b></td>
                                    <td style=\"width: 17%;text-align:center;\"><b>Notifica</b></td>
                                    <td style=\"width: 17%;text-align:center;\"><b>Anno</b></td> 
                                    <td style=\"width: 49%;text-align:center;\"><b>Descrizione</b></td>
                                </tr>";

            // $data = explode('-',$a_act['Data_Notifica']);

            $amountsTable .= "  <tr>
                                    <td style=\"width: 17%;font-size: 85%;text-align:center;\">".$a_act['ID_Cronologico']."/".$a_act['Anno_Cronologico']." DEL ".date_format(date_create($a_act['Data_Elaborazione']),"d/m/Y")."</td>
                                    <td style=\"width: 17%;text-align:center;\">".date_format(date_create($a_act['Data_Notifica']),"d/m/Y")."</td>
                                    <td style=\"width: 17%;text-align:center;\">".$a_act['Anno_Cronologico']."</td> 
                                    <td style=\"width: 49%;text-align:center;\">".$tipo_entrata."</td>
                                </tr>";

            if($tipo == 'CDS')  
                $amountsTable .= "  <tr>
                                        <td style=\"width: 15%;text-align:center;\"><b>Importo residuo</b></td>
                                        <td style=\"width: 23%;text-align:center;\"><b>Maggiorazione 10%</b></td>
                                        <td style=\"width: 12%;text-align:center;\"><b>Spese</b></td> 
                                        <td style=\"width: 15%;text-align:center;\"><b>Aggio</b></td>
                                        <td style=\"width: 20%;text-align:center;\"><b>Diritti di notifica</b></td> 
                                        <td style=\"width: 15%;text-align:center;\"><b>Totale</b></td>
                                    </tr>";
            else
                $amountsTable .= "  <tr>
                                        <td style=\"width: 15%;text-align:center;\"><b>Importo residuo</b></td>
                                        <td style=\"width: 23%;text-align:center;\"><b>Interessi</b></td>
                                        <td style=\"width: 12%;text-align:center;\"><b>Spese</b></td> 
                                        <td style=\"width: 15%;text-align:center;\"><b>Aggio</b></td>
                                        <td style=\"width: 20%;text-align:center;\"><b>Diritti di notifica</b></td> 
                                        <td style=\"width: 15%;text-align:center;\"><b>Totale</b></td>
                                    </tr>";

            $amountsTable .= "  <tr>
                                    <td style=\"width: 15%;text-align:center;\">".number_format($residuo,2,",",".")."</td>
                                    <td style=\"width: 23%;text-align:center;\">".number_format($interessi + $interessi_prec,2,",",".")."</td>
                                    <td style=\"width: 12%;text-align:center;\">0,00</td> 
                                    <td style=\"width: 15%;text-align:center;\">".number_format($aggio,2,",",".")."</td>
                                    <td style=\"width: 20%;text-align:center;\">".number_format($spese_post + $spese_post_prec,2,",",".")."</td> 
                                    <td style=\"width: 15%;text-align:center;\">".number_format($totale,2,",",".")."</td>
                                </tr>";

            $amountsTable .= "</tbody>
                        </table>
                    </div>";

            $amountsTable .= "<div><table style=\"width:100%;\" cellpadding=\"5\">";                                                            // tabella dettagli

            $amountsTable .= "  <thead>
                                    <tr>
                                        <th style=\"border: 1px solid black;width: 15%;text-align:center;\"><b>Anno Rif.</b></th>
                                        <th style=\"border: 1px solid black;width: 15%;text-align:center;\"><b>Cod. tributo</b></th>
                                        <th style=\"border: 1px solid black;width: 58%;text-align:center;\"><b>Descrizione</b></th>            
                                        <th style=\"border: 1px solid black;width: 12%;text-align:center;\"><b>Importo</b></th>
                                    </tr>
                                </thead>
                                <tbody>";
            
            for($i=0;$i<count($this->a_amounts['single_trib']);$i++){
                $amountsTable.= "   <tr>
                                        <td style=\"text-align:center;width: 15%;border: 1px solid black;\">".$this->a_amounts['single_trib'][$i]['anno']."</td>    
                                        <td style=\"text-align:center;width: 15%;border: 1px solid black;\">".explode(" - ",$this->a_amounts['single_trib'][$i]['cod_sanz'])[0]."</td>
                                        <td style=\"text-align:left;width: 58%;border: 1px solid black;\">".$this->a_amounts['single_trib'][$i]['info']."</td>
                                        <td style=\"text-align:right;width: 12%;border: 1px solid black;\">".$this->a_amounts['single_trib'][$i]['amount']." €</td>
                                    </tr>";
            }

            if($pag != null){
                $amountsTable .= "  <tr>
                                        <td style=\"text-align:center;width: 6%;\"></td>
                                        <td style=\"text-align:left;width: 10%;\"></td>
                                        <td style=\"text-align:center;width: 10%;\"></td>
                                        <td style=\"width: 62%;text-align:right;\">Pagato</td>            
                                        <td style=\"border: 1px solid black;width: 12%;text-align:right;\">".$tot_pag." €</td>
                                    </tr>";

                $amountsTable .= "  <tr>
                                        <td style=\"text-align:center;width: 6%;\"></td>
                                        <td style=\"text-align:left;width: 10%;\"></td>
                                        <td style=\"text-align:center;width: 10%;\"></td>
                                        <td style=\"width: 62%;text-align:right;\">Residuo</td>            
                                        <td style=\"border: 1px solid black;width: 12%;text-align:right;\">".$residuo." €</td>
                                    </tr>";
            }

            
            $amountsTable .= "  </tbody>
                            </table>
                        </div>";
            

            $amountsTable .= "</tbody>
                        </table>
                    </div>";

            return $amountsTable;
        }
        else
            return "";
    }

    public function getDeterminationData(){
        switch($this->a_result['CC']){
            case 'A318':                                // Anversa degli Abruzzi
                return "n. 14 del 13/09/2018";
            case 'C559':                                // Cervo
                return "n. 25 del 30/03/2021";
            case 'B559':                                // Camporosso
                return "n. 58 del 18/08/2017";
            case 'C261':                                // Castel San Giovanni
                return "n. 913 del 21/12/2018";
            case 'B256':                                // Bugnara
                return "n. ____ del ___/___/______";
            case 'D054':                                // Corte Brugnatella
                return "n. 24 del 24/06/2015";
            case 'G418':                                // Peia
                return "n. 184 del 10/11/2021";
            case 'G968':                                // Pradalunga
                return "n. 36 del 25/10/2019";
            case 'L975':                                // Villanova d'Albenga
                return "n. 39 del 18/12/20";
            case 'I986':                                // Strozza
                return "n. 102 del 12/08/2022";
            case 'M184':                                // Zogno
                return "n. 2 del 02/03/2023";
            case 'G195':                                // Ottone
                return "n. 1 del 13/08/2021";
            case 'F813':                                // Murialdo
                return "n. 13 del 06/06/2023";
            case 'C958':                                // Confienza
                return "n. 173 del 26/08/2021";
            case 'E519':                                // Leivi
                return "n. 36 del 30/11/2021";
            case 'C410':                                // Cazzano Sant'Andrea
                return "n. 142 del 13/11/2020";
            case 'F926':                                // Noli
                return "n. 307 del 19/05/2017";
            case 'H843':                                // San Fior
                return "n. 588 del 22/12/2021";
            case 'I640':                                // Serra Riccò
                return "n. ____ del ___/___/______";
            case 'A175':                                // Albuzzano
                return "n. 340 del 27/11/2021";
            case 'A171':                                // Albonese
                return "n. 11 del 26/10/2021";
            case 'I815':                                // Somaglia
                return "n. 54 del 07/10/2020";
            case 'B282':                                // Busalla
                return "n. 62 del 06/12/2021";
            default:
                return "n. ____ del ___/___/______";
        }
    }

    public function officeTimes(){
        switch($this->a_result['CC']){
            case 'C559':                                // Cervo (confermato mail xx/4/24 00:00)
                return '<tr>
                            <td style="width: 33%; text-align: center;"><span style="font-size:8px;">marted&igrave;</span></td>
                            <td style="width: 34%; text-align: center;">&nbsp;</td>
                            <td style="width: 33%; text-align: center;"><span style="font-size:8px;">dalle 15.00 alle 16.30</span></td>
                        </tr>
                        <tr>
                            <td style="width: 33%; text-align: center;"><span style="font-size:8px;"> sabato </span></td>
                            <td style="width: 34%; text-align: center;"><span style="font-size:8px;">dalle 09.00 alle 12.00</span></td>
                            <td style="width: 33%; text-align: center;">&nbsp;</td>
                        </tr>
                    ';
            case 'B559':                                // Camporosso (comunicato mail 16/4/24 11:26)
                return '<tr>
                            <td style="width: 33%; text-align: center;"><span style="font-size:8px;"> luned&igrave; e venerd&igrave;</span></td>
                            <td style="width: 34%; text-align: center;"><span style="font-size:8px;">dalle 9.00 alle 13.00</span></td>
                            <td style="width: 33%; text-align: center;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td style="width: 33%; text-align: center;"><span style="font-size:8px;"> marted&igrave; </span></td>
                            <td style="width: 34%; text-align: center;">&nbsp;</td>
                            <td style="width: 33%; text-align: center;"><span style="font-size:8px;">dalle 15.00 alle 17.30</span></td>
                        </tr>
                        <tr>
                            <td style="width: 33%; text-align: center;"><span style="font-size:8px;"> mercoled&igrave; e gioved&igrave;</span></td>
                            <td style="width: 67%; text-align: center;"><span style="font-size:8px;">previo appuntamento telefonico</span></td>
                        </tr>
                    ';
                                                        // Anversa degli Abruzzi (confermato mail 16/4/24 12:06)
            default:
                return '<tr>
                            <td style="width: 33%; text-align: center;"><span style="font-size:8px;">dal luned&igrave; al venerd&igrave;</span></td>
                            <td style="width: 34%; text-align: center;"><span style="font-size:8px;">dalle 8.30 alle 12.30</span></td>
                            <td style="width: 33%; text-align: center;">&nbsp;</td>
                        </tr>
                    ';
        }
    }

    public function getHtmlAmountsSoll160LAB(){                                                                                                         // sollecito di pagamento L.160

        if(!is_null($this->a_result)){

            $query = "SELECT * FROM atto WHERE Partita_ID = ".$this->a_result['Partita_ID']." AND Data_Stampa = (SELECT MAX(Data_Stampa) FROM atto WHERE Partita_ID = ".$this->a_result['Partita_ID']." AND ID<".$this->a_result['Atto_ID'].") ORDER BY Data_Elaborazione";
            $a_act = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query))[0];                                                                 // recupero ultimo atto emesso

            $query_ente = "SELECT * FROM enti_gestiti WHERE CC = '".$a_act['CC']."'";                                                                   // recupero dati ente per tabella riassuntiva
            if(strpos($a_act['CC'],'U') === false)
                $nome_ente = "Comune di ".$this->cls_db->getResults($this->cls_db->ExecuteQuery($query_ente))[0]['Denominazione'];
            else
                $nome_ente = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query_ente))[0]['Denominazione'];

            $tot_pag = floatval(str_replace(",",".",'0.00'));                                                                                                                          // recupero pagamenti partita
            $query_pag = "SELECT *, SUM(Importo) AS tot_pag FROM pagamento WHERE Partita_ID = ".$this->a_result['Partita_ID']." ";   
            $pag = floatval(str_replace(",",".",$this->cls_db->getResults($this->cls_db->ExecuteQuery($query_pag))[0]['tot_pag']));    
            if($pag != null)    
                $tot_pag = $pag;   

            $query_pag = "SELECT Tipo FROM partita_tributi WHERE ID = ".$this->a_result['Partita_ID']." ";                                              // recupero tipo entrata
            $tipo = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query_pag))[0]['Tipo'];
            $tipo_entrata = $this->getTaxType($tipo);                                                                                                   

            $query_ann_params = "SELECT * FROM `parametri_annuali` where CC = '".$a_act['CC']."' and Anno = ".date("Y")."";                             // recupero parametri annuali ente
            $a_ann_params = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query_ann_params))[0];

            $totale_codici = floatval(str_replace(",",".",$a_act['Totale_Dovuto'])) - floatval(str_replace(",",".",$a_act['Spese_Notifica_Precedenti'])) - floatval(str_replace(",",".",$a_act['Spese_Notifica'])) - floatval(str_replace(",",".",$a_act['Interessi_Precedenti'])) - floatval(str_replace(",",".",$a_act['Interessi']));
            $residuo = $totale_codici - $tot_pag;
            $residuo = floatval($residuo);

            $interessi = 0;
            $interessi_prec = 0;
  
            $interessi = floatval(str_replace(",",".",$a_act['Interessi']));
            $interessi_prec = floatval(str_replace(",",".",$a_act['Interessi_Precedenti']));

            $aggio = floatval(str_replace(",",".",$a_act['Diritto_Riscossione_Massimo']));
            $spese_post = floatval(str_replace(",",".",$a_act['Spese_Notifica']));
            $spese_post_prec = floatval(str_replace(",",".",$a_act['Spese_Notifica_Precedenti']));
        
            $totale = $interessi + $interessi_prec + $aggio + $spese_post + $spese_post_prec + $totale_codici - $tot_pag;                               // totale atto precedente

            $this->solict_values['spese_sped'] = number_format(floatval(str_replace(",",".",$a_ann_params['Spese_Postali'])), 2, ',', '.');             // valori da inserire nelle variabili del teso dinamico
            $this->solict_values['tot'] = $totale + $this->solict_values['spese_sped'];

            $amountsTable = "";
            
            $amountsTable .= "<div><table style=\"width:100%;border:1px solid black;\" >";                                                              // riquadro riepilogo

            $amountsTable .= "<tbody>
                                <tr>
                                    <td style=\"width: 10%;text-align:center;\"><b>Ente</b></td>
                                    <td style=\"width: 10%;text-align:center;\">".$a_act['CC']."</td>
                                    <td style=\"width: 80%;text-align:left;\">".$nome_ente."</td>            
                                </tr>";

            $amountsTable .= "  <tr>
                                    <td style=\"width: 17%;text-align:center;\"><b>Numero</b></td>
                                    <td style=\"width: 17%;text-align:center;\"><b>Notifica</b></td>
                                    <td style=\"width: 17%;text-align:center;\"><b>Anno</b></td> 
                                    <td style=\"width: 49%;text-align:center;\"><b>Descrizione</b></td>
                                </tr>";

            $data = explode('-',$a_act['Data_Notifica']);


            $amountsTable .= "  <tr>
                                    <td style=\"width: 17%;text-align:center;\">".$a_act['ID_Cronologico']."</td>
                                    <td style=\"width: 17%;text-align:center;\">".$data[2]."/".$data[1]."/".$data[0]."</td>
                                    <td style=\"width: 17%;text-align:center;\">".$a_act['Anno_Cronologico']."</td> 
                                    <td style=\"width: 49%;text-align:center;\">".$tipo_entrata."</td>
                                </tr>";

            if($tipo == 'CDS')  
                $amountsTable .= "  <tr>
                                        <td style=\"width: 15%;text-align:center;\"><b>Importo residuo</b></td>
                                        <td style=\"width: 23%;text-align:center;\"><b>Maggiorazione 10% (Precedenti)</b></td>
                                        <td style=\"width: 12%;text-align:center;\"><b>Spese</b></td> 
                                        <td style=\"width: 15%;text-align:center;\"><b>Aggio</b></td>
                                        <td style=\"width: 20%;text-align:center;\"><b>Spese Postali (Precedenti)</b></td> 
                                        <td style=\"width: 15%;text-align:center;\"><b>Totale</b></td>
                                    </tr>";
            else
                $amountsTable .= "  <tr>
                                        <td style=\"width: 15%;text-align:center;\"><b>Importo residuo</b></td>
                                        <td style=\"width: 23%;text-align:center;\"><b>Interessi (Precedenti)</b></td>
                                        <td style=\"width: 12%;text-align:center;\"><b>Spese</b></td> 
                                        <td style=\"width: 15%;text-align:center;\"><b>Aggio</b></td>
                                        <td style=\"width: 20%;text-align:center;\"><b>Spese Postali (Precedenti)</b></td> 
                                        <td style=\"width: 15%;text-align:center;\"><b>Totale</b></td>
                                    </tr>";

            $amountsTable .= "  <tr>
                                    <td style=\"width: 15%;text-align:center;\">".$residuo."</td>
                                    <td style=\"width: 23%;text-align:center;\">".$interessi." (".$interessi_prec.")</td>
                                    <td style=\"width: 12%;text-align:center;\">0,00</td> 
                                    <td style=\"width: 15%;text-align:center;\">".$aggio."</td>
                                    <td style=\"width: 20%;text-align:center;\">".$spese_post." (".$spese_post_prec.")</td> 
                                    <td style=\"width: 15%;text-align:center;\">".$totale."</td>
                                </tr>";

            $amountsTable .= "</tbody>
                        </table>
                    </div>";

            $amountsTable .= "<div><table style=\"width:100%;\" cellpadding=\"5\">";                                                            // tabella dettagli

            $amountsTable .= "  <thead>
                                    <tr>
                                        <th style=\"border: 1px solid black;width: 15%;text-align:center;\"><b>Anno Rif.</b></th>
                                        <th style=\"border: 1px solid black;width: 15%;text-align:center;\"><b>Cod. tributo</b></th>
                                        <th style=\"border: 1px solid black;width: 58%;text-align:center;\"><b>Descrizione</b></th>            
                                        <th style=\"border: 1px solid black;width: 12%;text-align:center;\"><b>Importo</b></th>
                                    </tr>
                                </thead>
                                <tbody>";
            
            for($i=0;$i<count($this->a_amounts['single_trib']);$i++){
                $amountsTable.= "   <tr>
                                        <td style=\"text-align:center;width: 15%;border: 1px solid black;\">".$this->a_amounts['single_trib'][$i]['anno']."</td>    
                                        <td style=\"text-align:center;width: 15%;border: 1px solid black;\">".explode(" - ",$this->a_amounts['single_trib'][$i]['cod_sanz'])[0]."</td>
                                        <td style=\"text-align:left;width: 58%;border: 1px solid black;\">".$this->a_amounts['single_trib'][$i]['info']."</td>
                                        <td style=\"text-align:right;width: 12%;border: 1px solid black;\">".$this->a_amounts['single_trib'][$i]['amount']."</td>
                                    </tr>";
            }

            if($pag != null){
                $amountsTable .= "  <tr>
                                        <td style=\"text-align:center;width: 6%;\"></td>
                                        <td style=\"text-align:left;width: 10%;\"></td>
                                        <td style=\"text-align:center;width: 10%;\"></td>
                                        <td style=\"width: 62%;text-align:right;\">Pagato</td>            
                                        <td style=\"border: 1px solid black;width: 12%;text-align:right;\">".$tot_pag."</td>
                                    </tr>";

                $amountsTable .= "  <tr>
                                        <td style=\"text-align:center;width: 6%;\"></td>
                                        <td style=\"text-align:left;width: 10%;\"></td>
                                        <td style=\"text-align:center;width: 10%;\"></td>
                                        <td style=\"width: 62%;text-align:right;\">Residuo</td>            
                                        <td style=\"border: 1px solid black;width: 12%;text-align:right;\">".$residuo."</td>
                                    </tr>";
            }

            
            $amountsTable .= "  </tbody>
                            </table>
                        </div>";
            

            $amountsTable .= "</tbody>
                        </table>
                    </div>";

            return $amountsTable;
        }
        else
            return "";
    }

    public function getDataAct(){
        if(!is_null($this->a_result)){

            $query = "SELECT * FROM atto WHERE ID = ".$this->a_result['Atto_Last_ID'];
            $a_act = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"atto");                                                                 // recupero ultimo atto emesso

            $query_ente = "SELECT Denominazione FROM enti_gestiti WHERE CC = '".$a_act['CC']."'";                                                                   // recupero dati ente per tabella riassuntiva
            if(strpos($a_act['CC'],'U') === false && strpos($a_act['CC'],'P') === false)
                $nome_ente = "Comune di ".$this->cls_db->getResults($this->cls_db->ExecuteQuery($query_ente))[0]['Denominazione'];
            else
                $nome_ente = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query_ente))[0]['Denominazione'];

            $tot_pag = floatval(str_replace(",",".",'0.00'));                                                                                                                          // recupero pagamenti partita
            $query_pag = "SELECT *, SUM(Importo) AS tot_pag FROM pagamento WHERE Partita_ID = ".$this->a_result['Partita_ID']." ";   
            $pag = floatval(str_replace(",",".",$this->cls_db->getResults($this->cls_db->ExecuteQuery($query_pag))[0]['tot_pag']));    
            if($pag != null)    
                $tot_pag = $pag;   

            $query_pag = "SELECT Tipo FROM partita_tributi WHERE ID = ".$this->a_result['Partita_ID']." ";                                              // recupero tipo entrata
            $tipo = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query_pag))[0]['Tipo'];
            $tipo_entrata = $this->getTaxType($tipo);                                                                                                   

            $query_ann_params = "SELECT * FROM `parametri_annuali` where CC = '".$a_act['CC']."' and Anno = ".date("Y")."";                             // recupero parametri annuali ente
            $a_ann_params = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query_ann_params))[0];

            $totale_codici = floatval(str_replace(",",".",$a_act['Totale_Dovuto'])) - floatval(str_replace(",",".",$a_act['Spese_Notifica_Precedenti'])) - floatval(str_replace(",",".",$a_act['Spese_Notifica'])) - floatval(str_replace(",",".",$a_act['Interessi_Precedenti'])) - floatval(str_replace(",",".",$a_act['Interessi']));
            $residuo = $totale_codici - $tot_pag;
            $residuo = floatval($residuo);

            $interessi = 0;
            $interessi_prec = 0;
  
            $interessi = floatval(str_replace(",",".",$a_act['Interessi']));
            $interessi_prec = floatval(str_replace(",",".",$a_act['Interessi_Precedenti']));

            $aggio = floatval(str_replace(",",".",$a_act['Diritto_Riscossione_Massimo']));
            $spese_post = floatval(str_replace(",",".",$a_act['Spese_Notifica']));
            $spese_post_prec = floatval(str_replace(",",".",$a_act['Spese_Notifica_Precedenti']));
        
            //var_dump("I: ".$interessi." - IP: ".$interessi_prec." - A: ".$aggio." - SP: ".$spese_post." - SPP: ".$spese_post_prec." - TC: ".$totale_codici." - TP: ".$tot_pag);die;
            $totale = $interessi + $interessi_prec + $aggio + $spese_post + $spese_post_prec + $totale_codici - $tot_pag;                               // totale atto precedente

            $date = $a_act['Data_Notifica'] !== null ? explode("-",$a_act['Data_Notifica']):null;

            if($date !== null) $date = $date[2]."/".$date[1]."/".$date[1]; else $date = "";

            $this->a_last_act_png = [
                "residuo" => number_format($residuo,2,",","."),
                "interessi" => number_format($interessi,2,",","."),
                "interessi_prec" => number_format($interessi_prec,2,",","."),
                "totali_interessi" => number_format($interessi+$interessi_prec,2,",","."),
                "spese" => "0,00",
                "oneri" => number_format($aggio,2,",","."),
                "spese_postali" => number_format($spese_post,2,",","."),
                "spese_postali_prec" => number_format($spese_post_prec,2,",","."),
                "totale_spese_postali" => number_format($spese_post+$spese_post_prec,2,",","."),
                "totale" => number_format($totale,2,",","."),
                "CC" => $a_act['CC'],
                "nome_ente" => $nome_ente,
                "ID_Crono" => $a_act["ID_Cronologico"],
                "Anno_Crono" => $a_act['Anno_Cronologico'],
                "Data_Notifica" => $date,
                "Entrata" => $tipo_entrata,
                "tipo" => $tipo
            ];
        }
        else
            $this->a_last_act_png = null;
    }

    public function getHtmlAmountsSoll160PignoLAB(){                                                                                                         // sollecito di pagamento L.160

        /** PARREBBE GIUSTO L'UNICA COSA DA CONTROLLARE E' L'AGGIO CHE NON CI DOVREBBE ESSERE E AL POSTO CI ANDREBBE L'ONERE **/

        if(!is_null($this->a_result)){

            $amountsTable = "";
            
            $amountsTable .= "<div><table style=\"width:100%;border:1px solid black;\" >";                                                              // riquadro riepilogo

            $amountsTable .= "<tbody>
                                <tr>
                                    <td style=\"width: 10%;text-align:center;\"><b>Ente</b></td>
                                    <td style=\"width: 10%;text-align:center;\">".$this->a_last_act_png['CC']."</td>
                                    <td style=\"width: 80%;text-align:left;\">".$this->a_last_act_png['nome_ente']."</td>            
                                </tr>";

            $amountsTable .= "  <tr>
                                    <td style=\"width: 17%;text-align:center;\"><b>Numero</b></td>
                                    <td style=\"width: 17%;text-align:center;\"><b>Notifica</b></td>
                                    <td style=\"width: 17%;text-align:center;\"><b>Anno</b></td> 
                                    <td style=\"width: 49%;text-align:center;\"><b>Descrizione</b></td>
                                </tr>";


            $amountsTable .= "  <tr>
                                    <td style=\"width: 17%;text-align:center;\">".$this->a_last_act_png['ID_Crono']."</td>
                                    <td style=\"width: 17%;text-align:center;\">".$this->a_last_act_png['Data_Notifica']."</td>
                                    <td style=\"width: 17%;text-align:center;\">".$this->a_last_act_png['Anno_Crono']."</td> 
                                    <td style=\"width: 49%;text-align:center;\">".$this->a_last_act_png['Entrata']."</td>
                                </tr>";

            if($this->a_last_act_png['tipo'] == 'CDS')  
                $amountsTable .= "  <tr>
                                        <td style=\"width: 15%;text-align:center;\"><b>Importo residuo</b></td>
                                        <td style=\"width: 23%;text-align:center;\"><b>Maggiorazione 10% (Precedenti)</b></td>
                                        <td style=\"width: 12%;text-align:center;\"><b>Spese</b></td> 
                                        <td style=\"width: 15%;text-align:center;\"><b>Oneri</b></td>
                                        <td style=\"width: 20%;text-align:center;\"><b>Spese Postali (Precedenti)</b></td> 
                                        <td style=\"width: 15%;text-align:center;\"><b>Totale</b></td>
                                    </tr>";
            else
                $amountsTable .= "  <tr>
                                        <td style=\"width: 15%;text-align:center;\"><b>Importo residuo</b></td>
                                        <td style=\"width: 23%;text-align:center;\"><b>Interessi (Precedenti)</b></td>
                                        <td style=\"width: 12%;text-align:center;\"><b>Spese</b></td> 
                                        <td style=\"width: 15%;text-align:center;\"><b>Oneri</b></td>
                                        <td style=\"width: 20%;text-align:center;\"><b>Spese Postali (Precedenti)</b></td> 
                                        <td style=\"width: 15%;text-align:center;\"><b>Totale</b></td>
                                    </tr>";

            $amountsTable .= "  <tr>
                                    <td style=\"width: 15%;text-align:center;\">".$this->a_last_act_png['residuo']."</td>
                                    <td style=\"width: 23%;text-align:center;\">".$this->a_last_act_png['interessi']." (".$this->a_last_act_png['interessi_prec'].")</td>
                                    <td style=\"width: 12%;text-align:center;\">".$this->a_last_act_png['spese']."</td> 
                                    <td style=\"width: 15%;text-align:center;\">".$this->a_last_act_png['oneri']."</td>
                                    <td style=\"width: 20%;text-align:center;\">".$this->a_last_act_png['spese_postali']." (".$this->a_last_act_png['spese_postali_prec'].")</td> 
                                    <td style=\"width: 15%;text-align:center;\">".$this->a_last_act_png['totale']."</td>
                                </tr>";

            $amountsTable .= "</tbody>
                        </table>
                    ";

            $amountsTable .= "<table style=\"width:100%;\" cellpadding=\"2\">";

            $amountsTable .= "<thead>
                            <tr>
                                <th style=\"border: 1px solid black;width: 10%;text-align:center;\"><b>Cod. Entrata</b></th>
                                <th style=\"border: 1px solid black;width: 10%;text-align:center;\"><b>Periodo</b></th>
                                <th style=\"border: 1px solid black;width: 65%;text-align:center;\"><b>Descrizione Entrata / Natura del debito</b></th>            
                                <th style=\"border: 1px solid black;width: 15%;text-align:center;\"><b>Importo</b></th>
                            </tr>
                            </thead>
                            <tbody>";
                    
            for($i=0;$i<count($this->a_amounts['single_trib']);$i++){
                $amountsTable.= "<tr>
                        <td style=\"text-align:center;width: 10%;border: 1px solid black;\">".explode(" - ",$this->a_amounts['single_trib'][$i]['cod_sanz'])[0]."</td>
                        <td style=\"text-align:center;width: 10%;border: 1px solid black;\">".$this->a_amounts['single_trib'][$i]['anno']."</td>
                        <td style=\"text-align:left;width: 65%;border: 1px solid black;\">".$this->a_amounts['single_trib'][$i]['info']."</td>
                        <td style=\"text-align:right;width: 15%;border: 1px solid black;\">".$this->a_amounts['single_trib'][$i]['amount']."</td>
                    </tr>";
            }
            $amountsTable.= "
                    </tbody></table></div>";

            return $amountsTable;
        }
        else
            return "";
    }

    public function getHtmlAmountsSollLAB(){                                                                             // sollecito di pagamento

        $amountsTable = "<div><table style=\"width:100%;\" cellpadding=\"5\">";

        $amountsTable .= "<thead>
                            <tr>
                                <th style=\"border: 1px solid black;width: 6%;text-align:center;\"><b>N.</b></th>
                                <th style=\"border: 1px solid black;width: 10%;text-align:center;\"><b>Codice</b></th>
                                <th style=\"border: 1px solid black;width: 10%;text-align:center;\"><b>Anno</b></th>
                                <th style=\"border: 1px solid black;width: 62%;text-align:center;\"><b>Descrizione</b></th>            
                                <th style=\"border: 1px solid black;width: 12%;text-align:center;\"><b>Importo</b></th>
                            </tr>
                            </thead>
                            <tbody>";
        
        for($i=0;$i<count($this->a_amounts['single_trib']);$i++){
            $n = $i+1;
            $amountsTable.= "   <tr>
                                    <td style=\"text-align:center;width: 6%;border: 1px solid black;\">".$n."</td>
                                    <td style=\"text-align:center;width: 10%;border: 1px solid black;\">".explode(" - ",$this->a_amounts['single_trib'][$i]['cod_sanz'])[0]."</td>
                                    <td style=\"text-align:center;width: 10%;border: 1px solid black;\">".$this->a_amounts['single_trib'][$i]['anno']."</td>
                                    <td style=\"text-align:left;width: 62%;border: 1px solid black;\">".$this->a_amounts['single_trib'][$i]['info']."</td>
                                    <td style=\"text-align:right;width: 12%;border: 1px solid black;\">".$this->a_amounts['single_trib'][$i]['amount']."</td>
                                </tr>";
        }

        $amountsTable .= "      <tr>
                                    <td style=\"text-align:center;width: 6%;\"></td>
                                    <td style=\"text-align:left;width: 10%;\"></td>
                                    <td style=\"text-align:center;width: 10%;\"></td>
                                    <td style=\"width: 62%;text-align:right;\">Diritti di Notifica</td>            
                                    <td style=\"border: 1px solid black;width: 12%;text-align:right;\">".$this->a_amounts['single'][1]['amount']."</td>
                                </tr>";

        $arrotondamento = 0;
        $temp1 = $this->a_amounts['single_trib_total'][0]['amount'];
        $temp2 = $this->a_amounts['single'][1]['amount'];
        $totale = floatval(str_replace(",",".",$temp1)) + floatval(str_replace(",",".",$temp2));
        $arrotondato = round($totale);
        $arrotondamento = $arrotondato - $totale;
        
        $amountsTable .= "      <tr>
                                    <td style=\"text-align:center;width: 6%;\"></td>
                                    <td style=\"text-align:left;width: 10%;\"></td>
                                    <td style=\"text-align:center;width: 10%;\"></td>
                                    <td style=\"width: 62%;text-align:right;\">Arrotondamento</td>            
                                    <td style=\"border: 1px solid black;width: 12%;text-align:right;\">".number_format($arrotondamento, 2, ',', '.')."</td>
                                </tr>";

        $amountsTable .= "      <tr>
                                    <td style=\"text-align:center;width: 6%;\"></td>
                                    <td style=\"text-align:left;width: 10%;\"></td>
                                    <td style=\"text-align:center;width: 10%;\"></td>
                                    <td style=\"width: 62%;text-align:right;\"><b>Totale dovuto</b></td>            
                                    <td style=\"border: 1px solid black;width: 12%;text-align:right;\"><b>".number_format($arrotondato, 2, ',', '.')."</b></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>";

        return $amountsTable;

    }

    public function getTributeTable(){
        $amountsTable = "<div><table style=\"width:100%;\" cellpadding=\"2\">";

        $amountsTable .= "<thead>
                        <tr>
                            <th style=\"border: 1px solid black;width: 10%;text-align:center;\"><b>Cod. Entrata</b></th>
                            <th style=\"border: 1px solid black;width: 10%;text-align:center;\"><b>Periodo</b></th>
                            <th style=\"border: 1px solid black;width: 65%;text-align:center;\"><b>Descrizione Entrata / Natura del debito</b></th>            
                            <th style=\"border: 1px solid black;width: 15%;text-align:center;\"><b>Importo</b></th>
                        </tr>
                        </thead>
                        <tbody>";
             
        for($i=0;$i<count($this->a_amounts['single_trib']);$i++){
            $amountsTable.= "<tr>
                    <td style=\"text-align:center;width: 10%;border: 1px solid black;\">".explode(" - ",$this->a_amounts['single_trib'][$i]['cod_sanz'])[0]."</td>
                    <td style=\"text-align:center;width: 10%;border: 1px solid black;\">".$this->a_amounts['single_trib'][$i]['anno']."</td>
                    <td style=\"text-align:left;width: 65%;border: 1px solid black;\">".$this->a_amounts['single_trib'][$i]['info']."</td>
                    <td style=\"text-align:right;width: 15%;border: 1px solid black;\">".$this->a_amounts['single_trib'][$i]['amount']."</td>
                </tr>";
        }
        $amountsTable.= "
                </tbody></table></div>";

        return $amountsTable;
    }

    public function getHtmlAmountsLAB($tipo = "atto"){

        $amountsTable = "";

        if($tipo == "atto")
        {

            $amountsTable = "<div><table style=\"width:100%;\" cellpadding=\"5\">";

            $amountsTable .= "<thead>
                            <tr><th style=\"background-color:#E0E0E0;border: 1px solid black;text-align:center;\" colspan=\"5\"><b>TABELLA DI DETTAGLIO</b></th></tr>
                            <tr>
                                <th style=\"border: 1px solid black;width: 35%;text-align:center;\"><b>Codice Sanzione</b></th>
                                <th style=\"border: 1px solid black;width: 6%;text-align:center;\"><b>Anno</b></th>
                                <th style=\"border: 1px solid black;width: 35%;text-align:center;\"><b>Natura del debito</b></th>            
                                <th style=\"border: 1px solid black;width: 12%;text-align:center;\"><b>Data Notifica</b></th>
                                <th style=\"border: 1px solid black;width: 12%;text-align:center;\"><b>Importo</b></th>
                            </tr>
                            </thead>
                            <tbody>";

                               
            for($i=0;$i<count($this->a_amounts['single_trib']);$i++){
                $amountsTable.= "<tr>
                        <td style=\"text-align:left;width: 35%;border: 1px solid black;\">".$this->a_amounts['single_trib'][$i]['cod_sanz']."</td>
                        <td style=\"text-align:center;width: 6%;border: 1px solid black;\">".$this->a_amounts['single_trib'][$i]['anno']."</td>
                        <td style=\"text-align:left;width: 35%;border: 1px solid black;\">".$this->a_amounts['single_trib'][$i]['info']."</td>
                        <td style=\"text-align:center;width: 12%;border: 1px solid black;\">".$this->a_amounts['single_trib'][$i]['data_not']."</td>
                        <td style=\"text-align:right;width: 12%;border: 1px solid black;\">".$this->a_amounts['single_trib'][$i]['amount']."</td>
                    </tr>";
            }
            $amountsTable.= "<tr>
                        <td colspan=\"4\" style=\"text-align:left;\"><b>Totale dovuto</b></td>
                        <td style=\"text-align:right;border: 1px solid black;\"><b>".$this->a_amounts['single_trib_total'][0]['amount']."</b></td>
                    </tr>
                    </tbody></table></div>";
            
            $amountsTable .= "<div><table style=\"width:100%;\" cellpadding=\"5\">
                            <thead>
                                <tr><th colspan=\"2\" style=\"background-color:#E0E0E0;border: 1px solid black;text-align:center;\"><b>IMPORTO DOVUTO</b></th></tr>
                            </thead>
                            <tbody>
            ";
            for($i=0;$i<count($this->a_amounts['single']);$i++){
                $amountsTable.= "<tr>
                        <td style=\"text-align:left;border: 1px solid black;width: 90%;\">".$this->a_amounts['single'][$i]['label']."</td>
                        <td style=\"text-align:right;border: 1px solid black;width: 10%;\">".$this->a_amounts['single'][$i]['amount']."</td>
                    </tr>";
            }
            $amountsTable.= "<tr>
                        <td style=\"text-align:left;border: 1px solid black;width: 90%;\">".$this->a_amounts['single_3'][0]['label']."</td>
                        <td style=\"text-align:right;border: 1px solid black;width: 10%;\">".$this->a_amounts['single_3'][0]['amount']."</td>
                    </tr>
                    <tr>
                        <td style=\"text-align:left;border: 1px solid black;width: 90%;\">".$this->a_amounts['total'][0]['label']."</td>
                        <td style=\"text-align:right;border: 1px solid black;width: 10%;\">".$this->a_amounts['total'][0]['amount']."</td>
                    </tr>
                </tbody>
            </table>
            </div>";

            if(isset($this->a_amounts['total'][1])){
                $amountsTable .= "
                            <div><table style=\"width:100%;\" cellpadding=\"5\">
                            <thead>
                                <tr><th colspan=\"3\" style=\"background-color:#E0E0E0;border: 1px solid black;text-align:center;\"><b>Oneri di Riscossione</b></th></tr>
                                <tr><th colspan=\"3\" style=\"border: 1px solid black;text-align:left;\"><b>IMPORTO DA PAGARE OLTRE I 60 GIORNI DALLA NOTIFICA:</b></th></tr>
                                <tr>
                                    <th style=\"border: 1px solid black;width:80%;\">DESCRIZIONE</th>
                                    <th style=\"border: 1px solid black;width:10%;\">ONERI</th>
                                    <th style=\"border: 1px solid black;width:10%;\">IMPORTO</th>
                                </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td style=\"text-align:left;border: 1px solid black;width:80%;\">".$this->a_amounts['single_6'][0]['label']."</td>
                                <td style=\"text-align:right;border: 1px solid black;width:10%;\">".$this->a_amounts['single_6'][0]['amount']."</td>
                                <td style=\"text-align:right;border: 1px solid black;width:10%;\">".$this->a_amounts['total'][1]['amount']."</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
            ";
            }


            /*$amountsTable.= "</table><hr><table  cellpadding=\"2\">";
            for($i=0;$i<count($this->a_amounts['total']);$i++){
                $amountsTable.= "<tr><td style=\"width:86%;text_align:left;\">".$this->a_amounts['total'][$i]['label']."</td>";
                $amountsTable.= "<td style=\"width:2%; text_align:right;\">".$this->a_amounts['total'][$i]['operator']."</td>";
                $amountsTable.= "<td style=\"width:10%; text_align:right;\">".$this->a_amounts['total'][$i]['amount']."</td>";
                $amountsTable.= "<td style=\"width:2%; text_align:right;\">&euro;</td></tr>";
            }
            $amountsTable.= "</table>";*/
        }
        else if($tipo == "pigno")
        {
            $amountsTable .= '<table width="100%" cellpadding="2" border="0">
                            <thead>
                                <tr><th colspan="2" style="border: 1px solid black;text-align:center;">Prospetto Analitico Debito</th></tr>
                            </thead>
                            <tbody>';

            $amountsTable.= "<tr><td style=\"border: 1px solid black;text-align:left;width:50%;\">Totale Tributi/entrate</td>";
            $amountsTable.= "<td style=\"border: 1px solid black;text-align:right;width:50%;\">".$this->a_last_act_png['residuo']."</td>";
            $amountsTable.= "</tr>";

            $amountsTable.= "<tr><td style=\"border: 1px solid black;text-align:left;width:50%;\">Interessi di mora (*)</td>";
            $amountsTable.= "<td style=\"border: 1px solid black;text-align:right;width:50%;\">".$this->a_last_act_png['totali_interessi']."</td>";
            $amountsTable.= "</tr>";

            $amountsTable.= "<tr><td style=\"border: 1px solid black;text-align:left;width:50%;\">Oneri/Collazione</td>";
            $amountsTable.= "<td style=\"border: 1px solid black;text-align:right;width:50%;\">".$this->a_last_act_png['oneri']."</td>";
            $amountsTable.= "</tr>";

            $amountsTable.= "<tr><td style=\"border: 1px solid black;text-align:left;width:50%;\">Spese di notifica Ingiunzione</td>";
            $amountsTable.= "<td style=\"border: 1px solid black;text-align:right;width:50%;\">".$this->a_last_act_png['totale_spese_postali']."</td>";
            $amountsTable.= "</tr>";

                            
            for($x = 0; $x < count($this->a_amounts['single']); $x++ )
            {
                if($x==0) $i = 1;
                else $i = 0;
                
                for(;$i<count($this->a_amounts['single'][$x]);$i++){
                    $amountsTable.= "<tr><td style=\"border: 1px solid black;text-align:left;width:50%;\">".$this->a_amounts['single'][$x][$i]['label']."</td>";
                    $amountsTable.= "<td style=\"border: 1px solid black;text-align:right;width:50%;\">".$this->a_amounts['single'][$x][$i]['amount']."</td>";
                    $amountsTable.= "</tr>";
                }

                if($x == count($this->a_amounts['single']) - 1)
                    for($i=0;$i<count($this->a_amounts['total'][$x]);$i++){

                        $amountsTable.= "<tr><td style=\"border: 1px solid black;text-align:left;width:50%;\"><b>TOTALE DA PAGARE</b></td>";
                        //$amountsTable.= "<tr><td style=\"border: 1px solid black;text-align:left;width:50%;\"><b>".$this->a_amounts['total'][$x][$i]['label']."</b></td>";
                        $amountsTable.= "<td style=\"border: 1px solid black;text-align:right;width:50%;\">".$this->a_amounts['total'][$x][$i]['amount']."</td>";
                        $amountsTable.= "</tr>";
                    }
                
            }

            $amountsTable.= "</tbody></table>";
            

        }else $amountsTable = "";

        //echo $amountsTable;
        return $amountsTable;
    }

    public function getHtmlAmountsNoCDSLAB($tipo = "atto"){

        $amountsTable = "";

        if($tipo == "atto")
        {

            $amountsTable = "<div><table style=\"width:100%;\" cellpadding=\"5\">";

            $amountsTable .= "<thead>
                            <tr><th style=\"background-color:#E0E0E0;border: 1px solid black;text-align:center;\" colspan=\"5\"><b>TABELLA DI DETTAGLIO</b></th></tr>
                            <tr>
                                <th style=\"border: 1px solid black;width: 35%;text-align:center;\"><b>Codice Sanzione</b></th>
                                <th style=\"border: 1px solid black;width: 6%;text-align:center;\"><b>Anno</b></th>
                                <th style=\"border: 1px solid black;width: 35%;text-align:center;\"><b>Natura del debito</b></th>            
                                <th style=\"border: 1px solid black;width: 12%;text-align:center;\"><b>Data Notifica</b></th>
                                <th style=\"border: 1px solid black;width: 12%;text-align:center;\"><b>Importo</b></th>
                            </tr>
                            </thead>
                            <tbody>";

                               
            for($i=0;$i<count($this->a_amounts['single_trib']);$i++){
                $amountsTable.= "<tr>
                        <td style=\"text-align:left;width: 35%;border: 1px solid black;\">".$this->a_amounts['single_trib'][$i]['cod_sanz']."</td>
                        <td style=\"text-align:center;width: 6%;border: 1px solid black;\">".$this->a_amounts['single_trib'][$i]['anno']."</td>
                        <td style=\"text-align:left;width: 35%;border: 1px solid black;\">".$this->a_amounts['single_trib'][$i]['info']."</td>
                        <td style=\"text-align:center;width: 12%;border: 1px solid black;\">".$this->a_amounts['single_trib'][$i]['data_not']."</td>
                        <td style=\"text-align:right;width: 12%;border: 1px solid black;\">".$this->a_amounts['single_trib'][$i]['amount']."</td>
                    </tr>";
            }
            $amountsTable.= "<tr>
                        <td colspan=\"4\" style=\"text-align:left;\"><b>Totale dovuto</b></td>
                        <td style=\"text-align:right;border: 1px solid black;\"><b>".$this->a_amounts['single_trib_total'][0]['amount']."</b></td>
                    </tr>
                    </tbody></table></div>";

            $amountsTable .= "<div><table style=\"width:100%;\" cellpadding=\"5\">
                    <thead>
                        <tr><th colspan=\"2\" style=\"background-color:#E0E0E0;border: 1px solid black;text-align:center;\"><b>Ulteriori Interessi</b></th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan=\"2\" style=\"text-align:left;border: 1px solid black;\">Gli ulteriori interessi al debito principale,calcolati al tasso legale dal momento in cui l’atto si è definito alla data di stampa dell’ingiunzione stessa, sono
                            indicati nella Tabella di Dettaglio alla voce +l<br>
                            Di seguito si riportano i saggi annuali utilizzati per il calcolo:</td>
                        </tr>
                        <tr>
                            <td style=\"width:50%;border:1px solid black;\">
                                <table style=\"width:100%;\">
                                    <thead>
                                        <tr><th style=\"width:40%;text-align:center;\">dal</th><th style=\"width:40%;text-align:center;\">al</th><th style=\"width:20%;text-align:center;\">saggio</th></tr>
                                    </thead>
                                    <tbody>
                                        ";

            for($i=0;$i<count($this->a_interessi_tributi);$i++){

                $dataFineInteresse = $this->a_interessi_tributi[$i]['Data_Fine'] !== null ? $this->a_interessi_tributi[$i]['Data_Fine'] : "---";

                $flagFirst = false;
                if($i==0){
                    $flagFirst == true;
                    do{ 
                        $dataFineInteresse = $this->a_interessi_tributi[$i]['Data_Fine'] !== null ? $this->a_interessi_tributi[$i]['Data_Fine'] : "---";

                        $amountsTable.= "<tr>
                            <td style=\"text-align:center;width: 40%;\">".$this->a_interessi_tributi[$i]['Data_Inizio']."</td>
                            <td style=\"text-align:center;width: 40%;\">".$dataFineInteresse."</td>
                            <td style=\"text-align:right;width: 20%;\">".$this->a_interessi_tributi[$i]['Tasso_Interessi']."</td>
                        </tr>";
                        $i++;
                    }while($i<(count($this->a_interessi_tributi)/2));

                    $amountsTable.= "</tbody></table></td><td style=\"width:50%;border:1px solid black;\">
                    <table style=\"width:100%;\">
                    <thead>
                        <tr><th style=\"width:40%;text-align:center;\">dal</th><th style=\"width:40%;text-align:center;\">al</th><th style=\"width:20%;text-align:center;\">saggio</th></tr>
                    </thead>
                    <tbody>";
                }

                if(count($this->a_interessi_tributi) == 1){
                    break;
                }

                $amountsTable.= "<tr>
                            <td style=\"text-align:center;width: 40%;\">".$this->a_interessi_tributi[$i]['Data_Inizio']."</td>
                            <td style=\"text-align:center;width: 40%;\">".$dataFineInteresse."</td>
                            <td style=\"text-align:right;width: 20%;\">".$this->a_interessi_tributi[$i]['Tasso_Interessi']."</td>
                        </tr>";
            }
            
            $amountsTable.= "</tbody></table></td></tr></tbody></table></div>";
            
            $amountsTable .= "<div><table style=\"width:100%;\" cellpadding=\"5\">
                            <thead>
                                <tr><th colspan=\"2\" style=\"background-color:#E0E0E0;border: 1px solid black;text-align:center;\"><b>IMPORTO DOVUTO</b></th></tr>
                            </thead>
                            <tbody>
            ";
            for($i=0;$i<count($this->a_amounts['single']);$i++){
                $amountsTable.= "<tr>
                        <td style=\"text-align:left;border: 1px solid black;width: 90%;\">".$this->a_amounts['single'][$i]['label']."</td>
                        <td style=\"text-align:right;border: 1px solid black;width: 10%;\">".$this->a_amounts['single'][$i]['amount']."</td>
                    </tr>";
            }
            $amountsTable.= "<tr>
                        <td style=\"text-align:left;border: 1px solid black;width: 90%;\">".$this->a_amounts['single_3'][0]['label']."</td>
                        <td style=\"text-align:right;border: 1px solid black;width: 10%;\">".$this->a_amounts['single_3'][0]['amount']."</td>
                    </tr>
                    <tr>
                        <td style=\"text-align:left;border: 1px solid black;width: 90%;\">".$this->a_amounts['total'][0]['label']."</td>
                        <td style=\"text-align:right;border: 1px solid black;width: 10%;\">".$this->a_amounts['total'][0]['amount']."</td>
                    </tr>
                </tbody>
            </table>
            </div>";

            if(isset($this->a_amounts['total'][1])){
                $amountsTable .= "
                            <div><table style=\"width:100%;\" cellpadding=\"5\">
                            <thead>
                                <tr><th colspan=\"3\" style=\"background-color:#E0E0E0;border: 1px solid black;text-align:center;\"><b>Oneri di Riscossione</b></th></tr>
                                <tr><th colspan=\"3\" style=\"border: 1px solid black;text-align:left;\"><b>IMPORTO DA PAGARE OLTRE I 60 GIORNI DALLA NOTIFICA:</b></th></tr>
                                <tr>
                                    <th style=\"border: 1px solid black;width:80%;\">DESCRIZIONE</th>
                                    <th style=\"border: 1px solid black;width:10%;\">ONERI</th>
                                    <th style=\"border: 1px solid black;width:10%;\">IMPORTO</th>
                                </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td style=\"text-align:left;border: 1px solid black;width:80%;\">".$this->a_amounts['single_6'][0]['label']."</td>
                                <td style=\"text-align:right;border: 1px solid black;width:10%;\">".$this->a_amounts['single_6'][0]['amount']."</td>
                                <td style=\"text-align:right;border: 1px solid black;width:10%;\">".$this->a_amounts['total'][1]['amount']."</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
            ";
            }
        }
        else if($tipo == "pigno")
        {
            
            for($x = 0; $x < count($this->a_amounts['single']); $x++ )
            {
                $amountsTable .= '<table width="100%" cellpadding="2" border="0">';
                for($i=0;$i<count($this->a_amounts['single'][$x]);$i++){
                    $amountsTable.= "<tr><td width=\"86%\" align=\"right\">".$this->a_amounts['single'][$x][$i]['label']."</td>";
                    $amountsTable.= "<td width=\"3%\" align=\"center\">".$this->a_amounts['single'][$x][$i]['operator']."</td>";
                    $amountsTable.= "<td width=\"8%\" align=\"right\">".$this->a_amounts['single'][$x][$i]['amount']."</td>";
                    $amountsTable.= "<td width=\"3%\" align=\"center\">&euro;</td></tr>";
                }
                $amountsTable .= '</table><hr><table width="100%" cellpadding="2"  border="0">';
                for($i=0;$i<count($this->a_amounts['total'][$x]);$i++){
                    $amountsTable.= "<tr><td width=\"86%\" align=\"right\">".$this->a_amounts['total'][$x][$i]['label']."</td>";
                    $amountsTable.= "<td width=\"3%\" align=\"center\">".$this->a_amounts['total'][$x][$i]['operator']."</td>";
                    $amountsTable.= "<td width=\"8%\" align=\"right\">".$this->a_amounts['total'][$x][$i]['amount']."</td>";
                    $amountsTable.= "<td width=\"3%\" align=\"center\">&euro;</td></tr>";
                }
                $amountsTable.= "</table>";
            }
            

        }else $amountsTable = "";

        //echo $amountsTable;
        return $amountsTable;
    }


    public function getHtmlAmounts($tipo = "atto"){

        $amountsTable = "";

        if($tipo == "atto")
        {
            $amountsTable = "<table cellpadding=\"2\">";
            for($i=0;$i<count($this->a_amounts['single']);$i++){
                $amountsTable.= "<tr><td style=\"width:86%;text_align:left;\">".$this->a_amounts['single'][$i]['label']."</td>";
                $amountsTable.= "<td style=\"width:2%; text_align:right;\">".$this->a_amounts['single'][$i]['operator']."</td>";
                $amountsTable.= "<td style=\"width:10%; text_align:right;\">".$this->a_amounts['single'][$i]['amount']."</td>";
                $amountsTable.= "<td style=\"width:2%; text_align:right;\">&euro;</td></tr>";
            }
            $amountsTable.= "</table><hr><table  cellpadding=\"2\">";
            for($i=0;$i<count($this->a_amounts['total']);$i++){
                $amountsTable.= "<tr><td style=\"width:86%;text_align:left;\">".$this->a_amounts['total'][$i]['label']."</td>";
                $amountsTable.= "<td style=\"width:2%; text_align:right;\">".$this->a_amounts['total'][$i]['operator']."</td>";
                $amountsTable.= "<td style=\"width:10%; text_align:right;\">".$this->a_amounts['total'][$i]['amount']."</td>";
                $amountsTable.= "<td style=\"width:2%; text_align:right;\">&euro;</td></tr>";
            }
            $amountsTable.= "</table>";
        }
        else if($tipo == "pigno")
        {
            
            for($x = 0; $x < count($this->a_amounts['single']); $x++ )
            {
                $amountsTable .= '<table width="100%" cellpadding="2" border="0">';
                for($i=0;$i<count($this->a_amounts['single'][$x]);$i++){
                    $amountsTable.= "<tr><td width=\"86%\" align=\"right\">".$this->a_amounts['single'][$x][$i]['label']."</td>";
                    $amountsTable.= "<td width=\"3%\" align=\"center\">".$this->a_amounts['single'][$x][$i]['operator']."</td>";
                    $amountsTable.= "<td width=\"8%\" align=\"right\">".$this->a_amounts['single'][$x][$i]['amount']."</td>";
                    $amountsTable.= "<td width=\"3%\" align=\"center\">&euro;</td></tr>";
                }
                $amountsTable .= '</table><hr><table width="100%" cellpadding="2"  border="0">';
                for($i=0;$i<count($this->a_amounts['total'][$x]);$i++){
                    $amountsTable.= "<tr><td width=\"86%\" align=\"right\">".$this->a_amounts['total'][$x][$i]['label']."</td>";
                    $amountsTable.= "<td width=\"3%\" align=\"center\">".$this->a_amounts['total'][$x][$i]['operator']."</td>";
                    $amountsTable.= "<td width=\"8%\" align=\"right\">".$this->a_amounts['total'][$x][$i]['amount']."</td>";
                    $amountsTable.= "<td width=\"3%\" align=\"center\">&euro;</td></tr>";
                }
                $amountsTable.= "</table>";
            }
            

        }else $amountsTable = "";

        //echo $amountsTable;
        return $amountsTable;
    }

    public function getHtmlAmountsLine($tipo = "atto"){

        $amountsTable = "";

        if($tipo == "atto")
        {
            $amountsTable = "<p>";
            for($i=0;$i<count($this->a_amounts['single']);$i++){
                if(array_key_exists($i,$this->a_amounts['single']) && array_key_exists($i,$this->a_amounts['total']))
                    $amountsTable.= $this->a_amounts['single'][$i]['label']." ".$this->a_amounts['single'][$i]['operator']." ".$this->a_amounts['total'][$i]['amount']." €<br/>";
            }
            $amountsTable.= "<hr><br/>";
            for($i=0;$i<count($this->a_amounts['total']);$i++){
                if(array_key_exists($i,$this->a_amounts['total']))
                    $amountsTable.= $this->a_amounts['total'][$i]['label']." ".$this->a_amounts['total'][$i]['operator']." ".$this->a_amounts['total'][$i]['amount']." €<br/>";
            }
            $amountsTable.= "</p>";
        }
        else if($tipo == "pigno")
        {

            for($x = 0; $x < count($this->a_amounts['single']); $x++ )
            {
                $amountsTable .= "<p>";
                for($i=0;$i<count($this->a_amounts['single'][$x]);$i++){
                    $amountsTable.= $this->a_amounts['single'][$x][$i]['label']." ".$this->a_amounts['single'][$x][$i]['operator']." ".$this->a_amounts['single'][$x][$i]['amount']." €<br/>";
                }
                $amountsTable.= "<hr><br/>";
                for($i=0;$i<count($this->a_amounts['total'][$x]);$i++){
                    $amountsTable.= $this->a_amounts['total'][$x][$i]['label']." ".$this->a_amounts['total'][$x][$i]['operator']." ".$this->a_amounts['total'][$x][$i]['amount']." €<br/>";
                }
                $amountsTable.= "</p>";
            }

        }else $amountsTable = "";

        //echo $amountsTable;
        return $amountsTable;
    }

    public function getReferences($Type = "atto"){
        switch($Type)
        {
            case "atto": $docType = $this->a_result['Atto']; break;
            case "pigno": $docType = $this->a_result['Nome_Pignoramento']; break;
            default: $docType = "";
        }
        $references[0] = $docType." ".$this->a_result['ID_Cronologico']."/".$this->a_result['Anno_Cronologico'];
        $references[1] = "Rif. ".$this->a_result['Comune_ID']."/".$this->a_result['CC']." ".$this->a_result['Tipo_Riscossione'];
        return $references;
    }

    public function getDocumentDetails($documentTypeId, $printTypeId=null, $officialType=null, $a_params = array()){
        $a_type = array();

        foreach($a_params as $key => $value)
        {
            $a_type[$key] = $value;
        }

        $a_type['PrintTypeId'] = (int) $printTypeId;
        $a_type['officialType'] = $officialType;
        $a_type['DocumentTypeId'] = $documentTypeId;
        switch($documentTypeId){
            case 11:
                $a_type['textTable'] = "text_sollecito_pre_ingiunzione";
                $a_type['dirName'] = "Solleciti_Pre_Ingiunzione";
                $a_type['tempFileName'] = "sollecitiPreIngiunzione";
                $a_type['finalFileName'] = "sollecitoPreIngiunzione";
                $a_type['title'] = "Solleciti pre ingiunzione";
                $a_type['docType'] = "Sollecito pre ingiunzione";
                $a_type['type'] = "SOLL_PRE";

                break;
            case 12:
                $a_type['textTable'] = "text_avviso_messa_in_mora";
                $a_type['dirName'] = "Avvisi_Messa_In_Mora";
                $a_type['tempFileName'] = "avvisiMessaInMora";
                $a_type['finalFileName'] = "avvisoMessaInMora";
                $a_type['title'] = "Avvisi di messa in mora";
                $a_type['docType'] = "Avviso di messa in mora";
                $a_type['type'] = "AV_MORA";

                break;

            case 2:
                $a_type['textTable'] = "";
                $a_type['dirName'] = "Ingiunzioni";
                $a_type['tempFileName'] = "Ingiunzioni";
                $a_type['finalFileName'] = "Ingiunzione";
                $a_type['title'] = "Ingiunzioni";
                $a_type['docType'] = "Ingiunzione";
                $a_type['type'] = "INGIUNZIONE";

                break;

            case 3:
                $a_type['textTable'] = "";
                $a_type['dirName'] = "Solleciti";
                $a_type['tempFileName'] = "sollecitiPostIngiunzione";
                $a_type['finalFileName'] = "sollecitoPostIngiunzione";
                $a_type['title'] = "Solleciti post ingiunzione";
                $a_type['docType'] = "Sollecito di pagamento";
                $a_type['type'] = "SOLL_POST";

                break;

            case 4:
                $a_type['textTable'] = "text_avviso_di_intimazione";
                $a_type['dirName'] = "Avvisi_di_intimazione";
                $a_type['tempFileName'] = "Avvisi_di_intimazione";
                $a_type['finalFileName'] = "Avviso_di_intimazione";
                $a_type['title'] = "Avviso di intimazione";
                $a_type['docType'] = "Avviso di intimazione";
                $a_type['type'] = "AV_INT";

                break;

            case 6:
                $a_type['textTable'] = "";
                $a_type['dirName'] = "Veicolo";
                $a_type['tempFileName'] = "PignoramentiVeicoli";
                $a_type['finalFileName'] = "PignoramentoVeicolo";
                $a_type['title'] = "Pignoramento veicolo";
                $a_type['docType'] = "veicolo";
                $a_type['type'] = "veicolo";

                break;

            case 7:
                $a_type['textTable'] = "";
                $a_type['dirName'] = "Datore_di_Lavoro";
                $a_type['tempFileName'] = "PignoramentiLavori";
                $a_type['finalFileName'] = "PignoramentoLavoro";
                $a_type['title'] = "Pignoramento datore di lavoro";
                $a_type['docType'] = "lavoro";
                $a_type['type'] = "lavoro";

                break;

            case 8:
                $a_type['textTable'] = "";
                $a_type['dirName'] = "Banca";
                $a_type['tempFileName'] = "PignoramentiBanche";
                $a_type['finalFileName'] = "Pignoramento_presso_banca_";
                $a_type['title'] = "Pignoramento presso banca";
                $a_type['docType'] = "banca";
                $a_type['type'] = "banca";

                break;

            case 22:
                $a_type['textTable'] = "";
                $a_type['dirName'] = "Preavviso_Fermo";
                $a_type['tempFileName'] = "PignoramentiFermo";
                $a_type['finalFileName'] = "Preavviso_fermo_";
                $a_type['title'] = "Preavviso fermo";
                $a_type['docType'] = "preav_fermo";
                $a_type['type'] = "preav_fermo";

                break;

        }
        $this->a_docDetails = $a_type;
    }


    public function setDocAmountsLAB($documentTypeId, $a_yearParams = null, $tipo = "atto"){
        $this->getTributeCodesForPrint();
        //var_dump($docType);
        //echo "<h1>second --> ".$a_yearParams."</h1>";

        if(!is_null($this->a_result) && $this->a_result['Tipo_Riscossione']=="CDS"){
            $str_interessi = "Maggiorazione del 10% semestrale";
            if(!is_null($this->a_result['Partita_Data_Decorrenza'])){
                $a_date = explode("-",$this->a_result['Partita_Data_Decorrenza']);
                if(count($a_date)==3)
                    $str_interessi.= " calcolata dal ".$a_date[2]."/".$a_date[1]."/".$a_date[0];
            }
        }
        else
            $str_interessi = "Interessi";

        $countAmounts = 0;
        $countAmountsTrib = 0;
        $codiciPayment = 0.00;
        $total = 0;
        $this->a_amounts = null;
        //$tre_perc = 0;

        if($tipo == "atto")
        {
            foreach ($this->a_annualTribute as $codice=>$a_codice){
                if($a_codice['Scorporo_ID']==0){
                    $codiciPayment+= $a_codice['Totale'];
                }
                else{
                    if($a_codice['Totale']>0.00){
                        $total += $a_codice['Totale'];
                        
                        //$this->a_amounts['single_trib'][$countAmounts]['operator'] = "+";
                        //$this->a_amounts['single_trib'][$countAmounts]['label'] = $a_codice['Testo'];
                        $this->a_amounts['single_trib'][$countAmountsTrib]['cod_sanz'] = $codice." - ".$a_codice['Descrizioni_Codice'];
                        $this->a_amounts['single_trib'][$countAmountsTrib]['anno'] = $a_codice['Anno'];
                        $this->a_amounts['single_trib'][$countAmountsTrib]['info'] = $a_codice['Info_Cartelle'];
                        $this->a_amounts['single_trib'][$countAmountsTrib]['data_not'] = $a_codice['Date_Decorrenze_Interessi'];
                        $this->a_amounts['single_trib'][$countAmountsTrib]['amount'] = number_format($a_codice['Totale'],2,",",".");

                        $countAmountsTrib++;
                    }

                }
            }

            $this->a_amounts['single_trib_total'][0]['amount'] = number_format($total,2,",",".");
        }
        else if($tipo == "pignoramento"){
            
            foreach ($this->a_annualTribute as $codice=>$a_codice){
                if($a_codice['Scorporo_ID']==0){
                    $codiciPayment+= $a_codice['Totale'];
                }
                else{
                    if($a_codice['Totale']>0.00){
                        $total += $a_codice['Totale'];
                        
                        //$this->a_amounts['single_trib'][$countAmounts]['operator'] = "+";
                        //$this->a_amounts['single_trib'][$countAmounts]['label'] = $a_codice['Testo'];
                        $this->a_amounts['single_trib'][$countAmountsTrib]['cod_sanz'] = $codice." - ".$a_codice['Descrizioni_Codice'];
                        $this->a_amounts['single_trib'][$countAmountsTrib]['anno'] = $a_codice['Anno'];
                        $this->a_amounts['single_trib'][$countAmountsTrib]['info'] = $a_codice['Info_Cartelle'];
                        //$this->a_amounts['single_trib'][$countAmountsTrib]['data_not'] = $a_codice['Date_Decorrenze_Interessi'];
                        $this->a_amounts['single_trib'][$countAmountsTrib]['amount'] = number_format($a_codice['Totale'],2,",",".");

                        $countAmountsTrib++;
                    }

                }
            }
        }

        if($tipo == "atto")
            $tot_pagamenti = $this->a_result['Totale_Pagato']+$codiciPayment;

        if($a_yearParams==null){
            $a_oneri['perc_min'] = 3;
            $a_oneri['perc_max'] = 6;
            $a_oneri['days_limit'] = 60;
        }
        else{
            $a_oneri['perc_min'] = $a_yearParams['Diritto_Riscossione_Minimo'];
            $a_oneri['perc_max'] = $a_yearParams['Diritto_Riscossione_Massimo'];
            $a_oneri['days_limit'] = $a_yearParams['Giorni_Diritto'];
        }

        //echo "<h1>Tipo = ".$docType."</h1>";


        switch($documentTypeId){
            case 11:
                if($this->a_result['Spese_Notifica_Precedenti']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Spese postali/notifica/ricerca dei precedenti atti di accertamento";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Spese_Notifica_Precedenti'],2,",",".");
                    $total += $this->a_result['Spese_Notifica_Precedenti'];
                    $countAmounts++;
                }
                if($this->a_result['Spese_Notifica']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Spese postali/notifica del presente Sollecito di pagamento";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Spese_Notifica'],2,",",".");
                    $total += $this->a_result['Spese_Notifica'];
                    $countAmounts++;
                }
                if($tot_pagamenti>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "-";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Pagamenti dei precedenti atti di accertamento";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($tot_pagamenti,2,",",".");
                    $total -= $tot_pagamenti;
                }

                $this->a_amounts['total'][0]['operator'] = "=";
                $this->a_amounts['total'][0]['label'] = "Differenza da versare";
                $this->a_amounts['total'][0]['amount'] = number_format($total,2,",",".");
                break;

            case 12:
                if($this->a_result['Spese_Notifica_Precedenti']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Spese postali/notifica/ricerca dei precedenti atti di accertamento";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Spese_Notifica_Precedenti'],2,",",".");
                    $total += $this->a_result['Spese_Notifica_Precedenti'];
                    $countAmounts++;
                }
                if($this->a_result['Interessi']>0 || $this->a_result['Interessi_Precedenti']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Interessi";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Interessi']+$this->a_result['Interessi_Precedenti'],2,",",".");
                    $total += $this->a_result['Spese_Notifica'];
                    $countAmounts++;
                }
                if($this->a_result['Spese_Notifica']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Spese postali/notifica del presente Avviso di messa in mora";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Spese_Notifica'],2,",",".");
                    $total += $this->a_result['Spese_Notifica'];
                    $countAmounts++;
                }
                if($tot_pagamenti>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "-";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Pagamenti dei precedenti atti di accertamento";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($tot_pagamenti,2,",",".");
                    $total -= $tot_pagamenti;
                }

                $this->a_amounts['total'][0]['operator'] = "=";
                $this->a_amounts['total'][0]['label'] = "Totale dovuto";
                $this->a_amounts['total'][0]['amount'] = number_format($total,2,",",".");
                break;

            case 2:

                $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                $this->a_amounts['single'][$countAmounts]['label'] = "Importo dovuto per i verbali (al netto delle somme corrisposte ad oggi)";
                $this->a_amounts['single'][$countAmounts]['amount'] = number_format($total,2,",",".");
                $countAmounts++;
                $total_perc_risc = $total;

                if($this->a_result['Spese_Notifica_Precedenti']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Spese postali/notifica/ricerca dei precedenti atti di accertamento";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Spese_Notifica_Precedenti'],2,",",".");
                    $total += $this->a_result['Spese_Notifica_Precedenti'];
                    $countAmounts++;
                }

                if($this->a_result['Spese_Notifica_Pignoramento']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Spese notifica dei precedenti pignoramenti";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Spese_Notifica_Pignoramento'],2,",",".");
                    $total += $this->a_result['Spese_Notifica_Pignoramento'];
                    $countAmounts++;
                }

                if($this->a_result['Spese_Accessorie_Pignoramento']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Spese accessorie dei precedenti pignoramenti";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Spese_Accessorie_Pignoramento'],2,",",".");
                    $total += $this->a_result['Spese_Accessorie_Pignoramento'];
                    $countAmounts++;
                }

                if($this->a_result['Interessi']>0 || $this->a_result['Interessi_Precedenti']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = $str_interessi;
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Interessi']+$this->a_result['Interessi_Precedenti'],2,",",".");
                    $total += $this->a_result['Interessi']+$this->a_result['Interessi_Precedenti'];
                    $total_perc_risc += $this->a_result['Interessi'];
                    $countAmounts++;
                }

                if($this->a_result['Spese_Notifica']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Spese postali/notifica della presente Ingiunzione";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Spese_Notifica'],2,",",".");
                    $total += $this->a_result['Spese_Notifica'];
                    $countAmounts++;
                }

                if($tot_pagamenti>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "-";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Pagamenti dei precedenti atti di accertamento";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($tot_pagamenti,2,",",".");
                    $total -= $tot_pagamenti;
                    $countAmounts++;
                }

                $this->a_amounts['single_3'][0]['operator'] = "-";
                $this->a_amounts['single_3'][0]['label'] = "Oneri di Riscossione 3%";
                $this->a_amounts['single_3'][0]['amount'] = number_format($total_perc_risc/100*3,2,",",".");

                $this->a_amounts['single_6'][0]['operator'] = "-";
                $this->a_amounts['single_6'][0]['label'] = "ONERI DI RISCOSSIONE CALCOLATI SULL'IMPORTO TOTALE 6,00%";
                $this->a_amounts['single_6'][0]['amount'] = number_format($total_perc_risc/100*6,2,",",".");

                if($this->a_result['Diritto_Riscossione_Minimo']>0){
                    $this->a_amounts['total'][0]['operator'] = "=";
                    $this->a_amounts['total'][0]['label'] = "TOTALE DOVUTO PER LA INGIUNZIONE (entro ".$a_oneri['days_limit']." giorni dalla notifica)";
                    $this->a_amounts['total'][0]['amount'] = number_format($total+$this->a_result['Diritto_Riscossione_Minimo'],2,",",".");

                    $this->a_amounts['total'][1]['operator'] = "=";
                    $this->a_amounts['total'][1]['label'] = "ONERI DI RISCOSSIONE COLCOLATI SULL'IMPORTO TOTALE ".$a_oneri['perc_max']."%";
                    $this->a_amounts['total'][1]['amount'] = number_format($total+$this->a_result['Diritto_Riscossione_Massimo'],2,",",".");
                }
                else{
                    $this->a_amounts['total'][0]['operator'] = "=";
                    $this->a_amounts['total'][0]['label'] = "TOTALE DOVUTO PER LA INGIUNZIONE (entro ".$a_oneri['days_limit']." giorni dalla notifica)";
                    $this->a_amounts['total'][0]['amount'] = number_format($total,2,",",".");
                }

                break;

            case 6:
            case 7:
            case 8:
            case 22:

                $countAmounts = 0;
                $query = "SELECT * FROM pignoramento_spese WHERE Pignoramento_ID = ".$this->a_result['ID']." AND CC = '".$this->a_result['CC']."'";
                $spese = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"pignoramento_spese");

                $query = "SELECT * FROM pignoramento_generale WHERE ID = ".$this->a_result['ID']." AND CC = '".$this->a_result['CC']."'";
                $atto_pignoramento = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"atto");
                $pagamenti_atto = number_format($this->cls_stp->totale_pagamenti($atto_pignoramento),2,",","");

                $spese_array = $this->cls_stp->spese_array($spese);
                $TOTALI_ARRAY = $this->cls_stp->totali_spese($spese);

                //var_dump($TOTALI_ARRAY);die;

                $this->a_amounts['single'][0][$countAmounts]['operator'] = "";
                $this->a_amounts['single'][0][$countAmounts]['label'] = "Ripresa debito precedente";
                $this->a_amounts['single'][0][$countAmounts]['amount'] = number_format($this->a_result['Importo_Atto'],2,",","");
                $countAmounts++;

                if($this->a_result['Interessi']>0 && $this->a_result['Tipo_Riscossione']!="CDS"){
                    if(!is_null($this->a_result) && $this->a_result['Tipo_Riscossione']=="CDS")
                        $str_interessi_pigno = "Ripresa Maggiorazione del 10% semestrale";
                    else
                        $str_interessi_pigno = "Nuovi Interessi";

                    $this->a_amounts['single'][0][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][0][$countAmounts]['label'] = $str_interessi_pigno;
                    $this->a_amounts['single'][0][$countAmounts]['amount'] = number_format($this->a_result['Interessi'],2,",","");
                }

                

                // $this->a_amounts['single'][0][$countAmounts]['operator'] = "";
                // $this->a_amounts['single'][0][$countAmounts]['label'] = "Ripresa debito precedente";
                // $this->a_amounts['single'][0][$countAmounts]['amount'] = number_format($this->a_result['Importo_Dovuto'],2,",","");
                // $countAmounts++;


                // $this->a_amounts['single'][0][$countAmounts]['operator'] = "-";
                // $this->a_amounts['single'][0][$countAmounts]['label'] = "Eventuale importo pagato successivamente alla notifica degli atti ingiuntivi e intimativi";
                // $this->a_amounts['single'][0][$countAmounts]['amount'] = $pagamenti_atto;

                $Debito_Precedente = (float) $this->a_result["Importo_Dovuto"];
                $this->a_amounts['total'][0][0]['operator'] = "=";
                $this->a_amounts['total'][0][0]['label'] = "Totale debito precedente";
                $this->a_amounts['total'][0][0]['amount'] = number_format($Debito_Precedente,2,",",".");
                //($this->a_amounts['single']);

                $countAmounts = 0;
                $ahhahaah = "";
                for($x_spesa=1;$x_spesa<count($spese_array)+1;$x_spesa++)
                {
                    if($spese_array[$x_spesa]['tipo_totale']==1)
                    {
                        $query_tariffa = "SELECT Descrizione FROM tariffe_coazione WHERE ID = '".$spese_array[$x_spesa]['ID']."'";
                        $descrizione_tariffa = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query_tariffa),"tariffe_coazione")["Descrizione"];
                        $ahhahaah = $descrizione_tariffa."\n";
                        
                        $this->a_amounts['single'][1][$countAmounts]['operator'] = "+";
                        $this->a_amounts['single'][1][$countAmounts]['label'] = $descrizione_tariffa;
                        $this->a_amounts['single'][1][$countAmounts]['amount'] = number_format($spese_array[$x_spesa]['rimborso'],2,",",".");

                        $countAmounts++;
                    }
                }

                //var_dump($ahhahaah);die;

                $this->a_amounts['single'][1][$countAmounts]['operator'] = "+";
                $this->a_amounts['single'][1][$countAmounts]['label'] = "Spese postali/diritti di notifica";
                $this->a_amounts['single'][1][$countAmounts]['amount'] = number_format($this->a_result["Totale_Spese_Notifica"],2,",",".");

                $partial_1 = $this->a_result["Totale_Spese_Notifica"] + $Debito_Precedente;
                $this->a_amounts['total'][1][0]['operator'] = "=";
                $this->a_amounts['total'][1][0]['label'] = "TOTALE 1";
                $this->a_amounts['total'][1][0]['amount'] = number_format($TOTALI_ARRAY["spese_accessorie"][1] + $partial_1,2,",",".");

                $countAmounts = 0;
                if($TOTALI_ARRAY[2]!=0)
                {
                    for($x_spesa=1;$x_spesa<count($spese_array)+1;$x_spesa++)
                    {
                        if($spese_array[$x_spesa]['tipo_totale']==2)
                        {
                            $query_tariffa = "SELECT Descrizione FROM tariffe_coazione WHERE ID = '".$spese_array[$x_spesa]['ID']."'";
                            $descrizione_tariffa = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query_tariffa),"tariffe_coazione")["Descrizione"];

                            $this->a_amounts['single'][2][$countAmounts]['operator'] = "+";
                            $this->a_amounts['single'][2][$countAmounts]['label'] = $descrizione_tariffa;
                            $this->a_amounts['single'][2][$countAmounts]['amount'] = number_format($spese_array[$x_spesa]['rimborso'],2,",",".");

                            $countAmounts++;
                        }
                    }

                    $this->a_amounts['total'][2][0]['operator'] = "=";
                    $this->a_amounts['total'][2][0]['label'] = "TOTALE 2";
                    $this->a_amounts['total'][2][0]['amount'] = number_format($TOTALI_ARRAY["spese_accessorie"][2] + $partial_1,2,",",".");
                }

                $countAmounts = 0;
                if($TOTALI_ARRAY[3]!=0)
                {
                    for($x_spesa=1;$x_spesa<count($spese_array)+1;$x_spesa++)
                    {
                        if($spese_array[$x_spesa]['tipo_totale']==3)
                        {
                            $query_tariffa = "SELECT Descrizione FROM tariffe_coazione WHERE ID = '".$spese_array[$x_spesa]['ID']."'";
                            $descrizione_tariffa = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query_tariffa),"tariffe_coazione")["Descrizione"];

                            $this->a_amounts['single'][3][$countAmounts]['operator'] = "+";
                            $this->a_amounts['single'][3][$countAmounts]['label'] = $descrizione_tariffa;
                            $this->a_amounts['single'][3][$countAmounts]['amount'] = number_format($spese_array[$x_spesa]['rimborso'],2,",",".");

                            $countAmounts++;
                        }
                    }

                    $this->a_amounts['total'][3][0]['operator'] = "=";
                    $this->a_amounts['total'][3][0]['label'] = "TOTALE 3";
                    $this->a_amounts['total'][3][0]['amount'] = number_format($TOTALI_ARRAY["spese_accessorie"][3] + $partial_1,2,",",".");
                }

                break;

            default:
                $this->a_amounts['total'] = array();
                $this->a_amounts['single'] = array();

                break;
        }
    }

    public function totalePignoramento(){
        
        if(count($this->a_amounts['total']) > 0)
            return $this->a_amounts['total'][count($this->a_amounts['total'])-1][0]['amount'];
        else
            return "0,00";
    }

    public function totalePignoramentoAlfabetico(){
        
        if(count($this->a_amounts['total']) > 0){
            $this->cls_convert_number->resetSeparator(",",".",true,"/");
            $this->cls_convert_number->setNumber($this->a_amounts['total'][count($this->a_amounts['total'])-1][0]['amount']);
            return $this->cls_convert_number->getNumber();
        }   
        else
            return "zero/00";
    }

    public function setDocAmounts($documentTypeId, $a_yearParams = null, $tipo = "atto"){
        $this->getTributeCodesForPrint();
        //var_dump($docType);
        //echo "<h1>second --> ".$a_yearParams."</h1>";

        if(!is_null($this->a_result) && $this->a_result['Tipo_Riscossione']=="CDS"){
            $str_interessi = "Maggiorazione del 10% semestrale";
            if(!is_null($this->a_result['Partita_Data_Decorrenza'])){
                $a_date = explode("-",$this->a_result['Partita_Data_Decorrenza']);
                if(count($a_date)==3)
                    $str_interessi.= " calcolata dal ".$a_date[2]."/".$a_date[1]."/".$a_date[0];
            }
        }
        else
            $str_interessi = "Interessi";

        $countAmounts = 0;
        $codiciPayment = 0.00;
        $total = 0;
        $this->a_amounts = null;

        if($tipo == "atto")
        {
            foreach ($this->a_annualTribute as $codice=>$a_codice){
                if($a_codice['Scorporo_ID']==0){
                    $codiciPayment+= $a_codice['Totale'];
                }
                else{
                    if($a_codice['Totale']>0.00){
                        $total += $a_codice['Totale'];

                        $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                        $this->a_amounts['single'][$countAmounts]['label'] = $a_codice['Testo'];
                        $this->a_amounts['single'][$countAmounts]['amount'] = number_format($a_codice['Totale'],2,",",".");

                        $countAmounts++;
                    }

                }
            }
        }

        if($tipo == "atto")
            $tot_pagamenti = $this->a_result['Totale_Pagato']+$codiciPayment;

        if($a_yearParams==null){
            $a_oneri['perc_min'] = 3;
            $a_oneri['perc_max'] = 6;
            $a_oneri['days_limit'] = 60;
        }
        else{
            $a_oneri['perc_min'] = $a_yearParams['Diritto_Riscossione_Minimo'];
            $a_oneri['perc_max'] = $a_yearParams['Diritto_Riscossione_Massimo'];
            $a_oneri['days_limit'] = $a_yearParams['Giorni_Diritto'];
        }

        //echo "<h1>Tipo = ".$docType."</h1>";


        switch($documentTypeId){
            case 11:
                if($this->a_result['Spese_Notifica_Precedenti']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Spese postali/notifica/ricerca dei precedenti atti di accertamento";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Spese_Notifica_Precedenti'],2,",",".");
                    $total += $this->a_result['Spese_Notifica_Precedenti'];
                    $countAmounts++;
                }
                if($this->a_result['Spese_Notifica']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Spese postali/notifica del presente Sollecito di pagamento";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Spese_Notifica'],2,",",".");
                    $total += $this->a_result['Spese_Notifica'];
                    $countAmounts++;
                }
                if($tot_pagamenti>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "-";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Pagamenti dei precedenti atti di accertamento";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($tot_pagamenti,2,",",".");
                    $total -= $tot_pagamenti;
                }

                $this->a_amounts['total'][0]['operator'] = "=";
                $this->a_amounts['total'][0]['label'] = "Differenza da versare";
                $this->a_amounts['total'][0]['amount'] = number_format($total,2,",",".");
                break;

            case 12:
                if($this->a_result['Spese_Notifica_Precedenti']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Spese postali/notifica/ricerca dei precedenti atti di accertamento";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Spese_Notifica_Precedenti'],2,",",".");
                    $total += $this->a_result['Spese_Notifica_Precedenti'];
                    $countAmounts++;
                }
                if($this->a_result['Interessi']>0 || $this->a_result['Interessi_Precedenti']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Interessi";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Interessi']+$this->a_result['Interessi_Precedenti'],2,",",".");
                    $total += $this->a_result['Spese_Notifica'];
                    $countAmounts++;
                }
                if($this->a_result['Spese_Notifica']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Spese postali/notifica del presente Avviso di messa in mora";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Spese_Notifica'],2,",",".");
                    $total += $this->a_result['Spese_Notifica'];
                    $countAmounts++;
                }
                if($tot_pagamenti>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "-";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Pagamenti dei precedenti atti di accertamento";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($tot_pagamenti,2,",",".");
                    $total -= $tot_pagamenti;
                }

                $this->a_amounts['total'][0]['operator'] = "=";
                $this->a_amounts['total'][0]['label'] = "Totale dovuto";
                $this->a_amounts['total'][0]['amount'] = number_format($total,2,",",".");
                break;

            case 2:
                if($this->a_result['Spese_Notifica_Precedenti']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Spese postali/notifica/ricerca dei precedenti atti di accertamento";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Spese_Notifica_Precedenti'],2,",",".");
                    $total += $this->a_result['Spese_Notifica_Precedenti'];
                    $countAmounts++;
                }

                if($this->a_result['Spese_Notifica_Pignoramento']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Spese notifica dei precedenti pignoramenti";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Spese_Notifica_Pignoramento'],2,",",".");
                    $total += $this->a_result['Spese_Notifica_Pignoramento'];
                    $countAmounts++;
                }

                if($this->a_result['Spese_Accessorie_Pignoramento']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Spese accessorie dei precedenti pignoramenti";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Spese_Accessorie_Pignoramento'],2,",",".");
                    $total += $this->a_result['Spese_Accessorie_Pignoramento'];
                    $countAmounts++;
                }

                if($this->a_result['Interessi']>0 || $this->a_result['Interessi_Precedenti']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = $str_interessi;
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Interessi']+$this->a_result['Interessi_Precedenti'],2,",",".");
                    $total += $this->a_result['Interessi']+$this->a_result['Interessi_Precedenti'];
                    $countAmounts++;
                }

                if($this->a_result['Spese_Notifica']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Spese postali/notifica della presente Ingiunzione";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Spese_Notifica'],2,",",".");
                    $total += $this->a_result['Spese_Notifica'];
                    $countAmounts++;
                }

                if($tot_pagamenti>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "-";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Pagamenti dei precedenti atti di accertamento";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($tot_pagamenti,2,",",".");
                    $total -= $tot_pagamenti;
                }

                if($this->a_result['Diritto_Riscossione_Minimo']>0){
                    $this->a_amounts['total'][0]['operator'] = "=";
                    $this->a_amounts['total'][0]['label'] = "TOTALE COMPLESSIVO (1) [Oneri di Riscossione al ".$a_oneri['perc_min']."% pagamento entro ".$a_oneri['days_limit']." giorni]";
                    $this->a_amounts['total'][0]['amount'] = number_format($total+$this->a_result['Diritto_Riscossione_Minimo'],2,",",".");

                    $this->a_amounts['total'][1]['operator'] = "=";
                    $this->a_amounts['total'][1]['label'] = "TOTALE COMPLESSIVO (2) [Oneri di Riscossione al ".$a_oneri['perc_max']."% pagamento oltre ".$a_oneri['days_limit']." giorni]";
                    $this->a_amounts['total'][1]['amount'] = number_format($total+$this->a_result['Diritto_Riscossione_Massimo'],2,",",".");
                }
                else{
                    $this->a_amounts['total'][0]['operator'] = "=";
                    $this->a_amounts['total'][0]['label'] = "Totale dovuto";
                    $this->a_amounts['total'][0]['amount'] = number_format($total,2,",",".");
                }

                break;

            case 6:
            case 7:
            case 8:
            case 22:
                $countAmounts = 0;
                $query = "SELECT * FROM pignoramento_spese WHERE Pignoramento_ID = ".$this->a_result['ID']." AND CC = '".$this->a_result['CC']."'";
                $spese = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"pignoramento_spese");

                $query = "SELECT * FROM pignoramento_generale WHERE ID = ".$this->a_result['ID']." AND CC = '".$this->a_result['CC']."'";
                $atto_pignoramento = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"atto");
                $pagamenti_atto = number_format($this->cls_stp->totale_pagamenti($atto_pignoramento),2,",","");

                $spese_array = $this->cls_stp->spese_array($spese);
                $TOTALI_ARRAY = $this->cls_stp->totali_spese($spese);

                $this->a_amounts['single'][0][$countAmounts]['operator'] = "";
                $this->a_amounts['single'][0][$countAmounts]['label'] = "Ripresa debito precedente";
                $this->a_amounts['single'][0][$countAmounts]['amount'] = number_format($this->a_result['Importo_Atto'],2,",","");
                $countAmounts++;

                if($this->a_result['Interessi']>0){
                    if(!is_null($this->a_result) && $this->a_result['Tipo_Riscossione']=="CDS")
                        $str_interessi_pigno = "Ripresa Maggiorazione del 10% semestrale";
                    else
                        $str_interessi_pigno = "Nuovi Interessi";

                    $this->a_amounts['single'][0][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][0][$countAmounts]['label'] = $str_interessi_pigno;
                    $this->a_amounts['single'][0][$countAmounts]['amount'] = number_format($this->a_result['Interessi'],2,",","");
                }

                

                // $this->a_amounts['single'][0][$countAmounts]['operator'] = "";
                // $this->a_amounts['single'][0][$countAmounts]['label'] = "Ripresa debito precedente";
                // $this->a_amounts['single'][0][$countAmounts]['amount'] = number_format($this->a_result['Importo_Dovuto'],2,",","");
                // $countAmounts++;


                // $this->a_amounts['single'][0][$countAmounts]['operator'] = "-";
                // $this->a_amounts['single'][0][$countAmounts]['label'] = "Eventuale importo pagato successivamente alla notifica degli atti ingiuntivi e intimativi";
                // $this->a_amounts['single'][0][$countAmounts]['amount'] = $pagamenti_atto;

                $Debito_Precedente = (float) $this->a_result["Importo_Dovuto"];
                $this->a_amounts['total'][0][0]['operator'] = "=";
                $this->a_amounts['total'][0][0]['label'] = "Totale debito precedente";
                $this->a_amounts['total'][0][0]['amount'] = number_format($Debito_Precedente,2,",",".");
                //($this->a_amounts['single']);

                $countAmounts = 0;
                for($x_spesa=1;$x_spesa<count($spese_array)+1;$x_spesa++)
                {
                    if($spese_array[$x_spesa]['tipo_totale']==1)
                    {
                        $query_tariffa = "SELECT Descrizione FROM tariffe_coazione WHERE ID = '".$spese_array[$x_spesa]['ID']."'";
                        $descrizione_tariffa = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query_tariffa),"tariffe_coazione")["Descrizione"];

                        $this->a_amounts['single'][1][$countAmounts]['operator'] = "+";
                        $this->a_amounts['single'][1][$countAmounts]['label'] = $descrizione_tariffa;
                        $this->a_amounts['single'][1][$countAmounts]['amount'] = number_format($spese_array[$x_spesa]['rimborso'],2,",",".");

                        $countAmounts++;
                    }
                }

                $this->a_amounts['single'][1][$countAmounts]['operator'] = "+";
                $this->a_amounts['single'][1][$countAmounts]['label'] = "Spese postali/diritti di notifica";
                $this->a_amounts['single'][1][$countAmounts]['amount'] = number_format($this->a_result["Totale_Spese_Notifica"],2,",",".");

                $partial_1 = $this->a_result["Totale_Spese_Notifica"] + $Debito_Precedente;
                $this->a_amounts['total'][1][0]['operator'] = "=";
                $this->a_amounts['total'][1][0]['label'] = "TOTALE 1";
                $this->a_amounts['total'][1][0]['amount'] = number_format($TOTALI_ARRAY["spese_accessorie"][1] + $partial_1,2,",",".");

                $countAmounts = 0;
                if($TOTALI_ARRAY[2]!=0)
                {
                    for($x_spesa=1;$x_spesa<count($spese_array)+1;$x_spesa++)
                    {
                        if($spese_array[$x_spesa]['tipo_totale']==2)
                        {
                            $query_tariffa = "SELECT Descrizione FROM tariffe_coazione WHERE ID = '".$spese_array[$x_spesa]['ID']."'";
                            $descrizione_tariffa = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query_tariffa),"tariffe_coazione")["Descrizione"];

                            $this->a_amounts['single'][2][$countAmounts]['operator'] = "+";
                            $this->a_amounts['single'][2][$countAmounts]['label'] = $descrizione_tariffa;
                            $this->a_amounts['single'][2][$countAmounts]['amount'] = number_format($spese_array[$x_spesa]['rimborso'],2,",",".");

                            $countAmounts++;
                        }
                    }

                    $this->a_amounts['total'][2][0]['operator'] = "=";
                    $this->a_amounts['total'][2][0]['label'] = "TOTALE 2";
                    $this->a_amounts['total'][2][0]['amount'] = number_format($TOTALI_ARRAY["spese_accessorie"][2] + $partial_1,2,",",".");
                }

                $countAmounts = 0;
                if($TOTALI_ARRAY[3]!=0)
                {
                    for($x_spesa=1;$x_spesa<count($spese_array)+1;$x_spesa++)
                    {
                        if($spese_array[$x_spesa]['tipo_totale']==3)
                        {
                            $query_tariffa = "SELECT Descrizione FROM tariffe_coazione WHERE ID = '".$spese_array[$x_spesa]['ID']."'";
                            $descrizione_tariffa = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query_tariffa),"tariffe_coazione")["Descrizione"];

                            $this->a_amounts['single'][3][$countAmounts]['operator'] = "+";
                            $this->a_amounts['single'][3][$countAmounts]['label'] = $descrizione_tariffa;
                            $this->a_amounts['single'][3][$countAmounts]['amount'] = number_format($spese_array[$x_spesa]['rimborso'],2,",",".");

                            $countAmounts++;
                        }
                    }

                    $this->a_amounts['total'][3][0]['operator'] = "=";
                    $this->a_amounts['total'][3][0]['label'] = "TOTALE 3";
                    $this->a_amounts['total'][3][0]['amount'] = number_format($TOTALI_ARRAY["spese_accessorie"][3] + $partial_1,2,",",".");
                }

                break;

            default:
                $this->a_amounts['total'] = array();
                $this->a_amounts['single'] = array();

                break;
        }
    }

    public function getDocCompletePath($cc, $atto, $finalDate = null){
        $path = $cc."/".$this->a_docDetails['dirName']."/STAMPE DEFINITIVE/";

        $filename= $this->a_docDetails['finalFileName']."_".$cc."_";
        $filename.= $atto["Anno_Cronologico"]."_".$atto["ID_Cronologico"]."_";

        if(is_null($finalDate))
            $filename.=  $atto["Data_Stampa"].".pdf";
        else
            $filename.=  $finalDate.".pdf";


        $a_path['name'] = $filename; 
        $a_path['root'] = ATTI."/".$path.$a_path['name'];
        $a_path['web'] = ATTI_WEB."/".$path.$a_path['name'];

        return $a_path;
    }
    





    public function getTypeDetails($type, $printTypeId=null, $officialType=null, $a_params = array()){
        $a_type = array();

        foreach($a_params as $key => $value)
        {
            $a_type[$key] = $value;
        }

        $a_type['PrintTypeId'] = (int) $printTypeId;
        $a_type['officialType'] = $officialType;
        switch($type){
            case "SOLL_PRE":
                $a_type['textTable'] = "text_sollecito_pre_ingiunzione";
                $a_type['dirName'] = "Solleciti_Pre_Ingiunzione";
                $a_type['tempFileName'] = "sollecitiPreIngiunzione";
                $a_type['finalFileName'] = "sollecitoPreIngiunzione";
                $a_type['title'] = "Solleciti pre ingiunzione";
                $a_type['docType'] = "Sollecito pre ingiunzione";
                $a_type['type'] = "SOLL_PRE";
                $a_type['DocumentTypeId'] = 11;
                break;
            case "AV_MORA":
                $a_type['textTable'] = "text_avviso_messa_in_mora";
                $a_type['dirName'] = "Avvisi_Messa_In_Mora";
                $a_type['tempFileName'] = "avvisiMessaInMora";
                $a_type['finalFileName'] = "avvisoMessaInMora";
                $a_type['title'] = "Avvisi di messa in mora";
                $a_type['docType'] = "Avviso di messa in mora";
                $a_type['type'] = "AV_MORA";
                $a_type['DocumentTypeId'] = 12;
                break;

            case "ING":
            case "SCORPORO_ING":
                $a_type['textTable'] = "";
                $a_type['dirName'] = "Ingiunzioni";
                $a_type['tempFileName'] = "Ingiunzioni";
                $a_type['finalFileName'] = "Ingiunzione";
                $a_type['title'] = "Ingiunzioni";
                $a_type['docType'] = "Ingiunzione";
                $a_type['type'] = "INGIUNZIONE";
                $a_type['DocumentTypeId'] = 2;

                break;

            case "SOLL_POST":
                $a_type['textTable'] = "";
                $a_type['dirName'] = "Solleciti";
                $a_type['tempFileName'] = "sollecitiPostIngiunzione";
                $a_type['finalFileName'] = "sollecitoPostIngiunzione";
                $a_type['title'] = "Solleciti post ingiunzione";
                $a_type['docType'] = "Sollecito di pagamento";
                $a_type['type'] = "SOLL_POST";
                $a_type['DocumentTypeId'] = 3;
                break;

            case "AV_INT":
                $a_type['textTable'] = "text_avviso_di_intimazione";
                $a_type['dirName'] = "Avvisi_di_intimazione";
                $a_type['tempFileName'] = "Avvisi_di_intimazione";
                $a_type['finalFileName'] = "Avviso_di_intimazione";
                $a_type['title'] = "Avviso di intimazione";
                $a_type['docType'] = "Avviso di intimazione";
                $a_type['type'] = "AV_INT";
                $a_type['DocumentTypeId'] = 4;

                break;

            case "veicolo":
                $a_type['textTable'] = "";
                $a_type['dirName'] = "Veicolo";
                $a_type['tempFileName'] = "PignoramentiVeicoli";
                $a_type['finalFileName'] = "PignoramentoVeicolo";
                $a_type['title'] = "Pignoramento veicolo";
                $a_type['docType'] = "veicolo";
                $a_type['type'] = "veicolo";
                $a_type['DocumentTypeId'] = 6;

                break;

            case "lavoro":
                $a_type['textTable'] = "";
                $a_type['dirName'] = "Datore_di_Lavoro";
                $a_type['tempFileName'] = "PignoramentiLavori";
                $a_type['finalFileName'] = "PignoramentoLavoro";
                $a_type['title'] = "Pignoramento datore di lavoro";
                $a_type['docType'] = "lavoro";
                $a_type['type'] = "lavoro";
                $a_type['DocumentTypeId'] = 7;

                break;

            case "banca":
                $a_type['textTable'] = "";
                $a_type['dirName'] = "Banca";
                $a_type['tempFileName'] = "PignoramentiBanche";
                $a_type['finalFileName'] = "Pignoramento_presso_banca_";
                $a_type['title'] = "Pignoramento presso banca";
                $a_type['docType'] = "banca";
                $a_type['type'] = "banca";
                $a_type['DocumentTypeId'] = 8;

                break;

            case "preav_fermo":
                $a_type['textTable'] = "";
                $a_type['dirName'] = "Preavviso_Fermo";
                $a_type['tempFileName'] = "PignoramentiFermo";
                $a_type['finalFileName'] = "Preavviso_fermo_";
                $a_type['title'] = "Preavviso fermo";
                $a_type['docType'] = "preav_fermo";
                $a_type['type'] = "preav_fermo";
                $a_type['DocumentTypeId'] = 22;

                break;

        }
        $this->a_docDetails = $a_type;
    }

    public function getPostalClient($id_ente, $rata=1, $Type = "atto"){
        $postalClient = "";

        for($i=0; $i< 3-strlen($id_ente) ;$i++)
            $postalClient .= "0";
        $postalClient .= $id_ente;

        switch($Type)
        {
            case "atto": $docType = $this->a_result['Atto'];break;
            case "pigno": $docType = $this->a_result['Nome_Pignoramento']; break;
            default: $docType = "";
        }

        //TIPO SERVIZIO + RISCOSSIONE 2 CIFRE
        switch($docType)
        {
            case "Sollecito pre ingiunzione":				$postalClient.="11";	break;
            case "Avviso di messa in mora":				    $postalClient.="12";	break;
            case "Ingiunzione":								$postalClient.="02";	break;
            case "Sollecito di pagamento":					$postalClient.="03";	break;
            case "Avviso di intimazione ad adempiere":		$postalClient.="04";	break;
            case "Sollecito avviso di intimazione":			$postalClient.="05";	break;
            case "Pignoramento di beni mobili registrati":	$postalClient.="06";	break;
            case "Pignoramento presso datore di lavoro":	$postalClient.="07";	break;
        }

        //NUMERO RATA 2 CIFRE
        for($i=0; $i< 2-strlen($rata) ;$i++)
            $postalClient .= "0";
        $postalClient .= $rata;

        //ANNO 2 CIFRE
        $cfr_anno = str_split($this->a_result['Anno_Cronologico']);
        if(count($cfr_anno)>2)
            $anno = $cfr_anno[2].$cfr_anno[3];
        else
            $anno = "00";
        $postalClient .= $anno;

        //ATTO 7 CIFRE
        for($i=0; $i< 7-strlen($this->a_result['ID_Cronologico']) ;$i++)
            $postalClient .= "0";
        $postalClient .= $this->a_result['ID_Cronologico'];

        //COD POSTA 2 CIFRE
        $cod_posta = fmod($postalClient,93);
        for($i=0; $i< 2-strlen($cod_posta) ;$i++)
            $postalClient .= "0";
        $postalClient .= $cod_posta;

        return $postalClient;
    }

    public function getSendType($address){
        $sendType = "";
        switch($this->a_result['Modalita_Stampa']){
            case "posta":
                $sendType = "in ".$address." tramite posta.";
                break;
            case "mani":
                $sendType = "in ".$address." mediante consegna a mani.";
                break;
            case "PEC":
                $sendType = "al seguente indirizzo di posta elettronica certificata ".$this->a_result['Utente_PEC']." ai sensi di legge.";
                break;
            case "ordinaria":
                $sendType = "in ".$address." tramite posta ordinaria.";
                break;
            case "raccomandata":
                $sendType = "in ".$address." mediante raccomandata ordinaria.";
                break;
        }
        return $sendType;
    }

    public function getTaxType($type){
        switch($type){
            case "CDS":
                //$taxType = "sanzioni amministrative";                                 // vecchia dicitura
                $taxType = "Contrav.cod.strada l.689/81";                               // nuova dicitura su modello LAB
                break;
            case "PUBBLICITA":
                //$taxType = "imposte sulla pubblicita'";                               // vecchia dicitura
                $taxType = "imposta comunale sulla pubblicita'";                        // nuova dicitura su modello LAB
                break;
            case "RIFIUTI":
                $taxType = "imposte sui rifiuti";
                break;
            case "OSAP":
                $taxType = "imposte sull'occupazione del suolo pubblico";
                break;
            case "IMMOBILI":
                //$taxType = "imposte sugli immobili";                                  // vecchia dicitura
                $taxType = "Imposta Municipale Unica";                                  // nuova dicitura su modello LAB
                break;
            case "PATRIMONIALE":
                $taxType = "imposte patrimoniali";
                break;
            default:
                $taxType = "SCONOSCIUTA";

        }
        return $taxType;
    }

    public function checkInstalment($cls_help, array $a_instalment){

        
        $a_return = array();
//        print_r($a_instalment);
        if ($a_instalment['instalmentNumber'] > 0) {
            $a_return['instalment'] = true;
            $a_instalment['instalmentAmounts'] = explode("*",$a_instalment['instalmentAmounts']);
            $a_instalment['instalmentExpires'] = explode("*",$a_instalment['instalmentExpires']);
            $tot_dovuto = 0;
            for($i=0;$i<count($a_instalment['instalmentAmounts']);$i++){
                $tot_dovuto+= $cls_help->stringToFloat($a_instalment['instalmentAmounts'][$i]);
            }

            $query = "SELECT SUM(Importo) AS Tot_Pagato_Rate, COUNT(Importo) AS Rate_Pagate  FROM pagamento WHERE Atto_ID=".$a_instalment['Atto_ID']." AND DocumentTypeId=".$a_instalment['DocumentTypeId']." GROUP BY Atto_ID";
            $a_rate = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
            if($a_rate==null){
                $a_rate['Tot_Pagato_Rate'] = 0;
                $a_rate['Rate_Pagate'] = 0;
            }

            if ($tot_dovuto - $a_rate['Tot_Pagato_Rate'] > $a_instalment['minAmount']) {
                //var_dump($a_instalment); die;
                $ratePagate = $a_rate['Rate_Pagate'];
                if($ratePagate > 0){
                    if(isset($a_instalment['instalmentExpires'][$ratePagate-1])){
                        $data_rata = new DateTime($cls_help->toDbDate($a_instalment['instalmentExpires'][$ratePagate-1]));
                        $data_rata->modify("+3 months");

                        if (date('Y-m-d') > $data_rata->format('Y-m-d')){
                            $a_return['status'] = "expired";
                            $a_return['instalment_date'] = $a_instalment['instalmentExpires'][$ratePagate-1];
                            $a_return['instalment_amount'] = $a_instalment['instalmentAmounts'][$ratePagate-1];
                            $a_return['last_instalment'] = $ratePagate;
                        }
                        else{
                            $a_return['status'] = "ongoing";
                        }
                    }
                    else{
                        /** questa sarebbe una casistica di errore però nella stampa è meglio mostrare più che un errore la rateizzazione come assente **/
                        $a_return['instalment'] = false;
                    }
                }
                else {
                    $a_return['instalment'] = false;
                }
            }
            else{
                $a_return['status'] = "completed";

            }

        }
        else{
            $a_return['instalment'] = false;
        }

//        print_r($a_return);
        return $a_return;

    }
}


?>