<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

if ($_SESSION['username']==NULL)
{
    header("Location:".WEB_ROOT."/autenticazione/accesso_negato.php");
    die;
}

include(INC."/header.php");
include(INC."/menu.php");

include_once (CLS."/cls_ruolo.php");
include_once (CLS."/cls_excel.php");
include_once (CLS."/cls_place.php");
include_once (CLS."/cls_registry.php");
include_once (CLS."/cls_file.php");

$c = $cls_help->getVar("c");

$pathFolder = SUPER_ROOT."/archivio/Importazioni_Excel/".$c."/";
$cls_file = new cls_file();
$cls_file->folderCreation($pathFolder);
$nomeFile = $pathFolder.$c."_ScartiImportazione_".date('Y-m-d_H-i-s').".xls";

$vedi_file = SUPER_WEB_ROOT.$cls_file->getWebPath($nomeFile);
?>
 
<script>
    function inizio()
    {
        $('#progressbar').progressbar({
            value: false
        });
        $( "#barlabel" ).text("Inizio importazione...");
    }

    function update(valore)
    {
        $( "#progressbar" ).progressbar({value: parseInt(valore) });
        $( "#barlabel" ).text( valore + "%" );
    }

    function nessun_risultato()
    {
        $( "#progressbar" ).progressbar({value: 100 });
        $( "#barlabel" ).text("Nessun risultato trovato");
    }

    function fine()
    {
        $( "#progressbar" ).progressbar({value: 100 });
        $( "#barlabel" ).text( "100%" );

        sleep(1000);
    }

    function mostra_file()
    {
        window.name = "Stampa";
        window.open('<?php echo $vedi_file; ?>',"Stampa");
    }

</script>

    <div class="row justify-content-md-center " style="margin-top: 3%;margin-bottom: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font18 text_center">Importazione generale</span>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-offset-1 col-lg-10">
            <div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>
        </div>
    </div>


<?php


flush();ob_flush();
flush();ob_flush();
echo "<script>inizio();</script>";
flush();ob_flush();
flush();ob_flush();

if($_FILES['file_excel']['tmp_name'] != ""){
    $fileExcel = SUPER_ROOT."/290/".$_FILES['file_excel']['name'];
    move_uploaded_file($_FILES['file_excel']['tmp_name'], $fileExcel);
}
else {
    $cls_help->alert("Errore in fase di upload del file!");
    die;
}

$cls_excel = new cls_excel();
$cls_excel->getArrayFromFile($fileExcel);

unlink($fileExcel);

$cls_place = new cls_place();
$cls_registry = new cls_registry();
$cls_ruolo = new cls_ruolo();

$ruoloQuery = $cls_ruolo->getLastRuolo_query($c);
$a_ruolo = $cls_db->getArrayLine($cls_db->SelectQuery($ruoloQuery));

$partitaQuery = $cls_ruolo->getLastPartita_query($c);
$a_partita = $cls_db->getArrayLine($cls_db->SelectQuery($partitaQuery));
$comune_id_partita = $a_partita['Comune_ID']+1;

$utenteQuery = $cls_registry->getLastRecord_query($c);
$a_utente = $cls_db->getArrayLine($cls_db->SelectQuery($utenteQuery));
$comune_id_utente = $a_utente['Comune_ID']+1;

