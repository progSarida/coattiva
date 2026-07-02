<?php
require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";

include CLASSI . "/comuni.php";
include CLASSI . "/targhe_estere.php";
include CLASSI . "/targhe_estere_utenti.php";
include_once CLASSI . "/motivi_mancata_contestazione_cds.php";
include CLASSI . "/targhe_estere_pagamenti.php";
include_once CLASSI . "/fatture.php";

if (!session_id()) session_start();

if($_SESSION['username']==NULL)
{
  header("Location:accesso_negato.php");
  die;
}

/*if ($_SESSION['CC_User'] == "***+")
	alertAllGlobalVariables();*/

$c = get_var('c');
$a = get_var('a');

$comune = new ente_gestito($c);

$nome_comune = ($comune->Nome==NULL?"":$comune->Nome." [".$c."]");
$nome_user = "Operatore: " . $_SESSION['username'];

$comune_locale = $c;
$anno_locale = $a;

$nome_comune_temp = $comune->Nome;

$richiesta = get_var('richiesta');
$sanzione1o2 = get_var('sanzione');
$accertatore1o2oV = get_var('accertatore1o2oV');
$voglionome = get_var('voglionome');

$cerca = get_var('cerca');
$esempioricerca = get_var('esempioricerca');

$dadata = get_var('dadata');
$adata = get_var('adata');

$primavolta = get_var('primavolta');

$memocodice = "";
//$imgFreccia = $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/immagini/Plus.png";
$imgFreccia = "../../immagini/Plus.png";
$imgCestino = "../../immagini/delete-icon.png";
$imgAnnulla = "../../immagini/exit.jpg";
$imgModifica = "../../immagini/modifica.jpg";
$imgRitorna = "../../immagini/undosimple.png";
$dimImg = "pwidth20 pheight20";

$numFiltroData = 0;
$numFiltroNomi = 0;
$numFiltroArticoli = 0;
$numFiltroComuni = 0;
$numFiltroTarghe = 0;
$numFiltroRiferimenti = 0;

$stringaJS = array();
$stringaJSRip = array();

$trovati = "";

//if ($esempioricerca != NULL) clean_string ($esempioricerca);

//$rrr = new targhe_estere_richieste_dati(NULL);

//$richiesta = "motivi";

switch ($richiesta)
{
	case "modello":
		$titolopagina = "Ricerca dei Modelli";
		$linkricerca = "ricerca_generica_2.php?c=$c&a=$a";
		$linknuovo = "cds_inserimenti_generali_new.php";
		$query = "select *
                from modello_violazione ";
		if ($esempioricerca != NULL)
		{
			$query .= "where Mod_Via like '%$esempioricerca%'
                or Mod_Localita like '%$esempioricerca%'";
        }
        $query .= " ORDER BY Mod_Progr asc";
        $scrittaComune = "Comune di $nome_comune_temp, anno $anno_locale.";
		break;
	case "rilevatore":
		$titolopagina = "Ricerca dei Rilevatori Elettronici";
		$linkricerca = "ricerca_generica_2.php?c=$c&a=$a";
		$linknuovo = "inserimento_generico.php?c=$c&a=$a";
		$query = "SELECT *
                FROM targhe_estere_rilevatori_velocita 
				WHERE Ril_Comune = '$c' AND ";
		if ($esempioricerca != NULL)
		{
			$query .= " Ril_Tipo LIKE '%$esempioricerca%' AND ";
        }
        $query .= " 1 ";
        $query .= " ORDER BY Ril_Progr asc";
        $scrittaComune = "Comune di $nome_comune_temp, anno $anno_locale.";
		break;
	case "accertatore":
		$titolopagina = "Ricerca degli Ufficiali Accertatori";
		$linkricerca = "ricerca_generica_2.php?c=$c&a=$a&voglionome=$voglionome&accertatore1o2oV=$accertatore1o2oV";
		$linknuovo = "inserimento_generico.php?c=$c&a=$a";
		$query = "SELECT *
                FROM targhe_estere_accertatori 
				WHERE Acc_Comune = '$c' AND ";
		if ($esempioricerca != NULL)
		{
			$query .= " (Acc_Accertatore LIKE '%$esempioricerca%'
                OR Acc_Matricola LIKE '$esempioricerca%') AND ";
        }
        $query .= " 1 ";
        $query .= " ORDER BY Acc_Matricola asc";
        $scrittaComune = "Comune di $nome_comune_temp, anno $anno_locale.";
		break;
	case "sanzione":
		$titolopagina = "Ricerca delle Sanzioni Accessorie";
		$linkricerca = "ricerca_generica_2.php?c=$c&a=$a&sanzione=$sanzione1o2";
		$linknuovo = "cds_inserimenti_generali_new.php?sanzione=$sanzione1o2";
		$query = "select * 
				from sanzioni_accessorie 
				where San_Anno='$anno_locale' 
				and San_Comune='$comune_locale' ";
		if ($esempioricerca != NULL)
		{
			$query .= " and San_Descrizione like '%$esempioricerca%'";
        }
        $query .= " ORDER BY San_Codice";
        $scrittaComune = "Comune di $nome_comune_temp, anno $anno_locale.";
		break;
	case "motivi":
		$titolopagina = "Ricerca dei Motivi di Mancata Contestazione";
		$linkricerca = "ricerca_generica_2.php?c=$c&a=$a";
		$linknuovo = "cds_inserimenti_generali_new.php";
		$query = "select *
                from motivi_mancata_contestazione_cds ";
		if ($esempioricerca != NULL)
		{
			$query .= "where (Mot_Descrizione like '%$esempioricerca%'
                or Mot_Codice like '$esempioricerca%') ";
            //and Mot_Comune='$comune_locale'";
        }
        else
        {
        	$query .= "where Mot_Comune='$comune_locale'";
        }
        $query .= " ORDER BY Mot_Progr asc";
        $scrittaComune = "Comune di $nome_comune_temp, anno $anno_locale.";
		break;
	case "marcheveicoli":
		$titolopagina = "Ricerca delle Marche dei Veicoli";
		$linkricerca = "ricerca_generica_2.php?c=$c&a=$a";
		$linknuovo = "cds_inserimenti_generali_new.php";
		$query = "select *
                from marche_veicoli ";
		if ($esempioricerca != NULL)
		{
			$query .= "where (Mar_Descrizione like '%$esempioricerca%'
                or Mar_Codice like '$esempioricerca%')";
        }
        $query .= " ORDER BY Mar_Descrizione asc";
        $scrittaComune = "Comune di $nome_comune_temp, anno $anno_locale.";
		break;
	case "viacomune":
		$titolopagina = "Ricerca delle Vie nel Comune";
		$linkricerca = "ricerca_generica_2.php?c=$c&a=$a";
		$linknuovo = "cds_inserimenti_generali_new.php";
		
		$query = "select *
				from via 
				where Via_Anagrafe = 'false' and Via_Comune = '$comune_locale' ";
		if ($esempioricerca != NULL)
		{
			$query .= " and Via_Nome like '%$esempioricerca%' ";
		}
		$query .= " ORDER BY Via_Nome asc";
		//alert ($query);
        $scrittaComune = "Comune di $nome_comune_temp, anno $anno_locale.";
		break;
	case "targheestere":
		$titolopagina = "Riepilogo Dati Esteri Inseriti";
		$linkricerca = "ricerca_generica_2.php?c=$c&a=$a";
		$linknuovo = "";
		
		// controllo se ce ne sono del comune in questione (PRIMAVOLTA)
		$queryNo = "select * from registro_cronologico_cds ";
		$queryNo .= " where Reg_Stato_Verbale = 'MANUALE'";
		$queryNo .= " AND Reg_Data_Esecuzione_Impossibile = '0000-00-00' ";
		$queryNo .= " AND Reg_Ente_Per_Richiesta = 1 ";
		$queryNo .= " AND Reg_Comune_Violazione = '$c' ";
		$resQueryNo = esegui_query($queryNo);
		if (numero_risposte_query($resQueryNo) == 0)
			$primavolta = 0;  //  evito che carichi all'inizio quelli del comune: se non ce ne sono, rimane vuota la pagina!
		
		$query = "select * from registro_cronologico_cds ";
		$query .= " where Reg_Stato_Verbale = 'MANUALE'";
		$query .= " AND Reg_Data_Esecuzione_Impossibile = '0000-00-00' ";
		$query .= " AND Reg_Ente_Per_Richiesta = 1 ";
		$query .= " ORDER BY Reg_Ente_Per_Richiesta, Reg_Progr";
		//alert ($query);
        $scrittaComune = "";
        
        if ($primavolta == 1) $newfiltro1 = $c;
        else $newfiltro1 = get_var('newfiltro1');
        $newfiltro2 = get_var('newfiltro2');
        $newfiltro3 = get_var('newfiltro3');
        	
        // comune
        $explodeQuery = explode ("ORDER BY", $query);
        if ($newfiltro1 != "")
        {
        	$query = $explodeQuery[0];
        	$query .= " AND Reg_Comune_Violazione = '" . $newfiltro1 . "'";
        	$query .= " ORDER BY ";
        	$query .= $explodeQuery[1];
        }
        // articolo   NON POSSO FARLO PERCHè MI MANDO 142/7 e non codice!!!
        /*$explodeQuery = explode ("ORDER BY", $query);
        if ($newfiltro2 != "")
        {
        	$query = $explodeQuery[0];
        	$query .= " AND (Reg_Articoli_Infrazione = '" . $newfiltro2 . "'";
        	$query .= " OR Reg_Articoli_Infrazione LIKE '%*" . $newfiltro2 . "%'";
        	$query .= " OR Reg_Articoli_Infrazione = '*" . $newfiltro2 . "*%')";
        	$query .= " ORDER BY ";
        	$query .= $explodeQuery[1];
        	echo $query;
        	return;
        }*/
        // targa
        $explodeQuery = explode ("ORDER BY", $query);
        if ($newfiltro3 != "")
        {
        	$query = $explodeQuery[0];
        	$query .= " AND Reg_Targa_Veicolo = '" . $newfiltro3 . "'";
        	$query .= " ORDER BY ";
        	$query .= $explodeQuery[1];
        }
		break;
	case "targheanalizzate":
		$titolopagina = "Riepilogo Dati Esteri Analizzati";
		$linkricerca = "ricerca_generica_2.php?c=$c&a=$a";
		$linknuovo = "";

		if ($primavolta == 1) $newfiltro2 = $c;
		else $newfiltro2 = get_var('newfiltro2');
		
		$newfiltro1 = get_var('newfiltro1');
		$newfiltro3 = get_var('newfiltro3');
		
		/*$newArrayArt = "";
		if ($newfiltro3 != "" && $_SESSION['CC_User'] == "***+")
		{
			$queryArticolo = "SELECT Tar_Progr FROM targhe_estere_articoli ";
			//$queryArticolo .= "WHERE Tar_Com = '$c' ";
			$resArticolo = esegui_query($queryArticolo);
			while ($rigaArt = risultati_query($resArticolo))
			{
				$myArt = new targhe_estere_articoli($rigaArt['Tar_Progr']);
				if ($myArt->ScriviArticoloCompleto() == $newfiltro3)
				{
					$newArrayArt .= Reg
					(Reg_Articoli_Infrazione = Tar_Progr OR Reg_Articoli_Infrazione LIKE Tar_Progr)
					
					$newArrayArt .= " ( Reg_Articoli_Infrazione LIKE '%*" . $newfiltro2 . "%'";
        			$query .= " OR Reg_Articoli_Infrazione = '*" . $newfiltro2 . "*%')";
				}
			}
		}*/
		
		
		// controllo se ce ne sono del comune in questione (PRIMAVOLTA)
		$queryNo = "select * from registro_cronologico_cds ";
		$queryNo .= " where Reg_Stato_Verbale = 'AUTOMATICO' ";
		$queryNo .= " AND Reg_Data_Esecuzione_Impossibile = '0000-00-00' ";
		$queryNo .= " AND Reg_Ente_Per_Richiesta = 1 ";
		$queryNo .= " AND Reg_Comune_Violazione = '$c' ";
		$resQueryNo = esegui_query($queryNo);
		if (numero_risposte_query($resQueryNo) == 0)
			$primavolta = 0;  //  evito che carichi all'inizio quelli del comune: se non ce ne sono, rimane vuota la pagina!
		
		$query = "select * from registro_cronologico_cds ";
		$query .= " where Reg_Stato_Verbale = 'AUTOMATICO' ";
		$query .= " AND Reg_Data_Esecuzione_Impossibile = '0000-00-00' ";
		$query .= " AND Reg_Ente_Per_Richiesta = 1 ";
		$query .= " ORDER BY Reg_Ente_Per_Richiesta, Reg_Comune_Violazione, Reg_Progr";
		$scrittaComune = "";
			
		// ente   NON POSSO FARLO PERCHè ho codici diversi per lo stesso stato!!!
		/*$explodeQuery = explode ("ORDER BY", $query);
		if ($newfiltro1 != "")
		{
			$query = $explodeQuery[0];
			$query .= " AND Reg_Comune_Violazione = '" . $newfiltro1 . "'";
			$query .= " ORDER BY ";
			$query .= $explodeQuery[1];
		}*/
		// comune
		$explodeQuery = explode ("ORDER BY", $query);
		if ($newfiltro2 != "")
		{
			$query = $explodeQuery[0];
			$query .= " AND Reg_Comune_Violazione = '" . $newfiltro2 . "'";
			$query .= " ORDER BY ";
			$query .= $explodeQuery[1];
		}
		// articolo   NON POSSO FARLO PERCHè MI MANDO 142/7 e non codice!!!
		/*$explodeQuery = explode ("ORDER BY", $query);
		if ($newfiltro3 != "")
		{
			$query = $explodeQuery[0];
			$query .= " AND Reg_Targa_Veicolo = '" . $newfiltro3 . "'";
			$query .= " ORDER BY ";
			$query .= $explodeQuery[1];
		}*/
		
		break;
	case "targhetrasgressore":
		$titolopagina = "Riepilogo Dati Esteri Analizzati";
		$linkricerca = "ricerca_generica_2.php?c=$c&a=$a";
		$linknuovo = "";
		
		// controllo se ce ne sono del comune in questione (PRIMAVOLTA)
		$queryNo = "select * from registro_cronologico_cds ";
		$queryNo .= " where (Reg_Stato_Verbale = 'MANUALE' OR Reg_Stato_Verbale = 'AUTOMATICO')";
		$queryNo .= " AND Reg_Data_Esecuzione_Impossibile = '0000-00-00' ";
		$queryNo .= " AND Reg_Ente_Per_Richiesta != 1 ";
		$queryNo .= " AND Reg_Comune_Violazione = '$c' ";
		$resQueryNo = esegui_query($queryNo);
		if (numero_risposte_query($resQueryNo) == 0)
			$primavolta = 0;  //  evito che carichi all'inizio quelli del comune: se non ce ne sono, rimane vuota la pagina!
		
		$query = "select * from registro_cronologico_cds ";
		$query .= " where (Reg_Stato_Verbale = 'MANUALE' OR Reg_Stato_Verbale = 'AUTOMATICO')";
		$query .= " AND Reg_Data_Esecuzione_Impossibile = '0000-00-00' ";
		$query .= " AND Reg_Ente_Per_Richiesta != 1 ";
		$query .= " ORDER BY Reg_Ente_Per_Richiesta, Reg_Comune_Violazione, Reg_Progr";
		$scrittaComune = "";
		
		if ($primavolta == 1) $newfiltro2 = $c;
		else $newfiltro2 = get_var('newfiltro2');
		
		$newfiltro1 = get_var('newfiltro1');
		$newfiltro3 = get_var('newfiltro3');
		$newfiltro4 = get_var('newfiltro4');
			
		// ente   NON POSSO FARLO PERCHè ho codici diversi per lo stesso stato!!!
		/*$explodeQuery = explode ("ORDER BY", $query);
		 if ($newfiltro1 != "")
		 {
		$query = $explodeQuery[0];
		$query .= " AND Reg_Comune_Violazione = '" . $newfiltro1 . "'";
		$query .= " ORDER BY ";
		$query .= $explodeQuery[1];
		}*/
		// comune
		$explodeQuery = explode ("ORDER BY", $query);
		if ($newfiltro2 != "")
		{
			$query = $explodeQuery[0];
			$query .= " AND Reg_Comune_Violazione = '" . $newfiltro2 . "'";
			$query .= " ORDER BY ";
			$query .= $explodeQuery[1];
		}
		// articolo   NON POSSO FARLO PERCHè MI MANDO 142/7 e non codice!!!
		/*$explodeQuery = explode ("ORDER BY", $query);
		if ($newfiltro3 != "")
		{
			$query = $explodeQuery[0];
			$query .= " AND Reg_Targa_Veicolo = '" . $newfiltro3 . "'";
			$query .= " ORDER BY ";
			$query .= $explodeQuery[1];
		}*/
        // targa
        $explodeQuery = explode ("ORDER BY", $query);
        if ($newfiltro4 != "")
        {
        	$query = $explodeQuery[0];
        	$query .= " AND Reg_Targa_Veicolo = '" . $newfiltro4 . "'";
        	$query .= " ORDER BY ";
        	$query .= $explodeQuery[1];
        }
		
		break;
	case "targheimportate":
		$titolopagina = "Riepilogo Dati Esteri Importati";
		$linkricerca = "ricerca_generica_2.php?c=$c&a=$a";
		$linknuovo = "";
		
		$query = "select * from targhe_estere_notifiche ";
		$query .= " where Tipo_Trasgressore = 'INATTESA'";
		$query .= " ORDER BY ID";
		//alert ($query);
        $scrittaComune = "";
		break;
		/*
	case "targheOLDestere":
		$titolopagina = "Riepilogo Dati Esteri Già Stampati";
		$linkricerca = "ricerca_generica_2.php?c=$c&a=$a";
		$linknuovo = "";
		
		$dadata = to_mysql_date($dadata);
		$adata = to_mysql_date($adata);
		
		$query = "select * from registro_cronologico_cds ";
		$query .= " where Reg_Stato_erbale = 'GENRATO'";
		$query .= " AND Est_Data_Infrazione > '$dadata' AND Est_Data_Infrazione < '$adata' ";
		$query .= " AND Reg_Data_Esecuzione_Impossibile = '0000-00-00' ";
		$query .= " ORDER BY Est_Ente_Per_Richiesta asc";
        $scrittaComune = "";
		break;
		*/
	case "utenti_esteri":
		$titolopagina = "Ricerca dei Nomi dei Contribuenti";
		$linkricerca = "ricerca_generica_2.php?c=$c&a=$a";
		$linknuovo = "";
		$query = "select *
                from targhe_estere_utenti ";
		if ($esempioricerca != NULL)
		{
			$query .= "where (Cognome like '$esempioricerca%'
                or Nome like '$esempioricerca%')";
        }
        $query .= " ORDER BY Cognome, Nome asc";
        $scrittaComune = "Comune di $nome_comune_temp, anno $anno_locale.";
		break;
	case "preinserimenti":
		$titolopagina = "Riepilogo Preinserimenti Esteri";
		$linkricerca = "ricerca_generica_2.php?c=$c&a=$a";
		$linknuovo = "";
		
		/*$query = "select * from targhe_estere_notifiche, registro_cronologico_cds ";
		$query .= " where Verbale_ID = Reg_Progr AND ";
		$query .= " Data_Stampa_Notifica = '0000-00-00' AND ";
		$query .= " Reg_Comune_Violazione = '$c' AND ";
		$query .= " Reg_Anno = '$a' ";
		$query .= " ORDER BY ID";*/
		
		$myNotifica = new targhe_estere_notifiche(NULL);
		$query = $myNotifica->QueryPreinserimenti($c, $a);
		
		//alert ($query);
        $scrittaComune = "";
		break;
	case "verbali":
		$titolopagina = "Riepilogo Verbali Esteri $a";
		$linkricerca = "ricerca_generica_2.php?c=$c&a=$a";
		$linknuovo = "";
		
		/*$query = "select * from targhe_estere_notifiche, registro_cronologico_cds ";
		$query .= " where Verbale_ID = Reg_Progr AND ";
		$query .= " Data_Stampa_Notifica != '0000-00-00' AND ";
		$query .= " Reg_Comune_Violazione = '$c' AND ";
		$query .= " Reg_Anno = '$a' ";
		$query .= " ORDER BY ID";*/
		
		$myNotifica = new targhe_estere_notifiche(NULL);
		$query = $myNotifica->QueryVerbali($c, $a);
		
		$newfiltro1 = get_var('newfiltro1');
		$newfiltro2 = get_var('newfiltro2');
		$newfiltro3 = get_var('newfiltro3');
		$newfiltro4 = get_var('newfiltro4');
		
		// stato
		//if ($newfiltro1)
		// data accertamento
		$explodeQuery = explode ("ORDER BY", $query);
		if ($newfiltro2 != "")
		{
			$query = $explodeQuery[0];
			$query .= " AND Reg_Data_Avviso = '" . $newfiltro2 . "'";
			$query .= " ORDER BY ";
			$query .= $explodeQuery[1];
		}
		// utente
		$explodeQuery = explode ("ORDER BY", $query);
		if ($newfiltro3 != "")
		{
			$query = $explodeQuery[0];
			$query .= " AND Utente_ID = '" . $newfiltro3 . "'";
			$query .= " ORDER BY ";
			$query .= $explodeQuery[1];
		}
		// riferimento verb comune
		$explodeQuery = explode ("ORDER BY", $query);
		if ($newfiltro4 != "")
		{
			$query = $explodeQuery[0];
			$query .= " AND Reg_Provenienza = '" . $newfiltro4 . "'";
			$query .= " ORDER BY ";
			$query .= $explodeQuery[1];
		}
		
        $scrittaComune = "";
		break;
	case "motivimancata":
		$titolopagina = "Riepilogo Motivi Mancata Contestazione";
		$linkricerca = "ricerca_generica_2.php?c=$c&a=$a";
		$linknuovo = "inserimento_generico.php?c=$c&a=$a";
		
		$dadata = to_mysql_date($dadata);
		$adata = to_mysql_date($adata);
		
		$query = "select * from motivi_mancata_contestazione_cds ";
		$query .= " ORDER BY Mot_Progr asc";
        $scrittaComune = "";
		break;
	case "ente":
		$titolopagina = "Ricerca degli Enti Esteri Per Richieste Dati";
		$linkricerca = "ricerca_generica_2.php?c=$c&a=$a";
		$linknuovo = "inserimento_generico.php?c=$c&a=$a";
		$query = "SELECT *
                FROM targhe_estere_zone_competenza ";
		if ($esempioricerca != NULL)
		{
			$query .= " WHERE Tar_Nazione_Nome LIKE '%$c%'  ";
        }
        $query .= " ORDER BY Tar_Nazione_Num asc";
        $scrittaComune = "Comune di $nome_comune_temp, anno $anno_locale.";
        
        $newfiltro1 = get_var('newfiltro1');
        //$newfiltro2 = get_var('newfiltro2');
        	
        // ente   NON POSSO FARLO PERCHè ho codici diversi per lo stesso stato!!!
		/*$explodeQuery = explode ("ORDER BY", $query);
		 if ($newfiltro1 != "")
		 {
		$query = $explodeQuery[0];
		$query .= " AND Reg_Comune_Violazione = '" . $newfiltro1 . "'";
		$query .= " ORDER BY ";
		$query .= $explodeQuery[1];
		}*/
		break;
	case "pagamentitemporanei":
		$titolopagina = "Ricerca dei Pagamenti Temporanei";
		$linkricerca = "ricerca_generica_2.php?c=$c&a=$a";
		$linknuovo = "inserimento_generico.php?c=$c&a=$a";
		$query = "SELECT *
                FROM targhe_estere_pagamenti ";
		//if ($esempioricerca != NULL)
		//{
		$query .= " WHERE Pag_Comune_CC = '$c' AND ";
		$query .= " Pag_Anno = '$a' AND ";
		$query .= " (Pag_Notifica = '0' OR Pag_Registro = '0') ";
		//}
        $query .= " ORDER BY Pag_Progr asc";
        $scrittaComune = "Comune di $nome_comune_temp, anno $anno_locale.";
		break;
	case "fatture":
		$titolopagina = "Ricerca delle Fatture";
		$linkricerca = "ricerca_generica_3.php?c=$c&a=$a";
		$linknuovo = "";
		$query = "SELECT *
                FROM fatture_generali WHERE 1 ";
		//if ($esempioricerca != NULL)
		//{
		/*$query .= " WHERE Pag_Comune_CC = '$c' AND ";
		$query .= " Pag_Anno = '$a' AND ";
		$query .= " (Pag_Notifica = '0' OR Pag_Registro = '0') ";*/
		//}
        $query .= " ORDER BY ID asc";
        $scrittaComune = "Tutti i Comuni, tutti gli Anni";
        
        if ($primavolta == 1) $newfiltro1 = $c;
        else $newfiltro1 = get_var('newfiltro1');
        if ($primavolta == 1) $newfiltro2 = date("Y");
        else $newfiltro2 = get_var('newfiltro2');
        $newfiltro3 = get_var('newfiltro3');
         
        // comune
        $explodeQuery = explode ("ORDER BY", $query);
        if ($newfiltro1 != "")
        {
        	$query = $explodeQuery[0];
        	$query .= " AND Fat_Comune = '" . $newfiltro1 . "'";
        	$query .= " ORDER BY ";
        	$query .= $explodeQuery[1];
        }
        // datafatt
        $explodeQuery = explode ("ORDER BY", $query);
        if ($newfiltro2 != "")
        {
        	$query = $explodeQuery[0];
        	$query .= " AND Fat_Anno = '" . $newfiltro2 . "'";
        	$query .= " ORDER BY ";
        	$query .= $explodeQuery[1];
        }
        // riscossione
        $explodeQuery = explode ("ORDER BY", $query);
        if ($newfiltro3 != "")
        {
        	$query = $explodeQuery[0];
        	$query .= " AND Fat_Tributo = '" . $newfiltro3 . "'";
        	$query .= " ORDER BY ";
        	$query .= $explodeQuery[1];
        }
        
		break;
	default:
		$titolopagina = "Nessun titolo";
		$linkricerca = "Nessun link";
		$linknuovo = "Nessun nuovo link";
		$query = "";
        $scrittaComune = "";
		break;
		
}

