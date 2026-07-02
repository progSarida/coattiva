<?php
include_once CLS."/cls_place.php";
include_once CLS."/cls_help.php";
class cls_registry{
    public $a_header;
    private $cls_help;

    public function __construct()
    {
        $this->cls_help = new cls_help();
    }

    public function getRecord_query($id){
        $query = "SELECT * FROM utente ";
        $query.= "WHERE ID=".$id;
        return $query;
    }

    public function getVAnagrafe_query($id){
        $query = "SELECT * FROM v_anagrafe ";
        $query.= "WHERE Utente_ID=".$id;
        return $query;
    }

    public function getCompany_query($cc, $VAT, $company=null){
        $query = "SELECT * FROM utente ";
        $query.= "WHERE CC_Comune='".$cc."' AND Genere='D' AND Partita_Iva='".$VAT."' ";
        if($company!=null)
            $query.= "AND Ditta=\"".$company."\"";

        return $query;
    }

    public function getPerson_query($cc, $fiscalCode){
        $query = "SELECT * FROM utente ";
        $query.= "WHERE CC_Comune='".$cc."' AND Genere!='D' AND Codice_Fiscale='".$fiscalCode."'";
        return $query;
    }

    public function getAddressFromName_query($cc, $ccDestinatario, $addressName, $cap, $city, $country){
        $query = "SELECT * FROM toponimo ";
        $query.= "WHERE CC_Comune='".$cc."' AND CC_Toponimo='".$ccDestinatario."' AND Nome=\"".$addressName."\" ";
        $query.= "AND Cap='".$cap."' AND Comune=\"".$city."\" AND Paese=\"".$country."\"";

        return $query;
    }

    public function getLastRecord_query ($cc){
        $query = "SELECT * FROM utente WHERE CC_Comune = '".$cc."' ORDER BY Comune_ID DESC LIMIT 1";
        return $query;
    }

