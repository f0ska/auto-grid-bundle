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

use Doctrine\ORM\Mapping\ToManyAssociationMapping;
use F0ska\AutoGridBundle\Model\FieldParameter;
use F0ska\AutoGridBundle\Model\Parameters;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormRegistryInterface;
use function Symfony\Component\String\u;

class AttributeService
{
    public const MAPPING_FIELD   = 'field';
    public const MAPPING_ASSOC   = 'associated';
    public const MAPPING_VIRTUAL = 'virtual';

    private MetaDataService $metaDataService;
    private PermissionService $permissionService;
    private FormRegistryInterface $formRegistry;
    private ConfigurationService $configuration;

    public function __construct(
        MetaDataService $metaDataService,
        PermissionService $permissionService,
        FormRegistryInterface $formRegistry,
        ConfigurationService $configuration
    ) {
        $this->metaDataService = $metaDataService;
        $this->permissionService = $permissionService;
        $this->formRegistry = $formRegistry;
        $this->configuration = $configuration;
    }

    public function buildAttributes(Parameters $parameters): void
    {
        $this->buildEntityFields($parameters);
        $this->buildEntityAttributes($parameters);
    }

    private function buildEntityAttributes(Parameters $parameters): void
    {
        $agId = $parameters->agId;
        $metadata = $this->metaDataService->getMetadata($agId);
        $default = [
                'title' => u($metadata->rootEntityName)
                    ->afterLast('\\')
                    ->snake()
                    ->replace('_', ' ')
                    ->title(true)
                    ->toString(),
                'entity' => $metadata->rootEntityName,
            ] + $this->configuration->getDefaultButtonsPositions();

        $parameters->attributes = $this->metaDataService->getEntityAttributes($agId) + $default;
        $parameters->permissions = $this->permissionService->getEntityActionPermissions($agId);
    }

    private function buildEntityFields(Parameters $parameters): void
    {
        $agId = $parameters->agId;
        $this->initFields($parameters);
        $this->initAssociations($parameters);
        foreach ($parameters->fields as $field) {
            $this->buildFieldAttributes($field, $agId);
            $this->buildFieldPermissions($field, $agId);
        }
        ksort($parameters->fields);
        $parameters->fields = array_combine(
            array_map(fn(FieldParameter $field) => $field->name, $parameters->fields),
            $parameters->fields
        );
    }

    private function initFields(Parameters $parameters): void
    {
        $metadata = $this->metaDataService->getMetadata($parameters->agId);
        foreach ($metadata->getFieldNames() as $key => $column) {
            $position = $this->metaDataService->getEntityFieldAttribute($parameters->agId, $column, 'position') ?? 0;
            $parameters->fields[sprintf('%d_%d', $position, $key)] = new FieldParameter(
                ['name' => $column, 'mappingType' => self::MAPPING_FIELD]
            );
        }
    }

    private function initAssociations(Parameters $parameters): void
    {
        $metadata = $this->metaDataService->getMetadata($parameters->agId);
        foreach ($metadata->getAssociationNames() as $key => $column) {
            $position = $this->metaDataService->getEntityFieldAttribute($parameters->agId, $column, 'position') ?? 0;
            $agSubId = $this->metaDataService->add($metadata->getAssociationTargetClass($column), null, true);
            $parameters->fields[sprintf('%d_%d', $position, $key)] = new FieldParameter(
                [
                    'name' => $column,
                    'mappingType' => self::MAPPING_ASSOC,
                    'agSubId' => $agSubId,
                ]
            );
            $this->initVirtualFields($parameters, $column, $agSubId, $key);
        }
    }

    private function initVirtualFields(
        Parameters $parameters,
        string $column,
        string $agSubId,
        int $parentPosition
    ): void {
        $fields = $this->metaDataService->getEntityFieldAttribute($parameters->agId, $column, 'fields');
        if (!empty($fields)) {
            $key = 0;
            foreach ($fields as $field) {
                $subfield = sprintf('%s.%s', $column, $field['name']);
                $position = $field['position'] ?? $this->metaDataService
                    ->getEntityFieldAttribute($parameters->agId, $subfield, 'position') ?? $parentPosition;
                $index = sprintf('%d_%d_%d', $position, $parentPosition, $key++);
                $parameters->fields[$index] = new FieldParameter(
                    [
                        'name' => sprintf('%s:%s', $column, $field['name']),
                        'mappingType' => self::MAPPING_VIRTUAL,
                        'agId' => $agSubId,
                        'subName' => $field['name'],
                        'subObject' => $column,
                        'attributes' => $field,
                    ]
                );
            }
        }
    }

