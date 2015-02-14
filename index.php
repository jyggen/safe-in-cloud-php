<?php
require_once __DIR__.'/vendor/autoload.php';

use Graceland\SafeInCloud\ApiClient;
use Graceland\SafeInCloud\Encrypter;
use GuzzleHttp\Client;

$guzzle    = new Client;
$key       = Encrypter::generateKey();
$encrypter = new Encrypter($key);
$client    = new ApiClient($guzzle, $encrypter);

var_dump($key, $client->shakeHands());
