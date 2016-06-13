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
// print($container["SETTINGS_DB"]);



class TestA {
	public function __construct(){
	}
	public function __invoke(){
		return "Hello i'm an invoke class";
	}
}

class TestB {
	public function __construct(){
	}
}

$anonymousFunction = function(){
	return "Basic anonymous function";
};
$closureTest = "Hello";
$closureFunction = function($c) use ($closureTest) {
	return $c . $closureTest;
};

$aTestA = new TestA();
$aTestB = new TestB();

print("<br />----------- Primitive types ----------");
print("<br />Anonymous function : f" . var_dump($anonymousFunction));
print("<br />Closure function : " . var_dump($closureFunction));
print("<br />Class A object : " . var_dump($aTestA));
print("<br />Class B object : " . var_dump($aTestB));
print("<br />----------- Method exists Invoke ----------");
print("<br />Anonymous function : " . var_dump(method_exists($anonymousFunction, '__invoke')));
print("<br />Closure function : " . var_dump(method_exists($closureFunction, '__invoke')));
print("<br />Class A : " . var_dump(method_exists($aTestA, '__invoke')));
print("<br />Class B : " . var_dump(method_exists($aTestB, '__invoke')));
print("<br />----------- Is object ----------");
print("<br />Anonymous function : " . var_dump(is_object($anonymousFunction)));
print("<br />Closure function : " . var_dump(is_object($closureFunction)));
print("<br />Class A : " . var_dump(is_object($aTestA)));
print("<br />Class B : " . var_dump(is_object($aTestB)));
print("<br />----------- Is closure ----------");
print("<br />Anonymous function : " . var_dump($anonymousFunction instanceof Closure));
print("<br />Closure function : " . var_dump($closureFunction instanceof Closure));
print("<br />Class A : " . var_dump($aTestA instanceof Closure));
print("<br />Class B : " . var_dump($aTestB instanceof Closure));
print("<br />---------------------------------");
print("<br />---------------------------------");
print("<br />---------- Container test -------");
$container["TestA"] = $aTestA;
$container["TestB"] = $aTestB;
$container["Anonymous"] = $anonymousFunction;
$container["Closure"] = $closureFunction("Meow");
print("<br />Container test A : " . $container["TestA"]);
print("<br />Container test B : " . $container["TestB"]);
print("<br />Container Anonymous : " . $container["Anonymous"]);
print("<br />Container Closure : " . $container["Closure"]);
print("---------- Container raw -------");
print("<br />Container test A : " . $container->output("TestA"));
print("<br />Container test B : " . $container->output("TestB"));
print("<br />Container Anonymous : " . $container->output("Anonymous"));
print("<br />Container Closure : " . $container->output("Closure"));
print("<br />---------------------------------");
print("<br />---------------------------------");