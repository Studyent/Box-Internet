#!/bin/bash


# Vérifie le nombre d'arguments fournis

if [ $# -eq 1 ]; then

    PLAGE=$1

elif [ $# -eq 2 ]; then
    # Si deux arguments sont fournis, ils représentent les octets de début et de fin de la plage


    OC_DEB=$1
    OC_FIN=$2
else
    echo "pas assez d'arguments ! "
    exit 1

fi
# Obtient l'adresse IP de la machine sur l'interface eth1

IP_MACHINE=$(ip addr show eth1 | grep 'inet' | cut -d ' ' -f6 | cut -d / -f1 | head -n 1)


# Sauvegarde de l'IFS (Internal Field Separator) actuel

ANCIEN_IFS=$IFS
# Divise l'adresse IP de la machine en ses composantes octet par octet

IFS="." read -r octet1 octet2 octet3 octet4 <<< "$IP_MACHINE";
# Configuration du masque de réseau et du sous-réseau

NETMASK="255.255.255.0"
SUBNET="$octet1.$octet2.$octet3.0"

IFS=$ANCIEN_IFS
# Détermine les adresses de début et de fin de la plage IP

if [ -n "$PLAGE" ]; then
	IP_DEB="$IP_MACHINE"
	fin=$((octet4 + PLAGE))
        IP_FIN="$octet1.$octet2.$octet3.$fin"

	
else
	
	IP_DEB="$octet1.$octet2.$octet3.$OC_DEB"
        IP_FIN="$octet1.$octet2.$octet3.$OC_FIN"


fi

# Met à jour la configuration du serveur DHCP avec le sous-réseau et la plage d'adresses

sudo sed -i "0,/subnet .* netmask .*/s//subnet $SUBNET netmask $NETMASK;/" /etc/dhcp/dhcpd.conf
sudo sed -i "0,/range .* .*;/s//range $IP_DEB $IP_FIN;/" /etc/dhcp/dhcpd.conf
# Redémarre le service DHCP pour appliquer les modifications

sudo systemctl restart isc-dhcp-server

