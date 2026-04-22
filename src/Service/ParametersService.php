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
use F0ska\AutoGridBundle\Model\FieldParameter;
use F0ska\AutoGridBundle\Model\Parameters;
use Symfony\Component\Routing\RouterInterface;

use function Symfony\Component\String\u;

class ParametersService
{
    public const MAPPING_FIELD               = 'field';
    public const MAPPING_ASSOC               = 'associated';
    public const MAPPING_ASSOCIATED_SUBFIELD = 'associated_subfield';
    public const MAPPING_PURE_VIRTUAL        = 'pure_virtual';

    public function __construct(
        private readonly EncoderService $encoderService,
        private readonly RouterInterface $router,
        private readonly ConfigurationService $configuration,
        private readonly ActionParametersListService $actionParametersList,
        private readonly MetaDataService $metaDataService,
        private readonly PermissionService $permissionService,
        private readonly GuesserService $guesserService
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
        $request = $parameters->request;
        $callback = fn($value) => $value !== null && $value != '' && $value !== [];

        foreach ($params as $key => $value) {
            if (!$this->actionParametersList->hasParameter($key)) {
                continue;
            }
            $value = $this->actionParametersList->normalizeParameter($key, $value, $parameters);
            if (is_array($value) && !empty($request[$key]) && is_array($request[$key])) {
                $request[$key] = array_filter(array_merge($request[$key], $value), $callback);
                continue;
            }
            $request[$key] = is_array($value) ? array_filter($value, $callback) : $value;
        }

        $request = array_filter($request, $callback);

        if (!empty($parameters->attributes['route'][$action])) {
            return $this->buildCustomRouteUrl($action, $request, $parameters);
        }

        return $this->router->generate(
            $parameters->route['name'],
            array_merge(
                $parameters->route['params'],
                $this->buildActionParams($parameters, $action, $request)
            )
        );
    }

    private function buildAttributes(Parameters $parameters): void
    {
        $this->buildEntityFields($parameters);
        $this->buildEntityAttributes($parameters);
    }

    private function buildEntityAttributes(Parameters $parameters): void
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

    private function buildEntityFields(Parameters $parameters): void
    {
        $agId = $parameters->agId;
        $this->initFields($parameters);
        $this->initAssociations($parameters);
        foreach ($parameters->fields as $field) {
            $field->parameters = $parameters;
            $this->buildFieldPermissions($field, $agId);
            $this->buildFieldAttributes($field);
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
            $this->initField($parameters, $column, $key, self::MAPPING_FIELD);
        }
        foreach ($this->metaDataService->getPureVirtualFieldNames($parameters->agId) as $key => $column) {
            $this->initField($parameters, $column, $key, self::MAPPING_PURE_VIRTUAL);
        }
    }

    private function initField(Parameters $parameters, string $column, $key, string $mappingType): void
    {
        $position = $this->metaDataService->getEntityFieldAttribute($parameters->agId, $column, 'position') ?? 0;
        $parameters->fields[sprintf('%d_%d', $position, $key)] = new FieldParameter(
            ['name' => $column, 'mappingType' => $mappingType, 'agId' => $parameters->agId]
        );
    }

    private function initAssociations(Parameters $parameters): void
    {
        $metadata = $this->metaDataService->getMetadata($parameters->agId);
        foreach ($metadata->getAssociationNames() as $key => $column) {
            $position = $this->metaDataService->getEntityFieldAttribute($parameters->agId, $column, 'position') ?? 0;
            $agSubId = $this->metaDataService->add($metadata->getAssociationTargetClass($column), null, true);
            $parameters->fields[sprintf('%d_a%d', $position, $key)] = new FieldParameter(
                [
                    'name'        => $column,
                    'mappingType' => self::MAPPING_ASSOC,
                    'agId'        => $parameters->agId,
                    'agSubId'     => $agSubId,
                ]
            );
            $this->initAssociatedSubfields($parameters, $column, $agSubId, $key);
        }
    }

    private function initAssociatedSubfields(
        Parameters $parameters,
        string $column,
        string $agSubId,
        int $parentPosition
    ): void {
        $key = 0;
        $fields = $this->metaDataService->getEntityFieldAttribute($parameters->agId, $column, 'fields');
        $virtualFields = $this->metaDataService->getPureVirtualFieldNames($agSubId);
        if (!empty($fields)) {
            foreach ($fields as $field) {
                $subfield = sprintf('%s.%s', $column, $field['name']);
                $position = $field['position'] ?? $this->metaDataService
                    ->getEntityFieldAttribute($parameters->agId, $subfield, 'position') ?? $parentPosition;
                $index = sprintf('%d_v%d_%d', $position, $parentPosition, $key++);
                $isVirtual = in_array($field['name'], $virtualFields);
                $parameters->fields[$index] = new FieldParameter(
                    [
                        'name'        => sprintf('%s:%s', $column, $field['name']),
                        'mappingType' => $isVirtual ? self::MAPPING_PURE_VIRTUAL : self::MAPPING_ASSOCIATED_SUBFIELD,
                        'agId'        => $agSubId,
                        'subName'     => $field['name'],
                        'subObject'   => $column,
                        'attributes'  => $field,
                    ]
                );
            }
        }
    }

    private function buildFieldAttributes(FieldParameter $field): void
    {
        switch ($field->mappingType) {
            case self::MAPPING_FIELD:
            case self::MAPPING_ASSOCIATED_SUBFIELD:
                $this->buildField($field);
                break;
            case self::MAPPING_ASSOC:
                $this->buildAssociated($field);
                break;
            case self::MAPPING_PURE_VIRTUAL:
                $this->buildVirtualField($field);
                break;
        }

        if (!isset($field->attributes['label'])) {
            $field->attributes['label'] = u($field->name)->snake()->replace('_', ' ')->title()->toString();
        }
    }

