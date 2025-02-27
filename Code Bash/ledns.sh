#!/bin/bash

if [ $# -lt 1 ]; then
    echo "Arguments manquants. Usage : $0 <zone_principale> [sous_dns...]"
    exit 1
fi

DNS=$1
shift # Retire le premier argument pour ne conserver que les sous-DNS (s'il y en a)

IP_DEB=$(ip addr show eth2 | grep 'inet' | cut -d ' ' -f6 | cut -d/ -f1 | head -n 1)
IP_AJOUT=$IP_DEB

# Sauvegardes des fichiers existants
sudo cp /etc/bind/named.conf.local /etc/bind/named.conf.local.bak
sudo cp /etc/bind/db.192 /etc/bind/db.192.bak

# Création du fichier de la zone principale
sudo touch "/etc/bind/db.$DNS.medmed.com"

Ajout_zone="zone \"$DNS.medmed.com\" {
    type master;
    file \"/etc/bind/db.$DNS.medmed.com\";
};"

# Vérifier si la zone existe déjà dans named.conf.local
if ! grep -q "zone \"$DNS.medmed.com\"" /etc/bind/named.conf.local; then
    echo "$Ajout_zone" | sudo tee -a /etc/bind/named.conf.local
    echo "Zone $DNS.medmed.com ajoutée à /etc/bind/named.conf.local."
else
    echo "Zone $DNS.medmed.com existe déjà dans named.conf.local."
fi

# Contenu de base du fichier de la zone principale
Ajout_content_dns_db_file="
;
; BIND data file for local loopback interface
;

\$TTL    604800
@       IN      SOA     $DNS.medmed.com. admin.$DNS.medmed.com. (
                              2         ; Serial
                         604800         ; Refresh
                          86400         ; Retry
                        2419200         ; Expire
                         604800 )       ; Negative Cache TTL
;
@       IN      NS      ns1.$DNS.medmed.com.
@       IN      A       $IP_AJOUT
ns1     IN      A       $IP_AJOUT
"

# Ajouter uniquement si le fichier est vide
if ! grep -q "SOA" "/etc/bind/db.$DNS.medmed.com"; then
    echo "$Ajout_content_dns_db_file" | sudo tee /etc/bind/db.$DNS.medmed.com > /dev/null
    echo "Fichier de zone db.$DNS.medmed.com créé."
else
    echo "Fichier de zone db.$DNS.medmed.com existe déjà."
fi

# Ajout en SSH dans le fichier du FAI
IP_R="10.10.10.1"
PORT_R=2226
CHEMIN="/home/stud/.ssh/id_rsa"

sudo ssh -o StrictHostKeyChecking=no -i $CHEMIN stud@10.10.10.2 -p "$PORT_R" "if ! grep -q '$DNS IN A $IP_R' /etc/bind/db.medmed.com; then echo '$DNS.medmed.com. IN A $IP_R' | sudo tee -a /etc/bind/db.medmed.com; sudo systemctl restart bind9; fi"

# Gestion des sous-DNS
for DNSs in "$@"; do
    # Vérifie si l'enregistrement A existe déjà dans le fichier local
    if ! grep -q "$DNSs.$DNS.medmed.com. IN A $IP_AJOUT" "/etc/bind/db.$DNS.medmed.com"; then
        echo "$DNSs.$DNS.medmed.com. IN A $IP_AJOUT" | sudo tee -a /etc/bind/db.$DNS.medmed.com > /dev/null
        echo "Enregistrement A ajouté pour $DNSs.$DNS.medmed.com dans db.$DNS.medmed.com."
    else
        echo "Enregistrement A pour $DNSs.$DNS.medmed.com existe déjà dans db.$DNS.medmed.com."
    fi

    # Vérifie si un fichier de zone à part doit etre créé pour le sous-DNS
    SousDNS_File="/etc/bind/db.$DNSs.$DNS.medmed.com"
    if [ ! -f "$SousDNS_File" ]; then
        echo "; Fichier de zone pour $DNSs.$DNS.medmed.com
\$TTL    604800
@       IN      SOA     $DNSs.$DNS.medmed.com. admin.$DNSs.$DNS.medmed.com. (
                              2         ; Serial
                         604800         ; Refresh
                          86400         ; Retry
                        2419200         ; Expire
                         604800 )       ; Negative Cache TTL
;
@       IN      NS      ns1.$DNSs.$DNS.medmed.com.
@       IN      A       $IP_AJOUT
ns1     IN      A       $IP_AJOUT
" | sudo tee "$SousDNS_File" > /dev/null
        echo "Fichier de zone créé pour $DNSs.$DNS.medmed.com."
    else
        echo "Fichier de zone pour $DNSs.$DNS.medmed.com existe déjà."
    fi

    # Ajouter une déclaration pour la zone dans named.conf.local
    if ! grep -q "zone \"$DNSs.$DNS.medmed.com\"" /etc/bind/named.conf.local; then
        echo "zone \"$DNSs.$DNS.medmed.com\" {
    type master;
    file \"/etc/bind/db.$DNSs.$DNS.medmed.com\";
};" | sudo tee -a /etc/bind/named.conf.local > /dev/null
        echo "Zone $DNSs.$DNS.medmed.com ajoutée à named.conf.local."
    else
        echo "Zone $DNSs.$DNS.medmed.com existe déjà dans named.conf.local."
    fi

    # Vérifie si l'enregistrement NS existe déjà sur le serveur distant
    sudo ssh -o StrictHostKeyChecking=no -i $CHEMIN stud@10.10.10.2 -p "$PORT_R" "
    if ! grep -q '$DNSs.$DNS.medmed.com. IN NS $DNS.medmed.com.' /etc/bind/db.medmed.com; then
        echo '$DNSs.$DNS.medmed.com. IN NS $DNS.medmed.com.' | sudo tee -a /etc/bind/db.medmed.com
        sudo systemctl restart bind9
        echo 'Enregistrement NS ajouté pour $DNSs.$DNS.medmed.com sur le serveur distant.'
    else
        echo 'Enregistrement NS pour $DNSs.$DNS.medmed.com existe déjà sur le serveur distant.'
    fi"
done

# Restart BIND localement
sudo systemctl restart bind9

# Renvoie
echo "Zone principale $DNS.medmed.com et ses sous-DNS configurés avec enregistrements A locaux, fichiers db créés, et délégations NS vérifiées sur le serveur FAI."
