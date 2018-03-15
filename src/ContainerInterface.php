<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 01.03.2018
 * Time: 10:58
 */
declare(strict_types=1);

namespace Alpipego\AWP\DI;

/**
 * Interface ContainerInterface
 *
 * Compatible with PSR-11 ContainerInterface but more type safe
 *
 * @package Alpipego\AWP\DI
 */
interface ContainerInterface
{
    public function get(string $id);

    public function has(string $id);

    public function set(string $id, $value);

    public function extend($id, $callable);

    public function addDefinition(string $definition);

    public function dump(): array;
}
