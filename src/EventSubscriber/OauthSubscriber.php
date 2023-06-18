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
use Throwable;

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

    public const SCOPE = 4300;

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
                throw new AccessDeniedHttpException('Content type header is invalid', null, 1);
            }

            if ($this->request->headers->has('Authorization') === false) {
                throw new AccessDeniedHttpException('Authorization header is missing', null, 2);
            }

            if ($this->request->request->getString('grant_type', '') !== 'client_credentials') {
                throw new AccessDeniedHttpException('Data form is invalid', null, 3);
            }

            $basicToken = $event->getRequest()->headers->get('Authorization');
            if (($token = base64_decode(substr($basicToken, 6), true)) === false) {
                throw new AccessDeniedHttpException('Authorization header is invalid', null, 4);
            }

            try {
                [$key, $secret] = explode(':', $token);
            } catch (Throwable $exception) {
                $this->logger->notice(sprintf('Basic token is invalid. End with Error %s', $exception->getMessage()));
            }

            if (!isset($key) || !is_string($key)) {
                throw new AccessDeniedHttpException('Basic key is missing', null, 6);
            }

            if (!isset($secret) || !is_string($secret)) {
                throw new AccessDeniedHttpException('Basic secret is missing', null, 7);
            }

            $identity = $this->getIdentity($key, $this->em, $this->logger, true);
            if ($secret !== $identity->getBasicSecret()) {
                throw new AccessDeniedHttpException('Basic secret is invalid', null, 8);
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
