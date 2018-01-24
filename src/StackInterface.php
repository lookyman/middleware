<?php
declare(strict_types=1);

namespace Lookyman\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface StackInterface extends RequestHandlerInterface
{

	public function push(MiddlewareInterface $middleware): void;

}
