<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");

include_once CLS . "/cls_DateTimeInLine.php";
include_once CLS . "/cls_check.php";
include_once CLS . "/cls_DateTime.php";
include_once CLS . "/cls_Utils.php";

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

$cls_date = new cls_DateTimeI("IT",false);
$cls_check = new cls_check();
$cls_Utils = new cls_Utils();

//AGGIUNGERE SANZIONE DA INGIUNZIONE


$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');


$data_elab_visual = date('d/m/Y');
$disabled = "";

$serieOption = "";
$queryIngiunzioni = "SELECT Comune_ID from partita_tributi WHERE CC = '" . $c . "' ORDER BY Comune_ID ASC";
$resIngiunzioni = $cls_db->getResults($cls_db->ExecuteQuery($queryIngiunzioni));

for($i=0; $i < count($resIngiunzioni) ; $i++)
    $serieOption .= "<option value='" . $resIngiunzioni[$i]['Comune_ID'] . "'>" . $resIngiunzioni[$i]['Comune_ID'] . "</option>";

/*$queryYears = "SELECT * FROM anni_gestiti WHERE CC_Anno = '".$c."' AND Gestione_Coattiva = 'Y' ORDER BY Anno DESC";
$a_years = $cls_db->getResults( $cls_db->SelectQuery($queryYears) );*/
$annoIniz = date('Y');

if($cls_help->getVar("anno_elab")==$annoIniz) $selectYear = "selected";
else $selectYear = "";

$dropAnni = "<option></option>";
$dropAnni .= "<option value='".$annoIniz."' ".$selectYear." >".$annoIniz."</option>";

for($i=0; $i<20; $i++){
    $annoIniz--;
    if($cls_help->getVar("anno_elab")==$annoIniz) $selectYear = "selected";
    else $selectYear = "";
    $dropAnni .= "<option value='".$annoIniz."' ".$selectYear.">".$annoIniz."</option>";
}

?>

