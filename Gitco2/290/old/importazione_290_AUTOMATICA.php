<?php
set_time_limit(0);

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
include(INC."/menu.php");
include_once(CLS."/cls_Utils.php");
include_once(CLS."/cls_DateTimeInLine.php");
include_once CLS . "/cls_CoazioneUtils.php";

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

$cls_coaz = new cls_Coazione();
$cls_date = new cls_DateTimeI("IT",false);
$cls_utils = new cls_Utils();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');
$progr_n0 = $cls_help->getVar('id_n0');


$numero_ruolo = $cls_help->getVar('numero_ruolo');
if($numero_ruolo==null)$numero_ruolo=0;

//$cls_split_payment = new cls_split_payment();
$query = "SELECT * FROM split_payment_parameters WHERE cc = '".$c."' OR cc = '****' ORDER BY cc DESC, id DESC LIMIT 1";//$cls_split_payment->getParametersQuery($c);
//$result = mysql_query($query);
$a_splitParams = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"split_payment_parameters");//mysql_fetch_array($result, MYSQLI_ASSOC);
$a_split = $cls_coaz->getLineByPriority($a_splitParams);

//print_r($a_split);
$a_splitNumber = array();
for($i=0;$i<count($a_split);$i++){
    for($y=1;$y<=6;$y++){
        switch($a_split[$i]['categories'][$y]){
            case 1://imposta principale
                $a_splitNumber['imposta_principale'] = "Split_Payment".$a_split[$i]['split_number'];
                break;
            case 10:
                $a_splitNumber['interessi'] = "Split_Payment".$a_split[$i]['split_number'];
                break;
            case 2://spese accertamento
                $a_splitNumber['spese_notifica'] = "Split_Payment".$a_split[$i]['split_number'];
                break;
            case 12://spese ricerca
                $a_splitNumber['spese_ricerca'] = "Split_Payment".$a_split[$i]['split_number'];
                break;
            case 13://spese ricerca
                $a_splitNumber['tributo_provinciale'] = "Split_Payment".$a_split[$i]['split_number'];
                break;
            case 15://spese ricerca
                $a_splitNumber['eca'] = "Split_Payment".$a_split[$i]['split_number'];
                break;

        }
    }

}
$query = "SELECT * FROM 290_n0_n9 WHERE ID = '".$progr_n0."'";
$duenovanta = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"290_n0_n9");// new N0N9( $progr_n0 );
$num_N1 = $duenovanta["Record_N1"];

flush();
ob_flush();

?>

<script>
$(document).ready(function(){

	$('#descr_ruolo').focus();

});

function inizio_estrazione_dati()
{
    $( "#form_importazione" ).hide();
    $('#progressbar_importazione').progressbar();
    $( "#barlabel_importazione" ).text("Caricamento dati...");
}

function fine_estrazione_dati()
{
    $( "#progressbar_importazione" ).progressbar({value: 100 });
    $( "#barlabel_importazione" ).text("Fine Caricamento!");
}

function inizio()
{
	//$( "#form_importazione" ).hide();
	$('#progressbar').progressbar();
	$( "#barlabel" ).text("Inizio Importazione...");
}

function fine()
{
	$( "#progressbar" ).progressbar({value: 100 });
	$( "#barlabel" ).text("Fine Importazione!");
	
setTimeout(function() {
			
		$( "div#importazione" ).append("<input type=button name=riepilogo class='btn btn-danger' value='Riepilogo Importazione' onclick='riepilogo();'>");
	
	}, 1000);

}

function riepilogo()
{
	location.href = "riepilogo_290.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&id_n0=<?php echo $progr_n0; ?>";
}

function successivo()
{
	location.href = "importazione_290.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&id_n0=<?php echo $progr_n0; ?>&numero_ruolo=<?php echo ($numero_ruolo+1); ?>";
}

function ruolo_next()
{
	$( "#progressbar" ).progressbar({value: 100 });
	$( "#barlabel" ).text("Ruolo Importato con successo!");
	setTimeout(function() {
		
		$( "#progressbar" ).hide();
		$( "#form_importazione" ).show();
		
		$( "div#importazione" ).append("<input type=button name=successivo class=button_azzurro value='Ruolo Successivo' onclick='successivo();'>");
	
	}, 2000);
}

function importa(){
    
    if($('#descr_ruolo').val()==""){
        alert("Inserire la descrizione del ruolo da importare!!");
        return false;
    }
    else{
        $('#form_importazione').submit();
    }
}

var blocca_menu = 1;
</script>

        <div class="row justify-content-md-center ">
            <div class="col col-md-auto text_center">
                <span class="titolo font18">Controlli Importazione</span>
            </div>
        </div>

		<div class="row" style="margin-top: 3%;">
            <div class="col-lg-10 col-lg-offset-1">
                <div class="table_interna text_center" id="progressbar_ini" style="height:55px;"><div class="text_center" id="barlabel_ini"></div></div>
            </div>
        </div>

        <div class="row justify-content-md-center " style="margin-top: 5%;">
            <div class="col col-md-auto text_center">
                <span class="titolo font18">Importazione File 290</span>
            </div>
        </div>

		<div class="row">
            <div class="col-lg-10 col-lg-offset-1">
                <form id=form_importazione name=form_importazione action="importazione_290.php?posted=true" method=post accept-charset=utf-8>
                    <input type=hidden name=id_n0 value="<?php echo $progr_n0; ?>">
                    <input type="hidden" name="c" value="<?php echo $c?>">
                    <input type="hidden" name="a" value="<?php echo $a?>">

                    Descrizione Ruolo <?php echo ($numero_ruolo+1)."/".$num_N1; ?>

                    <br><br>

                    <input type=text id=descr_ruolo name=descrizione_ruolo size=40 value="">

                    <br><br><br>

                    <input id=submit_import type=button onclick="importa();" class="btn btn-primary" value="Avvia Importazione">

                </form>

                <br><br>
                <div class="table_interna text_center" id="progressbar_importazione" style="height:55px;"><div class="text_center" id="barlabel_importazione"></div></div>
                <br><br>
                <div class="table_interna text_center" id="progressbar" style="height:55px;"><div class="text_center" id="barlabel"></div></div>
                <br><br>
                <div id=importazione></div>
            </div>
        </div>


<script>

$('#progressbar_ini').progressbar({value: 100 });
$( "#barlabel_ini" ).text("Controlli effettuati!");

<?php $posted = $cls_help->getVar('posted');  if($posted!=true){ ?>
$("#descr_ruolo").val("Ruolo CDS 2016");
$("#submit_import").trigger("click");
<?php } ?>
</script>

<?php



if($posted==true)
{
	flush();
	ob_flush();
	flush();
	ob_flush();
	echo "<script>inizio_estrazione_dati()</script>";
	sleep(2);
	flush();
	ob_flush();
	flush();
	ob_flush();
	
$descrizione_ruolo_N1 = $cls_help->getVar('descrizione_ruolo');

$omoN2 = array();
$omoN3 = array();

// = "SELECT * FROM 290_n0_n9 WHERE ID = '".$progr_n0."'";
//$duenovanta = $cls_coaz->getData_290($progr_n0);;//new N0N9( $progr_n0 );

    $query = "SELECT * FROM 290_n0_n9 WHERE ID = '".$progr_n0."'";
    $duenovanta = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"290_n0_n9");

    $query = "SELECT ID FROM 290_n1_n5 WHERE N0_ID = '".$duenovanta["ID"]."'";
    $n1id = $cls_db->getResults($cls_db->ExecuteQuery($query));

    $Tot100 = 0;
    $count = 0;
    for( $i=0; $i<(int)$duenovanta["Record_N1"]; $i++){
        $query = "SELECT * FROM 290_n1_n5 WHERE ID = '".$n1id[$i]['ID']."'";
        $duenovanta["n1"][$i] = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"290_n1_n5");//new N1N5($n1id[$i]['ID']);

        $Tot100 += $duenovanta["n1"][$i]["Record_N2"];
    }

    for( $i=0; $i<(int)$duenovanta["Record_N1"]; $i++)
    {


        $query = "SELECT ID FROM 290_n2 WHERE N1_ID = '" . $duenovanta["n1"][$i]['ID'] . "' AND N0_ID = '".$duenovanta["n1"][$i]['N0_ID']."'";
        $n2id = $cls_db->getResults($cls_db->ExecuteQuery($query));

        /*echo "<h1>".$query."</h1>";
        die;*/
        for( $x=0; $x<$duenovanta["n1"][$i]["Record_N2"]; $x++)
        {
            $query = "SELECT * FROM 290_n2 WHERE ID = '".$n2id[$x]['ID']."'";
            $duenovanta["n1"][$i]["n2"][$x] = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"290_n2");//new N2($n2id[$i]['ID']);


            $query = "SELECT * FROM 290_n3 WHERE Codice_Partita = '".$duenovanta["n1"][$i]["n2"][$x]['Codice_Partita']."' AND N0_ID = '".$duenovanta["n1"][$i]["n2"][$x]['N0_ID']."'";
            $a_n3 = $cls_db->getResults($cls_db->ExecuteQuery($query));//select_mysql_array("ID", "290_n3","Codice_Partita = '".$val['Codice_Partita']."' AND N0_ID = '".$val['N0_ID']."'");
            $duenovanta["n1"][$i]["n2"][$x]["num_n3"] = count($a_n3);


            for( $y=0; $y<count($a_n3); $y++)
                $duenovanta["n1"][$i]["n2"][$x]["n3"][$y] = $a_n3[$y];


            $query = "SELECT * FROM 290_n4 WHERE Codice_Partita = '".$duenovanta["n1"][$i]["n2"][$x]['Codice_Partita']."' AND N0_ID = '".$duenovanta["n1"][$i]["n2"][$x]['N0_ID']."'";
            $a_n4 = $cls_db->getResults($cls_db->ExecuteQuery($query));//select_mysql_array("ID", "290_n4","Codice_Partita = '".$val['Codice_Partita']."' AND N0_ID = '".$val['N0_ID']."'");
            $duenovanta["n1"][$i]["n2"][$x]["num_n4"] = count($a_n4);


            for( $z=0; $z<count($a_n4); $z++)
                $duenovanta["n1"][$i]["n2"][$x]["n4"][$z] = $a_n4[$z];
            //var_dump($a_n4);
            //die;


            flush();
            ob_flush();
            echo "<script>$( \"#progressbar_importazione\" ).progressbar({value: " .intval($count*100/$Tot100). " });$( \"#barlabel_importazione\" ).text(" .intval($count*100/$Tot100). "+'%');</script>";
            flush();
            ob_flush();

            $count++;
        }
    }
    flush();
    ob_flush();
    echo "<script>fine_estrazione_dati();</script>";
    flush();
    ob_flush();

