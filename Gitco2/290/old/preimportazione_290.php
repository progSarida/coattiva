<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include(INC."/header.php");
include(INC."/menu.php");
include_once(CLS."/cls_Utils.php");
include_once(CLS."/cls_CoazioneUtils.php");
include_once(CLS."/cls_DateTimeInLine.php");
include_once(CLS."/cls_ControlData.php");

if($_SESSION['username']==NULL)
{
	header("Location:".WEB_ROOT."/autenticazione/accesso_negato.php");
	die;
}

$cls_coaz = new cls_Coazione();
$cls_utils = new cls_Utils();
$cls_date = new cls_DateTimeI("DB",false);
$check = new ControlData();

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');
$control_submit = $cls_help->getVar('submit_file');


$query = "SELECT * FROM enti_gestiti WHERE CC = '".$c."'";

$comune = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"enti_gestiti");// new ente_gestito($c);
$codice_290 = $comune["Codice_290"];
$autorizzazione = $comune["Autorizzazione"];
$nome_comune = $comune["Denominazione"];

$nome_comune =($nome_comune==NULL?"":$nome_comune." [".$c."]");
$nome_user = "Operatore: ".$_SESSION['username'];

$testo_codice = "ATTENZIONE! Codice Ente per procedura di importazione 290 mancante per il Comune di ".$nome_comune."! ";
$testo_codice.= "Aggiungere il codice nei parametri per eseguire l'importazione.";


?>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>
    var modifica = 0;
    var operatore = "<?php echo $_SESSION['username']; ?>";

    //F5
    switchMenuImg("F5");
    F5_button = function()
    {
        location.href = "preimportazione_290.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }

//F11-F12 sono nel menu'
</script>	

<script>
function importa()
{
	location.href = "controlli_importazione_290.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&id_n0="+id_n0+"&solo_rate=0";
}

function rate()
{
	location.href = "controlli_importazione_290.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>&id_n0="+id_n0+"&solo_rate=1";
}

function bonifica()
{
	location.href = "bonifica_290.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
}

function abilitaconferma()
{
	if ($("#tastosfoglia").val() != "")
		$("#tastoconferma").attr("disabled", false);

	else if ($("#tastosfoglia").val() == "")
		$("#tastoconferma").attr("disabled", "disabled");
}

function fine()
{
	$( "#progress_bar" ).progressbar({value: 100 });
	$( "#barlabel" ).text("Preimportazione completata!");
	$( "div#importazione" ).append("<input id='buttonImportazione290' type=button name=avanti class='btn btn-primary' value='Controlli Importazione' onclick='importa();'>");

    $("#buttonImportazione290").trigger("click");
	if(operatore=="mirkop")
		$( "div#importazione" ).append("<input title='Importa solo rateizzazioni per partite presenti in archivio' type=button name=avanti class=sfondo_red value='Rateizzazione' onclick='rate();'>");
}

function ennezero()
{
	$('#progress_bar').progressbar();
	$( "#barlabel" ).text("Controllo N0 N9");
}

function enneuno()
{
	$('#progress_bar').progressbar();
	$( "#barlabel" ).text("Controllo N1 N5");
}

function control_ente()
{
	ritorno = confirm("!!!ATTENZIONE!!!\n\nIl file che si sta per importare non ha informazioni riguardanti il Codice Catastale del comune.\nEffettuare ugualmente l'importazione per il Comune di <?php echo $nome_comune; ?>?");
	if(ritorno)
	{
	}
	else
	{
		alert("Importazione annullata!");
		annulla();
	}
}

</script>

<div class="row justify-content-md-center ">
    <div class="col col-md-auto text_center">
        <span class="titolo font22 under_decor">Preimportazione File 290</span>
    </div>
</div>

<div class="row">
    <div class="col-lg-10 col-lg-offset-1" style="color: #ff0000;">
        IMPORTANTE<br><br>
        Durante l'importazione del file 290 e' assolutamente necessario non interrompere la procedura o uscire dalle pagine.<br>
        Seguire i passaggi necessari fino a completamento avvenuto.<br><br>

        1) Preimportazione<br>
        2) Controlli<br>
        3) Importazione per Ruolo<br>
        4) Riepilogo dei dati<br><br>

        L'eventuale uscita, soprattutto durante l'importazione per ruolo,
        potrebbe causare errori o dati incompleti.<br>
        Nel caso di blocco inaspettato dell'importazione cliccare sul tasto bonifica importazione.

        ATTENZIONE! TESTARE CONTROLLO CODICE FISCALE PER OMOCODICI!
    </div>
</div>
<div class="row">
    <div class="col col-lg-offset-9 col-lg-2">
        <input type=button value="Bonifica importazione" class="btn btn-danger" onclick="bonifica();">
    </div>
</div>

<div class="row justify-content-md-center ">
    <div class="col col-md-auto text_center">
        <span class="titolo font16 under_decor">Carica File</span>
    </div>
