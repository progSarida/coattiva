<div class="mainmenu width100">
    <ul class="text_left">
        <li class="li_hc"><a href="#" target="_self">Gestione Ente</a>
            <ul class="ul_ch">
                <li class="li_nc"><a onclick="menuClick('29');" href="#">Selezione Ente/Anno</a></li>
                <?php if ($_SESSION['CC_User'] == '****' || $_SESSION['CC_User'] == '***+') {
                    ?>
                    <li class="li_nc"><a onclick="menuClick('13');" href="#">Creazione ente</a></li>
<!--                    <li class="li_nc"><a onclick="menuClick('15');" href="#">Cancellazione ente</a></li>-->
                <?php }
                ?>
                <li class="li_nc"><a onclick="menuClick('14');" href="#">Creazione anno</a></li>
                <li class="li_nc"><a onclick="menuClick('16');" href="#">Cancellazione anno</a></li>
            </ul>
        </li>
        <li class="li_hc"><a href="#" target="_self">Anagrafe</a>
            <ul class="ul_ch">
                <li class="li_nc"><a onclick="menuClick('1');" href="#">Dati Soggetto</a></li>
                <li class="li_nc"><a onclick="menuClick('2');" href="#">Annotazioni</a></li>
                <li class="li_nc"><a onclick="menuClick('3');" href="#">Recapito</a></li>
                <li class="li_nc"><a onclick="menuClick('4');" href="#">Domicilio</a></li>
                <li class="li_nc"><a onclick="menuClick('5');" href="#">Dettagli</a></li>
                <li class="li_nc"><a onclick="menuClick('6');" href="#">Cambia Residenza e Storico</a></li>
                <li class="li_nc"><a onclick="menuClick('207');" href="#">Veicoli</a></li>
            </ul>
        </li>
        <li class="li_hc"><a href="#" target="_self">Ruolo</a>
            <ul class="ul_ch">
                <li class="li_nc"><a onclick="menuClick('104');" href="#">Gestione ruoli</a></li>
                <li><a href="#" onclick="menuClick('7');">Gestione partite</a>
                    <ul class="ul_ch">
                        <li><a href="#" onclick="menuClick('9');">Iter ingiuntivo</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('9');" href="#">Codice Tributo</a></li>
                                <li class="li_nc"><a onclick="menuClick('10');" href="#">Ingiunzione</a></li>
                                <li class="li_nc"><a onclick="menuClick('11');" href="#">Pagamenti</a></li>
                                <li class="li_nc"><a onclick="menuClick('12');" href="#">Ricorsi</a></li>
                            </ul>
                        </li>
                        <li><a href="#" onclick="menuClick('50');">Iter coattivo</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('50');" href="#">Pignoramento</a></li>
                                <li class="li_nc"><a onclick="menuClick('90');" href="#">Pagamenti pignoramento</a></li>
                                <li class="li_nc"><a onclick="menuClick('91');" href="#">Ricorsi pignoramento</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="li_nc"><a onclick="menuClick('45');" href="#">Ispezioni</a></li>
                <li class="li_nc"><a onclick="menuClick('44');" href="#">Lista Codici Tributo</a></li>
                <li class="li_nc"><a onclick="menuClick('51');" href="#">Lista Tariffe Coazione</a></li>
                <li><a href="#" target="_self">Importazioni 290</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('8');" href="#">Tracciato 290</a></li>
                        <li class="li_nc"><a onclick="menuClick('105');" href="#">Excel</a></li>
                    </ul>
                </li>
            </ul>
        </li>
        <li class="li_hc"><a href="#" target="_self">Elaborazioni</a>
            <ul class="ul_ch">
                <li><a href="#" target="_self">Atti</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('3001');" href="#">Sollecito pre ingiunzione</a></li>
                        <li class="li_nc"><a onclick="menuClick('3002');" href="#">Avviso di messa in mora</a></li>
                        <li class="li_nc"><a onclick="menuClick('22');" href="#">Ingiunzione</a></li>
                        <li class="li_nc"><a onclick="menuClick('32');" href="#">Sollecito di pagamento</a></li>
                        <li class="li_nc"><a onclick="menuClick('23');" href="#">Avviso di intimazione</a></li>
                    </ul>
                </li>
                <li><a href="#" target="_self">Pignoramenti</a>
                    <ul class="ul_ch">
                        <li><a href="#" target="_self">Sollecito di pagamento</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('107');" href="#">Beni mobili registrati</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li><a href="#" target="_self">Importazioni</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('62');" href="#">Importazione Notifiche</a></li>
                        <li class="li_nc"><a onclick="" href="#">Passaggio Verso Gitco2</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('63');" href="#">Flussi</a></li>
                                <li class="li_nc"><a onclick="menuClick('73');" href="#">Pagamenti</a></li>
                            </ul>
                        </li>
                        <li class="li_nc"><a onclick="menuClick('70');" href="#">Importazioni Pagamenti</a></li>
                    </ul>
                </li>
                <li><a href="#" target="_self">Bonifiche</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="" href="#">Bonifiche Pagamenti</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('71');" href="#">Non Telematici</a></li>
                                <li class="li_nc"><a onclick="menuClick('72');" href="#">Telematici</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="li_nc"><a onclick="" href="#">TRAFAT</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('112');" href="#">Crea file</a></li>
                        <li class="li_nc"><a onclick="menuClick('113');" href="#">Leggi file</a></li>
                    </ul>
                </li>
                <li class="li_nc"><a onclick="" href="#">Visura ACI</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('88');" href="#">Visura Massiva</a></li>
                        <li class="li_nc"><a onclick="menuClick('92');" href="#">Elenco veicoli</a></li>
                    </ul>
                </li>
                <li><a href="#" target="_self">Sgravi</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('200');" href="#">Crea sgravio automatico</a></li>
                        <li class="li_nc"><a onclick="menuClick('204');" href="#">Elenco sgravi/annullamenti</a></li>
                        <li class="li_nc"><a onclick="menuClick('255');" href="#">Visualizza file</a></li>
                    </ul>
                </li>

                <li class="li_nc"><a onclick="menuClick('81');" href="#">Visura</a></li>

            </ul>
        </li>

        <li class="li_hc"><a href="#" target="_self">Controlli di gestione</a>
            <ul class="ul_ch">
                <li class="li_nc"><a target="_self" href="#">Flussi</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('6001');" href="#">Gestione</a></li>
                    </ul>
                </li>
                <li class="li_nc"><a onclick="menuClick('300');" href="#">Ricevute PEC</a></li>
            </ul>
        </li>

        <li class="li_hc"><a href="#" target="_self">Stampe</a>
            <ul class="ul_ch">
                <li class="li_nc"><a target="_self" href="#">Elenco</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a target="_self" href="#">Atti</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('4001');" href="#">Sollecito pre ingiunzione</a></li>
                                <li class="li_nc"><a onclick="menuClick('4002');" href="#">Avviso di messa in mora</a></li>
                                <li class="li_nc"><a onclick="menuClick('4006');" href="#">Ingiunzione</a></li>
                                <li class="li_nc"><a onclick="menuClick('26');" href="#">Distinta Ingiunzione</a></li>
                                <li class="li_nc"><a onclick="menuClick('4010');" href="#">Dettaglio ingiunzione</a></li>
                                <li class="li_nc"><a onclick="menuClick('4011');" href="#">Sollecito post ingiunzione</a></li>
                                <li class="li_nc"><a onclick="menuClick('4007');" href="#">Avviso di intimazione</a></li>
                                <!--                                <li class="li_nc"><a onclick="menuClick('27');" href="#">Avviso di intimazione</a></li>-->
                            </ul>
                        </li>
                        <li class="li_nc"><a onclick="menuClick('4005');" href="#">Esiti notifiche</a></li>
                        <li class="li_nc"><a onclick="menuClick('80');" href="#">Pagamenti</a></li>
                        <li class="li_nc"><a onclick="menuClick('82');" href="#">Pignoramenti</a></li>
                        <li class="li_nc"><a onclick="menuClick('4004');" href="#">Posizioni</a></li>
                        <li class="li_nc"><a onclick="menuClick('4003');" href="#">Udienze</a></li>
                    </ul>
                </li>
                <li class="li_nc"><a target="_self" href="#">Stampa</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a target="_self" href="#">Atti</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('1001');" href="#">Sollecito pre ingiunzione</a></li>
                                <li class="li_nc"><a onclick="menuClick('1002');" href="#">Avviso di messa in mora</a></li>
                                <li class="li_nc"><a onclick="menuClick('24');" href="#">Ingiunzione</a></li>
                                <li class="li_nc"><a onclick="menuClick('42');" href="#">Sollecito post ingiunzione</a></li>
                                <li class="li_nc"><a onclick="menuClick('25');" href="#">Avviso di intimazione</a></li>
                            </ul>
                        </li>
                        <li class="li_nc"><a target="_self" href="#">Pignoramenti</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('75');" href="#">Presso datore di lavoro</a></li>
                                <li class="li_nc"><a onclick="menuClick('76');" href="#">Presso banca</a></li>
                                <li class="li_nc"><a onclick="menuClick('84');" href="#">Beni mobili registrati</a></li>
                                <li class="li_nc"><a onclick="menuClick('109');" href="#">Sollecito beni mobili registrati</a></li>
                            </ul>
                        </li>
                        <li class="li_nc"><a href="#" target="_self">Altri documenti</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('59');" href="#">Richiesta Codici INPS</a></li>
                                <li class="li_nc"><a onclick="menuClick('69');" href="#">Richieste validazione notifica</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="li_nc"><a target="_self" href="#">Stampa HTML</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a target="_self" href="#">Atti</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('1101');" href="#">Ingiunzione</a></li>
                                <li class="li_nc"><a onclick="menuClick('1102');" href="#">Avviso di intimazione</a></li>
                                <li class="li_nc"><a onclick="menuClick('1103');" href="#">Avviso di messa in mora</a></li>
                                <li class="li_nc"><a onclick="menuClick('1104');" href="#">Sollecito post ingiunzione</a></li>
                                <li class="li_nc"><a onclick="menuClick('1105');" href="#">Sollecito pre ingiunzione</a></li>
                            </ul>
                        </li>
                        <li class="li_nc"><a target="_self" href="#">Pignoramento</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('1201');" href="#">Pignoramento veicolo</a></li>
                                <li class="li_nc"><a onclick="menuClick('1202');" href="#">Presso datore di lavoro</a></li>
                                <li class="li_nc"><a onclick="menuClick('1203');" href="#">Presso banca</a></li>
                                <li class="li_nc"><a onclick="menuClick('1205');" href="#">Preavviso fermo</a></li>
                            </ul>
                        </li>
                        <li class="li_nc"><a target="_self" href="#">Altri documenti</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('1301');" href="#">Richiesta Codici INPS</a></li>
                                <li class="li_nc"><a onclick="menuClick('1302');" href="#">Richieste validazione notifica</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>

        <li class="li_hc"><a href="#" target="_self">Testi</a>
            <ul class="ul_ch">
                <li><a href="#" target="_self">Html</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('2000');" href="#">Testi</a></li>
                        <li class="li_nc"><a onclick="menuClick('2003');" href="#">Sottotesti</a></li>
                    </ul>
                </li>
                <li><a href="#" target="_self">Atti</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('2001');" href="#">Sollecito Pre Ingiunzione</a></li>
                        <li class="li_nc"><a onclick="menuClick('2002');" href="#">Avviso di messa in mora</a></li>
                        <li class="li_nc"><a onclick="menuClick('28');" href="#">Ingiunzione</a></li>
                        <li class="li_nc"><a onclick="menuClick('43');" href="#">Sollecito di pagamento</a></li>
                        <li class="li_nc"><a onclick="menuClick('501');" href="#">Avviso di intimazione</a></li>
                    </ul>
                </li>
                <li><a href="#" target="_self">Pignoramenti</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('54');" href="#">Presso datore di lavoro</a></li>
                        <li class="li_nc"><a onclick="menuClick('74');" href="#">Presso banca</a></li>
                        <li class="li_nc"><a onclick="menuClick('83');" href="#">Beni mobili registrati</a></li>
                        <li class="li_nc"><a onclick="menuClick('108');" href="#">Sollecito Beni mobili registrati</a>
                        </li>
                        <li class="li_nc"><a onclick="menuClick('86');" href="#">Preavviso fermo amministrativo</a></li>
                        <li class="li_nc"><a onclick="menuClick('87');" href="#">Fermo amministrativo</a></li>
                    </ul>
                </li>
                <li><a href="#" target="_self">Altri documenti</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('111');" href="#">Archiviazione atto</a></li>
                        <li class="li_nc"><a onclick="menuClick('57');" href="#">Richiesta rateizzazione</a></li>
                        <li class="li_nc"><a onclick="menuClick('58');" href="#">Esito rateizzazione</a></li>
                        <li class="li_nc"><a onclick="menuClick('61');" href="#">Richiesta matricole</a></li>
                        <li class="li_nc"><a onclick="menuClick('66');" href="#">Richiesta indirizzo</a></li>
                        <li class="li_nc"><a onclick="menuClick('67');" href="#">Richiesta certificato di decesso</a>
                        </li>
                        <li class="li_nc"><a onclick="menuClick('68');" href="#">Richiesta duplicato AR</a></li>
                    </ul>
                </li>
            </ul>
        </li>
        <li class="li_hc"><a href="#" target="_self">Parametri</a>
            <ul class="ul_ch">
                <li><a href="#" target="_self">Ente</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('55');" href="#">Stemma</a></li>
                        <li class="li_nc"><a onclick="menuClick('17');" href="#">Dati Ente</a></li>
                        <li class="li_nc"><a onclick="menuClick('18');" href="#">Gestore</a></li>
                        <li class="li_nc"><a onclick="menuClick('30');" href="#">Ufficio</a></li>
                    </ul>
                </li>
                <li><a href="#" target="_self">Autorita'</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('35');" href="#">Tribunale</a></li>
                        <li class="li_nc"><a onclick="menuClick('36');" href="#">Giudice di Pace</a></li>
                        <li class="li_nc"><a onclick="menuClick('37');" href="#">Corte d'appello</a></li>
                        <li class="li_nc"><a onclick="menuClick('38');" href="#">Commissione tributaria provinciale</a>
                        </li>
                        <li class="li_nc"><a onclick="menuClick('39');" href="#">Commissione tributaria regionale</a>
                        </li>
                        <li class="li_nc"><a onclick="menuClick('40');" href="#">Corte di cassazione</a></li>
                    </ul>
                </li>
                <li><a href="#" target="_self">Enti esterni</a>
                    <ul class="ul_ch">
