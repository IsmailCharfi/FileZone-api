<?php

namespace App\EventSubscriber\Authentication;

use App\Controller\AbstractApiController;
use App\Entity\User\User;
use App\Enum\ResponseMessageEnum;
use App\Repository\User\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationSuccessEventSubscriber implements EventSubscriberInterface
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event)
    {
        /**
         * fetch the user even though we have it in the event
         * the reason is that the user in event is UserInterface type not User
         * @var User
         */
        $user = $this->userRepository->findOneBy(['email' => $event->getUser()->getUserIdentifier()]);

        if (!$user->getIsActive()) {
            $event->getResponse()->setStatusCode(Response::HTTP_UNAUTHORIZED);
            $event->setData([
                'status' => Response::HTTP_UNAUTHORIZED,
                'message' => ResponseMessageEnum::ACCOUNT_DEACTIVATED,
                'data' => [],
            ]);
            return;
        }

        $event->setData([
            'status' => Response::HTTP_OK,
            'message' => "success",
            'data' => [
                'user' => $user->export(),
                'token' => $event->getData()["token"],
            ],
        ]);
    }

    public static function getSubscribedEvents(): array
    {
        return [Events::AUTHENTICATION_SUCCESS => "onAuthenticationSuccess"];
    }
}