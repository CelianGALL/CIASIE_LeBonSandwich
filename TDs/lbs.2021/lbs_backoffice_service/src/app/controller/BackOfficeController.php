<?php

namespace lbs\backoffice\app\controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \GuzzleHttp\Client;

class BackOfficeController
{
	private $container = null;

	public function __construct(\Slim\Container $container)
	{
		$this->container = $container;
	}

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
		// Headers
		foreach ($redirect_res->getHeaders() as $name => $values) {
			$resp = $resp->withHeader($name, implode(', ', $values));
		};

		// Initial Request Response
		$resp = $resp->withStatus($redirect_res->getStatusCode());
		$resp->getBody()->write($redirect_res->getBody());
		return $resp;
	}

	public function commandsRedirect(Request $req, Response $resp, array $args): Response
	{
		$resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
		$resp->getBody()->write("commandsRedirect");
		return $resp;
	}
}
