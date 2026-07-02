<?php

class cls_DateTime
{
  private $format;
  private $time;
  private $date;

  public function __construct($Date,$Format,$Time = false)
  {
    $this->time = $Time;
    $this->Select_Format($Format);
    $this->date = DateTime::createFromFormat($this->format, $this->CorrectDate($Date,$Format));
  }

  public function GetDate($Format = false)
  {
    //$date;
    if(!$Format) $date = $this->date->format($this->format);
    else $date = $this->date->format($this->Select_Format_Return($Format));

    if($this->date->format("Y") === "0001") return null;
    $date = str_replace('-',"/",$date);
    return $date;
  }

  public function GetDateDB()
  {
    //$date;
    $date = $this->date->format($this->Select_Format_Return("DB"));

    if($this->date->format("Y") === "0001") return null;//$this->GetAllZero("DB");
    return $date;
  }

  public function CompareDate($Format,$operator,$Date){

    $dateCompare = DateTime::createFromFormat($this->Select_Format_Return($Format), $this->CorrectDate($Date,$Format));

    switch($operator){
      case "==": return $this->date == $dateCompare;
      case ">": return $this->date > $dateCompare;
      case "<": return $this->date < $dateCompare;
      case ">=": return $this->date >= $dateCompare;
      case "<=": return $this->date <= $dateCompare;
    }

  }

  public function GetYear()
  {
    return $this->date->format("Y");
  }

  public function GetMonth()
  {
    return $this->date->format("m");
  }

  public function GetDay()
  {
    return $this->date->format("d");
  }

  public function GetHour()
  {
    if($this->time) return $this->date->format("H");
    else return "00";
  }

  public function GetMinute()
  {
    if($this->time) return $this->date->format("i");
    else return "00";
  }

  public function GetSecond()
  {
    if($this->time) return $this->date->format("s");
    else return "00";
  }

  public function AddYear($value)
  {
    if($value<0) $this->date->modify("-".($value*-1)." year");
    else $this->date->modify("+".$value." year");
  }

  public function AddMonth($value)
  {
    if($value<0) $this->date->modify("-".($value*-1)." month");
    else $this->date->modify("+".$value." month");
  }

  public function AddDay($value)
  {
    if($value<0) $this->date->modify("-".($value*-1)." day");
    else $this->date->modify("+".$value." day");
  }

  public function AbsoluteDateDiff($Date,$Format = "DB"){
    $format = $this->Select_Extern_Format($Format);
    $dateDiff = DateTime::createFromFormat($format, $this->CorrectDate($Date,$Format));

    $dateDiff2 = new DateTime($dateDiff->format($this->Select_Format_Return("DB")));
    $dateDiff1 = new DateTime($this->date->format($this->Select_Format_Return("DB")));

    return (int) $dateDiff1->diff($dateDiff2)->format("%a");
  }

  public function DateDiff($Date,$Format = "DB"){
    $format = $this->Select_Extern_Format($Format);
    $dateDiff = DateTime::createFromFormat($format, $this->CorrectDate($Date,$Format));

    $dateDiff2 = new DateTime($dateDiff->format($this->Select_Format_Return("DB")));
    $dateDiff1 = new DateTime($this->date->format($this->Select_Format_Return("DB")));

    return (int) $dateDiff1->diff($dateDiff2)->format("%r%a");
  }

  private function GetAllZero($Format)
  {
    $Format = $this->Select_Format_Return($Format);
    $DateNull = str_replace("Y","0000",$Format);
    $DateNull = str_replace("d","00",$DateNull);
    $DateNull = str_replace("m","00",$DateNull);

    $DateNull = str_replace("H","00",$DateNull);
    $DateNull = str_replace("i","00",$DateNull);
    $DateNull = str_replace("s","00",$DateNull);

    return $DateNull;
  }

