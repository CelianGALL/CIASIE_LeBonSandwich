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

## Mode production

Pour passer en mode production, retirer le mode debug de slim dans les paramètres du container.

Pour les services internes, retirer l'ouverture des ports vers la machine hôte dans le docker-compose : 

```yml
ports:
      - '19080:80'
      - '19043:443'
```

Cela permet de les masquer au public et les services à l'intérieur de la machine hôte auront toujours accès aux services internes.

Pour déployer sur webetu, faire attention au dns.