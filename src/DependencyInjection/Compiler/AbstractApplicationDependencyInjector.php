<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use ReflectionClass;
use ReflectionException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;
use Throwable;

/**
 * Class AbstractApplicationDependencyInjector
 * @package App\DependencyInjection
 *
 * @author Kamal Chahid <kchahid_@outlook.com>
 */
abstract class AbstractApplicationDependencyInjector implements CompilerPassInterface, InjectorInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition($this->getServiceName())) {
            return;
        }

        foreach ($container->getServiceIds() as $serviceId) {
            try {
                $definition = $container->getDefinition($serviceId);
            } catch (ServiceNotFoundException $e) {
                continue;
            }

            try {
                /** @phpstan-var class-string $definitionClass */
                $definitionClass = $definition->getClass();
                $reflectedServiceClass = new ReflectionClass($definitionClass);
            } catch (ReflectionException | Throwable $e) {
                continue;
            }

            if (!$reflectedServiceClass->isSubclassOf($this->getInterfaceName())) {
                continue;
            }
            $definition->addMethodCall($this->getMethodeName(), [new Reference($this->getServiceName())]);
        }
    }
}
