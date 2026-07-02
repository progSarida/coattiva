<?php

include_once CLS . "/cls_elaborationPignoramenti.php";

class cls_testElaborationPigno extends cls_elaborationPignoramenti
{

    public function showControl($control, $position_status, $extra=null)
    {
        switch($position_status)
        {
            case 1	: echo "<br>".$control.": ".$position_status." - Elaborabile<br>"; break;
            case 2	: echo "<br>".$control.": ".$position_status." - Flag Rielabora<br>"; break;
            case 3	: echo "<br>".$control.": ".$position_status." - Flag Rettifica<br>"; break;
            case 4	: echo "<br>".$control.": ".$position_status." - Pagata Completamente<br>"; break;
            case 5	: echo "<br>".$control.": ".$position_status." - Non notificata<br>"; break;
            case 6	: echo "<br>".$control.": ".$position_status." - Ricorso presente<br>"; break;
            case 7	: echo "<br>".$control.": ".$position_status." - Pignoramento attivo<br>"; break;
            case 8	: echo "<br>".$control.": ".$position_status." - Posizione Prescritta<br>"; break;
            case 9	: echo "<br>".$control.": ".$position_status." - Nei termini<br>"; break;
            case 10	: echo "<br>".$control.": ".$position_status." - Partita bloccata<br>"; break;
            case 11	: echo "<br>".$control.": ".$position_status." - Utente deceduto - CDS<br>"; break;
            case 12	: echo "<br>".$control.": ".$position_status." - Assenza ingiunzione<br>"; break;
            case 13	: echo "<br>".$control.": ".$position_status." - Fuori termine<br>"; break;
            case 14	: echo "<br>".$control.": ".$position_status." - Assenza recapito erede<br>"; break;
            case 15	: echo "<br>".$control.": ".$position_status." - Rettifica bloccata - tipo atto non coincidente<br>"; break;
            case 16	: echo "<br>".$control.": ".$position_status." - Rielabora bloccato - sollecito non forzabile<br>"; break;
            case 17	: echo "<br>".$control.": ".$position_status." - Anomalia notifica<br>"; break;
            case 18	: echo "<br>".$control.": ".$position_status." - In attesa di notifica (100gg<br>"; break;
            case 19	: echo "<br>".$control.": ".$position_status." - Assenza data flusso<br>"; break;
            case 20	: echo "<br>".$control.": ".$position_status." - Anomalia indirizzo<br>"; break;
            case 21	: echo "<br>".$control.": ".$position_status." - Data decorrenza interessi assente<br>"; break;
            case 22	: echo "<br>".$control.": ".$position_status." - Rateizzazione in corso<br>"; break;
            case 23	: echo "<br>".$control.": ".$position_status." - Atto Mancante<br>"; break;
            case 24	: echo "<br>".$control.": ".$position_status." - Atto non associabile a pignoramento<br>"; break;
            case 25	: echo "<br>".$control.": ".$position_status." - Assenza data notifica<br>"; break;
            case 26	: echo "<br>".$control.": ".$position_status." - Atto scaduto/vicino scadenza<br>"; break;
            case 27	: echo "<br>".$control.": ".$position_status." - CAD senza stato di giacenza<br>"; break;
            case 28	: echo "<br>".$control.": ".$position_status." - CAD non ritirato - Mancanza immagini<br>"; break;
            case 29	: echo "<br>".$control.": ".$position_status." - CAD non ritirato - Indirizzo non validato<br>"; break;
            case 30	: echo "<br>".$control.": ".$position_status." - CAD non ritirato - Mancanza immagini e indirizzo non validato<br>"; break;
            default: echo "<br>".$control.": ".$position_status."<br>";
        }

        if(!empty($extra))
            echo $extra."<br>";
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
        
        //   La Partita è stata bloccata
        
            $a_return = $this->checkBloccoCoazione();
            $this->showControl("checkBloccoCoazione", $a_return['position_status']);
        
        
        //  La Partita è stata pagata
        
            $a_return = $this->checkPagamento();
            $this->showControl("checkPagamento", $a_return['position_status']);
        

        //  Utente deceduto
        
            $a_return = $this->checkDeath();
            $this->showControl("checkDeath", $a_return['position_status']);
        

        //  Ricorso
        
            $a_return = $this->checkAppeal();
            $this->showControl("checkAppeal", $a_return['position_status']);
        

         // partita prescritta
         
            $a_return = $this->checkPartitaExpiration();
            $this->showControl("checkPartitaExpiration", $a_return['position_status']);
         

        //  Pignoramento
        
            $a_return = $this->checkPignoramento();
            $this->showControl("checkPignoramento", $a_return['position_status']);
        

        //  Rateizzazione
        
            $a_return = $this->checkRateizzazioneLastAtto();
            $this->showControl("checkRateizzazioneLastAtto", $a_return['position_status']);
        

        // Rettifica flag deve essere contrario check atti
        
            $a_return = $this->checkRettificaLastAtto();
            $this->showControl("checkRettificaLastAtto", $a_return['position_status']);
        

        //  Rielabora flag deve essere contrario check atti
        
            $a_return = $this->checkRielaboraLastAtto();
            $this->showControl("checkRielaboraLastAtto", $a_return['position_status']);
        

        
            $a_return = $this->checkAttoMancante();
            $this->showControl("checkAttoMancante", $a_return['position_status']);
        

        
            $a_return = $this->checkNonAssociabilePignoramento();
            $this->showControl("checkNonAssociabilePignoramento", $a_return['position_status']);
        

        
            $a_return = $this->checkAnomaliaLastAtto();
            $this->showControl("checkAnomaliaLastAtto", $a_return['position_status']);
        

        
            $a_return = $this->checkDataNotificaAssente();
            $this->showControl("checkDataNotificaAssente", $a_return['position_status']);
                

        
            $a_return = $this->checkActValidity();
            $this->showControl("checkActValidity", $a_return['position_status']);
        
        
       
       
        $a_return["v_checkPartita"] =1;
        $a_return["parametri_annuali"] =1;
        $a_return["check_error"] = 0;

        /*   var_dump($this->a_checkPartita['Partita_ID']);
        var_dump($this->a_checkPartita['Comune_ID']);
       var_dump($a_return);*/
        
        

        return $a_return;
    }
}