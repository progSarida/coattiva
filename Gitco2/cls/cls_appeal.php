<?php

class cls_appeal{

    public function getAllAppeal_query($partita_ID){

        $query = "SELECT appeal.*, ufficio_giudiziario.Tipo as Authority_Type, ufficio_giudiziario.Comune as Authority_Place, ";
        $query.= "atto.Atto, atto.ID_Cronologico, atto.Anno_Cronologico, ";
        $query.= "ufficio_giudiziario.Sezione as Authority_Section FROM appeal ";
        $query.= "JOIN atto ON atto.ID=appeal.Act_ID ";
        $query.= "LEFT JOIN ufficio_giudiziario ON ufficio_giudiziario.ID=appeal.Authority_ID ";
        $query.= "WHERE appeal.Partita_ID=".$partita_ID." GROUP BY appeal.ID";
        return $query;
    }

    public function getAppeal_query($appeal_id){

        $query = "SELECT appeal.*, ufficio_giudiziario.Tipo as Authority_Type, ufficio_giudiziario.Comune as Authority_Place, ";
        $query.= "ufficio_giudiziario.Sezione as Authority_Section, ";
        $query.= "merito.Number as Sentence_Number, merito.Date as Sentence_Date, merito.Sentence_Request_Date, ";
        $query.= "merito.Sentence_Challenger, merito.Sentence_Challenge_Date FROM appeal ";
        $query.= "LEFT JOIN ufficio_giudiziario ON ufficio_giudiziario.ID=appeal.Authority_ID ";
        $query.= "LEFT JOIN appeal AS prevAppeal ON prevAppeal.Act_ID=appeal.Act_ID AND prevAppeal.Court_Level = (appeal.Court_Level-1) ";
        $query.= "LEFT JOIN appeal_proceedings_status AS merito ON merito.Appeal_ID=prevAppeal.ID AND merito.Type=2 ";
        $query.= "WHERE appeal.ID=".$appeal_id;
        return $query;
    }

    public function getAppealPart_query($appeal_id){
        $query = "SELECT appeal_part.*, CONCAT(\"(\", utente.Comune_ID, \") \", utente.Cognome, utente.Ditta,\" \", utente.Nome) AS Part_Name FROM appeal_part ";
        $query.= "JOIN utente ON utente.ID=appeal_part.Part_ID WHERE Appeal_ID=".$appeal_id;
        return $query;
    }

    public function getCourtHearing_query($appeal_id){
        $query = "SELECT * FROM appeal_court_hearing WHERE Appeal_ID=".$appeal_id." ORDER BY Date ASC, Time ASC";
        return $query;
    }

    public function getProceedings_query($appeal_id){
        $query = "SELECT * FROM appeal_proceedings_status WHERE Appeal_ID=".$appeal_id;
        return $query;
    }

    public function getLawyerBill_query($appeal_id){
        $query = "SELECT * FROM appeal_lawyer_bill WHERE Appeal_ID=".$appeal_id;
        return $query;
    }

    public function getCourtHearingType_query(){
        $query = "SELECT * FROM court_hearing_type";
        return $query;
    }

    public function getAppealPartType_query(){
        $query = "SELECT * FROM appeal_part_type";
        return $query;
    }

    public function getAppealBodyType_query(){
        $query = "SELECT * FROM appeal_body_type";
        return $query;
    }

    public function getAppealProceedingsType_query(){
        $query = "SELECT * FROM appeal_proceedings_type";
        return $query;
    }

    public function getAppealType_query(){
        $query = "SELECT * FROM appeal_type";
        return $query;
    }

    public function getCourtHearingDoctype_query(){
        $query = "SELECT * FROM court_hearing_doctype";
        return $query;
    }

    public function getCourtHearingPath($c, $appeal_id, $court_hearing_id){
        return array(
            "plaintiff" => ATTI."/".$c."/appeal/".$appeal_id."/court_hearing/".$court_hearing_id."/plaintiff",
            "respondent" => ATTI."/".$c."/appeal/".$appeal_id."/court_hearing/".$court_hearing_id."/respondent"
        );
    }

    public function getProceedingStatusPath($c, $appeal_id){
        return array(
            1 => array (
                0 => ATTI."/".$c."/appeal/".$appeal_id."/proceedings_status/suspension/sentence",
                1 => ATTI."/".$c."/appeal/".$appeal_id."/proceedings_status/suspension/challenge"
            ),
            2 => array (
                0 => ATTI."/".$c."/appeal/".$appeal_id."/proceedings_status/main_action/sentence",
                1 => ATTI."/".$c."/appeal/".$appeal_id."/proceedings_status/main_action/challenge"
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