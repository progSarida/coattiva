F<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include_once INC . "/headerAjax.php";

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_html.php";
include_once CLS . "/cls_pdf.php";
include_once CLS . "/cls_Utils.php";
include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/cls_DateTime.php";
include_once CLS . "/cls_Soap_Visure.php";
include_once CLS . "/cls_check.php";
include_once CLS . "/cls_storico.php";													

$storico = new storico('storicoElaborazioni','5');
$cls_help = new cls_help();
$cls_db = new cls_db();
$cls_utils = new cls_Utils();
$cls_date = new cls_DateTimeI("DB",false);
$cls_check = new cls_check();

//AGGIUNGERE SANZIONE DA INGIUNZIONE


$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$storico_msg = "Elaborata visura veicoli";
$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
$nome_ente = $ente['Denominazione'];	

$genere = $cls_help->getVar('genere');
$dacognome = $cls_help->getVar('daco');
$acognome = $cls_help->getVar('acog');
$danome = $cls_help->getVar('dano');
$anome = $cls_help->getVar('anom');
$daNEl = $cls_help->getVar("da_n_elenco");
$aNEl = $cls_help->getVar("a_n_elenco");

//$query = "SELECT * FROM atto AS A JOIN partita_tributi AS P ON A.Partita_ID = P.ID JOIN utente AS U ON U.ID = P.Utente_ID WHERE A.ID = (SELECT MAX(A2.ID) FROM atto AS A2 WHERE A2.Partita_ID = A.Partita_ID AND (A2.DocumentTypeId = 4 OR A2.DocumentTypeId = 2))";
$query = "SELECT A.DocumentTypeId, U.Genere, U.Cognome, U.Nome, U.Ditta, U.Codice_Fiscale, U.Partita_Iva, U.ID as Utente_ID, U.Comune_ID as Utente_Comune_ID, P.Comune_ID as Partita_Comune_ID,
            A.Totale_Dovuto, A.Rate_Previste, A.Scadenze_Rate, A.Data_Notifica, A.Diritto_Riscossione_Massimo, A.Diritto_Riscossione_Minimo, A.ID, A.Partita_ID, A.Info_Cartella
            FROM atto AS A
            JOIN partita_tributi AS P ON A.Partita_ID = P.ID
            JOIN utente AS U ON U.ID = P.Utente_ID
            WHERE A.ID = (SELECT MAX(A2.ID) FROM atto AS A2 WHERE A2.Partita_ID = A.Partita_ID AND (A.DocumentTypeId = 2 OR A.DocumentTypeId = 4)) AND U.CC_Comune = '".$c."'";

if($dacognome != null){
    $strCompareDa = addslashes($dacognome)." ".addslashes($danome);
    $strCompareA = addslashes($acognome)." ".addslashes($anome);

    $query .= " AND ( CONCAT(COALESCE(U.Ditta,''),COALESCE(U.Cognome,''),' ',COALESCE(U.Nome,'')) >= '".$strCompareDa."' AND CONCAT(COALESCE(U.Ditta,''),COALESCE(U.Cognome,''),' ',COALESCE(U.Nome,'')) <= '".$strCompareA."' ) ";

    $storico_msg.= " dal contribuente ".$dacognome." ".$danome." al contribuente ".$acognome." ".$anome;
}

/*if($genere != "D"){
    if($dacognome != null)
    {
        $query.= " AND ( ( U.Cognome > '".addslashes($dacognome)."' ) ";
        $query.= "AND ( U.Cognome < '".addslashes($acognome)."' ) ";
        $query.= "OR ( U.Cognome = '".addslashes($dacognome)."' ";
        if($danome != null)
        {
            $query.= "AND U.Nome >= '".addslashes($danome)."' ";
        }

        $query.= ") OR ( U.Cognome = '".addslashes($acognome)."' ";
        if($anome != null)
        {
            $query.= "AND U.Nome <= '".addslashes($anome)."' ";
        }
        $query.= ") ) ";
    }
}
else{
    if($dacognome != null)
        $query.= " AND ( U.Ditta >= '".addslashes($dacognome)."' AND U.Ditta <= '".addslashes($acognome)."' ) ";
}*/

if($cls_help->getVar("tipo_partita") != null){
    $query .= " AND P.Tipo = '".$cls_help->getVar("tipo_partita")."' ";
    $storico_msg = " per le entrate di tipo ".$cls_help->getVar("tipo_partita");
}

if($cls_help->getVar("modalita_stampa") != null){
    $query .= " AND A.Modalita_Stampa = '".$cls_help->getVar("modalita_stampa")."' ";
}

