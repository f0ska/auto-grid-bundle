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

namespace F0ska\AutoGridBundle\Service;

use F0ska\AutoGridBundle\Model\Parameters;

class ParametersService
{
    public const MAPPING_FIELD               = 'field';
    public const MAPPING_ASSOC               = 'associated';
    public const MAPPING_ASSOCIATED_SUBFIELD = 'associated_subfield';
    public const MAPPING_PURE_VIRTUAL        = 'pure_virtual';

    public function __construct(
        private readonly ConfigurationService $configuration,
        private readonly GridUrlGenerator $urlGenerator,
        private readonly EntityAttributesBuilder $entityAttributesBuilder,
        private readonly FieldBuilder $fieldBuilder
    ) {
    }

    public function createParametersModel(array $initialParameters): Parameters
    {
        $parameters = new Parameters($initialParameters, $this);
        $this->buildAttributes($parameters);
        return $parameters;
    }

    public function getTemplate(string $code): string
    {
        return $this->configuration->getTemplate($code);
    }

    public function getActionUrl(string $action, array $params, Parameters $parameters): string
    {
        return $this->urlGenerator->generate($action, $params, $parameters);
    }

    private function buildAttributes(Parameters $parameters): void
    {
        $this->buildEntityFields($parameters);
        $this->buildEntityAttributes($parameters);
    }

    private function buildEntityAttributes(Parameters $parameters): void
    {
        $this->entityAttributesBuilder->build($parameters);
    }

    private function buildEntityFields(Parameters $parameters): void
    {
        $this->fieldBuilder->build($parameters);
    }

}
