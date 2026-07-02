<?php

include_once CLS . "/cls_ruolo.php";

$partita_ID = $cls_help->getVar('partita');
//$cls_help->alert($partita_ID);

$partita_Menu = $cls_db->getObjectLine( $cls_db->SelectQuery("SELECT * FROM partita_tributi WHERE ID = '".$partita_ID."' AND CC = '".$c."'") );

if($a=="" && $partita_ID>0){
?>
    <script>
        strLink = thisPage+"?";
        strLink += "c=" + adminCity;
        strLink += "&a=<?=$partita_Menu->Anno_Riferimento;?>&partita=<?=$partita_ID;?>";
        strLink += "&pageCalled=<?= $cls_help->getVar('pageCalled'); ?>";
        location.href = strLink;
    </script>
<?php
}


if($partita_Menu!=null){
    $utente = $cls_db->getObjectLine( $cls_db->SelectQuery("SELECT * FROM utente WHERE ID = '".$partita_Menu->Utente_ID."' AND CC_Comune = '".$c."'") );
    if($utente->Genere!='D')
        $denominazioneUtente = $utente->Cognome." ".$utente->Nome;
    else
        $denominazioneUtente = $utente->Ditta;
    $p = $utente->ID;
    //echo $p;
    $comune_utente_id = $utente->Comune_ID;
}
else{
    $utente = null;
    $denominazioneUtente = "";
    $comune_utente_id = "";
}

$cls_ruolo = new cls_ruolo();
$prevPage = $cls_db->getObjectLine( $cls_db->SelectQuery( $cls_ruolo->getPrevQueryItemID($partita_ID, $c, $a) ) );
$nextPage = $cls_db->getObjectLine( $cls_db->SelectQuery( $cls_ruolo->getNextQueryItemID($partita_ID, $c, $a) ) );
if($prevPage!="")
    $prevPage = $prevPage->ID;
if($nextPage!="")
    $nextPage = $nextPage->ID;

