<?php

require CONTROLLERS."/Ruolo.php";
require_once MODELS."/PartitaTributi.php";

class DatiPignoramentoController extends RuoloController{

    public $a_pvt_pignoramento;
    public $a_pvt_datori_lavoro;
    public $a_pvt_banche;
    public $a_pvt_immobili;

    public $Utente_ID;
    public $a_partita;

    public function __construct($partita_ID)
	{
		parent::__construct($partita_ID);

        if(empty($partita_ID))
        return;

        $this->a_partita = PartitaTributi::getById($partita_ID);
        $a_render['partita'] = $this->a_partita;

        $this->addRenderParams($a_render);

		$this->Utente_ID = $this->a_partita['Utente_ID'];
        $this->setViews();

	}

    /**
     * * RENDERIZZA TIPO PIGNORAMENTO
     */
    public function showDocumentTypePignoramento(){
        return $this->returnView('pignoramento/preinserimento/documentType');
    }

    public function setDocumentTypePignoramento(){
        $this->getDocumentTypePignoramento();
        $a_docTypes = $this->getRows("SELECT * FROM document_type WHERE ListId = 2 AND Id in (22,7,8) ORDER BY TableTypeId DESC","Id");
        if(empty($this->a_pvt_pignoramento['DocumentTypeId']))
            $this->a_pvt_pignoramento['DocumentTypeId'] = null;
        $a_selection = array("value" => "Id", "firstOpt" => 1, "selected" => $this->a_pvt_pignoramento['DocumentTypeId'], "text" => array("[Description]"));
        $a_render['optDocumentTypes'] = $this->html->getOptions($a_docTypes, $a_selection);
        $a_render['form_documentType']['action'] = WEB_ROOT."/coattiva/document_type_pignoramento_salva.php";
        $this->addRenderParams($a_render);
    }

    public function getDocumentTypePignoramento(){
        $query = "SELECT P.*, D.Description AS DocumentType FROM pignoramento_pvt P JOIN document_type D ON D.Id = P.DocumentTypeId WHERE P.Partita_ID = ".$this->Partita_ID;
        $this->a_pvt_pignoramento = $this->getRow($query);
    }

    public function saveDocumentTypePignoramento($documentTypeId){

        try{
            if(empty($documentTypeId)){
                $this->db->delete('pignoramento_pvt','Partita_ID='.$this->Partita_ID);
            }
            else{
                $query = "INSERT INTO pignoramento_pvt (Partita_ID,DocumentTypeId,Data_Aggiornamento) ".
                "VALUES (".$this->Partita_ID.",".$documentTypeId.",'".date('Y-m-d')."') ON DUPLICATE KEY UPDATE DocumentTypeId=".$documentTypeId.";";

                $this->db->ExecuteQuery($query);
            }
            $error = 0;
            $msg = "Salvataggio avvenuto con successo!";
        }
        catch(Exception $e){
            $this->db->Rollback();
            $error = 1;
            $msg = "Salvataggio fallito!";
        }

        return array("error"=>$error,"msg"=>$msg);
    }


    /**
     * * RENDERIZZA DATORI DI LAVORO
     */
    public function showDatoriLavoro(){
        return $this->returnView('pignoramento/preinserimento/datorilavoro');
    }

    public function showDatoreLavoro($rowkey){
        $query = "SELECT * FROM contratti_lavoro";
        $a_contracts = $this->getRows($query);
        $a_selection = array("value" => "Name", "firstOpt" => 1, "selected" => null, "text" => array("[Description]"));
        $a_render['keyPvt'] = $rowkey;
        $a_render['optContratti'] = $this->html->getOptions($a_contracts, $a_selection);
        $this->addRenderParams($a_render);
        return $this->returnView('pignoramento/preinserimento/datorelavoro');
    }

    public function getDatoriLavoro(){
        $query = "SELECT T.*, U.Ditta as Denominazione_Datore_Lavoro, U.Comune_ID AS Terzo_Comune_ID FROM terzo_pvt T ".
        "JOIN utente U ON U.ID=T.Terzo_ID WHERE T.Utente_ID = ".$this->Utente_ID;
        $this->a_pvt_datori_lavoro = $this->getRows($query);
    }

