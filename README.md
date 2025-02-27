# Projet AMS Réseau : Box Internet et Services

## Description du Projet

Ce projet, réalisé dans le cadre du semestre 5 de licence informatique à l’Université d’Avignon, a pour objectif la conception d’une box internet virtuelle. Cette dernière propose plusieurs services réseaux accessibles via une interface web ergonomique :

- DHCP (Dynamic Host Configuration Protocol)
- DNS (Domain Name System)
- Mesure de débit (via FTP)

L’idée est de permettre à un utilisateur d’interagir avec ces services comme il le ferait avec une box fournie par un **FAI (Fournisseur d’Accès Internet)**. L’accent a été mis sur l’ergonomie, en s’inspirant des interfaces existantes tout en optimisant l’expérience utilisateur.

---

## Architecture & Technologies

### Infrastructure Réseau
L’infrastructure repose sur Oracle VirtualBox et comprend trois machines virtuelles :
1. Client : Machine utilisateur accédant aux services.
2. Box : Serveur principal hébergeant les services.
3. FAI : Simule un fournisseur d’accès internet.

Configuration réseau :
- Client ↔ Box : Réseau interne (Interface eth1)
- Box ↔ FAI : Réseau bridge (Interface eth2)
- FAI ↔ Internet : Connexion externe

### Technologies Utilisées
| Composant       | Technologies  |
|----------------|--------------|
| Frontend   | HTML5, CSS3, SCSS, JavaScript |
| Backend   | PHP, Bash     |
| Services   | ISC DHCP, BIND9, Apache2, FTP |
| Système    | Ubuntu 20.04+ (via VirtualBox) |
| Graphiques | Gnuplot (pour la mesure de débit) |

---

## Installation & Déploiement

###  1. Prérequis
Avant de commencer, assurez-vous d’avoir :
- Oracle VirtualBox installé
- Une image OVA d’Ubuntu (remplir la version exacte)
- Accès root pour l’installation des paquets

### 2. Installation des Services
Sur la Box, exécutez :
```sh
sudo apt update && sudo apt upgrade -y
sudo apt install isc-dhcp-server bind9 apache2 vsftpd -y

Sur le FAI :

sudo apt update && sudo apt upgrade -y
sudo apt install apache2 bind9 vsftpd -y

 3. Configuration des Services
 Configuration du DHCP

Éditez le fichier de configuration DHCP :

sudo nano /etc/dhcp/dhcpd.conf

Ajoutez :

subnet 192.168.1.0 netmask 255.255.255.0 {
  range 192.168.1.10 192.168.1.100;
  option routers 192.168.1.1;
  option domain-name-servers 8.8.8.8;
}

Redémarrez le service :

sudo systemctl restart isc-dhcp-server

➤ Configuration du DNS

Ajoutez une zone DNS dans :

sudo nano /etc/bind/named.conf.local

Ajoutez :

zone "medmed.com" {
    type master;
    file "/etc/bind/db.medmed";
};

Redémarrez BIND :

sudo systemctl restart bind9

 Fonctionnalités & Utilisation
 Gestion DHCP

    Mode Facile : L’utilisateur entre un nombre d’appareils, la plage IP est générée automatiquement.
    Mode Expert : L’utilisateur définit manuellement la plage d’adresses.

 Gestion DNS

    Création d’un sous-DNS (service.medmed.com)
    Création d’une zone d’autorité (zone.medmed.com)
    Test et validation de la configuration via l’interface.

 Mesure de Débit

    Test du débit ascendant et descendant via FTP
    Affichage sous forme de graphique interactif
    Historique des tests pour comparaison

 Fichiers Importants
Fichier	Rôle
ajout.php	Gestion des ajouts de services
dhcp.php	Interface DHCP
dns.php	Interface DNS
menu.php	Menu de navigation
mesuredeb.php	Mesure du débit
ledns.sh	Script Bash pour la gestion DNS
 Problèmes et Solutions
 1. VirtualBox & Connexion Réseau

 Problème : Machines ne communiquaient pas correctement.
 Solution : Création manuelle de routes et ajustement des adaptateurs réseau.
 2. Erreurs avec www-data (Apache)

 Problème : Apache n’avait pas les permissions pour exécuter les scripts.
 Solution : Ajout de www-data aux utilisateurs sudo et configuration des droits.
 3. Erreurs Bind9 & DNS

 Problème : Les résolutions de nom ne fonctionnaient pas.
 Solution : Ajout de nameserver 10.10.10.2 dans /etc/resolv.conf et correction de la syntaxe.
 4. Mesure de Débit

 Problème : Impossible de récupérer les valeurs de débit en PHP.
 Solution : Passage à Bash et utilisation de Gnuplot pour la génération des graphiques.
 Commandes Utiles

Quelques commandes pour tester et gérer les services :
Test DHCP

Depuis le client :

dhclient -v

Afficher la configuration DHCP :

cat /var/lib/dhcp/dhclient.leases

Test DNS

Depuis la Box :

dig service.medmed.com

Depuis un Client :

nslookup service.medmed.com 10.10.10.1

Test Mesure de Débit

Lancer un test FTP :

wget ftp://10.10.10.2/testfile

Conclusion

Ce projet est une première étape vers une infrastructure réseau plus complète. Il a permis d’explorer plusieurs technologies et d’acquérir une expertise avancée en administration réseau, développement web et automatisation via Bash.


Projet réalisé par Lefki Meidi Thomas
Encadré par Rachid Elazouzi & Mohamed Morchid
Université d’Avignon - Année 2024-2025

