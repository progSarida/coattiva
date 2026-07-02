<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");

include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/cls_check.php";
include_once CLS . "/cls_storico.php";													

	
if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

$storico = new storico('storicoElaborazioni','5');
$cls_date = new cls_DateTimeI("IT",false);
$cls_check = new cls_check();

//AGGIUNGERE SANZIONE DA INGIUNZIONE


$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$done = $cls_help->getVar('done');

$storico_msg = "Elaborazione elenco veicoli";
$ente = $cls_db->getArrayLine($cls_db->SelectQuery("SELECT Denominazione FROM enti_gestiti WHERE CC = '".$c."'") );
$nome_ente = $ente['Denominazione'];

$title = "Elenco veicoli";
$action = "elenco_visura_massiva.php";
$buttonText = "Veicoli utenti";


$data_elab_visual = date('d/m/Y');
$disabled = "";

$serieOption = "";
$queryIngiunzioni = "SELECT Comune_ID from partita_tributi WHERE CC = '" . $c . "' ORDER BY Comune_ID ASC";
$resIngiunzioni = $cls_db->getResults($cls_db->ExecuteQuery($queryIngiunzioni));

for($i=0; $i < count($resIngiunzioni) ; $i++)
    $serieOption .= "<option value='" . $resIngiunzioni[$i]['Comune_ID'] . "'>" . $resIngiunzioni[$i]['Comune_ID'] . "</option>";

