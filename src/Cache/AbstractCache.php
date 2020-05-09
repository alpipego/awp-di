<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 28.02.2018
 * Time: 14:20
 */
declare(strict_types = 1);

namespace Alpipego\AWP\DI\Cache;

use Alpipego\AWP\DI\ContainerInterface;
use function Opis\Closure\serialize;
use function Opis\Closure\unserialize;

abstract class AbstractCache implements CacheInterface
{
    protected $key;
    protected $group;

    public function __construct(string $group = 'awp_di', string $key = 'container')
    {
        $this->key   = $key;
        $this->group = $group;
        add_action('setted_site_transient', [$this, 'clean']);
        add_action('setted_transient', [$this, 'clean']);
        add_action('deleted_site_transient', [$this, 'clean']);
        add_action('deleted_transient', [$this, 'clean']);
    }

    protected function serialize(ContainerInterface $container): string
    {
        return serialize($container->dump());
    }

    protected function unserialize(string $serializedContainer): ContainerInterface
    {
        return new Container(unserialize($serializedContainer));
    }

    public function clean(string $transient)
    {
        if ($transient === $this->key()) {
            return;
        }
        $this->delete();
    }

    protected function key()
    {
        return $this->group . '_' . $this->key;
    }
}
