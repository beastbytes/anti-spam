<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\AntiSpam\Tests\Support;

use HttpSoft\Message\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Dispatcher implements RequestHandlerInterface
{
    /**
     * @param array<MiddlewareInterface> $middlewares
     */
    public function __construct(private array $middlewares)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->dispatch($request);
    }

    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        return $this
            ->resolve(0)
            ->handle($request)
        ;
    }

    private function resolve(int $index): RequestHandlerInterface
    {
        return new RequestHandler(function (ServerRequestInterface $request) use ($index) {
            if (isset($this->middlewares[$index])) {
                return $this
                    ->middlewares[$index]
                    ->process($request, $this->resolve(++$index))
                ;
            }

            return (new ResponseFactory())
                ->createResponse()
            ;
        });
    }
}