?>
<!-- GESTIONE MODALI -->
<!-- Inclusione modale per ricerca utente-->
<?php include_once(ROOT . "/search_modal/offcanvas/user_entry_offcanvas.php"); ?>
<script>
    // Modali offcanvas
    function openOfcanvas(type,rif){
        // Reset campi input
        $('.user_entry').val("");

        // Reset spazi tabella
        $('#appendTableUserEntry').empty();

        selectRif = rif;
        switch (type){
            case 'user_entry':
                // Nasconde radio
                $("#checkbox_c").hide();
                // Setta stato checkbox (fisso perchè ho nascosto i radio) per ricerca
                document.getElementById('check_u_n').checked = true;
                document.getElementById('check_u_c').checked = false;
                document.getElementById('check_e_cA').checked = false;
                document.getElementById('check_e_cP').checked = false;
                document.getElementById('check_e_i').checked = false;
                // Setta titolo modale (fisso perchè ho nascosto i radio)
                $("#userEntrySearchModalLabel_u").show();
                $("#userEntrySearchModalLabel_e").hide();
                // Setta campo input (fisso perchè ho nascosto i radio)
                $("#ins_u_n").show();
                $("#ins_u_c").hide();
                $("#ins_e_cA").hide();
                $("#ins_e_cP").hide();
                $("#ins_e_i").hide();
                // Apre modale
                if(rif == 2 && $('#daco').val() == '')
                    alert("Inserire prima l'utente da cui far partire la ricerca");
                else
                    $('#userEntrySearchModal').modal('show');
                break;
        }
    }
    // Iserimento dati da modale a pagine
    function initialId(type,val){
        switch (type){
            case 'user':
                /*
                $("#genere").val(val['Genere']);                            // setta genere utente (M, F, D)
                if(lock == 'N')                                             // se non è già stato lockato
                    lock = val['Genere'];                                   // blocca la ricerca del secondo input sul tipo del primo
                */
                if(selectRif == 1)                                          // "Da Cognome/Nome"
                {
                    //alert("qui 1");
                    if(val['Ditta'] != '' && val['Ditta'] != null){         // è una ditta
                        $('#daco').val(val['Ditta']);
                        $('#acog').val(val['Ditta']);
                        $('#dano').val('');
                        $('#anom').val('');
                    } else{                                                 // è una persona
                        $('#daco').val(val['Cognome']);
                        $('#acog').val(val['Cognome']);
                        $('#dano').val(val['Nome']);
                        $('#anom').val(val['Nome']);
                    }

                }
                else if(selectRif == 2)                                     // "A Cognome/Nome"
                {
                    if(val['Ditta'] != '' && val['Ditta'] != null){         // è una ditta
                        $('#acog').val(val['Ditta']);
                        $('#anom').val('');
                    } else{                                                 // è una persona
                        $('#acog').val(val['Cognome']);
                        $('#anom').val(val['Nome']);
                    }
                }
                break;
            default: alert("Errore Ricerca");
        }
    }
    //F5
    switchMenuImg("F5");
    F5_button = function(){
        $("#viewTable").val("no");
        location.href="elenco_visura_massiva.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }

    //F10
    switchMenuImg("F10");
    F10_button = function(){
        $("#viewTable").val("si");
        $("#done").val(true);
        $("#visura_form").submit();
    }

    $( document ).ready(function() {
        $("#tipo_partita").val('<?= $cls_help->getVar("tipo_partita")?>');
        $("#da_n_elenco").val('<?= $cls_help->getVar("da_n_elenco")?>');
        $("#a_n_elenco").val('<?= $cls_help->getVar("a_n_elenco")?>');
    });

    function callParent(valorediritorno){
        switch(selectParent){
            case "utente":

                if(valorediritorno!=null)
                {
                    $.post("ajax/ajax_cognome.php?c=<?php echo $c; ?>" ,

                        { 'ajax': 'nome' ,
                            'ID': valorediritorno },

                        function (value) {

                            var array_ritorno = value.split('*');

                            if(selectRif==1)
                            {
                                $('#daco').val(array_ritorno[0]);
                                $('#acog').val(array_ritorno[0]);
                            }
                            else if(selectRif==2)
                            {
                                $('#acog').val(array_ritorno[0]);
                            }

                            if(array_ritorno.length == 3)
                            {
                                if(selectRif==1)
                                {
                                    $('#dano').val(array_ritorno[1]);
                                    $('#anom').val(array_ritorno[1]);
                                }
                                else if(selectRif==2)
                                {
                                    $('#anom').val(array_ritorno[1]);
                                }

                                $("#genere").val(array_ritorno[2]);
                            }
                            else
                            {
                                if(array_ritorno.length == 2) $("#genere").val(array_ritorno[1]);
                                else $("#genere").val("");

                                if(selectRif==1)
                                {
                                    $('#dano').val("");
                                    $('#anom').val("");
                                }
                                else if(selectRif==2)
                                {
                                    $('#anom').val("");
                                }
                            }
                        });
                }

                break;
        }

    }

    var selectParent = "";
    var selectRif = "";
    function RicercheDaId (value, rif)
    {
        selectParent = value;
        selectRif = rif;
        var valorediritorno = 0;
        //var strDim = Dim_Alert(600, 300);

        switch(value)
        {
            case "utente":

                //strDim = Dim_Alert(800, 500);
                var stringa = "<?= WEB_ROOT; ?>/search/coattiva/ricerca_alert_modale.php?richiesta=ricUtente&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
                //valorediritorno = window.showModalDialog(stringa,"", strDim);
                openWindowSearch(stringa,{width:800, height:500, left:(($(window).width()/2)-400), top:(($(window).height()/2)-250)});

                break;
        }
    }

</script>



<form id="visura_form" name="visura_form" action="<?= $action; ?>" method="post" >
    <input type=hidden name="c" value="<?php echo $c ?>" />
    <input type=hidden name="a" value="<?php echo $a ?>" />
    <input type=hidden name="genere" id="genere" value="" />
    <input type=hidden name="viewTable" id="viewTable" value="" />
    <input type=hidden name="done" id="done" value=false />

    <div class="row justify-content-md-center " style="margin: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">Selezione utenti</span>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

    <div class="row">
        <div class="col col-lg-2 col-lg-offset-1">
            <div class="form-group" style="padding-left:0;padding-right: 0;padding-bottom: 0; margin-left: 0;margin-right:0;margin-bottom: 0;">
                <input class="btn btn-primary form-control resize " type="button" value="Da Cognome / Nome" title="Cerca utente" onclick="/*RicercheDaId('utente',1);*/openOfcanvas('user_entry',1);" tabindex=4>
            </div>
        </div>
        <div class="col col-lg-2" style="padding:0; margin:0;">
            <div class="form-group" style="padding:0; margin:0;">
                <div class="col-lg-12" style="padding:0; margin:0;">
                    <input class="form-control resize" type="text" id="daco" name="daco" value="<?= $cls_help->getVar("daco"); ?>"  tabindex=5>
                </div>
            </div>
        </div>
        <div class="col col-lg-2">
            <div class="form-group">
                <div class="col-lg-12">
                    <input class="form-control resize" type="text" id="dano" name="dano" value="<?= $cls_help->getVar("dano"); ?>" tabindex=6>
                </div>
            </div>
        </div>
        <div class="col col-lg-4">
            <label class="col-lg-4 control-label resize" style="text-align: left;">Tipo Entrata</label>
            <div class="form-group">
                <div class="col-lg-8">
                    <select name=tipo_partita id=tipo_partita class="form-control resize" tabindex=6>
                        <option value=""></option>
                        <option value="CDS">CDS/AMMINISTRATIVA</option>
                        <option value="IMMOBILI">IMMOBILI</option>
                        <option value="IRPEF">IRPEF</option>
                        <option value="OSAP">OSAP</option>
                        <option value="PATRIMONIALE">PATRIMONIALE</option>
                        <option value="PUBBLICITA">PUBBLICITA'</option>
                        <option value="RIFIUTI">RIFIUTI</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col col-lg-2 col-lg-offset-1">
            <div class="form-group" style="padding-left:0;padding-right: 0;padding-bottom: 0; margin-left: 0;margin-right:0;margin-bottom: 0;">
                <input class="btn btn-primary form-control resize" type="button" value="A Cognome / Nome" title="Cerca utente" onclick="/*RicercheDaId('utente',2);*/openOfcanvas('user_entry',2);" tabindex=7>
            </div>
        </div>
        <div class="col col-lg-2" style="padding:0; margin:0;">
            <div class="form-group" style="padding:0; margin:0;">
                <div class="col-lg-12" style="padding:0; margin:0;">
                    <input class="form-control resize" type="text" id="acog" name="acog" value="<?= $cls_help->getVar("acog"); ?>"  tabindex=7>
                </div>
            </div>
        </div>
        <div class="col col-lg-2">
            <div class="form-group">
                <div class="col-lg-12">
                    <input class="form-control resize" type="text" id="anom" name="anom" value="<?= $cls_help->getVar("anom"); ?>"  tabindex=9>
                </div>
            </div>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

    <div class="row">
        <div class="col col-lg-3 col-lg-offset-1">
            <label class="col-lg-4 control-label resize" style="text-align: left;">Da partita</label>
            <div class="form-group">
                <div class="col-lg-8">
                    <select id="da_n_elenco" name="da_n_elenco" tabindex=11 class="form-control resize">
                        <option value=""></option>
                        <?php echo $serieOption ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-4">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Da data di notifica</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input type="text" class="form-control resize picker" <?php echo $disabled; ?> name="da_data" id="da_data" value="<?= $cls_help->getVar("da_data"); ?>" onchange="insert_a_data();" size=9  tabindex=13>
                </div>
            </div>
        </div>
        <div class="col col-lg-4">
            <label class="col-lg-4 control-label resize" style="text-align: left;">Da anno</label>
            <div class="form-group">
                <div class="col-lg-4">
                    <input type="text" class="form-control resize" id="da_anno" name="da_anno" value="<?= $cls_help->getVar("da_anno"); ?>" size=3  tabindex=15>
                </div>
            </div>
        </div>
    </div>

    <div class="row" >
        <div class="col col-lg-3 col-lg-offset-1">
            <label class="col-lg-4 control-label resize" style="text-align: left;">A partita</label>
            <div class="form-group">
                <div class="col-lg-8">
                    <select id="a_n_elenco" name="a_n_elenco" tabindex=12 class="form-control resize">
                        <option value=""></option>
                        <?php echo $serieOption ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col col-lg-4">
            <label class="col-lg-6 control-label resize" style="text-align: left;">A data di notifica</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input type="text" class="form-control resize picker" <?php echo $disabled; ?> name="a_data" id="a_data" value="<?= $cls_help->getVar("a_data"); ?>" size=9  tabindex=14>
                </div>
            </div>
        </div>
        <div class="col col-lg-4">
            <label class="col-lg-4 control-label resize" style="text-align: left;">Ad anno</label>
            <div class="form-group">
                <div class="col-lg-4">
                    <input type="text" class="form-control resize" id="ad_anno" name="ad_anno" value="<?= $cls_help->getVar("ad_anno"); ?>" size=3 tabindex=16 onblur="focusIndex();">
                </div>
            </div>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 2%; margin-top: 1%;"></div>

</form>
<?php
if($cls_help->getVar('viewTable')=="si")
{


$genere = $cls_help->getVar('genere');
$dacognome = $cls_help->getVar('daco');
$acognome = $cls_help->getVar('acog');
$danome = $cls_help->getVar('dano');
$anome = $cls_help->getVar('anom');
$daNEl = $cls_help->getVar("da_n_elenco");
$aNEl = $cls_help->getVar("a_n_elenco");





//$query = "SELECT * FROM atto AS A JOIN partita_tributi AS P ON A.Partita_ID = P.ID JOIN utente AS U ON U.ID = P.Utente_ID WHERE A.ID = (SELECT MAX(A2.ID) FROM atto AS A2 WHERE A2.Partita_ID = A.Partita_ID AND (A2.DocumentTypeId = 4 OR A2.DocumentTypeId = 2))";
$query = "SELECT A.DocumentTypeId, U.Genere, U.Cognome, U.Nome, U.Ditta, U.Codice_Fiscale, U.Partita_Iva, U.ID as Utente_ID, U.Comune_ID as Utente_Comune_ID, P.Comune_ID as Partita_Comune_ID,
            A.Totale_Dovuto, A.Rate_Previste, A.Scadenze_Rate, A.Data_Notifica, A.Diritto_Riscossione_Massimo, A.Diritto_Riscossione_Minimo, A.ID, A.Partita_ID, A.Info_Cartella,
            A.Atto, A.ID_Cronologico, A.Anno_Cronologico, A.Data_Notifica, P.Anno_Riferimento
            FROM atto AS A
            JOIN partita_tributi AS P ON A.Partita_ID = P.ID
            JOIN utente AS U ON U.ID = P.Utente_ID
            WHERE A.ID = (SELECT MAX(A2.ID) FROM atto AS A2 WHERE A2.Partita_ID = A.Partita_ID AND (A.DocumentTypeId = 2 OR A.DocumentTypeId = 4)) AND U.CC_Comune = '".$c."'";

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

if($dacognome != null){
    $strCompareDa = addslashes($dacognome)." ".addslashes($danome);
    $strCompareA = addslashes($acognome)." ".addslashes($anome);

    $query .= " AND ( CONCAT(COALESCE(U.Ditta,''),COALESCE(U.Cognome,''),' ',COALESCE(U.Nome,'')) >= '".$strCompareDa."' AND CONCAT(COALESCE(U.Ditta,''),COALESCE(U.Cognome,''),' ',COALESCE(U.Nome,'')) <= '".$strCompareA."' ) ";

    $storico_msg.= " dal contribuente ".$dacognome." ".$danome." al contribuente ".$acognome." ".$anome;
}

if($cls_help->getVar("tipo_partita") != null){
    $query .= " AND P.Tipo = '".$cls_help->getVar("tipo_partita")."' ";
    $storico_msg = " per le entrate di tipo ".$cls_help->getVar("tipo_partita");
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

?>

<div class="row justify-content-md-center ">
	<div class="col col-md-auto text_center">
			<span class="titolo font16 under_decor">Elenco veicoli per utente</span>
	</div>
</div>


<table class="table" style="margin-top: 3%;width: 98%; margin-left: 1%; border: 2px solid black;">
  <colgroup>
    <col style="width: 3%;">
    <col style="width: 7%;">
    <col style="width: 20%;">
    <col style="width: 20%;">
    <col style="width: 5%;">
    <col style="width: 5%;">
    <col style="width: 5%;">
    <col style="width: 15%;">
    <col style="width: 15%;">
    <col style="width: 5%;">
  </colgroup>
<tbody>
<?php
$utenteIdConfronto = -1;
//die;
$result = $cls_db->getResults($cls_db->ExecuteQuery($query));
for( $i=0; $i < count($result); $i++ )
{

//var_dump($result[$i]);
//die;
//if($result[$i]["Data_Notifica"] == null) continue;

    $check = $cls_check->setAct($result[$i]);
//$cls_help->alert($check);
    if($check!==true)
    {
        continue;
    }

    if(!$cls_check->checkActPayments(0))
    {
        continue;
    }else  if(!$cls_check->checkDatesLimit(0)){
        continue;
    }
//$cls_help->alert("buono");
    $dettaglio = $result[$i]["Atto"]. " ".$result[$i]["ID_Cronologico"]."/".$result[$i]["Anno_Cronologico"]." notificata il ".$cls_date->Get_DateNewFormat($result[$i]["Data_Notifica"],"DB");
    $residuo = $cls_check->docAmount - $cls_check->docPaymentsAmount;

    $query = "SELECT ID, Targa, Fabbrica, Tipo, Pignoramento_ID FROM veicoli where CC_Comune = '".$c."' AND Utente_ID = ".$result[$i]["Utente_ID"]." AND (TRIM(StatoVeicolo) = '' OR StatoVeicolo is null OR TRIM(StatoVeicolo) = 'Targa Attuale') AND Data_Visura = (SELECT MAX(Data_Visura) FROM veicoli WHERE CC_Comune = '".$c."' AND Utente_ID = ".$result[$i]["Utente_ID"].")";
    //$query = "SELECT ID, Targa, Fabbrica, Tipo, Pignoramento_ID FROM veicoli where CC_Comune = '".$c."' AND Utente_ID = ".$result[$i]["Utente_ID"]." AND Data_Visura = (SELECT MAX(Data_Visura) FROM veicoli WHERE CC_Comune = '".$c."' AND Utente_ID = ".$result[$i]["Utente_ID"].")";
    $elencoVeicoli = $cls_db->getResults($cls_db->ExecuteQuery($query));

    if(count($elencoVeicoli)==0) continue;

    $optionVeicoli = "<option value=''></option>";
    for($x=0; $x<count($elencoVeicoli); $x++)
    {
      $disColor = "";
      if($elencoVeicoli[$x]["Pignoramento_ID"]!=null)
        $disColor = " disabled style='color: red;' ";

      $optionVeicoli .= "<option ".$disColor." value='".$elencoVeicoli[$x]["ID"]."'>".$elencoVeicoli[$x]["Targa"]." ".$elencoVeicoli[$x]["Fabbrica"]." ".$elencoVeicoli[$x]["Tipo"]."</option>";
    }

    if($utenteIdConfronto != $result[$i]["Utente_ID"])
    {
      if($result[$i]["Genere"] == "D")
      {
        $utenteCompleto = $result[$i]["Ditta"];
      }
      else {
        $utenteCompleto = $result[$i]["Cognome"]." ".$result[$i]["Nome"];
      }
      ?>
      <tr style="background-color: #0095FF;color: white;border-top: 1px solid black;">
        <th colspan="10">Utente: <?= $utenteCompleto; ?></th>
      </tr>
      <tr style="background-color: #0095FF;color:white;">
        <th style="border-bottom: 2px solid black;"></th>
        <td style="border-bottom: 2px solid black;">Partita</td>
        <td style="border-bottom: 2px solid black;">Info</td>
        <td style="border-bottom: 2px solid black;">Dettagli</td>
        <td style="border-bottom: 2px solid black;">Importo</td>
        <td style="border-bottom: 2px solid black;">Pagamenti</td>
        <td style="border-bottom: 2px solid black;">Residuo</td>
        <td style="border-bottom: 2px solid black;">Veicoli</td>
        <td style="border-bottom: 2px solid black;">Tipo pignoramento</td>
        <td style="border-bottom: 2px solid black;"></td>
      </tr>
      <tr style="background-color: #ADD2FF;">
        <th></th>
        <td><?= $result[$i]["Partita_ID"]; ?><img width="30" src="<?= IMG ?>/select_arrow.png" style="cursor: pointer;" onclick="callIngiunzione('<?= $result[$i]["Partita_ID"]; ?>','<?= $result[$i]["Anno_Riferimento"]; ?>');"></td>
        <td><?= $result[$i]["Info_Cartella"]; ?></td>
        <td><?= $dettaglio; ?></td>
        <td><?= $cls_check->docAmount; ?></td>
        <td><?= $cls_check->docPaymentsAmount; ?></td>
        <td><?= $residuo; ?></td>
        <td>
          <div class="form-group">
           <select name=lista_veicoli id=lista_veicoli_<?= $i; ?> class="form-control resize">
                <?php echo $optionVeicoli; ?>
            </select>
          </div>
        </td>
          <td>
              <div class="form-group">
                  <select id=tipo_pignoramento_<?= $i; ?> class="form-control resize">
                      <option value=""></option>
                      <option value="veicolo"         >Beni mobili registrati</option>
                      <option value="preav_fermo"	    >Preavviso fermo amministrativo</option>
                  </select>
              </div>
          </td>
        <td><img width="30" src="<?= IMG ?>/Plus.png" style="cursor: pointer;" onclick="callPigno('<?= $result[$i]["Partita_ID"]; ?>','<?= $i; ?>');"></td>
      </tr>
      <?php
    }
    else{

    ?>
    <tr style="background-color: #ADD2FF;">
      <th></th>
      <td><?= $result[$i]["Partita_ID"]; ?><img width="30" src="<?= IMG ?>/select_arrow.png" style="cursor: pointer;" onclick="callIngiunzione('<?= $result[$i]["Partita_ID"]; ?>','<?= $result[$i]["Anno_Riferimento"]; ?>');"></td>
      <td><?= $result[$i]["Info_Cartella"]; ?></td>
      <td><?= $dettaglio; ?></td>
      <td><?= $cls_check->docAmount; ?></td>
      <td><?= $cls_check->docPaymentsAmount; ?></td>
      <td><?= $residuo; ?></td>
      <td>
        <div class="form-group">
          <div class="col-lg-8">
             <select name=lista_veicoli id=lista_veicoli_<?= $i; ?> class="form-control resize">
              <?php echo $optionVeicoli; ?>
            </select>
          </div>
        </div>
      </td>
        <td>
            <div class="form-group">
                <select id=tipo_pignoramento_<?= $i; ?> class="form-control resize">
                    <option value=""></option>
                    <option value="veicolo"         >Beni mobili registrati</option>
                    <option value="preav_fermo"	    >Preavviso fermo amministrativo</option>
                </select>
            </div>
        </td>
      <td><img width="30" src="<?= IMG ?>/Plus.png" style="cursor: pointer;" onclick="callPigno('<?= $result[$i]["Partita_ID"]; ?>','<?= $i; ?>');"></td>
    </tr>
    <?php
    }
    $utenteIdConfronto = $result[$i]["Utente_ID"];
  }
}
if($done)
    $storico->insRow('E', $storico_msg." per ente ".$nome_ente."[".$c."]");
  ?>

  <script type="text/javascript">

      function callIngiunzione(partita,anno){
          location.href='<?= WEB_ROOT; ?>/coattiva/ingiunzione.php?partita='+partita+'&c=<?= $c; ?>&a='+anno;
      }
    function callPigno(partita,flag){
      //location.href = "<?= WEB_ROOT; ?>/"
      var veicolo_ID = $("#lista_veicoli_"+flag).val();
      var tipo_Pigno = $("#tipo_pignoramento_"+flag).val();
      if(veicolo_ID == "" || tipo_Pigno=="")
      {
        alert("Prima selezionare il veicolo e il tipo procedimento");
        return false;
      }
      //alert(partita+" "+veicolo_ID+" "+tipo_Pigno);
      //return;
      location.href='<?= WEB_ROOT; ?>/coattiva/pignoramento.php?partita='+partita+'&c=<?= $c; ?>&a=<?= $a; ?>&flagInsert=1&ID_Veicolo_get='+veicolo_ID+"&tipo_pignoramento_get="+tipo_Pigno;

    }
  </script>
