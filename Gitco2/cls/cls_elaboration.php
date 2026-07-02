<?php
include_once CLS . "/cls_DateTime.php";
include_once CLS . "/cls_db.php";

class cls_elaboration
{

    public $TipoRiscossione;
    public $DocumentTypeId;
    public $TableTypeId;
    public $ElaborationDate;
    public $StatusPignoramento;

    public $a_docExpireDays = array();
    public $a_positionStatus = array();
    public $a_checkPartita = array();
    public $a_parAnnuali = array();
    public $a_lockupPeriods = array();
    public $a_interessiTributi = array();

    private $cls_db = null;

    public function __construct($a_params = null)
    {
        if (!is_null($a_params))
            $this->setParams($a_params);

        $this->cls_db = new cls_db();

    }

    public function setParams($a_params)
    {
        if (isset($a_params['Elaboration_DocumentTypeId']))
            $this->DocumentTypeId = (int)$a_params['Elaboration_DocumentTypeId'];
        if (isset($a_params['Data_Elaborazione']))
            $this->ElaborationDate = $a_params['Data_Elaborazione'];
        if (isset($a_params['Tipo_Riscossione']))
            $this->TipoRiscossione = $a_params['Tipo_Riscossione'];
        if (isset($a_params['Position_Status']))
            $this->a_positionStatus = $a_params['Position_Status'];
        if (isset($a_params['Parametri_Annuali']))
            $this->a_parAnnuali = $a_params['Parametri_Annuali'];
        if (isset($a_params['Lockup_Periods']))
            $this->a_lockupPeriods = $a_params['Lockup_Periods'];
        if (isset($a_params['Interessi_Tributi']))
            $this->a_interessiTributi = $a_params['Interessi_Tributi'];
        if (isset($a_params['Doc_Expire_Days']))
            $this->a_docExpireDays = $a_params['Doc_Expire_Days'];
        if (isset($a_params['Elaboration_TableTypeId']))
            $this->TableTypeId = (int)$a_params['Elaboration_TableTypeId'];
    }

    public function totaliCodiciTributo($a_tipo, $a_importo)
    {
        $a_totali = array("TOTALE" => 0, "BASE_INTERESSI" => 0);
        for ($i = 0; $i < count($a_tipo); $i++) {

            if ($a_tipo[$i] !== 'PAGAMENTO')
                $a_totali['TOTALE'] += $a_importo[$i];
            else
                $a_totali['TOTALE'] -= $a_importo[$i];

            if ($a_tipo[$i] !== 'INTERESSI' && $a_tipo[$i] !== 'PAGAMENTO')
                $a_totali['BASE_INTERESSI']+= $a_importo[$i];
            else if ($a_tipo[$i] == 'PAGAMENTO')
                $a_totali['BASE_INTERESSI']-= $a_importo[$i];

            // if ($this->TipoRiscossione == 'CDS') {
            //     if ($a_tipo[$i] !== 'INTERESSI' && $a_tipo[$i] !== 'PAGAMENTO')
            //         $a_totali['BASE_INTERESSI']+= $a_importo[$i];
            //     else if ($a_tipo[$i] == 'PAGAMENTO')
            //         $a_totali['BASE_INTERESSI']-= $a_importo[$i];
            // } else{
            //     if ($a_tipo[$i] == 'IMPORTO')
            //         $a_totali['BASE_INTERESSI'] += $a_importo[$i];
            //     else if ($a_tipo[$i] == 'PAGAMENTO')
            //         $a_totali['BASE_INTERESSI']-= $a_importo[$i];
            // } 
        }

        return $a_totali;
    }

    public function calcInterests($a_params)
    {
        if ($a_params['DocumentTypeId'] != 2 && $a_params['DocumentTypeId'] != 12 
        && $a_params['DocumentTypeId']!=22 && $a_params['DocumentTypeId']!=7 && $a_params['DocumentTypeId']!=8)
            return 0;

        if ($this->TipoRiscossione == 'CDS')
            return $this->calcCDSInterests($a_params);
        else
            return $this->calcTributeInterests($a_params);
    }


    public function calcCDSInterests($a_params, $a_calcParams = array("percentage" => 10, "months" => 6))
    {
        if (is_null($a_params['StartDate']) || is_null($a_params['EndDate']))
            return 0;

        $days = $this->calcDays($a_params['StartDate'], $a_params['EndDate']);
        $blockDays = $this->calcBlockDays($a_params);
        $semestri = floor(($days - $blockDays) / ($a_calcParams['months'] * 30));

        return round(abs($a_params['BaseAmount']) * $a_calcParams['percentage'] / 100 * $semestri, 2);
    }

    public function calcTributeInterests($a_params)
    {
        if (is_null($a_params['StartDate']) || is_null($a_params['EndDate']))
            return 0;

        $interessi = 0;
        foreach ($this->a_interessiTributi as $ID => $a_interessi) {
            if ($a_interessi['Data_Inizio'] > $a_params['EndDate'] || (!is_null($a_interessi['Data_Fine']) && $a_interessi['Data_Fine'] < $a_params['StartDate']))
                continue;

            if ($a_interessi['Data_Inizio'] > $a_params['StartDate'])
                $startDate = $a_interessi['Data_Inizio'];
            else
                $startDate = $a_params['StartDate'];

            if (is_null($a_interessi['Data_Fine']) || $a_interessi['Data_Fine'] > $a_params['EndDate'])
                $endDate = $a_params['EndDate'];
            else
                $endDate = $a_interessi['Data_Fine'];

            $days = $this->calcDays($startDate, $endDate);
            $blockDays = $this->calcBlockDays($a_params, $startDate, $endDate);
            $calcDays = $days - $blockDays;
            $dailyImport = $a_params['BaseAmount'] / 100 * $a_interessi['Tasso_Interessi'] / 365;
            //            $a_days = array(
            //                "BaseAmount" => $a_params['BaseAmount'],
            //                "dataInizio" => $startDate,
            //                "dataFine" => $endDate,
            //                "days" => $days,
            //                "blockDays" => $blockDays,
            //                "calcDays" => $days-$blockDays,
            //                "interesse" => $a_interessi['Tasso_Interessi'],
            //                "importo_giornaliero" => $dailyImport,
            //                "importo_parziale" => $dailyImport*($days-$blockDays)
            //            );

            //            echo "<br>";
            //            var_dump($a_days);
            //            echo "<br>";

            $interessi += ($dailyImport * $calcDays);
        }


        return round($interessi, 2);
    }

