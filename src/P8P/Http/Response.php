<?php
/**
 * P8P Framework - https://github.com/hpolaert/p8p
 *
 * @link      https://github.com/hpolaert/p8p
 * @copyright Copyright (c) 2016 Hugues Polaert
 * @license   https://github.com/hpolaert/p8p/LICENCE.md (MIT)
 */

namespace P8P\Http;

use Psr\Http\Message\ResponseInterface;

/**
 * P8P - Basic implementation of PSR-7 Response
 *
 * Representation of an outgoing, server-side response.
 *
 * Per the HTTP specification, this interface includes properties for
 * each of the following:
 *
 * - Protocol version
 * - Status code and reason phrase
 * - Headers
 * - Message body
 *
 * Responses are considered immutable; all methods that might change state MUST
 * be implemented such that they retain the internal state of the current
 * message and return an instance that contains the changed state.
 */
class Response extends Message implements ResponseInterface
{
	
	/** 
	 * @var array Standard HTTP Response Codes / reasons 
	 */
	protected $httpResponseStatus = [
			100 => 'Continue',
			101 => 'Switching Protocols',
			102 => 'Processing',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			207 => 'Multi-status',
			208 => 'Already Reported',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			306 => 'Switch Proxy',
			307 => 'Temporary Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Time-out',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Large',
			415 => 'Unsupported Media Type',
			416 => 'Requested range not satisfiable',
			417 => 'Expectation Failed',
			418 => 'I\'m a teapot',
			422 => 'Unprocessable Entity',
			423 => 'Locked',
			424 => 'Failed Dependency',
			425 => 'Unordered Collection',
			426 => 'Upgrade Required',
			428 => 'Precondition Required',
			429 => 'Too Many Requests',
			431 => 'Request Header Fields Too Large',
			451 => 'Unavailable For Legal Reasons',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Time-out',
			505 => 'HTTP Version not supported',
			506 => 'Variant Also Negotiates',
			507 => 'Insufficient Storage',
			508 => 'Loop Detected',
			511 => 'Network Authentication Required',
	];
	
	/**
	 * @var string Current reason of the response object
	 */
	protected $reasonPhrase = '';
	
	/**
	 * @var array Current HTTP Status Code 
	 */
	protected $statusCode = 200;	
	
    /**
     * Static constructor
     *
     * Instantiate the response with the protocol version, status code,
     * reason phrase, headers and message body
     *
     * @param int                                  $status  Status code
     * @param array                                $headers Response headers
     * @param string|null|resource|StreamInterface $body    Response body
     * @param string                               $version Protocol version
     * @param string|null                          $reason  Reason phrase (when empty a default will be used based on the status code)
     * 
     * @throws \InvalidArgumentException 
     * return Response
     */
	public function __construct(
		$status = 200,
		array $headers = [],
		$body = null,
		$protocol = '1.1',
		$reason = null
		) {
		 $this->checkReasonPhraseValidity($reason);
		 $this->checkReasonPhraseValidity($status);
		 if(!is_resource($body) || !is_string($body) || is_null($body)){
		 	throw new \InvalidArgumentException('Body is not a valid resource');
		 }
		 if(!is_array($headers)){
		 	throw new \InvalidArgumentException('Headers must be passed as an array');
		 }
		 if(!is_string($protocol)){
		 	throw new \InvalidArgumentException('HTTP Protocol must be a string');
		 }
		 return new static($status, $headers, $body, $protocol, $reason);
	}

	
	/**
	 * Constructor
	 *
	 * Instantiate the response with the protocol version, status code,
	 * reason phrase, headers and message body
	 *
	 * @param int                                  $status  Status code
	 * @param array                                $headers Response headers
	 * @param string|null|resource|StreamInterface $body    Response body
	 * @param string                               $version Protocol version
	 * @param string|null                          $reason  Reason phrase (when empty a default will be used based on the status code)
	 */
	public function __construct($status = 200,
		array $headers = [],
		StreamInterface $body = null,
		$protocol = '1.1',
		$reason = null){
		$this->headers = $headers;
		$this->body = $body;
		$this->reasonPhrase = $reason;
		$this->currentHttpProtocolVersion = $protocol;
	}
	

    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode(){
    	return $this->statusCode;
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * If no reason phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * status code.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * updated status and reason phrase.
     *
     * @see http://tools.ietf.org/html/rfc7231#section-6
     * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     *
     * @param int    $code         The 3-digit integer result code to set.
     * @param string $reasonPhrase The reason phrase to use with the
     *                             provided status code; if none is provided, implementations MAY
     *                             use the defaults as suggested in the HTTP specification.
     *
     * @return self
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = ''){
    	$this->checkStatusCodeValidity($code);
    	$this->checkReasonPhraseValidity($reasonPhrase);
    	$clone = clone $this;
    	$clone->statusCode = $code;
    	$clone->reasonPhrase = ($reasonPhrase == '' 
    			&& isset($this->httpResponseStatus[$code])) ? $this->httpResponseStatus[$code] : $reasonPhrase;
    	return $clone;
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be empty. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * status code.
     *
     * @see http://tools.ietf.org/html/rfc7231#section-6
     * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string Reason phrase; must return an empty string if none present.
     */
    public function getReasonPhrase(){
    	return $this->reasonPhrase;
    }
    
    /*********************************************
     * UTILITY - NOT PART OF PSR7 STANDARD
     *********************************************/
    /**
     * Check status code validity
     * [Not part of PSR-7 Standard]
     *
     * @param unknown $code
     * @throws \InvalidArgumentException
     */
    private function checkStatusCodeValidity($code){
    	if(!isset($this->httpResponseStatus[$code])){
    		throw new \InvalidArgumentException('Status code does not exist');
    	}
    	if(!is_int($this->httpResponseStatus[$code])){
    		throw new \InvalidArgumentException('Status code is not an integer');
    	}
    }
    
    /**
     * Check reason phrase validity
     * [Not part of PSR-7 Standard]
     *
     * @param unknown $reasonPhrase
     * @throws \InvalidArgumentException
     */
    private function checkReasonPhraseValidity($reasonPhrase){
    	if(!is_string($this->httpResponseStatus[$reasonPhrase])){
    		throw new \InvalidArgumentException('Reason phrase is not a string');
    	}
    }
    
    public function isClientError(){
    	
    }
    
    public function isServerError(){
    	 
    }
    
    public function notAllowed(){
    
    }
    
    public function isOk(){
    
    }
    
    public function isNotOk(){
    
    }
    
    public function withRedirect(){
    	
    }
    
    public function withXML(){
    	 
    }
    
    public function withJSON(){
    	 
    }
}