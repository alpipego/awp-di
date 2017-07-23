<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 22.07.2017
 * Time: 10:57
 */

namespace WPHibou\DI;

class ObjectDefinition
{
    public $constructorParams = [];
    public $bindings = [];

    public function constructorParam(string $name, $value) : self
    {
        $this->constructorParams[$name] = $value;

        return $this;
    }

    public function bind(string $class)
    {
        $this->bindings[] = $class;

        return $this;
    }
}
