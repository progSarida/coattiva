<?php

require_once VENDOR.'/autoload.php';
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once CLS.'/cls_db.php';
require_once CLS.'/cls_html.php';
require_once CLS.'/cls_file.php';

class BaseController{

    public $db;
    public $html;
    public $help;
    public $file;

    private $twig;
    public $a_render;

    function __construct()
    {

        $explodeFilter = new Twig\TwigFilter('explode', function($el) { return $el !== "" && $el !== null ? explode("*",$el) : array(); });
        $varDumpFilter = new Twig\TwigFilter('var_dump', function($el) { return var_dump($el); });
        $dateItaDBFilter = new Twig\TwigFilter('dateDb', function($el) { $date = explode("/",$el); return $date[2]."-".$date[1]."-".$date[0]; });
        $numberPointFilter = new Twig\TwigFilter('numberPoint', function($el) { return str_replace(",",".",$el); });
        $getPdfPathFilter = new Twig\TwigFilter('get_pdf_path', function($el) { 
            
            $lavoro = $banca = false;
            if($el["DocumentTypeId"] == 7)
                $lavoro = true;
            if($el["DocumentTypeId"] == 8)
                $banca = true;
            
            $crea_file_name=function($a_results,$suffix="Copia") use($lavoro,$banca){

                $pignoId = $a_results["ID"];
            
                if( is_dir( PIGNORAMENTI."/".$pignoId ) == false )
                {
                    $result["PathCompleto"] = "";
                    $result["PathCompleto_Relata"] = "";

                    return $result;
                }
                $prefix=$a_results["PrefixName"];
                $cc=$a_results['CC'];
                $anno= $a_results["Anno_Cronologico"];
                $id=$a_results["ID_Cronologico"];
                $notifica_id=$a_results["ID_Notifica"];
            
                $path=$pignoId."/";
                $filename=$prefix."_".$cc."_".$anno."_".$id."_".$notifica_id."_".$suffix.".pdf";
                if ($lavoro)
                {
                    $filename=$prefix."_".$cc."_".$anno."_".$id."_".$notifica_id."_Copia_".$suffix.".pdf";
                    $filename_Relata=$prefix."_".$cc."_".$anno."_".$id."_".$notifica_id."_"."Relata_".$suffix.".pdf";
                }
                else if($banca)
                {
                    $filename=$prefix."_".$cc."_".$anno."_".$id."_".$notifica_id."_Copia_".$suffix.".pdf";
                    $filename_Relata=$prefix."_".$cc."_".$anno."_".$id."_".$notifica_id."_"."Relata_".$suffix.".pdf";
                }
                else
                {
                    $filename=$prefix."_".$cc."_".$anno."_".$id."_".$notifica_id."_".$suffix.".pdf";
                    $filename_Relata=$prefix."_".$cc."_".$anno."_".$id."_".$notifica_id."_"."Relata".".pdf";
                }
                $path_completo =  PIGNORAMENTI_WEB."/".$path.$filename;
                $path_completo_Relata =  PIGNORAMENTI_WEB."/".$path.$filename_Relata;
                $result=array();
                $result["PathCompleto"] = $path_completo;
                $result["PathCompleto_Relata"] = $path_completo_Relata;
                return $result;
            };


            if ($el["DocumentTypeId"] == 7)
            {
                $is_debitore = $el["Tipo_Notifica"] == "debitore" ? true :false;
                if ($is_debitore)
                {
                    $a_filename = $crea_file_name($el,"debitore");
                }
                else
                {
                    $a_filename = $crea_file_name($el,"terzo");
                }
            }
            else if ($el["DocumentTypeId"] == 8)
            {
                $is_debitore = $el["Tipo_Notifica"] == "debitore" ? true :false;
                if ($is_debitore)
                {
                    $a_filename = $crea_file_name($el,"debitore");
                }
                else
                {
                    $a_filename = $crea_file_name($el,"banca");
                }
            }
            else
                $a_filename = $crea_file_name($el);
            
            return $a_filename; 
        
        });

        $this->twig = new Environment(new FilesystemLoader(VIEWS));

        $this->twig->addFilter($explodeFilter);
        $this->twig->addFilter($varDumpFilter);
        $this->twig->addFilter($dateItaDBFilter);
        $this->twig->addFilter($numberPointFilter);
        $this->twig->addFilter($getPdfPathFilter);

        $this->db = new CLS_DB();
        $this->help = new cls_help();
        $this->html = new cls_html();
        $this->file = new cls_file();
        $this->setRenderParams();
    }

    private function setRenderParams(){
        $this->setSessionParams();
    }

    private function setSessionParams(){
        $this->a_render['session'] = $_SESSION;
        $this->a_render['PATHS'] = PATHS;
    }

    public function addRenderParams(array $a_params){
        $this->a_render = array_merge($this->a_render,$a_params);
    }

    public function showView($view){
        echo $this->twig->render($view.'.html.twig', $this->a_render);
    }

    public function returnView($view){
        return $this->twig->render($view.'.html.twig', $this->a_render);
    }

    public function getRow($query){
        return $this->db->getArrayLine($this->db->ExecuteQuery($query));
    }

    public function getRows($query, $key=false){
        return $this->db->getResults($this->db->ExecuteQuery($query),"array",$key);
    }

    public function runQuery($query){
        return $this->db->ExecuteQuery($query);
    }
}