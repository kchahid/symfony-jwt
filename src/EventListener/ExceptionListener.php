<?php

declare(strict_types=1);

namespace App\EventListener;

use App\EventSubscriber\OauthSubscriber;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use function json_encode;
use function ucfirst;

/**
 * Class ExceptionListener
 * @package App\EventSubscriber
 *
 * @author Kamal Chahid <kchahid_@outlook.com>
 */
#[AsEventListener]
class ExceptionListener
{
    private Response $response;

    public function __construct()
    {
        $this->response = new Response();
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if ($exception instanceof AccessDeniedHttpException) {
            $this->response->setStatusCode(Response::HTTP_FORBIDDEN);
            $this->buildResponse(
                OauthSubscriber::SCOPE + $exception->getCode(),
                'Cannot validate the basic token',
                $exception->getMessage()
            );
        } else {
            $this->buildResponse(
                empty($exception->getCode()) ? 999 : $exception->getCode(),
                'Something went wrong.',
                $exception->getMessage()
            );
        }
        $event->setResponse($this->response);
    }

    private function buildResponse(int $code, string $message, string $description): void
    {
        $this->response->setContent(
            json_encode(
                [
                    'code' => $code,
                    'message' => ucfirst($message),
                    'description' => ucfirst($description)
                ],
                JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
            )
        );
    }
}
