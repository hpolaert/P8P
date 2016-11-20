<?php
/**
 * P8P Framework - https://github.com/hpolaert/p8p
 *
 * @link      https://github.com/hpolaert/p8p
 * @copyright Copyright (c) 2016 Hugues Polaert
 * @license   https://github.com/hpolaert/p8p/LICENCE.md (MIT)
 */

namespace P8P\Http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * P8P - Basic implementation of PSR-7 Stream interface
 *
 * Describes a data stream.
 *
 * Typically, an instance will wrap a PHP stream; this interface provides
 * a wrapper around the most common operations, including serialization of
 * the entire stream to a string.
 */
class Stream implements StreamInterface
{
    	
	/**
	 * @var resource	Stream resource
	 */
	protected $stream;
	
	/**
	 * @var boolean		Is stream seekable
	 */
	protected $isSeekable;
	
	/**
	 * @var boolean		Is stream readable
	 */
	protected $isReadable;
	
	/**
	 * @var boolean		Is stream writable
	 */
	protected $isWritable;
	
	/**
	 * @var null|int	Stream size
	 */
	protected $streamSize;
	
	/**
	 * @var array		Stream metadata 
	 */
	protected $metadata = [];
	
	/** 
	 * @var array Hash of readable and writable stream types 
	 */
	protected $readWriteHash = [
			'read' => [
					'r' => true, 'w+' => true, 'r+' => true, 'x+' => true, 'c+' => true,
					'rb' => true, 'w+b' => true, 'r+b' => true, 'x+b' => true,
					'c+b' => true, 'rt' => true, 'w+t' => true, 'r+t' => true,
					'x+t' => true, 'c+t' => true, 'a+' => true,
			],
			'write' => [
					'w' => true, 'w+' => true, 'rw' => true, 'r+' => true, 'x+' => true,
					'c+' => true, 'wb' => true, 'w+b' => true, 'r+b' => true,
					'x+b' => true, 'c+b' => true, 'w+t' => true, 'r+t' => true,
					'x+t' => true, 'c+t' => true, 'a' => true, 'a+' => true,
			],
	];
	
    /**
     * Constructor
     * 
     * Instantiate the stream object 
     * [Not part of PSR7 Standard]
     * 
     * @param resource $stream PHP Resource object
     * 
     * @return void
     */
    public function __construct($stream){
    	if($this->isValidResource($stream, __METHOD__)){
    		$this->stream = $stream;
    		$this->setStreamReadAndWriteAttributes();
    	}
    }
    
    /**
     * Is Stream Attached
     *
     * Check if the current stream is a valid resource
     * [Not part of PSR7 Standard]
     *
     * @return boolean
     */
    public function isStreamAttached(){
    	return is_resource($this->stream);
    }
    
    /**
     * Attach a stream to the current object
     *
     * Check if the current stream is a valid resource
     * [Not part of PSR7 Standard]
	 *
	 * @param resource $newStream PHP Resource object
     *
     * @return boolean
     */
    public function attachStream($newStream){
		if ($this->isValidResource($newStream, __METHOD__)) {
    		if($this->isStreamAttached() === true){
				$this->detach();
    		}
    		$this->stream = $newStream;
    		$this->setStreamReadAndWriteAttributes();
    	} 
    }
    
    /**
     * Set stream readable and writable attributes
     *
     * Check if the current stream is a valid resource
     * [Not part of PSR7 Standard]
     *
     * @param resource $stream PHP Resource object
     *
     * @return boolean
     */
    public function setStreamReadAndWriteAttributes(){
    	if($this->isStreamAttached() === true){
    		$mode = $this->getMetadata('mode');
    		$this->isReadable = isset($this->readWriteHash['read'][$mode]);
    		$this->isWritable = isset($this->readWriteHash['write'][$mode]);
    	}
    }
    
    /**
     * Is Valid Resource
     *
     * Check if the provided object is a valid resource
     * [Not part of PSR7 Standard]
     *
     * @param resource $stream PHP Resource object
     * @param string $method
	 *
	 * @throws \InvalidArgumentException
     * @return boolean
     */
    public function isValidResource($stream, $method){
    	if (is_resource($stream) === false) {
    		throw new InvalidArgumentException($method . ' argument must be a valid PHP resource');
    	}
    	return true;
    }
    
