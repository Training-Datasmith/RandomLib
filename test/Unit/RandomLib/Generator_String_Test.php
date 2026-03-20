<?php

declare(strict_types=1);

/*
 * The RandomLib library for securely generating random numbers and strings in PHP
 *
 * @author     Anthony Ferrara <ircmaxell@ircmaxell.com>
 * @copyright  2011 The Authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    Build @@version@@
 */

namespace RandomLib;

class GeneratorStringTest extends \PHPUnit_Framework_TestCase
{
    protected $generator = null;
    protected $mixer = null;
    protected $sources = [];

    public static function provideCharCombinations()
    {
        return [
            ['CHAR_LOWER', implode('', range('a', 'z'))],
            ['CHAR_UPPER', implode('', range('A', 'Z'))],
            ['CHAR_DIGITS', implode('', range(0, 9))],
            ['CHAR_UPPER_HEX', '0123456789ABCDEF'],
            ['CHAR_LOWER_HEX', '0123456789abcdef'],
            ['CHAR_BASE64', '+/0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'],
            ['EASY_TO_READ', '3479ACEFHJKLMNPRTUVWXYabcdefghijkmnopqrstuvwxyz'],
            ['CHAR_BRACKETS', '()<>[]{}'],
            ['CHAR_SYMBOLS', " !\"#$%&'()*+,-./:;<=>?@[\]^_`{|}~"],
            ['CHAR_PUNCT', ',.:;'],
            ['CHAR_ALPHA', implode('', array_merge(range('A', 'Z'), range('a', 'z')))],
            ['CHAR_ALNUM', implode('', array_merge(range(0, 9), range('A', 'Z'), range('a', 'z')))],
            ['CHAR_ALPHA | PUNCT', ',.:;ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz', Generator::CHAR_ALPHA | Generator::CHAR_PUNCT],
            ['CHAR_LOWER | EASY_TO_READ', 'abcdefghijkmnopqrstuvwxyz', Generator::CHAR_LOWER | Generator::EASY_TO_READ],
            ['CHAR_DIGITS | EASY_TO_READ', '3479', Generator::CHAR_DIGITS | Generator::EASY_TO_READ],
        ];
    }

    public function setUp()
    {
        $source1 = $this->getMock('RandomLib\Source');
        $source1->expects($this->any())
            ->method('generate')
            ->will($this->returnCallback(
                function ($size) {
                    $r = '';
                    for ($i = 0; $i < $size; $i++) {
                        $r .= chr($i % 256);
                    }

                    return $r;
                }
            ));
        $source2 = $this->getMock('RandomLib\Source');
        $source2->expects($this->any())
            ->method('generate')
            ->will($this->returnCallback(
                function ($size) {
                    $r = '';
                    for ($i = 0; $i < $size; $i++) {
                        $r .= chr(0);
                    }

                    return $r;
                }
            ));

        $this->mixer = $this->getMock('RandomLib\Mixer');
        $this->mixer->expects($this->any())
            ->method('mix')
            ->will($this->returnCallback(function (array $sources) {
                if (empty($sources)) {
                    return '';
                }
                $start = array_pop($sources);

                return array_reduce(
                    $sources,
                    function ($el1, $el2) {
                        return $el1 ^ $el2;
                    },
                    $start
                );
            }));

        $this->sources = [$source1, $source2];
        $this->generator = new Generator($this->sources, $this->mixer);
    }

    /**
     * @dataProvider provideCharCombinations
     */
    public function testScheme($schemeName, $expected, $scheme = 0)
    {
        // test for overspecification by doubling the expected amount
        if (!$scheme) {
            $scheme = constant("RandomLib\Generator::$schemeName");
        }
        $chars = $this->generator->generateString(strlen($expected) * 2, $scheme);
        $this->assertEquals($expected . $expected, $chars, sprintf('Testing Generator::%s failed', $schemeName));
    }
}
