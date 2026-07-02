<?php
trait tSelectSQL {
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
 }
?>