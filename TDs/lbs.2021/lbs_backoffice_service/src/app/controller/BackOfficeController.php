<?php

namespace lbs\backoffice\app\controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \GuzzleHttp\Client;

/**
 * BackOfficeController to handle redirections to api.auth.local
 *
 * @param  container  A slim dependencies container
 */
class BackOfficeController
{
	private $container = null;

	public function __construct(\Slim\Container $container)
	{
		$this->container = $container;
	}

	/**
	 * BackOffice method to redirect to api.auth.local
	 *
	 * @param  Request  A slim request object
	 * @param  Response A slim response object
	 * @param  args An array of arguments
	 * @return Response A slim Response element
	 */
	public function authRedirect(Request $req, Response $resp, array $args): Response
	{
		$client = new Client([
			'timeout'  => 20.0,
		]);

		// Redirect Response
		$redirect_res = $client->request("POST", "http://api.auth.local/auth", [
			'headers' => [
				'Authorization' => $req->getHeader("Authorization")
			],
		]);

		// Initial Request Response
		// Headers
		foreach ($redirect_res->getHeaders() as $name => $values) {
			$resp = $resp->withHeader($name, implode(', ', $values));
		};
		$resp = $resp->withStatus($redirect_res->getStatusCode());
		$resp->getBody()->write($redirect_res->getBody());
		return $resp;
	}


	/**
	 * BackOffice method to redirect to api.auth.local
	 *
	 * @param  Request  A slim request object
	 * @param  Response A slim response object
	 * @param  args An array of arguments
	 * @return Response A slim Response element
	 */
	public function commandsRedirect(Request $req, Response $resp, array $args): Response
	{
		$client = new Client([
			'timeout'  => 20.0,
		]);

		// Check auth
		if (!$req->hasHeader('Authorization')) {
			$resp = $resp->withStatus(401);
			$resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
			$resp->getBody()->write(json_encode([
				"type" => "error",
				"error" => "401",
				"message" => "Accès refusé, header d'authentification manquant.",
			]));
			return $resp;
		};

		try {
			$auth = $client->request("GET", "http://api.auth.local/check", [
				'headers' => [
					'Authorization' => $req->getHeader("Authorization")
				],
			]);
			if (json_decode($auth->getBody())->type === "success") {
				// Redirect Response
				$commands = $client->request("GET", "http://api.commande.local/commandes");
				// Initial Request Response
				// Headers
				foreach ($commands->getHeaders() as $name => $values) {
					$resp = $resp->withHeader($name, implode(', ', $values));
				};
				$resp = $resp->withStatus($commands->getStatusCode());
				$resp->getBody()->write($commands->getBody());
				return $resp;
			} else {
				$error = json_decode($auth->getBody());
				$message = $error->message;
				$code = json_decode($auth->getBody())->error;
				throw new \Exception($message, $code);
			}
		} catch (\Exception $e) {
			$resp = $resp->withStatus($e->getCode());
			$resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
			$resp->getBody()->write(json_encode([
				"type" => "error",
				"error" => $e->getCode(),
				"message" => $e->getMessage(),
			]));
			return $resp;
		}
	}
}
