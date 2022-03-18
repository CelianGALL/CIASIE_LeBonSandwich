<?php

namespace lbs\auth\api\controller;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use lbs\auth\api\models\User;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


/**
 * Class LBSAuthController
 * @package lbs\command\api\controller
 */
class LBSAuthController
{
	private $container = null;

	public function __construct(\Slim\Container $container)
	{
		$this->container = $container;
	}

	public function authenticate(Request $rq, Response $rs, $args): Response
	{

		if (!$rq->hasHeader('Authorization')) {
			$rs = $rs->withHeader('WWW-authenticate', 'Basic realm="commande_api api"');
			$rs = $rs->withAddedHeader('Content-Type', 'application/json;charset=utf-8');
			$rs->getBody()->write(json_encode([
				"type" => "error",
				"error" => "401",
				"message" => "Accès refusé, header d'authentification manquant.",
			]));
			return $rs;
		};

		$authstring = base64_decode(explode(" ", $rq->getHeader('Authorization')[0])[1]);
		list($email, $pass) = explode(':', $authstring);

		try {
			$user = User::select('id', 'email', 'username', 'passwd', 'refresh_token', 'level')->where('email', '=', $email)->firstOrFail();
			if (!password_verify($pass, $user->passwd)) {
				throw new \Exception("password check failed");
			};

			unset($user->passwd);
		} catch (ModelNotFoundException $e) {
			$rs = $rs->withHeader('WWW-authenticate', 'Basic realm="lbs auth"');
			$rs = $rs->withAddedHeader('Content-Type', 'application/json;charset=utf-8');
			$rs->getBody()->write(json_encode([
				"type" => "error",
				"error" => "401",
				"message" => "Erreur d'authentification",
			]));
			return $rs;
		} catch (\Exception $e) {
			$rs->getBody()->write(json_encode([
				"type" => "error",
				"error" => "401",
				"message" => "Erreur d'authentification",
			]));
			return $rs;
		}

		$secret = $this->container->settings['secret'];
		$token = JWT::encode(
			[
				'iss' => 'http://api.auth.local/auth',
				'aud' => 'http://api.backoffice.local',
				'iat' => time(),
				'exp' => time() + (12 * 30 * 24 * 3600),
				'uid' => $user->id,
				'lvl' => $user->level,
				'upr' => [
					'email' => $user->email,
					'username' => $user->username,
					'level' => $user->level
				]
			],
			$secret,
			'HS512'
		);

		$user->refresh_token = bin2hex(random_bytes(32));
		$user->save();
		$data = [
			'access-token' => $token,
			'refresh-token' => $user->refresh_token
		];

		$rs = $rs->withHeader('Content-Type', 'application/json;charset=utf-8');
		$rs->getBody()->write(json_encode([
			"type" => "success",
			"code" => "200",
			"message" => $data,
		]));
		return $rs;
	}

	public function checkCredentials(Request $rq, Response $rs, $args): Response
	{
		if (!$rq->hasHeader('Authorization')) {
			$rs = $rs->withHeader('WWW-authenticate', 'Basic realm="commande_api api"');
			$rs = $rs->withAddedHeader('Content-Type', 'application/json;charset=utf-8');
			$rs->getBody()->write(json_encode([
				"type" => "error",
				"error" => "401",
				"message" => "Accès refusé, header d'authentification manquant.",
			]));
			return $rs;
		};

		try {
			$jwt = explode(" ", $rq->getHeader('Authorization')[0])[1];
			$secret = $this->container->settings['secret'];
			$token = JWT::decode($jwt, new Key($secret, 'HS512'));

			$rs = $rs->withHeader('Content-Type', 'application/json;charset=utf-8');
			$rs->getBody()->write(json_encode([
				"type" => "success",
				"code" => "200",
				"message" => $token->upr
			]));
			return $rs;
		} catch (ExpiredException $e) {
			$rs = $rs->withHeader('WWW-authenticate', 'Basic realm="commande_api api"');
			$rs = $rs->withAddedHeader('Content-Type', 'application/json;charset=utf-8');
			$rs->getBody()->write(json_encode([
				"type" => "error",
				"error" => "401",
				"message" => "Accès refusé, token expiré.",
			]));
			return $rs;
		} catch (SignatureInvalidException $e) {
			$rs = $rs->withHeader('WWW-authenticate', 'Basic realm="commande_api api"');
			$rs = $rs->withAddedHeader('Content-Type', 'application/json;charset=utf-8');
			$rs->getBody()->write(json_encode([
				"type" => "error",
				"error" => "401",
				"message" => "Accès refusé, token invalide.",
			]));
			return $rs;
		} catch (BeforeValidException $e) {
			$rs = $rs->withHeader('WWW-authenticate', 'Basic realm="commande_api api"');
			$rs = $rs->withAddedHeader('Content-Type', 'application/json;charset=utf-8');
			$rs->getBody()->write(json_encode([
				"type" => "error",
				"error" => "401",
				"message" => "Accès refusé, token invalide.",
			]));
			return $rs;
		} catch (\UnexpectedValueException $e) {
			$rs = $rs->withHeader('WWW-authenticate', 'Basic realm="commande_api api"');
			$rs = $rs->withAddedHeader('Content-Type', 'application/json;charset=utf-8');
			$rs->getBody()->write(json_encode([
				"type" => "error",
				"error" => "401",
				"message" => "Accès refusé, token invalide.",
			]));
			return $rs;
		}
	}
}