</div>
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <form id=form_290 name=form_290 method="post" action="preimportazione_290.php" enctype="multipart/form-data">
                <input type="hidden" name="c" value="<?php echo $c?>">
                <input type="hidden" name="a" value="<?php echo $a?>">
                <input type="hidden" name="submit_file" value="1">
            <div class="row">
                <div class="col col-lg-12" style="margin-top: 2%;" >
                    <div class="form-group">
                        <!--<label class="col-lg-4 control-label resize " style="text-align: left;">Data notifica</label>-->
                        <div class="col-lg-12">
                            <input type="file" accept=".txt,.001,.002,.003,.004,.005" size="50" name="file290" id="tastosfoglia" class="resize form-control" onchange="abilitaconferma()">
                        </div>
                    </div>
                </div>
            </div>

                <?php
                if($autorizzazione==3 || $_SESSION['username']=="mirkop" || $_SESSION['username']=="gianluca"){?>
                <div class="row">
                    <div class="col col-lg-12" style="margin-top: 2%;" >
                        <div class="form-group">
                            <label class="col-lg-4 control-label resize " style="text-align: left;">Modifica Descrizione partita</label>
                            <div class="col-lg-18">
                                <input type="text" class="width50" name="descrizione_partita" value="" class="resize form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                }
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        Importazione Studio K (Aggiungi CF/PI nelle Info cartella) <input type="checkbox" name="flag_k" value="y">
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-2">
                        <input type="submit" disabled size="10" id="tastoconferma" class="btn btn-primary" value="Conferma">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row justify-content-md-center ">
        <div class="col col-md-auto text_center">
            <div class="table_interna text_center" id="progress_bar" style="height:55px;">
                <div class="text_center" id="barlabel"></div>
            </div>
        </div>
    </div>
        <div class="row">
            <div class="col-lg-10 col-lg-offset-1">
                <div id=importazione></div>
            </div>
        </div>



<?php 

if($control_submit==1) {

    $addInfoCartella = $cls_help->getVar("descrizione_partita");
    $studioKFlag = $cls_help->getVar("flag_k");
    if ($codice_290 == "" || $codice_290 == 0) {


        $cls_help->alert($comune["Autorizzazione"]." ".$testo_codice);
        die;
    }


$percorso_file = $_FILES['file290']['tmp_name'];
$nome_file = $_FILES['file290']['name'];

$elem_nome = explode("_",$nome_file);
$lista_comuni = $cls_coaz->Array_Selezione_Comuni(1,"****");

$control_ente = 0;
$control_elemento = 0;
$control_codice_catastale = "";
for($i=0;$i<count($lista_comuni);$i++)
{
	if(isset($elem_nome[3]))
	{
		if($lista_comuni[$i]['CC'] == $elem_nome[3])
		{
			$control_codice_catastale = $elem_nome[3];
			$control_ente = 1;	
		}
		$control_elemento = 1;
	}
}

if($control_ente == 0 && $control_elemento == 1)
{?>
<script>control_ente();</script>	
	
<?php }


$path_file = SUPER_ROOT."/290/";
$file290 = $path_file.$nome_file;

if($percorso_file != "")
{
    $cls_utils->crea_dir(SUPER_ROOT."/290");
	$v = move_uploaded_file($percorso_file, $file290);
}

$fopen290 = fopen($file290 , "r");
$testo290 = fread($fopen290, filesize($file290));
fclose($fopen290);
unlink($file290);

$listaN = explode("\r\n", $testo290);
$num = count($listaN);
if($num==1){
    $listaN = explode("\n", $testo290);
    $num = count($listaN);
}


//	CONTEGGIO N0 N1 N2 N3 N4 N5 N9	//
/**

$countN1 = substr_count($testo290, "\r\nN1");
alert("N1 - ".$countN1);
$countN2 = substr_count($testo290, "\r\nN2");
alert("N2 - ".$countN2);
$countN3 = substr_count($testo290, "\r\nN3");
alert("N3 - ".$countN3);
$countN4 = substr_count($testo290, "\r\nN4");
alert("N4 - ".$countN4);
$countN5 = substr_count($testo290, "\r\nN5");
alert("N5 - ".$countN5);

alert("Totale tra N0 e N9 - " . ( $countN1 + $countN2 + $countN3 + $countN4 + $countN5 + 2 ));

die;

*/

$tot_num = 0;

$ctrl290['N0'] = substr($listaN[0],0,2);

for($i=1;$i<$num;$i++)
{
	
	if($listaN[$num-$i]!="")
	{
		$ctrl290['N9'] = substr($listaN[$num-$i],0,2);
		$num = $num - $i + 1;
		break;
	}
}

flush();
ob_flush();
echo "<script>ennezero();</script>";
flush();
ob_flush();


//$cls_help->alert($ctrl290['N0']." ".$ctrl290['N9']);
if($ctrl290['N0'] == "N0" && $ctrl290['N9'] == "N9")
{
/**
		N0	Record di inizio File
			DATI GENERALI DELLA FORNITURA
*/

//		CODICE ENTE IMPOSITORE (coincide con N9)
		$codice_ente__N0 = substr($listaN[0],2,5);			

//		DATA INVIO FORNITURA
		$data_invio = substr($listaN[0],7,8);

		$anno_invio = substr($data_invio,0,4);
		$mese_invio = substr($data_invio,4,2);
		$giorno_invio = substr($data_invio,6,2);

		$data_invio__N0 = $giorno_invio."/".$mese_invio."/".$anno_invio;

        if($data_invio__N0 == "//" || $data_invio__N0 == '00/00/0000')
        {
            flush();
            ob_flush();
            echo "<script>$('#barlabel').text('Errore: Data Fornitura richiesta!! (N0 da 8 a 15). L\'intero File verra\' scartato.')</script>";
            flush();
            ob_flush();

            die;
        }

/**
		N9	Record di chiusura File
			DATI RIEPILOGATIVI DELLA FORNITURA
*/

//		CODICE ENTE IMPOSITORE (coincide con N0)
		$codice_ente__N9 = substr($listaN[$num-1],2,5);	

        //echo "<h1>".$codice_ente__N9."</h1>";
//		NUMERO RECORD TOTALI (compresi N0 e N9)
		$numero_record__N9 = intval(substr($listaN[$num-1],7,7));

//		NUMERO RECORD N1
		$num_record_N1__N9 = intval(substr($listaN[$num-1],14,7));
//		NUMERO RECORD N2
		$num_record_N2__N9 = intval(substr($listaN[$num-1],21,7));
//		NUMERO RECORD N3
		$num_record_N3__N9 = intval(substr($listaN[$num-1],28,7));
//		NUMERO RECORD N4
		$num_record_N4__N9 = intval(substr($listaN[$num-1],35,7));
//		NUMERO RECORD N5
		$num_record_N5__N9 = intval(substr($listaN[$num-1],42,7));
		

	if( $codice_ente__N9 != $codice_290 || ( $c != $control_codice_catastale && $control_ente == 1 ))
	{
		flush();
		ob_flush();
		//alert($codice_ente__N9." ".$codice_290);
		echo "<script>$('#barlabel').text(\"Errore: Codice ente 290 diverso dal codice ente del Comune di gestione.\")</script>";
		flush();
		ob_flush();
		die;
	}

	if($codice_ente__N0 != $codice_ente__N9 || $codice_ente__N0 != $codice_290 )
	{
		flush();
		ob_flush();
		echo "<script>$('#barlabel').text(\"Errore: Incongruenza del Codice Ente.\")</script>";
		flush();
		ob_flush();
		die;
	}
	else if ( $num != $numero_record__N9 )
	{
		flush();
		ob_flush();
		echo "<script>$('#barlabel').text(\"Errore: Numero record effettivo $num diverso da quello segnalato su N9 $numero_record__N9.\")</script>";
		flush();
		ob_flush();

		die;
	}
	else
	{
		//CREAZIONE ARRAY PER INSERIMENTO IN tabella 290_n0_n9
		//$field_N0 = array();
		//$value_N0 = array();

		/*$field_N0[] = 'Codice_Ente'; 				$value_N0[] = $codice_ente__N0;
		$field_N0[] = 'Data_Invio_Fornitura'; 		$value_N0[] = to_mysql_date($data_invio__N0);
		$field_N0[] = 'Record_Totali'; 				$value_N0[] = $numero_record__N9;
		$field_N0[] = 'Record_N1'; 					$value_N0[] = $num_record_N1__N9;
		$field_N0[] = 'Record_N2'; 					$value_N0[] = $num_record_N2__N9;
		$field_N0[] = 'Record_N3'; 					$value_N0[] = $num_record_N3__N9;
		$field_N0[] = 'Record_N4'; 					$value_N0[] = $num_record_N4__N9;
		$field_N0[] = 'Record_N5'; 					$value_N0[] = $num_record_N5__N9;*/
        $field_N0 = array();
        $field_N0['Codice_Ente'] = $codice_ente__N0;
        $field_N0['Data_Invio_Fornitura'] = $cls_date->GetDateDB($data_invio__N0,"IT");
        $field_N0['Record_Totali'] = $numero_record__N9;
        $field_N0['Record_N1'] = $num_record_N1__N9;
        $field_N0['Record_N2'] = $num_record_N2__N9;
        $field_N0['Record_N3'] = $num_record_N3__N9;
        $field_N0['Record_N4'] = $num_record_N4__N9;
        $field_N0['Record_N5'] = $num_record_N5__N9;
//		print_r($field_N0);
//        print_r($value_N0);

		$id_N0 =$cls_db->DbSave($cls_utils->GetObjectQuery($field_N0,"290_n0_n9"));// table_insert_record('290_n0_n9', $field_N0, $value_N0);
		

		if($id_N0==0)
		{
			flush();
			ob_flush();
			echo "<script>$('#barlabel').text(\"Errore: Inserimento dati nel Db fallito!\")</script>";
			flush();
			ob_flush();
			
			die;
		}
		echo "<script>var id_n0 = ".$id_N0.";</script>";
	}
}
else
{
	flush();
	ob_flush();
	echo "<script>$('#barlabel').text(\"Errore: Mancanza di almeno un record tra N0 e N9.\nL'intero File verra' scartato.\")</script>";
	flush();
	ob_flush();
	
	die;
}

echo "<script>enneuno();</script>";
flush();
ob_flush();
sleep(2);

for($y=0; $y<$num ; $y++)
{
	flush();
	ob_flush();
	$tipoN = substr($listaN[$y],0,2);

	switch($tipoN)
	{
		case "N1":

/**
			 N1	Record di inizio Ruolo
			 	RECORD CHE IDENTIFICA IL RUOLO
			 	(deve essere sempre presente)
*/

//				CODICE COMUNE DI ISCRIZIONE A RUOLO (coincide con N5)
			 	$codice_comune__N1 = intval(substr($listaN[$y],2,6));

//				PROGRESSIVO MINUTA (coincide con N5)
				$progr_minuta__N1 = intval(substr($listaN[$y],8,2));

//				TIPO RUOLO
//				1 = principale
//				2 = suppletivo
//				3 = straordinario
//				4 = speciale
//				5 = fallito
				$tipo_ruolo__N1 = intval(substr($listaN[$y],10,1));

//				NUMERO RUOLO
//				se previsto un numero di ruolo fisso
				$num_ruolo__N1 = intval(substr($listaN[$y],11,4));

//				NUMERO RATE
				$num_rate__N1 = intval(substr($listaN[$y],15,2));

//				RUOLO
//				0 = normale
//				1 = coattivo
				$ruolo__N1 = intval(substr($listaN[$y],17,1));

//				CODICE SEDE
//				solo se previsto
				$cod_sede__N1 = intval(substr($listaN[$y],18,4));

//				TIPO COMPENSO
//				4 = compenso a carico del contribuente
//				5 = compenso a carico dell'ente impositore
				$tipo_compenso__N1 = intval(substr($listaN[$y],22,1));

//				RUOLO I.C.I.A.P.
//				1 = ruolo i.c.i.a.p.
				$ruolo_ICIAP__N1 = intval(substr($listaN[$y],41,1));

//				NUMERO CONVENZIONE
//				impostato solo da Enti con convenzioni per fissare particolari compensi di riscossione
				$num_convenzione__N1 = intval(substr($listaN[$y],42,2));

//				FLAG ARTICOLI
//				4 = ART. 64 DPR.43/88
//				5 = ART. 65 DPR.43/88
//				9 = ART. 89 DPR.43/88
				$flag__N1 = substr($listaN[$y],44,1);
	
			break;

		case "N5":

/**
			N5	Record di chiusura del ruolo
				RECORD CHE CHIUDE IL RUOLO E NE RIEPILOGA IL CONTENUTO
				(deve essere sempre presente)
*/

//				CODICE COMUNE DI ISCRIZIONE A RUOLO (coincide con N1)
				$codice_comune__N5 = intval(substr($listaN[$y],2,6));

//				PROGRESSIVO MINUTA (coincide con N5)
 				$progr_minuta__N5 = intval(substr($listaN[$y],8,2));

//				NUMERO RECORD TOTALI (compresi N1 e N5)
				$numero_record__N5 = intval(substr($listaN[$y],10,7));

//				NUMERO RECORD N2
				$num_record_N2__N5 = intval(substr($listaN[$y],17,7));
//				NUMERO RECORD N3
				$num_record_N3__N5 = intval(substr($listaN[$y],24,7));
//				NUMERO RECORD N4
				$num_record_N4__N5 = intval(substr($listaN[$y],31,7));

//				TOTALE IMPOSTA
				$intero_imposta = intval(substr($listaN[$y],38,13));
				$decimali_imposta = intval(substr($listaN[$y],51,2));

				$totale_imposta__N5 = $intero_imposta.".".$decimali_imposta;

		if($codice_comune__N1 != $codice_comune__N5)
		{
			echo "<script>$('#barlabel').text(\"Errore: Il Codice Ente di N1 non coincide con quello di N5.\")</script>";
            die;
			break;
		}
		if($progr_minuta__N1 != $progr_minuta__N5)
		{
			echo "<script>$('#barlabel').text(\"Errore: Il Progressivo Minuta di N1 non coincide con quello di N5.\")</script>";
			die;
			break;
		}


				//CREAZIONE ARRAY PER INSERIMENTO IN tabella 290_n1_n5
				/*$field_N1 = array();
				$value_N1 = array();

				$field_N1[] = 'N0_ID'; 						$value_N1[] = $id_N0;
				$field_N1[] = 'Progressivo_Minuta';			$value_N1[] = $progr_minuta__N1;
				$field_N1[] = 'Codice_Ente';				$value_N1[] = $codice_comune__N1;*/
                $field_N1 = array();
                $field_N1['N0_ID'] = $id_N0;
                $field_N1['Progressivo_Minuta'] = $progr_minuta__N1;
                $field_N1['Codice_Ente'] = $codice_comune__N1;

if($codice_comune__N1==0){
	echo "<script>$('#barlabel').text(\"Errore: Il Codice Comune di N1 e' inesistente.\")</script>";
    die;
}

				/*$field_N1[] = 'Tipo_Ruolo'; 				$value_N1[] = $tipo_ruolo__N1;
				$field_N1[] = 'Num_Ruolo'; 					$value_N1[] = $num_ruolo__N1;
				$field_N1[] = 'Num_Rate'; 					$value_N1[] = $num_rate__N1;*/

                $field_N1['Tipo_Ruolo'] = $tipo_ruolo__N1;
                $field_N1['Num_Ruolo'] = $num_ruolo__N1;
                $field_N1['Num_Rate'] = $num_rate__N1;

if($num_rate__N1==0){
	echo "<script>$('#barlabel').text(\"Errore: Il numero di rate non e' previsto.\")</script>";
    die;
}
				
				/*$field_N1[] = 'Ruolo'; 						$value_N1[] = $ruolo__N1;
				$field_N1[] = 'Codice_Sede'; 				$value_N1[] = $cod_sede__N1;
				$field_N1[] = 'Tipo_Compenso'; 				$value_N1[] = $tipo_compenso__N1;
				$field_N1[] = 'Ruolo_ICIAP'; 				$value_N1[] = $ruolo_ICIAP__N1;
				$field_N1[] = 'Num_Convenzione'; 			$value_N1[] = $num_convenzione__N1;
				$field_N1[] = 'Flag_Articoli'; 				$value_N1[] = $flag__N1;
				$field_N1[] = 'Totale_Record_N1_N5';		$value_N1[] = $numero_record__N5;
				$field_N1[] = 'Record_N2';					$value_N1[] = $num_record_N2__N5;
				$field_N1[] = 'Record_N3';					$value_N1[] = $num_record_N3__N5;
				$field_N1[] = 'Record_N4';					$value_N1[] = $num_record_N4__N5;
				$field_N1[] = 'Totale_Imposta';				$value_N1[] = $totale_imposta__N5;*/

                $field_N1['Ruolo'] = $ruolo__N1;
                $field_N1['Codice_Sede'] = $cod_sede__N1;
                $field_N1['Tipo_Compenso'] = $tipo_compenso__N1;
                $field_N1['Ruolo_ICIAP'] = $ruolo_ICIAP__N1;
                $field_N1['Num_Convenzione'] = $num_convenzione__N1;
                $field_N1['Flag_Articoli'] = $flag__N1;
                $field_N1['Totale_Record_N1_N5'] = $numero_record__N5;
                $field_N1['Record_N2'] = $num_record_N2__N5;
                $field_N1['Record_N3'] = $num_record_N3__N5;
                $field_N1['Record_N4'] = $num_record_N4__N5;
                $field_N1['Totale_Imposta'] = $totale_imposta__N5;
			
				$id_N1[$progr_minuta__N1] = $cls_db->DbSave($cls_utils->GetObjectQuery($field_N1,"290_n1_n5"));// table_insert_record('290_n1_n5', $field_N1, $value_N1);

				$tot_num += $numero_record__N5;

			break;
	}
}
sleep(2);
if( $tot_num != $num - 2 )
{
    $cls_help->alert($tot_num." ".($num - 2));
	echo "<script>$('#barlabel').text(\"Errore: Numero record riportati negli N5 incongruenti con il totale riportato su N9.\")</script>";
	//mysql_query("DELETE FROM 290_n0_n9 WHERE ID = '".$id_N0."'");
	$cls_db->Delete("290_n0_n9"," ID = '".$id_N0."'");
	die;
}
else
{
    $id_N2 = null;
    $check_cod_partita = "****";
    $check_utente = "****";
    $conta_partita = 0;
	for($y=0; $y<$num ; $y++)
	{
		$tipoN = substr($listaN[$y],0,2);

		switch($tipoN)
		{
			case "N2":

				set_time_limit(30);
/**
	 		N2	Record Intestatario Ruolo
		 		RECORD CHE DESCRIVE I DATI ANAGRAFICI DELL'INTESTATARIO
		 		(deve essere sempre presente)
*/

//				NATURA GIURIDICA
                $natura_giuridica__N2 = intval(substr($listaN[$y],208,1));

                $cognome__N2 = "";
                $nome__N2 = "";
                $ditta__N2 = "";

                if($natura_giuridica__N2 == "1")
                {
                    //				CODICE FISCALE
//				nel caso di persona giuridica si inserisce la Partita Iva
                    $codice_fiscale__N2 = ltrim(rtrim(substr($listaN[$y],24,16)));

//				COGNOME
                    $cognome = ltrim(rtrim(substr($listaN[$y],209,24)));

                    $expCognome = explode(" ",$cognome);
                    for($l=0;$l<count($expCognome);$l++){
                        $expCognome[$l] = trim($expCognome[$l]);
                    }
                    if (($key = array_search("", $expCognome)) !== false) {
                        unset($expCognome[$key]);
                    }
                    $cognome__N2 = implode(" ",$expCognome);

//				NOME
                    $nome = ltrim(rtrim(substr($listaN[$y],233,20)));
                    $expNome = explode(" ",$nome);
                    for($l=0;$l<count($expNome);$l++){
                        $expNome[$l] = trim($expNome[$l]);
                    }
                    if (($key = array_search("", $expNome)) !== false) {
                        unset($expNome[$key]);
                    }
                    $nome__N2 = implode(" ",$expNome);
                    $nome__N2 = str_replace("*","",$nome__N2);
                    $check_new_utente = $cognome__N2." ".$nome__N2;
//				SESSO
                    $sesso__N2 = ltrim(rtrim(substr($listaN[$y],253,1)));

//				DATA DI NASCITA
                    $data_nascita = substr($listaN[$y],254,8);

                    $giorno_nascita = substr($data_nascita,0,2);
                    $mese_nascita = substr($data_nascita,2,2);
                    $anno_nascita = substr($data_nascita,4,4);

                    $data_nascita__N2 = $giorno_nascita."/".$mese_nascita."/".$anno_nascita;

//				CC_NASCITA
                    $cc_nascita__N2 = ltrim(rtrim(substr($listaN[$y],262,4)));

                    if($cc_nascita__N2=="")
                    {
                        $cc_nascita__N2 = substr($codice_fiscale__N2,11,4);
                    }
                    else
                    {
                        $query = "SELECT CC_Paese_Estero FROM paesi_esteri_lista WHERE CC_Paese_Estero = \"".$cc_nascita__N2."\"";
                        $control_CC_1 = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"paesi_esteri_lista")["CC_Paese_Estero"];// single_answer_query($query);
                        $query = "SELECT Com_Codice_Catastale FROM comuni_lista WHERE Com_Codice_Catastale = \"".$cc_nascita__N2."\"";
                        $control_CC_2 = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"comuni_lista")["Com_Codice_Catastale"];//single_answer_query($query);

                        if($control_CC_1!=null)
                        {
                            $cc_nascita__N2 = $control_CC_1;
                        }
                        else if($control_CC_2!=null)
                        {
                            $cc_nascita__N2 = $control_CC_2;
                        }
                        else
                        {
                            $cc_nascita__N2 = substr($codice_fiscale__N2,11,4);
                        }
                    }
                }
                else
                {
                    //				CODICE FISCALE
//				nel caso di persona giuridica si inserisce la Partita Iva
                    $codice_fiscale__N2 = ltrim(rtrim(substr($listaN[$y],24,11)));

//				DITTA
                    $ditta = ltrim(rtrim(substr($listaN[$y],209,76)));
                    $ditta = str_replace('"',"'",$ditta);
                    $expDitta = explode(" ",$ditta);
                    for($l=0;$l<count($expDitta);$l++){
                        $expDitta[$l] = trim($expDitta[$l]);
                    }
                    if (($key = array_search("", $expDitta)) !== false) {
                        unset($expDitta[$key]);
                    }
                    $ditta__N2 = implode(" ",$expDitta);
                    $check_new_utente = $ditta__N2;
                }

                $check_new_cod_partita = trim(substr($listaN[$y],10,14));

                if($check_new_cod_partita!=$check_cod_partita){
                    $ins_cod_partita = $check_new_cod_partita;
                    $conta_partita = 0;
                }
                else if($check_new_utente==$check_utente){
                    $ins_cod_partita = $check_cod_partita.$conta_partita;
                    $conta_partita++;
                }

                $check_cod_partita = $check_new_cod_partita;
                $check_utente = $check_new_utente;


//				CODICE COMUNE DI ISCRIZIONE A RUOLO (coincide con N1)
				$codice_comune__N2 = intval(substr($listaN[$y],2,6));

//				PROGRESSIVO MINUTA (coincide con N1)
				$progr_minuta__N2 = intval(substr($listaN[$y],8,2));

//				CODICE PARTITA
//				codice identificativo intestatario
				$codice_partita__N2 = $ins_cod_partita;
				//echo "N2 --> *".$codice_partita__N2."*";

//				NUMERO CONTRIBUENTE
//				puo' mancare
				$num_contribuente__N2 = intval(substr($listaN[$y],40,8));

//				CODICE CONTROLLO
				$cod_controllo__N2 = intval(substr($listaN[$y],48,2));

/**				INDIRIZZO RESIDENZA					*/
//				CODICE INDIRIZZO
				$codice_indirizzo_res__N2 = intval(substr($listaN[$y],50,6));

//				INDIRIZZO
				$indirizzo_res__N2 = ltrim(rtrim(substr($listaN[$y],56,30)));

//				CIVICO
				$civico_res__N2 = intval(substr($listaN[$y],86,5));

//				LETTERA CIVICO
				$let_civico_res__N2 = ltrim(rtrim(substr($listaN[$y],91,2)));
				/*$controlNum = preg_match("/[0-9]{2,2}/",$let_civico_res__N2, $matches, PREG_OFFSET_CAPTURE);
	
	if($controlNum==1)
	{
		$interno_res__N2 = $let_civico_res__N2;
		$let_civico_res__N2 = "";

        //echo "<h1>controlNum==1 ".$interno_res__N2."</h1>";
	}
	else
	{
        if(preg_match("/[a-zA-Z]{1,2}/",preg_replace('/\s+/', '', $let_civico_res__N2), $matches, PREG_OFFSET_CAPTURE)) $interno_res__N2 = 0;
        else{
            if(strlen(preg_replace('/\s+/', '', $let_civico_res__N2))!=0)
            {
                for($i=0; $i<strlen($let_civico_res__N2); $i++){
                    if(is_numeric($let_civico_res__N2[$i])){
                        $interno_res__N2 = $let_civico_res__N2[$i];
                        $let_civico_res__N2 = "";
                        break;
                    }
                }
            }
            else{
                $interno_res__N2 = 0;
                $let_civico_res__N2 = "";
            }

        }
        //echo "<h1>controlNum!=1 ".$interno_res__N2."</h1>";
	}*/



	/*$control = preg_match("/[[:digit:]]/",$indirizzo_res__N2, $matches, PREG_OFFSET_CAPTURE);
    $indirizzo_res__N2 = str_replace('"', "'", $indirizzo_res__N2);
	if($control==1)
	{
		$civico_res = substr($indirizzo_res__N2,$matches[0][1]);
		$civico_res = str_replace(".", " ", $civico_res);
		$civico_res = str_replace(",", " ", $civico_res);
		$civico_res = str_replace("/", " ", $civico_res);
				
		$civico = explode(" ",$civico_res);
		$num_civico = count($civico);
		
		$controlLet = preg_match("/[[:alpha:]]/",$civico[0], $mCivico, PREG_OFFSET_CAPTURE);
		if($controlLet == 1)
		{
			$lettera = substr($civico[0],$mCivico[0][1]);
			$numero = substr($civico[0],0,$mCivico[0][1]);

			if($civico_res__N2 == 0)
				$civico_res__N2 = $numero;
			if($let_civico_res__N2 == "")
				$let_civico_res__N2 = $lettera;
		}
		else
		{
			$civico_res__N2 = $civico[0];
		}
		
		if($num_civico>1)
		{
			$controlLet2 = preg_match("/[[:alpha:]]/",$civico[1], $mCivico, PREG_OFFSET_CAPTURE);
			if($controlLet2 == 1)
			{
				if($let_civico_res__N2 == "")
					$let_civico_res__N2 = $civico[1];
			}
			else
			{
				if($interno_res__N2 == 0)
					$interno_res__N2 = $civico[1];

                //echo "<h1>controlLet2 == 1 ".$interno_res__N2."</h1>";
			}
		
			if($num_civico>2)
			{
				$controlLet3 = preg_match("/[[:alpha:]]/",$civico[2], $mCivico, PREG_OFFSET_CAPTURE);
				if($controlLet3 == 1)
				{
					if($let_civico_res__N2 == "")
						$let_civico_res__N2 = $civico[2];
				}
				else
				{
					if($interno_res__N2 == 0)
						$interno_res__N2 = $civico[2];

                    //echo "<h1>controlLet3 == 1 ".$interno_res__N2."</h1>";
				}
			}
		}
		
		$indirizzo_res__N2 = substr($indirizzo_res__N2,0,$matches[0][1]-1);
		
	}*/

//				KM
				$km_res__N2 = intval(substr($listaN[$y],93,3)).".".intval(substr($listaN[$y],96,3));

//				CAP
				$cap_res__N2 = ltrim(rtrim(substr($listaN[$y],99,5)));

//				CC_INDIRIZZO
				$cc_res__N2 = ltrim(rtrim(substr($listaN[$y],104,4)));

//				FRAZIONE/LOCALITA'
				$frazione_res__N2 = ltrim(rtrim(substr($listaN[$y],108,21)));

		if($cc_res__N2=="")
		{
			$query = "SELECT Com_Codice_Catastale FROM comuni_lista WHERE Com_Cap = \"".$cap_res__N2."\" OR Com_Nome = \"".$frazione_res__N2."\"";
			$cc_res__N2 = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"comuni_lista")["Com_Codice_Catastale"];//single_answer_query($query);
		}

		if($cc_res__N2=="")
		{
			$query = "SELECT CC_Toponimo FROM toponimi_cappati WHERE Cap = \"".$cap_res__N2."\"";
			$cc_res__N2 = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"toponimi_cappati")["CC_Toponimo"];//single_answer_query($query);
		}
		
		if($cc_res__N2=="")
		{
			$query = "SELECT CC_Paese_Estero FROM paesi_esteri_lista WHERE Nome LIKE \"".$frazione_res__N2."%\"";
			$cc_res__N2 = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"paesi_esteri_lista")["CC_Paese_Estero"];//single_answer_query($query);
		}
		
		if($cap_res__N2 == "" || $cap_res__N2 == "00000")
		{
			$query = "SELECT Com_Cap FROM comuni_lista WHERE Com_Codice_Catastale = \"".$cc_res__N2."\"";
			$cap_res__N2 = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"comuni_lista")["Com_Cap"];//single_answer_query($query);
			
			if( substr($cap_res__N2,3) == "xx" )
			{
				$cap_res__N2 = substr($cap_res__N2,0,3) + "00";
			}
			else if( substr($cap_res__N2,4) == "x" )
			{
				$cap_res__N2 = substr($cap_res__N2,0,4) + "0";
			}
		}


