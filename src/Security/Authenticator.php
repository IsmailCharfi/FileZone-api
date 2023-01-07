<?php

namespace App\Security;

use App\Entity\User\User;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\DisabledException;

class Authenticator extends JWTAuthenticator
{
    public function doAuthenticate(Request $request)
    {
        $passport =  parent::doAuthenticate($request);

        /**
         * @var User $user
         */
        $user = $passport->getUser();

        if (!$user->getIsActive()) {
            throw new DisabledException('Account deactivated');
        }

        return $passport;
    }

}