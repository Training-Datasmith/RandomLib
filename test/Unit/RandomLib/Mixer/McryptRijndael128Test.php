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

namespace RandomLib\Mixer;

use SecurityLib\Strength;

class McryptRijndael128Test extends \PHPUnit_Framework_TestCase
{
    public static function provideMix()
    {
        $data = [
            [[], ''],
            [['', ''], ''],
            [['a'], '61'],
            [['a', 'b'], '6a'],
            [['aa', 'ba'], '688d'],
            [['ab', 'bb'], 'f8bc'],
            [['aa', 'bb'], 'a0f3'],
            [['aa', 'bb', 'cc'], '87c3'],
            [['aabbcc', 'bbccdd', 'ccddee'], '7cf2273e46c7'],
        ];

        return $data;
    }

    protected function setUp()
    {
        if (!extension_loaded('mcrypt')) {
            $this->markTestSkipped('mcrypt extension is not available');
        }
    }

    public function testConstructWithoutArgument()
    {
        $hash = new McryptRijndael128();
        $this->assertTrue($hash instanceof \RandomLib\Mixer);
    }

    public function testGetStrength()
    {
        $strength = new Strength(Strength::HIGH);
        $actual = McryptRijndael128::getStrength();
        $this->assertEquals($actual, $strength);
    }

    public function testTest()
    {
        $actual = McryptRijndael128::test();
        $this->assertTrue($actual);
    }

    /**
     * @dataProvider provideMix
     */
    public function testMix($parts, $result)
    {
        $mixer = new McryptRijndael128();
        $actual = $mixer->mix($parts);
        $this->assertEquals($result, bin2hex($actual));
    }
}