/**		******* FINE INDIRIZZO RESIDENZA	*******				*/

/**				INDIRIZZO DOMICILIO								*/
//				CODICE INDIRIZZO
				$codice_indirizzo_dom__N2 = intval(substr($listaN[$y],129,6));

//				INDIRIZZO
				$indirizzo_dom__N2 = ltrim(rtrim(substr($listaN[$y],135,30)));

//				CIVICO
				$civico_dom__N2 = intval(substr($listaN[$y],165,5));

//				LETTERA CIVICO
				$let_civico_dom__N2 = ltrim(rtrim(substr($listaN[$y],170,2)));
				
		$controlNum = preg_match("/[[:digit:]]/",$let_civico_dom__N2, $matches, PREG_OFFSET_CAPTURE);
		if($controlNum==1)
		{
			$interno_dom__N2 = $let_civico_dom__N2;
			$let_civico_dom__N2 = "";
		}
		else
		{
			$interno_dom__N2 = 0;
		}

		$control = preg_match("/[[:digit:]]/",$indirizzo_dom__N2, $matches, PREG_OFFSET_CAPTURE);
		if($control==1)
		{
			$civico_dom = substr($indirizzo_dom__N2,$matches[0][1]);
			$civico_dom = str_replace(".", " ", $civico_dom);
			$civico_dom = str_replace(",", " ", $civico_dom);
			$civico_dom = str_replace("/", " ", $civico_dom);
	
			$civico = explode(" ",$civico_dom);
				
			$controlLet = preg_match("/[[:alpha:]]/",$civico[0], $mCivico, PREG_OFFSET_CAPTURE);
			if($controlLet == 1)
			{
				$lettera = substr($civico[0],$mCivico[0][1]);
				$numero = substr($civico[0],0,$mCivico[0][1]);

				if($civico_dom__N2 == 0)
					$civico_dom__N2 = $numero;
				if($let_civico_dom__N2 == "")
					$let_civico_dom__N2 = $lettera;
			}
			else
			{
				$civico_dom__N2 = $civico[0];
			}
	
			if(count($civico)>1)
			{
				
			
			$controlLet2 = preg_match("/[[:alpha:]]/",$civico[1], $mCivico, PREG_OFFSET_CAPTURE);
			if($controlLet2 == 1)
			{
				if($let_civico_dom__N2 == "")
					$let_civico_dom__N2 = $civico[1];
			}
			else
			{
				if($interno_dom__N2 == 0)
					$interno_dom__N2 = $civico[1];
			}
			
			if(count($civico)>2)
			{
			
			$controlLet3 = preg_match("/[[:alpha:]]/",$civico[2], $mCivico, PREG_OFFSET_CAPTURE);
			if($controlLet3 == 1)
			{
				if($let_civico_dom__N2 == "")
					$let_civico_dom__N2 = $civico[2];
			}
			else
			{
				if($interno_dom__N2 == 0)
					$interno_dom__N2 = $civico[2];
			}
	
			$indirizzo_dom__N2 = substr($indirizzo_dom__N2,0,$matches[0][1]-1);
	
			}
			}
		}