    public function calcBlockDays($a_params, $parStartDate = null, $parEndDate = null)
    {
        if (is_null($parStartDate))
            $parStartDate = $a_params['StartDate'];
        if (is_null($parEndDate))
            $parEndDate = $a_params['EndDate'];

        //ESCLUSIONE IN BASE AL TIPO NON CORRISPONDENTE NEL Lockup_Type_Id
        if ($this->TipoRiscossione == "CDS") //2
            $checkLockupType = 3;
        else //3
            $checkLockupType = 2;

        $days = 0;

        //var_dump($this->a_lockupPeriods);die;

        foreach ($this->a_lockupPeriods as $id => $a_block) {
            if ($a_block['Lockup_Type_Id'] == $checkLockupType || $parStartDate > $a_block['End_Date'] || $a_block['Start_Date'] > $parEndDate)
                continue;

            if ($a_block['Start_Date'] > $parStartDate)
                $startDate = $a_block['Start_Date'];
            else
                $startDate = $parStartDate;

            if ($a_block['End_Date'] > $parEndDate)
                $endDate = $parEndDate;
            else
                $endDate = $a_block['End_Date'];

            $days += $this->calcDays($startDate, $endDate);
        }

        return $days;
    }


    public function calcDays($startDate, $endDate)
    {
        if (is_null($startDate) || is_null($endDate))
            return 0;

        $data1 = date_create($startDate);
        $data2 = date_create($endDate);
        $interval = date_diff($data1, $data2);
        return (int)$interval->format('%a') + 1;
    }

    public function checkPec($cf_pi, $pec, $inipecDate, $presso = null){
        $a_check = array("cf_pi"=>0, "pec"=>0,"expired"=>0,"inipec"=>0,"html"=>"");
        if(!empty($cf_pi) && $cf_pi!="00000000000")
            $a_check['cf_pi'] = 1;
        else{
            $a_check['html'] = "<i class=\"fa-solid fa-id-card fa-xl\" style='color: #2863c1; margin-top: 1.9rem' title='CF - PI Assente'></i>";
            return $a_check;
        }

        if(!empty($presso)){
            $a_check['html'] = "<i class=\"fa-solid fa-location-dot fa-xl\" style='color: #2863c1; margin-top: 1.9rem' title='Recapito presso ".$presso."'></i>";
            return $a_check;
        }

        if (!empty($pec)){
            $a_check['pec'] = 1;
        }
        if(!empty($inipecDate)){
            $date1 = date_create(date('Y-m-d'));
            $date2 = date_create($inipecDate);
            $diff = date_diff($date1,$date2);
            $days = (int)$diff->format("%a");
            if($days>15)
                $a_check['expired'] = 1;
        }

        if($a_check['pec']==1){
            if($a_check['expired']==0)
                $a_check['html'] = "<i class=\"fa-solid fa-envelope fa-xl\" style='color: darkgreen; margin-top: 1.9rem' title='".$pec."'></i>";
            else if($a_check['expired']==1){
                $a_check['html'] = "<i class=\"fa-solid fa-envelope fa-xl\" style='color: darkred; margin-top: 1.9rem' title='".$pec." aggiornata ".$days." giorni fa'></i>";
                $a_check['inipec'] = 1;
            }

        }
        else{
            if($a_check['expired']==0){
                if(!empty($inipecDate))
                    $a_check['html'] = "<i class=\"fa-solid fa-circle-minus fa-xl\" style='color: darkgreen; margin-top: 1.9rem' title='Pec controllata ".$days." giorni fa'></i>";
                else{
                    $a_check['html'] = "<i class=\"fa-solid fa-circle-xmark fa-xl\" style='color: darkred; margin-top: 1.9rem' title='Pec da verificare'></i>";
                    $a_check['inipec'] = 1;
                }

            }
            else{
                $a_check['html'] = "<i class=\"fa-solid fa-circle-xmark fa-xl\" style='color: darkred; margin-top: 1.9rem' title='Pec controllata ".$days." giorni fa'></i>";
                $a_check['inipec'] = 1;
            }

        }




        return $a_check;
    }


    /**
     * CONTROLLI PRE ELABORAZIONE
     * position_status
     *
     * Procedura di controllo partite per verificare la possibilità di elaborazione degli atti
     */
    public function checkBloccoCoazione()
    {

        if ($this->a_checkPartita['Flag_Blocco_Coazione'] == 'si')
            return array(
                'position_status' => 10,
                'flag_elaboration' => 0

            );

        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
    }

    //? Controllo su flag di sospensione della partita contabile
    //? Il campo Flag_Sospensione della tabella partita_tributi valorizzato a "si" indica una sospensione attiva
    //? In tutti gli altri casi il controllo viene superato
    public function checkFlagSospensione()
    {
        if (!empty($this->a_checkPartita['Flag_Sospensione']) && $this->a_checkPartita['Flag_Sospensione'] == 'si')
            return array(
                'position_status' => 46,
                'flag_elaboration' => 0

            );

        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
    }

