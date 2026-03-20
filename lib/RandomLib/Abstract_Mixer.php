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
 * An abstract mixer to implement a common mixing strategy
 *
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

use Security_Lib\Util;
/**
 * An abstract mixer to implement a common mixing strategy
 *
 * @see      http://tools.ietf.org/html/rfc4086#section-5.2
 *
 * @category PHPSecurityLib
 * @package  Random
 *
 * @author   Anthony Ferrara <ircmaxell@ircmaxell.com>
 */
abstract class Abstract_Mixer implements \Random_Lib\Mixer
{
    /**
     * Get the block size (the size of the individual blocks used for the mixing)
     *
     * @return int The block size
     */
    abstract protected function get_part_size();
    /**
     * Mix 2 parts together using one method
     *
     * @param string $part1 The first part to mix
     * @param string $part2 The second part to mix
     *
     * @return string The mixed data
     */
    abstract protected function mix_parts1($part1, $part2);
    /**
     * Mix 2 parts together using another different method
     *
     * @param string $part1 The first part to mix
     * @param string $part2 The second part to mix
     *
     * @return string The mixed data
     */
    abstract protected function mix_parts2($part1, $part2);
    /**
     * Mix the provided array of strings into a single output of the same size
     *
     * All elements of the array should be the same size.
     *
     * @param array $parts The parts to be mixed
     *
     * @return string The mixed result
     */
    public function mix(array $parts)
    {
        if (empty($parts)) {
            return '';
        }
        $len = Util::safe_strlen($parts[0]);
        $parts = $this->normalize_parts($parts);
        $string_size = count($parts[0]);
        $parts_size = count($parts);
        $result = '';
        $offset = 0;
        for ($i = 0; $i < $string_size; $i++) {
            $stub = $parts[$offset][$i];
            for ($j = 1; $j < $parts_size; $j++) {
                $new_key = $parts[($j + $offset) % $parts_size][$i];
                //Alternately mix the output for each source
                if ($j % 2 == 1) {
                    $stub ^= $this->mix_parts1($stub, $new_key);
                } else {
                    $stub ^= $this->mix_parts2($stub, $new_key);
                }
            }
            $result .= $stub;
            $offset = ($offset + 1) % $parts_size;
        }
        return Util::safe_substr($result, 0, $len);
    }
    /**
     * Normalize the part array and split it block part size.
     *
     * This will make all parts the same length and a multiple
     * of the part size
     *
     * @param array $parts The parts to normalize
     *
     * @return array The normalized and split parts
     */
    protected function normalize_parts(array $parts)
    {
        $block_size = $this->get_part_size();
        $callback = function ($value) {
            return Util::safe_strlen($value);
        };
        $max_size = max(array_map($callback, $parts));
        if ($max_size % $block_size != 0) {
            $max_size += $block_size - $max_size % $block_size;
        }
        foreach ($parts as &$part) {
            $part = $this->str_pad($part, $max_size, chr(0));
            $part = str_split($part, $block_size);
        }
        return $parts;
    }
    private function str_pad($string, $size, $character)
    {
        $start = Util::safe_strlen($string);
        $inc = Util::safe_strlen($character);
        for ($i = $start; $i < $size; $i += $inc) {
            $string = $string . $character;
        }
        return Util::safe_substr($string, 0, $size);
    }
}