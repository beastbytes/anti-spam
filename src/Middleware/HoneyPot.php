<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\AntiSpam\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Http\Method;
use Yiisoft\Http\Status;

class HoneyPot implements MiddlewareInterface
{
    public function __construct(private string $name, private ResponseFactoryInterface $responseFactory)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->isValid($request)) {
            return $this
                ->responseFactory
                ->createResponse(Status::I_AM_A_TEAPOT)
            ;
        }

        return $handler->handle($request);
    }

    private function isValid(ServerRequestInterface $request): bool
    {
        if ($request->getMethod() === Method::POST) {
            $data = $request->getParsedBody();
            $honeyPot = md5($this->name);

            return isset($data[$honeyPot]) && $data[$honeyPot] === '';
        }

        return true;
    }
}
