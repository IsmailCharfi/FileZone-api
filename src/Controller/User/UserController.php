<?php

namespace App\Controller\User;

use App\Controller\AbstractCrudController;
use App\Entity\User\User;
use App\Form\User\UserCreationType;
use App\Service\AuthManager;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/users")
 */
class UserController extends AbstractCrudController
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
     * @Route("/connected-user", name="get_connected_user", methods={"GET"})
     * @OA\Tag(name="User")
     */
    public function getConnectedUserData(): JsonResponse
    {
        return $this->successReturn($this->getConnectedUser()->export());
    }

    /**
     * @Route("/{id}", name="users_get_one_by_id", methods={"GET"})
     * @OA\Tag(name="User")
     */
    public function getUserById(User $user): JsonResponse
    {
        return $this->successReturn($user->export());
    }

    /**
     * @Route("/activate/{id}", name="activate_account_from_admin", methods={"POST"})
     * @OA\Tag(name="User")
     */
    public function activateAccount(User $user): JsonResponse
    {

        $this->authManager->activateAccount($user);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->successReturn();
    }

    /**
     * @Route("/deactivate/{id}", name="deactivate_account_from_admin", methods={"POST"})
     * @OA\Tag(name="User")
     */
    public function deactivateAccount(User $user): JsonResponse
    {

        $this->authManager->deactivateAccount($user);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->successReturn();
    }

    /**
     * @Route("", name="create_account", methods={"POST"})
     * @OA\Tag(name="User")
     */
    public function create(Request $request): JsonResponse
    {
        return $this->createEntity(
            $request,
            new User(),
            UserCreationType::class,
            fn(User $user) => $this->authManager->createRandomPassword($user)
        );
    }

    /**
     * @Route("/{id}", name="update_user", methods={"POST"})
     * @OA\Tag(name="User")
     */
    public function update(Request $request, User $user): JsonResponse
    {
        return $this->updateEntity($request, $user, UserCreationType::class);
    }

    /**
     * @Route("/{id}", name="delete_user", methods={"DELETE"})
     * @OA\Tag(name="User")
     */
    public function delete(User $user): JsonResponse
    {
        return $this->deleteEntity($user);
    }
}