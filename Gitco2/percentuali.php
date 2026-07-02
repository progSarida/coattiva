<?php
if(empty($_GET['c']))
    $_GET['c']="D925";
if(empty($_GET['a']))  
    $_GET['a']= 2018;

require $_SERVER['DOCUMENT_ROOT'].explode("/Gitco2",$_SERVER['SCRIPT_NAME'])[0]."/config/_config.php";


include(INC . "/headerAjax.php");
include_once CLS . "/cls_db.php";
$cls_db = new cls_db();
$a_doc = array(2=>"INGIUNZIONI", 3=>"SOLLECITI");
$a_temp = $a;
echo "<pre>STATISTICHE ".$c."<hr></pre>";
foreach($a_doc as $docId=>$doctype){
for($a_temp;$a_temp<2023;$a_temp++){
    
        $query='
        SELECT SUM(P.Importo), 
        A.Totale_Dovuto+A.Diritto_Riscossione_Minimo AS TOT_MIN, 
        A.Totale_Dovuto+A.Diritto_Riscossione_Massimo AS TOT_MAX 
        FROM `atto` A LEFT JOIN pagamento P ON P.Atto_ID<=A.ID AND P.Partita_ID=A.Partita_ID AND DocumentTableTypeId=1
        WHERE A.DocumentTypeId='.$docId.' AND A.CC="'.$c.'" AND A.Anno_Cronologico='.$a_temp;
        if($docId==3)
            $query.=' AND A.Data_Stampa is not null';
        else
            $query.=' AND A.Data_Notifica is not null';    
        $query.=' GROUP BY A.ID HAVING SUM(P.Importo)>=TOT_MIN-16.53
        ORDER BY TOT_MIN-SUM(P.Importo)-16.53 DESC';
        
        $a_recPag = $cls_db->getResults($cls_db->ExecuteQuery($query));
        
        $query='SELECT A.ID 
        FROM `atto` A
        WHERE A.DocumentTypeId='.$docId.' AND A.CC="'.$c.'" AND A.Anno_Cronologico='.$a_temp;
        if($docId==3)
            $query.=' AND A.Data_Stampa is not null';
        else
            $query.=' AND A.Data_Notifica is not null';    
        
        $a_rec = $cls_db->getResults($cls_db->ExecuteQuery($query));
        if(count($a_rec)>0)
            $perc = count($a_recPag)*100/count($a_rec);
        else
            $perc = 0;

        if($docId==3)
            echo "<pre>PERC. ".$doctype." PAGATI SU SPEDITI ".$c." ".$a_temp.": ".round($perc,2)."% - ".count($a_recPag)." su ".count($a_rec)."</pre>" ;
        else
            echo "<pre>PERC. ".$doctype." PAGATE SU NOTIFICATE ".$c." ".$a_temp.": ".round($perc,2)."% - ".count($a_recPag)." su ".count($a_rec)."</pre>" ;
    }
    $a_temp = $a;
    echo "<pre><hr></pre>";
}
$a_temp = $a;
$a_doc = array(7=>"PIGNO DATORE LAVORO", 8=>"PIGNO BANCA", 6=>"PIGNO VEICOLI");
foreach($a_doc as $docId=>$doctype){
    for($a_temp;$a_temp<2023;$a_temp++){
        
            $query='
            SELECT SUM(P.Importo), 
            PG.Totale_Dovuto
            FROM `pignoramento_generale` PG 
            JOIN notifica_atto N ON N.Atto_Notificato_ID=PG.ID AND N.Tipo_Notifica="debitore"
            LEFT JOIN pagamento P ON P.Atto_ID = PG.ID 
            AND P.Partita_ID=PG.Partita_ID AND DocumentTableTypeId=2
            WHERE PG.DocumentTypeId='.$docId.' AND PG.CC="'.$c.'" AND PG.Anno_Cronologico='.$a_temp.' AND N.Data_Notifica is not null 
            GROUP BY PG.ID HAVING SUM(P.Importo)>=PG.Totale_Dovuto-16.53
            ORDER BY PG.Totale_Dovuto-SUM(P.Importo)-16.53 DESC';
            
            $a_recPag = $cls_db->getResults($cls_db->ExecuteQuery($query));
            
            $query='SELECT PG.ID 
            FROM `pignoramento_generale` PG 
            JOIN notifica_atto N ON N.Atto_Notificato_ID=PG.ID AND N.Tipo_Notifica="debitore"
            WHERE PG.DocumentTypeId='.$docId.' AND PG.CC="'.$c.'" AND PG.Anno_Cronologico='.$a_temp.'
            AND N.Data_Notifica is not null';    
            
            $a_rec = $cls_db->getResults($cls_db->ExecuteQuery($query));
            if(count($a_rec)>0)
                $perc = count($a_recPag)*100/count($a_rec);
            else
                $perc = 0;
    
            echo "<pre>PERC. ".$doctype." PAGATI SU NOTIFICATI ".$c." ".$a_temp.": ".round($perc,2)."% - ".count($a_recPag)." su ".count($a_rec)."</pre>" ;
        }
        $a_temp = $a;
        echo "<pre><hr></pre>";
    }


include(INC . "/footer.php");