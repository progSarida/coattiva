<?php 
include_once CLS."/cls_db.php";
include_once CLS."/cls_LOG.php";
include_once CLS."/traits.php";

class InserimentoNelDb
{
    protected $cls_db;
    public $a_dati;
    public $table;
    public $log;
    
    //use tSelectSQL;
    protected function UpdateSQL($sql)
    {
        $this->cls_db->ExecuteQuery($sql);
    }
    protected function DeleteSQL($sql)
    {
        $this->cls_db->ExecuteQuery($sql);
    }
    protected function SelectSQL($sql)
    {
        $ret= $this->cls_db->getResults(
            $this->cls_db->ExecuteQuery($sql)
        );
        return $ret;
    }

    function __construct(cls_db $value)
    {
        $this->cls_db = $value;
        $this->log = new LoG();
    } 

    private function GetColumn()
    {
        return $this->cls_db->getColumnDataTypes($this->table);
    }

    public function Inserimento()
    {
        
        $scriviRiga = fn($r) =>$this->cls_db->DbSave(
            $this->cls_db->GetObjectQuery($this->table,
            $r, $this->GetColumn())
        );
        
        $arrayscrivi=function($r,$index) use ($scriviRiga,&$arrayscrivi){
            if($index<count($r))
            {
                $scriviRiga($r[$index]);
                $arrayscrivi($r,$index+1);
            };
        };
        $arrayscrivi($this->a_dati,0);

    }

}