<?php

declare(strict_types = 1);

namespace Lookyman\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;

interface StackInterface extends DelegateInterface
{

	public function push(MiddlewareInterface $middleware);

}
