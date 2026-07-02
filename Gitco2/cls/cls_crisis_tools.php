<?php

class cls_crisis_tools{

    public function getAllCrisis_query($partita_ID){

        $query = "SELECT crisis_tools.*, ufficio_giudiziario.Tipo as Authority_Type, ufficio_giudiziario.Comune as Authority_Place, ";
        $query.= "ufficio_giudiziario.Sezione as Authority_Section, crisis_type.Description as Crisis_Type FROM crisis_tools ";
        $query.= "JOIN crisis_tools_type as crisis_type ON crisis_type.ID = crisis_tools.Type ";
        $query.= "LEFT JOIN ufficio_giudiziario ON ufficio_giudiziario.ID=crisis_tools.Authority_ID ";
        $query.= "WHERE crisis_tools.Partita_ID=".$partita_ID." GROUP BY crisis_tools.ID";
        return $query;
    }

    public function getCrisis_query($crisis_tools_id){

        $query = "SELECT crisis_tools.*, ufficio_giudiziario.Tipo as Authority_Type, ufficio_giudiziario.Comune as Authority_Place, ";
        $query.= "ufficio_giudiziario.Sezione as Authority_Section, ";
        $query.= "merito.Number as Sentence_Number, merito.Date as Sentence_Date, merito.Sentence_Request_Date, ";
        $query.= "merito.Sentence_Challenger, merito.Sentence_Challenge_Date FROM crisis_tools ";
        $query.= "LEFT JOIN ufficio_giudiziario ON ufficio_giudiziario.ID=crisis_tools.Authority_ID ";
        $query.= "LEFT JOIN crisis_tools AS prevCrisis ON prevCrisis.Partita_ID=crisis_tools.Partita_ID AND prevCrisis.Court_Level = (crisis_tools.Court_Level-1) ";
        $query.= "LEFT JOIN crisis_tools_proceedings_status AS merito ON merito.Crisis_ID=prevCrisis.ID AND merito.Type=2 ";
        $query.= "WHERE crisis_tools.ID=".$crisis_tools_id;
        return $query;
    }

    public function getCrisisPart_query($crisis_tools_id){
        $query = "SELECT crisis_tools_part.*, CONCAT(\"(\", utente.Comune_ID, \") \", utente.Cognome, utente.Ditta,\" \", utente.Nome) AS Part_Name FROM crisis_tools_part ";
        $query.= "JOIN utente ON utente.ID=crisis_tools_part.Part_ID WHERE Crisis_ID=".$crisis_tools_id;
        return $query;
    }

    public function getCourtHearing_query($crisis_tools_id){
        $query = "SELECT * FROM crisis_tools_court_hearing WHERE Crisis_ID=".$crisis_tools_id." ORDER BY Date ASC, Time ASC";
        return $query;
    }

    public function getProceedings_query($crisis_tools_id){
        $query = "SELECT * FROM crisis_tools_proceedings_status WHERE Crisis_ID=".$crisis_tools_id;
        return $query;
    }

    public function getLawyerBill_query($crisis_tools_id){
        $query = "SELECT * FROM crisis_tools_lawyer_bill WHERE Crisis_ID=".$crisis_tools_id;
        return $query;
    }

    public function getCourtHearingType_query(){
        $query = "SELECT * FROM court_hearing_type";
        return $query;
    }

    public function getCrisisPartType_query(){
        $query = "SELECT * FROM crisis_tools_part_type";
        return $query;
    }

    public function getCrisisBodyType_query(){
        $query = "SELECT * FROM crisis_tools_body_type";
        return $query;
    }

    public function getCrisisProceedingsType_query(){
        $query = "SELECT * FROM crisis_tools_proceedings_type";
        return $query;
    }

    public function getCrisisType_query(){
        $query = "SELECT * FROM crisis_tools_type";
        return $query;
    }

    public function getCourtHearingDoctype_query(){
        $query = "SELECT * FROM court_hearing_doctype";
        return $query;
    }

    public function getCourtHearingPath($c, $crisis_tools_id, $court_hearing_id){
        return array(
            "plaintiff" => ATTI."/".$c."/crisis_tools/".$crisis_tools_id."/court_hearing/".$court_hearing_id."/plaintiff",
            "respondent" => ATTI."/".$c."/crisis_tools/".$crisis_tools_id."/court_hearing/".$court_hearing_id."/respondent"
        );
    }

    public function getProceedingStatusPath($c, $crisis_tools_id){
        return array(
            1 => array (
                0 => ATTI."/".$c."/crisis_tools/".$crisis_tools_id."/proceedings_status/suspension/sentence",
                1 => ATTI."/".$c."/crisis_tools/".$crisis_tools_id."/proceedings_status/suspension/challenge"
            ),
            2 => array (
                0 => ATTI."/".$c."/crisis_tools/".$crisis_tools_id."/proceedings_status/main_action/sentence",
                1 => ATTI."/".$c."/crisis_tools/".$crisis_tools_id."/proceedings_status/main_action/challenge"
            )
        );
    }

    public function getFilesRow(array $a_file, $page){

        $html = "";
        for($i=0;$i<count($a_file);$i++){
            $html.= "&nbsp;&nbsp;<a onMouseover=\"title='".$a_file[$i]['fileName']."'\" href=\"#\" ";
            $html.= "onclick=\"window.open('".SUPER_WEB_ROOT.$a_file[$i]['fileWeb']."')\" style=\"text-decoration: none;\">";
            $html.= "<img src=\"".$a_file[$i]['icon']."\" width=25 height=25 border=0></a>";
            $html.= "&nbsp;<a onMouseover=\"title='Elimina il file ".$a_file[$i]['fileName']."'\" href=\"#\" ";
            $html.= "onclick=\"File.ajaxRemove('".$page."','".WEB_ROOT."/coattiva/ajax/fileRemove.php','".$a_file[$i]['file']."')\" style=\"text-decoration: none;\">";
            $html.= "<img src=\"".IMG."/elimina_icon.png"."\" width=10 height=10 border=0></a>";
        }

        return $html;

    }

}


?>