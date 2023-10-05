<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\AntiSpam\Tests;

use BeastBytes\AntiSpam\Middleware\Hash;
use BeastBytes\AntiSpam\Middleware\HoneyPot;
use BeastBytes\AntiSpam\Tests\Support\Dispatcher;
use Generator;
use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\ServerRequestFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yiisoft\Http\Method;
use Yiisoft\Http\Status;

final class HashTest extends TestCase
{
    private const HASH_FIELD = 'email';
    private const HASH_FIELD_VALUE = 'a.n.other@example.com';

    public static function requestProvider(): Generator
    {
        foreach ([
                     'POST with valid Hash' => [
                         Method::POST,
                         [
                             self::HASH_FIELD => self::HASH_FIELD_VALUE,
                             md5(self::HASH_FIELD) => md5(self::HASH_FIELD_VALUE),
                         ],
                         Status::OK
                     ],
                     'POST with invalid Hash' => [
                         Method::POST,
                         [
                             self::HASH_FIELD => self::HASH_FIELD_VALUE,
                             md5(self::HASH_FIELD) => self::HASH_FIELD_VALUE,
                         ],
                         Status::I_AM_A_TEAPOT
                     ],
                     'GET with Hash' => [
                         Method::GET,
                         [
                             self::HASH_FIELD => self::HASH_FIELD_VALUE,
                             md5(self::HASH_FIELD) => md5(self::HASH_FIELD_VALUE),
                         ],
                         Status::OK
                     ],
                     'POST with empty Hash' => [
                         Method::POST,
                         [
                             self::HASH_FIELD => self::HASH_FIELD_VALUE,
                             md5(self::HASH_FIELD) => '',
                         ],
                         Status::I_AM_A_TEAPOT
                     ],
                     'POST with int Hash' => [
                         Method::POST,
                         [
                             self::HASH_FIELD => self::HASH_FIELD_VALUE,
                             md5(self::HASH_FIELD) => 0,
                         ],
                         Status::I_AM_A_TEAPOT
                     ],
                     'POST with null Hash' => [
                         Method::POST,
                         [
                             self::HASH_FIELD => self::HASH_FIELD_VALUE,
                             md5(self::HASH_FIELD) => null,
                         ],
                         Status::I_AM_A_TEAPOT
                     ],
                     'POST with empty data' => [
                         Method::POST,
                         [],
                         Status::I_AM_A_TEAPOT
                     ],
                     'GET with empty data' => [
                         Method::GET,
                         [],
                         Status::OK
                     ],
                 ] as $name => $data) {
            yield $name =>  $data;
        }
    }

    #[dataProvider('requestProvider')]
    public function testMiddleware(string $method, array $parsedBody, int $status): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest($method, '/')
            ->withParsedBody($parsedBody)
        ;

        $dispatcher = new Dispatcher([
            new Hash(self::HASH_FIELD, new ResponseFactory())
        ]);

        $response = $dispatcher->dispatch($request);

        $this->assertSame($status, $response->getStatusCode());
    }
}
