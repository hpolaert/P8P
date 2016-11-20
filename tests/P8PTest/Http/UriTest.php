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
    protected $MOCK_SERVER          = [];
    protected $MOCK_URI_ARRAY_HTTP  = [];
    protected $MOCK_URI_ARRAY_HTTPS = [];

    /**
     * Build mock server request
     * Simulate a $_SERVER request
     */
    public function __construct()
    {
        $this->MOCK_SERVER          = [
            'SERVER_PROTOCOL'      => 'HTTP/1.1',
            'REQUEST_METHOD'       => 'GET',
            'SCRIPT_NAME'          => '/test/hello/world.php',
            'REQUEST_URI'          => '/test/hello/world.php?hello=world',
            'QUERY_STRING'         => 'hello=world',
            'SERVER_NAME'          => 'localhost',
            'SERVER_PORT'          => 80,
            'HTTP_HOST'            => 'localhost',
            'HTTP_ACCEPT'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8',
            'HTTP_ACCEPT_CHARSET'  => 'ISO-8859-1,utf-8;q=0.7,*;q=0.3',
            'HTTP_USER_AGENT'      => 'Test',
            'REMOTE_ADDR'          => '127.0.0.1',
            'REQUEST_TIME'         => time(),
            'REQUEST_TIME_FLOAT'   => microtime(true)
        ];
        $this->MOCK_URI_ARRAY_HTTP  = [
            'scheme'   => 'http',
            'user'     => 'test',
            'password' => 'azerty123',
            'port'     => '80',
            'host'     => 'localhost',
            'path'     => '/test.php',
            'query'    => 'hello=world&test=123',
            'fragment' => ''
        ];
        $this->MOCK_URI_ARRAY_HTTPS = [
            'scheme'   => 'https',
            'user'     => 'test',
            'password' => 'azerty123',
            'port'     => '80',
            'host'     => 'localhost',
            'path'     => '/test.php',
            'query'    => 'hello=world&test=123',
            'fragment' => ''
        ];
    }

    /**
     * @Covers Uri::buildUriFromString
     * @Covers Uri::withScheme
     */
    public function testShouldReturnACloneInstance()
    {
        $uriOrigin = Uri::buildUriFromString(self::MOCK_BASIC_URI_REQUEST);
        $uriClone  = $uriOrigin->withScheme('http');
        $this->assertNotSame($uriOrigin, $uriClone);
    }

    /**
     * @Covers Uri::withScheme
     * @Covers Uri::getScheme
     */
    public function testScheme()
    {
        $uriBasic = Uri::buildUriFromString(self::MOCK_BASIC_URI_REQUEST);
        $uriHttps = Uri::buildUriFromString(self::MOCK_HTTPS_REQUEST);
        $uriEmpty = Uri::buildUriFromString(self::MOCK_EMPTY_SCHEME);
        $uriUser  = Uri::buildUriFromString(self::MOCK_USER_PSWRD);
        $uriPort  = Uri::buildUriFromString(self::MOCK_CUST_PORT);
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
        Uri::buildUriFromString(self::MOCK_INT_SCHEME);
        Uri::buildUriFromString(self::MOCK_NOT_ALLOWED_SCHEME);
    }

    /**
     * @Covers Uri::withPort
     * @Covers Uri::getPort
     */
    public function testPort()
    {
        $uriEmpty        = Uri::buildUriFromString(self::MOCK_EMPTY_SCHEME);
        $uriUser         = Uri::buildUriFromString(self::MOCK_USER_PSWRD);
        $uriPort         = Uri::buildUriFromString(self::MOCK_CUST_PORT);
        $uriDefHttpPort  = Uri::buildUriFromString(self::MOCK_DEFAULT_PORT_HTTP);
        $uriDefHttpsPort = Uri::buildUriFromString(self::MOCK_DEFAULT_PORT_HTTPS);
        $uriOthHttpsPort = Uri::buildUriFromString(self::MOCK_CUSTOM_OTHER_PORT_HTTPS);
        $uriCusHttpsPort = Uri::buildUriFromString(self::MOCK_CUSTOM_PORT_HTTPS, true, '800');
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
        $uriComplex  = Uri::buildUriFromString(self::MOCK_PATH_COMPLEX);
        $uriCustom   = Uri::buildUriFromString(self::MOCK_PATH_CUSTOM);
        $uriEmpty    = Uri::buildUriFromString(self::MOCK_PATH_EMPTY);
        $uriMultiple = Uri::buildUriFromString(self::MOCK_PATH_MULTIPLE);
        $uriSpace    = Uri::buildUriFromString(self::MOCK_PATH_SPACE);
        $uriSlash    = Uri::buildUriFromString(self::MOCK_PATH_SLASH);
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
        $uriWithoutPort                = Uri::buildUriFromString(self::MOCK_USER_PSWRD);
        $uriWithPort                   = Uri::buildUriFromString(self::MOCK_CUST_PORT);
        $uriWithoutUserInfoWithoutPort = Uri::buildUriFromString(self::MOCK_HTTPS_REQUEST);
        $uriWithoutUserInfo            = Uri::buildUriFromString(self::MOCK_CUSTOM_OTHER_PORT_HTTPS);
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
        $uriCustom = Uri::buildUriFromString(self::MOCK_CUST_PORT);
        $uriBasic  = Uri::buildUriFromString(self::MOCK_BASIC_URI_REQUEST);
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
        $uriSpaces = Uri::buildUriFromString(self::MOCK_PATH_SPACE_ARGS);
        $uriBasic  = Uri::buildUriFromString(self::MOCK_BASIC_URI_REQUEST);
        $this->assertEquals('var1=hello%20this%20is%20space&var2=world', $uriSpaces->getQuery());
        $this->assertEquals('arg1=hello&arg2=world', $uriBasic->getQuery());
        $this->assertEquals('arg5=helloworld', $uriBasic->withQuery('arg5=helloworld')->getQuery());
        $this->assertEquals('arg1=hello&arg2=world', $uriBasic->getQuery());
    }

    /**
     * @Covers Uri::buildUriFromRequest
     */
    public function testBuildUriFromRequest()
    {
        $uri = Uri::buildUriFromRequest($this->MOCK_SERVER);
        $this->assertEquals('http', $uri->getScheme());
        $this->assertEquals('hello=world', $uri->getQuery());
        $this->assertEquals('/test/hello/world.php', $uri->getPath());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Error, $_SERVER is null or empty
     */
    public function testBuildUriFromRequestNullException()
    {
        $uri = Uri::buildUriFromRequest([]);
    }

    /**
     * @Covers Uri::buildUriFromArray
     */
    public function testBuildUriFromArray()
    {
        $uriHttp  = Uri::buildUriFromArray($this->MOCK_URI_ARRAY_HTTP);
        $uriHttps = Uri::buildUriFromArray($this->MOCK_URI_ARRAY_HTTPS);
        $this->assertEquals('http', $uriHttp->getScheme());
        $this->assertEquals('hello=world&test=123', $uriHttp->getQuery());
        $this->assertEquals('', $uriHttp->getPort());
        $this->assertEquals('https', $uriHttps->getScheme());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Error, provided URI arguments array is empty
     */
    public function testBuildUriFromArrayException()
    {
        $uri = Uri::buildUriFromArray([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Error, provided URI string is null or empty
     */
    public function testbuildUriFromStringExceptionNullString()
    {
        $uri = Uri::buildUriFromString('');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Error, provided URI is not properly formatted / cannot be parsed
     */
    public function testbuildUriFromStringExceptionInvalidUriString()
    {
        $uri = Uri::buildUriFromString('//hellouser:@/world');
    }

    /**
     * @Covers Uri::unparseUri
     */
    public function testUnparseUri()
    {
        $this->assertEquals(self::MOCK_PATH_COMPLEX, Uri::unparseUri(parse_url(self::MOCK_PATH_COMPLEX)));
        $this->assertEquals(self::MOCK_PATH_CUSTOM, Uri::unparseUri(parse_url(self::MOCK_PATH_CUSTOM)));
        $this->assertEquals(self::MOCK_PATH_EMPTY, Uri::unparseUri(parse_url(self::MOCK_PATH_EMPTY)));
        $this->assertEquals(self::MOCK_PATH_MULTIPLE, Uri::unparseUri(parse_url(self::MOCK_PATH_MULTIPLE)));
        $this->assertEquals(self::MOCK_PATH_SPACE, Uri::unparseUri(parse_url(self::MOCK_PATH_SPACE)));
        $this->assertEquals(self::MOCK_PATH_SLASH, Uri::unparseUri(parse_url(self::MOCK_PATH_SLASH)));
    }

    /**
     * @Covers Uri::httpBuildQuery
     */
    public function testHttpBuildQuery()
    {
        $aQuery       = http_build_query(['hello' => 'world']);
        $aQueryActual = Uri::httpBuildQuery(['hello' => 'world']);
        $this->assertEquals($aQuery, $aQueryActual);
    }

    /**
     * @Covers Uri::getFragment
     * @Covers Uri::withFragment
     */
    public function testFragment()
    {
        $uriBasic = Uri::buildUriFromString(self::MOCK_BASIC_URI_REQUEST);
        $uriHttp  = Uri::buildUriFromArray($this->MOCK_URI_ARRAY_HTTP);
        $this->assertEquals('fragment', $uriBasic->getFragment());
        $this->assertEquals('', $uriHttp->getFragment());
        $this->assertEquals('hello', $uriHttp->withFragment('hello')->getFragment());
        $this->assertEquals('', $uriHttp->getFragment());
    }

    /**
     * @Covers Uri::withUserInfo
     * @Covers Uri::getAuthority
     */
    public function testWithUserInfo()
    {
        $uriAuthPort        = Uri::buildUriFromString(self::MOCK_CUST_PORT);
        $uriAuthWithoutPort = Uri::buildUriFromString(self::MOCK_USER_PSWRD);
        $uriBasic           = Uri::buildUriFromString(self::MOCK_BASIC_URI_REQUEST);
        $this->assertEquals('username:password@hostname:9090', $uriAuthPort->getAuthority());
        $this->assertEquals('username:password@example.com', $uriAuthWithoutPort->getAuthority());
        $this->assertEquals('www.test.com', $uriBasic->getAuthority());
        $this->assertEquals('john:azerty123@hostname:9090',
            $uriAuthPort->withUserInfo('john', 'azerty123')->getAuthority());
        $this->assertEquals('example.com', $uriAuthWithoutPort->withUserInfo('')->getAuthority());
        $this->assertEquals('paul:password123@www.test.com',
            $uriBasic->withUserInfo('paul', 'password123')->getAuthority());
        $this->assertEquals('www.test.com', $uriBasic->getAuthority());
    }

    /**
     * @Covers Uri::__toString()
     */
    public function testToString()
    {
        $uriAuthPort        = Uri::buildUriFromString(self::MOCK_CUST_PORT);
        $uriAuthWithoutPort = Uri::buildUriFromString(self::MOCK_USER_PSWRD);
        $uriBasic           = Uri::buildUriFromString(self::MOCK_BASIC_URI_REQUEST);
        $this->assertEquals(strtolower(self::MOCK_CUST_PORT), (string)$uriAuthPort);
        $this->assertEquals(strtolower(self::MOCK_USER_PSWRD), (string)$uriAuthWithoutPort);
        $this->assertEquals(strtolower(self::MOCK_BASIC_URI_REQUEST), (string)$uriBasic);
    }

}