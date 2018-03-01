<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 28.02.2018
 * Time: 14:20
 */
declare(strict_types=1);

namespace WPHibou\DI;

use function Opis\Closure\serialize;
use function Opis\Closure\unserialize;

class AbstractCache
{
    protected $key;
    protected $group;

    public function __construct(string $group = 'wp-hibou_di', string $key = 'container')
    {
        $this->key = $key;
        $this->group = $group;
    }

    protected function serialize(Container $container): string
    {
        return serialize($container);
    }

    protected function unserialize(string $serializedContainer): Container
    {
        return unserialize($serializedContainer);
    }
}
