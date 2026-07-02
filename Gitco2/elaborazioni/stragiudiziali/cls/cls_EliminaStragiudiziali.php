<?php
include_once CLS ."/traits.php";

class EliminaStragiudiziali
{
    protected $cls_db;
    protected $proc_id;
    protected $path;
    protected $callbackError;
    
    function  __construct($cls_db){
            $this->cls_db = $cls_db;
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
    public function PulisciPivot()
    {
        $query = "delete from partita_procedure_pvt where Procedure_Id = $this->proc_id";
        $this->Delete($query);
        return $this;
    }
    public function PulisciStragiudiziali()
    {
        $query = "delete from stragiudiziali where Procedure_Id = $this->proc_id";
        $this->Delete($query);
        return $this;
    }
    public function PulisciProcedure()
    {
        $query = "delete from procedures where Id = $this->proc_id";
        $this->Delete($query);
        return $this;
    }

    public function PulisciFile()
    {
        $path = STRAGIUDIZIALE . "/" . $this->proc_id;
        if (!file_exists($path)) return $this;
        $this->removeDirectory($path);
        return $this;
    }
}

?>