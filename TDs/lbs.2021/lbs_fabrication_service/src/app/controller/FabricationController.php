<?php

namespace lbs\fab\app\controller;

use \lbs\fab\app\models\Commande;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class FabricationController
{
	private $container = null;

	public function __construct(\Slim\Container $container)
	{
		$this->container = $container;
	}

	public function listCommands(Request $req, Response $resp, array $args): Response
	{
		/**
		 * Variables pagination
		 */
		$page = $req->getQueryParam("page", 1);
		$size = $req->getQueryParam("size", 10);

		/**
		 * Si le numéro de page demandé est < 0, retourne la 1ère page
		 */
		if ($page < 0) {
			$page = 1;
			$next_page = $page + 1;
			$previous_page = 1;
		} else {
			$next_page = $page + 1;
			$previous_page = $page - 1;
			if ($previous_page < 1) {
				$previous_page = 1;
			}
		}

		/**
		 * Gestion du cas où le numéro de page est supérieur au nombre de pages.
		 * Dans ce cas, la dernière page est retournée.
		 */
		if ($page > (Commande::count() / $size)) {
			$next_page = (ceil(Commande::count() / $size));
			$page = (ceil(Commande::count() / $size) - 1);
		};

		/**
		 * Gestion du status et mise en place de la pagination
		 */
		$status = $req->getQueryParam("s", null);
		if (isset($status)) {
			$commands = Commande::get(["id", "nom", "created_at", "livraison", "status"])->where("status", "=", $status)->skip(($page - 1) * $size)->take($size);
		} else {
			$commands = Commande::get(["id", "nom", "created_at", "livraison", "status"])->skip(($page - 1) * $size)->take($size);
		};

		/**
		 *  Structure et construction de la réponse
		 */
		$res = [
			"type" => "collection",
			"count" => Commande::count(),
			"size" => $size,
			"commands" => [],
			"links" => [
				/**
				 * Retrouver la route associée à son nom
				 * https://www.slimframework.com/docs/v3/objects/router.html#route-names
				 */
				"next" => [
					"href" => $this->container->get('router')->pathFor('commandes') . '?page=' . $next_page . '&size=' . $size
				],
				"prev" => [
					"href" => $this->container->get('router')->pathFor('commandes') . '?page=' . $previous_page . '&size=' . $size
				]

			]
		];
		foreach ($commands as $command) {
			$command_data = [
				"command" => $command,
				"links" => [
					"self" => [
						"href" => $this->container->get('router')->pathFor('commandes') . $command["id"] . '/'
					]
				]
			];
			array_push($res["commands"], $command_data);
		};
		$resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
		$resp->getBody()->write(json_encode($res));
		return $resp;
	}
}