    private function setDatoriLavoro(){
        $this->getDatoriLavoro();
        $a_render['pvt_datori_lavoro'] = $this->a_pvt_datori_lavoro;
        $query = "SELECT * FROM contratti_lavoro";
        $a_contracts = $this->getRows($query);
        foreach($this->a_pvt_datori_lavoro as $pvtId=>$datoreLavoro){            
            $a_selection = array("value" => "Name", "firstOpt" => 1, "selected" => $datoreLavoro['Tipo_Contratto_Lavoro'], "text" => array("[Description]"));
            $a_render['pvt_datori_lavoro'][$pvtId]['optContratti'] = $this->html->getOptions($a_contracts, $a_selection);
        }
        $a_render['form_datore_lavoro']['action'] = WEB_ROOT."/coattiva/dati_datore_lavoro_salva.php";
        $a_render['form_datore_lavoro']['delete'] = WEB_ROOT."/coattiva/dati_datore_lavoro_elimina.php";
        $this->addRenderParams($a_render);
    }

    public function saveDatoriLavoro($a_data){
        
        $this->db->Start_Transaction();
        $this->db->Begin_Transaction();
        $a_dataDT = $this->db->getColumnDataTypes("terzo_pvt");
        try{
            foreach($a_data as $data){
                if(empty($data['Terzo_ID']))
                    continue;
                $a_where = null;
        
                $data['Utente_ID'] = $_REQUEST['Utente_ID'];
                $data['CC'] = $this->c;
                $data['Data_Aggiornamento'] = date('Y-m-d');
                $data['Data_Costituzione_Ditta_Lavoro'] = $this->help->toDbDate($data['Data_Costituzione_Ditta_Lavoro']);
                $data['Data_Ditta_Operativa_Lavoro'] = $this->help->toDbDate($data['Data_Ditta_Operativa_Lavoro']);
                $data['Data_Dipendenze_Lavoro'] = $this->help->toDbDate($data['Data_Dipendenze_Lavoro']);
        
                if($data['ID']>0)
                    $a_where = array("ID"=>(!empty($data['ID']))?$data['ID']:null);
                
                $this->db->DbSave($this->db->GetObjectQuery("terzo_pvt", $data, $a_dataDT, $a_where));
            }
            
            $this->db->End_Transaction();
            $error = 0;
            $msg = "Salvataggio avvenuto con successo!";
        }
        catch(Exception $e){
            $this->db->Rollback();
            $error = 1;
            $msg = "Salvataggio fallito!";
        }

        return array("error"=>$error,"msg"=>$msg);
    }

    public function deleteDatoreLavoro($id){
        $this->db->delete('terzo_pvt','ID='.$id);
    }



    /**
    * * RENDERIZZA BANCHE
    */
    public function showBanche(){
        return $this->returnView('pignoramento/preinserimento/banche');
    }
    public function showBancheAll(){
        return $this->returnView('pignoramento/preinserimento/bancheAll');
    }

    public function showBanca($rowkey){
        $query = "SELECT * FROM titoli_banca";
        $a_titoli = $this->getRows($query);
        $a_selection = array("value" => "Name", "firstOpt" => 1, "selected" => null, "text" => array("[Description]"));
        $a_render['keyPvt'] = $rowkey;
        $a_render['optTitoli'] = $this->html->getOptions($a_titoli, $a_selection);
        $this->addRenderParams($a_render);
        return $this->returnView('pignoramento/preinserimento/banca');
    }

    public function getBanche(){
        $query = "SELECT BP.*, B.Denominazione as Denominazione_Banca FROM banche_pvt BP ".
        "JOIN banca B ON B.ID=BP.Terzo_ID WHERE BP.Utente_ID = ".$this->Utente_ID;
        $this->a_pvt_banche = $this->getRows($query);
    }

