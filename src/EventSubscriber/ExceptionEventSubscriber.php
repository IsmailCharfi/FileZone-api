<?php

namespace App\EventSubscriber;

use App\Controller\AbstractApiController;
use App\Enum\ResponseMessageEnum;
use App\Exception\AbstractAllowedMessageException;
use App\Exception\FormValidationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionEventSubscriber implements EventSubscriberInterface
{
    public function handleHttpException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        // no action in dev mode
        $host = $event->getRequest()->getHost();

        if (in_array($host, ['localhost', '127.0.0.1']) && !$exception instanceof FormValidationException) {
            return;
        }


        if ($exception instanceof NotFoundHttpException) {
            $event->setResponse(AbstractApiController::jsonResponse(
                [],
                Response::HTTP_NOT_FOUND,
                ResponseMessageEnum::NOT_FOUND)
            );
            return;
        }

        if ($exception instanceof UnauthorizedHttpException) {
            $event->setResponse(AbstractApiController::jsonResponse(
                [],
                Response::HTTP_UNAUTHORIZED,
                ResponseMessageEnum::UNAUTHORIZED)
            );
            return;
        }

        if ($exception instanceof FormValidationException) {
            $event->setResponse(AbstractApiController::jsonResponse(
                AbstractApiController::getErrorsFromForm($exception->getForm()),
                Response::HTTP_BAD_REQUEST,
                ResponseMessageEnum::BAD_REQUEST)
            );
            return;
        }

        if ($exception instanceof AbstractAllowedMessageException) {
            $event->setResponse(AbstractApiController::jsonResponse(
                [],
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception->getMessage()
            ));
            return;
        }

        $event->setResponse(AbstractApiController::jsonResponse(
            [],
            Response::HTTP_INTERNAL_SERVER_ERROR,
            ResponseMessageEnum::INTERNAL_ERROR)
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => "handleHttpException"];
    }
}
