<?php

class cls_mercurioService extends cls_help{

    function checkNotificationRow(array &$row, array $a_mercurioParams, array $a_path){

        $row["imgProblem"] = 0;
        $row['noImgFlag'] = false;
        $row['newFrontImg'] = "";
        $row['newBackImg'] = "";
        $row['problemText'] = "";
        $row['a_notificationType'] = null;
        $row['a_notificationStatus'] = null;

        switch ($row['documentType']){
            case "AVVISOINTIMAZIONE":
                $row['documentTypeAcronym'] = "A_IN";
                $row['typeStyle'] = "sfondo_giallo";
                $row['rowStyle'] = "sfondo_azzurro";
                break;

            case "INGIUNZIONE":
                $row['documentTypeAcronym'] = "ING";
                $row['typeStyle'] = "sfondo_verde";
                $row['rowStyle'] = "sfondo_azzurro";
                break;

            case "PIGNOBANCA":
                $row['documentTypeAcronym'] = "P_BA";
                $row['typeStyle'] = "sfondo_gitcolor";
                $row['rowStyle'] = "sfondo_azzurro";
                break;

            case "PIGNOLAVORO":
                $row['documentTypeAcronym'] = "P_LA";
                $row['typeStyle'] = "sfondo_gitcolor";
                $row['rowStyle'] = "sfondo_azzurro";
                break;

            case "PIGNOVEICOLO":
                $row['documentTypeAcronym'] = "P_VE";
                $row['typeStyle'] = "sfondo_gitcolor";
                $row['rowStyle'] = "sfondo_azzurro";
                break;

            case "AV_MORA":
                $row['documentTypeAcronym'] = "AV_MORA";
                $row['typeStyle'] = "sfondo_gitcolor";
                $row['rowStyle'] = "sfondo_azzurro";
                break;

            case "VERBALI":
                $row['documentTypeAcronym'] = "V_GI";
                $row['documentType'] = "VERBALI GITCO VECCHIO";
                $row['typeStyle'] = "sfondo_grigio";
                $row['rowStyle'] = "sfondo_grigio";

                break;

            default:
                $row['documentTypeAcronym'] = "???";
                $row['typeStyle'] = "sfondo_red";
                $row['rowStyle'] = "sfondo_red";
//                                        $this->alert($row['documentType']." SCONOSCIUTO!");
                break;
        }


        switch (substr($row['notificationStatusId'],0,2))  //  per questi e' AMMESSA la MANCANZA di IMMAGINI
        {
            case "03": // indirizzo inesatto
            case "04": // indirizzo insufficiente
            case "05": // indirizzo inesistente
            case "07": // irreperibile
            case "08": // sconosciuto
            case "09": // deceduto
            case "10": // trasferito
            $row['noImgFlag'] = true;
                break;
        }

        if ($row['noImgFlag'] === false && $this->toDbDate($row['notificationDate'])=="")
            $row['imgProblem'] = 4;
        else if($row['cc']=="")
            $row['imgProblem'] = 1;
        else if($row['crono'] == "")
            $row['imgProblem'] = 2;

        $row['Modalita_Notifica'] = 0;
        $row['Stato_Notifica'] = 0;
        $row['Motivo_Notifica'] = 0;
        if($row['notificationTypeId']!=""){
            $row['a_notificationType'] = $this->getNotificationParams($row['notificationTypeId'],$a_mercurioParams);
            if($row['a_notificationType']==null)
                $row['imgProblem'] = 6;
        }
        if($row['notificationStatusId']!=""){
            $row['a_notificationStatus'] = $this->getNotificationParams($row['notificationStatusId'],$a_mercurioParams);
            if($row['a_notificationStatus']==null)
                $row['imgProblem'] = 7;
        }
        if($row['notificationTypeId']!="" || $row['notificationStatusId']!=""){
            $this->getNotificationsID($row);
            if(!$row['Modalita_Notifica']>0 && !$row['Stato_Notifica']>0 && !$row['Motivo_Notifica']>0)
                $row['imgProblem'] = 11;
        }

        if ($row['importCheck'] != "")
            $row['noImgFlag'] = true;

        if (file_exists($a_path['imgDir'] . "/" . $row['frontImg'])){

            $esplodoXX = explode (".", $row['frontImg']);
            $radiceImg = $esplodoXX[0];
            $row['newFrontImg'] = $a_path['toImgPath'] . $radiceImg . ".jpg";

            $row['frontImgIcon'] = IMMAGINIWEB."/spunta.jpg";
        }
        else{
            $row['frontImgIcon'] = IMMAGINIWEB."/spuntaNO.jpg";
            if ($row['noImgFlag'] === false && $row['imgProblem']==0){
                if($row['importCheck']=="")
                    $a_return['imgProblem'] = 3;
            }

        }

        if (file_exists($a_path['imgDir'] . "/" . $row['backImg'])){

            $esplodoXX = explode (".", $row['backImg']);
            $radiceImg = $esplodoXX[0];
            $row['newBackImg'] = $a_path['toImgPath'] . $radiceImg . ".jpg";

            $row['backImgIcon'] = IMMAGINIWEB."/spunta.jpg";
        }
        else{
            $row['backImgIcon'] = IMMAGINIWEB."/spuntaNO.jpg";
            if ($row['noImgFlag'] === false && $row['imgProblem']==0){
                if($row['importCheck']=="")
                    $row['imgProblem'] = 3;
            }

        }

        if(!$row['attoId']>0)
            $row['imgProblem'] = 10;

        $row['esito'] = $this->getImgProblem($row['imgProblem']);

    }