    //? Controllo su flag di sospensione della partita contabile
    //? Il campo Flag_Sospensione della tabella partita_tributi valorizzato a "si" indica una sospensione attiva
    //? In tutti gli altri casi il controllo viene superato
    public function checkFlagCoobbligati()
    {
        if (!empty($this->a_checkPartita['Flag_Coobbligati']) && $this->a_checkPartita['Flag_Coobbligati'] == 1)
            return array(
                'position_status' => 48,
                'flag_elaboration' => 0

            );

        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
    }

    public function checkPagamento()
    {
        if (is_null($this->a_checkPartita['Totale_Dovuto_ATTO']))
            $this->a_checkPartita['Totale_Dovuto_ATTO'] = 0;

        if (is_null($this->a_checkPartita['TOTALE_PAGAMENTI']))
            $this->a_checkPartita['TOTALE_PAGAMENTI'] = 0;

        if (!is_null($this->a_checkPartita['DocumentTypeId']) && ($this->a_checkPartita['Totale_Dovuto_ATTO'] - $this->a_checkPartita['TOTALE_PAGAMENTI']) < $this->a_parAnnuali['Importo_Minimo'])
            return array(
                'position_status' => 4,
                'flag_elaboration' => 0
            );

        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
     
    }

    public function checkPagamentoConPignoramento()
    {
        $totaleDovuto = 0;
        if(!$this->checkPignoramentoAnnullato() && !empty($this->a_checkPartita['Totale_Dovuto_PG'])){
            if($this->a_checkPartita['Rate_Previste']>0){
                if($this->a_checkPartita['Tipo_Totale_Rate_PG']==1)
                    $totaleDovuto = $this->a_checkPartita['Totale_1_PG'];
                else if($this->a_checkPartita['Tipo_Totale_Rate_PG']==2)
                    $totaleDovuto = $this->a_checkPartita['Totale_2_PG'];
                else if($this->a_checkPartita['Tipo_Totale_Rate_PG']==3)
                    $totaleDovuto = $this->a_checkPartita['Totale_3_PG'];
                else
                    $totaleDovuto = $this->a_checkPartita['Totale_Dovuto_PG'];
            }
            else
                $totaleDovuto = $this->a_checkPartita['Totale_Dovuto_PG'];
            
            if (!is_null($this->a_checkPartita['DocumentTypeId_PG']) && ($totaleDovuto - $this->a_checkPartita['TOTALE_PAGAMENTI_PG']) < $this->a_parAnnuali['Importo_Minimo'])
                return array(
                    'position_status' => 31,
                    'flag_elaboration' => 0
    
                );
        }            
        else if (!empty($this->a_checkPartita['Totale_Dovuto_ATTO'])){
            $tot_pagamenti = 0;
            if(!empty($this->a_checkPartita['TOTALE_PAGAMENTI']))
                $tot_pagamenti+=$this->a_checkPartita['TOTALE_PAGAMENTI'];
            if(!empty($this->a_checkPartita['TOTALE_PAGAMENTI_PG']))
                $tot_pagamenti+=$this->a_checkPartita['TOTALE_PAGAMENTI_PG'];

            if($this->a_checkPartita['Rate_Previste']>0){
                if($this->a_checkPartita['Totale_Rateizzato']>0)
                    $totaleDovuto = $this->a_checkPartita['Totale_Rateizzato'];
                else if($this->a_checkPartita['Tipo_Totale_Rate']==1)
                    $totaleDovuto = $this->a_checkPartita['Totale_1_ATTO'];
                else if($this->a_checkPartita['Tipo_Totale_Rate']==2)
                    $totaleDovuto = $this->a_checkPartita['Totale_2_ATTO'];
            }
            else if(empty($this->a_checkPartita['Data_Notifica']))
                $totaleDovuto = $this->a_checkPartita['Totale_1_ATTO'];
            else{
                $checkDate = date('Y-m-d', strtotime($this->a_checkPartita['Data_Notifica']. ' + 60 days'));
                if($tot_pagamenti>0 && $this->a_checkPartita['Data_Pagamento']<$checkDate)
                    $totaleDovuto = $this->a_checkPartita['Totale_1_ATTO'];
                else
                    $totaleDovuto = $this->a_checkPartita['Totale_2_ATTO'];

                if($tot_pagamenti>0){
                    if($totaleDovuto >$tot_pagamenti+$this->a_parAnnuali['Importo_Minimo']){
                        if(date('Y-m-d')>$checkDate)
                            $totaleDovuto = $this->a_checkPartita['Totale_2_ATTO'];
                    }
                }

            }

            if (!is_null($this->a_checkPartita['DocumentTypeId']) && ($totaleDovuto - $tot_pagamenti) < $this->a_parAnnuali['Importo_Minimo'])
                return array(
                    'position_status' => 4,
                    'flag_elaboration' => 0
    
                );
        }

        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
     
    }

    public function checkRateizzazioneConPignoramento()
    {
        if (!$this->checkPignoramentoAnnullato() && !empty($this->a_checkPartita['Totale_Dovuto_PG']) && $this->a_checkPartita['Rate_Previste_PG'] > 0 )
            return array(
                'position_status' => 32,
                'flag_elaboration' => 0
            );
        else if (!empty($this->a_checkPartita['Totale_Dovuto_ATTO']) && $this->a_checkPartita['Rate_Previste'] > 0 )
            return array(
                'position_status' => 22,
                'flag_elaboration' => 0
            );

        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
    }

