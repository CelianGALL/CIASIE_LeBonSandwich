<?php

/**
 * File:  index.php
 */

require_once  __DIR__ . '/../src/vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \lbs\backoffice\app\controller\BackOfficeController;

$configuration = [
	'settings' => [
		'displayErrorDetails' => true, // Mettre à false pour déployer l'api en mode production
	],
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
	'/auth[/]',
	function (Request $req, Response $resp, $args): Response {
		$ctrl = new BackOfficeController($this);
		return $ctrl->authRedirect($req, $resp, $args);
	}
)->setName('auth');

$app->get(
	'/commands[/]',
	function (Request $req, Response $resp, $args): Response {
		$ctrl = new BackOfficeController($this);
		return $ctrl->commandsRedirect($req, $resp, $args);
	}
)->setName('commands');

$app->run();
