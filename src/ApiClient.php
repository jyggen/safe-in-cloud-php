<?php
namespace Graceland\SafeInCloud;

use GuzzleHttp\Client;
use RandomLib\Generator;

class ApiClient
{
    const NEVER_EXPIRES = -1;
    const ONE_TIME      = -2;
    const HOUR          = 3600;
    const FROM_STRING   = 0;
    const FROM_BASE64   = 1;
    const TO_STRING     = 2;
    const TO_BASE64     = 4;
    const KEY_SIZE      = 16;
    const IV_SIZE       = 8;
    const LOCALHOST_URL = 'http://localhost:19756/';

    protected $client;
    protected $generator;

    public function __construct(Client $client, Generator $generator)
    {
        $this->client    = $client;
        $this->generator = $generator;
    }

    public function shakeHands()
    {
        $key     = $this->generator->generate(static::KEY_SIZE);
        $nonce   = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), $this->getRandomizer());
        $payload = [
            'type'     => 'handshake',
            'key'      => base64_encode($key),
            'nonce'    => base64_encode($nonce),
            'verifier' => base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $nonce, MCRYPT_MODE_CBC, $nonce)),
        ];

        $response = $this->client->post(static::LOCALHOST_URL, [
            'body'    => json_encode($payload),
            'headers' => [
                'content-type' => 'application/json',
            ],
        ]);

        print $response->getBody();
    }

    protected function getRandomizer()
    {
        if (defined('MCRYPT_DEV_URANDOM')) {
            return MCRYPT_DEV_URANDOM;
        }

        if (defined('MCRYPT_DEV_RANDOM')) {
            return MCRYPT_DEV_RANDOM;
        }

        mt_srand();
        return MCRYPT_RAND;
    }
}

// function shakeHands() {
//         D.func();
//         var key = null;
//         var request = null,
//             response = null;
//         if (_key) {
//             // test handshake
//             request = {
//                 type: "test_handshake"
//             };
//             setRequestVerifier(request, _key);
//             response = sendRequest(request, _key);
//         }
//         if (!response || response.error) {
//             // new handshake
//             key = Base64.encode(getRandomByteArray(KEY_SIZE));
//             request = {
//                 type: "handshake",
//                 key: key
//             };
//             setRequestVerifier(request, key);
//             response = sendRequest(request, key);
//             if (!response.error) {
//                 // save key
//                 _key = key;
//             }
//         }
//         return response;
//     }