$data_fornitura = $cls_date->Get_DateNewFormat($duenovanta["Data_Invio_Fornitura"],"DB");// from_mysql_date($duenovanta->Data_Invio_Fornitura);

    flush();
    ob_flush();
    flush();
    ob_flush();
    echo "<script>inizio();</script>";
    sleep(2);
    flush();
    ob_flush();
    flush();
    ob_flush();


	$enne1 = $duenovanta["n1"][$numero_ruolo];
	$enne4 = $enne1["n2"][0]["n4"];
	
	$date_time = date("Y-m-d H:i:s");	
	if($enne1["Ruolo"]==1)
		$ruolo_N1 = "Coattivo";
	else 
		$ruolo_N1 = "Ordinario";
	
	//CREAZIONE ARRAY CAMPI $field_ruolo E VALORI $value_ruolo PER LA TABELLA ruolo
	//$field_ruolo = array();
	//$value_ruolo = array();

	//$comune_id = single_answer_query("SELECT MAX(Comune_ID) as CI FROM ruolo WHERE CC = '".$c."'");
    $query = "SELECT MAX(Comune_ID) as CI FROM ruolo WHERE CC = '".$c."'";
    $comune_id = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"ruolo")["CI"];

	/*$field_ruolo[] = 'Data_Inserimento';		$value_ruolo[] = $date_time;
	$field_ruolo[] = 'Comune_ID'; 				$value_ruolo[] = $comune_id+1;
	$field_ruolo[] = 'CC'; 						$value_ruolo[] = $c;
	$field_ruolo[] = 'Ruolo'; 					$value_ruolo[] = $ruolo_N1;
	$field_ruolo[] = 'Data_Fornitura'; 			$value_ruolo[] = $duenovanta->Data_Invio_Fornitura;
	$field_ruolo[] = 'Progr_Fornitura'; 		$value_ruolo[] = $enne1->Progressivo_Minuta;
	$field_ruolo[] = 'Descrizione'; 			$value_ruolo[] = $descrizione_ruolo_N1;
	$field_ruolo[] = 'Num_Rate'; 				$value_ruolo[] = $enne1->Num_Rate;
	$field_ruolo[] = 'Num_Ruolo';				$value_ruolo[] = $enne1->Num_Ruolo;
	$field_ruolo[] = 'Tipo_Compenso';			$value_ruolo[] = $enne1->Tipo_Compenso;
	$field_ruolo[] = 'Codice_Sede'; 			$value_ruolo[] = $enne1->Codice_Sede;
	$field_ruolo[] = 'ICIAP'; 					$value_ruolo[] = $enne1->Ruolo_ICIAP;
	$field_ruolo[] = 'Num_Convenzione';			$value_ruolo[] = $enne1->Num_Convenzione;
	$field_ruolo[] = 'Flag_Articolo';			$value_ruolo[] = $enne1->Flag_Articoli;*/

    $field_ruolo["Data_Inserimento"] = $date_time;
    $field_ruolo["Comune_ID"] = $comune_id+1;
    $field_ruolo["CC"] = $c;
    $field_ruolo["Ruolo"] = $ruolo_N1;
    $field_ruolo["Data_Fornitura"] = $duenovanta["Data_Invio_Fornitura"];
    $field_ruolo["Progr_Fornitura"] = $enne1["Progressivo_Minuta"];
    $field_ruolo["Descrizione"] = $descrizione_ruolo_N1;
    $field_ruolo["Num_Rate"] = $enne1["Num_Rate"];
    $field_ruolo["Num_Ruolo"] = $enne1["Num_Ruolo"];
    $field_ruolo["Tipo_Compenso"] = $enne1["Tipo_Compenso"];
    $field_ruolo["Codice_Sede"] = $enne1["Codice_Sede"];
    $field_ruolo["ICIAP"] = $enne1["Ruolo_ICIAP"];
    $field_ruolo["Num_Convenzione"] = $enne1["Num_Convenzione"];
    $field_ruolo["Flag_Articolo"] = $enne1["Flag_Articoli"];

    //print_r($cls_utils->GetObjectQuery($field_ruolo,"ruolo"));

    $new_ID_ruolo = $cls_db->DbSave($cls_utils->GetObjectQuery($field_ruolo,"ruolo"));

	//$new_ID_ruolo = table_insert_record("ruolo", $field_ruolo, $value_ruolo);

	if($new_ID_ruolo==0)
	{
		alert("Errore nella creazione del Ruolo! Procedura Annullata!");
		die;
	}
	
	//$partita = new partita(null, $c);
	$query_codici = "SELECT * FROM codice_tributo";
	$array_codici = $cls_db->getResults($cls_db->ExecuteQuery($query_codici));// mysql_array($query_codici);
	
	//CICLO ANAGRAFICHE INTESTATARI N2
	for($y=0;$y<$duenovanta["Record_N2"];$y++)
	{
	    $info_cartella = "";
		$importo = 0.00;
		$interessi = 0.00;
		$maggiorazione_sanzione = 0.00;
		$spese_not = 0.00;
		$spese_precedenti = 0.00;
		$control_ing = 0;
		$control_una_volta = 0;
		$numero_ing = 0;
		$control_rate = 0;
		$importi_rate = "";
		$scadenze_rate = "";
		
		set_time_limit(60);
		
		flush();
		ob_flush();
		echo "<script>$( \"#progressbar\" ).progressbar({value: " .intval($y*100/$duenovanta["Record_N2"]). " });$( \"#barlabel\" ).text(" .intval($y*100/$duenovanta["Record_N2"]). "+'%');</script>";
		flush();
		ob_flush();
		
		$enne2 = $enne1["n2"][$y];
		
		//$field_partita = array();
		//$value_partita = array();
		
		//$field_partita[0] = 'Coo_ID'; 					$value_partita[0] = "";
        $field_partita["Coo_ID"] = "";
		
		//$partita_comune_id = single_answer_query("SELECT MAX(Comune_ID) FROM partita_tributi WHERE CC = '".$c."'");

		$query = "SELECT MAX(Comune_ID) as CI FROM partita_tributi WHERE CC = '".$c."'";
        $partita_comune_id = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"partita_tributi")["CI"];
		
		/*$field_partita[] = 'CC'; 						$value_partita[] = $c;
		$field_partita[] = 'Comune_ID'; 				$value_partita[] = $partita_comune_id+1;
		$field_partita[] = 'Ruolo_ID'; 					$value_partita[] = $new_ID_ruolo;
		$field_partita[] = 'Coo_Tipo'; 					$value_partita[] = $enne2->Cointestatari;*/


        $field_partita["CC"] = $c;
        $field_partita["Comune_ID"] = $partita_comune_id+1;
        $field_partita["Ruolo_ID"] = $new_ID_ruolo;
        $field_partita["Coo_Tipo"] = $enne2["Cointestatari"];
		
		if($enne2["Flag_Partita"] == "no")
		{
			$query = "UPDATE 290_n2 SET Flag_Importazione = 'Scarto', Flag_Partita = 'no' WHERE ID = '".$enne2["ID"]."'";
			//safe_query($query);
            $cls_db->ExecuteQuery($query);
			continue;
		}

		$sesso_N2 = $enne2["Sesso"];
		if($enne2["Natura_Giuridica"]==1  )
		{
			$ctrl_sesso = number_format(substr($enne2["Codice_Fiscale"], 9,2));
			if( $ctrl_sesso > 40 )
			    $sesso_N2 = "F";
			else
			    $sesso_N2 = "M";
		}
		
		if( $enne2["Flag_Partita"] == "omoN2" )
		{
            if(!$sesso_N2!="")
                $sesso = "D";
            else $sesso = $sesso_N2;

			$control_omo = $cls_utils->check_omonimi($sesso, $enne2["Codice_Fiscale"], $enne2["Ditta"], $enne2["Codice_Fiscale"], $enne2["Nome"], $enne2["Cognome"], $enne2["CC_Nascita"], $enne2["Data_Nascita"], $c );

			$pos = strpos($control_omo, 'dubbi');
			$pos_omo = strpos($control_omo, 'omo');
			if($pos===false)
			{

				$omo = explode(" ",$control_omo);
				$utente_ID = $omo[1];
			}
			else
			{
				if($pos_omo===false)
				{
					$query = "UPDATE 290_n2 SET Flag_Importazione = 'Scarto', Flag_Partita = 'no' WHERE ID = '".$enne2->ID."'";
					//safe_query($query);
                    $cls_db->ExecuteQuery($query);
					continue;
				}
				else 
				{
					$omo_id = substr($control_omo, 0, $pos);
				
					$omo = explode(" ",$omo_id);
					$utente_ID = $omo[1];
					
				}								
			}
			
			//$utente_ID = single_answer_query("SELECT ID FROM utente WHERE Comune_ID = '".$utente_ID."' AND CC_Comune = '".$c."'");

			$query = "SELECT ID FROM utente WHERE Comune_ID = '".$utente_ID."' AND CC_Comune = '".$c."'";
            $utente_ID = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"utente")["ID"];
			
			//$field_partita[] = 'Utente_ID'; 			$value_partita[] = $utente_ID;

            $field_partita["Utente_ID"] = $utente_ID;

            $query = "SELECT * FROM utente WHERE ID = '".$utente_ID."' AND CC_Comune = '".$c."'";
            $utente_omo = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"utente");
			//$utente_omo = new utente($utente_ID, $c);

            //$this->Residenza = new indirizzo( $progr , 'res' , $c );
            $query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$utente_ID."' AND Tipo = 'res'";
            $utente_omo["Residenza"] = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"indirizzo");

            if($utente_omo["Residenza"]["Via_ID"]!=1)
            {
                $query = "SELECT * FROM toponimo WHERE ID = '".$utente_omo["Residenza"]["Via_ID"]."' AND CC_Comune = '".$c."'";
                $utente_omo["Residenza"]["Toponimo"] = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"toponimo");
            }

            else if($utente_omo["Residenza"]["Via_Cap_ID"]!=1)
            {
                $query = "SELECT * FROM toponimi_cappati WHERE ID = '".$utente_omo["Residenza"]["Via_Cap_ID"]."'";
                $utente_omo["Residenza"]["Toponimo"] = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"toponimi_cappati");
            }
            else
                $utente_omo["Residenza"]["Toponimo"] = null;


            //$this->Domicilio = new indirizzo( $progr , 'dom' , $c );
            $query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$utente_ID."' AND Tipo = 'dom'";
            $utente_omo["Domicilio"] = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"indirizzo");

            if($utente_omo["Domicilio"]["Via_ID"]!=1)
            {
                $query = "SELECT * FROM toponimo WHERE ID = '".$utente_omo["Domicilio"]["Via_ID"]."' AND CC_Comune = '".$c."'";
                $utente_omo["Domicilio"]["Toponimo"] = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"toponimo");
            }

            else if($utente_omo["Domicilio"]["Via_Cap_ID"]!=1)
            {
                $query = "SELECT * FROM toponimi_cappati WHERE ID = '".$utente_omo["Domicilio"]["Via_Cap_ID"]."'";
                $utente_omo["Domicilio"]["Toponimo"] = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"toponimi_cappati");
            }
            else
                $utente_omo["Domicilio"]["Toponimo"] = null;
			
			$residenza_omo = $utente_omo["Residenza"];
			$domicilio_omo = $utente_omo["Domicilio"];
			$note_omo = $utente_omo["Note"];
				
			//$ultimo_ruolo = single_answer_query("SELECT MAX(Comune_ID) FROM ruolo WHERE CC = '".$c."'");
			//$ultima_partita = single_answer_query("SELECT MAX(Comune_ID) FROM partita_tributi WHERE CC = '".$c."'");

            $ultimo_ruolo = $cls_db->getArrayLineNull($cls_db->ExecuteQuery("SELECT MAX(Comune_ID) as ID FROM ruolo WHERE CC = '".$c."'"),"ruolo")["ID"];
            $ultima_partita = $cls_db->getArrayLineNull($cls_db->ExecuteQuery("SELECT MAX(Comune_ID) as ID FROM partita_tributi WHERE CC = '".$c."'"),"partita_tributi")["ID"];
			
			//$field_utente = array();
			//$value_utente = array();
							
			$note290 = $note_omo."Ruolo ID ".$ultimo_ruolo." Partita ID ".($ultima_partita+1)." Intestatario";
			
			if($enne2["Indirizzo_Res"] != $residenza_omo["Toponimo"]["Nome"] || $enne2["Civico_Res"] != $residenza_omo["Civico"])
			{
				if($enne2["Civico_Res"] == 0)$civico_res = "";
				else $civico_res = $enne2["Civico_Res"];
				if($enne2["Interno_Res"] == 0)$interno_res = "";
				else $interno_res = $enne2["Interno_Res"];
			
				$note290 .= "\nIndirizzo di residenza da verificare: ";
				$note290 .= $enne2["Indirizzo_Res"]." civ. ".$civico_res." esp. ".$enne2["Lettera_Civico_Res"]." int. ".$interno_res;
			}
				
			if($enne2["Indirizzo_Dom"]!="")
			{
				if($enne2["Civico_Dom"] == 0)$civico_dom = "";
				else $civico_dom = $enne2["Civico_Dom"];
				if($enne2["Interno_Dom"] == 0)$interno_dom = "";
				else $interno_dom = $enne2["Interno_Dom"];
			
				$note290 .= "\nIndirizzo di domicilio da verificare: ";
				$note290 .= $enne2["Indirizzo_Dom"]." civ. ".$civico_dom." esp. ".$enne2["Lettera_Civico_Dom"]." int. ".$interno_dom;
			}
			
			$note290 .="\n**\n";
			//$value_utente[] = $note290;

            $field_utente['Note'] = $note290;

            $cls_db->DbSave($cls_utils->GetObjectQuery($field_utente,"utente"));
			
			//table_update_record("utente", $field_utente, $value_utente, "ID", $utente_ID);
			
			$query = "UPDATE 290_n2 SET Flag_Importazione = 'N2', Flag_Partita = 'si' WHERE ID = '".$enne2["ID"]."'";
			//safe_query($query);
            $cls_db->ExecuteQuery($query);
				
		}
		else 
		{
			if	($enne2["Flag_Partita"] == "Importare")
			{
				$query = "UPDATE 290_n2 SET Flag_Importazione = 'ok', Flag_Partita = 'si' WHERE ID = '".$enne2["ID"]."'";
				//safe_query($query);
                $cls_db->ExecuteQuery($query);
			}
		//CREAZIONE ARRAY CAMPI $field_utente E VALORI $value_utente PER LA TABELLA utente da N2
		//$field_utente = array();
		//$value_utente = array();
		
		//$field_utente[] = 'CC_Comune'; 				$value_utente[] = $c;

        $field_utente["CC_Comune"] = $c;

		if($enne2["Natura_Giuridica"]==1)
		{
			/*$field_utente[] = 'Genere'; 				$value_utente[] = $sesso_N2;
			$field_utente[] = 'Cognome'; 				$value_utente[] = $enne2->Cognome;
			$field_utente[] = 'Nome'; 					$value_utente[] = $enne2->Nome;
			$field_utente[] = 'CC_Nascita'; 			$value_utente[] = $enne2->CC_Nascita;*/

            $field_utente["Genere"] = $sesso_N2;
            $field_utente["Cognome"] = $enne2["Cognome"];
            $field_utente["Nome"] = $enne2["Nome"];
            $field_utente["CC_Nascita"] = $enne2["CC_Nascita"];
		
			if(substr($enne2["CC_Nascita"],0,1)!="Z")
			{
				//$comune = new comune($enne2["CC_Nascita"]);
                $query = "SELECT * FROM comuni_lista WHERE Com_Codice_Catastale = '".$enne2["CC_Nascita"]."'";
                $comune = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"comuni_lista");
		
				$paese_nascita = "Italia";
				$comune_nascita = $comune["Com_Nome"];

                $query = "SELECT * FROM province_lista WHERE Pro_Codice='".$comune['Com_Codice_Provincia']."'";
                $result = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"province_lista");

				$provincia_nascita = $result["Pro_Sigla"];
		
				/*$field_utente[] = 'Paese_Nascita'; 			$value_utente[] = $paese_nascita;
				$field_utente[] = 'Comune_Nascita'; 		$value_utente[] = $comune_nascita;
				$field_utente[] = 'Provincia_Nascita'; 		$value_utente[] = $provincia_nascita;*/

				$field_utente["Paese_Nascita"] = $paese_nascita;
                $field_utente["Comune_Nascita"] = $comune_nascita;
                $field_utente["Provincia_Nascita"] = $provincia_nascita;
		
			}
			else
			{
				//$paese_nascita = single_answer_query("SELECT Nome FROM paesi_esteri_lista WHERE CC_Paese_Estero = '".$enne2->CC_Nascita."'");

				$query = "SELECT Nome FROM paesi_esteri_lista WHERE CC_Paese_Estero = '".$enne2["CC_Nascita"]."'";
                $paese_nascita = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"paesi_esteri_lista")["Nome"];

				//$field_utente[] = 'Paese_Nascita'; 			$value_utente[] = $paese_nascita;

                $field_utente["Paese_Nascita"] = $paese_nascita;
			}
		
			/*$field_utente[] = 'Data_Nascita'; 			$value_utente[] = $enne2->Data_Nascita;
			$field_utente[] = 'Codice_Fiscale'; 		$value_utente[] = $enne2->Codice_Fiscale;*/

            $field_utente["Data_Nascita"] = $enne2["Data_Nascita"];
            $field_utente["Codice_Fiscale"] = $enne2["Codice_Fiscale"];
		}
		else
		{
			/*$field_utente[] = 'Genere'; 				$value_utente[] = "D";
			$field_utente[] = 'Ditta'; 					$value_utente[] = $enne2->Ditta;
			$field_utente[] = 'Partita_Iva'; 			$value_utente[] = $enne2->Codice_Fiscale;*/

            $field_utente["Genere"] = "D";
            $field_utente["Ditta"] = $enne2["Ditta"];
            $field_utente["Partita_Iva"] = $enne2["Codice_Fiscale"];
		}
		
		//$ultimo_ruolo = single_answer_query("SELECT MAX(Comune_ID) FROM ruolo WHERE CC = '".$c."'");
        $query = "SELECT MAX(Comune_ID) as ID FROM ruolo WHERE CC = '".$c."'";
        $ultimo_ruolo = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"ruolo")["ID"];


		//$ultima_partita = single_answer_query("SELECT MAX(Comune_ID) FROM partita_tributi WHERE CC = '".$c."'");
        $query = "SELECT MAX(Comune_ID) as ID FROM partita_tributi WHERE CC = '".$c."'";
        $ultima_partita = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"partita_tributi")["ID"];


			
		/*$field_utente[] = 'Note';					$value_utente[] = "Ruolo ID ".$ultimo_ruolo." Partita ID ".($ultima_partita+1)." Intestatario\n**\n";
		$field_utente[] = 'Data_Registrazione'; 	$value_utente[] = date("Y-m-d");*/

        $field_utente["Note"] = "Ruolo ID ".$ultimo_ruolo." Partita ID ".($ultima_partita+1)." Intestatario\n**\n";
        $field_utente["Data_Registrazione"] = date("Y-m-d");

		
		//$comune_id = single_answer_query("SELECT MAX(Comune_ID) FROM utente WHERE CC_Comune = '".$c."'");

        $query = "SELECT MAX(Comune_ID) as ID FROM utente WHERE CC_Comune = '".$c."'";
        $comune_id = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"utente")["ID"];

		
		//$field_utente[] = 'Comune_ID'; 				$value_utente[] = $comune_id+1;
        $field_utente["Comune_ID"] = $comune_id+1;

        $new_ID_utenteN2 = $cls_db->DbSave($cls_utils->GetObjectQuery($field_utente,"utente"));
		//$new_ID_utenteN2 = table_insert_record('utente', $field_utente, $value_utente);
		
		//$field_partita['Utente_ID'] = 'Utente_ID'; 			$value_partita[] = $new_ID_utenteN2;

        $field_partita['Utente_ID'] = $new_ID_utenteN2;
		
		//CREAZIONE ARRAY CAMPI $field_residenza E VALORI $value_residenza PER LA TABELLA indirizzo
		//$field_residenza = array();
		//$value_residenza = array();
		
		//$field_residenza[] = 'Utente_ID'; 			$value_residenza[] = $new_ID_utenteN2;

        $field_residenza["Utente_ID"] = $new_ID_utenteN2;
		
		//$field_via = array();
		//$value_via = array();
		
		/*$field_via[] = 'CC_Comune';				$value_via[] = $c;
		$field_via[] = 'CC_Toponimo'; 			$value_via[] = $enne2->CC_Indirizzo_Res;*/

        $field_via["CC_Comune"] = $c;
        $field_via["CC_Toponimo"] = $enne2["CC_Indirizzo_Res"];

        $indirizzo_res = $enne2["Indirizzo_Res"];
		if(substr($enne2["CC_Indirizzo_Res"],0,1)=="Z")
		{
			//$paese_residenza = single_answer_query("SELECT Nome FROM paesi_esteri_lista WHERE CC_Paese_Estero = '".$enne2->CC_Indirizzo_Res."'");

            $query = "SELECT Nome FROM paesi_esteri_lista WHERE CC_Paese_Estero = '".$enne2["CC_Indirizzo_Res"]."'";
            $paese_residenza = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"paesi_esteri_lista")["Nome"];

			$comune_residenza = $enne2["Frazione_Res"];
			$provincia_residenza = "";

            if($enne2["Civico_Res"] != 0)
                $indirizzo_res.= " ".$enne2["Civico_Res"];
            if($enne2["Lettera_Civico_Res"] != "")
                $indirizzo_res.= $enne2["Lettera_Civico_Res"];
            if($enne2["Interno_Res"] != 0)
                $indirizzo_res.= " ".$enne2["Interno_Res"];
		}
		else
		{
            $query = "SELECT * FROM comuni_lista WHERE Com_Codice_Catastale = '".$enne2["CC_Indirizzo_Res"]."'";
			$comune_ogg_res = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"comuni_lista");// new comune($enne2->CC_Indirizzo_Res);
			$paese_residenza = "Italia";
			$comune_residenza = $comune_ogg_res["Com_Nome"];

            $query = "SELECT * FROM province_lista WHERE Pro_Codice='".$comune_ogg_res['Com_Codice_Provincia']."'";
            $result = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"province_lista");

			$provincia_residenza = $result["Pro_Sigla"];
		}

        /*$field_via[] = 'Nome';					$value_via[] = $indirizzo_res;
		$field_via[] = 'Paese'; 				$value_via[] = $paese_residenza;
		$field_via[] = 'Comune'; 				$value_via[] = $comune_residenza;
		$field_via[] = 'Cap'; 					$value_via[] = $enne2->Cap_Res;*/

        $field_via["Nome"] = $indirizzo_res;
        $field_via["Paese"] = $paese_residenza;
        $field_via["Comune"] = $comune_residenza;
        $field_via["Cap"] = $enne2["Cap_Res"];
		
		//$control_via = single_answer_query("SELECT ID FROM toponimo WHERE CC_Toponimo = '".$enne2->CC_Indirizzo_Res."' AND Nome = \"".$indirizzo_res."\" AND CC_Comune='".$c."'");

            $query = "SELECT ID FROM toponimo WHERE CC_Toponimo = '".$enne2["CC_Indirizzo_Res"]."' AND Nome = \"".$indirizzo_res."\" AND CC_Comune='".$c."'";
            $control_via = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"toponimo")["ID"];
		
		if($control_via==null)
		{
			$new_ID_via = $cls_db->DbSave($cls_utils->GetObjectQuery($field_via,"toponimo"));// table_insert_record('toponimo', $field_via, $value_via);
			$ID_via = $new_ID_via;
			
			//$field_residenza[] = 'Via_ID'; 					$value_residenza[] = $ID_via;
            $field_residenza['Via_ID'] = $ID_via;
		}
		else 
		{
			//$field_residenza[] = 'Via_ID'; 					$value_residenza[] = $control_via;
            $field_residenza['Via_ID'] = $control_via;
		}
		
		
		/*$field_residenza[] = 'Via_Cap_ID'; 				$value_residenza[] = 1;
		$field_residenza[] = 'Tipo'; 					$value_residenza[] = 'res';
		$field_residenza[] = 'CC_Indirizzo'; 			$value_residenza[] = $enne2->CC_Indirizzo_Res;
		$field_residenza[] = 'Paese'; 					$value_residenza[] = $paese_residenza;
		$field_residenza[] = 'Comune'; 					$value_residenza[] = $comune_residenza;
		$field_residenza[] = 'Provincia'; 				$value_residenza[] = $provincia_residenza;*/

        $field_residenza['Via_Cap_ID'] = $control_via;
        $field_residenza['Tipo'] = "res";
        $field_residenza['CC_Indirizzo'] = $enne2["CC_Indirizzo_Res"];
        $field_residenza['Paese'] = $paese_residenza;
        $field_residenza['Comune'] = $comune_residenza;
        $field_residenza['Provincia'] = $provincia_residenza;
		
		if($enne2["Civico_Res"] != 0)
		{
			//$field_residenza[] = 'Civico'; 				$value_residenza[] = $enne2->Civico_Res;
            $field_residenza['Civico'] = $enne2["Civico_Res"];
		}
		
		//$field_residenza[] = 'Esponente'; 				$value_residenza[] = $enne2->Lettera_Civico_Res;
        $field_residenza['Esponente'] = $enne2["Lettera_Civico_Res"];
		
		if($enne2["Interno_Res"] != 0)
		{
			//$field_residenza[] = 'Interno'; 			$value_residenza[] = $enne2->Interno_Res;
            $field_residenza['Interno'] = $enne2["Interno_Res"];
		}
		
		/*$field_residenza[] = 'Cap'; 					$value_residenza[] = $enne2->Cap_Res;
		$field_residenza[] = 'Data_Inizio_Residenza';	$value_residenza[] = "1900-01-01";*/

        $field_residenza['Cap'] = $enne2["Cap_Res"];
        $field_residenza['Data_Inizio_Residenza'] = "1900-01-01";
		
		//table_insert_record("indirizzo", $field_residenza, $value_residenza);
		$cls_db->DbSave($cls_utils->GetObjectQuery($field_residenza,"indirizzo"));
		
		if($enne2["Indirizzo_Dom"]!="" && $enne2["Cap_Dom"]!="" && $enne2["Cap_Dom"]!="00000" && $enne2["CC_Indirizzo_Dom"]!="")
		{
			//CREAZIONE ARRAY CAMPI $field_domicilio E VALORI $value_domicilio PER LA TABELLA indirizzo
			//$field_domicilio = array();
			//$value_domicilio = array();
			
			//$field_domicilio[] = 'Utente_ID'; 			$value_domicilio[] = $new_ID_utenteN2;
            $field_domicilio["Utente_ID"] = $new_ID_utenteN2;
			
			//$field_via = array();
			//$value_via = array();
			
			/*$field_via[] = 'CC_Comune';				$value_via[] = $c;
			$field_via[] = 'CC_Toponimo'; 			$value_via[] = $enne2->CC_Indirizzo_Dom;*/

            $field_via["CC_Comune"] = $c;
            $field_via["CC_Toponimo"] = $enne2["CC_Indirizzo_Dom"];

            $indirizzo_dom = $enne2["Indirizzo_Dom"];
			if(substr($enne2["CC_Indirizzo_Dom"],0,1)=="Z")
			{
				//$paese_domicilio = single_answer_query("SELECT Nome FROM paesi_esteri_lista WHERE CC_Paese_Estero = '".$enne2->CC_Indirizzo_Dom."'");
				$query = "SELECT Nome FROM paesi_esteri_lista WHERE CC_Paese_Estero = '".$enne2["CC_Indirizzo_Dom"]."'";
                $paese_domicilio = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"paesi_esteri_lista")["Nome"];

				$comune_domicilio = $enne2["Frazione_Dom"];
				$provincia_domicilio = "";
                if($enne2["Civico_Dom"] != 0)
                    $indirizzo_dom.= " ".$enne2["Civico_Dom"];
                if($enne2["Lettera_Civico_Dom"] != "")
                    $indirizzo_dom.= $enne2["Lettera_Civico_Dom"];
                if($enne2["Interno_Dom"] != 0)
                    $indirizzo_dom.= " ".$enne2["Interno_Dom"];
			}
			else
			{
                $query = "SELECT * FROM comuni_lista WHERE Com_Codice_Catastale = '".$enne2["CC_Indirizzo_Dom"]."'";
                $comune_ogg_dom = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"comuni_lista");
				//$comune_ogg_dom = new comune($enne2->CC_Indirizzo_Dom);
				$paese_domicilio = "Italia";
				$comune_domicilio = $comune_ogg_dom["Com_Nome"];

                $query = "SELECT * FROM province_lista WHERE Pro_Codice='".$comune_ogg_dom['Com_Codice_Provincia']."'";
                $result = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"province_lista");

				$provincia_domicilio = $result["Pro_Sigla"];
			}

            /*$field_via[] = 'Nome';					$value_via[] = $indirizzo_dom;
			$field_via[] = 'Paese'; 				$value_via[] = $paese_domicilio;
			$field_via[] = 'Comune'; 				$value_via[] = $comune_domicilio;
			$field_via[] = 'Cap'; 					$value_via[] = $enne2->Cap_Dom;*/

            $field_via["Nome"] = $indirizzo_dom;
            $field_via["Paese"] = $paese_domicilio;
            $field_via["Comune"] = $comune_domicilio;
            $field_via["Cap"] = $enne2["Cap_Dom"];

            $query = "SELECT ID FROM toponimo WHERE CC_Toponimo = '".$enne2["CC_Indirizzo_Dom"]."' AND Nome = \"".$indirizzo_dom."\" AND CC_Comune='".$c."'";
            $control_via = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"toponimo")["ID"];
			//$control_via = single_answer_query("SELECT ID FROM toponimo WHERE CC_Toponimo = '".$enne2->CC_Indirizzo_Dom."' AND Nome = \"".$indirizzo_dom."\" AND CC_Comune='".$c."'");
			
			if($control_via==null)
			{
				$new_ID_via = $cls_db->DbSave($cls_utils->GetObjectQuery($field_via,"toponimo"));// table_insert_record('toponimo', $field_via, $value_via);
				$ID_via = $new_ID_via;
					
				//$field_domicilio[] = 'Via_ID'; 					$value_domicilio[] = $ID_via;

                $field_domicilio["Via_ID"] = $ID_via;
			}
			else
			{
				//$field_domicilio[] = 'Via_ID'; 					$value_domicilio[] = $control_via;
                $field_domicilio["Via_ID"] = $control_via;
			}
			
			
			/*$field_domicilio[] = 'Via_Cap_ID'; 				$value_domicilio[] = 1;
			$field_domicilio[] = 'Tipo'; 					$value_domicilio[] = 'dom';
			$field_domicilio[] = 'CC_Indirizzo'; 			$value_domicilio[] = $enne2->CC_Indirizzo_Dom;
			$field_domicilio[] = 'Paese'; 					$value_domicilio[] = $paese_domicilio;
			$field_domicilio[] = 'Comune'; 					$value_domicilio[] = $comune_domicilio;
			$field_domicilio[] = 'Provincia'; 				$value_domicilio[] = $provincia_domicilio;*/

            $field_domicilio["Via_Cap_ID"] = 1;
            $field_domicilio["Tipo"] = "dom";
            $field_domicilio["CC_Indirizzo"] = $enne2["CC_Indirizzo_Dom"];
            $field_domicilio["Paese"] = $paese_domicilio;
            $field_domicilio["Comune"] = $comune_domicilio;
            $field_domicilio["Provincia"] = $provincia_domicilio;
			
			if($enne2["Civico_Dom"] != 0)
			{
				//$field_domicilio[] = 'Civico'; 				$value_domicilio[] = $enne2->Civico_Dom;
                $field_domicilio["Civico"] = $enne2["Civico_Dom"];
			}
			
			//$field_domicilio[] = 'Esponente'; 				$value_domicilio[] = $enne2->Lettera_Civico_Dom;

            $field_domicilio["Esponente"] = $enne2["Lettera_Civico_Dom"];
			
			if($enne2["Interno_Dom"] != 0)
			{
				//$field_domicilio[] = 'Interno'; 			$value_domicilio[] = $enne2->Interno_Dom;
                $field_domicilio["Interno"] = $enne2["Interno_Dom"];
			}
			
			/*$field_domicilio[] = 'Cap'; 					$value_domicilio[] = $enne2->Cap_Dom;
			$field_domicilio[] = 'Data_Inizio_Residenza';	$value_domicilio[] = "1900-01-01";*/

            $field_domicilio["Cap"] = $enne2["Cap_Dom"];
            $field_domicilio["Data_Inizio_Residenza"] = "1900-01-01";

            $cls_db->DbSave($cls_utils->GetObjectQuery($field_domicilio,"indirizzo"));
			//table_insert_record("indirizzo", $field_domicilio, $value_domicilio);
		}
		
		}
		//CICLO ANAGRAFICHE COOBBLIGATI O COOINTESTATARI N3
		for($z=0;$z<$enne2["num_n3"];$z++)
		{
			$enne3 = $enne2["n3"][$z];
						
			$sesso_N3 = $enne3["Sesso"];
            if($enne3["Natura_Giuridica"]==1  )
            {
                $ctrl_sesso = number_format(substr($enne3["Codice_Fiscale"], 9,2));
                if( $ctrl_sesso > 40 ) $sesso_N3 = "F";
                else	$sesso_N3 = "M";
            }
			
			if( $enne2["Flag_Partita"] == "omoN3" )
			{
					if(!$sesso_N3!="")
					    $sesso = "D";
					else
					    $sesso = $sesso_N3;

					$control_omo = $cls_utils->check_omonimi($sesso, $enne3["Codice_Fiscale"], $enne3["Ditta"], $enne3["Codice_Fiscale"], $enne3["Nome"], $enne3["Cognome"], $enne3["CC_Nascita"], $enne3["Data_Nascita"], $c );
			
					$pos = strpos($control_omo, 'dubbi');
					$pos_omo = strpos($control_omo, 'omo');
					if($pos==false)
					{
							
						$omo = explode(" ",$control_omo);
						$utente_ID_N3 = $omo[1];
									
					}
					else
					{
						if($pos_omo==false)
						{
							$query = "UPDATE 290_n2 SET Flag_Importazione = 'N3', Flag_Partita = 'no' WHERE ID = '".$enne2->ID."'";
							$cls_db->ExecuteQuery($query);//safe_query($query);
							$query = "UPDATE 290_n3 SET Flag_Importazione = 'parz ".($partita_comune_id+1)."' WHERE ID = '".$enne3->ID."'";
							$cls_db->ExecuteQuery($query);//safe_query($query);
							continue;
						}
						else
						{
							$omo_id = substr($control_omo, 0, $pos);
								
							$omo = explode(" ",$omo_id);
							$utente_ID_N3 = $omo[1];
			
						}
			
					}

					$query = "SELECT ID FROM utente WHERE Comune_ID = '".$utente_ID_N3."' AND CC_Comune = '".$c."'";
                    $utente_ID_N3 = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"utente")["ID"];
					//$utente_ID_N3 = single_answer_query("SELECT ID FROM utente WHERE Comune_ID = '".$utente_ID_N3."' AND CC_Comune = '".$c."'");
					
					$field_partita["Coo_ID"] += "*".$utente_ID_N3;
					
					if($enne2["Cointestatari"]=="C")
						$coo = "Cointestatario";
					else
						$coo = "Coobbligato";
					
					//$ultimo_ruolo = single_answer_query("SELECT MAX(Comune_ID) FROM ruolo WHERE CC = '".$c."'");
					$query = "SELECT MAX(Comune_ID) as ID FROM ruolo WHERE CC = '".$c."'";
                    $ultimo_ruolo = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"ruolo")["ID"];
					//$ultima_partita = single_answer_query("SELECT MAX(Comune_ID) FROM partita_tributi WHERE CC = '".$c."'");
                    $query = "SELECT MAX(Comune_ID) as ID FROM partita_tributi WHERE CC = '".$c."'";
                    $ultima_partita = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"partita_tributi")["ID"];

                    $query = "SELECT * FROM utente WHERE ID = '".$utente_ID_N3."' AND CC_Comune = '".$c."'";
					$utente_omo = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"utente");// new utente($utente_ID_N3, $c);

                $query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$utente_ID_N3."' AND Tipo = 'res'";
                $utente_omo["Residenza"] = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"indirizzo");

                if($utente_omo["Residenza"]["Via_ID"]!=1)
                {
                    $query = "SELECT * FROM toponimo WHERE ID = '".$utente_omo["Residenza"]["Via_ID"]."' AND CC_Comune = '".$c."'";
                    $utente_omo["Residenza"]["Toponimo"] = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"toponimo");
                }

                else if($utente_omo["Residenza"]["Via_Cap_ID"]!=1)
                {
                    $query = "SELECT * FROM toponimi_cappati WHERE ID = '".$utente_omo["Residenza"]["Via_Cap_ID"]."'";
                    $utente_omo["Residenza"]["Toponimo"] = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"toponimi_cappati");
                }
                else
                    $utente_omo["Residenza"]["Toponimo"] = null;


                //$this->Domicilio = new indirizzo( $progr , 'dom' , $c );
                $query = "SELECT * FROM indirizzo WHERE Utente_ID = '".$utente_ID_N3."' AND Tipo = 'dom'";
                $utente_omo["Domicilio"] = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"indirizzo");

                if($utente_omo["Domicilio"]["Via_ID"]!=1)
                {
                    $query = "SELECT * FROM toponimo WHERE ID = '".$utente_omo["Domicilio"]["Via_ID"]."' AND CC_Comune = '".$c."'";
                    $utente_omo["Domicilio"]["Toponimo"] = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"toponimo");
                }

                else if($utente_omo["Domicilio"]["Via_Cap_ID"]!=1)
                {
                    $query = "SELECT * FROM toponimi_cappati WHERE ID = '".$utente_omo["Domicilio"]["Via_Cap_ID"]."'";
                    $utente_omo["Domicilio"]["Toponimo"] = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"toponimi_cappati");
                }
                else
                    $utente_omo["Domicilio"]["Toponimo"] = null;
					
					$residenza_omo = $utente_omo["Residenza"];
					$domicilio_omo = $utente_omo["Domicilio"];
					$note_omo = $utente_omo["Note"];
					
					
					//$field_utente = array();
					//$value_utente = array();
			
					//$field_utente[] = 'Note';
					
					$note290 = $note_omo."Ruolo ID ".$ultimo_ruolo." Partita ID ".($ultima_partita+1)." ".$coo;
					
					if($enne3["Indirizzo_Res"] != $residenza_omo["Toponimo"]["Nome"] || $enne3["Civico_Res"] != $residenza_omo["Civico"])
					{
						if($enne3["Civico_Res"] == 0)$civico_res ="";
						else $civico_res = $enne3["Civico_Res"];
						if($enne3["Interno_Res"] == 0)$interno_res ="";
						else $interno_res = $enne3["Interno_Res"];
						
						$note290 .= "\nIndirizzo di residenza da verificare: ";
						$note290 .= $enne3["Indirizzo_Res"]." civ. ".$civico_res." esp. ".$enne3["Lettera_Civico_Res"]." int. ".$interno_res;
					}
			
					if($enne3["Indirizzo_Dom"]!="")
					{
						if($enne3["Civico_Dom"] == 0)$civico_dom ="";
						else $civico_dom = $enne3["Civico_Dom"];
						if($enne3["Interno_Dom"] == 0)$interno_dom ="";
						else $interno_dom = $enne3["Interno_Dom"];
						
						$note290 .= "\nIndirizzo di domicilio da verificare: ";
						$note290 .= $enne3["Indirizzo_Dom"]." civ. ".$civico_dom." esp. ".$enne3["Lettera_Civico_Dom"]." int. ".$interno_dom;
					}
					$note290 .="\n**\n";
					//$value_utente[] = $note290;

                    $field_utente['Note'] = $note290;

                    $field_utente_where["ID"] =  $utente_ID_N3;

                    $cls_db->DbSave($cls_utils->GetObjectQuery($field_utente,"utente",$field_utente_where));
			
					//table_update_record("utente", $field_utente, $value_utente, "ID", $utente_ID_N3);
			
					$query = "UPDATE 290_n2 SET Flag_Importazione = 'N3', Flag_Partita = 'si' WHERE ID = '".$enne2["ID"]."'";
					$cls_db->ExecuteQuery($query);//safe_query($query);
					$query = "UPDATE 290_n3 SET Flag_Importazione = 'omo' WHERE ID = '".$enne3["ID"]."'";
                    $cls_db->ExecuteQuery($query);//safe_query($query);

			}
			else
			{
				$query = "UPDATE 290_n3 SET Flag_Importazione = 'ok' WHERE ID = '".$enne3["ID"]."'";
                $cls_db->ExecuteQuery($query);//safe_query($query);
				
			//CREAZIONE ARRAY CAMPI $field_utente E VALORI $value_utente PER LA TABELLA utente
			//$field_utente = array();
			//$value_utente = array();
			
			//$field_utente[] = 'CC_Comune'; 				$value_utente[] = $c;
            $field_utente['CC_Comune'] = $c;
			if($enne3["Natura_Giuridica"]==1)
			{
				/*$field_utente[] = 'Genere'; 				$value_utente[] = $sesso_N3;
				$field_utente[] = 'Cognome'; 				$value_utente[] = $enne3->Cognome;
				$field_utente[] = 'Nome'; 					$value_utente[] = $enne3->Nome;
				$field_utente[] = 'CC_Nascita'; 			$value_utente[] = $enne3->CC_Nascita;*/

                $field_utente['Genere'] = $sesso_N3;
                $field_utente['Cognome'] = $enne3["Cognome"];
                $field_utente['Nome'] = $enne3["Nome"];
                $field_utente['CC_Nascita'] = $enne3["CC_Nascita"];

				if(substr($enne3["CC_Nascita"],0,1)!="Z")
				{
                    $query = "SELECT * FROM comuni_lista WHERE Com_Codice_Catastale = '".$enne3["CC_Nascita"]."'";
					$comune = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"comuni_lista");// new comune($enne3->CC_Nascita);
	
					$paese_nascita = "Italia";
					$comune_nascita = $comune["Com_Nome"];

                    $query = "SELECT * FROM province_lista WHERE Pro_Codice='".$comune['Com_Codice_Provincia']."'";
                    $result = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"province_lista");

					$provincia_nascita = $result["Pro_Sigla"];
				
					/*$field_utente[] = 'Paese_Nascita'; 			$value_utente[] = $paese_nascita;
					$field_utente[] = 'Comune_Nascita'; 		$value_utente[] = $comune_nascita;
					$field_utente[] = 'Provincia_Nascita'; 		$value_utente[] = $provincia_nascita;*/

                    $field_utente['Paese_Nascita'] = $paese_nascita;
                    $field_utente['Comune_Nascita'] = $comune_nascita;
                    $field_utente['Provincia_Nascita'] = $provincia_nascita;
				
				}
				else
				{
				    $query = "SELECT Nome FROM paesi_esteri_lista WHERE CC_Paese_Estero = '".$enne3["CC_Nascita"]."'";
					$paese_nascita = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"paesi_esteri_lista")["Nome"];// single_answer_query("SELECT Nome FROM paesi_esteri_lista WHERE CC_Paese_Estero = '".$enne3->CC_Nascita."'");
					//$field_utente[] = 'Paese_Nascita'; 			$value_utente[] = $paese_nascita;

                    $field_utente['Paese_Nascita'] = $paese_nascita;
				}
				
				/*$field_utente[] = 'Data_Nascita'; 			$value_utente[] = $enne3->Data_Nascita;
				$field_utente[] = 'Codice_Fiscale'; 		$value_utente[] = $enne3->Codice_Fiscale;*/

                $field_utente['Data_Nascita'] = $enne3["Data_Nascita"];
                $field_utente['Codice_Fiscale'] = $enne3["Codice_Fiscale"];
			}
			else
			{
				/*$field_utente[] = 'Genere'; 				$value_utente[] = "D";
				$field_utente[] = 'Ditta'; 					$value_utente[] = $enne3->Ditta;
				$field_utente[] = 'Partita_Iva'; 			$value_utente[] = $enne3->Codice_Fiscale;*/

                $field_utente['Genere'] = "D";
                $field_utente['Ditta'] = $enne3["Ditta"];
                $field_utente['Partita_Iva'] = $enne3["Codice_Fiscale"];
			}
				
			if($enne2["Cointestatari"]=="C")
				$coo = "Cointestatario";
			else 
				$coo = "Coobbligato";	
			
			//$ultimo_ruolo = single_answer_query("SELECT MAX(Comune_ID) FROM ruolo WHERE CC = '".$c."'");
			$query = "SELECT MAX(Comune_ID) as ID FROM ruolo WHERE CC = '".$c."'";
            $ultimo_ruolo = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"ruolo")["ID"];

			//$ultima_partita = single_answer_query("SELECT MAX(Comune_ID) FROM partita_tributi WHERE CC = '".$c."'");
            $query = "SELECT MAX(Comune_ID) as ID FROM partita_tributi WHERE CC = '".$c."'";
            $ultima_partita = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"partita_tributi")["ID"];
			
			/*$field_utente[] = 'Note';					$value_utente[] = "Ruolo ID ".$ultimo_ruolo." Partita ID ".($ultima_partita+1)."\n".$coo."\n**\n";
			$field_utente[] = 'Data_Registrazione'; 	$value_utente[] = date("Y-m-d");*/

            $field_utente['Note'] = "Ruolo ID ".$ultimo_ruolo." Partita ID ".($ultima_partita+1)."\n".$coo."\n**\n";
            $field_utente['Data_Registrazione'] = date("Y-m-d");

            $query = "SELECT MAX(Comune_ID) as ID FROM utente WHERE CC_Comune = '".$c."'";
			$comune_id = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"utente")["ID"];//single_answer_query("SELECT MAX(Comune_ID) FROM utente WHERE CC_Comune = '".$c."'");

			//$field_utente[] = 'Comune_ID'; 				$value_utente[] = $comune_id+1;

            $field_utente['Comune_ID'] = $comune_id+1;
				
			$new_ID_utente = $cls_db->DbSave($cls_utils->GetObjectQuery($field_utente,"utente"));// table_insert_record('utente', $field_utente, $value_utente);
			
			$field_partita["Coo_ID"] += "*".$new_ID_utente;
			
			//CREAZIONE ARRAY CAMPI $field_residenza E VALORI $value_residenza PER LA TABELLA indirizzo
			//$field_residenza = array();
			//$value_residenza = array();
			
			//$field_residenza[] = 'Utente_ID'; 			$value_residenza[] = $new_ID_utente;

            $field_residenza["Utente_ID"] = $new_ID_utente;
			
			//$field_via = array();
			//$value_via = array();
			
			/*$field_via[] = 'CC_Comune';				$value_via[] = $c;
			$field_via[] = 'Nome';					$value_via[] = $enne3->Indirizzo_Res;
			$field_via[] = 'CC_Toponimo'; 			$value_via[] = $enne3->CC_Indirizzo_Res;*/

            $field_via["CC_Comune"] = $c;
            $field_via["Nome"] = $enne3["Indirizzo_Res"];
            $field_via["CC_Toponimo"] = $enne3["CC_Indirizzo_Res"];
				
			if(substr($enne3["CC_Indirizzo_Res"],0,1)=="Z")
			{
			    $query = "SELECT Nome FROM paesi_esteri_lista WHERE CC_Paese_Estero = '".$enne3["CC_Indirizzo_Res"]."'";
                $paese_residenza = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"paesi_esteri_lista")["Nome"];
				//$paese_residenza = single_answer_query("SELECT Nome FROM paesi_esteri_lista WHERE CC_Paese_Estero = '".$enne3->CC_Indirizzo_Res."'");
				$comune_residenza = $enne3["Frazione_Res"];
				$provincia_residenza = "";
			}
			else
			{
                $query = "SELECT * FROM comuni_lista WHERE Com_Codice_Catastale = '".$enne3["CC_Indirizzo_Res"]."'";
                $comune_ogg_res = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"comuni_lista");//
				//$comune_ogg_res = new comune($enne3->CC_Indirizzo_Res);
				$paese_residenza = "Italia";
				$comune_residenza = $comune_ogg_res["Com_Nome"];

                $query = "SELECT * FROM province_lista WHERE Pro_Codice='".$comune_ogg_res['Com_Codice_Provincia']."'";
                $result = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"province_lista");

				$provincia_residenza = $result["Pro_Sigla"];
			}
				
			/*$field_via[] = 'Paese'; 				$value_via[] = $paese_residenza;
			$field_via[] = 'Comune'; 				$value_via[] = $comune_residenza;
			$field_via[] = 'Cap'; 					$value_via[] = $enne3->Cap_Res;*/

            $field_via['Paese'] = $paese_residenza;
            $field_via['Comune'] = $comune_residenza;
            $field_via['Cap'] = $enne3["Cap_Res"];

            $query = "SELECT ID FROM toponimo WHERE CC_Toponimo = '".$enne3["CC_Indirizzo_Res"]."' AND Nome = \"".$enne3["Indirizzo_Res"]."\" AND CC_Comune='".$c."'";
			$control_via =  $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"toponimo")["ID"];//single_answer_query("SELECT ID FROM toponimo WHERE CC_Toponimo = '".$enne3->CC_Indirizzo_Res."' AND Nome = \"".$enne3->Indirizzo_Res."\" AND CC_Comune='".$c."'");
			
			if($control_via==null)
			{
				$new_ID_via = $cls_db->DbSave($cls_utils->GetObjectQuery($field_via,"toponimo"));// table_insert_record('toponimo', $field_via, $value_via);
				$ID_via = $new_ID_via;
					
				//$field_residenza[] = 'Via_ID'; 					$value_residenza[] = $ID_via;

                $field_residenza['Via_ID'] = $ID_via;
			}
			else
			{
				//$field_residenza[] = 'Via_ID'; 					$value_residenza[] = $control_via;

                $field_residenza['Via_ID'] = $control_via;
			}
					
			/*$field_residenza[] = 'Via_Cap_ID'; 				$value_residenza[] = 1;
			$field_residenza[] = 'Tipo'; 					$value_residenza[] = 'res';
			$field_residenza[] = 'CC_Indirizzo'; 			$value_residenza[] = $enne3->CC_Indirizzo_Res;
			$field_residenza[] = 'Paese'; 					$value_residenza[] = $paese_residenza;
			$field_residenza[] = 'Comune'; 					$value_residenza[] = $comune_residenza;
			$field_residenza[] = 'Provincia'; 				$value_residenza[] = $provincia_residenza;*/

			$field_residenza['Via_Cap_ID'] = 1;
			$field_residenza['Tipo'] = "res";
			$field_residenza['CC_Indirizzo'] = $enne3["CC_Indirizzo_Res"];
			$field_residenza['Paese'] = $paese_residenza;
			$field_residenza['Comune'] = $comune_residenza;
			$field_residenza['Provincia'] = $provincia_residenza;
			
			if($enne3["Civico_Res"] != 0)
			{
				//$field_residenza[] = 'Civico'; 				$value_residenza[] = $enne3->Civico_Res;

                $field_residenza['Civico'] = $enne3["Civico_Res"];
			}
			
			//$field_residenza[] = 'Esponente'; 				$value_residenza[] = $enne3->Lettera_Civico_Res;

			$field_residenza['Esponente'] = $enne3["Lettera_Civico_Res"];
			
			if($enne3["Interno_Res"] != 0)
			{
				//$field_residenza[] = 'Interno'; 			$value_residenza[] = $enne3->Interno_Res;
                $field_residenza['Interno'] = $enne3["Interno_Res"];
			}
			
			/*$field_residenza[] = 'Cap'; 					$value_residenza[] = $enne3->Cap_Res;
			$field_residenza[] = 'Data_Inizio_Residenza';	$value_residenza[] = "1900-01-01";*/

			$field_residenza['Cap'] = $enne3["Cap_Res"];
			$field_residenza['Data_Inizio_Residenza'] = "1900-01-01";
			
			$cls_db->DbSave($cls_utils->GetObjectQuery($field_residenza,"indirizzo"));//table_insert_record("indirizzo", $field_residenza, $value_residenza);
				
			if($enne3["Indirizzo_Dom"]!="" && $enne3["Cap_Dom"]!="00000" && $enne3["Cap_Dom"]!="" && $enne3["CC_Indirizzo_Dom"]!="")
			{
				//CREAZIONE ARRAY CAMPI $field_domicilio E VALORI $value_domicilio PER LA TABELLA indirizzo
				//$field_domicilio = array();
				//$value_domicilio = array();
					
				//$field_domicilio[] = 'Utente_ID'; 			$value_domicilio[] = $new_ID_utente;

                $field_domicilio['Utente_ID'] = $new_ID_utente;
					
				//$field_via = array();
				//$value_via = array();
					
				/*$field_via[] = 'CC_Comune';				$value_via[] = $c;
				$field_via[] = 'Nome';					$value_via[] = $enne3->Indirizzo_Dom;
				$field_via[] = 'CC_Toponimo'; 			$value_via[] = $enne3->CC_Indirizzo_Dom;*/

                $field_via['CC_Comune'] = $c;
                $field_via['Nome'] = $enne3["Indirizzo_Dom"];
                $field_via['CC_Toponimo'] = $enne3["CC_Indirizzo_Dom"];
			
				if(substr($enne3["CC_Indirizzo_Dom"],0,1)=="Z")
				{
				    $query = "SELECT Nome FROM paesi_esteri_lista WHERE CC_Paese_Estero = '".$enne3["CC_Indirizzo_Dom"]."'";
					$paese_domicilio = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"paesi_esteri_lista")["Nome"];//single_answer_query("SELECT Nome FROM paesi_esteri_lista WHERE CC_Paese_Estero = '".$enne3->CC_Indirizzo_Dom."'");
					$comune_domicilio = $enne3["Frazione_Dom"];
					$provincia_domicilio = "";
				}
				else
				{
                    $query = "SELECT * FROM comuni_lista WHERE Com_Codice_Catastale = '".$enne3["CC_Indirizzo_Dom"]."'";
                    $comune_ogg_dom = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"comuni_lista");
					//$comune_ogg_dom = new comune($enne3->CC_Indirizzo_Dom);
					$paese_domicilio = "Italia";
					$comune_domicilio = $comune_ogg_dom["Com_Nome"];

                    $query = "SELECT * FROM province_lista WHERE Pro_Codice='".$comune_ogg_dom['Com_Codice_Provincia']."'";
                    $result = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"province_lista");

					$provincia_domicilio = $result["Pro_Sigla"];
				}
			
				/*$field_via[] = 'Paese'; 				$value_via[] = $paese_domicilio;
				$field_via[] = 'Comune'; 				$value_via[] = $comune_domicilio;
				$field_via[] = 'Cap'; 					$value_via[] = $enne3->Cap_Dom;*/

                $field_via['Paese'] = $paese_domicilio;
                $field_via['Comune'] = $comune_domicilio;
                $field_via['Cap'] = $enne3["Cap_Dom"];

                $query = "SELECT ID FROM toponimo WHERE CC_Toponimo = '".$enne3["CC_Indirizzo_Dom"]."' AND Nome = \"".$enne3["Indirizzo_Dom"]."\" AND CC_Comune='".$c."'";
				$control_via = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"toponimo")["ID"];//single_answer_query("SELECT ID FROM toponimo WHERE CC_Toponimo = '".$enne3->CC_Indirizzo_Dom."' AND Nome = \"".$enne3->Indirizzo_Dom."\" AND CC_Comune='".$c."'");
					
				if($control_via==null)
				{
					$new_ID_via = $cls_db->DbSave($cls_utils->GetObjectQuery($field_via,"toponimo"));// table_insert_record('toponimo', $field_via, $value_via);
					$ID_via = $new_ID_via;
						
					//$field_domicilio[] = 'Via_ID'; 					$value_domicilio[] = $ID_via;

                    $field_domicilio['Via_ID'] = $ID_via;
				}
				else
				{
					//$field_domicilio[] = 'Via_ID'; 					$value_domicilio[] = $control_via;
                    $field_domicilio['Via_ID'] = $control_via;
				}
					
					
				/*$field_domicilio[] = 'Via_Cap_ID'; 				$value_domicilio[] = 1;
				$field_domicilio[] = 'Tipo'; 					$value_domicilio[] = 'dom';
				$field_domicilio[] = 'CC_Indirizzo'; 			$value_domicilio[] = $enne3->CC_Indirizzo_Dom;
				$field_domicilio[] = 'Paese'; 					$value_domicilio[] = $paese_domicilio;
				$field_domicilio[] = 'Comune'; 					$value_domicilio[] = $comune_domicilio;
				$field_domicilio[] = 'Provincia'; 				$value_domicilio[] = $provincia_domicilio;*/

                $field_domicilio['Via_Cap_ID'] = 1;
                $field_domicilio['Tipo'] = "dom";
                $field_domicilio['CC_Indirizzo'] = $enne3["CC_Indirizzo_Dom"];
                $field_domicilio['Paese'] = $paese_domicilio;
                $field_domicilio['Comune'] = $comune_domicilio;
                $field_domicilio['Provincia'] = $provincia_domicilio;
					
				if($enne3["Civico_Dom"] != 0)
				{
					//$field_domicilio[] = 'Civico'; 				$value_domicilio[] = $enne3->Civico_Dom;
                    $field_domicilio['Civico'] = $enne3["Civico_Dom"];
				}
					
				//$field_domicilio[] = 'Esponente'; 				$value_domicilio[] = $enne3->Lettera_Civico_Dom;
                $field_domicilio['Esponente'] = $enne3["Lettera_Civico_Dom"];
					
				if($enne3["Interno_Dom"] != 0)
				{
					//$field_domicilio[] = 'Interno'; 			$value_domicilio[] = $enne3->Interno_Dom;
                    $field_domicilio['Interno'] = $enne3["Interno_Dom"];
				}
					
				/*$field_domicilio[] = 'Cap'; 					$value_domicilio[] = $enne3->Cap_Dom;
				$field_domicilio[] = 'Data_Inizio_Residenza';	$value_domicilio[] = "1900-01-01";*/

                $field_domicilio['Cap'] = $enne3["Cap_Dom"];
                $field_domicilio['Data_Inizio_Residenza'] = "1900-01-01";
					
				$cls_db->DbSave($cls_utils->GetObjectQuery($field_domicilio,"indirizzo"));//table_insert_record("indirizzo", $field_domicilio, $value_domicilio);
			}
		}
		
		}
		
		//$field_partita[] = "Anno_Riferimento";				$value_partita[] = $enne2->n4[0]->Anno_Tributo;
        $field_partita["Anno_Riferimento"] = $enne2["n4"][0]["Anno_Tributo"];

		$array_tipo_partita = $cls_coaz->estraiTipoPartita($enne2["n4"][0]["Codice_Tributo"], $array_codici);
