<?php
namespace Graceland\SafeInCloud;

use GuzzleHttp\Message\ResponseInterface;

class Response
{
    protected $body;
    protected $encrypter;
    protected $request;
    protected $response;

    public function __construct(ResponseInterface $response, Request $request)
    {
        $this->request   = $request;
        $this->response  = $response;
        $this->encrypter = $this->request->getEncrypter();
        $this->body      = $this->response->json();
    }

    public function get($key, $default = null)
    {
        if ($this->has($key) === false) {
            return $default;
        }

        return $this->body[$key];
    }

    public function getDecrypted($key, $default = null)
    {
        if ($this->has($key) === false) {
            return $default;
        }

        return $this->decrypt($this->body[$key]);
    }

    public function has($key)
    {
        return (isset($this->body[$key]) === true);
    }

    public function isSuccessful()
    {
        return (isset($this->body['success']) === true and $this->body['success'] === true);
    }

    public function isValid()
    {
        if ($this->response->getStatusCode() < 200 or $this->response->getStatusCode() >= 300) {
            return false;
        }

        if (isset($this->body['nonce']) === false or isset($this->body['verifier']) === false) {
            return false;
        }

        $nonce    = base64_decode($this->body['nonce']);
        $verifier = $this->decrypt($this->body['verifier'], $nonce);

        return $verifier === $this->body['nonce'];
    }

    protected function decrypt($data, $nonce = null)
    {
        if ($nonce === null) {
            $nonce = $this->request->getNonce();
        }

        return $this->encrypter->decrypt(base64_decode($data), $nonce);
    }
}