    public function checkDataNotifica()
    {
        if (empty($this->a_checkPartita['DocumentTypeId_PG']) && !is_null($this->a_checkPartita['DocumentTypeId']))
        {
            if(is_null($this->a_checkPartita['Data_Notifica_Atto'])){
                if(!empty($this->a_checkPartita['Motivo_Notifica_ATTO'])){
                    if($this->a_checkPartita['Motivo_Notifica_ATTO']>=19 && $this->a_checkPartita['Motivo_Notifica_ATTO']<=26)
                        return array(
                            'position_status' => 20,
                            'flag_elaboration' => 0
                        );
                    else
                        return array(
                            'position_status' => 17,
                            'flag_elaboration' => 0
                        );
    
                }
    
                if($this->DocumentTypeId != 3 && $this->DocumentTypeId != 11 ){
                    if(is_null($this->a_checkPartita['Data_Flusso_ATTO'])){
                        return array(
                            'position_status' => 19,
                            'flag_elaboration' => 0
                        );
                    }
                    else {
                        $flowDate = date_create($this->a_checkPartita['Data_Flusso_ATTO']);
                        $today = date_create();
                        $diff = date_diff($flowDate, $today);
                        $giorni_attesa = (int)$diff->format("%a");
                        if ($giorni_attesa < 100) {
                            return array(
                                'position_status' => 18,
                                'flag_elaboration' => 0
                            );
                        } else {
                            return array(
                                'position_status' => 5,
                                'flag_elaboration' => 1
                            );
                        }
                    }
                }
                else
                    return array(
                        'position_status' => 5,
                        'flag_elaboration' => 0
                    );
            }
            else{
                if(!empty($this->a_checkPartita['Modalita_Notifica_ATTO'])){
                    if($this->a_checkPartita['Modalita_Notifica_ATTO']==11 || $this->a_checkPartita['Modalita_Notifica_ATTO']==12){
    
                        if(empty($this->a_checkPartita['Stato_Notifica_ATTO']))
                            return array(
                                'position_status' => 27,
                                'flag_elaboration' => 0
                            );
                        else{
                            if($this->a_checkPartita['Stato_Notifica_ATTO']==28 && 
                            ($this->a_checkPartita['Indirizzo_Validato_ATTO']!="si" || is_null($this->a_checkPartita['Indirizzo_Validato_ATTO']))){
                                return array(
                                    'position_status' => 30,
                                    'flag_elaboration' => 0
                                );
                            }
                            else if(empty($this->a_checkPartita['CAD_Fronte_ATTO'])){
                                return array(
                                    'position_status' => 28,
                                    'flag_elaboration' => 0
                                );
                                
                            }
                            else if($this->a_checkPartita['Indirizzo_Validato_ATTO']!="si" || is_null($this->a_checkPartita['Indirizzo_Validato_ATTO'])){
                                return array(
                                    'position_status' => 29,
                                    'flag_elaboration' => 0
                                );
                            }
                        }    
    
    
                    }
                        
                }
            }
            
        }

        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
    }

    public function checkDataNotificaPignoramento()
    {
        if (!empty($this->a_checkPartita['DocumentTypeId_PG']))
        {
            if(is_null($this->a_checkPartita['DATA_NOTIFICA_PG'])){
                if(!empty($this->a_checkPartita['Motivo_Notifica_PG'])){
                    if($this->a_checkPartita['Motivo_Notifica_PG']>=19 && $this->a_checkPartita['Motivo_Notifica_PG']<=26)
                        return array(
                            'position_status' => 33,
                            'flag_elaboration' => 0
                        );
                    else
                        return array(
                            'position_status' => 34,
                            'flag_elaboration' => 0
                        );
    
                }
    

                if(is_null($this->a_checkPartita['Data_Flusso_PG'])){
                    return array(
                        'position_status' => 37,
                        'flag_elaboration' => 0
                    );
                }
                else {
                    $flowDate = date_create($this->a_checkPartita['Data_Flusso_PG']);
                    $today = date_create();
                    $diff = date_diff($flowDate, $today);
                    $giorni_attesa = (int)$diff->format("%a");
                    if ($giorni_attesa < 100) {
                        return array(
                            'position_status' => 36,
                            'flag_elaboration' => 0
                        );
                    } else {
                        return array(
                            'position_status' => 35,
                            'flag_elaboration' => 1
                        );
                    }
                    }
                

            }
            else{
                if(!empty($this->a_checkPartita['Modalita_Notifica_PG'])){
                    if($this->a_checkPartita['Modalita_Notifica_PG']==11 || $this->a_checkPartita['Modalita_Notifica_PG']==12){
    
                        if(empty($this->a_checkPartita['Stato_Notifica_PG']))
                            return array(
                                'position_status' => 41,
                                'flag_elaboration' => 0
                            );
                        else{
                            if($this->a_checkPartita['Stato_Notifica_PG']==28 && 
                            ($this->a_checkPartita['Indirizzo_Validato_PG']!="si" || is_null($this->a_checkPartita['Indirizzo_Validato_PG']))){
                                return array(
                                    'position_status' => 44,
                                    'flag_elaboration' => 0
                                );
                            }
                            else if(empty($this->a_checkPartita['CAD_Fronte_PG'])){
                                return array(
                                    'position_status' => 42,
                                    'flag_elaboration' => 0
                                );
                                
                            }
                            else if($this->a_checkPartita['Indirizzo_Validato_PG']!="si" || is_null($this->a_checkPartita['Indirizzo_Validato_PG'])){
                                return array(
                                    'position_status' => 43,
                                    'flag_elaboration' => 0
                                );
                            }
                        }    
    
    
                    }
                        
                }
            }
            
        }

        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
    }