    public function setAllBanche()
    {
        //$this->getBanche();
        $IsExist = function($terzo_id) 
        {

            foreach($this->a_pvt_banche as $pvtId=>$banca){  
                
                if($banca['Terzo_ID']==$terzo_id)
                    return true;
            }
            return false;
        };

        

        $query = "SELECT BR.banca_id AS Terzo_ID, B.Denominazione AS Denominazione_Banca
                FROM utente as U
                JOIN indirizzo AS RES ON RES.Utente_ID = U.ID AND RES.Tipo='res'
                JOIN province_lista AS PL ON RES.Provincia = PL.Pro_Sigla
                JOIN regioni_lista AS RL ON RL.Reg_Codice = PL.Pro_Codice_Regione
                JOIN banca_regione AS BR ON BR.reg_codice = RL.Reg_Codice
                JOIN banca AS B ON B.ID = BR.banca_id AND B.Tipo_Banca = 'sede' AND disabled != 1
                WHERE U.ID = ".$this->Utente_ID.";";
        
        //$query = "SELECT ID as Terzo_ID, Denominazione as Denominazione_Banca FROM gitco2.banca where Tipo_Banca = 'sede'";
        $a_banche = $this->getRows($query);
        
        $banche_inserite = array();
        foreach($a_banche as $id=>$banca)
        {
            if(!$IsExist($banca['Terzo_ID']))
            {
                $pvt = array();
                $pvt['CC'] = $this->c;
                $pvt['Utente_ID'] = $this->Utente_ID;
                $pvt['Terzo_ID'] = $banca['Terzo_ID'];
                $pvt['Denominazione_Banca'] = $banca['Denominazione_Banca'];
                $pvt['Fonte_Dati'] = null;
                $pvt['Tipo_Titolo_Banca'] = null;
                $pvt['Note'] = null;
                $pvt['Titolo_Banca'] = null;
                $pvt['Intestatario_Banca'] = null;
                $pvt['Coointestatari_Banca'] = null;
                $pvt['Elaboration_Id'] = null;
                $pvt['Data_Aggiornamento'] = date('Y-m-d');
                $banche_inserite[]  = $pvt;
            }
        }
        $a_render['pvt_banche'] = $banche_inserite;
        $a_render['tutti_i_terzi'] = array_merge($this->a_pvt_banche,$banche_inserite);
        $a_render['numeroBanche'] = count($this->a_pvt_banche);
        $query = "SELECT * FROM titoli_banca";
        $a_titoli = $this->getRows($query);
        foreach($banche_inserite as $pvtId=>$banca){            
            $a_selection = array("value" => "Name", "firstOpt" => 1, "selected" => $banca['Tipo_Titolo_Banca'], "text" => array("[Description]"));
            $a_render['pvt_banche'][$pvtId]['optTitoli'] = $this->html->getOptions($a_titoli, $a_selection);
        }
  
        
        $this->addRenderParams($a_render);
        return $this->showBancheAll();

    }

    private function setBanche(){
        $this->getBanche();
        
        $a_render['pvt_banche'] = $this->a_pvt_banche;
        $query = "SELECT * FROM titoli_banca";
        $a_titoli = $this->getRows($query);
        foreach($this->a_pvt_banche as $pvtId=>$banca){            
            $a_selection = array("value" => "Name", "firstOpt" => 1, "selected" => $banca['Tipo_Titolo_Banca'], "text" => array("[Description]"));
            $a_render['pvt_banche'][$pvtId]['optTitoli'] = $this->html->getOptions($a_titoli, $a_selection);
        }
        $a_render['form_banca']['action'] = WEB_ROOT."/coattiva/dati_banca_salva.php";
        $a_render['form_banca']['delete'] = WEB_ROOT."/coattiva/dati_banca_elimina.php";
        $a_render['form_banca']['deleteAll'] = WEB_ROOT."/coattiva/dati_banca_elimina_All.php";
        $a_render['form_banca']['existing'] = WEB_ROOT."/coattiva/dati_banca_esistono.php";
        $a_render['form_banca']['creaPigno'] = WEB_ROOT."/elaborazioni/pignoramenti_banche/elab_check_pignoramento_banche.php";
        $a_render['form_banca']['data_elab'] = date('Y-m-d');
        $a_render['form_banca']['data_int'] = date('Y-m-d');
        $this->addRenderParams($a_render);
    }

    public function saveBanche($a_data){
        
        $this->db->Start_Transaction();
        $this->db->Begin_Transaction();
        $a_dataDT = $this->db->getColumnDataTypes("banche_pvt");
        try{
            foreach($a_data as $data){
                if(empty($data['Terzo_ID']))
                    continue;
                $a_where = null;
        
                $data['Utente_ID'] = $_REQUEST['Utente_ID'];
                $data['Data_Aggiornamento'] = date('Y-m-d');
                $data['CC'] = $this->c;
        
                if($data['ID']>0)
                    $a_where = array("ID"=>(!empty($data['ID']))?$data['ID']:null);
                
                $this->db->DbSave($this->db->GetObjectQuery("banche_pvt", $data, $a_dataDT, $a_where));
            }
            
            $this->db->End_Transaction();
            $error = 0;
            $msg = "Salvataggio avvenuto con successo!";
        }
        catch(Exception $e){
            $this->db->Rollback();
            $error = 1;
            $msg = "Salvataggio fallito!";
        }

        return array("error"=>$error,"msg"=>$msg);
    }

    public function deleteBanca($id){
        $this->db->delete('banche_pvt','ID='.$id);
    }

