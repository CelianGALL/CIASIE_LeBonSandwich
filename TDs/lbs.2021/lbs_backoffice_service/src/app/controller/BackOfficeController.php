<?php

namespace lbs\backoffice\app\controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class BackOfficeController
{
	private $container = null;

	public function __construct(\Slim\Container $container)
	{
		$this->container = $container;
	}

	public function authRedirect(Request $req, Response $resp, array $args): Response
	{
		$resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
		$resp->getBody()->write("authRedirect");
		return $resp;
	}

	public function commandsRedirect(Request $req, Response $resp, array $args): Response
	{
		$resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
		$resp->getBody()->write("commandsRedirect");
		return $resp;
	}
}
