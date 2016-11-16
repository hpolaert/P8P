<?php
/**
 * P8P Framework - https://github.com/hpolaert/p8p
*
* @link      https://github.com/hpolaert/p8p
* @copyright Copyright (c) 2016 Hugues Polaert
* @license   https://github.com/hpolaert/p8p/LICENCE.md (MIT)
*/
namespace P8PTest\Core;

use P8P\Http\Message;

/**
 * HTTP Message Test Cases
 *
 * Asserts P8P\Http\Message functionalities
 */
class MessageTest extends \PHPUnit_Framework_TestCase
{
	
	/**
	 * @runInSeparateProcess
	 * @requires extension xdebug
	 */
	public function testGivenHeaderIsIncludedIntoResponse()
	{
		$customHeaderName = 'Location';
		$customHeaderValue = 'foo';
		// Write custom header 
		ob_start();
		header('Location: foo');
		ob_end_clean();
		$expectedHeader = $customHeaderName . ': ' . $customHeaderValue;
		$headers = xdebug_get_headers();
		$this->assertContains($expectedHeader, $headers[0]);
	}
}