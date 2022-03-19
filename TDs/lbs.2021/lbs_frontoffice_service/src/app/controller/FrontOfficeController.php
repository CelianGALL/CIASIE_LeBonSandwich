<?php

namespace lbs\frontoffice\app\controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Twig\Loader\FilesystemLoader;
use \Twig\Environment;

class FrontOfficeController
{
	private $container = null;
	private $loader;
	private $twig;
	
	public function __construct(\Slim\Container $container)
	{
		$this->container = $container;
		$this->loader = new FilesystemLoader(__DIR__ . '/../templates');
		$this->twig = new Environment($this->loader, array('debug' => true));
	}

	public function buildCategories(Request $req, Response $resp, array $args): Response
	{
		// Ne fonctionne pas, accès refusé
		$json = file_get_contents("http://api.catalogue.local/items/categories");
		// Fonctionne
		$template = $this->twig->render("index.html.twig", [
			"categories" => $json["data"],
		]);
		$resp = $resp->withHeader('Content-Type', 'text/html;charset=utf-8');
		$resp->getBody()->write($template);
		return $resp;
	}

	public function buildCategoriesItems(Request $req, Response $resp, array $args): Response
	{
		// Ne fonctionne pas, accès refusé
		$json = file_get_contents("http://api.catalogue.local/items/categories");
		// Fonctionne
		$template = $this->twig->render("index.html.twig", [
			"categories" => $json["data"],
		]);
		$resp = $resp->withHeader('Content-Type', 'text/html;charset=utf-8');
		$resp->getBody()->write($template);
		return $resp;
	}
}