//
		if($array_tipo_partita!=null){
			//$field_partita[] = "Tipo";						$value_partita[] = $array_tipo_partita['Tipo'];
			//$field_partita[] = "Sottotipo";					$value_partita[] = $array_tipo_partita['Sottotipo'];
            $field_partita["Tipo"] = $array_tipo_partita['Tipo'];
            $field_partita["Sottotipo"] = $array_tipo_partita['Sottotipo'];
		}
		else 
			continue;
		
		$new_partita_ID = $cls_db->DbSave($cls_utils->GetObjectQuery($field_partita,"partita_tributi"));// table_insert_record("partita_tributi", $field_partita, $value_partita);

        $conta_info=0;
        $conta_anno=0;
        $a_info_cart = array();
        for($x=0;$x<$enne2["num_n4"];$x++) {
            set_time_limit(60);
            $enne4 = $enne2["n4"][$x];
            if ($x == 0) {
                $temp_anno_tributo = $enne4["Anno_Tributo"];
                $temp_info_cartella = $enne4["Info_Cartella"];
                $a_info_cart[$conta_info]['info'] = $enne4["Info_Cartella"];
                $a_info_cart[$conta_info]['anno'][$conta_anno] = $enne4["Anno_Tributo"];
            }

            if ($enne4["Anno_Tributo"] != $temp_anno_tributo) {
                if($enne4["Info_Cartella"] != $temp_info_cartella){
                    $conta_info++;
                    $conta_anno=0;
                }
                else{
                    $conta_anno++;
                }
                $a_info_cart[$conta_info]['info'] = $enne4["Info_Cartella"];
                $a_info_cart[$conta_info]['anno'][$conta_anno] = $enne4["Anno_Tributo"];
            }
            else if($enne4["Info_Cartella"] != $temp_info_cartella){
                $conta_info++;
                $conta_anno=0;

                $a_info_cart[$conta_info]['info'] = $enne4["Info_Cartella"];
                $a_info_cart[$conta_info]['anno'][$conta_anno] = $enne4["Anno_Tributo"];
            }

            $temp_anno_tributo = $enne4["Anno_Tributo"];
            $temp_info_cartella = $enne4["Info_Cartella"];
        }

