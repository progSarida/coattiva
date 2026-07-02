<?php
/*require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/anagrafe.php";*/


if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include_once INC . "/headerAjax.php";
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";

$cls_help = new cls_help();
$cls_db = new cls_db();


//if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
  header("Location:accesso_negato.php");
  die;
}

$richiesta = $cls_help->getVar('richiesta');
$posted = $cls_help->getVar('posted');
$gruppo = $cls_help->getVar('gruppo');
$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');

$layout = "<script>$('[tabindex=1]').focus();</script>";

switch ($richiesta)
{
	case ('generale'):

		$titolopagina = "Tipo Ricerca";

		$nomecella = array();
		$nomecella[0] = "<b>Cognome Nome / Ditta</b>";
		$nomecella[1] = "<b>Codice Fiscale / Partita Iva</b>";

		$cella = array();
		$cella[0] = "<input class='tab' tabindex='1' type=radio name=tipo value=ricUtente checked>";
		$cella[1] = "<input type=radio name=tipo value=ricCF>";

		$campo = "";
		$nomecampo = "";
		$riga = "";


		break;
	case ('ditta'):

		$titolopagina = "Tipo Ricerca";
		$linkricerca = "ricerca_alert_modale.php?richiesta=ricDitta&posted=true&c=".$c;
		$nomecella = array();
		$nomecella[0] = "<b>Ditta</b>";
		$nomecella[1] = "<b>Partita Iva</b>";

		$cella = array();
		$cella[0] = "<input class='tab' tabindex='1' type=radio name=tipo value=ricDitta checked>";
		$cella[1] = "<input type=radio name=tipo value=ricCF>";

		$campo = "";
		$nomecampo = "";
		$riga = "";


		break;

	case ("ricUtente"):

		 $titolopagina = "Ricerca Utente";
		 $linkricerca = "ricerca_alert_modale.php?richiesta=ricUtente&posted=true&c=".$c;

		    $nomecella = array();
	        $nomecella[0] = "<b>Cognome/Nome</b>";

	        $cella = array();
            $cella[0] = "<input class='tab' tabindex='1' id=cognome type=text name=last_name value='' size=40 >";

            if($_SESSION['aut_tipo']==1){
                $nomecella[1] = "<input type='checkbox' tabindex='2' id=allCities name=allCities value='y'> Cerca su tutti i comuni";
                $cella[1] = "";
            }

            $campo = "";
		    $nomecampo = "";
		    $riga = "";

		if( $posted == true )
		{

			$last_name = $cls_help->getVar('last_name');
            $allCities = $cls_help->getVar('allCities');

			if($last_name==null)$last_name="";

			$termine = explode(" ", $last_name);

			$nomeCognome = "Cognome like '%".addslashes($last_name)."%' or ";
			$ditta = "Ditta like '%".addslashes($last_name)."%' or ";

			for($i=0; $i<count($termine); $i++)
			{
				$ditta.= "Ditta like '".addslashes($termine[$i])."%' or ";
				if(count($termine) == 1)
				{
					$nomeCognome .= "Cognome like '".addslashes($termine[$i])."%' and Nome like '%' or ";
					$nomeCognome .= "Cognome like '%' and Nome like '".addslashes($termine[$i])."%' or ";
				}
				else
				{
					for($y=0; $y<count($termine); $y++)
					{
						if($i!=$y)
						{
							$nomeCognome .= "Cognome like '".addslashes($termine[$i])."%' and Nome like '".addslashes($termine[$y])."%' or ";
						}
					}
				}
			}

			$nomeCognome = substr($nomeCognome, 0, -3);
			$ditta = substr($ditta, 0, -3);

			// errore dovuto al fatto che il campo Con_Nome per le ditte e' nullo e crea problemi nella query di selezione
			if ($termine[0] == NULL or $termine[0] == '')
			{
				$nomeCognome = "Cognome like '%'";
				$ditta = "Ditta like '%'";
			}

			$query = "( SELECT ID, Comune_ID, Codice_Fiscale AS CF, Cognome, Nome, Genere AS Genere, Ditta, Forma_Giuridica, Cognome AS utente_nome, CC_Comune FROM utente ";
			$query.= "WHERE Cognome != '' AND (".$nomeCognome.") ";
            if($allCities!="y")
                $query.= "and CC_Comune='".$c."' ";
			$query.= ") UNION ";
			$query.= "( SELECT ID, Comune_ID, Partita_Iva AS CF, Cognome, Nome, Genere AS Genere, Ditta, Forma_Giuridica, Ditta AS utente_nome, CC_Comune FROM utente ";
			$query.= "WHERE Ditta != '' AND (".$ditta.") ";
            if($allCities!="y")
                $query.= "and CC_Comune='".$c."' ";
			$query.= ") ORDER BY utente_nome, Nome";

			//$resultContr = safe_query($query);
			//$numero_contrib = mysql_num_rows($resultContr);

      $resultContr = $cls_db->ExecuteQuery($query);
      $numero_contrib = $cls_db->getNumberRow($resultContr);

		}

		break;
	case ("ricDitta"):

			$titolopagina = "Ricerca Ditta";
			 $linkricerca = "ricerca_alert_modale.php?richiesta=ricDitta&posted=true&c=".$c;
   
			   $nomecella = array();
			   $nomecella[0] = "<b>Nome Ditta</b>";
   
			   $cella = array();
			   $cella[0] = "<input class='tab' tabindex='1' id=cognome type=text name=last_name value='' size=40 >";
   
			   // if($_SESSION['aut_tipo']==1){
				   // $nomecella[1] = "<input type='checkbox' tabindex='2' id=allCities name=allCities value='y'> Cerca su tutti i comuni";
				   // $cella[1] = "";
			   // }
   
			   $campo = "";
			   $nomecampo = "";
			   $riga = "";
   
		   if( $posted == true )
		   {
   
			   $last_name = $cls_help->getVar('last_name');
			   $allCities = $cls_help->getVar('allCities');
   
			   if($last_name==null)$last_name="";
   
			   $termine = explode(" ", $last_name);
   
			   $ditta = "Ditta like '%".addslashes($last_name)."%' or ";
   
			   for($i=0; $i<count($termine); $i++)
			   {
				   $ditta.= "Ditta like '".addslashes($termine[$i])."%' or ";
				   
			   }
			   
			   $ditta = substr($ditta, 0, -3);
   
			   // errore dovuto al fatto che il campo Con_Nome per le ditte e' nullo e crea problemi nella query di selezione
			   if ($termine[0] == NULL or $termine[0] == '')
			   {
				   
				   $ditta = "Ditta like '%'";
			   }
   
			   $query = "";
			   $query.= "( SELECT ID, Comune_ID, Partita_Iva AS CF, Cognome, Nome, Genere AS Genere, Ditta, Forma_Giuridica, Ditta AS utente_nome, CC_Comune FROM utente ";
			   $query.= "WHERE Ditta != '' AND (".$ditta.") ";
			   if($allCities!="y")
				   $query.= "and CC_Comune='".$c."' ";
			   $query.= ") ORDER BY utente_nome, Nome";
   
			   //$resultContr = safe_query($query);
			   //$numero_contrib = mysql_num_rows($resultContr);
   
		 $resultContr = $cls_db->ExecuteQuery($query);
		 $numero_contrib = $cls_db->getNumberRow($resultContr);
   
		   }
   
		   break;
	case ("ricCF"):

			$titolopagina = "Ricerca Codice Fiscale";
			$linkricerca = "ricerca_alert_modale.php?richiesta=ricCF&posted=true&c=".$c;

			$nomecella = array();
			$nomecella[0] = "<b>Codice fiscale / P.IVA</b>";

			$cella = array();
			$cella[0] = "<input class='tab' tabindex='1' type=text name=ric_CDF value='' size=20 id=codice_fiscale >";

            if($_SESSION['aut_tipo']==1){
                $nomecella[1] = "<input type='checkbox' tabindex='2' id=allCities name=allCities value='y'> Cerca su tutti i comuni";
                $cella[1] = "";
            }

			$campo = "";
			$nomecampo = "";
			$riga = "";

			if( $posted == true )
			{
				$ric_CDF = $cls_help->getVar('ric_CDF');
                $allCities = $cls_help->getVar('allCities');

				$query = "(SELECT ID, Comune_ID, Codice_Fiscale AS CF, Cognome, Nome, Genere AS Genere, Ditta, Forma_Giuridica, Cognome AS utente_nome, CC_Comune FROM utente ";
				$query.= "WHERE Genere != 'D' AND Codice_Fiscale like '".$ric_CDF."%' ";
                if($allCities!="y")
                    $query.= "and CC_Comune='".$c."' ";
				$query.= ") UNION ";
				$query.= "(SELECT ID, Comune_ID, Partita_Iva AS CF, Cognome, Nome, Genere AS Genere, Ditta, Forma_Giuridica, Ditta AS utente_nome, CC_Comune FROM utente ";
				$query.= "WHERE Genere = 'D' AND Partita_Iva like '".$ric_CDF."%' ";
                if($allCities!="y")
                    $query.= "and CC_Comune='".$c."' ";
				$query.= ") ORDER BY CF";

				//$resultCF = safe_query($query);
        $resultCF = $cls_db->ExecuteQuery($query);
			}

		break;

	case ("ricPaese"):

		$titolopagina = "Ricerca Paese";
		 $linkricerca = "ricerca_alert_modale.php?richiesta=ricPaese&posted=true";

		   $nomecella = array();
		$nomecella[0] = "<b>Paese</b>";
		       $cella = array();
		    $cella[0] = "<input class='tab' tabindex='1' type=text name=ric_paese value='' size=20 id=paese >";

		    $campo ="";
		    $nomecampo = "";
		    $riga = "";

		if( $posted == true )
		{
			$ric_paese = $cls_help->getVar('ric_paese');

			$query = "SELECT CC_Paese_Estero, Nome FROM paesi_esteri_lista ";
			$query.= "WHERE Nome LIKE '%".$ric_paese."%' ORDER BY Nome";

			//$resultPaese = safe_query($query);

			//$num_paesi = mysql_num_rows($resultPaese);

      $resultPaese = $cls_db->ExecuteQuery($query);
      $num_paesi = $cls_db->getNumberRow($resultPaese);
		}

		break;

	case ("ricComune"):

		$titolopagina = "Lista Comuni";
		 $linkricerca = "ricerca_alert_modale.php?richiesta=ricComune&posted=true";

		   $nomecella = array();
		$nomecella[0] = "<b>Ente</b>";

			   $cella = array();
			$cella[0] = "<input class='tab' tabindex='1' type=text name=ric_comune value='' size=20 id=comune >";

			   $campo = "";
		   $nomecampo = "";
			    $riga = "";

		if( $posted == true )
		{
			$ric_comune = $cls_help->getVar('ric_comune');

      		$query = "SELECT Com_Codice_Catastale, Com_Codice_Provincia, Com_Nome, Pro_Sigla, Com_Cap ";
      		$query.= "FROM comuni_lista, province_lista	WHERE Com_Nome LIKE '%".addslashes($ric_comune)."%' ";
      		$query.= "AND Pro_Codice = Com_Codice_Provincia ORDER BY Com_Nome";


      		//$resultComune = safe_query($query);

      		//$num_comuni = mysql_num_rows($resultComune);

          $resultComune = $cls_db->ExecuteQuery($query);
          $num_comuni = $cls_db->getNumberRow($resultComune);

		}

		break;

	case ('indirizzo_generale'):

			$comune = $cls_help->getVar('pc');
			$via = $cls_help->getVar('via_ric');
			$CC = $cls_help->getVar('pCC');
			$tipoRicInd = $cls_help->getVar('tipoRicInd');

			$check_cappato = "disabled";
			$check_generico = "";

			if($tipoRicInd == "cap")
				$check_cappato = "checked";
			else
				$check_generico = "checked";

			$titolopagina = "Tipo Ricerca";

			$nomecella = array();
			$nomecella[0] = "<b>Indirizzo con Cap</b>";
			$nomecella[1] = "<b>Indirizzo generico</b>";
			$nomecella[2] = "";

			$cella = array();
			$cella[0] = "<input class='tab' tabindex='1' type=radio name=tipo value=ricCap ".$check_cappato.">";
			$cella[1] = "<input type=radio name=tipo value=ricIndirizzo ".$check_generico.">";
			$cella[2] = "<input id=pCC type=hidden name=pCC value='".$CC."'>";
			$cella[2].= "<input id=via_ric type=hidden name=via_ric value='".$via."'>";
			$cella[2].= "<input id=pc type=hidden name=pc value='".$comune."'>";

			$campo = "";
			$nomecampo = "";
			$riga = "";


		break;

	case ("ricCap"):

		$comune_cap = $cls_help->getVar('pc');
		$via_cap = $cls_help->getVar('via_ric');
		$CC = $cls_help->getVar('pCC');
		$tipoRicInd = $cls_help->getVar('tipoRicInd');

		$titolopagina = "Ricerca Indirizzo 'cappato'";
		$linkricerca = "ricerca_alert_modale.php?richiesta=ricCap&posted=true";

		$nomecella = array();
		$nomecella[0] = "<b>Comune</b>";
		$nomecella[1] = "<b>Indirizzo</b>";
		$nomecella[2] = "";

		$cella = array();
		$cella[0] = "<input id=comune type=text class='readonly' name=comune value='".$comune_cap."' size=40 readonly>";
		$cella[1] = "<input class='tab' tabindex='1' id=indirizzo type=text name=indirizzo value='".$via_cap."' size=40 >";
		$cella[2] = "<input id=CC type=hidden name=CC value='".$CC."'>";

		$campo = "";
		$nomecampo = "";
		$riga = "";

		if( $posted == true )
		{
			if($via_cap==null)$via_cap = "";

			$termine = explode(" ", $via_cap);

			for($i=0; $i<count($termine); $i++)
			{
				$CAP = "Odonimo like '%".str_replace("'","\'",$termine[$i])."%' or ";
			}

			$CAP = substr($CAP, 0, -3);

			if ($termine[0] == NULL or $termine[0] == '')
			{
				$CAP = "Odonimo like '%'";
			}

			$query = "SELECT Cap, Odonimo, Num_Civici, ID ";
			$query.= "FROM toponimi_cappati	WHERE CC_Toponimo = '".$CC."' ";
			$query.= "AND ".$CAP." ORDER BY Odonimo";

			//$resultCap = safe_query($query);

			//$num_cap = mysql_num_rows($resultCap);

      $resultCap = $cls_db->ExecuteQuery($query);
      $num_cap = $cls_db->getNumberRow($resultCap);
		}

		break;

	case ("ricIndirizzo"):

		$CC = $cls_help->getVar('pCC');
		$via_cap = $cls_help->getVar('via_ric');

		$titolopagina = "Ricerca Indirizzo";
		$linkricerca = "ricerca_alert_modale.php?richiesta=ricIndirizzo&posted=true";

		$nomecella = array();
		$nomecella[0] = "<b>Indirizzo</b>";
		$nomecella[1] = "";

		$cella = array();
		$cella[0] = "<input class='tab' tabindex='1' name=indirizzo type=text value='".$via_cap."' size=30 id=indirizzo >";
		$cella[1] = "<input id=CC type=hidden name=CC value='".$CC."'>";

		$campo = "";
		$nomecampo = "";
		$riga = "";

		if( $posted == true )
		{
			if($via_cap==null) $via_cap = "";

			$termine = explode(" ", $via_cap);

			for($i=0; $i<count($termine); $i++)
			{
				$CAP = "Nome like '%".str_replace("'","\'",$termine[$i])."%' or ";
			}

			$CAP = substr($CAP, 0, -3);

			if ($termine[0] == NULL or $termine[0] == '')
			{
				$CAP = "Nome like '%'";
			}


			$query = "SELECT Cap, Paese, Comune, Nome, ID ";
			$query.= "FROM toponimo	WHERE CC_Toponimo = '".$CC."' AND ID != 1 AND CC_Comune ='".$c."'";
			$query.= "AND ".$CAP." ORDER BY Nome";

			//$resultVia = safe_query($query);

			//$num_via = mysql_num_rows($resultVia);

      $resultVia = $cls_db->ExecuteQuery($query);
      $num_via = $cls_db->getNumberRow($resultVia);
		}

		break;



	case ('ricGruppo'):

$cella = array();
$nomecella = array();
$linkricerca = "ricerca_alert_modale.php?richiesta=ricGruppo&posted=true";

switch($gruppo)
{

	case ("ric_esenzione"):

		$linkricerca .= "&gruppo=ric_esenzione";
		$titolopagina = "Ricerca Esenzioni";

		$nomecella[0] = "<b>Esenzione</b>";
		$nome_col = "Esenz";

break;

	case ("ric_situazione"):

		$linkricerca .= "&gruppo=ric_situazione";
		$titolopagina = "Ricerca Situazioni";

		$nomecella[0] = "<b>Situazione</b>";
		$nome_col = "Situaz";

break;
	case ("ric_controllo"):

		$linkricerca .= "&gruppo=ric_controllo";
		$titolopagina = "Ricerca Controllo";

		$nomecella[0] = "<b>Controllo</b>";
		$nome_col = "Control";

break;

	case ("ric_raggr"):

		$linkricerca .= "&gruppo=ric_raggr";
		$titolopagina = "Ricerca Raggruppamento";

		$nomecella[0] = "<b>Raggruppamento</b>";
		$nome_col = "Raggr";

break;

	case ("ric_sotto_raggr"):

		$linkricerca .= "&gruppo=ric_sotto_raggr";
		$titolopagina = "Ricerca Sottoraggruppamento";

		$nomecella[0] = "<b>Sottoraggruppamento</b>";
		$nome_col = "Sotto_Raggr";

break;
}

$cella[0] = "<input class='tab' tabindex='1' type=text name=varie_cercate value='' size=20 id=varie_cercate >";
$campo ="";
$nomecampo = "";
$riga = "";

if( $posted == true )
{
	$varie_cercate = $cls_help->getVar('varie_cercate');
	$nome_col = $cls_help->getVar('nome_col');

	$query = "SELECT Descrizione, ID , Tipo FROM dettagli_utente_lista ";
	$query.= "WHERE Tipo = '".$nome_col."' AND Descrizione LIKE '%".$varie_cercate."%' ";
	$query.= "ORDER BY Descrizione";

	//$resultGruppo = safe_query($query);
	//$num_varie = mysql_num_rows($resultGruppo);

  $resultGruppo = $cls_db->ExecuteQuery($query);
  $num_varie = $cls_db->getNumberRow($resultGruppo);

}

			break;

	default:

		$titolopagina = "Nessun titolo";
		$linkricerca = "Nessun link";
		$linknuovo = "Nessun nuovo link";
		$query = "";

		break;
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <base target="_self" />

    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- Keep the http-equiv meta tag for IE8 -->
<meta http-equiv="X-UA-Compatible" content="IE=8" />

<title>Anagrafe</title>

	<link rel=StyleSheet href="<?= WEB_ROOT; ?>/CSS/classi_semplici.css" type="text/css" media=screen>

	<script type="text/javascript" language="javascript" src="<?= JS; ?>/JQuery.js"></script>
	<script type="text/javascript" language="javascript" src="<?= JS; ?>/form_jquery.js"></script>
  	<script type="text/javascript" language="javascript" src="<?= JS; ?>/funzioni.js"></script>


  	<script>

       var richiesta = "<?php echo $richiesta; ?>";
          var gruppo = "<?php echo $gruppo; ?>";

			function GeneraLinkPagina(richiesta)
			{
				var link = "<?php if(isset($linkricerca)){echo $linkricerca;}else{echo "";} ?>";

				switch (richiesta)
				{

				case ("generale"):

					var radio = $('[name=tipo]:checked').val();

					link = "ricerca_alert_modale.php?richiesta="+radio+"&c=<?php echo $c; ?>";


					break;
				case ("ricUtente"):

					var cognomeRic = $("#cognome").val();
                    var allCities = $("#allCities:checked").val();

                    link +="&allCities="+allCities;
					link +="&last_name="+cognomeRic;


				break;
				case ("ricditta"):

					var cognomeRic = $("#cognome").val();
					var allCities = $("#allCities:checked").val();

					
					link +="&allCities="+allCities;
					link +="&last_name="+cognomeRic;


					break;		
				case ("ricCF"):

					var codice_fiscale = $("#codice_fiscale").val();
                    var allCities = $("#allCities:checked").val()

					link +="&ric_CDF="+codice_fiscale;
                    link +="&allCities="+allCities;

				break;

				case ("ricPaese"):

					var paeseRic = $("#paese").val();

					link +="&ric_paese="+paeseRic;

				break;

				case ("ricComune"):

					var comuneRic = $(":text").val();
					var italianoEstero = $(":radio:checked").val();

					link +="&ric_comune="+comuneRic;
					link +="&italiano_estero="+italianoEstero;

				break;

				case ("indirizzo_generale"):

					var radio = $('[name=tipo]:checked').val();
					var comune = $("#pc").val();
					var indirizzo = $("#via_ric").val();
					var CC = $("#pCC").val();

					link = "ricerca_alert_modale.php?richiesta="+radio+"&c=<?php echo $c; ?>";

					link += "&pc="+comune;
					link += "&via_ric="+indirizzo;
					link += "&pCC="+CC;

					break;

				case ("ricCap"):

					var comune = $("#comune").val();
					var indirizzo = $("#indirizzo").val();
					var CC = $("#CC").val();

					link += "&comune_cap="+comune;
					link += "&via_ric="+indirizzo;
					link += "&pCC="+CC;

				break;

				case ("ricFrazione"):

					var comuneFraz = $("#comuneFraz").val();
					var paeseFraz = $("#paeseFraz").val();
					var statoFraz = $("#statoFraz").val();
					var frazione = $("[name='frazione_cercata']").val();

					link += "&comune_cercato="+comuneFraz;
					link += "&paese_cercato="+paeseFraz;
					link += "&stato_cercato="+statoFraz;
					link += "&frazione_cercata="+frazione;
					link += "&tipoRicInd"+tipoRicInd;

				break;

				case ("ricIndirizzo"):

					var indirizzo = $("#indirizzo").val();
					var CC = $("#CC").val();

					link += "&via_ric="+indirizzo;
					link += "&pCC="+CC;
					link += "&c=<?php echo $c; ?>";

				break;

				case ("ricGruppo"):

					var gruppo_cercato = $("#varie_cercate").val();
					var nome_col = "<?php if(isset($nome_col)){echo $nome_col;}else{echo "";} ?>";

					link +="&varie_cercate="+gruppo_cercato;
					link +="&nome_col="+nome_col;

				break;
				}

				self.location.href = link;

//                document.getElementById('goLocation').href = link;
//                document.getElementById('goLocation').click();

			}

			function gruppoOggetto( value1 )
			{

				ricerca_oggetto = { gruppo:value1 };

				return ricerca_oggetto;

			}

function torna_valore(value)
{

    try{
        window.opener.callParent(value);
    }
    catch(e){
        //alert("errore call Parent");
        alert(e.description);
    }

    self.close();
}

function Paese( value1, value2 )
{
	ricerca_oggetto = { paese:value1, CC:value2 } ;

    return ricerca_oggetto;
}

function Comune( value1, value2, value3, value4 )
{
	ricerca_oggetto = { comune:value1, CC:value2, prov_sigla:value3, cap:value4 } ;

    return ricerca_oggetto;
}

function Cap( value1, value2, value3, value4 )
{
	ricerca_oggetto = { tipoRic: value1, cap:value2, indirizzo:value3, ID:value4 } ;

    return ricerca_oggetto;
}

function Dettagli( value1, value2 )
{
	ricerca_oggetto = { ID:value1, descrizione:value2 } ;

    return ricerca_oggetto;
}

function utente_all (value1,value2){
    ricerca_oggetto = { p:value1, c:value2 } ;

    return ricerca_oggetto;
}

function blurLast()
{
	$('[tabindex=1]').focus();
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

//ESC
    if (27 == keycode)
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

       self.close();

       return false;
   }
};