    /**
     * Output stream as string
     *
     * Reads all data from the stream into a string, from the beginning to end.
     *
     * This method MUST attempt to seek to the beginning of the stream before
     * reading data and read the stream until the end is reached.
     *
     * Warning: This could attempt to load a large amount of data into memory.
     *
     * This method MUST NOT raise an exception in order to conform with PHP's
     * string casting operations.
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#object.tostring
     * @return string
     */
    public function __toString(){
    	if (!$this->isStreamAttached()) {
    		return '';
    	}
    	try {
    		$this->rewind();
    		return $this->getContents();
    	} catch (RuntimeException $e) {
    		return '';
    	}
    }

    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function close(){
		if ($this->isStreamAttached() === true) {
    		fclose($this->stream);
    	}
    	$this->detach();
    }
    

    /**
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return resource|null Underlying PHP stream, if any
     */
    public function detach(){
    	if($this->isStreamAttached()){
	    	$oldStream = $this->stream;
	    	$this->stream = null;
	    	$this->isReadable = null;
	    	$this->isSeekable = null;
	    	$this->isWritable = null;
	    	$this->streamSize = null;
	    	return $oldStream;
    	}
	    return null;
    }

    /**
     * Get the size of the stream if known.
     *
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize(){
    	if (!$this->streamSize && $this->isStreamAttached() === true) {
    		$stats = fstat($this->stream);
    		$this->streamSize = isset($stats['size']) ? $stats['size'] : null;
    	}
    	return $this->streamSize;
    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int Position of the file pointer
     * @throws \RuntimeException on error.
     */
    public function tell(){
    	if (!$this->isStreamAttached() || ($position = ftell($this->stream)) === false) {
    		throw new RuntimeException('Could not get the position of the pointer in stream');
    	}
    	return $position;
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof(){
    	return $this->isStreamAttached() ? feof($this->stream) : true;
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable(){
    	if($this->isStreamAttached()){
    		$this->isSeekable = false;
    		$this->isSeekable = $this->getMetadata('seekable');
    	}
    	return $this->isSeekable;
    }

    /**
     * Seek to a position in the stream.
     *
     * @see http://www.php.net/manual/en/function.fseek.php
     *
     * @param int $offset Stream offset
     * @param int $whence Specifies how the cursor position will be calculated
     *                    based on the seek offset. Valid values are identical to the built-in
     *                    PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
     *                    offset bytes SEEK_CUR: Set position to current location plus offset
     *                    SEEK_END: Set position to end-of-stream plus offset.
     *
     * @throws \RuntimeException on failure.
     */
    public function seek($offset, $whence = SEEK_SET){
    	if (!$this->isSeekable() || fseek($this->stream, $offset, $whence) === -1) {
    		throw new RuntimeException('Could not seek in stream');
    	}
    }

    /**
     * Seek to the beginning of the stream.
     *
     * If the stream is not seekable, this method will raise an exception;
     * otherwise, it will perform a seek(0).
     *
     * @see seek()
     * @see http://www.php.net/manual/en/function.fseek.php
     * @throws \RuntimeException on failure.
     */
    public function rewind(){
    	if (!$this->isSeekable() || rewind($this->stream) === false) {
    		throw new RuntimeException('Could not rewind stream');
    	}
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable(){
    	return $this->isWritable;
    }

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written.
     *
     * @return int Returns the number of bytes written to the stream.
     * @throws \RuntimeException on failure.
     */
    public function write($string){
    	if (!$this->isWritable() || ($output = fwrite($this->stream, $string)) === false) {
    		throw new RuntimeException('Could not write to stream');
    	}
    	$this->streamSize = null;
    	return $output;
    }
    
    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable(){
    	return $this->isReadable;
    }

    /**
     * Read data from the stream.
     *
     * @param int $length Read up to $length bytes from the object and return
     *                    them. Fewer than $length bytes may be returned if underlying stream
     *                    call returns fewer bytes.
     *
     * @return string Returns the data read from the stream, or an empty string
     *     if no bytes are available.
     * @throws \RuntimeException if an error occurs.
     */
    public function read($length){
    	
    }

    /**
     * Returns the remaining contents in a string
     *
     * @return string
     * @throws \RuntimeException if unable to read.
     * @throws \RuntimeException if error occurs while reading.
     */
    public function getContents(){
    	if (!$this->isReadable() || ($contents = stream_get_contents($this->stream)) === false) {
    		throw new RuntimeException('Could not get contents of stream');
    	}
    	return $contents;
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @see http://php.net/manual/en/function.stream-get-meta-data.php
     *
     * @param string $key Specific metadata to retrieve.
     *
     * @return array|mixed|null Returns an associative array if no key is
     *     provided. Returns a specific key value if a key is provided and the
     *     value is found, or null if the key is not found.
     */
    public function getMetadata($key = null){
    	$this->metadata = stream_get_meta_data($this->stream);
    	if (is_null($key) === true) {
    		return $this->metadata;
    	}
    	return isset($this->metadata[$key]) ? $this->metadata[$key] : null;
    }
}