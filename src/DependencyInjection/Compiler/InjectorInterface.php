<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

/**
 * interface InjectorInterface
 * @package App\DependencyInjection
 *
 * @author Kamal Chahid <kchahid_@outlook.com>
 */
interface InjectorInterface
{
    public function getServiceName(): string;

    public function getInterfaceName(): string;

    public function getMethodeName(): string;
}
