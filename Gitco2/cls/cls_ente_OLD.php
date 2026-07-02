<?php
include_once CLS."/cls_help.php";

class cls_ente{

    public $a_ente;
    public $logo;
    public $type;
    public $a_header = array("left"=>array(),"right"=>array(),"logo"=>"","logoPath"=>"");
    public function __construct($a_ente=null){
        if(is_array($a_ente)){
            $this->a_ente = $a_ente;
            if($this->a_ente['Ufficio_ID']>0)
                $type = "Ufficio";
            else if($this->a_ente['Gestore_ID']>0)
                $type = "Gestore";
            else
                $type = "Info";
            $this->type = $type;
        }
    }

    public function getCityList_query( $auth, $cc ){

        $query = "SELECT DISTINCT enti_gestiti.ID, enti_gestiti.CC, enti_gestiti.Denominazione FROM enti_gestiti ";
        $query.= "JOIN anni_gestiti ON anni_gestiti.CC_Anno = enti_gestiti.CC ";
        $query.= "WHERE CC_Anno = CC AND Gestione_Coattiva = 'Y' ";
        if($auth==2)
            $query .= " AND enti_gestiti.CC = '" . $cc. "' ";
        else if($auth>2)
            $query .= " AND enti_gestiti.Autorizzazione = '" . $auth. "' ";
        $query.= " GROUP BY enti_gestiti.ID, enti_gestiti.CC, enti_gestiti.Denominazione ORDER BY enti_gestiti.Denominazione ";

        return $query;
    }

    public function getCityYearList_query( $cc ){

        $query = "SELECT * FROM anni_gestiti WHERE CC_Anno = '".$cc."' AND Gestione_Coattiva = 'Y' ORDER BY Anno DESC";

        return $query;
    }

    public function getCityList_options(array $a_cities){
        $select = "<select id='select_comune' size=1 onchange='changeCity();'>";
        $select.= "<option></option>";
        $select.= "<optgroup label='Ente di gestione'>";

        for($i=0;$i<count($a_cities);$i++)
            $select.= "<option value='".$a_cities[$i]['CC']."'>".$a_cities[$i]['Denominazione']." - ".$a_cities[$i]['CC']." (".$a_cities[$i]['ID'].")</option>";

        $select.="</optgroup>";
        $select.="</select>";
        return $select;
    }

    public function getCityYearList_options(array $a_years){

        $select = "<select id='select_anno'>";
        $select.= "<option></option>";
        $select.= "<optgroup label='Anno'>";

        for($i=0;$i<count($a_years);$i++)
            $select.= "<option value='".$a_years[$i]['Anno']."'>".$a_years[$i]['Anno']."</option>";

        $select.="</optgroup>";
        $select.="</select>";

        return $select;
    }

    public function getCityManager(){

        if($this->a_ente['Ufficio_Comune']!="")
            $city = $this->a_ente['Ufficio_Comune'];
        else if($this->a_ente['Gestore_Comune']!="")
            $city = $this->a_ente['Gestore_Comune'];
        else
            $city = $this->a_ente['Info_Comune'];

        return $city;
    }


    public function getContactsManager(){

        if($this->a_ente['Ufficio_ID']>0)
            $type = "Ufficio";
        else if($this->a_ente['Gestore_ID']>0)
            $type = "Gestore";
        else
            $type = "Info";

        $contacts = "";
        if($this->a_ente[$type.'_Fax']!="")
            $contacts.= "Fax: ".$this->a_ente[$type.'_Fax'];

        if($this->a_ente[$type.'_Mail']!=""){
            if($contacts!="")
                $contacts.= " - ";
            $contacts.= "Mail: ".$this->a_ente[$type.'_Mail'];
        }

        if($this->a_ente[$type.'_PEC']!=""){
            if($contacts!="")
                $contacts.= " - ";
            $contacts.= "PEC: ".$this->a_ente[$type.'_PEC'];
        }

        return $contacts;
    }

