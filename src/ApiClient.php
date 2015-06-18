<?php
namespace Graceland\SafeInCloud;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class ApiClient
{
    const EXPIRATION_HOUR  = 3600;
    const EXPIRATION_NEVER = -1;
    const EXPIRATION_ONCE  = -2;

    const URL = 'http://localhost:19756/';

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
        $request = $this->factory->createRequest('authenticate');

        $request->addData('expiresin', static::EXPIRATION_HOUR);
        $request->addEncryptedData('password', $password);

        $response = $this->send($request);

        if ($response->isValid() === false) {
            throw new \RuntimeException('Unable to authenticate against the API.');
        }

        if ($response->isSuccessful() === false || $response->has('token') === false) {
            throw new \RuntimeException('Authenticated unsuccessfully, invalid password?');
        }

        $this->token = $response->getDecrypted('token');

        return $this->token;
    }

    /**
     * @return string
    */
    public function getAuthToken()
    {
        return $this->token;
    }

    /**
     * @return array
    */
    public function getLogins()
    {
        if ($this->token === null) {
            throw new \RuntimeException('You need to be authenticated before making this request.');
        }

        $request = $this->factory->createRequest('get_logins');

        $request->addEncryptedData('token', $this->token);

        $response = $this->send($request);

        if ($response->isValid() === false) {
            throw new \RuntimeException('Unable to retrieve logins from the API.');
        }

        if ($response->isSuccessful() === false || $response->has('logins') === false) {
            throw new \RuntimeException('Retrieved web accounts unsuccessfully, invalid authentication token?');
        }

        return $response->getDecrypted('logins', []);
    }

    /**
     * @return array
    */
    public function getWebAccounts($url)
    {
        if ($this->token === null) {
            throw new \RuntimeException('You need to be authenticated before making this request.');
        }

        $request = $this->factory->createRequest('get_web_accounts_2');

        $request->addEncryptedData('token', $this->token);
        $request->addEncryptedData('url', $url);

        $response = $this->send($request);

        if ($response->isValid() === false) {
            throw new \RuntimeException('Unable to retrieve web accounts from the API.');
        }

        if ($response->isSuccessful() === false || $response->has('accounts') === false) {
            throw new \RuntimeException('Retrieved web accounts unsuccessfully, invalid authentication token?');
        }

        return $response->getDecrypted('accounts', [], ['login', 'password']);
    }

    public function doHandshake()
    {
        if ($this->isEncryptionKeyRegistered() === true) {
            return;
        }

        $this->registerEncryptionKey();

        if ($this->isEncryptionKeyRegistered() === false) {
            throw new \RuntimeException('Unable to register key with the API.');
        }
    }

    public function setAuthToken($token)
    {
        $this->token = $token;
    }

    protected function isEncryptionKeyRegistered()
    {
        if ($this->registered === false) {
            return false;
        }

        $request = $this->factory->createRequest('test_handshake');

        $request->addEncodedData('key', $this->factory->getEncrypter()->getKey());

        $response = $this->send($request);

        return ($response->isValid() && $response->isSuccessful());
    }

    protected function registerEncryptionKey()
    {
        $request = $this->factory->createRequest('handshake');

        $request->addEncodedData('key', $this->factory->getEncrypter()->getKey());

        $response         = $this->send($request);
        $this->registered = ($response->isValid() && $response->isSuccessful());
    }

    protected function send(Request $request)
    {
        $response = $this->client->post(static::URL, [
            'body' => json_encode($request->getPayload()),
            'headers' => [
                'content-type' => 'application/json',
            ],
        ]);

        return $this->factory->createResponse($response, $request);
    }
}
