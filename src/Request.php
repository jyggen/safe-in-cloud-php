<?php
namespace Graceland\SafeInCloud;

use GuzzleHttp\ClientInterface;

class Request
{
    protected $encrypter;

    protected $nonce;

    protected $payload = [];

    protected $type;

    public function __construct($type, Encrypter $encrypter)
    {
        $this->encrypter = $encrypter;
        $this->nonce     = $this->encrypter->generateNonce();
        $this->type      = $type;

        $this->setupPayload();
    }

    public function addData($key, $value)
    {
        $this->payload[$key] = $value;
    }

    public function addEncodedData($key, $value)
    {
        $this->addData($key, base64_encode($value));
    }

    public function addEncryptedData($key, $value)
    {
        $this->addEncodedData($key, $this->encrypter->encrypt($value, $this->nonce));
    }

    public function getEncrypter()
    {
        return $this->encrypter;
    }

    public function getNonce()
    {
        return $this->nonce;
    }

    public function getPayload()
    {
        return $this->payload;
    }

    protected function setupPayload()
    {
        $this->addData('type', $this->type);
        $this->addEncodedData('nonce', $this->nonce);
        $this->addEncryptedData('verifier', base64_encode($this->nonce));
    }
}
