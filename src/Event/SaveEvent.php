<?php
/*
 * This file is part of the F0ska/AutoGrid package.
 *
 * (c) Victor Shvets
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace F0ska\AutoGridBundle\Event;

use F0ska\AutoGridBundle\Model\Parameters;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class SaveEvent extends Event
{
    public const string EVENT_NAME = 'f0ska.autogrid.entity.save';

    private object $entity;
    private FormInterface $form;
    private Parameters $parameters;

    public function __construct(object $entity, FormInterface $form, Parameters $parameters)
    {
        $this->entity = $entity;
        $this->form = $form;
        $this->parameters = $parameters;
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    public function getForm(): FormInterface
    {
        return $this->form;
    }

    public function getParameters(): Parameters
    {
        return $this->parameters;
    }
}
