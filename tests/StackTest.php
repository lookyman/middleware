<?php

declare(strict_types = 1);

namespace Lookyman\Middleware;

use Interop\Http\Factory\ResponseFactoryInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @covers \Lookyman\Middleware\Stack
 */
final class StackTest extends TestCase
{

	public function testEmpty()
	{
		$response = $this->createMock(ResponseInterface::class);
		$responseFactory = $this->createMock(ResponseFactoryInterface::class);
		$responseFactory->expects(self::once())->method('createResponse')->with(200)->willReturn($response);
		$stack = new Stack($responseFactory);
		self::assertSame($response, $stack->process($this->createMock(ServerRequestInterface::class)));
	}

	public function testNonEmpty()
	{
		$middleware = function (string $text): MiddlewareInterface {
			return new class ($text) implements MiddlewareInterface {

				/**
				 * @var string
				 */
				private $text;

				public function __construct(string $text)
				{
					$this->text = $text;
				}

				public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
				{
					$response = $delegate->process($request);
					$response->getBody()->write($this->text);
					return $response;
				}

			};
		};

		$body = $this->createMock(StreamInterface::class);
		$body->expects(self::at(0))->method('write')->with('a')->willReturn(1);
		$body->expects(self::at(1))->method('write')->with('b')->willReturn(1);
		$response = $this->createMock(ResponseInterface::class);
		$response->expects(self::exactly(2))->method('getBody')->willReturn($body);
		$responseFactory = $this->createMock(ResponseFactoryInterface::class);
		$responseFactory->expects(self::once())->method('createResponse')->with(200)->willReturn($response);

		$stack = new Stack($responseFactory);
		$stack->push($middleware('a'));
		$stack->push($middleware('b'));
		self::assertSame($response, $stack->process($this->createMock(ServerRequestInterface::class)));
	}

}
