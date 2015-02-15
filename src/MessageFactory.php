<?php
namespace Graceland\SafeInCloud;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\ResponseInterface;

class MessageFactory
{
    protected $encrypter;

    public function __construct(Encrypter $encrypter)
    {
        $this->encrypter = $encrypter;
    }

    public function createRequest($type)
    {
        return new Request($type, $this->encrypter);
    }

    public function createResponse(ResponseInterface $response, Request $request)
    {
        return new Response($response, $request, $this->encrypter);
    }

    public function getEncrypter()
    {
        return $this->encrypter;
    }
}
