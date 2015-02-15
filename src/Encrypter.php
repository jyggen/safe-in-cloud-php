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

    /**
     * @return string
    */
    public function decrypt($value, $nonce)
    {
        $value = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->key, $value, MCRYPT_MODE_CBC, $nonce);
        return $this->stripPadding($value);
    }

    public function encrypt($value, $nonce)
    {
        $value = $this->addPadding($value);
        return mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key, $value, MCRYPT_MODE_CBC, $nonce);
    }

    public function generateIv()
    {
        return mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), $this->getRandomizer());
    }

    /**
     * @return string
    */
    public function getKey()
    {
        return $this->key;
    }

    protected function addPadding($value)
    {
        $pad   = static::BLOCK_SIZE - (strlen($value) % static::BLOCK_SIZE);
        $value = $value.str_repeat(chr($pad), $pad);

        return $value;
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

    protected function isPaddingValid($pad, $value)
    {
        $beforePad = strlen($value) - $pad;
        return substr($value, $beforePad) == str_repeat(substr($value, -1), $pad);
    }

    /**
     * @return string
    */
    protected function stripPadding($value)
    {
        $pad = ord($value[($len = strlen($value)) - 1]);
        return $this->isPaddingValid($pad, $value) ? substr($value, 0, $len - $pad) : $value;
    }
}
