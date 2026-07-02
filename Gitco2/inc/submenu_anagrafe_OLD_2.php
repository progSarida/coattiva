<script src="<?= JS ?>/CF/CodiceFiscale.js" type="text/javascript"></script>
<script>
    var pageNo = parseInt("<?php echo $submenuPageNo; ?>");
    function setLinkMenu(){
        $('#spanPage'+pageNo).removeClass("titolo").addClass("titoletto");
    }
</script>
<table align=center class=table_interna border=0 style="border:3px solid #6D95D5;">
	<tr>
		<td width=8% class="text_center">
			<a onMouseover="title='Cerca utente'" href="#" onclick="RicercheDaId('utente',0);" style="text-decoration: none;">
			<img src="<?= IMMAGINIWEB; ?>/User Folder.png" width=47 height=47 border=0>
			</a>
		</td>
		<td align=center width=7%>
		<?php if($mode=="consulta"){ if($cls_help->getVar("p")!=0) {?>

			<img src="<?= IMMAGINIWEB; ?>/semaforoRosso.png" width=50 height=50 border=0>

		<?php } else {?>

			<img src="<?= IMMAGINIWEB; ?>/semaforoSpento.png" width=50 height=50 border=0>

		<?php } }
		else if($mode=="modifica"){ if($cls_help->getVar("p")!=0){?>

			<img src="<?= IMMAGINIWEB; ?>/semaforoVerde.png" width=50 height=50 border=0>

		<?php } else {?>

			<img src="<?= IMMAGINIWEB; ?>/semaforoGiallo.png" width=50 height=50 border=0>

		<?php } }?>
		</td>
		<td width=15% class="text_center"><font class="titolo font18">ANAGRAFE</font><font class="titolo font14"> <?php echo $menuPageNumber; ?></font></td>
    	<td width=20% class="text_left">
            <em style="font-style : normal ;">
            <?php if($genere_utente!='D'){echo $cognome_utente." ".$nome_utente;}else{ echo $ditta; } ?></em>
        </td>
        <td width=10% class="text_center">
        	<font class="color_titolo font16">Ordinamento</font>
        	<select id=ordinamento name=ordinamento onchange="ordinamento();"><option value=ID>ID utente</option><option value=Nome>Alfabetico</option></select>
        </td>
        <td class="text_left width19">
            <a onMouseover="title='Gestione Ruolo'" href="#" style="text-decoration:none;display: inline;" onclick="ruolo('<?php echo $p; ?>');" >
                <img src="<?= IMMAGINIWEB; ?>/select.png" style="width:25px; height:25px; border:0;" >
            </a>
            <?php if(isset($pageCalled)) echo $pageCalled; ?></td>
        <td width=18% align=right>
		<form id=cerca_id method=post action="">
			<input type=hidden name=old_cod_contr value='<?php echo $comune_id; ?>'>
           	<input name=c type=hidden value='<?php echo $cls_help->getVar("c"); ?>'>
            <input name=a type=hidden value='<?php echo $cls_help->getVar("a"); ?>'>
		Utente ID &nbsp;
		<input id=id_cerca tabindex=1 class="valign_center text_right" type=text name=ric_cod_contr value='<?php echo $comune_id; ?>' size=3 onMouseover="title='Inserire il codice utente e premere Invio'">&nbsp;&nbsp;
        </form>

		</td>
</tr>
</table>

