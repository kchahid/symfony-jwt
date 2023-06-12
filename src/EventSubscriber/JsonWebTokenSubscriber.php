<?php

declare(strict_types=1);

namespace App\EventSubscriber;

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

    public function processRequestJwtMiddleware(RequestEvent $event): void
    {
        $this->jsonWebToken->process($event);
    }
}
