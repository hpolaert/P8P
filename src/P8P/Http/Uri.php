<?php
/**
 * P8P Framework - https://github.com/hpolaert/p8p
 *
 * @link      https://github.com/hpolaert/p8p
 * @copyright Copyright (c) 2016 Hugues Polaert
 * @license   https://github.com/hpolaert/p8p/LICENCE.md (MIT)
 */

namespace P8P\Http;

use Psr\Http\Message\UriInterface;

/**
 * Value object representing a URI.
 *
 * P8P - Basic implementation of PSR-7 URI interface
 *
 * This interface is meant to represent URIs according to RFC 3986 and to
 * provide methods for most common operations. Additional functionality for
 * working with URIs can be provided on top of the interface or externally.
 * Its primary use is for HTTP requests, but may also be used in other
 * contexts.
 *
 * Instances of this interface are considered immutable; all methods that
 * might change state MUST be implemented such that they retain the internal
 * state of the current instance and return an instance that contains the
 * changed state.
 *
 * Typically the Host header will be also be present in the request message.
 * For server-side requests, the scheme will typically be discoverable in the
 * server parameters.
 *
 * @see http://tools.ietf.org/html/rfc3986 (the URI specification)
 */
class Uri implements UriInterface
{
    /**
     * @var URI scheme
     */
    protected $uriScheme;
    /**
     * @var URI user
     */
    protected $uriUser;
    /**
     * @var URI password
     */
    protected $uriPassword;
    /**
     * @var URI host
     */
    protected $uriHost;
    /**
     * @var URI port
     */
    protected $uriPort;
    /**
     * @var URI path
     */
    protected $uriPath;
    /**
     * @var URI query
     */
    protected $uriQuery;
    /**
     * @var URI fragment
     */
    protected $uriFragment;

    /**
     * ----------------------------------------------
     * P8P Specific URI Methods
     * ----------------------------------------------
     */


    /**
     * Build URI from the current server request
     *
     * Fetch and assign each component of server request URI
     *
     * @param array|false $useForwardedHost In case the URI goes through a proxy (used to forward the original host)
     * @param string|null $customHttpsPort  In case a custom HTTPS has been defined
     *
     * @throws \RuntimeException
     * @return self
     */
    public static function buildUriFromServerRequest(bool $useForwardedHost = false, string $customHttpsPort = null)
    {
        // Current URL
        $req = $_SERVER;
        if (empty($req)) {
            throw new \RuntimeException('Error, $_SERVER is null or empty');
        }
        // Fetch scheme
        $https          = !empty($req['HTTPS']) && $req['HTTPS'] === 'on';
        $serverProtocol = $req['SERVER_PROTOCOL'] ?? '';
        $scheme         = $serverProtocol . ($https ? 's' : '');
        // Fetch port (if default port is used, assign empty string)
        $port               = $req['SERVER_PORT'] ?? '';
        $isHttpDefaultPort  = !$https && $port === '80';
        $isHttpsDefaultPort = $https && $port === '443';
        $isHttpsDefaultPort = !is_null($customHttpsPort) ? ($https && $port === $customHttpsPort) : $isHttpsDefaultPort;
        $port               = ($isHttpDefaultPort || $isHttpsDefaultPort) ? '' : $port;
        // Fetch user
        $user = $req['PHP_AUTH_USER'];
        $pass = $req['PHP_AUTH_PW'];
        // Fetch host
        $forwardedHost = $useForwardedHost && isset($req['HTTP_X_FORWARDED_HOST']);
        $basicHost     = $req['HTTP_HOST'] ?? null;
        $host          = $forwardedHost ? $req['HTTP_X_FORWARDED_HOST'] : $basicHost;
        $host          = $host ?? $req['SERVER_NAME'];
        // Fetch query string
        $query = $req['QUERY_STRING'] ?? '';
        // Fetch path
        $path = parse_url('http://url.com' . $req['REQUEST_URI'], PHP_URL_PATH);
        // Fragment cannot be fetched in php
        $fragment = '';
        return new static(compact($scheme, $user, $pass, $port, $path, $host, $query, $fragment), $https,
            $customHttpsPort);
    }

