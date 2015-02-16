<?php
namespace Graceland\SafeInCloud;

class EncrypterTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateKey()
    {
        $this->assertSame(Encrypter::KEY_SIZE, strlen(Encrypter::generateKey()));
    }

    public function testConstructor()
    {
        $this->assertInstanceOf('Graceland\\SafeInCloud\\Encrypter', new Encrypter('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testConstructorWithInvalidKeySize()
    {
        new Encrypter('aaaaaaaaaaaaaaaa');
    }

    public function testGenerateNonce()
    {
        $encrypter = new Encrypter('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa');
        $this->assertSame(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), strlen($encrypter->generateNonce()));
    }

    public function testEncryption()
    {
        $key       = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
        $encrypter = new Encrypter($key);
        $nonce     = $encrypter->generateNonce();

        $this->assertNotEquals($key, $encrypter->encrypt($key, $nonce));

        $encrypted = $encrypter->encrypt($key, $nonce);

        $this->assertEquals($key, $encrypter->decrypt($encrypted, $nonce));
    }

    public function testGetKey()
    {
        $key       = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
        $encrypter = new Encrypter($key);

        $this->assertSame($key, $encrypter->getKey());
    }
}
