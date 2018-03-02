<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 22.07.2017
 * Time: 10:53
 */

namespace WPHibou\DI;

use Pimple\Container as Pimple;
use Pimple\Exception\UnknownIdentifierException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;

class Container extends Pimple implements ContainerInterface
{
    private $definitions = [];

    /**
     * Calls `run()` method on all objects registered on plugin container
     */
    public function run()
    {
        $keys = array_merge($this->keys(), array_keys($this->definitions));
        foreach ($keys as $key) {
            $content = $this->get($key);

            if (is_object($content)) {
                try {
                    $reflection = new ReflectionClass($content);
                    if ($reflection->hasMethod('run')) {
                        $content->run();
                    }
                } catch (ReflectionException $e) {
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $id)
    {
        if (! is_string($id) || (is_object($id) && method_exists($id, '__toString'))) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                throw new ContainerException('Only strings should be passed as $id');
            }
        }

        $id = (string)$id;

        // simple value exists
        if ($this->offsetExists($id)) {
            return parent::offsetGet($id);
        }

        try {
            $reflector = new ReflectionClass($id);
        } catch (ReflectionException $e) {
            // if mapped value exists
            if (array_key_exists($id, $this->definitions)) {
                $this->offsetSet($id, $this->definitions[$id]);
                if (is_callable($this->definitions[$id])) {
                    return $this->definitions[$id]();
                }

                return $this->definitions[$id];
            }
            // check if complex value exists
            $configArray = $this->configArray($id);
            if (! empty($configArray)) {
                return $configArray;
            }

            // check if interface bound to value
            foreach ($this->definitions as $class => $definition) {
                if (! empty($definition->bindings) && ($key = array_search($id, $definition->bindings))) {
                    $class = $this->get($class);
                    $this->offsetSet($definition->bindings[$key], $class);

                    return $class;
                }
            }

            if (defined('WP_DEBUG') && WP_DEBUG) {
                throw new NotFoundException(sprintf('Identifier "%s" is not defined.', $id));
            }

            return false;
        }

        if ($reflector->isInterface()) {
            // check if interface mapped to value
            if (array_key_exists($id, $this->definitions)) {
                $id = $this->definitions[$id];
            } else {
                // check if interface bound to value
                foreach ($this->definitions as $class => $definition) {
                    if (! empty($definition->bindings) && ($key = in_array($id, $definition->bindings))) {
                        $this->offsetSet($definition->bindings[$key], $this->get($class));
                        $id = $class;
                        break;
                    }
                }
            }
            if ($this->has($id)) {
                return $this[$id];
            }
            $reflector = new ReflectionClass($id);
        }

        /** @var ReflectionMethod|null */
        $constructor = $reflector->getConstructor();

        if (! is_null($constructor)) {
            /** @var ReflectionParameter[] */
            $dependencies = $constructor->getParameters();
        }

        if (is_null($constructor) || empty($dependencies)) {
            $this->offsetSet($id, function () use ($id) {
                return new $id();
            });
            if (is_callable($this[$id])) {
                return $this[$id]();
            }

            return $this[$id];
        }

        $dependencies = array_map(function (ReflectionParameter $dependency) use ($id) {
            return $this->resolveDependency($dependency, $id);
        }, $dependencies);

        $this->offsetSet($id, function () use ($reflector, $dependencies) {
            return $reflector->newInstanceArgs($dependencies);
        });

        if (is_callable($this[$id])) {
            return $this[$id]();
        }

        return $this[$id];
    }

    private function configArray(string $id)
    {
        // check if this is an array-like config request and return it as array
        $return = [];
        $idArr  = explode('.', $id);
        foreach ($this->keys() as $key) {
            $keyArr = explode('.', $key);
            if (! array_diff($idArr, $keyArr)) {
                $keys  = array_diff($keyArr, $idArr);
                $value = $this[$key];
                while (($key = array_pop($keys))) {
                    $value = [$key => $value];
                }

                $return = array_merge_recursive($return, $value);
            }

            if (is_array($this->get($key))) {
                array_shift($idArr);

                return $this->recursiveArrayKeySearch($this->get($key), $idArr);
            }
        }

        return $return;
    }

    private function recursiveArrayKeySearch(array $array, array $keys)
    {
        if ($this->recursiveArrayKeyExists($array, ...$keys)) {
            return array_reduce($keys, function ($array, $value) {
                return $array[$value];
            }, $array);
        }

        return null;
    }

    private function recursiveArrayKeyExists(array $array, ...$keys): bool
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $array)) {
                return false;
            }
            $array = $array[$key];
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $id)
    {
        try {
            $this->get($id);

            return true;
        } catch (NotFoundException $e) {
            return false;
        }
    }

    private function resolveDependency(ReflectionParameter $dependency, string $id)
    {
        if (array_key_exists($id, $this->definitions)) {
            if (array_key_exists($dependency->getName(), $this->definitions[$id]->constructorParams)) {
                return $this->get($this->definitions[$id]->constructorParams[$dependency->getName()]);
            }
            // configuration values
            if (! $dependency->isCallable()) {
                // mapped values
                if (array_key_exists($dependency->getName(),
                        $this->definitions) && $this->has($this->definitions[$dependency->getName()])) {
                    return $this->get($this->definitions[$dependency->getName()]);
                }
            }
        }

        // simple values
        if (! $dependency->isCallable()) {
            if ($this->has($dependency->getName())) {
                return $this->get($dependency->getName());
            }
        }

        if ($dependency->isDefaultValueAvailable()) {
            return $dependency->getDefaultValue();
        }

        if (is_null($dependency->getClass())) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                throw new NotFoundException(sprintf('Identifier "%s" is not defined.', $dependency));
            }
        }

        return $this->get($dependency->getClass()->getName());
    }

    public function offsetGet($id)
    {
        try {
            $value = parent::offsetGet($id);
            if (in_array(gettype($value), ['object', 'array'], true)) {
                return $value;
            }

            try {
                $class = new ReflectionClass($value);

                return $this->get($class->getName());
            } catch (ReflectionException $e) {
                return $value;
            }
        } catch (UnknownIdentifierException $e) {
            if (! $this->has($id)) {
                /** @var ObjectDefinition $definition */
                foreach ($this->definitions as $class => $definition) {
                    if (! empty($definition->bindings)) {
                        $key = array_search($id, $definition->bindings, true);
                        if ($key !== false) {
                            $this->offsetSet($definition->bindings[$key], $this->get($class));

                            return $this->get($class);
                        }
                    }
                }

                if (defined('WP_DEBUG') && WP_DEBUG) {
                    throw new NotFoundException(sprintf('Identifier "%s" is not defined.', $id));
                }
            }

            return $this->get($id);
        }
    }

    public function addDefinition(string $definition)
    {
        if (! file_exists($definition)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                throw new \Exception(sprintf('%s not a readable file', gettype($definition)));
            }

            return false;
        }
        $definition = require_once $definition;

        if (! is_array($definition)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                throw new \Exception(sprintf('Definiton has to be an array, %s given', gettype($definition)));
            }

            return false;
        }

        $this->definitions = array_merge($this->definitions, $definition);
    }

    public function set(string $id, $value)
    {
        parent::offsetSet($id, $value);
    }

    public function dump(): array
    {
        $keys   = array_merge($this->keys(), array_keys($this->definitions));
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = $this->get($key);
        }

        return $values;
    }
}
