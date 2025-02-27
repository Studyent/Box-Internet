<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Administration Box</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Josefin Sans', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f2f2f2;
        }

        /* Menu */
        nav {
            background-color: #fff;
            color: #333;
            padding: 10px;
            text-align: center;
            display: block;
            width: 100%;
            border-bottom: 1px solid #ddd;
        }

        /* Dashboard Container */
        .dashboard {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            padding: 20px;
            align-items: start;
        }

        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: left;
            position: relative;
        }

        .card h2 {
            margin: 0 0 10px 0;
            font-size: 1.2rem;
            color: #333;
        }

        .card p {
            margin: 5px 0;
            color: #555;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }

        .status-indicator span {
            margin-left: 10px;
        }

        .status-indicator .online {
            color: green;
            font-weight: bold;
        }

        .status-indicator .offline {
            color: red;
            font-weight: bold;
        }

        .center-device {
            grid-column: span 3;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .device {
            width: 100px;
            height: 200px;
            background-color: #333;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
        }

        .device .icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .device p {
            margin: 0;
        }

        footer {
            text-align: center;
            padding: 20px;
            background-color: #fff;
            border-top: 1px solid #ddd;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>

    <!-- Dashboard -->
    <div class="dashboard">
        <!-- Internet Information -->
        <div class="card">
            <h2>Internet</h2>
            <p><strong>Adresse IP :</strong> 
                <?php 
                // Récupère l'adresse IP locale
                $ip = exec("ip addr show eth1 | grep 'inet' | cut -d ' ' -f6 | cut -d / -f1 | head -n 1");
                echo $ip ? $ip : 'Non disponible';
                ?>
            </p>
            <p><strong>Etat :</strong> En Service</p>
            <div class="status-indicator">
                <i class="fas fa-circle"></i>
                <span class="online">Actif</span>
            </div>
        </div>

        <!-- Interfaces Actives -->
        <div class="card">
            <h2>Interfaces Actives</h2>
            <?php 
            // Récupère les interfaces réseau actives
            $interfaces = shell_exec("ip link show | grep 'state UP' | awk '{print $2}' | sed 's/://'");
            $interfaces = $interfaces ? explode("\n", trim($interfaces)) : [];
            if (!empty($interfaces)) {
                foreach ($interfaces as $interface) {
                    echo "<p><i class='fas fa-network-wired'></i> $interface</p>";
                }
            } else {
                echo "<p>Aucune interface active</p>";
            }
            ?>
        </div>

        <!-- Device Information -->
        <div class="center-device">
            <div class="device">
                <i class="fas fa-wifi icon"></i>
                <p>Ma Box</p>
                <p>MEDMED</p>
            </div>
        </div>

        
        <div class="card">
            <h2>Telephone</h2>
            <p><strong>Statut :</strong> Disponible</p>
            <div class="status-indicator">
                <i class="fas fa-circle"></i>
                <span class="online">Connect�</span>
            </div>
        </div>


        <!-- Diagnostics -->
        <div class="card">
            <h2>Diagnostics</h2>
            <p><a href="diagnostics.php">Lancer un diagnostic</a></p>
        </div>
    </div>

    <footer>
         2024 - Administration de la Box MEDMED
    </footer>
</body>
</html>
