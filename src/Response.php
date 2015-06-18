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

    /**
     * @return mixed
    */
    public function get($key, $default = null)
    {
        if ($this->has($key) === false) {
            return $default;
        }

        return $this->body[$key];
    }

    /**
     * @return mixed
    */
    public function getDecrypted($key, $default = null, $assocKeys = [])
    {
        $value = $this->get($key, $default);

        if ($value === $default) {
            return $default;
        }

        return $this->decrypt($value, null, $assocKeys);
    }

    public function has($key)
    {
        return (isset($this->body[$key]) === true);
    }

    public function isSuccessful()
    {
        return (isset($this->body['success']) === true && $this->body['success'] === true);
    }

    public function isValid()
    {
        if ($this->response->getStatusCode() < 200 || $this->response->getStatusCode() >= 300) {
            return false;
        }

        if (isset($this->body['nonce']) === false || isset($this->body['verifier']) === false) {
            return false;
        }

        $nonce    = base64_decode($this->body['nonce']);
        $verifier = $this->decrypt($this->body['verifier'], $nonce);

        return $verifier === $this->body['nonce'];
    }

    /**
     * @return string
    */
    protected function decrypt($data, $nonce = null, $assocKeys = [])
    {
        if ($nonce === null) {
            $nonce = $this->request->getNonce();
        }

        if (is_array($data) === false) {
            return $this->encrypter->decrypt(base64_decode($data), $nonce);
        }

        $decrypted = [];
        $isAssoc   = (bool) count(array_filter(array_keys($data), 'is_string'));

        foreach ($data as $key => $value) {
            if ($isAssoc === true and in_array($key, $assocKeys) === false) {
                $decrypted[$key] = $value;
                continue;
            }

            $decrypted[$key] = $this->decrypt($value, $nonce, $assocKeys);
        }

        return $decrypted;
    }
}
