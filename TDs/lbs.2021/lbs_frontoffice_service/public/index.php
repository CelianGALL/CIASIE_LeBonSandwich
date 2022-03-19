<?php

/**
 * File:  index.php
 */

require_once  __DIR__ . '/../src/vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \lbs\frontoffice\app\controller\FrontOfficeController;

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

$app->get(
	'/categories[/]',
	function (Request $req, Response $resp, $args): Response {
		$ctrl = new FrontOfficeController($this);
		return $ctrl->buildCategories($req, $resp, $args);
	}
)->setName('categories');

$app->get(
	'/categories/{libelle}',
	function (Request $req, Response $resp, $args): Response {
		$ctrl = new FrontOfficeController($this);
		return $ctrl->buildCategoriesItems($req, $resp, $args);
	}
)->setName('categorieItems');

$app->run();