<script>
    //F5
    switchMenuImg("F5");
    F5_button = function(){
        $("#iniziaSgravio").val("no");
        location.href="elenco_visura_massiva.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }

    //F10
    switchMenuImg("F10");
    F10_button = function(){
        $("#iniziaSgravio").val("si");
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

    function setDataElab(el,id){
        $("#"+id).val("30/03/"+el.value);
    }

</script>



<form id="visura_form" name="visura_form" action="sgravio_automatico.php" method="post" >
    <input type=hidden name="c" value="<?php echo $c ?>" />
    <input type=hidden name="a" value="<?php echo $a ?>" />
    <input type=hidden name="genere" id="genere" value="" />
    <input type=hidden name="iniziaSgravio" id="iniziaSgravio" value="" />

    <div class="row justify-content-md-center " style="margin: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">Crea sgravio automatico</span>
        </div>
    </div>

    <div class="row justify-content-md-center " style="margin: 2%;">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">Selezione atti</span>
        </div>
    </div>

    <div class="row">
        <div class="col col-lg-4 col-lg-offset-2">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Data elaborazione</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <input class="form-control resize picker" type="text" id="data_elab" name="data_elab" value="<?= $cls_help->getVar("data_elab"); ?>"  tabindex=5>
                </div>
            </div>
        </div>
        <div class="col col-lg-4">
            <label class="col-lg-6 control-label resize" style="text-align: left;">Anno elaborazione</label>
            <div class="form-group">
                <div class="col-lg-6">
                    <select id="anno_elab" name="anno_elab" class="form-control resize" onchange="setDataElab(this,'data_elab');">
                        <?php echo $dropAnni ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 1%; margin-top: 1%;"></div>

    <div class="row">
        <div class="col col-lg-2 col-lg-offset-1">
            <div class="form-group" style="padding-left:0;padding-right: 0;padding-bottom: 0; margin-left: 0;margin-right:0;margin-bottom: 0;">
                <input class="btn btn-primary form-control resize " type="button" value="Da Cognome / Nome" title="Cerca utente" onclick="RicercheDaId('utente',1);" tabindex=4>
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
                    <select name=tipo_partita id=tipo_partita class="form-control resize">
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
                <input class="btn btn-primary form-control resize" type="button" value="A Cognome / Nome" title="Cerca utente" onclick="RicercheDaId('utente',2);" tabindex=7>
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
        <div class="col col-lg-3">
            <label class="col-lg-4 control-label resize" style="text-align: left;">Da data di notifica</label>
            <div class="form-group">
                <div class="col-lg-8">
                    <input type="text" class="form-control resize picker" <?php echo $disabled; ?> name="da_data" id="da_data" value="<?= $cls_help->getVar("da_data"); ?>" onchange="insert_a_data();" size=9  tabindex=13>
                </div>
            </div>
        </div>
        <div class="col col-lg-4">
            <label class="col-lg-4 control-label resize" style="text-align: left;">Da anno di riferimento</label>
            <div class="form-group">
                <div class="col-lg-8">
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
        <div class="col col-lg-3">
            <label class="col-lg-4 control-label resize" style="text-align: left;">A data di notifica</label>
            <div class="form-group">
                <div class="col-lg-8">


                    <input type="text" class="form-control resize picker" <?php echo $disabled; ?> name="a_data" id="a_data" value="<?= $cls_help->getVar("a_data"); ?>" size=9  tabindex=14>
                </div>
            </div>
        </div>
        <div class="col col-lg-4">
            <label class="col-lg-4 control-label resize" style="text-align: left;">Ad anno di riferimento</label>
            <div class="form-group">
                <div class="col-lg-8">
                    <input type="text" class="form-control resize" id="ad_anno" name="ad_anno" value="<?= $cls_help->getVar("ad_anno"); ?>" size=3 tabindex=16 onblur="focusIndex();">
                </div>
            </div>
        </div>
    </div>

    <div style="border-top: 2px solid #B0BBE8; width: 90%; margin-left: 5%;margin-bottom: 2%; margin-top: 1%;"></div>

</form>
<?php
$cls_help->alert($cls_help->getVar("iniziaSgravio"));
if($cls_help->getVar('iniziaSgravio')=="si") {

    $dataSgravio = new cls_DateTime(date("d/m/Y"),"IT",false);
    $dataElaborazioneMassima = new cls_DateTime($cls_help->getVar("data_elab"),"IT",false);
    if($dataSgravio->CompareDate("IT",">",$dataElaborazioneMassima->GetDate())){
        $cls_help->alert("Data di elaborazione già superata");
    }

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
            A.Atto, A.ID_Cronologico, A.Anno_Cronologico, A.Data_Notifica, P.Anno_Riferimento, SUM(PA.Importo) AS Tot_Pagamenti, A.Data_Elaborazione, A.Diritto_Riscossione_Massimo,
            A.Totale_Dovuto
            FROM atto AS A
            JOIN partita_tributi AS P ON A.Partita_ID = P.ID
            LEFT JOIN pagamento AS PA on PA.Atto_ID = A.ID AND PA.Partita_ID = A.Partita_ID AND Tipo_Atto NOT LIKE 'Pignoramento%'
            JOIN utente AS U ON U.ID = P.Utente_ID
            WHERE U.CC_Comune = '" . $c . "' AND A.ID = (SELECT MAX(ID) FROM atto as A2 WHERE A2.Partita_ID = A.Partita_ID) ";

    if ($genere != "D") {
        if ($dacognome != null) {

            $query .= " AND ( ( U.Cognome > '" . addslashes($dacognome) . "' ) ";
            $query .= "AND ( U.Cognome < '" . addslashes($acognome) . "' ) ";
            $query .= "OR ( U.Cognome = '" . addslashes($dacognome) . "' ";
            if ($danome != null) {
                $query .= "AND U.Nome >= '" . addslashes($danome) . "' ";
            }

            $query .= ") OR ( U.Cognome = '" . addslashes($acognome) . "' ";
            if ($anome != null) {
                $query .= "AND U.Nome <= '" . addslashes($anome) . "' ";
            }
            $query .= ") ) ";
        }
    } else {
        if ($dacognome != null)
            $query .= " AND ( U.Ditta >= '" . addslashes($dacognome) . "' AND U.Ditta <= '" . addslashes($acognome) . "' ) ";
    }

    if ($cls_help->getVar("tipo_partita") != null) {
        $query .= " AND P.Tipo = '" . $cls_help->getVar("tipo_partita") . "' ";
    }

    if ($daNEl != null) {
        $query .= " AND P.Comune_ID >= " . $daNEl;
    }

    if ($aNEl != null) {
        $query .= " AND P.Comune_ID <= " . $aNEl;
    }

    if ($cls_help->getVar("da_data") != null) {
        $query .= " AND A.Data_Notifica >= '" . $cls_date->GetDateDB($cls_help->getVar("da_data"), "IT") . "'";
    }

    if ($cls_help->getVar("a_data") != null) {
        $query .= " AND A.Data_Notifica <= '" . $cls_date->GetDateDB($cls_help->getVar("a_data"), "IT") . "'";
    }

    if ($cls_help->getVar("da_anno") != null) {
        $query .= " AND P.Anno_Riferimento >= '" . $cls_help->getVar("da_anno") . "'";
    }

    if ($cls_help->getVar("ad_anno") != null) {
        $query .= " AND P.Anno_Riferimento <= '" . $cls_help->getVar("ad_anno") . "'";
    }

//$query .= " GROUP BY U.ID, P.ID ORDER by U.ID";
    $query .= " GROUP BY A.Partita_ID ORDER by U.ID,P.ID";
    //echo $query;
    $result = $cls_db->getResults($cls_db->ExecuteQuery($query));

    for ($i = 0; $i < count($result); $i++) {
        $data_1 = new cls_DateTime($result[$i]["Data_Notifica"],"DB",false);

        $data_2 = new cls_DateTime($dataElaborazioneMassima->GetYear()."-01-01","DB",false);
        $data_3 = new cls_DateTime($dataElaborazioneMassima->GetYear()."-01-01","DB",false);
        $data_2->AddYear("-2");

        $query = "SELECT SUM(Importo) AS TOTALE_PAGAMENTI FROM pagamento WHERE Atto_ID < ".$result[$i]["ID"]." AND Partita_ID = ".$result[$i]["Partita_ID"]." AND Tipo_Atto NOT LIKE 'Pignoramento%' AND Tipo_Atto NOT LIKE 'Precedenti%' GROUP BY Partita_ID";
        //echo "<br>".$query."<br>";
        $resultsPag = $cls_db->getArrayLine($cls_db->ExecuteQuery($query));

        $totale = $result[$i]["Totale_Dovuto"] + $result[$i]["Diritto_Riscossione_Massimo"] - $resultsPag["TOTALE_PAGAMENTI"];

        //AGGIUNGERE I CONTROLLO CHE IL PAGAMENTO SIA INFERIORE AL PAGAMENTO TOTALE, (CHE IL PAGAMENTO MENO IL PARZIALE NON SIA 0, CHE CI SIA ANCORA QUALCOSA DA PAGARE)
        //$cls_help->alert($data_1->GetDate()."(".$data_2->GetDate().",".$data_3->GetDate().") --- ".$data_1->CompareDate("DB",">",$data_2->GetDate())." --- ".$data_1->CompareDate("DB","<",$data_3->GetDate())." --- Tot: ".$totale);
        if(($data_1->CompareDate("DB",">",$data_2->GetDate()) && $data_1->CompareDate("DB","<",$data_3->GetDate())) && ($result[$i]["Tot_Pagamenti"] == 0 || $result[$i]["Tot_Pagamenti"] == null || $totale > 0 )){
            //$cls_help->alert($data_1->GetDate()." --- ".$result[$i]["Tot_Pagamenti"]." --- OK --- ".$result[$i]["ID"]);
            $save = array();
            $save["Flag_Sgravio"] = "si";
            $save["Sgravio_Activation_Date"] = date("Y-m-d");

            $arrWhere = array("ID" => $result[$i]["Partita_ID"]);

            $a_paramsSgraviDoc = $cls_Utils->GetObjectQuery($save,"partita_tributi",$arrWhere);
            if(!$cls_db->DbSave($a_paramsSgraviDoc))
            {

                $error = 1;
                $msg = "Errore impossibile aggiornare i dati. ".$cls_db->GetError();
                $cls_db->Rollback();
                //header("Location: annulamento_sgravi.php?partita={$partita_ID}&p={$p}&c={$c}&a={$a}&error={$error}&msg={$msg}");
                //die;
            }else $msg = "Dati aggiornati correttamente";
        }//else $cls_help->alert($data_1->GetDate()." --- ".$result[$i]["Tot_Pagamenti"]." --- ERROR");
    }
}
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
