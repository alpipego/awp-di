<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 07.08.2017
 * Time: 13:33
 */
declare(strict_types=1);

namespace WPHibou\DI\Cache;

use WPHibou\DI\ContainerInterface;
use Pimple\Container as Pimple;

class Container extends Pimple implements ContainerInterface, \ArrayAccess
{
    private $container;

    public function __construct(array $container)
    {
        $this->container = $container;
        parent::__construct();
    }

    public function run()
    {
        foreach ($this->keys() as $key) {
            $content = $this->get($key);

            if (is_object($content)) {
                try {
                    $reflection = new \ReflectionClass($content);
                    if ($reflection->hasMethod('run')) {
                        $content->run();
                    }
                } catch (\ReflectionException $e) {
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        return $this->offsetGet($id);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->container[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        return $this->offsetExists($id);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return (bool)! empty($this->container[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        return false;
    }
}
