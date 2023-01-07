<?php

namespace App\Controller;

use App\Entity\User\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractApiController extends AbstractController
{

    public static function jsonResponse(array $data, int $status, string $message = ''): JsonResponse
    {
        return new JsonResponse([
            'status' => $status,
            'message' => $message,
            'data' => $data ?? []
        ], $status);
    }

    public function jsonReturn(array $data, int $status, string $message = ''): JsonResponse
    {
        return $this->json([
            'status' => $status,
            'message' => $message,
            'data' => $data ?? []
        ], $status);
    }

    public function successReturn(array $data = [], string $message = ''): JsonResponse
    {
        return $this->jsonReturn($data, Response::HTTP_OK, $message);
    }

    public function createdReturn(array $data = [], string $message = ''): JsonResponse
    {
        return $this->jsonReturn($data, Response::HTTP_CREATED, $message);
    }

    protected function internalErrorReturn(array $data = [], string $message = 'internal server error'): JsonResponse
    {
        return $this->jsonReturn($data, Response::HTTP_INTERNAL_SERVER_ERROR, $message);
    }

    public function notFoundReturn(array $data = [], string $message = 'not found'): JsonResponse
    {
        return $this->jsonReturn($data, Response::HTTP_NOT_FOUND, $message);
    }

    protected function validationErrorReturn(string $message = '', array $data = []): JsonResponse
    {
        return $this->jsonReturn($data, Response::HTTP_BAD_REQUEST, strlen($message) ? $message : 'Validation error');
    }

    protected function getConnectedUser(): User
    {
        /** @var User $user */
        $user = $this->getUser();

        return $user;
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function getRequestData(Request $request): array
    {
        $requestContent = $request->getContent();

        if (!$requestContent) {
            return [];
        }

        return json_decode($requestContent, true) ?? [];
    }

    protected function formValidationErrorReturn(FormInterface $form): JsonResponse
    {
        $output = self::getErrorsFromForm($form);

        return $this->jsonReturn($output, Response::HTTP_BAD_REQUEST, 'erreur de validation');
    }

    public static function getErrorsFromForm(FormInterface $form, bool $child = false): array
    {
        $errors = [];

        foreach ($form->getErrors() as $error) {
            if ($child) {
                $errors[] = $error->getMessage();
            } else {
                $errors[$error->getOrigin()->getName()][] = $error->getMessage();
            }
        }

        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = self::getErrorsFromForm($childForm, true)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }
}
