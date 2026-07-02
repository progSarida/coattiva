<?php

class cls_authority{

    public function getRecordsquery($authorityType, $cc){
        $query = "SELECT * FROM ufficio_giudiziario WHERE CC=\"".$cc."\" AND Tipo=\"".$authorityType."\"";
        return $query;
    }

    public function getAllRecordsquery($authorityType){
        $query = "SELECT * FROM ufficio_giudiziario WHERE CC=\"****\" AND Tipo=\"".$authorityType."\"";
        return $query;
    }

    public function getAuthorityquery($id){
        $query = "SELECT * FROM ufficio_giudiziario WHERE ID='".$id."'";
        return $query;
    }

    public function getAuthorityTypequery(){
        $query = "SELECT * FROM authority_type";
        return $query;
    }

    public function getContacts(array $a_authority){

        $a_contacts = array("name"=>"");
        switch($a_authority['Tipo']){
            case "giudice":
                $a_contacts['name'] = "Giudice di Pace di ".$a_authority['Comune'];
                break;
            case "comm_trib_prov":
                $a_contacts['name'] = "Commissione Tributaria Provinciale di ".$a_authority['Comune'];
                break;
            case "tribunale":
                $a_contacts['name'] = "Tribunale di ".$a_authority['Comune'];
                break;
            case "istituto":
                $a_contacts['name'] = "IVG ".$a_authority['Denominazione'];
                break;
        }

        $a_contacts['contacts'] = "";
        if($a_authority['Telefono']!="")
            $a_contacts['contacts'].= "Tel: ".$a_authority['Telefono'];
        if($a_authority['Fax']!=""){
            if($a_contacts['contacts']!="")
                $a_contacts['contacts'].= " - ";
            $a_contacts['contacts'].= "Fax: ".$a_authority['Fax'];
        }
        if($a_authority['Mail']!=""){
            if($a_contacts['contacts']!="")
                $a_contacts['contacts'].= " - ";
            $a_contacts['contacts'].= "Mail: ".$a_authority['Mail'];
        }
        if($a_authority['PEC']!=""){
            if($a_contacts['contacts']!="")
                $a_contacts['contacts'].= " - ";
            $a_contacts['contacts'].= "PEC: ".$a_authority['PEC'];
        }

        $a_contacts['address'] = $a_authority['Toponimo'];

        if($a_authority['Civico']>0)
            $a_contacts['address'].= ", ".$a_authority['Civico'];
        if($a_authority['Esponente']!="")
            $a_contacts['address'].= $a_authority['Esponente'];
        if($a_authority['Interno']>0)
            $a_contacts['address'].="/".$a_authority['Interno'];
        if($a_authority['Dettagli']!="")
            $a_contacts['address'].=", ".$a_authority['Dettagli'];

        $a_contacts['address'].= " - ".$a_authority['Cap']." ".$a_authority['Comune'];
        if($a_authority['Provincia']!="")
            $a_contacts['address'].= " (".$a_authority['Provincia'].")";

        $a_contacts['complete'] = "[".$a_contacts['name']." con sede in ".$a_contacts['address'].". RECAPITI: ".$a_contacts['contacts']."]";
        return $a_contacts;
    }

}

