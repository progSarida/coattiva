<?php

class cls_date
{
  private $format;
  private $time;

  public function __construct($Stato,$Time = false)
  {
    $this->time = $Time;
    $this->Select_Format($Stato);
  }

  private function Select_Format($Stato)
  {
    $hour = "";

    if($this->time) $hour = " H:i:s";
    else $hour = "";


    switch($Stato)
    {
      case "IT": $this->format = "d/m/Y".$hour; break;
      case "USA": $this->format = "m/d/Y".$hour; break;
      case "CHN": $this->format = "Y/m/d".$hour; break;
      default: $this->format = "d/m/Y".$hour; break;
    }
  }
  private function Select_Format_Return($Stato, $Time = null)
  {
    $hour = "";
    if($Time) $hour = " H:i:s";
    else $hour = "";


    switch($Stato)
    {
      case "IT": return "d/m/Y".$hour; break;
      case "USA": return "m/d/Y".$hour; break;
      case "CHN": return "Y/m/d".$hour; break;
      default: return "d/m/Y".$hour; break;
    }
  }

  private function Get_MysqlFormat($Time)
  {
    $hour = "";
    if($Time) $hour = " H:i:s";

    return "Y/m/d".$hour;
  }

  public function changeFormat($Stato, $Time = false)
  {
    if($Time == true) $this->time = true;
    if($Time == false) $this->time = false;
    Select_Format($Stato);
  }

  public function Get_DateNewFormat($date) {

    if($date == "0000-00-00" || $date == null) return "";

    $strDate = strtotime($date);
    //echo "<h1>Date ".$strDate."</h1></br>";
     return date($this->format, $strDate);
  }

  public function to_MysqlDate($date,$OldFormat,$Time = false)
  {
    $strDate = strtotime($date);
    $format = $this->Select_Format_Return($OldFormat,$Time);
    return date($this->Get_MysqlFormat($Time),strtotime(date($format, $strDate)));
  }
}



 ?>
