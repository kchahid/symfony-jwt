<?php

declare(strict_types=1);

namespace App\JWT;

use App\Contract\IdentityContractTrait;
use App\Exception\JsonWebTokenException;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint\RelatedTo;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Lcobucci\JWT\Validation\Validator;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use RuntimeException;
use Symfony\Component\Cache\PruneableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Contracts\Cache\ItemInterface;

use function sprintf;
use function substr;

/**
 * Class JsonWebToken
 * @package App\JWT
 *
 * @author Kamal Chahid <kchahid_@outlook.com>
 */
class JsonWebToken implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    use IdentityContractTrait;

    private Request $request;
    private Validator $validator;
    private string $authorizationHeader;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PruneableInterface $cache,
        private readonly int $duration
    ) {
        $this->validator = new Validator();
    }

    /*
     * iss (issuer): Issuer of the JWT
     * sub (subject): Subject of the JWT (the user)
     * aud (audience): Recipient for which the JWT is intended
     * exp (expiration time): Time after which the JWT expires
     * nbf (not before time): Time before which the JWT must not be accepted for processing
     * iat (issued at time): Time at which the JWT was issued; can be used to determine age of the JWT
     * jti (JWT ID): Unique identifier; can be used to prevent the JWT from being replayed (allows a token to be used only once)
    */
    final public function process(RequestEvent $event): void
    {
        $this->init($event->getRequest());

        $parser = new Parser(new JoseEncoder());
        try {
            /** @var Plain $token */
            $token = $parser->parse($this->authorizationHeader);
        } catch (InvalidArgumentException | RuntimeException $e) {
            $this->logger->notice('Invalid Jwt Token provided');
            $this->logger->debug(sprintf('end with error: %s', $e->getMessage()));
            throw new JsonWebTokenException(JsonWebTokenException::JWT);
        }

        $this->subjectGuard($token);
        $identity = $this->getIdentityByIssuer($token->claims()->get('iss', null), $this->logger);

        try {
            $this->validator->assert($token, new SignedWith(new Sha256(), InMemory::plainText($identity->getSecret())));
            $this->validator->assert($token, new IsExpired($this->duration));
        } catch (RequiredConstraintsViolated $exception) {
            $this->logger->info(sprintf('Identity verification failed (%s)', $identity->getIssuer()));
            $this->logger->notice($exception->getMessage());
            throw new JsonWebTokenException(JsonWebTokenException::JWT, $exception);
        }

        if ($token->claims()->has('jti')) {
            $this->jtiGuard();
        }
        $this->logger->info(sprintf('Identity verified (%s)', $token->claims()->get('iss')));
    }

    private function init(Request $request): void
    {
        $this->request = $request;
        $this->authorizationHeader = $this->request->headers->get('authorization');

        if (empty($this->authorizationHeader)) {
            $this->logger->notice('Authorization header is missing');
            throw new JsonWebTokenException(JsonWebTokenException::JWT);
        }

        /*
         * remove bearer from authorization header value
         */
        $this->authorizationHeader = substr($this->authorizationHeader, 7);
        if (empty($this->authorizationHeader)) {
            $this->logger->info('Token is missing.');
            throw new JsonWebTokenException(JsonWebTokenException::JWT);
        }
    }

    private function subjectGuard(Plain $token): void
    {
        if ($token->claims()->has('sub') === false) {
            $this->logger->info('Subject could not be verified.');
            $this->logger->notice('No claim sub provided in JWT');
            throw new JsonWebTokenException(JsonWebTokenException::SUBJECT);
        }

        try {
            $this->validator->assert($token, new RelatedTo($this->request->server->get('HTTP_HOST')));
        } catch (RequiredConstraintsViolated $exception) {
            $this->logger->notice(sprintf('Invalid JWT Subject (%s)', $token->claims()->get('sub')));
            throw new JsonWebTokenException(JsonWebTokenException::SUBJECT, $exception);
        }
    }

    private function jtiGuard(): void
    {
        $jtiExist = true;
        $this->cache->get($this->authorizationHeader, function (ItemInterface $item) use (&$jtiExist) {
            $this->logger->info('JTI was provided.');
            $item->expiresAfter($this->duration);
            $jtiExist = false;
            return $item->getKey();
        }, 1.0);

        if ($jtiExist === true) {
            throw new JsonWebTokenException(JsonWebTokenException::JTI);
        }
    }
}
