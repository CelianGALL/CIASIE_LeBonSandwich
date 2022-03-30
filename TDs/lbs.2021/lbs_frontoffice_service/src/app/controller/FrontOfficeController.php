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

		// Redirect Response
		$categories = file_get_contents("http://api.catalogue.local:8055/items/categories");

		$json = json_decode($categories, true);
		$template = $this->twig->render("index.html.twig", [
			"categories" => $json["data"],
		]);
		$resp = $resp->withHeader('Content-Type', 'text/html;charset=utf-8');
		$resp->getBody()->write($template);
		return $resp;
	}

	public function buildCategoriesItems(Request $req, Response $resp, array $args): Response
	{
		$cat_id = $req->getQueryParam("id", null);
		if ($cat_id) {
			$sandwiches_ids = file_get_contents("http://api.catalogue.local:8055/items/categories/$cat_id");
			$sandwiches_ids = json_decode($sandwiches_ids, true);
			$sandwiches = [];
			foreach ($sandwiches_ids["data"]["sandwiches"] as $id) {
				$data = file_get_contents('http://api.catalogue.local:8055/items/sandwiches/' . $id);
				$data = json_decode($data)->data;
				// var_dump($data);
				array_push($sandwiches, [
					"libelle" => $data->libelle,
					"description" => $data->description,
					"prix" => $data->prix,
				]);
			};

			$template = $this->twig->render("sandwiches.html.twig", [
				"sandwiches" => $sandwiches,
			]);
			$resp = $resp->withHeader('Content-Type', 'text/html;charset=utf-8');
			$resp->getBody()->write($template);
			return $resp;
		} else {
			$resp = $resp->withHeader('Content-Type', 'text/html;charset=utf-8');
			$resp->getBody()->write("Indiquez l'id d'une catégorie.");
			return $resp;
		}
	}
}
