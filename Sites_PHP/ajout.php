<?php


if($_SERVER["REQUEST_METHOD"] == "POST"){


    $ocu = validation($_POST["octett"]);
    $ocd = validation($_POST["octetq"]);

    if( !empty($ocu) && !empty($ocd) && (is_numeric($ocu) && (int)$ocu >= 0 && (int)$ocu <= 254) && 
      (is_numeric($ocd) && (int)$ocd >= 4 && (int)$ocd <= 254)  
    ){


        $ip = "192.168.".$ocu.".".$ocd;

        $command = "/var/www/html/script.sh ".escapeshellarg($ip);
        $sortie = exec($command,$output,$code_retour);

        if($code_retour != 0){
         echo "<p>"."Erreur la commande ne s'est pas déroulé correctement"."</p>";
         echo "<pre>".implode("\n",$output)."</pre>";

            
        }else{

            echo "<p>"."La commande s'est déroulé correctement"."</p>";
            

        }


    }else{

        echo "<p>"."Erreur, merci de rentrer des nombres valides pour les ip"."</p>";

    }




}else{
    echo "<p>"."Erreur lors de la soumission du formulaire"."</p>";
}

function validation($vari){
    $vari = htmlspecialchars($vari);
    $vari = strip_tags($vari);
    $vari = trim($vari);
   

    return $vari;

}


?>