<table align=center class=table_interna border=0 style="border-right:3px solid #6D95D5;border-left:3px solid #6D95D5;border-bottom:3px solid #6D95D5;margin-bottom: 2%;">
    <tr>
        <td style="text-align: center; " class="width12">
            <a href="<?= WEB_ROOT; ?>/anagrafe/dati_soggetto.php?p=<?= $p; ?>&c=<?= $c; ?>&a=<?= $a; ?>" style="cursor: pointer;text-decoration: none;">
                <span id="spanPage1" class="titolo font15">Dati soggetto</span>
            </a>
        </td>
        <td style="text-align: center; " class="width12">
            <a href="<?= WEB_ROOT; ?>/anagrafe/annotazioni.php?p=<?= $p; ?>&c=<?= $c; ?>&a=<?= $a; ?>" style="cursor: pointer;text-decoration: none;">
                <span id="spanPage2" class="titolo font15">Annotazioni</span>
            </a>
        </td>
        <td style="text-align: center; " class="width12">
            <a href="<?= WEB_ROOT; ?>/anagrafe/recapito.php?p=<?= $p; ?>&c=<?= $c; ?>&a=<?= $a; ?>" style="cursor: pointer;text-decoration: none;">
                <span id="spanPage3" class="titolo font15">Recapito</span>
            </a>
        </td>
        <td style="text-align: center; " class="width12">
            <a href="<?= WEB_ROOT; ?>/anagrafe/domicilio.php?p=<?= $p; ?>&c=<?= $c; ?>&a=<?= $a; ?>" style="cursor: pointer;text-decoration: none;">
                <span id="spanPage4" class="titolo font15">Domicilio</span>
            </a>
        </td>
        <!--<td style="text-align: center; " class="width12">
            <a href="<?= WEB_ROOT; ?>/anagrafe/dettagli.php?p=<?= $p; ?>&c=<?= $c; ?>&a=<?= $a; ?>" style="cursor: pointer;text-decoration: none;">
                <span id="spanPage5" class="titolo font15">Dettagli</span>
            </a>
        </td>-->
        <td style="text-align: center; " class="width12">
            <a href="<?= WEB_ROOT; ?>/anagrafe/cambia_residenza.php?p=<?= $p; ?>&c=<?= $c; ?>&a=<?= $a; ?>" style="cursor: pointer;text-decoration: none;">
                <span id="spanPage6" class="titolo font15">Storico residenza</span>
            </a>
        </td>
        <td style="text-align: center; " class="width12">
            <a href="<?= WEB_ROOT; ?>/anagrafe/Veicoli.php?p=<?= $p; ?>&c=<?= $c; ?>&a=<?= $a; ?>" style="cursor: pointer;text-decoration: none;">
                <span id="spanPage7" class="titolo font15">Veicoli</span>
            </a>
        </td>
    </tr>
