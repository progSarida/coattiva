<?php
if (!session_id()) session_start();

include_once($_SESSION['_path']);
include_once(ROOT."/_parameter.php");//dati database

include_once CLS . "/cls_db.php";
include_once CLS . "/cls_help.php";


$cls_db = new cls_db();
$cls_help = new cls_help();

if($_SESSION['username']==NULL)
{
    header("Location:/gitco2/autenticazione/accesso_negato.php");
    die;
}

$a = $cls_help->getVar('a');
$c = $cls_help->getVar('c');

$ajax = $cls_help->getVar('ajax');
$cancella_ruolo = $cls_help->getVar('id_ruolo');
switch($ajax)
{
	case "bonifica":

        $query = "SELECT * FROM ruolo WHERE ID = '".$cancella_ruolo."' AND CC = '".$c."'";
		$ruolo = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"ruolo");// new ruolo($cancella_ruolo, $c, $a);

        $query= "SELECT ID FROM partita_tributi WHERE Ruolo_ID = '".$ruolo["ID"]."' AND CC = '".$c."'";
        $partita_id = $cls_db->getResults($cls_db->ExecuteQuery($query));
        //$partita_id = select_mysql_array("ID", "partita_tributi" , "Ruolo_ID = '".$this->ID."' AND CC = '".$c."'");
        $ruolo["Partita"] = array();

        for( $i=0; $i<count($partita_id); $i++ )
        {
            $query = "SELECT * FROM partita_tributi WHERE ID = '".$partita_id[$i]['ID']."' AND CC = '".$c."'";
            $ruolo["Partita"][$i] = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"partita_tributi");// new partita( $partita_id[$i]['ID'] , $c );
        }

		
		$partita = $ruolo["Partita"];
				
		$utente_id = array();
		for($y=0;$y<count($partita);$y++)
		{
            $query = "SELECT * FROM utente WHERE ID = '".$partita[$y]["Utente_ID"]."' AND CC_Comune = '".$c."'";
            $utente = $cls_db->getArrayLineNull($cls_db->ExecuteQuery($query),"utente");
			//$utente = new utente($partita[$y]["Utente_ID"],$c);
			$note = $utente["Note"];
			
			$id = explode("\n**\n", $note);
			
			$num = strlen($cancella_ruolo);
			
			for($i=0;$i<count($id);$i++)
			{
				if(substr($id[$i],0,(9+$num)) == "Ruolo ID ".$cancella_ruolo)
				{
					array_splice($id,$i,1);
					$i--;
				}
			}
			
			$new_note = "";
			for($i=0;$i<count($id);$i++)
			{
				if($id[$i]!="")
					$new_note .= $id[$i]."\n**\n";
			}
            $new_note = str_replace("'","\'",$new_note);

			$query = "UPDATE utente SET Note = '".$new_note."' WHERE ID = ".$utente["ID"];
			$cls_db->ExecuteQuery($query);
			//safe_query($query);
		}
		
		$query = "DELETE FROM ruolo WHERE ID = ".$cancella_ruolo;
        $result = $cls_db->ExecuteQuery($query);
		//safe_query($query);

        if($result) echo "Ruolo ".$cancella_ruolo." eliminato con successo!";
		else echo "Errore! impossibile eliminare il ruolo ".$cancella_ruolo;
		break;
}


?>