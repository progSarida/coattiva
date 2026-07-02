<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC."/header.php");
include(INC."/menu.php");
include_once CLS."/cls_db.php";
include_once CLS."/cls_file.php";
include_once CLS."/cls_html.php";
include_once CLS."/cls_Giacenze.php";

$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_html = new cls_html();
$CreaSelect = new Class{

    private $cls_html;
    private $a_text;
    private $value;
    private $selected;
    private $options;

    public function Set($variabile, $valore)
    {
        $this->{$variabile} = $valore;
        return $this;
    }

    public function Options()
    {
        
        $a_descrizioni = $this->options;
        $a_selection = array("value"=>$this->value,"firstOpt"=>1,"selected"=>$this->selected,"text"=>$this->a_text);
        return $this->cls_html->getOptions($a_descrizioni,$a_selection);
    }

};

$webpath = IMMAGINI_NOTIFICHE_WEB;
$path = IMMAGINI_NOTIFICHE;

$CC = $cls_help->getVar('c');

$offset = $cls_help->getVar('offset')==null ? 0 : $cls_help->getVar('offset');

$giacenze = new Giacenze($cls_db);
$giacenze
->Set("CC",$CC)
->Set("offset",$offset)
->Set('web_path_root',$webpath)
->Set('path_root',$path)
->Esegui();

$contatore = $giacenze->GetNumberNotifiche();
$a_select = $giacenze->GetParametriNotifiche();

$optionsStatoNotifica = $CreaSelect
                ->Set("cls_html",$cls_html)
                ->Set("options",$a_select)
                ->Set("value","ID")
                ->Set("selected",null)
                ->Set("a_text",array("[Descrizione]"))
                ->Options();

$avviso = function($msg)
{
    return <<<EOL
    
    <style>
        .avviso{
            font-size: 40px;
            text-align: center;
            font-weight: bold;
        }
    </style>
    
    <table class="table table-hover" cellspacing="2" cellpadding="0" style="border-bottom:0px solid black;border-right:0px solid black;border-left:0px solid black;">
        <tbody class="table table-hover text_center" id="table_ricerca" border="0" cellspacing=0>
            <tr><td>
                <p class="avviso"> $msg </p>
            </td></tr>
        </tbody>
    </table>
    EOL;
};

if ($giacenze->IsResult())
{
    $result=$giacenze->GetResult();
?>
<link rel="stylesheet" type="text/css" href="<?= DATATABLE ?>/datatables.css"/>
<!-- due immagini -->
<?php
    if ($giacenze->IsFronteImmagine())
    {
?>
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
        .immagine{
            width :600px;
            height : 400px;
        }
        .select {
            height: 20px;
            width: 480px;
        }
        .tabella thead {
            background-color: #1c87c9;
            color: #ffffff;
        }
        <?php 
        if ($giacenze->IsDitta())
        {
        ?>
            .persona{
                    display:none; 
                }
        <?php
        }
        else
        {
        ?>
            .ditta{
            display:none; 
            }
        <?php
        }
        ?>
        <?php 
        if (!$giacenze->IsRetroImmagine())
        {
        ?>
            .ThRetro{
                    display:none; 
                }
        <?php
        }
        ?>
        
</style>
<table id="table_immmagini" class="table_immagini">
    <tr>
        <th>
            <div id=mostra_immagineFronte class="image-magnify4 resize" title="Clicca per allargare immagine" onclick="window.open('<?php echo $result['path_completo_immagine_fronte']; ?>')">
                <div class="thumbnail4 text_center">
                    <img id="thumbnail_image4" src="<?php echo $result['path_completo_immagine_fronte']; ?>" border="1" class = "immagine">
                </div>
            </div>
        </th>
        <th class=ThRetro>
            <div id=mostra_immagineRetro class="image-magnify4 resize" title="Clicca per allargare immagine" onclick="window.open('<?php echo $result['path_completo_immagine_retro']; ?>')">
                <div class="thumbnail4 text_center">
                    <img id="thumbnail_image4" src="<?php echo $result['path_completo_immagine_retro']; ?>" border="1" class="immagine">
                </div>
            </div>
        </th>
    </tr>
</table>
<?php
    }
    else{
        echo $avviso("NON CI SONO IMMAGINI PER QUESTO ATTO");
    } 
?>

<script>

    $(document).ready(function()
    {  
        var atto_ID=<?php echo $result["Atto_ID"] ?>;
        
        $('#StatoNotifica').on('change', function (e) {
            var Stato_Notifica=$('#StatoNotifica').val();
            console.log(Stato_Notifica=="" ? "vero" : "falso");
            console.log("Stato_Notifica="+Stato_Notifica);
            if ((Stato_Notifica=="") || (Stato_Notifica==null)) return;
            console.log("passato1");
            $.ajax( {
                type: "POST",
                async: true,
                url: "ajax/ajax_aggiorna_stato_notifica.php",
                data: {
                    atto_ID: atto_ID,
                    Stato_Notifica: Stato_Notifica
                },
                success: function(response){        
                var response = JSON.parse(response);
       
                if(response.esito == "OK")
                {
                    swal({
                            title: "SUCCESS!",
                            text:  response.message,
                            icon: "success",
                            timer: 25000,
                            buttons: false
                        });
                        window.location.href ="<?= WEB_ROOT ?>/coattiva/inserimento_manuale_giacenza.php?c=<?php echo $CC ?>&offset="+<?php echo $offset+1 ?>;
                }
                else{
                        
                    swal({
                            title: "ERROR!",
                            text:  response.message,
                            icon: "danger",
                            timer: 5000,
                            buttons: false
                        });
                       
                }
            },
            });
        });
    });

</script>
<br>
<div style="padding: 0 30px 20px 30px;">
    <style>
      #tabella {
        width: 80%;
        margin: 30px auto;
        border-collapse: collapse;
      }
      #tabella thead {
        background-color: #1c87c9;
        color: #ffffff;
      }
      #tabella th,
      #tabella td {
        padding: 10px;
        border: 1px solid #666666;
      }
    </style>
        <table id="tabella"  cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Comune ID</th>
                    <th>Id Cronologico</th>
                    <th>Anno Cronologico</th>
                    <th>Utente ID</th>
                    <th class="persona"> Cognome</th>
                    <th class="persona"> Nome</th>
                    <th class="ditta"> Ditta</th>
                    <th>Modalita Notifica</th>
                    <th>Stato Notifica</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th><a href="<?= WEB_ROOT ?>/coattiva/ingiunzione.php?partita=<?php echo $result["Partita_ID_Link"]?>&c=<?php echo $CC ?>"><?php echo $result["Partita_ID"]?></a></th>
                    <th><?php echo $result["ID_Cronologico"]?></th>
                    <th><?php echo $result["Anno_Cronologico"]?></th>
                    <th><?php echo $result["Utente_ID"]?></th>
                    <th class="persona"> <?php echo $result["Cognome"]?></th>
                    <th class="persona"> <?php echo $result["Nome"]?></th>
                    <th class="ditta"> <?php echo $result["Ditta"]?></th>
                    <th><?php echo $result["Modalita_Notifica"]?></th>
                    <th>
                        <b>Stato Notifica</b><br>
                        <select id="StatoNotifica" name="StatoNotifica" class="select">
                            <option></option>
                            <?php echo $optionsStatoNotifica; ?>
                        </select>
                    </th>
                                        
                </tr>
            </tbody>
        </table>
