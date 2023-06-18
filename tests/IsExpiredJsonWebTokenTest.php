<?php

declare(strict_types=1);

namespace App\Tests;

use App\JWT\IsExpired;
use App\Tests\Helper\JsonWebTokenTestHelper;
use DateTimeImmutable;
use Lcobucci\JWT\Validation\ConstraintViolation;
use PHPUnit\Framework\TestCase;

/**
 * Class IsExpiredJsonWebTokenTest
 * @package App\Tests
 */
class IsExpiredJsonWebTokenTest extends TestCase
{
    private const DURATION = 3600;

    private IsExpired $isExpired;

    public function setUp(): void
    {
        $this->isExpired = new IsExpired(self::DURATION);

        parent::setUp();
    }

    public function testSuccessIssuedAtTime(): void
    {
        $this->isExpired->assert(JsonWebTokenTestHelper::getValidJWT(true));

        static::assertTrue(true);
    }

    public function testJsonWebTokenExpired(): void
    {
        $this->expectException(ConstraintViolation::class);

        $this->isExpired->assert(JsonWebTokenTestHelper::getExpiredJWT(true));
    }

    public function testJsonWebTokenIsNotValidYet(): void
    {
        $this->expectException(ConstraintViolation::class);

        $this->isExpired = new IsExpired(self::DURATION, null, (new DateTimeImmutable())->modify('+1 day'));
        $this->isExpired->assert(JsonWebTokenTestHelper::getValidJWT(true));
    }

    public function testJsonWebTokenExpiredBeforDuration(): void
    {
        $this->expectException(ConstraintViolation::class);

        $this->isExpired = new IsExpired(self::DURATION, (new DateTimeImmutable())->modify('-30 minute'));
        $this->isExpired->assert(JsonWebTokenTestHelper::getValidJWT(true));
    }
}
