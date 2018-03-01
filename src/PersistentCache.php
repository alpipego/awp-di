<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 07.08.2017
 * Time: 12:47
 */
declare(strict_types=1);

namespace WPHibou\DI;

final class PersistentCache extends AbstractCache implements CacheInterface
{
    public function set(Container $container): bool
    {
        return wp_cache_set(self::KEY, $container, self::GROUP, 0);
    }

    public function get(): Container
    {
        $serializedContainer = wp_cache_get(self::KEY, self::GROUP);
        if ($serializedContainer === false) {
            throw new ContainerCacheException('Container not found in cache');
        }

        return $this->unserialize($serializedContainer);
    }

    public function delete(): bool
    {
        return wp_cache_delete(self::KEY, self::GROUP);
    }

    public function has(): bool
    {
        return (bool)wp_cache_get(self::KEY, self::GROUP);
    }
}
