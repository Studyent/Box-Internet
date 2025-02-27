#!/bin/bash

if [ "$#" -ne 2 ]; then

    echo "2 arguments sont demandés pour le changement d'ip"
    exit 1

fi

ip_box="192.168.1.1"
ip_client="192.168.1.2"
ip_change=$1
machine=$2



if [ "$machine" == "CLIENT" ]; then

    ssh_user="stud"
    rem_ip=$ip_client
    port_c=2222

elif [ "$machine" == "BOX" ]; then
    ssh_user="BOX"
    rem_ip=$ip_box
    port_b=2223
else
    echo "Machine inconnue: $machine"
    exit 1
fi

output=$(ssh -o StrictHostKeyChecking=no -i /var/www/.ssh/id_rsa $ssh_user@localhost -p $port_c "sudo ip link show" 2>&1)

if [ $? -eq 0 ]; then

    echo "IP changé sur $machine -> $rem_ip"
    echo "Sortie du ssh: $output"
else
    echo "IP inchangé sur $machine"
    echo "ERREUR SSH: $output"
    exit 1
fi