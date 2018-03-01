<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 28.02.2018
 * Time: 14:18
 */
declare(strict_types=1);

namespace WPHibou\DI;

interface CacheInterface
{
    public function set(Container $container): bool;

    public function get(): Container;

    public function delete(): bool;

    public function has(): bool;
}
