<?php

namespace App\Exception;

use Symfony\Component\Form\FormInterface;

class FormValidationException extends \Exception
{
    private FormInterface $form;

    public function __construct(FormInterface $form)
    {
        parent::__construct("Form Validation error");
        $this->form = $form;
    }

    /**
     * @return FormInterface
     */
    public function getForm(): FormInterface
    {
        return $this->form;
    }

    /**
     * @param FormInterface $form
     */
    public function setForm(FormInterface $form): void
    {
        $this->form = $form;
    }
}