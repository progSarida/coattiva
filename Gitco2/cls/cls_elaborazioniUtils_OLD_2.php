<?php

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/cls_math.php";
include_once CLS . "/cls_DateTime.php";
include_once CLS . "/cls_help.php";

class cls_elaborazioniUtils
{
    public $cls_db;
    public $cls_date;
    public $cls_math;
    public $cls_help;


    public function __construct()
    {
        $this->cls_db = new cls_db();
        $this->cls_date = new cls_DateTimeI("IT",false);
        $this->cls_math = new cls_math();
        $this->cls_help = new cls_help();
    }

    function getFiltersDescription(array $filter){

        $a_return = array();
        $i=0;
        if(isset($filter['daco'])){
            if($filter['daco']!=null && $filter['daco']!=""){

                $a_return[$i]['label'] = "NOMINATIVO";
                $da = "Da ". $filter['daco']." ".$filter["dano"];
                $a = $filter["acog"]." ".$filter["anom"];
                if($filter['acog'] != "" || $filter["anom"] != "")
                    $a = " A ".$a;
                $a_return[$i]['value'] = $da.$a;

                $i++;
            }
        }

        if(isset($filter['da_partita'])){
            if($filter['da_partita']!=null && $filter['da_partita']!=""){

                $a_return[$i]['label'] = "PARTITA";
                $da = "Da ". $filter['da_partita'];
                $a = "";
                if(isset($filter["a_partita"]))
                    if($filter['a_partita']!=null && $filter['a_partita']!="")
                        $a = " A ". $filter["a_partita"];
                $a_return[$i]['value'] = $da.$a;

                $i++;
            }
        }

        if(isset($filter['descrizione_da']) && isset($filter["descrizione_a"])){

            if($filter['descrizione_da']!=null && $filter['descrizione_da']!=""){
                if(!isset($a_return[$i]['value']))
                    $a_return[$i]['value'] = "";
                $a_return[$i]['label'] = "DESCRIZIONE";
                $a_return[$i]['value'] .= "Da ". $filter['descrizione_da'];
            }
            if($filter['descrizione_a']!=null && $filter['descrizione_a']!=""){
                if(!isset($a_return[$i]['value']))
                    $a_return[$i]['value'] = "";

                $a_return[$i]['label'] = "DESCRIZIONE";
                $a_return[$i]['value'] .= " A ". $filter['descrizione_a'];
            }

            $i++;
        }
        else if(isset($filter['descrizione_da'])){
            if($filter['descrizione_da']!=null && $filter['descrizione_da']!=""){
                $a_return[$i]['label'] = "DESCRIZIONE";
                $a_return[$i]['value'] = "Da ". $filter['descrizione_da'];
            }

            $i++;
        }
        else{
            if($filter['descrizione_a']!=null && $filter['descrizione_a']!=""){
                $a_return[$i]['label'] = "DESCRIZIONE";
                $a_return[$i]['value'] = "Fino ". $filter['descrizione_a'];
            }

            $i++;
        }

        if(isset($filter['importo_da']) && isset($filter["importo_a"])){
            if($filter['importo_da']!=null && $filter['importo_da']!=""){
                if(!isset($a_return[$i]['value']))
                    $a_return[$i]['value'] = "";

                $a_return[$i]['label'] = "IMPORTO";
                $a_return[$i]['value'] .= "Da ". $filter['importo_da'];
            }
            if($filter['importo_a']!=null && $filter['importo_a']!=""){
                if(!isset($a_return[$i]['value']))
                    $a_return[$i]['value'] = "";

                $a_return[$i]['label'] = "IMPORTO";
                $a_return[$i]['value'] .= " A ". $filter['importo_a'];
            }

            $i++;
        }
        else if(isset($filter['importo_da'])){
            if($filter['importo_da']!=null && $filter['importo_da']!=""){
                $a_return[$i]['label'] = "IMPORTO";
                $a_return[$i]['value'] = "Da ". $filter['importo_da'];
            }

            $i++;
        }
        else{
            if($filter['importo_a']!=null && $filter['importo_a']!=""){
                $a_return[$i]['label'] = "IMPORTO";
                $a_return[$i]['value'] = "Fino A ". $filter['importo_a'];
            }

            $i++;
        }

        if(isset($filter['query_type'])){
            if($filter['query_type']!=null && $filter['query_type']!=""){

                $a_return[$i]['label'] = "QUERY";
                $a_return[$i]['value'] = $filter['query_type'];

                $i++;
            }
        }

        if(isset($filter['anno_flusso_da'])){
            if($filter['anno_flusso_da']!=null && $filter['anno_flusso_da']!=""){

                $a_return[$i]['label'] = "ANNO FLUSSO";
                $da = "Da ". $filter['anno_flusso_da'];
                $a = "";
                if(isset($filter["anno_flusso_a"]))
                    if($filter['anno_flusso_a']!=null && $filter['anno_flusso_a']!="")
                        $a = " A ". $filter["anno_flusso_a"];
                $a_return[$i]['value'] = $da.$a;

                $i++;
            }
        }

        if(isset($filter['anno_pagamento_da'])){
            if($filter['anno_pagamento_da']!=null && $filter['anno_pagamento_da']!=""){

                $a_return[$i]['label'] = "ANNO PAGAMENTI";
                $da = "Da ". $filter['anno_pagamento_da'];
                $a = "";
                if(isset($filter["anno_pagamento_a"]))
                    if($filter['anno_pagamento_a']!=null && $filter['anno_pagamento_a']!="")
                        $a = " A ". $filter["anno_pagamento_a"];
                $a_return[$i]['value'] = $da.$a;

                $i++;
            }
        }

        if(isset($filter['range_giorni'])){
            if($filter['range_giorni']!=null && $filter['range_giorni']!=""){

                $a_return[$i]['label'] = "N° GIORNI";
                $a_return[$i]['value'] = $filter['range_giorni'];

                $i++;
            }
        }

        if(isset($filter['tipo_entrata'])){
            if($filter['tipo_entrata']!=null && $filter['tipo_entrata']!=""){

                $a_return[$i]['label'] = "TIPO ENTRATA";
                $a_return[$i]['value'] = $filter['tipo_entrata'];

                $i++;
            }
        }

        if(isset($filter['parzTot'])){
            if($filter['parzTot']!=null && $filter['parzTot']!=""){

                $a_return[$i]['label'] = "PAGAMENTO";
                $a_return[$i]['value'] = $filter['parzTot'];

                $i++;
            }
        }

        if(isset($filter['CC'])){
            if($filter['CC']!=null && $filter['CC']!=""){

                $a_return[$i]['label'] = "CODICE CATASTALE";
                $a_return[$i]['value'] = $filter['CC'];

                $i++;
            }
        }

        if(isset($filter['da_data_notifica'])){
            if($filter['da_data_notifica']!=null && $filter['da_data_notifica']!=""){

                $a_return[$i]['label'] = "DATA NOTIFICA";
                $da = "Da ". $filter['da_data_notifica'];
                $a = "";
                if(isset($filter["a_data_notifica"]))
                    if($filter['a_data_notifica']!=null && $filter['a_data_notifica']!="")
                        $a = " A ". $filter["a_data_notifica"];
                $a_return[$i]['value'] = $da.$a;

                $i++;
            }
        }

        if(isset($filter['data_inserimento_da'])){

            $check = false;
            if($filter['data_inserimento_da']!=null && $filter['data_inserimento_da']!=""){
                $check=true;
                $da = "Da ". $this->cls_date->Get_DateNewFormat($filter['data_inserimento_da']) ;
                $a = "";
            }
            if(isset($filter["data_inserimento_a"]))
                if($filter['data_inserimento_a']!=null && $filter['data_inserimento_a']!="") {
                    $check=true;
                    $a = " Fino a " . $this->cls_date->Get_DateNewFormat($filter["data_inserimento_a"]);
                }

            if($check) {
                $a_return[$i]['label'] = "DATA NOTIFICA";
                $a_return[$i]['value'] = $da . $a;
                $i++;
            }
        }

        if(isset($filter['da_anno_riferimento'])){
            if($filter['da_anno_riferimento']!=null && $filter['da_anno_riferimento']!=""){

                $a_return[$i]['label'] = "ANNO RIFERIMENTO";
                $da = "Da ". $filter['da_anno_riferimento'];
                $a = "";
                if(isset($filter["a_anno_riferimento"]))
                    if($filter['a_anno_riferimento']!=null && $filter['a_anno_riferimento']!="")
                        $a = " A ". $filter["a_anno_riferimento"];
                $a_return[$i]['value'] = $da.$a;

                $i++;
            }
        }

        if(isset($filter['tipo_partita'])){
            if($filter['tipo_partita']!=null && $filter['tipo_partita']!=""){

                $a_return[$i]['label'] = "TIPO PARTITA";
                $a_return[$i]['value'] = $filter['tipo_partita'];

                $i++;
            }
        }

        if(isset($filter['data_elaborazione'])){
            if($filter['data_elaborazione']!=null && $filter['data_elaborazione']!=""){

                $a_return[$i]['label'] = "DATA ELABORAZIONE";
                $a_return[$i]['value'] = $filter['data_elaborazione'];

                $i++;
            }
        }

        if(isset($filter['stampatore'])){
            if($filter['stampatore']!=null && $filter['stampatore']!=""){

                $a_return[$i]['label'] = "STAMPATORE";
                $a_return[$i]['value'] = $filter['stampatore']==1?"Sarida":"Mercurio service";

                $i++;
            }
        }

        if(isset($filter['stato'])){
            if($filter['stato']!=null && $filter['stato']!=""){

                $a_return[$i]['label'] = "STATO";
                $a_return[$i]['value'] = $filter['stato'];

                $i++;
            }
        }

        if(isset($filter['anomalie'])){
            if($filter['anomalie']!=null && $filter['anomalie']!=""){

                $a_return[$i]['label'] = "ANOMALIE";
                $a_return[$i]['value'] = $filter['anomalie'];

                $i++;
            }
        }

        return $a_return;
    }

