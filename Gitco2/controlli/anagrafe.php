<?php
require $_SERVER['DOCUMENT_ROOT'] . explode("/Gitco2", $_SERVER['SCRIPT_NAME'])[0] . "/config/_config.php";

//if (!session_id()) session_start();

if ($_SESSION['username'] == NULL) {
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

//include_once($_SESSION['_path']);
//include_once(ROOT . "/_parameter.php");

include(INC . "/header.php");
include(INC . "/menu.php");

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');
$p = $cls_help->getVar('p');
$auth = $_SESSION['aut_tipo'];

$cc_comune = "";
$class = "";

if (intval($auth) > 1) {
    $cc_comune = $c;
    $class = "hidden";
} else {
    $cc_comune = "";
    $class = "";
}

$queryCities = "SELECT * FROM enti_gestiti ORDER BY Denominazione";
$a_enti = $cls_db->getResults($cls_db->SelectQuery($queryCities));
$a_selection = array("value" => "CC", "firstOpt" => 1, "selected" => $cc_comune, "text" => array("[Denominazione]", " - ", "[CC]", "  ", "[Descrizione]"));
$optionsCities = $cls_html->getOptions($a_enti, $a_selection);


?>
<style>
/*Hidden class for adding and removing*/

body {
  background: #ececec;
}
.lds-dual-ring.hidden { 
display: none;
}
.lds-dual-ring {
  display: inline-block;
  width: 80px;
  height: 80px;
}
.lds-dual-ring:after {
  content: " ";
  display: block;
  width: 64px;
  height: 64px;
  margin: 5% auto;
  border-radius: 50%;
  border: 6px solid #fff;
  border-color: #fff transparent #fff transparent;
  animation: lds-dual-ring 1.2s linear infinite;
}
@keyframes lds-dual-ring {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}


.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    background: rgba(224, 240, 254,.8);
    z-index: 999;
    opacity: 1;
    transition: all 0.5s;
}


</style>

