<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include LIBRERIE . "/funzioni.php";


die;

$source_db = '10mirko';
$target_db = 'PROTOTIPO';
 
$server='';
$user='root';
$password='';

mysql_connect($server,$user,$password);
mysql_select_db($source_db);

$tables[0]['name'] = "autenticazione" ;
$tables[1]['name'] = "regioni_lista" ;
$tables[2]['name'] = "province_lista" ;
$tables[3]['name'] = "comuni_lista" ;
$tables[4]['name'] = "toponimi_cappati" ;
$tables[5]['name'] = "anni_gestiti" ;
$tables[6]['name'] = "paesi_esteri_lista" ;
$tables[7]['name'] = "comuni_esteri_lista" ;
$tables[8]['name'] = "dug_lista" ;
$tables[9]['name'] = "toponimo" ;
$tables[10]['name'] = "utente" ;
$tables[11]['name'] = "indirizzo" ;
$tables[12]['name'] = "controlli_utente" ;

// Get names of all tables in source database
$result = mysql_query("show tables");
$k=0;
for($k=0;$k<13; $k++)
{
	$name = $tables[$k]['name'];
	$this_result = mysql_query("show create table ".$name);
	$this_row = mysql_fetch_array($this_result);
	$tables[$k] = array('name'=>$name,'query'=>$this_row[1]);
}

$query = "CREATE DATABASE $target_db";
safe_query($query);
// Connect target database to create and populate tables
mysql_select_db($target_db);

$total=count($tables);

for($i=0;$i < $total;$i++)
{
	$name = $tables[$i]['name'];
	$q = $tables[$i]['query'];

	mysql_query($q);
	mysql_query("insert into $name select * from $source_db.$name");
}

echo "<script>alert('Il database è stato creato con successo.');</script>";
 
?>