//alert ($linkricerca);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<link rel="shortcut icon"  href="/gitco2/immagini/gitco.png">
<title><?php echo "$titolopagina"; ?></title>

<link rel=StyleSheet href="/gitco2/CSS/classi_semplici.css" type="text/css" media=screen>
<link rel=StyleSheet href="/gitco2/CSS/jquery-ui-1.10.3.custom.css" type="text/css" media=screen>
<style> .ui-datepicker { font-size:11px; } </style>


<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/JQuery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/form_jquery.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/funzioni.js" ></script>

<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/jquery-ui.js" ></script>
<script type="text/javascript" language="javascript" src="/gitco2/librerie/js/datepicker.js" ></script>
    

<script type="text/javascript" language="javascript" for="window">
			function GeneraLinkPagina(tipo)
			{
				if (tipo == "Cerca")
				{
					var link = "<?php echo "$linkricerca"; ?>";
					var ultimalettera = link.charAt(link.length - 1);
					if (ultimalettera == "p")
						link += "?";
					else
						link += "&";

					link += "cerca=Cerca";
					var esempio = $("#esempiomiaricerca").val();
					if (esempio != "")
						link += "&esempioricerca=" + $("#esempiomiaricerca").val();
					//alert (link);
					
					var richiesta = "<?php echo "$richiesta"; ?>";
					link += "&richiesta=" + richiesta;

					window.name = "ricercadati";
					window.open(link, "ricercadati");
				}
				else if (tipo == "Nuova")
				{
					var link = "<?php echo "$linknuovo"; ?>";
					var ultimalettera = link.charAt(link.length - 1);
					if (ultimalettera == "p")
						link += "?";
					else
						link += "&";

					var richiesta = "<?php echo "$richiesta"; ?>";
					link += "richiesta=" + richiesta;

					//alert (link);
					window.name = "ricercadati";
					window.open(link, "ricercadati");
				}
			}
			function EliminazioneRiga (codice)
			{
				var risposta = confirm ('Sei sicuro di voler eliminare questa richiesta dati?');
				if (risposta == true)
				{
					var richiesta = "<?php echo "$richiesta"; ?>";
					$.ajax(
							{
								type: "GET",  
								async: false,
								url: "eliminazione_generica.php",
								data:
								{
									"richiesta" : richiesta,
									"id" : codice
								},
								success: function (deleteRichiesta)
								{
									//alert (deleteRichiesta);
									switch (deleteRichiesta)
									{
									case "DELETE_OK": alert ("Eliminazione avvenuta con successo"); break;
									case "DELETE_ERROR": alert ("Errore durante la cancellazione del record"); break;
									default: alert ("PROBLEMA DELETE: " + deleteRichiesta);
									}
								}
							}
					);
					self.close();
				}
			}
			function RiportaRichiesta (codice)
			{
				var risposta = confirm ('Sei sicuro di voler lavorare nuovamente questa richiesta dati?');
				if (risposta == true)
				{
					var strRiporta = "ricerca_generica_3.php?";
					var richiesta = "<?php echo "$richiesta"; ?>";
					strRiporta += "richiesta=" + richiesta;
					strRiporta += "&c=" + com;
					strRiporta += "&a=" + anno;
					strRiporta += "&riportaricerca=" + codice;
					window.name = "ricercadati";
					window.open (strRiporta, 'ricercadati');
				}
			}

			function AnnullamentoVerbale (codice, com, anno)
			{
				var risposta = confirm ('Sei sicuro di voler annullare questo verbale?');
				if (risposta == true)
				{
					var strAnnull = "annullamento_verbale.php?";
					var richiesta = "<?php echo "$richiesta"; ?>";
					strAnnull += "richiesta=" + richiesta;
					strAnnull += "&c=" + com;
					strAnnull += "&a=" + anno;
					strAnnull += "&id=" + codice;
					window.name = "ricercadati";
					window.open (strAnnull, 'ricercadati');
				}
			}
			
			/*function AnnullamentoVerbale (numId, com, anno)
			{
				var strDim = "dialogWidth:400px";
				strDim += "; dialogHeight:420px";
				strDim += "; dialogTop:70px; dialogLeft:70px; status:yes; scroll:no;";
				var strAnnullo = "../modali/elenchi_fissi.php?";
				strAnnullo += "richiesta=" + "<?php echo "$richiesta"; ?>";
				strAnnullo += "&c=" + com;
				strAnnullo += "&a=" + anno;
				strAnnullo += "&notifica=" + numId;
				window.name = "ricercadati";
				window.open(strAnnullo, 'ricercadati');
				return;
				
				valorediritorno = window.showModalDialog(strRiepilogo, window, strDim);
				//var valorediritorno = { esito : "OKRINOTIFICA", verbale : 3, notifica : 91 };

				if (valorediritorno != undefined)
				{
					switch (valorediritorno.esito)
					{
						case "OKNOTIFICA":
							AltroRecord('');
							break;
						case "OKSTATO":
							AltroRecord('');
							break;
						case "OKRINOTIFICA":
							salvaTrasgressore(valorediritorno.notifica);
							AltroRecord(valorediritorno.verbale);
							break;
						default: break;
					}
				}
				//window.open (strRiepilogo);
			}*/


			
			
			function RiesumaRichiestaGiaGenerata (codice)
			{
				var risposta = confirm ('Sei sicuro di voler riesumare questa richiesta dati già generata?');
				if (risposta == true)
				{
					$.get("../classi_new/targhe_estere_classi.php",
							{
								"progressivo" : codice,
								"metodo" : "riesumogiagenrato"
							},
							function (insertRichiesta) {
								switch (insertRichiesta)
								{
								case true: alert ("Ora la richiesta è di nuovo possibile"); break;
								case false: alert ("Errore durante l'operazione"); break;
								default: alert ("PROBLEMA RIESUMO: " + insertRichiesta);
								}
							}
					);
					self.close();
					
				}
			}
			function cambiofiltro (num)
			{
				var strFiltro = "ricerca_generica_3.php?";
				var richiesta = "<?php echo "$richiesta"; ?>";
				strFiltro += "richiesta=" + richiesta;
				strFiltro += "&c=" + "<?=$c?>";
				strFiltro += "&a=" + "<?=$a?>";
				strFiltro += "&cerca=Cerca";
				//strFiltro += "&newfiltro" + num + "=" + $("[name=filtro" + num + "]").val();
				if ($("[name=filtro1]").val() != undefined)
					strFiltro += "&newfiltro1=" + $("[name=filtro1]").val();
				if ($("[name=filtro2]").val() != undefined)
					strFiltro += "&newfiltro2=" + $("[name=filtro2]").val();
				if ($("[name=filtro3]").val() != undefined)
					strFiltro += "&newfiltro3=" + $("[name=filtro3]").val();
				if ($("[name=filtro4]").val() != undefined)
					strFiltro += "&newfiltro4=" + $("[name=filtro4]").val();
				//alert (strFiltro);
				window.name = "ricercadati";
				window.open(strFiltro, "ricercadati");
				//location.href = strFiltro;
			}

			function DataMysqlInDataIta (dataMysql)
			{
				if (dataMysql == "") return "";

				var dataIta = dataMysql.substr(8, 2) + "/";
				dataIta += dataMysql.substr(5, 2) + "/";
				dataIta += dataMysql.substr(0, 4);
				
				return dataIta;
			}

			function OrdinaSelectData (filtrodata)
			{
				if (filtrodata == 0) return;
				
				var nomeFiltro = "[name=filtro" + filtrodata + "]";
				var selectedValue = $("" + nomeFiltro + "").val();
				
				var optionValues = [];

				$("" + nomeFiltro + " option").each(function() {
					 optionValues.push($(this).val());
				});

				var dataPresente;
				var newArray = new Array();
				var temp;

				for (var i = 0; i < optionValues.length; i++)  //  prendo una sola volta la stessa data
				{
					dataPresente = false;
					for (var j = 0; j < newArray.length; j++)
					{
						if (optionValues[i] == newArray[j])
							dataPresente = true;
					}
					if (dataPresente == false)
						newArray.push(optionValues[i]);
				}
				for (i = 0; i < newArray.length; i++)  //  ordino array
				{
					for (j = 0; j < newArray.length; j++)
					{
						if (newArray[i] < newArray[j])
						{
							temp = newArray[i];
							newArray[i] = newArray[j];
							newArray[j] = temp;
						}
					}
				}
				var optionnfiltro = "";
				var sellected = "";
				var dataIta;
				$("" + nomeFiltro + "").empty();
				//$("" + nomeFiltro + "").append("<option value=''></option>");
				for (i = 0; i < newArray.length; i++)  //  appendo le options
				{
					dataIta = DataMysqlInDataIta(newArray[i]);
					if (sellected == "") sellected = "";  //  serve per togliere l'errore giallo di eclipse
					if (newArray[i] == selectedValue) sellected = " selected ";
					else sellected = "";
					optionnfiltro = "<option value='" + newArray[i] + "' " + sellected + ">" + dataIta + "</option>";
					$("" + nomeFiltro + "").append(optionnfiltro);
				}
			}

			function OrdinaSelectNomi (filtronomi)
			{
				if (filtronomi == 0) return;
				
				var nomeFiltro = "[name=filtro" + filtronomi + "]";
				var selectedValue = $("" + nomeFiltro + "").val();
				
				var optionValues = [];
				var optionTexts = [];

				$("" + nomeFiltro + " option").each(function() {
					optionValues.push($(this).val());
					optionTexts.push($(this).text());
				});

				var nomePresente;
				var newArray = new Array();
				var newTextArray = new Array();
				var temp;
				var tempTxt;

				for (var i = 0; i < optionValues.length; i++)  //  prendo una sola volta la stessa data
				{
					nomePresente = false;
					for (var j = 0; j < newArray.length; j++)
					{
						if (optionValues[i] == newArray[j])
							nomePresente = true;
					}
					if (nomePresente == false)
					{
						newArray.push(optionValues[i]);
						newTextArray.push(optionTexts[i]);
					}
				}
				for (i = 0; i < newTextArray.length; i++)  //  ordino array
				{
					for (j = 0; j < newTextArray.length; j++)
					{
						if (newTextArray[i] < newTextArray[j])
						{
							temp = newArray[i];
							newArray[i] = newArray[j];
							newArray[j] = temp;
							tempTxt = newTextArray[i];
							newTextArray[i] = newTextArray[j];
							newTextArray[j] = tempTxt;
						}
					}
				}
				
				var optionnfiltro = "";
				var sellected = "";
				$("" + nomeFiltro + "").empty();
				//$("" + nomeFiltro + "").append("<option value=''></option>");
				for (i = 0; i < newArray.length; i++)  //  appendo le options
				{
					if (sellected == "") sellected = "";  //  serve per togliere l'errore giallo di eclipse
					if (newArray[i] == selectedValue) sellected = " selected ";
					else sellected = "";
					optionnfiltro = "<option value='" + newArray[i] + "' " + sellected + ">" + newTextArray[i] + "</option>";
					$("" + nomeFiltro + "").append(optionnfiltro);
				}
			}

			function OrdinaSelectArticoli (filtroarticoli)
			{
				if (filtroarticoli == 0) return;
				
				var nomeFiltro = "[name=filtro" + filtroarticoli + "]";
				var selectedValue = $("" + nomeFiltro + "").val();
				
				var optionValues = [];

				$("" + nomeFiltro + " option").each(function() {
					 optionValues.push($(this).val());
				});

				var dataPresente;
				var newArray = new Array();
				var temp;

				for (var i = 0; i < optionValues.length; i++)  //  prendo una sola volta la stessa data
				{
					dataPresente = false;
					for (var j = 0; j < newArray.length; j++)
					{
						if (optionValues[i] == newArray[j])
							dataPresente = true;
					}
					if (dataPresente == false)
						newArray.push(optionValues[i]);
				}
				for (i = 0; i < newArray.length; i++)  //  ordino array
				{
					for (j = 0; j < newArray.length; j++)
					{
						if (newArray[i] < newArray[j])
						{
							temp = newArray[i];
							newArray[i] = newArray[j];
							newArray[j] = temp;
						}
					}
				}
				var optionnfiltro = "";
				var sellected = "";
				$("" + nomeFiltro + "").empty();
				//$("" + nomeFiltro + "").append("<option value=''></option>");
				for (i = 0; i < newArray.length; i++)  //  appendo le options
				{
					if (sellected == "") sellected = "";  //  serve per togliere l'errore giallo di eclipse
					if (newArray[i] == selectedValue) sellected = " selected ";
					else sellected = "";
					optionnfiltro = "<option value='" + newArray[i] + "' " + sellected + ">" + newArray[i] + "</option>";
					$("" + nomeFiltro + "").append(optionnfiltro);
				}
			}


			function inizio()
			{
				$('#progressbar').progressbar({
					value: false
				});
				$( "#barlabel" ).text("Inizio elaborazione...");
			}

			function update(testo, valore)
			{
				$( "#progressbar" ).progressbar({value: parseInt(valore) });
				$( "#barlabel" ).text(testo + " " +  valore + "%" );
			}

			function nessun_risultato()
			{
				$( "#progressbar" ).progressbar({value: 100 });
				$( "#barlabel" ).text("Nessun risultato trovato");
			}

			function fine(value)
			{
				$( "#progressbar" ).progressbar({value: 100 });
				$( "#barlabel" ).text( value );
			}
			
       </script>
