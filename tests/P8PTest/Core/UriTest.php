<?php
/**
 * P8P Framework - https://github.com/hpolaert/p8p
 *
 * @link      https://github.com/hpolaert/p8p
 * @copyright Copyright (c) 2016 Hugues Polaert
 * @license   https://github.com/hpolaert/p8p/LICENCE.md (MIT)
 */
namespace P8PTest\Core;

use P8P\Http\Uri;

/**
 * HTTP URI Test Cases
 *
 * Asserts P8P\Http\Uri functionalities
 */
class UriTest extends \PHPUnit_Framework_TestCase
{

    private static $MOCK_URI_REQUEST = 'http://www.test.com/path?arg1=hello&arg2=world#fragment';

    /**
     * @Covers Uri::withScheme
     */
    public function testShouldReturnACloneInstance()
    {
        $uriOrigin = new Uri();
        $uriOrigin->buildUriFromSting(self::$MOCK_URI_REQUEST);
        $uriClone = $uriOrigin->withScheme('http');
        $this->assertNotSame($uriOrigin, $uriClone);
    }

    /**
     * @expectedException \P8P\Exception\NotFoundException
     * @expectedExceptionMessage Error, key "doesNotExist" is not regist
     */
    /* public function testNotFoundException()
     {
         $this->testContainer['doesNotExist'];
     }*/
}