//				KM
				$km_dom__N2 = intval(substr($listaN[$y],172,6));

//				CAP
				$cap_dom__N2 = ltrim(rtrim(substr($listaN[$y],178,5)));

//				CC_INDIRIZZO
				$cc_dom__N2 = ltrim(rtrim(substr($listaN[$y],183,4)));

//				FRAZIONE/LOCALITA'
				$frazione_dom__N2 = ltrim(rtrim(substr($listaN[$y],187,21)));
				
			if($cc_dom__N2=="")
			{
				$query = "SELECT Com_Codice_Catastale FROM comuni_lista WHERE Com_Cap = \"".$cap_dom__N2."\" OR Com_Nome = \"".$frazione_dom__N2."\"";
				$cc_dom__N2 = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"comuni_lista")["Com_Codice_Catastale"];//single_answer_query($query);
			}

			if($cc_dom__N2=="")
			{
				$query = "SELECT CC_Toponimo FROM toponimi_cappati WHERE Cap = \"".$cap_dom__N2."\"";
				$cc_dom__N2 = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"toponimi_cappati")["CC_Toponimo"];//single_answer_query($query);
			}
			
			if($cc_dom__N2=="")
			{
				$query = "SELECT CC_Paese_Estero FROM paesi_esteri_lista WHERE Nome LIKE \"".$frazione_dom__N2."%\"";
				$cc_dom__N2 = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"paesi_esteri_lista")["CC_Paese_Estero"];//single_answer_query($query);
			}
			
			if($cap_dom__N2 == "" || $cap_dom__N2 == "00000")
			{
				$query = "SELECT Com_Cap FROM comuni_lista WHERE Com_Codice_Catastale = \"".$cc_dom__N2."\"";
				$cap_dom__N2 = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"comuni_lista")["Com_Cap"];//single_answer_query($query);
						
				if( substr($cap_dom__N2,3) == "xx" )
				{
					$cap_dom__N2 = substr($cap_dom__N2,0,3) + "00";
				}
				else if( substr($cap_dom__N2,4) == "x" )
				{
					$cap_dom__N2 = substr($cap_dom__N2,0,4) + "0";
				}
				
			}

/**		******* FINE INDIRIZZO DOMICILIO	*******				*/


