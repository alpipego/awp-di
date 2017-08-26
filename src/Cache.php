<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 07.08.2017
 * Time: 12:47
 */
declare(strict_types = 1);

namespace WPHibou\DI;

class Cache
{
    const KEY = 'container';
    const GROUP = 'wp-hibou/di';

    public function set(Container $container) : bool
    {
        return wp_cache_add(self::KEY, $container, self::GROUP, 0);
    }

    public function overwrite(Container $container) : bool
    {
        return wp_cache_set(self::KEY, $container, self::GROUP, 0);
    }

    public function get() : Container
    {
        $container = wp_cache_get(self::KEY, self::GROUP);
        if ($container === false) {
            throw new ContainerCacheException('Container not found in cache');
        }

        return $container;
    }

    public function delete() : bool
    {
        return wp_cache_delete( self::KEY, self::GROUP );
    }
}
