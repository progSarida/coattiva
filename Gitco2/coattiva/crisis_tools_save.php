<?php

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";


include(INC."/header.php");
include_once (CLS."/cls_file.php");
include_once (CLS."/cls_crisis_tools.php");

$msg = "";
$error = 0;

if(isset($_POST['delete'])){
    if($_POST['delete']==1 && isset($_POST['Crisis_ID'])){
        $query = "DELETE crisis_tools, crisis_tools_court_hearing, crisis_tools_lawyer_bill, crisis_tools_part, crisis_tools_proceedings_status FROM crisis_tools ";
        $query.= "LEFT JOIN crisis_tools_court_hearing ON crisis_tools_court_hearing.Crisis_ID = crisis_tools.ID ";
        $query.= "LEFT JOIN crisis_tools_lawyer_bill ON crisis_tools_lawyer_bill.Crisis_ID = crisis_tools.ID ";
        $query.= "LEFT JOIN crisis_tools_part ON crisis_tools_part.Crisis_ID = crisis_tools.ID ";
        $query.= "LEFT JOIN crisis_tools_proceedings_status ON crisis_tools_proceedings_status.Crisis_ID = crisis_tools.ID ";
        $query.= "WHERE crisis_tools.ID=".$_POST['Crisis_ID'];

        $cls_db->ExecuteQuery($query);
        $msg = "Eliminazione avvenuta con successo!";
    }
    else
    {
        $msg = "Errore, eliminazione non riuscita";
        $error = 1;
        //header("Location: ".WEB_ROOT."/coattiva/crisis_tools_list.php?c=".$_POST['c']."&a=".$_POST['a']."&partita=".$_POST['Partita_ID']."&msg={$msg}&error={$error}");
        //die;
    }

}
else{

    $msg = "Salvataggio avvenuto con successo!";
    $cls_crisis_tools = new cls_crisis_tools();
    $cls_file = new cls_file();

    $a_fields = array(
        "CC", "Partita_ID", "Court_Level", "Type", "Start_Date", "End_Date", "Authority_ID", "Judge", "Amendment_Date", "Notification_Date",
        "Registration_Date", "Dossier_Submission_Date", "RG", "Trespassers_Part", "Body_Part", "Body_Lawyer", "Body_Lawyer_Bar",
        "Trespassers_Lawyer",  "Trespassers_Lawyer_Bar", "Total", "Act_Amount", "Legal_Costs", "Actual_Costs", "Notes", "Body_Type"
    );

    $a_values = array(
        $_POST['c'], 
        $_POST['Partita_ID'], $_POST['Court_Level'], 
        $_POST['Crisis_Type'], $cls_help->toDbDate($_POST['Start_Date']), $cls_help->toDbDate($_POST['End_Date']), 
        $_POST['Authority_ID'], 

        $_POST['Judge'], $cls_help->toDbDate($_POST['Amendment_Date']), $cls_help->toDbDate($_POST['Notification_Date']), $cls_help->toDbDate($_POST['Registration_Date']),
        $cls_help->toDbDate($_POST['Dossier_Submission_Date']), $_POST['RG'], 
        
        $_POST['Trespassers_Part'], $_POST['Body_Part'],

        $_POST['Body_Lawyer'], $_POST['Body_Lawyer_Bar'], $_POST['Trespassers_Lawyer'], $_POST['Trespassers_Lawyer_Bar'],

        $cls_help->stringToFloat($_POST['Judge_Total']), $cls_help->stringToFloat($_POST['Judge_Act_Amount']),
        $cls_help->stringToFloat($_POST['Judge_Legal_Costs']), $cls_help->stringToFloat($_POST['Judge_Actual_Costs']),
        
        $_POST['Notes'], $_POST['Body_Type']
    );

    $bindTypes = "siisssissssssiissssddddss";

    $cls_db->Start_Transaction();
    $cls_db->Begin_Transaction();

    if($_POST['Crisis_ID']>0){
        $query = "UPDATE crisis_tools SET ";
        for($i=0;$i<count($a_fields);$i++){
            if($i>0)
                $query.= ", ";
            $query.= $a_fields[$i]."=?";
        }

        $query.= " WHERE ID=".$_POST['Crisis_ID'];
        $checkBind = $cls_db->bind_array($query,$bindTypes,$a_values);

        if($checkBind===false) {
            echo "ERROR ".mysqli_error($cls_db->conn);
            $cls_db->Rollback();
            die;
        }
    }else{
        $insertFields = "";
        for($i=0;$i<count($a_fields);$i++){
            if($i>0)
                $insertFields.= ", ";
            $insertFields.= $a_fields[$i];
        }
        $insertValues = "?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?";

        $query = "INSERT INTO crisis_tools (".$insertFields.") ";
        $query.= "VALUES (".$insertValues.")";

        $checkBind = $cls_db->bind_array($query,$bindTypes,$a_values);

        if($checkBind===false) {
            echo "ERROR ".mysqli_error($cls_db->conn);
            $cls_db->Rollback();
            die;
        }

        $_POST['Crisis_ID'] = mysqli_insert_id($cls_db->conn);
    }

    $query = "SELECT * FROM crisis_tools_part WHERE Crisis_ID=".$_POST['Crisis_ID'];
    $a_trespasser = $cls_db->getResults($cls_db->SelectQuery($query));

    if(isset($_POST['id_trespassers'])){
        $_POST['id_trespassers'] = array_values($_POST['id_trespassers']);
        for($i=0;$i<count($_POST['id_trespassers']);$i++){
            $check = 0;
            for($j=0;$j<count($a_trespasser);$j++){
                if($_POST['id_trespassers'][$i]==$a_trespasser[$j]['Part_ID']){
                    unset($a_trespasser[$j]);
                    $a_trespasser = array_values($a_trespasser);
                    $check = 1;
                    break;
                }
            }

            if($check==1)
                continue;

            $query = "INSERT INTO crisis_tools_part (Crisis_ID, Part_ID) VALUES (".$_POST['Crisis_ID'].",".$_POST['id_trespassers'][$i].")";
            $cls_db->ExecuteQuery($query);
        }

        for($j=0;$j<count($a_trespasser);$j++){
            $query = "DELETE FROM crisis_tools_part WHERE Part_ID=".$a_trespasser[$j]['Part_ID'];
            $cls_db->ExecuteQuery($query);
        }
    }
    else
    {
        $query = "DELETE FROM crisis_tools_part WHERE Crisis_ID=".$_POST['Crisis_ID'];
        $cls_db->ExecuteQuery($query);
    }

    if(isset($_POST['Court_Hearing_ID'])){
        $a_fields = array(
            "Crisis_ID", "Partita_ID","Date","Time","Type","Plaintiff_Proceedings_State","Plaintiff_Docs_Date",
            "Respondent_Proceedings_State","Respondent_Docs_Date"
        );

        $bindTypes = "iissiisis";

        for($z=1;$z<=count($_POST['Court_Hearing_ID']);$z++){
            $a_values = array(
                $_POST['Crisis_ID'],$_POST['Partita_ID'], $cls_help->toDbDate($_POST['Court_Hearing_Date'][$z]),$_POST['Court_Hearing_Time'][$z]==""?null:$_POST['Court_Hearing_Time'][$z],$_POST['Court_Hearing_Type'][$z],
                $_POST['Plaintiff_Proceedings_State'][$z], $cls_help->toDbDate($_POST['Plaintiff_Docs_Date'][$z]),
                $_POST['Respondent_Proceedings_State'][$z], $cls_help->toDbDate($_POST['Respondent_Docs_Date'][$z])
            );

            if($_POST['Court_Hearing_ID'][$z]>0){
                $query = "UPDATE crisis_tools_court_hearing SET ";
                for($i=0;$i<count($a_fields);$i++){
                    if($i>0)
                        $query.= ", ";
                    $query.= $a_fields[$i]."=?";
                }

                $query.= " WHERE ID=".$_POST['Court_Hearing_ID'][$z];

                $checkBind = $cls_db->bind_array($query,$bindTypes,$a_values);
                if($checkBind===false) {
                    echo "ERROR ".mysqli_error($cls_db->conn);
                    $cls_db->Rollback();
                    die;
                }

            }
            else{
                $insertFields = "";
                for($i=0;$i<count($a_fields);$i++){
                    if($i>0)
                        $insertFields.= ", ";
                    $insertFields.= $a_fields[$i];
                }

                $insertValues = "?,?,?,?,?,?,?,?,?";

                $query = "INSERT INTO crisis_tools_court_hearing (".$insertFields.") ";
                $query.= "VALUES (".$insertValues.")";

                $checkBind = $cls_db->bind_array($query,$bindTypes,$a_values);

                if($checkBind===false) {
                    echo "ERROR ".mysqli_error($cls_db->conn);
                    $cls_db->Rollback();
                    die;
                }

                $_POST['Court_Hearing_ID'][$z] = mysqli_insert_id($cls_db->conn);
            }

            $courtHearingPath = $cls_crisis_tools->getCourtHearingPath($_POST['c'],$_POST['Crisis_ID'],$_POST['Court_Hearing_ID'][$z]);

            $cls_file->folderCreation($courtHearingPath['plaintiff']);
            $cls_file->folderCreation($courtHearingPath['respondent']);

            if($_FILES['Plaintiff_Docs']['tmp_name'][$z] != ""){
                $file = $courtHearingPath['plaintiff']."/".$_FILES['Plaintiff_Docs']['name'][$z];
                move_uploaded_file($_FILES['Plaintiff_Docs']['tmp_name'][$z], $file);
            }

            if($_FILES['Respondent_Docs']['tmp_name'][$z] != ""){
                $file = $courtHearingPath['respondent']."/".$_FILES['Respondent_Docs']['name'][$z];
                move_uploaded_file($_FILES['Respondent_Docs']['tmp_name'][$z], $file);
            }
        }
    }

    $a_fields = array(
        "Crisis_ID", "Partita_ID","Type","Number","Date","File_Date","Outcome",
        "Outcome_Notification_Date","Notes","Sentence_Request_Date","Sentence_Challenger","Sentence_Challenge_Date"
    );
    $bindTypes = "iiisssisssss";
    for($z=1;$z<=count($_POST['Proceeding_ID']);$z++){
        $a_values = array(
            $_POST['Crisis_ID'],$_POST['Partita_ID'], $z, $_POST['Sentence_Number'][$z], $cls_help->toDbDate($_POST['Sentence_Date'][$z]),
            $cls_help->toDbDate($_POST['Sentence_File_Date'][$z]), $_POST['Outcome'][$z], $cls_help->toDbDate($_POST['Outcome_Notification_Date'][$z]),
            $_POST['Proceeding_Notes'][$z], $cls_help->toDbDate($_POST['Sentence_Request_Date'][$z]), $_POST['Sentence_Challenger'][$z],
            $cls_help->toDbDate($_POST['Sentence_Challenge_Date'][$z])
        );

        if($_POST['Proceeding_ID'][$z]>0){
            $query = "UPDATE crisis_tools_proceedings_status SET ";
            for($i=0;$i<count($a_fields);$i++){
                if($i>0)
                    $query.= ", ";
                $query.= $a_fields[$i]."=?";
            }

            $query.= " WHERE ID=".$_POST['Proceeding_ID'][$z];

            $checkBind = $cls_db->bind_array($query,$bindTypes,$a_values);
            if($checkBind===false) {
                echo "ERROR ".mysqli_error($cls_db->conn);
                $cls_db->Rollback();
                die;
            }

        }
        else{
            $insertFields = "";
            for($i=0;$i<count($a_fields);$i++){
                if($i>0)
                    $insertFields.= ", ";
                $insertFields.= $a_fields[$i];
            }

            $insertValues = "?,?,?,?,?,?,?,?,?,?,?,?";

            $query = "INSERT INTO crisis_tools_proceedings_status (".$insertFields.") ";
            $query.= "VALUES (".$insertValues.")";

            $checkBind = $cls_db->bind_array($query,$bindTypes,$a_values);

            if($checkBind===false) {
                echo "ERROR ".mysqli_error($cls_db->conn);
                $cls_db->Rollback();
                die;
            }
        }

        $a_proceedingsPath = $cls_crisis_tools->getProceedingStatusPath($_POST['c'],$_POST['Crisis_ID']);
        $cls_file->folderCreation($a_proceedingsPath[$z][0]);
        $cls_file->folderCreation($a_proceedingsPath[$z][1]);

        if(isset($_FILES['Sentence_Docs']['tmp_name'][$z])){
            if($_FILES['Sentence_Docs']['tmp_name'][$z] != ""){
                $file = $a_proceedingsPath[$z][0]."/".$_FILES['Sentence_Docs']['name'][$z];
                move_uploaded_file($_FILES['Sentence_Docs']['tmp_name'][$z], $file);
            }
        }

        if(isset($_FILES['Challenge_Docs']['tmp_name'][$z])){
            if($_FILES['Challenge_Docs']['tmp_name'][$z] != ""){
                $file = $a_proceedingsPath[$z][1]."/".$_FILES['Challenge_Docs']['name'][$z];
                move_uploaded_file($_FILES['Challenge_Docs']['tmp_name'][$z], $file);
            }
        }

    }

    $a_fields = array(
        "Crisis_ID", "Partita_ID","Type","Bill_Number","Bill_Date","Lawyer","Fee",
        "Rights","Overheads", "Lawyer_Fund","VAT","CU","Stamp_Duty","Other_Costs","Withholding_Tax","Bill_Total",
        "Payer", "Payment_Date","VAT_Exemption","Withholding_Tax_Exemption","Notes","Part"
    );
    $bindTypes = "iiisssddddddddddssiisi";

    for($z=1;$z<=count($_POST['Lawyer_Bill_ID']);$z++){
        $a_values = array(
            $_POST['Crisis_ID'],$_POST['Partita_ID'], $z, $_POST['Bill_Number'][$z], $cls_help->toDbDate($_POST['Bill_Date'][$z]), $_POST['Lawyer'][$z],
            $cls_help->stringToFloat($_POST['Fee'][$z]), $cls_help->stringToFloat($_POST['Rights'][$z]), $cls_help->stringToFloat($_POST['Overheads'][$z]),
            $cls_help->stringToFloat($_POST['Lawyer_Fund'][$z]), $cls_help->stringToFloat($_POST['VAT'][$z]), $cls_help->stringToFloat($_POST['CU'][$z]),
            $cls_help->stringToFloat($_POST['Stamp_Duty'][$z]), $cls_help->stringToFloat($_POST['Other_Costs'][$z]), $cls_help->stringToFloat($_POST['Withholding_Tax'][$z]),
            $cls_help->stringToFloat($_POST['Bill_Total'][$z]),
            $_POST['Payer'][$z], $cls_help->toDbDate($_POST['Payment_Date'][$z]), $_POST['VAT_Exemption'][$z], $_POST['Withholding_Tax_Exemption'][$z],
            $_POST['Lawyer_Notes'][$z], $_POST['Part'][$z]
        );

        if($_POST['Lawyer_Bill_ID'][$z]>0){
            $query = "UPDATE crisis_tools_lawyer_bill SET ";
            for($i=0;$i<count($a_fields);$i++){
                if($i>0)
                    $query.= ", ";
                $query.= $a_fields[$i]."=?";
            }

            $query.= " WHERE ID=".$_POST['Lawyer_Bill_ID'][$z];

            $checkBind = $cls_db->bind_array($query,$bindTypes,$a_values);
            if($checkBind===false) {
                echo "ERROR ".mysqli_error($cls_db->conn);
                $cls_db->Rollback();
                die;
            }

        }
        else{
            $insertFields = "";
            for($i=0;$i<count($a_fields);$i++){
                if($i>0)
                    $insertFields.= ", ";
                $insertFields.= $a_fields[$i];
            }

            $insertValues = "?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?";

            $query = "INSERT INTO crisis_tools_lawyer_bill (".$insertFields.") ";
            $query.= "VALUES (".$insertValues.")";

            $checkBind = $cls_db->bind_array($query,$bindTypes,$a_values);

            if($checkBind===false) {
                echo "ERROR ".mysqli_error($cls_db->conn);
                $cls_db->Rollback();
                die;
            }
        }
    }

    $cls_db->End_Transaction();

}

//header("Location: ".WEB_ROOT."/coattiva/crisis_tools_list.php?c=".$_POST['c']."&a=".$_POST['a']."&partita=".$_POST['Partita_ID']."&msg={$msg}&error={$error}");

include(INC."/footer.php");

?>

<script>

    location.href = "<?=WEB_ROOT."/coattiva/crisis_tools_list.php?c=".$_POST['c']."&a=".$_POST['a']."&partita=".$_POST['Partita_ID']."&msg={$msg}&error={$error}";?>";
</script>