$a_scarti = array();
$checkInsertRuolo = 0;
//$cls_help->alert(count($cls_excel->a_sheet[0]));
//print_r($cls_excel->a_sheet[0]);
for($k=0;$k<count($cls_excel->a_sheet[0]);$k++)
{

    set_time_limit(40);

    flush();ob_flush();
    flush();ob_flush();
    echo "<script>update(".ceil(($k * 100) / count($cls_excel->a_sheet[0])).");</script>";
    flush();ob_flush();
    flush();ob_flush();

    $a_toSave = array();

    $row = $cls_excel->a_sheet[0][$k];
    $cls_db->Begin_Transaction();

    //if($k == 0) var_dump($row);
    $row['ERRORE_SCARTO'] = "";
    //FINE FILE
    if($row['CODICE_CATASTALE_COMUNE']=="" && $row['COGNOME_DITTA']==""){
        continue;
    }
    else if($row['CODICE_CATASTALE_COMUNE']!=$c || $row['CODICE_CATASTALE_COMUNE']=="") {
        $cls_help->alert('Importazione arrestata! Codice catastale mancante o diverso da quello di gestione!');
        die;
    }


    $a_toSave['CC_NASCITA'] = "";
    $a_toSave['SESSO'] = "";
    $a_toSave['CODICE_FISCALE'] = "";
    $typeCF = 0;
    if($row['COGNOME_DITTA']!="" && $row['NOME']!="" && $row["TIPO_SOGGETTO"] != "DITTA"){

        $typeCF = 1;
        if($row['CODICE_FISCALE']=="" && $row['DATA_NASCITA']!=""){

            if(strtoupper($row['PAESE_NASCITA'])=="ITALIA"){
                $cityQuery = $cls_place->getCity_query($row['COMUNE_NASCITA'],$row['CODICE_CATASTALE_NASCITA']);
                $a_city = $cls_db->getArrayLine($cls_db->SelectQuery($cityQuery));
                $a_toSave['COMUNE_NASCITA'] = $a_city['Com_Nome'];
                $a_toSave['CC_NASCITA'] = $a_city['Com_Codice_Catastale'];
                $a_toSave['PAESE_NASCITA'] = ucfirst(strtolower($row['PAESE_NASCITA']));
            }
            else{
                $countryQuery = $cls_place->getCountry_query($row['PAESE_NASCITA'],$row['CODICE_CATASTALE_NASCITA']);
                $a_country = $cls_db->getArrayLine($cls_db->SelectQuery($countryQuery));
                $a_toSave['CC_NASCITA'] = $a_country['CC_Paese_Estero'];
                $a_toSave['COMUNE_NASCITA'] = ucfirst(strtolower($row['COMUNE_NASCITA']));
                $a_toSave['PAESE_NASCITA'] = ucfirst(strtolower($row['PAESE_NASCITA']));
            }

            if($a_toSave['CC_NASCITA']!=""){

                $a_toSave['NOME'] = strtoupper($row['NOME']);
                $a_toSave['COGNOME'] = strtoupper($row['COGNOME_DITTA']);
                $a_toSave['DATA_NASCITA'] = $cls_help->toDbDate($row['DATA_NASCITA']);
                $a_toSave['SESSO'] = strtoupper(substr($row['TIPO_SOGGETTO'],0,1));

                $a_toSave['CODICE_FISCALE'] = $cls_registry->compute_CF($a_toSave['NOME'],$a_toSave['COGNOME'],$a_toSave['SESSO'],$a_toSave['DATA_NASCITA'],$a_toSave['CC_NASCITA']);
                $cf_pi = $a_toSave['CODICE_FISCALE'];

            }
            else{
                $row['ERRORE_SCARTO'] = "ANAGRAFICA ERRATA";
                if($_SESSION['username']=="mirkop"){
                    echo "<br><br>";
                    print_r($row);
                    echo "<br><br>";
                }
                $a_scarti[] = $row;
                continue;
            }
        }
        else if($row['CODICE_FISCALE']!=""){
            $a_toSave = $cls_registry->decode_CF($row['CODICE_FISCALE']);
            if($a_toSave==null){
                $row['ERRORE_SCARTO'] = "CODICE FISCALE ERRATO";
                $a_scarti[] = $row;
                continue;
            }

            $a_toSave['NOME'] = strtoupper($row['NOME']);
            $a_toSave['COGNOME'] = strtoupper($row['COGNOME_DITTA']);
            $a_toSave['CODICE_FISCALE'] = $row['CODICE_FISCALE'];
            $cf_pi = $a_toSave['CODICE_FISCALE'];

            $a_toSave['SOCIETA'] = "";
            $a_toSave['PARTITA_IVA'] = "";

            $a_toSave['DEFUNTO'] = $row['DEFUNTO'];
        }

        if($a_toSave['CC_NASCITA']=="" &&  $row["TIPO_SOGGETTO"] != "DITTA"){
            $row['ERRORE_SCARTO'] = "CODICE CATASTALE DI NASCITA NON TROVATO";
            if($_SESSION['username']=="mirkop"){
                echo "<br><br>";
                print_r($row);
                echo "<br><br>";
            }
            $a_scarti[] = $row;
            continue;
        }

    }
    else if($row['COGNOME_DITTA']!="" && $row['PARTITA_IVA']!="" &&  $row["TIPO_SOGGETTO"] == "DITTA"){

        $typeCF = 2;
        $a_toSave['NOME'] = "";
        $a_toSave['COGNOME'] = "";
        $a_toSave['CODICE_FISCALE'] = "";

        $a_toSave['SOCIETA'] = strtoupper($row['COGNOME_DITTA']);
        $a_toSave['PARTITA_IVA'] = $row['PARTITA_IVA'];
        $cf_pi = $a_toSave['PARTITA_IVA'];

        $a_toSave['DEFUNTO'] = "";

        $a_toSave['CC_NASCITA'] = "";
        $a_toSave['DATA_NASCITA'] = "";
        $a_toSave['SESSO'] = "D";

        if($row["NOME"]!=""){
            $row['ERRORE_SCARTO'] = "ANAGRAFICA ERRATA";
            if($_SESSION['username']=="mirkop"){
                echo "<br><br>";
                print_r($row);
                echo "<br><br>";
            }
            $a_scarti[] = $row;
            continue;
        }

    }
    else{
        $row['ERRORE_SCARTO'] = "ANAGRAFICA ERRATA";
        if($_SESSION['username']=="mirkop"){
            echo "<br><br>";
            print_r($row);
            echo "<br><br>";
        }
        $a_scarti[] = $row;
        continue;
    }

    $a_toSave['CC_IMPORTAZIONE'] = $row['CODICE_CATASTALE_COMUNE'];

    $controlCF_PI = $cls_registry->check_CFPI($cf_pi,$typeCF);

    if($controlCF_PI!==true){
        //echo $controlCF_PI;
        $row['ERRORE_SCARTO'] = strtoupper($controlCF_PI);
        if($_SESSION['username']=="mirkop"){
            echo "<br><br>";
            print_r($row);
            echo "<br><br>";
        }
        $a_scarti[] = $row;
        continue;
    }

    $a_toSave['UTENTE_ID'] = null;
    if( $a_toSave['SESSO']=="D"){
        $companyQuery = $cls_registry->getCompany_query($a_toSave['CC_IMPORTAZIONE'],$a_toSave['PARTITA_IVA'],$a_toSave['SOCIETA']);
        $a_company = $cls_db->getArrayLine($cls_db->SelectQuery($companyQuery));
        if($a_company['ID']>0)
            $a_toSave['UTENTE_ID'] = $a_company['ID'];
    }
    else{
        $personQuery = $cls_registry->getPerson_query($a_toSave['CC_IMPORTAZIONE'],$a_toSave['CODICE_FISCALE']);
        $a_person = $cls_db->getArrayLine($cls_db->SelectQuery($personQuery));
        if($a_person['ID']>0)
            $a_toSave['UTENTE_ID'] = $a_person['ID'];
    }

    $a_toSave['PRESSO'] = trim($row['PRESSO']);

    //INDIRIZZO DESTINATARIO
    $a_toSave['PAESE_DESTINATARIO'] = ucwords(strtolower($row['PAESE_DESTINATARIO']));
    $a_toSave['COMUNE_DESTINATARIO'] = ucwords(strtolower($row['COMUNE_DESTINATARIO']));
    $a_toSave['PROVINCIA_DESTINATARIO'] = $row['PROVINCIA_DESTINATARIO'];
    $a_toSave['VIA_DESTINATARIO'] = strtoupper($row['VIA_DESTINATARIO']);
    $a_toSave['CIVICO_DESTINATARIO'] = $row['CIVICO_DESTINATARIO'];
    $a_toSave['ESPONENTE_DESTINATARIO'] = strtoupper($row['ESPONENTE_DESTINATARIO']);
    $a_toSave['INTERNO_DESTINATARIO'] = $row['INTERNO_DESTINATARIO'];
    $a_toSave['DETTAGLI_DESTINATARIO'] = strtoupper($row['DETTAGLI_DESTINATARIO']);
    $a_toSave['CC_DESTINATARIO'] = $row['CODICE_CATASTALE_DESTINATARIO'];
    $a_toSave['CAP_DESTINATARIO'] = $row['CAP_DESTINATARIO'];

    if($a_toSave['PAESE_DESTINATARIO']=="Italia" || $a_toSave['PAESE_DESTINATARIO']=="")
    {
        $cityQuery = $cls_place->getCity_query($a_toSave['COMUNE_DESTINATARIO'],$a_toSave['CC_DESTINATARIO']);
        $a_city = $cls_db->getArrayLine($cls_db->SelectQuery($cityQuery));

        if($a_city['Com_Codice_Catastale']==""){
            $cityQuery = $cls_place->getCityFromZipcode_query($a_toSave['CAP_DESTINATARIO']);
            $a_city = $cls_db->getArrayLine($cls_db->SelectQuery($cityQuery));
        }

        $a_toSave['COMUNE_DESTINATARIO'] = ucwords($a_city['Com_Nome']);
        $a_toSave['CC_DESTINATARIO'] = $a_city['Com_Codice_Catastale'];
        $a_toSave['PROVINCIA_DESTINATARIO'] = $a_city['Pro_Sigla'];
        $a_toSave['CAP_DESTINATARIO'] = str_replace("x",0, $a_city['Com_Cap']);

    }
    else{
        $countryQuery = $cls_place->getCountry_query($a_toSave['PAESE_DESTINATARIO'],$a_toSave['CC_DESTINATARIO']);
        $a_country = $cls_db->getArrayLine($cls_db->SelectQuery($countryQuery));

        $a_toSave['CC_DESTINATARIO'] = $a_country['CC_Paese_Estero'];
        $a_toSave['PAESE_DESTINATARIO'] = $a_country['Nome'];
        $a_toSave['PROVINCIA_DESTINATARIO'] = "";
        $a_toSave['VIA_DESTINATARIO'].= ", ".$a_toSave['CIVICO_DESTINATARIO'].$a_toSave['ESPONENTE_DESTINATARIO']." ".$a_toSave['DETTAGLI_DESTINATARIO'];
    }

    if($a_toSave['CC_DESTINATARIO']==""){
        $row['ERRORE_SCARTO'] = "PROBLEMI NEL RICONOSCIMENTO DEL COMUNE/PAESE DEL DESTINATARIO";
        if($_SESSION['username']=="mirkop"){
            echo "<br><br>";
            print_r($row);
            echo "<br><br>";
        }
        $a_scarti[] = $row;
        continue;
    }

    $a_toSave['TIPO_RISCOSSIONE'] = "";
    $a_toSave['SOTTOTIPO_RISCOSSIONE'] = "";
    $a_toSave['TIPO_INFO_TRIBUTO'] = "E";
    $a_toSave['TIPO_SANZIONE'] = "";

    switch ($row['TIPO_RISCOSSIONE']){
        case "TARES / TARI":
            $a_toSave['TIPO_RISCOSSIONE'] = "RIFIUTI";
            $a_toSave['SOTTOTIPO_RISCOSSIONE'] = "TARES";
            break;
        case "TSRSU":
            $a_toSave['TIPO_RISCOSSIONE'] = "RIFIUTI";
            $a_toSave['SOTTOTIPO_RISCOSSIONE'] = "TSRSU";
            break;
        case "IMU":
            $a_toSave['TIPO_RISCOSSIONE'] = "IMMOBILI";
            $a_toSave['SOTTOTIPO_RISCOSSIONE'] = "IMU";
            break;
        case "ICI":
            $a_toSave['TIPO_RISCOSSIONE'] = "IMMOBILI";
            $a_toSave['SOTTOTIPO_RISCOSSIONE'] = "ICI";
            break;
        case "CDS / AMMINISTRATIVA":
            $a_toSave['TIPO_RISCOSSIONE'] = "CDS";
            $a_toSave['SOTTOTIPO_RISCOSSIONE'] = "";
            $a_toSave['TIPO_INFO_TRIBUTO'] = "S";
            $a_toSave['TIPO_SANZIONE'] = "VE";
            break;
        case "IRPEF":
            $a_toSave['TIPO_RISCOSSIONE'] = "IRPEF";
            $a_toSave['SOTTOTIPO_RISCOSSIONE'] = "";
            break;
        case "OSAP":
            $a_toSave['TIPO_RISCOSSIONE'] = "OSAP";
            $a_toSave['SOTTOTIPO_RISCOSSIONE'] = "";
            break;
        case "PATRIMONIALE":
            $a_toSave['TIPO_RISCOSSIONE'] = "PATRIMONIALE";
            $a_toSave['SOTTOTIPO_RISCOSSIONE'] = "";
            break;
        case "PUBBLICITA":
            $a_toSave['TIPO_RISCOSSIONE'] = "PUBBLICITA";
            $a_toSave['SOTTOTIPO_RISCOSSIONE'] = "";
            break;
        default:
            $row['ERRORE_SCARTO'] = "TIPO RISCOSSIONE SCONOSCIUTA";
            if($_SESSION['username']=="mirkop"){
                echo "<br><br>";
                print_r($row);
                echo "<br><br>";
            }
            $a_scarti[] = $row;
            continue;
            break;
    }

    $a_toSave['DATA_DECORRENZA_INTERESSI'] = "";
    $a_toSave['DATA_NOTIFICA_ATTO'] = "";
    $row['DATA_NOTIFICA_ACCERTAMENTO'] = $cls_help->toItalianDate($row['DATA_NOTIFICA_ACCERTAMENTO']);
    $row['DATA_NOTIFICA_ATTO'] = $cls_help->toItalianDate($row['DATA_NOTIFICA_ATTO']);

    if($row['DATA_NOTIFICA_ATTO']!=null){
        $a_toSave['DATA_DECORRENZA_INTERESSI'] =  $cls_help->toDbDate($row['DATA_NOTIFICA_ATTO']);
        $a_toSave['DATA_NOTIFICA_ATTO'] = $a_toSave['DATA_DECORRENZA_INTERESSI'];
    }
    else if($row['DATA_NOTIFICA_ACCERTAMENTO']!=null){
        switch ($row['TIPO_RISCOSSIONE']){
            case "PUBBLICITA":
            case "IMU":
            case "ICI":
            case "OSAP":
                $a_toSave['DATA_DECORRENZA_INTERESSI'] = $cls_help->toDbDate($row['DATA_NOTIFICA_ACCERTAMENTO']);
                break;
            case "CDS / AMMINISTRATIVA":
                $a_toSave['DATA_DECORRENZA_INTERESSI'] = date("Y-m-d" ,strtotime( $cls_help->toDbDate($row['DATA_NOTIFICA_ACCERTAMENTO'])."+2 month" ));
                break;
            default:
                $a_toSave['DATA_DECORRENZA_INTERESSI'] = date("Y-m-d" ,strtotime( $cls_help->toDbDate($row['DATA_NOTIFICA_ACCERTAMENTO'])."+1 month" ));
        }
    }
    else{
        $row['ERRORE_SCARTO'] = "DATA DI NOTIFICA ASSENTE";
        if($_SESSION['username']=="mirkop"){
            echo "<br><br>";
            print_r($row);
            echo "<br><br>";
        }
        $a_scarti[] = $row;
        continue;
    }

    $totaleDovuto = (double)$row['IMPORTO_ACCERTAMENTO']+(double)$row['SPESE_ACCERTAMENTO']+(double)$row['SPESE_RICERCA']+(double)$row['INTERESSI_ACCERTAMENTO'];
    $totaleDovuto+= (double)$row['SANZIONE_GENERICA']+(double)$row['SANZIONE_DICHIARAZIONE']+(double)$row['SANZIONE_PAGAMENTO'];
    $totaleDovuto+= (double)$row['MAGGIORAZIONE_ACCERTAMENTO']+(double)$row['DIRITTI_ACCESSORI']+(double)$row['ADDIZIONALE_COMUNALE']+(double)$row['ADDIZIONALE_PROVINCIALE']-(double)$row['PAGAMENTO'];
    $totaleDovuto+= (double)$row['ONERI_RISCOSSIONE']+(double)$row['INTERESSI_ATTO']+(double)$row['SPESE_NOTIFICA_ATTO'];
    if( $totaleDovuto <=0 && $totaleDovuto!=(double)$row['DOVUTO_TOTALE']){
        $row['ERRORE_SCARTO'] = "ERRORE NEGLI IMPORTI";
        if($_SESSION['username']=="mirkop"){
            echo "<br><br>";
            print_r($row);
            echo "<br><br>";
        }
        $a_scarti[] = $row;
        continue;
    }

    $a_toSave['INFORMAZIONI_CARTELLA'] = $row['INFORMAZIONI_CARTELLA'];
    if($row['DEFUNTO']!="")
        $a_toSave['INFORMAZIONI_CARTELLA'].= " - DEFUNTO: ".$row['DEFUNTO'];

    $a_info = "";
    $infoQuery = $cls_ruolo->getPartitaFromInfoCartella_query($a_toSave['CC_IMPORTAZIONE'],$a_toSave['INFORMAZIONI_CARTELLA']);
    $a_info = $cls_db->getArrayLine($cls_db->SelectQuery($infoQuery));

    if($a_info['Partita_ID']>0){
        $row['ERRORE_SCARTO'] = "PARTITA CONTABILE GIA' PRESENTE IN ARCHIVIO";
        if($_SESSION['username']=="mirkop"){
            echo "<br><br>";
            print_r($row);
            echo "<br><br>";
        }
        $a_scarti[] = $row;
        continue;
    }

    if($checkInsertRuolo == 0) {
        $ruolo_comune_id = $a_ruolo['Comune_ID']+1;

        $descrizione_ruolo = "Ruolo n.".$ruolo_comune_id." importato da Excel il ".date('d/m/Y');
        $a_bind = array($a_toSave['CC_IMPORTAZIONE'],$ruolo_comune_id,date('Y-m-d'),date('Y-m-d'), $descrizione_ruolo,1);

        $query = "INSERT INTO ruolo (CC,Comune_ID,Data_Fornitura,Data_Inserimento,Descrizione,Progr_Fornitura) ";
        $query.= "VALUES (?,?,?,?,?,?);";

        $stmt = mysqli_prepare($cls_db->conn,$query);
        $stmt->bind_param('sisssi',$a_bind[0],$a_bind[1],$a_bind[2],$a_bind[3],$a_bind[4],$a_bind[5]);
        $checkBind = $stmt->execute();

        if($checkBind===false) {
            $cls_db->Rollback();
            $cls_help->alert('Errore nel salvataggio del ruolo!');
            die;
        }
        else {
            $id_ruolo = $cls_db->lastInsertId();
            $checkInsertRuolo = 1;
        }
    }

    $new_utente = 0;
    if($a_toSave['UTENTE_ID']==null){

        if($a_toSave['SESSO']!="D"){
            $fields = "CC_Comune,Comune_ID,Genere,Cognome,Nome,Codice_Fiscale,CC_Nascita,Paese_Nascita,Comune_Nascita,Data_Nascita";
            $values = "?,?,?,?,?,?,?,?,?,?";
            $bindTypes = "sissssssss";
            $a_bind = array($a_toSave['CC_IMPORTAZIONE'],$comune_id_utente,$a_toSave['SESSO'],$a_toSave['COGNOME'],$a_toSave['NOME'], $a_toSave['CODICE_FISCALE'],
                $a_toSave['CC_NASCITA'],$a_toSave['PAESE_NASCITA'],$a_toSave['COMUNE_NASCITA'],$a_toSave['DATA_NASCITA']);

        }
        else{
            $fields = "CC_Comune,Comune_ID,Genere,Ditta,Partita_Iva";
            $values = "?,?,?,?,?";
            $bindTypes = "sisss";
            $a_bind = array($a_toSave['CC_IMPORTAZIONE'],$comune_id_utente,$a_toSave['SESSO'],$a_toSave['SOCIETA'],$a_toSave['PARTITA_IVA']);
        }

        $queryBind = "INSERT INTO utente (".$fields.") VALUES(".$values.")";
        $stmt = mysqli_prepare($cls_db->conn,$queryBind);
        if($a_toSave['SESSO']!="D")
            $stmt->bind_param($bindTypes,$a_bind[0],$a_bind[1],$a_bind[2],$a_bind[3],$a_bind[4],$a_bind[5],$a_bind[6],$a_bind[7],$a_bind[8],$a_bind[9]);
        else
            $stmt->bind_param($bindTypes,$a_bind[0],$a_bind[1],$a_bind[2],$a_bind[3],$a_bind[4]);

        $checkBind = $stmt->execute();
        if($checkBind===false) {
            $cls_db->Rollback();

            $row['ERRORE_SCARTO'] = "ERRORE NEL SALVATAGGIO DELL'UTENTE";
            if($_SESSION['username']=="mirkop"){
                echo "<br><br>";
                print_r($row);
                echo "<br><br>";
            }
            $a_scarti[] = $row;

            continue;
        }
        else{
            $a_toSave['UTENTE_ID'] = $cls_db->lastInsertId();
            $new_utente = 1;
        }


        $addressQuery = $cls_registry->getAddressFromName_query($a_toSave['CC_IMPORTAZIONE'],$a_toSave['CC_DESTINATARIO'],
            $a_toSave['VIA_DESTINATARIO'],$a_toSave['CAP_DESTINATARIO'],$a_toSave['COMUNE_DESTINATARIO'],$a_toSave['PAESE_DESTINATARIO']);
        $a_address = $cls_db->getArrayLine($cls_db->SelectQuery($addressQuery));
        $a_toSave['TOPONIMO_ID'] = $a_address['ID'];

        if($a_toSave['TOPONIMO_ID']==null){
            $fields = "CC_Comune,CC_Toponimo,Nome,Cap,Comune,Paese";
            $values = "?,?,?,?,?,?";
            $bindTypes = "ssssss";
            $a_bind = array($a_toSave['CC_IMPORTAZIONE'],$a_toSave['CC_DESTINATARIO'],$a_toSave['VIA_DESTINATARIO'],$a_toSave['CAP_DESTINATARIO'],
                $a_toSave['COMUNE_DESTINATARIO'],$a_toSave['PAESE_DESTINATARIO']);

            $queryBind = "INSERT INTO toponimo (".$fields.") VALUES(".$values.")";
            $stmt = mysqli_prepare($cls_db->conn,$queryBind);
            $stmt->bind_param($bindTypes,$a_bind[0],$a_bind[1],$a_bind[2],$a_bind[3],$a_bind[4],$a_bind[5]);

            $checkBind = $stmt->execute();
            if($checkBind===false) {
                $cls_db->Rollback();

                $row['ERRORE_SCARTO'] = "ERRORE NEL SALVATAGGIO DEL TOPONIMO";
                if($_SESSION['username']=="mirkop"){
                    echo "<br><br>";
                    print_r($row);
                    echo "<br><br>";
                }
                $a_scarti[] = $row;
                continue;
            }
            else
                $a_toSave['TOPONIMO_ID'] = $cls_db->lastInsertId();
        }




        $fields = "Tipo,CC_Indirizzo,Cap,Comune,Civico,Data_Inizio_Residenza,Dettagli,Esponente,Interno,Paese,Provincia,Utente_ID,Via_Cap_ID,Via_ID";
        $values = "?,?,?,?,?,?,?,?,?,?,?,?,?,?";
        $bindTypes = "ssssisssissiii";
        $a_bind = array("res", $a_toSave['CC_DESTINATARIO'],$a_toSave['CAP_DESTINATARIO'],$a_toSave['COMUNE_DESTINATARIO'],$a_toSave['CIVICO_DESTINATARIO'],
            "1900-01-01",$a_toSave['DETTAGLI_DESTINATARIO'],$a_toSave['ESPONENTE_DESTINATARIO'],$a_toSave['INTERNO_DESTINATARIO'],$a_toSave['PAESE_DESTINATARIO'],
            $a_toSave['PROVINCIA_DESTINATARIO'],$a_toSave['UTENTE_ID'],1,$a_toSave['TOPONIMO_ID']);


        if($row['PRESSO'] != ""){
            $a_bind[0] = "rec";

            $fields.= ",Presso";
            $values.= ",?";
            $bindTypes.= "s";

            $a_bind[] = $a_toSave['PRESSO'];


            $queryBind = "INSERT INTO indirizzo (".$fields.") VALUES(".$values.")";
            $stmt = mysqli_prepare($cls_db->conn,$queryBind);

            $stmt->bind_param($bindTypes,$a_bind[0],$a_bind[1],$a_bind[2],$a_bind[3],$a_bind[4],$a_bind[5],$a_bind[6],$a_bind[7],
                $a_bind[8],$a_bind[9],$a_bind[10],$a_bind[11],$a_bind[12],$a_bind[13],$a_bind[14]);
        }
        else{
            $queryBind = "INSERT INTO indirizzo (".$fields.") VALUES(".$values.")";
            $stmt = mysqli_prepare($cls_db->conn,$queryBind);

            $stmt->bind_param($bindTypes,$a_bind[0],$a_bind[1],$a_bind[2],$a_bind[3],$a_bind[4],$a_bind[5],$a_bind[6],$a_bind[7],
                $a_bind[8],$a_bind[9],$a_bind[10],$a_bind[11],$a_bind[12],$a_bind[13]);
        }

        $checkBind = $stmt->execute();
        if($checkBind===false) {
            $cls_db->Rollback();

            $row['ERRORE_SCARTO'] = "ERRORE NEL SALVATAGGIO DELL'INDIRIZZO";
            if($_SESSION['username']=="mirkop"){
                echo "<br><br>";
                print_r($row);
                echo "<br><br>";
            }
            $a_scarti[] = $row;
            continue;
        }
    }

    $a_toSave['PARTITA_ID'] = null;
    $a_toSave['ANNO_RIFERIMENTO'] = $row['ANNO_RIFERIMENTO'];
    $a_toSave['NOTE'] = $row['NOTE'];
    $fields = "CC,Comune_ID,Ruolo_ID,Anno_Riferimento,Tipo,Sottotipo,Utente_ID,Note_Blocco";
    $values = "?,?,?,?,?,?,?,?";
    $bindTypes = "siiissis";
    $a_bind = array($a_toSave['CC_IMPORTAZIONE'],$comune_id_partita,$id_ruolo,$a_toSave['ANNO_RIFERIMENTO'],$a_toSave['TIPO_RISCOSSIONE'],
        $a_toSave['SOTTOTIPO_RISCOSSIONE'],$a_toSave['UTENTE_ID'],$a_toSave['NOTE']);
    $queryBind = "INSERT INTO partita_tributi (".$fields.") VALUES(".$values.")";
    $stmt = mysqli_prepare($cls_db->conn,$queryBind);
    $stmt->bind_param($bindTypes,$a_bind[0],$a_bind[1],$a_bind[2],$a_bind[3],$a_bind[4],$a_bind[5],$a_bind[6],$a_bind[7]);
    $checkBind = $stmt->execute();
    if($checkBind===false) {
        $cls_db->Rollback();

        $row['ERRORE_SCARTO'] = "ERRORE NEL SALVATAGGIO DELLA PARTITA";
        if($_SESSION['username']=="mirkop"){
            echo "<br><br>";
            print_r($row);
            echo "<br><br>";
        }
        $a_scarti[] = $row;
        continue;
    }
    else
        $a_toSave['PARTITA_ID'] = $cls_db->lastInsertId();



    $fields = "Codice_Tributo,Imposta,CC,Partita_ID,Info_Cartella,Anno_Tributo,Data_Decorrenza_Interessi,Tipo_Info,Tipo_Sanzione";
    $values = "?,?,?,?,?,?,?,?,?";
    $bindTypes = "sdsisisss";
    $a_bind = array("",0.00,$a_toSave['CC_IMPORTAZIONE'],$a_toSave['PARTITA_ID'],$a_toSave['INFORMAZIONI_CARTELLA'],$a_toSave['ANNO_RIFERIMENTO'],
        $a_toSave['DATA_DECORRENZA_INTERESSI'],$a_toSave['TIPO_INFO_TRIBUTO'],$a_toSave['TIPO_SANZIONE']);
    $queryBind = "INSERT INTO tributo (".$fields.") VALUES(".$values.")";
    $stmt = mysqli_prepare($cls_db->conn,$queryBind);
    $stmt->bind_param($bindTypes,$a_bind[0],$a_bind[1],$a_bind[2],$a_bind[3],$a_bind[4],$a_bind[5],$a_bind[6],$a_bind[7],$a_bind[8]);

    $saltaRecord = 0;
    for($j=1;$j<=16;$j++)
    {
        if($j<10)
            $key = "0".$j;
        else
            $key = $j;

        $a_toSave['CODICE_TRIBUTO'] = "S_".$key;
        $a_toSave['IMPORTO'] = 0;

        switch($j){
            case 1:     $a_toSave['IMPORTO'] = $row['SPESE_RICERCA'];               break;
            case 2:     $a_toSave['IMPORTO'] = $row['PAGAMENTO'];                   break;
//            case 3:     $a_toSave['IMPORTO'] = $row['SPESE_NOTIFICA_ATTO'];                break;
            case 4:     $a_toSave['IMPORTO'] = $row['IMPORTO_ACCERTAMENTO'];        break;
            case 5:     $a_toSave['IMPORTO'] = $row['SPESE_ACCERTAMENTO'];          break;
//            case 6:     $a_toSave['IMPORTO'] = $row['SPESE_NOTIFICA_ATTO'];               break;
            case 7:     $a_toSave['IMPORTO'] = $row['SANZIONE_GENERICA'];           break;
            case 8:     $a_toSave['IMPORTO'] = $row['SANZIONE_DICHIARAZIONE'];      break;
            case 9:     $a_toSave['IMPORTO'] = $row['SANZIONE_PAGAMENTO'];          break;
//            case 10:    $a_toSave['IMPORTO'] = $row['INTERESSI_ATTO']; break;
            case 11:    $a_toSave['IMPORTO'] = $row['DIRITTI_ACCESSORI'];           break;
            case 12:    $a_toSave['IMPORTO'] = $row['ADDIZIONALE_COMUNALE'];        break;
            case 13:    $a_toSave['IMPORTO'] = $row['ADDIZIONALE_PROVINCIALE'];     break;
//            case 14:    $a_toSave['IMPORTO'] = $row['ONERI_RISCOSSIONE'];           break;
            case 15:    $a_toSave['IMPORTO'] = $row['MAGGIORAZIONE_ACCERTAMENTO'];  break;
            case 16:    $a_toSave['IMPORTO'] = $row['INTERESSI_ACCERTAMENTO'];  break;
        }

        if($a_toSave['IMPORTO']==0)
            continue;

        $a_bind[0] = $a_toSave['CODICE_TRIBUTO'];

        $a_bind[1] = number_format($a_toSave['IMPORTO'],2,".","");

        $checkBind = $stmt->execute();
        if($checkBind===false)
        {
            $cls_db->Rollback();

            $row['ERRORE_SCARTO'] = "ERRORE NEL SALVATAGGIO DEI CODICI TRIBUTO DELLA PARTITA";
            if($_SESSION['username']=="mirkop"){
                echo "<br><br>";
                print_r($row);
                echo "<br><br>";
            }
            $a_scarti[] = $row;
            $saltaRecord = 1;
            break;
        }
    }

    if($saltaRecord==1)
        continue;
    else{
        $parzialeAtto = $row['ONERI_RISCOSSIONE']+$row['INTERESSI_ATTO']+$row['SPESE_NOTIFICA_ATTO'];
        if($row['TIPO_ATTO']!=""){
            if($row['TIPO_ATTO']=="INGIUNZIONE" || $row['TIPO_ATTO']=="AVVISO DI MESSA IN MORA"){
//                if( $parzialeAtto <= 0 ){
//                    $cls_db->Rollback();
//
//                    $row['ERRORE_SCARTO'] = "IMPORTI ATTO ASSENTI!";
//                    if($_SESSION['username']=="mirkop"){
//                        echo "<br><br>";
//                        print_r($row);
//                        echo "<br><br>";
//                    }
//                    $a_scarti[] = $row;
//                    continue;
//                }
//                else
                    if(!$row['NUMERO_CRONOLOGICO_ATTO']>0 || !$row['ANNO_CRONOLOGICO_ATTO']>0){
                    $cls_db->Rollback();

                    $row['ERRORE_SCARTO'] = "ERRORE NEL CRONOLOGICO ATTO! ".$row['NUMERO_CRONOLOGICO_ATTO']." ".$row['ANNO_CRONOLOGICO_ATTO'];
                    if($_SESSION['username']=="mirkop"){
                        echo "<br><br>";
                        print_r($row);
                        echo "<br><br>";
                    }
                    $a_scarti[] = $row;

                    die;
                    continue;
                }
                else{

                    $a_crono = preg_split('/(?<=[0-9])(?=[a-z]+)/i',$row['NUMERO_CRONOLOGICO_ATTO']);
                    $a_toSave['ID_Cronologico'] = 0;
                    $a_toSave['Protocollo'] = "";

                    if(is_numeric($a_crono[0]) && $a_crono[0]>0){
                        $a_toSave['ID_Cronologico'] = $a_crono[0];
                    }
                    else{
                        $cls_db->Rollback();
                        $row['ERRORE_SCARTO'] = "ERRORE NEL NUMERO CRONOLOGICO ATTO! INT";
                        if($_SESSION['username']=="mirkop"){
                            echo "<br><br>";
                            print_r($row);
                            echo "<br><br>";
                        }
                        $a_scarti[] = $row;
                        continue;
                    }

                    if(isset($a_crono[1])){
                        if(is_string($a_crono[1])){
                            $a_toSave['Protocollo'] = $a_crono[1];
                        }
                        else{
                            $cls_db->Rollback();
                            $row['ERRORE_SCARTO'] = "ERRORE NEL NUMERO CRONOLOGICO ATTO! STRING";
                            if($_SESSION['username']=="mirkop"){
                                echo "<br><br>";
                                print_r($row);
                                echo "<br><br>";
                            }
                            $a_scarti[] = $row;
                            continue;
                        }
                    }

                    if($row['ANNO_CRONOLOGICO_ATTO']>0){
                        $a_toSave['Anno_Cronologico'] = $row['ANNO_CRONOLOGICO_ATTO'];
                    }
                    else{
                        $cls_db->Rollback();
                        $row['ERRORE_SCARTO'] = "ERRORE ANNO CRONOLOGICO!";
                        if($_SESSION['username']=="mirkop"){
                            echo "<br><br>";
                            print_r($row);
                            echo "<br><br>";
                        }
                        $a_scarti[] = $row;
                        continue;
                    }

                    $a_toSave['Stato_Notifica'] = substr($row['GIACENZA'],0,2);
                    $a_toSave['Motivo_Notifica'] = substr($row['ANOMALIA'],0,2);

                    $fields = "CC, Partita_ID, Info_Cartella, Data_Notifica, ID_Cronologico, Anno_Cronologico, Protocollo, Cronologico_Vecchio, ";
                    $fields.= "Stato_Notifica, Motivo_Notifica, Data_Elaborazione, Data_Calcolo_Interessi, Data_Stampa, Stato_Stampa, Atto, ";
                    $fields.= "Tipo_Ufficiale, Modalita_Stampa, Interessi, Spese_Notifica, Totale_Dovuto ";
                    $values = "?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?";
                    $bindTypes = "sissiissiisssssssddd";
                    $a_bind = array($a_toSave['CC_IMPORTAZIONE'],$a_toSave['PARTITA_ID'],$a_toSave['INFORMAZIONI_CARTELLA'],$a_toSave['DATA_NOTIFICA_ATTO'],
                        $a_toSave['ID_Cronologico'], $a_toSave['Anno_Cronologico'],$a_toSave['Protocollo'],"si",$a_toSave['Stato_Notifica'],$a_toSave['Motivo_Notifica'],
                        $a_toSave['DATA_NOTIFICA_ATTO'],$a_toSave['DATA_NOTIFICA_ATTO'],$a_toSave['DATA_NOTIFICA_ATTO'], "Stampato", ucfirst(strtolower($row['TIPO_ATTO'])),
                        "diretta", "posta", $row['INTERESSI_ATTO'], $row['SPESE_NOTIFICA_ATTO'], $row['DOVUTO_TOTALE']
                    );

                    $queryBind = "INSERT INTO atto (".$fields.") VALUES(".$values.")";
                    $stmt = mysqli_prepare($cls_db->conn,$queryBind);
                    $stmt->bind_param($bindTypes,$a_bind[0],$a_bind[1],$a_bind[2],$a_bind[3],$a_bind[4],$a_bind[5],$a_bind[6],$a_bind[7],$a_bind[8],
                        $a_bind[9],$a_bind[10],$a_bind[11],$a_bind[12],$a_bind[13],$a_bind[14],$a_bind[15],$a_bind[16],$a_bind[17],$a_bind[18],$a_bind[19] );

                    $checkBind = $stmt->execute();
                    if($checkBind===false)
                    {
                        $cls_db->Rollback();

                        $row['ERRORE_SCARTO'] = "ERRORE NEL SALVATAGGIO DELL'ATTO";
                        if($_SESSION['username']=="mirkop"){
                            echo "<br><br>";
                            print_r($row);
                            echo "<br><br>";
                        }
                        $a_scarti[] = $row;
                        $saltaRecord = 1;
                        break;
                    }
                }
            }
            else{
                $cls_db->Rollback();
                $row['ERRORE_SCARTO'] = "TIPO ATTO ERRATO!";
                if($_SESSION['username']=="mirkop"){
                    echo "<br><br>";
                    print_r($row);
                    echo "<br><br>";
                }
                $a_scarti[] = $row;
                continue;
            }

        }
        else{
            if( $parzialeAtto > 0 ){
                $cls_db->Rollback();
                $row['ERRORE_SCARTO'] = "TIPO_ATTO NON SELEZIONATO CON IMPORTI PRESENTI!";
                if($_SESSION['username']=="mirkop"){
                    echo "<br><br>";
                    print_r($row);
                    echo "<br><br>";
                }
                $a_scarti[] = $row;
                continue;
            }
        }
    }

    if($row['ERRORE_SCARTO']==""){
        $cls_db->End_Transaction();
        $comune_id_partita++;
        if($new_utente==1)
            $comune_id_utente++;
    }
}

