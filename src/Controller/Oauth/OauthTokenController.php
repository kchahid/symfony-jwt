<?php

declare(strict_types=1);

namespace App\Controller\Oauth;

use App\Entity\Identity;
use App\Exception\InternalException;
use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class OauthTokenController
 * @package App\Controller
 *
 * @author Kamal Chahid <kchahid_@outlook.com>
 */
class OauthTokenController extends AbstractController implements TokenAuthenticatedController
{
    #[Route(path: 'oauth/token', name: 'jwt_token_generation', methods: ['POST'])]
    public function index(Request $request): JsonResponse
    {
        /** @phpstan-var Identity|null $identity */
        $identity = $request->get('identity', null);
        if ($identity === null) {
            throw new InternalException('Identity is missing');
        }

        /** @phpstan-var non-empty-string $secret */
        $secret = $identity->getSecret();
        $configuration = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText($secret));

        /** @phpstan-var non-empty-string $issuer */
        $issuer = $identity->getIssuer();
        $token = $configuration->builder()
            ->withHeader('alg', 'HS256')
            ->withHeader('typ', 'JWT')
            ->issuedAt(new DateTimeImmutable())
            ->issuedBy($issuer)
            ->relatedTo($request->server->get('HTTP_HOST'));
        return new JsonResponse(
            [
                'code' => 200,
                'type' => 'Bearer',
                'token' => $token->getToken(new Sha256(), InMemory::plainText($secret))->toString()
            ],
            JsonResponse::HTTP_OK
        );
    }
}
