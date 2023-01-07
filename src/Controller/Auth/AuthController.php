<?php

namespace App\Controller\Auth;

use App\Controller\AbstractCrudController;
use App\Entity\Folder\Folder;
use App\Entity\User\User;
use App\Form\User\UserCreationType;
use App\Service\AuthManager;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/auth")
 */
class AuthController extends AbstractCrudController
{

    private AuthManager $authManager;

    public function __construct(
        AuthManager            $authManager,
        EntityManagerInterface $entityManager
    )
    {
        parent::__construct($entityManager);
        $this->authManager = $authManager;
    }

    /**
     * @Route("/signup", name="sign_up", methods={"POST"})
     * @OA\Tag(name="Auth")
     */
    public function signup(Request $request): JsonResponse
    {
        return $this->createEntity(
            $request,
            new User(),
            UserCreationType::class,
            function (User $user) {
                $this->authManager->createRandomPassword($user);
                $root = new Folder();
                $root->setCreator($user);
                $user->setRoot($root);
            }
        );
    }

}