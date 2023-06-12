<?php

declare(strict_types=1);

namespace App\JWT;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Class JsonWebToken
 * @package App\JWT
 *
 * @author Kamal Chahid <kchahid_@outlook.com>
 */
class JsonWebToken implements LoggerAwareInterface
{
    use LoggerAwareTrait;

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
    }
}
