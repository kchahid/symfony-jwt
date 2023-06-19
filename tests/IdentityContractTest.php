<?php

declare(strict_types=1);

namespace App\Tests;

use App\Contract\IdentityContractTrait;
use App\Exception\InternalException;
use App\Exception\JsonWebTokenException;
use App\Tests\Helper\Utils;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;

use function md5;

/**
 * Class IdentityContractTest
 * @package App\Tests
 */
class IdentityContractTest extends TestCase
{
    use IdentityContractTrait;

    private const SCOPE = 4030;

    private ?FilesystemAdapter $cache = null;
    private LoggerInterface $logger;
    private EntityManagerInterface $em;
    private Request $request;

    public function setUp(): void
    {
        $identity = Utils::getCorrectIdentityData();

        // mock cache
        $this->cache = $this->createMock(FilesystemAdapter::class);
        $this->cache->method('get')
            ->willReturn($identity);

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->em = $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = new Request();
        $this->request->server->set('APP_ENV', 'lorem ipsum');

        parent::setUp();
    }

    public function testSuccessGetIdentityFromCache(): void
    {
        $identity = $this->getIdentity('lorem ipsum', $this->em, $this->logger);

        self::assertEquals('lorem ipsum', $identity->getIssuer());
        self::assertContains('lorem ipsum', $identity->getAllowedEnv());
        self::assertEquals(md5('lorem ipsum'), $identity->getSecret());
    }

    public function testEmptyIssuer(): void
    {
        $this->expectException(JsonWebTokenException::class);
        try {
            $this->getIdentityByIssuer(null, $this->em, $this->logger);
        } catch (JsonWebTokenException $e) {
            self::assertEquals(self::SCOPE + JsonWebTokenException::ISSUER, $e->getCode());
            throw $e;
        }
    }

    public function testRequestOrCachePropertyMissing(): void
    {
        $this->expectException(InternalException::class);
        $this->cache = null;
        $this->getIdentityByIssuer('lorem ipsum', $this->em, $this->logger);
    }
}