//        continue;
        $info_cartella = "";
        for($x=0;$x<count($a_info_cart);$x++){
            if($x>0){
                $info_cartella.= " - ";
            }

            if(count($a_info_cart[$x]['anno'])>1)
                $info_cartella.= $a_info_cart[$x]['info']." ANNI";
            else
                $info_cartella.= $a_info_cart[$x]['info']." ANNO";

            for($x_anno=0;$x_anno<count($a_info_cart[$x]['anno']);$x_anno++){
                $info_cartella.= " ".$a_info_cart[$x]['anno'][$x_anno];
            }
        }

		//CICLO INFO CONTABILI N4
		for($x=0;$x<$enne2["num_n4"];$x++)
		{
			set_time_limit(60);
			$enne4 = $enne2["n4"][$x];

			//CREAZIONE ARRAY CAMPI $field_tributo E VALORI $value_tributo PER LA TABELLA tributo
			//$field_tributo = array();
			//$value_tributo = array();
			$query = "SELECT MAX(Comune_ID) as ID FROM tributo WHERE CC = '".$c."'";
			$comune_id = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"tributo")["ID"];//single_answer_query("SELECT MAX(Comune_ID) FROM tributo WHERE CC = '".$c."'");
			
			/*$field_tributo[] = 'CC';						$value_tributo[] = $c;
			$field_tributo[] = 'Comune_ID';					$value_tributo[] = $comune_id+1;
			$field_tributo[] = 'Partita_ID'; 				$value_tributo[] = $new_partita_ID;
			$field_tributo[] = 'Anno_Tributo'; 				$value_tributo[] = $enne4->Anno_Tributo;
			$field_tributo[] = 'Codice_Tributo';			$value_tributo[] = $enne4->Codice_Tributo;
			$field_tributo[] = 'Imposta';					$value_tributo[] = $enne4->Imposta + $enne4->Imponibile;*/

            $field_tributo['CC'] = $c;
            $field_tributo['Comune_ID'] = $comune_id+1;
            $field_tributo['Partita_ID'] = $new_partita_ID;
            $field_tributo['Anno_Tributo'] = $enne4["Anno_Tributo"];
            $field_tributo['Codice_Tributo'] = $enne4["Codice_Tributo"];
            $field_tributo['Imposta'] = $enne4["Imposta"] + $enne4["Imponibile"];


			
			if($enne4["Data_Decorrenza_Interessi"]!="0000-00-00" && $enne4["Data_Decorrenza_Interessi"]!="" && $enne4["Data_Decorrenza_Interessi"]!=null)
			{
				//$field_tributo[] = 'Data_Decorrenza_Interessi'; $value_tributo[] = $enne4->Data_Decorrenza_Interessi;
                $field_tributo['Data_Decorrenza_Interessi'] = $enne4["Data_Decorrenza_Interessi"];
			}
			/*$field_tributo[] = 'Info_Cartella';				$value_tributo[] = $info_cartella;//$enne4->Info_Cartella;
			$field_tributo[] = 'Tipo_Info';					$value_tributo[] = $enne4->Tipo_Info;*/

            $field_tributo['Info_Cartella'] = $info_cartella;
            $field_tributo['Tipo_Info'] = $enne4["Tipo_Info"];
			
			if($enne4["Tipo_Info"]=="E")
			{
				/*$field_tributo[] = 'Titolo_Entrata'; 			$value_tributo[] = $enne4->Titolo_Entrata;
				$field_tributo[] = 'Descrizione_Entrata';		$value_tributo[] = $enne4->Descrizione_Entrata;*/

                $field_tributo['Titolo_Entrata'] = $enne4["Titolo_Entrata"];
                $field_tributo['Descrizione_Entrata'] = $enne4["Descrizione_Entrata"];
			}
			else if($enne4["Tipo_Info"]=="S")
			{
				/*$field_tributo[] = 'Tipo_Sanzione';				$value_tributo[] = $enne4->Tipo_Sanzione;
				$field_tributo[] = 'Titolo_Sanzione';			$value_tributo[] = $enne4->Titolo_Sanzione;
				$field_tributo[] = 'Data_Sanzione';				$value_tributo[] = $enne4->Data_Sanzione;
				$field_tributo[] = 'Targa_Sanzione';			$value_tributo[] = $enne4->Targa_Sanzione;*/

                $field_tributo['Tipo_Sanzione'] = $enne4["Tipo_Sanzione"];
                $field_tributo['Titolo_Sanzione'] = $enne4["Titolo_Sanzione"];
                $field_tributo['Data_Sanzione'] = $enne4["Data_Sanzione"];
                $field_tributo['Targa_Sanzione'] = $enne4["Targa_Sanzione"];
			}
			else if($enne4["Tipo_Info"]=="M")
			{
				//$field_tributo[] = 'Matricola';					$value_tributo[] = $enne4->Matricola;

                $field_tributo['Matricola'] = $enne4["Matricola"];
			}
			
			if($enne4["Codice_Tributo"] == "S_02")
			{
				/*$field_tributo[] = 'Scorporo_Tributo';				$value_tributo[] = $enne4->Scorporo_Tributo;
				$field_tributo[] = 'Scorporo_Interessi';			$value_tributo[] = $enne4->Scorporo_Interessi;
				$field_tributo[] = 'Scorporo_Spese_Ricerca';		$value_tributo[] = $enne4->Scorporo_Spese_Ricerca;
				$field_tributo[] = 'Scorporo_Spese_Notifica';		$value_tributo[] = $enne4->Scorporo_Spese_Notifica;
				$field_tributo[] = 'Scorporo_Eca';					$value_tributo[] = $enne4->Scorporo_Eca;
				$field_tributo[] = 'Scorporo_Tributo_Provinciale';	$value_tributo[] = $enne4->Scorporo_Tributo_Provinciale;*/

                $field_tributo['Scorporo_Tributo'] = $enne4["Scorporo_Tributo"];
                $field_tributo['Scorporo_Interessi'] = $enne4["Scorporo_Interessi"];
                $field_tributo['Scorporo_Spese_Ricerca'] = $enne4["Scorporo_Spese_Ricerca"];
                $field_tributo['Scorporo_Spese_Notifica'] = $enne4["Scorporo_Spese_Notifica"];
                $field_tributo['Scorporo_Eca'] = $enne4["Scorporo_Eca"];
                $field_tributo['Scorporo_Tributo_Provinciale'] = $enne4["Scorporo_Tributo_Provinciale"];
				
				$salva = array();//new pagamento(null, $c);
			
				$salva["Importo"] = $enne4["Imposta"];
				$amount = 0;
				foreach($a_splitNumber as $key=>$value){
                    switch($key){
                        case "imposta_principale":
                            $amount = $enne4["Scorporo_Tributo"];
                            break;
                        case "interessi":
                            $amount = $enne4["Scorporo_Interessi"];
                            break;
                        case "spese_notifica":
                            $amount = $enne4["Scorporo_Spese_Notifica"];
                            break;
                        case "spese_ricerca":
                            $amount = $enne4["Scorporo_Spese_Ricerca"];
                            break;
                        case "eca":
                            $amount = $enne4["Scorporo_Eca"];
                            break;
                        case "tributo_provinciale":
                            $amount = $enne4["Scorporo_Tributo_Provinciale"];
                            break;
                    }

                    if($amount>0)
                        $salva[$value] = $amount;
                }
                
				$salva["Tipo_Atto"] = "Precedenti";
				$salva["CC"] = $c;
				$salva["Partita_ID"] = $new_partita_ID;
				$salva["Data_Registrazione"] = date('Y-m-d');
				$control_salva = $cls_db->DbSave($cls_utils->GetObjectQuery($salva,"pagamento"));// $salva->Insert();
			}

            $ID_tributo = $cls_db->DbSave($cls_utils->GetObjectQuery($field_tributo,"tributo"));//table_insert_record("tributo", $field_tributo, $value_tributo);
			//$ID_tributo = mysql_insert_id();
			
			$query = "UPDATE 290_n4 SET Flag_Importazione = 'ok' WHERE ID = '".$enne4["ID"]."'";
			$cls_db->ExecuteQuery($query);//safe_query($query);

		}		
		
	}//CHIUSURA FOR N2
	
	$numero_ruolo++;
	if($numero_ruolo == $duenovanta["Record_N1"])
	{
		echo "<script>fine();</script>";
	}
	else
	{
		echo "<script>ruolo_next();</script>";
	}
}
?>

<?php include(INC."/footer.php"); ?>