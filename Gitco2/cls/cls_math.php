<?php
	include_once($_SESSION['_path']);
	include_once(ROOT."/_parameter.php");

	include_once CLS."/cls_DateTimeInLine.php";

  class cls_math
  {
    public $cls_date;

    public function __construct()
    {
      $this->cls_date = new cls_DateTimeI("IT",false);
    }

    function conv_num($value)
    {

    	if($value == null)
    		return null;

    	$virgola = strpos($value, ",");
    	$punto = strpos($value, ".");

    	if($virgola != false && $punto != false)
    	{
    		if($virgola < $punto)
    		{
    			$value = str_replace(",", "", $value);
    			$value = str_replace(".", ",", $value);
    		}
    		else
    		{
    			$value = str_replace(".", "", $value);
    		}
    	}
    	else if ($virgola == false && $punto != false)
    	{
    		$value = number_format($value, 2);
    		$value = str_replace(".", ",", $value);
    	}
    	else if ($virgola != false && $punto == false)
    	{
    		$value = str_replace(",", ".", $value);
    	}

    	return (float) $value;

    }

    function next_months ( $date , $num )
    {
    	if( substr($date,2,1) == "/" )
    		$date = $this->cls_date->GetDateDB($date,"IT");

    	$date_array = explode("-",$date);
    	if(checkdate($date_array[1], $date_array[2], $date_array[0]) == false)
    		return false;

    	$iniMonth = number_format($date_array[1]);
    	$stringaDate = "";

    	for($i=0;$i<$num;$i++)
    	{
    		$dateMonth = date('d/m/Y',strtotime(date("Y-m-d", strtotime($date)) . "+".($i+1)." month"));

    		$day = substr($dateMonth,0,2);
    		$month = substr($dateMonth,3,2);
    		$year = substr($dateMonth,6,4);

    	if(checkdate($month, $day, $year) == true)
    	{

    		$prevMonth = ($iniMonth+$i)%12;
    		$nextMonth = ($prevMonth+1)%12;

    		if($prevMonth == 0)
    			$prevMonth = 12;
    		if($nextMonth == 0)
    			$nextMonth = 12;

    		if($nextMonth == $month)
    		{
    			$stringaDate .= $dateMonth."*";
    		}
    		else
    		{
    			$query_date = $year."-".$nextMonth."-".$day;
    			$rightDate = date('t/m/Y', strtotime($query_date));

    			$stringaDate .= $rightDate."*";
    		}
    	}

    	}

    	$stringaDate = substr($stringaDate,0,strlen($stringaDate)-1);

    	return $stringaDate;
    }
  }

?>
