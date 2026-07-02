<?php

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
    header("Location:/Gitco2/autenticazione/accesso_negato.php");
    die;
}

include_once($_SESSION['_path']);;
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");

$form_type = $cls_help->getVar("form_type");
if($form_type==null)
    $form_type = 2;

$subtext_variable = $cls_help->getVar("subtext_variable");
$subtext_id = $cls_help->getVar("subtext_id");
if($subtext_id==null)
    $subtext_id = 1;

$rs = new cls_db();

$a_textTypeRows = $rs->getResults($rs->SelectQuery("SELECT * FROM document_type WHERE EnabledHtml=1 ORDER BY Description"));
$a_selection = array("value"=>"Id","firstOpt"=>0,"selected"=>$form_type,"text"=>array("[Description]"));
$opt_textType = $cls_html->getOptions($a_textTypeRows, $a_selection);

$a_varRow = $rs->getResults($rs->SelectQuery("SELECT * FROM text_variables WHERE Form_Type_ID=".$form_type));
$subtextTitle = "SOTTOTESTO";
for($i=0;$i<count($a_varRow);$i++){
    if($a_varRow[$i]["Type"]==$subtext_variable)
        $subtextTitle = $a_varRow[$i]["Description"];
}

$a_variableResults = $rs->getResults($rs->SelectQuery("SELECT DISTINCT Variable FROM subtext_parameters where Form_Type_ID=".$form_type." AND CC = '*****' AND Disabled=0"));
if($subtext_variable==null)
    $subtext_variable = $a_variableResults[0]['Variable'];

$a_selection = array("value"=>"Variable","firstOpt"=>0,"selected"=>$subtext_variable,"text"=>array("[Variable]"));
$opt_variables = $cls_html->getOptions($a_variableResults, $a_selection);

$query = "SELECT * FROM subtext_parameters where Form_Type_ID=".$form_type." AND CC = '*****' AND Variable='".$subtext_variable."' AND Disabled=0";
$a_rowsModel = $rs->getResults($rs->SelectQuery($query));
$a_selection = array("value"=>"Type_ID","firstOpt"=>0,"selected"=>$subtext_id,"text"=>array("[Type_Description]"));
$opt_subtext = $cls_html->getOptions($a_rowsModel, $a_selection);
$subtextType = "";
$rowModel = null;
for($i=0;$i<count($a_rowsModel);$i++){
    if($a_rowsModel[$i]["Variable"]==$subtext_variable && $a_rowsModel[$i]["Type_ID"]==$subtext_id){
        $rowModel = $a_rowsModel[$i];
        $subtextType = $rowModel["Type_Description"];
    }

}
$query = "SELECT * FROM subtext_parameters where Form_Type_ID=".$form_type." AND CC = '$c' AND Variable='".$subtext_variable."' AND Type_ID=".$subtext_id." AND Disabled=0";
$row = $rs->getArrayLine($rs->SelectQuery($query));
$cityText = $adminCityName;
$submit = "Update";


if(!isset($row['Form_Type_ID'])){
    if(isset($rowModel)){
        $row = $rowModel;
        $cls_help->alert("Non sono presenti dati salvati per il sottotesto di questa variabile su questo ente! Modificare il testo e salvare i dati.");
        $cityText = "Modello generico";
        $submit = "Insert";
    }
    else{
        $cls_help->alert("Non sono presenti sottotesti per questo tipo di stampa! Contattare l'amministratore.");
    }
}

if(isset($row['Content'])){
    $title = $row['Content'];
    $title = str_replace("€", "&euro;", $title);
}
else
    $title = "";

