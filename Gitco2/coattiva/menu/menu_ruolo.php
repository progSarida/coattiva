<script>
var stringaMODE = "&p=<?php echo $p; ?>&c=<?php echo $c; ?>&a=<?php echo $a; ?>";


	$("*").on( "change" , "input, textarea, select" , function( event ) {

	var elem = $( this );
	elem.addClass( "sfondo_giallo", ":change" );

	});

	$("*").on( "focus blur","input, textarea, select", function( event ) {
	var elem = $( this );

	elem.toggleClass( "focused", elem.is( ":focus" ) );

	});


	function menuClick (value)
	{
		$elab_atti = "/gitco2/elaborazioni/elabora_atto.php?"+stringaMODE;
		$stampa_atti = "/gitco2/stampe/stampa_atto.php?"+stringaMODE;
		$elenco_atti = "/gitco2/stampe/elenco_atto.php?"+stringaMODE;
		$autorita = "/gitco2/parametri/ufficio_giudiziario.php?"+stringaMODE;
		switch(value)
		{
			case '1':	 menulink = "/gitco2/anagrafe/dati_soggetto.php?"+stringaMODE;				break;
			case '2':  	 menulink = "/gitco2/anagrafe/annotazioni.php?"+stringaMODE;				break;		
			case '3':    menulink = "/gitco2/anagrafe/recapito.php?"+stringaMODE;					break;
			case '4':	 menulink = "/gitco2/anagrafe/domicilio.php?"+stringaMODE;					break;
			case '5':	 menulink = "/gitco2/anagrafe/dettagli.php?"+stringaMODE;					break;
			case '6':	 menulink = "/gitco2/anagrafe/cambia_residenza.php?"+stringaMODE;			break;

			case '7':	 menulink = "/gitco2/coattiva/gestione_ruolo.php?"+stringaMODE;				break;

			case '8':	 menulink = "/gitco2/290/preimportazione_290.php?"+stringaMODE;				break;

			case '44':	 menulink = "/gitco2/coattiva/lista_codici_tributo.php?"+stringaMODE;		break;
			case '9':	 menulink = "/gitco2/coattiva/gestione_partita.php?"+stringaMODE;			break;
			case '10':	 menulink = "/gitco2/coattiva/ingiunzione.php?"+stringaMODE;				break;
			case '11':	 menulink = "/gitco2/coattiva/pagamento.php?"+stringaMODE;					break;
			case '12':	 menulink = "/gitco2/coattiva/ricorso.php?"+stringaMODE;					break;

			case '13':	 menulink = "/gitco2/gestione/crea_comune.php?"+stringaMODE;				break;
			case '14':	 menulink = "/gitco2/gestione/crea_anno.php?"+stringaMODE;					break;
			case '15':	 menulink = "/gitco2/gestione/elimina_comune.php?"+stringaMODE;				break;
			case '16':	 menulink = "/gitco2/gestione/elimina_anno.php?"+stringaMODE;				break;

			case '17':	 menulink = "/gitco2/parametri/dati_ente.php?"+stringaMODE;					break;
			case '18':	 menulink = "/gitco2/parametri/gestore.php?"+stringaMODE;					break;
			case '30':	 menulink = "/gitco2/parametri/ufficio.php?"+stringaMODE;					break;

			case '35':	 menulink = $autorita + "&tipo_ufficio=tribunale";							break;
			case '36':	 menulink = $autorita + "&tipo_ufficio=giudice";							break;
			case '37':	 menulink = $autorita + "&tipo_ufficio=appello";							break;
			case '38':	 menulink = $autorita + "&tipo_ufficio=comm_trib_prov";						break;
			case '39':	 menulink = $autorita + "&tipo_ufficio=comm_trib_reg";						break;
			case '40':	 menulink = $autorita + "&tipo_ufficio=cassazione";							break;
			
			case '19':	 menulink = "/gitco2/parametri/CDS/par_generali_CDS.php?"+stringaMODE;		break;
			case '20':	 menulink = "/gitco2/parametri/CDS/par_annuali_CDS.php?"+stringaMODE;		break;
			case '21':	 menulink = "/gitco2/parametri/stampe/testo_avviso_intimazione.php?"+stringaMODE;		break;
			case '28':	 menulink = "/gitco2/parametri/stampe/testo_ingiunzione.php?"+stringaMODE;	break;
			case '43':	 menulink = "/gitco2/parametri/stampe/testo_sollecito_ingiunzione.php?"+stringaMODE;	break;
			case '31':	 menulink = "/gitco2/parametri/stampe/testo_preavviso_ingiunzione.php?"+stringaMODE;	break;
			
			case '22':	 menulink = $elab_atti + "&tipo_atto=Ingiunzione";							break;
			case '32':	 menulink = $elab_atti + "&tipo_atto=sollecito";							break;
			case '23':	 menulink = $elab_atti + "&tipo_atto=avv_intimazione";						break;

			case '34':	 menulink = $stampa_atti + "&tipo_atto=preavviso_ing";						break;
			case '24':	 menulink = $stampa_atti + "&tipo_atto=Ingiunzione";						break;
			case '42':	 menulink = $stampa_atti + "&tipo_atto=sollecito";							break;
			case '25':	 menulink = $stampa_atti + "&tipo_atto=avv_intimazione";					break;
			case '33':	 menulink = $elenco_atti + "&tipo_atto=preavviso_ing";						break;
			case '26':	 menulink = $elenco_atti + "&tipo_atto=Ingiunzione";						break;
			case '41':	 menulink = $elenco_atti + "&tipo_atto=sollecito";							break;
			case '27':	 menulink = $elenco_atti + "&tipo_atto=avv_intimazione";					break;

			case '29':	 menulink = "/gitco2/menu/scelta_CC_e_anno.php?"+stringaMODE;				break;
			default:	 alert ("Errore nella scelta del menu");									break;
		}
		
		top.location.href = menulink;
	
	}

