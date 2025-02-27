<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/html; charset=utf-8');

$message = ""; // Message à afficher
$messageClass = ""; // Classe CSS pour le message (success/error)

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Mode simple : Création d'un DNS unique
    if (!empty($_POST["dns_name"])) {

        $dns = validation($_POST["dns_name"]);
        $ip = exec("ip addr show eth2 | grep 'inet' | cut -d ' ' -f6 | cut -d/ -f1 | head -n 1");

        $output = [];
        $command = "/var/www/html/sous_dns.sh '$dns'";
        exec($command, $output, $ret);

        if ($ret != 0) {
            $message = "ERREUR LORS DE LA CREATION DU DNS.";
            $messageClass = "error";
        } else {
            $message = "DNS EN SERVICE.";
            $messageClass = "success";
        }
    }

    // Mode expert : Création d'une zone avec sous-DNS
    if (isset($_POST["zone_dns"]) && !empty($_POST["dns_second"]) && is_array($_POST["dns_second"])) {
        $zone_dns = validation($_POST["zone_dns"]);
        $tab_dns = [];

        foreach ($_POST["dns_second"] as $dns_second) {
            if (!empty($dns_second)) {
                $tab_dns[] = validation($dns_second);
            }
        }

        if (empty($tab_dns)) {
            $message = "ERREUR LORS DE LA CREATION DE LA ZONE";
            $messageClass = "error";
        } else {
            $command = "/var/www/html/ledns.sh '$zone_dns'";
            foreach ($tab_dns as $dns) {
                $command .= " '$dns'";
            }
            exec($command, $output, $ret);

            if ($ret !== 0) {
                $message = "ERREUR LORS DE LA CREATION DE LA ZONE";
                $messageClass = "error";
            } else {
                $message = "ZONE $zone_dns EN SERVICE AINSI QUE SES SOUS DNS.";
                $messageClass = "success";
            }
        }
    }
}

// Fonction de validation
function validation($vari) {
    $vari = htmlspecialchars($vari);
    $vari = strip_tags($vari);
    $vari = trim($vari);

    return $vari;
}

/*
  Ci-dessous les fonctions pour le listing des DNS à dig ainsi que leur requetage

  Gestion du formulaire pour la demande de visualisation des DNS
*/

function dig($dns) {
    $command = escapeshellcmd("dig $dns");
    $output = [];
    exec($command, $output);
    return implode("<br>", $output); // Retourne les résultats formatas
}

function listeDns() {
    $chemin = "/etc/bind/";
    $l_dns = glob($chemin . "db.*");
    $dns_a_dig = [];

    for ($i = 0; $i < count($l_dns); $i++) {
        $extract = explode("db.", basename($l_dns[$i]));
        if (count($extract) === 2) {
            $dns_a_dig[] = $extract[1];
        }
    }

    return $dns_a_dig;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["dns_a_dig"])) {
    $dns_a_dig = htmlspecialchars($_POST["dns_a_dig"]);
    $resultat = dig($dns_a_dig);

    // Afficher le résultat
    echo "<!DOCTYPE html>";
    echo "<html lang='fr'>";
    echo "<head><title>Résultat Dig</title></head>";
    echo "<body>";
    echo "<h1>Résultat du Dig pour $dns_a_dig</h1>";
    echo "<pre>" . ($resultat ? $resultat : "Aucun résultat trouvé pour $dns_a_dig.") . "</pre>";
    echo "<a href='dns.php'>Retour</a>";
    echo "</body>";
    echo "</html>";
    exit;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Gestion des DNS</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            display: flex;
        }
       
        .notif-banner {
            padding: 15px;
            border-radius: 5px;
            font-size: 16px;
            margin-bottom: 20px;
            max-width: 300px; /* Limite la largeur */
            width: 100%; /* S'assure qu'elle n'excède pas le conteneur */
            position: relative; /* Position relative par rapport à son conteneur */
        }
        .notif-banner.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .notif-banner.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .notif-container {
            flex: 1;
            margin-right: 20px;
            display: flex; /* Pour aligner les bannières */
            justify-content: flex-start; /* Positionne à gauche */
        }
        .form-container {
            flex: 3; /* Ajuste l'espace du formulaire */
        }


        h1, h2 {
            text-align: center;
            color: #343a40;
        }
        label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
        }
        input[type="text"], input[type="number"], select {
            width: 100%;
            padding: 10px;
            margin: 5px 0 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 10px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .top-left {
            margin-bottom: 20px;
        }
        .hidden {
            display: none;
        }
        #dns-container {
            margin-top: 10px;
        }
        .dns-input {
            margin: 5px 0;
        }
    </style>
    <script>
        // Fonction pour basculer entre le mode normal et le mode expert
        function change_mode() {
            const expertMode = document.getElementById('mode').checked;
            document.getElementById('facile').classList.toggle('hidden', expertMode);
            document.getElementById('exp').classList.toggle('hidden', !expertMode);
        }

        // Fonction pour générer les champs des sous-DNS en mode expert
        function ajout_dns() {
            const conte = document.getElementById('dns-container');
            conte.innerHTML = ''; // Réinitialiser le conteneur

            const nombre_dns = document.getElementById('nb_sousdns').value;

            if (nombre_dns < 1 || nombre_dns > 10) {
                alert("Le nombre de sous-DNS doit etre compris entre 1 et 10.");
                return;
            }

            for (let i = 0; i < nombre_dns; i++) {
                const ajout = document.createElement('input');
                ajout.type = 'text';
                ajout.name = 'dns_second[]';
                ajout.placeholder = `Sous-DNS ${i + 1}`;
                ajout.classList.add('dns-input');

                conte.appendChild(ajout);
                conte.appendChild(document.createElement('br'));
            }
        }
    </script>
    
    