if($daNEl!=null)
{
    $query .= " AND P.Comune_ID >= ".$daNEl;
    $storico_msg.= " dalla partita ".$daNEl;
}

if($aNEl!=null)
{
    $query .= " AND P.Comune_ID <= ".$aNEl;
    $storico_msg.= " alla partita ".$aNEl;
}

if($cls_help->getVar("da_data")!=null)
{
    $query .= " AND A.Data_Notifica >= '".$cls_date->GetDateDB($cls_help->getVar("da_data"),"IT")."'";
    $storico_msg.= " dalla data ".$cls_date->GetDateDB($cls_help->getVar("da_data"),"IT");
}

if($cls_help->getVar("a_data")!=null)
{
    $query .= " AND A.Data_Notifica <= '".$cls_date->GetDateDB($cls_help->getVar("a_data"),"IT")."'";
    $storico_msg.= " alla data ".$cls_date->GetDateDB($cls_help->getVar("a_data"),"IT");
}

if($cls_help->getVar("da_anno")!=null)
{
    $query .= " AND P.Anno_Riferimento >= '".$cls_help->getVar("da_anno")."'";
    $storico_msg.= " dall'anno ".$cls_help->getVar("da_anno");
}

if($cls_help->getVar("ad_anno")!=null)
{
    $query .= " AND P.Anno_Riferimento <= '".$cls_help->getVar("ad_anno")."'";
    $storico_msg.= " all'anno ".$cls_help->getVar("ad_anno");
}

//$query .= " GROUP BY U.ID, P.ID ORDER by U.ID";
$query .= " GROUP BY U.ID, P.ID, A.DocumentTypeId ORDER by U.ID,P.ID";


//echo $query;
//die;


$result = $cls_db->getResults($cls_db->ExecuteQuery($query));

?>

<script>
    function inizio()
    {
        $('#progressbar').progressbar({
            value: false
        });
        $( "#barlabel" ).text("Inizio elaborazione...");
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

    function novpn()
    {
        $( "#progressbar" ).progressbar({value: 100 });
        $( "#barlabel" ).text("VPN NON CONNESSA!");
    }

    function busyvpn()
    {
        $( "#progressbar" ).progressbar({value: 100 });
        $( "#barlabel" ).text("VPN OCCUPATA!");
    }


    function fine(value)
    {
        $( "#progressbar" ).progressbar({value: 100 });
        $( "#barlabel" ).text( value );
        $( "div#vedi_file" ).append("<div class='row' style='margin-top: 4%;'><div class='col-lg-2 col-lg-offset-1'><input type=button name=avanti class='btn btn-primary resize' value='Elenco elaborazioni' onclick='mostra_file();'></div></div>");
    }

    function mostra_file()
    {
        window.open('<?= WEB_ROOT."/elaborazioni/Temp/resoconto_visura.pdf"; ?>');
        self.close();
    }

</script>


<div class="row justify-content-md-center " style="margin-top: 1%;">
    <div class="col col-md-auto text_center">
        <span class="titolo font18 under_decor">Elaborazione visure ACI-PRA</span>
    </div>
</div>
<div class="row" style="margin-top: 3%;">
    <div class="col-lg-10 col-lg-offset-1">
        <div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div id=vedi_file></div>
    </div>
</div>


<?php


flush();
ob_flush();

echo "<script>inizio();</script>";

flush();
ob_flush();
flush();
ob_flush();
sleep(2);

$pathPdf = $cls_utils->crea_dir(ROOT."/elaborazioni/Temp");

$pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);
//$pdf->setPrintHeader(false);
$pdf->setHeaderTitle("RESOCONTO VISURE");
$pdf->SetMargins(10, 10, 10);
$pdf->setCellPaddings(2,1,2,1);

$styleDash = array('dash' => '6,6');
$styleRetta = array('dash' => '0');

$pdf->AddPage();
$pdf->SetFont('Helvetica', 'B', 11);

$pdf->SetAutoPageBreak(true);
$pdf->Ln(10);

$array_width = array();
$array_intestaz = array();

$array_width[] = 20;	$array_intestaz[] = "ID U.";
$array_width[] = 20;	$array_intestaz[] = "ID P.";
$array_width[] = 20;	$array_intestaz[] = "N° V.";
$array_width[] = 35;	$array_intestaz[] = "Nome";
$array_width[] = 35;	$array_intestaz[] = "Cognome";
$array_width[] = 63;	$array_intestaz[] = "Ditta";


$array_width_2[] = 60;  $array_intestaz_2[] = "Esito";
$array_width_2[] = 133; $array_intestaz_2[] = "Info";

