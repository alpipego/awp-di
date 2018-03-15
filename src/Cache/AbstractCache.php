<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 28.02.2018
 * Time: 14:20
 */
declare(strict_types=1);

namespace Alpipego\AWP\DI\Cache;

use Alpipego\AWP\DI\ContainerInterface;
use function Opis\Closure\serialize;
use function Opis\Closure\unserialize;

class AbstractCache
{
    protected $key;
    protected $group;

    public function __construct(string $group = 'wp-hibou_di', string $key = 'container')
    {
        $this->key   = $key;
        $this->group = $group;
    }

    protected function serialize(ContainerInterface $container): string
    {
        return serialize($container->dump());
    }

    protected function unserialize(string $serializedContainer): ContainerInterface
    {
        return new Container(unserialize($serializedContainer));
    }
}
