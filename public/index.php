<?php
/**
 * P8P Framework - Http://....
 *
 * @link      https://github.com/hpolaert/p8p
 * @copyright Copyright (c) 2016 Hugues Polaert
 * @license   https://github.com/hpolaert/p8p/LICENCE.md (MIT)
 *
 * Bootstrap File - Load the application
 */

require_once("../vendor/autoload.php");

$container = new \P8P\Core\Container();

$container["SETTINGS_DB"] = function(){return "e";};
// $container["SETTINGS_DB"] = "efze";
print($container["SETTINGS_DB"]);



class TestA {
	public function __construct(){
		print "Hello, i'm class A and i'm being called"
	}
	public function __invoke(){
		return "Hello i'm an invoke class";
	}
}

class TestB {
	public function __construct(){

	}
	public function __invoke(){
		return "Hello i'm an invoke class";
	}
}