    private function buildFieldPermissions(FieldParameter $field, string $agId): void
    {
        if ($field->mappingType === self::MAPPING_ASSOCIATED_SUBFIELD) {
            $field->permissions = $this->permissionService->getEntityFieldActionPermissions(
                $field->agId,
                $field->subName,
                $agId
            );
            return;
        }
        $field->permissions = $this->permissionService->getEntityFieldActionPermissions($agId, $field->name);
    }

    private function buildField(FieldParameter $field): void
    {
        $name = $field->subName ?? $field->name;
        $metadata = $this->metaDataService->getMetadata($field->agId);
        $hasIndex = $this->hasIndex($metadata, $name);
        $field->fieldMapping = $metadata->getFieldMapping($name);

        $attributes = $this->metaDataService->getEntityFieldAttributes($field->agId, $name);
        foreach ($attributes as $key => $value) {
            if ($value !== null && !isset($field->{$key}) && !isset($field->attributes[$key])) {
                $field->attributes[$key] = $value;
            }
        }

        if ($field->fieldMapping->notInsertable) {
            $field->permissions['create'] = false;
        }
        if ($field->fieldMapping->notUpdatable) {
            $field->permissions['edit'] = false;
        }

        $field->canSort = $field->attributes['can_sort'] ?? $hasIndex;
        $field->canFilter = $field->attributes['can_filter'] ?? $hasIndex;
        $field->filterCondition = $field->attributes['filterable']['condition'] ?? null;

        $this->guesserService->guessFieldFormType($field, $field->agId);
        $this->guesserService->guessFilterCondition($field);
    }

    private function buildVirtualField(FieldParameter $field): void
    {
        $name = $field->subName ?? $field->name;
        $attributes = $this->metaDataService->getEntityFieldAttributes($field->agId, $name);
        foreach ($attributes as $key => $value) {
            if ($value !== null && !isset($field->{$key}) && !isset($field->attributes[$key])) {
                $field->attributes[$key] = $value;
            }
        }

        $field->canSort = false;
        $field->canFilter = false;
        $field->canEdit = false;

        if (empty($attributes['virtual_column']['dql'])) {
            return;
        }

        $field->canSort = $field->attributes['can_sort'] ?? false;
        $parameters = $field->parameters;
        $parameters->query['has_dql'] = true;

        $parameters->query['virtual_alias_map'][$field->name] = $this->buildVirtualDqlAlias($field);
    }

    private function buildVirtualDqlAlias(FieldParameter $field): string
    {
        $metadata = $this->metaDataService->getMetadata($field->parameters->agId);
        return 'vdql_' . md5($metadata->rootEntityName . ':' . $field->name);
    }

    private function hasIndex(ClassMetadata $metadata, string $fieldName): bool
    {
        if (
            $metadata->isIndexed($fieldName)
            || $metadata->isIdentifier($fieldName)
            || $metadata->isUniqueField($fieldName)
        ) {
            return true;
        }

        // Check class-level indexes
        $table = $metadata->table;
        if (!empty($table['indexes'])) {
            foreach ($table['indexes'] as $index) {
                if (in_array($fieldName, $index['columns'])) {
                    return true;
                }
            }
        }

        if (!empty($table['uniqueConstraints'])) {
            foreach ($table['uniqueConstraints'] as $constraint) {
                if (in_array($fieldName, $constraint['columns'])) {
                    return true;
                }
            }
        }

        return false;
    }

    private function buildAssociated(FieldParameter $field): void
    {
        $name = $field->name;
        $metadata = $this->metaDataService->getMetadata($field->agId);
        $field->associationMapping = $metadata->getAssociationMapping($name);

        $attributes = $this->metaDataService->getEntityFieldAttributes($field->agId, $name);
        foreach ($attributes as $key => $value) {
            if ($value !== null) {
                $field->attributes[$key] = $value;
            }
        }

        $field->canSort = $field->attributes['can_sort'] ?? true;
        $field->canFilter = $field->attributes['can_filter'] ?? true;
        $field->filterCondition = $field->attributes['filterable']['condition'] ?? null;

        $this->guesserService->guessAssociatedFormType($field);
        $this->guesserService->guessFilterCondition($field);
    }

    private function buildActionParams(Parameters $parameters, string $action, array $params): array
    {
        $result = ['_fragment' => $parameters->attributes['container_id']];
        if ($this->configuration->isSingleParamRequest()) {
            $result[$this->configuration->getSingleParamRequestCode()] = $this->encoderService
                ->encodeAction($parameters->agId, $action, $params)
            ;
            return $result;
        }
        $result[$this->configuration->getMultiParamRequestId()] = $parameters->agId;
        $result[$this->configuration->getMultiParamRequestAction()] = $action;
        $result[$this->configuration->getMultiParamRequestParams()] = $params;
        return $result;
    }

    private function buildCustomRouteUrl(string $action, array $request, Parameters $parameters): string
    {
        $prefix = $parameters->route['custom_prefix'];
        $route = ($prefix ?? '') . ($parameters->attributes['route'][$action]['route'] ?? $action);
        $params = $parameters->attributes['route'][$action]['parameters'];
        $finalParams = [];

        if (!empty($request['id'])) {
            $finalParams['id'] = (int) $request['id'];
        }

        /** Reuse available request parameters you provide */
        foreach ($params as $key) {
            if (isset($parameters->route['custom'][$key])) {
                $finalParams[$key] = $parameters->route['custom_params'][$key];
            } elseif (isset($parameters->route['params'][$key])) {
                $finalParams[$key] = $parameters->route['params'][$key];
            }
        }

        return $this->router->generate($route, $finalParams);
    }
}
