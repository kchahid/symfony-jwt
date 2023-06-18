<?php

declare(strict_types=1);

namespace App\Tests;

use App\DependencyInjection\Compiler\JsonWebTokenInjector;
use App\JWT\JsonWebTokenAwareInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class JsonWebTokenInjectorTest
 * @package App\Tests
 */
class JsonWebTokenInjectorTest extends TestCase
{
    private JsonWebTokenInjector $jsonWebTokenInjector;

    public function setUp(): void
    {
        $this->jsonWebTokenInjector = new JsonWebTokenInjector();

        parent::setUp();
    }

    public function testSuccessServiceName(): void
    {
        self::assertEquals('jwt_event_subscriber', $this->jsonWebTokenInjector->getServiceName());
    }

    public function testSuccessInterfaceName(): void
    {
        self::assertEquals(JsonWebTokenAwareInterface::class, $this->jsonWebTokenInjector->getInterfaceName());
    }

    public function testSucessMethodName(): void
    {
        self::assertEquals('setJsonWebTokenSubscriber', $this->jsonWebTokenInjector->getMethodeName());
    }
}
