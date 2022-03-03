# Micro-framework SLIM

## Erreurs fréquentes

### Ecriture de corps de requête

```php
function ( $rq, Response $rs, $args) {
	$rs = $rs->withStatus(201);
	$rs->getBody()->write(json_encode($o)) ;
	return $rs ;  
}
```

> ⚠️ Le flag retourné par $rs->getBody()->write() est en fait un flag qui dit si l'écriture s'est bien passée.

Comment reproduire l'erreur :

```php
/* Retourne donc true ou false au lieu du résultat de l'écriture du body comme on s'y attendrait */

function ( $rq, Response $rs, $args) {
	$rs = $rs->withStatus(201) ;
	$rs = $rs->getBody()->write(json_encode($o)) ;
	return $rs ; 
}
```

### Aucun message d'erreur retourné

Il faut définir displayErrorDetails à true (false par défaut) dans un fichier config ou en début de main. On doit la définir à false en mode développement.

```php
$configuration = [
    'settings' => ['displayErrorDetails' => true,
	'dbconf' => '/conf/db.conf.ini' ]];

$c = new \Slim\Container($configuration);
$app = new \Slim\App($c);

// récupérer le conteneur :
$container = $app->getContainer();
$prod = $container['settings']['displayErrorDetails'] ; 
```

### Accéder au container

On peut accéder au container dans les routes via $this, ce n'est pas possible en dehors : 

```php
$configuration = [
	'settings' => [
		'displayErrorDetails' => true, // Mettre à false pour déployer l'api en mode production
		'dbconf' => function ($c) {
			return parse_ini_file(__DIR__ . '/../src/app/conf/commande.db.conf.ini');
		}
	]
];

$c = new \Slim\Container($configuration);
$app = new \Slim\App($c);

$this["settings"]["dbconf"] // RIEN IMPOSSIBLE

$app->get(
	'/commandes/',
	function (Request $req, Response $resp, $args): Response {
		$this // container
		$ctrl = new TD1CommandController($this);
		return $ctrl->listCommands($req, $resp, $args);
	}
);
```

### Appel de fonctions dans le container de dépendances

Le container de dépendances appelle les fonctions au moment où on demande la clé associée.

> ⚠️ Les fonctions dans la config du container qui sont en deuxième niveau ne sont pas éxecutées !

```php
$configuration = [
	'settings' => [
		'displayErrorDetails' => true, // Mettre à false pour déployer l'api en mode production
		/* Pas Appelée
		'dbconf' => function ($c) {
		return parse_ini_file(__DIR__ . '/../src/app/conf/commande.db.conf.ini');
		}
		*/
	],
	/* Appelée
		'dbconf' => function ($c) {
		return parse_ini_file(__DIR__ . '/../src/app/conf/commande.db.conf.ini');
		}
	*/
];

$db = new Illuminate\Database\Capsule\Manager();
$db->addConnection($c->dbconf); /* configuration avec nos paramètres */
[...]
```# CIASIE_LeBonSandwich
