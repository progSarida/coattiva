<?php

if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");

include(INC."/header.php");
include(INC."/menu.php");

if($_SESSION['username']==NULL)
{
    header("Location:/Gitco2/autenticazione/accesso_negato.php");
    die;
}

$c = $cls_help->getVar("c");
$a = $cls_help->getVar("a");

$form_type = $cls_help->getVar("form_type");
if($form_type==null)
    $form_type = 2;

$rs = new cls_db();
$row = $rs->getArrayLineNull($rs->SelectQuery("SELECT * FROM text_parameters where Form_Type_ID=".$form_type." AND CC = '$c'"),"text_parameters");
$cityText = $adminCityName;
$submit = "Update";
if($row['Form_Type_ID']==null){
    $row = $rs->getArrayLineNull($rs->SelectQuery("SELECT * FROM text_parameters where Form_Type_ID=".$form_type." AND CC = '*****'"),"text_parameters");
    $cls_help->alert("Non sono presenti testi salvati per questo comune! Modificare il testo e salvare i dati per creare un modello per questo ente.");
    $cityText = "Modello generico";
    $submit = "Insert";
}


$title = $row['Content'];
$title = str_replace("€", "&euro;", $title);

$a_textTypeRows = $rs->getResults($rs->SelectQuery("SELECT DT.*, IF(TP.Form_Type_ID IS NULL, 'black', 'green') AS Color FROM document_type AS DT LEFT JOIN text_parameters AS TP ON DT.Id = TP.Form_Type_ID AND TP.CC = '".$c."'  WHERE EnabledHtml=1 ORDER BY Description"));
$a_selection = array("value"=>"Id","firstOpt"=>0,"selected"=>$form_type,"text"=>array("[Description]"),"color"=>"Color");
//var_dump($a_textTypeRows);
$opt_textType = $cls_html->getOptions($a_textTypeRows, $a_selection);
$textTitle = "TESTO";
for($i=0;$i<count($a_textTypeRows);$i++){
    if($a_textTypeRows[$i]["Id"]==$form_type)
        $textTitle = $a_textTypeRows[$i]["Description"];
}

$a_varRow = $rs->getResults($rs->SelectQuery("SELECT V.*, GROUP_CONCAT(S.Type_Description SEPARATOR '*') AS Subtext_Descriptions FROM text_variables V LEFT JOIN subtext_parameters S ON V.Type=S.Variable AND S.CC='*****' AND V.Form_Type_ID=S.Form_Type_ID WHERE V.Form_Type_ID=".$form_type." GROUP BY V.Type"));

?>

<style>


    .keywordList{
        max-height: 155px;
        overflow-y: scroll;
        -webkit-overflow-scrolling: touch;
    }

</style>

<script>

    function pageReload(formType){
        openLocation(pageName,"form_type="+formType);
    }

    $(document).ready( function() {
        //spinner = new mySpinner("spinner_page","<?=AJAXWEB?>/session_progress.php");

        $('#keywordsList li').click(function() {
            $('#html_text').append($(this).text());
        })

        $('#form_type').val("<?=$form_type?>");

    });

    switchMenuImg("F3");
    F3_button = function(){
        //alert("ehi!");
        $('[name=textParameters_form]').submit();
    }

    switchMenuImg("F11");
    F11_button = function(){

        $("#frameHelp").attr("src","<?= SUPER_WEB_ROOT."/archivio/help/TestiDinamici.pdf"; ?>");
        $("#helpModalLabel").empty().append("<b>Help Testi Dinamici</b>");
        $("#helpModal").modal('show');

    }

