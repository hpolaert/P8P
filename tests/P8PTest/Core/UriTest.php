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

    const MOCK_BASIC_URI_REQUEST = 'http://www.test.com/path?arg1=hello&arg2=world#fragment';
    const MOCK_HTTPS_REQUEST = 'https://www.test.com/path?arg1=hello&arg2=world#fragment';
    const MOCK_EMPTY_SCHEME = 'www.test.com/path?arg1=hello&arg2=world#fragment';
    const MOCK_USER_PSWRD = 'http://username:password@example.com/';
    const MOCK_CUST_PORT = 'Http://username:password@hostname:9090/path?arg=value#anchor';
    const MOCK_INT_SCHEME = '123://test.com';
    const MOCK_NOT_ALLOWED_SCHEME = 'hello://test.com';
    const MOCK_DEFAULT_PORT_HTTP = 'http://www.test.com:80/hello';
    const MOCK_DEFAULT_PORT_HTTPS = 'https://www.test.com:443/hello';
    const MOCK_CUSTOM_PORT_HTTPS = 'https://www.test.com:800/hello';
    const MOCK_CUSTOM_OTHER_PORT_HTTPS = 'https://www.test.com:222/hello';
    const MOCK_EXCEPTION_PORT_LENGTH_6 = '999999';
    const MOCK_PATH_CUSTOM = 'http://www.test.com/hello';
    const MOCK_PATH_COMPLEX = 'http://www.test.com/hello-world/p8p';
    const MOCK_PATH_SLASH = 'http://www.test.com/';
    const MOCK_PATH_EMPTY = 'http://www.test.com';
    const MOCK_PATH_SPACE = 'http://www.test.com/i have/spaces in my uri';
    const MOCK_PATH_SPACE_ARGS = 'http://www.test.com/i have/spaces in my uri/?var1=hello this is space&var2=world';
    const MOCK_PATH_MULTIPLE = 'http://www.test.com/hello/world';

    /**
     * @Covers Uri::buildUriFromSting
     * @Covers Uri::withScheme
     */
    public function testShouldReturnACloneInstance()
    {
        $uriOrigin = Uri::buildUriFromSting(self::MOCK_BASIC_URI_REQUEST);
        $uriClone  = $uriOrigin->withScheme('http');
        $this->assertNotSame($uriOrigin, $uriClone);
    }

    /**
     * @Covers Uri::withScheme
     * @Covers Uri::getScheme
     */
    public function testScheme()
    {
        $uriBasic = Uri::buildUriFromSting(self::MOCK_BASIC_URI_REQUEST);
        $uriHttps = Uri::buildUriFromSting(self::MOCK_HTTPS_REQUEST);
        $uriEmpty = Uri::buildUriFromSting(self::MOCK_EMPTY_SCHEME);
        $uriUser  = Uri::buildUriFromSting(self::MOCK_USER_PSWRD);
        $uriPort  = Uri::buildUriFromSting(self::MOCK_CUST_PORT);
        $this->assertEquals('http', $uriBasic->getScheme());
        $this->assertEquals('https', $uriHttps->getScheme());
        $this->assertEquals('', $uriEmpty->getScheme());
        $this->assertEquals('http', $uriUser->getScheme());
        $this->assertEquals('http', $uriPort->getScheme());
        $this->assertEquals('https', $uriBasic->withScheme('https')->getScheme());
        $this->assertEquals('', $uriBasic->withScheme('')->getScheme());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Error, scheme must be either http, https or an empty string
     */
    public function testParseSchemeNotAllowedException()
    {
        Uri::buildUriFromSting(self::MOCK_INT_SCHEME);
        Uri::buildUriFromSting(self::MOCK_NOT_ALLOWED_SCHEME);
    }

    /**
     * @Covers Uri::withPort
     * @Covers Uri::getPort
     */
    public function testPort()
    {
        $uriEmpty        = Uri::buildUriFromSting(self::MOCK_EMPTY_SCHEME);
        $uriUser         = Uri::buildUriFromSting(self::MOCK_USER_PSWRD);
        $uriPort         = Uri::buildUriFromSting(self::MOCK_CUST_PORT);
        $uriDefHttpPort  = Uri::buildUriFromSting(self::MOCK_DEFAULT_PORT_HTTP);
        $uriDefHttpsPort = Uri::buildUriFromSting(self::MOCK_DEFAULT_PORT_HTTPS);
        $uriOthHttpsPort = Uri::buildUriFromSting(self::MOCK_CUSTOM_OTHER_PORT_HTTPS);
        $uriCusHttpsPort = Uri::buildUriFromSting(self::MOCK_CUSTOM_PORT_HTTPS, true, '800');
        $this->assertEquals('', $uriEmpty->getPort());
        $this->assertEquals('', $uriUser->getPort());
        $this->assertEquals('9090', $uriPort->getPort());
        $this->assertEquals('', $uriDefHttpPort->getPort());
        $this->assertEquals('', $uriDefHttpsPort->getPort());
        $this->assertEquals('222', $uriOthHttpsPort->getPort());
        $this->assertEquals('', $uriCusHttpsPort->getPort());
        $this->assertEquals('7070', $uriEmpty->withPort('7070')->getPort());
        $this->assertEquals('443', $uriEmpty->withPort('443')->getPort());
        $this->assertEquals('', $uriDefHttpsPort->withCustomHttpsPort('443')->getPort());
        $this->assertEquals('443', $uriDefHttpsPort->withCustomHttpsPort('443', '622')->getPort());
        $this->assertEquals('443', $uriDefHttpPort->withPort('443')->getPort());
        $this->assertEquals('', $uriDefHttpPort->getPort());
        $this->assertEquals('', $uriDefHttpPort->withPort('80')->getPort());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Error, port length must be between 1 and 5 digits
     */
    public function testParsePortLengthException()
    {
        Uri::buildUriFromArray(['port' => self::MOCK_EXCEPTION_PORT_LENGTH_6]);
    }

    /**
     * @Covers Uri::withPath
     * @Covers Uri::getPath
     */
    public function testPath()
    {
        $uriComplex  = Uri::buildUriFromSting(self::MOCK_PATH_COMPLEX);
        $uriCustom   = Uri::buildUriFromSting(self::MOCK_PATH_CUSTOM);
        $uriEmpty    = Uri::buildUriFromSting(self::MOCK_PATH_EMPTY);
        $uriMultiple = Uri::buildUriFromSting(self::MOCK_PATH_MULTIPLE);
        $uriSpace    = Uri::buildUriFromSting(self::MOCK_PATH_SPACE);
        $uriSlash    = Uri::buildUriFromSting(self::MOCK_PATH_SLASH);
        $this->assertEquals('/hello-world/p8p', $uriComplex->getPath());
        $this->assertEquals('/hello', $uriCustom->getPath());
        $this->assertEquals('/', $uriEmpty->getPath());
        $this->assertEquals('/hello/world', $uriMultiple->getPath());
        $this->assertEquals('/i%20have/spaces%20in%20my%20uri', $uriSpace->getPath());
        $this->assertEquals('/', $uriSlash->getPath());
        $this->assertEquals('/hello/world', $uriCustom->withPath('/hello/world')->getPath());
        $this->assertEquals('/hello', $uriCustom->getPath());
    }

    /**
     * @Covers Uri::getUserInfo
     * @Covers Uri::getAuthority
     */
    public function testGetAuthorityAndUserInfos()
    {
        $uriWithoutPort                = Uri::buildUriFromSting(self::MOCK_USER_PSWRD);
        $uriWithPort                   = Uri::buildUriFromSting(self::MOCK_CUST_PORT);
        $uriWithoutUserInfoWithoutPort = Uri::buildUriFromSting(self::MOCK_HTTPS_REQUEST);
        $uriWithoutUserInfo            = Uri::buildUriFromSting(self::MOCK_CUSTOM_OTHER_PORT_HTTPS);
        $this->assertEquals('username:password@example.com', $uriWithoutPort->getAuthority());
        $this->assertEquals('username:password@hostname:9090', $uriWithPort->getAuthority());
        $this->assertEquals('www.test.com', $uriWithoutUserInfoWithoutPort->getAuthority());
        $this->assertEquals('www.test.com:222', $uriWithoutUserInfo->getAuthority());
        $this->assertEquals('username:password', $uriWithoutPort->getUserInfo());
        $this->assertEquals('username:password', $uriWithPort->getUserInfo());
        $this->assertEquals('', $uriWithoutUserInfoWithoutPort->getUserInfo());
        $this->assertEquals('', $uriWithoutUserInfo->getUserInfo());
    }

    /**
     * @Covers Uri::getHost
     * @Covers Uri::withHost
     */
    public function testHost()
    {
        $uriCustom = Uri::buildUriFromSting(self::MOCK_CUST_PORT);
        $uriBasic  = Uri::buildUriFromSting(self::MOCK_BASIC_URI_REQUEST);
        $this->assertEquals('hostname', $uriCustom->getHost());
        $this->assertEquals('www.test.com', $uriBasic->getHost());
        $this->assertEquals('localhost', $uriBasic->withHost('localhost')->getHost());
        $this->assertEquals('www.test.com', $uriBasic->getHost());
    }

    /**
     * @Covers Uri::getQuery
     * @Covers Uri::withQuery
     */
    public function testQuery()
    {
        $uriSpaces = Uri::buildUriFromSting(self::MOCK_PATH_SPACE_ARGS);
        $uriBasic  = Uri::buildUriFromSting(self::MOCK_BASIC_URI_REQUEST);
        $this->assertEquals('var1=hello%20this%20is%20space&var2=world', $uriSpaces->getQuery());
        $this->assertEquals('arg1=hello&arg2=world', $uriBasic->getQuery());
        $this->assertEquals('arg5=helloworld', $uriBasic->withQuery('arg5=helloworld')->getQuery());
        $this->assertEquals('arg1=hello&arg2=world', $uriBasic->getQuery());
    }
}