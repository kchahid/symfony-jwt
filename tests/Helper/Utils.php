<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use App\Entity\Identity;
use Carbon\Carbon;

use function md5;

/**
 * Class Utils
 * @package App\Tests\Helper
 */
final class Utils
{
    public static function getCorrectIdentityData(): Identity
    {
        return (new Identity())
            ->setBasicSecret('lorem ipsum')
            ->setBasicKey('lorem ipsum')
            ->setStatus(true)
            ->setIssuer('lorem ipsum')
            ->setAllowedEnv(['lorem ipsum'])
            ->setCreatedAt(new Carbon())
            ->setSecret(md5('lorem ipsum'));
    }

    public static function getIdentityWithDummySecret(): Identity
    {
        return (new Identity())
            ->setBasicSecret('lorem ipsum')
            ->setBasicKey('lorem ipsum')
            ->setStatus(true)
            ->setIssuer('lorem ipsum')
            ->setAllowedEnv(['lorem ipsum'])
            ->setCreatedAt(new Carbon())
            ->setSecret(md5('dummy secret'));
    }
}
