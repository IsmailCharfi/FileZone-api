<?php

namespace App\Service;

use App\Entity\User\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationSuccessResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AuthManager
{
    private UserPasswordHasherInterface $passwordHasher;
/*    private MailSender $mailSender;*/
    private JWTTokenManagerInterface $jwtManager;
    private EventDispatcherInterface $dispatcher;
    private const ACTIVATION_LINK_VALIDITY_IN_HOURS = 72; //hours
    private const RESET_PASSWORD_LINK_VALIDITY_IN_HOURS = 2; //hours

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
/*        MailSender                  $mailSender,*/
        JWTTokenManagerInterface    $jwtManager,
        EventDispatcherInterface    $dispatcher
    )
    {
        $this->passwordHasher = $passwordHasher;
        /*$this->mailSender = $mailSender;*/
        $this->jwtManager = $jwtManager;
        $this->dispatcher = $dispatcher;
    }

    public function activateAccount(User $user)
    {
        $user->setIsActive(true);
    }

    public function deactivateAccount(User $user)
    {
        $user->setIsActive(false);
    }

    public function canActivateAccountFromLink(User $user): bool
    {
        return (
            !$user->getIsActive() &&
            $user->getActivationHash() &&
            $user->getActivationLinkSentAt() &&
            $user->getActivationLinkSentAt()->diff(new \DateTime())->h < self::ACTIVATION_LINK_VALIDITY_IN_HOURS
        );
    }

    public function canResetPassword(User $user): bool
    {
        return (
            $user->getIsActive() &&
            $user->getResetPasswordHash() &&
            $user->getResetPasswordLinkSentAt() &&
            $user->getResetPasswordLinkSentAt()->diff(new \DateTime())->h < self::RESET_PASSWORD_LINK_VALIDITY_IN_HOURS
        );
    }

    public function activateAccountFromLink(User $user, string $password)
    {
        $this->activateAccount($user);
        $this->setPassword($user, $password);
        $user->setActivationHash(null);
        $user->setActivationLinkSentAt(null);
        $user->setActivatedAt(new \DateTime());
    }

    public function resetPasswordFromLink(User $user, string $password): bool
    {
        $this->setPassword($user, $password);
        $user->setResetPasswordHash(null);
        $user->setResetPasswordLinkSentAt(null);

        return true;
    }

    public function setPassword(User $user, string $password)
    {
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $password
        );
        $user->setPassword($hashedPassword);
    }

    public function createRandomPassword(User $user)
    {
        $this->setPassword($user, $this->createHash($user));
    }

    public function requestPasswordReset(User $user, string $clientUrl)
    {
        $user->setResetPasswordHash($this->createHash($user));
        $this->sendPasswordResettingMail($user, $clientUrl . "/reset-password/" . $user->getResetPasswordHash());
        $user->setResetPasswordLinkSentAt(new \DateTime());
    }

    public function requestAccountActivation(User $user, string $clientUrl)
    {
        $user->setActivationHash($this->createHash($user));
        $this->sendAccountActivationMail($user, $clientUrl . "/activate-account/" . $user->getActivationHash());
        $user->setActivationLinkSentAt(new \DateTime());
    }

    public function impersonate(User $userToSwitchTo): JWTAuthenticationSuccessResponse
    {
        $jwt = $this->jwtManager->create($userToSwitchTo);
        $response = new JWTAuthenticationSuccessResponse($jwt);
        $event = new AuthenticationSuccessEvent(['token' => $jwt], $userToSwitchTo, $response);

        $this->dispatcher->dispatch($event, Events::AUTHENTICATION_SUCCESS);

        $response->setData($event->getData());

        return $response;
    }

    private function createHash(User $user): string
    {
        return md5(uniqid(rand() . $user->getEmail()) . (new \DateTime())->format('dhIsmY'));
    }

    private function sendAccountActivationMail(User $user, string $clientUrl)
    {
        /*$this
            ->mailSender
            ->send(
                $user->getEmail(),
                "Activation du compte",
                "Mail/Authentication/activate-account.html.twig",
                ['user' => $user, 'link' => $clientUrl],
                2,
                $user,
            );*/
    }

    private function sendPasswordResettingMail(User $user, string $clientUrl)
    {
        /*$this
            ->mailSender
            ->send(
                $user->getEmail(),
                "Reset Password",
                "Mail/Authentication/reset-password.html.twig",
                ['user' => $user, 'link' => $clientUrl],
                2,
                $user,
            );*/
    }
}