$y1_vert = $pdf->setRow($array_intestaz, "up" , $styleRetta, null, 0 ,$array_width );
$y2_vert = $pdf->setRow($array_intestaz_2, "down" , $styleRetta, null, 0 ,$array_width_2 );

$pdf->SetFont('Helvetica', '', 8);

$num_atti = count($result);
$query="SELECT * FROM semaforo WHERE Procedure_Type_Id = 6";
$a_semaforo = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

if(!empty($a_semaforo['Datetime'])){
    $datetime1 = new DateTime($a_semaforo['Datetime']); // 11 October 2013
    $datetime2 = new DateTime(date('Y-m-d H:i:s')); // 13 October 2013

    $interval = $datetime1->diff($datetime2);
    if($interval->i<10){
        echo "<script>busyvpn();</script>";
        ob_flush();ob_flush();
        die;
    }
    else{
        $query = "DELETE FROM semaforo WHERE Procedure_Type_Id = 6";
        $cls_db->ExecuteQuery($query);
    }
}

$query = "INSERT INTO semaforo (Procedure_Type_Id, Datetime, User_Id) ".
"VALUES (6, '". date('Y-m-d H:i:s')."', ".$_SESSION['aut_progr'].")";
$cls_db->ExecuteQuery($query);

require CONFIG_ROOT."/_aciServer.php";
if(strpos(shell_exec(ACICHECK_CMD),ACISERVERIP)){
    shell_exec(ACIVPN_KILL);
    sleep(2);
}

if(strpos(shell_exec(ACICHECK_CMD),ACISERVERIP)===false){
    shell_exec(ACIVPN_CMD);
    sleep(3);

    if(strpos(shell_exec(ACICHECK_CMD),ACISERVERIP)===false){
        echo "<script>novpn();</script>";
        ob_flush();ob_flush();
        die;
    }
}

$soap = new SoapCMN();

