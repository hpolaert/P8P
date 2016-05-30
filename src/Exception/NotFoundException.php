<?php
/**
 * P8P Framework - https://github.com/hpolaert/p8p
 *
 * @link      https://github.com/hpolaert/p8p
 * @copyright Copyright (c) 2016 Hugues Polaert
 * @license   https://github.com/hpolaert/p8p/LICENCE.md (MIT)
 */

namespace P8P\Exception;

use Exception;

/**
 * Base interface representing a generic exception in a container.
 */
class NotFoundException extends Exception implements \Interop\Container\Exception\NotFoundException
{
    public function __construct(){

    }
}