    public function checkDataNotificaConPignoramento(){
        if (!$this->checkPignoramentoAnnullato() && !empty($this->a_checkPartita['DocumentTypeId_PG']))
            return $this->checkDataNotificaPignoramento();
        else
            return $this->checkDataNotifica();
    }

    public function checkAppeal()
    {
        if (!is_null($this->a_checkPartita['APPEAL_ID']))
            return array(
                'position_status' => 6,
                'flag_elaboration' => 0
            );

        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
    }

    public function checkPignoramentoAnnullato(){
        if (strpos($this->a_checkPartita['Stato_Pignoramento'], 'Annullato') === false && strpos($this->a_checkPartita['Stato_Pignoramento'], 'Archiviato') === false)
            return false;
        else
            return true;
    }

    public function checkPignoramento()
    {
        $this->StatusPignoramento = null;
        if (!empty($this->a_checkPartita['DocumentTypeId_PG'])) {
            if (!$this->checkPignoramentoAnnullato())
                return array(
                    'position_status' => 7,
                    'flag_elaboration' => 0
                );
        }

        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
    }

    public function checkAttoDaElaborareConPignoramentoAttivo()
    {
        $this->StatusPignoramento = null;
        if (!empty($this->a_checkPartita['DocumentTypeId_PG'])) {
            if (!$this->checkPignoramentoAnnullato())
                if($this->DocumentTypeId!=2)
                    return array(
                        'position_status' => 45,
                        'flag_elaboration' => 0
                    );
        }

        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
    }
    
    public function checkDeath()
    {
        /**
         * USA DATA MORTE controlla se non è null + pezzo CDS
         */
        if (!is_null($this->a_checkPartita['Data_Morte'])) {
            if ($this->a_checkPartita['Tipo_Riscossione'] == "CDS")
                return array(
                    'position_status' => 11,
                    'flag_elaboration' => 0
                );
            else if (is_null($this->a_checkPartita['Rec_ID']) || empty($this->a_checkPartita['Rec_Presso']))
                return array(
                    'position_status' => 14,
                    'flag_elaboration' => 0
                );
        }

        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
    }

    public function checkRielabora()
    {
        if (strpos($this->a_checkPartita['Rielabora_Flag'], 'si') !== false)
        {
            if($this->TableTypeId == 2 || $this->DocumentTypeId == 3 || $this->DocumentTypeId == 11)
                return array(
                    'position_status' => 16,
                    'flag_elaboration' => 0
                );
            else if(!$this->checkPignoramentoAnnullato() && !empty($this->a_checkPartita['DocumentTypeId_PG']))
                return array(
                    'position_status' => 38,
                    'flag_elaboration' => 0
                );
            else
                return array(
                    'position_status' => 2,
                    'flag_elaboration' => 1
                );

        }

        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
    }

    public function checkRettifica()
    {
        if (strpos($this->a_checkPartita['Rettifica_Flag'], 'si') !== false)
        {
            if($this->DocumentTypeId == $this->a_checkPartita['DocumentTypeId']){
                if(!$this->checkPignoramentoAnnullato() && empty($this->a_checkPartita['DocumentTypeId_PG']))
                    return array(
                        'position_status' => 3,
                        'flag_elaboration' => 1
                    );
                else
                    return array(
                        'position_status' => 39,
                        'flag_elaboration' => 0
                    );  
            }
            else
                return array(
                    'position_status' => 15,
                    'flag_elaboration' => 0
                );              
        }

        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
    }

    //controllo prescrizione
    public function checkPartitaExpiration($startDate = null, $endDate = null)
    {
        $arr = array();
        if(!empty($this->a_checkPartita['Partita_Data_Decorrenza']))
            $arr[] = $this->a_checkPartita['Partita_Data_Decorrenza'];
        else
            return array(
                'position_status' => 21,
                'flag_elaboration' => 0
            );

        if(!empty($this->a_checkPartita['Data_Notifica_Atto']))
            $arr[] = $this->a_checkPartita['Data_Notifica_Atto'];
        if(!empty($this->a_checkPartita['Last_Data_Notifica_Atto']))
            $arr[] = $this->a_checkPartita['Last_Data_Notifica_Atto'];
        if (is_null($this->StatusPignoramento) && !empty($this->a_checkPartita['DATA_NOTIFICA_PG']))
            $arr[] = $this->a_checkPartita['DATA_NOTIFICA_PG'];

        if(count($arr)>1)
            $primoAttoEmesso = true;
        else
            $primoAttoEmesso = false;

        $max = max(array_map('strtotime', $arr));
        if (is_null($startDate))
            $startDate = date('Y-m-d', $max);
        if (is_null($endDate))
            $endDate = $this->ElaborationDate;

        if($this->checkPrescription($startDate, $endDate, $primoAttoEmesso))
            return array(
                'position_status' => 8,
                'flag_elaboration' => 0
            );
        else if(!empty($this->a_checkPartita['Data_Notifica_Primo_Atto'])){
            if($this->checkPrescription($this->a_checkPartita['Partita_Data_Decorrenza'], $this->a_checkPartita['Data_Notifica_Primo_Atto'], false))
                return array(
                    'position_status' => 47,
                    'flag_elaboration' => 0
                );
        }


        // if ($this->TipoRiscossione == "CDS" || $this->TipoRiscossione == "PATRIMONIALE" || count($arr)>1)
        //     $days = 365*5;
        // else{
        //     $days = 365*3;
        //     $year = date('Y', $max);
        //     if($startDate < $year.'-03-01')
        //         $startDate = ($year-1).'-12-31';
        //     else
        //         $startDate = ($year).'-12-31';
        // }

        // $a_params = array(
        //     'StartDate' => $startDate,
        //     'EndDate' => $endDate
        // );

        // $blockDays = $this->calcBlockDays($a_params);
        // $totalDays = $days + $blockDays;

        // $prescriptionDate = date_create($startDate);// ultima data di notifica (atto o pigno) o data decorrenza partita
        // date_add($prescriptionDate, date_interval_create_from_date_string($totalDays . " days"));// data prescrizione posizione

        // if ($endDate > $prescriptionDate->format('Y-m-d')) {
        //     return array(
        //         'position_status' => 8,
        //         'flag_elaboration' => 0
        //     );
        // }

        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
    }

