#!/bin/bash

# Vérifie si 1 ou 2 arguments sont fournis

if [ $# -eq 1 ]; then
    DNS=$1

elif [ $# -eq 2 ]; then

    DNS=$1
    DNSs=$2

else

   echo "Arguments manquants"
   exit 1

fi

# Obtient l'adresse IP actuelle de l'interface eth2

IP_DEB=$(ip addr show eth2 | grep 'inet' | cut -d ' ' -f6 | cut -d/ -f1 | head -n 1)

# Adresse IP à utiliser dans les fichiers DNS

IP_AJOUT=$IP_DEB;

# Sauvegarde des fichiers de configuration existants

sudo cp /etc/bind/named.conf.local /etc/bind/named.conf.local.bak
sudo cp /etc/bind/db.192 /etc/bind/db.192.bak

# Ajoute la configuration DNS pour le domaine principal dans named.conf.local

echo "$Ajout_DNS" | sudo tee -a "/etc/bind/named.conf.local"
# Si un domaine secondaire est fourni, préparer son fichier DNS

if [ -n "DNSs" ]; then

   sudo touch "/etc/bind/db.$DNSs.projet.com"

   ajout_dns_second="

;
; BIND data file for local loopback interface
;

\$TTL    604800
@       IN      SOA     $DNSs.projet.com. admininistrator.$DNSs.projet.com. (
                              2         ; Serial
                         604800         ; Refresh
                          86400         ; Retry
                        2419200         ; Expire
                         604800 )       ; Negative Cache TTL
;
@       IN      NS      $DNSs.projet.com.
@       IN      NS      $DNSs.projet.com.
@       IN      A       $IP_AJOUT
ns1     IN      A       $IP_AJOUT

"

fi

sudo touch "/etc/bind/db.$DNS.medmed.com"


Ajout_content_dns_db_file="

;
; BIND data file for local loopback interface
;

\$TTL    604800
@       IN      SOA     $DNS.medmed.com. admininistrator.$DNS.medmed.com. (
                              2         ; Serial
                         604800         ; Refresh
                          86400         ; Retry
                        2419200         ; Expire
                         604800 )       ; Negative Cache TTL
;
@       IN      NS      $DNS.medmed.com.
@	IN	NS	$DNS.medmed.com.
@	IN	A	$IP_AJOUT
ns1     IN      A       $IP_AJOUT

"

if grep -q ";" /etc/bind/named.conf.local ; then

	echo "$Ajout_DNS"  sudo tee -a "/etc/bind/named.conf.local"

fi

echo "$Ajout_content_dns_db_file" | sudo tee /etc/bind/db.$DNS.medmed.com

if [ -n "$DNSs" ]; then

echo "$ajout_dns_second" | sudo tee /etc/bind/db.$DNSs.projet.com

fi


Ajout_content_dns_arp_file="

;
; BIND reverse data file for local loopback interface
;
\$TTL    604800
@       IN      SOA     $DNS.medmed.com. admin.$DNS.medmed.com. (
                              2         ; Serial
                         604800         ; Refresh
                          86400         ; Retry
                        2419200         ; Expire
                         604800 )       ; Negative Cache TTL
;
@            IN      NS      $DNS.medmed.com.
1    	     IN      PTR     $DNS.medmed.com.

"

echo "$Ajout_content_dns_arp_file" | sudo tee /etc/bind/db.192

# Ajuste les permissions sur les fichiers de configuration BIND

sudo chown 777 /etc/bind/*
sudo chown root:root /etc/bind/*

IP_R="10.10.10.1"
PORT_R=2226
CHEMIN="/home/stud/.ssh/id_rsa"

#ajout des dns
sudo ssh -o StrictHostKeyChecking=no -i /home/stud/.ssh/id_rsa stud@10.10.10.2 -p "$PORT_R" "if ! grep -q '$DNS IN A $IP_R' /etc/bind/db.medmed.com.; then echo '$DNS.medmed.com.	IN	A	$IP_R' | sudo tee -a /etc/bind/db.medmed.com ; sudo systemctl restart bind9; fi"
if [ -n "$DNSs" ]; then

sudo ssh -o StrictHostKeyChecking=no -i /home/stud/.ssh/id_rsa stud@10.10.10.2 -p "$PORT_R" "if ! grep -q '$DNSs IN A $IP_R' /etc/bind/db.projet.com.; then echo '$DNSs.projet.com.       IN      A       $IP_R' | sudo tee -a /etc/bind/db.projet.com ; sudo systemctl restart bind9; fi"

fi
# Vérification du succès des commandes et affichage des messages d'erreur si nécessaire

if [ $? -eq 0 ]; then

    echo "SOUS DNS créé sur $IP_R ajouté sur -> $10.10.10.2"
    echo "Sortie du ssh: $output"
else
    echo "Le sous dns créé n'a pas pu etre ajouté sur $10.10.10.2"
    echo "ERREUR SSH: $output"
    exit 1
fi
# Redémarre le service BIND local pour appliquer les modifications

sudo systemctl restart bind9
