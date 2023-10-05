<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\AntiSpam\Tests;

use BeastBytes\AntiSpam\Form\HoneyPotInput;
use BeastBytes\AntiSpam\Middleware\HoneyPot;
use BeastBytes\AntiSpam\Tests\Support\Dispatcher;
use BeastBytes\AntiSpam\Tests\Support\TestFormModel;
use Generator;
use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\ServerRequestFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Yiisoft\Http\Method;
use Yiisoft\Http\Status;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Widget\WidgetFactory;

class HoneyPotTest extends TestCase
{
    private const HONEY_POT_FIELD = 'name';
    private const HONEY_POT_FIELD_VALUE = 'SPAM';

    private ?ContainerInterface $container = null;

    public static function requestProvider(): Generator
    {
        foreach ([
            'POST with full HoneyPot' => [
                Method::POST,
                [
                    md5(self::HONEY_POT_FIELD) => self::HONEY_POT_FIELD_VALUE,
                ],
                Status::I_AM_A_TEAPOT
            ],
            'GET with full HoneyPot' => [
                Method::GET,
                [
                    md5(self::HONEY_POT_FIELD) => self::HONEY_POT_FIELD_VALUE,
                ],
                Status::OK
            ],
            'POST with empty HoneyPot' => [
                Method::POST,
                [
                    md5(self::HONEY_POT_FIELD) => '',
                ],
                Status::OK
            ],
            'POST with int HoneyPot' => [
                Method::POST,
                [
                    md5(self::HONEY_POT_FIELD) => 0,
                ],
                Status::I_AM_A_TEAPOT
            ],
            'POST with null HoneyPot' => [
                Method::POST,
                [
                    md5(self::HONEY_POT_FIELD) => null,
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

    public static function fieldProvider(): Generator
    {
        $reflection = new ReflectionClass(TestFormModel::class);
        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            $name = $property->getName();
            yield $name => ['field' => $name];
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
            new Honeypot(self::HONEY_POT_FIELD, new ResponseFactory())
        ]);

        $response = $dispatcher->dispatch($request);

         $this->assertSame($status, $response->getStatusCode());
    }

    /**
     * @throws \Yiisoft\Definitions\Exception\InvalidConfigException
     * @throws \Yiisoft\Factory\NotFoundException
     * @throws \Yiisoft\Definitions\Exception\NotInstantiableException
     * @throws \Yiisoft\Definitions\Exception\CircularReferenceException
     */
    #[dataProvider('fieldProvider')]
    public function testInput(string $field): void
    {
        WidgetFactory::initialize($this->getContainer());

        $form = new TestFormModel();
        $result = HoneyPotInput::widget()
            ->formAttribute($form, $field)
            ->render()
        ;

        $cls = get_class($form);
        $formClass = substr($cls, strrpos($cls, '\\') + 1);
        $inputId = strtolower($formClass . '-' . $field);
        $inputName = $formClass . '[' . $field . ']';
        $label = $form->getAttributeLabel($field);
        $hiddenInputName = md5($inputName);
        $honeyPotInput = <<<HONEY_POT_INPUT
<div>
<label for="$inputId">$label</label>
<input type="text" id="$inputId" name="$inputName" value>
<input type="text" id="$hiddenInputName" name="$hiddenInputName" value style="display:none;">
</div>
HONEY_POT_INPUT;

        $this->assertSame($honeyPotInput, $result);
    }

    protected function get(string $id)
    {
        return $this
            ->getContainer()
            ->get($id);
    }

    private function getContainer(): ContainerInterface
    {
        if ($this->container === null) {
            $this->container = new SimpleContainer([]);
        }

        return $this->container;
    }
}
