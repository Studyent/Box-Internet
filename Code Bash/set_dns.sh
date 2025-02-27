#!/bin/bash

# Vérifie si exactement 2 arguments sont fournis

if [ $# -ne 2 ] ; then

    echo "Des arguments sont manquants !"
    exit 1
fi
# Assignation des arguments à des variables

DNS=$1
IP_DEB=$2

# Sauvegarde de l'IFS actuel et changement pour traiter l'adresse IP

ANCIEN_IFS=$IFS

# Décomposition de l'adresse IP initiale en octets

IFS="." read -r octet1 octet2 octect3 octet4 <<< "$IP_DEB";
# Adresse IP initiale à utiliser pour les ajouts

IP_AJOUT=$IP_DEB;

# Contenu à ajouter pour la configuration DNS dans named.conf.local


Ajout_DNS="
zone \"$DNS.com\" {
    type master;
    file \"/etc/bind/db.$DNS.com\";
};

"
# Contenu à ajouter pour la configuration ARP dans named.conf.local

Ajout_ARP="
zone \"1.168.192.in-addr.arpa\"{
    type master;
    file \"/etc/bind/db.192\";
};

"
# Sauvegarde des fichiers de configuration existants

sudo cp /etc/bind/named.conf.local /etc/bind/named.conf.local.bak
sudo cp /etc/bind/db.192 /etc/bind/db.192.bak

# Ajout de la configuration DNS dans named.conf.local

echo "$Ajout_DNS" | sudo tee -a "/etc/bind/named.conf.local"
#echo "$Ajout_ARP" | sudo tee -a "/etc/bind/named.conf.local"
sudo touch "/etc/bind/db.$DNS.com"
# Contenu à ajouter dans le fichier de base DNS

Ajout_content_dns_db_file="
;
; BIND data file for local loopback interface
;

\$TTL    604800
@       IN      SOA     ns1.$DNS.com. admininistrator.$DNS.com. (
                              2         ; Serial
                         604800         ; Refresh
                          86400         ; Retry
                        2419200         ; Expire
                         604800 )       ; Negative Cache TTL
;
@       IN      NS      ns1.$DNS.com.
@	IN	NS	$DNS.com.
@	IN	A	192.168.1.1
ns1     IN      A       192.168.1.1

"
# Ajout du contenu DNS dans le fichier approprié
#grep -q pour une exécution silencieuse
if grep -q ";" /etc/bind/named.conf.local ; then

	echo "$Ajout_DNS"  sudo tee -a "/etc/bind/named.conf.local"

fi

echo "$Ajout_content_dns_db_file" | sudo tee /etc/bind/db.$DNS.com


Ajout_content_dns_arp_file="

;
; BIND reverse data file for local loopback interface
;
\$TTL    604800
@       IN      SOA     ns1.$DNS.com. admin.$DNS.com. (
                              2         ; Serial
                         604800         ; Refresh
                          86400         ; Retry
                        2419200         ; Expire
                         604800 )       ; Negative Cache TTL
;
@            IN      NS      ns1.$DNS.com.
1    	     IN      PTR     ns1.$DNS.com.

"

echo "$Ajout_content_dns_arp_file" | sudo tee /etc/bind/db.192

#restauration de l'ancien ifs
IFS=$ANCIEN_IFS
#ajout des permissions
sudo chown 777 /etc/bind/*
sudo chown root:root /etc/bind/*
#redémarrage de bind pour prendre les modifications en compte
sudo systemctl restart bind9
