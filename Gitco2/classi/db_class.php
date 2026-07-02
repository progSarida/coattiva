<?php

require $_SERVER['DOCUMENT_ROOT'] . "/Gitco2/percorsi.php";
include_once LIBRERIE . "/funzioni.php";

class db_class //extends mysqli
{
	public function esegui_query($query)
	{
		//bla bla bla	
		$res = mysql_query($query);
		return $res;
	}
	
	public function last_id_query()
	{
		$id = mysql_insert_id();
		return $id;
	}
	
	public function fetch_assoc_query($result)
	{
		$res = mysql_fetch_assoc($result);
		return $res;
	}
	
	public function num_rows_query($result)
	{
		$res = mysql_num_rows($result);
		return $res;
	}
	
	public function inizia_trans()
	{
		//bla bla bla
	}
	
	public function commit_trans()
	{
		//bla bla bla
	}
	
	public function rollback_trans()
	{
		//bla bla bla
	}
}
