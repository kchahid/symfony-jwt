<?php

declare(strict_types=1);

namespace App\Tests;

use App\Controller\Oauth\OauthTokenController;
use App\EventSubscriber\OauthSubscriber;
use App\Tests\Helper\Utils;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class OauthTokenSubscriberTest
 * @package App\Tests
 */
class OauthTokenSubscriberTest extends TestCase
{
    private OauthSubscriber $oauthSubscriber;
    private ControllerEvent&MockObject $controllerEvent;

    public function setUp(): void
    {
        $identity = Utils::getCorrectIdentityData();

        // mock cache
        $cache = $this->createMock(FilesystemAdapter::class);
        $cache
            ->method('get')
            ->willReturn($identity);

        $controller = new OauthTokenController();
        $callable = [$controller, 'index'];

        /*
         * mock controller event.
         * as it's final class, use ByPassFinalClassHook
         */
        $this->controllerEvent = $this->getMockBuilder(ControllerEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->controllerEvent
            ->method('getRequest')
            ->willReturn(new Request());

        $this->controllerEvent
            ->method('getController')
            ->willReturn($callable);

        // mock oauth subscriber
        $this->oauthSubscriber = new OauthSubscriber(
            $cache,
            $this->getMockBuilder(EntityManagerInterface::class)
                ->disableOriginalConstructor()
                ->getMock()
        );

        $this->oauthSubscriber
            ->setLogger(
                $this->getMockBuilder(LoggerInterface::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );

        parent::setUp();
    }

    public function testContentTypeHeaderIsMissing(): void
    {
        $this->expectException(AccessDeniedHttpException::class);

        try {
            $this->oauthSubscriber->onKernelController($this->controllerEvent);
        } catch (AccessDeniedHttpException $exception) {
            self::assertStringContainsString('Content type header is invalid', $exception->getMessage());

            throw $exception;
        }
    }

    public function testContentTypeHeaderIsInvalid(): void
    {
        $this->expectException(AccessDeniedHttpException::class);
        $this->controllerEvent->getRequest()->headers->set('Content-Type', 'application/json');

        try {
            $this->oauthSubscriber->onKernelController($this->controllerEvent);
        } catch (AccessDeniedHttpException $exception) {
            self::assertStringContainsString('Content type header is invalid', $exception->getMessage());

            throw $exception;
        }
    }

    public function testAuthorizationHeaderIsMissing(): void
    {
        $this->expectException(AccessDeniedHttpException::class);
        $this->controllerEvent->getRequest()->headers->set('Content-Type', 'application/x-www-form-urlencoded');

        try {
            $this->oauthSubscriber->onKernelController($this->controllerEvent);
        } catch (AccessDeniedHttpException $exception) {
            self::assertStringContainsString('Authorization header is missing', $exception->getMessage());

            throw $exception;
        }
    }

    public function testDataFormIsInvalid(): void
    {
        $this->expectException(AccessDeniedHttpException::class);

        $this->controllerEvent->getRequest()->headers->set('Content-Type', 'application/x-www-form-urlencoded');
        $this->controllerEvent->getRequest()->headers->set('Authorization', 'lorem ipsum');
        $this->controllerEvent->getRequest()->request->set('grant_type', 'lorem ipsum');

        try {
            $this->oauthSubscriber->onKernelController($this->controllerEvent);
        } catch (AccessDeniedHttpException $exception) {
            self::assertStringContainsString('Data form is invalid', $exception->getMessage());

            throw $exception;
        }
    }

    public function testAuthorizationHeaderIsInvalid(): void
    {
        $this->expectException(AccessDeniedHttpException::class);

        $this->controllerEvent->getRequest()->headers->set('Content-Type', 'application/x-www-form-urlencoded');
        $this->controllerEvent->getRequest()->headers->set('Authorization', 'lorem ipsum');
        $this->controllerEvent->getRequest()->request->set('grant_type', 'client_credentials');

        try {
            $this->oauthSubscriber->onKernelController($this->controllerEvent);
        } catch (AccessDeniedHttpException $exception) {
            self::assertStringContainsString('Authorization header is invalid', $exception->getMessage());

            throw $exception;
        }
    }

    public function testSecretBasicAuthorizationHeaderIsInvalid(): void
    {
        $this->expectException(AccessDeniedHttpException::class);

        $this->controllerEvent->getRequest()->headers->set('Content-Type', 'application/x-www-form-urlencoded');
        $this->controllerEvent->getRequest()->headers->set('Authorization', 'Basic sd==');
        $this->controllerEvent->getRequest()->request->set('grant_type', 'client_credentials');

        try {
            $this->oauthSubscriber->onKernelController($this->controllerEvent);
        } catch (AccessDeniedHttpException $exception) {
            self::assertStringContainsString('Basic secret is missing', $exception->getMessage());

            throw $exception;
        }
    }

    public function testIdentitySecretIsNotSameAsHeader(): void
    {
        $this->expectException(AccessDeniedHttpException::class);

        $this->controllerEvent->getRequest()->headers->set('Content-Type', 'application/x-www-form-urlencoded');
        $this->controllerEvent->getRequest()->headers->set('Authorization', 'Basic bG9yZW06aXBzdW0=');
        $this->controllerEvent->getRequest()->request->set('grant_type', 'client_credentials');

        try {
            $this->oauthSubscriber->onKernelController($this->controllerEvent);
        } catch (AccessDeniedHttpException $exception) {
            self::assertStringContainsString('Basic secret is invalid', $exception->getMessage());

            throw $exception;
        }
    }

    // if no exception thrown, it's success
    public function testSuccessBasicToken(): void
    {
        $this->controllerEvent->getRequest()->headers->set('Content-Type', 'application/x-www-form-urlencoded');
        $this->controllerEvent->getRequest()->headers->set('Authorization', 'Basic bG9yZW0gaXBzdW06bG9yZW0gaXBzdW0=');
        $this->controllerEvent->getRequest()->request->set('grant_type', 'client_credentials');

        $this->oauthSubscriber->onKernelController($this->controllerEvent);

        self::assertTrue(true);
    }

    public function testSuccessSubscribedEvent(): void
    {
        $result = $this->oauthSubscriber::getSubscribedEvents();

        self::assertArrayHasKey(KernelEvents::CONTROLLER, $result);
        self::assertEquals('onKernelController', $result[KernelEvents::CONTROLLER]);
        self::assertCount(1, $result);
    }
}
