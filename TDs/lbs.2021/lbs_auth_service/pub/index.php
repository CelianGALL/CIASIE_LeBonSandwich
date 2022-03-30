<?php

/**
 * File:  index.php
 */

require_once  __DIR__ . '/../src/vendor/autoload.php';

use lbs\auth\api\controller\LBSAuthController;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$configuration = [
	'settings' => [
		'displayErrorDetails' => true, // Mettre à false pour déployer l'api en mode production
		'secret' => base64_encode('mysecret')
	],
	'dbconf' => function ($c) {
		return parse_ini_file(__DIR__ . '/../src/api/config/auth.db.conf.ini');
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


/**
 * @api {post} auth[/] Request an authentification token
 * @apiName Auth
 * @apiVersion 1.0.0
 * @apiGroup Authentication
 * 
 * @apiDescription The authentication route that works with JWT and Basic HTTP
 * @apiHeader {json} Header { "Authorization": "Basic your_token" }
 *
 * @apiSuccessExample Success-Response :
 * {
 *   "type": "success",
 *   "code": "200",
 *   "message": {
 *      "access-token": "your_access_token",
 *      "refresh-token": "your_refresh_token"
 *   }
 * }
 * 
 * @apiError {json} ErreurAuthentification Wrong credentials.
 * @apiErrorExample {json} Error-Response:
 *     HTTP/1.1 401 Unauthorized
 *     {
 *       "type": "error"
 *       "error": "401"
 *       "message": "Erreur d'authentification"
 *     }
 */
$app->post(
	'/auth[/]',
	function (Request $req, Response $resp, $args): Response {
		$ctrl = new LBSAuthController($this);
		return $ctrl->authenticate($req, $resp, $args);
	}
)->setName('auth');

/**
 * @api {get} check[/] Check your authentication token
 * @apiName AuthCheck
 * @apiVersion 1.0.0
 * @apiGroup Authentication
 * 
 * @apiDescription The authentication route that allows you to check your JWT token obtained from /auth/ route.
 * @apiHeader {json} Header { "Authorization": "Basic your_token_from_the_auth_route" }
 *
 * @apiSuccessExample Success-Response :
 * {
 *   "type": "success",
 *   "code": "200",
 *   "message": {
 *      "email": "your_email",
 *      "username": "your_username",
 *      "level": "your_access_level"
 *   }
 * }
 * 
 * @apiError {json} AccessDenied Authorization header missing.
 * @apiErrorExample {json} Error-Response:
 *     HTTP/1.1 401 Unauthorized
 *     {
 *       "type": "error"
 *       "error": "401"
 *       "message": "Accès refusé, header d'authentification manquant."
 *     }
 */
$app->get(
	'/check[/]',
	function (Request $req, Response $resp, $args): Response {
		$ctrl = new LBSAuthController($this);
		return $ctrl->checkCredentials($req, $resp, $args);
	}
)
	->setName('check');

$app->run();