?>

    <style>
        .keywordList{
            max-height: 155px;
            overflow-y: scroll;
            -webkit-overflow-scrolling: touch;
        }

        .table_label_H{background-color: #6397e2;text-align:center; height:3rem; line-height:2.8rem;font-size :1.5rem;font-weight:400;border-right:1px solid #E7E7E7; border-bottom:1px solid #E7E7E7;}
        .table_label_Subtext{background-color: #ff7b86;text-align:center; height:3rem; line-height:2.8rem;font-size :1.5rem;font-weight:400;border-right:1px solid #E7E7E7; border-bottom:1px solid #E7E7E7;}
        .table_label_small_H{background-color: #6397e2;text-align:center; height:3rem; line-height:2.8rem;font-size :1.2rem;font-weight:400;border-right:1px solid #E7E7E7; border-bottom:1px solid #E7E7E7;}
        .table_caption_H{background-color: rgba(132, 212, 251, 0.78);padding-left:0.5rem;line-height:2.2rem;height:2.2rem;font-size :1rem;font-weight:600;border-right:1px solid #E7E7E7; border-bottom:1px solid #E7E7E7;}
        .table_caption_Subtext{background-color: rgba(255, 124, 124, 0.32);padding-left:0.5rem;line-height:2.2rem;height:2.2rem;font-size :1rem;font-weight:600;border-right:1px solid #E7E7E7; border-bottom:1px solid #E7E7E7;}

    </style>
<script>


    function pageReload(check){
        get = "";
        if($('#form_type').val()!=null)
            get+= "form_type="+$('#form_type').val();
        if($('#subtext_variable').val()!=null && check!==true)
            get+= "&subtext_variable="+$('#subtext_variable').val();
        if($('#subtext_id').val()!=null)
            get+= "&subtext_id="+$('#subtext_id').val();

        openLocation(pageName,get);
    }

    $(document).ready( function() {

        $('#keywordsList li').click(function() {
            $('#html_text').append($(this).text());
        })

        $('#form_type').val("<?=$form_type?>");

        switchMenuImg("F3");
        F3_button = function(){
            //alert("ehi!");
            $('[name=subtextParameters_form]').submit();
        }

    });

</script>

    <form id="subtextParameters_form" name="subtextParameters_form" method="post" action="subtextParameters_exe.php" accept-charset="UTF-8" enctype='multipart/form-data'>

        <input type=hidden name=submitInfo value="<?php echo $submit; ?>" >
        <input type=hidden name=c value="<?php echo $c; ?>" >
        <input type=hidden name=a value="<?php echo $a; ?>" >
        <?php
            if($opt_subtext == "")
                echo "<input type=hidden name=subtext_id value='".$subtext_id."'>";
        ?>

    <!--<div class="container">-->
        <div class="row" style="margin-top:50px;">
            <div class="col-md-10 col-md-offset-1 text_center">
                <span class="color_titolo font_bold font20">Parametri variabili</span>
            </div>
        </div>
        <hr>
        <div class="row" style="margin-top:20px;">
            <div class="col-md-2 col-md-offset-1 text_left">
                <span class="color_titolo font_bold font16">Tipo stampa</span>
            </div>
            <div class="col-md-3 text_left">
                <select name="form_type" id="form_type" class="width90" onchange="pageReload(true);">
                    <?php echo $opt_textType;?>
                </select>
            </div>
            <div class="col-md-2 text_left">
                <span class="color_titolo font_bold font16">Variabile</span>
            </div>
            <div class="col-md-3 text_left">
                <select name="subtext_variable" id="subtext_variable" class="width60" onchange="pageReload();">
                    <?=$opt_variables;?>
                </select>
            </div>

        </div>
        <div class="row" style="margin-top:20px;">
            <div class="col-md-2 col-md-offset-1 text_left">
                <span class="color_titolo font_bold font16">Sottotesto</span>
            </div>
            <div class="col-md-4 text_left">
                <select name="subtext_id" id="subtext_id" class="width90" onchange="pageReload();">
                    <?=$opt_subtext;?>
                </select>
            </div>
            <div class="col-md-4 text_left">
                <span class="color_titolo font_bold font16"><?=$cityText;?></span>
            </div>

        </div>
        <div class="row" style="margin-top:20px;">
            <div class="col-md-2 col-md-offset-1 text_left">
                <span class="color_titolo font_bold font16">Ultima modifica</span>
            </div>
            <div class="col-md-3 text_left">
                <span class="color_titolo font_bold font14">Operatore: </span>
                <span class="font14"><?php if(isset($row['User'])) echo $row['User']; ?></span>
            </div>
            <div class="col-md-3 text_left">
                <span class="color_titolo font_bold font14">Data: </span>
                <span class="font14"><?php if(isset($row['Date'])) echo $cls_help->toItalianDate($row['Date'])?></span>
            </div>
        </div>
        <hr>
        <div class="row" style="margin-top:20px;">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel-heading" style="margin-bottom:5px;text-align: center;"><span class="color_titolo font_bold font16">KEYWORDS</span>

                    <div id="keywordsList" class="keywordList text_left">
                        <?php

                        for($i=0;$i<count($a_varRow);$i++){
                            if(substr($a_varRow[$i]["Type"],0,2)!="{{") {
                                ?>
                                <div class="col-sm-4 table_label_H"><b><?= $a_varRow[$i]["Type"] ?></b></div>
                                <div class="col-sm-8 table_caption_H"
                                     style="height:3rem; line-height:2.8rem;font-size :1.5rem;font-weight:400;"><?= $a_varRow[$i]["Description"] ?></div>
                                <div class="clean_row HSpace4"></div>
                                <?php
                            }
                        }
                        ?>
                    </div>

                </div>

            </div>
            <div class="col-md-10 col-md-offset-1">
                <div class="panel-heading" style="margin-bottom:5px;text-align: center;"><span class="color_titolo font_bold font16"><?=strtoupper($subtextTitle)."<br>".$subtextType;?></span>
                    <textarea id="html_text" name="html_text" class="form-control"><?php  echo $title; ?></textarea>
                </div>

            </div>
        </div>
    <!--</div>-->
    </form>



<script>
    var edit = CKEDITOR.replace('html_text', {
        customConfig: '',
        filebrowserBrowseUrl: './ckfinder/ckfinder.html',
        filebrowserImageBrowseUrl: './ckfinder/ckfinder.html?type=Images',
        disallowedContent: 'img{width,height,float}',
        extraAllowedContent: 'img[width,height,align];span{background}',
        extraPlugins: 'colorbutton,font,justify,print,tableresize,uploadimage,uploadfile,pastefromword,liststyle',
        height: 600,
        contentsCss: [
            'http://cdn.ckeditor.com/4.11.3/full-all/contents.css',
            'assets/css/pastefromword.css'
        ],
    });

    edit.config.allowedContent = true;
    edit.config.removePlugins = 'Source';
    edit.execCommand( 'shiftEnter' );

</script>

<?php

include(INC."/footer.php");

?>