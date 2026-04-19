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

use Doctrine\DBAL\Types\Types;
use F0ska\AutoGridBundle\DBAL\TypesCompatibility;
use F0ska\AutoGridBundle\Model\FieldParameter;
use F0ska\AutoGridBundle\View\BinaryViewService;
use F0ska\AutoGridBundle\View\ChoiceViewService;
use F0ska\AutoGridBundle\View\DefaultViewService;
use F0ska\AutoGridBundle\View\ViewServiceRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class TemplateGuesserService
{
    private array $templateByFormType = [
        CheckboxType::class => 'boolean',
        EntityType::class   => 'debug',
    ];
    private array $templateByDbalType = [
        Types::SIMPLE_ARRAY              => 'simple_array',
        Types::JSON                      => 'json',
        TypesCompatibility::TYPES_ARRAY  => 'json',
        TypesCompatibility::TYPES_OBJECT => 'json',
        Types::ASCII_STRING              => 'ascii_string',
        Types::BINARY                    => 'binary',
        Types::BLOB                      => 'binary',
    ];
    private array $dateFormats = [
        Types::DATE_MUTABLE                  => 'date',
        Types::DATE_IMMUTABLE                => 'date',
        Types::DATEINTERVAL                  => 'interval',
        Types::DATETIME_MUTABLE              => 'datetime',
        Types::DATETIME_IMMUTABLE            => 'datetime',
        Types::DATETIMETZ_MUTABLE            => 'datetime',
        Types::DATETIMETZ_IMMUTABLE          => 'datetime',
        Types::TIME_MUTABLE                  => 'time',
        Types::TIME_IMMUTABLE                => 'time',
        TypesCompatibility::TYPES_DATE_POINT => 'datetime',
        TypesCompatibility::TYPES_DAY_POINT  => 'date',
        TypesCompatibility::TYPES_TIME_POINT => 'time',
    ];

    private ConfigurationService $configuration;
    private ViewServiceRegistry $viewServiceRegistry;

    public function __construct(
        ConfigurationService $configuration,
        ViewServiceRegistry $viewServiceRegistry
    ) {
        $this->configuration = $configuration;
        $this->viewServiceRegistry = $viewServiceRegistry;
    }

    public function guess(FieldParameter $field, array $entityAttributes = []): void
    {
        $this->guessViewService($field);

        $this->setTruncate($field);
        $this->setFormat($field);
        $this->setFormExtra($field);

        $this->guessTemplate($field, $entityAttributes);
    }

    private function guessViewService(FieldParameter $field): void
    {
        $serviceId = $field->attributes['view_service'] ?? null;

        if ($serviceId && $this->viewServiceRegistry->has($serviceId)) {
            $field->view['service'] = $serviceId;
            return;
        }

        if (isset($field->view['choices'])) {
            $field->view['service'] = ChoiceViewService::class;
            return;
        }

        if (in_array($field->fieldMapping?->type, [Types::BINARY, Types::BLOB], true)) {
            $field->view['service'] = BinaryViewService::class;
            return;
        }

        $field->view['service'] = DefaultViewService::class;
    }

    private function guessTemplate(FieldParameter $field, array $entityAttributes): void
    {
        if (!empty($field->view['template'])) {
            return;
        }

        $template = $field->attributes['view_template'] ?? null;

        if ($template) {
            $field->view['template'] = $template;
            return;
        }

        if (isset($entityAttributes['template']['grid']['column_value'])) {
            $field->view['template'] = $entityAttributes['template']['grid']['column_value'];
            return;
        }

        if (isset($field->view['choices'])) {
            $field->view['template'] = $this->configuration->getFieldTemplate('choice');
            return;
        }

        $field->view['template'] = $this->getFieldTemplate($field);
    }

    private function setTruncate(FieldParameter $field): void
    {
        $field->view['grid_truncate'] = $this->configuration->getGridTextTruncate();
        if (!empty($field->attributes['grid_truncate'])) {
            $field->view['grid_truncate'] = $field->attributes['grid_truncate'];
        }
    }

    private function setFormat(FieldParameter $field): void
    {
        $type = $field->fieldMapping?->type;
        if (!$type || !isset($this->dateFormats[$type])) {
            return;
        }
        if (empty($field->view['format'])) {
            $field->view['format'] = $this->configuration->getFieldFormat($this->dateFormats[$type]);
        }
        if (empty($field->view['template'])) {
            $field->view['template'] = $this->configuration->getFieldTemplate('date');
        }
    }

    private function getFieldTemplate(FieldParameter $field): string
    {
        $type = $field->attributes['form']['type'] ?? '';
        $code = $this->templateByFormType[$type] ?? null;
        if ($code) {
            return $this->configuration->getFieldTemplate($code);
        }

        $dbalType = $field->fieldMapping->type ?? '';
        $code = $this->templateByDbalType[$dbalType] ?? null;
        if ($code) {
            return $this->configuration->getFieldTemplate($code);
        }

        return $this->configuration->getFieldTemplate('text');
    }

    private function setFormExtra(FieldParameter $field): void
    {
        if (isset($field->view['money_pattern']) && str_contains($field->view['money_pattern'], '{{ widget }}')) {
            [$prefix, $suffix] = explode('{{ widget }}', $field->view['money_pattern']);
            if (!isset($field->attributes['value_prefix'])) {
                $field->attributes['value_prefix'] = $prefix;
            }
            if (!isset($field->attributes['value_suffix'])) {
                $field->attributes['value_suffix'] = $suffix;
            }
        }
    }
}
