<?php

class BuildFilter
{

    private $Html;

    public function __construct($input)
    {
        $this->Html = array();

        foreach ($input as $key => $value){
            if($value["isDrop"] == true) $this->Html[] = $this->createDrop($value);
            else $this->Html[] = $this->createInput($value);
        }
    }

    public function getHtml(){
        $allHtml = "";
        foreach ($this->Html as $item)
            $allHtml .= $item;

        return $allHtml;
    }

    private function createInput($value){
        return $this->setHtml($value["name"],$value["value"]);
    }

    private function createDrop($value){
        if($value["isFinalOption"] == true){
            return $this->setHtml($value["name"],$value["value"]);
        }
        else{
            $option = $this->createOption($value["value"]);
            return $this->setHtml($value["name"],$option);
        }
    }

    private function createOption($value){
        $option = '<option value=""></option>';
        foreach ($value as $item){
            $option .= '<option value="'.$item["id"].'">'.$item["descr"].'</option>';
        }

        return $option;
    }

    private function setHtml($name,$value){
        switch($name){
            case "ente": return
                '<div class="row">
                    <div class="col-lg-4 col-md-offset-1">
                        <label class="col-lg-12 control-label resize" style="text-align: left;color: blue;font-weight: bold;">Ente</label>
                    </div>
                    <div class="col col-lg-4" >
                        <div class="form-group">
                            <div class="col-lg-12">
                                <select id="CC" name="CC" class="form-control resize" tabindex=1>
                                    '.$value.'
                                </select>
                            </div>
                        </div>
                    </div>
                </div>';

            case "printer": return
                '<div class="row">
                    <div class="col-lg-4 col-md-offset-1">
                        <label class="col-lg-12 control-label resize" style="text-align: left;color: blue;font-weight: bold;">Stampatore</label>
                    </div>
                    <div class="col col-lg-4" >
                        <div class="form-group">
                            <div class="col-lg-12">
                                <select id="PrinterId" name="PrinterId" class="form-control resize" tabindex=1>
                                    '.$value.'
                                </select>
                            </div>
                        </div>
                    </div>
                </div>';

            case "stato": return
                '<div class="row" >
                    <div class="col-lg-4 col-md-offset-1">
                        <label class="col-lg-12 control-label resize" style="text-align: left;color: blue;font-weight: bold;">Stato</label>
                    </div>
                    <div class="col col-lg-4" >
                        <div class="form-group">
                            <div class="col-lg-12">
                                <select id="stato" name="stato" class="form-control resize" tabindex=1>
                                    '.$value.'
                                </select>
                            </div>
                        </div>
                    </div>
                </div>';

            case "anomalie": return
                '<div class="row">
                    <div class="col-lg-4 col-md-offset-1">
                        <label class="col-lg-12 control-label resize" style="text-align: left;color: blue;font-weight: bold;">Anomalie</label>
                    </div>
                    <div class="col col-lg-4" >
                        <div class="form-group">
                            <div class="col-lg-12">
                                <select id="anomalie" name="anomalie" class="form-control resize" tabindex=1>
                                    '.$value.'
                                </select>
                            </div>
                        </div>
                    </div>
                </div>';

            case "gradoRicorso": return
                '<div class="row">
                    <div class="col-lg-4 col-md-offset-1">
                        <label class="col-lg-12 control-label resize" style="text-align: left;color: blue;font-weight: bold;">Grado ricorso</label>
                    </div>
                    <div class="col col-lg-4" >
                        <div class="form-group">
                            <div class="col-lg-12">
                                <select id="Court_Level" name="Court_Level" class="form-control resize" tabindex=1>
                                    '.$value.'
                                </select>
                            </div>
                        </div>
                    </div>
                </div>';

            case "parzTot": return
                '<div class="row">
                    <div class="col-lg-4 col-md-offset-1">
                        <label class="col-lg-12 control-label resize" style="text-align: left;color: blue;font-weight: bold;">Pagamenti partita</label>
                    </div>
                    <div class="col col-lg-4" >
                        <div class="form-group">
                            <div class="col-lg-12">
                                <select id="parzTot" name="parzTot" class="form-control resize" tabindex=1>
                                    '.$value.'
                                </select>
                            </div>
                        </div>
                    </div>
                </div>';

            case "tipoFile": return
                '<div class="row">
                    <div class="col-lg-4 col-md-offset-1">
                        <label class="col-lg-12 control-label resize" style="text-align: left;color: blue;font-weight: bold;">Tipo File</label>
                    </div>
                    <div class="col col-lg-4" >
                        <div class="form-group">
                            <div class="col-lg-12">
                                <select id="file_type" name="file_type" class="form-control resize" tabindex=1>
                                    <option value=""></option>
                                    '.$value.'
                                </select>
                            </div>
                        </div>
                    </div>
                </div>';

            case "autorita": return
                '<div class="row">
                    <div class="col-lg-4 col-md-offset-1">
                        <label class="col-lg-12 control-label resize" style="text-align: left;color: blue;font-weight: bold;">Tipo Autorità</label>
                    </div>
                    <div class="col col-lg-4" >
                        <div class="form-group">
                            <div class="col-lg-12">
                                <select id="autority_type" name="autority_type" class="form-control resize" tabindex=1>
                                    <option value=""></option>
                                    '.$value.'
                                </select>
                            </div>
                        </div>
                    </div>
                </div>';

            case "tipoEntrata": return
                '<div class="row">
                    <div class="col-lg-4 col-md-offset-1">
                        <label class="col-lg-12 control-label resize" style="text-align: left;color: blue;font-weight: bold;">Tipo Entrata</label>
                    </div>
                    <div class="col col-lg-4" >
                        <div class="form-group">
                            <div class="col-lg-12">
                                <select id="tipo_entrata" name="tipo_entrata" class="form-control resize" tabindex=1>
                                    '.$value.'
                                </select>
                            </div>
                        </div>
                    </div>
                </div>';

            case "annoFlusso": return
                '<div class="row">
                    <div class="col-lg-2 col-md-offset-1">
                        <label class="col-lg-12 control-label resize" style="text-align: left;color: blue;font-weight: bold;">Anno flusso da/a</label>
                    </div>
                    <div class="col col-lg-4" style="margin: 0;padding: 0;">
                        <div class="form-group">
                            <div class="col-lg-12">
                                <input class="form-control resize" placeholder="Anno flusso da ..." type="text" id="anno_flusso_da" name="anno_flusso_da" value="'.$value[0].'" maxlength="4" tabindex=7>
                            </div>
                        </div>
                    </div>
                    <div class="col col-lg-4" style="padding: 0;margin: 0;">
                        <div class="form-group">
                            <div class="col-lg-12">
                                <input class="form-control resize" type="text" placeholder="Anno flusso a ..." id="anno_flusso_a" name="anno_flusso_a" value="'.$value[1].'" maxlength="4" tabindex=7>
                            </div>
                        </div>
                    </div>
                </div>';

            case "annoCrono": return
                '<div class="row">
                    <div class="col-lg-2 col-md-offset-1">
                        <label class="col-lg-12 control-label resize" style="text-align: left;color: blue;font-weight: bold;">Anno cronologico</label>
                    </div>
                    <div class="col col-lg-4" style="margin: 0;padding: 0;">
                        <div class="form-group">
                            <div class="col-lg-12">
                                <input class="form-control resize" placeholder="Anno cronologico da ..." type="text" id="anno_cronologico_da" name="anno_cronologico_da" value="'.$value[0].'" maxlength="4" tabindex=7>
                            </div>
                        </div>
                    </div>
                    <div class="col col-lg-4" style="padding: 0;margin: 0;">
                        <div class="form-group">
                            <div class="col-lg-12">
                                <input class="form-control resize" type="text" placeholder="Anno cronologico a ..." id="anno_cronologico_a" name="anno_cronologico_a" value="'.$value[1].'" maxlength="4" tabindex=7>
                            </div>
                        </div>
                    </div>
                </div>';

            case "idCrono": return
                '<div class="row">
                    <div class="col-lg-2 col-md-offset-1">
                        <label class="col-lg-12 control-label resize" style="text-align: left;color: blue;font-weight: bold;">ID cronologico</label>
                    </div>
                    <div class="col col-lg-4" style="margin: 0;padding: 0;">
                        <div class="form-group">
                            <div class="col-lg-12">
                                <input class="form-control resize" placeholder="Id cronologico da ..." type="text" id="id_cronologico_da" name="id_cronologico_da" value="'.$value[0].'" tabindex=7>
                            </div>
                        </div>
                    </div>
                    <div class="col col-lg-4" style="padding: 0;margin: 0;">
                        <div class="form-group">
                            <div class="col-lg-12">
                                <input class="form-control resize" type="text" placeholder="Id cronologico a ..." id="id_cronologico_a" name="id_cronologico_a" value="'.$value[1].'" tabindex=7>
                            </div>
                        </div>
                    </div>
                </div>';

            case "dataUdienza": return
                '<div class="row">
                    <div class="col-lg-2 col-md-offset-1">
                        <label class="col-lg-12 control-label resize" style="text-align: left;color: blue;font-weight: bold;">Data udienza</label>
                    </div>
                    <div class="col col-lg-4" style="margin: 0;padding: 0;">
                        <div class="form-group">
                            <div class="col-lg-12">
                                <input class="form-control resize" placeholder="Data udienza da ..." type="date" id="data_udienza_da" name="data_udienza_da" value="'.$value[0].'" tabindex=7>
                            </div>
                        </div>
                    </div>
                    <div class="col col-lg-4" style="padding: 0;margin: 0;">
                        <div class="form-group">
                            <div class="col-lg-12">
                                <input class="form-control resize" type="date" placeholder="Data udienza a ..." id="data_udienza_a" name="data_udienza_a" value="'.$value[1].'" tabindex=7>
                            </div>
                        </div>
                    </div>
                </div>';

            case "dataInserimento": return
                '<div class="row">
                    <div class="col-lg-2 col-md-offset-1">
                        <label class="col-lg-12 control-label resize" style="text-align: left;color: blue;font-weight: bold;">Data inserimento da/a</label>
                    </div>
                    <div class="col col-lg-4" style="margin: 0;padding: 0;">
                        <div class="form-group">
                            <div class="col-lg-12">
                                <input class="form-control resize" placeholder="Data inserimento da ..." type="date" id="data_inserimento_da" name="data_inserimento_da" value="'.$value[0].'" tabindex=7>
                            </div>
                        </div>
                    </div>
                    <div class="col col-lg-4" style="padding: 0;margin: 0;">
                        <div class="form-group">
                            <div class="col-lg-12">
                                <input class="form-control resize" type="date" placeholder="Data inserimento a ..." id="data_inserimento_a" name="data_inserimento_a" value="'.$value[1].'" tabindex=7>
                            </div>
                        </div>
                    </div>
                </div>';

            case "annoPagamento": return
                '<div class="row">
                    <div class="col-lg-2 col-md-offset-1">
                        <label class="col-lg-12 control-label resize" style="text-align: left;color: blue;font-weight: bold;">Anno pagamento da/a</label>
                    </div>
                    <div class="col col-lg-4" style="margin: 0;padding: 0;">
                        <div class="form-group">
                            <div class="col-lg-12">
                                <input class="form-control resize" placeholder="Anno pagamento da ..." type="date" id="anno_pagamento_da" name="anno_pagamento_da" value="'.$value[0].'" maxlength="4" tabindex=7>
                            </div>
                        </div>
                    </div>
                    <div class="col col-lg-4" style="padding: 0;margin: 0;">
                        <div class="form-group">
                            <div class="col-lg-12">
                                <input class="form-control resize" type="date" placeholder="Anno pagamento a ..." id="anno_pagamento_a" name="anno_pagamento_a" value="'.$value[1].'" maxlength="4" tabindex=7>
                            </div>
                        </div>
                    </div>
                </div>';

            case "rangeGiorni": return
                '<div class="row">
                    <div class="col-lg-4 col-md-offset-1">
                        <label class="col-lg-12 control-label resize" style="text-align: left;color: blue;font-weight: bold;">N° giorni</label>
                    </div>
                    <div class="col col-lg-4">
                        <div class="form-group">
                            <div class="col-lg-12">
                                <input class="form-control resize" placeholder="Numero giorni" type="text" id="range_giorni" name="range_giorni" value="'.$value.'" tabindex=7>
                            </div>
                        </div>
                    </div>
                </div>';

            case "partita": return
                '<div class="row">
                    <div class="col-lg-2 col-md-offset-1">
                        <label class="col-lg-12 control-label resize" style="text-align: left;color: blue;font-weight: bold;">Partita</label>
                    </div>
                    <div class="col col-lg-4" style="margin: 0;padding: 0;">
                        <div class="form-group">
                            <div class="col-lg-12">
                                <input class="form-control resize" placeholder="Partita da ..." type="text" id="partita_da" name="partita_da" value="'.$value[0].'" tabindex=7>
                            </div>
                        </div>
                    </div>
                    <div class="col col-lg-4" style="padding: 0;margin: 0;">
                        <div class="form-group">
                            <div class="col-lg-12">
                                <input class="form-control resize" type="text" placeholder="Partita a ..." id="partita_a" name="partita_a" value="'.$value[1].'" tabindex=7>
                            </div>
                        </div>
                    </div>
                </div>';
                
            case "utente_da": return
                '<div class="row">
                <div class="col-lg-2 col-md-offset-1">
                    <label class="col-lg-12 control-label resize" style="text-align: left;color: blue;font-weight: bold;">Da Cognome/Nome</label>
                </div>
                <div class="col col-lg-4" style="margin: 0;padding: 0;">
                    <div class="form-group">
                        <div class="col-lg-12">
                            <input class="form-control resize" placeholder="Cognome da ..." type="text" id="daco" name="daco" value="'.$value[0].'" tabindex=7 ondblclick="openOfcanvas(`userSospSearchModal`,1);">
                        </div>
                    </div>
                </div>
                <div class="col col-lg-4" style="padding: 0;margin: 0;">
                    <div class="form-group">
                        <div class="col-lg-12">
                            <input class="form-control resize" type="text" placeholder="Nome da ..." id="dano" name="dano" value="'.$value[1].'" tabindex=7 ondblclick="openOfcanvas(`userSospSearchModal`,1);">
                        </div>
                    </div>
                </div>
            </div>';

            case "utente_a": return
                '<div class="row">
                <div class="col-lg-2 col-md-offset-1">
                    <label class="col-lg-12 control-label resize" style="text-align: left;color: blue;font-weight: bold;">A Cognome/nome</label>
                </div>
                <div class="col col-lg-4" style="margin: 0;padding: 0;">
                    <div class="form-group">
                        <div class="col-lg-12">
                            <input class="form-control resize" placeholder="Cognome a ..." type="text" id="acog" name="acog" value="'.$value[0].'" tabindex=7 ondblclick="openOfcanvas(`userSospSearchModal`,2);">
                        </div>
                    </div>
                </div>
                <div class="col col-lg-4" style="padding: 0;margin: 0;">
                    <div class="form-group">
                        <div class="col-lg-12">
                            <input class="form-control resize" type="text" placeholder="Nome a ..." id="anom" name="anom" value="'.$value[1].'" tabindex=7 ondblclick="openOfcanvas(`userSospSearchModal`,2);">
                        </div>
                    </div>
                </div>
            </div>';

            case "banca": return
                '<div class="row">
                    <div class="col-lg-4 col-md-offset-1">
                        <label class="col-lg-12 control-label resize" style="text-align: left;color: blue;font-weight: bold;">Banca</label>
                    </div>
                    <div class="col col-lg-4" >
                        <div class="form-group">
                            <div class="col-lg-12">
                                <select id="ID_Banca" name="ID_Banca" class="form-control resize" tabindex=1>
                                    '.$value.'
                                </select>
                            </div>
                        </div>
                    </div>
                </div>';

            case "dataNotifica": return
            '<div class="row">
                <div class="col-lg-2 col-md-offset-1">
                    <label class="col-lg-12 control-label resize" style="text-align: left;color: blue;font-weight: bold;">Data notifica da/a</label>
                </div>
                <div class="col col-lg-4" style="margin: 0;padding: 0;">
                    <div class="form-group">
                        <div class="col-lg-12">
                            <input class="form-control resize" placeholder="Data notifica da ..." type="date" id="da_data_notifica" name="da_data_notifica" value="'.$value[0].'" tabindex=7>
                        </div>
                    </div>
                </div>
                <div class="col col-lg-4" style="padding: 0;margin: 0;">
                    <div class="form-group">
                        <div class="col-lg-12">
                            <input class="form-control resize" type="date" placeholder="Data notifica a ..." id="a_data_notifica" name="a_data_notifica" value="'.$value[1].'" tabindex=7>
                        </div>
                    </div>
                </div>
            </div>';
        }
    }
}