//				INDICATORE DI PARTITA COINTESTATA
				$cointestatari__N2 = ltrim(rtrim(substr($listaN[$y],285,1)));



				//CREAZIONE ARRAY PER INSERIMENTO IN tabella 290_n2
				/*$field_N2 = array();
				$value_N2 = array();

				$field_N2[] = 'N1_ID'; 						$value_N2[] = $id_N1[$progr_minuta__N2];
				$field_N2[] = 'N0_ID'; 						$value_N2[] = $id_N0;
				$field_N2[] = 'Progressivo_Minuta';			$value_N2[] = $progr_minuta__N2;
				$field_N2[] = 'Codice_Ente';				$value_N2[] = $codice_comune__N2;
				$field_N2[] = 'Codice_Partita'; 			$value_N2[] = $codice_partita__N2;
				$field_N2[] = 'Codice_Fiscale'; 			$value_N2[] = $codice_fiscale__N2;
				$field_N2[] = 'Numero_Contribuente'; 		$value_N2[] = $num_contribuente__N2;
				$field_N2[] = 'Codice_Controllo'; 			$value_N2[] = $cod_controllo__N2;*/
                $field_N2 = array();
                $field_N2["N1_ID"] = $id_N1[$progr_minuta__N2];
                $field_N2["N0_ID"] = $id_N0;
                $field_N2["Progressivo_Minuta"] = $progr_minuta__N2;
                $field_N2["Codice_Ente"] = $codice_comune__N2;
                $field_N2["Codice_Partita"] = $codice_partita__N2;
                $field_N2["Codice_Fiscale"] = $codice_fiscale__N2;
                $field_N2["Numero_Contribuente"] = $num_contribuente__N2;
                $field_N2["Codice_Controllo"] = $cod_controllo__N2;

				/*$field_N2[] = 'Codice_Indirizzo_Res'; 		$value_N2[] = $codice_indirizzo_res__N2;
				$field_N2[] = 'Indirizzo_Res'; 				$value_N2[] = $indirizzo_res__N2;
				$field_N2[] = 'Civico_Res'; 				$value_N2[] = $civico_res__N2;
				$field_N2[] = 'Lettera_Civico_Res'; 		$value_N2[] = $let_civico_res__N2;
				$field_N2[] = 'Interno_Res'; 				$value_N2[] = $interno_res__N2;
				$field_N2[] = 'Km_Res'; 					$value_N2[] = $km_res__N2;
				$field_N2[] = 'Cap_Res';					$value_N2[] = $cap_res__N2;
				$field_N2[] = 'CC_Indirizzo_Res';			$value_N2[] = $cc_res__N2;
				$field_N2[] = 'Frazione_Res';				$value_N2[] = $frazione_res__N2;*/

                $field_N2["Codice_Indirizzo_Res"] = $codice_indirizzo_res__N2;
                $field_N2["Indirizzo_Res"] = utf8_decode($indirizzo_res__N2);
                $field_N2["Civico_Res"] = $civico_res__N2;
                //$cls_help->alert($let_civico_res__N2);
                $field_N2["Lettera_Civico_Res"] = $let_civico_res__N2;
                $field_N2["Interno_Res"] = null;//$interno_res__N2
                $field_N2["Km_Res"] = $km_res__N2;
                $field_N2["Cap_Res"] = $cap_res__N2;
                $field_N2["CC_Indirizzo_Res"] = $cc_res__N2;
                $field_N2["Frazione_Res"] = $frazione_res__N2;
			
				/*$field_N2[] = 'Codice_Indirizzo_Dom'; 		$value_N2[] = $codice_indirizzo_dom__N2;
				$field_N2[] = 'Indirizzo_Dom'; 				$value_N2[] = $indirizzo_dom__N2;
				$field_N2[] = 'Civico_Dom'; 				$value_N2[] = $civico_dom__N2;
				$field_N2[] = 'Lettera_Civico_Dom'; 		$value_N2[] = $let_civico_dom__N2;
				$field_N2[] = 'Interno_Dom'; 				$value_N2[] = $interno_dom__N2;
				$field_N2[] = 'Km_Dom'; 					$value_N2[] = $km_dom__N2;
				$field_N2[] = 'Cap_Dom';					$value_N2[] = $cap_dom__N2;
				$field_N2[] = 'CC_Indirizzo_Dom';			$value_N2[] = $cc_dom__N2;
				$field_N2[] = 'Frazione_Dom';				$value_N2[] = $frazione_dom__N2;*/

                $field_N2["Codice_Indirizzo_Dom"] = $codice_indirizzo_dom__N2;
                $field_N2["Indirizzo_Dom"] = $indirizzo_dom__N2;
                $field_N2["Civico_Dom"] = $civico_dom__N2;
                $field_N2["Lettera_Civico_Dom"] = $let_civico_dom__N2;
                $field_N2["Interno_Dom"] = $interno_dom__N2;
                $field_N2["Km_Dom"] = $km_dom__N2;
                $field_N2["Cap_Dom"] = $cap_dom__N2;
                $field_N2["CC_Indirizzo_Dom"] = $cc_dom__N2;
                $field_N2["Frazione_Dom"] = $frazione_dom__N2;

				//$field_N2[] = 'Natura_Giuridica'; 			$value_N2[] = $natura_giuridica__N2;

                $field_N2["Natura_Giuridica"] = $natura_giuridica__N2;

if($natura_giuridica__N2==1)
{
				/*$field_N2[] = 'Cognome'; 					$value_N2[] = $cognome__N2;
				$field_N2[] = 'Nome'; 						$value_N2[] = $nome__N2;*/

                $field_N2["Cognome"] = $cognome__N2;
                $field_N2["Nome"] = $nome__N2;
				
				if($cognome__N2!="")
				{
					if(strlen($codice_fiscale__N2) > 11 )
					{
						$ctrl_sesso = number_format(substr($codice_fiscale__N2, 9,2));
						if( $ctrl_sesso > 40 ) $sesso__N2 = "F";
						else	$sesso__N2 = "M";
					}
				}
								
				//$field_N2[] = 'Sesso'; 						$value_N2[] = $sesso__N2;

                $field_N2["Sesso"] = $sesso__N2;
				
				/*$field_N2[] = 'Data_Nascita'; 				$value_N2[] = to_mysql_date($data_nascita__N2);
				$field_N2[] = 'CC_Nascita';					$value_N2[] = $cc_nascita__N2;*/

                $field_N2["Data_Nascita"] = $cls_date->GetDateDB($data_nascita__N2,"IT");
                $field_N2["CC_Nascita"] = $cc_nascita__N2;
}
else
{
				//$field_N2[] = 'Ditta';						$value_N2[] = $ditta__N2;

                $field_N2["Ditta"] = $ditta__N2;
}

				//$field_N2[] = 'Cointestatari';				$value_N2[] = $cointestatari__N2;

                $field_N2["Cointestatari"] = $cointestatari__N2;

				if($id_N1[$progr_minuta__N2]==0)break;

				//print_r($cls_utils->GetObjectQuery($field_N2,"290_n2"));
                $check->Reset();
                $check->setTable("290_n2");
                $check->setArrayControl($field_N2);
                $field_N2 = $check->automaticCheck();
                $field_N2["Json_Error"] = str_replace("'","\'",$check->getJson());

                $id_N2[$codice_partita__N2] = $cls_db->DbSave($cls_utils->GetObjectQuery($field_N2,"290_n2"));// table_insert_record('290_n2', $field_N2, $value_N2);

if($id_N2[$codice_partita__N2]==0)
{
	echo "<script>$('#barlabel').text(\"Errore: ID N2  Inserimento dati nel Db fallito!\")</script>";
	
	//mysql_query("DELETE FROM 290_n0_n9 WHERE ID = '".$id_N0."'");
    $cls_db->Delete("290_n0_n9"," ID = '".$id_N0."'");
			
	die;
}

				break;

			case "N3":

/**
			N3	Record Coobbligato/Cointestatario Ruolo
				RECORD CHE DESCRIVE I DATI ANAGRAFICI DEL COOBLIGATO O COINTESTATO
*/

//				CODICE COMUNE DI ISCRIZIONE A RUOLO (coincide con N1)
				$codice_comune__N3 = intval(substr($listaN[$y],2,6));

//				PROGRESSIVO MINUTA (coincide con N1)
				$progr_minuta__N3 = intval(substr($listaN[$y],8,2));

//				CODICE PARTITA (coincide con N2)
//				codice identificativo intestatario
				$codice_partita__N3 = ltrim(rtrim(substr($listaN[$y],10,14)));

//				CODICE FISCALE
//				nel caso di persona giuridica si inserisce la Partita Iva
				$codice_fiscale__N3 = ltrim(rtrim(substr($listaN[$y],24,16)));

/**				INDIRIZZO RESIDENZA			????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????		*/
//				CODICE INDIRIZZO
				$codice_indirizzo_res__N3 = intval(substr($listaN[$y],40,6));

//				INDIRIZZO
				$indirizzo_res__N3 = ltrim(rtrim(substr($listaN[$y],46,30)));

//				CIVICO
				$civico_res__N3 = intval(substr($listaN[$y],76,5));

//				LETTERA CIVICO
				$let_civico_res__N3 = ltrim(rtrim(substr($listaN[$y],81,2)));

/**                       ???????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????      */
				
		$controlNum = preg_match("/[[:digit:]]/",$let_civico_res__N3, $matches, PREG_OFFSET_CAPTURE);
		if($controlNum==1)
		{
			$interno_res__N3 = $let_civico_res__N3;
			$let_civico_res__N3 = "";
		}
		else
		{
			$interno_res__N3 = 0;
		}

		$control = preg_match("/[[:digit:]]/",$indirizzo_res__N3, $matches, PREG_OFFSET_CAPTURE);
        $indirizzo_res__N3 = str_replace('"', "'", $indirizzo_res__N3);
		if($control==1)
		{
			$civico_res = substr($indirizzo_res__N3,$matches[0][1]);
			$civico_res = str_replace(".", " ", $civico_res);
			$civico_res = str_replace(",", " ", $civico_res);
			$civico_res = str_replace("/", " ", $civico_res);

			$civico = explode(" ",$civico_res);


			$controlLet = preg_match("/[[:alpha:]]/",$civico[0], $mCivico, PREG_OFFSET_CAPTURE);
			if($controlLet == 1)
			{
				$lettera = substr($civico[0],$mCivico[0][1]);
				$numero = substr($civico[0],0,$mCivico[0][1]);

				if($civico_res__N3 == 0)
					$civico_res__N3 = $numero;
				if($let_civico_res__N3 == "")
					$let_civico_res__N3 = $lettera;
			}
			else
			{
				$civico_res__N3 = $civico[0];
			}

			$controlLet2 = preg_match("/[[:alpha:]]/",$civico[1], $mCivico, PREG_OFFSET_CAPTURE);
			if($controlLet2 == 1)
			{
				if($let_civico_res__N3 == "")
				$let_civico_res__N3 = $civico[1];
			}
			else
			{
				if($interno_res__N3 == 0)
					$interno_res__N3 = $civico[1];
			}

			if(isset($civico[2])){
                $controlLet3 = preg_match("/[[:alpha:]]/",$civico[2], $mCivico, PREG_OFFSET_CAPTURE);
                if($controlLet3 == 1)
                {
                    if($let_civico_res__N3 == "")
                        $let_civico_res__N3 = $civico[2];
                }
                else
                {
                    if($interno_res__N3 == 0)
                        $interno_res__N3 = $civico[2];
                }
            }

			$indirizzo_res__N3 = substr($indirizzo_res__N3,0,$matches[0][1]-1);

		}

//				KM
				$km_res__N3 = intval(substr($listaN[$y],83,3)).".".intval(substr($listaN[$y],96,3));

//				CAP
				$cap_res__N3 = ltrim(rtrim(substr($listaN[$y],89,5)));

//				CC_INDIRIZZO
				$cc_res__N3 = ltrim(rtrim(substr($listaN[$y],94,4)));

                //FRAZIONE/LOCALITA'
				$frazione_res__N3 = ltrim(rtrim(substr($listaN[$y],98,21)));

	if($cc_res__N3=="")
	{
		$query = "SELECT Com_Codice_Catastale FROM comuni_lista WHERE Com_Cap = \"".$cap_res__N3."\" OR Com_Nome = \"".$frazione_res__N3."\"";
		$cc_res__N3 = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"comuni_lista")["Com_Codice_Catastale"];//single_answer_query($query);
	}

	if($cc_res__N3=="")
	{
		$query = "SELECT CC_Toponimo FROM toponimi_cappati WHERE Cap = \"".$cap_res__N3."\"";
		$cc_res__N3 = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"toponimi_cappati")["CC_Toponimo"];//single_answer_query($query);
	}
	
	if($cc_res__N3=="")
	{
		$query = "SELECT CC_Paese_Estero FROM paesi_esteri_lista WHERE Nome LIKE \"".$frazione_res__N3."%\"";
		$cc_res__N3 = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"paesi_esteri_lista")["CC_Paese_Estero"];//single_answer_query($query);
	}
	
	if($cap_res__N3 == "" || $cap_res__N3 == "00000")
	{
		$query = "SELECT Com_Cap FROM comuni_lista WHERE Com_Codice_Catastale = \"".$cc_res__N3."\"";
		$cap_res__N3 = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"comuni_lista")["Com_Cap"];//single_answer_query($query);
			
		if( substr($cap_res__N3,3) == "xx" )
		{
			$cap_res__N3 = substr($cap_res__N3,0,3) + "00";
		}
		else if( substr($cap_res__N3,4) == "x" )
		{
			$cap_res__N3 = substr($cap_res__N3,0,4) + "0";
		}
	}

