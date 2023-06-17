<?php

declare(strict_types=1);

namespace App\Tests;

use App\Controller\Oauth\OauthTokenController;
use App\Entity\Identity;
use App\Exception\InternalException;
use Carbon\Carbon;
use Lcobucci\JWT\Signer\InvalidKeyProvided;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

use function json_decode;
use function md5;

/**
 * Class OauthTokenTest
 * @package App\Tests
 */
class OauthTokenTest extends TestCase
{
    public function testSuccessJWTGeneration(): void
    {
        $oauth = new OauthTokenController();
        $request = new Request();
        $identity = (new Identity())
            ->setBasicSecret('lorem ipsum')
            ->setBasicKey('lorem ipsum')
            ->setStatus(true)
            ->setIssuer('lorem ipsum')
            ->setAllowedEnv(['lorem ipsum'])
            ->setCreatedAt(new Carbon())
            ->setSecret(md5('lorem ipsum'))
        ;
        $request->attributes->set('identity', $identity);
        $request->server->set('HTTP_HOST', 'lorem');

        $response = $oauth->index($request);
        $data = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertJson($response->getContent());

        self::assertArrayHasKey('code', $data);
        self::assertArrayHasKey('type', $data);
        self::assertArrayHasKey('token', $data);

        self::assertEquals(200, $data['code']);
        self::assertEquals('Bearer', $data['type']);
    }

    public function testMissingIdentityAttribute(): void
    {
        $this->expectException(InternalException::class);

        (new OauthTokenController())->index(new Request());
    }

    public function testShorterBitsJWTSecret(): void
    {
        $this->expectException(InvalidKeyProvided::class);

        $request = new Request();
        $identity = (new Identity())
            ->setBasicSecret('lorem ipsum')
            ->setBasicKey('lorem ipsum')
            ->setStatus(true)
            ->setIssuer('lorem ipsum')
            ->setAllowedEnv(['lorem ipsum'])
            ->setCreatedAt(new Carbon())
            ->setSecret('lorem ipsum')
        ;
        $request->attributes->set('identity', $identity);
        $request->server->set('HTTP_HOST', 'lorem');

        (new OauthTokenController())->index($request);
    }

    public function testMissingIssuerJWT(): void
    {
        $this->expectException(InvalidKeyProvided::class);

        $request = new Request();
        $identity = (new Identity())
            ->setBasicSecret('lorem ipsum')
            ->setBasicKey('lorem ipsum')
            ->setStatus(true)
            ->setIssuer('')
            ->setAllowedEnv(['lorem ipsum'])
            ->setCreatedAt(new Carbon())
            ->setSecret('lorem ipsum')
        ;
        $request->attributes->set('identity', $identity);
        $request->server->set('HTTP_HOST', 'lorem');

        (new OauthTokenController())->index($request);
    }
}
