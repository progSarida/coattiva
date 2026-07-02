<?php
require CONTROLLERS."/Controller.php";

class RuoloController extends BaseController{

    public $Partita_ID;
    public $c;
    public $a;
    public $navigation;

    public function __construct($Partita_ID)
	{
		parent::__construct();

		$this->Partita_ID = $Partita_ID;
        $this->c = $_REQUEST['c'];
        $this->a = $_REQUEST['a'];
        // $this->setNavigation();
	}

    private function setNavigation(){
        if(!empty($partita_ID)){
            $query = "SELECT NEXT_P.ID AS next, PREV_P.ID AS prev FROM partita_tributi PT
            LEFT JOIN partita_tributi PREV_P ON PREV_P.ID=(SELECT MAX(ID) FROM partita_tributi WHERE ID<PT.ID AND CC=PT.CC AND Anno_Riferimento = PT.Anno_Riferimento AND Is_Discharged=0)
            LEFT JOIN partita_tributi NEXT_P ON NEXT_P.ID=(SELECT MIN(ID) FROM partita_tributi WHERE ID>PT.ID AND CC=PT.CC AND Anno_Riferimento = PT.Anno_Riferimento AND Is_Discharged=0)
            WHERE PT.ID=".$partita_ID;
        }
        else{
            $query = "SELECT MIN(ID) AS next, MAX(ID) AS prev FROM partita_tributi WHERE CC='".$this->c."' AND Anno_Riferimento = '".$this->a."' AND Is_Discharged=0 ";
        }

        $this->navigation = $this->getRow($query);
    
    }

}