<!--                        <li class="li_nc"><a onclick="menuClick('46');" href="#">Avvocati</a></li>-->
                        <li class="li_nc"><a onclick="menuClick('47');" href="#">Sedi banche</a></li>
                        <li class="li_nc"><a onclick="menuClick('48');" href="#">Filiali banche</a></li>

                        <li class="li_nc"><a onclick="menuClick('60');" href="#">INPS</a></li>
                        <li class="li_nc"><a onclick="menuClick('85');" href="#">Tribunali / Ist. vendite
                                giudiziarie</a></li>
                        <li class="li_nc"><a onclick="menuClick('64');" href="#">Uffici anagrafici</a></li>
                        <li class="li_nc"><a onclick="menuClick('5001');" href="#">Uffici giudiziari</a></li>
                        <li class="li_nc"><a onclick="menuClick('65');" href="#">Uffici postali</a></li>
                    </ul>
                </li>
                <li><a href="#" target="_self">Generali</a>
                    <ul class="ul_ch">
                        <li><a onclick="menuClick('20');" href="#">Parametri annuali</a></li>
                        <li><a onclick="menuClick('106');" href="#">Interessi tributi</a></li>
                        <li><a onclick="menuClick('56');" href="#">Scorpori pagamenti</a></li>
                        <li><a onclick="menuClick('89');" href="#">Ricorsi</a></li>
                        <li><a onclick="menuClick('211');" href="#">Email/PEC</a></li>
                    </ul>
                </li>
                <li><a href="#" target="_self">Entrate</a>
                    <ul class="ul_ch">
                        <li><a href="#" target="_self">CDS / AMMINISTRATIVA</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('400');" href="#">Generali</a></li>
                                <!--<li class="li_nc"><a onclick="menuClick('77');" href="#">Email</a></li>-->
                                <li class="li_nc"><a onclick="menuClick('53');" href="#">Pagamento</a></li>
                                <li class="li_nc"><a onclick="menuClick('19');" href="#">Responsabili</a></li>
                            </ul>
                        </li>
                        <li><a href="#" target="_self">IMMOBILI</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('401');" href="#">Generali</a></li>
                                <!--<li class="li_nc"><a onclick="menuClick('92');" href="#">Email</a></li>-->
                                <li class="li_nc"><a onclick="menuClick('93');" href="#">Pagamento</a></li>
                                <li class="li_nc"><a onclick="menuClick('94');" href="#">Responsabili</a></li>
                            </ul>
                        </li>
                        <li><a href="#" target="_self">RIFIUTI</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('402');" href="#">Generali</a></li>
                                <!--<li class="li_nc"><a onclick="menuClick('96');" href="#">Email</a></li>-->
                                <li class="li_nc"><a onclick="menuClick('97');" href="#">Pagamento</a></li>
                                <li class="li_nc"><a onclick="menuClick('98');" href="#">Responsabili</a></li>
                            </ul>
                        </li>
                        <li><a href="#" target="_self">PATRIMONIALE</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('403');" href="#">Generali</a></li>
                                <!--<li class="li_nc"><a onclick="menuClick('100');" href="#">Email</a></li>-->
                                <li class="li_nc"><a onclick="menuClick('101');" href="#">Pagamento</a></li>
                                <li class="li_nc"><a onclick="menuClick('102');" href="#">Responsabili</a></li>
                            </ul>
                        </li>
                        <li><a href="#" target="_self">OSAP</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('404');" href="#">Generali</a></li>
                                <!--<li class="li_nc"><a onclick="menuClick('200');" href="#">Email</a></li>-->
                                <li class="li_nc"><a onclick="menuClick('201');" href="#">Pagamento</a></li>
                                <li class="li_nc"><a onclick="menuClick('202');" href="#">Responsabili</a></li>
                            </ul>
                        </li>
                        <li><a href="#" target="_self">PUBBLICITA'</a>
                            <ul class="ul_ch">
                                <li class="li_nc"><a onclick="menuClick('405');" href="#">Generali</a></li>
                                <!--<li class="li_nc"><a onclick="menuClick('204');" href="#">Email</a></li>-->
                                <li class="li_nc"><a onclick="menuClick('205');" href="#">Pagamento</a></li>
                                <li class="li_nc"><a onclick="menuClick('206');" href="#">Responsabili</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li><a href="#" target="_self">Tariffe</a>
                    <ul class="ul_ch">
                        <li class="li_nc"><a onclick="menuClick('52');" href="#">Pignoramento</a></li>
                    </ul>
                </li>

            </ul>
        </li>

    </ul>
</div>
