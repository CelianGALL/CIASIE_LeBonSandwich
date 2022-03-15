<?php

namespace lbs\command\app\controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Illuminate\Support\Str;

use \lbs\command\app\models\Commande;
use \lbs\command\app\models\Item;

use Respect\Validation\Validator as v;

class CommandController
{
	private $container = null; 
	
	public function __construct(\Slim\Container $container)
	{
		$this->container = $container;
	}

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
		$token = $req->getQueryParam("token", null);
		// Check access token
		if ($token === null) {
			$token = $req->getHeader('token'); // returns an array
			if (isset($token[0])) {
				$token = $token[0];
			} else {
				$token = null;
			}
		}
		if (isset($token)) {
			$embed = $req->getQueryParam("embed", null);
			$res["type"] = "resource";
			$res["commandes"] = Commande::where("id", "like", $args["id"])->get(["id", "mail", "nom", "created_at", "livraison", "montant"]);
			if ($embed === 'items') {
				$items = Item::where("command_id", "like", $args["id"])->get(["id", "libelle", "tarif", "quantite"]);
				$res["commandes"][0]["items"] = $items;
			}
			// Il faut utiliser le générateur d'url de slim pour générer ces routes à partir de leur nom et non en dur
			// https://www.slimframework.com/docs/v3/objects/router.html#route-names
			// 
			// echo $app->getContainer()->get('router')->pathFor('hello', [
			// 	'name' => 'Josh'
			// ]);
			$res["links"]["items"]["href"] = '/commandes/' . $args["id"] . '/items/';
			$res["links"]["self"]["href"] = '/commandes/' . $args["id"];
			if (isset($res["commandes"])) {
				$resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
				$resp->getBody()->write(json_encode($res));
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
		} else {
			$resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
			$resp->getBody()->write(json_encode([
				"type" => "error",
				"error" => "403",
				"message" => "Accès refusé, token incorrect.",
			]));
			return $resp;
		}
	}

	public function listItemsByCommandId(Request $req, Response $resp, array $args): Response
	{
		$items = Item::where("command_id", "like", $args["id"])->get(["id", "libelle", "tarif", "quantite"]);
		if (isset($items)) {
			$resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
			$resp->getBody()->write(json_encode([
				"type" => "collection",
				"count" => count($items),
				"items" => $items,
			]));
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

	public function addCommand(Request $req, Response $resp, array $args): Response
	{

		if ($req->getAttribute('has_errors')) {
			//There are errors, read them
			$errors = $req->getAttribute('errors');

			$resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
			$resp->getBody()->write(json_encode([
				$errors,
			]));
			return $resp;
		} else {
			$command_data = $req->getParsedBody();

			$c = new Commande();
			$c->id = Str::uuid()->toString();
			$c->nom = filter_var($command_data['nom'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			$c->mail = filter_var($command_data['mail'], FILTER_SANITIZE_EMAIL);
			$c->livraison = \DateTime::createFromFormat('Y-m-d H:i', $command_data['livraison']['date'] . ' ' . $command_data['livraison']['heure']);
			$c->status = Commande::CREATED;
			$c->token = bin2hex(random_bytes(32));
			foreach ($command_data["items"] as $item) {
				$i = new Item();
				$i->uri = $item["uri"];
				$i->libelle = $item["libelle"];
				$i->tarif = $item["tarif"];
				$i->quantite = $item["q"];
				$i->command_id = $c->id;
				$c->montant += $item["tarif"] * $item["q"];
				$i->save();
			}
			$c->save();

			$res['commande']['nom'] = $c->nom;
			$res['commande']['mail'] = $c->mail;
			$res['commande']['date_livraison'] = $c->livraison;
			$res['commande']['id'] = $c->id;
			$res['commande']['token'] = $c->token;
			$res['commande']['montant'] = $c->montant;
			$resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
			$resp->getBody()->write(json_encode($res));
			return $resp;
		}
	}
}