    public function existingRows($id)
    {
        $ret = $this->db->getResults($this->db->ExecuteQuery("Select * from banche_pvt where Utente_ID = $id"));
        return count($ret)>0;
    }
    public function deleteBancaAll($Utente_ID){
        $this->db->Start_Transaction();
        $this->db->Begin_Transaction();
        try{
            $this->db->delete('banche_pvt','Utente_ID='.$Utente_ID);
            $this->db->End_Transaction();
            $error = 0;
            $msg = "Salvataggio avvenuto con successo!";
        }
        catch(Exception $e){
            $this->db->Rollback();
            $error = 1;
            $msg = "Salvataggio fallito!";
        }

        return array("error"=>$error,"msg"=>$msg);
    }

    /**
    * * RENDERIZZA IMMOBILI
    */
    public function showImmobili(){
        return $this->returnView('pignoramento/preinserimento/immobili');
    }

    public function showImmobile($rowkey){
        $query = "SELECT * FROM tipi_immobile";
        $a_tipi_immobile = $this->getRows($query);
        $a_selection = array("value" => "Name", "firstOpt" => 0, "selected" => null, "text" => array("[Description]"));
        $a_render['optTipo'] = $this->html->getOptions($a_tipi_immobile, $a_selection);
        $query = "SELECT * FROM categorie_fabbricato";
        $a_categorie_fabbricato = $this->getRows($query);
        $a_selection = array("value" => "Name", "firstOpt" => 1, "selected" => null, "text" => array("[Description]"));
        $a_render['optCategoria'] = $this->html->getOptions($a_categorie_fabbricato, $a_selection);
        $a_render['keyPvt'] = $rowkey;
        
        $this->addRenderParams($a_render);
        return $this->returnView('pignoramento/preinserimento/immobile');
    }

    public function getImmobili(){
        $query = "SELECT IP.* FROM immobili_pvt IP WHERE IP.Utente_ID = ".$this->Utente_ID;
        $this->a_pvt_immobili = $this->getRows($query);
    }

    private function setImmobili(){
        $this->getImmobili();
        $a_render['pvt_immobili'] = $this->a_pvt_immobili;
        $query = "SELECT * FROM tipi_immobile";
        $a_tipi_immobile = $this->getRows($query);
        $query = "SELECT * FROM categorie_fabbricato";
        $a_categorie_fabbricato = $this->getRows($query);
        foreach($this->a_pvt_immobili as $pvtId=>$immobile){            
            $a_selection = array("value" => "Name", "firstOpt" => 0, "selected" => $immobile['Tipo_Immobiliare'], "text" => array("[Description]"));
            $a_render['pvt_immobili'][$pvtId]['optTipo'] = $this->html->getOptions($a_tipi_immobile, $a_selection);
            $a_selection = array("value" => "Name", "firstOpt" => 1, "selected" => $immobile['Categoria_Fabbricato'], "text" => array("[Description]"));
            $a_render['pvt_immobili'][$pvtId]['optCategoria'] = $this->html->getOptions($a_categorie_fabbricato, $a_selection);
        }
        $a_render['form_immobile']['action'] = WEB_ROOT."/coattiva/dati_immobile_salva.php";
        $a_render['form_immobile']['delete'] = WEB_ROOT."/coattiva/dati_immobile_elimina.php";
        $this->addRenderParams($a_render);
    }

    public function saveImmobili($a_data){
        
        $this->db->Start_Transaction();
        $this->db->Begin_Transaction();
        $a_dataDT = $this->db->getColumnDataTypes("immobili_pvt");
        try{
            foreach($a_data as $data){
                $a_where = null;
        
                $data['Utente_ID'] = $_REQUEST['Utente_ID'];
                $data['Data_Aggiornamento'] = date('Y-m-d');
                $data['CC'] = $this->c;
        
                if($data['ID']>0)
                    $a_where = array("ID"=>(!empty($data['ID']))?$data['ID']:null);
                
                $this->db->DbSave($this->db->GetObjectQuery("immobili_pvt", $data, $a_dataDT, $a_where));
            }
            
            $this->db->End_Transaction();
            $error = 0;
            $msg = "Salvataggio avvenuto con successo!";
        }
        catch(Exception $e){
            $this->db->Rollback();
            $error = 1;
            $msg = "Salvataggio fallito!";
        }

        return array("error"=>$error,"msg"=>$msg);
    }

    public function deleteImmobile($id){
        $this->db->delete('immobili_pvt','ID='.$id);
    }

    /**
    * * CARICAMENTO DATI PER HTML
    */
    private function setViews(){
        $this->setDocumentTypePignoramento();
        $this->setDatoriLavoro();
        $this->setBanche();
        $this->setImmobili();

    }


   

}