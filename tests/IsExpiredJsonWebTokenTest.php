<?php

declare(strict_types=1);

namespace App\Tests;

use App\JWT\IsExpired;
use DateTimeImmutable;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\ConstraintViolation;
use PHPUnit\Framework\TestCase;

use function md5;

/**
 * Class IsExpiredJsonWebTokenTest
 * @package App\Tests
 */
class IsExpiredJsonWebTokenTest extends TestCase
{
    private const DURATION = 3600;

    private string $secret;
    private Configuration $configuration;
    private IsExpired $isExpired;

    public function setUp(): void
    {
        $this->secret = md5('lorem ipsum');
        $this->isExpired = new IsExpired(self::DURATION);
        $this->configuration = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText($this->secret));

        parent::setUp();
    }

    public function testSuccessIssuedAtTime(): void
    {
        $this->isExpired->assert(
            $this->getToken(
                $this->configuration->builder()
                    ->withHeader('alg', 'HS256')
                    ->withHeader('typ', 'JWT')
                    ->issuedBy('lorem upsum')
                    ->issuedAt(new DateTimeImmutable())
                    ->relatedTo('lorem ipsum')
            )
        );

        static::assertTrue(true);
    }

    public function testJsonWebTokenExpired(): void
    {
        $this->expectException(ConstraintViolation::class);

        $this->isExpired->assert(
            $this->getToken(
                $this->configuration->builder()
                    ->withHeader('alg', 'HS256')
                    ->withHeader('typ', 'JWT')
                    ->issuedBy('lorem upsum')
                    ->issuedAt((new DateTimeImmutable())->modify('-1 day'))
                    ->relatedTo('lorem ipsum')
            )
        );
    }

    public function testJsonWebTokenIsNotValidYet(): void
    {
        $this->expectException(ConstraintViolation::class);

        $this->isExpired = new IsExpired(self::DURATION, null, (new DateTimeImmutable())->modify('+1 day'));
        $this->isExpired->assert(
            $this->getToken(
                $this->configuration->builder()
                    ->withHeader('alg', 'HS256')
                    ->withHeader('typ', 'JWT')
                    ->issuedBy('lorem upsum')
                    ->issuedAt(new DateTimeImmutable())
                    ->relatedTo('lorem ipsum')
            )
        );
    }

    public function testJsonWebTokenExpiredBeforDuration(): void
    {
        $this->expectException(ConstraintViolation::class);

        $this->isExpired = new IsExpired(self::DURATION, (new DateTimeImmutable())->modify('-30 minute'));
        $this->isExpired->assert(
            $this->getToken(
                $this->configuration->builder()
                    ->withHeader('alg', 'HS256')
                    ->withHeader('typ', 'JWT')
                    ->issuedBy('lorem upsum')
                    ->issuedAt(new DateTimeImmutable())
                    ->relatedTo('lorem ipsum')
            )
        );
    }

    private function getToken(Builder $builder): Token
    {
        return $builder->getToken(new Sha256(), InMemory::plainText($this->secret));
    }
}
