<?php

include_once "cls_CreaExcel.php";

class Spese
{
    private $a_tariffe;
    public $totPerPosizione;
    public $a_crediti;
    public $Anno_Cronologico;
    public function __construct($cls_db,$Anno_Cronologico)
    {        
            $query = "SELECT * FROM tariffe_coazione";
            $this->a_tariffe = $cls_db->getResults($cls_db->ExecuteQuery($query),"array","ID");
            $this->Anno_Cronologico = $Anno_Cronologico;
    }
    public function CreaTotali($row)
    {
        for($y=1;$y<=10;$y++){
            if(!empty($row['Spesa_'.$y.'_ID'])){
                $index = $row['Spesa_'.$y.'_ID'];
                $tariffa = $this->a_tariffe[$row['Spesa_'.$y.'_ID']];
                if ($this->Anno_Cronologico<=2022)
                {
                    if($tariffa['Descrizione']=="Progetto di attribuzione del ricavato" ||
                $tariffa['Descrizione']=="Richiesta di copia autentica dell'atto di pignoramento notificato per la trascrizione nei pubblici registri" ||
                $tariffa['Descrizione']=="Iscrizione del fermo/pignoramento di beni mobili registrati nei pubblici registri" ||
                $tariffa['Descrizione']=="Revoca del fermo amministrativo/pignoramento di beni mobili registrati" ||
                $tariffa['Descrizione']=="Stima dei beni pignorati e formazione fascicolo" )
                    continue;
                }
                
                if(!isset($this->a_crediti[$index]))
                    $this->a_crediti[$index] = array("Descrizione"=>$tariffa['Descrizione'], "Totale"=>0);
    
                $this->a_crediti[$index]['Totale']+= $row['Rimborso_'.$y];
                $this->totPerPosizione+= $row['Rimborso_'.$y];
            }
        }
        
    }
}

class RimborsoSpeseStruttura
{

    public $CC;
    public $Anno_Cronologico;
    public $cls_db;
    public $clausole_query;
    public $a_result;
    public $Noresult;
    public $struttura =array();

    function retQuery()
    {
        if(!isset($this->clausole_query)) $this->clausole_query = "";
        $q = "
        SELECT 
        if(U.Genere='D',CONCAT(U.Ditta,IF(SRL.ID>0,CONCAT(' ',SRL.Sigla),'')),CONCAT(U.Cognome,' ',U.Nome)) As Denominazione, 
        PT.Comune_ID AS Partita_Comune_ID,
        if(U.Genere='D',U.Partita_Iva,U.Codice_Fiscale) AS CF_PI,
        concat(IV.Indirizzo,',',IV.Civico,' - ', IV.CAP, ' ' , IV.Comune) as Indirizzo,
        PS.*, PT.Tipo AS Tipo_Riscossione,
        PG.Anno_Cronologico,
        EG.Denominazione as Ente_Affidatario,
        PT.Tipo as Tipo
        FROM sgravio S
        JOIN partita_tributi PT ON PT.ID=S.Partita_ID    
        JOIN utente U ON PT.Utente_ID = U.ID
        LEFT JOIN forma_giuridica_societa AS SRL ON SRL.ID = U.Forma_Giuridica
        join v_indirizzo_con_via as IV on U.ID = IV.Utente_ID 
        JOIN sgravi_documenti SD ON SD.Sgravio_ID=S.ID AND SD.DocumentId is not null
        JOIN document_type DT ON DT.Id=SD.DocumentTypeId AND DT.TableTypeId=2    
        JOIN pignoramento_generale AS PG ON PG.Partita_ID=S.Partita_ID
        JOIN enti_gestiti as EG on EG.CC = PG.CC
        JOIN pignoramento_spese as PS ON PG.ID=PS.Pignoramento_ID
        WHERE S.CC = '$this->CC' AND PG.Anno_Cronologico=$this->Anno_Cronologico
        $this->clausole_query
        order by Denominazione
        ";
        //AND PS.Is_Remitted=0 
        return $q;
    }

    function __construct($cls_db,$CC,$Anno_Cronologico)
    {
        $this->CC = $CC;
        $this->Anno_Cronologico = $Anno_Cronologico;
        $this->cls_db = $cls_db;
        $query = $this->retQuery();
        $this->a_result = $cls_db->getResults($cls_db->ExecuteQuery($query),"array","ID");
    }

