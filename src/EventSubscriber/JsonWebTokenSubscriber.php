<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Contract\IdentityContractTrait;
use App\JWT\JsonWebTokenAwareInterface;
use App\JWT\JsonWebTokenAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class JsonWebTokenSubscriber
 * @package App\EventSubscriber
 *
 * @author Kamal Chahid <kchahid_@outlook.com>
 */
class JsonWebTokenSubscriber implements EventSubscriberInterface, JsonWebTokenAwareInterface
{
    use JsonWebTokenAwareTrait;
    use IdentityContractTrait;

    public function processRequestJwtMiddleware(RequestEvent $event): void
    {
        // ignore oauth endpoint
        if ($event->getRequest()->getPathInfo() === '/oauth/token') {
            return;
        }
        $this->jsonWebToken->process($event);
    }

    /**
     * @return array <mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['processRequestJwtMiddleware', 249]
            ]
        ];
    }
}
