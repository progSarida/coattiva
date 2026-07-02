<?php
include_once CLS."/cls_db.php";
include_once CLS."/cls_Stampe.php";

class cls_ruolo{

    public $a_result;
    public $a_codiciTributo;
    public $a_amounts;
    public $a_docDetails;

    public $a_instalment;

    private $cls_db;
    private $cls_stp;

    public function __construct()
    {
        $this->cls_db = new cls_db();
        $this->cls_stp = new cls_Stampe();
    }

    public function getPrevQueryItemID($id, $c, $a){
        if($id>0)
            $idPrevWhere = "ID < ".$id." AND ";
        else
            $idPrevWhere = " ";

        return "SELECT ID FROM partita_tributi WHERE ".$idPrevWhere." CC='".$c."' AND Anno_Riferimento='".$a."' ORDER BY ID DESC LIMIT 1";
    }

    public function getNextQueryItemID($id, $c, $a){
        if($id>0)
            $idPrevWhere = "ID > ".$id." AND ";
        else
            $idPrevWhere = " ";

        return "SELECT ID FROM partita_tributi WHERE ".$idPrevWhere." CC='".$c."' AND Anno_Riferimento='".$a."' ORDER BY ID ASC LIMIT 1";
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
        $query = "SELECT * FROM partita_tributi WHERE CC = '".$cc."' ORDER BY Comune_ID DESC LIMIT 1";
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

        for($i=0;$i<count($a_codiciTributo['Codice']);$i++){
            $this->a_codiciTributo[$i]['Codice'] = $a_codiciTributo['Codice'][$i];
            $this->a_codiciTributo[$i]['Importo'] = $a_codiciTributo['Importo'][$i];
            $this->a_codiciTributo[$i]['Testo'] = $a_codiciTributo['Testo'][$i];
            $this->a_codiciTributo[$i]['Scorporo_ID'] = $a_codiciTributo['Scorporo_ID'][$i];
            if(isset($a_codiciTributo['Categoria'][$i]))
                $this->a_codiciTributo[$i]['Categoria'] = $a_codiciTributo['Categoria'][$i];
            else
                $this->a_codiciTributo[$i]['Categoria'] = "";
        }
    }

