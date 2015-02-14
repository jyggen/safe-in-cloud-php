<?php
namespace Graceland\SafeInCloud;

use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use RandomLib\Generator;

class ApiClient
{
    const LOCALHOST_URL = 'http://localhost:19756/';

    protected $client;

    protected $encrypter;

    protected $keyIsRegistered = false;

    public function __construct(Client $client, Encrypter $encrypter)
    {
        $this->client    = $client;
        $this->encrypter = $encrypter;
    }

    public function authenticate($password)
    {
        $this->shakeHands();

        if ($this->makeRequest('authenticate', [
            'expiresin' => 3600,
            'password'  => function ($payload) use ($password) {
                return base64_encode($this->encrypter->encrypt($password, base64_decode($payload['nonce'])));
            },
        ]) === false) {
            throw new \RuntimeException('Unable to authenticate against the API.');
        }
    }

    protected function makeRequest($type, array $additionalData = [])
    {
        $nonce    = $this->encrypter->generateIv();
        $payload  = array_merge([
            'type'     => $type,
            'nonce'    => base64_encode($nonce),
            'verifier' => base64_encode($this->encrypter->encrypt(base64_encode($nonce), $nonce)),
        ], $additionalData);

        foreach ($payload as $key => $data) {
            if (is_callable($data) === true) {
                $payload[$key] = $data($payload);
            }
        }

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
        $this->keyIsRegistered = $this->makeRequest('handshake', [
            'key' => base64_encode($this->encrypter->getKey()),
        ]);

        return $this->keyIsRegistered;
    }

    protected function shakeHands()
    {
        if ($this->validateKey() === true) {
            return;
        }

        if ($this->registerKey() === false or $this->validateKey() === false) {
            throw new \RuntimeException('Unable to register key with the API.');
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
        $verifier = base64_decode($this->encrypter->decrypt(base64_decode($body['verifier']), $nonce));

        return $verifier === $nonce;
    }
}
