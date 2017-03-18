<?php

declare(strict_types = 1);

namespace Lookyman\Middleware;

use Interop\Http\Factory\ResponseFactoryInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class Stack implements StackInterface
{

	/**
	 * @var DelegateInterface
	 */
	private $top;

	public function __construct(ResponseFactoryInterface $responseFactory)
	{
		$this->top = new class ($responseFactory) implements DelegateInterface
		{

			/**
			 * @var ResponseFactoryInterface
			 */
			private $responseFactory;

			public function __construct(ResponseFactoryInterface $responseFactory)
			{
				$this->responseFactory = $responseFactory;
			}

			public function process(ServerRequestInterface $request): ResponseInterface
			{
				return $this->responseFactory->createResponse();
			}

		};
	}

	public function push(MiddlewareInterface $middleware)
	{
		$this->top = new class ($middleware, $this->top) implements DelegateInterface
		{

			/**
			 * @var MiddlewareInterface
			 */
			private $middleware;

			/**
			 * @var DelegateInterface
			 */
			private $delegate;

			public function __construct(MiddlewareInterface $middleware, DelegateInterface $delegate)
			{
				$this->middleware = $middleware;
				$this->delegate = $delegate;
			}

			public function process(ServerRequestInterface $request): ResponseInterface
			{
				return $this->middleware->process($request, $this->delegate);
			}

		};
	}

	public function process(ServerRequestInterface $request): ResponseInterface
	{
		return $this->top->process($request);
	}

}
