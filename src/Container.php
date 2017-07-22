<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 22.07.2017
 * Time: 10:53
 */

namespace WPHibou\DI;

use Pimple\Container as Pimple;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;

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
                $reflection = new ReflectionClass($content);
                if ($reflection->hasMethod('run')) {
                    $content->run();
                }
            }
        }
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        // simple value exists
        if ($this->has($id)) {
            return $this[$id];
        }

        try {
            $reflector = new ReflectionClass($id);
        } catch (ReflectionException $e) {
            // check if complex value exists
            $configArray = $this->configArray($id);
            if (! empty($configArray)) {
                return $configArray;
            }
            // if mapped value exists
            if (array_key_exists($id, $this->definitions) && $this->has($this->definitions[$id])) {
                return $this[$this->definitions[$id]];
            }
            throw new NotFoundException(sprintf('Identifier "%s" is not defined.', $id));
        }

        if ($reflector->isInterface()) {
            if (array_key_exists($id, $this->definitions)) {
                $id = $this->definitions[$id];
                if ($this->has($id)) {
                    return $this[$id];
                }
                $reflector = new ReflectionClass($id);
            }
        }

        /** @var \ReflectionMethod|null */
        $constructor = $reflector->getConstructor();

        if (! is_null($constructor)) {
            /** @var \ReflectionParameter[] */
            $dependencies = $constructor->getParameters();
        }

        if (is_null($constructor) || empty($dependencies)) {
            $this->offsetSet($id, function () use ($id) {
                return new $id();
            });

            return $this[$id];
        }

        $dependencies = array_map(function (ReflectionParameter $dependency) use ($id) {
            return $this->resolveDependency($dependency, $id);
        }, $dependencies);

        $this->offsetSet($id, function () use ($reflector, $dependencies) {
            return $reflector->newInstanceArgs($dependencies);
        });

        return $this[$id];
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id)
    {
        return isset($this[$id]);
    }

    private function configArray(string $id)
    {
        // check if this is an array-like config request and return it as array
        $return = [];
        $idArr  = (array)explode('.', $id);
        foreach ($this->keys() as $key) {
            $keyArr = explode('.', $key);
            if (! array_diff($idArr, $keyArr)) {
                $keys  = array_diff($keyArr, $idArr);
                $value = $this[$key];
                while ($key = array_pop($keys)) {
                    $value = [$key => $value];
                }

                $return = array_merge_recursive($return, $value);
            }
        }

        return $return;
    }

    private function resolveDependency(ReflectionParameter $dependency, string $id) : string
    {
        if (array_key_exists($id, $this->definitions)) {
            if (array_key_exists($dependency->getName(), $this->definitions[$id]->constructorParams)) {
                return $this->get($this->definitions[$id]->constructorParams[$dependency->getName()]);
            }
            // configuration values
            if (! $dependency->isCallable()) {
                // simple values
                if ($this->has($dependency->getName())) {
                    return $this->get($dependency->getName());
                }
                // mapped values
                if (array_key_exists($dependency->getName(), $this->definitions) && $this->has($this->definitions[$dependency->getName()])) {
                    return $this->get($this->definitions[$dependency->getName()]);
                }
            }
        }

        if ($dependency->isDefaultValueAvailable()) {
            return $dependency->getDefaultValue();
        }

        if (is_null($dependency->getClass())) {
            throw new NotFoundException(sprintf('Identifier "%s" is not defined.', $dependency));
        }

        return $this->get($dependency->getClass()->getName());
    }

    public function addDefiniton($definition) : array
    {
        if (is_string($definition)) {
            if (! file_exists($definition)) {
                throw new \Exception(sprintf('%s not a readable file', gettype($definition)));
            }
            $definition = require_once $definition;
        }

        if (! is_array($definition)) {
            throw new \Exception(sprintf('Definiton has to be an array, %s given', gettype($definition)));
        }

        $this->definitions = array_merge($this->definitions, $definition);
    }
}