</table>
<script>setLinkMenu();</script>
<script type="text/javascript">

    $( document ).ready(function() {
        $(document).on('submit','#cerca_id',function(){

            $.ajax({
                type: "POST",
                url: "modali/ricerca_codice_result.php",
                data: $( "#cerca_id" ).serialize(),
                success: function (data){
                    //alert(data);
                    top.location.href="<?= WEB_ROOT; ?>/anagrafe/<?= $pagina; ?>?mode=consulta&p="+data+"&c=<?= $c; ?>&a=<?php echo $a; ?>";
                }
                //,dataType: dataType
            });
        });
    });

    function ruolo (value)
    {
        if(value != 0)
            location.href="<?= WEB_ROOT; ?>/coattiva/gestione_ruolo.php?p="+value+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
        else
            alert('Nessun utente è stato selezionato!');
    }

    function callParent(valorediritorno) {
//alert(selectParent);
        switch(selectParent){

            case "utente":

                //alert(valorediritorno);
                if(valorediritorno!=null && valorediritorno!=undefined)
                    top.location.href="<?= WEB_ROOT; ?>/anagrafe/<?= $pagina; ?>?mode=consulta&p="+valorediritorno.p+"&c="+valorediritorno.c+"&a=<?php echo $a; ?>";

                break;

            case "stato":
                if(valorediritorno!=null && valorediritorno!=undefined) {

                    if (selectRif == 0) {

                        paese_ritorno = valorediritorno.paese;
                        $('#paese_nascita').val(paese_ritorno);
												//alert("parent "+$('#paese_nascita').val());
												document.getElementById("paese_nascita").dispatchEvent(new Event("change"));
                        if (paese_ritorno != "Italia") {
                            $('#comune_nascita').val(null);
                            $('#comune_nascita').removeClass('sfondo_ricerca').addClass('sfondo_bianco');
                            $('#comune_nascita').attr('readonly', false);
                            $('#dati_sogg_prov_nasc').val(null);
                            $('#dati_sogg_prov_nasc').attr('disabled', 'disabled');
                            $('#CC_nascita').val(valorediritorno.CC);
                            $('.provincia_dati_sogg').hide();
                        }
                        else {
                            $('#comune_nascita').val(null);
                            $('#comune_nascita').attr('disabled', false);
                            $('#comune_nascita').attr('readonly', 'readonly');
                            $('#comune_nascita').addClass('sfondo_ricerca').removeClass('sfondo_bianco');
                            $('#dati_sogg_prov_nasc').attr('disabled', false);
                            $('.provincia_dati_sogg').show();
                        }
                    }
                    else if (selectRif == 2) {
                        paese_ritorno = valorediritorno.paese;
                        $('#paese_cf').val(paese_ritorno);

												document.getElementById("paese_cf").dispatchEvent(new Event("change"));

                        if (paese_ritorno != "Italia") {
                            $('#comune_cf').val(null);
                            $('#comune_cf').removeClass('sfondo_ricerca').addClass('sfondo_grigio');
                            $('#comune_cf').attr('readonly', false);
                            $('#CC_cf').val(valorediritorno.CC);
                        }
                        else {
                            $('#comune_cf').val(null);
                            $('#comune_cf').attr('disabled', false);
                            $('#comune_cf').attr('readonly', 'readonly');
                            $('#comune_cf').addClass('sfondo_ricerca').removeClass('sfondo_grigio');
                        }
                    }
                    else {
                        paese_ritorno = valorediritorno.paese;
                        $('#paese').val(paese_ritorno);

												document.getElementById("paese").dispatchEvent(new Event("change"));

                        if (paese_ritorno != "Italia") {
                            $('#civico').val(null);
                            $('#esponente').val(null);
                            $('#interno').val(null);
                            $('#dettagli').val(null);

                            $('#ID_via_cap').val(1);
                            $('#ID_via').val(0);

                            $("#comune").removeClass( "validateCustom vld_Custom_r");
                            $("#cap").removeClass( "validateCustom vld_Custom_r");

                            $('#scelta_indirizzo_1').hide();
                            $('#scelta_indirizzo_2').show();
														$("#via").removeClass("validateCustom vld_Custom_r");
														$("#via_estero").addClass("validateCustom vld_Custom_r");

                            $('#CC').val(valorediritorno.CC);

														$('#comune').val(null);
                            $('#comune').removeClass('sfondo_ricerca').addClass('sfondo_bianco');
                            $('#comune').attr('readonly', false);
														$('#comune').css("background-color","");
														$('#comune').css("border","");
														/*$('#comune').addClass("validateCustom vld_Custom_r");

														var arrayClass = document.getElementById("comune").className.split(/\s+/);
														var allClass = "";
									          for(var x = 0; x<arrayClass.length; x++)
									          {
															allClass = allClass + " " + arrayClass[x];
									          }
														alert(allClass);*/

														$('#dati_sogg_prov').val(null);
                            $('#dati_sogg_prov').attr('readonly', false);
                            $('#frazione').val(null);
                            $('#cap').val(null);
                            $('#cap').attr('readonly', false);
                            $('#via').val(null);
                            $('#via').attr('ondblclick', "RicercheDaId('via',0);");
                            $('#via').addClass('sfondo_ricerca').removeClass('sfondo_rosso sfondo_bianco');
                            $('#via').attr('readonly', 'readonly');
                            //func_stato_estero_indirizzo('nascondi');

                        }
                        else {
                            $("#comune").addClass("validateCustom vld_Custom_r");
                            $("#cap").addClass("validateCustom vld_Custom_r");
                            $('#scelta_indirizzo_2').hide();
                            $('#scelta_indirizzo_1').show();
														$("#via_estero").removeClass("validateCustom vld_Custom_r");
														$("#via").addClass("validateCustom vld_Custom_r");

                            $('#comune').addClass('sfondo_ricerca').removeClass('sfondo_bianco');
                            $('#comune').val(null);
                            $('#comune').attr('readonly', 'readonly');
														$('#comune').css("background-color","rgb(153, 204, 255)");
														$('#comune').css("border","2px solid black");
														//$('#comune')

														$('#frazione').val(null);
                            $('#dati_sogg_prov').val(null);
                            $('#dati_sogg_prov').attr('disabled', false);
                            $('#cap').attr('readonly', 'readonly');
                            $('#cap').val(null);
                            $('#via').attr('ondblclick', "");
                            $('#via').val(null);
                            $('#via').attr('readonly', 'readonly');
                            $('#via').addClass('sfondo_ricerca').removeClass('sfondo_rosso sfondo_bianco');
                            $('#civico').val(null);
                            $('#esponente').val(null);
                            $('#interno').val(null);
                            $('#dettagli').val(null);
                            //func_stato_estero_indirizzo('mostra');
                        }
                    }
                }
                break;

            case "ente":
						//alert("menu ente");
                if (selectRif == 0) {

                    if (valorediritorno != null && valorediritorno != undefined) {
                        $('#comune_nascita').val(valorediritorno.comune);
                        $('#dati_sogg_prov_nasc').val(valorediritorno.prov_sigla);
                        $('#CC_nascita').val(valorediritorno.CC);
												//alert("1 ente call");
												document.getElementById("comune_nascita").dispatchEvent(new Event("change"));
                    }
                }
                else if(selectRif == 2){
                    if (valorediritorno != null && valorediritorno != undefined) {
                        $('#comune_cf').val(valorediritorno.comune);
                        $('#CC_cf').val(valorediritorno.CC);
												//alert("2 ente call");
                    }
                }
                else {

										//alert("menu ente 1");
                    if (valorediritorno != null && valorediritorno != undefined) {
											$('#ID_via_cap').val("");
											$('#ID_via').val("");
                        $('#comune').val(valorediritorno.comune);

												document.getElementById("comune").dispatchEvent(new Event("change"));

                        $('#dati_sogg_prov').val(valorediritorno.prov_sigla);
                        $('#CC').val(valorediritorno.CC);

                        pattern_numeri = /[^0-9]/;
                        cap_control = valorediritorno.cap;

                        if (cap_control.match(pattern_numeri)) {
                            cap_control = cap_control.replace('x', 0);
                            cap_control = cap_control.replace('x', 0);
                            $('#via').val(null);
                            $('#via').addClass('sfondo_ricerca').removeClass('sfondo_rosso sfondo_bianco');
                            $('#via').attr('readonly', 'readonly');
														$('#via').css("background-color","rgb(153, 204, 255)");
														$('#via').css("border","2px solid black");
                            $('#cap').val(cap_control);
														document.getElementById("cap").dispatchEvent(new Event("change"));
                            $('#cap').attr('readonly', 'readonly');
                            $('#via').attr('ondblclick', "RicercheDaId('indirizzo_generale',0);");
                            $('#via').attr('alt', "cap");
                        }
                        else {
                            $('#via').val(null);
                            $('#via').addClass('sfondo_ricerca').removeClass('sfondo_rosso sfondo_bianco');
                            $('#via').attr('readonly', 'readonly');
                            $('#cap').val(cap_control);
														document.getElementById("cap").dispatchEvent(new Event("change"));
                            $('#cap').attr('readonly', false);
                            $('#via').attr('ondblclick', "RicercheDaId('indirizzo_generale',0);");
                            $('#via').attr('alt', "via");
                        }

                        $('#civico').val(null);
                        $('#esponente').val(null);
                        $('#interno').val(null);
                        $('#dettagli').val(null);
                    }
                }
                break;

            case "indirizzo_generale":
                if(valorediritorno!=null && valorediritorno!=undefined) {

                    tipoRicInd = valorediritorno.tipoRic;

                    if (tipoRicInd == "cap") {
                        if (valorediritorno != null && valorediritorno != undefined) {
                            $('#cap').val(valorediritorno.cap);
                            $('#via').val(valorediritorno.indirizzo);
                            $('#ID_via_cap').val(valorediritorno.ID);
                            $('#ID_via').val(1);
														document.getElementById("via").dispatchEvent(new Event("change"));
                        }
                    }
                    else if (tipoRicInd == "via") {
                        if (valorediritorno != null && valorediritorno != undefined) {
                            $('#cap').val(valorediritorno.cap);
                            $('#cap').attr('readonly', false);

														$('#via').val(valorediritorno.indirizzo);
                            $('#via').addClass('sfondo_ricerca').removeClass('sfondo_rosso sfondo_bianco sfondo_giallo');
														$("#via").css("background-color","rgb(153, 204, 255)");
														$("#via").css("border","2px solid black");

														$('#ID_via').val(valorediritorno.ID);
                            $('#ID_via_cap').val(1);
                            $('#civico').val(null);
                            $('#esponente').val(null);
                            $('#interno').val(null);
                            $('#dettagli').val(null);
														document.getElementById("via").dispatchEvent(new Event("change"));
                        }
                    }
                    else if (valorediritorno == "no_via") {
                        $('#ID_via_cap').val(1);
                        $('#ID_via').val(0);
                        $('#cap').attr('readonly', false);

											  $('#via').attr('readonly', false);
                        $('#via').val(null);
                        $('#via').removeClass('sfondo_ricerca sfondo_bianco sfondo_giallo').addClass('sfondo_rosso');
												$('#via').css("background-color","");
												$('#via').css("border","");

												$('#civico').val(null);
                        $('#esponente').val(null);
                        $('#interno').val(null);
                        $('#dettagli').val(null);
                        alert('Inserire manualmente il nuovo indirizzo sul campo evidenziato in rosso o effettuare un doppio click per effettuare una nuova ricerca.\n\nSI PREGA DI COMPILARE IL NUOVO INDIRIZZO INTERAMENTE SENZA ABBREVIAZIONI PER FACILITARE LE FUTURE RICERCHE DELLO STESSO.');
                    }
                }
                break;
            case "via":

                if(valorediritorno!=null && valorediritorno!=undefined)
                {
                    $('#cap').val(valorediritorno.cap);
                    $('#via').val(valorediritorno.indirizzo);
                    $('#via').addClass('sfondo_ricerca').removeClass('sfondo_rosso sfondo_bianco sfondo_giallo');
                    $('#ID_via').val(valorediritorno.ID);
                    $('#ID_via_cap').val(1);
										document.getElementById("via").dispatchEvent(new Event("change"));
                }
                else
                {
                    $('#ID_via_cap').val(1);
                    $('#ID_via').val(0);
                    $('#via').attr('readonly',false);
                    $('#via').val(null);
                    $('#via').removeClass('sfondo_ricerca sfondo_bianco sfondo_giallo').addClass('sfondo_rosso');
                    alert('Inserire manualmente il nuovo indirizzo sul campo evidenziato in rosso o effettuare un doppio click per effettuare una nuova ricerca.\n\nSI PREGA DI COMPILARE IL NUOVO INDIRIZZO INTERAMENTE SENZA ABBREVIAZIONI PER FACILITARE LE FUTURE RICERCHE DELLO STESSO.');
                }

                $('#civico').val(null);
                $('#esponente').val(null);
                $('#interno').val(null);
                $('#dettagli').val(null);

                break;
            case "cap":
                if(valorediritorno!=null && valorediritorno!=undefined)
                {
                    $('#cap').val(valorediritorno.cap);
                    $('#via').val(valorediritorno.indirizzo);
                    $('#ID_via_cap').val(valorediritorno.ID);
                    $('#ID_via').val(1);
										document.getElementById("via").dispatchEvent(new Event("change"));
                }
                else
                {
                    RicercheDaId('via',0);
                }
                break;
            case "esenzione":
                if(valorediritorno!=null && valorediritorno!=undefined)
                {
                    $('#esenzione').val(valorediritorno.descrizione);
                    $('#ese').val(valorediritorno.ID);
                }
                break;
            case "situazione":
                if(valorediritorno!=null && valorediritorno!=undefined)
                {
                    $('#situazione').val(valorediritorno.descrizione);
                    $('#sit').val(valorediritorno.ID);
                }
                break;
            case "controllo":
                if(valorediritorno!=null && valorediritorno!=undefined)
                {
                    $('#controllo').val(valorediritorno.descrizione);
                    $('#con').val(valorediritorno.ID);
                }
                break;
            case "raggr":

                if(valorediritorno!=null && valorediritorno!=undefined)
                {
                    $('#raggr').val(valorediritorno.descrizione);
                    $('#rag').val(valorediritorno.ID);
                }
                break;
            case "sotto_raggr":
                if(valorediritorno!=null && valorediritorno!=undefined)
                {
                    $('#sottoraggr').val(valorediritorno.descrizione);
                    $('#sot').val(valorediritorno.ID);
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

   				//strDim = Dim_Alert(600, 300);
   				var stringa = "<?=WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=generale&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
   				//valorediritorno = window.showModalDialog(stringa,"", strDim);

	        openWindowSearch(stringa,{width:600, height:300, left:(($(window).width()/2)-300), top:(($(window).height()/2)-150)});

   				break;

   			case "stato":
   				if(modalita=="modifica")
   		    	{
                    //strDim = Dim_Alert(600, 300);
                    var stringa = "<?=WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricPaese";
                    //valorediritorno = window.showModalDialog(stringa, "", strDim);

										openWindowSearch(stringa,{width:600, height:300, left:(($(window).width()/2)-300), top:(($(window).height()/2)-150)});

   		    	}
   				break;

   			case "ente":
   				if(modalita=="modifica")
   		    	{
					//strDim = Dim_Alert(600, 300);
					//alert("RicercheDaId ente 1");
   					var stringa = "<?=WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricComune";

                    if(($('#paese_nascita').val()=="Italia" && rif==0) || ($('#paese').val()=="Italia" && rif==1) || ($('#paese_cf').val()=="Italia" && rif==2)) {
                        //valorediritorno = window.showModalDialog(stringa, "", strDim);

												openWindowSearch(stringa,{width:600, height:300, left:(($(window).width()/2)-300), top:(($(window).height()/2)-150)});
                    }
   		    	}
   				break;

   			case "indirizzo_generale":

   				if(modalita=="modifica")
   		    	{

	   				//strDim = Dim_Alert(750, 400);
	   				pvia = $('#via').val();
	   				pcomune = $('#comune').val();
	   				pCC = $('#CC').val();
	   				tipoRicInd = $('#via').attr('alt');

	   				var stringa = "<?=WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=indirizzo_generale&via_ric="+pvia+"&pc="+pcomune+"&pCC="+pCC+"&tipoRicInd="+tipoRicInd+"&c=<?php echo $c; ?>";
	   				//valorediritorno = window.showModalDialog(stringa, "", strDim);

						openWindowSearch(stringa,{width:750, height:400, left:(($(window).width()/2)-375), top:(($(window).height()/2)-200)});

   		    	}

   	   			break;

   			case "via":

   				if(modalita=="modifica")
   		    	{

	   	   			if(rif==1)
	   	   			{
	   	   	   		//	if($('#ID_via_cap').val() == 1 && $('#via').val() != null && $('#via').val()!="")
	   	   	   			//{
									if($("#ID_via_cap").val() > 1)
									{
										alert("Hai selezionato un indirizzo cappato, e quindi non è possibile abilitare la scrittura.");
									}
									else if($("#ID_via_cap").val() == 1){
										ctrl_giallo = $('#via').hasClass('sfondo_giallo');

		   	   					if( ctrl_giallo == false )
		   	   					{
			 	   						$('#via').prop('readonly',false).toggleClass('sfondo_ricerca').toggleClass('sfondo_giallo');
											$('#via').css("background-color","");
											$('#via').css("border","");
			 	   						alert("Ora e' possibile modificare l'indirizzo. Terminata l'operazione cliccare nuovamente sulla gomma.\n\nSi ricorda che questa funzione serve per correggere errori di battitura e non per inserire un nuovo indirizzo.");
			 	   						$('#via').focus();
		   	   					}
		   	   					else if( ctrl_giallo == true )

		   	   					{
		   	   						$('#via').prop('readonly',true).toggleClass('sfondo_ricerca').toggleClass('sfondo_giallo');
											$('#via').css("background-color","rgb(153, 204, 255)");
											$('#via').css("border","2px solid black");
		   	   						alert("Operazione effettuata correttamente");
		   	   						$('#via').focus();
		   	   					}
									}
									else{
										alert("Prima di inserire manualmente l'indirizzo effettuare la ricerca");
									}

	   	   	   		//	}
	   	   			}
	   	   			else
	   	   			{

		   				//strDim = Dim_Alert(600, 300);
						pCC = $('#CC').val();
						pvia = $('#via').val();

		   				var stringa = "<?=WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricIndirizzo&pCC="+pCC+"&via_ric="+pvia+"&c=<?php echo $c; ?>";
		   				//valorediritorno = window.showModalDialog(stringa, "", strDim);

							openWindowSearch(stringa,{width:600, height:300, left:(($(window).width()/2)-300), top:(($(window).height()/2)-150)});

	   	   			}
   		    	}
   			break;

   			case "cap":
   				if(modalita=="modifica")
   		    	{
	   				//strDim = Dim_Alert(750, 400);
	   				pvia = $('#via').val();
	   				pcomune = $('#comune').val();
	   				pCC = $('#CC').val();

	   				var stringa = "<?=WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricCap&via_ric="+pvia+"&pc="+pcomune+"&pCC="+pCC;
	   				//valorediritorno = window.showModalDialog(stringa, "", strDim);

						openWindowSearch(stringa,{width:750, height:400, left:(($(window).width()/2)-375), top:(($(window).height()/2)-200)});
   		    	}

   			break;

   			case "esenzione":
   				if(modalita=="modifica")
   		    	{
                    //strDim = Dim_Alert(370, 330);
                    var stringa = "<?=WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricGruppo&gruppo=ric_esenzione&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
                    //valorediritorno = window.showModalDialog(stringa,"", strDim);

										openWindowSearch(stringa,{width:370, height:330, left:(($(window).width()/2)-185), top:(($(window).height()/2)-165)});
   		    	}
   	   			break;

   			case "situazione":
   				if(modalita=="modifica")
   		    	{
   				//strDim = Dim_Alert(370, 330);
   				var stringa = "<?=WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricGruppo&gruppo=ric_situazione&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
   				//valorediritorno = window.showModalDialog(stringa,"", strDim);

					openWindowSearch(stringa,{width:370, height:330, left:(($(window).width()/2)-185), top:(($(window).height()/2)-165)});

   		    	}
   	   			break;

   			case "controllo":
   				if(modalita=="modifica")
   		    	{
   				//strDim = Dim_Alert(370, 330);
   				var stringa = "<?=WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricGruppo&gruppo=ric_controllo&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
   				//valorediritorno = window.showModalDialog(stringa,"", strDim);

					openWindowSearch(stringa,{width:370, height:330, left:(($(window).width()/2)-185), top:(($(window).height()/2)-165)});
   		    	}
   	   			break;

   			case "raggr":
   				if(modalita=="modifica")
   		    	{
   					//strDim = Dim_Alert(370, 330);
   					var stringa = "<?=WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricGruppo&gruppo=ric_raggr&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
   					//valorediritorno = window.showModalDialog(stringa,"", strDim);

						openWindowSearch(stringa,{width:370, height:330, left:(($(window).width()/2)-185), top:(($(window).height()/2)-165)});
   		    	}
   	   			break;

   			case "sotto_raggr":
   				if(modalita=="modifica")
   		    	{
   					//strDim = Dim_Alert(370, 330);
   					var stringa = "<?=WEB_ROOT; ?>/search/comuni/ricerca_alert_modale.php?richiesta=ricGruppo&gruppo=ric_sotto_raggr&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
   					//valorediritorno = window.showModalDialog(stringa,"", strDim);

						openWindowSearch(stringa,{width:370, height:330, left:(($(window).width()/2)-185), top:(($(window).height()/2)-165)});
   		    	}
   	   			break;

   		}
   	}

</script>
