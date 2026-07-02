<?php
include_once($_SERVER['DOCUMENT_ROOT']."/gitco2/_path.php");
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");
include_once CLS."/cls_pdf.php";
include_once CLS."/cls_file.php";
include_once CLS."/cls_ente.php";
include_once CLS."/cls_registry.php";

$id = 1;
$lan = 1;
$city = "A950";

$rs = new cls_db();
$charge_rows = $rs->SelectQuery("SELECT * FROM Form where FormTypeId='$id' AND LanguageId = '$lan' AND CityId = '$city'");

$query = "SELECT * FROM v_atti ";
$query.= "WHERE 1=1 ";
$query.= "AND CC='".$c."' ";

$a_results = $rs->getArrayLine($rs->SelectQuery($query));

$cls_ente = new cls_ente($a_enteAdmin);
$cls_ente->setPrintHeader();
$cls_registry = new cls_registry();
$a_recipientHeader = $cls_registry->printHeader($a_results);

$pdf = new cls_pdf("P", "mm", "A4", true, 'UTF-8', false);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetCellPadding(0);

$pdf->AddPage("P");

$pdf->setManagerHeader($cls_ente->a_header);

$pdf->setRecipientHeader($a_recipientHeader);
$pdf->SetFont('Arial', '', 8.5);
$pdf->SetMargins(7.0, 10.0, 7.0);

$pdf->ln(0);
$cls_file = new cls_file();
$dimsx = $cls_file->imageSize("firmasx.jpg",140,45);
$dimdx = $cls_file->imageSize("firmadx.jpg",140,45);
$amounts = <<<EOF
<table>
<tr>
<td style="width:86%;" align="left">Imposta comunale sulla pubblicita'</td>
<td style="width:2%; text_align:right;">+</td>
<td style="width:10%; text_align:right;">81,81</td>
<td style="width:2%; text_align:right;">&euro;</td>
</tr>
<tr>
<td style="width:86%;" align="left">Sanzione pecuniaria omessa dichiarazione</td>
<td style="width:2%; text_align:right;">+</td>
<td style="width:10%; text_align:right;">163,62</td>
<td style="width:2%; text_align:right;">&euro;</td>
</tr>
<tr>
<td style="width:86%;" align="left">Sanzione pecuniaria tardivo pagamento</td>
<td style="width:2%; text_align:right;">+</td>
<td style="width:10%; text_align:right;">24,54</td>
<td style="width:2%; text_align:right;">&euro;</td>
</tr>
<tr>
<td style="width:86%;" align="left">Interessi moratori</td>
<td style="width:2%; text_align:right;">+</td>
<td style="width:10%; text_align:right;">1,58</td>
<td style="width:2%; text_align:right;">&euro;</td>
</tr>
<tr>
<td style="width:86%;" align="left">Altri diritti e accessori</td>
<td style="width:2%; text_align:right;">+</td>
<td style="width:10%; text_align:right;">6,54</td>
<td style="width:2%; text_align:right;">&euro;</td>
</tr>
<tr>
<td style="width:86%;" align="left">Interessi Ingiunzioni/Avvisi di Messa in Mora</td>
<td style="width:2%; text_align:right;">+</td>
<td style="width:10%; text_align:right;">0,12</td>
<td style="width:2%; text_align:right;">&euro;</td>
</tr>
<tr><td vertical-align="middle" colspan="4"> <hr></td></tr>
<tr>
<td style="width:86%;" align="left"><strong>TOTALE COMPLESSIVO (1) [ Entro 60 giorni dalla notifica - Oneri Riscossione 3,00% - Euro 9,09 ]</strong></td>
<td style="width:2%; text_align:right;"><strong>=</strong></td>
<td style="width:10%; text_align:right;"><strong>312,21</strong></td>
<td style="width:2%; text_align:right;"><strong>&euro;</strong></td>
</tr>
<tr>
<td style="width:86%;" align="left"><strong>TOTALE COMPLESSIVO (2) [ Oltre 60 giorni dalla notifica - Oneri Riscossione 6,00% - Euro 18,19 ]</strong></td>
<td style="width:2%; text_align:right;"><strong>=</strong></td>
<td style="width:10%; text_align:right;"><strong>321,31</strong></td>
<td style="width:2%; text_align:right;"><strong>&euro;</strong></td>
</tr>
</table>
EOF;

$firmasx = <<<EOF
<img src="firmasx.jpg" style="width: $dimsx[0]px; height: $dimsx[1]px;" /><br>
<span>RICCARDO SAMBUCETI</span>
EOF;
$firmadx = <<<EOF
<img src="firmadx.jpg" style="width: $dimsx[0]px; height: $dimsx[1]px;" /><br>
<span>STEFANO MENICHETTI</span>
EOF;