    public function checkPrescription($startDate, $endDate, bool $primoAttoEmesso){
        if ($this->TipoRiscossione == "CDS" || $this->TipoRiscossione == "PATRIMONIALE" || $primoAttoEmesso===true)
            $days = 365*5;
        else{
            $days = 365*3;
            $year = date('Y', strtotime($startDate));
            if($startDate < $year.'-03-01')
                $startDate = ($year-1).'-12-31';
            else
                $startDate = ($year).'-12-31';
        }

        $a_params = array(
            'StartDate' => $startDate,
            'EndDate' => $endDate
        );

        $blockDays = $this->calcBlockDays($a_params);
        $totalDays = $days + $blockDays;

        $prescriptionDate = date_create($startDate);// ultima data di notifica (atto o pigno) o data decorrenza partita
        date_add($prescriptionDate, date_interval_create_from_date_string($totalDays . " days"));// data prescrizione posizione

        if ($endDate > $prescriptionDate->format('Y-m-d'))
            return true;
        else
            return false;
    }

    // controllo atto nei termini se non è sollecito ingiunzione
    public function checkActExpiration($startDate = null, $endDate = null, $expireDays = null)
    {

        if (!is_null($this->a_checkPartita['DocumentTypeId'])) {
            if (isset($this->a_docExpireDays[$this->a_checkPartita['DocumentTypeId']])) {
                if ($expireDays == null)
                    $expireDays = $this->a_docExpireDays[$this->a_checkPartita['DocumentTypeId']]['ExpireDays'];

                if (is_null($startDate))
                    $startDate = $this->a_checkPartita['Data_Notifica_Atto'];
                if (is_null($endDate))
                    $actElaborationDate = $this->ElaborationDate; //elaboration_date da passare nei parametri della classe
                else
                    $actElaborationDate = $endDate;


                $a_params = array(
                    'StartDate' => $startDate,
                    'EndDate' => $actElaborationDate
                );
                $blockDays = $this->calcBlockDays($a_params);
                $totalDays = $expireDays + $blockDays;
                


                $expirationDate = date_create($startDate); // or your date string
                date_add($expirationDate, date_interval_create_from_date_string($totalDays . " days"));

                //sto elaborando atto diverso da sollecito (!=3) e la data di scadenza del precedente atto è maggiore della data di elaborazione dell'atto
                //in questo caso la scadenza non è ancora passata per cui l'atto precedente è nei termini e il controllo interviene
                if ($this->DocumentTypeId != 3 && $expirationDate->format('Y-m-d') > $actElaborationDate) {
                    /*
                    var_dump("sto elaborando atto diverso da sollecito");
                    var_dump('DocumentTypeId: ');
                    var_dump($this->DocumentTypeId);
                    var_dump('StartDate:  '.$startDate,);
                    var_dump('blockDays:  '.$blockDays,);
                    var_dump('totalDays:  '.$totalDays,);
                    var_dump('expirationDate: ');
                    var_dump($expirationDate->format('Y-m-d'));
                    
                    var_dump('actElaborationDate: ');
                    var_dump($actElaborationDate);
                    var_dump($expirationDate->format('Y-m-d').">". $actElaborationDate);
                    var_dump(9);

                    die;
                   */

                   
                    return array(
                        'position_status' => 9,
                        'flag_elaboration' => 0
                    );
                }
            }
        }

        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
    }

    // controllo fuori termine solo per sollecito ingiunzione
    public function checkSollecitoExpiration($startDate = null, $endDate = null, $expireDays = null)
    {
        if (!is_null($this->a_checkPartita['DocumentTypeId']) && $this->DocumentTypeId == 3 
        && (empty($this->a_checkPartita['DocumentTypeId_PG']) || $this->checkPignoramentoAnnullato())) {
            if (isset($this->a_docExpireDays[$this->a_checkPartita['DocumentTypeId']])) {
                if ($expireDays == null)
                    $expireDays = $this->a_docExpireDays[$this->a_checkPartita['DocumentTypeId']]['ExpireDays'];

                if (is_null($startDate))
                    $startDate = $this->a_checkPartita['Data_Notifica_Atto']; //DATA NOTIFICA (ING)
                if (is_null($endDate))
                    $actElaborationDate = $this->ElaborationDate; //elaboration_date da passare nei parametri della classe
                else
                    $actElaborationDate = $endDate;

                $a_params = array(
                    'StartDate' => $startDate,
                    'EndDate' => $actElaborationDate
                );

                $blockDays = $this->calcBlockDays($a_params);
                $totalDays = $expireDays + $blockDays;
                $expirationDate = date_create($startDate); //setto come data iniziale la data di notifica del precedente atto
                date_add($expirationDate, date_interval_create_from_date_string($totalDays . " days")); // aggiungo i giorni per trovare la data di scadenza dei termini
                //$date non è più la data di notifica ma la data di scadenza del termine

                //sto elaborando sollecito (3) e la data di scadenza del precedente atto è minore della data di elaborazione del sollecito
                //in questo caso la scadenza è gia passata per cui l'atto precedente è fuori termine e il controllo interviene
                if ($expirationDate->format('Y-m-d') < $actElaborationDate) {

                    /*
                        var_dump("sto elaborando sollecito");
                        var_dump('DocumentTypeId: ');
                        var_dump($this->DocumentTypeId);
                        var_dump('a_checkPartita DocumentTypeId: ');
                        var_dump($this->a_checkPartita['DocumentTypeId']);
                        var_dump('expirationDate: ');
                        var_dump($expirationDate->format('Y-m-d'));
                        var_dump('actElaborationDate: ');
                        var_dump($actElaborationDate);
                        var_dump($expirationDate->format('Y-m-d')."<". $actElaborationDate);
                        var_dump(13); 
                       
                    die; 
                  */
                   
                    return array(
                        'position_status' => 13,
                        'flag_elaboration' => 0
                    );
                } //ING O AVV O AVV MORA FUORI TERMINE (vale solo per sollecito)
                else {
                    /*
                    var_dump("sto elaborando sollecito");
                   
                    var_dump('a_checkPartita DocumentTypeId: ');
                    var_dump($this->a_checkPartita['DocumentTypeId']);
                    var_dump('DocumentTypeId: ');
                    var_dump($this->DocumentTypeId);
                    var_dump('expirationDate: ');
                    var_dump($expirationDate->format('Y-m-d'));
                    var_dump('actElaborationDate: ');
                    var_dump($actElaborationDate);
                    var_dump($expirationDate->format('Y-m-d').">=". $actElaborationDate);
                    var_dump('NO 13');
                    die;
                    */
                }
            }
        }
        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
    }

