<?php

declare(strict_types=1);

namespace App\Tests;

use App\Controller\Oauth\OauthTokenController;
use App\Entity\Identity;
use App\Exception\InternalException;
use App\Tests\Helper\Utils;
use Lcobucci\JWT\Signer\InvalidKeyProvided;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use TypeError;

use function json_decode;

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

        $this->identity = Utils::getCorrectIdentityData();

        $this->request->server->set('HTTP_HOST', 'lorem ipsum');

        parent::setUp();
    }

    public function testSuccessJWTGeneration(): void
    {
        $this->request->attributes->set('identity', $this->identity);
        $response = $this->oauth->index($this->request);

        /** @phpstan-var  string $responseData */
        $responseData = $response->getContent();
        $data = json_decode($responseData, true, 512, JSON_THROW_ON_ERROR);

        self::assertJson($responseData);

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
