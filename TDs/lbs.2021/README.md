# Docker-compose

## Accéder au service

http://api.commande.local:19080/index.php

> Modifier le fichier hosts de windows pour faire correspondre 127.0.0.1 => api.commande.local

## Fonctionnement

### Accéder au réseau

Les fichiers accessibles depuis le service sont dans 'public'.

Le fichier docker-compose crée un vhost au lancement : 

```yml
environment:
      - VHOST_HOSTNAME=api.commande.local
      - VHOST_DOCROOT=/var/www/public
ports:
	- '19080:80'
	- '19043:443'
```

### Accéder au adminer

> **Serveur** : mysql.commande  
> **Utilisateur** : root  
> **Mot de passe** : comroot  
> **Base de données** : command_lbs  

## dist

Les fichiers .dist sont utilisés pour mettre sur github un squelette du fichier (ne comportant pas les vraies données d'identification à la BDD par exemple).