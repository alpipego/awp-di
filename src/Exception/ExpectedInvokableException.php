<?php
/**
 * Created by PhpStorm.
 * User: alpipego
 * Date: 01.03.2018
 * Time: 13:40
 */
declare(strict_types=1);

namespace Alpipego\AWP\DI\Exception;

use Psr\Container\ContainerExceptionInterface;

class ExpectedInvokableException extends \InvalidArgumentException implements ContainerExceptionInterface
{

}