    /**
     * Build URI from an associative array
     *
     * @param array       $uriArguments     Build an URI from an associative array, which indexes are :
     *                                      scheme, user, password, port, host, path, query, fragment
     * @param array|false $useForwardedHost In case the URI goes through a proxy (used to forward the original host)
     * @param string|null $customHttpsPort  In case a custom HTTPS has been defined
     *
     * @throws \InvalidArgumentException
     * @return self
     */
    public static function buildUriFromArray(
        array $uriArguments,
        bool $useForwardedHost = false,
        string $customHttpsPort = null
    ) {
        // Make sure the array has at least one value
        if (empty($uriArguments)) {
            throw new \InvalidArgumentException('Error, provided URI arguments array is empty');
        }
        // Build URI from array
        return new static($uriArguments, $useForwardedHost, $customHttpsPort);
    }

    /**
     * Build an URI from a given string
     *
     * @param string      $uri              URI as string
     * @param array|false $useForwardedHost In case the URI goes through a proxy (used to forward the original host)
     * @param string|null $customHttpsPort  In case a custom HTTPS has been defined
     *
     * @throws \InvalidArgumentException
     * @return self
     */
    public static function buildUriFromSting(
        string $uri,
        bool $useForwardedHost = false,
        string $customHttpsPort = null
    ) {
        // Make sure the array has at least one value
        if (!isset($uri) || trim($uri) === '') {
            throw new \InvalidArgumentException('Error, provided URI string is null or empty');
        }
        $parsedUri = parse_url($uri);
        // Check if URI is properly formatted
        if ($parsedUri === false) {
            throw new \InvalidArgumentException('Error, provided URI is not properly formatted / cannot be parsed');
        }
        // Build URI from parsed string
        return new static($parsedUri, $useForwardedHost, $customHttpsPort);
    }

    /**
     * Build URI - Main constructor
     *
     * Assign each URI component to the current URI object
     *
     * @param array       $parsedUri
     * @param array|false $useForwardedHost In case the URI goes through a proxy (used to forward the original host)
     * @param string|null $customHttpsPort  In case a custom HTTPS has been defined
     *
     * @return void
     */
    public function __construct(
        array $parsedUri,
        bool $useForwardedHost = false,
        string $customHttpsPort = null
    ) {
        // Build URI from parsed string URI
        $this->uriScheme   = $this->parseScheme(($parsedUri['scheme']) ?? '');
        $this->uriUser     = $parsedUri['user'] ?? '';
        $this->uriPassword = $parsedUri['pass'] ?? '';
        $this->uriPort     = isset($parsedUri['port']) ? $this->parsePort($parsedUri['port'],
            $this->uriScheme === 'https', $customHttpsPort) : '';
        $this->uriHost     = $parsedUri['host'] ?? '';
        $this->uriPath     = $this->urlEncode($parsedUri['path'] ?? '/');
        $this->uriQuery    = $this->urlEncode($parsedUri['query'] ?? '');
        $this->uriFragment = $this->urlEncode($parsedUri['fragment'] ?? '');
    }

    /**
     * ----------------------------------------------
     * P8P URI utilities
     * ----------------------------------------------
     */

    /**
     * Unparse URI
     *
     * Rebuild an URI string from a parsed URL
     * Used to "serialize" the current object into a string
     *
     * @param array $uriArguments Parsed URL
     *
     * @return string URI as a string
     */
    public static function unparseUri(Array $uriArguments) : string
    {
        // Extract each URI parameters
        $uriStringOutput = isset($uriArguments['scheme']) ? $uriArguments['scheme'] . '://' : '';
        $uriStringOutput .= $uriArguments['host'] ?? '';
        $uriStringOutput .= isset($uriArguments['port']) ? ':' . $uriArguments['port'] : '';
        $uriStringOutput .= $uriArguments['user'] ?? '';
        $uriStringOutput .= isset($uriArguments['pass']) ? ':' . $uriArguments['pass'] : '';
        $uriStringOutput .= ($uriArguments['user'] || $uriArguments['pass']) ? '@' : '';
        $uriStringOutput .= isset($uriArguments['path']) ? $uriArguments['path'] : '';
        $uriStringOutput .= isset($uriArguments['query']) ? '?' . $uriArguments['query'] : '';
        $uriStringOutput .= isset($uriArguments['fragment']) ? '#' . $uriArguments['fragment'] : '';
        return $uriStringOutput;
    }

