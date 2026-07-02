
<?php 

$autorizzazione = get_var('aut_tipo');
$id = get_var('id');


$ctrlUsername = $_SESSION['username'];
	
if ($ctrlUsername != "lucal" &&
	$ctrlUsername != "matteo" &&  
	$ctrlUsername != "andrea" && 
	$ctrlUsername != "riccardo" && 
	$ctrlUsername != "daniela")
{
	alert ("L'utente non è abilitato alla fatturazione");
	echo "<script>history.back();</script>";
	return;
}

?>

<script>
var stringaMODE = "c=<?php echo $c; ?>&a=<?php echo $a; ?>&id=<?php echo $id; ?>";

	$("*").on( "change" , "input, textarea, select" , function( event ) {

	var elem = $( this );
	elem.addClass( "sfondo_giallo", ":change" );

	});

	$("*").on( "focus blur","input, textarea, select", function( event ) {
	var elem = $( this );

	elem.toggleClass( "focused", elem.is( ":focus" ) );

	});

	function focusIndex()
	{
		$('[tabindex=1]').focus();
	}


function menuClick (value)
{
	switch(value)
	{
		case '1':	 menulink = "/gitco2/fatturazione/fatture.php?"+stringaMODE;		break;
		case '2':  	 menulink = "/gitco2/fatturazione/stampe/pagina_stampa_fatture.php?"+stringaMODE;	break;	
		case '3':  	 menulink = "/gitco2/fatturazione/modali/parametri_tabelle.php?"+stringaMODE;	break;
		case '4':  	 menulink = "/gitco2/fatturazione/spedizioni.php?"+stringaMODE;	break;
		case '5':  	 menulink = "/gitco2/fatturazione/pagamenti.php?"+stringaMODE;	break;
		case '6':  	 menulink = "/gitco2/fatturazione/email_fatture.php?"+stringaMODE;	break;
		case '7':	 menulink = "/gitco2/fatturazione/fattureSTC.php?"+stringaMODE;		break;
		case '8':  	 menulink = "/gitco2/fatturazione/email_scaricate.php?"+stringaMODE;	break;
		case '9':  	 menulink = "/gitco2/fatturazione/email_messeaposto.php?"+stringaMODE;	break;
		
		default:	 alert ("Errore nella scelta del menu");												break;
	}
	
	top.location.href = menulink;

}



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


//NUOVO UTENTE E RITORNO AL MENU
function link(value)
{
	switch(value)
	{
		case "menu":

			stringa = "/gitco2/fatturazione/fatture.php?c=<?php echo $c; ?>&a=<?php echo $a; ?>";
				
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
				ritorno = confirm("Si stanno inserendo nuovi dati nel database.\n\nConfermare l'operazione?");
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

			case "Salva": 	
				ritorno = confirm("Si stanno inserendo nuovi dati nel database.\n\nConfermare l'operazione?");
				$('#invia_submit').val('Salva');

				break;

			case "Elabora": 	
				ritorno = confirm("Si stanno elaborando nuovi dati per l'inserimento nel database.\n\nConfermare l'operazione?");
				$('#invia_submit').val('Elabora');

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
	<li class="li_hc"><a onclick="" href="#" target="_self" >Fatturazione</a>
		<ul class="ul_ch">
			<li class="li_nc"><a onclick="menuClick('1');" href="#" >Gestione fatture</a></li>
			<li class="li_nc"><a onclick="menuClick('4');" href="#" >Spedizioni</a></li>
			<li class="li_nc"><a onclick="menuClick('5');" href="#" >Accrediti</a></li>
			<li class="li_nc"><a onclick="" href="#" target="_self" >Posta PEC</a>
				<ul class="ul_ch">
					<li class="li_hc"><a onclick="menuClick('6');" href="#" >Scarica PEC</a>
					<li class="li_nc"><a onclick="menuClick('8');" href="#" >Controlla PEC</a></li>
					<?php if ($_SESSION['CC_User'] == "***+") { ?>
						<li class="li_nc"><a onclick="menuClick('9');" href="#" >Aggiusta PEC</a></li>
					<?php } ?>
				</ul>
			</li>
		</ul>
    </li>
    <li class="li_hc"><a onclick="menuClick('2');" href="#" target="_self" >Stampe</a>
		<!-- <ul class="ul_ch">
			<li class="li_nc"><a onclick="menuClick('17');" href="#" >Selezione Ente/Anno</a></li>
         	<li class="li_nc"><a onclick="menuClick('36');" href="#" >Creazione anno</a></li>
        	
         	<li class="li_nc"><a onclick="menuClick('19');" href="#" >Creazione anno</a></li>
         	<li class="li_nc"><a onclick="menuClick('20');" href="#" >Cancellazione ente</a></li>
         	<li class="li_nc"><a onclick="menuClick('21');" href="#" >Cancellazione anno</a></li>
         	
    	</ul> -->
    </li>
	<li class="li_hc"><a onclick="menuClick('3');" href="#" target="_self" >Tabelle</a>
    </li>
</ul>
</div>