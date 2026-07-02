<?php

define("DB_HOST", "");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "gitco2");

abstract class ADatabase {
	
	protected $host      = DB_HOST;
	protected $user      = DB_USER;
	protected $pass      = DB_PASS;
	protected $dbname    = DB_NAME;
	
	abstract public function query($query);
	abstract public function bind($param, $value);
	abstract public function execute();
	abstract public function results();
	abstract public function single();
	abstract public function rowCount();
	abstract public function lastInsertId();
	
	abstract public function beginTransaction();
	abstract public function commit();
	abstract public function rollback();
	abstract public function close();
}


class PdoDB extends ADatabase{
	
	protected $dbh;
	protected $error;	
	protected $stmt;

	public function __construct(){
		// Set DSN
		$dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname;
		// Set options
		$options = array(
				PDO::ATTR_PERSISTENT    => true,
				PDO::ATTR_ERRMODE       => PDO::ERRMODE_EXCEPTION
		);
		
		// Create a new PDO instance
		try{
			$this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
		}
		// Catch any errors
		catch(PDOException $e){
			$this->error = $e->getMessage();
		}
	}
	
	public function query($query){
		$this->stmt = $this->dbh->prepare($query);
	}
	
	public function bind($param, $value, $type = null){
		if (is_null($type)) {
			switch (true) {
				case is_int($value):
					$type = PDO::PARAM_INT;
					break;
				case is_bool($value):
					$type = PDO::PARAM_BOOL;
					break;
				case is_null($value):
					$type = PDO::PARAM_NULL;
					break;
				default:
					$type = PDO::PARAM_STR;
			}
		}
		$this->stmt->bindValue($param, $value, $type);
	}
	
	public function execute(){
		return $this->stmt->execute();
	}
	
	public function results(){
		$this->execute();
		return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function single(){
		$this->execute();
		return $this->stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	public function rowCount(){
		return $this->stmt->rowCount();
	}
	
	public function lastInsertId(){
		return $this->dbh->lastInsertId();
	}
	
	public function beginTransaction(){
		return $this->dbh->beginTransaction();
	}
	
	public function commit(){
		return $this->dbh->commit();
	}
	
	public function rollback(){
		return $this->dbh->rollBack();
	}
	
	public function debugDumpParams(){
		return $this->stmt->debugDumpParams();
		
	}
	
	public function close(){
		return $this->dbh = null;
	}
	
	public function getError() {
		return $this->dbh->errorInfo();
	}
}


class Database extends PdoDB{
	
	public function __construct(){
		parent::__construct();
	}
}

// $database = new Database();

// $database->query("SELECT * FROM utente WHERE ID = :ID");
// $database->bind(':ID', 50);
// $result = $database->single();
// $database->close();

?>