    /**
     * Generates a URL-encoded query string from the associative (or indexed) array provided
     *
     * Alias to PHP http_build_query method
     *
     * @param mixed       $queryData     Object or an array
     * @param string|null $numericPrefix Prepend each index of the query with a numeric index
     * @param string|null $argSeparator  Override the default php argument separator (?)
     * @param int|null    $encType       PHP_QUERY_RFC1738 or PHP_QUERY_RFC3986 encoding
     *
     * @return String
     */
    public static function httpBuildQuery(
        $queryData,
        string $numericPrefix = null,
        string $argSeparator = null,
        int $encType = null
    ) : String
    {
        // Alias to http_build_query PHP function
        return http_build_query($queryData, $numericPrefix, $argSeparator, $encType);
    }

    /**
     * Parse Scheme
     *
     * Utility method to check the validity of a scheme
     *
     * @param string $scheme Scheme to be analysed and filtered
     *
     * @return string Filtered scheme
     */
    protected function parseScheme(string $scheme) : string
    {
        $scheme = strtolower(str_replace('://', '', $scheme));
        if (!in_array($scheme, ['http', 'https', ''])) {
            throw new \InvalidArgumentException('Error, scheme must be either http, https or an empty string');
        }
        return $scheme;
    }

    /**
     * Parse Port
     *
     * Utility method to check the validity of a port
     *
     * @param string      $scheme           Port to be analysed and filtered
     * @param array|false $useForwardedHost In case the URI goes through a proxy (used to forward the original host)
     * @param string|null $customHttpsPort  In case a custom HTTPS has been defined
     *
     * @return string Filtered port
     */
    protected function parsePort(
        string $port,
        bool $https = false,
        string $customHttpsPort = null
    ) : string
    {
        if ($port !== '' && !preg_match('/^[\d]{1,5}$/', $port)) {
            throw new \InvalidArgumentException('Error, port length must be between 1 and 5 digits');
        }
        $isHttpDefaultPort  = !$https && $port === '80';
        $isHttpsDefaultPort = $https && $port === '443';
        $isHttpsDefaultPort = !is_null($customHttpsPort) ? ($https && $port === $customHttpsPort) : $isHttpsDefaultPort;
        $port               = ($isHttpDefaultPort || $isHttpsDefaultPort) ? '' : $port;
        return $port;
    }

    /**
     * URL Encode
     *
     * Utility method to rawurlencode each part of an url without encoding slashes
     *
     * @param string $url
     *
     * @return string Reformatted URL
     */
    protected function urlEncode(string $url) : string
    {
        return str_replace(' ', '%20', $url);
    }

    /**
     * ----------------------------------------------
     * PSR-7 Implementations
     * ----------------------------------------------
     */

    /**
     * Retrieve the scheme component of the URI.
     *
     * If no scheme is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.1.
     *
     * The trailing ":" character is not part of the scheme and MUST NOT be
     * added.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.1
     * @return string The URI scheme.
     */
    public function getScheme()
    {
        return $this->uriScheme;
    }

    /**
     * Retrieve the authority component of the URI.
     *
     * If no authority information is present, this method MUST return an empty
     * string.
     *
     * The authority syntax of the URI is:
     *
     * <pre>
     * [user-info@]host[:port]
     * </pre>
     *
     * If the port component is not set or is the standard port for the current
     * scheme, it SHOULD NOT be included.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-3.2
     * @return string The URI authority, in "[user-info@]host[:port]" format.
     */
    public function getAuthority()
    {
        $authority = $this->getUserInfo() ? $this->getUserInfo() . '@' : '';
        $authority .= $this->uriHost;
        $authority .= $this->uriPort !== '' ? ':' . $this->uriPort : '';
        return $authority;
    }

    /**
     * Retrieve the user information component of the URI.
     *
     * If no user information is present, this method MUST return an empty
     * string.
     *
     * If a user is present in the URI, this will return that value;
     * additionally, if the password is also present, it will be appended to the
     * user value, with a colon (":") separating the values.
     *
     * The trailing "@" character is not part of the user information and MUST
     * NOT be added.
     *
     * @return string The URI user information, in "username[:password]" format.
     */
    public function getUserInfo()
    {
        return $this->uriUser . ($this->uriPassword ? ':' . $this->uriPassword : '');
    }

    /**
     * Retrieve the host component of the URI.
     *
     * If no host is present, this method MUST return an empty string.
     *
     * The value returned MUST be normalized to lowercase, per RFC 3986
     * Section 3.2.2.
     *
     * @see http://tools.ietf.org/html/rfc3986#section-3.2.2
     * @return string The URI host.
     */
    public function getHost()
    {
        return $this->uriHost;
    }

