<?php
/**
 * P8P Framework - https://github.com/hpolaert/p8p
 *
 * @link      https://github.com/hpolaert/p8p
 * @copyright Copyright (c) 2016 Hugues Polaert
 * @license   https://github.com/hpolaert/p8p/LICENCE.md (MIT)
 */
namespace P8PTest\Container\Fixtures;

/**
 * Fixture : invokable
 */
class Invokable
{
    public function __invoke(){
        return 'Hello World!';
    }
    
    public function sayHello(){
    	return 'Hello World!';
    }
}