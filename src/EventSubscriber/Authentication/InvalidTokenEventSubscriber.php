<?php

namespace App\EventSubscriber\Authentication;

use App\Controller\AbstractApiController;
use App\Enum\ResponseMessageEnum;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\DisabledException;

class InvalidTokenEventSubscriber implements EventSubscriberInterface
{
    public function onInvalidToken(JWTInvalidEvent $event) {
        $exception = $event->getException();

        if ($exception->getPrevious() instanceof DisabledException) {
            $event->setResponse(AbstractApiController::jsonResponse(
                [],
                Response::HTTP_UNAUTHORIZED,
                ResponseMessageEnum::ACCOUNT_DEACTIVATED
            ));
            return;
        }

        $event->setResponse(AbstractApiController::jsonResponse(
            [],
            Response::HTTP_UNAUTHORIZED,
            ResponseMessageEnum::LOGIN_FAILURE
        ));


    }

    public static function getSubscribedEvents(): array
    {
        return [Events::JWT_INVALID => "onInvalidToken"];
    }
}