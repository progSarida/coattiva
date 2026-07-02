<?php

include_once CLS."/cls_InserimentoNelDB.php";

class LOGImmaginiCAD extends InserimentoNelDb
{
    protected $operatore;
    protected $data_elaborazione;
    protected $nome_file;
    protected $operazione;

    public function InserimentoDati()
    {
        $riga=array(
            "Nome_File"=>$this->nome_file,
            "Operatore"=>$this->operatore,
            "Operazione"=>$this->operazione,
            "Data_Elaborazione"=>$this->data_elaborazione,
            "Tipo"=>"CAD"
        );
        $this->a_dati=array(0=>$riga);
        $this->table = "immagini";
        parent::Inserimento();
        return $this;
    }
    public function Set($nome_variabile,$valore)
    {
        $this->{$nome_variabile}=$valore;
        return $this;
    }
}
?>