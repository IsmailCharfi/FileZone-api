<?php

namespace App\EventSubscriber\Authentication;

use App\Controller\AbstractApiController;
use App\Enum\ResponseMessageEnum;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\DisabledException;

class AuthenticationFailureEventSubscriber implements EventSubscriberInterface
{
    public function onAuthenticationFailure(AuthenticationFailureEvent $event)
    {
        $event->setResponse(AbstractApiController::jsonResponse(
            [],
            Response::HTTP_UNAUTHORIZED,
            ResponseMessageEnum::LOGIN_FAILURE
        ));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::AUTHENTICATION_FAILURE => "onAuthenticationFailure",
        ];
    }
}