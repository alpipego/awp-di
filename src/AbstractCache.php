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
    const KEY = 'container';
    const GROUP = 'wp-hibou/di';

    protected function serialize(Container $container): string
    {
        return serialize($container);
    }

    protected function unserialize(string $serializedContainer): Container
    {
        return unserialize($serializedContainer);
    }
}
