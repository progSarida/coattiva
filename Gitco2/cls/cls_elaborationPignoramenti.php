<?php
include_once CLS . "/cls_LOG.php";
include_once CLS . "/cls_elaboration.php";

class cls_elaborationPignoramenti extends cls_elaboration
{
     public $log;

     public function __construct($a_params = null)
     {
        parent::__construct($a_params);
        $this->log = new LOG();
     }

     /**
     * CONTROLLI PRE ELABORAZIONE
     * position_status
     *
     * Procedura di controllo partite per verificare la possibilità di elaborazione dei pignoramenti
     * 
     */

    private function logga($msg)
    {
        $partita_id = $this->a_checkPartita['Partita_ID'];
        $this->log->info("Partita ID : $partita_id ".$msg);
    }
    private function ScriviLog($position_status)
    {
        switch($position_status)
        {
            case 1	:$this->logga("Elaborabile"); break;
            case 2	:$this->logga("Flag Rielabora"); break;
            case 3	:$this->logga("Flag Rettifica"); break;
            case 4	:$this->logga("Pagata Completamente"); break;
            case 5	:$this->logga("Non notificata"); break;
            case 6	:$this->logga("Ricorso presente"); break;
            case 7	:$this->logga("Pignoramento attivo"); break;
            case 8	:$this->logga("Posizione Prescritta"); break;
            case 9	:$this->logga("Nei termini"); break;
            case 10	:$this->logga("Partita bloccata"); break;
            case 11	:$this->logga("Utente deceduto - CDS"); break;
            case 12	:$this->logga("Assenza ingiunzione"); break;
            case 13	:$this->logga("Fuori termine"); break;
            case 14	:$this->logga("Assenza recapito erede"); break;
            case 15	:$this->logga("Rettifica bloccata - tipo atto non coincidente"); break;
            case 16	:$this->logga("Rielabora bloccato - sollecito non forzabile"); break;
            case 17	:$this->logga("Anomalia notifica"); break;
            case 18	:$this->logga("In attesa di notifica (100gg)"); break;
            case 19	:$this->logga("Assenza data flusso"); break;
            case 20	:$this->logga("Anomalia indirizzo"); break;
            case 21	:$this->logga("Data decorrenza interessi assente"); break;
            case 22	:$this->logga("Rateizzazione in corso"); break;
            case 23	:$this->logga("Atto Mancante"); break;
            case 24	:$this->logga("Atto non associabile a pignoramento"); break;
            case 25	:$this->logga("Assenza data notifica"); break;
            case 26	:$this->logga("Atto scaduto/vicino scadenza"); break;
            case 27	:$this->logga("CAD senza stato di giacenza"); break;
            case 28	:$this->logga("CAD non ritirato - Mancanza immagini"); break;
            case 29	:$this->logga("CAD non ritirato - Indirizzo non validato"); break;
            case 30	:$this->logga("CAD non ritirato - Mancanza immagini e indirizzo non validato"); break;
        }
    }
     public function checkAttoMancante()
     {
        if (is_null($this->a_checkPartita['DocumentTypeId']))
            return array(
                'position_status' => 23,
                'flag_elaboration' => 0
            );
        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
     }

     public function checkNonAssociabilePignoramento()
     {

        if (( $this->a_checkPartita['DocumentTypeId'] !=2 )&& ($this->a_checkPartita['DocumentTypeId'] !=4 ))
            return array(
                'position_status' => 24,
                'flag_elaboration' => 0
            );
        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
    }