for( $i=0; $i < $num_atti; $i++ )
{
    echo "<script>update(".ceil($i*100/$num_atti).");</script>";

    flush();
    ob_flush();
    flush();
    ob_flush();
    //var_dump($result[$i]);
    //die;

    $array_value = array();

    $array_value[0] = $result[$i]["Utente_Comune_ID"];
    $array_value[1] = $result[$i]["Partita_Comune_ID"];
    $array_value[2] = 0;
    $array_value[3] = $result[$i]["Nome"];
    $array_value[4] = $result[$i]["Cognome"];
    $array_value[5] = $result[$i]["Ditta"];


    $check = $cls_check->setAct($result[$i]);

    if($check!==true)
    {
        $array_value_2[0] = strtoupper($check);
        $array_value_2[1] = $result[$i]["Info_Cartella"];
        $y = $pdf->setRow($array_value, "no", $styleDash ,null, 0, $array_width);
        $y = $pdf->setRow($array_value_2, "down", $styleDash ,null, 0, $array_width_2);
        continue;
    }

    if(!$cls_check->checkActPayments(0))
    {
        $array_value_2[0] = "CHECK PAGAMENTO NEGATIVO";
        $array_value_2[1] = $result[$i]["Info_Cartella"];
        $y = $pdf->setRow($array_value, "no", $styleDash ,null, 0, $array_width);
        $y = $pdf->setRow($array_value_2, "down", $styleDash ,null, 0, $array_width_2);
        continue;
    }else  if(!$cls_check->checkDatesLimit(0)){
        $array_value_2[0] = "CHECK TERMINI NEGATIVO";
        $array_value_2[1] = $result[$i]["Info_Cartella"];
        $y = $pdf->setRow($array_value, "no", $styleDash ,null, 0, $array_width);
        $y = $pdf->setRow($array_value_2, "down", $styleDash ,null, 0, $array_width_2);
        continue;
    }


    $query = "SELECT ID, Data_Visura, Targa, ProgressivoVisura FROM veicoli where CC_Comune = '".$c."' AND Utente_ID = ".$result[$i]["Utente_ID"]." AND Data_Visura = (SELECT MAX(Data_Visura) FROM veicoli WHERE CC_Comune = '".$c."' AND Utente_ID = ".$result[$i]["Utente_ID"].")";
    $resultControlVisura = $cls_db->getResults($cls_db->ExecuteQuery($query));

    $datePlussDay = "";

    $dataVisPrec = isset($resultControlVisura[0]["Data_Visura"])?$resultControlVisura[0]["Data_Visura"]:null;
    if($dataVisPrec!=null)
    {
        $datePlussDay = new cls_DateTime($dataVisPrec,"DB",false);
        $dataVisuraPrecedente = $datePlussDay->GetDate("IT");
        $number = $cls_help->getVar("min_day");
        if(!is_numeric($number)) $number = 0;
        $datePlussDay->AddDay($number);
    }
    else{
        $datePlussDay = new cls_DateTime(date("Y-m-d"),"DB",false);
        $dataVisuraPrecedente = "PRIMA VISURA";
        $datePlussDay->AddDay(-1);
    }

    //var_dump($soap->getAllFunction());
    //echo $dataVisPrec." --- ".$datePlussDay->GetDate()." --- ".$datePlussDay->CompareDate(date("Y-m-d"),"DB","<")."<br>";

    if( $datePlussDay->CompareDate("DB","<",date("Y-m-d"))){

        $cls_db->Start_Transaction();
        $cls_db->Begin_Transaction();

        //echo "<br>CF: ".$result[$i]["Codice_Fiscale"]." --- PI: ".$result[$i]["Partita_Iva"]." --- Nome: ".$result[$i]["Nome"]." --- Cognome: ".$result[$i]["Cognome"]." --- Ditta: ".$result[$i]["Ditta"]." --- ID: ".$result[$i]["Utente_ID"]."<br>";




        //$y = $pdf->setRow($array_value, "down", $styleDash ,null, 0, $array_width);


        if($result[$i]["Codice_Fiscale"] != "" && $result[$i]["Codice_Fiscale"] != null)
        {
            //echo "<h1>".$result[$i]["Codice_Fiscale"]."</h1>";
            $resultSoap = $soap->SearchForCF(trim((string)$result[$i]["Codice_Fiscale"]));//"VRDGLC89A16D969M","TGLFNC75M23B963F"
        }
        else if($result[$i]["Partita_Iva"] != "" && $result[$i]["Partita_Iva"] != "00000000000"){
            $resultSoap = $soap->SearchForPI($result[$i]["Partita_Iva"]);//"01338160995"

        }else{
            $array_value[2] = 0;

            if($result[$i]["Genere"] == "D") {
                $array_value_2[0] = "NEGATIVO MANCA P.I.";
                $array_value_2[1] = $result[$i]["Info_Cartella"];
                //$Tipo_Persona = "Giuridica";
                //$resultSoap = $soap->SearchForNominative($Tipo_Persona,$result[$i]["Ditta"]);
            }
            else {
                $array_value_2[0] = "NEGATIVO MANCA C.F.";
                $array_value_2[1] = $result[$i]["Info_Cartella"];
                //$Tipo_Persona = "Fisica";
                //$resultSoap = $soap->SearchForNominative($Tipo_Persona,$result[$i]["Cognome"],$result[$i]["Nome"]);
            }
            //alert()
            $y = $pdf->setRow($array_value, "no", $styleDash ,null, 0, $array_width);
            $y = $pdf->setRow($array_value_2, "down", $styleDash ,null, 0, $array_width_2);
            continue;
        }

        if(isset($resultSoap->DatiRisposta->ElencoVeicoli->Veicolo))
        {
            $obj_veicoli = $resultSoap->DatiRisposta->ElencoVeicoli->Veicolo;
            if(gettype($obj_veicoli) == "array") {
                $array_value[2] = count($obj_veicoli);
                $array_value_2[0] = "POSITIVO";
                $array_value_2[1] = $result[$i]["Info_Cartella"];
            }
            else if(isset($obj_veicoli->Targa)) {
                if($obj_veicoli->Targa != null) {
                    $array_value[2] = 1;
                    $array_value_2[0] = "POSITIVO";
                    $array_value_2[1] = $result[$i]["Info_Cartella"];
                }
                else {
                    $array_value[2] = 0;
                    $array_value_2[0] = "NESSUN RECORD TROVATO";
                    $array_value_2[1] = $result[$i]["Info_Cartella"];
                }
            }
            else {
                $array_value[2] = 0;
                $array_value_2[0] = "NESSUN RECORD TROVATO";
                $array_value_2[1] = $result[$i]["Info_Cartella"];
            }

            $y = $pdf->setRow($array_value, "no", $styleDash ,null, 0, $array_width);
            $y = $pdf->setRow($array_value_2, "down", $styleDash ,null, 0, $array_width_2);
        }
        else{
            $array_value[2] = 0;
            $array_value_2[0] = "NESSUN RECORD TROVATO";
            $array_value_2[1] = $result[$i]["Info_Cartella"];
            $y = $pdf->setRow($array_value, "no", $styleDash ,null, 0, $array_width);
            $y = $pdf->setRow($array_value_2, "down", $styleDash ,null, 0, $array_width_2);
        }




        if(isset($obj_veicoli)) {
            $a_veicoli = array();


            if(isset($obj_veicoli->Targa)){
                $a_veicoli[0] = $obj_veicoli;
            }
            else if(isset($obj_veicoli[0]))
            {
                $a_veicoli = $obj_veicoli;
            }


            foreach ($a_veicoli as $value) {

                /*if(!isset($value->Targa))
                    continue;*/
                $where = array();

                for($x=0; $x<count($resultControlVisura); $x++)
                {
                    if($resultControlVisura[$x]["Targa"] == $value->Targa){
                        $where = array("ID" => $resultControlVisura[$x]["ID"]);
                        break;
                    }
                }

                $save = new stdClass();

                $save->Data_Visura = date("Y-m-d");
                $save->Utente_ID = $result[$i]["Utente_ID"];
                $save->ProgressivoVisura = 1;
                $save->CC_Comune = $c;

                $save->ProgressivoLista = $value->ProgressivoLista;
                $save->ProvinciaCompetenza = $value->ProvinciaCompetenza;
                $save->Targa = $value->Targa;
                $save->SerieTarga = $value->SerieTarga;
                $save->StatoVeicolo = $value->StatoVeicolo;
                $save->Causale = $value->Causale;
                $save->FlagGiuridico = $value->FlagGiuridico;
                $save->DataPrimaImmatricolazione = $value->DataPrimaImmatricolazione;
                $save->CodiceUltimaFormalita = $value->CodiceUltimaFormalita;
                $save->DescrizioneUltimaFormalita = $value->DescrizioneUltimaFormalita;
                $save->DataUltimaFormalita = $value->DataUltimaFormalita;

                $save->Telaio = $value->DatiTecnici->Telaio;
                $save->Fabbrica = $value->DatiTecnici->Fabbrica;
                $save->Tipo = $value->DatiTecnici->Tipo;
                $save->Serie = $value->DatiTecnici->Serie;
                $save->ClasseVeicolo = $value->DatiTecnici->ClasseVeicolo;

                $save->Cognome = $value->Soggetto->Cognome;
                $save->Nome = $value->Soggetto->Nome;
                $save->DataNascita = $resultSoap->DatiRichiesta->DataNascita;
                //$result[$count]["ComuneNascita"] = $value->Soggetto->ComuneNascita;//
                $save->CodiceFiscale = $value->Soggetto->CodiceFiscale;
                $save->PartitaIva = $value->Soggetto->PartitaIva;
                $save->ProvinciaResidenza = $value->Soggetto->ProvinciaResidenza;
                $save->CodiceRuoloSoggetto = $value->Soggetto->CodiceRuoloSoggetto;
                $save->DescrizioneRuoloSoggetto = $value->Soggetto->DescrizioneRuoloSoggetto;
                $save->DataRiferimentoRuoloSoggetto = $value->Soggetto->DataRiferimentoRuoloSoggetto;

                $check = true;
                if(count($where) > 0) $check = $cls_db->DbSave($cls_utils->GetObjectQuery((array) $save,"veicoli",$where));
                else $check = $cls_db->DbSave($cls_utils->GetObjectQuery((array) $save,"veicoli"));


                if(!$check)
                {
                    $cls_db->Rollback();
                    $error = 1;
                    $msg = "Errore, impossibile inserire i dati";
                    break;
                }
            }
        }
        $cls_db->End_Transaction();
    }
    else{
      $array_value[0] = $result[$i]["Utente_Comune_ID"];
      $array_value[1] = $result[$i]["Partita_Comune_ID"];
      $array_value[2] = 0;
      $array_value[3] = $result[$i]["Nome"];
      $array_value[4] = $result[$i]["Cognome"];
      $array_value[5] = $result[$i]["Ditta"];
      $array_value_2[0] = "EFFETTUATA IN DATA: ".$dataVisuraPrecedente;
      $array_value_2[1] = $result[$i]["Info_Cartella"];

      $y = $pdf->setRow($array_value, "no", $styleDash ,null, 0, $array_width);
      $y = $pdf->setRow($array_value_2, "down", $styleDash ,null, 0, $array_width_2);
    }
}//CHIUSURA ATTI
$pdf->Output( $pathPdf."/resoconto_visura.pdf" , 'F');

shell_exec(ACIVPN_KILL);
$query = "DELETE FROM semaforo WHERE Procedure_Type_Id = 6";
$cls_db->ExecuteQuery($query);

if($num_atti == 0)
{
    echo "<script>nessun_risultato();</script>";
}

else{
    $storico->insRow('E', $storico_msg." per ente ".$nome_ente."[".$c."]");
    echo "<script>fine('Elaborazione completata');</script>";
}


?>

<?php include(INC."/footer.php"); ?>