</head>
<!--  <body class="finestra" onload="popopen=setTimeout('focOnMe()',500);"> -->
<body class="sfondo_new_gitco">
	<center>
	<H3><b><?php echo "$titolopagina"; ?></b></H3>
	<hr>
	</center>

<?php



	if ($cerca == NULL) // nella pagina precedente ho cliccato  per aprire questa finestra: qui mi chiede CERCA o NUOVA
	{
?>
			<center>
			<table class="table_modale" cellspacing="0" cellpadding="0" border="0">
				<tr>          
					<td width="34%" align="center">
						<input class="ricerca" type=text name="esempioricerca" id="esempiomiaricerca" value="">
					</td>
					<td width="33%" align="center">
						<input class="ricerca" type="submit" name="cerca" value="Cerca" onClick="GeneraLinkPagina('Cerca');">
						<input type="hidden" name="primavolta" value="1">
					</td>
					<td>
						<?php
						if ($richiesta != "modello")
						{
							echo <<< TASTONUOVO
						<input class="ricerca" type=submit name="cerca" value="Nuova" onClick="GeneraLinkPagina('Nuova');">
							
TASTONUOVO;
						}
						?>
					</td>
				</tr>
			</table>
			</center>
			
<?php 
	}
	else if ($cerca == 'Nuova') // ho scelto NUOVA (tra CERCA e NUOVA)
	{
		$stringaNuova = "window.open('cds_inserimenti_generali_new.php?";
		$stringaNuova .= "richiesta=$richiesta&damodificare=$memocodice',";
		$stringaNuova .= "'ricercadati');";
		
		//alert ($stringaNuova);
		
		echo <<< JSNUOVOELEMENTO
			
			<script>
				$stringaNuova
			</script>
			
JSNUOVOELEMENTO;
	}
	else if ($cerca == 'Cerca') // ho scelto CERCA (tra CERCA e NUOVA)
	{
		$result = esegui_query($query);
		$numerorighe = numero_risposte_query($result);
		
		// se non ho sanzioni in lista
		if ($numerorighe == 0)
		{
			$stringaNuova = "window.open('cds_inserimenti_generali_new.php?";
			$stringaNuova .= "richiesta=$richiesta&damodificare=$memocodice',";
			$stringaNuova .= "'ricercadati');";
			if ($richiesta != "modello" && 
				$richiesta != "marcheveicoli" && 
				$richiesta != "targheestere" &&
				$richiesta != "targheanalizzate" &&
				$richiesta != "targhetrasgressore" &&
				$richiesta != "targheOLDestere" &&
				$richiesta != "targheimportate" &&
				$richiesta != "verbali" &&
				$richiesta != "motivimancata" &&
				$richiesta != "preinserimenti" &&
				$richiesta != "pagamentitemporanei" &&
				$richiesta != "fatture")
			{
				echo <<< JSNESSUNELEMENTO
				<script>
					flag = confirm ("ERRORE: Non ci sono dati corrispondenti alla ricerca. Vuoi inserirne uno nuovo?");
					if (flag == true)
						$stringaNuova
					else
						window.close();
				</script>
				
JSNESSUNELEMENTO;
				die;
			}
			else if ($richiesta == "modello" || 
					$richiesta == "targheestere" || 
					$richiesta == "targheanalizzate" ||
					$richiesta == "targhetrasgressore" ||
					$richiesta == "targheOLDestere" || 
					$richiesta == "utenti_esteri" ||
					$richiesta == "targheimportate" ||
					$richiesta == "verbali" ||
					$richiesta == "motivimancata" ||
					$richiesta == "preinserimenti" ||
					$richiesta == "pagamentitemporanei" ||
					$richiesta == "fatture")
			{
				echo <<< JSNESSUNELEMENTO
				$query
				<script>
					alert ("ERRORE: Non ci sono dati corrispondenti alla ricerca.");
					window.close();
				</script>
				
JSNESSUNELEMENTO;
				die;
			}
		}
		else // almeno un elemento in lista
		{         
			$i = 0; // contatore : serve per identificare righe pari e righe dispari
			$string1 = ($numerorighe==1?"o":"i");
			$string2 = ($numerorighe==1?"":"s");
			
			$trovati = "Trovat" . $string1 . " " . $numerorighe . " record" . $string2 . ".";
			
			//echo "<br>" . $query;
			
?>
			<center>
			<table class="table_modale width60" cellspacing="0" cellpadding="0" border="0">
			<tr class="intestazione">
				<!-- <td class="width40 text_center">
					<b><?php echo $scrittaComune?>
					 <?php echo $trovati?></b>
					 <br>
					 &nbsp;
				</td>
				<td class="width20 text_center">
				</td> -->
				<td class="width80 text_center">
					<div class="text_center" id="progressbar"><div class="text_center" id="barlabel"></div></div>
				</td>
			</tr>
			</table>
			</center>
			
			<script>inizio();</script>
			
			<center>
			<table class="table_modale width85" cellspacing="0" cellpadding="0" border="0">
			<tr class="intestazione pheight25">
				<?php RigaFiltro ($richiesta); ?>
			</tr>
			<tr class=intestazione>
				<?php RigaIntestazioneScelta ($richiesta); ?>
			</tr>
			
				
			
<?php



function CreoStringaReturnArray ($array, $conto)
{
	$stringa = "ritornoArray[" . $conto . "] = { ";
	for ($i = 0; $i < count($array); $i++)
	{
		if ($i % 2 == 0)
		{
			$stringa .= $array[$i] . ":";
		}
		else
		{
			$stringa .= '"' . $array[$i] . '" , ';
		}
	}
	$stringa = substr ($stringa, 0, -2);  //  tolgo l'ultima virgola 
	$stringa .= " }; ";
	return $stringa;
}

			while ($elemento_trovato = risultati_query($result))  // RIGHE VILAZIONI
			{
				if ($i++ % 2) { $stile_riga = 'class="riga_pari"'; }
				else          { $stile_riga = 'class="riga_dispari"'; }
				
				$percentuale = ceil($i*100/$numerorighe);
				$script = "update('$trovati', $percentuale);";
				echo "<script>" . $script . "</script>";
				
				set_time_limit(300);
					
				$escludiQuesto = false;
					
				switch ($richiesta)
				{
				case "modello":
					$memocodice = $elemento_trovato['Mod_Progr'];
					$tipostrada = $elemento_trovato['Mod_Tipo_Strada'] . $elemento_trovato['Mod_Numero_Strada'];
					$via = $elemento_trovato['Mod_Via'];
					$localita = $elemento_trovato['Mod_Localita'];
					$km = $elemento_trovato['Mod_Km'];
					$direzione = $elemento_trovato['Mod_Direzione_Marcia'];
					$accertatore = $elemento_trovato['Mod_Accertatore'];
					$rilevatore = $elemento_trovato['Mod_Rilevatore_Velocita'];
					
					$query = "SELECT Acc_Accertatore FROM targhe_estere_accertatori WHERE Acc_Progr = $accertatore";
					$accertatore = single_answer_query ($query);
					$query = "SELECT Ril_Tipo FROM rilevatori_velocita WHERE Ril_Progr = $rilevatore";
					$rilevatoretipo = single_answer_query ($query);
					$query = "SELECT Ril_Marca FROM rilevatori_velocita WHERE Ril_Progr = $rilevatore";
					$rilevatore = $rilevatoretipo . " " . single_answer_query ($query);
					
					$stringaLink = "window.returnValue = $memocodice;";
					//$stringaLink .= " window.event.returnValue = false;";
					$stringaLink .= " window.close();";
					//alert ($stringaLink);
					
					$stringaModif = "window.open('cds_inserimenti_generali_new.php?";
					$stringaModif .= "richiesta=$richiesta&damodificare=$memocodice',";
					$stringaModif .= "'ricercadati');";
					//alert ($stringaModif);
					break;
				case "rilevatore":
					$myRilevatore = new targhe_estere_rilevatori_velocita($elemento_trovato['Ril_Progr']);
					$memocodice = $elemento_trovato['Ril_Progr'];
					$tiporilevatore = $elemento_trovato['Ril_Tipo'];
					$marcarilevatore = $elemento_trovato['Ril_Marca'];
					$modellorilevatore = $elemento_trovato['Ril_Modello'];
					$omologazione = $elemento_trovato['Ril_Omologazione'];
					
					if ($elemento_trovato['Ril_Velocita'] == "Y")
						$tolleranza = $elemento_trovato['Ril_Tolleranza'] . "%";
					else $tolleranza = "";
					$velocita = $elemento_trovato['Ril_Velocita'];
					$matricolaFTRD = $elemento_trovato['Ril_Matricola_Sistema'];
					$testoCompleto = $myRilevatore->DescrizionePerVerbaleRilevatore();
					
					$stringaLink = "window.returnValue = $memocodice;";
					//$stringaLink .= " window.event.returnValue = false;";
					$stringaLink .= " window.close();";
					
					/*$_SESSION['CC_User'] != "***+"
					{
						$stringaModif = "window.open('cds_inserimenti_generali_new.php?";
						$stringaModif .= "richiesta=$richiesta&damodificare=$memocodice',";
						$stringaModif .= "'ricercadati');";
						//alert ($stringaModif);
					}
					else */
					{
						$stringaModif = "window.open('inserimento_generico.php?";
						$stringaModif .= "c=$c&richiesta=$richiesta&damodificare=$memocodice',";
						$stringaModif .= "'ricercadati');";
						
						$scrittaRilevatore = $tiporilevatore;
						if ($marcarilevatore != "") $scrittaRilevatore .= " " . $marcarilevatore;
						if ($modellorilevatore != "") $scrittaRilevatore .= " " . $modellorilevatore;
						$provoArray = array ("Ril_Progr", $memocodice, "Ril_Tipo", $scrittaRilevatore, "Ril_Velocita", $velocita);
						$stringaJS[] = CreoStringaReturnArray ($provoArray, $i-1);
						$stringaLink = "RitornoPagina(" . ($i-1) . ");";
						$stringaLink .= " window.close();";
					}
					break;
				case "accertatore":
					$memocodice = $elemento_trovato['Acc_Progr'];
					$accertatorenome = $elemento_trovato['Acc_Accertatore'];
					$matricolaaccertatore = $elemento_trovato['Acc_Matricola'];
					$firmaaccertatore = $elemento_trovato['Acc_Firma_Digitale'];
					
					/*if ($voglionome == 1)
						$stringaLink = "window.returnValue = '" . addslashes($accertatorenome) . "';";
					else*/
						$stringaLink = "window.returnValue = $memocodice;";
					//$stringaLink .= " window.event.returnValue = false;";
					$stringaLink .= " window.close();";
					
					$stringaModif = "window.open('inserimento_generico.php?";
					$stringaModif .= "c=$c&richiesta=$richiesta&damodificare=$memocodice',";
					$stringaModif .= "'ricercadati');";
					//alert ($stringaModif);
					$provoArray = array ("Acc_Progr", $memocodice, "Acc_Accertatore", $accertatorenome);
					$stringaJS[] = CreoStringaReturnArray ($provoArray, $i-1);
					$stringaLink = "RitornoPagina(" . ($i-1) . ");";
					$stringaLink .= " window.close();";
					break;
				case "sanzione":
					$memocodice = $elemento_trovato['San_Progr'];
					$videocodice = $elemento_trovato['San_Codice'];
					$descrizionesanzione = $elemento_trovato['San_Descrizione'];
					$descrizionesanzione = stripslashes ($descrizionesanzione);
					
					$stringaLink = "window.returnValue = $memocodice;";
					//$stringaLink .= " window.event.returnValue = false;";
					$stringaLink .= " window.close();";
					
					$stringaModif = "window.open('cds_inserimenti_generali_new.php?";
					$stringaModif .= "richiesta=$richiesta&damodificare=$memocodice',";
					$stringaModif .= "'ricercadati');";
					//alert ($stringaModif);
					break;
				case "motivi":
					$memocodice = $elemento_trovato['Mot_Progr'];
					$motivocodice = $elemento_trovato['Mot_Codice'];
					$descrizionemotivo = $elemento_trovato['Mot_Descrizione'];
					$commentomotivo = $elemento_trovato['Mot_Commento'];
					$descrizionemotivo = stripslashes ($descrizionemotivo);
					$commentomotivo = stripslashes ($commentomotivo);
					
					$stringaLink = "window.returnValue = $memocodice;";
					//$stringaLink .= " window.event.returnValue = false;";
					$stringaLink .= " window.close();";
					
					$stringaModif = "window.open('cds_inserimenti_generali_new.php?";
					$stringaModif .= "richiesta=$richiesta&damodificare=$memocodice',";
					$stringaModif .= "'ricercadati');";
					//alert ($stringaModif);
					break;
				case "marcheveicoli":
					$memocodice = $elemento_trovato['Mar_Progr'];
					$marcacodice = $elemento_trovato['Mar_Codice'];
					$descrizionemarca = $elemento_trovato['Mar_Descrizione'];
					
					$stringaLink = "window.returnValue = '$descrizionemarca';";
					//$stringaLink .= " window.event.returnValue = false;";
					$stringaLink .= " window.close();";
					
					$stringaModif = "window.open('cds_inserimenti_generali_new.php?";
					$stringaModif .= "richiesta=$richiesta&damodificare=$memocodice',";
					$stringaModif .= "'ricercadati');";
					//alert ($stringaModif);
					break;
				case "viacomune":
					$memocodice = $elemento_trovato['Via_Progr'];
					$siglacodice = $elemento_trovato['Via_Top'];
					$nomevia = $elemento_trovato['Via_Nome'];
					
					$query2 = "SELECT Top_Nome FROM topos WHERE Top_Progr = $siglacodice";
					$viapiazza = single_answer_query($query2);
					
					$nomecompleto = $viapiazza . " " . $nomevia;
					
					$stringaLink = "window.returnValue = $memocodice;";
					//$stringaLink .= " window.event.returnValue = false;";
					$stringaLink .= " window.close();";
					
					$stringaModif = "window.open('cds_inserimenti_generali_new.php?";
					$stringaModif .= "richiesta=$richiesta&damodificare=$memocodice',";
					$stringaModif .= "'ricercadati');";
					//alert ($stringaModif);
					break;
				case "targheestere":
					$myRegistro = new registro_cronologico_cds($elemento_trovato['Reg_Progr']);
					$memocodice = $myRegistro->Reg_Progr;
					$numeroente = $myRegistro->Reg_Ente_Per_Richiesta;
					
					$enterichiesta = $myRegistro->Coll_Ente_Richiesta->Tar_Nazione_Nome;
					
					$comuneCCluogo = $myRegistro->Reg_Comune_Violazione;
					$myCom = new ente_gestito($comuneCCluogo);
					$comuneluogo = $myCom->Nome;
					if ($comuneluogo == "") $comuneluogo = $comuneCCluogo;
					$datainfr = $myRegistro->Reg_Data_Avviso;
					$datainfr = from_mysql_date($datainfr);
					$orainfr = $myRegistro->Reg_Ora_Avviso;
					$orainfr = substr($orainfr, 0, 5);
					$targa = $myRegistro->Reg_Targa_Veicolo;
					$articolo = $myRegistro->Coll_Articoli_Infranti[0]->ScriviArticoloCompleto();
					
					$stringaLink = "window.returnValue = $memocodice;";
					//$stringaLink .= " window.event.returnValue = 'false';";
					$stringaLink .= " window.close();";
					
					//$stringaModif = "EliminazioneRiga($memocodice)";
					$stringaModif = "";
					//alert ($stringaModif);
					
					$numFiltroNomi = 1;
					$numFiltroArticoli = 2;
					$numFiltroComuni = 1;
					$numFiltroTarghe = 3;
					/*if ($primavolta == 1) $newfiltro1 = $c;
					else $newfiltro1 = get_var('newfiltro1');
					$newfiltro2 = get_var('newfiltro2');
					$newfiltro3 = get_var('newfiltro3');*/
					
					AggiungiTendina($arrayfiltro1, 1, $comuneCCluogo, $comuneluogo, $comuneCCluogo, $newfiltro1);
					AggiungiTendina($arrayfiltro2, 2, $articolo, $articolo, $articolo, $newfiltro2);
					AggiungiTendina($arrayfiltro3, 3, $targa, $targa, $targa, $newfiltro3);
					//alert ("if ($newfiltro1 != NULL && $enterichiesta != $newfiltro1)");
					if ($newfiltro1 != NULL && $comuneCCluogo != $newfiltro1)
						$escludiQuesto = true;
					if ($newfiltro2 != NULL && $articolo != $newfiltro2)
						$escludiQuesto = true;
					if ($newfiltro3 != NULL && $targa != $newfiltro3)
						$escludiQuesto = true;
					break;
				case "targheanalizzate":
					$myRegistro = new registro_cronologico_cds($elemento_trovato['Reg_Progr']);
					$memocodice = $myRegistro->Reg_Progr;
					$numeroente = $myRegistro->Reg_Ente_Per_Richiesta;
					
					$enterichiesta = $myRegistro->Coll_Ente_Richiesta->Tar_Nazione_Nome;
					
					$comuneCCluogo = $myRegistro->Reg_Comune_Violazione;
					$annoVerb = $myRegistro->Reg_Anno;
					$myCom = new ente_gestito($comuneCCluogo);
					$comuneluogo = $myCom->Nome;
					if ($comuneluogo == "") $comuneluogo = $comuneCCluogo;
					$datainfr = $myRegistro->Reg_Data_Avviso;
					$datainfr = from_mysql_date($datainfr);
					$orainfr = $myRegistro->Reg_Ora_Avviso;
					$orainfr = substr($orainfr, 0, 5);
					$targa = $myRegistro->Reg_Targa_Veicolo;
					$articolo = $myRegistro->Coll_Articoli_Infranti[0]->ScriviArticoloCompleto();
					
					//$stringaLink = "window.returnValue = $memocodice;";
					//$stringaLink .= " window.event.returnValue = 'false';";
					$provoArray = array ("Reg_Progr", $memocodice, "Esito", "RICARICA");
					$stringaJS[] = CreoStringaReturnArray ($provoArray, $i-1);
					$stringaLink = "RitornoPagina(" . ($i-1) . ");";
					$stringaLink .= " window.close();";
					
					$stringaAnnulla = "AnnullamentoVerbale($memocodice, '$comuneCCluogo', $annoVerb)";
					$stringaElimina = "EliminazioneRiga($memocodice)";
					//alert ($stringaModif);
					
					$numFiltroNomi = 2;
					$numFiltroArticoli = 3;
					$numFiltroComuni = 2;
					
					//if ($primavolta == 1) $newfiltro2 = $c;
					//else $newfiltro2 = get_var('newfiltro2');

					//$newfiltro1 = get_var('newfiltro1');
					//$newfiltro3 = get_var('newfiltro3');
					
					AggiungiTendina($arrayfiltro1, 1, $enterichiesta, $enterichiesta, $enterichiesta, $newfiltro1);
					AggiungiTendina($arrayfiltro2, 2, $comuneCCluogo, $comuneluogo, $comuneCCluogo, $newfiltro2);
					AggiungiTendina($arrayfiltro3, 3, $articolo, $articolo, $articolo, $newfiltro3);
					//alert ("if ($newfiltro1 != NULL && $enterichiesta != $newfiltro1)");
					if ($newfiltro1 != NULL && $enterichiesta != $newfiltro1)
						$escludiQuesto = true;
					if ($newfiltro2 != NULL && $comuneCCluogo != $newfiltro2)
						$escludiQuesto = true;
					if ($newfiltro3 != NULL && $articolo != $newfiltro3)
						$escludiQuesto = true;
					break;
				case "targhetrasgressore":
					$myRegistro = new registro_cronologico_cds($elemento_trovato['Reg_Progr']);
					$memocodice = $myRegistro->Reg_Progr;
					$numeroente = $myRegistro->Reg_Ente_Per_Richiesta;
					
					$enterichiesta = $myRegistro->Coll_Ente_Richiesta->Tar_Nazione_Nome;
					
					$comuneCCluogo = $myRegistro->Reg_Comune_Violazione;
					$annoVerb = $myRegistro->Reg_Anno;
					$myCom = new ente_gestito($comuneCCluogo);
					$comuneluogo = $myCom->Nome;
					if ($comuneluogo == "") $comuneluogo = $comuneCCluogo;
					$datainfr = $myRegistro->Reg_Data_Avviso;
					$datainfr = from_mysql_date($datainfr);
					$orainfr = $myRegistro->Reg_Ora_Avviso;
					$orainfr = substr($orainfr, 0, 5);
					$targa = $myRegistro->Reg_Targa_Veicolo;
					if ($myRegistro->Reg_Articoli_Infrazione == "")
						alert ($myRegistro->Reg_Progr);
					$articolo = $myRegistro->Coll_Articoli_Infranti[0]->ScriviArticoloCompleto();
					
					//$stringaLink = "window.returnValue = $memocodice;";
					//$stringaLink .= " window.event.returnValue = 'false';";
					$provoArray = array ("Reg_Progr", $memocodice, "Esito", "RICARICA");
					$stringaJS[] = CreoStringaReturnArray ($provoArray, $i-1);
					$stringaLink = "RitornoPagina(" . ($i-1) . ");";
					$stringaLink .= " window.close();";
					
					if ($myRegistro->Reg_Stato_Verbale == "AUTOMATICO")
						$provo2Array = array ("Reg_Progr", $memocodice, "Esito", "RIPORTAINDIETROIMPORTATO");
					else if ($myRegistro->Reg_Stato_Verbale == "MANUALE")
						$provo2Array = array ("Reg_Progr", $memocodice, "Esito", "RIPORTAINDIETRORICHIESTA");
					else if ($myRegistro->Reg_Stato_Verbale == "WEBIMPORTATO")
						$provo2Array = array ("Reg_Progr", $memocodice, "Esito", "RIPORTAINDIETROWEB");
					else
						$provo2Array = array ("Reg_Progr", $memocodice, "Esito", "RIPORTAINDIETRONONSO");
						
					$stringaJSRip[] = CreoStringaReturnArray ($provo2Array, $i-1);
					$stringaRiporta = "RitornoIndietroPagina(" . ($i-1) . ");";
					$stringaRiporta .= " window.close();";
					
					$stringaAnnulla = "AnnullamentoVerbale($memocodice, '$comuneCCluogo', $annoVerb)";
					$stringaElimina = "EliminazioneRiga($memocodice)";
					//alert ($stringaModif);
					
					$numFiltroNomi = 1;
					$numFiltroArticoli = 3;
					$numFiltroComuni = 2;
					$numFiltroTarghe = 4;
					
					/*if ($primavolta == 1) $newfiltro2 = $c;
					else $newfiltro2 = get_var('newfiltro2');

					$newfiltro1 = get_var('newfiltro1');
					$newfiltro3 = get_var('newfiltro3');
					$newfiltro4 = get_var('newfiltro4');*/
					
					AggiungiTendina($arrayfiltro1, 1, $enterichiesta, $enterichiesta, $enterichiesta, $newfiltro1);
					AggiungiTendina($arrayfiltro2, 2, $comuneCCluogo, $comuneluogo, $comuneCCluogo, $newfiltro2);
					AggiungiTendina($arrayfiltro3, 3, $articolo, $articolo, $articolo, $newfiltro3);
					AggiungiTendina($arrayfiltro4, 4, $targa, $targa, $targa, $newfiltro4);
					//alert ("if ($newfiltro1 != NULL && $enterichiesta != $newfiltro1)");
					if ($newfiltro1 != NULL && $enterichiesta != $newfiltro1)
						$escludiQuesto = true;
					if ($newfiltro2 != NULL && $comuneCCluogo != $newfiltro2)
						$escludiQuesto = true;
					if ($newfiltro3 != NULL && $articolo != $newfiltro3)
						$escludiQuesto = true;
					if ($newfiltro4 != NULL && $targa != $newfiltro4)
						$escludiQuesto = true;
					break;
				case "targheimportate":
					$myNotifica = new targhe_estere_notifiche($elemento_trovato['ID']);
					$memocodice = $myNotifica->ID;
					
					$query2 = "SELECT Tar_Nazione_Nome FROM targhe_estere_zone_competenza ";
					$query2 .= " WHERE Tar_Progr = '" . $myNotifica->Coll_Verbale->Reg_Ente_Per_Richiesta . "'";
					$enterichiesta = single_answer_query($query2);
					
					$comuneCCluogo = $myNotifica->Coll_Verbale->Reg_Comune_Violazione;
					$myCom = new ente_gestito($comuneCCluogo);
					$comuneluogo = $myCom->Nome;
					if ($comuneluogo == "") $comuneluogo = $comuneCCluogo;
					
					$cognome = substr($myNotifica->Coll_Utente->Cognome, 0, 20);
					$nome = $myNotifica->Coll_Utente->Nome;
					$genere = $myNotifica->Coll_Verbale->Reg_Genere_Infrazione;
					$targa = $myNotifica->Coll_Verbale->Reg_Targa_Veicolo;
					
					$stringaLink = "window.returnValue = $memocodice;";
					//$stringaLink .= " window.event.returnValue = 'false';";
					$stringaLink .= " window.close();";
					
					$stringaModif = "EliminazioneRiga($memocodice)";
					//alert ($stringaModif);
					break;
				case "targheOLDestere":
					$myRegistro = new registro_cronologico_cds($elemento_trovato['Reg_Progr']);
					$memocodice = $myRegistro->Reg_Progr;
					$numeroente = $myRegistro->Reg_Ente_Per_Richiesta;
					
					$query2 = "SELECT Tar_Nazione_Nome FROM targhe_estere_zone_competenza ";
					$query2 .= " WHERE Tar_Progr = $numeroente";
					$enterichiesta = single_answer_query($query2);
					
					$comuneluogo = $myRegistro->Reg_Comune_Violazione;
					$myCom = new ente_gestito($comuneluogo);
					$comuneluogo = $myCom->Nome;
					$datainfr = $myRegistro->Reg_Data_Avviso;
					$datainfr = from_mysql_date($datainfr);
					$orainfr = $myRegistro->Reg_Ora_Avviso;
					$orainfr = substr($orainfr, 0, 5);
					$targa = $myRegistro->Reg_Targa_Veicolo;
					$articolo = $myRegistro->Coll_Articoli_Infranti[0]->ScriviArticoloCompleto();
					
					$stringaLink = "window.returnValue = $memocodice;";
					//$stringaLink .= " window.event.returnValue = 'false';";
					$stringaLink .= " window.close();";
					
					$stringaModif = "RiesumaRichiestaGiaGenerata($memocodice)";
					//alert ($stringaModif);
					break;
				case "utenti_esteri":
					$memocodice = $elemento_trovato['ID'];
					$cognomecontrib = $elemento_trovato['Cognome'];
					$nomecontrib = $elemento_trovato['Nome'];
					$ritorno = $memocodice . "**" .
							$cognomecontrib . "**" . 
							$nomecontrib;
					
					//alert ($ritorno);
							
					if (strlen($cognomecontrib) > 30)
						$cognomecontrib = substr($cognomecontrib, 0, 30) . "...";
					$nomecontrib = $elemento_trovato['Nome'];
					if (strlen($nomecontrib) > 30)
						$nomecontrib = substr($nomecontrib, 0, 30) . "...";
					$cfcontrib = $elemento_trovato['Codice_Fiscale'];
					
					/*if ($voglionome == 1)
						$stringaLink = "window.returnValue = '" . addslashes($accertatorenome) . "';";
					else*/
						$stringaLink = "window.returnValue = '$ritorno';";
					//$stringaLink .= " window.event.returnValue = false;";
					$stringaLink .= " window.close();";
					
					$stringaModif = "window.open('cds_inserimenti_generali_new.php?";
					$stringaModif .= "richiesta=$richiesta&damodificare=$memocodice',";
					$stringaModif .= "'ricercadati');";
					//alert ($stringaModif);
					break;
				case "preinserimenti":
					$myNotifica = new targhe_estere_notifiche($elemento_trovato['ID']);
					$memocodice = $myNotifica->ID;
					$memoVerb = $myNotifica->Coll_Verbale->Reg_Progr;
					$numeroente = $myNotifica->Coll_Verbale->Reg_Ente_Per_Richiesta;
					$cognomenome = $myNotifica->Coll_Utente->Cognome;
					if ($myNotifica->Coll_Utente->Nome != "")
						$cognomenome .= " " . substr($myNotifica->Coll_Utente->Nome, 0, 1) . ".";
					$comuneluogo = $myNotifica->Coll_Verbale->Reg_Comune_Violazione;
					$myCom = new ente_gestito($comuneluogo);
					$comuneluogo = $myCom->Nome;
					$datainfr = $myNotifica->Coll_Verbale->Reg_Data_Avviso;
					$datainfr = from_mysql_date($datainfr);
					$orainfr = $myNotifica->Coll_Verbale->Reg_Ora_Avviso;
					$orainfr = substr($orainfr, 0, 5);
					$targa = $myNotifica->Coll_Verbale->Reg_Targa_Veicolo;
					$articolo = $myNotifica->Coll_Verbale->Coll_Articoli_Infranti[0]->ScriviArticoloCompleto();
					
					$stringaLink = "window.returnValue = $memoVerb;";
					//$stringaLink .= " window.event.returnValue = 'false';";
					$stringaLink .= " window.close();";
					
					if ($numeroente == 1 || $numeroente == 0)
					{
						$puntointerr = "/gitco2/immagini/puntointerrogativo.jpg";
						$titleinterr = "Da definire la nazione";
						$immaginePreins = "<img class='pwidth20 pheigth20' src='$puntointerr' title='$titleinterr'>";
					}
					else 
					{
						$idNoleggio = $myNotifica->NotificaNoleggioCollegata();
						if ($idNoleggio != null)
						{
							$puntointerr = "";
							$titleinterr = "";
							$immaginePreins = "<input type=image src='../../immagini/noleggio.jpg' class='$dimImg' title='Noleggio'>";
						}
						else
						{
							$puntointerr = "";
							$titleinterr = "";
							$immaginePreins = "";
						}
					}
					
					//$stringaModif = "RiesumaRichiestaGiaGenerata($memocodice)";
					//alert ($stringaModif);
					break;
				case "verbali":
					$myNotifica = new targhe_estere_notifiche($elemento_trovato['ID']);
					$memocodice = $myNotifica->ID;
					$memoVerb = $myNotifica->Coll_Verbale->Reg_Progr_Registro;
					$memoRiferimento = strtoupper($myNotifica->Coll_Verbale->Reg_Provenienza);
					$numeroente = $myNotifica->Coll_Verbale->Reg_Ente_Per_Richiesta;
					$statoEnte = $myNotifica->Coll_Verbale->Coll_Ente_Richiesta->Tar_Nazione_Nome;
					$numStatoEnte = $myNotifica->Coll_Verbale->Coll_Ente_Richiesta->Tar_Nazione_Num;
					$cognomenome = $myNotifica->Coll_Utente->Cognome;
					if ($myNotifica->Coll_Utente->Nome != "")
						$cognomenome .= " " . substr($myNotifica->Coll_Utente->Nome, 0, 1) . ".";
					$comuneluogo = $myNotifica->Coll_Verbale->Reg_Comune_Violazione;
					$myCom = new ente_gestito($comuneluogo);
					$comuneluogo = $myCom->Nome;
					$datainfr = $myNotifica->Coll_Verbale->Reg_Data_Avviso;
					$datainfr = from_mysql_date($datainfr);
					$orainfr = $myNotifica->Coll_Verbale->Reg_Ora_Avviso;
					$orainfr = substr($orainfr, 0, 5);
					$targa = $myNotifica->Coll_Verbale->Reg_Targa_Veicolo;
					$articolo = $myNotifica->Coll_Verbale->Coll_Articoli_Infranti[0]->ScriviArticoloCompleto();
					
					if (isset($myNotifica->Coll_Pagamenti[0]))
					{
						$differenzaGiorni = $myNotifica->Coll_Verbale->CalcoloDifferenzaGiorni($myNotifica->Data_Notifica, $myNotifica->Coll_Pagamenti[0]->TrovoDataUltimoPagamento());
						$giaPagato = $myNotifica->Coll_Pagamenti[0]->ImportoGlobaleGiaPagato();
						$bloccato = $myNotifica->Coll_Pagamenti[0]->PagamentoBloccato($myNotifica->Verbale_ID);
					}
					else
					{
						$differenzaGiorni = -1;
						$giaPagato = 0;
						$bloccato = false;
					}
					
					//$importo = number_format($myNotifica->Coll_Verbale->CalcoloGiustomporto($differenzaGiorni), 2, ",", ".");
					$importo = number_format($myNotifica->CalcoloNotImportoTotale(), 2, ",", ".");
					
					$stringaLink = "window.returnValue = $memoVerb;";
					//$stringaLink .= " window.event.returnValue = 'false';";
					$stringaLink .= " window.close();";
					
					$diffPagamento = $giaPagato - $importo;
					if ($diffPagamento >= -0.005 && $diffPagamento <= 0.005) $diffPagamento = 0;
					
					$numGiaPagato = number_format($giaPagato, 2, ",", ".");

					if ($bloccato == true)
						$immaginePagamento = "<input type=image src='../../immagini/eurobloccato.png' class='$dimImg' title='Pagato: $numGiaPagato €, riscossione bloccata da operatore'>";
					else if ($giaPagato == 0)
						$immaginePagamento = "";
					else if ($diffPagamento >= 0)
						$immaginePagamento = "<input type=image src='../../immagini/eurogiallo.png' class='$dimImg' title='Pagato: $numGiaPagato €'>";
					else if ($myNotifica->Data_Notifica == "0000-00-00")
						$immaginePagamento = "<input type=image src='../../immagini/euronero.png' class='$dimImg' title='Pagato $numGiaPagato €, ma data notifica sconosciuta'>";
					else
						$immaginePagamento = "<input type=image src='../../immagini/euromezzo.png' class='$dimImg' title='Pagato in parte: $numGiaPagato €'>";
					
					$pdfVerbale = $myNotifica->LinkVerbalePdf($PathVerbaliEsteri);
					if (file_exists($_SERVER['DOCUMENT_ROOT'] . $pdfVerbale))
						$immagineStampa = "<input type=image src='../../immagini/print.png' class='$dimImg' title='Stampato'>";
					else
						$immagineStampa = "";
					
					if ($myNotifica->Coll_Verbale->Reg_Data_Annullamento != "0000-00-00")
					{
						$immagineAnnullamento = "<input type=image src='../../immagini/bloccato.jpg' class='$dimImg' title='Annullato'>";
					}
					else
					{
						$idNoleggio = $myNotifica->NotificaNoleggioCollegata();
						if ($idNoleggio != null)
						{
							$immagineAnnullamento = "<input type=image src='../../immagini/noleggio.jpg' class='$dimImg' title='Noleggio'>";
						}
						else $immagineAnnullamento = "";
					}
					
					
					//$stringaModif = "RiesumaRichiestaGiaGenerata($memocodice)";
					//alert ($stringaModif);
					
					$numFiltroData = 2;
					$numFiltroNomi = 3;
					$numFiltroRiferimenti = 4;

					/*$newfiltro1 = get_var('newfiltro1');
					$newfiltro2 = get_var('newfiltro2');
					$newfiltro3 = get_var('newfiltro3');*/
					
					AggiungiTendina($arrayfiltro1, 1, $numStatoEnte, $statoEnte, $numStatoEnte, $newfiltro1);
					AggiungiTendina($arrayfiltro2, 2, $myNotifica->Coll_Verbale->Reg_Data_Avviso, $datainfr, $myNotifica->Coll_Verbale->Reg_Data_Avviso, $newfiltro2);
					AggiungiTendina($arrayfiltro3, 3, $myNotifica->Utente_ID, $cognomenome, $myNotifica->Utente_ID, $newfiltro3);
					AggiungiTendina($arrayfiltro4, 4, $memoRiferimento, $memoRiferimento, $memoRiferimento, $newfiltro4);
					
					//alert ("if ($newfiltro1 != NULL && $enterichiesta != $newfiltro1)");
					if ($newfiltro1 != NULL && $numStatoEnte != $newfiltro1)
						$escludiQuesto = true;
					if ($newfiltro2 != NULL && $myNotifica->Coll_Verbale->Reg_Data_Avviso != $newfiltro2)
						$escludiQuesto = true;
					if ($newfiltro3 != NULL && $myNotifica->Utente_ID != $newfiltro3)
						$escludiQuesto = true;
					if ($newfiltro4 != NULL && $myNotifica->Coll_Verbale->Reg_Provenienza != $newfiltro4)
						$escludiQuesto = true;
					
					break;
				case "motivimancata":
					$myMotivo = new motivi_mancata_contestazione_cds($elemento_trovato['Mot_Progr']);
					$memocodice = $myMotivo->Mot_Progr;
					$motComune = $myMotivo->Mot_Comune;
					$motDescr = $myMotivo->Mot_Descrizione;
					$motJSDescr = addslashes($motDescr);
					$motComm = $myMotivo->Mot_Commento;
					$motJSComm = addslashes($motComm);
					$motCodice = $myMotivo->Mot_Codice;
					$provoArray = array ("Mot_Progr", $memocodice, "Mot_Descrizione", $motJSDescr);
					$stringaJS[] = CreoStringaReturnArray ($provoArray, $i-1);
					//$stringaLink .= " window.event.returnValue = 'false';";
					//$stringaJS .= " window.close();";
					
					$stringaLink = "RitornoPagina(" . ($i-1) . ");";
					//alert ($stringaLink);
					$stringaLink .= " window.close();";
					
					$stringaModif = "window.open('inserimento_generico.php?";
					$stringaModif .= "richiesta=$richiesta&damodificare=$memocodice',";
					$stringaModif .= "'ricercadati');";
					
					//$stringaModif = "RiesumaRichiestaGiaGenerata($memocodice)";
					//alert ($stringaModif);
					break;
				case "ente":
					$memocodice = $elemento_trovato['Tar_Progr'];
					$numEnte = $elemento_trovato['Tar_Nazione_Num'];
					$nazEnte = $elemento_trovato['Tar_Nazione_Nome'];
					$regEnte = $elemento_trovato['Tar_Regione'];
					$ind1Ente = $elemento_trovato['Tar_Indirizzo_Prima_Riga'];
					$ind2Ente = $elemento_trovato['Tar_Indirizzo_Seconda_Riga'];
					$ind3Ente = $elemento_trovato['Tar_Indirizzo_Terza_Riga'];
					$ind4Ente = $elemento_trovato['Tar_Indirizzo_Quarta_Riga'];
					$ind5Ente = $elemento_trovato['Tar_Indirizzo_Quinta_Riga'];
					$telfax = $elemento_trovato['Tar_Telefono_Fax'];
					$email = $elemento_trovato['Tar_Email'];
					$lingEnte = $elemento_trovato['Tar_Linguaggio'];
					$tipoEnte = $elemento_trovato['Tar_Tipo_Richiesta_AutoManual'];
					
					$stringaLink = "window.returnValue = $memocodice;";
					//$stringaLink .= " window.event.returnValue = false;";
					$stringaLink .= " window.close();";
					
					/*$_SESSION['CC_User'] != "***+"
					{
						$stringaModif = "window.open('cds_inserimenti_generali_new.php?";
						$stringaModif .= "richiesta=$richiesta&damodificare=$memocodice',";
						$stringaModif .= "'ricercadati');";
						//alert ($stringaModif);
					}
					else */
					{
						$stringaModif = "window.open('inserimento_generico.php?";
						$stringaModif .= "c=$c&richiesta=$richiesta&damodificare=$memocodice',";
						$stringaModif .= "'ricercadati');";
						
						$provoArray = array (	"Tar_Progr", $memocodice, 
												"Tar_Nazione_Num", $numEnte, 
												"Tar_Nazione_Nome", $nazEnte, 
												"Tar_Regione", $regEnte,
												"Tar_Indirizzo_Prima_Riga", $ind1Ente,
												"Tar_Indirizzo_Seconda_Riga", $ind2Ente,
												"Tar_Indirizzo_Terza_Riga", $ind3Ente,
												"Tar_Indirizzo_Quarta_Riga", $ind4Ente,
												"Tar_Indirizzo_Quinta_Riga", $ind5Ente,
												"Tar_Linguaggio", $lingEnte,
												"Tar_Tipo_Richiesta_AutoManual", $tipoEnte,
												"Tar_Telefono_Fax", $telfax,
												"Tar_Email", $email);
						$stringaJS[] = CreoStringaReturnArray ($provoArray, $i-1);
						$stringaLink = "RitornoPagina(" . ($i-1) . ");";
						$stringaLink .= " window.close();";
					}
					
					$numFiltroNomi = 1;

					//$newfiltro1 = get_var('newfiltro1');
					//$newfiltro2 = get_var('newfiltro2');
					
					AggiungiTendina($arrayfiltro1, 1, $nazEnte, $nazEnte, $nazEnte, $newfiltro1);
					//alert ("if ($newfiltro1 != NULL && $enterichiesta != $newfiltro1)");
					if ($newfiltro1 != NULL && $nazEnte != $newfiltro1)
						$escludiQuesto = true;
					break;
				case "pagamentitemporanei":
					$myPagamento = new targhe_estere_pagamenti($elemento_trovato['Pag_Progr']);
					$memocodice = $myPagamento->Pag_Progr;
					$pagComune = $myPagamento->Pag_Comune_CC;
					$pagTrasgressore = $myPagamento->Pag_Trasgressore;
					$pagDataPag = from_mysql_date($myPagamento->Pag_Data_Pag);
					$pagImporto = number_format($myPagamento->Pag_Importo_Pag, 2, ",", ".");
					$provoArray = array ("Pag_Progr", $memocodice);//, "Mot_Descrizione", $motJSDescr);
					$stringaJS[] = CreoStringaReturnArray ($provoArray, $i-1);
					//$stringaLink .= " window.event.returnValue = 'false';";
					//$stringaJS .= " window.close();";
					
					$stringaLink = "RitornoPagina(" . ($i-1) . ");";
					//alert ($stringaLink);
					$stringaLink .= " window.close();";
					
					$stringaModif = "window.open('inserimento_generico.php?";
					$stringaModif .= "richiesta=$richiesta&damodificare=$memocodice',";
					$stringaModif .= "'ricercadati');";
					
					//$stringaModif = "RiesumaRichiestaGiaGenerata($memocodice)";
					//alert ($stringaModif);
					break;
				case "fatture":
					$myFattura = new fatture_generali($elemento_trovato['ID']);
					$memocodice = $myFattura->ID;
					$fatComune = $myFattura->Fat_Comune;
					$fatNumero = $myFattura->Fat_Numero;
					$esplodoBarre = explode("/", $fatNumero);
					$fatNumero = "<font color='red'>" . $esplodoBarre[0] . "</font>";
					$fatNumero .= "/" . $esplodoBarre[1];
					$fatNumero .= "/" . $esplodoBarre[2];
					$fatData = from_mysql_date($myFattura->Fat_Data);
					$fatTotale = number_format($myFattura->Fat_Totale, 2, ",", ".");
					$fatAnnoBilancio = $myFattura->Fat_Anno_Bilancio;
					$fatAnnoCompetenza = $myFattura->Fat_Anno_Competenza;
					$fatTesto = $myFattura->Fat_Testo_Da_A_Periodo;
					$fatAccredito = number_format($myFattura->Fat_Accredito, 2, ",", ".");
					$myDati = new fatture_dati_cig($myFattura->Fat_Dati_Cig);
					$fatRisc = $myDati->Tipo_Tributo;// . "-" . $myFattura->Fat_Tributo . " $memocodice, $myFattura->Fat_Dati_Comune , $myDati->ID";
					$fatGest = $myDati->Tipo_Gestione;
					$fatTipo = $myFattura->Fat_Tipo;
					$myCom = new fatture_dati_sedi_comuni(null);
					$comuneluogo = $myCom->NomeComuneDaCCFatture($myFattura->Fat_Comune);
					$provoArray = array ("ID", $memocodice);//, "Mot_Descrizione", $motJSDescr);
					$stringaJS[] = CreoStringaReturnArray ($provoArray, $i-1);
					//$stringaLink .= " window.event.returnValue = 'false';";
					//$stringaJS .= " window.close();";
					
					$myInvio = new fatture_invii(null);
					$idInvio = $myInvio->CercaInvioDaFattura($myFattura->ID);
					$myInvio = new fatture_invii($idInvio);
					$myEmail = new fatture_email(null);
					$arrayEmail = $myEmail->CercaListaEmailDaSDI($myInvio->Identificativo_SDI);
					$esitoEmail = $arrayEmail[0];
					$iconaEmail = $arrayEmail[1];
					$statoEmail = $arrayEmail[2];
					if ($iconaEmail == "/gitco2/immagini/puntointerrogativo.jpg")
					{
						if ($myInvio->Data_Invio != "0000-00-00" && $myInvio->Data_Invio != "")
						{
							$iconaEmail = "/gitco2/immagini/enter.png";
						}
					}
					$imgEsito = "<img src='$iconaEmail' class='$dimImg' title='$statoEmail'>";
					
					$stringaLink = "RitornoPagina(" . ($i-1) . ");";
					//alert ($stringaLink);
					$stringaLink .= " window.close();";
					
					$stringaModif = "";
					
					//$stringaModif = "RiesumaRichiestaGiaGenerata($memocodice)";
					//alert ($stringaModif);
					$numFiltroComuni = 1;
					$numFiltroArticoli = 2;
					$numFiltroNomi = 3;
					AggiungiTendina($arrayfiltro1, 1, $fatComune, $comuneluogo, $fatComune, $newfiltro1);
					AggiungiTendina($arrayfiltro2, 2, $myFattura->Fat_Anno, $myFattura->Fat_Anno, $myFattura->Fat_Anno, $newfiltro2);
					AggiungiTendina($arrayfiltro3, 3, $fatRisc, $fatRisc, $fatRisc, $newfiltro3);
					break;
				default:
					break;
				}
				
				if ($escludiQuesto == false)
				{
				
					switch ($richiesta)
					{
						case "modello":
	
							echo <<< TABELLAMODELLI
				<tr $stile_riga>
					<td align="center">
						<input type=image src="$imgFreccia" class="$dimImg" alt="Clicca qui per inserire la selezione" onClick="$stringaLink">
					</td>
					<td align=center>$memocodice</td>
					<td align=center>$tipostrada</td>
					<td align=center>$via</td>
					<td align=center>$localita</td>
					<td align=center>$km</td>
					<td align=center>$direzione</td>
					<td align=center>$accertatore</td>
					<td align=center>$rilevatore</td>
				</tr>
	
TABELLAMODELLI;
							break;
						case "rilevatore":
	
							echo <<< TABELLARILEVATORI
				<tr $stile_riga>
					<td align="center">
						<input type=image src="$imgFreccia" class="$dimImg" alt="Clicca qui per inserire la selezione" onClick="$stringaLink">
					</td>
					<td align=center>$memocodice</td>
					<!--      <td align=center>$tiporilevatore</td>
					<td align=center>$marcarilevatore</td>
					<td align=center>$modellorilevatore</td>
					<td align=center>$omologazione</td>        -->
					<td align=center>$testoCompleto</td>
					<td align=center>$tolleranza</td>
					<td align=center>$matricolaFTRD</td>
					<td align=center>
						<!--<input class="ricerca" type="button" name="cerca" value="Modifica" onClick="$stringaModif">-->
						<input type=image src="$imgModifica" class="$dimImg" title="Modifica" onClick="$stringaModif">
					</td>
				</tr>
	
TABELLARILEVATORI;
							break;
						case "accertatore":
							//$nome_acc = addslashes($accertatorenome);
	
							echo <<< TABELLAACCERTATORI
				<tr $stile_riga>
					<td align="center">
						<input type=image src="$imgFreccia" class="$dimImg" alt="Clicca qui per inserire la selezione" onClick="$stringaLink">
					</td>
					<td align=center>$memocodice</td>
					<td align=center>$accertatorenome</td>
					<td align=center>$matricolaaccertatore</td>
					<td align=center>$firmaaccertatore</td>
					<td align=center>
						<input class="ricerca" type="button" name="cerca" value="Modifica" onClick="$stringaModif">
					</td>
				</tr>
	
TABELLAACCERTATORI;
							break;
						case "sanzione":
	
							echo <<< TABELLASANZIONI
				<tr $stile_riga>
					<td align="center">
						<input type=image src="$imgFreccia" class="$dimImg" alt="Clicca qui per inserire la selezione" onClick="$stringaLink">
					</td>
					<td align=center>$videocodice</td>
					<td align=left>$descrizionesanzione</td>
					<td align=center>
						<input class="ricerca" type="button" name="cerca" value="Modifica" onClick="$stringaModif">
					</td>
				</tr>
	
TABELLASANZIONI;
							break;
						case "motivi":
	
							echo <<< TABELLARILEVATORI
				<tr $stile_riga>
					<td align="center">
						<input type=image src="$imgFreccia" class="$dimImg" alt="Clicca qui per inserire la selezione" onClick="$stringaLink">
					</td>
					<td align=center>$memocodice</td>
					<td align=center>$motivocodice</td>
					<td align=left>$descrizionemotivo</td>
					<td align=left>$commentomotivo</td>
					<td align=center>
						<input class="ricerca" type="button" name="cerca" value="Modifica" onClick="$stringaModif">
					</td>
				</tr>
	
TABELLARILEVATORI;
							break;
						case "marcheveicoli":
	
							echo <<< TABELLAMARCHE
				<tr $stile_riga>
					<td align="center">
						<input type=image src="$imgFreccia" class="$dimImg" alt="Clicca qui per inserire la selezione" onClick="$stringaLink">
					</td>
					<td align=center>$memocodice</td>
					<td align=center>$marcacodice</td>
					<td align=left>$descrizionemarca</td>
					
				</tr>
	
TABELLAMARCHE;
							//alert ($descrizionemarca);
							break;
						case "viacomune":
	
							echo <<< TABELLAVIE
				<tr $stile_riga>
					<td align="center">
						<input type=image src="$imgFreccia" class="$dimImg" alt="Clicca qui per inserire la selezione" onClick="$stringaLink">
					</td>
					<td align=center>$memocodice</td>
					<td align=center>
TABELLAVIE;
							echo htmlentities($nomecompleto);
							echo <<< TABELLAVIE
					</td>
					<td align=center>
						<input class="ricerca" type="button" name="cerca" value="Modifica" onClick="$stringaModif">
					</td>
				</tr>
	
TABELLAVIE;
							//alert ($descrizionemarca);
							break;
						case "targheestere":
	
							echo <<< TABELLARICHIESTETARGHEESTERE
				<tr $stile_riga>
					<td align="center">
						<input type=image src="$imgFreccia" class="$dimImg" alt="Clicca qui per inserire la selezione" onClick="$stringaLink">
					</td>
					<td align=center>$memocodice</td>
					<!--<td align=center>$enterichiesta</td>-->
					<td align=center>$comuneluogo</td>
					<td align=center>$datainfr</td>
					<td align=center>$orainfr</td>
					<td align=center>$targa</td>
					<td align=center>$articolo</td>
					<td align=center>
						<input type="image" src="$imgCestino" class="$dimImg" title="Elimina" onClick="$stringaModif">
					</td>
				</tr>
	
TABELLARICHIESTETARGHEESTERE;
							//alert ($descrizionemarca);
							break;
						case "targheanalizzate":
	
							echo <<< TABELLARICHIESTETARGHEESTERE
				<tr $stile_riga>
					<td align=center>
						<!--<input class="ricerca" type="button" value="Modifica" onClick="$stringaLink">-->
						<input type=image src="$imgFreccia" class="$dimImg" alt="Clicca qui per inserire la selezione" onClick="$stringaLink">
					</td>
					<td align=center>$memocodice</td>
					<td class="font10" align=center>$enterichiesta</td>
					<td class="font11" align=center>$comuneluogo</td>
					<td align=center>$datainfr</td>
					<td align=center>$orainfr</td>
					<td align=center>$targa</td>
					<td class="font10" align=center>$articolo</td>
					<td align=center>
						<input type="image" src="$imgAnnulla" class="$dimImg" title="Annulla" onClick="$stringaAnnulla">
						<input type="image" src="$imgCestino" class="$dimImg" title="Elimina" onClick="$stringaElimina">
					</td>
				</tr>
	
TABELLARICHIESTETARGHEESTERE;
							//alert ($descrizionemarca);
							break;
						case "targhetrasgressore":
	
							echo <<< TABELLARICHIESTETARGHEESTERE
				<tr $stile_riga>
					<td align=center>
						<!--<input class="ricerca" type="button" value="Modifica" onClick="$stringaLink">-->
						<input type=image src="$imgFreccia" class="$dimImg" alt="Clicca qui per inserire la selezione" onClick="$stringaLink">
					</td>
					<td align=center>$memocodice</td>
					<td class="font10" align=center>$enterichiesta</td>
					<td class="font11" align=center>$comuneluogo</td>
					<td align=center>$datainfr</td>
					<td align=center>$orainfr</td>
					<td align=center>$targa</td>
					<td class="font10" align=center>$articolo</td>
					<td align=center>
						<input type="image" src="$imgRitorna" class="$dimImg" title="Riporta a 'richiesta'" onClick="$stringaRiporta">
						<input type="image" src="$imgAnnulla" class="$dimImg" title="Annulla" onClick="$stringaAnnulla">
						<input type="image" src="$imgCestino" class="$dimImg" title="Elimina" onClick="$stringaElimina">
					</td>
				</tr>
	
TABELLARICHIESTETARGHEESTERE;
							//alert ($descrizionemarca);
							break;
						case "targheimportate":
							
							echo <<< TABELLARICHIESTETARGHEESTERE
				<tr $stile_riga>
					<td align="center">
						<input type=image src="$imgFreccia" class="$dimImg" alt="Clicca qui per inserire la selezione" onClick="$stringaLink">
					</td>
					<td align=center>$memocodice</td>
					<td align=center>$enterichiesta</td>
					<td align=center>$comuneluogo</td>
					<td align=center>$cognome</td>
					<td align=center>$nome</td>
					<td align=center>$genere</td>
					<td align=center>$targa</td>
				</tr>
	
TABELLARICHIESTETARGHEESTERE;
							//alert ($descrizionemarca);
							break;
						case "targheOLDestere":
	
							echo <<< TABELLARICHIESTETARGHEESTERE
				<tr $stile_riga>
					<td align=center>$memocodice</td>
					<td align=center>$enterichiesta</td>
					<td align=center>$comuneluogo</td>
					<td align=center>$datainfr</td>
					<td align=center>$orainfr</td>
					<td align=center>$targa</td>
					<td align=center>$articolo</td>
					<td align=center>
					</td>
					<td align=center>
						<input class="ricerca" type="button" name="cerca" value="Riesuma" onClick="$stringaModif">
					</td>
				</tr>
	
TABELLARICHIESTETARGHEESTERE;
							//alert ($descrizionemarca);
							break;
						case "utenti_esteri":
	
							echo <<< TABELLACONTRIBUENTI
				<tr $stile_riga>
					<td align="center">
						<input type=image src="$imgFreccia" class="$dimImg" alt="Clicca qui per inserire la selezione" onClick="$stringaLink">
					</td>
					<td align=center>$memocodice</td>
					<td align=center>$cognomecontrib</td>
					<td align=left>$nomecontrib</td>
					<td align=left>$cfcontrib</td>
					
				</tr>
	
TABELLACONTRIBUENTI;
							//alert ($descrizionemarca);
							break;
						case "preinserimenti":
	
							echo <<< TABELLAPREINSERIMENTI
				<tr $stile_riga>
					<td align="center">
						<input type=image src="$imgFreccia" class="$dimImg" alt="Clicca qui per inserire la selezione" onClick="$stringaLink">
					</td>
					<td align=center>$memocodice</td>
					<td align=center><font size='-1'>$cognomenome</font></td>
					<td align=center>$comuneluogo</td>
					<td align=center>$datainfr</td>
					<td align=center>$orainfr</td>
					<td align=center>$targa</td>
					<td align=center>$articolo</td>
					<td align=center>$immaginePreins</td>
				</tr>
	
TABELLAPREINSERIMENTI;
							//alert ($descrizionemarca);
							break;
						case "verbali":
	
							echo <<< TABELLAVERBALI
				<tr $stile_riga>
					<td align="center">
						<input type=image src="$imgFreccia" class="$dimImg" alt="Clicca qui per inserire la selezione" onClick="$stringaLink">
					</td>
					<td align=center><font size='-1'>$memoVerb/$a</font></td>
					<td align=center><font size='-1'>$memoRiferimento</font></td>
					<td align=center title="$cognomenome"><font size='-1'>$cognomenome</font></td>
					<!--<td align=center>$comuneluogo</td>-->
					<td align=center><font size='-1'>$datainfr</font></td>
					<td align=center><font size='-1'>$orainfr</font></td>
					<td align=center><font size='-1'>$targa</font></td>
					<td align=center><font size='-1'>$articolo</font></td>
					<td align=right><font size='-1'>$importo</font></td>
					<td align=center>
						$immaginePagamento
					</td>
					<td align=center>
						$immagineStampa
					</td>
					<td align=center>
						$immagineAnnullamento
					</td>
				</tr>
	
TABELLAVERBALI;
							//alert ($descrizionemarca);
							break;
						case "motivimancata":
	
							echo <<< TABELLAMOTIVI
				<tr $stile_riga>
					<td align="center">
						<input type=image src="$imgFreccia" class="$dimImg" alt="Clicca qui per inserire la selezione" onClick="$stringaLink">
					</td>
					<td align=center>$memocodice</td>
					<td align=center><font size='-1'>$motDescr</font></td>
					<td align=center><font size='-1'>$motComm</font></td>
					<td align=center>
						<input class="ricerca" type="button" name="cerca" value="Modifica" onClick="$stringaModif">
					</td>
				</tr>
	
TABELLAMOTIVI;
							//alert ($descrizionemarca);
							break;
						case "ente":
	
							echo <<< TABELLAENTI
				<tr $stile_riga>
					<td align="center">
						<input type=image src="$imgFreccia" class="$dimImg" alt="Clicca qui per inserire la selezione" onClick="$stringaLink">
					</td>
					<td align=center>$memocodice</td>
					<td align=center>$numEnte</td>
					<td align=center>$nazEnte</td>
					<td align=center>$regEnte</td>
					<td align=center>$ind1Ente</td>
					<td align=center>
						<input class="ricerca" type="button" name="cerca" value="Modifica" onClick="$stringaModif">
					</td>
				</tr>
	
TABELLAENTI;
							break;
						case "pagamentitemporanei":
	
							echo <<< TABELLAPAGAMENTITEMP
				<tr $stile_riga>
					<td align="center">
						<input type=image src="$imgFreccia" class="$dimImg" alt="Clicca qui per inserire la selezione" onClick="$stringaLink">
					</td>
					<td align=center>$memocodice</td>
					<td align=center>$pagTrasgressore</td>
					<td align=center>$pagDataPag</td>
					<td align=center>$pagImporto</td>
				</tr>
	
TABELLAPAGAMENTITEMP;
							break;
						case "fatture":
	
							echo <<< FATTURE
				<tr $stile_riga>
					<td class="text_center font10" rowspan="2">
						<input type=image src="$imgFreccia" class="$dimImg" alt="Clicca qui per inserire la selezione" onClick="$stringaLink">
					</td>
					<td class="text_center"><b>$fatNumero</b></td>
					<td class="text_center">$fatAnnoBilancio</td>
					<td class="text_center">$comuneluogo</td>
					<td class="text_center"><b>$fatTipo</b></td>
					<td class="text_center">$fatRisc</td>
					<td class="text_center font10">$fatGest</td>
					<td class="text_right"><b>$fatTotale</b></td>
					<td class="text_center font10" rowspan="2">
						$imgEsito
					</td>
				</tr>
				<tr $stile_riga>
					<td class="text_center font11">$fatData</td>
					<td class="text_center">$fatAnnoCompetenza</td>
					<td class="text_left font11" colspan="4">$fatTesto</td>
					<td class="text_right">$fatAccredito</td>
				</tr>
	
FATTURE;
							break;
					}
			
				//$san_nome_temp = addslashes($san_trovata['San_Descrizione']);
				//alert ("sanz " . $memocodice);
				/*$descrizioneconslash = $san_nome_temp;
				$san_trovata['San_Descrizione'] = stripslashes($san_trovata['San_Descrizione']);
				
				$stringaLink = "window.opener.location.href=";
				$stringaLink .= "'cds_modifica_modello.php?sanzcodice=$memocodice&memosanzioneaccessoria=$sanzione1o2';";
				$stringaLink .= " self.close();";
					//alert ($stringaLink);
				echo <<< FINERICERCASANZIONE
					<td width="7%">
					<input type=image src="$imgFreccia" class="$dimImg" alt="Clicca qui per inserire la selezione" onClick="$stringaLink">
					</td>
					<td width=13% align=center>
						$san_trovata[San_Codice]
					</td>
					<td width=70%>
						$san_trovata[San_Descrizione]
					</td>
					<td width=10%>
						<input class="ricerca" type="button" name="cerca" value="Modifica" onClick="javascript:window.open('cds_inserimento_sanzioni_accessorie_new.php?sanzmodif=$san_trovata[San_Progr]','ru_win','width=420, height=300, left=30 top=30, scrollbars=no');self.close();">
					</td>
				</tr>
				
FINERICERCASANZIONE;*/
				}  //  fine if $escudiQuesto
			}
			//echo "</table></center>";
		}
	}
	else //posted diverso da null e da true
	{
		echo "ERRORE di comunicazione tra i form del programma. Contattare l'amministratore del sistema.";
	}
	
