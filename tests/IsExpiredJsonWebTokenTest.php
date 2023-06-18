<?php

declare(strict_types=1);

namespace App\Tests;

use App\JWT\IsExpired;
use App\Tests\Helper\JsonWebTokenTestHelper;
use DateTimeImmutable;
use Lcobucci\JWT\Token;
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
        /** @var Token $token */
        $token = JsonWebTokenTestHelper::getValidJWT(true);
        $this->isExpired->assert($token);

        static::assertTrue(true);
    }

    public function testJsonWebTokenExpired(): void
    {
        $this->expectException(ConstraintViolation::class);

        /** @var Token $token */
        $token = JsonWebTokenTestHelper::getExpiredJWT(true);
        $this->isExpired->assert($token);
    }

    public function testJsonWebTokenIsNotValidYet(): void
    {
        $this->expectException(ConstraintViolation::class);

        $this->isExpired = new IsExpired(self::DURATION, null, (new DateTimeImmutable())->modify('+1 day'));
        /** @var Token $token */
        $token = JsonWebTokenTestHelper::getValidJWT(true);
        $this->isExpired->assert($token);
    }

    public function testJsonWebTokenExpiredBeforDuration(): void
    {
        $this->expectException(ConstraintViolation::class);

        $this->isExpired = new IsExpired(self::DURATION, (new DateTimeImmutable())->modify('-30 minute'));
        /** @var Token $token */
        $token = JsonWebTokenTestHelper::getValidJWT(true);
        $this->isExpired->assert($token);
    }
}
