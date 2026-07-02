<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC."/header.php");
include(INC."/menu.php");
include_once CLS."/cls_db.php";
include_once CLS."/cls_file.php";
include_once CLS."/cls_html.php";
include_once CLS."/cls_CaricaImmaginiCAD.php";


$cls_db = new cls_db();
$cls_html = new cls_html();

$workpath = IMPORTAZIONE_CAD."/". $_SESSION['username'];
$webpath = IMPORTAZIONE_CAD_WEB."/". $_SESSION['username'];

CaricamentoImmaginiCAD::CreaPath($workpath);

$carica_immagini = new CaricamentoImmaginiCAD();
$immagine = $carica_immagini
->Set("workpath",$workpath)
->Set("webpath",$webpath)
->CaricaImmagini()
->Immagine();

$file_presenti = $carica_immagini->GetNumber()>0;

$CreaSelect = new Class{

        private $query;
        private $cls_html;
        private $cls_db;
        private $a_text;
        private $value;
        private $selected;

        public function Set($variabile, $valore)
        {
            $this->{$variabile} = $valore;
            return $this;
        }

        public function Options()
        {
            $a_enti = $this->cls_db->getResults( $this->cls_db->SelectQuery($this->query) );
            $a_selection = array("value"=>$this->value,"firstOpt"=>1,"selected"=>$this->selected,"text"=>$this->a_text);
            return $this->cls_html->getOptions($a_enti,$a_selection);
        }

};

$optionsCities = $CreaSelect
                    ->Set("cls_html",$cls_html)
                    ->Set("cls_db",$cls_db)
                    ->Set("query","SELECT EG.* FROM enti_gestiti EG LEFT JOIN anni_gestiti A ON A.CC_Anno=EG.CC WHERE A.ID is not null Group BY EG.Denominazione ORDER BY EG.Denominazione")
                    ->Set("value","CC")
                    ->Set("selected",null)
                    ->Set("a_text",array("[Denominazione]"," - ","[CC]","  ","[Descrizione]"))
                    ->Options();

?>
<link rel="stylesheet" type="text/css" href="<?= DATATABLE ?>/datatables.css"/>
<script type="text/javascript" src="<?= DATATABLE ?>/datatables.js"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script>
    var web_root = "<?php echo  WEB_ROOT ?>";
    var web_datatable = "<?php echo  DATATABLE ?>";
    var web_dteditor = "<?php echo  ELAB_DTEDITOR_WEB ?>";
    var CC='';
    var AttoID='';
    var flag = "<?php echo $immagine["flag"] ?>";
    var pathcompleto = "<?php echo $immagine["pathcompleto"] ?>";
    var pathcompletoF = "<?php echo $immagine["pathcompletoF"] ?>";
    var pathcompletoR = "<?php echo $immagine["pathcompletoR"] ?>";
    var path = "<?php echo $immagine["path"] ?>";
    var filename = "<?php echo $immagine["filename"] ?>";
    var filenameR = "<?php echo $immagine["filenameR"] ?>";
    var filenameF = "<?php echo $immagine["filenameF"] ?>";
    
    switchMenuImg("F11");
    F11_button = function(){
        $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/Inserimento_Manuale_CAD.pdf"; ?>");
        $("#helpModalLabel").empty().append("<b>Help Inserimento Multiplo CAD</b>");
        $("#helpModal").modal('show');
    }
</script>
<style>
    .select {
    height: 20px;
    width: 480px;
    }
    .immagine{
                width :500px;
                height : 450px;
            }

    .showPdf{
        width :700px;
        height : 400px;
    }