?>

			</table>
			</center>
			
			
			<script>
			OrdinaSelectData(<?=$numFiltroData?>);
			OrdinaSelectNomi(<?=$numFiltroNomi?>);
			OrdinaSelectArticoli(<?=$numFiltroArticoli?>);
			OrdinaSelectNomi(<?=$numFiltroTarghe?>);
			OrdinaSelectNomi(<?=$numFiltroRiferimenti?>);
			OrdinaSelectNomi(<?=$numFiltroComuni?>);
			</script>
			
			<script>fine("<?=$trovati?>");</script>
			
			
<script>
function RitornoPagina (num)
{
	var ritornoArray = new Array();
	<?php
	for ($i = 0; $i < count($stringaJS); $i++)
	{
		echo $stringaJS[$i] . "\n";
	}
	?>
	window.returnValue = ritornoArray[num];
	window.close();
}
function RitornoIndietroPagina (num)
{
	var ritornoArray = new Array();
	<?php
	for ($i = 0; $i < count($stringaJSRip); $i++)
	{
		echo $stringaJSRip[$i] . "\n";
	}
	?>
	window.returnValue = ritornoArray[num];
	window.close();
}
</script>
</body>
</html>

<?php 

function RigaIntestazioneScelta($richiesta)
{
	//alert ($richiesta);
	switch ($richiesta)
	{
	case "modello":
		echo <<< RIGAINTESTAZIONE
				<td width="5%" align="center"><b>&nbsp;</b></td>
				<td width="5%" align="center"><b>Codice</b></td>
				<td width="5%" align="center"><b>Strada</b></td>
				<td width="15%" align="center"><b>Via</b></td>
				<td width="15%" align="center"><b>Localita</b></td>
				<td width="10%" align="center"><b>Km</b></td>
				<td width="15%" align="center"><b>Direzione</b></td>
				<td width="15%" align="center"><b>Accertatore</b></td>
				<td width="15%" align="center"><b>Rilevatore</b></td>
				
RIGAINTESTAZIONE;
		break;
	case "rilevatore":
		echo <<< RIGAINTESTAZIONE
				<td width="5%" align="center"><b>&nbsp;</b></td>
				<td width="5%" align="center"><b>Codice</b></td>
				<!--    <td width="15%" align="center"><b>Tipo</b></td>
				<td width="10%" align="center"><b>Marca</b></td>
				<td width="10%" align="center"><b>Modello</b></td>
				<td width="25%" align="center"><b>Omologazione</b></td>      -->
				<td width="70%" align="center"><b>Descrizione</b></td>
				<td width="5%" align="center"><b>Toll.</b></td>
				<td width="10%" align="center"><b>Matr.Foto</b></td>
				<td width="5%" align="center"><b>&nbsp;</b></td>
				
RIGAINTESTAZIONE;
		break;
	case "accertatore":
		echo <<< RIGAINTESTAZIONE
				<td width="8%" align="center"><b>&nbsp;</b></td>
				<td width="8%" align="center"><b>Codice</b></td>
				<td width="30%" align="center"><b>Nome</b></td>
				<td width="18%" align="center"><b>Matricola</b></td>
				<td width="10%" align="center"><b>Firma Dig.</b></td>
				<td width="16%" align="center"><b>&nbsp;</b></td>
				
RIGAINTESTAZIONE;
		break;
	case "sanzione":
		echo <<< RIGAINTESTAZIONE
				<td width="10%" align="center"><b>&nbsp;</b></td>
				<td width="10%" align="center"><b>Codice</b></td>
				<td width="70%" align="center"><b>Descrizione</b></td>
				<td width="20%" align="center"><b>&nbsp;</b></td>
				
RIGAINTESTAZIONE;
		break;
	case "motivi":
		echo <<< RIGAINTESTAZIONE
				<td width="5%" align="center"><b>&nbsp;</b></td>
				<td width="5%" align="center"><b>Codice</b></td>
				<td width="5%" align="center"><b>Motivo</b></td>
				<td width="20%" align="center"><b>Descrizione</b></td>
				<td width="55%" align="center"><b>Oggetto</b></td>
				<td width="10%" align="center"><b>&nbsp;</b></td>
				
RIGAINTESTAZIONE;
		break;
	case "marcheveicoli":
		echo <<< RIGAINTESTAZIONE
				<td width="10%" align="center"><b>&nbsp;</b></td>
				<td width="10%" align="center"><b>Codice</b></td>
				<td width="20%" align="center"><b>Motivo</b></td>
				<td width="60%" align="center"><b>Descrizione</b></td>
				
RIGAINTESTAZIONE;
		break;
	case "viacomune":
		echo <<< RIGAINTESTAZIONE
				<td width="10%" align="center"><b>&nbsp;</b></td>
				<td width="20%" align="center"><b>Codice</b></td>
				<td width="50%" align="center"><b>Via</b></td>
				<td width="20%" align="center"><b>&nbsp;</b></td>
				
RIGAINTESTAZIONE;
		break;
	case "targheestere":
		echo <<< RIGAINTESTAZIONE
				<td width="10%" align="center"><b>&nbsp;</b></td>
				<td width="10%" align="center"><b>Codice</b></td>
				<!--<td width="12%" align="center"><b>Ente</b></td>-->
				<td width="25%" align="center"><b>Comune</b></td>
				<td width="10%" align="center"><b>Data</b></td>
				<td width="10%" align="center"><b>Ora</b></td>
				<td width="12%" align="center"><b>Targa</b></td>
				<td width="13%" align="center"><b>Art.</b></td>
				<td width="10%" align="center"><b>&nbsp;</b></td>
				
RIGAINTESTAZIONE;
		break;
	case "targheanalizzate":
		echo <<< RIGAINTESTAZIONE
				<td width="8%" align="center"><b>&nbsp;</b></td>
				<td width="8%" align="center"><b>Cod.</b></td>
				<td width="12%" align="center"><b>Ente</b></td>
				<td width="18%" align="center"><b>Comune</b></td>
				<td width="11%" align="center"><b>Data</b></td>
				<td width="9%" align="center"><b>Ora</b></td>
				<td width="12%" align="center"><b>Targa</b></td>
				<td width="10%" align="center"><b>Art.</b></td>
				<td width="12%" align="center"><b>&nbsp;</b></td>
				
RIGAINTESTAZIONE;
		break;
	case "targhetrasgressore":
		echo <<< RIGAINTESTAZIONE
				<td width="5%" align="center"><b>&nbsp;</b></td>
				<td width="5%" align="center"><b>Cod.</b></td>
				<td width="12%" align="center"><b>Ente</b></td>
				<td width="18%" align="center"><b>Comune</b></td>
				<td width="11%" align="center"><b>Data</b></td>
				<td width="9%" align="center"><b>Ora</b></td>
				<td width="15%" align="center"><b>Targa</b></td>
				<td width="13%" align="center"><b>Art.</b></td>
				<td width="12%" align="center"><b>&nbsp;</b></td>
				
RIGAINTESTAZIONE;
		break;
	case "targheimportate":
		echo <<< RIGAINTESTAZIONE
				<td width="6%" align="center"><b></b></td>
				<td width="7%" align="center"><b>Codice</b></td>
				<td width="11%" align="center"><b>Ente</b></td>
				<td width="11%" align="center"><b>Comune</b></td>
				<td width="32%" align="center"><b>Cognome</b></td>
				<td width="7%" align="center"><b>Nome</b></td>
				<td width="15%" align="center"><b>Genere</b></td>
				<td width="11%" align="center"><b>Targa</b></td>
				
RIGAINTESTAZIONE;
		break;
	case "targheOLDestere":
		echo <<< RIGAINTESTAZIONE
				<td width="8%" align="center"><b>Codice</b></td>
				<td width="12%" align="center"><b>Ente</b></td>
				<td width="20%" align="center"><b>Comune</b></td>
				<td width="9%" align="center"><b>Data</b></td>
				<td width="9%" align="center"><b>Ora</b></td>
				<td width="12%" align="center"><b>Targa</b></td>
				<td width="10%" align="center"><b>Art.</b></td>
				<td width="10%" align="center"><b>&nbsp;</b></td>
				<td width="10%" align="center"><b>&nbsp;</b></td>
				
RIGAINTESTAZIONE;
		break;
	case "utenti_esteri":
		echo <<< RIGAINTESTAZIONE
				<td width="10%" align="center"><b>&nbsp;</b></td>
				<td width="10%" align="center"><b>Codice</b></td>
				<td width="30%" align="center"><b>Cognome</b></td>
				<td width="20%" align="center"><b>Nome</b></td>
        		<td width="30%" align="center"><b>Cod.Fisc.</b></td>
				
RIGAINTESTAZIONE;
		break;
	case "preinserimenti":
		echo <<< RIGAINTESTAZIONE
				<td width="10%" align="center"><b>&nbsp;</b></td>
				<td width="8%" align="center"><b>Codice</b></td>
				<td width="22%" align="center"><b>Nome</b></td>
				<td width="20%" align="center"><b>Comune</b></td>
				<td width="9%" align="center"><b>Data</b></td>
				<td width="9%" align="center"><b>Ora</b></td>
				<td width="10%" align="center"><b>Targa</b></td>
				<td width="8%" align="center"><b>Art.</b></td>
				<td width="4%" align="center"></td>
				
RIGAINTESTAZIONE;
		break;
	case "verbali":
		echo <<< RIGAINTESTAZIONE
				<td width="3%" align="center"><b>&nbsp;</b></td>
				<td width="10%" align="center"><b>Verb</b></td>
				<td width="12%" align="center"><b>Rif</b></td>
				<td width="21%" align="center"><b>Nome</b></td>
				<!--<td width="20%" align="center"><b>Comune</b></td>-->
				<td width="10%" align="center"><b>Data</b></td>
				<td width="8%" align="center"><b>Ora</b></td>
				<td width="13%" align="center"><b>Targa</b></td>
				<td width="6%" align="center"><b>Art.</b></td>
				<td width="7%" align="right"><b>Importo</b>
				<td width="3%" align="center"><b>&nbsp;</b></td>
        		<td width="3%" align="center"><b>&nbsp;</b></td>
        		<td width="3%" align="center"><b>&nbsp;</b></td>
				
RIGAINTESTAZIONE;
		break;
	case "motivimancata":
		echo <<< RIGAINTESTAZIONE
				<td width="5%" align="center"></td>
				<td width="5%" align="center"><b>Codice</b></td>
				<td width="35%" align="center"><b>Descrizione</b></td>
				<td width="45%" align="center"><b>Commento</b></td>
        		<td width="10%" align="center"></td>
				
RIGAINTESTAZIONE;
		break;
	case "ente":
		echo <<< RIGAINTESTAZIONE
				<td width="5%" align="center"><b>&nbsp;</b></td>
				<td width="5%" align="center"><b>Codice</b></td>
				<td width="15%" align="center"><b>Num</b></td>
				<td width="20%" align="center"><b>Nome</b></td>
				<td width="20%" align="center"><b>Regione</b></td>
				<td width="35%" align="center"><b>Indirizzo</b></td>
				
RIGAINTESTAZIONE;
		break;
	case "pagamentitemporanei":
		echo <<< RIGAINTESTAZIONE
				<td width="5%" align="center"></td>
				<td width="5%" align="center"><b>Codice</b></td>
				<td width="35%" align="center"><b>Pagante</b></td>
				<td width="45%" align="center"><b>Data Pag</b></td>
        		<td width="10%" align="center"><b>Importo</b></td>
				
RIGAINTESTAZIONE;
		break;
	case "fatture":
		echo <<< RIGAINTESTAZIONE
				<td class="width5 text_center"></td>
				<td class="width5 text_center"><b>Fattura</b></td>
				<td class="width8 text_center"><b>Anno</b></td>
				<td class="width20 text_center"><b>Comune</b></td>
				<td class="width15 text_center"><b>Tipo</b></td>
				<td class="width10 text_center"><b>Riscossione</b></td>
				<td class="width20 text_center"><b>Gestione</b></td>
        		<td class="width10 text_right"><b>Importo</b></td>
        		<td class="width7 text_center"><b>Esito</b></td>
				
RIGAINTESTAZIONE;
		break;
	default:
		break;
	}
}

