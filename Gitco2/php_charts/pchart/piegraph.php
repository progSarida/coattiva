<?php
/*
 *	2D exploded pie graph
 */

// Inclusione delle librerie
include("pChart/pData.class");
include("pChart/pChart.class");

// Definizione del Dataset
$DataSet = new pData;

// array dei valori
$DataSet->AddPoint(array(10,2,3,5,3),"Serie1");

// array con le etichette dei valori
$DataSet->AddPoint(array("Jan","Feb","Mar","Apr","May"),"Serie2");

$DataSet->AddAllSeries();
$DataSet->SetAbsciseLabelSerie("Serie2");

// Inizializzazione del grafico
$Test = new pChart(340,250);
$Test->setFontProperties("Fonts/tahoma.ttf",10);
$Test->drawFilledRoundedRectangle(7,7,333,243,5,240,240,240);
$Test->drawRoundedRectangle(5,5,335,245,5,230,230,230);

// Disegna il grafico a torta
$Test->AntialiasQuality = 0;
$Test->setShadowProperties(2,2,200,200,200);
$Test->drawFlatPieGraphWithShadow($DataSet->GetData(),$DataSet->GetDataDescription(),130,130,80,PIE_PERCENTAGE,8);
$Test->clearShadow();

// Inserisce il titolo
$Test->drawTitle(100,20,"Test Pie Graph",0,0,0);

// Inserisce la legenda
$Test->drawPieLegend(270,15,$DataSet->GetData(),$DataSet->GetDataDescription(),250,250,250);

// salva il file con l'immagine
//$Test->Render("piegraph.png");

// se invece si vuole mostrare direttamente l'immagine senza salvarla su disco, commentare la riga precedente e scommentare quella seguente
$Test->Stroke();

?>