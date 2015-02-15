<?php
require_once __DIR__.'/vendor/autoload.php';

use Graceland\SafeInCloud\ApiClient;
use Graceland\SafeInCloud\Encrypter;
use Graceland\SafeInCloud\MessageFactory;
use GuzzleHttp\Client;

$client = ApiClient::create();

if ($client->authenticate('password') === false) {
    die('Wrong password.');
}

var_dump($client->getLogins());
