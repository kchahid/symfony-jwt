<?php

declare(strict_types=1);

namespace App\JWT;

/**
 * interface JsonWebTokenAwareInterface
 * @package App\Service\JWT
 *
 * @author Kamal Chahid <kchahid_@outlook.com>
 */
interface JsonWebTokenAwareInterface
{
    public function setJsonWebTokenSubscriber(JsonWebToken $jsonWebToken): void;
}
