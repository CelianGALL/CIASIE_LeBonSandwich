<?php

namespace lbs\backoffice\app\errors;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ApiError
{
	public function __invoke(Request $request, Response $response, \Exception $exception)
	{
		$response = $response->withStatus(500);
		$response = $response->withHeader('Content-Type', 'text/html');
		$response = $response->getBody()->write("Something went wrong !");

		return $response;
	}
}
