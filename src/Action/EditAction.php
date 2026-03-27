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

namespace F0ska\AutoGridBundle\Action;

use F0ska\AutoGridBundle\Builder\EntityBuilder;
use F0ska\AutoGridBundle\Model\AutoGrid;
use F0ska\AutoGridBundle\Model\Parameters;
use F0ska\AutoGridBundle\Service\FormProcessorService;

class EditAction extends AbstractAction
{
    protected EntityBuilder $entityBuilder;
    private FormProcessorService $formProcessor;

    public function __construct(
        EntityBuilder $entityBuilder,
        FormProcessorService $formProcessor
    ) {
        $this->entityBuilder = $entityBuilder;
        $this->formProcessor = $formProcessor;
    }

    public function execute(AutoGrid $autoGrid, Parameters $parameters): void
    {
        $entity = $this->entityBuilder->loadEntity($parameters);
        $this->formProcessor->process($entity, $autoGrid, $parameters);
    }

    public function isIdRequired(): bool
    {
        return true;
    }
}
