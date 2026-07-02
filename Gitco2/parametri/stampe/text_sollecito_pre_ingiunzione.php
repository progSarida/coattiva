<?php

include_once($_SERVER['DOCUMENT_ROOT']."/gitco2/_path.php");
include_once(ROOT."/_parameter.php");

include_once(INC."/header.php");
include_once(INC."/menu.php");
include_once(CLS."/cls_textParameters.php");
include_once(CLS."/cls_ente.php");

$table = "text_sollecito_pre_ingiunzione";

$cls_text = new cls_text($table);
$pageTitle = $cls_text->getPageTitle();
$a_text = $cls_db->getArrayLine($cls_db->SelectQuery($cls_text->getCCParametersQuery($c)));
$idParams = $a_text['ID'];

$a_params = $cls_text->getFieldNames();
if($a_text['ID']==null)
    $a_text = $cls_db->getArrayLine($cls_db->SelectQuery($cls_text->getParametersQuery(date("Y-m-d"))));

$cls_text->setTextArray($a_text);

$keyInformation = $cls_text->getFieldText();
if(!$idParams>0)
    $cls_text->a_text[$keyInformation] = "";

if($cls_text->a_text[$keyInformation]=="")
    $cls_help->alert("Attenzione il campo informazioni e' vuoto!");

