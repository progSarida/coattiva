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

            if($type=="Gestore")
                $manager = "Concessionario ".$this->a_ente[$type.'_Denominazione'];
            else
                $manager = $this->a_ente[$type.'_Denominazione'];

            $this->a_ente['Manager_Tipo'] = $type;
            $this->a_ente['Ente_Denominazione'] = $this->getCityDenomination();

            $this->a_ente['Info_Address'] = $this->getAddressRow("Info");
            $this->a_ente['Gestore_Address'] = $this->getAddressRow("Gestore");
            $this->a_ente['Ufficio_Address'] = $this->getAddressRow("Ufficio");

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

    public function getCityManager($type=null){
        if(!is_null($type)){
            $type = ucfirst(strtolower($type));
            if(!empty($this->a_ente[$type.'_Comune']))
                return $this->a_ente[$type.'_Comune'];
            else
                return null;
        }

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
        //var_dump($this->a_ente);
        return $comune;
    }

    public function setRecipientHeader($type, $placeDate = null, $a_references = null){

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

        $country = "";

        $a_address = array();
        if(strlen($address)>50){
            $a_address[0] = substr($address, 0, strrpos(substr($address, 0, 50), ' '));
            $a_address[1] = substr($address, strlen($a_address[0])+1, strrpos(substr($address, strlen($a_address[0])+1), ' '));
        }
        else
            $a_address[0] = $address;

        $a_header['addressName'] = $address;
        $a_header['addressCap'] = "";
        $a_header['addressCity'] = "";
        $a_header['addressProvince'] = "";
        $a_header['addressCountry'] = $country;
        $cityCap = "";
        if($this->a_ente[$type.'_Comune']!=""){
            $cityCap = $this->a_ente[$type.'_Cap']." ".$this->a_ente[$type.'_Comune'];
            $a_header['addressCap'] = $this->a_ente[$type.'_Cap'];
            $a_header['addressCity'] = $this->a_ente[$type.'_Comune'];
            if($this->a_ente[$type.'_Provincia']!=""){
                $cityCap.= " ".$this->a_ente[$type.'_Provincia'];
                $a_header['addressProvince'] = $this->a_ente[$type.'_Provincia'];
            }

        }

        $a_header['addressRow'] = array();
        for($i=0;$i<count($a_address);$i++){
            $a_header['addressRow'][] = $a_address[$i];
        }
        $a_header['addressRow'][] = $cityCap;
        $a_header['addressRow'][] = $country;

        $a_header['address'] = strtoupper($address)." - ".strtoupper($cityCap);
        if($country!="")
            $a_header['address'].= $country;

        $denomination = $this->a_ente[$type.'_Denominazione'];

        $a_header['recipient'] = $denomination;
        if(strlen($denomination)>45){
            $a_denomination = array();
            $a_denomination[0] = substr($denomination, 0, strrpos(substr($denomination, 0, 40), ' '));
            $a_denomination[1] = substr($denomination, strlen($a_denomination[0])+1, strrpos(substr($denomination, strlen($a_denomination[0])+1), ' '));
            $a_header['denomination'] = $a_denomination;
        }
        else{
            $a_header['denomination'][0] = $denomination;
        }

        $a_header['placeDate'] = $placeDate;
        if(!empty($a_references))
            $a_header['references'] = $a_references;

        return $a_header;
    }

    public function setPrintHeader($printType=null, $a_SMA=null){

        $this->getLogo();
        if($printType==4){
            if($this->a_ente['Gestore_ID']!=null){
                $type = "Gestore";
                $this->a_header['left'][0] = $this->a_ente[$type.'_Tipo']." ".$this->a_ente[$type.'_Denominazione'];
                $this->a_header['left'][3] = $this->a_ente['Info_Denominazione']." (".$this->a_ente[$type.'_Provincia'].")";
                
//                $this->a_header['left'][6] = "Gestione: ".$this->getCityDenomination();
            }
            else{
                $type = "Info";
                $this->a_header['left'][0] = $this->a_ente[$type.'_Denominazione']." (".$this->a_ente[$type.'_Provincia'].")";
                $this->a_header['left'][3] = "";
//                $this->a_header['left'][6] = "";
            }

            $this->a_header['left'][1] = $this->getAddressRow($type);
            $this->a_header['left'][2] = $this->getMailPecRow($type);
            
            $this->a_header['left'][4] = "";
            $this->a_header['left'][5] = "";


            $type2 = "Ufficio";
        }
        else if(is_null($a_SMA) || $a_SMA['SMA']=="n" || $printType>2){
            if($this->a_ente['Gestore_ID']!=null){
                $type = "Gestore";
                $this->a_header['left'][0] = $this->a_ente[$type.'_Tipo']." ".$this->a_ente[$type.'_Denominazione'];
//                $this->a_header['left'][6] = "Gestione: ".$this->getCityDenomination();
            }
            else{
                $type = "Info";
                $this->a_header['left'][0] = $this->a_ente[$type.'_Denominazione']." (".$this->a_ente[$type.'_Provincia'].")";
//                $this->a_header['left'][6] = "";
            }

            $this->a_header['left'][1] = $this->getAddressRow($type);
            $this->a_header['left'][2] = $this->getCfPiRow($type);
            $this->a_header['left'][3] = $this->getPhoneFaxRow($type);
            $this->a_header['left'][4] = $this->getMailSiteRow($type);
            $this->a_header['left'][5] = "Servizio: Riscossione coattiva";

            $type2 = "Ufficio";
        }
        else{

            $strExtraSMA = "";
            if($printType==2)
                $strExtraSMA = "_Mod23O";
            $this->a_header['left'][0] = $a_SMA['Restituzione1'.$strExtraSMA];
            $this->a_header['left'][1] = $a_SMA['Restituzione2'.$strExtraSMA];
            $this->a_header['left'][2] = ucfirst(strtolower("RESTITUZIONE PIEGO IN CASO DI MANCATO RECAPITO:"));
            $this->a_header['left'][3] = $a_SMA['Restituzione3'.$strExtraSMA];
            $this->a_header['left'][4] = $a_SMA['Restituzione4'.$strExtraSMA];
            $this->a_header['left'][5] = $a_SMA['Restituzione5'.$strExtraSMA];


            if(!empty($this->a_ente['Ufficio_ID']))
                $type2 = "Ufficio";
            else if(!empty($this->a_ente['Gestore_ID']))
                $type2 = "Gestore";
            else
                $type2 = "Info";
        }

        $this->a_header['right'][0] = $this->a_ente[$type2.'_Denominazione'];
        $this->a_header['right'][1] = $this->getAddressRow_1($type2);
        $this->a_header['right'][2] = $this->getPhoneFaxRow($type2);
        $this->a_header['right'][3] = $this->getMailPecRow($type2);

        $a_timeTable = $this->getTimeTableRows($type2);

        $this->a_header['right'][4] = $a_timeTable[0];
        $this->a_header['right'][5] = $a_timeTable[1];
    }


    public function setPrintHeaderLAB($printType=null, $a_SMA=null){

        $this->getLogo();

        $this->a_header['left_under'][0] = $this->getComonDenomination("Info");
        $this->a_header['left_under'][1] = $this->getComonAddressRow_1("Info");
        $this->a_header['left_under'][2] = $this->getComonAddressRow_2("Info");
        $this->a_header['left_under'][3] = $this->getComonContact("Info");

        $strExtraSMA = "";
        if($printType==2)
            $strExtraSMA = "_Mod23O";

        $this->a_header['left'][0] = "IN CASO DI MANCATO RECAPITO, RESTITUIRE A:";
        $this->a_header['left'][1] = strtoupper($a_SMA['Restituzione3'.$strExtraSMA]);
        $this->a_header['left'][2] = strtoupper($a_SMA['Restituzione4'.$strExtraSMA]);
        $this->a_header['left'][3] = strtoupper($a_SMA['Restituzione5'.$strExtraSMA]);


        if(!empty($this->a_ente['Ufficio_ID']))
            $type2 = "Ufficio";
        else if(!empty($this->a_ente['Gestore_ID']))
            $type2 = "Gestore";
        else
            $type2 = "Info";

        $this->a_header['right'][0] = $this->getAddressRow_1($type2);
        $this->a_header['right'][1] = $this->getAddressRow_2($type2);
        $this->a_header['right'][2] = $this->getAddressRow_SO_1($type2);
        $this->a_header['right'][3] = $this->getAddressRow_SO_2($type2);
        $this->a_header['right'][4] = $this->getPIRow($type2);
        $this->a_header['right'][5] = $this->getPhoneFaxRow($type2);
        $this->a_header['right'][6] = $this->getPecRow($type2);

        $a_timeTable = $this->getTimeTableRows($type2);

        //$this->a_header['right'][7] = $a_timeTable[0];
        //$this->a_header['right'][8] = $a_timeTable[1];
    }

    public function getComonDenomination($type){

        if($this->a_ente[$type.'_Denominazione']!=""){
           return $this->a_ente[$type.'_Denominazione'];
        }

        return "";
    }

    public function getComonAddressRow_1($type){

        $address = "";
        if($this->a_ente[$type.'_Via']!=""){
            $address = ucwords(strtolower($this->a_ente[$type.'_Via']));
            if(!empty($this->a_ente[$type.'_Civico']))
                $address.=" ".$this->a_ente[$type.'_Civico'];
            if(!empty($this->a_ente[$type.'_Esponente']))
                $address.= $this->a_ente[$type.'_Esponente'];
            if(!empty($this->a_ente[$type.'_Interno']))
                $address.="/".$this->a_ente[$type.'_Interno'];
        }

        return $address;

    }


    public function getComonAddressRow_2($type){

        $address = "";
        if($this->a_ente[$type.'_Comune']!=""){
            $address = $this->a_ente[$type.'_Cap']." ".$this->a_ente[$type.'_Comune'];
            if(!empty($this->a_ente[$type . '_Provincia']))
                $address.= " (".$this->a_ente[$type . '_Provincia'].")";
        }

        return $address;

    }

    public function getComonContact($type){
        
        $data = "";
        if($this->a_ente[$type.'_PI']!=""){
            $data = "P.IVA ". $this->a_ente[$type.'_PI']." - ";
        }
        if($this->a_ente[$type.'_Telefono']!=""){
            $data .= "TEL ". $this->a_ente[$type.'_Telefono'];
        }

        return $data;

    }

    public function getLogo(){
        $this->a_header['logo_ente'] = $this->logo['ente'] = array("webPath"=>STEMMIWEB."/".$this->a_ente['CC']."/".$this->a_ente['Stemma_1'],"rootPath"=>STEMMI."/".$this->a_ente['CC']."/".$this->a_ente['Stemma_1']);
        $this->logo['gestore'] = array("webPath"=>"","rootPath"=>"");

        if($this->a_ente['Gestore_ID']!=null && $this->a_ente['Gestore_Tipo']=="Concessionario"){
            if($this->a_ente['Gestore_Stemma']!=""){
                $this->a_header['logo'] = STEMMIWEB."/".$this->a_ente['CC']."/".$this->a_ente['Gestore_Stemma'];
                $this->a_header['logoPath'] = STEMMI."/".$this->a_ente['CC']."/".$this->a_ente['Gestore_Stemma'];


            }
            else{
                $this->a_header['logo'] = IMMAGINIWEB."/logo_LAB.png";
                $this->a_header['logoPath'] = IMMAGINI."/logo_LAB.png";
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
            if(!empty($this->a_ente[$type.'_Civico']))
                $address.=", ".$this->a_ente[$type.'_Civico'];
            if(!empty($this->a_ente[$type.'_Esponente']))
                $address.= $this->a_ente[$type.'_Esponente'];
            if(!empty($this->a_ente[$type.'_Interno']))
                $address.="/".$this->a_ente[$type.'_Interno'];
            if(!empty($this->a_ente[$type.'_Dettagli']))
                $address.=", ".$this->a_ente[$type.'_Dettagli'];
            if(!empty($this->a_ente[$type.'_Comune']))
                $address.= " - ".$this->a_ente[$type.'_Cap']." ".$this->a_ente[$type.'_Comune'];
            if(!empty($this->a_ente[$type . '_Provincia']))
                $address.= " (".$this->a_ente[$type . '_Provincia'].")";
//            if(!empty($this->a_ente[$type.'_Frazione']))
//                $address.= " ".$this->a_ente[$type.'_Frazione'];
        }

        return $address;
    }

    public function getAddressRow_1($type){
        $address = "";
        if($this->a_ente[$type.'_Via']!=""){
            $address = "Sede Legale ".ucwords(strtolower($this->a_ente[$type.'_Via']));
            if(!empty($this->a_ente[$type.'_Civico']))
                $address.=" ".$this->a_ente[$type.'_Civico'];
            if(!empty($this->a_ente[$type.'_Esponente']))
                $address.= $this->a_ente[$type.'_Esponente'];
            if(!empty($this->a_ente[$type.'_Interno']))
                $address.="/".$this->a_ente[$type.'_Interno'];
        }

        return $address;
    }

    public function getAddressRow_2($type){
        $address = "";
        if($this->a_ente[$type.'_Comune']!=""){
            $address = $this->a_ente[$type.'_Cap']." ".$this->a_ente[$type.'_Comune'];
            if(!empty($this->a_ente[$type . '_Provincia']))
                $address.= " (".$this->a_ente[$type . '_Provincia'].")";
        }

        return $address;
    }

    public function getAddressRow_SO_1($type){
        $address = "";
        if($this->a_ente[$type.'_Via_SO']!=""){
            $address = "Sede Operativa ".ucwords(strtolower($this->a_ente[$type.'_Via_SO']));
            if(!empty($this->a_ente[$type.'_Civico_SO']))
                $address.=" ".$this->a_ente[$type.'_Civico_SO'];
            if(!empty($this->a_ente[$type.'_Esponente_SO']))
                $address.= $this->a_ente[$type.'_Esponente_SO'];
            if(!empty($this->a_ente[$type.'_Interno_SO']))
                $address.="/".$this->a_ente[$type.'_Interno_SO'];
        }

        return $address;
    }

    public function getAddressRow_SO_2($type){
        $address = "";
        if($this->a_ente[$type.'_Comune_SO']!=""){
            $address = $this->a_ente[$type.'_Cap_SO']." ".$this->a_ente[$type.'_Comune_SO'];
            if(!empty($this->a_ente[$type . '_Provincia_SO']))
                $address.= " (".$this->a_ente[$type . '_Provincia_SO'].")";
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
            $mailSite = "e-mail: " . $this->a_ente[$type.'_Mail'] ."  -  Sito: ".$this->a_ente[$type.'_Sito'];

            if($this->a_ente[$type.'_Mail'] == "")
                $mailSite = "Sito: ".$this->a_ente[$type.'_Sito'];
            else if($this->a_ente[$type.'_Sito'] == "")
                $mailSite = "e-mail: " . $this->a_ente[$type.'_Mail'];
        }
        return $mailSite;
    }

    public function getMailPecRow($type)
    {
        $mailPec = "";
        if($this->a_ente[$type.'_Mail']!="" || $this->a_ente[$type.'_PEC']!="")
        {
            $mailPec = "e-mail: " . $this->a_ente[$type.'_Mail'] ."  -  PEC: ".$this->a_ente[$type.'_PEC'];

            if($this->a_ente[$type.'_Mail'] == "")
                $mailPec = "PEC: ".$this->a_ente[$type.'_PEC'];
            else if($this->a_ente[$type.'_PEC'] == "")
                $mailPec = "e-mail: " . $this->a_ente[$type.'_Mail'];
        }
        return $mailPec;
    }

    public function getPecRow($type)
    {
        $Pec = "";
        if($this->a_ente[$type.'_PEC']!="")
        {
            $Pec = "PEC: ".$this->a_ente[$type.'_PEC'];
        }
        return $Pec;
    }

    public function getPIRow($type){
        $PI = "";
        if($this->a_ente[$type.'_PI']!="")
        {
            $PI = "P.IVA: ".$this->a_ente[$type.'_PI'];
        }
        return $PI;
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

    public function getStemmaImgArray(){
        $a_img = array();
        $defaultFilename = "Not_Found.png";
        $a_defaultImg = array(
            "flag"=>false,
            "filename"=>$defaultFilename,
            "path"=>STEMMI."/DEFAULT/".$defaultFilename,
            "webpath"=>STEMMIWEB."/DEFAULT/".$defaultFilename
        );
        if(is_file($a_defaultImg['path'])) {
            $imagick = new Imagick($a_defaultImg['path']);
            $a_defaultImg['dim'] = $imagick->getImageGeometry();
        }
        else
            return false;

        for($i=1;$i<=3;$i++){
            switch($i){
                case 1: $filename = $this->a_ente["Stemma_1"];  break;
                case 2: $filename = $this->a_ente["Stemma_2"];  break;
                case 3: $filename = $this->a_ente["Gestore_Stemma"];  break;
            }

            $a_img[$i] = array(
                "flag"=>true,
                "filename"=>$filename,
                "path"=>STEMMI."/".$this->a_ente["CC"]."/".$filename,
                "webpath"=>STEMMIWEB."/".$this->a_ente["CC"]."/".$filename
            );
            if(is_file($a_img[$i]['path'])){
                $imagick = new Imagick($a_img[$i]['path']);
                $a_img[$i]['dim'] = $imagick->getImageGeometry();
            }
            else
                $a_img[$i] = $a_defaultImg;
        }
        return $a_img;
    }
}