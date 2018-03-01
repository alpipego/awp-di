<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 28.02.2018
 * Time: 14:19
 */
declare(strict_types=1);

namespace WPHibou\DI\Cache;

use WPHibou\DI\ContainerInterface;
use WPHibou\DI\Exception\ContainerCacheException;

final class TransientCache extends AbstractCache implements CacheInterface
{
    public function set(ContainerInterface $container): bool
    {
        return set_site_transient($this->key(), $this->serialize($container), 0);
    }

    private function key()
    {
        return $this->group . '_' . $this->key;
    }

    public function get(): ContainerInterface
    {
        $serializedContainer = get_site_transient($this->key());
        if ($serializedContainer === false) {
            throw new ContainerCacheException('Container not found in cache');
        }

        return $this->unserialize($serializedContainer);
    }

    public function delete(): bool
    {
        return delete_site_transient($this->key());
    }

    public function has(): bool
    {
        return (bool)get_site_transient($this->key());
    }
}
