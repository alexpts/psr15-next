<?php
namespace PTS\NextRouter\Controller;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserController
{
    public function get(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        return new JsonResponse(['action' => 'get']);
    }

    public function index(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        return new JsonResponse(['action' => 'main']);
    }
}