?>



    <div class="col-md-10 col-md-offset-1">
        <div class="col-md-9">
            <div class="panel panel-primary" style="margin-top: 30px;!important;">
                <div class="panel-heading"><h4>Modifica il verbale per Comune</h4></div>
                <form id="form_form" method="post" action="update_formali.php" accept-charset="UTF-8" enctype='multipart/form-data'>
                    <div class="panel-body">
                        <input type="hidden" name="c" value="<?=$c;?>">
                        <input type="hidden" name="a" value="<?=$a;?>">
                        <input type="hidden" name="lan" value="<?php echo $lan;?>">
                        <input type="hidden" name="city" value="<?php echo $city;?>">
                        <input type="hidden" name="formid" value="<?php echo $id;?>">
                        <?php
                        while ($row = mysqli_fetch_array($charge_rows)) {
                            $title = utf8_encode($row['Content']);
                            $title = str_replace("€", "&euro;", $title);
                            ?>
                            <textarea  id="note_verbali" name="note_verbali" rows="50" class="form-control"><?php  echo $title; ?></textarea>
                            <?php
                            $title = str_replace("<p>&nbsp;</p>", "<br><br>", $title);
                            $title = str_replace("<div>&nbsp;</div>", "<br><br>", $title);

                            $title = str_replace("{FIRMASX}", $firmasx, $title);
                            $title = str_replace("{FIRMADX}", $firmadx, $title);
                            $title = str_replace("{IMPORTI}", $amounts, $title);
                            $pdf->writeHTML($title);
                        }
                        ?>

                        <div class="panel-footer">
                            <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                                <button type="submit" name="update" class="btn btn-success ">Modifica</button>
                                <a class="btn btn-default" href="tbl_form.php?Search_FormType=<?php echo $id;?>">Indietro</a>
                            </div>
                        </div>
                        <?php

                        ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="panel panel-primary" style="margin-top: 30px;!important; height: 1000px;overflow: scroll;">
            <div class="panel-heading"><h4>Descrizione</h4></div>
                <ul class="list">
                    <li class="list-group-item"><b>{ProtocolId}</b>-> Numero Cronologico</li>
                    <li class="list-group-item"><b>{ProtocolYear}</b>-> Anno verbale</li>
                    <li class="list-group-item"><b>{ProtocolLetter}</b>-> Lettera comune (nel cronologico)</li>
                    <li class="list-group-item"><b>{Code}</b>-> Riferimento comune</li>
                    <li class="list-group-item"><b>{CurrentDate}</b>-> Data corrente</li>
                    <li class="list-group-item"><b>{FineDate}</b>-> Data verbale</li>
                    <li class="list-group-item"><b>{FineTime}</b>-> Ora verbale</li>
                    <li class="list-group-item"><b>{Locality}</b>-> Ente verbale</li>
                    <li class="list-group-item"><b>{Address}</b>-> Località verbale</li>
                    <li class="list-group-item"><b>{VehicleTypeId}</b>-> Tipo veicolo</li>
                    <li class="list-group-item"><b>{VehicleBrand}</b>-> Marca veicolo</li>
                    <li class="list-group-item"><b>{VehicleModel}</b>-> Modello veicolo</li>
                    <li class="list-group-item"><b>{VehicleColor}</b>-> Colore veicolo</li>
                    <li class="list-group-item"><b>{VehiclePlate}</b>-> Targa veicolo</li>
                    <li class="list-group-item"><b>{ArticleId}</b>-> Articolo</li>
                    <li class="list-group-item"><b>{ArticleDescription}</b>-> Descrizione articolo</li>
                    <li class="list-group-item"><b>{ArticleAdditionalText}</b>-> Testo addizionale articolo</li>
                    <li class="list-group-item"><b>{AdditionalSanctionId}</b>-> Sanzione accessoria</li>
                    <li class="list-group-item"><b>{ReasonId}</b>-> Mancata contestazione</li>
                    <li class="list-group-item"><b>{BankAccount}</b>-> C/C</li>
                    <li class="list-group-item"><b>{BankOwner}</b>-> Intestatario conto</li>
                    <li class="list-group-item"><b>{BankIban}</b>-> IBAN</li>
                    <li class="list-group-item"><b>{PartialFee}</b>-> Importo sanzione ridotta</li>
                    <li class="list-group-item"><b>{AdditionalFee}</b>-> Costi aggiuntivi</li>
                    <li class="list-group-item"><b>{NotificationFee}</b>-> Costi notifica</li>
                    <li class="list-group-item"><b>{ResearchFee}</b>-> Costi ricerca</li>
                    <li class="list-group-item"><b>{TotalPartialFee}</b>-> Totale sanzione ridotta</li>
                    <li class="list-group-item"><b>{AdditionalFeeCAN}</b>-> Spese CAN</li>
                    <li class="list-group-item"><b>{CANFee}</b>-> Importo con CAN</li>
                    <li class="list-group-item"><b>{TotalPartialFeeCAN}</b>-> Importo ridotto verbale con CAN</li>
                    <li class="list-group-item"><b>{AdditionalFeeCAD}</b>-> Spese CAD</li>
                    <li class="list-group-item"><b>{CADFee}</b>-> Importo con CAD</li>
                    <li class="list-group-item"><b>{TotalPartialFeeCAD}</b>-> Importo ridotto verbale con CAD</li>
                    <li class="list-group-item"><b>{Fee}</b>-> Importo sanzione</li>
                    <li class="list-group-item"><b>{TotalFee}</b>-> Importo totale verbale</li>
                    <li class="list-group-item"><b>{TotalFeeCAN}</b>-> Importo totale verbale con CAN</li>
                    <li class="list-group-item"><b>{TotalFeeCAD}</b>-> Importo totale verbale con CAD</li>
                    <li class="list-group-item"><b>{MaxFee}</b>-> Importo massimo sanzione</li>
                    <li class="list-group-item"><b>{TotalMaxFee}</b>-> Importo totale verbale massimo</li>
                    <li class="list-group-item"><b>{TotalMaxFeeCAN}</b>-> Importo massimo verbale con CAN</li>
                    <li class="list-group-item"><b>{TotalMaxFeeCAD}</b>-> Importo massimo verbale con CAD</li>
                    <li class="list-group-item"><b>{TrespasserName}</b>-> Trasgressore</li>
                    <li class="list-group-item"><b>{TrespasserBornCity}</b>-> Luogo nascita</li>
                    <li class="list-group-item"><b>{TrespasserBornDate}</b>-> Data nascita</li>
                    <li class="list-group-item"><b>{TrespasserRentName}</b>-> Ditta noleggio</li>
                    <li class="list-group-item"><b>{DateRent}</b>-> Data identificazione noleggiante</li>
                    <li class="list-group-item"><b>{TrespasserAddress}</b>-> Indirizzo trasgressore</li>
                    <li class="list-group-item"><b>{TrespasserCity}</b>-> Citta trasgressore</li>
                    <li class="list-group-item"><b>{TrespasserProvince}</b>-> Provincia trasgressore</li>
                    <li class="list-group-item"><b>{TrespasserName}</b>-> Locatario/Noleggiante</li>
                    <li class="list-group-item"><b>{TrespasserBornCity}</b>-> Luogo nascita Locatario/Noleggiante</li>
                    <li class="list-group-item"><b>{TrespasserBornDate}</b>-> Data nascita Locatario/Noleggiante</li>
                    <li class="list-group-item"><b>{ControllerName}</b>-> Accertatore</li>


                </ul>
            </div>
        </div>

    <script>
        switchMenuImg("F3");
        F3_button = function(){
            $("#form_form").submit();
        }

        function openPdf(value){

            window.name = "Stampa";
            window.open(value,"_blank");
        }


        var edit = CKEDITOR.replace('note_verbali', {

            customConfig: '',
            filebrowserBrowseUrl: './ckfinder/ckfinder.html',
            filebrowserImageBrowseUrl: './ckfinder/ckfinder.html?type=Images',
            disallowedContent: 'img{width,height,float}',
            extraAllowedContent: 'img[width,height,align];span{background}',
            extraPlugins: 'colorbutton,font,justify,print,tableresize,uploadimage,uploadfile,pastefromword,liststyle',
            height: 1000,
            contentsCss: [
                'http://cdn.ckeditor.com/4.11.3/full-all/contents.css',
                'assets/css/pastefromword.css'
            ],
        });
        edit.config.allowedContent = true;
        edit.config.removePlugins = 'Source';
        editor.execCommand( 'shiftEnter' );


    </script>

<?php
$pdf->Output( "provahtml.pdf", 'F' );
$responseEcho = str_replace("<","&lt;",$title);
$responseEcho = str_replace(">","&gt;",$responseEcho);
echo $responseEcho;
?>
<script>openPdf("provahtml.pdf")</script>
<?php
include(INC."/footer.php");
