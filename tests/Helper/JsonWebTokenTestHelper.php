<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use DateTimeImmutable;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token;

use function md5;

/**
 * Class JsonWebTokenTestHelper
 * @package App\Tests\Helper
 */
final class JsonWebTokenTestHelper
{
    /**
     * @return non-empty-string
     */
    private static function getSecret(): string
    {
        return md5('lorem ipsum');
    }

    public static function getValidJWT(bool $asObject = false): string|Token
    {
        return self::jsonWebToken(
            Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText(self::getSecret()))
                ->builder()
                ->withHeader('alg', 'HS256')
                ->withHeader('typ', 'JWT')
                ->issuedBy('lorem ipsum')
                ->issuedAt(new DateTimeImmutable())
                ->relatedTo('lorem ipsum'),
            $asObject
        );
    }

    public static function getExpiredJWT(bool $asObject = false): string|Token
    {
        return self::jsonWebToken(
            Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText(self::getSecret()))
                ->builder()
                ->withHeader('alg', 'HS256')
                ->withHeader('typ', 'JWT')
                ->issuedBy('lorem ipsum')
                ->issuedAt((new DateTimeImmutable())->modify('-1 day'))
                ->relatedTo('lorem ipsum'),
            $asObject
        );
    }

    public static function getJWTWithoutSubject(bool $asObject = false): string|Token
    {
        return self::jsonWebToken(
            Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText(self::getSecret()))
                ->builder()
                ->withHeader('alg', 'HS256')
                ->withHeader('typ', 'JWT')
                ->issuedBy('lorem ipsum')
                ->issuedAt(new DateTimeImmutable()),
            $asObject
        );
    }

    private static function jsonWebToken(Builder $builder, bool $asObject = false): string|Token
    {
        return match ($asObject) {
            true => $builder->getToken(new Sha256(), InMemory::plainText(self::getSecret())),
            default => $builder->getToken(new Sha256(), InMemory::plainText(self::getSecret()))->toString()
        };
    }
}