    function getImgProblem($problemId){
        $esito['img'] = IMMAGINIWEB."/spuntaNO.jpg";
        switch ($problemId){
            case 0:     $esito['text'] = "OK";
                        $esito['img'] = IMMAGINIWEB."/spunta.jpg";                      break;

            case 1:     $esito['text'] = "Problema: Comune Riferimento assente";            break;
            case 2:     $esito['text'] = "Problema: Numero Verbale assente";                break;
            case 3:     $esito['text'] = "Problema: Immagini assenti";                      break;
            case 4:     $esito['text'] = "Problema: Data Notifica assente";                 break;
            case 5:     $esito['text'] = "Problema: Verbale Targa Estera non trovato";      break;
            case 6:     $esito['text'] = "Problema: Tipo Esito Notifica sconosciuto";       break;
            case 7:     $esito['text'] = "Problema: Stato Esito Notifica sconosciuto";      break;
            case 8:     $esito['text'] = "Problema: Immagine Fronte gia' importata";        break;
            case 9:     $esito['text'] = "Problema: Immagine Retro gia' importata";         break;
            case 10:    $esito['text'] = "Problema: Atto non trovato!";                     break;
            case 11:    $esito['text'] = "Problema: Stato di notifica non riconosciuto!";   break;
            default:    $esito['text'] = "Problema: sconosciuto";                           break;
        }

        return $esito;
    }

    function getNotificationParams($articolo, array $a_mercurioParams){
        for($i=0;$i<count($a_mercurioParams);$i++){
            if(strpos($articolo, $a_mercurioParams[$i]['Articolo'])!==false && strpos($articolo, $a_mercurioParams[$i]['Descrizione'])!==false){
                return $a_mercurioParams[$i];
            }
        }
        return null;
    }


    public function getNotificationsID (array &$row)
    {
        if(isset($row['a_notificationType']['Tipo_Dato'])){
            switch($row['a_notificationType']['Tipo_Dato'])
            {
                case "modalita":    $row['Modalita_Notifica'] = $row['a_notificationType']['ID'];     break;
                case "stato":       $row['Stato_Notifica'] = $row['a_notificationType']['ID'];        break;
                case "motivo":      $row['Motivo_Notifica'] = $row['a_notificationType']['ID'];       break;
            }
        }
        if(isset($row['a_notificationStatus']['Tipo_Dato'])) {
            switch ($row['a_notificationStatus']['Tipo_Dato']) {
                case "modalita":
                    $row['Modalita_Notifica'] = $row['a_notificationStatus']['ID'];
                    break;
                case "stato":
                    $row['Stato_Notifica'] = $row['a_notificationStatus']['ID'];
                    break;
                case "motivo":
                    $row['Motivo_Notifica'] = $row['a_notificationStatus']['ID'];
                    break;
            }
        }
    }
}


?>