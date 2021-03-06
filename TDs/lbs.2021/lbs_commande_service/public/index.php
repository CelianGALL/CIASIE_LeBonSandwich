<?php

/**
 * File:  index.php
 */

require_once  __DIR__ . '/../src/vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \lbs\command\app\controller\CommandController;

use Respect\Validation\Validator as v;

$configuration = [
	'settings' => [
		'displayErrorDetails' => true, // Mettre à false pour déployer l'api en mode production
	],
	'dbconf' => function ($c) {
		return parse_ini_file(__DIR__ . '/../src/app/config/commande.db.conf.ini');
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
	'/commandes[/]',
	function (Request $req, Response $resp, $args): Response {
		$ctrl = new CommandController($this);
		return $ctrl->listCommands($req, $resp, $args);
	}
)
->setName('commandes');

$app->get(
	'/commandes/{id}[/]',
	function (Request $req, Response $resp, $args): Response {
		$ctrl = new CommandController($this);
		return $ctrl->listCommandsById($req, $resp, $args);
	}
)->setName('commandes_id');

$app->get(
	'/commandes/{id}/items[/]',
	function (Request $req, Response $resp, $args): Response {
		$ctrl = new CommandController($this);
		return $ctrl->listItemsByCommandId($req, $resp, $args);
	}
)->setName('commandes_id_items');



// Create the addCommandValidator
$nomValidator = v::stringType()->alpha();
$mailValidator = v::email();
$livraisonDateValidator = v::date('Y-m-d')->min('now');
$livraisonHeureValidator = v::date('H:i');
$itemsValidator = v::arrayType();
$addCommandValidator = array(
  'nom' => $nomValidator,
  'mail' => $mailValidator,
  'livraison' => array(
    'date' => $livraisonDateValidator,
	'heure' => $livraisonHeureValidator,
  ),
  'items' => $itemsValidator
);
$app->post(
	'/commandes[/]',
	function (Request $req, Response $resp, $args): Response {
		$ctrl = new CommandController($this);
		return $ctrl->addCommand($req, $resp, $args);
	}
)->setName('add_command')->add(new \DavidePastore\Slim\Validation\Validation($addCommandValidator));

$app->run();