    public function setPrintAmounts($docType, $a_yearParams = null, $tipo = "atto"){

        //var_dump($docType);
        //echo "<h1>second --> ".$a_yearParams."</h1>";
        $countAmounts = 0;
        $codiciPayment = 0.00;
        $total = 0;
        $this->a_amounts = null;

        if($tipo == "atto")
        {
            for($y=0;$y<count($this->a_codiciTributo);$y++){
                if($this->a_codiciTributo[$y]['Scorporo_ID']==0){
                    $codiciPayment+= $this->a_codiciTributo[$y]['Importo'];
                }
                else{
                    if($this->a_codiciTributo[$y]['Importo']>0.00){
                        $total += $this->a_codiciTributo[$y]['Importo'];

                        $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                        $this->a_amounts['single'][$countAmounts]['label'] = $this->a_codiciTributo[$y]['Testo'];
                        $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_codiciTributo[$y]['Importo'],2,",",".");

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
                if($this->a_result['Interessi']>0){
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
                if($this->a_result['Interessi']>0){
                    $this->a_amounts['single'][$countAmounts]['operator'] = "+";
                    $this->a_amounts['single'][$countAmounts]['label'] = "Interessi";
                    $this->a_amounts['single'][$countAmounts]['amount'] = number_format($this->a_result['Interessi']+$this->a_result['Interessi_Precedenti'],2,",",".");
                    $total += $this->a_result['Spese_Notifica'];
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
                $this->a_amounts['single'][0][$countAmounts]['label'] = "Ripresa totale debito precedente";
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

    public function getHtmlAmounts($tipo = "atto"){

        $amountsTable = "";

        if($tipo == "atto")
        {
            $amountsTable = "<table cellpadding=\"2\">";
            for($i=0;$i<count($this->a_amounts['single']);$i++){
                $amountsTable.= "<tr><td style=\"width:86%;text_align=left;\">".$this->a_amounts['single'][$i]['label']."</td>";
                $amountsTable.= "<td style=\"width:2%; text_align:right;\">".$this->a_amounts['single'][$i]['operator']."</td>";
                $amountsTable.= "<td style=\"width:10%; text_align:right;\">".$this->a_amounts['single'][$i]['amount']."</td>";
                $amountsTable.= "<td style=\"width:2%; text_align:right;\">&euro;</td></tr>";
            }
            $amountsTable.= "</table><hr><table  cellpadding=\"2\">";
            for($i=0;$i<count($this->a_amounts['total']);$i++){
                $amountsTable.= "<tr><td style=\"width:86%;text_align=left;\">".$this->a_amounts['total'][$i]['label']."</td>";
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
                $amountsTable .= "
                                  <table cellpadding=\"2\">";
                for($i=0;$i<count($this->a_amounts['single'][$x]);$i++){
                    $amountsTable.= "<tr><td style='text_align=left;width:86%;'>".$this->a_amounts['single'][$x][$i]['label']."</td>";
                    $amountsTable.= "<td style='text_align:right;width:2%;'>".$this->a_amounts['single'][$x][$i]['operator']."</td>";
                    $amountsTable.= "<td style='text_align:right;width:10%;'>".$this->a_amounts['single'][$x][$i]['amount']."</td>";
                    $amountsTable.= "<td style='text_align:right;width:2%;'>&euro;</td></tr>";
                }
                $amountsTable.= "</table><hr><table cellpadding=\"2\">";
                for($i=0;$i<count($this->a_amounts['total'][$x]);$i++){
                    $amountsTable.= "<tr><td style='text_align=left;width:86%;'>".$this->a_amounts['total'][$x][$i]['label']."</td>";
                    $amountsTable.= "<td style='text_align:right;width:2%;'>".$this->a_amounts['total'][$x][$i]['operator']."</td>";
                    $amountsTable.= "<td style='text_align:right;width:10%;'>".$this->a_amounts['total'][$x][$i]['amount']."</td>";
                    $amountsTable.= "<td style='text_align:right;width:2%;'>&euro;</td></tr>";
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
                $amountsTable.= $this->a_amounts['single'][$i]['label']." ".$this->a_amounts['single'][$i]['operator']." ".$this->a_amounts['total'][$i]['amount']." €<br/>";
            }
            $amountsTable.= "<hr><br/>";
            for($i=0;$i<count($this->a_amounts['total']);$i++){
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
                $a_type['tempFileName'] = "ingiunzioni";
                $a_type['finalFileName'] = "ingiunzione";
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
                $taxType = "sanzioni amministrative";
                break;
            case "PUBBLICITA":
                $taxType = "imposte sulla pubblicita'";
                break;
            case "RIFIUTI":
                $taxType = "imposte sui rifiuti";
                break;
            case "OSAP":
                $taxType = "imposte sull'occupazione del suolo pubblico";
                break;
            case "IMMOBILI":
                $taxType = "imposte sugli immobili";
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

            if ($tot_dovuto - $a_instalment['totalPayed'] > $a_instalment['minAmount']) {

                $probRata = $tot_dovuto / $a_instalment['instalmentNumber'];
                $ratePagate = $a_instalment['totalPayed'] / $probRata;
                $ratePagate = round($ratePagate, 0);

                $data_rata = new DateTime($cls_help->toDbDate($a_instalment['instalmentExpires'][$ratePagate + 1]));
                $data_rata->modify("+3 months");

                if (date('Y-m-d') > $data_rata->format('Y-m-d')){
                    $a_return['status'] = "expired";
                    $a_return['instalment_date'] = $a_instalment['instalmentExpires'][$ratePagate+1];
                    $a_return['instalment_amount'] = $a_instalment['instalmentAmounts'][$ratePagate+1];
                }
                else{
                    $a_return['status'] = "ongoing";
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