    public function getCityDenomination(){
        $comune = "";
        if(substr($this->a_ente['CC'],0,1)!="U")
            $comune.= "Comune di ";
        $comune.= $this->a_ente['Denominazione'];
        return $comune;
    }

    public function setPrintHeader(){

        $this->getLogo();
        if($this->a_ente['Gestore_ID']!=null){
            $type = "Gestore";
            $this->a_header['left'][0] = $this->a_ente[$type.'_Tipo']." ".$this->a_ente[$type.'_Denominazione'];
            $this->a_header['left'][6] = "Gestione: ".$this->getCityDenomination();
        }
        else{
            $type = "Info";
            $this->a_header['left'][0] = $this->a_ente[$type.'_Denominazione']." (".$this->a_ente[$type.'_Provincia'].")";
            $this->a_header['left'][6] = "";
        }

        $this->a_header['left'][1] = $this->getAddressRow($type);
        $this->a_header['left'][2] = $this->getCfPiRow($type);
        $this->a_header['left'][3] = $this->getPhoneFaxRow($type);
        $this->a_header['left'][4] = $this->getMailSiteRow($type);
        $this->a_header['left'][5] = "Servizio: Riscossione coattiva";

        $type = "Ufficio";
        $this->a_header['right'][0] = $this->a_ente[$type.'_Denominazione'];
        $this->a_header['right'][1] = $this->getAddressRow($type);
        $this->a_header['right'][2] = $this->getPhoneFaxRow($type);
        $this->a_header['right'][3] = $this->getMailPecRow($type);

        $a_timeTable = $this->getTimeTableRows($type);

        $this->a_header['right'][4] = $a_timeTable[0];
        $this->a_header['right'][5] = $a_timeTable[1];
    }

    public function getLogo(){
        $this->logo['ente'] = array("webPath"=>STEMMIWEB."/".$this->a_ente['CC']."/".$this->a_ente['Stemma_1'],"rootPath"=>STEMMI."/".$this->a_ente['CC']."/".$this->a_ente['Stemma_1']);
        $this->logo['gestore'] = array("webPath"=>"","rootPath"=>"");

        if($this->a_ente['Gestore_ID']!=null && $this->a_ente['Gestore_Tipo']=="Concessionario"){
            if($this->a_ente['Gestore_Stemma']!=""){
                $this->a_header['logo'] = STEMMIWEB."/".$this->a_ente['CC']."/".$this->a_ente['Gestore_Stemma'];
                $this->a_header['logoPath'] = STEMMI."/".$this->a_ente['CC']."/".$this->a_ente['Gestore_Stemma'];
            }
            else{
                $this->a_header['logo'] = IMMAGINIWEB."/sarida_logo.png";
                $this->a_header['logoPath'] = IMMAGINI."/sarida_logo.png";
            }
            $this->logo['Gestore'] = array("webPath"=>$this->a_header['logo'],"rootPath"=>$this->a_header['logoPath']);
        }
        else{
            $this->a_header['logo'] = STEMMIWEB."/".$this->a_ente['CC']."/".$this->a_ente['Stemma_1'];
            $this->a_header['logoPath'] = STEMMI."/".$this->a_ente['CC']."/".$this->a_ente['Stemma_1'];
        }
        $this->logo['auto'] = array("webPath"=>$this->a_header['logo'],"rootPath"=>$this->a_header['logoPath']);
    }
    
    public function getAddressRow($type){
        $address = "";
        if($this->a_ente[$type.'_Via']!=""){
            $address = ucwords(strtolower($this->a_ente[$type.'_Via']));
            if($this->a_ente[$type.'_Frazione'])
                $address = $this->a_ente[$type.'_Frazione'].", ".$address;

            if($this->a_ente[$type.'_Civico']>0)
                $address.=", ".$this->a_ente[$type.'_Civico'];
            if($this->a_ente[$type.'_Esponente']!="")
                $address.= $this->a_ente[$type.'_Esponente'];
            if($this->a_ente[$type.'_Interno']>0)
                $address.="/".$this->a_ente[$type.'_Interno'];
            if($this->a_ente[$type.'_Dettagli']!="")
                $address.=", ".$this->a_ente[$type.'_Dettagli'];

            if($this->a_ente[$type.'_Comune']!="")
                $address.= " - ".$this->a_ente[$type.'_Cap']." ".$this->a_ente[$type.'_Comune']." (".$this->a_ente[$type.'_Provincia']. ")";
        }

        return $address;
    }