    public function checkDataNotificaAssente()
    {
        if (is_null($this->a_checkPartita['Data_Notifica_Atto'] ))
            return array(
                'position_status' => 25,
                'flag_elaboration' => 0
            );
        else if(!empty($this->a_checkPartita['Modalita_Notifica_Atto'])){
            if($this->a_checkPartita['Modalita_Notifica_Atto']==11 || $this->a_checkPartita['Modalita_Notifica_Atto']==12){

                if(empty($this->a_checkPartita['Stato_Notifica_Atto']))
                    return array(
                        'position_status' => 27,
                        'flag_elaboration' => 0
                    );
                else{

                    if($this->a_checkPartita['DocumentTypeId']==4 || $this->a_checkPartita['DocumentTypeId']==12){
                        if($this->a_checkPartita['Stato_Notifica_Atto']==28 && 
                        ($this->a_checkPartita['Indirizzo_Validato_Atto']!="si" || is_null($this->a_checkPartita['Indirizzo_Validato_Atto']))){
                            return array(
                                'position_status' => 30,
                                'flag_elaboration' => 0
                            );
                        }
                        else if($this->a_checkPartita['Indirizzo_Validato_Atto']!="si" || is_null($this->a_checkPartita['Indirizzo_Validato_Atto'])){
                            return array(
                                'position_status' => 29,
                                'flag_elaboration' => 0
                            );
                        }
                    }
                    else{
                        if($this->a_checkPartita['Stato_Notifica_Atto']==28 && 
                        ($this->a_checkPartita['Indirizzo_Validato_Atto']!="si" || is_null($this->a_checkPartita['Indirizzo_Validato_Atto']))){
                            return array(
                                'position_status' => 30,
                                'flag_elaboration' => 0
                            );
                        }
                        else if(empty($this->a_checkPartita['CAD_Fronte_Atto'])){
                            return array(
                                'position_status' => 28,
                                'flag_elaboration' => 0
                            );
                        }
                        else if($this->a_checkPartita['Indirizzo_Validato_Atto']!="si" || is_null($this->a_checkPartita['Indirizzo_Validato_Atto'])){
                            return array(
                                'position_status' => 29,
                                'flag_elaboration' => 0
                            );
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

    //CONTROLLO ERRATO NON PRENDE IN CONSIDERAZIONE I GIORNI DI BLOCCO E IL CONTROLLO SUI 30 GIORNI HA QUALQUADRA CHE NON COSA
    //HO AGGIUNTO LA CHECKACTVALIDITY
    private function InScadenza($dataNotifica)
    {
        $dataOggi = date_create();
        $interval = date_diff($dataOggi, date_create($dataNotifica));
        $days =  (int)$interval->format('%a') + 1;
        echo $dataNotifica." ".$days;
        return $days<=30;
    }
    public function checkDataNotificaScadenza()
    {
        $dataNotifica = $this->a_checkPartita['Data_Notifica_Atto'];

        if ($this->InScadenza($dataNotifica))
            return array(
                'position_status' => 26,
                'flag_elaboration' => 0
            );
        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
    }

    public function checkRettificaLastAtto()
    {
        if (strpos($this->a_checkPartita['Rettifica_Flag_LastAtto'], "si") !== false)
        {
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

    public function checkRielaboraLastAtto()
    {
        if (strpos($this->a_checkPartita['Rielabora_Flag_LastAtto'], "si") !== false)
        {
            return array(
                'position_status' => 16,
                'flag_elaboration' => 0
            );
        }

        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
    }

    public function checkRateizzazioneLastAtto()
    {

        if ( $this->a_checkPartita['Rate_Previste_LastAtto'] > 0 )
            return array(
                'position_status' => 22,
                'flag_elaboration' => 0
            );
        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
    }

    public function checkActValidity($startDate = null, $endDate = null, $expireDays = null)
    {

        if (!is_null($this->a_checkPartita['DocumentTypeId'])) {//SOLO ING
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
                

                //? PROCEDURE CAUTELARI: Preavviso di fermo (22)
                //? BASTA CHE CI SIA UN ATTO NOTIFICATO E NON SI CONTROLLA LA SCADENZA
                if($this->DocumentTypeId != 22){
                    $blockDays = $this->calcBlockDays($a_params);
                    $totalDays = $expireDays + $blockDays;

                    //? Margine 30 giorni su scadenza ing non centra con i giorni da aspettare dopo la notifica
                    $totalDays-= 30;

                    $expirationDate = date_create($startDate); // or your date string
                    date_add($expirationDate, date_interval_create_from_date_string($totalDays . " days"));
                    //* sto elaborando atto diverso da sollecito (!=3) e la data di scadenza del precedente atto è maggiore della data di elaborazione dell'atto
                    //* in questo caso la scadenza non è ancora passata per cui l'atto precedente è nei termini e il controllo interviene
                    if ($this->DocumentTypeId != 3 && $expirationDate->format('Y-m-d') < $actElaborationDate) {
                        return array(
                            'position_status' => 26,
                            'flag_elaboration' => 0
                        );
                    }
                }
                else
                {
                    //? SECONDO EMANUELA IL PREAVVISO DI FERMO DEVE AVERE LA DATA DELL'ULTIMO ATTO NOTIFICATO
                    //? DEVONO ESSERE PASSATI 90GG

                    $totalDays = 90;

                    $expirationDate = date_create($startDate); // or your date string
                    date_add($expirationDate, date_interval_create_from_date_string($totalDays . " days"));
                    if ($expirationDate->format('Y-m-d') > $actElaborationDate) {
                        return array(
                            'position_status' => 40,
                            'flag_elaboration' => 0
                        );
                    }
                }
            }
        }

        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
    }

    public function checkAnomaliaLastAtto(){
        
        if(!empty($this->a_checkPartita['Motivo_Notifica_LastAtto'])){
            if($this->a_checkPartita['Motivo_Notifica_LastAtto']>=19 && $this->a_checkPartita['Motivo_Notifica_LastAtto']<=26)
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
        return array(
            'position_status' => 1,
            'flag_elaboration' => 1
        );
        
    }
         
    // public function checkPignoramento()
    // {
    //     if(is_null($this->a_checkPartita['DocumentTypeId_PG']))
    //         return array(
    //             'position_status' => 7,
    //             'flag_elaboration' => 0
    //         );
    //     return array(
    //         'position_status' => 1,
    //         'flag_elaboration' => 1
    //     );
    // }
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
        
        //  La Partita è stata pagata
        if ($a_return['position_status'] == 1) {
            $a_return = $this->checkPagamento();
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

        //  Pignoramento
        if ($a_return['position_status'] == 1) {
            $a_return = $this->checkPignoramento();
        }

        //  Rateizzazione
        if ($a_return['position_status'] == 1) {
            $a_return = $this->checkRateizzazioneLastAtto();
        }

        // Rettifica flag deve essere contrario check atti
        if ($a_return['position_status'] == 1) {
            $a_return = $this->checkRettificaLastAtto();
        }

        //  Rielabora flag deve essere contrario check atti
        if ($a_return['position_status'] == 1) {
            $a_return = $this->checkRielaboraLastAtto();
        }

        if ($a_return['position_status'] == 1) {
            $a_return = $this->checkAttoMancante();
        }

        if ($a_return['position_status'] == 1) {
            $a_return = $this->checkNonAssociabilePignoramento();
        }

        if ($a_return['position_status'] == 1) {
            $a_return = $this->checkAnomaliaLastAtto();
        }

        if ($a_return['position_status'] == 1) {
            $a_return = $this->checkDataNotificaAssente();
        }        

        if ($a_return['position_status'] == 1) {
            $a_return = $this->checkActValidity();
        }
        
       
       
        $a_return["v_checkPartita"] =1;
        $a_return["parametri_annuali"] =1;
        $a_return["check_error"] = 0;

        /*   var_dump($this->a_checkPartita['Partita_ID']);
        var_dump($this->a_checkPartita['Comune_ID']);
       var_dump($a_return);*/
        
        $this->ScriviLog($a_return['position_status']);

        return $a_return;
    }
}