    private function buildFieldAttributes(FieldParameter $field, string $agId): void
    {
        switch ($field->mappingType) {
            case self::MAPPING_FIELD:
                $this->buildField($field, $agId);
                break;
            case self::MAPPING_ASSOC:
                $this->buildAssociated($field, $agId);
                break;
            case self::MAPPING_VIRTUAL:
                $this->buildField($field, $field->agId);
                break;
        }

        if (!isset($field->attributes['label'])) {
            $field->attributes['label'] = u($field->name)->snake()->replace('_', ' ')->title()->toString();
        }
    }

    private function buildFieldPermissions(FieldParameter $field, string $agId): void
    {
        $field->permissions = $this->permissionService->getEntityFieldActionPermissions($agId, $field->name);
    }

    private function buildField(FieldParameter $field, string $agId): void
    {
        $name = $field->subName ?? $field->name;
        $metadata = $this->metaDataService->getMetadata($agId);
        $hasIndex = $metadata->isIndexed($name) || $metadata->isIdentifier($name) || $metadata->isUniqueField($name);
        $field->fieldMapping = $metadata->getFieldMapping($name);

        $attributes = $this->metaDataService->getEntityFieldAttributes($agId, $name);
        foreach ($attributes as $key => $value) {
            if ($value !== null && !isset($field->{$key})) {
                $field->attributes[$key] = $value;
            }
        }

        $field->canSort = $field->attributes['can_sort'] ?? $hasIndex;
        $field->canFilter = $field->attributes['can_filter'] ?? $hasIndex;

        if (empty($field->attributes['form']['type'])) {
            $field->attributes['form']['type'] = TextType::class;
            $this->guessFormType($field, $agId);
        }
    }

    private function guessFormType(FieldParameter $field, string $agId): void
    {
        $metadata = $this->metaDataService->getMetadata($agId);
        $guesser = $this->formRegistry->getTypeGuesser();
        if ($guesser) {
            $guess = $guesser->guessType($metadata->rootEntityName, $field->subName ?? $field->name);
            if ($guess->getType() === CheckboxType::class && $this->configuration->formBooleanAsSelect()) {
                $field->attributes['form']['type'] = ChoiceType::class;
                $field->attributes['form']['options'] = [
                    'required' => true,
                    'expanded' => false,
                    'choices' => ['f0ska.autogrid.choice.yes' => '1', 'f0ska.autogrid.choice.no' => '0'],
                ];
                return;
            }
            $field->attributes['form']['type'] = $guess->getType();
            $extra = ['required' => !$field->fieldMapping->nullable && $field->fieldMapping->type !== 'boolean'];
            $options = ($field->attributes['form']['options'] ?? []) + $guess->getOptions() + $extra;
            $field->attributes['form']['options'] = $options;
        }
    }

    private function buildAssociated(FieldParameter $field, string $agId): void
    {
        $name = $field->name;
        $metadata = $this->metaDataService->getMetadata($agId);
        $field->associationMapping = $metadata->getAssociationMapping($name);

        $attributes = $this->metaDataService->getEntityFieldAttributes($agId, $name);
        foreach ($attributes as $key => $value) {
            if ($value !== null) {
                $field->attributes[$key] = $value;
            }
        }

        $field->canSort = $field->attributes['can_sort'] ?? true;
        $field->canFilter = $field->attributes['can_filter'] ?? true;

        $field->attributes['form']['type'] = EntityType::class;
        $field->attributes['form']['options']['class'] = $field->associationMapping->targetEntity;
        if ($field->associationMapping instanceof ToManyAssociationMapping) {
            $field->attributes['form']['options']['multiple'] = true;
            $field->canSort = false;
        }
        if (
            empty($field->attributes['form']['options']['choice_label'])
            && empty($field->attributes['form']['choices'])
        ) {
            $field->attributes['form']['options']['choice_label'] = $this->guessChoiceLabel($field->agSubId);
        }
    }

    private function guessChoiceLabel(string $agId): string
    {
        $vars = ['title', 'label', 'name', 'code', 'model', 'reference', 'sku', 'uuid'];
        $names = $this->metaDataService->getMetadata($agId)->getFieldNames();
        foreach ($vars as $var) {
            foreach ($names as $name) {
                if (str_contains(strtolower($name), $var)) {
                    return $name;
                }
            }
        }
        return 'id';
    }
}
