#aggiornamento spese notifica preavviso sbagliate ( 4.3 spese errate sostituite con 20 )

UPDATE `pignoramento_generale` PG JOIN notifica_atto NA ON NA.Atto_Notificato_ID=PG.ID 
SET NA.Spese_Notifica=20, PG.Totale_Dovuto=PG.Totale_Dovuto-4.3+20, PG.Spese_Notifica_Debitore=20, PG.Totale_Spese_Notifica=20 
WHERE PG.Elaboration_Id=817 AND PG.Spese_Notifica_Debitore=4.3