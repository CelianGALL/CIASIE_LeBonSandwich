<?php

namespace lbs\command\app\controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \lbs\command\app\models\Commande;
use \lbs\command\app\models\Item;

class TD1CommandController
{
	public function listCommands(Request $req, Response $resp, array $args): Response
	{
		$res = [];
		$res["type"] = "collection";
		$commandes = Commande::get(["id", "mail", "created_at", "montant"]);
		$res["count"] = count($commandes);
		$res["commandes"] = $commandes;

		$resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
		$resp->getBody()->write(json_encode($res));
		return $resp;
	}

	public function listCommandsById(Request $req, Response $resp, array $args): Response
	{
		$res = [];
		$res["type"] = "resource";
		$res["commandes"] = Commande::where("id", "like", $args["id"])->get(["id", "mail", "nom", "created_at", "livraison", "montant"]);
		// 
		// 
		// Il faut utiliser le générateur d'url de slim pour générer ces routes à partir de leur nom et non en dur
		// 
		// https://www.slimframework.com/docs/v3/objects/router.html#route-names
		// 
		// echo $app->getContainer()->get('router')->pathFor('hello', [
		// 	'name' => 'Josh'
		// ]);
		// 
		$res["links"]["items"]["href"] = '/commandes/' . $args["id"] . '/items/';
		$res["links"]["self"]["href"] = '/commandes/' . $args["id"];

		if (isset($res["commandes"])) {
			var_dump($args);
			// 
			// 
			// Il faut utiliser cette méthode pour récupérer ce qui se trouve après le ? (fin du td4 dernière question il ne me manque plus que celle la)
			// 					II
			// 					v
			// $req->getQueryParam("embed", $default = null);
			if (isset($args["embed"])) {
				foreach ($res["commandes"] as $commande) {
					$commande["items"] = $commande->items()->get();
				}
			};
			$resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
			$resp->getBody()->write(json_encode($res));
			return $resp;
		} else {
			/* 
			Il faut générer une erreur ici car c'est une erreur que le contrôleur trouve.
			Ce n'est pas une erreur handlable dans le container de dépendances de Slim.
			*/
			$resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
			$resp->getBody()->write(json_encode([
				"type" => "error",
				"error" => "404",
				"message" => 'Ressource non disponible (id non existant) : ' . $args["id"],
			]));
			return $resp;
		}
	}

	public function listItemsByCommandId(Request $req, Response $resp, array $args): Response
	{
		$items = Item::where("command_id", "like", $args["id"])->get(["id", "libelle", "tarif", "quantite"]);
		if (isset($items)) {
			$resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
			$resp->getBody()->write(json_encode($items));
			return $resp;
		} else {
			$resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
			$resp->getBody()->write(json_encode([
				"type" => "error",
				"error" => "404",
				"message" => 'Ressource non disponible (id non existant) : ' . $args["id"],
			]));
			return $resp;
		}
	}
}
