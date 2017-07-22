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

    public function constructorParam(string $name, $value) : self
    {
        $this->constructorParams[$name] = $value;

        return $this;
    }
}
