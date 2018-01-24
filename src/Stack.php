<?php
declare(strict_types=1);

namespace Lookyman\Middleware;

use Interop\Http\Factory\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Stack implements StackInterface
{

	/**
	 * @var RequestHandlerInterface
	 */
	private $top;

	public function __construct(ResponseFactoryInterface $responseFactory)
	{
		$this->top = new class ($responseFactory) implements RequestHandlerInterface
		{

			/**
			 * @var ResponseFactoryInterface
			 */
			private $responseFactory;

			public function __construct(ResponseFactoryInterface $responseFactory)
			{
				$this->responseFactory = $responseFactory;
			}

			public function handle(ServerRequestInterface $request): ResponseInterface
			{
				return $this->responseFactory->createResponse();
			}

		};
	}

	public function push(MiddlewareInterface $middleware): void
	{
		$this->top = new class ($middleware, $this->top) implements RequestHandlerInterface
		{

			/**
			 * @var MiddlewareInterface
			 */
			private $middleware;

			/**
			 * @var RequestHandlerInterface
			 */
			private $requestHandler;

			public function __construct(MiddlewareInterface $middleware, RequestHandlerInterface $requestHandler)
			{
				$this->middleware = $middleware;
				$this->requestHandler = $requestHandler;
			}

			public function handle(ServerRequestInterface $request): ResponseInterface
			{
				return $this->middleware->process($request, $this->requestHandler);
			}

		};
	}

	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		return $this->top->handle($request);
	}

}
