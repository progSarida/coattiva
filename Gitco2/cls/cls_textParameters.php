<?php


class cls_text
{
    public $a_textParams;
    public $a_text;
    public $a_textReplaced;
    public $table;

    public $cls_help;

    public function __construct($table){
        $this->table = $table;
        $this->cls_help = new cls_help();
    }

    public function getCCParametersQuery ($cc){
        $query = "SELECT * FROM ".$this->table." WHERE cc = '" . $cc . "' ";
        return $query;
    }

    public function getParametersQuery ($date){
        $query = "SELECT * FROM ".$this->table." WHERE updated <= '". $date . "' ORDER BY updated DESC LIMIT 1";
        return $query;
    }

    public function getPageTitle(){
        switch($this->table){
            case "text_sollecito_pre_ingiunzione":
                return "Testo Sollecito Pre Ingiunzione";
                break;
            case "text_avviso_messa_in_mora":
                return "Testo Avviso di Messa in Mora";
                break;
            default:
                return "Testo sconosciuto!";
        }
    }

    public function getFieldNames(){
        $a_params = array(
            1=>array("field"=>"Intestazione relata","id"=>"header","var"=>array()),
            2=>array("field"=>"Sottointestazione relata","id"=>"subheader","var"=>array()),
            3=>array("field"=>"Testo relata","id"=>"text","var"=>array("{DESTINATARIO}","{TIPO_INVIO}"))
        );

        $a_direct = array(
            "title"=>"Riscossione diretta",
            "params"=>$a_params,
            "page"=>1
        );

        $a_collector = array(
            "title"=>"Ufficiale della riscossione",
            "params"=>$a_params,
            "page"=>1
        );
        $a_collector['params'][3]['var'][] = "{GESTORE}";
        $a_bailiff = array(
            "title"=>"Ufficiale giudiziario",
            "params"=>$a_params,
            "page"=>1
        );
        $a_bailiff['params'][1]['var'][] = "{TRIBUNALE}";
        $a_bailiff['params'][3]['var'][] = "{TRIBUNALE}";

        switch($this->table){
            case "text_sollecito_pre_ingiunzione":

                $a_fields = array("oggetto","comunicazione importi","dati pagamento",
                    "titolo avvertenze","avvertenze 1","avvertenze 2","avvertenze 3",
                    "titolo informazioni", "informazioni", "titolo opposizione", "opposizione","saluti",
                    "firma sinistra", "firma destra", "testo firme");
                $a_align = array("J","J","J","C","J","J","J","C","J","C","J","J","L","R","J");
                $a_page = array(1,1,1,1,1,1,1,1,1,1,1,1,1,1,1);
                $a_fontWeight = array("B","","","B","","","","B","","B","","","","","");
                $a_var = array(
                    0=>array("{CRONOLOGICO}","{TIPO_RISCOSSIONE}","{ANNO_RISCOSSIONE}","{ENTE_GESTITO}"),
                    1=>array("{INFO_CARTELLA}","{IMPORTO_DOVUTO}"),
                    2=>array("{GIORNI_PAGAMENTO}","{NUMERO_CCP}","{INTESTATARIO_CCP}"),
                    6=>array("{RIFERIMENTI_PAGAMENTO}","{RECAPITI_UFFICIO}")
                );
                $a_notification = array();

                break;
            case "text_avviso_messa_in_mora":

                $a_fields = array("oggetto","comunicazione importi","dati pagamento",
                    "titolo riesame","riesame provvedimento 1","riesame provvedimento 2","titolo avvertenze",
                    "avvertenze","titolo informazioni", "informazioni", "titolo opposizione", "opposizione",
                    "crediti tributari","crediti non tributari","tutti i crediti",
                    "saluti","firma sinistra", "firma destra", "testo firme");
                $a_align = array("J","J","J","C","J","J","C","J","C","J","C","J","J","J","J","J","L","R","J");
                $a_page = array(1,1,1,1,1,1,1,1,1,1,1,1,2,2,2,2,2,2,2);
                $a_fontWeight = array("B","","","B","","","B","","B","","B","","","","","","","","");
                $a_var = array(
                    0=>array("{TITOLO_ATTO}","{TIPO_RISCOSSIONE}","{ANNO_RISCOSSIONE}","{ENTE_GESTITO}"),
                    1=>array("{INFO_CARTELLA}","{IMPORTO_DOVUTO}"),
                    2=>array("{GIORNI_PAGAMENTO}","{NUMERO_CCP}","{INTESTATARIO_CCP}","{SPESE_RACCOMANDATA}","{SPESE_RACCOMANDATA_AG}"),
                    7=>array("{RIFERIMENTI_PAGAMENTO}","{RECAPITI_UFFICIO}"),
                    12=>array("{GIORNI_CTP}","{RECAPITI_CTP}"),
                    13=>array("{GIORNI_GDP}","{RECAPITI_GDP}")
                );
                $a_notification = array("collector"=>$a_collector,"bailiff"=>$a_bailiff);
                $a_notification['collector']['page'] = 2;
                $a_notification['bailiff']['page'] = 2;
                break;

        }

        $i=1;
        foreach($a_fields as $key=>$value){

            $this->a_textParams[$i]['field'] = $a_fields[$i-1];
            $this->a_textParams[$i]['page'] = $a_page[$i-1];
            $this->a_textParams[$i]['alignment'] = $a_align[$i-1];
            $this->a_textParams[$i]['fontWeight'] = $a_fontWeight[$i-1];
            if(isset($a_var[$i-1]))
                $this->a_textParams[$i]['variables'] = $a_var[$i-1];
            else
                $this->a_textParams[$i]['variables'] = array();
            $i++;
        }
        $this->a_textParams[1]['notification'] = $a_notification;
    }

    public function setTextArray($a_text){
        if(!is_array($a_text)){
            $this->cls_help->alert('Attenzione! Inserire i parametri di testo per il '.$this->getPageTitle());
            die;
        }
        else{
            $this->a_text = $a_text;
            $this->a_textReplaced = $a_text;
        }
    }

    public function checkInformations(){
        if(isset($this->a_text[$this->getFieldText()])){
            if($this->a_text[$this->getFieldText()]==""){
                $this->cls_help->alert("Attenzione il campo informazioni e' vuoto!");
                die;
            }
        }
    }

    public function getNotificationReport(){

    }

    public function getFieldText($field="informazioni"){

        for($i=1;$i<=count($this->a_textParams);$i++){
            if ($this->a_textParams[$i]['field'] == $field)
                return 'field'.$i;
        }

        return false;
    }

    public function replaceVariables(array $a_var){

        $this->a_textReplaced = $this->a_text;

        for($i=1;$i<=count($this->a_textParams);$i++){
            if($i==1){
                foreach($this->a_textParams[1]['notification'] as $notifType=>$a_notification){
                    foreach($a_notification['params'] as $keyPar=>$a_notifPar){
                        foreach($a_notifPar['var'] as $keyVar=>$varName){
                            foreach($a_var as $key=>$value) {
                                if ($key == $varName) {
                                    $replaceText = str_replace($key, $value, $this->a_textReplaced[$notifType."_".$a_notifPar['id']]);
                                    $this->a_textReplaced[$notifType."_".$a_notifPar['id']] = $replaceText;

                                }
                            }
                        }
                    }
                }
            }

            foreach($this->a_textParams[$i]['variables'] as $varName){
                foreach($a_var as $key=>$value) {
                    if ($key == $varName) {
                        $this->a_textReplaced['field'.$i] = str_replace($key, $value, $this->a_textReplaced['field'.$i]);
                    }
                }
            }
        }
    }
}

?>