</div>
<!-- bottoni -->
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

    .visible
    {
        display:inline-block;
    }

    .notvisible
    {
        display: none;
    }
</style>
<script>
   $(document).ready(function(){
        var offset = <?php echo $offset ?>;
        var contatore = <?php echo $contatore ?>;
        if(offset>0)
        {
            $("#Indietro").addClass( "visible" );
        }
        else
        {
            $("#Indietro").addClass( "notvisible" );
        }
        if(offset<contatore-1)
        {
            $("#Avanti").addClass( "visible" );
        }
        else
        {
            $("#Avanti").addClass( "notvisible" );
        }
        $('#Indietro').on('click', function (e) {
            window.location.href ="<?= WEB_ROOT ?>/coattiva/inserimento_manuale_giacenza.php?c=<?php echo $CC ?>&offset="+<?php echo $offset-1 ?>;
        });
        $('#Avanti').on('click', function (e) {
            window.location.href ="<?= WEB_ROOT ?>/coattiva/inserimento_manuale_giacenza.php?c=<?php echo $CC ?>&offset="+<?php echo $offset+1 ?>;
        });
        $('#txtoffset').keypress(function(event){
            var keycode = (event.keyCode ? event.keyCode : event.which);
            if(keycode == '13'){
               var valore = (event.target.value);
               if($.isNumeric(valore))
               {
                    var max = <?php echo $contatore+1 ?>;
                    if((valore>0) && (valore<max))
                    {
                        valore--;
                        window.location.href ="<?= WEB_ROOT ?>/coattiva/inserimento_manuale_giacenza.php?c=<?php echo $CC ?>&offset="+valore;
                    }
               }
            }
        });
   });
    

</script>
<div id="bottoni" class="bottoni">
    <button id="Indietro" name="Indietro"> Indietro</button>
    <button id="Avanti" name="Avanti" >Avanti</button>
</div>
<style>
    #Contatore{
        margin:auto;
        position:relative;
        width: max-content;
        padding-bottom: 25px;
    }

    #Contatore p{
        justify-content: center;
    }
</style>
<div id="Contatore">
      <p><strong> Rimanenti :<input type="text" style="text-align: right;" id="txtoffset" name="txtoffset" maxlength="4" size="4" value="<?php echo $offset+1 ?> "> / <?php echo $contatore ?></strong></p>
</div>
<!-- fine bottoni-->
<?php 

} 
else //cartella vuota
{

    echo $avviso("NON CI SONO GIACENZE NON ASSEGNATE");

} 

?>