    public function CreaStruttura()
    {
        $this->Noresult = count($this->a_result )==0;
        foreach($this->a_result as $row)
        {
            $spese = new Spese($this->cls_db,$this->Anno_Cronologico);
            $spese->CreaTotali($row);
            $rowOut = array(
                "Codice_Ente" => $row["CC"],
                "Ente_Affidatario" => $row["Ente_Affidatario"],
                "Anno" => $row["Anno_Cronologico"],
                "Partita_ID" =>$row["Partita_Comune_ID"],
                "Entrata" =>"",
                "Debitore" =>$row["Denominazione"],
                "CF_PI" =>$row["CF_PI"],
                "Residenza_Sede"=>$row["Indirizzo"],
                "Tipo_Procedure"=>$spese->a_crediti,
                "Totale_Per_Posizione"=>$spese->totPerPosizione,
                "Entrata" => $row["Tipo"]

            );
            $this->struttura[]=$rowOut;
        }
    }

}



class CreaArt17Excel extends CreaExcel
{
    
    
    public $callback;

    
    private function SezioneTesta()
    {
        $creaCella = fn($colonna,$riga,$stringa) =>$this->CreaCella($colonna,$riga,$stringa);

        $creaCella("A",$this->rowCount,"ELENCO RIMBORSI RELATIVI A PROCEDURE CAUTELARI/DI COAZIONE ATTIVATE E NON PAGATE DAI DEBITORI SUCCESSIVAMENTE ALL'ANNO DELLA RICHIESTA");
        $this->rowCount+=2;
        $creaCella("A",$this->rowCount,"CODICE ENTE");
        $creaCella("B",$this->rowCount,"ENTE AFFIDATARIO DEL SERVIZIO");
        $creaCella("C",$this->rowCount,"ANNO");
        $creaCella("D",$this->rowCount,"PARTITA ID");
        $creaCella("E",$this->rowCount,"ENTRATA");
        $creaCella("F",$this->rowCount,"DEBITORE");
        $creaCella("G",$this->rowCount,"C.F./P.IVA");
        $creaCella("H",$this->rowCount,"RESIDENZA/SEDE");
        $creaCella("I",$this->rowCount,"TIPO PROCEDURA");
        $creaCella("J",$this->rowCount,"IMPORTO");
        $creaCella("K",$this->rowCount,"TOTALE PER POSIZIONE");

        $this->FaiBold("A1","A$this->rowCount:K$this->rowCount");
        $this->rowCount++;


    }
    private function SezioneCoda()
    {
        $creaCella = fn($colonna,$riga,$stringa) =>$this->CreaCella($colonna,$riga,$stringa);
        $creaCella("I",$this->rowCount,"TOTALE GENERALE");
        $this->FaiBold("I$this->rowCount");
        $this->FaiRightAlign("I$this->rowCount");

        // in K$this->rowCount posso mettere formula
        $pos = $this->rowCount-1;

        //raddoppio per richiesta Sambuceti
        
        $creaCella("J",$this->rowCount,"=SUM(J1:J$pos)");
        $this->FaiValuta("J$this->rowCount");
        $this->FaiBold("J$this->rowCount");

        $creaCella("K",$this->rowCount,"=SUM(J1:J$pos)");
        $this->FaiValuta("K$this->rowCount");
        $this->FaiBold("K$this->rowCount");
    }


    public function CreaExcel(RimborsoSpeseStruttura $rimborsoSpese )
    {
        $creaCella = fn($colonna,$riga,$stringa) =>$this->CreaCella($colonna,$riga,$stringa);
        $this->rowCount = 1;
        $tot = count($rimborsoSpese->struttura);
        $this->SezioneTesta();
        foreach($rimborsoSpese->struttura as $i=>$partita)
        {
            call_user_func($this->callback,$i,$tot);
            $a_procedure = $partita["Tipo_Procedure"];
            foreach($a_procedure as $i=>$procedure)
            {
                $creaCella("A",$this->rowCount,$partita["Codice_Ente"]);
                $creaCella("B",$this->rowCount,$partita["Ente_Affidatario"]);
                $creaCella("C",$this->rowCount,$partita["Anno"]);
                $creaCella("D",$this->rowCount,$partita["Partita_ID"]);
                $creaCella("E",$this->rowCount,$partita["Entrata"]);
                $creaCella("F",$this->rowCount,$partita["Debitore"]);
                $creaCella("G",$this->rowCount,$partita["CF_PI"]);
                $creaCella("H",$this->rowCount,$partita["Residenza_Sede"]);
                $creaCella("I",$this->rowCount,$procedure["Descrizione"]);
                $creaCella("J",$this->rowCount,$procedure["Totale"]);
                $this->FaiValuta("J$this->rowCount");
                $this->rowCount++;
            }
            $posTotale = $this->rowCount-1;
            $creaCella("K",$posTotale,$partita["Totale_Per_Posizione"]);
            $this->FaiValuta("K$posTotale");

        }
        $this->SezioneCoda();
    }


}
?>