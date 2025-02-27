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
