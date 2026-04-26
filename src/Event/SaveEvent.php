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
    public const EVENT_NAME = 'f0ska.autogrid.entity.save';

    public function __construct(
        private readonly object $entity,
        private readonly FormInterface $form,
        private readonly Parameters $parameters
    )
    {
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