    /**
     * Retrieve the port component of the URI.
     *
     * If a port is present, and it is non-standard for the current scheme,
     * this method MUST return it as an integer. If the port is the standard port
     * used with the current scheme, this method SHOULD return null.
     *
     * If no port is present, and no scheme is present, this method MUST return
     * a null value.
     *
     * If no port is present, but a scheme is present, this method MAY return
     * the standard port for that scheme, but SHOULD return null.
     *
     * @return null|int The URI port.
     */
    public function getPort()
    {
        return $this->uriPort;
    }

    /**
     * Retrieve the path component of the URI.
     *
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     *
     * Normally, the empty path "" and absolute path "/" are considered equal as
     * defined in RFC 7230 Section 2.7.3. But this method MUST NOT automatically
     * do this normalization because in contexts with a trimmed base path, e.g.
     * the front controller, this difference becomes significant. It's the task
     * of the user to handle both "" and "/".
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.3.
     *
     * As an example, if the value should include a slash ("/") not intended as
     * delimiter between path segments, that value MUST be passed in encoded
     * form (e.g., "%2F") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.3
     * @return string The URI path.
     */
    public function getPath()
    {
        return $this->uriPath;
    }

    /**
     * Retrieve the query string of the URI.
     *
     * If no query string is present, this method MUST return an empty string.
     *
     * The leading "?" character is not part of the query and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.4.
     *
     * As an example, if a value in a key/value pair of the query string should
     * include an ampersand ("&") not intended as a delimiter between values,
     * that value MUST be passed in encoded form (e.g., "%26") to the instance.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.4
     * @return string The URI query string.
     */
    public function getQuery()
    {
        return $this->uriQuery;
    }

    /**
     * Retrieve the fragment component of the URI.
     *
     * If no fragment is present, this method MUST return an empty string.
     *
     * The leading "#" character is not part of the fragment and MUST NOT be
     * added.
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.5.
     *
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.5
     * @return string The URI fragment.
     */
    public function getFragment()
    {
        return $this->uriFragment;
    }

    /**
     * Return an instance with the specified scheme.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified scheme.
     *
     * Implementations MUST support the schemes "http" and "https" case
     * insensitively, and MAY accommodate other schemes if required.
     *
     * An empty scheme is equivalent to removing the scheme.
     *
     * @param string $scheme The scheme to use with the new instance.
     *
     * @return self A new instance with the specified scheme.
     * @throws \InvalidArgumentException for invalid schemes.
     * @throws \InvalidArgumentException for unsupported schemes.
     */
    public function withScheme($scheme)
    {
        $scheme           = $this->parseScheme($scheme);
        $clone            = clone $this;
        $clone->uriScheme = $scheme;
        return $clone;
    }

    /**
     * Return an instance with the specified user information.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified user information.
     *
     * Password is optional, but the user information MUST include the
     * user; an empty string for the user is equivalent to removing user
     * information.
     *
     * @param string      $user     The user name to use for authority.
     * @param null|string $password The password associated with $user.
     *
     * @return self A new instance with the specified user information.
     */
    public function withUserInfo($user, $password = null)
    {
        $clone              = clone $this;
        $clone->uriUser     = $user;
        $clone->uriPassword = $password ?? '';
        return $clone;
    }

    /**
     * Return an instance with the specified host.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified host.
     *
     * An empty host value is equivalent to removing the host.
     *
     * @param string $host The hostname to use with the new instance.
     *
     * @return self A new instance with the specified host.
     * @throws \InvalidArgumentException for invalid hostnames.
     */
    public function withHost($host)
    {
        $clone          = clone $this;
        $clone->uriHost = $host;
        return $clone;
    }

    /**
     * Return an instance with the specified port.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified port.
     *
     * Implementations MUST raise an exception for ports outside the
     * established TCP and UDP port ranges.
     *
     * A null value provided for the port is equivalent to removing the port
     * information.
     *
     * @param null|int $port The port to use with the new instance; a null value
     *                       removes the port information.
     *
     * @return self A new instance with the specified port.
     * @throws \InvalidArgumentException for invalid ports.
     */
    public function withPort($port)
    {
        $port           = $this->parsePort($port);
        $clone          = clone $this;
        $clone->uriPort = $port;
        return $clone;
    }

