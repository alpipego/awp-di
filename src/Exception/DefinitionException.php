<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 01.03.2018
 * Time: 13:25
 */

namespace WPHibou\DI;

use Psr\Container\ContainerExceptionInterface;

class DefinitionException extends \InvalidArgumentException implements ContainerExceptionInterface
{
}