    function NotifImportataGiaPresente ($val)
    {
        $queryCerca = "SELECT ID FROM notifiche_importate ";
        $queryCerca .= "WHERE DocumentId = ".$val->DocumentId." AND DocumentTypeId = ".$val->DocumentTypeId." ";
        $queryCerca .= "AND CC_Comune = '" . $val->CC_Comune . "' AND Num_Viol = '" . $val->Num_Viol . "'";
        if($val->DocumentId>0){
            $return = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($queryCerca),"notifiche_importate")["ID"];//$rigaCerca['ID'];
            return $return;
        }
        else
            return null;

    }

    public function getDocumentFromCronoAtto ( $documentTypeId, $crono, $CC )
    {
        $cronologico = explode("/", $crono);

        $query = "SELECT atto.*, partita_tributi.Tipo AS Tipo_Partita ";
        $query.= "FROM atto JOIN partita_tributi ON partita_tributi.ID = atto.Partita_ID ";
        $query.= "WHERE atto.CC = '".$CC."' AND atto.DocumentTypeId = ".$documentTypeId." AND atto.ID_Cronologico=".$cronologico[0]." AND ";
        $query.= "atto.Anno_Cronologico = ".$cronologico[1]." AND partita_tributi.Is_Discharged=0";

        $array_result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
        //$array_result = mysql_fetch_array($result, MYSQL_ASSOC);
//        $array_result = mysql_fetch_row($result);
        return $array_result;

    }

    public function getDocumentFromCronoPigno ( $documentTypeId, $crono, $CC )
    {
        $cronologico = explode("/", $crono);

        $query = "SELECT * ";
        $query.= "FROM pignoramento_generale ";
        $query.= "WHERE CC = '".$CC."' AND DocumentTypeId = ".$documentTypeId." AND ID_Cronologico ='".$cronologico[0]."' AND ";
        $query.= "Anno_Cronologico = ".$cronologico[1]." ";

        //$result = mysql_query($query);
        //$array_result = mysql_fetch_array($result, MYSQL_ASSOC);
        $array_result = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));

        return $array_result;

    }

    public function getDataPartita($ID,$c,$AnnoRif)
    {
        $partita = new stdClass();
        $query = "SELECT * FROM partita_tributi WHERE ID = '".$ID."' AND CC = '".$c."' AND Anno_Riferimento = '".$AnnoRif."' AND Is_Discharged=0";
        $partita = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"partita_tributi");
        //$partita = new partita($array_partite[$k]['ID'], $c, $array_partite[$k]['Anno_Riferimento']);

        $query = "SELECT * FROM tributo WHERE Partita_ID = '".$ID."' AND CC = '".$c."' ORDER BY Codice_Tributo ASC";
        $partita->Tributo = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query),"object");

        $query = "SELECT * FROM pagamento WHERE Partita_ID = '".$partita->ID."'";
        $partita->Pagamento = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query),"object");//select_mysql_array("ID", "pagamento","Partita_ID = '".$this->ID."'");

        //print_r($partita->Tributo);
        for($i = 0; $i < count($partita->Tributo); $i++)
        {
            $query = "SELECT Tipo_Codice FROM codice_tributo WHERE Codice_Tributo = '".$partita->Tributo[$i]->Codice_Tributo."'";
            $partita->Tributo[$i]->Tipo_Codice = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"codice_tributo")["Tipo_Codice"];
            if($partita->Tributo[$i]->Tipo_Codice=="IMPORTO" && $this->cls_date->Get_DateNewFormat($partita->Tributo[$i]->Data_Decorrenza_Interessi,"DB")!=null)
                if(!isset($partita->Data_Inizio_Interessi))
                    $partita->Data_Inizio_Interessi = $partita->Tributo[$i]->Data_Decorrenza_Interessi;
        }


        //$atto_id = select_mysql_array("ID", "atto","Partita_ID = '".$this->ID."'");
        $query = "SELECT ID FROM atto WHERE Partita_ID = '".$ID."'";
        $atto_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

        $dataInizioInteressi = isset($partita->Data_Inizio_Interessi)?$partita->Data_Inizio_Interessi:null;
        $testoSemestri = "";
        $countGiri = 0;
        $partita->Somma_Spese_Notifica = 0;
        $partita->ultimo_atto = 0;

        for( $i=0; $i<count($atto_id); $i++)
        {
            $query = "SELECT * FROM atto WHERE ID = ".$atto_id[$i]['ID']." AND CC = '".$c."'";
            $partita->Atto[$i] = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"atto");// new atto( $atto_id[$i]['ID'] , $c );
            $partita->Atto[$i]->Scadenze_Rate = explode("*",utf8_decode($partita->Atto[$i]->Scadenze_Rate));

            $partita->Somma_Spese_Notifica += $partita->Atto[$i]->Spese_Notifica;
            $partita->Somma_Spese_Notifica += $partita->Atto[$i]->CAN;
            $partita->Somma_Spese_Notifica += $partita->Atto[$i]->CAD;

            $partita->Atto[$i]->Semestri = "";
            if($partita->Atto[$i]->Atto == "Ingiunzione" || $partita->Atto[$i]->Atto == "Avviso di intimazione ad adempiere" || $partita->Atto[$i]->Atto == "Avviso di messa in mora")
            {
                $partita->ultimo_atto = $atto_id[$i]['ID'];


                $data1 = new DateTime($dataInizioInteressi);
                $data2 = new DateTime($partita->Atto[$i]->Data_Calcolo_Interessi);
                $interval = $data1->diff($data2);
                $partita->Atto[$i]->Data_Inizio_Calcolo = $dataInizioInteressi;
                $semestri = floor($interval->format('%a')/182.5);
                if($partita->Atto[$i]->Interessi>0){
                    if($partita->Tipo == "CDS") {
                        if ($semestri <= 1)
                            $semestri = "1 semestre calcolato";
                        else
                            $semestri .= " semestri calcolati";
                    }
                    else{
                        $semestri = "Interesse calcolato";
                    }

                    if($countGiri>0)
                        $testoSemestri.= " + ";

                    $testoSemestri.= $semestri." dal ".$this->cls_date->Get_DateNewFormat($partita->Atto[$i]->Data_Inizio_Calcolo,"DB")." al ".$this->cls_date->Get_DateNewFormat($partita->Atto[$i]->Data_Calcolo_Interessi,"DB");

                    $countGiri++;
                }

                $partita->Atto[$i]->Semestri = $testoSemestri;

                $dataInizioInteressi = $partita->Atto[$i]->Data_Calcolo_Interessi;


                if($partita->Atto[$i]->Atto == "Ingiunzione")
                {
                    if( $partita->Atto[$i]->Data_Notifica < date("Y-m-d" , strtotime( date('Y-m-d')."-1 year" )) )
                        $partita->ultimo_atto_scaduto = $atto_id[$i]['ID'];
                }
                else
                {
                    $partita->ultimo_avviso = $atto_id[$i]['ID'];

                    if( $partita->Atto[$i]->Data_Notifica < date("Y-m-d" , strtotime( date('Y-m-d')."-180 days" )) )
                        $partita->ultimo_atto_scaduto = $atto_id[$i]['ID'];
                }
            }

            $partita->Atto_Not = $i+1;


            if($partita->Atto[$i]->Stato != "Annullata")
            {
                $partita->Atto_Calc = $i+1;
            }
        }

        return $partita;
    }

    public function calcola_interessi_tributi( $data_inizio, $data_fine, $importo, $val )
    {
        $data_inizio = $this->cls_date->GetDateDB($data_inizio,"IT");
        $data_fine = $this->cls_date->GetDateDB($data_fine,"IT");

        $interessi_array = array();

        if( $data_inizio!=null && $data_inizio!="0000-00-00" && $data_inizio!="" )
        {
            $j=0;

            for($i=0;$i<count($val->ID);$i++)
            {
                if($data_inizio >= $val->Data_Inizio[$i] && ($data_inizio <= $val->Data_Fine[$i] || $val->Data_Fine[$i]==null || $val->Data_Fine[$i]=="0000-00-00"))
                {
                    $interessi_array[$j]['Data_Partenza'] = $data_inizio;
                    $interessi_array[$j]['Tasso'] = $val->Tasso_Interessi[$i];

                    if($data_fine <= $val->Data_Fine[$i] || $val->Data_Fine[$i]==null || $val->Data_Fine[$i]=="0000-00-00")
                    {
                        $interessi_array[$j]['Data_Termine'] = $data_fine;
                        break;
                    }
                    else
                    {
                        $interessi_array[$j]['Data_Termine'] = $val->Data_Fine[$i];
                    }

                }
                else if($data_inizio < $val->Data_Inizio[$i] && $data_fine >= $val->Data_Inizio[$i])
                {
                    $j++;

                    $interessi_array[$j]['Data_Partenza'] = $val->Data_Inizio[$i];
                    $interessi_array[$j]['Tasso'] = $val->Tasso_Interessi[$i];

                    if($data_fine <= $val->Data_Fine[$i] || $val->Data_Fine[$i]==null)
                    {
                        $interessi_array[$j]['Data_Termine'] = $data_fine;
                        break;
                    }
                    else
                    {
                        $interessi_array[$j]['Data_Termine'] = $val->Data_Fine[$i];
                    }
                }

            }

            $contatoreInteressi = count($interessi_array);

            for($j=0;$j<$contatoreInteressi;$j++)
            {
                $interessi_array[$j]['Interesse_Giornaliero'] = $importo * $interessi_array[$j]['Tasso'] / 100 / 365;
                $interessi_array[$j]['Numero_Giorni'] = $this->calcola_giorni( $this->cls_date->Get_DateNewFormat($interessi_array[$j]['Data_Partenza'],"DB") , $this->cls_date->Get_DateNewFormat($interessi_array[$j]['Data_Termine'],"DB") );

                $interessi_array[$j]['Interesse_Parziale'] = number_format($interessi_array[$j]['Interesse_Giornaliero'] * $interessi_array[$j]['Numero_Giorni'],2);
            }
        }

        return $interessi_array;
    }

    public function totale_interessi_tributi($interessi_array)
    {
        $tot_interessi = 0;
        for($k=0;$k<count($interessi_array);$k++)
        {
            $tot_interessi+=$interessi_array[$k]['Interesse_Parziale'];
        }

        return $tot_interessi;
    }

    public function calcInterests($a_params){
//        var_dump($a_params);
//        echo "<br><br>";
        switch($a_params['CalcType']){
            case "CDS":
                return $this->calcCDSInterests($a_params);
            default:
                return $this->calcTributeInterests($a_params);
        }
    }

    public function calcBlockDays($a_params, $parStartDate = null, $parEndDate = null){
        if(is_null($parStartDate))
            $parStartDate = $a_params['StartDate'];
        if(is_null($parEndDate))
            $parEndDate = $a_params['EndDate'];
        if($a_params['CalcType']=="CDS")
            $checkLockupType = 3;
        else
            $checkLockupType = 2;

        $days = 0;

        foreach($a_params['a_blocks'] as $id=>$a_block){
            if($a_block['Lockup_Type_Id']==$checkLockupType || $parStartDate>$a_block['End_Date'] || $a_block['Start_Date']>$parEndDate)
                continue;

            if($a_block['Start_Date']>$parStartDate)
                $startDate = $a_block['Start_Date'];
            else
                $startDate = $parStartDate;

            if($a_block['End_Date']>$parEndDate)
                $endDate = $parEndDate;
            else
                $endDate = $a_block['End_Date'];

            $days+= $this->calcDays($startDate, $endDate);
        }
        return $days;
    }

    public function calcDays($startDate, $endDate){
        if(is_null($startDate) || is_null($endDate))
            return 0;

        $data1 = date_create($startDate);
        $data2 = date_create($endDate);
        $interval = date_diff($data1, $data2);
        return (int)$interval->format('%a');
    }

    public function calcCDSInterests($a_params, $a_calcParams = array("percentage"=>10, "months"=>6)){
        if(is_null($a_params['StartDate']) || is_null($a_params['EndDate']))
            return 0;

        $days = $this->calcDays($a_params['StartDate'], $a_params['EndDate']);
        $blockDays = $this->calcBlockDays($a_params);
        $semestri = floor( ($days-$blockDays) / ($a_calcParams['months']*30) );

        return round( abs($a_params['BaseAmount']) * $a_calcParams['percentage'] / 100 * $semestri , 2 );

    }

    public function calcTributeInterests($a_params){
        if(is_null($a_params['StartDate']) || is_null($a_params['EndDate']))
            return 0;

        $interessi = 0;
        foreach($a_params['a_interessiTributi'] as $ID=>$a_interessi){
            if($a_interessi['Data_Inizio']>$a_params['EndDate'] || (!is_null($a_interessi['Data_Fine']) && $a_interessi['Data_Fine']<$a_params['StartDate']))
                continue;

            if($a_interessi['Data_Inizio']>$a_params['StartDate'])
                $startDate = $a_interessi['Data_Inizio'];
            else
                $startDate = $a_params['StartDate'];

            if(is_null($a_interessi['Data_Fine']) || $a_interessi['Data_Fine']>$a_params['EndDate'])
                $endDate = $a_params['EndDate'];
            else
                $endDate = $a_interessi['Data_Fine'];

            $days = $this->calcDays($startDate, $endDate);
            $blockDays = $this->calcBlockDays($a_params, $startDate, $endDate);

            $dailyImport = $a_params['BaseAmount']/100*$a_interessi['Tasso_Interessi']/365;
            $a_days = array(
                "BaseAmount" => $a_params['BaseAmount'],
                "dataInizio" => $startDate,
                "dataFine" => $endDate,
                "days" => $days,
                "blockDays" => $blockDays,
                "calcDays" => $days-$blockDays,
                "interesse" => $a_interessi['Tasso_Interessi'],
                "importo_giornaliero" => $dailyImport,
                "importo_parziale" => $dailyImport*($days-$blockDays)
            );

//            echo "<br>";
//            var_dump($a_days);
//            echo "<br>";
            $interessi+= $a_days['importo_parziale'];
        }


        return round($interessi,2);
    }

    function calcola_interessi ( $data_inizio, $data_fine, $importo , $perc = 10 , $mesi = 6 )
    {
        if( substr($data_inizio,2,1) != "/" )
            $data_inizio = $data_inizio;
        if( substr($data_fine,2,1) != "/" )
            $data_fine = $data_fine;

        if( $data_inizio!=null && $data_inizio!="00/00/0000" && $data_inizio!="" )
        {
            $importo = abs($importo);

            $percImp = $importo * $perc / 100;

            $num_perc = floor( $this->calcola_giorni( $data_inizio , $data_fine ) / ($mesi*30) );

            $interessi = $percImp * $num_perc;
            $interessi = number_format( $interessi , 2 );
        }
        else
        {
            $interessi = 0;
            $interessi = number_format( $interessi , 2 );
        }

        return $interessi;
    }

    function calcola_giorni($data_ini,$data_fine)
    {
        if(($data_ini=="//" || $data_ini==NULL) && ($data_fine=="//" or $data_fine==NULL)) {return 0; die;}

        $a_ini = substr($data_ini,6);
        $mese_ini = substr($data_ini,3,2);
        $giorno_ini = substr($data_ini,0,2);

        $a_fine = substr($data_fine,6);
        $mese_fine = substr($data_fine,3,2);
        $giorno_fine = substr($data_fine,0,2);

        if($a_ini > $a_fine)
        {
            return 0;
            die;
        }
        else if($a_ini == $a_fine)
        {
            if($mese_ini > $mese_fine)
            {
                return 0;
                die;
            }
            else if($mese_ini == $mese_fine)
            {
                if($giorno_ini >= $giorno_fine)
                {
                    return 0;
                    die;
                }
                else if($giorno_ini < $giorno_fine)
                {
                    $giorno_tmp = $giorno_fine - $giorno_ini;
                }
            }
            else if($mese_ini < $mese_fine)
            {
                $g_ini = cal_days_in_month(0,$mese_ini,$a_ini);
                $g_ini_tmp = $g_ini-$giorno_ini;
                $giorni = 0;
                $mese_tmp = $mese_fine - $mese_ini;
                for($m=1; $m <= $mese_tmp-1; $m++)
                {
                    $mese = $mese_ini + $m;
                    if($mese < 13)
                    {
                        $giorni_me = cal_days_in_month(0,$mese,$a_ini);
                        $giorni += $giorni_me;
                    }
                }
                $giorno_tmp = $giorni + $giorno_fine + $g_ini_tmp;
            }
        }
        else if($a_fine > $a_ini)
        {
            $diff_anni = $a_fine - $a_ini;
            $conto_anni = 0;
            if($diff_anni > 1)
            {
                $mol=$diff_anni-1;
                $conto_anni=365*$mol;
            }

            $ultimo = "31/12/".$a_ini;
            $primo = "01/01/".$a_fine;
            $gio_ini = $this->calcola_giorni($data_ini,$ultimo);
            $gio_fine = $this->calcola_giorni($primo,$data_fine);

            $giorno_tmp = $gio_ini + $gio_fine + $conto_anni;
        }
        return $giorno_tmp;
    }

    public function totaleCodici($val){
        $a_codiciTrib = array("TOTALE"=>0,"IMPORTO_INTERESSI"=>0,"PAGAMENTO"=>0,"SPESE_INGIUNZIONE"=>0);
        //var_dump($val->Tributo);
        for ($i = 0; $i < count($val->Tributo); $i++)
        {
            if($val->Tributo[$i]->Tipo_Codice=="PAGAMENTO"){
                $a_codiciTrib["PAGAMENTO"] += $val->Tributo[$i]->Imposta;
                $a_codiciTrib["TOTALE"] -= $val->Tributo[$i]->Imposta;
//                $a_codiciTrib["IMPORTO_INTERESSI"] -= $this->Tributo[$i]->Imposta;
            }
            else{
                $a_codiciTrib["TOTALE"] += $val->Tributo[$i]->Imposta;
                if($val->Tipo=="CDS"){
                    if($val->Tributo[$i]->Tipo_Codice!="INTERESSI")
                        $a_codiciTrib["IMPORTO_INTERESSI"] += $val->Tributo[$i]->Imposta;
                }
                else{
                    if($val->Tributo[$i]->Tipo_Codice=="IMPORTO")
                        $a_codiciTrib["IMPORTO_INTERESSI"] += $val->Tributo[$i]->Imposta;
                }

//                alert($this->Tributo[$i]->Codice_Tributo);
                if($val->Tributo[$i]->Codice_Tributo=="S_03")
                    $a_codiciTrib["SPESE_INGIUNZIONE"] += $val->Tributo[$i]->Imposta;
            }
        }

        if($a_codiciTrib["TOTALE"]<$a_codiciTrib["IMPORTO_INTERESSI"])
            $a_codiciTrib["IMPORTO_INTERESSI"] = $a_codiciTrib["TOTALE"];

        return $a_codiciTrib;
    }

    public function pagamenti_completi($atto){
        $query = "SELECT SUM(Importo) AS TOTALE_PAGAMENTI FROM pagamento WHERE Atto_ID <= ".$atto->ID." AND Partita_ID = ".$atto->Partita_ID." AND Tipo_Atto NOT LIKE 'Pignoramento%' AND Tipo_Atto NOT LIKE 'Precedenti%' GROUP BY Partita_ID";
        $results = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));

        return isset($results["TOTALE_PAGAMENTI"])?$results["TOTALE_PAGAMENTI"]:null;
        //return $line['TOTALE_PAGAMENTI'];
    }

    public function checkProcess($val){
        if($val->Partita_ID>0){
            $query = "SELECT * FROM appeal WHERE Partita_ID=".$val->Partita_ID." ORDER BY ID DESC LIMIT 1";
            //$result = safe_query($query);
            $a_appeal = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));// mysql_fetch_array($result);
            if(count($a_appeal)>0){
                for($i = 0; $i<count($a_appeal); $i++)
                {
                    if($a_appeal[$i]['ID']>0 && ($a_appeal[$i]['End_Date']=="0000-00-00" || $a_appeal[$i]['End_Date']==null || $a_appeal[$i]['End_Date']=="")){
                        return false;
                    }
                }
            }

            }
        }

    function da_a_partita( $c , $da_n_elenco = null , $a_n_elenco = null , $where = null )
    {
        $query = "SELECT * FROM partita_tributi ";
        $query.= "WHERE CC = '".$c."' AND Is_Discharged=0 ";
        if($da_n_elenco != null)
        {
            $query.= "AND ( Comune_ID >= '".$da_n_elenco."' AND Comune_ID <= '".$a_n_elenco."' ) ";
        }
        if($where != null)
        {
            $query.= "AND ".$where." ";
        }

        $query.= "ORDER BY Comune_ID ASC";

        return $query;
    }

    function da_a_utente ( $c , $dacognome = null , $acognome = null , $danome = null , $anome = null )
    {
        $query = "(SELECT ID, Nome, Cognome AS utente_cognome FROM utente ";
        $query.= "WHERE Cognome != '' AND CC_Comune = '".$c."' ";
        if($dacognome != null)
        {
            $query.= "AND ( ( Cognome > '".addslashes($dacognome)."' ) ";
            $query.= "AND ( Cognome < '".addslashes($acognome)."' ) ";
            $query.= "OR ( Cognome = '".addslashes($dacognome)."' ";
            if($danome != null)
            {
                $query.= "AND Nome >= '".addslashes($danome)."' ";
            }

            $query.= ") OR ( Cognome = '".addslashes($acognome)."' ";
            if($anome != null)
            {
                $query.= "AND Nome <= '".addslashes($anome)."' ";
            }
            $query.= ") ) ";
        }

        $query.= " ) ";

        $query.= "UNION ";
        $query.= "(SELECT ID, Nome, Ditta AS utente_cognome FROM utente ";
        $query.= "WHERE Ditta != '' AND CC_Comune = '".$c."' ";

        if($dacognome != null)
        {
            $query.= "AND ( Ditta >= '".addslashes($dacognome)."' AND Ditta <= '".addslashes($acognome)."' ) ";
        }
        $query.= ") ";
        $query.= "ORDER BY utente_cognome ASC, Nome ASC";

        return $query;
    }

    function da_a_data( $c , $atto , $campo_data , $dadata = null , $adata = null , $where = null , $ctrl_anno = true )
    {
        $query = "SELECT A.* FROM atto A ";
        $query.= "WHERE A.CC = '".$c."' ";

        if( ($atto == "Ingiunzione" || $atto == "Avviso di intimazione ad adempiere") && $campo_data == "Data_Notifica" )
        {
            $query.= "AND ";
            $query.= "( ";

            $query.= "( Atto = 'Ingiunzione' AND  Data_Notifica is not null ";

            if($ctrl_anno == true)//SCADENZA INGIUNZIONE DOPO UN ANNO
                $query.= "AND ( Data_Notifica < '". date("Y-m-d" , strtotime( date('Y-m-d')."-1 year" )) ."' ) ";

            $query.= ") OR ( Atto = '".$atto."' AND Data_Notifica is not null ";

            if($ctrl_anno == true)//SCADENZA AVVISO DOPO 6 MESI
                $query.= "AND ( Data_Notifica < '". date("Y-m-d" , strtotime( date('Y-m-d')."-6 month" )) ."' ) ";

            $query.= ") OR ( Rielabora_Flag = 'si' ) ) ";

        }

        if($dadata != null)
        {
            $query.= "AND ( ".$campo_data." >= '".$dadata."' AND ".$campo_data." <= '".$adata."' ) ";
        }

        if($where != null)
        {
            $query.= "AND ".$where." ";
        }
        $query.= "ORDER BY Comune_ID ASC";

        return $query;
    }

    public function checkProcessAtto($processType, array $a_params,& $val){
        //$this->cls_help->alert($processType);
        if($val->Partita_ID>0){
            $query = "SELECT * FROM appeal WHERE Partita_ID=".$val->Partita_ID." ORDER BY ID DESC LIMIT 1";
            $a_appeal = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));

            if($a_appeal){
                if($a_appeal['ID']>0 && $this->cls_date->Get_DateNewFormat($a_appeal['End_Date'],"IT") == null){
                    return false;
                }
            }
        }

        switch($processType){
            case "ingiunzione":
                return $this->checkIngiunzione($a_params,$val);
            case "avviso_mora":
                return $this->checkAvvisoMora($a_params,$val);
            case "sollecito_pre_ingiunzione":
                return $this->checkSollecitoPreIngiunzione($a_params,$val);
            case "avviso":
                //$this->cls_help->alert("qui");
                return $this->checkAvviso($a_params,$val);
            case "sollecito":
                return $this->checkSollecito($a_params,$val);
            case "pignoramento":
                return $this->checkPignoramento($a_params,$val);
        }
    }

    public function checkPignoramento(array $a_params,$val){
        if($val->Data_Notifica==null||$val->Data_Notifica==''||$val->Data_Notifica=='0000-00-00')
            return false;
        else if($this->controlloPignoramento($val)!=null){
            return false;
        }
        else if($this->checkPagamenti($a_params,$val)===false)
            return false;
        else if($this->checkPignoramentoDates($val->Totale_Dovuto,$val)===false)
            return false;
        else if($val->Stato_Notifica!=0 && $val->Indirizzo_Validato!="si")
            return false;
        else
            return true;
    }

    public function checkPignoramentoDates($totaleDovuto,$val){
        $beginDate = new DateTime($val->Data_Notifica);
        $expireDate = new DateTime($val->Data_Notifica);
        switch($val->Atto)
        {
            case "Ingiunzione":
                if($totaleDovuto>=1000)
                    $beginDate->modify("+2 months");
                else
                    $beginDate->modify("+4 months");

                $expireDate->modify("+1 year");
                break;
            case "Avviso di intimazione ad adempiere":
                $beginDate->modify("+1 month");
                $expireDate->modify("+6 months");
                break;
        }


        if( date("Y-m-d") >= $beginDate->format("Y-m-d") && date("Y-m-d") < $expireDate->format("Y-m-d"))
            return true;
        else
            return false;

    }

    public function checkSollecito(array $a_params,$val){

        if($this->checkPagamenti($a_params,$val)===false)
            return false;
        else{
            if(($val->Atto=="Ingiunzione" || $val->Atto=="Sollecito di pagamento")){
                if($val->Rielabora_Flag=="si")
                    return true;
                else if($this->controlloPignoramento($val)!=null)
                    return false;
                else if($this->checkExpireDate(true,$val)===false)
                    return false;
                else
                    return true;
            }
            else
                return false;
        }
    }

    public function checkAvviso(array $a_params,$val){
        if($this->checkPagamenti($a_params,$val)===false)
            return false;
        else{
            if(($val->Atto=="Ingiunzione" || $val->Atto=="Avviso di intimazione ad adempiere")){
                if($val->Rielabora_Flag=="si")
                    return true;
                else if($this->controlloPignoramento($val)!=null){
                    $array_pignoramenti = $this->controlloPignoramento($val);
                    $pignoramento = $array_pignoramenti[(count($array_pignoramenti)-1)];

                    if($pignoramento->Stato_Stampa == "Stampato" || $pignoramento->ID_Cronologico > 0)
                    {
                        //$this->cls_help->alert(1);
                        return false;
                    }

                }
                else if($val->Data_Notifica==null||$val->Data_Notifica==""||$val->Data_Notifica=='0000-00-00')
                {
                    //$this->cls_help->alert(2);
                     return false;
                }
                else if($val->Rettifica_Flag=="si") {
                    //$this->cls_help->alert(3);
                    return false;
                }
                else if($this->checkExpireDate(false,$val)===false){
                    //$this->cls_help->alert(4);
                    return false;
                }
                else if($val->Stato_Notifica!=0 && $val->Indirizzo_Validato!="si"){
                    //$this->cls_help->alert(5);
                    return false;
                }
                else
                    return true;
            }
            else
                return false;

        }
    }

    public function checkSollecitoPreIngiunzione(array $a_params,$val){
        if($this->checkPagamenti($a_params,$val)===false)
            return false;
        else{
            if($val->Atto=="Sollecito pre ingiunzione"){
                if($val->Rielabora_Flag=="si")
                    return true;
                else if($this->checkExpireDate(false,$val)===false)
                    return false;
                else
                    return true;
            }
            else
                return false;

        }
    }

    public function checkAvvisoMora(array $a_params,$val){
        if($this->checkPagamenti($a_params,$val)===false)
            return false;
        else{
            if($val->Atto=="Avviso di messa in mora" || $val->Atto=="Sollecito pre ingiunzione"){
                if($val->Rielabora_Flag=="si")
                    return true;
                else if($this->checkExpireDate(false,$val)===false)
                    return false;
                else
                    return true;
            }
            else
                return false;

        }
    }

    public function checkIngiunzione(array $a_params,&$val){
        if($this->checkPagamenti($a_params,$val)===false)
        {
            return false;
        }
        else{
            if($val->Rielabora_Flag=="si")
                return true;
            else if($val->Rettifica_Flag=="si")
                return true;
            else if($val->Data_Notifica==null||$val->Data_Notifica==""||$val->Data_Notifica=='0000-00-00')
                return false;
            else if($this->controlloPignoramento($val)!=null){
                $array_pignoramenti = $this->controlloPignoramento($val);
                $pignoramento = $array_pignoramenti[(count($array_pignoramenti)-1)];

                if($pignoramento->Stato_Pignoramento!="Annullato")
                    return false;
            }
            else if($this->checkExpireDate(false,$val)===false)
                return false;
            else if($val->Stato_Notifica!=0 && $val->Indirizzo_Validato!="si")
                return false;
            else
                return true;
        }

    }

    public function checkExpireDate($valid,$val){

        switch($val->Atto){
            case "Sollecito pre ingiunzione":
                if($this->cls_date->Get_DateNewFormat($val->Data_Stampa,"DB")!=null){
                    $expireDate = new DateTime($val->Data_Stampa);
                    $expireDate->modify("+1 month");
                }
                else
                    return false;

                break;
            case "Avviso di messa in mora":
                if($this->cls_date->Get_DateNewFormat($val->Data_Notifica,"DB")!=null){
                    $expireDate = new DateTime($val->Data_Notifica);
                    $expireDate->modify("+15 days");
                }
                else
                    return false;

                break;
            case "Ingiunzione":
                if($this->cls_date->Get_DateNewFormat($val->Data_Notifica,"DB")!=null) {
                    $expireDate = new DateTime($val->Data_Notifica);
                    $expireDate->modify("+1 year");

                    $startDate = new DateTime($val->Data_Notifica);
                    $startDate->modify("+1 month");
                }
                else
                    return false;
                break;
            case "Sollecito di pagamento":
                if($this->cls_date->Get_DateNewFormat($val->Data_Stampa,"DB")!=null) {
                    $expireDate = new DateTime($val->Data_Stampa);
                    $expireDate->modify("+1 month");
                }
                else
                    return false;
                break;
            case "Avviso di intimazione ad adempiere":
                if($this->cls_date->Get_DateNewFormat($val->Data_Notifica,"DB")!=null) {
                    $expireDate = new DateTime($val->Data_Notifica);
                    $expireDate->modify("+6 months");
                }
                else
                    return false;

                break;
        }

        if($valid===false) {
            if (isset($expireDate)) {
                if (date("Y-m-d") < $expireDate->format("Y-m-d"))
                    return false;
                else
                    return true;
            }
        }
        else{
            if($val->Atto=="Ingiunzione"){
                if(isset($startDate)){
                    if( date("Y-m-d") < $startDate->format("Y-m-d") )
                        return false;
                    else
                        return true;
                }
                if (isset($expireDate)) {
                    if (date("Y-m-d") > $expireDate->format("Y-m-d"))
                        return false;
                    else
                        return true;
                }
            }
            else{
                if (isset($expireDate)) {
                    if (date("Y-m-d") < $expireDate->format("Y-m-d"))
                        return false;
                    else
                        return true;
                }
            }
        }
//        else if($valid===true){
//            if( date("Y-m-d") > $expireDate->format("Y-m-d") && to_mysql_date($expireDate->format("Y-m-d"))!=null)
//                return false;
//            else
//                return true;
//        }

    }

    public function spese_array($val)
    {
        $spese = array();

        $spese[1]['ID'] 			= 	$val->Spesa_1_ID;
        $spese[2]['ID'] 			= 	$val->Spesa_2_ID;
        $spese[3]['ID'] 			= 	$val->Spesa_3_ID;
        $spese[4]['ID'] 			= 	$val->Spesa_4_ID;
        $spese[5]['ID'] 			= 	$val->Spesa_5_ID;
        $spese[6]['ID'] 			= 	$val->Spesa_6_ID;
        $spese[7]['ID'] 			= 	$val->Spesa_7_ID;
        $spese[8]['ID'] 			= 	$val->Spesa_8_ID;
        $spese[9]['ID'] 			= 	$val->Spesa_9_ID;
        $spese[10]['ID'] 			= 	$val->Spesa_10_ID;

        $spese[1]['tipo_spesa']		= 	$val->Tipo_Spesa_1;
        $spese[2]['tipo_spesa'] 	= 	$val->Tipo_Spesa_2;
        $spese[3]['tipo_spesa'] 	= 	$val->Tipo_Spesa_3;
        $spese[4]['tipo_spesa'] 	= 	$val->Tipo_Spesa_4;
        $spese[5]['tipo_spesa'] 	= 	$val->Tipo_Spesa_5;
        $spese[6]['tipo_spesa'] 	= 	$val->Tipo_Spesa_6;
        $spese[7]['tipo_spesa'] 	= 	$val->Tipo_Spesa_7;
        $spese[8]['tipo_spesa'] 	= 	$val->Tipo_Spesa_8;
        $spese[9]['tipo_spesa'] 	= 	$val->Tipo_Spesa_9;
        $spese[10]['tipo_spesa'] 	= 	$val->Tipo_Spesa_10;

        $spese[1]['extra_spesa']	= 	$val->Extra_Spesa_1;
        $spese[2]['extra_spesa'] 	= 	$val->Extra_Spesa_2;
        $spese[3]['extra_spesa'] 	= 	$val->Extra_Spesa_3;
        $spese[4]['extra_spesa'] 	= 	$val->Extra_Spesa_4;
        $spese[5]['extra_spesa'] 	= 	$val->Extra_Spesa_5;
        $spese[6]['extra_spesa'] 	= 	$val->Extra_Spesa_6;
        $spese[7]['extra_spesa'] 	= 	$val->Extra_Spesa_7;
        $spese[8]['extra_spesa'] 	= 	$val->Extra_Spesa_8;
        $spese[9]['extra_spesa'] 	= 	$val->Extra_Spesa_9;
        $spese[10]['extra_spesa'] 	= 	$val->Extra_Spesa_10;

        $spese[1]['rimborso'] 		= 	$val->Rimborso_1;
        $spese[2]['rimborso'] 		= 	$val->Rimborso_2;
        $spese[3]['rimborso'] 		= 	$val->Rimborso_3;
        $spese[4]['rimborso'] 		= 	$val->Rimborso_4;
        $spese[5]['rimborso'] 		= 	$val->Rimborso_5;
        $spese[6]['rimborso'] 		= 	$val->Rimborso_6;
        $spese[7]['rimborso'] 		= 	$val->Rimborso_7;
        $spese[8]['rimborso'] 		= 	$val->Rimborso_8;
        $spese[9]['rimborso'] 		= 	$val->Rimborso_9;
        $spese[10]['rimborso'] 		= 	$val->Rimborso_10;

        $spese[1]['tipo_totale'] 	= 	$val->Tipo_Totale_1;
        $spese[2]['tipo_totale'] 	= 	$val->Tipo_Totale_2;
        $spese[3]['tipo_totale'] 	= 	$val->Tipo_Totale_3;
        $spese[4]['tipo_totale'] 	= 	$val->Tipo_Totale_4;
        $spese[5]['tipo_totale'] 	= 	$val->Tipo_Totale_5;
        $spese[6]['tipo_totale'] 	= 	$val->Tipo_Totale_6;
        $spese[7]['tipo_totale'] 	= 	$val->Tipo_Totale_7;
        $spese[8]['tipo_totale'] 	= 	$val->Tipo_Totale_8;
        $spese[9]['tipo_totale'] 	= 	$val->Tipo_Totale_9;
        $spese[10]['tipo_totale'] 	= 	$val->Tipo_Totale_10;

        return $spese;

    }

    public function inserisci_spese_array($spese,&$val)
    {

        $val->Tipo_Totale_1 = $spese[1]['ID'];
        $val->Spesa_1_ID = $spese[1]['tipo_totale'];
        $val->Tipo_Spesa_1 = $spese[1]['tipo_spesa'];
        $val->Extra_Spesa_1 = $spese[1]['extra_spesa'];
        $val->Rimborso_1 = $spese[1]['rimborso'];

        $val->Tipo_Totale_2 = $spese[2]['ID'];
        $val->Spesa_2_ID = $spese[2]['tipo_totale'];
        $val->Tipo_Spesa_2 = $spese[2]['tipo_spesa'];
        $val->Extra_Spesa_2 = $spese[2]['extra_spesa'];
        $val->Rimborso_2 = $spese[2]['rimborso'];

        $val->Tipo_Totale_3 = $spese[3]['ID'];
        $val->Spesa_3_ID = $spese[3]['tipo_totale'];
        $val->Tipo_Spesa_3 = $spese[3]['tipo_spesa'];
        $val->Extra_Spesa_3 = $spese[3]['extra_spesa'];
        $val->Rimborso_3 = $spese[3]['rimborso'];

        $val->Tipo_Totale_4 = $spese[4]['ID'];
        $val->Spesa_4_ID = $spese[4]['tipo_totale'];
        $val->Tipo_Spesa_4 = $spese[4]['tipo_spesa'];
        $val->Extra_Spesa_4 = $spese[4]['extra_spesa'];
        $val->Rimborso_4 = $spese[4]['rimborso'];

        $val->Tipo_Totale_5 = $spese[5]['ID'];
        $val->Spesa_5_ID = $spese[5]['tipo_totale'];
        $val->Tipo_Spesa_5 = $spese[5]['tipo_spesa'];
        $val->Extra_Spesa_5 = $spese[5]['extra_spesa'];
        $val->Rimborso_5 = $spese[5]['rimborso'];

        $val->Tipo_Totale_6 = $spese[6]['ID'];
        $val->Spesa_6_ID = $spese[6]['tipo_totale'];
        $val->Tipo_Spesa_6 = $spese[6]['tipo_spesa'];
        $val->Extra_Spesa_6 = $spese[6]['extra_spesa'];
        $val->Rimborso_6 = $spese[6]['rimborso'];

        $val->Tipo_Totale_7 = $spese[7]['ID'];
        $val->Spesa_7_ID = $spese[7]['tipo_totale'];
        $val->Tipo_Spesa_7 = $spese[7]['tipo_spesa'];
        $val->Extra_Spesa_7 = $spese[7]['extra_spesa'];
        $val->Rimborso_7 = $spese[7]['rimborso'];

        $val->Tipo_Totale_8 = $spese[8]['ID'];
        $val->Spesa_8_ID = $spese[8]['tipo_totale'];
        $val->Tipo_Spesa_8 = $spese[8]['tipo_spesa'];
        $val->Extra_Spesa_8 = $spese[8]['extra_spesa'];
        $val->Rimborso_8 = $spese[8]['rimborso'];

        $val->Tipo_Totale_9 = $spese[9]['ID'];
        $val->Spesa_9_ID = $spese[9]['tipo_totale'];
        $val->Tipo_Spesa_9 = $spese[9]['tipo_spesa'];
        $val->Extra_Spesa_9 = $spese[9]['extra_spesa'];
        $val->Rimborso_9 = $spese[9]['rimborso'];

        $val->Tipo_Totale_10 = $spese[10]['ID'];
        $val->Spesa_10_ID = $spese[10]['tipo_totale'];
        $val->Tipo_Spesa_10 = $spese[10]['tipo_spesa'];
        $val->Extra_Spesa_10 = $spese[10]['extra_spesa'];
        $val->Rimborso_10 = $spese[10]['rimborso'];

        $totale_rimborso = 0;
        for($x=1; $x<11; $x++)
            $totale_rimborso+= $spese[$x]['rimborso'];

        $val->Totale_Rimborso = $totale_rimborso;
    }

    public function controlloPignoramento(&$val)
    {
        //$pignoramento_id = select_mysql_array("ID", "pignoramento_generale","Atto_ID = '".$this->ID."'");
        $query = "SELECT ID FROM pignoramento_generale WHERE Atto_ID = '".$val->ID."'";
        $pignoramento_id = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));

        $val->Check_Pignoramento = null;
        $pignoramento = null;
        for( $i=0; $i<count($pignoramento_id); $i++)
        {
            $query = "SELECT * FROM pignoramento_generale WHERE ID = ".$pignoramento_id[$i]['ID']." AND CC = '".$val->CC."'";
            $pignoramento[$i] = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"pignoramento_generale");//new pignoramento( $pignoramento_id[$i]['ID'] , $this->CC );
            if($i==(count($pignoramento_id)-1))
                $val->Check_Pignoramento = $pignoramento[$i];
        }

        return $pignoramento;
    }

    public function checkPagamenti(array $a_params,$val){
        if(!isset($a_params['importo_minimo'])){
            return false;
        }

        $totale_dovuto = $val->Totale_Dovuto;

        $data_not = new DateTime($val->Data_Notifica);
        $data_not->modify("+2 months");
        if($this->controlloDataPrimoPagamento($val)!=null){
            if ($this->controlloDataPrimoPagamento($val) > $data_not->format('Y-m-d'))
                $totale_dovuto += $val->Diritto_Riscossione_Massimo;
            else
                $totale_dovuto += $val->Diritto_Riscossione_Minimo;
        }
        else{
            if (date("Y-m-d") > $data_not->format('Y-m-d'))
                $totale_dovuto += $val->Diritto_Riscossione_Massimo;
            else
                $totale_dovuto += $val->Diritto_Riscossione_Minimo;
        }

        if ($totale_dovuto - $this->pagamenti_completi($val) > $a_params['importo_minimo']) {
            if ($val->Rate_Previste > 0) {
                $data_rata = new cls_DateTime($val->Scadenze_Rate[count($val->Scadenze_Rate) - 1],"IT",false);
                $data_rata->AddMonth(3);
                //$data_rata = new DateTime();
                //$data_rata->modify("+3 months");
                if (date('Y-m-d') > $data_rata->GetDateDB()/*$data_rata->format('Y-m-d')*/)
                    return true;
                else
                    return false;
            }
            else
                return true;
        }
        else
            return false;
    }

    public function controlloDataPrimoPagamento($val)
    {
        if(isset($val->Pagamento[0]->Data_Pagamento))
            if($val->Pagamento[0]->Data_Pagamento!=null)
            {
                return $val->Pagamento[0]->Data_Pagamento;
            }
            else
                return null;
    }

    function estrai_quinto_campo($quintoCampo)
    {
        $num_digits = strlen($quintoCampo);
        for($i=16;$i>$num_digits;$i--){
            $quintoCampo = '0'.$quintoCampo;
        }

        //ID COMUNE 3 CIFRE
        $posizioneDa = 0;
        $posizioneA = 3;
        $codiceTemp = substr($quintoCampo, $posizioneDa, $posizioneA);

        $codiceId = "";
        $numTrovato = false;
        // arriva 013 : devo ottenere 13
        for ($i = 0; $i < strlen($codiceTemp); $i++)
        {
            $temp = substr($codiceTemp, $i, 1);
            if ($temp >= '1' && $temp <= '9')
            {
                $numTrovato = true;
                $codiceId .= $temp;
            }
            else if ($numTrovato == true)
            {
                $codiceId .= $temp;  //  � 0 alla fine: va tenuto
            }
            else
            {
                // � 0 iniziale: va tolto
            }
        }

        $queryComune = "SELECT CC FROM enti_gestiti WHERE ID = '$codiceId'";
        //echo "<br>" . $queryComune;
        $resComune = $this->cls_db->ExecuteQuery($queryComune);// mysql_query($queryComune);

        if ($this->cls_db->getNumberRow($resComune) == 0 ) $ccComune = "";
        else
        {
            $rigaComune = $this->cls_db->getArrayLineNull($resComune,"enti_gestiti");// mysql_fetch_assoc($resComune);
            $ccComune = $rigaComune['CC'];
        }


        //TIPO SERVIZIO + RISCOSSIONE 2 CIFRE
        $posizioneDa += $posizioneA;
        $posizioneA = 2;
        $numeroServizio = substr($quintoCampo, $posizioneDa, $posizioneA);

        switch ($numeroServizio)
        {
            case "02": $tipoServizio = "Ingiunzione"; break;
            case "03": $tipoServizio = "Sollecito di pagamento"; break;
            case "04": $tipoServizio = "Avviso di intimazione ad adempiere"; break;
            case "05": $tipoServizio = "Sollecito avviso di intimazione"; break;
            case "06": $tipoServizio = "Pignoramento beni mobili registrati"; break;
            case "07": $tipoServizio = "Pignoramento presso datore di lavoro"; break;
            case "08": $tipoServizio = "Pignoramento presso banca"; break;

            case "12": $tipoServizio = "Avviso di messa in mora"; break;
            case "11": $tipoServizio = "Sollecito pre ingiunzione"; break;
        }

        //NUMERO RATA 2 CIFRE
        $posizioneDa += $posizioneA;
        $posizioneA = 2;
        $numeroTempRata = substr($quintoCampo, $posizioneDa, $posizioneA);

        $numeroRata = "";
        $numTrovato = false;
        // arriva 03 : devo ottenere 3
        for ($i = 0; $i < strlen($numeroTempRata); $i++)
        {
            $temp = substr($numeroTempRata, $i, 1);
            if ($temp >= '1' && $temp <= '9')
            {
                $numTrovato = true;
                $numeroRata .= $temp;
            }
            else if ($numTrovato == true)
            {
                $numeroRata .= $temp;  //  � 0 alla fine: va tenuto
            }
            else
            {
                // � 0 iniziale: va tolto
            }
        }

        if ($numeroRata == "") $numeroRata = 0;

        //ANNO 2 CIFRE
        $posizioneDa += $posizioneA;
        $posizioneA = 2;
        $annoGestione = substr($quintoCampo, $posizioneDa, $posizioneA);

        //ATTO 7 CIFRE
        $posizioneDa += $posizioneA;
        $posizioneA = 7;
        $numTempAtto = substr($quintoCampo, $posizioneDa, $posizioneA);

        $numeroAtto = "";
        $numTrovato = false;
        // arriva 00001230 : devo ottenere 1230
        for ($i = 0; $i < strlen($numTempAtto); $i++)
        {
            $temp = substr($numTempAtto, $i, 1);
            if ($temp >= '1' && $temp <= '9')
            {
                $numTrovato = true;
                $numeroAtto .= $temp;
            }
            else if ($numTrovato == true)
            {
                $numeroAtto .= $temp;  //  � 0 alla fine: va tenuto
            }
            else
            {
                // � 0 iniziale: va tolto
            }
        }

        $oggetto = array(
            $ccComune,
            $numeroServizio,
            $numeroRata,
            $annoGestione,
            $numeroAtto
        );

        return $oggetto;
    }

    public function getIDFromCrono ( $tipo, $crono, $CC , $flag)
    {
        $cronologico = explode("/", $crono);
        switch($flag)
        {
            case "Atto":
                if($tipo == "AVVISOINTIMAZIONE")	$tipo = "Avviso di intimazione ad adempiere";
                else if($tipo == "INGIUNZIONE")		$tipo = "Ingiunzione";
                else if($tipo == "AV_MORA")		    $tipo = "Avviso di messa in mora";
                else if($tipo == "SOLL_PRE")		$tipo = "Sollecito pre ingiunzione";
                else if($tipo == "SOLLECITOINGIUNZIONE")		$tipo = "Sollecito di pagamento";

                $query = "SELECT atto.ID, partita_tributi.Tipo AS Tipo_Partita ";
                $query.= "FROM atto JOIN partita_tributi ON partita_tributi.ID = atto.Partita_ID ";
                $query.= "WHERE atto.CC = '".$CC."' AND atto.Atto = '".$tipo."' AND atto.ID_Cronologico=".$cronologico[0]." AND ";
                $query.= "atto.Anno_Cronologico = ".$cronologico[1]." ";

                //echo $query;
                break;
            case "Pigno" : $query = "SELECT pignoramento_generale.ID, partita_tributi.Tipo AS Tipo_Partita ";
                $query.= "FROM pignoramento_generale ";
                $query.= "JOIN partita_tributi ON partita_tributi.ID=pignoramento_generale.Partita_ID ";
                $query.= "WHERE pignoramento_generale.CC = '".$CC."' AND pignoramento_generale.Tipo = '".$tipo."' ";
                $query.= "AND pignoramento_generale.ID_Cronologico=".$cronologico[0]." AND pignoramento_generale.Anno_Cronologico = ".$cronologico[1]." ";
                break;
        }



        $result = $this->cls_db->ExecuteQuery($query);
        //mysqli_fetch_array($result, MYSQLI_NUM);
        //$array_result = mysql_fetch_row($result);
        $arrRes = mysqli_fetch_array($result, MYSQLI_NUM);

        if($arrRes != null) return $arrRes;
        else return array(0 => null, 1 => null);

    }

    public function ListaPagamentiDaBonificare ($telematico)  // telematico: Y o N
    {
        $querySel = "SELECT ID FROM pagamenti_importati WHERE Esito = 'DABONIFICARE' AND Telematico = '$telematico'";
        $resCerca = $this->cls_db->ExecuteQuery($querySel);

        $arrayDaBonificare = array();
        while ($rigaCerca = mysqli_fetch_assoc($resCerca))
        {
            $arrayDaBonificare[] = $rigaCerca['ID'];
        }
        return $arrayDaBonificare;
    }

    public function data_conto_terzi($data, $Data_Cambio_Conto,$Conto_Terzi)  //  data in formato YYYY-mm-dd
    {
        if ($this->cls_date->Get_DateNewFormat($Data_Cambio_Conto,"DB") != "")
        {
            if ($data < $Data_Cambio_Conto)
            {
                if ($Conto_Terzi == "si") $esito = "N";  //  "Non Conto Terzi prima della data di cambio ";
                else $esito = "Y";  //  "Conto Terzi prima della data di cambio ";
            }
            else
            {
                if ($Conto_Terzi == "si") $esito = "Y";  //  "Conto Terzi dopo la data di cambio ";
                else $esito = "N";  //  "Non Conto Terzi dopo la data di cambio ";
            }
        }
        else
        {
            if ($Conto_Terzi == "si") $esito = "Y";  //  "sempre Conto Terzi ";
            else $esito = "N";  //  "mai Conto Terzi ";
        }

        return $esito;
    }

    public function Array_Selezione_Anni($c, $gestione)
    {
        $where = "CC_Anno = '".$c."' ";

        switch ($gestione)
        {
            case "COATTIVA": 		$where.= " AND Gestione_Coattiva = 'Y' "; 		break;
            case "TARGHEESTERE": 	$where.= " AND Gestione_Targhe_Estere = 'Y' "; 	break;
            case "PUBBLICITA": 		$where.= " AND Gestione_Pubblicita = 'Y' ";		break;
            default: 				alert ("Parametro assente!"); 					break;
        }

        $query = "SELECT * FROM anni_gestiti WHERE ".$where." ORDER BY Anno DESC";
        $array_anni = $this->cls_db->getResultsNull($this->cls_db->ExecuteQuery($query),"anni_gestiti");// select_mysql_array("*", "anni_gestiti", $where, "Anno","DESC");



        return $array_anni;
    }

    public function Options_Anni_Veloci($c, $gestione, $pagina)
    {
        $array_anni = $this->Array_Selezione_Anni($c, $gestione);

        $select = "<select id='select_anno_veloce' onchange='conferma_anno_js(\"".$pagina."\",\"".$c."\")'>";

        for($i=0;$i<count($array_anni);$i++)
            $select.= "<option value='".$array_anni[$i]['Anno']."'>".$array_anni[$i]['Anno']."</option>";

        $select.="</select>";

        return $select;

    }

    public function ProssimoComuneId($CC)
    {
        $queryMaxId = "SELECT MAX(Comune_ID) as maxx FROM pagamento WHERE CC = '" . $CC . "'";
        $result = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($queryMaxId),"pagamento");

        return isset($result["maxx"])?($result["maxx"]+1):1;
    }

    public function TipiPagamento ($selected, $uscita, $attorif = null){
        //  uscita pu� essere:
        // SELECT
        // TIPODASCRITTA
        // SCRITTADATIPO

        // elenco tipi accettati (promemoria)
        // da aggiornare man mano che si hanno nuovi Tipi
        $arrayTipi = array(
//				"VERBALE_CDS",
//				"SOLLECITO_CDS",
            "SOLL_PRE",
            "AVV_MORA",
            "INGIUNZIONE_CDS",
            "SOLLECITO_INGIUNZIONE_CDS",
            "AVVISO_INTIMAZIONE_CDS",
//				"SOLLECITO_AVVISO_INTIMAZIONE_CDS",
            "PIGNORAMENTO_VEICOLO_CDS",
            "PIGNORAMENTO_DATORE_LAVORO_CDS",
            "PIGNORAMENTO_BANCA_CDS"
        );
        $arrayScritte = array(
//				"Verbale",
//				"Sollecito",
            "Sollecito pre ingiunzione",
            "Avviso di messa in mora",
            "Ingiunzione",
            "Sollecito ingiunzione",
            "Avviso di intimazione ad adempiere",
//				"Sollecito di avviso di intimazione ad adempiere",
            "Pignoramento beni mobili registrati",
            "Pignoramento presso datore di lavoro",
            "Pignoramento presso banca"
        );
        $arrayTipiPigno = array(
//				"Verbale",
//				"Sollecito",
            "Sollecito pre ingiunzione",
            "Avviso di messa in mora",
            "Ingiunzione",
            "Sollecito ingiunzione",
            "Avviso di intimazione ad adempiere",
//				"Sollecito di avviso di intimazione ad adempiere",
            "veicolo",
            "terzi",
            "terzi"
        );

        switch ($uscita)
        {
            case "SELECT":
                $esitoFunction = "<option value=''></option>\n";
                for ($i = 0; $i < count($arrayTipi); $i++)
                {
                    if ($selected == $arrayTipi[$i]) $selTag = " selected ";
                    else $selTag = "";

                    $selected = $attorif == $arrayTipi[$i] ? "selected":"";
                    //echo "<h1>Selected --> ".$attorif." --- ".$arrayTipi[$i]." --- ".$arrayScritte[$i]."</h1>";
                    $esitoFunction .= "<option value='" . $arrayTipi[$i] . "' " . $selTag ." ".$selected." >" . $arrayScritte[$i] . "</option>\n";
                }
                break;
            case "TIPODASCRITTA":
                $esitoFunction = "";
                for ($i = 0; $i < count($arrayScritte); $i++)
                {
                    if ($selected == $arrayScritte[$i])
                    {
                        $esitoFunction = $arrayTipi[$i];
                        break;
                    }
                }
                break;
            case "SCRITTADATIPO":
                $esitoFunction = "";
                for ($i = 0; $i < count($arrayTipi); $i++)
                {
                    if ($selected == $arrayTipi[$i])
                    {
                        $esitoFunction = $arrayScritte[$i];
                        break;
                    }
                }
                break;
            case "SCRITTADATIPOPIGNO":
                $esitoFunction = "";
                for ($i = 0; $i < count($arrayTipi); $i++)
                {
                    if ($selected == $arrayTipi[$i])
                    {
                        $esitoFunction = $arrayTipiPigno[$i];
                        break;
                    }
                }
                break;
            default:
                $esitoFunction = "Errore scelta: " . $uscita;
                break;
        }
        //alert ($esitoFunction . " e " . $selected);
        return $esitoFunction;
    }

    public function cercaIDdaCrono ( $tipoAtto, $crono, $CC )
    {
        $cronologico = explode("/", $crono);
        if($tipoAtto == "AVVISOINTIMAZIONE")	$tipoAtto = "Avviso di intimazione ad adempiere";
        else if($tipoAtto == "INGIUNZIONE")		$tipoAtto = "Ingiunzione";
        else if($tipoAtto == "AV_MORA")		    $tipoAtto = "Avviso di messa in mora";
        else if($tipoAtto == "SOLL_PRE")		$tipoAtto = "Sollecito pre ingiunzione";
        else if($tipoAtto == "SOLLECITOINGIUNZIONE")		$tipoAtto = "Sollecito di pagamento";

        $query = "SELECT ID ";
        $query.= "FROM atto ";
        $query.= "WHERE CC = '".$CC."' AND Atto = '".$tipoAtto."' AND ID_Cronologico ='".$cronologico[0]."' AND ";
        $query.= "Anno_Cronologico = ".$cronologico[1]." ";

        $return = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"atto")["ID"];// single_query($query);

        return $return;

    }

    public function cercaIDdaCronoPagamento ( $docTypeId, $crono_id, $crono_year, $CC )
    {

        $query = "SELECT ID ";
        $query.= "FROM atto ";
        $query.= "WHERE CC = '".$CC."' AND DocumentTypeId = '".$docTypeId."' AND ID_Cronologico ='".$crono_id."' AND ";
        $query.= "Anno_Cronologico = ".$crono_year." ";

        $return = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"atto")["ID"];// single_query($query);

        return $return;

    }

    public function cercaIDdaCronoPagamentoPigno ( $tipeId, $crono, $anno, $CC )
    {

        $query = "SELECT ID ";
        $query.= "FROM pignoramento_generale ";
        $query.= "WHERE CC = '".$CC."' AND DocumentTypeId = '".$tipeId."' AND ID_Cronologico ='".$crono."' AND ";
        $query.= "Anno_Cronologico = ".$anno." ";

        $return = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"pignoramento_generale")["ID"];

        return $return;

    }

    function InsertUpdatePagamento ($pagVal, $forzoInsertUpdate = null)  //  INSERT o UPDATE o null
    {
        $insUpd = "";
        $fields = array();
        $values = array();

        $pagVal->Comune_ID = $this->ProssimoComuneId($pagVal->CC);

        if ($forzoInsertUpdate != null)
        {
            $insUpd = $forzoInsertUpdate;
        }
        else
        {
            $pagVal->ID = $this->PagamentoGiaPresente($pagVal);
            if ($pagVal->ID == NULL)
            {
                $insUpd = "INSERT";
            }
            else
            {
                $insUpd = "UPDATE";
            }
        }

        foreach ($pagVal as $campo => $valore)
        {
            if (isset($campo) &&
                $campo != "ID" &&
                $campo != "Cronologico_Atto")
            {
                $fields[] = $campo;
                if($valore == '') $valore = 'NULL';
                $values[] = $valore;
            }
        }

        $questoPag = "";
        if ($insUpd == "INSERT")
        {
            $risposta = $this->insert_pagam_locale($fields, $values);
            switch ($risposta)
            {
                case true:
                    $risposta = "INSERT_OK";
                    $questoPag = $this->PagamentoGiaPresente($pagVal);
                    break;
                case false:
                    $risposta = "INSERT_ERROR";
                    break;
                default: break;
            }
        }
        else if ($insUpd == "UPDATE")
        {
            $risposta = $this->update_pagam_locale($pagVal->ID, $fields, $values);
            switch ($risposta)
            {
                case 0:
                    $risposta = "DIMENSIONI_ERRATE";
                    break;
                case 1:
                    $risposta = "ID_VUOTO";
                    break;
                case 2:
                    $risposta = "CAMPI_UGUALI";
                    break;
                case 3:
                    $risposta = "UPDATE_OK";
                    $questoPag = $pagVal->ID;
                    break;
                case 4:
                    $risposta = "UPDATE_ERROR";
                    break;
                default:
                    $risposta = "SCONOSCIUTO_UPDATE";
                    break;
            }
        }
        else $risposta = "INSERT_ERROR_2";
        $risposta .= "**" . $questoPag;
        return $risposta;
    }

    function PagamentoGiaPresente ($pagVal)
    {
        $queryCerca = "SELECT ID FROM pagamento ";
        $queryCerca .= "WHERE Data_Pagamento = '" . $pagVal->Data_Pagamento . "' ";
        $queryCerca .= "AND Importo = '" . $pagVal->Importo . "' ";
        $queryCerca .= "AND CC = '" . $pagVal->CC . "' ";
        $queryCerca .= "AND Partita_ID = '" . $pagVal->Partita_ID . "' ";
        $queryCerca .= "AND Atto_ID = '" . $pagVal->Atto_ID . "' ";
        $queryCerca .= "AND Rata = '" . $pagVal->Rata . "' ";
        $queryCerca .= "AND Bollettino = '" . $pagVal->Bollettino . "' ";
        //$resCerca = mysql_query($queryCerca);
        //$rigaCerca = mysql_fetch_assoc($resCerca);
        return $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($queryCerca),"pagamento")["ID"];//$rigaCerca['ID'];
    }

    public function insert_pagam_locale($fields_to_insert, $values_to_insert)
    {
        $dim1 = count($fields_to_insert);
        $dim2 = count($values_to_insert);
        if ($dim1 != $dim2 || $dim1 == 0 || $dim2 == 0) return 0;

        $clause = "";
        for ($i = 0; $i < $dim1; $i++)
        {
            $clause .= $fields_to_insert[$i];
            if ($i < $dim1-1) $clause = $clause . ", ";
        }
        $query = "INSERT INTO pagamento (" . $clause . ") VALUES (";
        $clause = "";
        for ($i = 0; $i < $dim1; $i++)
        {
            if($values_to_insert[$i] == 'NULL') $clause .= "" . $values_to_insert[$i] . "";
            else $clause .= "'" . $values_to_insert[$i] . "'";
            if ($i < $dim1-1) $clause = $clause . ", ";
        }
        $query .= $clause . ")";

        return $this->cls_db->ExecuteQuery($query);//mysql_query($query);

        //echo "<br>" . $query;

        return true;
    }

    public function update_pagam_locale($key, $fields_to_update, $values_to_update)
    {
        $dim1 = count($fields_to_update);
        $dim2 = count($values_to_update);

        if ($dim1 != $dim2 || $dim1 == 0) return 0;

        if ($key == 0 || $key == '0' || $key == NULL) return 1;

        $query = "SELECT CC FROM pagamento WHERE ID = '" . $key . "'";
        $resultCC = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"pagamento")["CC"];// single_answer_query($query);

        $query = "SELECT * FROM pagamento WHERE ID = '".$key."' AND CC = '".$resultCC."'";
        $myOldPag = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"pagamento");//new pagamento($key, $resultCC);

        $clause = "";
        for ($i = 0; $i < $dim1; $i++)
        {
            if ($fields_to_update[$i] != $values_to_update[$i])
            {
                if ($fields_to_update[$i] != "Comune_ID")  //  se � UPDATE, il Comune_ID non va modificato
                {
                    if($values_to_update[$i] == 'NULL') $clause .= $fields_to_update[$i] . "=" .$values_to_update[$i]. " , ";
                    else $clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
                }
            }
            //echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
        }
        if ($clause == "") return 2;  // non updata nulla, perch� sono tutti uguali

        $clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "

        $query = "UPDATE pagamento SET $clause WHERE ID = '" . $key . "'";

        if ($this->cls_db->ExecuteQuery($query)/*mysql_query($query)*/ != NULL) return 3;
        else return 4;

        /*echo "<br>" . $query;

        return 3;*/
    }

    function InsertUpdatePagamImportato ($pagVal, $forzoInsertUpdate = null)  //  INSERT o UPDATE o null
    {
        $insUpd = "";
        $fields = array();
        $values = array();

        $pagVal->Data_Importazione = date('Y-m-d');
        $pagVal->Operatore = $_SESSION['username'];

        if ($forzoInsertUpdate != null)
        {
            $insUpd = $forzoInsertUpdate;
        }
        else
        {
            $pagVal->ID = $this->PagamImportatoGiaPresente($pagVal->Quinto_Campo, $pagVal->Importo_Pagato);
            if ($pagVal->ID == NULL)
            {
                $insUpd = "INSERT";
            }
            else
            {
                $insUpd = "UPDATE";
            }
        }

        foreach ($pagVal as $campo => $valore)
        {
            if (isset($campo) && $campo != "ID")
            {
                $fields[] = $campo;
                if($valore == '') $valore = 'NULL';
                $values[] = $valore;
            }
        }

        $questaNot = "";
        if ($insUpd == "INSERT")
        {
            $risposta = $this->insert_pagam_importato_locale($fields, $values);
            switch ($risposta)
            {
                case true:
                    $risposta = "INSERT_OK";
                    $questaNot = $this->PagamImportatoGiaPresente($pagVal->Quinto_Campo, $pagVal->Importo_Pagato);
                    break;
                case false:
                    $risposta = "INSERT_ERROR";
                    break;
                default: break;
            }
        }
        else if ($insUpd == "UPDATE")
        {
            $risposta = $this->update_pagam_importato_locale($pagVal->ID, $fields, $values);
            switch ($risposta)
            {
                case 0:
                    $risposta = "DIMENSIONI_ERRATE";
                    break;
                case 1:
                    $risposta = "ID_VUOTO";
                    break;
                case 2:
                    $risposta = "LOG_ANTECEDENTE";
                    break;
                case 3:
                    $risposta = "CAMPI_UGUALI";
                    break;
                case 4:
                    $risposta = "UPDATE_OK";
                    $questaNot = $pagVal->ID;
                    break;
                case 5:
                    $risposta = "UPDATE_ERROR";
                    break;
                default:
                    $risposta = "SCONOSCIUTO_UPDATE";
                    break;
            }
        }
        else $risposta = "INSERT_ERROR";
        //$risposta .= "**" . $questaNot;
        return $risposta;
    }

    function tipo_pignoramento($objType, $Tipo_Terzi, $tipo=null)
    {
        switch($objType)
        {
            case "veicolo":
                if($tipo==null)
                    $tipo_pignoramento = "Pignoramento beni mobili registrati";
                else if($tipo == "sigla" )
                    $tipo_pignoramento = "IVG";
                break;
            case "terzi":
                switch($Tipo_Terzi)
                {
                    case "lavoro":
                        if($tipo==null)
                            $tipo_pignoramento = "Pignoramento presso datore di lavoro";
                        else if($tipo == "sigla" )
                            $tipo_pignoramento = "DatoreLavoro";
                        break;
                    case "banca":
                        if($tipo==null)
                            $tipo_pignoramento = "Pignoramento presso banca";
                        else if($tipo == "sigla" )
                            $tipo_pignoramento = "Banca";
                        break;
                }

                break;
            default:
                $tipo_pignoramento = "sconosciuto";
                break;
        }

        return $tipo_pignoramento;
    }

    public function update_pagam_importato_locale($key, $fields_to_update, $values_to_update)
    {
        $dim1 = count($fields_to_update);
        $dim2 = count($values_to_update);

        if ($dim1 != $dim2 || $dim1 == 0) return 0;

        if ($key == 0 || $key == '0' || $key == NULL) return 1;

        $query = "SELECT * FROM pagamenti_importati WHERE ID = '" . $key . "'";
        $myOldPagamImportato = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"pagamenti_importati");// new pagamenti_importati($key);

        $clause = "";
        for ($i = 0; $i < $dim1; $i++)
        {
            if ($fields_to_update[$i] != $values_to_update[$i])
            {
                if($values_to_update[$i] == 'NULL') $clause .= $fields_to_update[$i] . "=" .$values_to_update[$i]. " , ";
                else $clause .= $fields_to_update[$i] . "='" .$values_to_update[$i]. "' , ";
            }
            //echo ("<br>" . $myOldReg->$fields_to_update[$i] . "!=" . $values_to_update[$i]);
        }
        //alert ($clause);
        if ($clause == "") return 3;  // non updata nulla, perch� sono tutti uguali

        $clause = substr ($clause, 0, -2);  //  tolgo l'ultimo ", "

        $daNonEseguire = "UPDATE pagamenti_importati SET Data_Importazione";

        $query = "UPDATE pagamenti_importati SET $clause WHERE ID = '" . $key . "'";

        if ($daNonEseguire == substr($query, 0, strlen($daNonEseguire))) return 3;

        //echo "<br>" . $query;

        if ($this->cls_db->ExecuteQuery($query) != NULL) return 4;
        else return 5;

        /*echo "<br>" . $query;

        return 4;*/
    }

    public function PagamImportatoGiaPresente($Quinto_Campo,$Importo_Pagato)
    {
        $queryCerca = "SELECT ID FROM pagamenti_importati ";
        $queryCerca .= "WHERE Quinto_Campo = '" . $Quinto_Campo . "' AND ";
        $queryCerca .= "Importo_Pagato = '" . $Importo_Pagato . "' ";
        //$resCerca = mysql_query($queryCerca);
        //$rigaCerca = mysql_fetch_assoc($resCerca);
        return $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($queryCerca),"pagamenti_importati")["ID"];//$rigaCerca['ID'];
    }

    public function insert_pagam_importato_locale($fields_to_insert, $values_to_insert)
    {
        $dim1 = count($fields_to_insert);
        $dim2 = count($values_to_insert);
        if ($dim1 != $dim2 || $dim1 == 0 || $dim2 == 0) return 0;

        $clause = "";
        for ($i = 0; $i < $dim1; $i++)
        {
            $clause .= $fields_to_insert[$i];
            if ($i < $dim1-1) $clause = $clause . ", ";
        }
        $query = "INSERT INTO pagamenti_importati (" . $clause . ") VALUES (";
        $clause = "";
        for ($i = 0; $i < $dim1; $i++)
        {
            if($values_to_insert[$i] == 'NULL') $clause .= "" . $values_to_insert[$i] . "";
            else $clause .= "'" . $values_to_insert[$i] . "'";
            if ($i < $dim1-1) $clause = $clause . ", ";
        }
        $query .= $clause . ")";

        return $this->cls_db->ExecuteQuery($query);// mysql_query($query);

        /*echo "<br>" . $query;

        return true;*/
    }

    function GetDataUtente($utente)
    {
        //var_dump($utente->Forma_Giuridica);
        $query = "SELECT * FROM forma_giuridica_societa WHERE ID = '".$utente->Forma_Giuridica."' AND CC = '*****'";
        $result = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"forma_giuridica_societa");//new forma_giuridica($val['Forma_Giuridica']);
        $utente->Sigla_Forma_Giuridica = $result->Sigla;

        $query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$utente->ID."' AND Tipo = 'res'";
        $utente->Residenza = $this->cls_db->getObjectLine($this->cls_db->ExecuteQuery($query));// new indirizzo( $progr , 'res' , $c );

        if($utente->Residenza != null)
            if($utente->Residenza->Via_ID!=1)
            {
                $query = "SELECT * FROM toponimo WHERE ID = '".$utente->Residenza->Via_ID."' AND CC_Comune = '".$utente->CC_Comune."'";
                $utente->Residenza->Toponimo = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"toponimo");//new toponimo( $utente->Residenza->Via_ID , $utente->CC_Comune );
            }
            else if($utente->Residenza->Via_Cap_ID!=1)
            {
                $query = "SELECT * FROM toponimi_cappati WHERE ID = '".$utente->Residenza->Via_Cap_ID."'";
                $utente->Residenza->Toponimo = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"toponimi_cappati");//new toponimo_cap( $utente->Residenza->Via_Cap_ID );
            }
            else
                $utente->Residenza->Toponimo = null;
        //var_dump($utente->Residenza->Tipo);
        //die;
        $query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$utente->ID."' AND Tipo = 'dom'";
        $utente->Domicilio = $this->cls_db->getObjectLine($this->cls_db->ExecuteQuery($query));//new indirizzo( $progr , 'dom' , $c );

        if($utente->Domicilio != null)
            if($utente->Domicilio->Via_ID!=1)
            {
                $query = "SELECT * FROM toponimo WHERE ID = '".$utente->Domicilio->Via_ID."' AND CC_Comune = '".$utente->CC_Comune."'";
                $utente->Domicilio->Toponimo = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"toponimo");//new toponimo( $utente->Residenza->Via_ID , $utente->CC_Comune );
            }
            else if($utente->Domicilio->Via_Cap_ID!=1)
            {
                $query = "SELECT * FROM toponimi_cappati WHERE ID = '".$utente->Domicilio->Via_Cap_ID."'";
                $utente->Domicilio->Toponimo = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"toponimi_cappati");//new toponimo_cap( $utente->Residenza->Via_Cap_ID );
            }
            else
                $utente->Domicilio->Toponimo = null;

        $query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$utente->ID."' AND Tipo = 'rec'";
        $utente->Recapito = $this->cls_db->getObjectLine($this->cls_db->ExecuteQuery($query));//new indirizzo( $progr , 'rec' , $c );

        if($utente->Recapito != null)
            if($utente->Recapito->Via_ID!=1)
            {
                $query = "SELECT * FROM toponimo WHERE ID = '".$utente->Recapito->Via_ID."' AND CC_Comune = '".$utente->CC_Comune."'";
                $utente->Recapito->Toponimo = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"toponimo");//new toponimo( $utente->Residenza->Via_ID , $utente->CC_Comune );
            }
            else if($utente->Recapito->Via_Cap_ID!=1)
            {
                $query = "SELECT * FROM toponimi_cappati WHERE ID = '".$utente->Recapito->Via_Cap_ID."'";
                $utente->Recapito->Toponimo = $this->cls_db->getObjectLineNull($this->cls_db->ExecuteQuery($query),"toponimi_cappati");//new toponimo_cap( $utente->Residenza->Via_Cap_ID );
            }
            else
                $utente->Recapito->Toponimo = null;

        return $utente;
    }

    public function info_utente($utente)
    {
        $utente = $this->GetDataUtente($utente);
        $righe_indirizzo = $this->righe_indirizzo($utente);
        if($utente->Genere=="D")
        {
            $informazioni['riga1'] = $utente->Ditta." ".$utente->Sigla_Forma_Giuridica;
            $informazioni['riga2'] = "Partita Iva: ".$utente->Partita_Iva;
            $informazioni['riga3'] = "Codice INPS: ".$utente->Azienda;
            $informazioni['riga4'] = "";
            $informazioni['riga5'] = "Indirizzo: ".$righe_indirizzo['Completo'];
        }
        else
        {
            $informazioni['riga1'] = $utente->Cognome." ".$utente->Nome;
            $informazioni['riga2'] = "Codice fiscale: ".$utente->Codice_Fiscale;
            $informazioni['riga3'] = "Comune di nascita: ".$utente->Comune_Nascita." (".$utente->Provincia_Nascita.") ".$utente->Paese_Nascita;
            $informazioni['riga4'] = "Data di nascita: ".$this->cls_date->Get_DateNewFormat($utente->Data_Nascita,"DB");// from_mysql_date($this->Data_Nascita);
            $informazioni['riga5'] = "Indirizzo: ".$righe_indirizzo['Completo'];
        }

        return $informazioni;

    }

    public function righe_indirizzo($utente)
    {
        if($utente->Recapito!=null)
            $indirizzo = $utente->Recapito;
        else if($utente->Domicilio!=null)
            $indirizzo = $utente->Domicilio;
        else
            $indirizzo = $utente->Residenza;

        if(strtoupper($indirizzo->Paese)=="ITALIA")
        {
            $ind_1 = $indirizzo->Toponimo->Nome;
            if($indirizzo->Frazione)
                $ind_1 = $indirizzo->Frazione.", ".$ind_1;

            if($indirizzo->Civico)
                $ind_1.= ", ".$indirizzo->Civico;
            if($indirizzo->Esponente)
                $ind_1.= " ".$indirizzo->Esponente;
            if($indirizzo->Interno)
                $ind_1.="/".$indirizzo->Interno;
            if($indirizzo->Dettagli)
                $ind_1.=", ".$indirizzo->Dettagli;

            $ind_3 = "";
        }
        else
        {
            $ind_1 = $indirizzo->Toponimo->Nome;
            if($indirizzo->Frazione)
                $ind_1 = $indirizzo->Frazione.", ".$ind_1;

            $ind_3 = $indirizzo->Paese;
        }

        $ind_2 = $indirizzo->Cap." ".$indirizzo->Comune;
        $ind_2_senza_prov = $ind_2;
        if($indirizzo->Provincia!=null)
            $ind_2.= " ".$indirizzo->Provincia;

        $indirizzo_destinatario = array();
        $indirizzo_destinatario['Riga1'] = $ind_1; // indirizzo destinatario

        /////////////////////
        $lunghezza = strlen($ind_1);
        if($lunghezza<50)
        {
            $indirizzo_destinatario['Riga1'] = strtoupper($ind_1);
            $indirizzo_destinatario['Riga2'] = strtoupper($ind_2);
            $indirizzo_destinatario['Riga3'] = strtoupper($ind_3);
            $indirizzo_destinatario['Riga4'] = "";
        }
        else if($lunghezza<=100)
        {
            $pos = $lunghezza/2;
            //echo $pos;
            for( $i=0; $i<$pos; $i++)
            {
                $carattere = substr(strtoupper($ind_1), $pos-$i,1);
                //echo $carattere."*";
                if($carattere==" ")
                {
                    //echo $pos-$i;
                    $pos = $pos-$i;
                    break;
                }
            }

            $indirizzo_destinatario['Riga1'] = substr(strtoupper($ind_1), 0 , $pos);
            $indirizzo_destinatario['Riga2'] = substr(strtoupper($ind_1), $pos+1);
            $indirizzo_destinatario['Riga3'] = strtoupper($ind_2);
            $indirizzo_destinatario['Riga4'] = strtoupper($ind_3);
        }
        ///////////////////////

        $indirizzo_destinatario['Completo'] = strtoupper($ind_1)." - ".strtoupper($ind_2);
        if($ind_3!="")
            $indirizzo_destinatario['Completo'].= ", ".strtoupper($ind_3);

        $indirizzo_destinatario['Senza_Provincia'] = strtoupper($ind_1)." - ".strtoupper($ind_2_senza_prov);
        if($ind_3!="")
            $indirizzo_destinatario['Senza_Provincia'].= ", ".strtoupper($ind_3);

        if($utente->Genere == "D")
        {
            $indirizzo_destinatario['Destinatario'] = $utente->Ditta;
            if($utente->Sigla_Forma_Giuridica!=null)
                $indirizzo_destinatario['Destinatario'].= " ".$utente->Sigla_Forma_Giuridica;
        }
        else
        {
            $indirizzo_destinatario['Destinatario'] = $utente->Cognome." ".$utente->Nome;
        }

        if(isset($utente->Recapito))
            if($utente->Recapito->ID>0)
                $indirizzo_destinatario['Destinatario'].= " C/O ".strtoupper($utente->Recapito->Presso);

        if(strlen($indirizzo_destinatario['Destinatario'])>45){
            $a_destinatario = array();
            $a_destinatario[0] = substr($indirizzo_destinatario['Destinatario'], 0, strrpos(substr($indirizzo_destinatario['Destinatario'], 0, 40), ' '));
            $a_destinatario[1] = substr($indirizzo_destinatario['Destinatario'], strlen($a_destinatario[0])+1, 40);
            $indirizzo_destinatario['a_destinatario'] = $a_destinatario;
        }

        return $indirizzo_destinatario;
    }



}