    public function checkPresenzaIngiunzione()
    {
        if (($this->DocumentTypeId == 3 || $this->DocumentTypeId == 4) 
        && (int)$this->a_checkPartita['DocumentTypeId'] != 2 && (empty($this->a_checkPartita['DocumentTypeId_PG']) || $this->checkPignoramentoAnnullato()))
            return array(
                'position_status' => 12,
                'flag_elaboration' => 0
            );

        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
    }

    public function checkPresenzaAtto()
    {
     
        if($this->TableTypeId == 2 && (int)$this->a_checkPartita['DocumentTypeId'] != 2 && (int)$this->a_checkPartita['DocumentTypeId'] != 4)    
            return array(
                'position_status' => 23,
                'flag_elaboration' => 0
            );

        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
    }

    public function checkRateizzazione()
    {

        if ( $this->a_checkPartita['Rate_Previste'] > 0 )
            return array(
                'position_status' => 22,
                'flag_elaboration' => 0
            );
        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
    }

    public function getTributi($partita_id){
        $query = "SELECT T.Anno_Tributo, T.Codice_Tributo, T.Imposta, CT.Tipo_Codice,
                    IF(T.Tipo_Sanzione = 'VE','Verbale',
                    IF(T.Tipo_Sanzione = 'AC','Accertamento',
                    IF(T.Tipo_Sanzione = 'OR','Ordinanza',
                    IF(T.Tipo_Sanzione = 'IN','Ingiunzione',
                    IF(T.Tipo_Sanzione = 'DM','Decreto Ministeriale',''))))) 
                    AS Tipo_Sanzione 
                    FROM `tributo` AS T
                    JOIN codice_tributo AS CT ON CT.codice_tributo=T.codice_tributo
                    where T.Partita_ID = " . $partita_id;

        $a_tributi = $this->cls_db->getResults($this->cls_db->ExecuteQuery($query));
        $descrCodTrib = "";

        $totaleTributi = 0;
        for ($i = 0; $i < count($a_tributi); $i++) {

            if ($a_tributi[$i]["Tipo_Codice"] == "PAGAMENTO")
                $totaleTributi -= (float) $a_tributi[$i]["Imposta"];
            else
                $totaleTributi += (float) $a_tributi[$i]["Imposta"];

            $descrCodTrib .= " - Codice Tributo: " . $a_tributi[$i]["Codice_Tributo"] . ", Anno: " . $a_tributi[$i]["Anno_Tributo"] . 
                ", Tipo atto: " . $a_tributi[$i]["Tipo_Sanzione"] . ", Importo: " . $a_tributi[$i]["Imposta"] . "\n";
        }

        return array(
            'description' => $descrCodTrib,
            'total' => round($totaleTributi,2)
        );
    }

