#!/bin/bash

# Identifiants FTP
FTP_HOST="10.10.10.2"
FTP_USER="stud"
FTP_PASS="stud"

# Résultats
UPLOAD_FILE="upload_results.txt"

# Taille totale à transférer (par défaut 1000 MB si aucun argument n'est passé)
SIZE_MB=${1:-1000}

# Réinitialiser les résultats
echo "# Taille cumulée(MB) Vitesse(MB/s)" > $UPLOAD_FILE

# Fonction pour créer un fichier temporaire de taille donn�e
create_test_file() {
    local size_mb=$1
    local file_name="test_${size_mb}MB.bin"
    dd if=/dev/zero of=$file_name bs=1M count=$size_mb >/dev/null 2>&1
    echo $file_name
}

# Fonction pour tester l'upload par morceaux
test_upload() {
    local size_mb=$1
    local total_uploaded=0

    # Définir la taille des morceaux (100 MB par défaut, ajusté pour les petites tailles)
    local chunk_size=$(($size_mb >= 500 ? 100 : ($size_mb / 10)))

    while [ $total_uploaded -lt $size_mb ]; do
        if [ $(($total_uploaded + $chunk_size)) -gt $size_mb ]; then
            chunk_size=$(($size_mb - $total_uploaded))
        fi

        # Créer un fichier temporaire pour ce morceau
        chunk_file=$(create_test_file $chunk_size)

        START_TIME=$(date +%s.%N)
        curl -T "$chunk_file" ftp://$FTP_USER:$FTP_PASS@$FTP_HOST/ --silent
        END_TIME=$(date +%s.%N)

        # Calculer le temps et la vitesse
        DURATION=$(echo "$END_TIME - $START_TIME" | bc)
        SPEED=$(echo "$chunk_size / $DURATION" | bc -l)

        # Mettre à jour la taille totale transférée
        total_uploaded=$(($total_uploaded + $chunk_size))

        # écrire les résultats dans le fichier
        echo "$total_uploaded $SPEED" >> $UPLOAD_FILE

        # Nettoyer le fichier temporaire
        rm -f $chunk_file
    done
}

# Tester l'upload avec la taille spécifiée
test_upload $SIZE_MB