</script>
<script>
	var fn = function (e)
	{
		if (!e)
		{
	    	e = window.event;
		}

	    var keycode = e.keyCode;
	    if (e.which)
	        keycode = e.which;

	 	//var src = e.srcElement;
	  	//if (e.target)
	       	//src = e.target;

	     	
	//CONSULTA F2
	   	    if (113 == keycode)
		    {
		        // Firefox and other non IE browsers
		        if (e.preventDefault)
		        {
		            e.preventDefault();
		            e.stopPropagation();
		        }
		        // Internet Explorer
		        else if (e.keyCode)
		        {
		            e.keyCode = 0;
		            e.returnValue = false;
		            e.cancelBubble = true;
		        }		

				cambia_F2();

		        return false;
		    }
	//SALVA F3
	   	    else if (114 == keycode)
	   	    {
	   	        // Firefox and other non IE browsers
	   	        if (e.preventDefault)
	   	        {
	   	            e.preventDefault();
	   	            e.stopPropagation();
	   	        }
	   	        // Internet Explorer
	   	        else if (e.keyCode)
	   	        {
	   	            e.keyCode = 0;
	   	            e.returnValue = false;
	   	            e.cancelBubble = true;
	   	        }		

				salva_form();
	         	
	   	        return false;
	   	    }
	//ELIMINA F4
	   	    else if (115 == keycode)
		    {
		        // Firefox and other non IE browsers
		        if (e.preventDefault)
		        {
		            e.preventDefault();
		            e.stopPropagation();
		        }
		        // Internet Explorer
		        else if (e.keyCode)
		        {
		            e.keyCode = 0;
		            e.returnValue = false;
		            e.cancelBubble = true;
		        }		

				cancella_form();
				
		        return false;
		    }
	//ANNULLA F5
	   	    else if (116 == keycode)
		    {
		        // Firefox and other non IE browsers
		        if (e.preventDefault)
		        {
		            e.preventDefault();
		            e.stopPropagation();
		        }
		        // Internet Explorer
		        else if (e.keyCode)
		        {
		            e.keyCode = 0;
		            e.returnValue = false;
		            e.cancelBubble = true;
		        }		

		        annulla();
		        	
		        return false;
		    }
	//MODIFICA F6
	   	    else if (117 == keycode)
		    {
		        // Firefox and other non IE browsers
		        if (e.preventDefault)
		        {
		            e.preventDefault();
		            e.stopPropagation();
		        }
		        // Internet Explorer
		        else if (e.keyCode)
		        {
		            e.keyCode = 0;
		            e.returnValue = false;
		            e.cancelBubble = true;
		        }		

				nuovo_F6();
		        
		        return false;
		    }
	//RECORD PRECEDENTE F7
			else if (118 == keycode)
	   	    {
	   	        // Firefox and other non IE browsers
	   	        if (e.preventDefault)
	   	        {
	   	            e.preventDefault();
	   	            e.stopPropagation();
	   	        }
	   	        // Internet Explorer
	   	        else if (e.keyCode)
	   	        {
	   	            e.keyCode = 0;
	   	            e.returnValue = false;
	   	            e.cancelBubble = true;
	   	        }		

	   	     	cambia_pag('prev');
   	        	
	   	        return false;
	   	    }
	//RECORD SUCCESSIVO F8
			else if (119 == keycode)
	   	    {
	   	        // Firefox and other non IE browsers
	   	        if (e.preventDefault)
	   	        {
	   	            e.preventDefault();
	   	            e.stopPropagation();
	   	        }
	   	        // Internet Explorer
	   	        else if (e.keyCode)
	   	        {
	   	            e.keyCode = 0;
	   	            e.returnValue = false;
	   	            e.cancelBubble = true;
	   	        }		

	   	     	cambia_pag('suc');
	   	     	
	   	        return false;
	   	    }
	//RICERCA F9
			else if (120 == keycode)
	   	    {
	   	        // Firefox and other non IE browsers
	   	        if (e.preventDefault)
	   	        {
	   	            e.preventDefault();
	   	            e.stopPropagation();
	   	        }
	   	        // Internet Explorer
	   	        else if (e.keyCode)
	   	        {
	   	            e.keyCode = 0;
	   	            e.returnValue = false;
	   	            e.cancelBubble = true;
	   	        }	

	   	        ricerca_F9();

	   	     	return false;

	   	    }

	//STAMPA F10
			else if (121 == keycode)
	   	    {
	   	        // Firefox and other non IE browsers
	   	        if (e.preventDefault)
	   	        {
	   	            e.preventDefault();
	   	            e.stopPropagation();
	   	        }
	   	        // Internet Explorer
	   	        else if (e.keyCode)
	   	        {
	   	            e.keyCode = 0;
	   	            e.returnValue = false;
	   	            e.cancelBubble = true;
	   	        }	

	   	        stampa_F10();

	   	     	return false;	

	   	    }
	   	    
	//HELP F11
			else if (122 == keycode)
	   	    {
	   	        // Firefox and other non IE browsers
	   	        if (e.preventDefault)
	   	        {
	   	            e.preventDefault();
	   	            e.stopPropagation();
	   	        }
	   	        // Internet Explorer
	   	        else if (e.keyCode)
	   	        {
	   	            e.keyCode = 0;
	   	            e.returnValue = false;
	   	            e.cancelBubble = true;
	   	        }		

	   	     	window.open('/gitco2/help/intestazione.html','help','width=650,height=400,top=70,left=70,scrollbars=yes, menubar=yes');
	   	     	
	   	        return false;
	   	    }
	//HOME F12
			else if (123 == keycode)
	   	    {
	   	        // Firefox and other non IE browsers
	   	        if (e.preventDefault)
	   	        {
	   	            e.preventDefault();
	   	            e.stopPropagation();
	   	        }
	   	        // Internet Explorer
	   	        else if (e.keyCode)
	   	        {
	   	            e.keyCode = 0;
	   	            e.returnValue = false;
	   	            e.cancelBubble = true;
	   	        }		

	   	     	link('menu');
	   	     	
	   	        return false;
	   	    }
	//PAGINA GIU PagDown
			else if (34 == keycode)
	   	    {
	   	        // Firefox and other non IE browsers
	   	        if (e.preventDefault)
	   	        {
	   	            e.preventDefault();
	   	            e.stopPropagation();
	   	        }
	   	        // Internet Explorer
	   	        else if (e.keyCode)
	   	        {
	   	            e.keyCode = 0;
	   	            e.returnValue = false;
	   	            e.cancelBubble = true;
	   	        }		

	   	     	pag_prec();
	   	        	
	   	        return false;
	   	    }
	//PAGINA SU PagUp
			else if (33 == keycode)
	   	    {
	   	        // Firefox and other non IE browsers
	   	        if (e.preventDefault)
	   	        {
	   	            e.preventDefault();
	   	            e.stopPropagation();
	   	        }
	   	        // Internet Explorer
	   	        else if (e.keyCode)
	   	        {
	   	            e.keyCode = 0;
	   	            e.returnValue = false;
	   	            e.cancelBubble = true;
	   	        }		

	   	     	pag_suc();
	   	     	
	   	        return false;
	   	    }
	//ENTER
			else if (13 == keycode)
	   	    {

	   	     	
	   	        return true;
	   	    }
	 
	};

	document.onkeydown = fn;

