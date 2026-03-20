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
 * PHP version 5.3
 *
 * @category  PHPSecurityLib
 * @package   Random
 *
 * @author    Anthony Ferrara <ircmaxell@ircmaxell.com>
 * @copyright 2011 The Authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 * @version   Build @@version@@
 */
namespace Random_Lib;

use Security_Lib\Strength;
/**
 * An abstract mixer to implement a common mixing strategy
 *
 * @category PHPSecurityLib
 * @package  Random
 */
abstract class Abstract_Source implements \Random_Lib\Source
{
    /**
     * Return an instance of Strength indicating the strength of the source
     *
     * @return \SecurityLib\Strength An instance of one of the strength classes
     */
    public static function get_strength()
    {
        return new Strength(Strength::VERYLOW);
    }
    /**
     * If the source is currently available.
     * Reasons might be because the library is not installed
     *
     * @return bool
     */
    public static function is_supported()
    {
        return true;
    }
    /**
     * Returns a string of zeroes, useful when no entropy is available.
     *
     * @param int $size The size of the requested random string
     *
     * @return string A string of the requested size
     */
    protected static function empty_value($size)
    {
        return str_repeat(chr(0), $size);
    }
}