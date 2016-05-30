<?php
/**
 * P8P Framework - Http://....
 *
 * @link      https://github.com/hpolaert/p8p
 * @copyright Copyright (c) 2016 Hugues Polaert
 * @license   https://github.com/hpolaert/p8p/LICENCE.md (MIT)
 */
namespace P8P;

/**
 * App
 *
 * ...
 *
 */

class App {
    /**
     * Current version
     *
     * @var string
     */
    const VERSION = '3.5.0-dev';

    /**
     * Title
     *
     * XXXX
     *
     * @param bool|false $silent
     * @return ResponseInterface
     *
     * @throws Exception
     */

    function run(){

    }

    /**
     * App Builder
     *
     * @param array|null $container
     */
    public function __construct($container = []){
        if (is_array($container)) {
            $container = new Container($container);
        }
        if (!$container instanceof ContainerInterface) {
            throw new InvalidArgumentException('Expected a ContainerInterface');
        }
        $this->container = $container;
    }
}