//$cls_help->alert($submenuPageNo);
?>
<script>
    var prevID = "<?php echo $prevPage; ?>";
    var partitaID = "<?php echo $partita_ID; ?>";
    var nextID = "<?php echo $nextPage; ?>";
    var pageNo = parseInt("<?php echo $submenuPageNo; ?>");
    var taxPayer = "<?php echo $p; ?>";
    var pageCalling = "";

    if(pageNo=="")
        alert("ERRORE! Numero pagina del sottomenu non impostata!");

    function openRuolo(){
        openLocation('gestione_ruolo',"p="+taxPayer);
    }

    function openSubLink(pageName){
        var ultimo_atto = $("#ultimoAtto").val();
        var calling_page = $("#nomePagina").val();

        if(pageCalling != ""){
            if(ultimo_atto == undefined ) openLocation(pageName, "partita="+partitaID+"&p="+taxPayer+"&pageCalled="+pageCalling);
            else openLocation(pageName, "calling_page="+calling_page+"&last_act="+ultimo_atto+"&partita="+partitaID+"&p="+taxPayer+"&pageCalled="+pageCalling);
        }
        else{
            if(ultimo_atto == undefined ) openLocation(pageName, "partita="+partitaID+"&p="+taxPayer);
            else openLocation(pageName, "calling_page="+calling_page+"&last_act="+ultimo_atto+"&partita="+partitaID+"&p="+taxPayer);
        }


    }


    function callParentSubMenu(value) {

        if(value!=null){
            if(typeof value !== 'object')
                top.location.href="<?= WEB_ROOT; ?>/coattiva/gestione_ruolo.php?mode=consulta&p="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
            else
                top.location.href="<?= WEB_ROOT; ?>/coattiva/"+pageName+".php?mode=consulta&partita="+value.ID+"&c=<?php echo $c; ?>&a="+value.Anno;
        }
    }

    function getSubmenuPagename(number){
        switch(number){
            case 1: pagename = "gestione_partita"; break;
            case 2: pagename = "ingiunzione"; break;
            case 3: pagename = "pagamento"; break;
            case 4: pagename = "scorporo_pagamento"; break;
            case 5: pagename = "appeal_list"; break;
            // case 6: pagename = "informativaBanche"; break;
            case 6: pagename = "coazione"; break;
            case 7: pagename = "pagamento_pignoramento"; break;
            case 8: pagename = "ricorso_pignoramento"; break;
            case 9: pagename = "annulamento_sgravi"; pageCalling = "sgravi"; break;
            case 10: pagename = "annulamento_sgravi"; pageCalling = "annullamento"; break;
            case 11: pagename = "annulamento_sgravi"; pageCalling = "sgravi_1"; break;
            case 12: pagename = "annulamento_sgravi"; pageCalling = "annullamento_1"; break;

        }
        return pagename;
    }

    //F7
    switchMenuImg("F7");
    F7_button = function(){
        if( checkModify() )
            openLocation(pageName, "partita="+prevID+"&p="+taxPayer+"&a=<?php echo $a; ?>&pageCalled=<?= $cls_help->getVar('pageCalled'); ?>");
    }

    //F8
    switchMenuImg("F8");
    F8_button = function(){
        if( checkModify() )
            openLocation(pageName, "partita="+nextID+"&p="+taxPayer+"&a=<?php echo $a; ?>&pageCalled=<?= $cls_help->getVar('pageCalled'); ?>");
    }

    F9_button = function(){
        var stringa = "<?= WEB_ROOT; ?>/search/coattiva/search_sub_menu.php?richiesta=generale&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
        var dim = new Array();
        dim['width'] = 700;
        dim['height'] = 500;
        dim['top'] = 100;
        dim['left'] = 100;

        openNewWindow(stringa,dim);
    }

    //F7
    switchMenuImg("pagedown");
    pagedown_button = function(){
        if(pageNo>1)
            page = getSubmenuPagename((pageNo-1));
        else
            page = getSubmenuPagename(6);

        if( checkModify() )
            openLocation(page, "partita="+partitaID+"&p="+taxPayer);
    }

    //F8
    switchMenuImg("pageup");
    pageup_button = function(){
        if(pageNo<9)
            page = getSubmenuPagename((pageNo+1));
        else
            page = getSubmenuPagename(1);

        if( checkModify() )
            openLocation(page, "partita="+partitaID+"&p="+taxPayer);
    }

    function setLinkMenu(){
        if((pageNo>0 && pageNo<6) || (pageNo > 8 && pageNo < 11))
            $('#iterIngiuntivo').show();
        else if((pageNo>5 && pageNo < 9) || (pageNo > 10 && pageNo < 13))
            $('#iterCoattivo').show();
//alert("setLinkMenu "+pageNo);
        $('#spanPage'+pageNo).removeClass("titolo").addClass("titoletto");
    }

    function processJson(a_backValue) {
        // 'data' is the json object returned from the server
        //alert(thisPage);
        if (a_backValue['response'] === true) {
            top.location.href = thisPage+"?partita=" + a_backValue['ID'] + "&c="+ a_backValue['c'] +"&a=" + a_backValue['a']+"&pageCalled=<?= $cls_help->getVar('pageCalled'); ?>";
        }
        else{
            alert('Codice partita non trovato!');
            openLocation(pageName,"partita="+partitaID+"&pageCalled=<?= $cls_help->getVar('pageCalled'); ?>");
        }
    }

    $(document).ready(function() {

        $("#search_partitaID").attr("action","<?=WEB_ROOT?>/ajax/ajaxSearch.php");

        var options = {
            dataType:  'json',
            success:   processJson
        };

        $('#search_partitaID').ajaxForm(options);

    });
</script>

<table class="table_interna text_center" border=0 style="border:2px solid #6D95D5;">
    <tr>
        <td class="text_center width8">
            <a onMouseover="title='Cerca utente/partita'" href="#" onClick="F9_button();" style="text-decoration: none;">
                <img src="<?php echo IMG; ?>/F9_on.png" width=47 height=47 border=0>
            </a>
        </td>
        <td class="text_center width15"><span class="titolo font18">PARTITA</span><span class="titolo font14"><br> Pag <?php echo $submenuPageNo; ?>/12</span></td>

        <!--<td class="text_center width15">
            <?php if($submenuPageNo==1||$submenuPageNo==2||$submenuPageNo==9||($submenuPageNo==6 && $cls_help->getVar("pignoramento") == null)) {
                ?>

            <?php } ?>
        </td>-->
        <td class="text_center width35"><em style="background-color:rgb(251,255,208);font-style : normal ;"><?php echo $denominazioneUtente." (Utente ID: ".$comune_utente_id.")"; ?></em></td>
        <td class="text_left">
            <a onMouseover="title='Gestione Ruolo'" href="#" style="text-decoration:none;display: inline;" onClick="openRuolo();" >
                <img src="<?php echo IMG; ?>/select_arrow.png" style="width:25px; height:25px; border:0;" >
            </a>
            <?php if(isset($pageCalled)) echo $pageCalled;?>
        </td>
        <td class="text_center width18">
            <form id=search_partitaID method=post action="">
                <input name=c type=hidden value='<?php echo $c; ?>'>
                <input type=hidden name="searchType" value="partitaID">

                <span class="font15 titolo valign_center">Partita ID  </span>

                <input id=id_cerca tabindex=1 class="valign_center text_right" type=text name=partitaID value='<?php if($partita_Menu!=null) echo $partita_Menu->Comune_ID; ?>'
                       size=4 onMouseover="title='Inserire il codice utente e premere Invio'">
            </form>
        </td>
    </tr>