</script>
<style>
    .table_label_H{background-color: #6397e2;text-align:center; height:3rem; line-height:2.8rem;font-size :1.5rem;font-weight:400;border-right:1px solid #E7E7E7; border-bottom:1px solid #E7E7E7;}
    .table_label_Subtext{background-color: #ff7b86;text-align:center; height:3rem; line-height:2.8rem;font-size :1.5rem;font-weight:400;border-right:1px solid #E7E7E7; border-bottom:1px solid #E7E7E7;}
    .table_label_small_H{background-color: #6397e2;text-align:center; height:3rem; line-height:2.8rem;font-size :1.2rem;font-weight:400;border-right:1px solid #E7E7E7; border-bottom:1px solid #E7E7E7;}
    .table_caption_H{background-color: rgba(132, 212, 251, 0.78);padding-left:0.5rem;line-height:2.2rem;height:2.2rem;font-size :1rem;font-weight:600;border-right:1px solid #E7E7E7; border-bottom:1px solid #E7E7E7;}
    .table_caption_Subtext{background-color: rgba(255, 124, 124, 0.32);padding-left:0.5rem;line-height:2.2rem;height:2.2rem;font-size :1rem;font-weight:600;border-right:1px solid #E7E7E7; border-bottom:1px solid #E7E7E7;}
</style>

    <form id="textParameters_form" name="textParameters_form" method="post" action="textParameters_exe.php" accept-charset="UTF-8" enctype='multipart/form-data'>

        <input type=hidden name=submitInfo value="<?php echo $submit; ?>" >
        <input type=hidden name=c value="<?php echo $c; ?>" >
        <input type=hidden name=a value="<?php echo $a; ?>" >

    <!--<div class="container">-->
        <div class="row" style="margin-top:50px;">
            <div class="col-md-10 col-md-offset-1 text_center">
                <span class="color_titolo font_bold font20">Parametri di testo</span>
            </div>
        </div>
        <hr>
        <div class="row" style="margin-top:20px;">
            <div class="col-md-2 col-md-offset-1 text_left">
                <span class="color_titolo font_bold font16">Tipo stampa</span>
            </div>
            <div class="col-md-4 text_left">
                <select name="form_type" id="form_type" onchange="pageReload(this.value);">
                    <?php echo $opt_textType; ?>

                </select>
            </div>
            <div class="col-md-5 text_left">
                <span class="color_titolo font_bold font16"><?=$cityText;?></span>
            </div>

        </div>
        <div class="row" style="margin-top:20px;">
            <div class="col-md-2 col-md-offset-1 text_left">
                <span class="color_titolo font_bold font16">Ultima modifica</span>
            </div>
            <div class="col-md-4 text_left">
                <span class="color_titolo font_bold font14">Operatore: </span>
                <span class="font14"><?=$row['User']?></span>
            </div>
            <div class="col-md-3 text_left">
                <span class="color_titolo font_bold font14">Data: </span>
                <span class="font14"><?=$cls_help->toItalianDate($row['Date'])?></span>
            </div>
        </div>
        <hr>
        <!--<div class="row" style="margin-top:20px;">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel-heading" style="margin-bottom:5px;text-align: center;"><span class="color_titolo font_bold font16">VARIABILI</span>

                    <div id="keywordsList" class="keywordList text_left">
                        <?php

                        for($i=0;$i<count($a_varRow);$i++){
                        $subtexts = "";
                        if(substr($a_varRow[$i]["Type"],0,2)=="{{"){
                            $keywordDescClass = "table_caption_Subtext";
                            $keywordClass= "table_label_Subtext";
                            if($a_varRow[$i]["Subtext_Descriptions"]!=""){
                                $a_subtexts = explode("*",$a_varRow[$i]["Subtext_Descriptions"]);
                                $subtexts = " ( ";
                                for($k=0;$k<count($a_subtexts);$k++){
                                    if($k>0)
                                        $subtexts.=" - ";
                                    $subtexts.=$a_subtexts[$k];
                                }
                                $subtexts.= " )";
                            }
                        }
                        else{
                            $keywordDescClass = "table_caption_H";
                            $keywordClass ="table_label_H";
                        }

                            ?>
                            <div class="col-sm-4 <?=$keywordClass;?>"><b><?=$a_varRow[$i]["Type"]?></b></div>
                            <div class="col-sm-8 <?=$keywordDescClass;?>" style="height:3rem; line-height:2.8rem;font-size :1.5rem;font-weight:400;"><?=$a_varRow[$i]["Description"];?><i><?=$subtexts?></i></div>
                            <div class="clean_row HSpace4"></div>
                            <?php

                        }
                        ?>
                    </div>

                </div>

            </div>
            <div class="col-md-10 col-md-offset-1">
                <div class="panel-heading" style="margin-bottom:5px;text-align: center;"><span class="color_titolo font_bold font16"><?=strtoupper($textTitle);?></span>
                    <textarea id="html_text" name="html_text" class="form-control"><?php  echo "";// $title; ?></textarea>
                </div>

            </div>
        </div>-->

        <div class="row" style="">
            <div class="col-md-10 col-md-offset-1">
                    <div style="display: inline-flex;width:100%;">
                    <div id="title"><?=strtoupper($textTitle);?></div>
                        <div id="preview">
                            <a onMouseover="title='Anteprima'" href="#" onClick="anteprima();" >
                                <img src="<?= IMMAGINIWEB; ?>/anteprima_b.png" width=40 height=40 border=0 >
                            </a>
                        </div>
                    </div>
                <div style="width:100%;background-color: #707070;display: flex;">
                    <div id="containerEdit">
                        <div id="divContainerEdit">
                            <textarea name="html_text" id="html_text"><?php  echo $title; ?></textarea>
                        </div>
                    </div>

                    <div data-state="close" id="containerVar">
                        <div style="width: 100%;">
                            <a onclick="resizeDiv();" title="Elenco variabili" class="btn btn-outline" data-toggle="collapse" href="#allVariable" role="button" aria-expanded="false" aria-controls="allVariable" style="color: whitesmoke;border: 1px solid whitesmoke; border-radius: 5px;margin-top: 1%;margin-right: 1%;"><i class="fa fa-bars fa-2x" aria-hidden="true"></i></a>
                            <div class="collapse multi-collapse" id="allVariable">
                                <div class="card card-body" style="background-color: #2681FF;">
                                    <div id="variable">
                                        <?php

                                        for($i=0;$i<count($a_varRow);$i++){
                                            $subtexts = "";
                                            if(substr($a_varRow[$i]["Type"],0,2)=="{{"){
                                                ?>
                                                <div style='display:flex;margin-right: 1%; margin-left: 1%;'>
                                                    <div class='dragend' style='float: left;text-align: center;border-bottom: 1px solid white;font-weight: bold;width:35%;font-size: 12px;color: #181BB8;' onclick='copy(this);' onmouseover='selectDivVar(this);'><?=$a_varRow[$i]["Type"]?></div>
                                                    <div style='float: left;text-align: center;border-bottom: 1px solid white;font-weight: bold;width:65%;font-size: 12px;'><?=$a_varRow[$i]["Description"];?></div>
                                                </div>
                                                <?php
                                            }
                                            else{
                                                ?>
                                                <div style='display:flex;margin-right: 1%; margin-left: 1%;'>
                                                    <div class='dragend' style='float: left;text-align: center;border-bottom: 1px solid white;font-weight: bold;width:35%;font-size: 12px;' onclick='copy(this);' onmouseover='selectDivVar(this);'><?=$a_varRow[$i]["Type"]?></div>
                                                    <div style='float: left;text-align: center;border-bottom: 1px solid white;font-weight: bold;width:65%;font-size: 12px;'><?=$a_varRow[$i]["Description"];?></div>
                                                </div>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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

    function anteprima(){
        //spinner.startSpinner();
        //alert("Anteprima");
        $.ajax({
            url: "ajax/anteprima_testo.php",
            data : {
                'c' : '<?=$c;?>',
                'type': $("#form_type").val(),
                'title': '<?=strtoupper($textTitle);?>'
            },
            dataType : 'json',
            type: 'POST',
            success: function (resp) {
                //spinner.closeSpinner();
                ShowAlert(resp.error,resp.msg);
                if(resp.error == 0)
                    showFileOnModal(resp.path,"Anteprima testo "+"<?=strtoupper($textTitle);?>",resp.path.split('.').pop());
            },
            error:function(resp)
            {
                //spinner.closeSpinner();
                //console.log(resp.responseText);
                ShowAlert(1,"Si è verificato un errore!");
            }
        });
    }

    function selectDivVar(element) {
        window.getSelection()
            .selectAllChildren(
                element
            );
    }


    function copy(element){
        CKEDITOR.instances.html_text.insertText(element.innerText);
    }

    function resizeDiv(){

        if($("#containerVar").attr("data-state") == "close"){
            $("#containerVar").css("width","40%");
            $("#containerEdit").css("width","60%");
            $("#containerVar").attr("data-state","open");
        }
        else{
            $("#containerVar").css("width","10%");
            $("#containerEdit").css("width","90%");
            $("#containerVar").attr("data-state","close");
        }
    }
</script>
    <style>

        .dragend:hover {
            background-color: #8C8C8C;
            border-radius: 6px;
        }

        #title {
            background-color: #3577F2;
            width:90%;
            height: 40px;
            border-bottom: 2px solid #2D64CC;
            text-align: center;
            font-weight: bold;
            color: white;
            justify-content: center;
            align-content: center;
            display: flex;
            flex-direction: column;
            /*display: inline-block;*/
            /*vertical-align: middle;*/
        }

        #preview{
            background-color: #3577F2;
            width:10%;
            height: 40px;
            border-bottom: 2px solid #2D64CC;
            text-align: center;
            font-weight: bold;
            color: white;
            justify-content: center;
            align-content: center;
            display: flex;
            flex-direction: column;
            /*display: inline-block;*/
        }

        #containerVar {
            width: 10%;
            float:right;
            /*margin-top: 1%;
            margin-bottom: 1%;*/
            /*height:960px;*/
            background-color: #3577F2;
            text-align: right;
        }

        #containerEdit {
            float:right;
            width:90%;
            background-color: #9CC8FF;
        }

        #variable {
            overflow-y: scroll;
            /*height: 960px;*/
            margin-bottom: 3%;
            background-color: #3577F2;
            color: white;
            width: 100%;
        }

        #divContainerEdit {
            position:relative;
            margin-left: auto;
            margin-right: auto;
            width:692px;
            margin-top: 1%;
            margin-bottom: 1%;
        }


    </style>

<?php

include(INC."/footer.php");

?>