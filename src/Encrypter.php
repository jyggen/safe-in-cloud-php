<?php
namespace Graceland\SafeInCloud;

class Encrypter
{
    const BLOCK_SIZE = 16;
    const KEY_SIZE   = 16;

    protected $generator;
    protected $key;

    public static function generateKey()
    {
        return bin2hex(openssl_random_pseudo_bytes(static::KEY_SIZE));
    }

    public function __construct($key)
    {
        if (strlen($key) !== (static::KEY_SIZE * 2)) {
            throw new \RuntimeException('The supplied encryption key must be '.(static::KEY_SIZE * 2).' bits');
        }

        $this->key = $key;
    }

    public function encrypt($string, $iv = null)
    {
        if ($iv === null) {
            $iv = $this->generateIv();
        }

        $string = base64_encode($string);
        $pad    = static::BLOCK_SIZE - (strlen($string) % static::BLOCK_SIZE);
        $string = $string.str_repeat(chr($pad), $pad);

        return mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key, $string, MCRYPT_MODE_CBC, $iv);
    }

    public function generateIv()
    {
        return mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), $this->getRandomizer());
    }

    public function getKey()
    {
        return $this->key;
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
