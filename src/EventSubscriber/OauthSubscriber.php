<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Contract\IdentityContractTrait;
use App\Controller\Oauth\TokenAuthenticatedController;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Cache\CacheInterface;

use function base64_decode;
use function explode;
use function is_array;
use function is_string;
use function sprintf;
use function substr;

/**
 * Class OauthSubscriber
 * @package App\EventSubscriber
 *
 * @author Kamal Chahid <kchahid_@outlook.com>
 */
class OauthSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    use IdentityContractTrait;

    private Request $request;

    public function __construct(
        private readonly CacheInterface $cache,
        private readonly EntityManagerInterface $em
    ) {
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (is_array($controller)) {
            $controller = $controller[0];
        }

        if ($controller instanceof TokenAuthenticatedController) {
            $this->request = $event->getRequest();
            if ($this->request->headers->get('content-type', '') !== 'application/x-www-form-urlencoded') {
                throw new AccessDeniedHttpException('content type header is invalid');
            }

            if ($this->request->headers->has('Authorization') === false) {
                throw new AccessDeniedHttpException('Authorization header is missing');
            }

            if ($this->request->request->getString('grant_type', '') !== 'client_credentials') {
                throw new AccessDeniedHttpException('data form is invalid');
            }

            $basicToken = $event->getRequest()->headers->get('authorization');
            if (($token = base64_decode(substr($basicToken, 6), true)) === false) {
                throw new AccessDeniedHttpException('Authorization header is invalid');
            }

            [$key, $secret] = explode(':', $token);
            if (!is_string($key)) {
                throw new AccessDeniedHttpException('basic key is missing');
            }

            if (!is_string($secret)) {
                throw new AccessDeniedHttpException('basic secret is missing');
            }

            $identity = $this->getIdentity($key, $this->em, $this->logger, true);
            if ($secret !== $identity->getBasicSecret()) {
                throw new AccessDeniedHttpException('basic secret is invalid');
            }

            $this->logger->info(sprintf('Identity verified (%s)', $identity->getIssuer()));
            $this->request->attributes->set('identity', $identity);
        }
    }

    /**
     * @return array <mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController'
        ];
    }
}
