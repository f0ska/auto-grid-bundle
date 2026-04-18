<?php

declare(strict_types=1);

namespace F0ska\AutoGridBundle\Model;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

class FormProcessResult
{
    private bool $success;
    private object $entity;
    private ?FormInterface $form;

    public function __construct(bool $success, object $entity, ?FormInterface $form = null)
    {
        $this->success = $success;
        $this->entity = $entity;
        $this->form = $form;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    public function getForm(): ?FormInterface
    {
        return $this->form;
    }
}
