<?php
/*
 *	Line Graph
 */

// Inclusione delle librerie
include("pChart/pData.class");
include("pChart/pChart.class");

// Definizione del Dataset
$DataSet = new pData;
$DataSet->AddPoint(array(-5,0,7,10,10,11,12,14,16,17,18,18,19,19,18,15,12,10,9),"Serie1");
$DataSet->AddPoint(array(10,11,11,12,12,13,14,15,17,19,22,24,23,23,22,20,18,16,14),"Serie2");
$DataSet->AddPoint(array(4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22),"Serie3");
$DataSet->AddAllSeries();
$DataSet->RemoveSerie("Serie3");
$DataSet->SetAbsciseLabelSerie("Serie3");
$DataSet->SetSerieName("January","Serie1");
$DataSet->SetSerieName("February","Serie2");
$DataSet->SetYAxisName("Temperature");
$DataSet->SetYAxisUnit("°C");
$DataSet->SetXAxisUnit("h");

// Inizializzazione del grafico
$Test = new pChart(700,230);
$Test->setFontProperties("Fonts/tahoma.ttf",8);
$Test->setGraphArea(60,30,680,180);
$Test->drawFilledRoundedRectangle(7,7,693,223,5,240,240,240);
$Test->drawRoundedRectangle(5,5,695,225,5,230,230,230);
$Test->drawGraphArea(255,255,255,TRUE);
$Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,90,2);
$Test->drawGrid(4,TRUE,230,230,230,50);

// Disegna la linea dello 0
$Test->setFontProperties("Fonts/tahoma.ttf",6);
$Test->drawTreshold(0,143,55,72,TRUE,TRUE);

// Disegna la linea del grafico
$Test->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());
$Test->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),3,2,255,255,255);

// Inserisce la legenda
$Test->setFontProperties("Fonts/tahoma.ttf",8);
$Test->drawLegend(70,40,$DataSet->GetDataDescription(),255,255,255);

// Inserisce il titolo
$Test->setFontProperties("Fonts/tahoma.ttf",10);
$Test->drawTitle(60,22,"Temperature",50,50,50,585);

// salva il file con l'immagine
//$Test->Render("linegraph.png");

// se invece si vuole mostrare direttamente l'immagine senza salvarla su disco, commentare la riga precedente e scommentare quella seguente
$Test->Stroke();

?>