//
/**		******* FINE INDIRIZZO RESIDENZA	*******				*/

/**				INDIRIZZO DOMICILIO								*/
//				CODICE INDIRIZZO
				$codice_indirizzo_dom__N3 = intval(substr($listaN[$y],119,6));

//				INDIRIZZO
				$indirizzo_dom__N3 = ltrim(rtrim(substr($listaN[$y],125,30)));

//				CIVICO
				$civico_dom__N3 = intval(substr($listaN[$y],155,5));

//				LETTERA CIVICO
				$let_civico_dom__N3 = ltrim(rtrim(substr($listaN[$y],160,2)));
	$controlNum = preg_match("/[[:digit:]]/",$let_civico_dom__N3, $matches, PREG_OFFSET_CAPTURE);
	if($controlNum==1)
	{
		$interno_dom__N3 = $let_civico_dom__N3;
		$let_civico_dom__N3 = "";
	}
	else
	{
		$interno_dom__N3 = 0;
	}

	$control = preg_match("/[[:digit:]]/",$indirizzo_dom__N3, $matches, PREG_OFFSET_CAPTURE);
	if($control==1)
	{
		$civico_dom = substr($indirizzo_dom__N3,$matches[0][1]);
		$civico_dom = str_replace(".", " ", $civico_dom);
		$civico_dom = str_replace(",", " ", $civico_dom);
		$civico_dom = str_replace("/", " ", $civico_dom);

		$civico = explode(" ",$civico_dom);


		$controlLet = preg_match("/[[:alpha:]]/",$civico[0], $mCivico, PREG_OFFSET_CAPTURE);
		if($controlLet == 1)
		{
			$lettera = substr($civico[0],$mCivico[0][1]);
			$numero = substr($civico[0],0,$mCivico[0][1]);

			if($civico_dom__N3 == 0)
				$civico_dom__N3 = $numero;
			if($let_civico_dom__N3 == "")
				$let_civico_dom__N3 = $lettera;
		}
		else
		{
			$civico_dom__N3 = $civico[0];
		}

		$controlLet2 = preg_match("/[[:alpha:]]/",$civico[1], $mCivico, PREG_OFFSET_CAPTURE);
		if($controlLet2 == 1)
		{
			if($let_civico_dom__N3 == "")
				$let_civico_dom__N3 = $civico[1];
		}
		else
		{
			if($interno_dom__N3 == 0)
				$interno_dom__N3 = $civico[1];
		}

		$controlLet3 = preg_match("/[[:alpha:]]/",$civico[2], $mCivico, PREG_OFFSET_CAPTURE);
		if($controlLet3 == 1)
		{
			if($let_civico_dom__N3 == "")
				$let_civico_dom__N3 = $civico[2];
		}
		else
		{
			if($interno_dom__N3 == 0)
				$interno_dom__N3 = $civico[2];
		}

		$indirizzo_dom__N3 = substr($indirizzo_dom__N3,0,$matches[0][1]-1);

}


//				KM
				$km_dom__N3 = intval(substr($listaN[$y],162,6));

//				CAP
				$cap_dom__N3 = ltrim(rtrim(substr($listaN[$y],168,5)));

//				CC_INDIRIZZO
				$cc_dom__N3 = ltrim(rtrim(substr($listaN[$y],173,4)));

//				FRAZIONE/LOCALITA'
				$frazione_dom__N3 = ltrim(rtrim(substr($listaN[$y],177,21)));
	
	if($cc_dom__N3=="")
	{
		$query = "SELECT Com_Codice_Catastale FROM comuni_lista WHERE Com_Cap = \"".$cap_dom__N3."\" OR Com_Nome = \"".$frazione_dom__N3."\"";
		$cc_dom__N3 = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"comuni_lista")["Com_Codice_Catastale"];//single_answer_query($query);
	}
	
	if($cc_dom__N3=="")
	{
		$query = "SELECT CC_Toponimo FROM toponimi_cappati WHERE Cap = \"".$cap_dom__N3."\"";
		$cc_dom__N3 = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"toponimi_cappati")["CC_Toponimo"];//single_answer_query($query);
	}
	
	if($cc_dom__N3=="")
	{
		$query = "SELECT CC_Paese_Estero FROM paesi_esteri_lista WHERE Nome LIKE \"".$frazione_dom__N3."%\"";
		$cc_dom__N3 = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"paesi_esteri_lista")["CC_Paese_Estero"];//single_answer_query($query);
	}

	if($cap_dom__N3 == "" || $cap_dom__N3 == "00000")
	{
		$query = "SELECT Com_Cap FROM comuni_lista WHERE Com_Codice_Catastale = \"".$cc_dom__N3."\"";
		$cap_dom__N3 = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"comuni_lista")["Com_Cap"];//single_answer_query($query);
			
		if( substr($cap_dom__N3,3) == "xx" )
		{
			$cap_dom__N3 = substr($cap_dom__N3,0,3) + "00";
		}
		else if( substr($cap_dom__N3,4) == "x" )
		{
			$cap_dom__N3 = substr($cap_dom__N3,0,4) + "0";
		}
	}

/**		******* FINE INDIRIZZO DOMICILIO	*******				*/

//				NATURA GIURIDICA
				$natura_giuridica__N3 = intval(substr($listaN[$y],198,1));

if($natura_giuridica__N3 == "1")
{
	
//				COGNOME
				$cognome__N3 = ltrim(rtrim(substr($listaN[$y],199,24)));

//				NOME
				$nome__N3 = ltrim(rtrim(substr($listaN[$y],223,20)));

//				SESSO
				$sesso__N3 = ltrim(rtrim(substr($listaN[$y],243,1)));

//				DATA DI NASCITA
				$data_nascita = substr($listaN[$y],244,8);
			
				$giorno_nascita = substr($data_nascita,0,2);
				$mese_nascita = substr($data_nascita,2,2);
				$anno_nascita = substr($data_nascita,4,4);

				$data_nascita__N3 = $giorno_nascita."/".$mese_nascita."/".$anno_nascita;

//				CC_NASCITA
				$cc_nascita__N3 = ltrim(rtrim(substr($listaN[$y],252,4)));

		if($cc_nascita__N3=="")
		{
				$cc_nascita__N3 = substr($codice_fiscale__N3,11,4);
		}
		else
		{
			$query = "SELECT CC_Paese_Estero FROM paesi_esteri_lista WHERE CC_Paese_Estero = \"".$cc_nascita__N3."\"";
			$control_CC_1 = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"paesi_esteri_lista")["CC_Paese_Estero"];//single_answer_query($query);
			$query = "SELECT Com_Codice_Catastale FROM comuni_lista WHERE Com_Codice_Catastale = \"".$cc_nascita__N3."\"";
			$control_CC_2 = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"comuni_lista")["Com_Codice_Catastale"];//single_answer_query($query);
			
			if($control_CC_1!=null)
			{
				$cc_nascita__N3 = $control_CC_1;
			}
			else if($control_CC_2!=null)
			{
				$cc_nascita__N3 = $control_CC_2;
			}
			else
			{
				$cc_nascita__N3 = substr($codice_fiscale__N3,11,4);
			}
		}
}
else
{

//				DITTA
				$ditta__N3 = ltrim(rtrim(substr($listaN[$y],199,76)));
}


			//CREAZIONE ARRAY PER INSERIMENTO IN tabella 290_n3
			/*$field_N3 = array();
			$value_N3 = array();

			$field_N3[] = 'N2_ID'; 						$value_N3[] = $id_N2[$codice_partita__N3];
			$field_N3[] = 'N0_ID'; 						$value_N3[] = $id_N0;
			$field_N3[] = 'Progressivo_Minuta';			$value_N3[] = $progr_minuta__N3;
			$field_N3[] = 'Codice_Ente';				$value_N3[] = $codice_comune__N3;
			$field_N3[] = 'Codice_Partita'; 			$value_N3[] = $ins_cod_partita;
			$field_N3[] = 'Codice_Fiscale'; 			$value_N3[] = $codice_fiscale__N3;*/
            $field_N3 = array();
            $field_N3["N2_ID"] = $id_N2[$codice_partita__N3];
            $field_N3["N0_ID"] = $id_N0;
            $field_N3["Progressivo_Minuta"] = $progr_minuta__N3;
            $field_N3["Codice_Ente"] = $codice_comune__N3;
            $field_N3["Codice_Partita"] = $ins_cod_partita;
            $field_N3["Codice_Fiscale"] = $codice_fiscale__N3;

			/*$field_N3[] = 'Codice_Indirizzo_Res'; 		$value_N3[] = $codice_indirizzo_res__N3;
			$field_N3[] = 'Indirizzo_Res'; 				$value_N3[] = $indirizzo_res__N3;
			$field_N3[] = 'Civico_Res'; 				$value_N3[] = $civico_res__N3;
			$field_N3[] = 'Lettera_Civico_Res'; 		$value_N3[] = $let_civico_res__N3;
			$field_N3[] = 'Interno_Res'; 				$value_N3[] = $interno_res__N3;
			$field_N3[] = 'Km_Res'; 					$value_N3[] = $km_res__N3;
			$field_N3[] = 'Cap_Res';					$value_N3[] = $cap_res__N3;
			$field_N3[] = 'CC_Indirizzo_Res';			$value_N3[] = $cc_res__N3;
			$field_N3[] = 'Frazione_Res';				$value_N3[] = $frazione_res__N3;*/

            $field_N3["Codice_Indirizzo_Res"] = $codice_indirizzo_res__N3;
            $field_N3["Indirizzo_Res"] = utf8_decode($indirizzo_res__N3);
            $field_N3["Civico_Res"] = $civico_res__N3;
            $field_N3["Lettera_Civico_Res"] = $let_civico_res__N3;
            $field_N3["Interno_Res"] = $interno_res__N3;
            $field_N3["Km_Res"] = $km_res__N3;
            $field_N3["Cap_Res"] = $cap_res__N3;
            $field_N3["CC_Indirizzo_Res"] = $cc_res__N3;
            $field_N3["Frazione_Res"] = $frazione_res__N3;

			/*$field_N3[] = 'Codice_Indirizzo_Dom'; 		$value_N3[] = $codice_indirizzo_dom__N3;
			$field_N3[] = 'Indirizzo_Dom'; 				$value_N3[] = $indirizzo_dom__N3;
			$field_N3[] = 'Civico_Dom'; 				$value_N3[] = $civico_dom__N3;
			$field_N3[] = 'Lettera_Civico_Dom'; 		$value_N3[] = $let_civico_dom__N3;
			$field_N3[] = 'Interno_Dom'; 				$value_N3[] = $interno_dom__N3;
			$field_N3[] = 'Km_Dom'; 					$value_N3[] = $km_dom__N3;
			$field_N3[] = 'Cap_Dom';					$value_N3[] = $cap_dom__N3;
			$field_N3[] = 'CC_Indirizzo_Dom';			$value_N3[] = $cc_dom__N3;
			$field_N3[] = 'Frazione_Dom';				$value_N3[] = $frazione_dom__N3;*/

            $field_N3["Codice_Indirizzo_Dom"] = $codice_indirizzo_dom__N3;
            $field_N3["Indirizzo_Dom"] = $indirizzo_dom__N3;
            $field_N3["Civico_Dom"] = $civico_dom__N3;
            $field_N3["Lettera_Civico_Dom"] = $let_civico_dom__N3;
            $field_N3["Interno_Dom"] = $interno_dom__N3;
            $field_N3["Km_Dom"] = $km_dom__N3;
            $field_N3["Cap_Dom"] = $cap_dom__N3;
            $field_N3["CC_Indirizzo_Dom"] = $cc_dom__N3;
            $field_N3["Frazione_Dom"] = $frazione_dom__N3;

			//$field_N3[] = 'Natura_Giuridica'; 			$value_N3[] = $natura_giuridica__N3;

            $field_N3["Natura_Giuridica"] = $natura_giuridica__N3;

			if($natura_giuridica__N3 == 1)
			{
			/*$field_N3[] = 'Cognome'; 					$value_N3[] = $cognome__N3;
			$field_N3[] = 'Nome'; 						$value_N3[] = $nome__N3;*/

            $field_N3["Cognome"] = $cognome__N3;
            $field_N3["Nome"] = $nome__N3;
			
			if($cognome__N3!="")
			{
				if(strlen($codice_fiscale__N3) > 11 )
				{
					$ctrl_sesso = number_format(substr($codice_fiscale__N3, 9,2));
					if( $ctrl_sesso > 40 ) $sesso__N3 = "F";
					else	$sesso__N3 = "M";
				}
			}
			
			/*$field_N3[] = 'Sesso'; 						$value_N3[] = $sesso__N3;
			$field_N3[] = 'Data_Nascita'; 				$value_N3[] = to_mysql_date($data_nascita__N3);
			$field_N3[] = 'CC_Nascita';					$value_N3[] = $cc_nascita__N3;*/

                $field_N3["Sesso"] = $sesso__N3;
                $field_N3["Data_Nascita"] = $cls_date->GetDateDB($data_nascita__N3,"IT");
                $field_N3["CC_Nascita"] = $cc_nascita__N3;
			}
			else
			{
				//$field_N3[] = 'Ditta';						$value_N3[] = $ditta__N3;

                $field_N3["Ditta"] = $ditta__N3;
			}
			if($id_N2[$codice_partita__N3]==0)break;

                $check->Reset();
                $check->setTable("290_n3");
                $check->setArrayControl($field_N3);
                $field_N3 = $check->automaticCheck();
                $field_N3["Json_Error"] = str_replace("'","\'",$check->getJson());

			$control_id = $cls_db->DbSave($cls_utils->GetObjectQuery($field_N3,"290_n3"));// table_insert_record('290_n3', $field_N3, $value_N3);

			if($control_id==0)
			{
				echo "<script>$('#barlabel').text(\"Errore: ID N3  Inserimento dati nel Db fallito!\")</script>";
				//mysql_query("DELETE FROM 290_n0_n9 WHERE ID = '".$id_N0."'");

                $cls_db->Delete("290_n0_n9"," ID = '".$id_N0."'");

					die;
			}

				break;
				
			case "N4":