</head>
<body>
    <?php 
    // Inclure menu.php avec le message et la classe correspondante
    include 'menu.php'; 
    if (!empty($message)): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const notifBanner = document.getElementById("notif-banner");
                if (notifBanner) {
                    notifBanner.textContent = "<?php echo addslashes($message); ?>";
                    notifBanner.classList.add("<?php echo $messageClass; ?>");
                    notifBanner.style.display = "block"; // Affiche la banni�re

                    // Disparait après 5 secondes
                    setTimeout(() => {
                        notifBanner.style.transition = "opacity 0.5s ease";
                        notifBanner.style.opacity = "0";
                        setTimeout(() => notifBanner.style.display = "none", 500); // Cache la div apr�s la transition
                    }, 5000);
                }
            });
        </script>
    <?php endif; ?>

    <div class="container">
        <!-- Conteneur du formulaire -->
        <div class="form-container">
            <div class="top-left">
                <label>
                    <input type="checkbox" id="mode" onchange="change_mode()"> Mode Expert
                </label>
            </div>
            <h1>Gestion des DNS</h1>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <!-- Mode normal -->
                <div id="facile">
                    <label for="dns">Nom du DNS :</label>
                    <input type="text" name="dns_name" placeholder="exemple.com">
                </div>

                <!-- Mode expert -->
                <div id="exp" class="hidden">
                    <label for="zone_dns">Nom de la zone :</label>
                    <input type="text" name="zone_dns" placeholder="mazone">
                    <label for="nb_sousdns">Nombre de sous-DNS :</label>
                    <input type="number" id="nb_sousdns" min="1" max="10">
                    <button type="button" onclick="ajout_dns()">AJOUTER</button>
                    <div id="dns-container"></div>
                </div>

                <button type="submit">Envoyer</button>
            </form>

            <h2>Testez un DNS</h2>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <label for="dns_a_dig">SELECTION D'UN DNS A TESTER :</label>
                <select name="dns_a_dig" id="dns_a_dig">
                    <?php
                    $dns_disponibles = listeDns();
                    if (!empty($dns_disponibles)) {
                        foreach ($dns_disponibles as $dns) {
                            echo "<option value='" . htmlspecialchars($dns) . "'>" . htmlspecialchars($dns) . "</option>";
                        }
                    } else {
                        echo "<option value='' disabled>Aucun DNS disponible</option>";
                    }
                    ?>
                </select>
                <button type="submit" name="test_dns">Tester</button>
            </form>
        </div>
    </div>
</body>
</html>