function RigaFiltro($richiesta)
{
	//alert ($richiesta);
	switch ($richiesta)
	{
	case "modello":
		break;
	case "rilevatore":
		break;
	case "accertatore":
		break;
	case "sanzione":
		break;
	case "motivi":
		break;
	case "marcheveicoli":
		break;
	case "viacomune":
		break;
	case "targheestere":
		echo <<< RIGAFILTRO
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"><select class='width90' name="filtro1" onchange="cambiofiltro(1);"><option value=''></option></select></td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"><select class='width90' name="filtro3" onchange="cambiofiltro(3);"><option value=''></option></select></td>
				<td align="center"><select class='width90' name="filtro2" onchange="cambiofiltro(2);"><option value=''></option></select></td>
				<td align="center"></td>
				
RIGAFILTRO;
		break;
	case "targheanalizzate":
		echo <<< RIGAFILTRO
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"><select class='width90' name="filtro1" onchange="cambiofiltro(1);"><option value=''></option></select></td>
				<td align="center"><select class='width90' name="filtro2" onchange="cambiofiltro(2);"><option value=''></option></select></td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"><select class='width90' name="filtro3" onchange="cambiofiltro(3);"><option value=''></option></select></td>
				<td align="center"></td>
				
RIGAFILTRO;
		break;
	case "targhetrasgressore":
		echo <<< RIGAFILTRO
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"><select class='width90' name="filtro1" onchange="cambiofiltro(1);"><option value=''></option></select></td>
				<td align="center"><select class='width90' name="filtro2" onchange="cambiofiltro(2);"><option value=''></option></select></td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"><select class='width90' name="filtro4" onchange="cambiofiltro(4);"><option value=''></option></select></td>
				<td align="center"><select class='width90' name="filtro3" onchange="cambiofiltro(3);"><option value=''></option></select></td>
				<td align="center"></td>
				
RIGAFILTRO;
		break;
	case "targheimportate":
		break;
	case "targheOLDestere":
		break;
	case "utenti_esteri":
		break;
	case "preinserimenti":
		break;
	case "verbali":
		echo <<< RIGAFILTRO
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"><select class='width90' name="filtro4" onchange="cambiofiltro(4);"><option value=''></option></select></td>
				<td align="center"><select class='width90' name="filtro3" onchange="cambiofiltro(3);"><option value=''></option></select></td>
				<td align="center"><select class='width90' name="filtro2" onchange="cambiofiltro(2);"><option value=''></option></select></td>
				<td align="center"></td>
				<td align="center"><select class='width90' name="filtro1" onchange="cambiofiltro(1);"><option value=''></option></select></td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"></td>
				
RIGAFILTRO;
		break;
	case "motivimancata":
		break;
	case "ente":
		echo <<< RIGAFILTRO
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"><select class='width90' name="filtro1" onchange="cambiofiltro(1);"><option value=''></option></select></td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"></td>
				
RIGAFILTRO;
		break;
	case "pagamentitemporanei":
		break;
	case "fatture":
		echo <<< RIGAFILTRO
				<td class="text_center"></td>
				<td class="text_center"></td>
				<td class="text_center"><select class='width90' name="filtro2" onchange="cambiofiltro(2);"><option value=''></option></select></td>
				<td class="text_center"><select class='width90' name="filtro1" onchange="cambiofiltro(1);"><option value=''></option></select></td>
				<td class="text_center"></td>
				<td class="text_center"><select class='width90' name="filtro3" onchange="cambiofiltro(3);"><option value=''></option></select></td>
				<td class="text_center"></td>
				<td class="text_right"></td>
				<td class="text_right"></td>
				
RIGAFILTRO;
		break;
	default:
		break;
	}
}

function AggiungiTendina (&$arrayfiltro, $numeroFiltro, $valore, $scritta, $controllo, $selected)
{
	/*if ($_SESSION['CC_User'] == "***+" && $numeroFiltro == 4)
		alert ($numeroFiltro . " e " . $valore);*/
	$risp = true;
	for ($pppp = 0; $pppp < count($arrayfiltro); $pppp++)
	{
		if ($arrayfiltro[$pppp] == $controllo)
		{
			$risp = false;
			break;
		}
	}
	if ($risp == true)
	{
		if ($selected != "" && $valore == $selected) $scrittaSel = " selected ";
		else $scrittaSel = "";
		$arrayfiltro[] = $valore;
		$optionfiltro = "<option value='" . $valore . "' $scrittaSel title='aaaa'>" . $scritta . "</option>";
		echo "<script>$('[name=filtro$numeroFiltro]').append(\"$optionfiltro\");</script>";
		//alert ($controllo);
	}
}

?>