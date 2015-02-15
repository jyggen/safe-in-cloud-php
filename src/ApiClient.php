<?php
namespace Graceland\SafeInCloud;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\Response;
use RandomLib\Generator;

class ApiClient
{
    const LOCALHOST_URL = 'http://localhost:19756/';

    protected $client;

    protected $factory;

    protected $registered = false;

    protected $token;

    public static function create()
    {
        $guzzle    = new Client;
        $encrypter = new Encrypter(Encrypter::generateKey());
        $factory   = new MessageFactory($encrypter);

        return new static($guzzle, $factory);
    }

    public function __construct(ClientInterface $client, MessageFactory $factory)
    {
        $this->client  = $client;
        $this->factory = $factory;
    }

    public function authenticate($password)
    {
        $this->doHandshake();

        $request = $this->factory->createRequest('authenticate');

        $request->addData('expiresin', 3600);
        $request->addEncryptedData('password', $password);

        $response = $this->send($request);

        if ($response->isValid() === false) {
            throw new \RuntimeException('Unable to authenticate against the API.');
        }

        if ($response->isSuccessful() === false or $response->has('token') === false) {
            return false;
        }

        $this->token = $response->getDecrypted('token');

        return ($this->token !== null);
    }

    public function getLogins()
    {
        if ($this->token === null) {
            throw new \RuntimeException('You need to be authenticated before making this request.');
        }

        $this->doHandshake();

        $request = $this->factory->createRequest('get_logins');

        $request->addEncryptedData('token', $this->token);

        $response = $this->send($request);

        if ($response->isValid() === false) {
            throw new \RuntimeException('Unable to retrieve logins from the API.');
        }

        if ($response->isSuccessful() === false or $response->has('logins') === false) {
            return [];
        }

        return $response->get('logins', []);
    }

    public function getWebAccounts($url)
    {
        if ($this->token === null) {
            throw new \RuntimeException('You need to be authenticated before making this request.');
        }

        $this->doHandshake();

        $request = $this->factory->createRequest('get_web_accounts_2');

        $request->addEncryptedData('token', $this->token);
        $request->addEncryptedData('url', $url);

        $response = $this->send($request);

        if ($response->isValid() === false) {
            throw new \RuntimeException('Unable to retrieve web accounts from the API.');
        }

        if ($response->isSuccessful() === false or $response->has('accounts') === false) {
            return [];
        }

        return $response->get('accounts', []);
    }

    public function doHandshake()
    {
        if ($this->encryptionKeyIsRegistered() === true) {
            return;
        }

        $this->registerEncryptionKey();

        if ($this->encryptionKeyIsRegistered() === false) {
            throw new \RuntimeException('Unable to register key with the API.');
        }
    }

    protected function encryptionKeyIsRegistered()
    {
        if ($this->registered === false) {
            return false;
        }

        $request = $this->factory->createRequest('test_handshake');

        $request->addEncodedData('key', $this->factory->getEncrypter()->getKey());

        $response = $this->send($request);

        return ($response->isValid() and $response->isSuccessful());
    }

    protected function registerEncryptionKey()
    {
        $request = $this->factory->createRequest('handshake');

        $request->addEncodedData('key', $this->factory->getEncrypter()->getKey());

        $response         = $this->send($request);
        $this->registered = ($response->isValid() and $response->isSuccessful());
    }

    protected function send(Request $request)
    {
        $response = $this->client->post(static::LOCALHOST_URL, [
            'body'    => json_encode($request->getPayload()),
            'headers' => [
                'content-type' => 'application/json',
            ],
        ]);

        return $this->factory->createResponse($response, $request);
    }
}
