<?php
namespace Graceland\SafeInCloud;

use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use RandomLib\Generator;

class ApiClient
{
    const NEVER_EXPIRES = -1;
    const ONE_TIME      = -2;
    const HOUR          = 3600;
    const LOCALHOST_URL = 'http://localhost:19756/';

    protected $client;

    protected $encrypter;

    protected $keyIsRegistered = false;

    public function __construct(Client $client, Encrypter $encrypter)
    {
        $this->client    = $client;
        $this->encrypter = $encrypter;
    }

    protected function makeRequest($type, array $additionalData = [])
    {
        $nonce    = $this->encrypter->generateIv();
        $payload  = array_merge([
            'type'     => $type,
            'nonce'    => base64_encode($nonce),
            'verifier' => base64_encode($this->encrypter->encrypt($nonce, $nonce)),
        ], $additionalData);

        $response = $this->client->post(static::LOCALHOST_URL, [
            'body'    => json_encode($payload),
            'headers' => [
                'content-type' => 'application/json',
            ],
        ]);

        return $this->validateResponse($response);
    }

    protected function registerKey()
    {
        if ($this->makeRequest('handshake', [
            'key' => base64_encode($this->encrypter->getKey()),
        ])) {
            $this->keyIsRegistered = true;
        }
    }

    protected function shakeHands()
    {
        if ($this->validateKey() === false) {
            $this->registerKey();
        }
    }

    protected function validateKey()
    {
        if ($this->keyIsRegistered === false) {
            return false;
        }

        return $this->makeRequest('test_handshake', [
            'key' => base64_encode($this->encrypter->getKey()),
        ]);
    }

    protected function validateResponse(Response $response)
    {
        if ($response->getStatusCode() < 200 or $response->getStatusCode() >= 300) {
            return false;
        }

        $body = $response->json();

        if (isset($body['nonce']) === false or isset($body['verifier']) === false) {
            return false;
        }

        $nonce    = base64_decode($body['nonce']);
        $verifier = $this->encrypter->decrypt(base64_decode($body['verifier']), $nonce);

        return $verifier === $nonce;
    }
}