</script>
<script>	
	
//NUOVO UTENTE E RITORNO AL MENU
function link(value)
{

	switch(value)
	{
		case "menu":

			stringa = "/gitco2/menu/home.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
				
		break;
				
	}
			
	top.location.href = stringa;

}

function submit_buttons (value)
{
	var ritorno = null;

		switch(value)
		{
			case "Insert": 	
				ritorno = confirm("Si stanno inserendo nuovi dati nel database. \n\nConfermare l'operazione?");
				$('#invia_submit').val('Insert');

				break;
			case "Delete":  
				ritorno = confirm("Si stanno eliminando i dati dal database relativi all'utente corrente.\nLa versione precedente dei dati non sar\xE0 in alcun modo ripristinabile in futuro. \n\nConfermare l'operazione?");	
				$('#invia_submit').val('Delete');

				break;
			case "Update": 	
				ritorno = confirm("Si stanno modificando i dati del database relativi all'utente corrente.\nLa versione precedente dei dati non sar\xE0 in alcun modo ripristinabile in futuro. \n\nConfermare l'operazione?");
				$('#invia_submit').val('Update');

				break;
			}
		
		if(value=="Delete")
		{
			if(ritorno)
			{
				ritorno2 = confirm("Sei sicuro di voler eliminare i dati?");
				if(ritorno2)
					{return true;}
				else
					{return false;}
			}
			else
			{return false;}
		}
		else
		{	
			if(ritorno)
				{return	true;}
			else
				{return	false;}
		}
	
}
</script>

