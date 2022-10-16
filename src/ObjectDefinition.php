<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 22.07.2017
 * Time: 10:57
 */

namespace Alpipego\AWP\DI;

class ObjectDefinition
{
    public $constructorParams = [];
    public $bindings = [];

    public static function __set_state(array $array) : ObjectDefinition
    {
        $o = new self();
        foreach ($array as $key => $thing) {
            if (empty($thing)) {
                continue;
            }
            $o->$key = $thing;
        }

        return $o;
    }

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
