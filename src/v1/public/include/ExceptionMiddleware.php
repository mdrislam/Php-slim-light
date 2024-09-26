<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Psr7\Response;

class ExceptionMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        try {
            // Try to handle the request
            return $handler->handle($request);
        } catch (\Exception $e) {
            // Handle the exception
            $response = new Response();
            $response->getBody()->write('Something went wrong!');
            return $response->withStatus(500);
        }
    }
}

$app->add(new ExceptionMiddleware());
