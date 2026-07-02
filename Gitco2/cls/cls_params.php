<?php

class cls_params{
    
    public $a_checks = array();

    public function checkParams($a_params, $tipo_riscossione = null){
        $this->a_checks = array();

        if(array_key_exists("annual",$a_params))
            $this->checkAnnualParams($a_params['annual']);
        if(array_key_exists("general",$a_params) && !is_null($tipo_riscossione))
            $this->checkGeneralParams($a_params['general'], $tipo_riscossione);
        if(array_key_exists("payment",$a_params) && !is_null($tipo_riscossione))
            $this->checkPaymentParams($a_params['payment'], $tipo_riscossione);
        if(array_key_exists("responsible",$a_params) && !is_null($tipo_riscossione))
            $this->checkResponsibleParams($a_params['responsible'], $tipo_riscossione);
        if(array_key_exists("appeal",$a_params))
            $this->checkAppealParams($a_params['appeal']);
        if(array_key_exists("interests",$a_params))
            $this->checkInterestParams($a_params['interests']);
        if(array_key_exists("text",$a_params))
            $this->checkTextParams($a_params['text']);
        if(array_key_exists("subtext",$a_params) && array_key_exists("subtext_general",$a_params))
            $this->checkSubtextParams($a_params['subtext'],$a_params['subtext_general']);
        if(array_key_exists("authority",$a_params))
            $this->checkAuthorityParams($a_params['authority']);

    }

    public function setHtmlChecks(){
        $html = "<ol>";
        foreach ($this->a_checks['negative'] as $a_check)
            $html.= "<li>" . $a_check['msg'] . "</li>";
        $html.= "</ol>";
        return $html;
        
    }

    private function setCheckList($a_check){
        if($a_check["check"]===false)
            $this->a_checks['negative'][] = $a_check;
        else
            $this->a_checks['positive'][] = $a_check;
    }

    public function checkAnnualParams($a_params){
        $a_check = array("msg"=>"Parametri annuali presenti","check"=>true);
        if(empty($a_params))
            $a_check = array("msg"=>"Parametri annuali assenti!","check"=>false);

        $this->setCheckList($a_check);

        return $a_check;
    }

    public function checkInterestParams($a_params){
        $a_check = array("msg"=>"Interessi tributi presenti","check"=>true);
        if(empty($a_params))
            $a_check = array("msg"=>"Interessi tributi assenti!","check"=>false);

        $this->setCheckList($a_check);

        return $a_check;
    }

    public function checkAppealParams($a_params){
        $a_check = array("msg"=>"Parametri ricorso presenti","check"=>true);
        if(empty($a_params))
            $a_check = array("msg"=>"Parametri ricorso assenti!","check"=>false);

        $this->setCheckList($a_check);

        return $a_check;  
    }

    public function checkTextParams($a_params){
        $a_check = array("msg"=>"Testo presente","check"=>true);
        if(empty($a_params))
            $a_check = array("msg"=>"Testo assente!","check"=>false);

        $this->setCheckList($a_check);

        return $a_check;    
    }

    public function checkSubtextParams($a_params, $a_genParams){
        $a_check = array("msg"=>"Sottotesti presenti","check"=>true);
        if(count($a_genParams)>count($a_params)){
            $a_check = array("msg"=>"Sottotesti assenti o incompleti!","check"=>false);
            $countPar = count($a_genParams)-count($a_params);
            if($countPar>1)
                $a_check['msg'].= " ".$countPar." parametri da inserire";
            else
                $a_check['msg'].= " ".$countPar." parametro da inserire";
        }

        $this->setCheckList($a_check);

        return $a_check;
    }

    public function checkAuthorityParams($a_params){
        $a_check = array("msg"=>"Parametri autorità presenti","check"=>true);
        if (empty($a_params['giudice'])) {
            if($a_check['msg']=="")
                $a_check['msg'].= "Parametri autorità assenti: ";
            else
                $a_check['msg'].= ", ";
            $a_check['msg'].= "Giudice di Pace";
            $a_check['check'] = false;
        }
        if (empty($a_params['cort_giust_trib'])) {
            if($a_check['msg']=="")
                $a_check['msg'].= "Parametri autorità assenti: ";
            else
                $a_check['msg'].= ", ";
            $a_check['msg'].= "Corte di giustizia tributaria di I grado";
            $a_check['check'] = false;
        }
        if (empty($a_params['tribunale'])) {
            if($a_check['msg']=="")
                $a_check['msg'].= "Parametri autorità assenti: ";
            else
                $a_check['msg'].= ", ";
            $a_check['msg'].= "Tribunale";
            $a_check['check'] = false;
        }

        $this->setCheckList($a_check);

        return $a_check;
    }

