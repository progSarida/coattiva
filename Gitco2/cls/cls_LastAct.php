<?php

    class LastAct
    {
        private static function retQuery($utente_id,$limit)
        {
            return "select ".
            "A.DocumentTypeId as LastDocumentTypeID, ".
            "DT.Description as LastDocumentType, ".
            "A.ID_Cronologico as LastIdCronologico, ".
            "A.Anno_Cronologico as LastAnnoCronologico, ".
            "A.Data_Notifica as LastDataNotifica, ".
            "A.Totale_Dovuto + IFNULL(A.Diritto_Riscossione_Massimo,0) as LastTotaleDovuto, ".
            "A.Info_Cartella as InfoCartella ".
            "from atto as A ".
            "join partita_tributi as PT on PT.ID =A.Partita_ID ".
            "join utente as U on PT.Utente_ID=U.ID ".
            "join document_type as DT on A.DocumentTypeId=DT.ID ".
            "Where U.ID = ".$utente_id." AND A.Data_Notifica is not null ".
            "Order by A.Data_Notifica desc ".$limit;
        }

        private static function retQueryPartita($partita_id,$limit)
        {
            return "select ".
            "A.DocumentTypeId as LastDocumentTypeID, ".
            "DT.Description as LastDocumentType, ".
            "A.ID_Cronologico as LastIdCronologico, ".
            "A.Anno_Cronologico as LastAnnoCronologico, ".
            "A.Data_Notifica as LastDataNotifica, ".
            "A.Totale_Dovuto + IFNULL(A.Diritto_Riscossione_Massimo,0) as LastTotaleDovuto, ".
            "A.Info_Cartella as InfoCartella ".
            "from atto as A ".
            "join document_type as DT on A.DocumentTypeId=DT.ID ".
            "Where A.Partita_ID = ".$partita_id." AND A.Data_Notifica is not null ".
            "Order by A.Data_Notifica desc ".$limit;
        }

        public static function GetLastAct(cls_db $cls_db,$utente_id,$limit="LIMIT 1;")
        {
            return $cls_db->getResults($cls_db->ExecuteQuery(self::retQuery($utente_id,$limit)))[0];
        }

        public static function GetLastActByPartita(cls_db $cls_db,$partita_id,$limit="LIMIT 1;")
        {
            return $cls_db->getResults($cls_db->ExecuteQuery(self::retQueryPartita($partita_id,$limit)))[0];
        }
    }
?>