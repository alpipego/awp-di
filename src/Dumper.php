<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 07.08.2017
 * Time: 12:47
 */
declare(strict_types = 1);

namespace WPHibou\DI;

class Dumper
{
    public function serialize(Container $c)
    {
        $serialized = [];
        foreach ($c->keys() as $key) {
            try {
                $serialized[$key] = serialize($c->get($key));
            } catch (\Exception $e) {
                echo '<code><pre>';
                var_dump($key, $e->getMessage());
                echo '</pre></code>';
            }
        }

        return $serialized;
    }

    public function serializeItem($get)
    {
        try {
            $get = serialize($get);
        } catch (\Exception $e) {
            echo '<code><pre>';
            var_dump($e->getMessage());
            var_dump($get instanceof \Closure);
            echo '</pre></code>';
        }

        return $get;
    }
}
