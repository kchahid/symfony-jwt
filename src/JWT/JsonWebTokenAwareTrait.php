<?php

declare(strict_types=1);

namespace App\JWT;

/**
 * trait JsonWebTokenAwareTrait
 * @package App\Service\JWT
 *
 * @author Kamal Chahid <kchahid_@outlook.com>
 */
trait JsonWebTokenAwareTrait
{
    protected JsonWebToken $jsonWebToken;

    public function setJsonWebTokenSubscriber(JsonWebToken $jsonWebToken): void
    {
        $this->jsonWebToken = $jsonWebToken;
    }
}
