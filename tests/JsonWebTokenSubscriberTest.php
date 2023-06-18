<?php

declare(strict_types=1);

namespace App\Tests;

use App\EventSubscriber\JsonWebTokenSubscriber;
use App\JWT\JsonWebToken;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class JsonWebTokenSubscriberTest
 * @package App\Tests
 */
class JsonWebTokenSubscriberTest extends TestCase
{
    private JsonWebTokenSubscriber $jsonWebTokenSubscriber;

    public function setUp(): void
    {
        $this->jsonWebTokenSubscriber = new JsonWebTokenSubscriber();

        parent::setUp();
    }

    public function testSuccessJsonWebTokenVerification(): void
    {
        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $jsonWebToken = $this->getMockBuilder(JsonWebToken::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->jsonWebTokenSubscriber->setJsonWebTokenSubscriber($jsonWebToken);
        $this->jsonWebTokenSubscriber->processRequestJwtMiddleware($event);

        self::assertTrue(true);
    }

    public function testSuccessSubscribedEvent(): void
    {
        $result = $this->jsonWebTokenSubscriber::getSubscribedEvents();

        self::assertArrayHasKey(KernelEvents::REQUEST, $result);
        self::assertEquals(['processRequestJwtMiddleware', 249], $result[KernelEvents::REQUEST][0]);
        self::assertCount(1, $result);
    }

    /**
     * for other cases like error, exception, success cases
     * @see JsonWebTokenTest::class
     */
}
