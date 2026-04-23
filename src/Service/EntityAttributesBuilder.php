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

use Doctrine\ORM\Mapping\ClassMetadata;
use F0ska\AutoGridBundle\Model\Parameters;

use function Symfony\Component\String\u;

class EntityAttributesBuilder
{
    public function __construct(
        private readonly ConfigurationService $configuration,
        private readonly MetaDataService $metaDataService,
        private readonly PermissionService $permissionService
    ) {
    }

    public function build(Parameters $parameters): void
    {
        $agId = $parameters->agId;
        $metadata = $this->metaDataService->getMetadata($agId);
        $default = [
            'title'  => $this->buildEntityTitle($metadata),
            'entity' => $metadata->rootEntityName,
        ];

        $parameters->permissions = $this->permissionService->getEntityActionPermissions($agId);
        $parameters->attributes = $this->metaDataService->getEntityAttributes($agId) + $default;

        $buttons = $this->configuration->getDefaultButtonsPositions();
        foreach ($buttons as $button => $positions) {
            foreach ($positions as $position => $enabled) {
                if (!isset($parameters->attributes['button'][$button][$position])) {
                    $parameters->attributes['button'][$button][$position] = $enabled;
                }
            }
        }
    }

    private function buildEntityTitle(ClassMetadata $metadata): ?string
    {
        if (!$this->configuration->showEntityTitle()) {
            return null;
        }

        return u($metadata->rootEntityName)
            ->afterLast('\\')
            ->snake()
            ->replace('_', ' ')
            ->title(true)
            ->toString()
        ;
    }
}