<!-- ********** GESTIONE LINK MENU ********** -->
<script>
    //F5
    switchMenuImg("F5");
    F5_button = function() {
        location.href = "anagrafe.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
    }

    //F10
    switchMenuImg("F10");
    F10_button = function() {

        auth = "<?php echo $auth; ?>";

        cod_comune = $('#cod_comune').val();
        if(cod_comune == ''){
                                alert('È OBBLIGATORIO SELEZIONARE UNA VOCE DAL MENU A TENDINA CODICE CATASTALE') ;
                                $('#cod_comune').css('border-color', 'red');
                                setTimeout(function() {
                                                            $('#cod_comune').css('border-color', '');
                                                        }, 2000);
                                return;                        
                            }
        
        status = $('#stat_process option:selected')[0].getAttribute("value");
        if(status == ''){
                            alert('È OBBLIGATORIO SELEZIONARE UNA VOCE DAL MENU A TENDINA OPERAZIONE');
                            $('#stat_process').css('border-color', 'red');
                            setTimeout(function() {
                                                        $('#stat_process').css('border-color', '');
                                                    }, 2000);
                            return;
                        }
        
        c = "<?php echo $c; ?>";
       
        //cod_comune = $('#cod_comune option:selected')[0].getAttribute("value");
       
       
        $.ajax({
                    
            beforeSend: function () { // Before we send the request, remove the .hidden class from the spinner and default to inline-block.
                $('#loader').removeClass('hidden')
            },
            url: "ajax/ajax_omonimi.php",
            method: "POST",
            dataType: "JSON",
            data: {
                'auth': auth,
                'cod_comune': cod_comune,
                'stat_process': status,
                'c': c,
            },
            
            success: function(data) {
               
                if (data.esito == "OK") {

                    if (data.message == "EXCEL_DISPONIBILE") {
                        /*
                        $('#alert').html("");
                        $('#alert').html(
                            '<div class="alert alert-success" style="text-align: center" role="alert">' +
                                'L\'ESPORTAZIONE DEI DATI  È ANDATA A BUON FINE' +
                            '</div>'
                        );
                        */
                        swal({
                            title: "SUCCESS!",
                            text: 'File generato correttamente, controllare nei download!',
                            icon: "success",
                            timer: 25000,
                            buttons: false
                        });
                        setTimeout(function(){
                            $('#alert').html('');
                            }, 5000);
                        

                        var link = document.createElement("a");
                        document.body.appendChild(link);
                        link.setAttribute("type", "hidden");
                        link.href = data.data;
                        link.download = data.nome_file;
                        link.click();
                        document.body.removeChild(link);


                    } else {
                        /*
                        $('#alert').html("");
                        $('#alert').html(
                            '<div class="alert alert-success" style="text-align: center" role="alert">' +
                                'DATI NON DUPLICATI RISPETTO AI PARAMERI FORNITI' +
                            '</div>'
                        );
                        */
                        ShowAlert(2,"Dati non duplicati rispetto ai parametri forniti");
                        setTimeout(function(){
                            $('#alert').html('');
                            }, 5000);
                    }

                } else {
                    var msg = "";
                    var new_msg = "";
                        if(data.message == 'PARAMETRI_INESISTENTI'){
                            msg = "I CAMPI DEVONO ESSERE RIEMPITI OBBLIGATORIAMENTE";
                            new_msg = "I campi devono essere riempiti obbligatoriamente!";
                        }
                        if(data.message == 'DATI_INCOGRUENTI'){
                            msg = "I DATI SONO INCONGRUENTI";
                            new_msg = "I dati sono incongruenti!";
                        }
                        if(data.message == 'DUPLICATI_NON_ELIMINABILI'){
                            msg = "I DATI DUPLICATI NON SONO ELIMINABILI";
                            new_msg = "I dati duplicati non sono eliminabili!";
                        }
                        if(data.message == 'NO_DUPLICATI'){
                            msg = "NON ESISTONO DUPLICATI";
                            new_msg = "Non esistono duplicati!";
                        }
                    /*
                    $('#alert').html("");
                    $('#alert').html(
                        '<div class="alert alert-danger" style="text-align: center" role="alert">' +
                        msg +
                        '</div>'
                    );
                    */
                    ShowAlert(1,new_msg);
                    setTimeout(function(){
                            $('#alert').html('');
                            }, 5000);
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                
                console.log("TIPO DATA: " + (typeof(data)));
                console.log(errorThrown);
                /*
                $('#alert').html(
                    '<div class="alert alert-danger" style="text-align: center" role="alert">' +
                    'L\'ESPORTAZIONE DEI DATI NON È ANDATA A BUON FINE' +
                    '</div>'
                );
                */
                ShowAlert(1,"L'esportazione dei dati nonn è andata a buon fine!");
            },
            complete: function () { // Set our complete callback, adding the .hidden class and hiding the spinner.
                $('#loader').addClass('hidden')
            }
        });


    }
    //F11-F12 sono nel menu'
    switchMenuImg("F11");
        F11_button = function(){

            console.log($(this).attr('data-tab'));
            $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/RICERCA DOPPIONI ANAGRAFICA E NORMALIZZAZIONE.pdf"; ?>");
            $("#helpModalLabel").empty().append("<b>Help RICERCA DOPPIONI ANAGRAFICA E NORMALIZZAZIONE</b>");
            $("#helpModal").modal('show');

        }
</script>


<div class="card ">
    <div class="row justify-content-md-center ">
        <div class="col col-md-auto text_center">
            <span class="titolo font16 under_decor">GESTIONE ANAGRAFICA</span>
        </div>
    </div>
    <div class="card-body">
        <form id="elenco_omonimi" name="elenco_omonimi" method="post" target="elenco">
            <input type=hidden name="c" value="<?php echo $c ?>">
            <input type=hidden name="a" value="<?php echo $a ?>">

            <div class="row row-no-gutters" style="margin-top: 2%;">
                <div class="col col-lg-3 <?php echo $class ?> " style="margin-left:200px;">
                    <div class="form-group">
                        <label class="col-lg-4 control-label resize" style="text-align: left;">CODICE CATASTALE</label>
                        <div class="col-lg-8">
                            <select id="cod_comune" name="cod_comune" class="form-control resize" tabindex=6>
                                <?php echo $optionsCities ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col col-lg-3" style="margin-left:200px;">
                    <div class="form-group">
                        <label class="col-lg-4 control-label resize" style="text-align: left;">OPERAZIONE</label>
                        <div class="col-lg-8">
                            <select id="stat_process" name="stat_process" class="form-control resize" tabindex=6 required>
                                <option value=""></option>
                                <option value="1"> PROVVISORIA </option>
                                <option value="2"> DEFINITIVA </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </form>


        <div id="loader" class="lds-dual-ring hidden overlay"></div>


    </div>
</div>
<div id="alert"></div>

<?php include(INC . "/footer.php"); ?>