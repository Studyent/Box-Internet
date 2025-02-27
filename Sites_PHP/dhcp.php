<?php

ini_set("display_errors", 1);
error_reporting(E_ALL);
header('Content-Type: text/html; charset=utf-8');

$message = "";
$messageClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code_retour = 0;
    $ip = exec("ip addr show eth1 | grep 'inet' | cut -d ' ' -f6 | cut -d / -f1 | head -n 1");
    $octets = explode(".", $ip);

    if (isset($_POST["plage"]) && !empty($_POST["plage"])) {
        $plage = validation($_POST["plage"]);
        $command = "/var/www/html/lol.sh " . escapeshellarg($plage);
        exec($command, $output, $code_retour);
    }

    if (isset($_POST["octett"]) && isset($_POST["octetq"])) {
        $ocu = validation($_POST["octett"]);
        $ocd = validation($_POST["octetq"]);
    }

    if (!empty($ocu) && !empty($ocd) && is_numeric($ocu) && is_numeric($ocd) && ($ocu >= 0 && $ocu <= 255) && ($ocd >= 4 && $ocd <= 254)) {
        if ($ocu < $ocd) {
            $ip_deb = $octets[0] . "." . $octets[1] . "." . $octets[2] . "." . $ocu;
            $ip_fin = $octets[0] . "." . $octets[1] . "." . $octets[2] . "." . $ocd;
            $command = "/var/www/html/lol.sh '$ocu' '$ocd'";
            exec($command, $output, $code_retour);
        } else {
            $message = "L'IP du debut ne peut pas etre superieure a celle de fin.";
            $messageClass = "error";
        }
    }

    if ($code_retour != 0) {
        $message = "Erreur : la commande ne s'est pas deroulee correctement.";
        $messageClass = "error";
    } else {
        $message = "La commande s'est deroulee avec succes.";
        $messageClass = "success";
    }
}

function validation($vari)
{
    $vari = htmlspecialchars($vari);
    $vari = strip_tags($vari);
    $vari = trim($vari);

    return $vari;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Gestion du DHCP</title>
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
            flex-direction: column;
        }
        .notif-banner {
            padding: 15px;
            border-radius: 5px;
            font-size: 16px;
            margin-bottom: 20px;
            display: none;
        }
        .notif-banner.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            display: block;
        }
        .notif-banner.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            display: block;
        }
        h1, h3 {
            text-align: center;
            color: #343a40;
        }
        label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
        }
        input[type="text"], input[type="number"] {
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
            display: block;
            width: 100%;
        }
        button:hover {
            background-color: #0056b3;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>
    <div class="container">
        <?php if (!empty($message)): ?>
            <div class="notif-banner <?php echo $messageClass; ?>" id="notif-banner">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <h1>Gestion du DHCP</h1>
        <h3>Mode facile ou expert</h3>

        <input type="checkbox" id="mode" onchange="switch_exp()"> <label for="mode">Mode Expert</label>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <div id="facile">
                <label for="plage">Nombre d'appareils voulu :</label>
                <input type="text" name="plage" pattern="\d{1,3}" placeholder="10">
            </div>
            <div id="expert" class="hidden">
                <label>Debut de la plage :</label>
                <input type="text" name="octett" pattern="\d{1,3}" placeholder="<?php echo $octets[0] . '.' . $octets[1] . '.' . $octets[2] . '.x'; ?>">
                <label>Fin de la plage :</label>
                <input type="text" name="octetq" pattern="\d{1,3}" placeholder="<?php echo $octets[0] . '.' . $octets[1] . '.' . $octets[2] . '.x'; ?>">
            </div>
            <button type="submit">Valider mon choix</button>
        </form>
    </div>

    <script>
        function switch_exp() {
            const expertDiv = document.getElementById('expert');
            const facileDiv = document.getElementById('facile');
            const modeCheckbox = document.getElementById('mode');

            if (modeCheckbox.checked) {
                expertDiv.classList.remove('hidden');
                facileDiv.classList.add('hidden');
            } else {
                expertDiv.classList.add('hidden');
                facileDiv.classList.remove('hidden');
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            const notifBanner = document.getElementById("notif-banner");
            if (notifBanner) {
                setTimeout(() => {
                    notifBanner.style.transition = "opacity 0.5s ease";
                    notifBanner.style.opacity = "0";
                    setTimeout(() => {
                        notifBanner.style.display = "none";
                    }, 500); // Cache après la transition
                }, 5000);
            }
        });
    </script>
</body>
</html>
