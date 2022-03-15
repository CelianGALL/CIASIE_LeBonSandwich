<?php

namespace lbs\fab\app\controller;

use \lbs\fab\app\models\Commande;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class FabricationController
{
	public function listCommands(Request $req, Response $resp, array $args): Response
	{
		$res = [];
		$res["type"] = "collection";
		$commandes = Commande::get(["id", "nom", "created_at", "livraison", "status"]);
		$res["count"] = count($commandes);
		foreach ($commandes as $commande) {
			array_push($res["commandes"], $commande);
			$res["links"]["self"]["href"] = '/commandes/' . $commande["id"] . '/';
		}

		$resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
		$resp->getBody()->write(json_encode($res));
		return $resp;
	}
}
