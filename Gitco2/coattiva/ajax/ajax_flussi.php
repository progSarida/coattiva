<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT . "/_parameter.php"); //dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";
include_once CLS."/cls_LOG.php";



$cls_db = new cls_db();
$cls_help = new cls_help();
$log = new LOG();

unset($_SESSION['flussi_arr']);

$num_flusso = $cls_help->getVar('num_flusso');
$anno_flusso = $cls_help->getVar('anno_flusso');
$status = $cls_help->getVar('flowStatus');
$c = $cls_help->getVar('c');
$auth = $cls_help->getVar('aut');
$cod_catastale = $cls_help->getVar('cod_comune');






if (is_null($num_flusso) || is_null($anno_flusso) || is_null($status) || (is_null($cod_catastale) && $auth === 1))
{
	echo json_encode(['message' => 'KO_DATI_INESISTENTI']);
	return;
}

$num_flusso = trim($num_flusso);
$anno_flusso = trim($anno_flusso);
$status = trim($status);
$cod_catastale = trim($cod_catastale);



	if($auth > 1)
	{

					
			if(((empty($anno_flusso) && !empty($status) && empty($num_flusso)))) //STATUS VALORIZZATO  NUMERO FLUSSO E ANNO  NON VALORIZZATI
					{
						echo json_encode(['message' => 'KO_OPERAZIONE_NON_VALIDA']);
						return;
					}	
			if(	!((!empty($num_flusso) && !empty($anno_flusso) && is_numeric($num_flusso) && is_numeric($anno_flusso) && !empty($status) && is_numeric($status) ) || // Tutti VALORIZZATI E NUMERICI	
						(empty($num_flusso) && !empty($anno_flusso) && is_numeric($anno_flusso) && empty($status)) || // ANNO VALORIZZATO NUMERO FLUSSO E STATUS NON VALORIZZATI
						(empty($anno_flusso) && !empty($num_flusso) && is_numeric($num_flusso) && empty($status))|| // NUMERO FLUSSO VALORIZZATO  ANNO E STATUS NON VALORIZZATI
						(empty($anno_flusso) && !empty($status) && is_numeric($status) && empty($num_flusso))|| // STATUS VALORIZZATO  NUMERO FLUSSO E ANNO  NON VALORIZZATI
						(!empty($anno_flusso) && !empty($status) && is_numeric($status)  && is_numeric($anno_flusso) && empty($num_flusso))|| // STATUS E anno  VALORIZZATI  NUMERO FLUSSO   NON VALORIZZATO
						(!empty($anno_flusso) && !empty($num_flusso) && is_numeric($num_flusso)  && is_numeric($anno_flusso) && empty($status))|| // NUMERO FLUSSO E ANNO  VALORIZZATI  NUMERO FLUSSO   NON VALORIZZATO
						(!empty($status) && !empty($num_flusso) && is_numeric($num_flusso)  && is_numeric($status) && empty($anno_flusso)) // STATUS E ANNO  VALORIZZATi  NUMERO FLUSSO   NON VALORIZZATO
					)
				)
				{
					echo json_encode(['message' => 'KO_DATI_NON_VALIDI']);
					return;
				}
			
	}
	else
	{
		
			if(((empty($anno_flusso) && !empty($status) && empty($num_flusso) && empty($cod_catastale)))) //STATUS VALORIZZATO - NUMERO FLUSSO, ANNO FLUSSO E CODICE CATASTALE NON VALORIZZATI
			{
				echo json_encode(['message' => 'KO_OPERAZIONE_NON_VALIDA']);
				return;
			}

			if(!(
					// TUTTI VALORIZZATI 	
					(!empty($num_flusso) && !empty($anno_flusso) && is_numeric($num_flusso) && is_numeric($anno_flusso) && !empty($status) && is_numeric($status) && !empty($cod_catastale)) || 

					//ALMENO UNO è VALORIZZATO
					(empty($num_flusso) && !empty($anno_flusso) && is_numeric($anno_flusso) && empty($status)  && empty($cod_catastale)) || // ANNO VALORIZZATO - NUMERO FLUSSO, STATUS E CODICE CATASTALE  NON VALORIZZATI
					(empty($anno_flusso) && !empty($num_flusso) && is_numeric($num_flusso) && empty($status)  && empty($cod_catastale))|| // NUMERO FLUSSO VALORIZZATO - ANNO FLUSSO, STATUS E CODICE CATASTALE NON VALORIZZATI
					(empty($anno_flusso) && empty($num_flusso)  && empty($status)  && !empty($cod_catastale))|| // CODICE CATASTALE VALORIZZATO - ANNO FLUSSO, STATUS E NUMERO FLUSSO  NON VALORIZZATI
					
					//DUE SONO VALORIZZATI
					(!empty($anno_flusso) && !empty($status)  && is_numeric($anno_flusso) && empty($num_flusso) && empty($cod_catastale))|| // STATUS E ANNO  VALORIZZATI  NUMERO FLUSSO NON VALORIZZATO
					(!empty($anno_flusso) && !empty($num_flusso) && is_numeric($num_flusso)  && is_numeric($anno_flusso) && empty($status) && empty($cod_catastale))|| // NUMERO FLUSSO E ANNO  VALORIZZATI  NUMERO FLUSSO   NON VALORIZZATO
					(!empty($status) && !empty($num_flusso) && is_numeric($num_flusso)  && empty($anno_flusso) && empty($cod_catastale)) || // STATUS E ANNO  VALORIZZATi  NUMERO FLUSSO   NON VALORIZZATO
					(!empty($status) && !empty($cod_catastale) && empty($num_flusso) && empty($anno_flusso)) || 
					(!empty($anno_flusso) && is_numeric($anno_flusso) && !empty($cod_catastale) && empty($num_flusso)  && empty($status))||
					(!empty($num_flusso) && is_numeric($num_flusso) && !empty($cod_catastale) && empty($anno_flusso) && empty($status) )||

					//TRE  SONO VALORIZZATI
					(!empty($anno_flusso) && !empty($num_flusso) && is_numeric($num_flusso)  && is_numeric($anno_flusso) && !empty($cod_catastale) && empty($status))|| // NUMERO FLUSSO, ANNO FLUSSO, CODICE CATASTALE  VALORIZZATI - STATUS NON VALORIZZATO
					(!empty($anno_flusso) && !empty($num_flusso) && is_numeric($num_flusso)  && is_numeric($anno_flusso) && empty($cod_catastale) && !empty($status) )|| // NUMERO FLUSSO, ANNO FLUSSO,  STATUS  VALORIZZATI - CODICE CATASTALE  NON VALORIZZATO
					(!empty($anno_flusso) && empty($num_flusso) && is_numeric($anno_flusso)  && !empty($cod_catastale) && !empty($status) )|| //  ANNO FLUSSO, CODICE CATASTALE, STATUS  VALORIZZATI -  NUMERO FLUSSO NON VALORIZZATO
					(empty($anno_flusso) && !empty($num_flusso) && is_numeric($num_flusso)  && !empty($cod_catastale) && !empty($status) ) // NUMERO FLUSSO, STATUS , CODICE CATASTALE  VALORIZZATI -  ANNO FLUSSO NON VALORIZZATO
					
				))
			{
				
				echo json_encode(['message' => 'KO_DATI_NON_VALIDI']);
				return;
			}
		
	}


