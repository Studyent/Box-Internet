#!/bin/bash

# Identifiants FTP
FTP_HOST="10.10.10.2"
FTP_USER="stud"
FTP_PASS="stud"

# Fichiers de résultats

DOWNLOAD_FILE="download_results.txt"
GRAPH_FILE="combined_graph.png"

# Taille totale à transférer (par défaut : 1000 MB si aucun argument n'est fourni)
SIZE_MB=${1:-1000}

# Réinitialisation du fichier des résultats
# Ajoute un en-tête avec les colonnes "Taille cumulée" et "Vitesse"
echo "# Taille cumulée(MB) Vitesse(MB/s)" > $DOWNLOAD_FILE

# Fonction pour tester le téléchargement par morceaux
test_download() {
    local size_mb=$1
    local total_downloaded=0

    # Taille des morceaux à télécharger (100 MB par défaut, ajusté pour les petites tailles)
    local chunk_size=$(($size_mb >= 500 ? 100 : ($size_mb / 10)))
        # Boucle pour télécharger les morceaux jusqu'à atteindre la taille totale
    while [ $total_downloaded -lt $size_mb ]; do
        if [ $(($total_downloaded + $chunk_size)) -gt $size_mb ]; then
            chunk_size=$(($size_mb - $total_downloaded))
        fi

        local file_name="test_${chunk_size}MB.bin"

        START_TIME=$(date +%s.%N)
        curl -o /dev/null ftp://$FTP_USER:$FTP_PASS@$FTP_HOST/$file_name --silent
        END_TIME=$(date +%s.%N)

        # Calculer le temps et la vitesse
        DURATION=$(echo "$END_TIME - $START_TIME" | bc)
        SPEED=$(echo "$chunk_size / $DURATION" | bc -l)

        # Mettre à jour la taille totale transférée
        total_downloaded=$(($total_downloaded + $chunk_size))

        # écrire les résultats dans le fichier
        echo "$total_downloaded $SPEED" >> $DOWNLOAD_FILE
    done
}

# Lancer le test de téléchargement avec la taille spécifiée
test_download $SIZE_MB

# Générer un graphe combiné avec Gnuplot
gnuplot <<- EOF
    set terminal png size 800,600
    set output "$GRAPH_FILE"
    set title "Débit Upload & Download (Taille: ${SIZE_MB} MB)"
    set xlabel "Taille cumulée (MB)"
    set ylabel "Vitesse (MB/s)"
    set grid
    set xtics auto
    set ytics auto
    plot \
        "upload_results.txt" using 1:2 with linespoints title "Upload" lc rgb "blue", \
        "download_results.txt" using 1:2 with linespoints title "Download" lc rgb "red"
EOF
