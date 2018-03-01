<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 28.02.2018
 * Time: 14:18
 */
declare(strict_types=1);

namespace WPHibou\DI\Cache;

use WPHibou\DI\ContainerInterface;

interface CacheInterface
{
    public function set(ContainerInterface $container): bool;

    public function get(): ContainerInterface;

    public function delete(): bool;

    public function has(): bool;
}
