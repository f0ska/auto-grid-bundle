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

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\ToManyAssociationMapping;
use F0ska\AutoGridBundle\Model\FieldParameter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class GuesserService
{
    private MetaDataService $metaDataService;
    private FormRegistryInterface $formRegistry;
    private ConfigurationService $configuration;

    public function __construct(
        MetaDataService $metaDataService,
        FormRegistryInterface $formRegistry,
        ConfigurationService $configuration
    ) {
        $this->metaDataService = $metaDataService;
        $this->formRegistry = $formRegistry;
        $this->configuration = $configuration;
    }

    public function guessFieldFormType(FieldParameter $field, string $agId): void
    {
        if (!empty($field->attributes['form']['type'])) {
            return;
        }
        $field->attributes['form']['type'] = TextType::class;
        $this->guessFormType($field, $agId);
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
            $options = ($field->attributes['form']['options'] ?? []) + $guess->getOptions();
            $field->attributes['form']['options'] = $options;
        }

        $this->guessSpecificTypes($field);
        $this->guessDateTypes($field);
        $this->guessGenericAttributes($field);
    }

    private function guessSpecificTypes(FieldParameter $field): void
    {
        if ($field->fieldMapping->enumType) {
            $field->attributes['form']['type'] = EnumType::class;
            $field->attributes['form']['options']['class'] = $field->fieldMapping->enumType;
        }

        switch ($field->fieldMapping->type) {
            case Types::JSON:
            case Types::OBJECT:
                $field->attributes['form']['type'] = TextareaType::class;
                $field->attributes['form']['transformer'] = $this->getJsonTransformer(
                    $field->fieldMapping->type === Types::OBJECT
                );
                break;
            case 'date_point':
                $field->attributes['form']['type'] = DateTimeType::class;
                $field->attributes['form']['transformer'] = $this->getDatePointTransformer();
                break;
        }
    }

    private function guessDateTypes(FieldParameter $field): void
    {
        switch ($field->fieldMapping->type) {
            case Types::DATE_IMMUTABLE:
            case Types::DATE_MUTABLE:
            case Types::DATETIME_MUTABLE:
            case Types::DATETIME_IMMUTABLE:
            case Types::DATETIMETZ_MUTABLE:
            case Types::DATETIMETZ_IMMUTABLE :
            case Types::TIME_MUTABLE:
            case Types::TIME_IMMUTABLE :
            case 'date_point':
                $field->attributes['form']['options']['widget'] = 'single_text';
                if (!isset($field->attributes['range_filter'])) {
                    $field->attributes['range_filter'] = $this->configuration->formDateAsRange();
                }
                break;
        }
    }

    private function guessGenericAttributes(FieldParameter $field): void
    {
        if ($field->fieldMapping->length) {
            $field->attributes['form']['options']['constraints'][] = new Length(
                max: $field->fieldMapping->length
            );
            $field->attributes['form']['options']['attr']['maxlength'] = $field->fieldMapping->length;
        }

        if ($field->fieldMapping->scale > 0) {
            $field->attributes['form']['options']['attr']['step'] = 0.1 ** $field->fieldMapping->scale;
        }

        $required = $field->fieldMapping->nullable === false && $field->fieldMapping->type !== 'boolean';
        $field->attributes['form']['options']['required'] = $required;
        if ($required) {
            $field->attributes['form']['options']['constraints'][] = new NotBlank();
        } elseif ($field->fieldMapping->nullable === false) {
            $field->attributes['form']['options']['constraints'][] = new NotNull();
        }
    }

    private function getJsonTransformer(bool $isObject): CallbackTransformer
    {
        return new CallbackTransformer(
            function (array|object|null $data): ?string {
                if (empty($data)) {
                    return null;
                }
                return json_encode(
                    $data,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
            },
            function (?string $json) use ($isObject): array|object|null {
                if (empty($json)) {
                    return null;
                }
                $data = json_decode($json, !$isObject);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new TransformationFailedException(json_last_error_msg());
                }
                return $data;
            }
        );
    }

    private function getDatePointTransformer(): CallbackTransformer
    {
        return new CallbackTransformer(
            function (?DateTimeInterface $data): ?DateTimeInterface {
                if (empty($data)) {
                    return null;
                }
                return new DateTimeImmutable($data->format(DateTimeInterface::ATOM));
            },
            function (?DateTimeInterface $data): ?DateTimeInterface {
                if (empty($data)) {
                    return null;
                }
                return new DatePoint($data->format(DateTimeInterface::ATOM));
            }
        );
    }

    public function guessAssociatedFormType(FieldParameter $field): void
    {
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
