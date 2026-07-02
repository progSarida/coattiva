<?php
require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";

include(INC."/header.php");
include(INC."/menu.php");

//include(CLS."/cls_db.php");
include(CLS."/cls_Utils.php");

//set_time_limit(0);

$a_uploadFlow = $cls_db->getResults($cls_db->SelectQuery("SELECT COUNT(*) TOT FROM `flows` WHERE CityId='".$c."' AND CreationDate is not null AND UploadDate is null AND DATEDIFF(CURDATE(),CreationDate)>3"));
$a_processingFlow = $cls_db->getResults($cls_db->SelectQuery("SELECT COUNT(*) TOT FROM `flows` WHERE CityId='".$c."' AND UploadDate is not null AND ProcessingDate is null AND DATEDIFF(CURDATE(),UploadDate)>5"));
$a_paymentFlow = $cls_db->getResults($cls_db->SelectQuery("SELECT COUNT(*) TOT FROM `flows` WHERE CityId='".$c."' AND ProcessingDate is not null AND PostagePaymentDate is null AND DATEDIFF(CURDATE(),ProcessingDate)>3"));
$a_sendFlow = $cls_db->getResults($cls_db->SelectQuery("SELECT COUNT(*) TOT FROM `flows` WHERE CityId='".$c."' AND PostagePaymentDate is not null AND SendDate is null AND DATEDIFF(CURDATE(),PostagePaymentDate)>3"));

if($_SESSION['aut_tipo']!=2) {

    ?>

    <table class="width95 text_center" border="0">
        <tr class="sfondo_new_gitco">
            <td class="text_center" colspan="2"><span style="color: white;"><b>ANALISI FLUSSI DATI IN USCITA - <?php echo $adminCity; ?></b></span></td>
        </tr
        <?php
        $cont = 1;
        for($i=0;$i<4;$i++){
            switch($i){
                case 0:
                    $label = "Data Upload assente > 3gg";
                    $value = $a_uploadFlow[0]['TOT'];
                    break;
                case 1:
                    $label = "Data Fine Lavorazione assente > 5gg";
                    $value = $a_processingFlow[0]['TOT'];
                    break;
                case 2:
                    $label = "Data Pagamento assente > 3gg";
                    $value = $a_paymentFlow[0]['TOT'];
                    break;
                case 3:
                    $label = "Data Consegna assente > 3gg";
                    $value = $a_sendFlow[0]['TOT'];
                    break;

            }

            if($value>0){
                if($cont%2==0)
                    $class = "class=\"riga_dispari\"";
                else
                    $class="";
                ?>
                <tr <?=$class;?>>
                    <td class="text_center width35"><span class="titolo"><?= $label; ?></span></td>
                    <td class="text_left"> <?= $value; ?></td>
                </tr>
                <?php
                $cont++;
            }

        }
        if($cont==1){
            ?>
            <tr>
                <td class="text_center" colspan="2"><span class="titolo"><b>Nessun flusso pendente</b></span></td>
            </tr>
            <?php
        }
        ?>
    </table>
    <?php
}

/*

// Function to remove folders and files 
function rrmdir($dir) {
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file)
            if ($file != "." && $file != "..") rrmdir("$dir/$file");
        rmdir($dir);
    }
    else if (file_exists($dir)) unlink($dir);
}

// Function to Copy folders and files       
function rcopy($src, $dst) {
    if (file_exists ( $dst ))
        rrmdir ( $dst );
    if (is_dir ( $src )) {
        //mkdir ( $dst );
        $files = scandir ( $src );
        foreach ( $files as $file )
            if ($file != "." && $file != "..")
                rcopy ( "$src/$file", "$dst/$file" );
    } else if (file_exists ( $src ))
        copy ( $src, $dst );
}

defined('DS') ? NULL : define('DS',DIRECTORY_SEPARATOR);

function full_move($src, $dst){
    full_copy($src, $dst);
    full_remove($src);
}

function full_copy($src, $dst) {
    if (is_dir($src)) {
        @mkdir( $dst, 0777 ,TRUE);
        $files = scandir($src);
        foreach($files as $file){
            if ($file != "." && $file != ".."){
                full_copy("$src".DS."$file", "$dst".DS."$file");
            }
        }
    } else if (file_exists($src)){
        copy($src, $dst);
    }
}

function full_remove($dir) {
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file){
            if ($file != "." && $file != ".."){
                full_remove("$dir".DS."$file");
            }
        }
        rmdir($dir);
    }else if (file_exists($dir)){
        unlink($dir);
    }
}


    function scansiona($path_partial,$path_set,$ente,$utils){
        //var_dump(scandir($path_partial));die;
        $allDir = scandir($path_partial);

        for($i=0,$a=0; $i < count($allDir) ; $i++){
            
            if($allDir[$i] != "." && $allDir[$i] != ".."){
                
                if(is_dir($path_partial."/".$allDir[$i]) && $allDir[$i] == $ente){

                    //var_dump("!!!!!!!!!!!!!! COPY !!!!!!!!!!!!!!");

                    full_copy($path_partial."/".$allDir[$i], $utils->crea_dir($path_set."/".$allDir[$i]));
                    full_remove($path_partial."/".$allDir[$i]);
                    //rcopy($path_partial."/".$value , $utils->crea_dir($path_set."/".$value) );
                }
                else if(is_dir($path_partial."/".$allDir[$i])) scansiona($path_partial."/".$allDir[$i],$path_set."/".$allDir[$i],$ente,$utils);
            }
        }
        return;
    }

//var_dump(scandir(ARCHIVIO));die;
    $utils = new cls_Utils();
    $cls_db = new cls_db();

    $query = "select CC from enti_gestiti where Autorizzazione = 3";
    $CC = $cls_db->getResults($cls_db->ExecuteQuery($query));

    //var_dump($CC);die;

    foreach(scandir(ARCHIVIO) as $key => $value){
        
        if($value != "." && $value != ".."){
            //var_dump($value);
            foreach($CC as $key_1 => $ente)
                if(is_dir(ARCHIVIO."/".$value)){
                    //var_dump("---------------- hola ".$value." -------------------");
                    scansiona(ARCHIVIO."/".$value,ARCHIVIO."/IRTEL/".$value,$ente["CC"],$utils);
                }
                    
            //$path_copy = $utils->crea_dir(ARCHIVIO."/".$value)
        }
    }

    echo "Fine cartelle!!!"*/
    ?>

<br>

<?php include(INC."/footer.php"); ?>