    /**
     * UtilityMethod to check default ports (not part of PSR7)
     *
     * Return an instance with the specified port.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified port.
     *
     * Implementations MUST raise an exception for ports outside the
     * established TCP and UDP port ranges.
     *
     * A null value provided for the port is equivalent to removing the port
     * information.
     *
     * @param null|int    $port            The port to use with the new instance; a null value
     *                                     removes the port information.
     * @param string|null $customHttpsPort In case a custom HTTPS has been defined
     *
     * @return self A new instance with the specified port.
     * @throws \InvalidArgumentException for invalid ports.
     */
    public function withCustomHttpsPort($port, $customHttpsPort = 443)
    {
        $port           = $this->parsePort($port, $this->getScheme() === 'https', $customHttpsPort);
        $clone          = clone $this;
        $clone->uriPort = $port;
        return $clone;
    }

    /**
     * Return an instance with the specified path.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified path.
     *
     * The path can either be empty or absolute (starting with a slash) or
     * rootless (not starting with a slash). Implementations MUST support all
     * three syntaxes.
     *
     * If an HTTP path is intended to be host-relative rather than path-relative
     * then it must begin with a slash ("/"). HTTP paths not starting with a slash
     * are assumed to be relative to some base path known to the application or
     * consumer.
     *
     * Users can provide both encoded and decoded path characters.
     * Implementations ensure the correct encoding as outlined in getPath().
     *
     * @param string $path The path to use with the new instance.
     *
     * @return self A new instance with the specified path.
     * @throws \InvalidArgumentException for invalid paths.
     */
    public function withPath($path)
    {
        $path           = $this->urlEncode($path);
        $clone          = clone $this;
        $clone->uriPath = $path;
        return $clone;
    }

    /**
     * Return an instance with the specified query string.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified query string.
     *
     * Users can provide both encoded and decoded query characters.
     * Implementations ensure the correct encoding as outlined in getQuery().
     *
     * An empty query string value is equivalent to removing the query string.
     *
     * @param string $query The query string to use with the new instance.
     *
     * @return self A new instance with the specified query string.
     * @throws \InvalidArgumentException for invalid query strings.
     */
    public function withQuery($query)
    {
        $query           = $this->urlEncode($query);
        $clone           = clone $this;
        $clone->uriQuery = $query;
        return $clone;
    }

    /**
     * Return an instance with the specified URI fragment.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified URI fragment.
     *
     * Users can provide both encoded and decoded fragment characters.
     * Implementations ensure the correct encoding as outlined in getFragment().
     *
     * An empty fragment value is equivalent to removing the fragment.
     *
     * @param string $fragment The fragment to use with the new instance.
     *
     * @return self A new instance with the specified fragment.
     */
    public function withFragment($fragment)
    {
        $fragment           = $this->urlEncode($fragment);
        $clone              = clone $this;
        $clone->uriFragment = $fragment;
        return $clone;
    }

    /**
     * Return the string representation as a URI reference.
     *
     * Depending on which components of the URI are present, the resulting
     * string is either a full URI or relative reference according to RFC 3986,
     * Section 4.1. The method concatenates the various components of the URI,
     * using the appropriate delimiters:
     *
     * - If a scheme is present, it MUST be suffixed by ":".
     * - If an authority is present, it MUST be prefixed by "//".
     * - The path can be concatenated without delimiters. But there are two
     *   cases where the path has to be adjusted to make the URI reference
     *   valid as PHP does not allow to throw an exception in __toString():
     *     - If the path is rootless and an authority is present, the path MUST
     *       be prefixed by "/".
     *     - If the path is starting with more than one "/" and no authority is
     *       present, the starting slashes MUST be reduced to one.
     * - If a query is present, it MUST be prefixed by "?".
     * - If a fragment is present, it MUST be prefixed by "#".
     *
     * @see http://tools.ietf.org/html/rfc3986#section-4.1
     * @return string
     */
    public function __toString()
    {
        // Extract each URI parameters
        $uriArray['scheme']   = $this->parseScheme($this->uriScheme);
        $uriArray['host']     = $this->uriHost;
        $uriArray['user']     = $this->uriUser;
        $uriArray['pass']     = $this->uriPassword;
        $uriArray['port']     = $this->parsePort($this->uriPort, $this->parseScheme($this->uriScheme) === 'https');
        $uriArray['path']     = $this->urlEncode($this->uriPath);
        $uriArray['query']    = $this->urlEncode($this->uriQuery);
        $uriArray['fragment'] = $this->urlEncode($this->uriFragment);
        return self::unparseUri($uriArray);
    }
}