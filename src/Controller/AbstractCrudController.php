<?php

namespace App\Controller;

use App\Entity\AbstractEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractCrudController extends AbstractApiController
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @throws FormValidationException
     */
    protected function createEntity(
        Request        $request,
        AbstractEntity $entity,
        string         $formType,
        callable       $afterSubmit = null,
        callable       $export = null
    ): JsonResponse
    {
        return $this->getDataAndSubmit($request, $entity, $formType, true, $afterSubmit, $export);
    }

    /**
     * @throws FormValidationException
     */
    protected function updateEntity(
        Request        $request,
        AbstractEntity $entity,
        string         $formType,
        callable       $afterSubmit = null,
        callable       $export = null
    ): JsonResponse
    {
        return $this->getDataAndSubmit($request, $entity, $formType, false, $afterSubmit, $export);
    }

    protected function deleteEntity(AbstractEntity $entity): JsonResponse
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return $this->successReturn();
    }

    /**
     * @throws FormValidationException
     */
    private function getDataAndSubmit(
        Request        $request,
        AbstractEntity $entity,
        string         $formType,
        bool           $createMode,
        callable       $afterSubmit = null,
        callable       $export = null
    ): JsonResponse
    {
        $data = $this->getRequestData($request);
        $form = $this->createForm($formType, $entity);

        $form->submit($data);
        if ($form->isSubmitted() && $form->isValid()) {

            if ($afterSubmit) {
                $afterSubmit($entity);
            }
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            if ($createMode) return $this->createdReturn($export ? $export($entity) : $entity->export());
            return $this->successReturn($export ? $export($entity) : $entity->export());
        }
        throw  new FormValidationException($form);
    }

}