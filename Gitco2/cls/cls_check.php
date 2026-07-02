<?php

include_once CLS . "/cls_db.php";

class cls_check{
    //DB
    private $cls_db;

    //Array documento
    private $a_document;

    //Minimo per proseguire
    public $minimumAmount;
    public $riscossioneDays;

    //tipo documento e tabella appartenenza
    public $docTypeId;
    public $tableTypeId;

    //dovuto del documento
    public $docAmount;
    public $instalmentNumber;
    public $a_instalmentExpireDates;

    //Controllo termini
    public $notificationDate;
    public $startDate;
    public $endDate;

    //Pagamenti documento
    public $docPaymentsAmount;
    public $firstPaymentDate;
    public $lastPaymentDate;

    public function __construct()
    {
        $this->cls_db = new cls_db();
        $this->setMinAmount();
    }

    public function setAct($a_document){
        $this->resetAct();
        //$this->setActArray($a_document);
        $msg = $this->setActArray($a_document);
        if($msg!==true) return $msg;
        //else return true;
        $this->docTypeId = $this->a_document['DocumentTypeId'];

        $this->setDocumentTable();
        $this->setActDates();
        $this->setActPayments();
        return true;
    }

    private function setActArray($a_document){
        //var_dump($a_document);
        //echo "<br><br>";
        $this->a_document = $a_document;
        /*if(!isset($this->a_document['DocumentTypeId']) || !isset($this->a_document['Totale_Dovuto']) ||
        !isset($this->a_document['Rate_Previste']) || !isset($this->a_document['Scadenze_Rate']) ||
        !isset($this->a_document['Data_Notifica']) || !isset($this->a_document['Diritto_Riscossione_Massimo']) ||
        !isset($this->a_document['Diritto_Riscossione_Minimo']))*/
        if(empty($this->a_document['DocumentTypeId']) || empty($this->a_document['Totale_Dovuto']) || empty($this->a_document["Data_Notifica"]))
        {
            //echo "<br>Elementi mancanti nell'array dell'atto inserito nella classe cls_check!<br>";
            //die;
            if(empty($this->a_document['DocumentTypeId'])) $msg = "Tipologia atto assente";
            else if(empty($this->a_document['Totale_Dovuto'])) $msg = "Totale dovuto assente";
            else $msg = "Data di notifica assente";
            return $msg;
        }
        return true;
    }

    private function resetAct(){
        $this->startDate = null;
        $this->endDate = null;
        $this->notificationDate = null;
        $this->docTypeId = null;
        $this->tableTypeId = null;
        $this->docPaymentsAmount = null;
        $this->firstPaymentDate = null;
        $this->lastPaymentDate = null;
        $this->instalmentNumber = null;
        $this->a_instalmentExpireDates = array();

        $this->a_document = null;

    }

    private function setActDates(){
        if($this->a_document['Data_Notifica']!=null){
            $this->notificationDate = new DateTime($this->a_document['Data_Notifica']);

            switch($this->docTypeId){
                case 2://ingiunzione
                    $this->startDate = new DateTime($this->a_document['Data_Notifica']);
                    $this->startDate->modify("+1 month");

                    $this->endDate = new DateTime($this->a_document['Data_Notifica']);
                    $this->endDate->modify("+1 year");
                    break;

                case 4://avviso di intimazione
                    $this->startDate = new DateTime($this->a_document['Data_Notifica']);
                    $this->startDate->modify("+5 days");

                    $this->endDate = new DateTime($this->a_document['Data_Notifica']);
                    $this->endDate->modify("+6 months");
                    break;
            }
        }
    }

    public function checkDatesLimit($endMarginDays=20, $checkDate = null)
    {
        if($checkDate==null)
            $checkDate = date("Y-m-d");

        if($endMarginDays>0)
            $this->endDate->modify("-".$endMarginDays." days");

        if ($checkDate >= $this->startDate->format("Y-m-d") && $checkDate < $this->endDate->format("Y-m-d"))
            return true;
        else
            return false;
    }

    public function checkActPayments($extraDays=90){

        if ($this->docAmount - $this->docPaymentsAmount > $this->minimumAmount) {
            if ($this->instalmentNumber > 0) {
                $data_rata = DateTime::createFromFormat("d-m-Y",str_replace("/","-",$this->a_instalmentExpireDates[count($this->a_instalmentExpireDates) - 1]));
                if($extraDays>0)
                    $data_rata->modify("+".$extraDays." days");
                if (date('Y-m-d') > $data_rata->format('Y-m-d'))
                    return true;
                else
                    return false;
            }
            else{
                $paymentCheck = $this->notificationDate;
                if($extraDays>0)
                    $paymentCheck->modify("+".$extraDays." days");
                if (date('Y-m-d') > $paymentCheck->format('Y-m-d'))
                    return true;
                else
                    return false;
            }
        }
        else
            return false;
    }


    private function setActPayments(){
        $this->docAmount = $this->a_document['Totale_Dovuto'];
        $this->instalmentNumber = $this->a_document['Rate_Previste'];
        $this->a_instalmentExpireDates = explode("*",$this->a_document['Scadenze_Rate']);

        $query = "SELECT Partita_ID, SUM(Importo) AS TOT, MIN(Data_Pagamento) AS FIRST_DATE, MAX(Data_Pagamento) AS LAST_DATE FROM pagamento ";
        $query.= "WHERE Atto_ID <= ".$this->a_document['ID']." AND Partita_ID = ".$this->a_document['Partita_ID']." ";
        $query.= "AND DocumentTableTypeId = ".$this->tableTypeId." GROUP BY Partita_ID";
        $a_payment = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
        if($a_payment != null)
        {
            $this->docPaymentsAmount = $a_payment['TOT'];
            $this->firstPaymentDate = $a_payment['FIRST_DATE'];
            $this->lastPaymentDate = $a_payment['LAST_DATE'];

            //AGGIUNTA DIRITTO DI RISCOSSIONE AL TOTALE
            $riscossioneDate = $this->notificationDate;
            if ($this->riscossioneDays > 0)
                $riscossioneDate->modify("+" . $this->riscossioneDays . " days");

            if ($this->firstPaymentDate != null) {
                if ($this->firstPaymentDate > $riscossioneDate->format('Y-m-d'))
                    $this->docAmount += $this->a_document["Diritto_Riscossione_Massimo"];
                else
                    $this->docAmount += $this->a_document["Diritto_Riscossione_Minimo"];
            } else {
                if (date("Y-m-d") > $riscossioneDate->format('Y-m-d'))
                    $this->docAmount += $this->a_document["Diritto_Riscossione_Massimo"];
                else
                    $this->docAmount += $this->a_document["Diritto_Riscossione_Minimo"];
            }
        }
    }

    private function setDocumentTable(){
        $query = "SELECT TableTypeId FROM document_type WHERE Id = ".$this->docTypeId;
        $this->tableTypeId = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query))['TableTypeId'];
    }

    private function setMinAmount(){
        $query = "SELECT Importo_Minimo, Giorni_Diritto FROM parametri_annuali WHERE Anno=".date("Y");
        $a_params = $this->cls_db->getArrayLine($this->cls_db->ExecuteQuery($query));
        if(isset($a_params['Importo_Minimo'])){
            $this->minimumAmount = $a_params['Importo_Minimo'];
            $this->riscossioneDays = $a_params['Giorni_Diritto'];
        }
        else{
            echo "PARAMETRI ANNUALI ASSENTI PER L'ANNO ".date("Y");
            die;
        }

    }
}

