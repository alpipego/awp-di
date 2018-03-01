<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 01.03.2018
 * Time: 09:52
 */
declare(strict_types=1);

namespace WPHibou\DI\Cache;

use WPHibou\DI\ContainerInterface;
use WPHibou\DI\Exception\ContainerCacheException;

final class Cache implements CacheInterface
{
    private $cache;

    public function __construct(string $group = 'wp-hibou_di', string $key = 'container')
    {
        $this->cache =
            wp_using_ext_object_cache()
                ? new PersistentCache($group, $key)
                : new TransientCache($group, $key);
    }

    public function set(ContainerInterface $container): bool
    {
        return $this->cache->set($container);
    }

    /**
     * @throws ContainerCacheException
     */
    public function get(): ContainerInterface
    {
        try {
            return $this->cache->get();
        } catch (ContainerCacheException $e) {
            throw $e;
        }
    }

    public function delete(): bool
    {
        return $this->cache->delete();
    }

    public function has(): bool
    {
        return $this->cache->has();
    }
}
