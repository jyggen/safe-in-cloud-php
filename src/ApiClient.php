<?php
namespace Graceland\SafeInCloud;

use GuzzleHttp\Client;
use RandomLib\Generator;

class ApiClient
{
    const NEVER_EXPIRES = -1;
    const ONE_TIME      = -2;
    const HOUR          = 3600;
    const LOCALHOST_URL = 'http://localhost:19756/';

    protected $client;
    protected $encrypter;

    public function __construct(Client $client, Encrypter $encrypter)
    {
        $this->client    = $client;
        $this->encrypter = $encrypter;
    }

    public function shakeHands()
    {
        $nonce    = $this->encrypter->generateIv();
        $payload  = [
            'type'     => 'handshake',
            'key'      => base64_encode($this->encrypter->getKey()),
            'nonce'    => base64_encode($nonce),
            'verifier' => base64_encode($this->encrypter->encrypt($nonce, $nonce)),
        ];

        $response = $this->client->post(static::LOCALHOST_URL, [
            'body'    => json_encode($payload),
            'headers' => [
                'content-type' => 'application/json',
            ],
        ]);

        return $response->json();
    }
}