?>

    <script>

        switchMenuImg("F3");
        F3_button = function(){
            if(checkVariables()){
                control_salva = submit_buttons('Salva');
                if(control_salva)
                    $("#form_<?php echo $table; ?>").submit();
            }
        }

        switchMenuImg("F5");
        F5_button = function(){
            stringaPHP = "c=<?php echo $c; ?>&a=<?php echo $a; ?>";
            stringa = "<?php echo $table; ?>.php?"+stringaPHP;
            top.location.href = stringa;
        }

        var params = <?php echo json_encode( $a_params ) ?>;

        function checkVariables(){
            for (var key in params) {
                // skip loop if the property is from prototype
                if (!params.hasOwnProperty(key)) continue;

                var obj = params[key];
                for (var prop in obj) {
                    // skip loop if the property is from prototype
                    if(!obj.hasOwnProperty(prop)) continue;

                    if(prop=="variables"){
                        var variables = obj[prop];
                        var testo = $('#field'+key).val();

                        char_consentiti(key);
                        // checkTesto(testo);

                        for (var num in variables) {
                            // skip loop if the property is from prototype
                            if (!variables.hasOwnProperty(num)) continue;

                            var checkVar = testo.indexOf(variables[num]);
                            if (checkVar == -1)
                            {
                                var message = "Non hai inserito il campo obbligatorio ' ";
                                message += variables[num];
                                message += " ' nel campo ' ";
                                message += obj['field'];
                                message += " '";
                                alert (message);
                                return false;
                            }
                        }
                    }
                }
            }
            return true;
        }

        function char_consentiti(fieldNumber) {
            campo = $('#field'+fieldNumber);
            // espressione regolare
            var re = new RegExp("[0-9a-zA-Z :;.,+-?'*!/{}_<>%]+$", "");
            // recupero il valore del campo
            var valore = campo.val();
            // ciclo le lettere del valore e le verifico
            for ( var i = 0; i < valore.length; i++ ) {
                // se non è un carattere consentito
                if (!valore.charAt(i).match(re)) {
                    if (valore.charAt(i) == String.fromCharCode(13)) char = "INVIO";
                    else if (valore.charAt(i) == String.fromCharCode(10)) char = "INVIO";
                    else char = valore.charAt(i);

                    // aggiorno il valore del campo
                    campo.val(valore.substring(0, i));
                    field = $('#id'+fieldNumber).text();
                    var messageError = "Hai inserito un carattere non valido ' " + char + " ' nel campo ' " + field + " ' ";
                    alert(messageError);
                    // esco
                    return;
                }
            }
        }

        $(document).ready(function(){
            $('#form_<?php echo $table; ?>').ajaxForm(
                function(value) {
                    var array_ritorno = value.split(' ');

                    if(array_ritorno[0]=='SAVED')
                    {
                        alert('Testo salvato correttamente!');
                    }
                    else if(array_ritorno[0]=='ERROR')
                    {
                        alert('Salvataggio testo fallito! '+array_ritorno[1]);
                    }
                });

        });

    </script>


    <table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
        <tr>
            <td><span class="titolo font16 under_decor"><?php echo $pageTitle; ?></span></td>
        </tr>
    </table>

    <table class="table_interna text_center" border="0" cellspacing="10" cellpadding="0">
        <tr>
            <td><span class="titolo font12">Intestazione</span></td>
        </tr>
    </table>

    <table class="table_interna text_center borderino" border="0" cellspacing="10" cellpadding="0">
        <tr >
            <td class="width20">Logo Gestore</td>
            <td class="width4"></td>
            <td class="width46 text_left">Dati Gestore</td>
            <td class="width30 text_left">Dati Ufficio</td>
        </tr>
        <tr >
            <td colspan=4><br></td>
        </tr>
        <tr >
            <td class="width20">Riferimenti</td>
            <td class="width4"></td>
            <td class="width46 text_left"></td>
            <td class="width30 text_left">Destinatario</td>
        </tr>
    </table>

    <br>

    <form name="<?php echo $table; ?>" id="form_<?php echo $table; ?>" action="<?php echo $table; ?>_exe.php" method="post">

        <input type="hidden" name="c" value="<?php echo $c?>">
        <input type="hidden" name="table" value="<?php echo $table; ?>">
        <input type="hidden" name="idParams" value="<?php echo $idParams; ?>">

        <table class="table_interna text_center" border="0">
            <thead>
            <tr>
                <td class="width15">
                    <div id="legendaIngiunzione">
                        <span class="color_red font12">Campo</span>
                    </div>
                </td>
                <td class="width15">
			<span class="color_red font12">
			Pag. / Allin. / Font
			</span>
                </td>
                <td class="width45">
			<span class="color_red font12">
			Testo
			</span>
                </td>
                <td>
			<span class="color_red font12">
			Variabili obbligatorie
			</span>
                </td>
            </tr>
            </thead>
            <tbody id="sort">
            <?php


            for($i=1;$i<=count($cls_text->a_textParams);$i++){
                $fieldId = $cls_text->a_textParams[$i]['field'];
                $expId = explode(" ", $fieldId);
                $checkField = $expId[0];

                $variables = implode("<br>",$cls_text->a_textParams[$i]['variables']);

                $disabledAlign = "disabled";
                $disabledWeight = "disabled";
                $disabledPage = "disabled";
                $alignment = "<script>$('#alignment".$i."').val('".$cls_text->a_textParams[$i]['alignment']."');</script>";
                $fontWeight = "<script>$('#fontweight".$i."').val('".$cls_text->a_textParams[$i]['fontWeight']."');</script>";
                $page = "<script>$('#fontweight".$i."').val('".$cls_text->a_textParams[$i]['page']."');</script>";
                if($checkField!="firma"){
                    $input = "<textarea id=\"field".$i."\" name=\"field[".$i."]\" onkeyup=\"char_consentiti(".$i.");\" style=\"width:95%\" rows=\"4\">";
                    $input.= $cls_text->a_text['field'.$i]."</textarea>";
                }
                else{

                    $input = "<select id=\"field".$i."\" name=\"field[".$i."]\" class=\"width95\">";
                    $input.= "<option value=\"{FUNZIONARIORESPONSABILE}\">Legale Rappresentante/Funzionario Responsabile</option>";
                    $input.= "<option value=\"{RESPONSABILEPROCEDIMENTO}\">Responsabile del Procedimento</option>";
                    $input.= "</select>";

                    $input.= "<script>$('#field".$i."').val(\"".$cls_text->a_text['field'.$i]."\")</script>";
                }

                ?>

                <tr >
                    <td>
                        <div id="id<?=$i?>"><?=ucwords($fieldId)?>:</div>
                    </td>
                    <td>
                        <select class="width95" id="page<?=$i?>" name="page[<?=$i?>]" <?php echo $disabledPage; ?>>
                            <option value="1">Pagina 1</option>
                            <option value="2">Pagina 2</option>
                            <option value="3">Pagina 3</option>
                            <option value="4">Pagina 4</option>
                        </select>
                        <?php echo $page; ?>
                        <select class="width95" id="alignment<?=$i?>" name="alignment[<?=$i?>]" <?php echo $disabledAlign; ?>>
                            <option value="L">Sinistra</option>
                            <option value="C">Centro</option>
                            <option value="R">Destra</option>
                            <option value="J">Giustificato</option>
                        </select>
                        <?php echo $alignment; ?>
                        <select class="width95" id="fontweight<?=$i?>" name="fontweight[<?=$i?>]" <?php echo $disabledWeight; ?>>
                            <option value="normal">Normale</option>
                            <option value="bold">Grassetto</option>
                        </select>
                        <?php echo $fontWeight; ?>

                    </td>
                    <td><?php echo $input; ?></td>
                    <td>
                        <span class="font12"><?php echo $variables; ?></span>
                    </td>
                </tr>

                <?php
                if($cls_text->a_textParams[$i]['field']=="comunicazione importi"){
                    ?>
                    <tr>
                        <td>
                            <div>Dettaglio Importi:</div>
                        </td>
                        <td></td>
                        <td><textarea class="sfondo_grigio width95" rows="2">DETTAGLIO IMPORTI</textarea></td>
                        <td></td>
                    </tr>

                    <?php
                }
            }

            ?>

            </tbody>
        </table>
        <br>

    </form>

<?php

include(INC."/footer.php");