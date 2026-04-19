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

namespace F0ska\AutoGridBundle\Service\Provider;

use F0ska\AutoGridBundle\Model\FieldParameter;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChoiceProvider
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getLabels(mixed $values, FieldParameter $field): array
    {
        if ($values === null) {
            return [];
        }
        if (!is_iterable($values)) {
            $values = [$values];
        }
        if (empty($values)) {
            return [];
        }
        $result = [];
        foreach ($values as $value) {
            $key = $this->resolveKey($value);
            $choice = $this->getSelectedChoice($field, $key);
            if ($choice) {
                $label = $choice->label;
                if ($label instanceof TranslatableInterface) {
                    $label = $label->trans($this->translator);
                } elseif (is_string($label)) {
                    $label = $this->translator->trans($label);
                }
                $result[] = $label;
            }
        }
        return $result;
    }

    public function getValues(mixed $values, FieldParameter $field): array
    {
        if ($values === null) {
            return [];
        }
        if (!is_iterable($values)) {
            $values = [$values];
        }
        $result = [];
        foreach ($values as $value) {
            $key = $this->resolveKey($value);
            $choice = $this->getSelectedChoice($field, $key);
            if ($choice) {
                $result[] = $choice->value;
            }
        }
        return $result;
    }

    private function resolveKey(mixed $value): mixed
    {
        if (is_object($value)) {
            return enum_exists($value::class) ? $value->value : (method_exists($value, 'getId') ? $value->getId(
            ) : (string) $value);
        }
        return $value;
    }

    private function getSelectedChoice(FieldParameter $field, mixed $key): ?ChoiceView
    {
        $type = gettype($key);
        foreach ($field->view['choices'] ?? [] as $choice) {
            $value = $choice->value;
            settype($value, $type);
            if ($value === $key) {
                return $choice;
            }
        }
        return null;
    }
}
