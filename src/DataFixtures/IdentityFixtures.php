<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Identity;
use Carbon\Carbon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class IdentityFixtures
 * @package App\DataFixtures
 *
 * @author Kamal Chahid <kchahid_@outlook.com>
 */
class IdentityFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $identity = new Identity();
        $identity
            ->setIssuer('test')
            ->setAllowedEnv(['dev'])
            ->setSecret('e50280f9f89484b79afbc10308a17281')
            ->setCreatedAt(Carbon::now())
            ->setStatus(true);

        $manager->persist($identity);
        $manager->flush();
    }
}