    public function checkPaymentParams($a_params, $tipo_riscossione){
        $a_check = array("msg"=>"Parametri pagamento ".$tipo_riscossione." presenti","check"=>true);
        if(empty($a_params[$tipo_riscossione]))
            $a_check = array("msg"=>"Parametri pagamento ".$tipo_riscossione." assenti!","check"=>false);

        $this->setCheckList($a_check);

        return $a_check;
    }

    public function checkGeneralParams($a_params, $tipo_riscossione){
        $a_check = array("msg"=>"Parametri generali ".$tipo_riscossione." presenti","check"=>true);
        if(empty($a_params[$tipo_riscossione]))
            $a_check = array("msg"=>"Parametri generali ".$tipo_riscossione." assenti!","check"=>false);
        else{
            if (($a_params[$tipo_riscossione]['Restituzione1'] == "" || is_null($a_params[$tipo_riscossione]['Restituzione1']))) {
                if($a_check['msg']=="")
                    $a_check['msg'].= "Parametri generali ".$tipo_riscossione." assenti: ";
                else
                    $a_check['msg'].= ", ";
                $a_check['msg'].= "Dati Mod23L per Atti giudiziari";
                $a_check['check'] = false;
            } 
            
            if (($a_params[$tipo_riscossione]['Restituzione1_Mod23O'] == "" || is_null($a_params[$tipo_riscossione]['Restituzione1_Mod23O']))) {
                if($a_check['msg']=="")
                    $a_check['msg'].= "Parametri generali ".$tipo_riscossione." assenti: ";
                else
                    $a_check['msg'].= ", ";
                $a_check['msg'].= "Dati Mod23O per Raccomandata";
                $a_check['check'] = false;
            }
        }

        $this->setCheckList($a_check);

        return $a_check;
    }

    public function checkResponsibleParams($a_params, $tipo_riscossione){
        $a_check = array("msg"=>"Parametri responsabili ".$tipo_riscossione." presenti","check"=>true);
        if(empty($a_params[$tipo_riscossione]))
            $a_check = array("msg"=>"Parametri responsabili ".$tipo_riscossione." assenti!","check"=>false);
        else{
            if ($a_params[$tipo_riscossione]['Funzionario_Firma'] == "" && $a_params[$tipo_riscossione]['Funzionario_Testo'] != "si"){
                if($a_check['msg']=="")
                    $a_check['msg'].= "Parametri responsabili ".$tipo_riscossione." assenti: ";
                else
                    $a_check['msg'].= ", ";
                $a_check['msg'].= "Firma del Funzionario responsabile / Legale rappresentante";
                $a_check['check'] = false;
            }
            if ($a_params[$tipo_riscossione]['Responsabile_Firma'] == "" && $a_params[$tipo_riscossione]['Responsabile_Testo'] != "si"){
                if($a_check['msg']=="")
                    $a_check['msg'].= "Parametri responsabili ".$tipo_riscossione." assenti: ";
                else
                    $a_check['msg'].= ", ";
                $a_check['msg'].= "Firma del Responsabile del procedimento assente!";
                $a_check['check'] = false;
            }
            if ($a_params[$tipo_riscossione]['Ufficiale_Firma'] == "" && $a_params[$tipo_riscossione]['Ufficiale_Testo'] != "si"){
                if($a_check['msg']=="")
                    $a_check['msg'].= "Parametri responsabili ".$tipo_riscossione." assenti: ";
                else
                    $a_check['msg'].= ", ";
                $a_check['msg'].= "Firma dell'Ufficiale della riscossione assente!";
                $a_check['check'] = false;
            }
            if ($a_params[$tipo_riscossione]['Responsabile_Richieste_Firma'] == "" && $a_params[$tipo_riscossione]['Responsabile_Richieste_Testo'] != "si"){
                if($a_check['msg']=="")
                    $a_check['msg'].= "Parametri responsabili ".$tipo_riscossione." assenti: ";
                else
                    $a_check['msg'].= ", ";
                $a_check['msg'].= "Firma del Responsabile delle richieste assente!";
                $a_check['check'] = false;
            }
        }

        $this->setCheckList($a_check);
        
        return $a_check;
    }


}

?>