  private function Select_Format($Format)
  {
    $hour = "";

    if($this->time) $hour = " H:i:s";
    else $hour = "";

    switch($Format)
    {
      case "IT": $this->format = "d-m-Y".$hour; break;
      case "USA": $this->format = "m-d-Y".$hour; break;
      case "DB": $this->format = "Y-m-d".$hour; break;
      default: $this->format = "d-m-Y".$hour; break;
    }
  }

  private function Select_Extern_Format($Format)
  {
    $hour = "";

    if($this->time) $hour = " H:i:s";
    else $hour = "";

    switch($Format)
    {
      case "IT": return "d-m-Y".$hour;
      case "USA": return "m-d-Y".$hour;
      case "DB": return "Y-m-d".$hour;
      default: return "d-m-Y".$hour;
    }
  }
  private function Select_Format_Return($Stato)
  {
    $hour = "";
    if($this->time) $hour = " H:i:s";
    else $hour = "";

    switch($Stato)
    {
      case "IT": return "d-m-Y".$hour; break;
      case "USA": return "m-d-Y".$hour; break;
      case "DB": return "Y-m-d".$hour; break;
      default: return "d-m-Y".$hour; break;
    }
  }

  private function BuildRegularExpression($Format)
  {
    $Format = str_replace(" ","",$Format);
    $RegularExpression = "/^";
    for($i=0; $i < strlen($Format) ; $i++)
    {
      switch($Format[$i])
      {
        case "Y": $RegularExpression .= "[0-9]{4}"; break;
        case "m": $RegularExpression .= "[0-9]{2}"; break;
        case "d": $RegularExpression .= "[0-9]{2}"; break;
        case "H": $RegularExpression .= "[0-9]{2}"; break;
        case "i": $RegularExpression .= "[0-9]{2}"; break;
        case "s": $RegularExpression .= "[0-9]{2}"; break;
        case "-": $RegularExpression .= "[-]{1}"; break;
        case ":": $RegularExpression .= "[:]{1}"; break;
      }
    }
    $RegularExpression .= "$/";
    return $RegularExpression;
  }

  private function CorrectDate($data,$Format)
  {
    if (strpos($data, '/')) $data = str_replace('/',"-",$data);
    if($this->time && preg_match('/^[0-9-]{10}$/',str_replace("/","-",str_replace(" ","",$data))))
    {
        $data .= " 00:00:00";
    }
    if(!$this->time && preg_match('/^[0-9]{2}[:]{1}[0-9]{2}[:]{1}[0-9]{2}$/',substr(str_replace(" ","",$data),10,18)))
    {
      $data = substr(str_replace(" ","",$data),0,10);
    }

    if($this->time)
    {
      if(!preg_match($this->BuildRegularExpression($this->Select_Format_Return($Format)),str_replace(" ","",$data))|| $this->CheckAllZero(str_replace(" ","",$data)))
      {
          $Format = $this->Select_Format_Return($Format);
          $DateError = str_replace("Y","0001",$Format);
          $DateError = str_replace("d","01",$DateError);
          $DateError = str_replace("m","01",$DateError);

          $DateError = str_replace("H","00",$DateError);
          $DateError = str_replace("i","00",$DateError);
          $DateError = str_replace("s","00",$DateError);

          return $DateError;
      }
      else return substr(str_replace(" ","",$data),0,10)." ".substr(str_replace(" ","",$data),10,18);
    }
    else
    {
      if(!preg_match($this->BuildRegularExpression($this->Select_Format_Return($Format)),str_replace(" ","",$data)) || $this->CheckAllZero(str_replace(" ","",$data)))
      {
          $Format = $this->Select_Format_Return($Format);
          $DateError = str_replace("Y","0001",$Format);
          $DateError = str_replace("d","01",$DateError);
          $DateError = str_replace("m","01",$DateError);

          return $DateError;
      }
      else return str_replace(" ","",$data);

    }
  }

  private function CheckAllZero($data)
  {
    for($i =0; $i< strlen($data); $i++)
    {
      if($data[$i] !== "0" && $data[$i] !== "-" && $data[$i] !== ":")
        return false;
    }
    return true;
  }

}



 ?>
