<?php

declare (strict_types=1);
/*
 * The RandomLib library for securely generating random numbers and strings in PHP
 *
 * @author     Anthony Ferrara <ircmaxell@ircmaxell.com>
 * @copyright  2011 The Authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    Build @@version@@
 */
/**
 * The Mcrypt abstract mixer class
 *
 * PHP version 5.3
 *
 * @category   PHPCryptLib
 * @package    Random
 * @subpackage Mixer
 *
 * @author     Anthony Ferrara <ircmaxell@ircmaxell.com>
 * @copyright  2013 The Authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 * @version    Build @@version@@
 */
namespace Random_Lib;

/**
 * The mcrypt abstract mixer class
 *
 * @category   PHPCryptLib
 * @package    Random
 * @subpackage Mixer
 *
 * @author     Anthony Ferrara <ircmaxell@ircmaxell.com>
 * @author     Chris Smith <chris@cs278.org>
 */
abstract class Abstract_Mcrypt_Mixer extends Abstract_Mixer
{
    /**
     * mcrypt module resource
     *
     * @var resource
     */
    private $mcrypt;
    /**
     * Block size of cipher
     *
     * @var int
     */
    private $block_size;
    /**
     * Cipher initialization vector
     *
     * @var string
     */
    private $initv;
    /**
     * {@inheritdoc}
     */
    public static function test()
    {
        return extension_loaded('mcrypt');
    }
    /**
     * Construct mcrypt mixer
     */
    public function __construct()
    {
        $this->mcrypt = mcrypt_module_open($this->get_cipher(), '', MCRYPT_MODE_ECB, '');
        $this->block_size = mcrypt_enc_get_block_size($this->mcrypt);
        $this->initv = str_repeat(chr(0), mcrypt_enc_get_iv_size($this->mcrypt));
    }
    /**
     * Performs cleanup
     */
    public function __destruct()
    {
        if ($this->mcrypt) {
            mcrypt_module_close($this->mcrypt);
        }
    }
    /**
     * Fetch the cipher for mcrypt.
     *
     * @return string
     */
    abstract protected function get_cipher();
    /**
     * {@inheritdoc}
     */
    protected function get_part_size()
    {
        return $this->block_size;
    }
    /**
     * {@inheritdoc}
     */
    protected function mix_parts1($part1, $part2)
    {
        return $this->encrypt_block($part1, $part2);
    }
    /**
     * {@inheritdoc}
     */
    protected function mix_parts2($part1, $part2)
    {
        return $this->decrypt_block($part2, $part1);
    }
    /**
     * Encrypts a block using the suppied key
     *
     * @param string $input Plaintext to encrypt
     * @param string $key   Encryption key
     *
     * @return string Resulting ciphertext
     */
    private function encrypt_block($input, $key)
    {
        if (!$input && !$key) {
            return '';
        }
        $this->prepare_cipher($key);
        $result = mcrypt_generic($this->mcrypt, $input);
        mcrypt_generic_deinit($this->mcrypt);
        return $result;
    }
    /**
     * Derypts a block using the suppied key
     *
     * @param string $input Ciphertext to decrypt
     * @param string $key   Encryption key
     *
     * @return string Resulting plaintext
     */
    private function decrypt_block($input, $key)
    {
        if (!$input && !$key) {
            return '';
        }
        $this->prepare_cipher($key);
        $result = mdecrypt_generic($this->mcrypt, $input);
        mcrypt_generic_deinit($this->mcrypt);
        return $result;
    }
    /**
     * Sets up the mcrypt module
     *
     * @param string $key
     *
     * @return void
     */
    private function prepare_cipher($key)
    {
        if (0 !== mcrypt_generic_init($this->mcrypt, $key, $this->initv)) {
            throw new \RuntimeException('Failed to prepare mcrypt module');
        }
    }
}