<?php

/**
 * File:  index.php
 *
 * 
 * IL ME RESTE LA DERNIERE QUESTION DU TD4 A FAIRE
 * 
 * IL FAUT UTILISER LE GENERATEUR D'URL DE SLIM POUR DONNER UN NOM AUX ROUTES ET LES APPELER DANS LE CONTROLEUR PAR LEUR NOM
 * PRATIQUE POUR NE PAS AVOIR A CHANGER TOUTES LES ROUTES DANS LE CONTROLEUR ET DANS L INDEX EN CAS DE CHANGEMENT
 * 
 * 
 */

require_once  __DIR__ . '/../src/vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \lbs\command\app\controller\TD1CommandController;

$configuration = [
	'settings' => [
		'displayErrorDetails' => true, // Mettre à false pour déployer l'api en mode production
	],
	'dbconf' => function ($c) {
		return parse_ini_file(__DIR__ . '/../src/app/conf/commande.db.conf.ini');
	},
	"notFoundHandler" => function ($c) {
		return function ($req, $resp) {
			$resp = $resp->withStatus(404);
			$resp->getBody()->write(json_encode(
				[
					"type" => "error",
					"error" => "404",
					"message" => "Ressource non disponible"
				]
			));
			return $resp;
		};
	},
	"notAllowedHandler" => function ($c) {
		return function ($req, $resp, $methods) {
			$resp = $resp->withStatus(405);
			$resp->getBody()->write(json_encode(
				[
					"type" => "error",
					"error" => "405",
					"message" => 'Methode autorisee : ' . implode(",", $methods),
				]
			));
			return $resp;
		};
	},
];

$c = new \Slim\Container($configuration);
$app = new \Slim\App($c);

$db = new Illuminate\Database\Capsule\Manager();

$db->addConnection($c->dbconf); /* configuration avec nos paramètres */
$db->setAsGlobal(); /* rendre la connexion visible dans tout le projet */
$db->bootEloquent(); /* établir la connexion */

$app->get(
	'/commandes',
	function (Request $req, Response $resp, $args): Response {
		$ctrl = new TD1CommandController($this);
		return $ctrl->listCommands($req, $resp, $args);
	}
)->setName('commandes'); // Ca c'est le nommage des routes dont je parle au début en tant que "générateur d'url de slim"

$app->get(
	'/commandes/{id}',
	function (Request $req, Response $resp, $args): Response {
		$ctrl = new TD1CommandController($this);
		return $ctrl->listCommandsById($req, $resp, $args);
	}
)->setName('commandes_id');

$app->get(
	'/commandes/{id}/items',
	function (Request $req, Response $resp, $args): Response {
		$ctrl = new TD1CommandController($this);
		return $ctrl->listItemsByCommandId($req, $resp, $args);
	}
)->setName('commandes_id_items');

$app->run();
