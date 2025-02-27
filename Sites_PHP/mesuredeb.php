<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Debit Internet</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #343a40;
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
            align-items: center;
        }
        h1, h3 {
            text-align: center;
            color: #343a40;
            margin-bottom: 20px;
        }
        .hidden {
            display: none;
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
        input[type="checkbox"] {
            margin: 10px;
            transform: scale(1.2);
        }
        select, input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        img {
            margin: 20px 0;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 100%;
        }
        .history {
            margin-top: 20px;
            text-align: center;
        }
        .history ul {
            list-style: none;
            padding: 0;
        }
        .history li {
            margin-bottom: 10px;
        }
        .history a {
            color: #007bff;
            text-decoration: none;
        }
        .history a:hover {
            text-decoration: underline;
        }
    </style>
    <script>
        function change_mode() {
            const exp = document.getElementById('exp');
            const facile = document.getElementById('facile');
            const mode = document.getElementById('mode');

            if (mode.checked) {
                exp.classList.remove('hidden');
                facile.classList.add('hidden');
            } else {
                exp.classList.add('hidden');
                facile.classList.remove('hidden');
            }
        }

        function startTest(fileSize) {
            window.location.href = `mesuredeb.php?action=start&file_size=${fileSize}`;
        }
    </script>
</head>
<body>
    <?php include 'menu.php'; ?>
    <div class="container">
        <h1>Test de Debit Internet</h1>

        <h3>Choisissez un mode</h3>
        <label for="mode">Mode Expert</label>
        <input type="checkbox" id="mode" onclick="change_mode()">

        <div id="facile">
            <h3>Mode Facile</h3>
            <button onclick="startTest('1000')">Lancer un test</button>
        </div>

        <div id="exp" class="hidden">
            <h3>Mode Expert</h3>
            <form method="GET" action="mesuredeb.php">
                <label for="file_size">Taille du fichier (MB):</label>
                <select name="file_size" id="file_size">
                    <option value="100">100 MB</option>
                    <option value="500">500 MB</option>
                    <option value="1000">1000 MB</option>
                </select>
                <br>
                <button type="submit" name="action" value="start">Lancer le test</button>
            </form>
        </div>

        <?php
        $graph_dir = "graphs";
        $graph_file = "$graph_dir/combined_graph_" . time() . ".png";

        // Creer le repertoire des graphes s'il n'existe pas
        if (!is_dir($graph_dir)) {
            mkdir($graph_dir);
        }

        // Si l'utilisateur a lance un test
        if (isset($_GET['action']) && $_GET['action'] === 'start') {
            $size = isset($_GET['file_size']) ? intval($_GET['file_size']) : 1000;

            // Supprimer les anciens fichiers de resultats
            @unlink("upload_results.txt");
            @unlink("download_results.txt");

            // Executer les scripts upload et download
            shell_exec("bash upload.sh $size");
            shell_exec("bash download.sh $size");

            // Copier le graphique genere
            @rename("combined_graph.png", $graph_file);
        }

        // Afficher le graphique le plus recent
        $graphs = glob("$graph_dir/*.png");
        rsort($graphs);

        if (!empty($graphs)) {
            echo '<img src="' . $graphs[0] . '" alt="Graphique combine Upload & Download">';
        }

        // Calculer la moyenne des vitesses
        function calculate_average($file) {
            if (!file_exists($file)) {
                return 0;
            }

            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $total_speed = 0;
            $count = 0;

            foreach ($lines as $line) {
                if (preg_match('/^#/', $line)) continue;
                list($size, $speed) = explode(" ", $line);
                $total_speed += floatval($speed);
                $count++;
            }

            return $count > 0 ? $total_speed / $count : 0;
        }

        $avg_upload = calculate_average("upload_results.txt");
        $avg_download = calculate_average("download_results.txt");

        echo "<p>Moyenne Upload : " . round($avg_upload, 2) . " MB/s</p>";
        echo "<p>Moyenne Download : " . round($avg_download, 2) . " MB/s</p>";

        // Afficher l'historique des graphes
        echo '<div class="history"><h3>Historique des Graphes</h3><ul>';
        foreach ($graphs as $graph) {
            $date = date("d-m-Y H:i:s", filemtime($graph));
            echo '<li><a href="' . $graph . '" target="_blank">' . $date . '</a></li>';
        }
        echo '</ul></div>';
        ?>
    </div>
</body>
</html>
