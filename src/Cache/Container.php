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
        return json_decode($this->container[$offset]);
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