    public function printHeader(array $a_recipient, $placeDate = null){

        if($a_recipient['Rec_ID']!=null)
            $type = "Rec";
        else if($a_recipient['Dom_ID']!=null)
            $type = "Dom";
        else
            $type = "Res";

        $address = ucwords(strtolower($a_recipient[$type.'_Via']));
        if($a_recipient[$type.'_Frazione'])
            $address = $a_recipient[$type.'_Frazione'].", ".$address;
        if($a_recipient[$type.'_Paese']=="Italia"){
            if($a_recipient[$type.'_Civico']>0)
                $address.=", ".$a_recipient[$type.'_Civico'];
            if($a_recipient[$type.'_Esponente']!="")
                $address.= $a_recipient[$type.'_Esponente'];
            if($a_recipient[$type.'_Interno']>0)
                $address.="/".$a_recipient[$type.'_Interno'];
            if($a_recipient[$type.'_Dettagli']!="")
                $address.=", ".$a_recipient[$type.'_Dettagli'];

            $country = "";
        }
        else
            $country = $a_recipient[$type.'_Paese'];


//        $a_address = array();
        $a_address = explode( "\n", wordwrap( strtoupper($address), 38));
//        if(strlen($address)>40){
//            $a_address[0] = substr($address, 0, strrpos(substr($address, 0, 39), ' '));
//            $a_address[1] = substr($address, strlen($a_address[0])+1, strrpos(substr($address, strlen($a_address[0])+1), ' '));
//        }
//        else
//            $a_address[0] = $address;

        $a_header['addressName'] = $address;
        $a_header['addressCap'] = "";
        $a_header['addressCity'] = "";
        $a_header['addressProvince'] = "";
        $a_header['addressCountry'] = $country;
        $cityCap = "";
        if($a_recipient[$type.'_Comune']!=""){
            $cityCap = $a_recipient[$type.'_Cap']." ".$a_recipient[$type.'_Comune'];
            $a_header['addressCap'] = $a_recipient[$type.'_Cap'];
            $a_header['addressCity'] = $a_recipient[$type.'_Comune'];
            if($a_recipient[$type.'_Provincia']!=""){
                $cityCap.= " ".$a_recipient[$type.'_Provincia'];
                $a_header['addressProvince'] = $a_recipient[$type.'_Provincia'];
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

        $denomination = $a_recipient['Cognome_Ditta'];
        if($a_recipient['Genere']!="D" && $a_recipient['Nome']!="")
            $denomination.= " ".$a_recipient['Nome'];

        if($a_recipient[$type.'_Presso']!="")
            $denomination.= " C/O ".$a_recipient[$type.'_Presso'];

        $a_header['recipient'] = $denomination;
        $a_header['denomination'] = explode( "\n", wordwrap( strtoupper($denomination), 38));
//        if(strlen($denomination)>40){
//            $a_denomination = array();
//            $a_denomination[0] = substr($denomination, 0, strrpos(substr($denomination, 0, 39), ' '));
//            $a_denomination[1] = substr($denomination, strlen($a_denomination[0])+1, strrpos(substr($denomination, strlen($a_denomination[0])+1), ' '));
//            $a_header['denomination'] = $a_denomination;
//        }
//        else{
//            $a_header['denomination'][0] = $denomination;
//        }

        $a_header['references'][0] = "PARTITA NUMERO:  ".$a_recipient['Comune_ID']." / ".$a_recipient['Anno_Riferimento'];
        $a_header['references'][1] = "CODICE UTENTE:  ".$a_recipient['Utente_Comune_ID']." / ".$a_recipient['CC'];
        if(isset($a_recipient['Protocollo']) && $a_recipient['Protocollo']!=""){
            $a_header['references'][2] = "PROTOCOLLO:  ".$a_recipient['Protocollo'];
            $a_header['references'][3] = "DEL:  ".$this->cls_help->toItalianDate($a_recipient['Data_Protocollo']);
        }
        else{
            $a_header['references'][2] = "";
            $a_header['references'][3] = "";
        }

        $a_header['placeDate'] = $placeDate;

        return $a_header;

    }
    public function printHeaderTerzo(array $a_recipient, $placeDate = null){

        if($a_recipient['Rec_ID']!=null)
            $type = "Rec";
        else if($a_recipient['Dom_ID']!=null)
            $type = "Dom";
        else
            $type = "Res";

        $address = ucwords(strtolower($a_recipient[$type.'_Via']));
        if($a_recipient[$type.'_Frazione'])
            $address = $a_recipient[$type.'_Frazione'].", ".$address;
        if($a_recipient[$type.'_Paese']=="Italia"){
            if($a_recipient[$type.'_Civico']>0)
                $address.=", ".$a_recipient[$type.'_Civico'];
            if($a_recipient[$type.'_Esponente']!="")
                $address.= $a_recipient[$type.'_Esponente'];
            if($a_recipient[$type.'_Interno']>0)
                $address.="/".$a_recipient[$type.'_Interno'];
            if($a_recipient[$type.'_Dettagli']!="")
                $address.=", ".$a_recipient[$type.'_Dettagli'];

            $country = "";
        }
        else
            $country = $a_recipient[$type.'_Paese'];



        $a_address = explode( "\n", wordwrap( strtoupper($address), 38));

        $a_header['addressName'] = $address;
        $a_header['addressCap'] = "";
        $a_header['addressCity'] = "";
        $a_header['addressProvince'] = "";
        $a_header['addressCountry'] = $country;
        $cityCap = "";
        if($a_recipient[$type.'_Comune']!=""){
            $cityCap = $a_recipient[$type.'_Cap']." ".$a_recipient[$type.'_Comune'];
            $a_header['addressCap'] = $a_recipient[$type.'_Cap'];
            $a_header['addressCity'] = $a_recipient[$type.'_Comune'];
            if($a_recipient[$type.'_Provincia']!=""){
                $cityCap.= " ".$a_recipient[$type.'_Provincia'];
                $a_header['addressProvince'] = $a_recipient[$type.'_Provincia'];
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

        $denomination = $a_recipient['Cognome_Ditta'];
        if($a_recipient['Genere']!="D" && $a_recipient['Nome']!="")
            $denomination.= " ".$a_recipient['Nome'];

        if($a_recipient[$type.'_Presso']!="")
            $denomination.= " C/O ".$a_recipient[$type.'_Presso'];

        $a_header['recipient'] = $denomination;
        $a_header['denomination'] = explode( "\n", wordwrap( strtoupper($denomination), 38));

        $a_header['placeDate'] = $placeDate;

        return $a_header;

    }

    public function printHeaderBanca(array $a_recipient, $placeDate = null){

        

        $address = ucwords(strtolower($a_recipient['Toponimo']));
        if($a_recipient['Frazione'])
            $address = $a_recipient['Frazione'].", ".$address;
        if($a_recipient['Paese']=="Italia"){
            if($a_recipient['Civico']>0)
                $address.=", ".$a_recipient['Civico'];
            if($a_recipient['Esponente']!="")
                $address.= $a_recipient['Esponente'];
            if($a_recipient['Interno']>0)
                $address.="/".$a_recipient['Interno'];
            if($a_recipient['Dettagli']!="")
                $address.=", ".$a_recipient['Dettagli'];

            $country = "";
        }
        else
            $country = $a_recipient['Paese'];



        $a_address = explode( "\n", wordwrap( strtoupper($address), 38));

        $a_header['addressName'] = $address;
        $a_header['addressCap'] = "";
        $a_header['addressCity'] = "";
        $a_header['addressProvince'] = "";
        $a_header['addressCountry'] = $country;
        $cityCap = "";
        if($a_recipient['Comune']!=""){
            $cityCap = $a_recipient['Cap']." ".$a_recipient['Comune'];
            $a_header['addressCap'] = $a_recipient['Cap'];
            $a_header['addressCity'] = $a_recipient['Comune'];
            if($a_recipient['Provincia']!=""){
                $cityCap.= " ".$a_recipient['Provincia'];
                $a_header['addressProvince'] = $a_recipient['Provincia'];
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

        $denomination = $a_recipient['Denominazione'];

        
        $a_header['recipient'] = $denomination;
        $a_header['denomination'] = explode( "\n", wordwrap( strtoupper($denomination), 38));

        $a_header['placeDate'] = $placeDate;

        return $a_header;

    }
    function decode_CF( $CF )
    {
        $array_CF = Array();

        $alfabeto = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $alfabeto_disp = "BAKPLCQDREVOSFTGUHMINJWZYX";
        $numeri = "0123456789";
        $numeri_disp = "10   2 3 4   5 6 7 8 9";

        $lettere_mesi = "ABCDEHLMPRST";
        $lettere_omocodia = "LMNPQRSTUV";
        $checkOmocodia = 0;

        $cognome = substr($CF,0,3);
        $array_CF['COGNOME'] = $cognome;
        $nome = substr($CF,3,3);
        $array_CF['NOME'] = $nome;

        $annoStr = substr($CF,6,2);
        $anno = "";
        for($i=0;$i<strlen($annoStr);$i++){
            if (preg_match("/^\d+$/", substr($annoStr,$i,1)))
                $anno.= substr($annoStr,$i,1);
            else{
                $checkOmocodia = 1;
                $anno.= strpos($lettere_omocodia, substr($annoStr,$i,1));
            }
        }
        $anno = (int)$anno;

        $mese = substr($CF,8,1);
        $mese = strpos($lettere_mesi, $mese)+1;
        if(strlen($mese)<2)		$mese_nascita = "0".$mese;
        else					$mese_nascita = $mese;

        $giornoStr = substr($CF,9,2);
        $giorno = "";
        for($i=0;$i<strlen($giornoStr);$i++){
            if (preg_match("/^\d+$/", substr($giornoStr,$i,1)))
                $giorno.= substr($giornoStr,$i,1);
            else{
                $checkOmocodia = 1;
                $giorno.= strpos($lettere_omocodia, substr($giornoStr,$i,1));
            }
        }

        if(intval($giorno) > 40){
            $array_CF['SESSO'] = "F";
            $giorno = intval($giorno) - 40;
            $giorno = strval($giorno);
        }
        else{
            $array_CF['SESSO'] = "M";
        }

        if(strlen($giorno)<2)	$giorno_nascita = "0".$giorno;
        else					$giorno_nascita = $giorno;

        $anno_odierno = date('Y');
        $pref_anno = substr($anno_odierno,0,2);
        $pref_anno_int = intval($pref_anno);
        $post_anno = substr($anno_odierno,2,2);
        $post_anno_int = intval($post_anno);

        if( $anno - $post_anno_int >= -5 )
            $pref_anno = strval( $pref_anno_int - 1 );

        $anno_nascita = $pref_anno . $anno;
        $array_CF['DATA_NASCITA'] = $anno_nascita."-".$mese_nascita."-".$giorno_nascita;

        $ccStr = substr($CF,12,3);
        $CC = substr($CF,11,1);

        for($i=0;$i<strlen($ccStr);$i++){
            if (preg_match("/^\d+$/", substr($ccStr,$i,1))){
                $CC.= substr($ccStr,$i,1);
            }
            else {
                $checkOmocodia = 1;
                $CC.= strpos($lettere_omocodia, substr($ccStr, $i, 1));
            }
        }

        $array_CF['CC_NASCITA'] = $CC;
        if($CC != null)
        {
            $cls_db = new cls_db();
            $cls_place = new cls_place();
            $verifica_stato = substr($CC,0,1);
            if($verifica_stato=="Z")
            {
                $countryQuery = $cls_place->getCountry_query("",$CC);
                $a_country = $cls_db->getArrayLine($cls_db->SelectQuery($countryQuery));
                if(is_null($a_country))
                    return false;

                $array_CF['PAESE_NASCITA'] = $a_country['Nome'];
                $array_CF['COMUNE_NASCITA'] = "";
                $array_CF['CC_NASCITA'] = $a_country['CC_Paese_Estero'];
                $array_CF['PROVINCIA_NASCITA'] = "";
            }
            else
            {
                $cityQuery = $cls_place->getCity_query("",$CC);
                $a_city = $cls_db->getArrayLine($cls_db->SelectQuery($cityQuery));

                if(is_null($a_city))
                    return false;

                $array_CF['PAESE_NASCITA'] = "Italia";
                $array_CF['COMUNE_NASCITA'] = $a_city['Com_Nome'];
                $array_CF['CC_NASCITA'] = $a_city['Com_Codice_Catastale'];
                $array_CF['PROVINCIA_NASCITA'] = $a_city['Pro_Sigla'];
            }
        }
        else
        {
            $array_CF['PAESE_NASCITA'] = "";
            $array_CF['COMUNE_NASCITA'] = "";
        }

        $array_CF['OMOCODIA'] = $checkOmocodia;

        $sommaCod = 0;
        for($i=0;$i<strlen($CF)-1;$i++){
            $char = substr($CF,$i,1);
            if(($i%2)==0)
                $sommaCod+= strrpos($numeri_disp,$char) + strrpos($alfabeto_disp,$char);
            else
                $sommaCod+= strrpos($numeri,$char) + strrpos($alfabeto,$char);
        }

        $array_CF['CODICE_CONTROLLO'] = substr($alfabeto,($sommaCod%26),1);
        if($array_CF['CODICE_CONTROLLO']!=substr($CF,15,1))
            return false;
        else
            return $array_CF;
    }

    function compute_CF($cognome, $nome, $tipo, $data, $cod_comune)
    {
        $alfabeto = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $vocali = "AEIOU";
        $numeri = "0123456789";
        $mesi = "ABCDEHLMPRST";
        $alfabeto_disp = "BAKPLCQDREVOSFTGUHMINJWZYX";
        $numeri_disp = "10   2 3 4   5 6 7 8 9";

        $CF = "";
        $code = 0;
        if($tipo=="D") return "Impossibile generare il Codice Fiscale per una persona giuridica.";

        // Determina
        for($i=0; $i<=1; $i++)
        {
            $word = ($i==0 ? $cognome : $nome);
            $word = str_replace(" ","",$word);
            $word = str_replace("\'","",$word);
            $word = str_replace("à","a",$word);
            $word = str_replace("è","e",$word);
            $word = str_replace("é","e",$word);
            $word = str_replace("ì","i",$word);
            $word = str_replace("ò","o",$word);
            $word = str_replace("ù","u",$word);
            $word = strtoupper($word);

            $extracted_cons = "";
            $extracted_vocs = "";

            for($j=0; $j<strlen($word); $j++)
            {
                $char = substr($word,$j,1);
                $isthere = strrpos($vocali, $char);
                if($isthere===FALSE) // NOTA: I tre "=" sono voluti.
                    $extracted_cons = $extracted_cons.$char;
                else
                    $extracted_vocs = $extracted_vocs.$char;
            }

            $num_cons = strlen($extracted_cons);
            $num_vocs = strlen($extracted_vocs);

            if    ($num_cons>3 and $i==1)
                $CF = $CF.substr($extracted_cons,0,1).substr($extracted_cons,2,2);
            else if($num_cons>2)
                $CF = $CF.substr($extracted_cons,0,3);
            else if($num_cons==2 and $num_vocs>0)
                $CF = $CF.$extracted_cons.substr($extracted_vocs,0,1);
            else if($num_cons==1 and $num_vocs==1)
                $CF = $CF.$extracted_cons.$extracted_vocs."X";
            else if($num_cons==1 and $num_vocs>1)
                $CF = $CF.$extracted_cons.substr($extracted_vocs,0,2);
            else if($num_cons==0 and $num_vocs>2)
                $CF = $CF.substr($extracted_vocs,0,3);
            else if($num_cons==0 and $num_vocs==2)
                $CF = $CF.$extracted_vocs."X";
            else return "Le lettere che compongono cognome e nome non sono sufficienti per la generazione del Codice Fiscale. Controllare cognome e nome.";
        }

        if($data!=null)
            $array_data = explode("-",$data);
        else
            return false;

        $CF = $CF.substr($array_data[0],2,2);
        $CF = $CF.substr($mesi,$array_data[1]-1,1);
        $CF = $CF.($tipo=="M" ? substr($array_data[2]+100,1,2) : substr($array_data[2]+140,1,2));

        $CF = $CF.$cod_comune;

        for($i=0; $i<strlen($CF); $i++)
        {
            $char = substr($CF,$i,1);
            if(($i%2)==0) // NOTA: se $i � pari, cio� se la lettera � dispari.
                $code = $code + strrpos($numeri_disp,$char) + strrpos($alfabeto_disp,$char);
            else
                $code = $code + strrpos($numeri,$char) + strrpos($alfabeto,$char);
        }

        $CF = $CF.substr($alfabeto,($code%26),1);

        if(strlen($CF)!=16)
            return "Non e' stato possibile generare il Codice Fiscale.";

        return $CF;
    }

    public function check_CFPI($code, $type)
    {

        if($type==2)
        {
            // se la PI e' piu' corta di 11 caratteri allora segnala un errore
            if ( strlen($code) != 11 ) return 'lungh PI';

            // se esistono caratteri diversi da numeri segnala un errore
            if ( preg_match("/[^0-9]/",$code) ) return 'formato PI';

            return true;   // OK, il CF/la PI ha il formato corretto
        }
        else if($type==1)
        {
            // se il CF e' piu' corto di 16 caratteri allora segnala un errore
            if ( strlen($code) != 16 ) return 'lungh CF';

            // se esistono caratteri diversi da lettere e numeri segnala un errore
            if ( preg_match("/[^a-zA-Z0-9]/",$code) ) return 'cara CF';

            // se il formato non e' AAAAAADDADDADDDA segnala l'errore
            if ( !preg_match("/^[a-zA-Z]{6}[0-9]{2}[a-zA-Z][0-9]{2}[a-zA-Z][0-9]{3}[a-zA-Z]$/",$code) ) return 'formato CF';

            return true;   // OK, il CF/la PI ha il formato corretto

        }
    }
}

