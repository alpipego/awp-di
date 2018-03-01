<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 28.02.2018
 * Time: 14:19
 */
declare(strict_types=1);

namespace WPHibou\DI;

final class TransientCache extends AbstractCache implements CacheInterface
{

    public function set(Container $container): bool
    {
        return set_site_transient(self::KEY, $this->serialize($container), 0);
    }

    public function get(): Container
    {
        $serializedContainer = get_site_transient(self::KEY);
        if ($serializedContainer === false) {
            throw new ContainerCacheException('Container not found in cache');
        }

        return $this->unserialize($serializedContainer);
    }

    public function delete(): bool
    {
        return delete_site_transient(self::KEY);
    }

    public function has(): bool
    {
        return (bool)get_site_transient(self::KEY);
    }
}