/**
			N4	Record delle informazioni contabili
				RECORD CHE RIPORTA LE INFORMAZIONI CONTABILI DELLA PARTITA
				(deve essere sempre presente)
*/

//                N4
//                000007 CODICE ENTE
//                01 PROGRESSIVO MINUTA
//                1990000000027  CODICE PARTITA
//                2014 ANNO TRIBUTO
//                8500 CODICE TRIBUTO
//                00000000000 IMPONIBILE
//                00 IMPONIBILE DECIMALI
//                00000000081 IMPOSTA
//                81 IMPOSTA DECIMALI
//                00
//                00000000
//                __
//                A27 AGLIANO TERME ANNO/RUOLO: 1990/000000027
//                E
//
//                A27 AGLIANO
//                 TERME ANNO/RU
//                OLO: 19

//				CODICE COMUNE DI ISCRIZIONE A RUOLO (coincide con N1)
				$codice_comune__N4 = intval(substr($listaN[$y],2,6));

//				PROGRESSIVO MINUTA (coincide con N1)
				$progr_minuta__N4 = intval(substr($listaN[$y],8,2));

//				CODICE PARTITA (coincide con N2)
//				codice identificativo intestatario
				$codice_partita__N4 = trim(substr($listaN[$y],10,14));
                //echo "N4 --> *".$codice_partita__N4."*";
//				ANNO TRIBUTO
				$anno_tributo__N4 = intval(substr($listaN[$y],24,4));

//				CODICE TRIBUTO
				$codice_tributo__N4 = ltrim(rtrim(substr($listaN[$y],28,4)));

//				IMPONIBILE
				$interi = intval(substr($listaN[$y],32,11));
				$decimali = substr($listaN[$y],43,2);
				$imponibile__N4 = $interi.".".$decimali;

//				IMPOSTA
				$interi = intval(substr($listaN[$y],45,11));
				$decimali = substr($listaN[$y],56,2);
				$imposta__N4 = $interi.".".$decimali;

//				NUMERO SEMESTRI DI INTERESSI
				$num_semestri__N4 = intval(substr($listaN[$y],58,2));

//				DATA DECORRENZA INTERESSI
				$data_decorrenza = substr($listaN[$y],60,8);

				$giorno_decorrenza = substr($data_decorrenza,0,2);
				$mese_decorrenza = substr($data_decorrenza,2,2);
				$anno_decorrenza = substr($data_decorrenza,4,4);

				$data_decorrenza__N4 = $giorno_decorrenza."/".$mese_decorrenza."/".$anno_decorrenza;

//				CODICE REPARTO
				$codice_reparto__N4 = ltrim(rtrim(substr($listaN[$y],68,2)));

//				INFORMAZIONI DA RIPORTARE SULLA CARTELLA
                $info_cartella__N4 = "";
                if($studioKFlag=="y"){
                    if($cognome__N2!=""){
                        if($codice_fiscale__N2!="")
                            $info_cartella__N4.= $codice_fiscale__N2." - ";
                        else
                            $info_cartella__N4.= $cognome__N2." - ";
                    }
                    else if($ditta__N2!=""){
                        if($codice_fiscale__N2>0)
                            $info_cartella__N4.= $codice_fiscale__N2." - ";
                        else
                            $info_cartella__N4.= $ditta__N2." - ";
                    }
                }

                if($addInfoCartella!="")
                    $info_cartella__N4.= $addInfoCartella." ";

				$info_cartella__N4.= ltrim(rtrim(substr($listaN[$y],70,75)));

//				TIPO INFORMAZIONI SUL RUOLO
//				E = iscrizioni coattive delle entrate patrimoniali
//				S = sanzioni amministrative
//				M = matricola
				$tipo_info__N4 = ltrim(rtrim(substr($listaN[$y],145,1)));

				$tipo_sanzione__N4 = "";
