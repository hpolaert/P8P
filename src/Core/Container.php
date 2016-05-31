<?php
/**
 * P8P Framework - https://github.com/hpolaert/p8p
 *
 * @link      https://github.com/hpolaert/p8p
 * @copyright Copyright (c) 2016 Hugues Polaert
 * @license   https://github.com/hpolaert/p8p/LICENCE.md (MIT)
 */
namespace P8P\Core;

use Closure;
use Interop\Container\ContainerInterface;
use P8P\Exception\ContainerException;
use P8P\Exception\NotFoundException;

/**
 * Container
 *
 * Simple dependency injection container for providing services throughout
 * the framework (default services are injected through the service provider)
 *
 * Implements Interop container principles
 */
class Container implements ContainerInterface, \ArrayAccess
{
    /**
     * @var array Store objects and parameters
     */
    protected $mixed = [];

    /**
     * @var array Store objects which should return a new instance
     */
    protected $storage = [];

    /**
     * @var array Raw output of callables
     */
    protected $objOutput = [];

    /**
     * @var array When objects are already in used, prevents overriding
     */
    protected $frozenKeys = [];

    /**
     * @var array Keep in memory registered keys
     */
    protected $registeredKeys = [];

    /**
     * Container instantiation
     *
     * Instantiates a storage facility to store
     * callables which should return a new instance
     */
    public function __construct()
    {
        $this->storage = new \SplObjectStorage();
    }

    /**
     * Assign a new object or property to the container
     *
     * @param mixed $key The offset to assign the object or property
     * @param mixed $value The object or property to be assigned
     *
     * @throws ContainerException
     * @return void
     */
    public function offsetSet($key, $value)
    {
        // Check if the key is available
        if (isset($this->frozenKeys[$key])) {
            throw new ContainerException("Cannot assign object or property to an already registered and used key");
        }

        // If it is, disable access to it and store it
        $this->mixed[$key] = $value;
        $this->registeredKeys[$key] = true;
    }

    /**
     * Fetch an object or a property according to its key
     *
     * @param mixed $key The offset to retrieve
     *
     * @throws NotFoundException
     * @return mixed Can return all value types
     */
    public function offsetGet($key)
    {
        // Check if the key is available
        if (isset($this->registeredKeys[$key])) {
            throw new NotFoundException(sprintf('Error, key "%s" is not registered', $key));
        }

        // If $key refers to an object already invoked, a property or an uninvokable object
        if (isset($this->objOutput[$key])
            || !$this->mixed[$key] instanceof Closure
            || !is_object($this->mixed[$key])
            || !method_exists($this->mixed[$key], '__invoke')
        ) {
            // Return it as it is
            return $this->mixed[$key];
        } elseif (isset($this->storage[$this->mixed[$key]])) {
            // If the object should be re-instantiated every time
            return $this->mixed[$key]($this);
        }

        // At this point $key refers to a callable or an object which hasn't already been instantiated
        $output = $this->mixed[$key];
        $this->mixed[$key] = $output($this);

        // Store raw output and freeze the key
        $this->objOutput[$key] = $this->mixed[$key];
        $this->frozenKeys[$key] = true;

        // Return the raw output of the object
        return $this->mixed[$key];
    }

    /**
     * Gets a property or a callable
     *
     * @param string $Key The unique identifier for the parameter or object
     *
     * @throws NotFoundException if the key is not registered
     * @return mixed Can return an object or a property
     *
     */
    public function output($key)
    {
        // Check if the key is available
        if (isset($this->registeredKeys[$key])) {
            throw new NotFoundException(sprintf('Error, key "%s" is not registered', $key));
        }

        // If raw output has already been registered
        if (isset($this->objOutput[$key])) {
            return $this->objOutput[$key];
        }

        // Call new instance and generate the raw output
        return $this->mixed[$key];
    }

    /**
     * Check if an object or property is registered to a given key
     *
     * @param mixed $key An offset to check for
     *
     * @return bool true on success or false on failure
     */
    public function offsetExists($key) : bool
    {
        return isset($this->mixed[$key]);
    }

    /**
     * Erase a registered key from all instances
     *
     * @param mixed $key
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        if (isset($this->registeredKeys[$key])) {
            if (is_object($this->mixed[$key])) {
                unset($this->storage[$this->$this[$key]]);
            }
            unset($this->$this[$key], $this->frozenKeys[$key], $this->objOutput[$key], $this->registeredKeys[$key]);
        }
    }

    /**
     * Store an object assigned to a key into the factory container
     *
     * @param mixed $key
     *
     * @throws ContainerException if the callable cannot be reinstantiated
     * @return callable returned to the setter
     */
    public function forceNew($callable)
    {
        // Check if $callable is eligible to reinstatiations
        if (!method_exists($callable, '__invoke')) {
            throw new ContainerException(sprintf('Callable is not a valid closure'));
        }

        // Store the callable in the objects library
        $this->storage->attach($callable);
        return $callable;
    }

    /**
     * Alias to offsetExists to respect Interop principles
     *
     * @param string $key Identifier of the entry to look for.
     *
     * @return bool true if key is registered, false if it isn't
     */

    public function has($key) : bool
    {
        return $this->offsetExists($key);
    }

    /**
     * Alias to offsetGet to respect Interop principles
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundException  No entry was found for this identifier.
     * @return mixed Object or property
     */
    public function get($key)
    {
        return $this->offsetGet($key);
    }
}