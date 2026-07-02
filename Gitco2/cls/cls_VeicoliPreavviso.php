<?php

    class VeicoliPreavviso
    {
        private const tabVeicoliDetenuti =
                                "<strong>ULTERIORI VEICOLI DETENUTI ED ATTUALMENTE&nbsp;NON OGGETTO DI PREAVVISO DI FERMO AMMINISTRATIVO</strong><br />".
                                "<table border=\"0\" style=\"width:100%;\" cellpadding=\"1\"> ".
                                "    <thead> ".
                                "    <tr> ".
                                "    <td align=\"left\"><strong>Tipo</strong></td> ".
                                "    <td align=\"left\"><strong>Targa</strong></td> ".
                                "    <td align=\"left\"><strong>Marca</strong></td> ".
                                "    <td align=\"left\"><strong>Modello</strong></td> ".

                                "    </tr> ".
                                "    </thead> ".
                                "<tbody> ".
                                " {{PlaceHolder}}".
                                "</tbody> ".
                                "</table> ";

        private const tabVeicoliFermo =
                                "<strong>VEICOLO CHE VERRA&#39; SOTTOPOSTO AL FERMO AMMINISTRATIVO</strong><br />".
                                "<table border=\"0\" style=\"width:100%;\" cellpadding=\"1\"> ".
                                "    <thead> ".
                                "    <tr> ".
                                "    <td align=\"left\"><strong>Tipo</strong></td> ".
                                "    <td align=\"left\"><strong>Targa</strong></td> ".
                                "    <td align=\"left\"><strong>Marca</strong></td> ".
                                "    <td align=\"left\"><strong>Modello</strong></td> ".
                                "    </tr> ".
                                "    </thead> ".
                                "<tbody> ".
                                " {{PlaceHolder}}".
                                "</tbody> ".
                                "</table> ";

        private static function retQuery($utente_id,$id_veicolo_fermo)
        {
            return "select  ".
            "Utente_ID, ".
            "V.SerieTarga as Tipo, ".
            "V.Tipo as Modello, ".
            "V.Fabbrica as Marca, ".
            "V.Targa, ".
            "V.Serie, ".
            "V.ClasseVeicolo, ".
            "V.Data_Visura, ".
            "V.StatoVeicolo ".
            "from veicoli as V ".
            "join utente as U on V.Utente_ID=U.ID ".
            "Where U.ID = ".$utente_id." ".
            "And V.ID <> ".$id_veicolo_fermo." AND (V.StatoVeicolo NOT IN ('Perdita possesso','Radiazione') OR V.StatoVeicolo is null) ".
            "Order by V.DataPrimaImmatricolazione desc ";
        }

        private static function GetVeicoli(cls_db $cls_db,$utente_id,$id_veicolo_fermo)
        {
            return $cls_db->getResults($cls_db->ExecuteQuery(self::retQuery($utente_id,$id_veicolo_fermo)));
        }

        private static function transform($a_result,$textrow,$index,&$result)
        {

            if(($index<3) && ($index<count($a_result)))
            {
                $result.=$textrow($a_result[$index]);
                self::transform($a_result,$textrow,$index+1,$result);
            }
            else{
                if(count($a_result)>=3)
                    $result.=$textrow(array("Tipo"=>"Omissis","Marca"=>"Omissis","Targa"=>"Omissis","Modello"=>"Omissis"));
                return $result;
            }
                
        }

        private static function RigaHTML($Tipo,$Targa, $Marca,$Modello)
        {
            $Tipo = "<td align=\"left\">".$Tipo."</td>";
            $Targa = "<td align=\"left\">".$Targa."</td>";
            $Marca = "<td align=\"left\">".$Marca."</td>";
            $Modello = "<td align=\"left\">".$Modello."</td>";
            
            return "<tr>".$Tipo.$Targa.$Marca.$Modello."</tr>";
        }
        public static function GetHTMLTable(cls_db $cls_db,$utente_id,$id_veicolo_fermo)
        {
            $a_result = self::GetVeicoli($cls_db,$utente_id,$id_veicolo_fermo);
            if(count($a_result)==0)
                return "";
            
            $textrow = function ($row)
            {
                return self::RigaHTML($row["Tipo"],$row["Targa"],$row["Marca"],$row["Modello"]);
            };
            
            self::transform($a_result,$textrow,0,$rowshtml);
            return str_replace("{{PlaceHolder}}",$rowshtml,self::tabVeicoliDetenuti);

        }

        public static function VeicoloFermoHTML($Tipo,$Targa,$Marca,$Modello)
        {
            return str_replace("{{PlaceHolder}}",self::RigaHTML(strtoupper($Tipo),$Targa,$Marca,$Modello),self::tabVeicoliFermo);
        }
    }
?>