    public function getCfPiRow($type){
        $CfPi = "";
        if($this->a_ente[$type.'_PI']!="" || $this->a_ente[$type.'_CF']!=""){
            $CfPi = "P.I.: " . $this->a_ente[$type.'_PI'] ."  -  C.F.: ".$this->a_ente[$type.'_CF'];

            if($this->a_ente[$type.'_PI'] == "")
                $CfPi = "C.F.: ".$this->a_ente[$type.'_CF'];
            else if($this->a_ente[$type.'_CF'] == "")
                $CfPi = "P.I.: " . $this->a_ente[$type.'_PI'];
        }

        return $CfPi;
    }

    public function getPhoneFaxRow($type){
        $phoneFax = "";
        if($this->a_ente[$type.'_Telefono']!="" || $this->a_ente[$type.'_Fax']!="")
        {
            $phoneFax = "Tel: " . $this->a_ente[$type.'_Telefono'] ."  -  Fax: ".$this->a_ente[$type.'_Fax'];

            if($this->a_ente[$type.'_Telefono'] == "")
                $phoneFax = "Fax: ".$this->a_ente[$type.'_Fax'];
            else if($this->a_ente[$type.'_Fax'] == "")
                $phoneFax = "Tel: " . $this->a_ente[$type.'_Telefono'];
        }

        return $phoneFax;
    }

    public function getMailSiteRow($type)
    {
        $mailSite = "";
        if($this->a_ente[$type.'_Mail']!="" || $this->a_ente[$type.'_Sito']!=""){
            $mailSite = "eMail: " . $this->a_ente[$type.'_Mail'] ."  -  Sito: ".$this->a_ente[$type.'_Sito'];

            if($this->a_ente[$type.'_Mail'] == "")
                $mailSite = "Sito: ".$this->a_ente[$type.'_Sito'];
            else if($this->a_ente[$type.'_Sito'] == "")
                $mailSite = "eMail: " . $this->a_ente[$type.'_Mail'];
        }
        return $mailSite;
    }

    public function getMailPecRow($type)
    {
        $mailPec = "";
        if($this->a_ente[$type.'_Mail']!="" || $this->a_ente[$type.'_PEC']!="")
        {
            $mailPec = "eMail: " . $this->a_ente[$type.'_Mail'] ."  -  PEC: ".$this->a_ente[$type.'_PEC'];

            if($this->a_ente[$type.'_Mail'] == "")
                $mailPec = "PEC: ".$this->a_ente[$type.'_PEC'];
            else if($this->a_ente[$type.'_PEC'] == "")
                $mailPec = "eMail: " . $this->a_ente[$type.'_Mail'];
        }
        return $mailPec;
    }

    public function getTimeTableRows($type)
    {
        $orario = $this->a_ente[$type.'_Orario'];
        if($orario!=""){
            if(strlen($orario) <= 50){
                $a_timeTable[0] = $orario;
                $a_timeTable[1] = "";
            }
            else{
                $pos = 50;
                for( $i=0; $i<$pos; $i++){
                    $carattere = substr($orario, $pos-$i,1);
                    if($carattere==" ") {
                        $pos = $pos-$i;
                        break;
                    }
                }

                $a_timeTable[0] = substr($orario, 0 , $pos);
                $a_timeTable[1] = substr($orario, $pos+1);
            }
            return $a_timeTable;
        }
        else
            return array(null,null);


    }
}