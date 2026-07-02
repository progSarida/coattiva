<?php
include_once CLS . "/cls_db.php";

class EliminaArt17
{
    
    protected $cls_db;
    protected $proc_id;
    protected $anno_riferimento;
    protected $comune;
    protected $path;
    protected $callbackError;
    
    function  __construct($cls_db){
            $this->cls_db = $cls_db;
    }

    function retQuery()
    {
        $query = "SELECT PS.Pignoramento_ID
            FROM sgravio S
            JOIN partita_tributi PT ON PT.ID=S.Partita_ID    
            JOIN sgravi_documenti SD ON SD.Sgravio_ID=S.ID AND SD.DocumentId is not null
            JOIN document_type DT ON DT.Id=SD.DocumentTypeId AND DT.TableTypeId=2    
            JOIN pignoramento_generale AS PG ON PG.Partita_ID=S.Partita_ID
            JOIN pignoramento_spese as PS ON PG.ID=PS.Pignoramento_ID
            WHERE S.CC = '".$this->comune."' AND PG.Anno_Cronologico=".$this->anno_riferimento;
        return $query;
    }


    public function ResettaPignoramenti()
    {
        $query = $this->retQuery();
        $cls_db = $this->cls_db;

        $a_pg = $cls_db->getResults($cls_db->ExecuteQuery($query));
        
        for($i=0; $i < count($a_pg); $i++){
            $query = "UPDATE pignoramento_spese SET Is_Remitted = 0 WHERE Pignoramento_ID = " . $a_pg[$i]["Pignoramento_ID"];
            $cls_db->ExecuteQuery($query);
        };
        return $this;

    }
    public function SvuotaDirectory()
    {
        $path = PROCEDURE . "/" . $this->proc_id;
        if (!file_exists($path)) return $this;
        $this->removeDirectory($path);
        return $this;
        
    }
    

    public function CancellaProcedura()
    {
        $query = "delete from procedures where Id = $this->proc_id";
        $this->Delete($query);
        return $this;
    }
    public function Set($variabile,$valore)
    {
        $this->{$variabile}= $valore;
        return $this;
    }

    private function Delete($query)
    {
        try {
            // First of all, let's begin a transaction
            $this->cls_db->Start_Transaction();
            $this->cls_db->Begin_Transaction();

            // A set of queries; if one fails, an exception should be thrown
            $this->cls_db->ExecuteQuery($query);
            if($this->callbackError) call_user_func($this->callbackError,"Tutto ok $query");
            $this->cls_db->End_Transaction();

        } catch (Exception $e) {
            // An exception has been thrown
            // We must rollback the transaction
            $this->cls_db->Rollback();
            $this->cls_db->End_Transaction();
            if($this->callbackError) call_user_func($this->callbackError,$e->getMessage());
        }
    }

    private function removeDirectory($path) {

        $files = glob($path . '/*');
        foreach ($files as $file) {
            is_dir($file) ? $this->removeDirectory($file) : unlink($file);
        }
        rmdir($path);
    
        return;
    }
}
?>