document.onkeydown = fn;
       </script>
</head>

<body class="sfondo_new_gitco">
	<center>

	<h3><b><?php echo $titolopagina; ?></b></h3>

	</center>
    <a href=?? id=?goLocation? style=?display:none;?></a>
<?php if ($posted == NULL) { ?>

			<center>
<table class=table_modale cellspacing="5" cellpadding="0" border="0">

	<tr>
		<td colspan=4><br></td>
	</tr>
<?php for($k=0;$k<count($cella);$k++){?>


	<tr>
		<td></td>
		<td><?php echo $nomecella[$k]; ?></td>
		<td width=49% align=center><?php echo $cella[$k]; ?></td>
		<td></td>
	</tr>

<?php }?>

	<tr>
		<td colspan=4><?php echo $riga; ?></td>
	</tr>
	<tr>
		<td></td>
		<td><?php echo $nomecampo; ?></td>
		<td width=49% align=center><?php echo $campo; ?></td>
		<td></td>
	</tr>

	<tr><td colspan=4><hr></td></tr>
	<tr>
		<td></td>
		<td colspan=2 align="center">
		<input tabindex=2 class="ricerca" type=submit name="cerca" value="Cerca" onClick="GeneraLinkPagina(richiesta);">
		</td>
		<td></td>
	</tr>
	<tr>
		<td colspan=2><br></td>
	</tr>
</table>
		</center>

<?php }	else if ($posted == TRUE){

switch($richiesta)
{
case ("ricUtente"):

if ($cls_db->getNumberRow($resultContr)==0)
{echo"<script>alert('Contribuente non trovato.'); self.close();</script>";}
else
{
	$j = 0;
   	$i = 0; // contatore : serve per identificare righe pari e righe dispari
?>

<!-- RICERCA CONTRIBUENTE -->
<table align=center cellspacing=0 border=0>
	<tr class = riga_pari style="height:35px;" >
    	<td width=5% align=center></td>
        <td width=40% ><b>Utente</b></td>
        <td width=10% ><b>Tipo</b></td>
        <td width=1% align=center><br></td>
        <td width=5% align=center><b>ID</b></td>
        <td width=1% align=center><br></td>
        <td width=7% align=center><b>CC</b></td>
        <td width=1% align=right><br></td>
        <td width=30% ><b>CF / P.IVA</b></td>
	</tr>
<?php
//$forma = new forma_giuridica();
//$array_forma = $forma->array_completo();

$stringa = "SELECT * FROM forma_giuridica_societa WHERE CC = '*****'";
$data = $cls_db->ExecuteQuery($stringa);

//query = mysql_query($stringa);
$array_forma = array();

while($line = mysqli_fetch_array($data, MYSQLI_ASSOC))
{
  $array_forma[$line['ID']] = $line;
}

while($contr = mysqli_fetch_array($resultContr, MYSQLI_ASSOC))
      {
      	$add_tag = "";
      	if($contr['Genere']=='D')
      	{
      		$forma_descr = "";
      		if($contr['Forma_Giuridica']!="")
      		{
      			$index_value = $contr['Forma_Giuridica'];
      			if($index_value>0)
      				$forma_descr = $array_forma[$index_value]['Sigla'];
      		}
      		$utente_visual = $contr['Ditta']." ".$forma_descr;
      		$genere_visual = "Ditta";
      	}
      	else
      	{
      		$utente_visual = $contr['Cognome']." ".$contr['Nome'];
      		if($contr['Genere']=='M')
      			$genere_visual = "Maschio";
      		else if($contr['Genere']=='F')
      			$genere_visual = "Femmina";
      	}

         if ($i++ % 2)
         {$stile_riga = 'class="riga_pari"';}
         else
         {$stile_riga = 'class="riga_dispari"';}
         if($j==0)
         	$add_tag = "tabindex=1";
         else if($j == $cls_db->getNumberRow($resultContr) - 1)
         	$add_tag = "onblur=\"blurLast();\"";
?>
	<tr <?php echo $stile_riga ?>>
    	<td align=center>
    		<input type=image src="<?= IMMAGINIWEB; ?>/select.png" id=tab_<?php echo $j+1 ?> <?php echo $add_tag; ?> style="width:25px; height:25px; border:0;"
    		title="Clicca qui per inserire l'utente" onClick="dettagliOgg = utente_all('<?php echo $contr['ID']; ?>','<?php echo $contr['CC_Comune']; ?>');torna_valore(dettagliOgg);">
    	</td>
        <td align=left><?php echo $utente_visual; ?></td>
        <td align=left><?php echo $genere_visual; ?></td>
        <td align=center><br></td>
        <td align=center><?php echo $contr['Comune_ID']; ?></td>
        <td align=center><br></td>
        <td align=center><?php echo $contr['CC_Comune']; ?></td>
        <td align=center><br></td>
        <td align=left><?php echo $contr['CF']; ?></td>
	</tr>
<?php $j++;}
		}?>
</table> <?php

break;
case ("ricDitta"):

	if ($cls_db->getNumberRow($resultContr)==0)
	{echo"<script>alert('Ditta non trovata.'); self.close();</script>";}
	else
	{
		$j = 0;
		   $i = 0; // contatore : serve per identificare righe pari e righe dispari
	?>
	
	<!-- RICERCA CONTRIBUENTE -->
	<table align=center cellspacing=0 border=0>
		<tr class = riga_pari style="height:35px;" >
			<td width=5% align=center></td>
			<td width=40% ><b>Utente</b></td>
			<td width=10% ><b>Tipo</b></td>
			<td width=1% align=center><br></td>
			<td width=5% align=center><b>ID</b></td>
			<td width=1% align=center><br></td>
			<td width=7% align=center><b>CC</b></td>
			<td width=1% align=right><br></td>
			<td width=30% ><b>CF / P.IVA</b></td>
		</tr>
	<?php
	//$forma = new forma_giuridica();
	//$array_forma = $forma->array_completo();
	
	$stringa = "SELECT * FROM forma_giuridica_societa WHERE CC = '*****'";
	$data = $cls_db->ExecuteQuery($stringa);
	
	//query = mysql_query($stringa);
	$array_forma = array();
	
	while($line = mysqli_fetch_array($data, MYSQLI_ASSOC))
	{
	  $array_forma[$line['ID']] = $line;
	}
	
	while($contr = mysqli_fetch_array($resultContr, MYSQLI_ASSOC))
		  {
			  $add_tag = "";
			  if($contr['Genere']=='D')
			  {
				  $forma_descr = "";
				  if($contr['Forma_Giuridica']!="")
				  {
					  $index_value = $contr['Forma_Giuridica'];
					  if($index_value>0)
						  $forma_descr = $array_forma[$index_value]['Sigla'];
				  }
				  $utente_visual = $contr['Ditta']." ".$forma_descr;
				  $genere_visual = "Ditta";
			  }
			  
	
			 if ($i++ % 2)
			 {$stile_riga = 'class="riga_pari"';}
			 else
			 {$stile_riga = 'class="riga_dispari"';}
			 if($j==0)
				 $add_tag = "tabindex=1";
			 else if($j == $cls_db->getNumberRow($resultContr) - 1)
				 $add_tag = "onblur=\"blurLast();\"";
	?>
		<tr <?php echo $stile_riga ?>>
			<td align=center>
				<input type=image src="<?= IMMAGINIWEB; ?>/select.png" id=tab_<?php echo $j+1 ?> <?php echo $add_tag; ?> style="width:25px; height:25px; border:0;"
				title="Clicca qui per inserire la dirra" onClick="dettagliOgg = utente_all('<?php echo $contr['ID']; ?>','<?php echo $contr['CC_Comune']; ?>');torna_valore(dettagliOgg);">
			</td>
			<td align=left><?php echo $utente_visual; ?></td>
			<td align=left><?php echo $genere_visual; ?></td>
			<td align=center><br></td>
			<td align=center><?php echo $contr['Comune_ID']; ?></td>
			<td align=center><br></td>
			<td align=center><?php echo $contr['CC_Comune']; ?></td>
			<td align=center><br></td>
			<td align=left><?php echo $contr['CF']; ?></td>
		</tr>
	<?php $j++;}
			}?>
	</table> <?php
	
	break;

case ('ricCF'):

	if ($cls_db->getNumberRow($resultCF)==0)
	{echo"<script>alert('Codice Fiscale / Partita IVA non trovato.');self.close();</script>";}
	else
	{
		$j = 0;
		$i = 0; // contatore : serve per identificare righe pari e righe dispari
		?>

<table align=center cellspacing=0 border=0>
	<tr class = riga_pari style="height:35px;" >
        <td width=5% align=center></td>
        <td width=40% ><b>Utente</b></td>
        <td width=10% ><b>Tipo</b></td>
        <td width=1% align=center><br></td>
        <td width=5% align=center><b>ID</b></td>
        <td width=1% align=center><br></td>
        <td width=7% align=center><b>CC</b></td>
        <td width=1% align=right><br></td>
        <td width=30% ><b>CF / P.IVA</b></td>
	</tr>
<?php
//$forma = new forma_giuridica();
//$array_forma = $forma->array_completo();

$stringa = "SELECT * FROM forma_giuridica_societa WHERE CC = '*****'";
$data = $cls_db->ExecuteQuery($stringa);

//query = mysql_query($stringa);
$array_forma = array();

while($line = mysqli_fetch_array($data, MYSQLI_ASSOC))
{
  $array_forma[$line['ID']] = $line;
}

while($CDF_trovato = mysqli_fetch_array($resultCF, MYSQLI_ASSOC))
	{
		$add_tag = "";
		if($CDF_trovato['Genere']=='D')
      	{
      		$forma_descr = "";
      		if($CDF_trovato['Forma_Giuridica']!="")
      		{
      			$index_value = $CDF_trovato['Forma_Giuridica'];
      			$forma_descr = $array_forma[$index_value]['Sigla'];
      		}
      		$utente_visual = $CDF_trovato['Ditta']." ".$forma_descr;
      		$genere_visual = "Ditta";
      	}
      	else
      	{
      		$utente_visual = $CDF_trovato['Cognome']." ".$CDF_trovato['Nome'];
      		if($CDF_trovato['Genere']=='M')
      			$genere_visual = "Maschio";
      		else if($CDF_trovato['Genere']=='F')
      			$genere_visual = "Femmina";
      	}

         if ($i++ % 2)
         {$stile_riga = 'class="riga_pari"';}
         else
         {$stile_riga = 'class="riga_dispari"';}
         if($j==0)
         	$add_tag = "tabindex=1";
         else if($j == $cls_db->getNumberRow($resultCF) - 1)
         	$add_tag = "onblur=\"blurLast();\"";
?>
	<tr <?php echo $stile_riga ?>>
    	<td width=5% align=center>
    	<input type=image src="<?= IMMAGINIWEB; ?>/select.png" id=tab_<?php echo $j+1 ?> <?php echo $add_tag; ?> style="width:25px; height:25px; border:0;" title="Clicca qui per inserire l'utente"
               onClick="dettagliOgg = utente_all('<?php echo $CDF_trovato['ID']; ?>','<?php echo $CDF_trovato['CC_Comune']; ?>');torna_valore(dettagliOgg);"></td>
        <td align=left><?php echo $utente_visual; ?></td>
        <td align=left><?php echo $genere_visual; ?></td>
        <td align=center><br></td>
        <td align=center><?php echo $CDF_trovato['Comune_ID']; ?></td>
        <td align=center><br></td>
        <td align=center><?php echo $CDF_trovato['CC_Comune']; ?></td>
        <td align=center><br></td>
        <td align=left><?php echo $CDF_trovato['CF']; ?></td>
	</tr>
<?php $j++;}
}?>
</table> <?php

break;


case("ricPaese"):

if ($num_paesi==0)
{echo"<script>alert('Non � stato trovato nessun paese estero simile a \"$ric_paese\".');self.close();</script>";}
else
{
	$j = 0;
	$i = 0; // contatore : serve per identificare righe pari e righe dispari
?>

<table align=center cellspacing=0 border=0>
	<tr class = riga_pari style="height:35px;" >
		<td width=5%>&nbsp;</td>
		<td width=70%><b>Stato</b></td>
		<td width=5% align=center><br></td>
		<td width=15% align=center><b>Codice</b></td>
		<td width=5% align=center><br></td>
	</tr>
<?php while($paese_trovato = mysqli_fetch_array($resultPaese, MYSQLI_ASSOC))
{
		$add_tag = "";
		$paese_temp = addslashes($paese_trovato['Nome']);
		if ($i++ % 2)
        {$stile_riga = 'class="riga_pari"';}
        else
        {$stile_riga = 'class="riga_dispari"';}
        if($j==0)
        	$add_tag = "tabindex=1";
        else if($j == $num_paesi-1)
        	$add_tag = "onblur=\"blurLast();\"";
?>

	<tr <?php echo $stile_riga; ?>>
		<td width=5% align=center>
		<input type=image src="<?= IMMAGINIWEB; ?>/select.png" id=tab_<?php echo $j+1 ?> <?php echo $add_tag; ?> style="width:25px; height:25px; border:0;" title="Clicca qui per inserire il paese"
		onClick="paeseOgg = Paese('<?php echo $paese_temp; ?>','<?php echo $paese_trovato['CC_Paese_Estero']; ?>');torna_valore(paeseOgg);"></td>
		<td width=70%><?php echo $paese_trovato['Nome']; ?></td>
		<td width=5% align=center><br></td>
        <td width=15% align=center><?php echo $paese_trovato['CC_Paese_Estero']; ?></td>
        <td width=5% align=center><br></td>
	</tr>
<?php $j++;}?>
</table>
<?php }



break;

case('ricComune'):

	if ($num_comuni==0){	echo"<script>alert('Non � stato trovato nessun Ente simile a \"$ric_comune\".'); self.close();</script>";}
	else
	{
		$i = 0; // contatore : serve per identificare righe pari e righe dispari
		$j = 0;
?>

<table align=center cellspacing=0 border=0>
	<tr class = riga_pari style="height:35px;" >
		<td width=5%>&nbsp;</td>
		<td width=50%><b>Comune</b></td>
		<td width=5% align=center><br></td>
		<td width=15% align=center><b>Provincia</b></td>
		<td width=5% align=center><br></td>
		<td width=15% align=center><b>Codice</b></td>
		<td width=5% align=center><br></td>
	</tr>

<?php
        while($com_trovato = mysqli_fetch_array($resultComune))
        {
        	$add_tag = "";
        	$com_nome_temp = addslashes($com_trovato['Com_Nome']);
            if ($i++ % 2)
            {$stile_riga = 'class="riga_pari"';}
			else
			{$stile_riga = 'class="riga_dispari"';}
			if($j==0)
				$add_tag = "tabindex=1";
			else if($j == $num_comuni-1)
				$add_tag = "onblur=\"blurLast();\"";
?>


	<tr <?php echo $stile_riga; ?>>
		<td width=5% align=center>
		<input type=image src="<?= IMMAGINIWEB; ?>/select.png" id=tab_<?php echo $j+1 ?> <?php echo $add_tag; ?> style="width:25px; height:25px; border:0;" title="Clicca qui per inserire il comune"
		onClick="comuneOgg = Comune('<?php echo $com_nome_temp; ?>','<?php echo $com_trovato['Com_Codice_Catastale']; ?>','<?php echo $com_trovato['Pro_Sigla']; ?>','<?php echo $com_trovato['Com_Cap']; ?>');torna_valore(comuneOgg);"></td>
        <td width=50%><?php echo $com_trovato['Com_Nome']; ?></td>
		<td width=5% align=center><br></td>
		<td width=15% align=center><?php echo $com_trovato['Pro_Sigla']; ?></td>
		<td width=5% align=center><br></td>
		<td width=15% align=center><?php echo $com_trovato['Com_Codice_Catastale']; ?></td>
		<td width=5% align=center><br></td>
	</tr>

<?php
$j++;
        }?>
</table>
<?php }


break;

case('ricCap'):

	if ($num_cap==0){	echo"<script>alert('Non � stata trovata nessuna via con CAP'); self.close();</script>";	}
	else
	{
		$j = 0;
		$i = 0; // contatore : serve per identificare righe pari e righe dispari
		?>

<table align=center cellspacing=0 border=0>
	<tr class = riga_pari style="height:35px;" >
		<td width=5%>&nbsp;</td>
		<td width=40%><b>Indirizzo</b></td>
		<td width=5% align=center><br></td>
		<td width=30% align=center><b>Civici</b></td>
		<td width=5% align=center><br></td>
		<td width=10% align=center><b>CAP</b></td>
		<td width=5% align=center><br></td>
	</tr>

<?php
        while($cap_trovato = mysqli_fetch_array($resultCap, MYSQLI_ASSOC))
        {
        	$add_tag = "";
        	$odonimo_temp = addslashes($cap_trovato['Odonimo']);
            if ($i++ % 2)
            {$stile_riga = 'class="riga_pari"';}
			else
			{$stile_riga = 'class="riga_dispari"';}
			if($j==0)
				$add_tag = "tabindex=1";
			else if($j == $num_cap-1)
				$add_tag = "onblur=\"blurLast();\"";
?>

	<tr <?php echo $stile_riga; ?>>
		<td width=5% align=center>
		<input type=image src="<?= IMMAGINIWEB; ?>/select.png" id=tab_<?php echo $j+1 ?> <?php echo $add_tag; ?> style="width:25px; height:25px; border:0;" title="Clicca qui per inserire il comune"
		onClick="capOgg = Cap('cap','<?php echo $cap_trovato['Cap']; ?>','<?php echo $odonimo_temp; ?>','<?php echo $cap_trovato['ID']; ?>');torna_valore(capOgg);"></td>
        <td width=40%><?php echo $odonimo_temp; ?></td>
		<td width=5% align=center><br></td>
		<td width=30% align=center><?php echo $cap_trovato['Num_Civici']; ?></td>
		<td width=5% align=center><br></td>
		<td width=10% align=center><?php echo $cap_trovato['Cap']; ?></td>
		<td width=5% align=center><br></td>
	</tr>

<?php $j++;}?>
</table>
<?php }


break;

case ('ricIndirizzo'):

if ($num_via==0)
{echo"<script>alert('Non � stato trovato alcun indirizzo.');torna_valore('no_via');</script>";}
else
{
	$j = 0;
	$i = 0; // contatore : serve per identificare righe pari e righe dispari
?>

<table align=center cellspacing=0 border=0>
	<tr class = riga_pari style="height:35px;" >
		<td width=5%>&nbsp;</td>
		<td width=40%><b>Indirizzo</b></td>
		<td width=5% align=center><br></td>
		<td width=30%><b>Comune</b></td>
		<td width=5% align=center><br></td>
		<td width=15% align=center><b>CAP</b></td>
		<td width=5% align=center><br></td>
	</tr>

<?php
        while($via_trovata = mysqli_fetch_array($resultVia, MYSQLI_ASSOC))
        {
        	$add_tag = "";
        	$odonimo_temp = addslashes($via_trovata['Nome']);
        	$comune_temp = addslashes($via_trovata['Comune']);

            if ($i++ % 2)
            {$stile_riga = 'class="riga_pari"';}
			else
			{$stile_riga = 'class="riga_dispari"';}
			if($j==0)
				$add_tag = "tabindex=1";
			else if($j == $num_via-1)
				$add_tag = "onblur=\"blurLast();\"";
?>

	<tr <?php echo $stile_riga; ?>>
		<td width=5% align=center>
		<input type=image src="<?= IMMAGINIWEB; ?>/select.png" id=tab_<?php echo $j+1 ?> <?php echo $add_tag; ?> style="width:25px; height:25px; border:0;" title="Clicca qui per inserire il comune"
		onClick="viaOgg = Cap('via','<?php echo $via_trovata['Cap']; ?>','<?php echo $odonimo_temp; ?>','<?php echo $via_trovata['ID']; ?>');torna_valore(viaOgg);"></td>
        <td width=40%><?php echo $odonimo_temp; ?></td>
		<td width=5% align=center><br></td>
		<td width=30%><?php echo $comune_temp; ?></td>
		<td width=5% align=center><br></td>
		<td width=15% align=center><?php echo $via_trovata['Cap']; ?></td>
		<td width=5% align=center><br></td>
	</tr>

<?php $j++;}?>
</table>
<?php }

break;

case ('ricGruppo'):

	if( $posted == true )
	{
if ($num_varie==0){	echo"<script>alert('Non è stato trovato alcun risultato.');self.close();</script>"; }
else
{
	$j = 0;
	$i = 0; // contatore : serve per identificare righe pari e righe dispari
?>

<table align=center cellspacing=0 border=0>
	<tr class = riga_pari style="height:35px;" >
		<td width=15%>&nbsp;</td>
		<td width=80%><b>Descrizione</b></td>
		<td width=5% align=center><br></td>

	</tr>

<?php
        while( $dettagli_trovati = mysqli_fetch_array($resultGruppo, MYSQLI_ASSOC) )
        {
        	$add_tag = "";
        	$desc_temp = addslashes($dettagli_trovati['Descrizione']);

            if ($i++ % 2)
            {$stile_riga = 'class="riga_pari"';}
			else
			{$stile_riga = 'class="riga_dispari"';}
			if($j==0)
				$add_tag = "tabindex=1";
			else if($j == $num_varie-1)
				$add_tag = "onblur=\"blurLast();\"";
?>

	<tr <?php echo $stile_riga; ?>>
		<td width=15% align=center>
		<input type=image src="<?= IMMAGINIWEB; ?>/select.png" id=tab_<?php echo $j+1 ?> <?php echo $add_tag; ?> style="width:25px; height:25px; border:0;" title="Clicca qui per inserire il valore"
		onClick="dettagliOgg = Dettagli('<?php echo $dettagli_trovati['ID']; ?>','<?php echo $desc_temp; ?>');torna_valore(dettagliOgg);"></td>
        <td width=80%><?php echo $dettagli_trovati['Descrizione']; ?></td>
		<td width=5% align=center><br></td>
	</tr>

<?php $j++;}?>
</table>
<?php }
	}

break;



default:
break;
}

}

echo $layout;

?>

</body>
</html>
