<?php

declare(strict_types=1);

namespace App;

use App\DependencyInjection\Compiler\JsonWebTokenInjector;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

/**
 * Class Kernel
 * @package App
 *
 * @author Kamal Chahid <kchahid_@outlook.com>
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new JsonWebTokenInjector());

        parent::build($container);
    }
}
