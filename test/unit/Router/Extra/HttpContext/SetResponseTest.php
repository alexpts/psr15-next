<?php

use PHPUnit\Framework\TestCase;
use PTS\NextRouter\Extra\HttpContext;
use Zend\Diactoros\Response\JsonResponse;

class SetResponseTest extends TestCase
{
    public function testSetResponse(): void
    {
        $context = new HttpContext;
        $response = new JsonResponse(['status' => 'ok']);
        $context->setResponse($response);

        $this->assertInstanceOf(JsonResponse::class, $context->response);
    }
}
