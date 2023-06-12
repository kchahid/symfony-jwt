<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use App\JWT\JsonWebTokenAwareInterface;

/**
 * Class JsonWebTokenInjector
 * @package App\DependencyInjection\Compiler
 *
 * @author Kamal Chahid <kchahid_@outlook.com>
 */
class JsonWebTokenInjector extends AbstractApplicationDependencyInjector
{
    final public function getServiceName(): string
    {
        return 'jwt_event_subscriber';
    }

    final public function getInterfaceName(): string
    {
        return JsonWebTokenAwareInterface::class;
    }

    final public function getMethodeName(): string
    {
        return 'setJsonWebTokenSubscriber';
    }
}
