<?php

declare(strict_types=1);

namespace App\Tests;

use App\Exception\JsonWebTokenException;
use App\JWT\JsonWebToken;
use App\Tests\Helper\JsonWebTokenTestHelper;
use App\Tests\Helper\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use function sprintf;

/**
 * Class JsonWebTokenTest
 * @package App\Tests
 */
class JsonWebTokenTest extends TestCase
{
    private const DURATION = 3600;
    private const SCOPE = 4030;

    private JsonWebToken $jsonWebToken;
    private RequestEvent $request;

    public function setUp(): void
    {
        $this->buildJsonWebTokenClass();
        $httpKernel = $this->getMockBuilder(HttpKernelInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = new RequestEvent($httpKernel, new Request(), null);
        $this->request->getRequest()->server->set('HTTP_HOST', 'lorem ipsum');
    }

    public function testMissingAuthorizationHeader(): void
    {
        $this->expectException(JsonWebTokenException::class);

        try {
            $this->jsonWebToken->process($this->request);
        } catch (JsonWebTokenException $exception) {
            self::assertEquals(self::SCOPE + JsonWebTokenException::JWT, $exception->getCode());
            self::assertStringContainsString('JWT is invalid/missing', $exception->getMessage());

            throw $exception;
        }
    }

    public function testEmptyAuthorizationHeader(): void
    {
        $this->expectException(JsonWebTokenException::class);

        $this->request->getRequest()->headers->set('Authorization', 'Bearer');

        try {
            $this->jsonWebToken->process($this->request);
        } catch (JsonWebTokenException $exception) {
            self::assertEquals(self::SCOPE + JsonWebTokenException::JWT, $exception->getCode());
            self::assertStringContainsString('JWT is invalid/missing', $exception->getMessage());

            throw $exception;
        }
    }

    public function testInvlaidJsonWebTokenValue(): void
    {
        $this->expectException(JsonWebTokenException::class);

        $this->request->getRequest()->headers->set('Authorization', 'lorem ipsum');

        try {
            $this->jsonWebToken->process($this->request);
        } catch (JsonWebTokenException $exception) {
            self::assertEquals(self::SCOPE + JsonWebTokenException::JWT, $exception->getCode());
            self::assertStringContainsString('JWT is invalid/missing', $exception->getMessage());

            throw $exception;
        }
    }

    public function testMissingSubject(): void
    {
        $this->expectException(JsonWebTokenException::class);

        /** @phpstan-var string $tokenAsString */
        $tokenAsString = JsonWebTokenTestHelper::getJWTWithoutSubject();
        $this->request->getRequest()->headers->set('Authorization', sprintf('Bearer %s', $tokenAsString));

        try {
            $this->jsonWebToken->process($this->request);
        } catch (JsonWebTokenException $exception) {
            self::assertEquals(self::SCOPE + JsonWebTokenException::SUBJECT, $exception->getCode());
            self::assertStringContainsString('Subject is invalid/missing', $exception->getMessage());

            throw $exception;
        }
    }

    public function testSecretIsInvalid(): void
    {
        $this->expectException(JsonWebTokenException::class);
        $identity = Utils::getIdentityWithDummySecret();

        // mock repository
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->method('findOneBy')
            ->willReturn($identity);

        // mock manager
        $objectManager = $this->createMock(EntityManagerInterface::class);
        $objectManager->method('getRepository')
            ->willReturn($repository);

        // mock cache
        $cache = $this->createMock(FilesystemAdapter::class);
        $cache->method('get')
            ->willReturn($identity);

        /** @phpstan-var string $tokenAsString */
        $tokenAsString = JsonWebTokenTestHelper::getValidJWT();
        $this->request->getRequest()->headers->set('Authorization', sprintf('Bearer %s', $tokenAsString));

        try {
            $this->buildJsonWebTokenClass($objectManager, $cache);
            $this->jsonWebToken->process($this->request);
        } catch (JsonWebTokenException $exception) {
            self::assertEquals(self::SCOPE + JsonWebTokenException::JWT, $exception->getCode());
            self::assertStringContainsString('JWT is invalid/missing', $exception->getMessage());

            throw $exception;
        }
    }

    public function testExpiredJsonWebToken(): void
    {
        $this->expectException(JsonWebTokenException::class);
        $identity = Utils::getCorrectIdentityData();

        // mock repository
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->method('findOneBy')
            ->willReturn($identity);

        // mock manager
        $objectManager = $this->createMock(EntityManagerInterface::class);
        $objectManager->method('getRepository')
            ->willReturn($repository);

        // mock cache
        $cache = $this->createMock(FilesystemAdapter::class);
        $cache->method('get')
            ->willReturn($identity);

        /** @phpstan-var string $tokenAsString */
        $tokenAsString = JsonWebTokenTestHelper::getExpiredJWT();
        $this->request->getRequest()->headers->set('Authorization', sprintf('Bearer %s', $tokenAsString));

        try {
            $this->buildJsonWebTokenClass($objectManager, $cache);
            $this->jsonWebToken->process($this->request);
        } catch (JsonWebTokenException $exception) {
            self::assertEquals(self::SCOPE + JsonWebTokenException::JWT, $exception->getCode());
            self::assertStringContainsString('JWT is invalid/missing', $exception->getMessage());

            throw $exception;
        }
    }

    public function testJTIAlreadyExist(): void
    {
        $this->expectException(JsonWebTokenException::class);
        $identity = Utils::getCorrectIdentityData();

        // mock repository
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->method('findOneBy')
            ->willReturn($identity);

        // mock manager
        $objectManager = $this->createMock(EntityManagerInterface::class);
        $objectManager->method('getRepository')
            ->willReturn($repository);

        // mock cache
        $cache = $this->createMock(FilesystemAdapter::class);
        $cache->method('get')
            ->willReturn($identity);

        /** @phpstan-var string $tokenAsString */
        $tokenAsString = JsonWebTokenTestHelper::getValidJWTWithJTI();
        $this->request->getRequest()->headers->set('Authorization', sprintf('Bearer %s', $tokenAsString));

        try {
            $this->buildJsonWebTokenClass($objectManager, $cache);
            $this->jsonWebToken->process($this->request);
        } catch (JsonWebTokenException $exception) {
            self::assertEquals(self::SCOPE + JsonWebTokenException::JTI, $exception->getCode());
            self::assertStringContainsString('JWT already used', $exception->getMessage());

            throw $exception;
        }
    }

    /*
     * if no error was throwen, means the test is succesful
     */
    public function testSuccessJWT(): void
    {
        $identity = Utils::getCorrectIdentityData();

        // mock repository
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->method('findOneBy')
            ->willReturn($identity);

        // mock manager
        $objectManager = $this->createMock(EntityManagerInterface::class);
        $objectManager->method('getRepository')
            ->willReturn($repository);

        // mock cache
        $cache = $this->createMock(FilesystemAdapter::class);
        $cache->method('get')
            ->willReturn($identity);

        /** @phpstan-var string $tokenAsString */
        $tokenAsString = JsonWebTokenTestHelper::getValidJWT();
        $this->request->getRequest()->headers->set('Authorization', sprintf('Bearer %s', $tokenAsString));

        $this->buildJsonWebTokenClass($objectManager, $cache);
        $this->jsonWebToken->process($this->request);

        self::assertTrue(true);
    }

    private function buildJsonWebTokenClass(?MockObject $em = null, ?MockObject $cache = null): void
    {
        $em ??= $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cache ??= $this->getMockBuilder(FilesystemAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        /**
         * @phpstan-var MockObject&EntityManagerInterface $em
         * @phpstan-var MockObject&FilesystemAdapter $cache
         */
        $this->jsonWebToken = new JsonWebToken($em, $cache, self::DURATION);
        $this->jsonWebToken->setLogger($logger);
    }
}