</style>
<?php 
    if ($file_presenti)
    {
?>
<script>
    function AssegnaButtonClick(atto,tipo_atto)
    {
       AttoID = atto;
       var comune_scelto = $('#Citta').val();
       if (Conferma(1))
       {
        $.ajax({
            type: "POST",
            async: true,
            url: "ajax/ajax_carica_CAD.php",
            data: {
                atto_corrente:AttoID,
                flag:flag,
                filename:filename,
                pathcompleto:pathcompleto,
                filenameF:filenameF,
                filenameR:filenameR,
                pathcompletoF:pathcompletoF,
                pathcompletoR:pathcompletoR,
                tipo_atto:tipo_atto,
                comune_scelto:comune_scelto
               
            },
            success: function(response) {
                var result =JSON.parse(response);
                gestione_file_CAD(1);
                location.reload();
            },
            error: function(response){
                console.log(response);
            }
        });
       }
       

    }
    function InserisciInNome(result)
    {
        $('#Nome').empty();//.append('<option selected="selected" value="Tutti">Tutti</option>');
        var toAppend = '';
           $.each(result,function(i,o){
           if (o.Genere!="D")
            toAppend += '<option value = '+o.Utente_ID+'>'+o.Cognome + ', ' +o.Nome +'  ID='+ o.Comune_ID +'</option>';
           else
            toAppend += '<option value = '+o.Utente_ID+'>'+o.Ditta.replaceAll(',',' ') +' ID='+ o.Comune_ID +'</option>';
          });

         $('#Nome').append(toAppend);
    }
    function Ajax(){
        var comune = $('#Citta').val();
        //alert(comune);
        if(comune!="")
        $.ajax({
            type: "POST",
            async: true,
            url: "ajax/ajax_get_data_nome.php",
            data: {

                cc:comune
            },
            success: function(response) {
                var result =JSON.parse(response);
                InserisciInNome(result);
                $( "#Nome" ).trigger( "change" );
            },
            error: function(response){
                console.log(response);
            }
        });
    }
    $(document).ready(function()
    {
        if($('#Citta').val()=="")
        {
            $('#Nome').attr('disabled', 'disabled');
        };
        $('#Citta').on('change', function (e) {
            var optionSelected = $("option:selected", this);
            var valueSelected = this.value;
            if (valueSelected!="")
            {
                $('#Nome').removeAttr('disabled');
                CC=valueSelected;
            }   
            else
            {
                $('#Nome').attr('disabled', 'disabled');
                $("#Nome option[value='']").attr('selected', true)
            }
            Ajax();
            $('#dt_table').DataTable().ajax.reload();

        });
        $('#Nome').on('change',function(e){
            $('#dt_table').DataTable().ajax.reload();
           
        });
        

    });
</script>

<!--
<div class="tableFixHead" style="overflow-y: auto; max-height: 64vh !important; width: 80%; margin-left: 10%; overflow-y: auto; display: block;">
-->
<?php
    if ($immagine["flag"]){
?>

    <!-- due immagini -->
    <style>
        .table_immagini{
            width: 100%;
        }
        .table_immagini th{
            width: 50%;
        }
        .table_immagini.center {
            margin-left: auto; 
            margin-right: auto;
        }

    </style>
    <table id="table_immmagini" class="table_immagini">
    <tr>
        <th>
            <div id=mostra_immagineFronte class="image-magnify4 resize" title="Clicca per allargare immagine" onclick="window.open('<?php echo $immagine['webpathcompleto']; ?>')">
                <div class="thumbnail4 text_center">
                    <img class="immagine" id="thumbnail_image4" src="<?php echo $immagine['webpathcompletoF']; ?>" border="1>">
                </div>
            </div>
        </th>
        <th>
            <div id=mostra_immagineRetro class="image-magnify4 resize" title="Clicca per allargare immagine" onclick="window.open('<?php echo $immagine['webpathcompleto']; ?>')">
                <div class="thumbnail4 text_center">
                    <img class="immagine" id="thumbnail_image4" src="<?php echo $immagine['webpathcompletoR']; ?>" border="1>">
                </div>
            </div>
        </th>
    </tr>
    </table>
<?php } else {
?>
    <!-- una sola immagine -->
    <div id=mostra_immagine class="image-magnify4 resize" title="Clicca per allargare immagine" onclick="window.open('<?php echo $immagine['webpathcompleto']; ?>')">
			<div class="thumbnail4 text_center">
                <?php if($immagine["isPDF"])
                {
                    ?>
                    <iframe class="showPdf" id="thumbnail_image4" src="<?php echo $immagine['webpathcompleto']; ?>" border="1"></iframe>
                    <?php
                }
                else{
                    ?>
                    <img class="immagine" id="thumbnail_image4" src="<?php echo $immagine['webpathcompleto']; ?>" border="1">
                    <?php
                }
                ?>
				
			</div>
		</div>
<?php } ?>
    <table class="table table-hover" cellspacing="2" cellpadding="0" style="border-bottom:0px solid black;border-right:0px solid black;border-left:0px solid black;">
        <colgroup>
            <col style="width: 10%">
            <col style="width: 10%">
        </colgroup>
        <thead border="0" cellspacing=0 style="border-bottom: 2px solid #6963FF;">
            <tr >
                <th class=" text_center"><b>Città</b><br>
                <select id="Citta" name="Citta" class="select">
                        <option></option>
                        <?php echo $optionsCities; ?>
                    </select>
                </th>
                <th class=" text_center"><b>Nome</b><br>            
                    <select name="Nome" id="Nome" class="select">
                    </select>
                </th>
            </tr>
        </thead>

        <tbody class="table table-hover text_center" id="table_ricerca" border="0" cellspacing=0>
        </tbody>

    </table>
<!--
</div> 
-->
<div style="padding: 0 30px 20px 30px;">
        <table id="dt_table" class="table table-striped table-bordered display" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Partita ID</th>
                    <th>Anno Crono</th>
                    <th>Id Crono</th>
                    <th>Atto</th>
                    <th>Raccomandata</th>
                    <th>Info Cartella</th>
                    <th></th>
                </tr>
            </thead>
        </table>
</div>
<script src="js\DTTableSceltaMultipla.js"></script>  
<style>
    .bottoni{
        margin:auto;
        position:relative;
        width: max-content;
        padding-bottom: 25px;
    }

    /* CSS */
    .bottoni button {
    background-image: linear-gradient(-180deg, #37AEE2 0%, #1E96C8 100%);
    border-radius: .5rem;
    box-sizing: border-box;
    color: #FFFFFF;
    display:inline-block;
    font-size: 16px;
    justify-content: center;
    padding: 1rem 1.75rem;
    text-decoration: none;
    /*width: 100%;*/
    border: 0;
    cursor: pointer;
    user-select: none;
    -webkit-user-select: none;
    touch-action: manipulation;
    }

    .bottoni button:hover {
    background-image: linear-gradient(-180deg, #1D95C9 0%, #17759C 100%);
    }


</style>
<script>
    function Conferma(stato)
    {
        var a=new Array(
            "0",
            "Sei sicuro dell'assegnazione ?",
            "Sei sicuro di non processare ?",
            "Sei sicuro di scartare ?",
            "Sei sicuro che sia un CDS ?",
            "Sei sicuro che sia un duplicato ?"
        );
        return confirm(a[stato]);
    }

    function gestione_file_CAD(stato)
    {
        $.ajax({
                type: "POST",
                async: true,
                url: "ajax/ajax_gestione_file_CAD.php",
                data: {
                    stato:stato,
                    flag:flag,
                    path:path,
                    filename:filename,
                    filenameR:filenameR,
                    filenameF:filenameF
                },
                success: function(response) {
                    var result =JSON.parse(response);
                    console.log(result);
                },
                error: function(response){
                    console.log(response);
                }
            });
    }
    function GestioneFile(stato)
    {
        if (Conferma(stato))
        {
            gestione_file_CAD(stato);
            location.reload();
        }
    }
</script>
<div id="bottoni" class="bottoni">
    <button id="CDS" name="CDS" onclick="GestioneFile(4);">CDS</button>
    <button id="Duplicato" name="Duplicato" onclick="GestioneFile(5);">Duplicato</button>
    <button id="NonProcessato" name="NonProcessato" onclick="GestioneFile(2);">Non processato</button>
    <button id="Scarta" name="Scarta" onclick="GestioneFile(3)">Scarta</button>
</div>
<?php 

} //cartella vuota
else{
?>
    <style>
        .avviso{
            font-size: 40px;
            text-align: center;
            font-weight: bold;
        }
    </style>
    <!-- file non presenti -->
    <table class="table table-hover" cellspacing="2" cellpadding="0" style="border-bottom:0px solid black;border-right:0px solid black;border-left:0px solid black;">
    <tbody class="table table-hover text_center" id="table_ricerca" border="0" cellspacing=0>
        <tr><td>
            <p class="avviso"> CARTELLA IMPORTAZIONE VUOTA</p>
        </td></tr>
    </tbody>
    </table>

<?php
} 

die;
include(INC."/footer.php");
