<?php
require_once __DIR__.'/vendor/autoload.php';

$guzzle    = new GuzzleHttp\Client;
$factory   = new RandomLib\Factory;
$generator = $factory->getMediumStrengthGenerator();
$client    = new Graceland\SafeInCloud\ApiClient($guzzle, $generator);

$client->shakeHands();
