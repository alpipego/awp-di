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
use WPHibou\DI\Exception\ContainerCacheException;

class Container implements ContainerInterface
{
    private $values;
    private $keys;

    public function __construct(array $values)
    {
        $this->values = $values;
        $this->keys   = array_keys($values);
    }

    public function run()
    {
        foreach ($this->keys as $key) {
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
    public function get(string $id)
    {
        return $this->values[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $id)
    {
        return (bool)! empty($this->values[$id]);
    }

    public function set(string $id, $value)
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            throw new ContainerCacheException(sprintf('Value "%s" can\'t be set on cached container.', $id));
        }
    }

    public function addDefinition(string $definition)
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            throw new ContainerCacheException(
                sprintf('Definition "%s" can\'t be set on cached container.', $definition)
            );
        }
    }

    public function dump(): array
    {
        return $this->values;
    }

    public function extend($id, $callable)
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            throw new ContainerCacheException(sprintf('Cached container identifier "%s" cannot be extended.', $id));
        }
    }
}