$query = " 	SELECT 	f.ID AS flowId, 
						f.Number AS Num_Flusso, 
						f.Year AS Anno_Flusso, 
						f.CityId AS CC, 
						
						f.CreationDate AS CreationDate, 
						f.UploadDate AS UploadDate, 
						f.SendDate AS SendDate, 
						f.CancelDate AS CancelDate, 
						f.PostagePaymentDate AS PostagePaymentDate, 
						f.ProcessingDate AS ProcessingDate,
						f.DocumentTypeId AS DocumentTypeId,
						


						dt.TableTypeId AS TableTypeId,
						dt.Description AS Description,

						
						a.Comune_ID AS Comune_ID, 
						a.Partita_ID AS Partita_ID , 
						a.ID AS actID, 
						a.Atto as Atto, 
						CONCAT(a.ID_Cronologico, '/' , a.Anno_Cronologico) AS Cronologico, 
						a.Data_Notifica as Data_Notifica, "
							. "(SELECT coalesce(MAX(Descrizione), '') "
							. "  FROM parametri_notifica as pn"
							. "  WHERE a.Modalita_Notifica = pn.ID"
							. " ) as Modalita_Not_Descrizione, "
							. "(SELECT coalesce(MAX(Descrizione), '') "
							. " FROM parametri_notifica as pn  "
							. " WHERE a.Stato_Notifica = pn.ID  "
							. " ) as Stato_Not_Descrizione, "
							. "(	SELECT coalesce(MAX(Descrizione), '')  "
							. " 	FROM parametri_notifica as pn	 "
							. " 	WHERE a.Motivo_Notifica = pn.ID	 "
							. " ) as Anomalia_Not_Descrizione, "
							. " pt.Utente_ID AS Utente_ID, 
						
						CONCAT(u.Nome, '  ' , u.Cognome) AS Nome_Cognome, 
						u.Genere as Genere, u.Ditta AS Ditta, pt.Comune_ID AS Com_ID "


							. " 	FROM flows AS f  "
							. " 		LEFT JOIN atto AS a on a.FlowID = f.ID "
							. " 		LEFT JOIN partita_tributi AS pt on pt.ID = a.Partita_ID "
							. "			LEFT JOIN utente AS u on u.ID = pt.Utente_ID  "
							. "			LEFT JOIN document_type AS dt on dt.ID = f.DocumentTypeId "
							. " 	WHERE 1 = 1 ";

							if(intval($auth) > 1 ){
								$query .= " AND f.CityId = '".$c."'";
							}

							if 	(!empty(trim($num_flusso)))
								$query .= " AND f.Number = ".$num_flusso;

							if 	(!empty(trim($anno_flusso)))
								$query .= " AND f.Year = ".$anno_flusso;

							if 	(!empty(trim($cod_catastale)))
								$query .= " AND f.CityId = '".$cod_catastale."'";

							if 	(!empty(trim($status)))
								{
									
									switch ($status) {

										case 1:
											$query .= "  AND  f.CreationDate IS NOT NULL  AND  f.UploadDate IS NULL  AND  f.CancelDate IS NULL AND  f.SendDate IS NULL  AND  f.PostagePaymentDate IS NULL  AND  f.ProcessingDate  IS NULL ";
											break;
										case 2:
											$query .= "  AND  f.UploadDate IS NOT NULL AND  f.CreationDate IS NOT NULL  AND  f.CancelDate IS  NULL AND  f.SendDate IS  NULL  AND  f.PostagePaymentDate IS NULL AND  f.ProcessingDate IS NULL ";
											break;
										case 3:
											$query .= "  AND  f.ProcessingDate IS NOT NULL AND  f.CancelDate IS  NULL  AND  f.SendDate IS  NULL AND  f.PostagePaymentDate IS  NULL ";
											break;
										case 4:
											$query .= "  AND  f.PostagePaymentDate IS NOT NULL  AND  f.CancelDate IS  NULL  AND  f.SendDate IS  NULL ";
											break;
										case 5:
											$query .= "  AND  f.SendDate IS NOT NULL  AND  f.CancelDate IS  NULL  "; 
											break;
										case 6:
											$query .= " AND  f.CancelDate IS NOT NULL  ";
											break;
									}
								}	

						$query .= " AND f.CityID <> 'ZZZZ' ORDER BY Anno_Flusso DESC, Num_Flusso DESC ";

						$results = $cls_db->ExecuteQuery($query);

						$flussi_arr = array();

						if (isset($results))
						 {
							$flussi = $cls_db->getResults($results);

							foreach($flussi as $record)
							{
								$flussi_arr[$record["flowId"]]["flowId"] =  $record["flowId"];
								$flussi_arr[$record["flowId"]]["Num_Flusso"] = $record["Num_Flusso"];
								$flussi_arr[$record["flowId"]]["Anno_Flusso"] = $record["Anno_Flusso"];
								$flussi_arr[$record["flowId"]]["CC"] = $record["CC"];
								$flussi_arr[$record["flowId"]]["CreationDate"] =  $record["CreationDate"];
								$flussi_arr[$record["flowId"]]["UploadDate"] = $record["UploadDate"];
								$flussi_arr[$record["flowId"]]["SendDate"] =  $record["SendDate"];
								$flussi_arr[$record["flowId"]]["CancelDate"] = $record["CancelDate"];
								$flussi_arr[$record["flowId"]]["PostagePaymentDate"] =  $record["PostagePaymentDate"];
								$flussi_arr[$record["flowId"]]["ProcessingDate"] = $record["ProcessingDate"];
								$flussi_arr[$record["flowId"]]["DocumentTypeId"] = $record["DocumentTypeId"];

								$flussi_arr[$record["flowId"]]["document_type"][$record["DocumentTypeId"]]["TableTypeId"] = $record["TableTypeId"];
								$flussi_arr[$record["flowId"]]["document_type"][$record["DocumentTypeId"]]["Description"] = $record["Description"];
								
								$flussi_arr[$record["flowId"]]["Utenti"][ $record["Utente_ID"] ]["Utente_ID"] = $record["Utente_ID"];
								$flussi_arr[$record["flowId"]]["Utenti"][ $record["Utente_ID"] ]["Comune_ID"] = $record["Comune_ID"];
								$flussi_arr[$record["flowId"]]["Utenti"][ $record["Utente_ID"] ]["Com_ID"] = $record["Com_ID"];
								$flussi_arr[$record["flowId"]]["Utenti"][ $record["Utente_ID"] ]["Partita_ID"] = $record["Partita_ID"];
								$flussi_arr[$record["flowId"]]["Utenti"][ $record["Utente_ID"] ]["actID"] = $record["actID"];
								$flussi_arr[$record["flowId"]]["Utenti"][ $record["Utente_ID"] ]["Atto"] = $record["Atto"];
								$flussi_arr[$record["flowId"]]["Utenti"][ $record["Utente_ID"] ]["Cronologico"] = $record["Cronologico"];
								$flussi_arr[$record["flowId"]]["Utenti"][ $record["Utente_ID"] ]["Data_Notifica"] = $record["Data_Notifica"];
								$flussi_arr[$record["flowId"]]["Utenti"][ $record["Utente_ID"] ]["Modalita_Not_Descrizione"] = $record["Modalita_Not_Descrizione"];
								$flussi_arr[$record["flowId"]]["Utenti"][ $record["Utente_ID"] ]["Stato_Not_Descrizione"] = $record["Stato_Not_Descrizione"];
								$flussi_arr[$record["flowId"]]["Utenti"][ $record["Utente_ID"] ]["Anomalia_Not_Descrizione"] = $record["Anomalia_Not_Descrizione"];
								$flussi_arr[$record["flowId"]]["Utenti"][ $record["Utente_ID"] ]["Nome_Cognome"] = $record["Nome_Cognome"];
								$flussi_arr[$record["flowId"]]["Utenti"][ $record["Utente_ID"] ]["Genere"] = $record["Genere"];
								$flussi_arr[$record["flowId"]]["Utenti"][ $record["Utente_ID"] ]["Ditta"] = $record["Ditta"];
							}
						} // if (isset($results))

if(count($flussi_arr) === 0)
{
	echo json_encode( ['message' => 'KO_FLUSSI_NON_TROVATI',]);
	return;
}

$_SESSION['flussi_arr'] = $flussi_arr;

echo json_encode( ['message' => 'OK', 'nf' => $num_flusso, 'af' => $anno_flusso, 'status' => $status, 'cod_catastale' => $cod_catastale ]);
return;