echo "<script>fine();</script>";

flush();ob_flush();flush();ob_flush();
flush();ob_flush();flush();ob_flush();

if(count($a_scarti)>0){
    $objPHPExcelScarti = new PHPExcel();
    $objPHPExcelScarti->getProperties()
        ->setCreator("Sarida")
        ->setLastModifiedBy($_SESSION['username'])
        ->setTitle("Scarti importazione Gitco")
        ->setSubject("Scarti")
        ->setDescription("Record scartati a causa di errori di compilazione");
    $objPHPExcelScarti->setActiveSheetIndex(0);

    $col = 0;
    foreach(array_keys($a_scarti[0]) as $key) {
        $objPHPExcelScarti->getActiveSheet()->setCellValueByColumnAndRow($col, 1, $key);

        $colString = PHPExcel_Cell::stringFromColumnIndex($col);

        if($key=="ANNO_RIFERIMENTO" || $key=="CIVICO_DESTINATARIO" || $key=="INTERNO_DESTINATARIO" ||  $key=="ANNO_CRONOLOGICO_ATTO")
        {
            $objPHPExcelScarti->getActiveSheet()
                ->getStyle($colString."2:".$colString.(count($a_scarti)+2))
                ->getNumberFormat()
                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
        }
        else if($key=="IMPORTO_ACCERTAMENTO" || $key=="SPESE_ACCERTAMENTO" || $key=="SPESE_RICERCA" || $key=="MAGGIORAZIONE_ACCERTAMENTO")
        {
            $objPHPExcelScarti->getActiveSheet()
                ->getStyle($colString."2:".$colString.(count($a_scarti)+2))
                ->getNumberFormat()
                ->setFormatCode('[$€ ]#,##0.00_-');
        }
        else if($key=="SPESE_NOTIFICA_ATTO" || $key=="SANZIONE_GENERICA" || $key=="INTERESSI_ACCERTAMENTO")
        {
            $objPHPExcelScarti->getActiveSheet()
                ->getStyle($colString."2:".$colString.(count($a_scarti)+2))
                ->getNumberFormat()
                ->setFormatCode('[$€ ]#,##0.00_-');
        }
        else if($key=="SANZIONE_DICHIARAZIONE" || $key=="SANZIONE_PAGAMENTO" || $key=="INTERESSI_ATTO")
        {
            $objPHPExcelScarti->getActiveSheet()
                ->getStyle($colString."2:".$colString.(count($a_scarti)+2))
                ->getNumberFormat()
                ->setFormatCode('[$€ ]#,##0.00_-');
        }
        else if($key=="DIRITTI_ACCESSORI" || $key=="ADDIZIONALE_COMUNALE" || $key=="ADDIZIONALE_PROVINCIALE")
        {
            $objPHPExcelScarti->getActiveSheet()
                ->getStyle($colString."2:".$colString.(count($a_scarti)+2))
                ->getNumberFormat()
                ->setFormatCode('[$€ ]#,##0.00_-');
        }
        else if($key=="ONERI_RISCOSSIONE" || $key=="PAGAMENTO" || $key=="DOVUTO_TOTALE")
        {
            $objPHPExcelScarti->getActiveSheet()
                ->getStyle($colString."2:".$colString.(count($a_scarti)+2))
                ->getNumberFormat()
                ->setFormatCode('[$€ ]#,##0.00_-');
        }
        else if($key=="DATA_NASCITA" || $key=="DATA_NOTIFICA_ATTO" || $key=="DATA_NOTIFICA_ACCERTAMENTO")
        {
            $objPHPExcelScarti->getActiveSheet()
                ->getStyle($colString."2:".$colString.(count($a_scarti)+2))
                ->getNumberFormat()
                ->setFormatCode("dd/mm/yy");
        }
        else{
            $objPHPExcelScarti->getActiveSheet()
                ->getStyle($colString . "2:" . $colString . (count($a_scarti) + 2))
                ->getNumberFormat()
                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//            $objValidation = $objPHPExcelScarti->getActiveSheet()->getCell('B7')->getDataValidation();
//            if ($key == "TIPO_SOGGETTO") {
//                $list = "MASCHIO,FEMMINA,DITTA";
//                $objValidation->setFormula1($list);
//            }
        }

        $col++;
    }

    $row = 2; // 1-based index
    for($i=0;$i<count($a_scarti);$i++) {
        $col = 0;
        foreach($a_scarti[$i] as $key=>$value) {
            if($key=="PARTITA_IVA"){
                $colString = PHPExcel_Cell::stringFromColumnIndex($col);
                $objPHPExcelScarti->getActiveSheet()->setCellValueExplicit($colString.$row, $value, PHPExcel_Cell_DataType::TYPE_STRING);
            }
            else
                $objPHPExcelScarti->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $value);
            $col++;
        }
        $row++;
    }

    $writer = PHPExcel_IOFactory::createWriter($objPHPExcelScarti, 'Excel5');

    $writer->save($nomeFile);

    if(is_file($nomeFile)){
        echo "<script>mostra_file();</script>";
    }

}else{
    $cls_help->alert("Nessuno scarto effettuato!");
}

include(INC."/footer.php");