<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 22.07.2017
 * Time: 10:54
 */

namespace WPHibou\DI;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \InvalidArgumentException implements NotFoundExceptionInterface
{

}
