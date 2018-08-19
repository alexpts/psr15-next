<?php

use Zend\HttpHandlerRunner\Emitter\EmitterStack;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;
use Zend\HttpHandlerRunner\Emitter\SapiStreamEmitter;

$responseEmitter = new EmitterStack;
$responseEmitter->push(new SapiEmitter);
$responseEmitter->push(new SapiStreamEmitter);

return $responseEmitter;