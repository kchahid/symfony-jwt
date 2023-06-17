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
use TypeError;

use function json_decode;
use function md5;

/**
 * Class OauthTokenTest
 * @package App\Tests
 */
class OauthTokenTest extends TestCase
{
    private OauthTokenController $oauth;
    private Request $request;
    private Identity $identity;

    public function setUp(): void
    {
        $this->oauth = new OauthTokenController();
        $this->request = new Request();

        $this->identity = (new Identity())
            ->setBasicSecret('lorem ipsum')
            ->setBasicKey('lorem ipsum')
            ->setStatus(true)
            ->setIssuer('lorem ipsum')
            ->setAllowedEnv(['lorem ipsum'])
            ->setCreatedAt(new Carbon())
            ->setSecret(md5('lorem ipsum'))
        ;

        $this->request->server->set('HTTP_HOST', 'lorem ipsum');

        parent::setUp();
    }

    public function testSuccessJWTGeneration(): void
    {
        $this->request->attributes->set('identity', $this->identity);
        $response = $this->oauth->index($this->request);
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
        // override secret
        $this->identity->setSecret('lorem ipsum');

        $this->request->attributes->set('identity', $this->identity);
        (new OauthTokenController())->index($this->request);
    }

    public function testMissingIssuerJWT(): void
    {
        $this->expectException(TypeError::class);
        // override issuer
        $this->identity->setIssuer(null);

        $this->request->attributes->set('identity', $this->identity);
        (new OauthTokenController())->index($this->request);
    }
}