</table>

<table class="table_interna text_center" border="0" cellspacing="5" cellpadding="0" style="border:2px solid #6D95D5;">
    <tr id="iterIngiuntivo" style="display:none;">
        <td class="width12">
            <a href="#" onclick="openSubLink(getSubmenuPagename(1));" style="text-decoration: none;">
                <span id="spanPage1" class="titolo font15">Codici tributo</span>
            </a>
        </td>
        <td class="width13">
            <a href="#" onclick="openSubLink(getSubmenuPagename(2));" style="text-decoration: none;">
                <span id="spanPage2" class="titolo font15">Ingiunzioni</span>
            </a>
        </td>
        <td class="width12">
            <a href="#" onclick="openSubLink(getSubmenuPagename(9));" style="text-decoration: none;">
                <span id="spanPage9" class="titolo font15">Sgravi</span>
            </a>
        </td>
        <td class="width13">
            <a href="#" onclick="openSubLink(getSubmenuPagename(10));" style="text-decoration: none;">
                <span id="spanPage10" class="titolo font15">Annullamento</span>
            </a>
        </td>
        <td class="width13">
            <a href="#" onclick="openSubLink(getSubmenuPagename(3));" style="text-decoration: none;">
                <span id="spanPage3" class="titolo font15">Pagamenti</span>
            </a>
        </td>
        <td class="width12">
            <a href="#" onclick="openSubLink(getSubmenuPagename(4));" style="text-decoration: none;">
                <span id="spanPage4" class="titolo font15">Scorpori</span>
            </a>
        </td>
        <td class="width12">
            <a href="#" onclick="openSubLink(getSubmenuPagename(5));" style="text-decoration: none;">
                <span id="spanPage5" class="titolo font15">Ricorsi</span>
            </a>
        </td>
        <td class="width13">
            <a href="#" onclick="openSubLink(getSubmenuPagename(6));" style="text-decoration: none;">
                <span class="titolo font14"><i>Iter coattivo<i></span>
                <img alt="" src="<?= IMMAGINIWEB; ?>/forward.png" style="width:12px; height:12px; border:0;">
            </a>
        </td>
    </tr>
    <tr id="iterCoattivo" style="display:none;">
        <td class="width16">
            <a href="#" onclick="openSubLink(getSubmenuPagename(1));" style="text-decoration: none;">
                <img alt="" src="<?= IMMAGINIWEB; ?>/rewind.png" style="width:12px; height:12px; border:0;">
                <span class="titolo font14"><i>Iter ingiuntivo</i></span>
            </a>
        </td>
<!--        <td class="width34" colspan="2">-->
<!--            <a href="#" onclick="openSubLink(getSubmenuPagename(6));" style="text-decoration: none;">-->
<!--                <span id="spanPage6" class="titolo font15">Informativa Banche</span>-->
<!--            </a>-->
<!--        </td>-->
        <td class="width17">
            <a href="#" onclick="openSubLink(getSubmenuPagename(6));" style="text-decoration: none;">
                <span id="spanPage6" class="titolo font15">Pignoramento</span>
            </a>
        </td>
        <td class="width17">
            <a href="#" onclick="openSubLink(getSubmenuPagename(11));" style="text-decoration: none;">
                <span id="spanPage11" class="titolo font15">Sgravi</span>
            </a>
        </td>
        <td class="width17">
            <a href="#" onclick="openSubLink(getSubmenuPagename(12));" style="text-decoration: none;">
                <span id="spanPage12" class="titolo font15">Annullamento</span>
            </a>
        </td>
        <td class="width17">
            <a href="#" onclick="openSubLink(getSubmenuPagename(7));" style="text-decoration: none;">
                <span id="spanPage7" class="titolo font15">Pagamenti</span>
            </a>
        </td>
        <td class="width16">
            <a href="#" onclick="openSubLink(getSubmenuPagename(8));" style="text-decoration: none;">
                <span id="spanPage8" class="titolo font15">Ricorsi</span>
            </a>
        </td>
    </tr>
</table>
<script>setLinkMenu();</script>