    public function getAmounts($a_checkPartita, $totale_tributi){

        if (empty($a_checkPartita['TOTALE_PAGAMENTI']))
            $a_checkPartita['TOTALE_PAGAMENTI'] = 0;

        if(empty($a_checkPartita['Totale_Dovuto_ATTO']))
            $totale = $totale_tributi;
        else{

            $flagAct = true;
            if(!is_null($a_checkPartita['ID_CRONOLOGICO_PG']) 
                && $a_checkPartita['Stato_Pignoramento']!="Annullato" 
                && $a_checkPartita['Stato_Pignoramento']!="Archiviato"){
                
                $date = new cls_DateTime($a_checkPartita['Data_Elaborazione_Pignoramento'],"DB");
                if($date->CompareDate("DB",">",$a_checkPartita['Data_Elaborazione_ATTO']))
                {
                    $flagAct = false;
                    $totale = (float)$a_checkPartita['Totale_Dovuto_PG'];
                }
            }
            
            if($flagAct){
                if(is_null($a_checkPartita['Rate_Previste_Atto']) || ((int)$a_checkPartita['Rate_Previste_Atto'] === 0 && $a_checkPartita['Rate_Previste_Atto'] !== null) ){
                    $totale = (float)$a_checkPartita['Totale_Dovuto_Atto_Iniziale'] + (float)$a_checkPartita['Diritto_Riscossione_Massimo_ATTO'];
                }       
                else{
                    $data_not = new cls_DateTime($a_checkPartita['Data_Notifica_Atto'],"DB");
                    $data_not->AddDay(60);
                    if($data_not->CompareDate("DB","<",$a_checkPartita['Data_Richiesta_Rate_Atto']))
                        $totale = (float)$a_checkPartita['Totale_Dovuto_Atto_Iniziale'] + (float)$a_checkPartita['Diritto_Riscossione_Massimo_ATTO'];
                    else 
                    {
                        $num_rate = $a_checkPartita['Rate_Previste_Atto'] === null ? 0 : (int)$a_checkPartita['Rate_Previste_Atto'];
                        $dateRate = explode("*",$a_checkPartita['Scadenze_Rate_Atto']);
                        $flagAllPayment = true;
                        for($i = 0,$a=1; $i < $num_rate; $i++,$a++){
                            $data_rate_temp = explode("/",$dateRate[$i]);
                            $data_rate = $data_rate_temp[2]."-".$data_rate_temp[1]."-".$data_rate_temp[0];
                            $data_rate = new cls_DateTime($data_rate,"DB");

                            $query = "SELECT Importo FROM pagamento WHERE Atto_ID = ".$a_checkPartita['ID_ATTO']." AND DocumentTypeId = ".$a_checkPartita['DocumentTypeId']." AND Rata = ".$a;
                            $result = $this->cls_db->getArrayLineNull($this->cls_db->ExecuteQuery($query),"pagamento");
                            if($result["Importo"] == null){
                                $totale = (float)$a_checkPartita['Totale_Dovuto_Atto_Iniziale'] + (float)$a_checkPartita['Diritto_Riscossione_Massimo_ATTO'];
                                $flagAllPayment = false;
                                break;
                            }
                        }

                        if($flagAllPayment){
                            $totale = (float)$a_checkPartita['Totale_Rateizzato_Atto'];
                        }
                    }
                }
            }
        }

        $residual = $totale - (float)$a_checkPartita['TOTALE_PAGAMENTI'];
        if($residual<0) 
            $residual = 0;

        $a_return = array(
            'residual' => round($residual,2),
            'total' => round($totale,2),
            'payments' => round($a_checkPartita['TOTALE_PAGAMENTI'],2)
        );
        return $a_return;
    }

    public function getPositionStatus($a_checkPartita)
    {
        $a_return = array(
            "position_status" => 1,
            "v_checkPartita" => 1,
            "parametri_annuali" => 1,
            "flag_elaboration" => 1,
            "check_error" => 0
        );

        if (!isset($a_checkPartita['CC'])) {

            $a_return['v_checkPartita'] = 0;
            $a_return['check_error'] = 1;

            return $a_return;
        } else
            $this->a_checkPartita = $a_checkPartita;

        if (!isset($this->a_parAnnuali['Importo_Minimo'])) {

            $a_return['parametri_annuali'] = 0;
            $a_return['check_error'] = 1;

            return $a_return;
        }

        //  La Partita è stata pagata
        if ($a_return['position_status'] == 1) {
            $a_return = $this->checkPagamentoConPignoramento();
        }

        //  Utente deceduto
        if ($a_return['position_status'] == 1) {
            $a_return = $this->checkDeath();
        }

        //   La Partita è stata bloccata
        if ($a_return['position_status'] == 1) {
            $a_return = $this->checkBloccoCoazione();
        }

        //   La Partita è stata sospesa
        if ($a_return['position_status'] == 1) {
            $a_return = $this->checkFlagSospensione();
        }

        //   La Partita è stata bloccata per presenza coobbligati
        if ($a_return['position_status'] == 1) {
            $a_return = $this->checkFlagCoobbligati();
        }

        //  Ricorso
        if ($a_return['position_status'] == 1) {
            $a_return = $this->checkAppeal();
        }

         // partita prescritta
         if ($a_return['position_status'] == 1) {
            $a_return = $this->checkPartitaExpiration();
         }

        //Tipo di atto da elaborare permesso con pignoramento presente - solo Ingiunzione
        if ($a_return['position_status'] == 1) {
            $a_return = $this->checkAttoDaElaborareConPignoramentoAttivo();
        }

        // //  Pignoramento
        // if ($a_return['position_status'] == 1) {
        //     $a_return = $this->checkPignoramento();
        // }

        //  Rateizzazione
        if ($a_return['position_status'] == 1) {
            $a_return = $this->checkRateizzazioneConPignoramento();
        }

        // Rettifica flag
        if ($a_return['position_status'] == 1) {
            $a_return = $this->checkRettifica();
        }

        //  Rielabora flag
        if ($a_return['position_status'] == 1) {
            $a_return = $this->checkRielabora();
        }
        
        //  Notificata
        if ($a_return['position_status'] == 1) {
            $a_return = $this->checkDataNotificaConPignoramento();
        }

         // Assenza Ingiunzione
         if ($a_return['position_status'] == 1) {
            $a_return = $this->checkPresenzaIngiunzione();
        }
       
        //nei termini
        if ($a_return['position_status'] == 1) {
            $a_return = $this->checkActExpiration();
        }

        //nei termini Sollecito
        if ($a_return['position_status'] == 1) {
            $a_return = $this->checkSollecitoExpiration();
        }
       
       
        $a_return["v_checkPartita"] =1;
        $a_return["parametri_annuali"] =1;
        $a_return["check_error"] = 0;

        /*   var_dump($this->a_checkPartita['Partita_ID']);
        var_dump($this->a_checkPartita['Comune_ID']);
       var_dump($a_return);*/
        
        return $a_return;
    }

    
}
