<?php

declare(strict_types=1);

namespace App\JWT;

use Carbon\Carbon;
use DateTimeImmutable;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\JWT\Validation\ConstraintViolation;

/**
 * Class IsExpired
 * @package App\Service\JWT
 *
 * @author Kamal Chahid <kchahid_@outlook.com>
 */
final readonly class IsExpired implements Constraint
{
    public function __construct(
        private int $duration,
        private ?DateTimeImmutable $exp = null,
        private ?DateTimeImmutable $nbf = null
    ) {
    }

    public function assert(Token $token): void
    {
        /** @phpstan-var UnencryptedToken $token */
        $iat = $token->claims()->get('iat', new DateTimeImmutable());
        $now = Carbon::now();

        if ($this->nbf !== null && $now < (clone $now)->setTimestamp($this->nbf->getTimestamp())) {
            throw new ConstraintViolation('JWToken is not valid');
        }

        if (
            ($this->exp !== null && $now > (clone $now)->setTimestamp($this->exp->getTimestamp())) ||
            $now > (clone $now)->setTimestamp($iat->getTimestamp())->addSeconds($this->duration)
        ) {
            throw new ConstraintViolation('JWToken is expired');
        }
    }
}