switch($tipo_info__N4)
{
	case "E":

//				TITOLO (deve essere sempre valorizzato)
                $titolo_entrata_exp = explode(" ",ltrim(rtrim(substr($listaN[$y],146,11))));
				$titolo_entrata__N4 = $titolo_entrata_exp[0];

//				DESCRIZIONE
				$descrizione_entrata__N4 = ltrim(rtrim(substr($listaN[$y],146,32)));

	break;

	case "S":

//				TIPO DI SANZIONE
//				VE = verbale
//				OR = ordinanza
//				IN = ingiunzione
//				DM = decreto ministeriale
				$tipo_sanzione__N4 = ltrim(rtrim(substr($listaN[$y],146,2)));

//				TITOLO (deve essere sempre valorizzato)
				$titolo_sanzione__N4 = ltrim(rtrim(substr($listaN[$y],148,12)));

//				DATA SANZIONE (deve essere sempre valorizzato)
				$data_sanzione = substr($listaN[$y],160,6);

				$giorno_sanzione = substr($data_sanzione,0,2);
				$mese_sanzione = substr($data_sanzione,2,2);
				$anno_sanzione = substr($data_sanzione,4,2);

if($data_sanzione!=000000)
{
	$anno_ini = substr($anno_tributo__N4,0,2);
	
	$anno1 = intval( ( $anno_ini - 1 ) . $anno_sanzione );
	$anno2 = intval( 	  $anno_ini    . $anno_sanzione );

	$diff1 = $anno_tributo__N4 - $anno1;
	$diff2 = $anno_tributo__N4 - $anno2;
	
	if($diff1 > $diff2)
	{
		$anno_sanzione = $anno2;
	}
	else
	{
		$anno_sanzione = $anno1;
	}
}

				$data_sanzione__N4 = $giorno_sanzione."/".$mese_sanzione."/".$anno_sanzione;

//				TARGA AUTOMOBILISTICA
				$targa__N4 = ltrim(rtrim(substr($listaN[$y],166,12)));

					break;

case "M":

//				MATRICOLA
				$matricola__N4 = ltrim(rtrim(substr($listaN[$y],146,10)));

					
				break;
}

				if($codice_tributo__N4 == 'S_02')
				{
				    /******         DATA SCADENZA NON QUESTO ***************************/
					if(substr($listaN[$y],178,9)>0)
                        $scorporo_tributo__N4 = number_format(substr($listaN[$y],178,7)).".".substr($listaN[$y],185,2);
					else
                        $scorporo_tributo__N4 = "0.00";
					
					if(substr($listaN[$y],187,9)>0)
                        $scorporo_magg_interessi__N4 = number_format(substr($listaN[$y],187,7)).".".substr($listaN[$y],194,2);
					else
                        $scorporo_magg_interessi__N4 = "0.00";
					
					if(substr($listaN[$y],196,6)>0)
						$scorporo_sp_notifica__N4 = number_format(substr($listaN[$y],196,4)).".".substr($listaN[$y],200,2);
					else
						$scorporo_sp_notifica__N4 = "0.00";
					
					if(substr($listaN[$y],202,6)>0)
						$scorporo_sp_ricerca__N4 = number_format(substr($listaN[$y],202,4)).".".substr($listaN[$y],206,2);
					else
						$scorporo_sp_ricerca__N4 = "0.00";

                    if(substr($listaN[$y],208,6)>0)
                        $scorporo_eca__N4 = number_format(substr($listaN[$y],208,4)).".".substr($listaN[$y],212,2);
                    else
                        $scorporo_eca__N4 = "0.00";

                    if(substr($listaN[$y],214,6)>0)
                        $scorporo_trib_prov__N4 = number_format(substr($listaN[$y],214,4)).".".substr($listaN[$y],218,2);
                    else
                        $scorporo_trib_prov__N4 = "0.00";
				}
				else if($codice_tributo__N4 == 5242){
                    if($tipo_sanzione__N4=="VE"){
                        $infoIng = ltrim(rtrim(substr($listaN[$y],178,100)));
                        if($infoIng!="")
                            $info_cartella__N4.= " - ". $infoIng;
                    }
                }

				
				//CREAZIONE ARRAY PER INSERIMENTO IN tabella 290_n4
				/*$field_N4 = array();
				$value_N4 = array();

				$field_N4[] = 'N2_ID';						$value_N4[] = $id_N2[$codice_partita__N4];
				$field_N4[] = 'N0_ID'; 						$value_N4[] = $id_N0;
				$field_N4[] = 'Progressivo_Minuta';			$value_N4[] = $progr_minuta__N4;
				$field_N4[] = 'Codice_Ente';				$value_N4[] = $codice_comune__N4;
				$field_N4[] = 'Codice_Partita'; 			$value_N4[] = $ins_cod_partita;*/
                $field_N4 = array();
                $field_N4["N2_ID"] = $id_N2[$codice_partita__N4];
                $field_N4["N0_ID"] = $id_N0;
                $field_N4["Progressivo_Minuta"] = $progr_minuta__N4;
                $field_N4["Codice_Ente"] = $codice_comune__N4;
                $field_N4["Codice_Partita"] = $ins_cod_partita;

				/*$field_N4[] = 'Anno_Tributo'; 				$value_N4[] = $anno_tributo__N4;
				$field_N4[] = 'Codice_Tributo'; 			$value_N4[] = $codice_tributo__N4;
				$field_N4[] = 'Imponibile'; 				$value_N4[] = $imponibile__N4;
				$field_N4[] = 'Imposta'; 					$value_N4[] = $imposta__N4;
				$field_N4[] = 'Num_Semestri_Interessi'; 	$value_N4[] = $num_semestri__N4;*/

                $field_N4["Anno_Tributo"] = $anno_tributo__N4;
                $field_N4["Codice_Tributo"] = $codice_tributo__N4;
                $field_N4["Imponibile"] = $imponibile__N4;
                $field_N4["Imposta"] = $imposta__N4;
                $field_N4["Num_Semestri_Interessi"] = $num_semestri__N4;

                $field_N4["Data_Decorrenza_Interessi"] = '';
				if($cls_date->GetDateDB($data_decorrenza__N4,"IT"))
                    $field_N4["Data_Decorrenza_Interessi"] = $cls_date->GetDateDB($data_decorrenza__N4,"IT");// to_mysql_date($data_decorrenza__N4);
				else
                    $field_N4["Data_Decorrenza_Interessi"] = date("Y-m-d");

                /*$field_N4[] = 'Codice_Reparto';				$value_N4[] = $codice_reparto__N4;
				$field_N4[] = 'Info_Cartella';				$value_N4[] = $info_cartella__N4;
				$field_N4[] = 'Tipo_Info';					$value_N4[] = $tipo_info__N4;*/

                $field_N4["Codice_Reparto"] = $codice_reparto__N4;
                $field_N4["Info_Cartella"] = $info_cartella__N4;
                $field_N4["Tipo_Info"] = $tipo_info__N4;

switch($tipo_info__N4)
{
	case "E":

				/*$field_N4[] = 'Titolo_Entrata';				$value_N4[] = $titolo_entrata__N4;
				$field_N4[] = 'Descrizione_Entrata';		$value_N4[] = $descrizione_entrata__N4;*/

                $field_N4["Titolo_Entrata"] = $titolo_entrata__N4;
                $field_N4["Descrizione_Entrata"] = $descrizione_entrata__N4;

	break;

	case "S":

				/*$field_N4[] = 'Tipo_Sanzione'; 				$value_N4[] = $tipo_sanzione__N4;
				$field_N4[] = 'Titolo_Sanzione';			$value_N4[] = $titolo_sanzione__N4;
				$field_N4[] = 'Data_Sanzione';				$value_N4[] = to_mysql_date($data_sanzione__N4);
				$field_N4[] = 'Targa_Sanzione';				$value_N4[] = $targa__N4;*/

                $field_N4["Tipo_Sanzione"] = $tipo_sanzione__N4;
                $field_N4["Titolo_Sanzione"] = $titolo_sanzione__N4;
                $field_N4["Data_Sanzione"] = $cls_date->GetDateDB($data_sanzione__N4,"IT");
                $field_N4["Targa_Sanzione"] = $targa__N4;

	break;

	case "M":

				//$field_N4[] = 'Matricola';					$value_N4[] = $matricola__N4;

                $field_N4["Matricola"] = $matricola__N4;

	break;
}

                if($codice_tributo__N4 == 'S_02'){
                    /*$field_N4[] = 'Scorporo_Tributo';				$value_N4[] = $scorporo_tributo__N4;
                    $field_N4[] = 'Scorporo_Interessi';				$value_N4[] = $scorporo_magg_interessi__N4;
                    $field_N4[] = 'Scorporo_Spese_Notifica';		$value_N4[] = $scorporo_sp_notifica__N4;
                    $field_N4[] = 'Scorporo_Spese_Ricerca';			$value_N4[] = $scorporo_sp_ricerca__N4;
                    $field_N4[] = 'Scorporo_Eca';					$value_N4[] = $scorporo_eca__N4;
                    $field_N4[] = 'Scorporo_Tributo_Provinciale';	$value_N4[] = $scorporo_trib_prov__N4;*/

                    $field_N4["Scorporo_Tributo"] = $scorporo_tributo__N4;
                    $field_N4["Scorporo_Interessi"] = $scorporo_magg_interessi__N4;
                    $field_N4["Scorporo_Spese_Notifica"] = $scorporo_sp_notifica__N4;
                    $field_N4["Scorporo_Spese_Ricerca"] = $scorporo_sp_ricerca__N4;
                    $field_N4["Scorporo_Eca"] = $scorporo_eca__N4;
                    $field_N4["Scorporo_Tributo_Provinciale"] = $scorporo_trib_prov__N4;
                }
                else if($codice_tributo__N4 == '6666')
				{					
					/*$field_N4[] = 'Pagante';					$value_N4[] = $pagante__N4;
					$field_N4[] = 'Tipo_Pagamento';				$value_N4[] = $moda_pag__N4;
					$field_N4[] = 'Quietanza';					$value_N4[] = $quietanza__N4;
					$field_N4[] = 'Bollettario';				$value_N4[] = $bollettario__N4;
					$field_N4[] = 'Data_Registrazione';			$value_N4[] = to_mysql_date($data_registrazione__N4);*/

                    /******************************************************************************************************************************
                     *
                     *
                     *                                  VARIABILI NON IIZIALIZZATE
                     *
                     *
                     ******************************************************************************************************************************/




                    /*$field_N4["Pagante"] = $pagante__N4 = null;
                    $field_N4["Tipo_Pagamento"] = $moda_pag__N4 = null;
                    $field_N4["Quietanza"] = $quietanza__N4 = null;
                    $field_N4["Bollettario"] = $bollettario__N4 = null;
                    $field_N4["Data_Registrazione"] = $cls_date->GetDateDB($data_registrazione__N4,"IT");*/
					
					/*$field_N4[] = 'Scorporo_Tributo';			$value_N4[] = $scorporo_tributo__N4;
					$field_N4[] = 'Scorporo_Interessi';			$value_N4[] = $scorporo_magg_interessi__N4;
					$field_N4[] = 'Scorporo_Spese_Notifica';	$value_N4[] = $scorporo_sp_notifica__N4;
					$field_N4[] = 'Scorporo_Spese_Ricerca';		$value_N4[] = $scorporo_sp_ricerca__N4;*/

                    $field_N4["Scorporo_Tributo"] = $scorporo_tributo__N4;
                    $field_N4["Scorporo_Interessi"] = $scorporo_magg_interessi__N4;
                    $field_N4["Scorporo_Spese_Notifica"] = $scorporo_sp_notifica__N4;
                    $field_N4["Scorporo_Spese_Ricerca"] = $scorporo_sp_ricerca__N4;
				}
				
				if($id_N2[$codice_partita__N4]==0)break;


                $check->Reset();
                $check->setTable("290_n4");
                $check->setArrayControl($field_N4);
                $field_N4 = $check->automaticCheck();
                $field_N4["Json_Error"] = str_replace("'","\'",$check->getJson());

				$control_id = $cls_db->DbSave($cls_utils->GetObjectQuery($field_N4,"290_n4"));// table_insert_record('290_n4', $field_N4, $value_N4);

				if($control_id == 0)
				{
						echo "<script>$('#barlabel').text(\"Errore: ID N4  Inserimento dati nel Db fallito!\")</script>";
						//mysql_query("DELETE FROM 290_n0_n9 WHERE ID = '".$id_N0."'");
                        $cls_db->Delete("290_n0_n9"," ID = '".$id_N0."'");
						die;
				}

	break;
}

flush();
ob_flush();

echo "<script>$( \"#progress_bar\" ).progressbar({value: " .intval($y*100/$num). " });$( \"#barlabel\" ).text(" .intval($y*100/$num). "+'%');</script>";


		}//CHIUSURA CICLO
	}//CHIUSURA ELSE
	
	echo "<script>fine();</script>";

}//CHIUSURA IF (control_submit)

?>
<?php include(INC."/footer.php"); ?>