<?php

declare(strict_types=1);

namespace App\Tests;

use App\Controller\HomePageController;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Routing\Annotation\Route;

use function json_decode;

/**
 * Class HomePageControllerTest
 * @package App\Tests
 */
class HomePageControllerTest extends TestCase
{
    private HomePageController $homePageController;

    public function setUp(): void
    {
        $this->homePageController = new HomePageController();

        parent::setUp();
    }

    public function testSuccessRouteAttribute(): void
    {
        $reflector = new ReflectionClass($this->homePageController);

        $method = $reflector->getMethod('index');
        $attributes = $method->getAttributes();

        /** @var Route $routeAttribute */
        $routeAttribute = $attributes[0]->newInstance();

        self::assertEquals('/', $routeAttribute->getPath());
        self::assertEquals('healt_check', $routeAttribute->getName());
        self::assertCount(1, $routeAttribute->getMethods());
        self::assertEquals('GET', $routeAttribute->getMethods()[0]);
    }

    public function testSuccessResponse(): void
    {
        $response = $this->homePageController->index();
        /** @phpstan-var array{code: int, message: string} $jsonData */
        $jsonData = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertEquals(200, $response->getStatusCode());
        self::assertJson($response->getContent());
        self::assertEquals(200, $jsonData['code']);
        self::assertEquals('Running', $jsonData['message']);
    }
}
