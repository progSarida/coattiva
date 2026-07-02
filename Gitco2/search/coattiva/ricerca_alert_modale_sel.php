<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include_once INC . "/headerAjax.php";
include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS . "/cls_DateTimeInLine.php";

$cls_db = new cls_db();
$cls_help = new cls_help();
$cls_date = new cls_DateTimeI("IT",false);

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
  header("Location:accesso_negato.php");
  die;
}

$richiesta = $cls_help->getVar('richiesta');
$posted = $cls_help->getVar('posted');
$c = $cls_help->getVar('c');
$a = $cls_help->getVar('a');

$settore = $cls_help->getVar('settore');
$sottosettore = $cls_help->getVar('sottosettore');

function array_completo( $c = "*****" )
{
  $cls_db = new cls_db();

  $query = "SELECT * FROM forma_giuridica_societa WHERE CC = '".$c."'";
  $result = $cls_db->ExecuteQuery($query);// mysql_query();
  $results = array();

  while($line = mysqli_fetch_array($result, MYSQLI_ASSOC))
  {
    $results[$line['ID']] = $line;
  }

  return $results;

}


switch ($richiesta)
{
	case ('generale'):

		$titolopagina = "Ricerca Utente/Partita";

		$nomecella = array();
		$nomecella[0] = "<b>Utente</b> - Cognome Nome / Ditta";
		$nomecella[1] = "<b>Utente</b> - Codice Fiscale / Partita Iva";
		$nomecella[2] = "<b>Partita</b> - Cronologico atto";
		$nomecella[3] = "<b>Partita</b> - Cronologico pignoramento";
		$nomecella[4] = "<b>Partita</b> - Informazioni cartella";

		$cella = array();
		$cella[0] = "<input type=radio name=tipo value=ricUtente checked>";
		$cella[1] = "<input type=radio name=tipo value=ricCF>";
		$cella[2] = "<input type=radio name=tipo value=ricCrono>";
		$cella[3] = "<input type=radio name=tipo value=ricPigno>";
		$cella[4] = "<input type=radio name=tipo value=ricInfoCart>";

		$campo = "";
		$nomecampo = "";
		$riga = "";

		break;

	case ('partita_gen'):

		$titolopagina = "Ricerca Partita";

		$nomecella = array();
		$nomecella[0] = "<b>Informazioni cartella</b>";
		$nomecella[1] = "<b>Cronologico atto</b>";

		$cella = array();
		$cella[0] = "<input type=radio name=tipo value=ricInfoCart checked>";
		$cella[1] = "<input type=radio name=tipo value=ricCronoAtto>";

		$campo = "";
		$nomecampo = "";
		$riga = "";

		break;

	case ("ricCrono"):

		$titolopagina = "Ricerca Cronologico";
		$linkricerca = "ricerca_alert_modale_sel.php?richiesta=ricCrono&posted=true&c=".$c."&a=".$a;

		$nomecella = array();
		$nomecella[0] = "<b>Protocollo</b>";
		$nomecella[1] = "<b>Cronologico</b>";
		$nomecella[2] = "<b>Anno</b>";

		$cella = array();
		$cella[0] = "<input type=text name=proto value='' size=7 id=proto>";
		$cella[1] = "<input type=text class='text_right' name=crono value='' size=7 id=crono>";
		$cella[2] = "<input type=text class='text_right' name=anno value='' size=7 id=anno>";

		$campo = "";
		$nomecampo = "";
		$riga = "";

		if( $posted == true )
		{
			$id_proto = $cls_help->getVar('proto');
			$id_crono = $cls_help->getVar('crono');
			$anno_crono  = $cls_help->getVar('anno');

			$query = "SELECT DISTINCT PA.ID, PA.Anno_Riferimento, PA.Comune_ID, AT.Info_Cartella, AT.ID AS ID_Atto FROM partita_tributi as PA , atto as AT ";
            $query.= "WHERE AT.Partita_ID = PA.ID AND PA.CC = '".$c."' ";
			if($anno_crono!=null)
                $query.= "AND AT.Anno_Cronologico = '".$anno_crono."' ";
            if($id_crono!=null)
                $query.= "AND AT.ID_Cronologico = '".$id_crono."' ";
            if($id_proto!=null)
                $query.= "AND AT.Protocollo = '".$id_proto."' ";

			$resultCrono = $cls_db->ExecuteQuery($query); // safe_query($query);
		}

		break;

	case ("ricPigno"):

		$titolopagina = "Ricerca Pignoramento";
		$linkricerca = "ricerca_alert_modale_sel.php?richiesta=ricPigno&posted=true&c=".$c."&a=".$a;

		$nomecella = array();
		$nomecella[0] = "<b>Protocollo</b>";
		$nomecella[1] = "<b>Cronologico</b>";
		$nomecella[2] = "<b>Anno</b>";

		$cella = array();
		$cella[0] = "<input type=text name=proto value='' size=7 id=proto>";
		$cella[1] = "<input type=text class='text_right' name=crono value='' size=7 id=crono>";
		$cella[2] = "<input type=text class='text_right' name=anno value='' size=7 id=anno>";

		$campo = "";
		$nomecampo = "";
		$riga = "";

		if( $posted == true )
		{
			$id_proto = $cls_help->getVar('proto');
			$id_crono = $cls_help->getVar('crono');
			$anno_crono  = $cls_help->getVar('anno');

			$query = "SELECT DISTINCT PA.ID, PA.Anno_Riferimento, PA.Comune_ID, PG.Tipo, PG.Tipo_Terzi, PG.ID AS ID_Pigno, PG.ID_Cronologico AS ID_Crono, PG.Anno_Cronologico AS Anno_Crono FROM partita_tributi as PA , pignoramento_generale as PG ";
			$query.= "WHERE PG.Partita_ID = PA.ID ";
			if($anno_crono!=null)
				$query.= "AND PG.Anno_Cronologico = '".$anno_crono."' ";
			if($id_crono!=null)
				$query.= "AND PG.ID_Cronologico = '".$id_crono."' ";
			if($id_proto!=null)
				$query.= "AND PG.Protocollo = '".$id_proto."' ";
			$query.= "AND PA.CC = '".$c."' ORDER BY Anno_Crono ASC, ID_Crono ASC";

			$resultPigno = $cls_db->ExecuteQuery($query);// safe_query($query);
		}

		break;

	case ("ricInfoCart"):

		$titolopagina = "Ricerca Informazioni Cartella";
		$linkricerca = "ricerca_alert_modale_sel.php?richiesta=ricInfoCart&posted=true&c=".$c."&a=".$a;

		$nomecella = array();
		$nomecella[0] = "<b>Informazioni cartella</b>";

		$cella = array();
		$cella[0] = "<input type=text name=ric_info value='' size=20 id=info_cartella>";

		$campo = "";
		$nomecampo = "";
		$riga = "";

		if( $posted == true )
		{
			$ric_info= $cls_help->getVar('ric_info');

			$query = "SELECT DISTINCT PA.ID, PA.Anno_Riferimento, PA.Comune_ID, TR.Info_Cartella FROM partita_tributi as PA , tributo as TR ";
			$query.= "WHERE TR.Partita_ID = PA.ID AND TR.Info_Cartella LIKE '% ".addslashes($ric_info)."%' AND PA.CC = '".$c."' ";

			$resultInfo = $cls_db->ExecuteQuery($query);// safe_query($query);
		}

		break;

	case ('gen_ruolo'):

			$titolopagina = "Ricerca Ruolo";

			$nomecella = array();
			$nomecella[0] = "<b>Descrizione</b>";
			$nomecella[1] = "<b>Anno Fornitura</b>";

			$cella = array();
			$cella[0] = "<input type=radio name=tipo value=ricDescRuolo checked>";
			$cella[1] = "<input type=radio name=tipo value=ricDataRuolo>";

			$campo = "";
			$nomecampo = "";
			$riga = "";


		break;

	case ('codice'):

			$settore = $cls_help->getVar('tipo');
			$sottosettore = $cls_help->getVar('sottotipo');
			$titolopagina = "Ricerca Codice Tributo";

			$nomecella = array();
			$nomecella[0] = "<b>Descrizione</b>";
			$nomecella[1] = "<b>Codice Tributo</b>";

			$cella = array();
			$cella[0] = "<input type=radio name=tipo value=ricDescCod checked>";
			$cella[1] = "<input type=radio name=tipo value=ricCodice>";

			$campo = "";
			$nomecampo = "";
			$riga = "";


			break;

	case ("ricUtente"):

		 $titolopagina = "Ricerca Utente";
		  $linkricerca = "ricerca_alert_modale_sel.php?richiesta=ricUtente&posted=true&c=".$c."&a=".$a;

		    $nomecella = array();
	     $nomecella[0] = "<b>Cognome/Ditta</b>";
         $nomecella[1] = "<b>Nome</b>";
         $nomecella[2] = "<b>Selezione</b>";

	            $cella = array();
		     $cella[0] = "<input id=last_name type=text name=last_name value='' size=40>";
             $cella[1] = "<input id=name type=text name=name value='' size=40>";
             $cella[2] = '<select name="type_sel" id="type_sel" style="width: 100%;">
                              <option value="all">Tutti</option>
                              <option value="person">Persona fisica</option>
                              <option value="business">Ditta</option>
                            </select>';

		     $campo = "";
		     $nomecampo = "";
		     $riga = "";

		if( $posted == true )
		{
			$a = $cls_help->getVar('a');
			$last_name_ditta = $cls_help->getVar('last_name');
            $name = $cls_help->getVar('name');
            $type_sel = $cls_help->getVar('type_sel');

			if($last_name_ditta==null)$last_name="";
            if($name == null) $name = "";
            if($type_sel == "all"){
                $nomeCognome = " ( Cognome like '%".addslashes($last_name_ditta)."%' and Nome like '%".addslashes($name)."%' AND Cognome != '' AND Nome != '' ) or ( Ditta like '%".addslashes($last_name_ditta)."%' AND Ditta != '') ";
            }
            else if($type_sel == "person"){
                $nomeCognome = " Cognome like '%".addslashes($last_name_ditta)."%' and Nome like '%".addslashes($name)."%' AND Cognome != '' AND Nome != '' ";
            }
            else $nomeCognome = " Ditta like '%".addslashes($last_name_ditta)."%' AND Ditta != '' ";

            /** GV - 16/06/2022 - START 
			 * 	$query = "SELECT DISTINCT utente.ID, partita_tributi.Utente_ID, IF(Codice_Fiscale = '' or Codice_Fiscale is null, Partita_Iva, Codice_Fiscale )  AS CF , Cognome, Nome, Genere AS Genere, Ditta, Forma_Giuridica, IF( Ditta = '' or Ditta is null, Cognome, Ditta ) AS utente_nome FROM utente, partita_tributi ";
			 * */
			
			$query = "SELECT DISTINCT utente.ID, utente.Comune_ID, partita_tributi.Utente_ID, IF(Codice_Fiscale = '' or Codice_Fiscale is null, Partita_Iva, Codice_Fiscale )  AS CF , Cognome, Nome, Genere AS Genere, Ditta, Forma_Giuridica, IF( Ditta = '' or Ditta is null, Cognome, Ditta ) AS utente_nome FROM utente, partita_tributi ";

			/** GV - 16/06/2022 -   END */
						
            $query.= "WHERE 1 = 1 AND (".$nomeCognome.") AND utente.ID = partita_tributi.Utente_ID AND CC_Comune='".$c."' ";

			/*$termine = explode(" ", $last_name);

			$nomeCognome = "Cognome like '%".addslashes($last_name)."%' and Nome like '%' or ";
			$nomeCognome .= "Cognome like '%' and Nome like '%".addslashes($last_name)."%' or ";

			$ditta = "Ditta like '%".addslashes($last_name)."%' or ";

			for($i=0; $i<count($termine); $i++)
			{
				$ditta = "Ditta like '%".addslashes($termine[$i])."%' or ";
				if(count($termine) == 1)
				{
					$nomeCognome .= "Cognome like '%".addslashes($termine[$i])."%' and Nome like '%' or ";
					$nomeCognome .= "Cognome like '%' and Nome like '%".addslashes($termine[$i])."%' or ";
				}
				else
				{
					for($y=0; $y<count($termine); $y++)
					{
						if($i!=$y)
						{
							$nomeCognome .= "Cognome like '%".addslashes($termine[$i])."%' and Nome like '%".addslashes($termine[$y])."%' or ";
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
			}*/

			/*$query = "(SELECT DISTINCT utente.ID, partita_tributi.Utente_ID, Codice_Fiscale AS CF, Cognome, Nome, Genere AS Genere, Ditta, Forma_Giuridica, Cognome AS utente_nome FROM utente, partita_tributi ";
			$query.= "WHERE Cognome != '' AND (".$nomeCognome.") AND utente.ID = partita_tributi.Utente_ID AND CC_Comune='".$c."') ";
			$query.= "UNION ";
			$query.= "(SELECT DISTINCT utente.ID, partita_tributi.Utente_ID, Partita_Iva AS CF, Cognome, Nome, Genere AS Genere, Ditta, Forma_Giuridica, Ditta AS utente_nome FROM utente, partita_tributi ";
			$query.= "WHERE Ditta != '' AND (".$ditta.") AND utente.ID = partita_tributi.Utente_ID AND CC_Comune='".$c."') ";
			$query.= "ORDER BY utente_nome, Nome";*/

            //echo $query;
            //die;
			$resultContr = $cls_db->ExecuteQuery($query);// safe_query($query);
			$numero_contrib = $cls_db->getNumberRow($resultContr);// mysql_num_rows($resultContr);

		}

		break;

	case ("ricCF"):

			$titolopagina = "Ricerca Codice Fiscale";
			$linkricerca = "ricerca_alert_modale_sel.php?richiesta=ricCF&posted=true&c=".$c."&a=".$a;

			$nomecella = array();
			$nomecella[0] = "<b>Codice fiscale / P.IVA</b>";

			$cella = array();
			$cella[0] = "<input type=text name=ric_CDF value='' size=20 id=codice_fiscale>";

			$campo = "";
			$nomecampo = "";
			$riga = "";

			if( $posted == true )
			{
				$ric_CDF = $cls_help->getVar('ric_CDF');
				/** GV - 16/06/2022 - START 
				 * 	$query = "(SELECT DISTINCT utente.ID, partita_tributi.Utente_ID, Codice_Fiscale AS CF, Cognome, Nome, Genere AS Genere, Ditta, Forma_Giuridica, Cognome AS utente_nome FROM utente, partita_tributi ";
				 * */
				$query = "(SELECT DISTINCT utente.ID, utente.Comune_ID, partita_tributi.Utente_ID, Codice_Fiscale AS CF, Cognome, Nome, Genere AS Genere, Ditta, Forma_Giuridica, Cognome AS utente_nome FROM utente, partita_tributi ";
				/** GV - 16/06/2022 -   END */
				$query.= "WHERE Genere != 'D' AND (Codice_Fiscale like '".$ric_CDF."%' and CC_Comune='".$c."') AND utente.ID = partita_tributi.Utente_ID ) ";
				$query.= "UNION ";
				/** GV - 16/06/2022 - START 
				 * $query.= "(SELECT DISTINCT utente.ID, partita_tributi.Utente_ID, Partita_Iva AS CF, Cognome, Nome, Genere AS Genere, Ditta, Forma_Giuridica, Ditta AS utente_nome FROM utente, partita_tributi ";
				 * */
				$query.= "(SELECT DISTINCT utente.ID, utente.Comune_ID, partita_tributi.Utente_ID, Partita_Iva AS CF, Cognome, Nome, Genere AS Genere, Ditta, Forma_Giuridica, Ditta AS utente_nome FROM utente, partita_tributi ";
				/** GV - 16/06/2022 -   END */
				$query.= "WHERE Genere = 'D' AND (Partita_Iva like '".$ric_CDF."%' and CC_Comune='".$c."') AND utente.ID = partita_tributi.Utente_ID ) ";
				$query.= "ORDER BY CF";

				$resultCF = $cls_db->ExecuteQuery($query);// safe_query($query);
			}

		break;

		case ("ricDescRuolo"):

			$titolopagina = "Ricerca Ruolo";
			$linkricerca = "ricerca_alert_modale_sel.php?richiesta=ricDescRuolo&posted=true&c=".$c;

			$nomecella = array();
			$nomecella[0] = "<b>Descrizione</b>";

			$cella = array();
			$cella[0] = "<input type=text name=ric_Ruolo value='' size=20 id=ruolo_desc >";

			$campo = "";
			$nomecampo = "";
			$riga = "";

			if( $posted == true )
			{
				$ric_Ruolo = $cls_help->getVar('ric_Ruolo');

				$query = "SELECT * FROM ruolo WHERE Descrizione LIKE '%".addslashes($ric_Ruolo)."%' AND CC = '".$c."'";

				$resultRuolo = $cls_db->ExecuteQuery($query);
			}

			break;

		case ("ricDataRuolo"):

				$titolopagina = "Ricerca Ruolo";
				$linkricerca = "ricerca_alert_modale_sel.php?richiesta=ricDataRuolo&posted=true&c=".$c;

				$nomecella = array();
				$nomecella[0] = "<b>Anno Fornitura</b>";

				$cella = array();
				$cella[0] = "<input type=text name=ric_Ruolo value='' size=20 id=ruolo_anno >";

				$campo = "";
				$nomecampo = "";
				$riga = "";

				if( $posted == true )
				{
					$ric_Ruolo = $cls_help->getVar('ric_Ruolo');

					$query = "SELECT * FROM ruolo WHERE Data_Fornitura LIKE '%".addslashes($ric_Ruolo)."%' AND CC = '".$c."' ORDER BY Data_Fornitura";

					$resultRuoloAnno = $cls_db->ExecuteQuery($query);//safe_query($query);
				}

			break;

			case ("ricDescCod"):

				$titolopagina = "Ricerca Codice Tributo";
				$linkricerca = "ricerca_alert_modale_sel.php?richiesta=ricDescCod&posted=true&c=".$c."&settore=".$settore."&sottosettore=".$sottosettore;

				$nomecella = array();
				$nomecella[0] = "<b>Descrizione</b>";

				$cella = array();
				$cella[0] = "<input type=text name=ric_Codice value='' size=20 id=cod_desc >";

				$campo = "";
				$nomecampo = "";
				$riga = "";

				if( $posted == true )
				{
					$ric_Codice = $cls_help->getVar('ric_Codice');

					$query = "SELECT * FROM codice_tributo WHERE ( ( Settore = '".$settore."' ";

					if($sottosettore!="")
						$query.= " AND ( Sottosettore = '".$sottosettore."' OR Sottosettore = '' ) ";
					$query.=" ) OR Settore='SARIDA' AND Disabled!='Y' ";

					$query.= " ) AND Descrizione LIKE '%".$ric_Codice."%' ORDER BY Descrizione";

					$resultDescCod = $cls_db->ExecuteQuery($query);//safe_query($query);
				}

				break;

			case ("ricCodice"):

				$titolopagina = "Ricerca Codice Tributo";
				$linkricerca = "ricerca_alert_modale_sel.php?richiesta=ricCodice&posted=true&c=".$c."&settore=".$settore."&sottosettore=".$sottosettore;

				$nomecella = array();
				$nomecella[0] = "<b>Codice</b>";

				$cella = array();
				$cella[0] = "<input type=text name=ric_Codice value='' size=20 id=codice >";

				$campo = "";
				$nomecampo = "";
				$riga = "";

				if( $posted == true )
				{
					$ric_Codice = $cls_help->getVar('ric_Codice');

					$query = "SELECT * FROM codice_tributo WHERE ( ( Settore = '".$settore."' ";

					if($sottosettore!="")
                        $query.= " AND ( Sottosettore = '".$sottosettore."' OR Sottosettore = '' ) ";
                    $query.=" ) OR Settore='SARIDA' AND Disabled!='Y' ";
                    $query.= " ) AND Codice_Tributo LIKE '%".$ric_Codice."%' ORDER BY Codice_Tributo";

					$resultCodice = $cls_db->ExecuteQuery($query);//safe_query($query);
				}

				break;

			case ("listaCodice"):

					$titolopagina = "Lista Codici Tributo";
					$linkricerca = "ricerca_alert_modale_sel.php?richiesta=ricCodice&posted=true&c=".$c;

					$nomecella = array();
					$nomecella[0] = "<b>Codice</b>";

					$cella = array();
					$cella[0] = "<input type=text name=ric_Codice value='' size=20 id=codice >";

					$campo = "";
					$nomecampo = "";
					$riga = "";

					if( $posted == true )
					{
						$ric_Codice =  $cls_help->getVar('ric_Codice');

						$query = "SELECT * FROM codice_tributo WHERE Codice_Tributo LIKE '%".$ric_Codice."%' ORDER BY Codice_Tributo";

						$resultCodice = $cls_db->ExecuteQuery($query);//safe_query($query);
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
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- Keep the http-equiv meta tag for IE8 -->
<meta http-equiv="X-UA-Compatible" content="IE=8" />

<title>Anagrafe</title>

	<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>

	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js"></script>
	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js"></script>
  	<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js"></script>


  	<script>

        var richiesta = "<?php echo $richiesta; ?>";

//       window.Owner = Application.Current.MainWindow;
//       window.opener.callParent();

        	function GeneraLinkPagina(richiesta)
			{
				var link = "<?php if(isset($linkricerca)){echo $linkricerca;}else{echo "";} ?>";

				switch (richiesta)
				{

				case ("generale"):

					var radio = $('[name=tipo]:checked').val();

					link = "ricerca_alert_modale_sel.php?richiesta="+radio+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";


					break;

				case ("gen_ruolo"):

					var radio = $('[name=tipo]:checked').val();

					link = "ricerca_alert_modale_sel.php?richiesta="+radio+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";


					break;

				case ("partita_gen"):

					var radio = $('[name=tipo]:checked').val();

					link = "ricerca_alert_modale.php?richiesta="+radio+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";


					break;

				case ("ricInfoCart"):

					var infoCartella = $("#info_cartella").val();

					link +="&ric_info="+infoCartella;

				break;

				case ("ricCrono"):

					var idCrono = $("#crono").val();
					var idProto = $("#proto").val();
					var annoCrono = $("#anno").val();

					link +="&crono="+idCrono+"&proto="+idProto+"&anno="+annoCrono;

				break;

				case ("ricPigno"):

					var idCrono = $("#crono").val();
					var idProto = $("#proto").val();
					var annoCrono = $("#anno").val();

					link +="&crono="+idCrono+"&proto="+idProto+"&anno="+annoCrono;

				break;

				case ("codice"):

					var radio = $('[name=tipo]:checked').val();

					link = "ricerca_alert_modale_sel.php?richiesta="+radio+"&c=<?php echo $c; ?>&a=<?php echo $a; ?>";
					link +="&settore=<?php echo $settore; ?>";
					link +="&sottosettore=<?php echo $sottosettore; ?>";

					break;

				case ("ricUtente"):

					var cognomeRic = $("#last_name").val();
                    var nomeRic = $("#name").val();
                    var typeRic = $("#type_sel").val();

					link +="&last_name="+cognomeRic+"&name="+nomeRic+"&type_sel="+typeRic;

				break;

				case ("ricCF"):

					var codice_fiscale = $("#codice_fiscale").val();

					link +="&ric_CDF="+codice_fiscale;

				break;

				case ("ricDescRuolo"):

					var ruolo_desc = $("#ruolo_desc").val();

					link +="&ric_Ruolo="+ruolo_desc;


					break;

				case ("ricDataRuolo"):

					var ruolo_anno = $("#ruolo_anno").val();

					link +="&ric_Ruolo="+ruolo_anno;


					break;

				case ("ricDescCod"):

					var cod_desc = $("#cod_desc").val();

					link +="&ric_Codice="+cod_desc;



					break;

				case ("ricCodice"):

					var codice = $("#codice").val();

					link +="&ric_Codice="+codice;



					break;


				}

                location.href = link;



			}

function torna_valore(value)
{
    try{
        window.opener.callParent(value);
    }
    catch(e){
        //alert(e);
        alert(e.description);
    }

	self.close();
}

function partita_ogg( value1, value2, value3 )
{
	ricerca_oggetto = { ID:value1, Anno:value2, ID_Atto:value3 } ;

    return ricerca_oggetto;
}

function ruolo_ogg( value1, value2, value3, value4, value5, value6 )
{
	ricerca_oggetto = { ID:value1, Descrizione:value2, Data:value3, Tipo:value4, Num_Rate:value5, Num_Ruolo:value6 } ;

    return ricerca_oggetto;
}

function codice_ogg( value1, value2, value3 )
{
	ricerca_oggetto = { ID:value1, Descrizione:value2, Codice:value3 } ;

    return ricerca_oggetto;
}

       </script>
</head>

<body class="sfondo_new_gitco">
	<center>

	<h3><b><?php echo $titolopagina; ?></b></h3>

	</center>

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
		<input class="ricerca" type=submit name="cerca" value="Cerca" onClick="GeneraLinkPagina(richiesta);">
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

if ( $cls_db->getNumberRow($resultContr)==0)
{echo"<script>alert('Contribuente non trovato.'); self.close();</script>";}
else
{
   	$i = 0; // contatore : serve per identificare righe pari e righe dispari
?>

<!-- RICERCA CONTRIBUENTE -->
<table align=center cellspacing=0 border=0>
	<tr class = riga_pari style="height:35px;" >
    	<td width=5% align=center></td>
        <td width=40% ><b>Utente</b></td>
        <td width=10% ><b>Tipo</b></td>
        <td width=5% align=center><br></td>
        <td width=5% align=right><b>ID</b></td>
        <td width=5% align=center><br></td>
        <td width=30% ><b>CF / P.IVA</b></td>
	</tr>
<?php
//$forma = new forma_giuridica();
$array_forma = array_completo();

while($contr = mysqli_fetch_array($resultContr, MYSQLI_ASSOC))
{
	$add_tag = "";
	if($contr['Genere']=='D')
	{
		$forma_descr = "";
		if($contr['Forma_Giuridica']!="")
		{
			$index_value = $contr['Forma_Giuridica'];
			if(isset($array_forma[$index_value]['Sigla']))
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
?>
	<tr <?php echo $stile_riga ?>>
    	<td class="text_center width5">
    		<input type=image src="<?= IMMAGINIWEB; ?>/select.png" style="width:25px; height:25px; border:0;"
    		title="Clicca qui per inserire l'utente" onClick="torna_valore('<?php echo $contr['ID']; ?>');">
    	</td>
        <td width=40% align=left><?php echo $utente_visual; ?></td>
        <td width=10% align=left><?php echo $genere_visual; ?></td>
        <td width=5% align=center><br></td>
		<!--  GV - 16/06/2022 - START  
        <td width=5% align=right><?php // echo  $contr['ID']; ?></td>
		-->
		<td width=5% align=right><?php echo $contr['Comune_ID']; ?></td>
		<!--  GV - 16/06/2022 -   END -->
        <td width=5% align=center><br></td>
        <td width=30% align=left><?php echo $contr['CF']; ?></td>
	</tr>
<?php }
		}?>
</table> <?php

break;


case ('ricCF'):

	if (mysqli_num_rows($resultCF)==0)
	{echo"<script>alert('Codice Fiscale / Partita IVA non trovato.');self.close();</script>";}
	else
	{
		$i = 0; // contatore : serve per identificare righe pari e righe dispari
		?>

<table align=center cellspacing=0 border=0>
	<tr class = riga_pari style="height:35px;" >
    	<td width=5% align=center></td>
    	<td width=28% ><b>CF / P.IVA</b></td>
    	<td width=5% align=center><br></td>
        <td width=27% ><b>Utente</b></td>
        <td width=10% ><b>Tipo</b></td>
        <td width=5% align=center><br></td>
        <td width=5% align=right><b>ID</b></td>
        <td width=5% align=center><br></td>
	</tr>
<?php

//$forma = new forma_giuridica();
$array_forma = array_completo();

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
?>
	<tr <?php echo $stile_riga ?>>
    	<td class="text_center width5"><input type=image src="<?= IMMAGINIWEB; ?>/select.png" style="width:25px; height:25px; border:0;" title="Clicca qui per inserire l'utente" onClick="torna_valore('<?php echo $CDF_trovato['ID']; ?>');"></td>
        <td width=28% align=left><?php echo $CDF_trovato['CF']; ?></td>
        <td width=5% align=center><br></td>
        <td width=37% align=left><?php echo $utente_visual; ?></td>
        <td width=10% align=left><?php echo $genere_visual; ?></td>
        <td width=5% align=center><br></td>
		<!--  GV - 16/06/2022 - START 
        <td width=5% align=right><?php // echo $CDF_trovato['ID']; ?></td>
		-->
		<td width=5% align=right><?php echo $CDF_trovato['Comune_ID']; ?></td>
		<!--  GV - 16/06/2022 -   END -->  
        <td width=5% align=center><br></td>
	</tr>
<?php }
}?>
</table> <?php

break;

case ('ricDescRuolo'):

	if ($cls_db->getNumberRow($resultRuolo)==0)
	{echo"<script>alert('Descrizione ruolo non trovata.');self.close();</script>";}
	else
	{
		$i = 0; // contatore : serve per identificare righe pari e righe dispari
		?>

<table align=center cellspacing=0 border=0>
	<tr class = riga_pari style="height:35px;" >
    	<td width=5% align=center></td>
    	<td width=50% ><b>Descrizione</b></td>
        <td width=20% ><b>Data</b></td>
        <td width=10% ><b>Tipo</b></td>
        <td width=5% align=center><br></td>
        <td width=5% align=right><b>ID</b></td>
        <td width=5% align=center><br></td>
	</tr>
<?php while($Ruolo_trovato = mysqli_fetch_array($resultRuolo, MYSQLI_ASSOC))
	{


         if ($i++ % 2)
         {$stile_riga = 'class="riga_pari"';}
         else
         {$stile_riga = 'class="riga_dispari"';}
?>
	<tr <?php echo $stile_riga ?>>
    	<td width=5% align=center><input type=image src="<?= IMMAGINIWEB; ?>/select.png" style="width:25px; height:25px; border:0;" title="Clicca qui per inserire l'utente"
    	onClick="ruolo = ruolo_ogg('<?php echo $Ruolo_trovato['ID']; ?>','<?php echo $Ruolo_trovato['Descrizione']; ?>','<?php echo $cls_date->Get_DateNewFormat($Ruolo_trovato['Data_Fornitura'],"DB"); ?>','<?php echo $Ruolo_trovato['Ruolo']; ?>','<?php echo $Ruolo_trovato['Num_Rate']; ?>','<?php echo $Ruolo_trovato['Num_Ruolo']; ?>'); torna_valore(ruolo);"></td>
        <td width=50% align=left><?php echo $Ruolo_trovato['Descrizione']; ?></td>
        <td width=20% align=left><?php echo $cls_date->Get_DateNewFormat($Ruolo_trovato['Data_Fornitura'],"DB"); ?></td>
        <td width=10% align=left><?php echo $Ruolo_trovato['Ruolo']; ?></td>
        <td width=5% align=center><br></td>
        <td width=5% align=right><?php echo $Ruolo_trovato['ID']; ?></td>
        <td width=5% align=center><br></td>
	</tr>
<?php }
}?>
</table> <?php

break;

case ('ricDataRuolo'):

	if ($cls_db->getNumberRow($resultRuoloAnno)==0)
	{echo"<script>alert('Descrizione ruolo non trovata.');self.close();</script>";}
	else
	{
		$i = 0; // contatore : serve per identificare righe pari e righe dispari
		?>

<table align=center cellspacing=0 border=0>
	<tr class = riga_pari style="height:35px;" >
    	<td width=5% align=center></td>
    	<td width=50% ><b>Descrizione</b></td>
        <td width=20% ><b>Data</b></td>
        <td width=10% ><b>Tipo</b></td>
        <td width=5% align=center><br></td>
        <td width=5% align=right><b>ID</b></td>
        <td width=5% align=center><br></td>
	</tr>
<?php while($Ruolo_trovato = mysqli_fetch_array($resultRuoloAnno, MYSQLI_ASSOC))
	{


         if ($i++ % 2)
         {$stile_riga = 'class="riga_pari"';}
         else
         {$stile_riga = 'class="riga_dispari"';}
?>
	<tr <?php echo $stile_riga ?>>
    	<td width=5% align=center><input type=image src="<?= IMMAGINIWEB; ?>/select.png" style="width:25px; height:25px; border:0;" title="Clicca qui per inserire l'utente"
    	onClick="ruolo = ruolo_ogg('<?php echo $Ruolo_trovato['ID']; ?>','<?php echo $Ruolo_trovato['Descrizione']; ?>','<?php echo $cls_date->Get_DateNewFormat($Ruolo_trovato['Data_Fornitura'],"DB"); ?>','<?php echo $Ruolo_trovato['Ruolo']; ?>','<?php echo $Ruolo_trovato['Num_Rate']; ?>','<?php echo $Ruolo_trovato['Num_Ruolo']; ?>'); torna_valore(ruolo);"></td>
        <td width=50% align=left><?php echo $Ruolo_trovato['Descrizione']; ?></td>
        <td width=20% align=left><?php echo $cls_date->Get_DateNewFormat($Ruolo_trovato['Data_Fornitura'],"DB"); ?></td>
        <td width=10% align=left><?php echo $Ruolo_trovato['Ruolo']; ?></td>
        <td width=5% align=center><br></td>
        <td width=5% align=right><?php echo $Ruolo_trovato['ID']; ?></td>
        <td width=5% align=center><br></td>
	</tr>
<?php }
}?>
</table> <?php

break;

case ('ricDescCod'):

	if ($cls_db->getNumberRow($resultDescCod)==0)
	{echo"<script>alert('Descrizione codice non trovata.');self.close();</script>";}
	else
	{
		$i = 0; // contatore : serve per identificare righe pari e righe dispari
		?>

<table align=center cellspacing=0 border=0>
	<tr class = riga_pari style="height:35px;" >
    	<td class="width5 text_center"></td>
    	<td class="width5" ><b>Codice</b></td>
    	<td class="width1 text_center"><br></td>
    	<td class="width10" ><b>Settore</b></td>
    	<td class="width1 text_center"><br></td>
    	<td class="width35 text_left" ><b>Descrizione</b></td>
        <td class="width1 text_center"><br></td>
        <td class="width20 text_left" ><b>Tipo codice</b></td>
        <td class="width1 text_center"><br></td>
        <td class="width20 text_left" ><b>Autorita'</b></td>
        <td class="width1 text_center"><br></td>
	</tr>
<?php while($Codice_trovato = mysqli_fetch_array($resultDescCod, MYSQLI_ASSOC))
	{


         if ($i++ % 2)
         {$stile_riga = 'class="riga_pari"';}
         else
         {$stile_riga = 'class="riga_dispari"';}
?>
	<tr <?php echo $stile_riga ?>>
    	<td class="width5 text_center">
    	<input type=image src="<?= IMMAGINIWEB; ?>/select.png" style="width:25px; height:25px; border:0;" title="Clicca qui per inserire l'utente"
    	onClick="codice = codice_ogg('<?php echo $Codice_trovato['ID']; ?>','<?php echo addslashes($Codice_trovato['Descrizione']); ?>','<?php echo $Codice_trovato['Codice_Tributo']; ?>'); torna_valore(codice);">
    	</td>
    	<td class="width5" ><?php echo $Codice_trovato['Codice_Tributo']; ?></td>
    	<td class="width1 text_center"><br></td>
    	<td class="width10" ><?php echo $Codice_trovato['Settore']; ?></td>
    	<td class="width1 text_center"><br></td>
    	<td class="width35 text_left" ><?php echo $Codice_trovato['Descrizione']; ?></td>
        <td class="width1 text_center"><br></td>
        <td class="width20 text_left" ><?php echo $Codice_trovato['Tipo_Codice']; ?></td>
        <td class="width1 text_center"><br></td>
        <td class="width20 text_left" ><?php echo $Codice_trovato['Autorita_Ricorso']; ?></td>
        <td class="width1 text_center"><br></td>
	</tr>
<?php }
}?>
</table> <?php

break;

case ('ricCodice'):

	if ($cls_db->getNumberRow($resultCodice)==0)
	{echo"<script>alert('Codice Tributo non trovato.');self.close();</script>";}
	else
	{
		$i = 0; // contatore : serve per identificare righe pari e righe dispari
		?>

<table align=center cellspacing=0 border=0>
	<tr class = riga_pari style="height:35px;" >
    	<td class="width5 text_center"></td>
    	<td class="width5" ><b>Codice</b></td>
    	<td class="width1 text_center"><br></td>
    	<td class="width10" ><b>Settore</b></td>
    	<td class="width1 text_center"><br></td>
    	<td class="width35 text_left" ><b>Descrizione</b></td>
        <td class="width1 text_center"><br></td>
        <td class="width20 text_left" ><b>Tipo codice</b></td>
        <td class="width1 text_center"><br></td>
        <td class="width20 text_left" ><b>Autorita'</b></td>
        <td class="width1 text_center"><br></td>
	</tr>
<?php while($Codice_trovato = mysqli_fetch_array($resultCodice, MYSQLI_ASSOC))
	{
		if ($i++ % 2)
		{$stile_riga = 'class="riga_pari"';}
		else
		{$stile_riga = 'class="riga_dispari"';}
?>
	<tr <?php echo $stile_riga ?>>
    	<td class="width5 text_center"><input type=image src="<?= IMMAGINIWEB; ?>/select.png" style="width:25px; height:25px; border:0;" title="Clicca qui per inserire l'utente"
    	onClick="codice = codice_ogg('<?php echo $Codice_trovato['ID']; ?>','<?php echo addslashes($Codice_trovato['Descrizione']); ?>','<?php echo $Codice_trovato['Codice_Tributo']; ?>'); torna_valore(codice);">
        <td class="width5" ><?php echo $Codice_trovato['Codice_Tributo']; ?></td>
    	<td class="width1 text_center"><br></td>
    	<td class="width10" ><?php echo $Codice_trovato['Settore']; ?></td>
    	<td class="width1 text_center"><br></td>
    	<td class="width35 text_left" ><?php echo $Codice_trovato['Descrizione']; ?></td>
        <td class="width1 text_center"><br></td>
        <td class="width20 text_left" ><?php echo $Codice_trovato['Tipo_Codice']; ?></td>
        <td class="width1 text_center"><br></td>
        <td class="width20 text_left" ><?php echo $Codice_trovato['Autorita_Ricorso']; ?></td>
        <td class="width1 text_center"><br></td>
	</tr>
<?php }
}?>
</table> <?php

break;

case ('listaCodice'):

	if ($cls_db->getNumberRow($resultCodice)==0)
	{echo"<script>alert('Codice Tributo non trovato.');self.close();</script>";}
	else
	{
		$i = 0; // contatore : serve per identificare righe pari e righe dispari
		?>

<table class="text_center pwidth800" border="0" cellspacing=0>
	<tr class = riga_pari style="height:35px;" >
    	<td class="width1 text_center"></td>
    	<td class="width5" ><b>Codice</b></td>
    	<td class="width1 text_center"><br></td>
    	<td class="width15" ><b>Settore</b></td>
    	<td class="width1 text_center"><br></td>
    	<td class="width50 text_left" ><b>Descrizione</b></td>
        <td class="width1 text_center"><br></td>
        <td class="width25 text_left" ><b>Autorita'</b></td>
        <td class="width1 text_center"><br></td>
	</tr>
<?php while($Codice_trovato = mysqli_fetch_array($resultCodice, MYSQLI_ASSOC))
	{
         if ($i++ % 2)
         {$stile_riga = 'class="riga_pari"';}
         else
         {$stile_riga = 'class="riga_dispari"';}
?>
	<tr <?php echo $stile_riga ?>>
    	<td class="width1 text_center"></td>
        <td class="width5" ><?php echo $Codice_trovato['Codice_Tributo']; ?></td>
    	<td class="width1 text_center"><br></td>
    	<td class="width15" ><?php echo $Codice_trovato['Settore']; ?></td>
    	<td class="width1 text_center"><br></td>
    	<td class="width50 text_left" ><?php echo $Codice_trovato['Descrizione']; ?></td>
        <td class="width1 text_center"><br></td>
        <td class="width25 text_left" ><?php echo $Codice_trovato['Autorita_Ricorso']; ?></td>
        <td class="width1 text_center"><br></td>
	</tr>
<?php }
}?>
</table> <br><?php

break;

case ('ricCrono'):

	if ($cls_db->getNumberRow($resultCrono)==0)
	{echo"<script>alert('Partita non trovata.');self.close();</script>";}
	else
	{
		$i = 0; // contatore : serve per identificare righe pari e righe dispari
		?>

<table align=center cellspacing=0 border=0>
	<tr class = riga_pari style="height:35px;" >
    	<td width=5% align=center></td>
    	<td width=70% ><b>Informazioni cartella</b></td>
        <td width=5% align=center><br></td>
        <td width=5% align=right><b>ID</b></td>
        <td width=5% align=center><br></td>
	</tr>
<?php

while($Crono_trovato = mysqli_fetch_array($resultCrono, MYSQLI_ASSOC))
	{
         if ($i++ % 2)
         {$stile_riga = 'class="riga_pari"';}
         else
         {$stile_riga = 'class="riga_dispari"';}
?>
	<tr <?php echo $stile_riga ?>>
    	<td class="text_center width5"><input type=image src="<?= IMMAGINIWEB; ?>/select.png" style="width:25px; height:25px; border:0;" title="Clicca qui per inserire l'utente"
    	onClick="partita = partita_ogg('<?php echo $Crono_trovato['ID']; ?>','<?php echo $Crono_trovato['Anno_Riferimento']; ?>','<?php echo $Crono_trovato['ID_Atto']; ?>'); torna_valore(partita);"></td>
        <td width=70% align=left><?php echo $Crono_trovato['Info_Cartella']; ?></td>
        <td width=5% align=center><br></td>
        <td width=5% align=right><?php echo $Crono_trovato['Comune_ID']; ?></td>
        <td width=5% align=center><br></td>
	</tr>
<?php }
}?>
</table> <?php

break;

case ('ricPigno'):

	if ($cls_db->getNumberRow($resultPigno)==0)
	{echo"<script>alert('Partita non trovata.');self.close();</script>";}
	else
	{
		$i = 0; // contatore : serve per identificare righe pari e righe dispari
		?>

<table align=center cellspacing=0 border=0>
	<tr class = riga_pari style="height:35px;" >
    	<td width=5% align=center></td>
    	<td width=20% ><b>Cronologico</b></td>
    	<td width=5% align=center><br></td>
        <td width=50% align=left><b>Tipo pignoramento</b></td>
        <td width=5% align=center><br></td>
        <td width=10% align=right><b>ID</b></td>
        <td width=5% align=center><br></td>
	</tr>
<?php

while($Pigno_trovato = mysqli_fetch_array($resultPigno, MYSQLI_ASSOC))
	{
         if ($i++ % 2)
         {$stile_riga = 'class="riga_pari"';}
         else
         {$stile_riga = 'class="riga_dispari"';}

         if($Pigno_trovato['Tipo']=="terzi")
         {
         	$tipo_visualizzato = "Presso ".$Pigno_trovato['Tipo_Terzi'];
         }
         else if($Pigno_trovato['Tipo']=="veicolo")
         {
         	$tipo_visualizzato = "Beni mobili registrati";
         }
         else
         	$tipo_visualizzato = $Pigno_trovato['Tipo'];
?>
	<tr <?php echo $stile_riga ?>>
    	<td class="text_center width5"><input type=image src="<?= IMMAGINIWEB; ?>/select.png" style="width:25px; height:25px; border:0;" title="Clicca qui per inserire l'utente"
    	onClick="partita = partita_ogg('<?php echo $Pigno_trovato['ID']; ?>','<?php echo $Pigno_trovato['Anno_Riferimento']; ?>','<?php echo $Pigno_trovato['ID_Pigno']; ?>'); torna_valore(partita);"></td>
        <td width=20% align=left><?php echo $Pigno_trovato['ID_Crono']."/".$Pigno_trovato['Anno_Crono']; ?></td>
        <td width=5% align=center><br></td>
        <td width=50% align=left><?php echo strtoupper($tipo_visualizzato); ?></td>
        <td width=5% align=center><br></td>
        <td width=10% align=right><?php echo $Pigno_trovato['Comune_ID']; ?></td>
        <td width=5% align=center><br></td>
	</tr>
<?php }
}?>
</table> <?php

break;

case ('ricInfoCart'):

	if ($cls_db->getNumberRow($resultInfo)==0)
	{echo"<script>alert('Partita non trovata.');self.close();</script>";}
	else
	{
		$i = 0; // contatore : serve per identificare righe pari e righe dispari
		?>

<table align=center cellspacing=0 border=0>
	<tr class = riga_pari style="height:35px;" >
    	<td width=5% align=center></td>
    	<td width=70% ><b>Informazioni cartella</b></td>
        <td width=5% align=center><br></td>
        <td width=5% align=right><b>ID</b></td>
        <td width=5% align=center><br></td>
	</tr>
<?php

while($Info_trovato = mysqli_fetch_array($resultInfo, MYSQLI_ASSOC))
	{
         if ($i++ % 2)
         {$stile_riga = 'class="riga_pari"';}
         else
         {$stile_riga = 'class="riga_dispari"';}
?>
	<tr <?php echo $stile_riga ?>>
    	<td class="text_center width5"><input type=image src="<?= IMMAGINIWEB; ?>/select.png" style="width:25px; height:25px; border:0;" title="Clicca qui per inserire l'utente"
    	onClick="partita = partita_ogg('<?php echo $Info_trovato['ID']; ?>','<?php echo $Info_trovato['Anno_Riferimento']; ?>',''); torna_valore(partita);"></td>
        <td width=70% align=left><?php echo $Info_trovato['Info_Cartella']; ?></td>
        <td width=5% align=center><br></td>
        <td width=5% align=right><?php echo $Info_trovato['Comune_ID']; ?></td>
        <td width=5% align=center><br></td>
	</tr>
<?php }
}?>
</table> <?php

break;


default:
	break;

}

}
?>

</body>
</html>