<script>

function focusCampo()
{
	$("#id_cerca").focus();
}

</script>

<div class="mainmenu width100">
<ul class="text_left">
	<li class="li_hc"><a href="#" target="_self" >Gestione Ente</a><ul class="ul_ch">
			<li class="li_nc"><a onclick="menuClick('29');" href="#" >Selezione Ente/Anno</a></li>
         	<li class="li_nc"><a onclick="menuClick('13');" href="#" >Creazione ente</a></li>
        	<li class="li_nc"><a onclick="menuClick('14');" href="#" >Creazione anno</a></li>
         	<li class="li_nc"><a onclick="menuClick('15');" href="#" >Cancellazione ente</a></li>
         	<li class="li_nc"><a onclick="menuClick('16');" href="#" >Cancellazione anno</a></li>
    </ul></li>
	<li class="li_hc"><a href="#" target="_self" >Anagrafe</a><ul class="ul_ch">
         <li class="li_nc"><a onclick="menuClick('1');" href="#" >Dati Soggetto</a></li>
         <li class="li_nc"><a onclick="menuClick('2');" href="#" >Annotazioni</a></li>
         <li class="li_nc"><a onclick="menuClick('3');" href="#" >Recapito</a></li>
         <li class="li_nc"><a onclick="menuClick('4');" href="#" >Domicilio</a></li>
         <li class="li_nc"><a onclick="menuClick('5');" href="#" >Dettagli</a></li>
         <li class="li_nc"><a onclick="menuClick('6');" href="#" >Cambia Residenza e Storico</a></li>
    </ul></li>
    <li class="li_hc"><a href="#" target="_self" >Ruolo</a><ul class="ul_ch">
         <li class="li_nc"><a onclick="menuClick('7');" href="#" >Gestione</a></li>
         <li><a href="#" target="_self" >Partita</a><ul class="ul_ch">
         	<li class="li_nc"><a onclick="menuClick('9');" href="#"  >Codice Tributo</a></li>
         	<li class="li_nc"><a onclick="menuClick('10');" href="#" >Ingiunzione</a></li>
         	<li class="li_nc"><a onclick="menuClick('11');" href="#" >Pagamenti</a></li>
         	<li class="li_nc"><a onclick="menuClick('12');" href="#" >Ricorsi</a></li>
    	 </ul></li>
         <li class="li_nc"><a onclick="menuClick('8');" href="#" >Importazione 290</a></li>
         <li class="li_nc"><a onclick="menuClick('44');" href="#" >Lista Codici Tributo</a></li>
    </ul></li>
    <li class="li_hc"><a href="#" target="_self" >Elaborazioni</a><ul class="ul_ch">
    		<li class="li_nc"><a onclick="menuClick('22');" href="#" >Ingiunzione</a></li>
    		<li class="li_nc"><a onclick="menuClick('32');" href="#" >Sollecito di pagamento</a></li>
        	<li class="li_nc"><a onclick="menuClick('23');" href="#" >Avviso di intimazione</a></li>
    </ul></li>
    
    <li class="li_hc"><a href="#" target="_self" >Stampe</a>
    <ul class="ul_ch">
         	<li class="li_nc"><a target="_self" href="#" >Preavviso Ingiunzione</a>
         		<ul class="ul_ch">
         			<li class="li_nc"><a onclick="menuClick('33');" href="#" >Elenco</a></li>
        			<li class="li_nc"><a onclick="menuClick('34');" href="#" >Stampa</a></li>
         		</ul>
         	</li>
         	<li class="li_nc"><a target="_self" href="#" >Ingiunzione</a>
         		<ul class="ul_ch">
         			<li class="li_nc"><a onclick="menuClick('26');" href="#" >Elenco</a></li>
        			<li class="li_nc"><a onclick="menuClick('24');" href="#" >Stampa</a></li>
         		</ul>
         	</li>
         	<li class="li_nc"><a target="_self" href="#" >Sollecito di pagamento</a>
         		<ul class="ul_ch">
         			<li class="li_nc"><a onclick="menuClick('41');" href="#" >Elenco</a></li>
        			<li class="li_nc"><a onclick="menuClick('42');" href="#" >Stampa</a></li>
         		</ul>
         	</li>
        	<li class="li_nc"><a target="_self" href="#" >Avviso di intimazione</a>
        		<ul class="ul_ch">
         			<li class="li_nc"><a onclick="menuClick('27');" href="#" >Elenco</a></li>
        			<li class="li_nc"><a onclick="menuClick('25');" href="#" >Stampa</a></li>
         		</ul>
         	</li>
    </ul></li>
    
     <li class="li_hc"><a href="#" target="_self" >Testi</a><ul class="ul_ch">
         	<li class="li_nc"><a onclick="menuClick('31');" href="#" >Preavviso Ingiunzione</a></li>
         	<li class="li_nc"><a onclick="menuClick('28');" href="#" >Ingiunzione</a></li>
         	<li class="li_nc"><a onclick="menuClick('43');" href="#" >Sollecito di pagamento</a></li>
         	<li class="li_nc"><a onclick="menuClick('21');" href="#" >Avviso di intimazione</a></li>
         </ul></li>
         
    <li class="li_hc"><a href="#" target="_self" >Parametri</a><ul class="ul_ch">
         <li class="li_nc"><a onclick="menuClick('17');" href="#" >Dati ente</a></li>
         <li class="li_nc"><a onclick="menuClick('18');" href="#" >Gestore</a></li>
         <li class="li_nc"><a onclick="menuClick('30');" href="#" >Ufficio</a></li>
         <li><a href="#" target="_self" >Autorita'</a><ul class="ul_ch">
         	<li class="li_nc"><a onclick="menuClick('35');" href="#" >Tribunale</a></li>
         	<li class="li_nc"><a onclick="menuClick('36');" href="#" >Giudice di Pace</a></li>
         	<li class="li_nc"><a onclick="menuClick('37');" href="#" >Corte d'appello</a></li>
         	<li class="li_nc"><a onclick="menuClick('38');" href="#" >Commissione tributaria provinciale</a></li>
         	<li class="li_nc"><a onclick="menuClick('39');" href="#" >Commissione tributaria regionale</a></li>
         	<li class="li_nc"><a onclick="menuClick('40');" href="#" >Corte di cassazione</a></li>
    	 </ul></li>
         <li><a href="#" target="_self" >CDS</a><ul class="ul_ch">
         	<li class="li_nc"><a onclick="menuClick('19');" href="#" >Parametri generali</a></li>
         	<li class="li_nc"><a onclick="menuClick('20');" href="#" >Parametri annuali</a></li>
    	 </ul></li>
    	 
    </ul></li>
    
</ul>
</div>